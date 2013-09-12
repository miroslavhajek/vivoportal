<?php
namespace Vivo\CMS\Api;

use Vivo\Indexer\Query\QueryInterface;
use Vivo\Indexer\QueryParams;
use Vivo\Indexer\Query\Parser\ParserInterface;
use Vivo\Indexer\IndexerInterface as VivoIndexerInterface;
use Vivo\CMS\Indexer\IndexerHelperInterface;
use Vivo\Indexer\QueryBuilder;
use Vivo\Repository\RepositoryInterface;
use Vivo\Repository\Exception\EntityNotFoundException;
use Vivo\CMS\Model;
use Vivo\CMS\Model\Site;
use Vivo\CMS\Api\DocumentInterface as DocumentApi;
use Vivo\Storage\PathBuilder\PathBuilderInterface;
use Vivo\Repository\EventInterface as RepositoryEventInterface;
use Vivo\Indexer\Query\Term as TermQuery;
use Vivo\Indexer\IndexerEvent;
use Vivo\Repository\Watcher;

use Zend\EventManager\EventManagerInterface;

/**
 * Indexer
 * Indexer API
 */
class Indexer implements IndexerInterface
{
    /**
     * Query Parser
     * @var ParserInterface
     */
    protected $queryParser;

    /**
     * Vivo Indexer
     * @var VivoIndexerInterface
     */
    protected $indexer;

    /**
     * Indexer helper
     * @var IndexerHelperInterface
     */
    protected $indexerHelper;

    /**
     * Query Builder
     * @var QueryBuilder
     */
    protected $qb;

    /**
     * Repository
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * Document API
     * @var DocumentApi
     */
    protected $documentApi;

    /**
     * Path Builder
     * @var PathBuilderInterface
     */
    protected $pathBuilder;

    /**
     * Indexer event manager
     * @var EventManagerInterface
     */
    protected $indexerEvents;

    /**
     * Watcher
     * @var Watcher
     */
    protected $watcher;

    /**
     * Constructor
     * @param \Vivo\Indexer\IndexerInterface $indexer
     * @param \Vivo\CMS\Indexer\IndexerHelperInterface $indexerHelper
     * @param \Vivo\Indexer\Query\Parser\ParserInterface $queryParser
     * @param \Vivo\Indexer\QueryBuilder $queryBuilder
     * @param \Vivo\Repository\RepositoryInterface $repository
     * @param DocumentApi $documentApi
     * @param \Vivo\Storage\PathBuilder\PathBuilderInterface $pathBuilder
     * @param \Zend\EventManager\EventManagerInterface $indexerEvents
     * @param \Zend\EventManager\EventManagerInterface $repositoryEvents
     * @param Watcher $watcher
     */
    public function __construct(VivoIndexerInterface $indexer,
                                IndexerHelperInterface $indexerHelper,
                                ParserInterface $queryParser,
                                QueryBuilder $queryBuilder,
                                RepositoryInterface $repository,
                                DocumentApi $documentApi,
                                PathBuilderInterface $pathBuilder,
                                EventManagerInterface $indexerEvents,
                                EventManagerInterface $repositoryEvents,
                                Watcher $watcher)
    {
        $this->indexer          = $indexer;
        $this->indexerHelper    = $indexerHelper;
        $this->queryParser      = $queryParser;
        $this->qb               = $queryBuilder;
        $this->repository       = $repository;
        $this->documentApi      = $documentApi;
        $this->pathBuilder      = $pathBuilder;
        $this->indexerEvents    = $indexerEvents;
        $this->watcher          = $watcher;

        //Attach listeners
        $repositoryEvents->attach(RepositoryEventInterface::EVENT_COMMIT, array($this, 'onRepositoryCommit'));
    }

