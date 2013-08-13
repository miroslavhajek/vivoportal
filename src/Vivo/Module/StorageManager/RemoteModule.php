<?php
namespace Vivo\Module\StorageManager;

use Vivo\Storage\Factory as StorageFactory;
use Vivo\Storage\StorageInterface;
use Vivo\Storage\PathBuilder\PathBuilderInterface as PathBuilder;

/**
 * RemoteModule
 * Provides access to remote modules specified by a URL
 */
class RemoteModule
{
    /**
     * Storage Factory
     * @var StorageFactory
     */
    protected $storageFactory;

    /**
     * Cached module storage instances
     * @var array
     */
    protected $storageInstances     = array();

    /**
     * Name of the module descriptor file
     * @var string
     */
    protected $descriptorName;

    /**
     * PathBuilder object
     * @var PathBuilder
     */
    protected $pathBuilder;

    /**
     * Constructor
     * @param \Vivo\Storage\Factory $storageFactory
     * @param string $descriptorName
     * @param PathBuilder $pathBuilder
     */
    public function __construct(StorageFactory $storageFactory, $descriptorName, PathBuilder $pathBuilder)
    {
        $this->storageFactory   = $storageFactory;
        $this->descriptorName   = $descriptorName;
        $this->pathBuilder      = $pathBuilder;
    }

    /**
     * Creates and returns a storage which will be used to access the remote module
     * @param string $moduleUrl
     * @return StorageInterface
     */
    public function getStorage($moduleUrl)
    {
        if (!array_key_exists($moduleUrl, $this->storageInstances)) {
            //TODO - configure for specific storage implementation based on the $moduleUrl
            $streamSpec     = 'file://';
            $root           = substr($moduleUrl, strlen($streamSpec));
            $storageConfig  = array(
                'class'     => 'Vivo\Storage\LocalFileSystemStorage',
                'options'   => array(
                    'root'          => $root,
                    'path_builder'  => $this->pathBuilder,
                ),
            );
            $this->storageInstances[$moduleUrl] = $this->storageFactory->create($storageConfig);
        }
        return $this->storageInstances[$moduleUrl];
    }

    /**
     * Returns the path of the remote module in the storage
     * @param string $moduleUrl
     * @return string
     */
    public function getModulePathInStorage($moduleUrl)
    {
        //TODO - implement based on the $moduleUrl
        //For file system storage the module is always at the root of the storage
        $modulePath = $this->pathBuilder->getStoragePathSeparator();
        return $modulePath;
    }

    /**
     * Returns the remote module descriptor
     * If the descriptor file is missing in the remote module, returns null
     * @param string $moduleUrl
     * @return array|null
     */
    public function getModuleDescriptor($moduleUrl)
    {
        $storage        = $this->getStorage($moduleUrl);
        $path           = $this->getModulePathInStorage($moduleUrl);
        $descriptorPath = $this->pathBuilder->buildStoragePath(array($path, $this->descriptorName), true, false, false);
        if ($storage->isObject($descriptorPath)) {
            $data           = $storage->get($descriptorPath);
            $jsonContent    = json_decode($data, true);
        } else {
            $jsonContent    = null;
        }
        return $jsonContent;
    }

    /**
     * Returns the path builder
     * @return \Vivo\Storage\PathBuilder\PathBuilderInterface
     */
    public function getPathBuilder()
    {
        return $this->pathBuilder;
    }
}