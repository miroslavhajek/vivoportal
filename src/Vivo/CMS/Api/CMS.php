<?php
namespace Vivo\CMS\Api;

use Vivo\CMS\Model;
use Vivo\CMS\Exception;
use Vivo\CMS\Api\Exception as ApiException;
use Vivo\CMS\UuidConvertor\UuidConvertorInterface;
use Vivo\Repository\RepositoryInterface;
use Vivo\Indexer\QueryBuilder;
use Vivo\Uuid\GeneratorInterface as UuidGeneratorInterface;
use Vivo\Storage\PathBuilder\PathBuilderInterface;
use Vivo\Security\Manager\AbstractManager as AbstractSecurityManager;
use Vivo\IO\InputStreamInterface;

use DateTime;

/**
 * Main business class to interact with CMS.
 */
class CMS
{
    /**
     * RegEx for UUID
     */
    const UUID_PATTERN = '[\d\w]{32}';

    const RESOURCE_NAME = 'resource';

    /**
     * Repository
     * @var RepositoryInterface
     */
    private $repository;

    /**
     * UUID Convertor
     * @var UuidConvertorInterface
     */
    protected $uuidConvertor;

    /**
     * @var UuidGeneratorInterface
     */
    protected $uuidGenerator;

    /**
     * Path builder
     * @var PathBuilderInterface
     */
    protected $pathBuilder;

    /**
     * Security Manager
     * @var AbstractSecurityManager
     */
    protected $securityManager;

    /**
     * Construct
     * @param \Vivo\Repository\RepositoryInterface $repository
     * @param \Vivo\CMS\UuidConvertor\UuidConvertorInterface $uuidConvertor
     * @param \Vivo\Uuid\GeneratorInterface $uuidGenerator
     * @param \Vivo\Storage\PathBuilder\PathBuilderInterface $pathBuilder
     */
    public function __construct(RepositoryInterface $repository,
                                UuidConvertorInterface $uuidConvertor,
                                UuidGeneratorInterface $uuidGenerator,
                                PathBuilderInterface $pathBuilder)
    {
        $this->repository       = $repository;
        $this->uuidConvertor    = $uuidConvertor;
        $this->uuidGenerator    = $uuidGenerator;
        $this->pathBuilder      = $pathBuilder;
    }

    /**
     * Returns entity within site by rel path.
     * @param string $relPath
     * @param Model\Site $site
     * @return Model\Entity
     */
    public function getSiteEntity($relPath, Model\Site $site)
    {
        $path   = $this->getEntityAbsolutePath($relPath, $site);
        $entity = $this->repository->getEntity($path);
        return $entity;
    }

    /**
     * Returns entity specified by path, UUID or symbolic reference
     * @param string $ident Path, UUID or symbolic reference
     * @throws \Vivo\CMS\Exception\InvalidArgumentException
     * @throws \Vivo\Repository\Exception\EntityNotFoundException
     * @return \Vivo\CMS\Model\Entity
     */
    public function getEntity($ident)
    {
        $uuid   = $this->getUuidFromEntityIdent($ident);
        if ($uuid) {
            //$ident is UUID or symbolic reference
            $path   = $this->uuidConvertor->getPath($uuid);
            if (!$path) {
                throw new Exception\InvalidArgumentException(
                    sprintf("%s: Cannot convert entity identifier '%s' (UUID = '%s') to path",
                            __METHOD__, $ident, $uuid));
            }
        } else {
            //Assume $ident is a path
            $path   = $ident;
        }
        return $this->repository->getEntity($path);
    }

    /**
     * @param \Vivo\CMS\Model\Entity $entity
     * @return \Vivo\CMS\Model\Entity[]
     */
    public function getChildren(Model\Entity $entity)
    {
        return $this->repository->getChildren($entity);
    }

    /**
     * @param \Vivo\CMS\Model\Entity $entity
     */
    public function removeChildren(Model\Entity $entity)
    {
        foreach ($this->getChildren($entity) as $child) {
            $this->removeEntity($child);
        }
    }

    /**
     * @param \Vivo\CMS\Model\Entity $entity
     * @return \Vivo\CMS\Model\Entity
     */
    public function getParent(Model\Entity $entity)
    {
        return $this->repository->getParent($entity);
    }

    /**
     * Saves entity
     * The entity is prepared before saving into repository
     * @param Model\Entity $entity
     * @param bool $commit
     */
    public function saveEntity(Model\Entity $entity, $commit = true)
    {
        $entity = $this->prepareEntityForSaving($entity);
        $this->repository->saveEntity($entity);
        if ($commit) {
            $this->repository->commit();
        }
    }

