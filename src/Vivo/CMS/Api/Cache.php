<?php
namespace Vivo\CMS\Api;

use Vivo\Cache\CacheManager;

use Zend\Cache\Storage\StorageInterface as CacheStorageInterface;
use Zend\Cache\Storage\ClearByNamespaceInterface;
use Zend\Cache\Storage\ClearByPrefixInterface;
use Zend\Cache\Storage\ClearExpiredInterface;
use Zend\Cache\Storage\FlushableInterface;
use Zend\Cache\Storage\AvailableSpaceCapableInterface;
use Zend\Cache\Storage\TotalSpaceCapableInterface;

use Zend\Stdlib\ArrayUtils;

/**
 * Cache API
 */
class Cache
{
    /**#@+
     * Cache subject name
     */
    const CACHE_SUBJECT_REPOSITORY  = 'repository';
    const CACHE_SUBJECT_NAVIGATION  = 'navigation';
    const CACHE_SUBJECT_OVERVIEW    = 'overview';
    /**#@-*/

    /**
     * Cache Manager - plugin manager for caches
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * Cache manager configuration - cache definitions
     * @var array
     */
    protected $cacheManagerConfig;

    /**
     * Caches used in the individual areas
     * @var array
     */
    protected $usedCaches   = array(
        self::CACHE_SUBJECT_REPOSITORY  => null,
        self::CACHE_SUBJECT_NAVIGATION  => null,
        self::CACHE_SUBJECT_OVERVIEW    => null,
    );

    /**
     * Constructor
     * @param CacheManager $cacheManager
     * @param array $cacheManagerConfig
     * @param array $usedCaches
     */
    public function __construct(CacheManager $cacheManager, array $cacheManagerConfig, array $usedCaches)
    {
        $this->cacheManager         = $cacheManager;
        $this->cacheManagerConfig   = $cacheManagerConfig;
        $this->usedCaches           = ArrayUtils::merge($this->usedCaches, $usedCaches);
    }

    /**
     * Returns array of all configured cache names
     * @return array
     */
    public function getConfiguredCacheNames()
    {
        $cacheNames = array_keys($this->cacheManagerConfig);
        return $cacheNames;
    }

    /**
     * Returns name of cache used for the specified cache subject
     * Returns null when no cache is used for the specified subject
     * When $cacheSubject == null, returns all used caches in an array
     * @param string|null $cacheSubject One of self::CACHE_SUBJECT_... constants
     * @throws Exception\InvalidArgumentException
     * @return string|null|array
     */
    public function getSubjectCacheName($cacheSubject = null)
    {
        if (is_null($cacheSubject)) {
            $usedCache  = $this->usedCaches;
        } else {
            if (array_key_exists($cacheSubject, $this->usedCaches)) {
                $usedCache  = $this->usedCaches[$cacheSubject];
            } else {
                throw new Exception\InvalidArgumentException(
                    sprintf("%s: Unknown cache subject '%s'", __METHOD__, $cacheSubject));
            }
        }
        return $usedCache;
    }

    /**
     * Returns space information about cache medium for the specified cache subject or for all used caches
     * Returns array with keys 'name', 'total', 'available', 'used' containing cache name and respective
     * sizes in MB or null when the size cannot be assessed
     * When $cacheSubject == null, returns array of such arrays or nulls when cache is not currently used
     * for the respective subject
     * @param string|null $cacheSubject Set to null to get info on all used caches
     * @return array
     */
    public function getCacheSpaceInfo($cacheSubject = null)
    {
        if (is_null($cacheSubject)) {
            //Get space info for all used caches
            $retval = array();
            foreach ($this->usedCaches as $subject => $cacheName) {
                if (is_null($cacheName)) {
                    //Cache is not used for this subject
                    $retval[$subject]   = null;
                } else {
                    $retval[$subject]   = $this->doGetCacheSpaceInfo($subject);
                }
            }
        } else {
            //Get space info for a single cache subject
            $retval = $this->doGetCacheSpaceInfo($cacheSubject);
        }
        return $retval;
    }

    /**
     * Returns space information about cache for the specified cache subject
     * Returns array(
     *      'name'      => cache name,
     *      'total'     => total space,
     *      'available' => available space,
     *      'used'      => used space,
     * )
     * All sizes are in MB
     * @param string $cacheSubject
     * @throws Exception\InvalidArgumentException
     * @return array
     */
    protected function doGetCacheSpaceInfo($cacheSubject)
    {
        $spaceInfo  = array(
            'name'      => null,
            'total'     => null,
            'available' => null,
            'used'      => null,
        );
        if (array_key_exists($cacheSubject, $this->usedCaches)) {
            $cacheName  = $this->usedCaches[$cacheSubject];
            $cache      = $this->cacheManager->get($cacheName);
            $spaceInfo['name']          = $cacheName;
            if ($cache instanceof TotalSpaceCapableInterface) {
                $spaceInfo['total']     = (int) round($cache->getTotalSpace() / 1000000);
            }
            if ($cache instanceof AvailableSpaceCapableInterface) {
                $spaceInfo['available'] = (int) round($cache->getAvailableSpace() / 1000000);
            }
            if (!is_null($spaceInfo['total']) && (!is_null($spaceInfo['available']))) {
                $spaceInfo['used']      = $spaceInfo['total'] - $spaceInfo['available'];
            }
        } else {
            throw new Exception\InvalidArgumentException(
                sprintf("%s: Unknown cache subject '%s'", __METHOD__, $cacheSubject));
        }
        return $spaceInfo;
    }