    /**
     * Returns entities specified by the indexer query
     * @param QueryInterface|string $spec Either QueryInterface or a string query
     * @param QueryParams|array|null $queryParams Either a QueryParams object or an array specifying the params
     * @return Model\Entity[]
     */
    public function getEntitiesByQuery($spec, $queryParams = null)
    {
        if (is_string($spec)) {
            //Parse string query to Query object
            $spec   = $this->queryParser->stringToQuery($spec);
        }
        $result     = $this->indexer->find($spec, $queryParams);
        $hits       = $result->getHits();
        $entities   = array();
        foreach ($hits as $hit) {
            $doc    = $hit->getDocument();
            $path   = $doc->getFieldValue('\\path');
            try {
                $entity = $this->repository->getEntity($path);
                $entities[] = $entity;
            } catch (EntityNotFoundException $e) {
                //Entity not found
                $this->indexerEvents->trigger('log', $this, array(
                    'message'   => sprintf("Entity not found at path '%s' (%s)", $path, $e->getMessage()),
                    'priority'  => \VpLogger\Log\Logger::WARN,
                ));
            }
        }
        return $entities;
    }

    /**
     * Reindex all entities (contents and children) saved under the given path
     * Returns number of reindexed items
     * @param \Vivo\CMS\Model\Site $site
     * @param string $path Path to entity within the site
     * @param bool $deep If true reindexes whole subtree
     * @param bool $suppressErrors
     * @throws \Exception
     * @return int
     */
    public function reindex(Site $site, $path = '/', $deep = false, $suppressErrors = false)
    {
        //The reindexing may not rely on the indexer in any way!
        $pathComponents = array($site->getPath(), $path);
        $path           = $this->pathBuilder->buildStoragePath($pathComponents, true, false, false);
        $this->repository->commit();
        //Deactivate watcher, otherwise all entities in the site will be stored in the watcher (=>high mem requirements)
        $isWatcherActive    = $this->watcher->isActive();
        $this->watcher->isActive(false);
        $this->watcher->clear();
        $this->indexer->begin();
        try {
            if ($deep) {
                $delQuery   = $this->indexerHelper->buildTreeQuery($path);
            } else {
                $delQuery   = $this->qb->cond(sprintf('\path:%s', $path));
            }
            $this->indexer->delete($delQuery);
            //Index
            $count      = $this->doReindex($path, $deep, $suppressErrors);
            $this->indexer->commit();
        } catch (\Exception $e) {
            $this->indexer->rollback();
            $this->watcher->isActive($isWatcherActive);
            $this->watcher->clear();
            throw $e;
        }
        //Reactivate the watcher
        $this->watcher->isActive($isWatcherActive);
        $this->watcher->clear();
        return $count;
    }

    /**
     * Purges the indexer database
     * Removes all items from index
     */
    public function purge()
    {
        $this->indexer->deleteAllDocuments();
        $this->indexer->commit();
    }

    /**
     * Recursive method traversing and reindexing the entity tree
     * @param string $entityPath
     * @param bool $deep
     * @param bool $suppressErrors
     * @return int Number of reindexed items
     * @throws \Exception
     */
    protected function doReindex($entityPath, $deep = false, $suppressErrors = false)
    {
        $count  = 0;
        //Index the entity
        try {
            $entity     = $this->repository->getEntityFromStorage($entityPath);
            if ($entity) {
                try{
                    $this->indexEntity($entity);
                    $count      = 1;
                } catch (\Exception $e) {
                    //Exception during reindexing
                    //Index failed event is triggered by $this->indexEntity()
                    if (!$suppressErrors) {
                        //Rethrow
                        throw $e;
                    }
                }
            }
        } catch (\Exception $e) {
            //Exception during getEntityFromStorage(), e.g. unserialize exception
            //Trigger index failed event
            $event      = new IndexerEvent(null, $this);
            $event->setEntityPath($entityPath);
            $event->setEntity(null);
            $event->setException($e);
            $event->setParam('log', array(
                'message'   => sprintf("Retrieving entity '%s' from storage failed", $entityPath),
                'priority'  => \VpLogger\Log\Logger::ERR,
            ));
            $this->indexerEvents->trigger(IndexerEvent::EVENT_INDEX_FAILED, $event);
            if (!$suppressErrors) {
                //Rethrow
                throw $e;
            }

        }
        //Index the subtree
        if ($deep) {
            $childPaths = $this->repository->getChildEntityPaths($entityPath);
            foreach ($childPaths as $childPath) {
                $count  += $this->doReindex($childPath, $deep, $suppressErrors);
            }
        }
        return $count;
    }