    /**
     * Prepares entity to be saved
     * @param \Vivo\CMS\Model\Entity $entity
     * @return \Vivo\CMS\Model\Entity
     */
    public function prepareEntityForSaving(Model\Entity $entity)
    {
        $username       = $this->securityManager->getPrincipalDomain()
                          . '\\' . $this->securityManager->getPrincipalUsername();
        $now            = new DateTime();
        $sanitizedPath  = $this->pathBuilder->sanitize($entity->getPath());
        $entity->setPath($sanitizedPath);
        if(!$entity->getUuid()) {
            $entity->setUuid($this->uuidGenerator->create());
        }
        if (!$entity->getCreated() instanceof \DateTime) {
            $entity->setCreated($now);
        }
        $entity->setModified($now);
        if (!$entity->getCreatedBy()) {
            $entity->setCreatedBy($username);
        }
        $entity->setModifiedBy($username);
        return $entity;
    }

    /**
     * Removes entity from storage
     * @param \Vivo\CMS\Model\Entity $entity
     */
    public function removeEntity(Model\Entity $entity)
    {
        $this->repository->deleteEntity($entity);
        $this->repository->commit();
    }

//     /**
//      * @param Model\Document $document
//      * @param int $index
//      * @return Model\Content
//      */
//     public function getPublishedContent(Model\Document $document, $index)
//     {
//         $index = $index ? $index : 0; //@todo: exception na is_int($index);
//         $contents = $this->getDocumentContents($document, $index);
//         foreach ($contents as $content) {
//             if($content->getState() == Workflow\AbstractWorkflow::STATE_PUBLISHED) {
//                 return $content;
//             }
//         }
//
//         return null;
//     }
//
//     public function getContents(Model\Document $document, $index)
//     {
//         if(!is_integer($index)) {
//             throw new \InvalidArgumentException(sprintf('Argument %d passed to %s must be an type of %s, %s given', 2, __METHOD__, 'integer', gettype($index)));
//         }
//
//         return $this->repository->getChildren($document->getPath().'/Contents.'.$index);
//     }

    /**
     * Returns resource name for repository.
     *
     * @param \Vivo\CMS\Model\Content\File $file
     * @return string
     */
    public function getResourceName(Model\Content\File $file)
    {
        return sprintf('%s.%s', self::RESOURCE_NAME, $file->getExt());
    }

    /**
     * Writes resource represented by a stream into the repository
     * @param Model\Entity $entity 'Owner' of the resource
     * @param string $name Resource name
     * @param InputStreamInterface $inputStream
     */
    public function writeResource(Model\Entity $entity, $name, InputStreamInterface $inputStream)
    {
        $this->repository->writeResource($entity, $name, $inputStream);
        $this->repository->commit();
    }

    /**
     * @param Model\Entity $entity
     * @param string $name
     * @param string $data
     */
    public function saveResource(Model\Entity $entity, $name, $data)
    {
        $this->repository->saveResource($entity, $name, $data);
        $this->repository->commit();
    }

    /**
     * Returns input stream for resource of entity.
     * @param Model\Entity $entity
     * @param string $resourcePath
     * @return \Vivo\IO\InputStreamInterface
     */
    public function readResource(Model\Entity $entity, $resourcePath)
    {
        return $this->repository->readResource($entity, $resourcePath);
    }

    /**
     * Returns content of entity resource.
     * @param Model\Entity $entity
     * @param string $name
     * @return string
     */
    public function getResource(Model\Entity $entity, $name)
    {
        return $this->repository->getResource($entity, $name);
    }

    /**
     * Returns entity resource mtime or false when the resource is not found
     * @param Model\Entity $entity
     * @param string $name
     * @return int|bool
     */
    public function getResourceMtime(Model\Entity $entity, $name)
    {
        $mtime  = $this->repository->getResourceMtime($entity, $name);
        return $mtime;
    }

    /**
     * Returns all resource names without entity object.
     * @param Model\Entity $entity
     * @return array
     */
    public function scanResources(Model\Entity $entity)
    {
        return $this->repository->scanResources($entity);
    }

    /**
     * @param Model\Entity $entity
     * @param string $name
     */
    public function removeResource(Model\Entity $entity, $name)
    {
        $this->repository->deleteResource($entity, $name);
        $this->repository->commit();
    }

