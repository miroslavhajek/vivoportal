<?php
namespace Vivo\Module\ResourceManager;

use Vivo\Module\Exception;
use Vivo\Module\ResourceProviderInterface;
use Vivo\Module\StorageManager\StorageManager as ModuleStorageManager;
use Vivo\Storage\PathBuilder\PathBuilderInterface;
use Vivo\CMS\Model\Entity;

use Zend\ModuleManager\ModuleManager;

/**
 * ResourceManager
 * Provides access to module resources
 */
class ResourceManager
{
    /**
     * Vivo Module Manager
     * @var ModuleManager
     */
    protected $moduleManager;

    /**
     * Module storage manager
     * @var ModuleStorageManager
     */
    protected $moduleStorageManager;

    /**
     * Path Builder
     * @var PathBuilderInterface
     */
    protected $pathBuilder;

    /**
     * Array of ResourceManager configuration options
     * @var array
     */
    protected $options      = array(
        'type_map'      => array(
            'view'      => 'view',
            'layout'    => 'view/layout',
            'resource'  => 'resource',
            'metadata'  => 'config/metadata',
        ),
        'default_type'  => 'resource',
    );

    /**
     * Constructor
     * @param \Vivo\Module\StorageManager\StorageManager $moduleStorageManager
     * @param array $options Configuration options
     */
    public function __construct(ModuleStorageManager $moduleStorageManager,
                                array $options = array())
    {
        $this->moduleStorageManager = $moduleStorageManager;
        $this->pathBuilder          = $moduleStorageManager->getPathBuilder();
        $this->options              = array_merge($this->options, $options);
    }

    /**
     * Sets the Vivo module manager
     * @param \Zend\ModuleManager\ModuleManager $moduleManager
     */
    public function setModuleManager(ModuleManager $moduleManager)
    {
        $this->moduleManager    = $moduleManager;
    }

    /**
     * Returns the resource data
     * @param $moduleName
     * @param string $pathToResource
     * @param string|null $type Null = default type
     * @throws \Vivo\Module\Exception\ResourceNotFoundException
     * @throws \Vivo\Module\Exception\AsyncCallException
     * @return string
     */
    public function getResource($moduleName, $pathToResource, $type = null)
    {
        if (!$this->moduleManager) {
            throw new Exception\AsyncCallException(sprintf('%s: Module manager not set', __METHOD__));
        }
        $module = $this->moduleManager->getModule($moduleName);
        if (!$module) {
            throw new Exception\ResourceNotFoundException(
                sprintf("%s: Module '%s' not loaded", __METHOD__, $moduleName));
        }
        if ($module instanceof ResourceProviderInterface) {
            //Delegate resource retrieval to the module
            /* @var $module ResourceProviderInterface */
            $resource   = $module->getResource($type, $pathToResource);
        } else {
            //Retrieve the resource manually
            $resourceBase   = $this->getFolderForType($type);
            $components     = array($resourceBase, $pathToResource);
            $pathInModule   = $this->pathBuilder->buildStoragePath($components, false, false, false);
            try {
                $resource       = $this->moduleStorageManager->getFileData($moduleName, $pathInModule);
            } catch (Exception\FileNotFoundException $e) {
                $localException = new Exception\ResourceNotFoundException(
                    sprintf("%s: Resource of type '%s' not found in module '%s' on path '%s'",
                            __METHOD__, $type, $moduleName, $pathToResource), 0, $e);
                throw $localException;
            }
        }
        return $resource;
    }

    /**
     * Checks if the specified resource exists
     * @param string $moduleName
     * @param string $pathToResource
     * @param string|null $type
     * @return bool
     */
    public function resourceExists($moduleName, $pathToResource, $type = null)
    {
        $exists = true;
        try {
            $this->getResource($moduleName, $pathToResource, $type);
        } catch (Exception\ResourceNotFoundException $e) {
            $exists = false;
        }
        return $exists;
    }