    /**
     * Flushes all caches currently set for the individual cache subjects
     * (ie not all configured caches, only those currently used)
     * Returns array with results: keys are cache subjects (repository, navigation, ...) values:
     *      null    = no cache used for this subject
     *      true    = flush successful
     *      false   = cache either not flushable, or flush failed
     * @return array
     */
    public function flushAllSubjectCaches()
    {
        $result = array();
        foreach ($this->usedCaches as $subject => $cacheName) {
            if (is_null($cacheName)) {
                //Cache not configured for this subject
                $result[$subject]   = null;
            } else {
                //Cache is configured for this subject
                if ($this->isFlushable($cacheName)) {
                    //Cache for this subject is flushable, try to flush
                    $result[$subject]   = $this->flush($cacheName);
                } else {
                    //Cache for this subject is not flushable
                    $result[$subject]   = false;
                }
            }
        }
        return $result;
    }

    /**
     * Returns if the given cache exists
     * @param string $cacheName
     * @return bool
     */
    public function cacheExists($cacheName)
    {
        $exists = $this->cacheManager->has($cacheName);
        return $exists;
    }

    /**
     * Returns if the cache is clearable by namespace
     * @param string $cacheName
     * @return bool
     * @throws Exception\InvalidArgumentException
     */
    public function isClearableByNamespace($cacheName)
    {
        if (!$this->cacheExists($cacheName)) {
            throw new Exception\InvalidArgumentException(
                sprintf("%s: Cache '%s' is not defined", __METHOD__, $cacheName));
        }
        /** @var $cache CacheStorageInterface */
        $cache          = $this->cacheManager->get($cacheName);
        $isClearable    = $cache instanceof ClearByNamespaceInterface;
        return $isClearable;
    }

    /**
     * Returns if the cache is clearable by prefix
     * @param string $cacheName
     * @return bool
     * @throws Exception\InvalidArgumentException
     */
    public function isClearableByPrefix($cacheName)
    {
        if (!$this->cacheExists($cacheName)) {
            throw new Exception\InvalidArgumentException(
                sprintf("%s: Cache '%s' is not defined", __METHOD__, $cacheName));
        }
        /** @var $cache CacheStorageInterface */
        $cache          = $this->cacheManager->get($cacheName);
        $isClearable    = $cache instanceof ClearByPrefixInterface;
        return $isClearable;
    }

    /**
     * Returns if the cache is clearable by expired
     * @param string $cacheName
     * @return bool
     * @throws Exception\InvalidArgumentException
     */
    public function isClearableByExpired($cacheName)
    {
        if (!$this->cacheExists($cacheName)) {
            throw new Exception\InvalidArgumentException(
                sprintf("%s: Cache '%s' is not defined", __METHOD__, $cacheName));
        }
        /** @var $cache CacheStorageInterface */
        $cache          = $this->cacheManager->get($cacheName);
        $isClearable    = $cache instanceof ClearExpiredInterface;
        return $isClearable;
    }

    /**
     * Returns if the cache is flushable
     * @param string $cacheName
     * @return bool
     * @throws Exception\InvalidArgumentException
     */
    public function isFlushable($cacheName)
    {
        if (!$this->cacheExists($cacheName)) {
            throw new Exception\InvalidArgumentException(
                sprintf("%s: Cache '%s' is not defined", __METHOD__, $cacheName));
        }
        /** @var $cache CacheStorageInterface */
        $cache          = $this->cacheManager->get($cacheName);
        $flushable      = $cache instanceof FlushableInterface;
        return $flushable;
    }

    /**
     * Flushes the cache
     * @param string $cacheName
     * @return bool
     * @throws Exception\IllegalCacheOperationException
     */
    public function flush($cacheName)
    {
        if (!$this->isFlushable($cacheName)) {
            throw new Exception\IllegalCacheOperationException(
                sprintf("%s: Cache '%s' is not flushable", __METHOD__, $cacheName));
        }
        /** @var $cache FlushableInterface */
        $cache  = $this->cacheManager->get($cacheName);
        $retval = $cache->flush();
        return $retval;
    }

    /**
     * Returns cache capabilities
     * @param string $cacheName
     * @param bool $asArray Return capabilities as array only
     * @return \Zend\Cache\Storage\Capabilities|array
     * @throws Exception\InvalidArgumentException
     */
    public function getCapabilities($cacheName, $asArray = true)
    {
        if (!$this->cacheExists($cacheName)) {
            throw new Exception\InvalidArgumentException(sprintf("%s: Cache '%s' is not defined", __METHOD__));
        }
        /** @var $cache CacheStorageInterface */
        $cache          = $this->cacheManager->get($cacheName);
        $capabilities   =$cache->getCapabilities();
        if ($asArray) {
            $capArray   = array(
                'adapter'               => get_class($capabilities->getAdapter()),
                'expired_read'          => $capabilities->getExpiredRead(),
                'max_key_length'        => $capabilities->getMaxKeyLength(),
                'max_ttl'               => $capabilities->getMaxTtl(),
                'min_ttl'               => $capabilities->getMinTtl(),
                'namespace_is_prefix'   => $capabilities->getNamespaceIsPrefix(),
                'namespace_separator'   => $capabilities->getNamespaceSeparator(),

                //TODO - dokončit
//                ''  => $capabilities->getStaticTtl(),
//                ''  => $capabilities->getSupportedDatatypes(),
//                ''  => $capabilities->getExpiredRead(),
//                ''  => $capabilities->getSupportedMetadata(),
//                ''  => $capabilities->getTtlPrecision(),
//                ''  => $capabilities->getUseRequestTime(),


            );
            $capabilities   = $capArray;
        }
        return $capabilities;
    }

}