    /**
     * Adds entity into index
     * If the entity cannot be indexed, throws an exception
     * @param \Vivo\CMS\Model\Entity $entity
     * @throws \Exception
     * @return void
     */
    protected function indexEntity(Model\Entity $entity)
    {
        $event      = new IndexerEvent(null, $this);
        $event->setEntity($entity);
        $event->setEntityPath($entity->getPath());
        if ($entity instanceof Model\Document) {
            try {
                $publishedContentTypes  = $this->documentApi->getPublishedContentTypes($entity);
            } catch (\Exception $e) {
                //Cannot get published content types
                $event->setException($e);
                $event->setParam('log', array(
                    'message'   => sprintf("Getting published contents for entity '%s' failed", $entity->getPath()),
                    'priority'  => \VpLogger\Log\Logger::ERR,
                ));
                $this->indexerEvents->trigger(IndexerEvent::EVENT_INDEX_FAILED, $event);
                throw $e;
            }
        } else {
            $publishedContentTypes    = array();
        }
        $options    = array('published_content_types' => $publishedContentTypes);
        try {
            $idxDoc     = $this->indexerHelper->createDocument($entity, $options);
            $event->setIdxDoc($idxDoc);
            $this->indexerEvents->trigger(IndexerEvent::EVENT_INDEX_PRE, $event);
            $this->indexer->addDocument($idxDoc);
            $this->indexerEvents->trigger(IndexerEvent::EVENT_INDEX_POST, $event);
        } catch (\Exception $e) {
            $event->setException($e);
            $event->setParam('log', array(
                'message'   => sprintf("Indexing entity '%s' failed", $entity->getPath()),
                'priority'  => \VpLogger\Log\Logger::ERR,
            ));
            $this->indexerEvents->trigger(IndexerEvent::EVENT_INDEX_FAILED, $event);
            throw $e;
        }
    }

    /**
     * Removes entity and whole its subtree from index
     * @param Model\Entity|string $spec Either an Entity object or its path
     */
    protected function deleteEntity($spec)
    {
        $delQuery   = $this->indexerHelper->buildTreeQuery($spec);
        $this->indexer->delete($delQuery);
    }

    /**
     * Saves or updates an entity in index
     * @param \Vivo\CMS\Model\Entity $entity
     */
    public function saveEntity(Model\Entity $entity)
    {
        //Remove old doc
        $entityTerm = $this->indexerHelper->buildEntityTerm($entity);
        $delQuery   = new TermQuery($entityTerm);
        $this->indexer->delete($delQuery);
        //Insert new one
        $this->indexEntity($entity);
    }

    /**
     * Repository commit listener
     * @param \Vivo\Repository\EventInterface $event
     */
    public function onRepositoryCommit(RepositoryEventInterface $event)
    {
        $this->indexer->begin();
        /** @var $deleteEntityPaths array */
        $deleteEntityPaths  = $event->getParam('delete_entity_paths');
        foreach ($deleteEntityPaths as $deleteEntityPath) {
            $this->deleteEntity($deleteEntityPath);
        }
        /** @var $saveEntities Model\Entity[] */
        $saveEntities   = $event->getParam('save_entities');
        foreach ($saveEntities as $entity) {
            $this->saveEntity($entity);
        }
        /** @var $saveResources array('data' => ..., 'entity' => ...) */
        $saveResources   = $event->getParam('save_data');
        foreach ($saveResources as $key => $value) {
            $this->saveEntity($value['entity']);
        }
        /** @var $saveStreams array('stream' => IO\InputStreamInterface[], 'entity' => ...) */
        $saveStreams   = $event->getParam('save_streams');
        foreach ($saveStreams as $key => $value) {
            $this->saveEntity($value['entity']);
        }

        $this->indexer->commit();
    }
}
