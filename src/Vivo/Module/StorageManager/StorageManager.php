<?php
namespace Vivo\Module\StorageManager;

use Vivo\Storage\StorageInterface;
use Vivo\Module\Exception;
use Vivo\Storage\StorageUtil;
use Vivo\Module\StorageManager\RemoteModule;
use Vivo\Storage\PathBuilder\PathBuilderInterface as PathBuilder;

/**
 * StorageManager
 * Module storage manager - performs operations on module storage
 */
class StorageManager
{
    /**
     * Module storage
     * @var StorageInterface
     */
    protected $storage;

    /**
     * Paths in storage, where modules can be found
     * @var array
     */
    protected $modulePaths  = array();

    /**
     * Name of the file containing the JSON data describing the module
     * @var string
     */
    protected $descriptorName;

    /**
     * Array of info about modules present in storage
     * @var array
     */
    protected $modulesInfo           = array();

    /**
     * Default path in storage, where new modules are installed
     * @var string
     */
    protected $defaultInstallPath;

    /**
     * Storage utilities
     * @var StorageUtil
     */
    protected $storageUtil;

    /**
     * Remote module access
     * @var RemoteModule
     */
    protected $remoteModule;

    /**
     * Path builder
     * @var PathBuilder
     */
    protected $pathBuilder;

    /**
     * Constructor
     * @param \Vivo\Storage\StorageInterface $storage
     * @param array $modulePaths
     * @param string $descriptorName
     * @param string|null $defaultInstallPath
     * @param \Vivo\Storage\StorageUtil $storageUtil
     * @param RemoteModule $remoteModule
     * @param \Vivo\Storage\PathBuilder\PathBuilderInterface $pathBuilder
     * @throws \Vivo\Module\Exception\InvalidArgumentException
     */
    public function __construct(StorageInterface $storage,
                                array $modulePaths,
                                $descriptorName,
                                $defaultInstallPath = null,
                                StorageUtil $storageUtil,
                                RemoteModule $remoteModule,
                                PathBuilder $pathBuilder)
    {
        if (is_null($defaultInstallPath)) {
            //Default install path not specified, use the storage root as default
            $defaultInstallPath = $pathBuilder->getStoragePathSeparator();
        }
        if (!in_array($defaultInstallPath, $modulePaths)) {
            throw new Exception\InvalidArgumentException(
                sprintf("%s: The default install path '%s' is not a module path.", __METHOD__, $defaultInstallPath));
        }
        $this->storage              = $storage;
        $this->modulePaths          = $modulePaths;
        $this->descriptorName       = $descriptorName;
        $this->defaultInstallPath   = $defaultInstallPath;
        $this->storageUtil          = $storageUtil;
        $this->remoteModule         = $remoteModule;
        $this->pathBuilder          = $pathBuilder;
    }

    /**
     * Returns an array with information about modules added to the storage
     * @throws \Vivo\Module\Exception\DescriptorException
     * @return array
     */
    public function getModulesInfo()
    {
        if (empty($this->modulesInfo)) {
            $this->modulesInfo   = array();
            foreach ($this->modulePaths as $moduleBasePath) {
                $scan   = $this->storage->scan($moduleBasePath);
                foreach ($scan as $item) {
                    $modulePath     = $this->pathBuilder->buildStoragePath(array($moduleBasePath, $item), true);
                    $moduleFilePath = $this->pathBuilder->buildStoragePath(array($moduleBasePath, $item, 'Module.php'), true);
                    $moduleJsonPath = $this->pathBuilder->buildStoragePath(array($moduleBasePath, $item, $this->descriptorName), true);
                    if ($this->storage->isObject($moduleFilePath)) {
                        $moduleJson = $this->getJsonContent($this->storage, $moduleJsonPath);
                        if ((!isset($moduleJson['name'])) || ($item != $moduleJson['name'])) {
                            throw new Exception\DescriptorException(
                                sprintf("%s: Name in the module descriptor does not match module name '%s'.",
                                    __METHOD__, $item));
                        }
                        $moduleDesc = array(
                            'name'          => $item,
                            'storage_path'  => $modulePath,
                            'descriptor'    => $moduleJson,
                        );
                        $this->modulesInfo[$item] = $moduleDesc;
                    }
                }
            }
        }
        return $this->modulesInfo;
    }

    /**
     * Returns info about a module
     * @param string $moduleName
     * @return array
     * @throws \Vivo\Module\Exception\InvalidArgumentException
     */
    public function getModuleInfo($moduleName)
    {
        if (!$this->moduleExists($moduleName)) {
            throw new Exception\InvalidArgumentException(sprintf("%s: Module '%s' does not exist", __METHOD__, $moduleName));
        }
        $modulesInfo    = $this->getModulesInfo();
        $moduleInfo     = $modulesInfo[$moduleName];
        return $moduleInfo;
    }

