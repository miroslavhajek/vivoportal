<?php
namespace Vivo\Cache;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Cache\StorageFactory;

/**
 * CacheAbstractFactory
 * Service manager abstract factory to create cache instances
 */
class CacheAbstractFactory implements AbstractFactoryInterface
{
    /**
     * Abstract factory options
     * array (
     *      'cache_name'    => array(
     *          //Options to pass to StorageFactory::factory(), e.g.:
     *          'adapter'   => 'apc',
     *          'plugins'   => array(
     *              'exception_handler' => array('throw_exceptions' => false),
     *          ),
     *      ),
     * )
     * @var array
     */
    protected $options  = array();

    /**
     * When set to true, a null cache will be returned upon every request instead of the configured cache
     * @var bool
     */
    protected $serveNullCache   = false;

    /**
     * Canonical names created so far
     * @var array
     */
    protected $canonicalNames   = array();

    /**
     * @var array map of characters to be replaced through strtr
     */
    protected $canonicalNamesReplacements = array('-' => '', '_' => '', ' ' => '', '\\' => '', '/' => '');

    /**
     * Constructor
     * @param bool $serveNullCache
     * @param array $options
     */
    public function __construct($serveNullCache = false, array $options = array())
    {
        $this->serveNullCache   = $serveNullCache;
        $this->setOptions($options);
    }

    /**
     * Sets options
     * @param array $options
     */
    protected function setOptions(array $options)
    {
        $adjustedOptions    = array();
        foreach ($options as $cacheName => $cacheConfig) {
            $canonicalCacheName = $this->canonicalizeName($cacheName);
            $adjustedOptions[$canonicalCacheName]   = $cacheConfig;
        }
        $this->options          = array_merge($this->options, $adjustedOptions);
    }

    /**
     * Determine if we can create a service with name
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $cacheName  = $this->getCacheName($name, $requestedName);
        if ($cacheName) {
            return true;
        }
        return false;
    }

    /**
     * Create service with name
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return mixed
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        if ($this->serveNullCache) {
            //Create null cache
            $options    = array(
                'adapter'   => array(
                    'name'      => 'Vivo\null',
                ),
            );
        } else {
            //Create caches as defined
            $cacheName  = $this->getCacheName($name, $requestedName);
            $options    = $this->options[$cacheName];
            $this->createFolderForFsCache($options);
        }
        $cache      = StorageFactory::factory($options);
        return $cache;
    }

    /**
     * Checks if the canonical name or alternatively the requested name (in this order) exists in options as a cache
     * name and returns the first match (returned name is always canonical)
     * If neither name exists in options, returns null
     * @param $canonicalName
     * @param $requestedName
     * @return string|null
     */
    protected function getCacheName($canonicalName, $requestedName)
    {
        if (array_key_exists($canonicalName, $this->options)) {
            return $canonicalName;
        }
        $requestedNameCanonical = $this->canonicalizeName($requestedName);
        if (array_key_exists($requestedNameCanonical, $this->options)) {
            return $requestedNameCanonical;
        }
        return null;
    }

    /**
     * If the options specify a file system cache, checks if the cache folder exists and tries to create it if not
     * @param array $options
     * @throws Exception\RuntimeException
     */
    protected function createFolderForFsCache(array $options)
    {
        if (isset($options['adapter']['name'])
                && strtolower($options['adapter']['name'])  == 'filesystem'
                && isset($options['adapter']['options']['cache_dir'])) {
            $cacheDir   = $options['adapter']['options']['cache_dir'];
            if (!is_dir($cacheDir)) {
                if (!is_file($cacheDir)) {
                    //Cache dir does not exist, create it
                    $result = mkdir($cacheDir, 0777, true);
                    if (!$result) {
                        throw new Exception\RuntimeException(
                            sprintf("%s: Creating cache directory '%s' failed",
                                __METHOD__, $cacheDir));
                    }
                } else {
                    //A file with the same name already exists
                    throw new Exception\RuntimeException(
                        sprintf("%s: Cache directory '%s' cannot be created; a file with the same name exists",
                            __METHOD__, $cacheDir));
                }
            }
        }
    }

    /**
     * Canonicalize name
     * @see Zend\ServiceManager\ServiceManager::canonicalizeName()
     * @param  string $name
     * @return string
     */
    protected function canonicalizeName($name)
    {
        if (!isset($this->canonicalNames[$name])) {
            // this is just for performance instead of using str_replace
            $this->canonicalNames[$name] = strtolower(strtr($name, $this->canonicalNamesReplacements));
        }
        return $this->canonicalNames[$name];
    }
}
