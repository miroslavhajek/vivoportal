<?php
namespace Vivo\Repository;

use Vivo\CMS\Model\Entity;
use Vivo\CMS\Model\PathInterface;
use Vivo\Storage\StorageInterface;
use Vivo\Repository\Watcher;
use Vivo\Storage\PathBuilder\PathBuilderInterface;
use Vivo\IO\IOUtil;
use Vivo\CMS\UuidConvertor\UuidConvertorInterface;

use Zend\Serializer\Adapter\AdapterInterface as Serializer;
use Zend\Cache\Storage\StorageInterface as Cache;
use Zend\EventManager\EventManagerInterface;

/**
 * Repository class provides methods to work with CMS repository.
 * Repository supports transactions.
 * Commit method commits the current transaction, making its changes permanent.
 * Rollback rolls back the current transaction, canceling its changes.
 */
class Repository implements RepositoryInterface
{
    /**
     * Entity object filename
     */
    const ENTITY_FILENAME = 'Entity.object';

    /**
     * Event manager
     * @var EventManagerInterface
     */
    protected $events;

    /**
     * @var \Vivo\Storage\StorageInterface
     */
    private $storage;

    /**
     * Path in storage where temp files can be created
     * @var string
     */
    protected $tmpPathInStorage = '/_tmp';

    /**
     * Object watcher
     * @var Watcher
     */
    protected $watcher;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * The cache for objects of the model
     * The entities are passed to this cache as objects, thus the cache has to support serialization
     * @var Cache
     */
    protected $cache;

    /**
     * PathBuilder
     * @var PathBuilderInterface
     */
    protected $pathBuilder;

    /**
     * @var \Vivo\IO\IOUtil
     */
    protected $ioUtil;

    /**
     * Uuid Convertor
     * @var UuidConvertorInterface
     */
    protected $uuidConvertor;

    /**
     * List of entities that are prepared to be persisted
     * @var PathInterface[]
     */
    protected $saveEntities         = array();

    /**
     * List of streams that are prepared to be persisted
     * Map: path => array('stream' => IO\InputStreamInterface[], 'entity' => ...)
     * @var array
     */
    protected $saveStreams          = array();

    /**
     * List of data items prepared to be persisted
     * Map: path => array('data' => ..., 'entity' => ...)
     * @var array
     */
    protected $saveData             = array();

    /**
     * List of files that are prepared to be copied
     * @var array
     */
    protected $copyFiles            = array();

    /**
     * List of paths of entities prepared for deletion
     * @var string[]
     */
    protected $deleteEntityPaths    = array();

    /**
     * List of resource paths that are prepared to be deleted
     * @var string[]
     */
    protected $deletePaths          = array();

    /**
     * List of temporary files which will be moved to their final place if everything is well in commit
     * Map: path => tempPath
     * @var string[]
     */
    protected $tmpFiles             = array();

    /**
     * List of temporary paths which will be deleted if everything is well in commit
     * Map: path => tempPath
     * @var string[]
     */
    protected $tmpDelFiles          = array();

    /**
     * Constructor
     * @param \Vivo\Storage\StorageInterface $storage
     * @param \Zend\Cache\Storage\StorageInterface $cache
     * @param \Zend\Serializer\Adapter\AdapterInterface $serializer
     * @param Watcher $watcher
     * @param \Vivo\IO\IOUtil $ioUtil
     * @param \Zend\EventManager\EventManagerInterface $events
     * @param \Vivo\CMS\UuidConvertor\UuidConvertorInterface $uuidConvertor
     * @throws Exception\Exception
     */
    public function __construct(StorageInterface $storage,
                                Cache $cache = null,
                                Serializer $serializer,
                                Watcher $watcher,
                                IOUtil $ioUtil,
                                EventManagerInterface $events,
                                UuidConvertorInterface $uuidConvertor)
    {
        if ($cache) {
            //Check that cache supports all required data types
            $requiredTypes  = array('NULL', 'boolean', 'integer', 'double', 'string', 'array', 'object');
            $supportedTypes = $cache->getCapabilities()->getSupportedDatatypes();
            foreach ($requiredTypes as $requiredType) {
                if (!$supportedTypes[$requiredType]) {
                    throw new Exception\Exception(sprintf(
                        "%s: The cache used for Repository must support data type '%s'", __METHOD__, $requiredType));
                }
            }
        }
        $this->storage          = $storage;
        $this->cache            = $cache;
        $this->serializer       = $serializer;
        $this->watcher          = $watcher;
        $this->ioUtil           = $ioUtil;
        $this->pathBuilder      = $this->storage->getPathBuilder();
        $this->events           = $events;
        $this->uuidConvertor    = $uuidConvertor;
    }