    /**
     * Returns if a module exists in the storage
     * @param string $moduleName
     * @param string|null $version
     * @return boolean
     */
    public function moduleExists($moduleName, $version = null)
    {
        $modules        = $this->getModulesInfo();
        if (!array_key_exists($moduleName, $modules)) {
            return false;
        }
        if (is_null($version)) {
            return true;
        }
        $ourVersion = $modules[$moduleName]['descriptor']['version'];
        $versionOk  = $this->isVersionOk($ourVersion, $version);
        return $versionOk;
    }

    /**
     * Returns true when the specified VModule dependencies are met
     * Detailed info is returned in $info
     * @param array $dependencies
     * @param array $info
     * @return bool
     */
    public function checkVmoduleDependencies(array $dependencies, array &$info)
    {
        $info   = array();
        $result = true;
        foreach ($dependencies as $depName => $depVersion) {
            $info[$depName] = array(
                'name'              => $depName,
                'required_version'  => $depVersion,
                'present_version'   => null,
                'dependency_ok'     => false,
            );
            if ($this->moduleExists($depName)) {
                $moduleInfo = $this->getModuleInfo($depName);
                $info[$depName]['present_version']  = $moduleInfo['descriptor']['version'];
                if ($this->moduleExists($depName, $depVersion)) {
                    $info[$depName]['dependency_ok']    = true;
                } else {
                    $result                             = false;
                    $info[$depName]['dependency_ok']    = false;
                }
            } else {
                $result                             = false;
                $info[$depName]['dependency_ok']    = false;
                $info[$depName]['present_version']  = null;
            }
        }
        return $result;
    }

    /**
     * Returns an array with values from a json file
     * If the file cannot be found in storage, returns null
     * @param \Vivo\Storage\StorageInterface $storage
     * @param string $path
     * @return array|null
     */
    protected function getJsonContent(StorageInterface $storage, $path)
    {
        if ($storage->isObject($path)) {
            $data   = $storage->get($path);
            $jsonContent    = json_decode($data, true);
        } else {
            $jsonContent    = null;
        }
        return $jsonContent;
    }

    /**
     * Adds a module to the module storage
     * Returns module info on success
     * @param string $moduleUrl
     * @param bool $force Should the module be added even though dependencies are not satisfied?
     * @param string|null $path Path in module storage where the module should be placed (null = default install path)
     * @return array Module info
     * @throws \Vivo\Module\Exception\DependencyException
     * @throws \Vivo\Module\Exception\DescriptorException
     * @throws \Vivo\Module\Exception\InvalidArgumentException
     */
    public function addModule($moduleUrl, $force = false, $path = null)
    {
        if (is_null($path)) {
            $path   = $this->defaultInstallPath;
        } else {
            if (!in_array($path, $this->modulePaths)) {
                throw new Exception\InvalidArgumentException(
                    sprintf("%s: The path '%s' is not a module path.", __METHOD__, $path));
            }
        }
        $remoteStorage          = $this->remoteModule->getStorage($moduleUrl);
        $pathInRemoteStorage    = $this->remoteModule->getModulePathInStorage($moduleUrl);
        $moduleDescriptor       = $this->remoteModule->getModuleDescriptor($moduleUrl);
        $remotePathBuilder      = $this->remoteModule->getPathBuilder();
        if (!$moduleDescriptor) {
            throw new Exception\DescriptorException(
                sprintf("%s: Cannot read module descriptor from '%s' for module at URL '%s'",
                    __METHOD__, $this->descriptorName, $moduleUrl));
        }
        if (!$this->isDescriptorValid($moduleDescriptor)) {
            throw new Exception\DescriptorException(
                sprintf("%s: Invalid descriptor (%s) for module at URL '%s'",
                    __METHOD__, $this->descriptorName, $moduleUrl));
        }
        $moduleName = $moduleDescriptor['name'];
        //Check that the module name has not been added yet
        if ($this->moduleExists($moduleName)) {
            throw new Exception\InvalidArgumentException(
                sprintf("%s: Module '%s' (%s) already exists in module storage", __METHOD__, $moduleName, $moduleUrl));
        }
        //Read module dependencies on other vmodules and check they have been added (otherwise throw an exception)
        if ((!$force) && (isset($moduleDescriptor['require']))) {
            $dependencies   = $moduleDescriptor['require'];
            $info           = array();
            if (!$this->checkVmoduleDependencies($dependencies, $info)) {
                throw new Exception\DependencyException(
                    sprintf("%s: Dependencies of module '%s' are not satisfied.", __METHOD__, $moduleName));
            }
        }
        //TODO - Read module dependencies on libraries, throw an exception, if unsatisfied
        //Copy the module source to the module storage
        $fullPath   = $this->pathBuilder->buildStoragePath(array($path, $moduleName), true);
        $this->storageUtil->copy($remoteStorage, $pathInRemoteStorage, $remotePathBuilder,
                                 $this->storage, $fullPath, $this->pathBuilder);
        //Reset the cached info about modules
        $this->modulesInfo  = array();
        $moduleInfo         = $this->getModuleInfo($moduleName);
        return $moduleInfo;
    }