    /**
     * Returns an input stream for the resource
     * @param string $moduleName
     * @param string $pathToResource
     * @param string|null $type Null = default type
     * @throws \Vivo\Module\Exception\ResourceNotFoundException
     * @throws \Vivo\Module\Exception\AsyncCallException
     * @return \Vivo\IO\FileInputStream
     */
    public function readResource($moduleName, $pathToResource, $type = null)
    {
        if (!$this->moduleManager) {
            throw new Exception\AsyncCallException(sprintf('%s: Module manager not set', __METHOD__));
        }
        $module = $this->moduleManager->getModule($moduleName);
        if (!$module) {
            throw new Exception\ResourceNotFoundException(
                sprintf("%s: Module '%s' not loaded", __METHOD__, $moduleName));
        }
        if ($module instanceof ResourceProviderInterface) {
            //Delegate resource retrieval to the module
            /* @var $module ResourceProviderInterface */
            $stream   = $module->getResourceStream($type, $pathToResource);
        } else {
            //Retrieve the resource manually
            $pathInModule       = $this->getResourcePathInModule($pathToResource, $type);
            try {
                $stream         = $this->moduleStorageManager->getFileStream($moduleName, $pathInModule);
            } catch (Exception\FileNotFoundException $e) {
                $localException = new Exception\ResourceNotFoundException(
                    sprintf("%s: Resource of type '%s' not found in module '%s' on path '%s'",
                        __METHOD__, $type, $moduleName, $pathToResource), 0, $e);
                throw $localException;
            }
        }
        return $stream;
    }

    /**
     * Returns folder corresponding to the specified resource type
     * @param null|string $type
     * @throws \Vivo\Module\Exception\InvalidArgumentException
     * @return string
     */
    protected function getFolderForType($type = null)
    {
        if (is_null($type)) {
            $type   = $this->options['default_type'];
        }
        if (!isset($this->options['type_map'][$type])) {
            throw new Exception\InvalidArgumentException(
                sprintf("%s: Resource type '%s' missing in type map", __METHOD__, $type));
        }
        $folder = $this->options['type_map'][$type];
        return $folder;
    }

    /**
     * Returns resource mtime or false when the resource is not found
     * @param string $moduleName
     * @param string $pathToResource
     * @param string|null $type Resource type
     * @throws \Vivo\Module\Exception\ResourceNotFoundException
     * @throws \Vivo\Module\Exception\AsyncCallException
     * @return bool|int
     */
    public function getResourceMtime($moduleName, $pathToResource, $type = null)
    {
        if (!$this->moduleManager) {
            throw new Exception\AsyncCallException(sprintf('%s: Module manager not set', __METHOD__));
        }
        $module = $this->moduleManager->getModule($moduleName);
        if (!$module) {
            throw new Exception\ResourceNotFoundException(
                sprintf("%s: Module '%s' not loaded", __METHOD__, $moduleName));
        }
        if ($module instanceof ResourceProviderInterface) {
            //Delegate resource mtime retrieval to the module
            /* @var $module ResourceProviderInterface */
            $mtime      = $module->getResourceMtime($type, $pathToResource);
        } else {
            //Retrieve the resource mtime manually
            $pathInModule   = $this->getResourcePathInModule($pathToResource, $type);
            try {
                $mtime          = $this->moduleStorageManager->getFileMtime($moduleName, $pathInModule);
            } catch (\Exception $e) {
                $mtime  = false;
                //Log error
                $events = new \Zend\EventManager\EventManager();
                $events->trigger('log', $this, array(
                    'message'   => sprintf("Getting mtime for resource '%s' in module '%s' failed ('%s')",
                                        $pathInModule, $moduleName, $e->getMessage()),
                    'priority'  => \VpLogger\Log\Logger::ERR,
                ));
            }
        }
        return $mtime;
    }

    /**
     * Returns resource path in module
     * @param string $pathToResource
     * @param string|null $type
     * @return string
     */
    public function getResourcePathInModule($pathToResource, $type = null)
    {
        $resourceBase   = $this->getFolderForType($type);
        $components     = array($resourceBase, $pathToResource);
        $pathInModule   = $this->pathBuilder->buildStoragePath($components, false, false, false);
        return $pathInModule;
    }
}