    /**
     * Returns entity relative path within site
     * Relative path starts and ends with slash.
     * @param Model\Entity|string $spec
     * @throws ApiException\UnexpectedValueException
     * @throws ApiException\InvalidArgumentException
     * @return string
     */
    public function getEntityRelPath($spec)
    {
        if ($spec instanceof Model\Entity) {
            $path   = $spec->getPath();
        } elseif (is_string($spec)) {
            $path   = $spec;
        } else {
            throw new ApiException\InvalidArgumentException(
                sprintf("%s: Spec must be either an entity or a string", __METHOD__));
        }
        $components = $this->pathBuilder->getStoragePathComponents($path);
        $comp       = '';
        while (!empty($components) && ($comp != 'ROOT')) {
            $comp   = array_shift($components);
        }
        if ($comp != 'ROOT') {
            throw new ApiException\UnexpectedValueException(
                sprintf("%s: Cannot get relative path from '%s'", __METHOD__, $path));
        }
        $relPath    = $this->pathBuilder->buildStoragePath($components, true, true);
        return $relPath;
    }

    /**
     * Returns site path of given entity.
     * @param Model\Entity|string $spec
     * @throws ApiException\UnexpectedValueException
     * @throws ApiException\InvalidArgumentException
     * @return string
     */
    public function getEntitySitePath($spec) {
        if ($spec instanceof Model\Entity) {
            $path   = $spec->getPath();
        } elseif (is_string($spec)) {
            $path   = $spec;
        } else {
            throw new ApiException\InvalidArgumentException(
                sprintf("%s: Spec must be either an entity or a string", __METHOD__));
        }
        $components = $this->pathBuilder->getStoragePathComponents($path);
        $comp       = '';
        while (!empty($components) && ($comp != 'ROOT')) {
            $comp   = array_pop($components);
        }
        if (empty($components)) {
            throw new ApiException\UnexpectedValueException(
                sprintf("%s: Cannot get site path from '%s'", __METHOD__, $path));
        }
        $sitePath   = $this->pathBuilder->buildStoragePath($components, true, false);
        return $sitePath;
    }

    /**
     * Returns
     * @param string $path
     * @param Model\Site $site
     * @return string
     */
    public function getEntityAbsolutePath($path, Model\Site $site)
    {
        $components = $this->pathBuilder->getStoragePathComponents($path);
        if ((count($components) < 2) || ($components[0] != $site->getName()) || ($components[1] != 'ROOT')) {
            //Relative path
            array_unshift($components, $site->getName(), 'ROOT');
        }
        $absolutePath   = $this->pathBuilder->buildStoragePath($components, true);

//        if (substr($path, 0, 1) == '/' && substr($path, -1) == '/') {
//            //it's relative path
//            $components     = array($site->getPath(), 'ROOT', $path);
//            $absolutePath   = $this->pathBuilder->buildStoragePath($components, true);
//        } else {
//            //it's absolute path
//            $absolutePath   = $path;
//        }
        return $absolutePath;
    }

    /**
     * If $ident represents UUID or a symbolic reference, returns corresponding UUID, otherwise null
     * @param string $ident
     * @return null|string
     */
    protected function getUuidFromEntityIdent($ident)
    {
        if (preg_match('/^'.self::UUID_PATTERN.'$/i', $ident)) {
            //UUID
            $uuid   = $ident;
            $uuid   = strtoupper($uuid);
        } elseif (preg_match('/^\[ref:('.self::UUID_PATTERN.')\]$/i', $ident, $matches)) {
            //Symbolic reference in [ref:uuid] format
            $uuid   = $matches[1];
            $uuid = strtoupper($uuid);
        } else {
            //The ident is not a UUID
            $uuid   = null;
        }
        return $uuid;
    }

    /**
     * Returns an array of duplicate uuids under specified path, works directly on storage
     * array(
     *  'uuid1' => array(
     *      'path1',
     *      'path2',
     *  ),
     *  'uuid2' => array(
     *      'path3',
     *      'path4',
     *  ),
     * )
     * @param string $path
     * @return array
     */
    public function getDuplicateUuidsInStorage($path)
    {
        $uuids          = array();
        $descendants    = $this->repository->getDescendantsFromStorage($path);
        $me             = $this->repository->getEntityFromStorage($path);
        if ($me) {
            $descendants[]  = $me;
        }
        /** @var $descendant Model\Entity */
        foreach ($descendants as $descendant) {
            $uuid   = $descendant->getUuid();
            if (!array_key_exists($uuid, $uuids)) {
                $uuids[$uuid]   = array();
            }
            $uuids[$uuid][]  = $descendant->getPath();
        }
        foreach ($uuids as $uuid => $paths) {
            if (count($paths) == 1) {
                unset($uuids[$uuid]);
            }
        }
        return $uuids;
    }

    /**
     * Sets the security manager
     * @param \Vivo\Security\Manager\AbstractManager $securityManager
     */
    public function setSecurityManager($securityManager)
    {
        $this->securityManager = $securityManager;
    }
}