    /**
     * Checks if a version number satisfies the requirements
     * @param string $ourVersion
     * @param string $requiredVersion May be prepended with an operator '=' or '>='
     * @return bool
     */
    protected function isVersionOk($ourVersion, $requiredVersion)
    {
        $ourVersion         = strtolower($ourVersion);
        $requiredVersion    = strtolower($requiredVersion);
        if (substr($requiredVersion, 0, 2) == '>=') {
            $requiredVersion    = substr($requiredVersion, 2);
            $operator           = '>=';
        } elseif (substr($requiredVersion, 0, 1) == '=') {
            $requiredVersion    = substr($requiredVersion, 1);
            $operator           = '=';
        } else {
            $operator           = '=';
        }
        $result = version_compare($ourVersion, $requiredVersion, $operator);
        return $result;
    }

    /**
     * Returns true if the descriptor is valid
     * @param array $descriptor
     * @return bool
     */
    protected function isDescriptorValid(array $descriptor)
    {
        if (!isset($descriptor['name'])) {
            return false;
        }
        if (!isset($descriptor['version'])) {
            return false;
        }
        if ((!isset($descriptor['type'])) || (!in_array($descriptor['type'], array('site', 'core')))) {
            return false;
        }
        return true;
    }

    /**
     * Returns path to a module in the storage
     * @param string $moduleName
     * @return string
     */
    public function getPathToModule($moduleName)
    {
        $moduleInfo = $this->getModuleInfo($moduleName);
        $pathToModule   = $moduleInfo['storage_path'];
        return $pathToModule;
    }

    /**
     * Returns contents of a file in module storage
     * @param string $moduleName
     * @param string $pathInModule
     * @throws Exception\FileNotFoundException
     * @return string
     */
    public function getFileData($moduleName, $pathInModule)
    {
        $fullPath   = $this->getFullPathToFile($moduleName, $pathInModule);
        try {
            $data       = $this->storage->get($fullPath);
        } catch (\Vivo\Storage\Exception\IOException $e) {
            $localException = new Exception\FileNotFoundException(
                sprintf("%s: File '%s' not found in module '%s'", __METHOD__, $pathInModule, $moduleName), 0, $e);
            throw $localException;
        }
        return $data;
    }

    /**
     * Returns stream for a file in module storage
     * @param string $moduleName
     * @param string $pathInModule
     * @throws Exception\FileNotFoundException
     * @return \Vivo\IO\InputStreamInterface
     */
    public function getFileStream($moduleName, $pathInModule)
    {
        $fullPath   = $this->getFullPathToFile($moduleName, $pathInModule);
        try {
            $stream     = $this->storage->read($fullPath);
        } catch (\Vivo\IO\Exception\RuntimeException $e) {
            $localException = new Exception\FileNotFoundException(
                sprintf("%s: File '%s' not found in module '%s'", __METHOD__, $pathInModule, $moduleName), 0, $e);
            throw $localException;
        }
        return $stream;
    }

    /**
     * Returns mtime for a file in a module or false when the file does not exist
     * @param string $moduleName
     * @param string $pathInModule
     * @return bool|int
     */
    public function getFileMtime($moduleName, $pathInModule)
    {
        $fullPath   = $this->getFullPathToFile($moduleName, $pathInModule);
        $mtime      = $this->storage->mtime($fullPath);
        if ($mtime ===  false) {
            //Log not found file
            $events = new \Zend\EventManager\EventManager();
            $events->trigger('log', $this,  array(
                'message'   => sprintf("File '%s' not found in module '%s'", $fullPath, $moduleName),
                'priority'  => \VpLogger\Log\Logger::ERR,
            ));
        }
        return $mtime;
    }

    /**
     * Builds and returns a full absolute path to a file in a module
     * @param string $moduleName
     * @param string $pathInModule Path to a file relative to the module root
     * @return string
     */
    public function getFullPathToFile($moduleName, $pathInModule)
    {
        $components = array($this->getPathToModule($moduleName), $pathInModule);
        $fullPath   = $this->pathBuilder->buildStoragePath($components, true);
//        if (!$this->storage->isObject($fullPath)) {
//            throw new Exception\InvalidArgumentException(
//                sprintf("%s: Path '%s' not found in module '%s'", __METHOD__, $pathInModule, $moduleName));
//        }
        return $fullPath;
    }

    /**
     * Returns PathBuilder
     * @return \Vivo\Storage\PathBuilder\PathBuilderInterface
     */
    public function getPathBuilder()
    {
        return $this->pathBuilder;
    }
}