    /**
     * Returns entity identified by path
     * When the entity is not found, throws an exception
     * @param string $path Entity path
     * @return null|\Vivo\CMS\Model\Entity
     * @throws Exception\EntityNotFoundException
     */
    public function getEntity($path)
    {
        //Get entity from watcher
        if ($entity = $this->watcher->get($path)) {
            return $entity;
        }
        //Get entity from cache
        if ($entity = $this->getEntityFromCache($path)) {
            $this->watcher->add($entity);
            return $entity;
        }
        //Get the entity from storage
        if ($entity = $this->getEntityFromStorage($path)) {
            return $entity;
        }
        throw new Exception\EntityNotFoundException(sprintf("%s: No entity found at path '%s'", __METHOD__, $path));
    }

    /**
     * Returns if an entity exists in the repository at the given path
     * @param string $path
     * @return boolean
     */
    public function hasEntity($path)
    {
        try {
            $this->getEntity($path);
            $hasEntity  = true;
        } catch (Exception\EntityNotFoundException $e) {
            $hasEntity  = false;
        }
        return $hasEntity;
    }

    /**
     * Looks up an entity in cache and returns it
     * If the entity does not exist in cache or its mtime is older than the specified mtime, returns null
     * @param string $path
     * @throws Exception\EntityNotFoundException
     * @throws Exception\RuntimeException
     * @return \Vivo\CMS\Model\Entity|null
     */
    protected function getEntityFromCache($path)
    {
        $entity = null;
        if ($this->cache) {
            //Get current mtime of the entity from storage
            if (!$minMtime  = $this->getStorageMtime($path)) {
                throw new Exception\EntityNotFoundException(
                    sprintf("%s: No entity found at path '%s'", __METHOD__, $path));
            }
            $key            = $this->hashPathForCache($path);
            $cacheSuccess   = null;
            $item           = $this->cache->getItem($key, $cacheSuccess);
            if ($cacheSuccess) {
                $cacheMtime = $item['mtime'];
                if ($minMtime > $cacheMtime) {
                    //mtime in cache is older than the specified mtime, cache item is stale
                    $this->removeEntityFromCache($path, true);
                    $this->events->trigger('log', $this,
                        array ('message'    => sprintf("Stale item removed from cache '%s'", $path),
                            'priority'   => \VpLogger\Log\Logger::DEBUG));
                } else {
                    //mtime is ok
                    //TODO - Log cache hit
                    $entity = $item['entity'];
                }
            } else {
                //TODO - Log cache miss
            }
        }
        return $entity;
    }

    /**
     * Stores an entity to cache
     * @param Entity $entity
     * @throws Exception\RuntimeException
     */
    protected function storeEntityToCache(Entity $entity)
    {
        if (!$entity->getPath()) {
            throw new Exception\RuntimeException(sprintf("%s: Cannot store entity to cache, path not set", __METHOD__));
        }
        $key    = $this->hashPathForCache($entity->getPath());
        $mtime  = $this->getStorageMtime($entity->getPath());
        $item   = array(
            'mtime'     => $mtime,
            'entity'    => $entity,
        );
        $this->cache->setItem($key, $item);
    }

    /**
     * Removes an entity from cache by path
     * @param string $path
     * @param bool $removeDescendants Remove also all descendants of the specified entity?
     */
    protected function removeEntityFromCache($path, $removeDescendants = true)
    {
        if ($removeDescendants) {
            //Remove also all descendants of this entity
            $descendants    = $this->getChildren($path, false, true);
            foreach ($descendants as $descendant) {
                $descPath   = $descendant->getPath();
                $key        = $this->hashPathForCache($descPath);
                $this->cache->removeItem($key);
            }
        }
        //Remove the entity
        $key    = $this->hashPathForCache($path);
        $this->cache->removeItem($key);
    }

    /**
     * Looks up an entity in storage and returns it
     * If the entity is not found returns null
     * @param string $path
     * @throws Exception\UnserializationException
     * @return \Vivo\CMS\Model\Entity|null
     */
    public function getEntityFromStorage($path)
    {
        $pathComponents = array($path, self::ENTITY_FILENAME);
        $fullPath       = $this->pathBuilder->buildStoragePath($pathComponents, true, false, false);
        if (!$this->storage->isObject($fullPath)) {
            //Entity not found in storage, remove entity from watcher and cache to eliminate possible inconsistencies
            $this->watcher->remove($path);
            if ($this->cache) {
                //Do not try to remove descendants from cache - some invalid paths (e.g. /) might cause reading of
                //all entities / searching the complete repository which might result in an exception when there is
                //an entity which cannot be unserialized
                $this->removeEntityFromCache($path, false);
            }
            return null;
        }
        $entitySer      = $this->storage->get($fullPath);
        /* @var $entity \Vivo\CMS\Model\Entity */
        try {
            $entity         = $this->serializer->unserialize($entitySer);
        } catch (\Exception $e) {
            throw new Exception\UnserializationException(
                sprintf("%s: Can't unserialize entity with path `%s`.", __METHOD__, $fullPath), null, $e);
        }
        //Trigger post-unserialize event
        $eventParams    = array(
            'entity'    => $entity,
        );
        $event          = new Event(EventInterface::EVENT_UNSERIALIZE_POST, $this, $eventParams);
        $this->events->trigger($event);

        //Set volatile path property of entity instance
        $entity->setPath($path);
        //Store entity to watcher
        $this->watcher->add($entity);
        //Store entity to cache
        if ($this->cache) {
            $this->storeEntityToCache($entity);
        }
        return $entity;
    }

    /**
     * Returns parent folder
     * If there is no parent folder (ie this is a root), returns null
     * @param PathInterface $folder
     * @return \Vivo\CMS\Model\Entity
     */
    public function getParent(PathInterface $folder)
    {
        $path               = $this->getAndCheckPath($folder);
        $pathElements       = $this->pathBuilder->getStoragePathComponents($path);
        // One path element is site name
        if (count($pathElements) == 1) {
            //$folder is a root folder
            return null;
        }
        array_pop($pathElements);
        $parentFolderPath   = $this->pathBuilder->buildStoragePath($pathElements, true, false, false);
        $parentFolder       = $this->getEntity($parentFolderPath);
        return $parentFolder;
    }

    /**
     * Returns children of an entity
     * When $deep == true, returns descendants rather than children
     * @param PathInterface|string $spec Either PathInterface object or directly a path as a string
     * @param bool|string $className
     * @param bool $deep
     * @param bool $ignoreErrors
     * @throws Exception\InvalidArgumentException
     * @throws \Exception
     * @return \Vivo\CMS\Model\Entity[]
     */
    public function getChildren($spec,
                                $className = false,
                                $deep = false,
                                $ignoreErrors = false)
    {
        $path   = null;
        if ($spec instanceof PathInterface) {
            $path   = $this->getAndCheckPath($spec);
        } elseif (is_string($spec)) {
            $path   = $spec;
        }
        if (is_null($path)) {
            throw new Exception\InvalidArgumentException(
                sprintf("%s: spec must be either a PathInterface object or a string", __METHOD__));
        }
        $children       = array();
        $descendants    = array();
        $childPaths     = $this->getChildEntityPaths($path);

        //TODO: tady se zamyslet, zda neskenovat podle tridy i obsahy
//         if (is_subclass_of($class_name, 'Vivo\Cms\Model\Content')) {
//             $names = $this->storage->scan("$path/Contents");
//         }
//         else {
//             $names = $this->storage->scan($path);
//         }
        foreach ($childPaths as $childPath) {
            try {
                if ($entity = $this->getEntity($childPath)/* && ($entity instanceof CMS\Model\Site || CMS::$securityManager->authorize($entity, 'Browse', false))*/) {
                    $children[] = $entity;
                }
            } catch (Exception\EntityNotFoundException $e) {
                //Fix for the situation when a directory exists without an Entity.object
            } catch (\Exception $e) {
                //Another exception, e.g. unserialization exception
                if ($ignoreErrors) {
                    continue;
                } else {
                    throw $e;
                }
            }
        }

        //@todo: sorting?

        foreach ($children as $child) {
            if (!$className || $child instanceof $className) {
                $descendants[]  = $child;
            }
            //All descendants
            if ($deep) {
                $childDescendants   = $this->getChildren($child, $className, $deep, $ignoreErrors);
                $descendants        = array_merge($descendants, $childDescendants);
            }
        }
        return $descendants;
    }

    /**
     * Returns true when the folder has children
     * @param PathInterface $folder
     * @return bool
     */
    public function hasChildren(PathInterface $folder)
    {
        $path = $this->getAndCheckPath($folder);
        foreach ($this->storage->scan($path) as $name) {
            $pathElements   = array($path, $name, self::ENTITY_FILENAME);
            $childPath      = $this->pathBuilder->buildStoragePath($pathElements, true, false, false);
            if ($this->storage->contains($childPath)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Saves entity to repository
     * Changes become persistent when commit method is called within request
     * @param \Vivo\CMS\Model\PathInterface $entity
     * @return mixed|\Vivo\CMS\Model\PathInterface
     * @throws Exception\InvalidPathException
     */
    public function saveEntity(PathInterface $entity)
    {
        $entityPath = $this->getAndCheckPath($entity);
        $sanitized  = $this->pathBuilder->sanitize($entityPath);
        if ($entityPath != $sanitized) {
            throw new Exception\InvalidPathException(
                sprintf("%s: Invalid path '%s', expected '%s'", __METHOD__, $entityPath, $sanitized));
        }
        $this->saveEntities[$entityPath]    = $entity;
        return $entity;
    }

    /**
     * Adds a stream to the list of streams to be saved
     * @param PathInterface $entity
     * @param string $name
     * @param \Vivo\IO\InputStreamInterface $stream
     * @return void
     */
    public function writeResource(PathInterface $entity, $name, \Vivo\IO\InputStreamInterface $stream)
    {
        $entityPath                 = $this->getAndCheckPath($entity);
        $pathComponents             = array($entityPath, $name);
        $path                       = $this->pathBuilder->buildStoragePath($pathComponents, true, false, false);
        $this->saveStreams[$path]   = array('stream' => $stream, 'entity' => $entity);
    }

    /**
     * Adds an entity resource (data) to the list of resources to be saved
     * @param PathInterface $entity
     * @param string $name Name of resource
     * @param string $data
     */
    public function saveResource(PathInterface $entity, $name, $data)
    {
        $entityPath             = $this->getAndCheckPath($entity);
        $pathComponents         = array($entityPath, $name);
        $path                   = $this->pathBuilder->buildStoragePath($pathComponents, true, false, false);
        $this->saveData[$path]  = array('data' => $data, 'entity' => $entity);
    }

    /**
     * Returns an input stream for reading from the resource
     * @param PathInterface $entity
     * @param string $name Resource file name.
     * @return \Vivo\IO\InputStreamInterface
     */
    public function readResource(PathInterface $entity, $name)
    {
        $entityPath     = $this->getAndCheckPath($entity);
        $pathComponents = array($entityPath, $name);
        $path           = $this->pathBuilder->buildStoragePath($pathComponents, true, false, false);
        $stream         = $this->storage->read($path);
        return $stream;
    }

    /**
     * @param PathInterface $entity
     * @return array
     */
    public function scanResources(PathInterface $entity)
    {
        $entityPath = $entity->getPath();

        $resources = array();
        $names = $this->storage->scan($entityPath);

        foreach ($names as $name) {
            $path = $this->pathBuilder->buildStoragePath(array($entityPath, $name, false, false));
            if($name != self::ENTITY_FILENAME && $this->storage->isObject($path)) {
                $resources[] = $name;
            }
        }

        return $resources;
    }

    /**
     * Returns resource from storage
     * @param PathInterface $entity
     * @param string $name
     * @throws Exception\InvalidArgumentException
     * @return string
     */
    public function getResource(PathInterface $entity, $name)
    {
        if ($name == '' || is_null($name)) {
            throw new Exception\InvalidArgumentException(sprintf("%s: Resource name cannot be empty", __METHOD__));
        }
        $entityPath     = $this->getAndCheckPath($entity);
        $pathComponents = array($entityPath, $name);
        $path           = $this->pathBuilder->buildStoragePath($pathComponents, true, false, false);
        $data           = $this->storage->get($path);
        return $data;
    }

    /**
     * Returns entity resource mtime or false when the resource is not found
     * @param PathInterface $entity
     * @param string $name
     * @throws Exception\InvalidArgumentException
     * @return int|bool
     */
    public function getResourceMtime(PathInterface $entity, $name)
    {
        if ($name == '' || is_null($name)) {
            throw new Exception\InvalidArgumentException(sprintf("%s: Resource name cannot be empty", __METHOD__));
        }
        $entityPath     = $this->getAndCheckPath($entity);
        $path           = $this->pathBuilder->buildStoragePath(array($entityPath, $name), true, false, false);
        $mtime          = $this->storage->mtime($path);
        if ($mtime === false) {
            //Log not found resource
            $this->events->trigger('log', $this,  array(
                'message'   => sprintf("Resource '%s' not found with entity '%s'", $name, $entity->getPath()),
                'priority'  => \VpLogger\Log\Logger::ERR,
            ));
        }
        return $mtime;
    }

    /**
     * Returns resource size in bytes
     * @param \Vivo\CMS\Model\PathInterface $entity
     * @param string $name
     * @throws \Vivo\Repository\Exception\InvalidArgumentException
     * @return int
     */
    public function getResourceSize(PathInterface $entity, $name)
    {
        if ($name == '' || is_null($name)) {
            throw new Exception\InvalidArgumentException(sprintf('%s: Resource name cannot be empty', __METHOD__));
        }

        $entityPath = $this->getAndCheckPath($entity);
        $path       = $this->pathBuilder->buildStoragePath(array($entityPath, $name), true, false, false);
        return $this->storage->size($path);
    }

    /**
     * Adds an entity to the list of entities to be deleted
     * @param PathInterface $entity
     */
    public function deleteEntity(PathInterface $entity)
    {
        $path   = $this->getAndCheckPath($entity);
        $this->deleteEntityByPath($path);
    }

    /**
     * Deletes entity by path
     * @param string $path
     */
    public function deleteEntityByPath($path)
    {
        $path                   = $this->pathBuilder->sanitize($path);
        $this->deleteEntityPaths[] = $path;
    }

    /**
     * Adds an entity's resource to the list of resources to be deleted
     * @param PathInterface $entity
     * @param string $name Name of the resource
     */
    public function deleteResource(PathInterface $entity, $name)
    {
        $entityPath             = $this->getAndCheckPath($entity);
        $pathComponents         = array($entityPath, $name);
        $path                   = $this->pathBuilder->buildStoragePath($pathComponents, true, false, false);
        $this->deletePaths[]    = $path;
    }

    /**
     * Moves entity
     * @param PathInterface $entity
     * @param string $target
     * @return null|\Vivo\CMS\Model\Entity
     */
    public function moveEntity(PathInterface $entity, $target)
    {
        $this->watcher->remove($entity->getPath());
        if ($this->cache) {
            $this->removeEntityFromCache($entity->getPath(), true);
        }
        $this->storage->move($entity->getPath(), $target);
        if ($this->hasEntity($target)) {
            $moved  = $this->getEntity($target);
        } else {
            $moved  = null;
        }
        return $moved;
    }

    /**
     * Copies entity
     * @param PathInterface $entity
     * @param string $target
     * @return null|\Vivo\CMS\Model\Entity
     */
    public function copyEntity(PathInterface $entity, $target)
    {
        $this->storage->copy($entity->getPath(), $target);
        if ($this->hasEntity($target)) {
            $copy   = $this->getEntity($target);
        } else {
            $copy   = null;
        }
        return $copy;
    }

    /**
     * Returns descendants of a specific path from storage
     * If $suppressUnserializationErrors is set to false, returns an array of entities
     * If $suppressUnserializationErrors is set to true, returns
     * array(
     *      'entities'  => array of descendants,
     *      'erroneous' => array(
     *          'child path' => Exception,...
     * )
     * @param string $path
     * @param bool $suppressUnserializationErrors If true, unserialization errors will not be thrown
     * @throws \Exception|Exception\UnserializationException
     * @return Entity[]|array
     */
    public function getDescendantsFromStorage($path, $suppressUnserializationErrors = false)
    {
        /** @var $descendants Entity[] */
        $descendants    = array();
        $erroneous      = array();
        $names = $this->storage->scan($path);
        foreach ($names as $name) {
            $childPath = $this->pathBuilder->buildStoragePath(array($path, $name), true, false, false);
            if (!$this->storage->isObject($childPath)) {
                $entity     = null;
                try {
                    $entity = $this->getEntityFromStorage($childPath);
                } catch (Exception\EntityNotFoundException $e) {
                    //Fix for the situation when a directory exists without an Entity.object
                } catch (Exception\UnserializationException $e) {
                    if ($suppressUnserializationErrors) {
                        $erroneous[$childPath]  = $e;
                    } else {
                        throw $e;
                    }
                }
                if ($entity) {
                    $descendants[]      = $entity;
                }
                $childDescendantsStruct = $this->getDescendantsFromStorage($childPath, $suppressUnserializationErrors);
                if ($suppressUnserializationErrors) {
                    $childDescendants   = $childDescendantsStruct['entities'];
                    $erroneous          = array_merge($erroneous, $childDescendantsStruct['erroneous']);
                } else {
                    $childDescendants   = $childDescendantsStruct;
                }
                $descendants        = array_merge($descendants, $childDescendants);
            }
        }
        if ($suppressUnserializationErrors) {
            $retVal = array(
                'entities'  => $descendants,
                'erroneous' => $erroneous,
            );
        } else {
            $retVal = $descendants;
        }
        return $retVal;
    }

    /**
     * Begins transaction
     * A transaction is always open, do not call 'begin()' explicitly
     */
    public function begin()
    {
        throw new Exception\Exception("%s: A transaction is always open, do not call 'begin()' explicitly", __METHOD__);
    }

    /**
     * Commit commits the current transaction, making its changes permanent
     */
    public function commit()
    {
        try {
            //Delete - Phase 1 (move to temp files)
            //a) Entities
            foreach ($this->deleteEntityPaths as $path) {
                $tmpPath                    = $this->pathBuilder->buildStoragePath(
                                                    array($this->tmpPathInStorage, uniqid('del-')), true, false, false);
                $this->tmpDelFiles[$path]   = $tmpPath;
                $this->storage->move($path, $tmpPath);
            }
            //b) Resources
            foreach ($this->deletePaths as $path) {
                $tmpPath                    = $this->pathBuilder->buildStoragePath(
                                                    array($this->tmpPathInStorage, uniqid('del-')), true, false, false);
                $this->tmpDelFiles[$path]   = $tmpPath;
                $this->storage->move($path, $tmpPath);
            }
            //Save - Phase 1 (serialize entities and files into temp files)
            //a) Entity
            foreach ($this->saveEntities as $entity) {
                $pathElements           = array($entity->getPath(), self::ENTITY_FILENAME);
                $path                   = $this->pathBuilder->buildStoragePath($pathElements, true, false, false);
                $tmpPath                = $path . '.' . uniqid('tmp-');
                $this->tmpFiles[$path]  = $tmpPath;
                //Create entity clone which will be serialized and stored - this is to prevent changes to the entity
                //kept in memory
                $entityCopy     = clone $entity;
                //Trigger pre-serialize event
                $eventParams    = array(
                    'entity'    => $entityCopy,
                );
                $event          = new Event(EventInterface::EVENT_SERIALIZE_PRE, $this, $eventParams);
                $this->events->trigger($event);
                $entitySer      = $this->serializer->serialize($entityCopy);
                $this->storage->set($tmpPath, $entitySer);
            }
            //b) Data
            foreach ($this->saveData as $path => $data) {
                $tmpPath                = $path . '.' . uniqid('tmp-');
                $this->tmpFiles[$path]  = $tmpPath;
                $this->storage->set($tmpPath, $data['data']);
            }
            //c) Streams
            foreach ($this->saveStreams as $path => $data) {
                $tmpPath                = $path . '.' . uniqid('tmp-');
                $this->tmpFiles[$path]  = $tmpPath;
                $output                 = $this->storage->write($tmpPath);
                $this->ioUtil->copy($data['stream'], $output, 4096);
                $output->close();
            }

            //Delete - Phase 2 (delete the temp files) - this is done in removeTempFiles

            //Save Phase 2 (rename temp files to real ones)
            foreach ($this->tmpFiles as $path => $tmpPath) {
                if (!$this->storage->move($tmpPath, $path)) {
                    throw new Exception\Exception(
                        sprintf("%s: Move failed; source: '%s', destination: '%s'", __METHOD__, $tmpPath, $path));
                }
            }

            //The actual commit is successfully done, now process references to entities in Watcher, Cache, etc

            //Delete entities from Watcher and Cache
            foreach ($this->deleteEntityPaths as $path) {
                $this->watcher->remove($path);
                if ($this->cache) {
                    $this->removeEntityFromCache($path, true);
                }
            }
            //Save entities to Watcher and Cache
            foreach ($this->saveEntities as $entity) {
                //Watcher
                $this->watcher->add($entity);
                //Cache
                if ($this->cache) {
                    $this->storeEntityToCache($entity);
                }
            }
            //Trigger commit event
            $eventParams    = array(
                'copy_files'            => $this->copyFiles,
                'delete_paths'          => $this->deletePaths,
                'delete_entity_paths'   => $this->deleteEntityPaths,
                'save_data'             => $this->saveData,
                'save_entities'         => $this->saveEntities,
                'save_streams'          => $this->saveStreams,
                'tmp_del_files'         => $this->tmpDelFiles,
                'tmp_files'             => $this->tmpFiles,
            );
            $event          = new Event(EventInterface::EVENT_COMMIT, $this, $eventParams);
            $this->events->trigger($event);
            //Clean-up after commit
            $this->reset();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Resets/clears the changes scheduled for this transaction
     * Calling this function on uncommitted transaction may lead to data loss!
     */
    protected function reset()
    {
        $this->copyFiles            = array();
        $this->deletePaths          = array();
        $this->deleteEntityPaths    = array();
        $this->saveData             = array();
        $this->saveEntities         = array();
        $this->saveStreams          = array();
        $this->removeTempFiles();
    }

    /**
     * Rollback rolls back the current transaction, canceling changes
     */
    public function rollback()
    {
        //Move back everything that was moved during Delete Phase 1
        foreach ($this->tmpDelFiles as $path => $tmpPath) {
            try {
                $this->storage->move($tmpPath, $path);
            } catch (\Exception $e) {
                //Just continue
            }
        }
        //Delete remaining temp files - done by reset()
        $this->reset();
    }

    /**
     * Removes temporary files created during commit
     */
    protected function removeTempFiles()
    {
        //TempDelFiles
        foreach ($this->tmpDelFiles as $tmpPath) {
            try {
                if ($this->storage->contains($tmpPath)) {
                    $this->storage->remove($tmpPath);
                }
            } catch (\Exception $e) {
                //Just continue, we should try to remove all temp files even though an exception has been thrown
            }
        }
        $this->tmpDelFiles          = array();
        //TempFiles
        foreach ($this->tmpFiles as $path) {
            try {
                if ($this->storage->contains($path)) {
                    $this->storage->remove($path);
                }
            } catch (\Exception $e) {
                //Just continue, we should try to remove all temp files even though an exception has been thrown
            }
        }
        $this->tmpFiles             = array();
    }

    /**
     * Retrieves and returns entity path, if path is not set, throws an exception
     * @param PathInterface $entity
     * @return string
     * @throws Exception\PathNotSetException
     */
    protected function getAndCheckPath(PathInterface $entity)
    {
        $path   = $entity->getPath();
        if (!$path) {
            throw new Exception\PathNotSetException(sprintf("%s: Entity has no path set", __METHOD__));
        }
        return $path;
    }

    /**
     * Returns mtime of a file in storage
     * If file does not exist in storage, returns null
     * @param string $path
     * @return int|null
     */
    protected function getStorageMtime($path)
    {
        $mtime  = $this->storage->mtime($path);
        if ($mtime === false) {
            $mtime  = null;
        }
        return $mtime;
    }

    /**
     * Returns child entity paths
     * @param string $path
     * @return string[]
     */
    public function getChildEntityPaths($path)
    {
        $childEntityPaths   = array();
        $names = $this->storage->scan($path);
        sort($names); // sort it in a natural way
        foreach ($names as $name) {
            $childPath = $this->pathBuilder->buildStoragePath(array($path, $name), true, false, false);
            if (!$this->storage->isObject($childPath)) {
                $childEntityPaths[] = $childPath;
            }
        }
        return $childEntityPaths;
    }

    /**
     * Hashes entity path to be used as cache key
     * @param string $path
     * @return string
     */
    protected function hashPathForCache($path)
    {
        $hash   = hash('md4', $path);
        return $hash;
    }
}
