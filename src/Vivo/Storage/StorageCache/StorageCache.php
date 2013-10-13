<?php
namespace Vivo\Storage\StorageCache;

use Vivo\Storage\StorageInterface;
use Vivo\Storage\StorageCache\KeyNormalizer\KeyNormalizerInterface;
use Vivo\Storage\PathBuilder\PathBuilderInterface;

use Zend\Cache\Storage\StorageInterface as ZendCache;

/**
 * StorageCache
 * Transparently caches Storage objects
 */
class StorageCache implements StorageCacheInterface
{
    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * @var ZendCache
     */
    protected $cache;

    /**
     * @var KeyNormalizerInterface
     */
    protected $cacheKeyNormalizer;

    /**
     * Constructor
     * @param \Zend\Cache\Storage\StorageInterface $cache
     * @param \Vivo\Storage\StorageInterface $storage
     * @param KeyNormalizerInterface|null $cacheKeyNormalizer Optional normalizer transforming key prior to calling cache
     */
    public function __construct(ZendCache $cache,
                                StorageInterface $storage,
                                KeyNormalizerInterface $cacheKeyNormalizer = null)
    {
        $this->cache                = $cache;
        $this->storage              = $storage;
        $this->cacheKeyNormalizer   = $cacheKeyNormalizer;
    }

    /**
     * Checks whether item exists in cache, if not there checks the storage
     * @param string $path to item
     * @return boolean TRUE if item exists otherwise FALSE
     */
    public function contains($path)
    {
        $cacheKey   = $this->normalizeCacheKey($path);
        if ($this->cache->hasItem($cacheKey)) {
            return true;
        } else {
            return $this->storage->contains($path);
        }
    }

    /**
     * Checks whether item on the given path is an object.
     * @param string $path Path to the item
     * @return bool
     */
    public function isObject($path)
    {
        $cacheKey   = $this->normalizeCacheKey($path);
        if ($this->cache->hasItem($cacheKey)) {
            return true;
        }
        return $this->storage->isObject($path);
    }

    /**
     * Returns item modification time in milliseconds.
     * @param string $path to item
     * @return mixed item modification time in milliseconds or FALSE if item doesn't exist
     */
    public function mtime($path)
    {
        return $this->storage->mtime($path);
    }

    /**
     * Returns item from cache, if not there, gets the item from the underlying storage
     * @param string $path to item
     * @return mixed
     */
    public function get($path)
    {
        $cacheKey   = $this->normalizeCacheKey($path);
        $success    = null;
        $item       = $this->cache->getItem($cacheKey, $success);
        if (!$success) {
            $item   = $this->storage->get($path);
            $this->cache->setItem($cacheKey, $item);
        }
        return $item;
    }

    /**
     * Saves item to cache as well as to the underlying storage (WRITE-THROUGH cache)
     * If an item under specified path already exists, it will be overwritten.
     * @param string $path to item
     * @param mixed $variable data
     */
    public function set($path, $variable)
    {
        $cacheKey   = $this->normalizeCacheKey($path);
        $result = $this->cache->setItem($cacheKey, $variable);
        $this->storage->set($path, $variable);
    }

    /**
     * Touches an item
     * Removes the item from the cache and touches the item in the underlying storage
     * @param string $path to item
     */
    public function touch($path)
    {
        $cacheKey   = $this->normalizeCacheKey($path);
        $this->cache->removeItem($cacheKey);
        $this->storage->touch($path);
    }

    /**
     * Renames/moves item in the cache as well as in the underlying storage
     * @param string $path to item
     * @param string $target path
     */
    public function move($path, $target)
    {
        $cacheKey       = $this->normalizeCacheKey($path);
        $cacheTarget    = $this->normalizeCacheKey($target);
        $success        = null;
        $cachedItem     = $this->cache->getItem($cacheKey, $success);
        if ($success) {
            //Item found in the cache
            $this->cache->removeItem($cacheKey);
            $this->cache->setItem($cacheTarget, $cachedItem);
        }
        $this->storage->move($path, $target);
    }

    /**
     * Copies item to another location in the cache as well as in the underlying storage
     * @param string $path to item
     * @param string $target path to copy
     */
    public function copy($path, $target)
    {
        $cacheKey       = $this->normalizeCacheKey($path);
        $cacheTarget    = $this->normalizeCacheKey($target);
        $success        = null;
        $cachedItem     = $this->cache->getItem($cacheKey, $success);
        if ($success) {
            //Item found in the cache
            $this->cache->setItem($cacheTarget, $cachedItem);
        }
        $this->storage->copy($path, $target);
    }

    /**
     * Removes item from specified path in the cache as well as in the storage
     * If the item doesn't exist, nothing happens
     * @param string $path to item
     * @return boolean TRUE on success, FALSE if item doesn't exist
     */
    public function remove($path)
    {
        $cacheKey   = $this->normalizeCacheKey($path);
        $this->cache->removeItem($cacheKey);
        $this->storage->remove($path);
    }

    /**
     * Scans the storage and returns list of child items
     * @param string $path to item
     * @return array
     */
    public function scan($path)
    {
        return $this->storage->scan($path);
    }

    /**
     * Normalizes key for use with cache
     * @param string $key
     * @return string
     */
    protected function normalizeCacheKey($key)
    {
        if ($this->cacheKeyNormalizer) {
            $normalized = $this->cacheKeyNormalizer->normalizeKey($key);
        } else {
            $normalized = $key;
        }
        return $normalized;
    }

    /**
     * Returns input stream for reading resource.
     * @param string $path
     * @return \Vivo\IO\InputStreamInterface
     */
    public function read($path)
    {
        $stream     = $this->storage->read($path);
        return $stream;
    }

    /**
     * Returns output stream for writing resource.
     * @param string $path
     * @return \Vivo\IO\OutputStreamInterface
     */
    public function write($path)
    {
        //Remove the item from cache
        $cacheKey   = $this->normalizeCacheKey($path);
        $this->cache->removeItem($cacheKey);
        $stream     = $this->storage->write($path);
        return $stream;
    }

    /**
     * Returns PathBuilder for this storage
     * @return PathBuilderInterface
     */
    public function getPathBuilder()
    {
        return $this->storage->getPathBuilder();
    }

    /**
     * Returns size of the file in bytes
     * If $path is not a file, returns null
     * @param string $path
     * @return integer
     */
    public function size($path)
    {
        return $this->storage->size($path);
    }

    /**
     * Returns item permissions
     * @param string $path
     * @param bool $oct Returns octal number when set to true, otherwise decimal
     * @return int|string
     */
    public function getPermissions($path, $oct = false)
    {
        return $this->storage->getPermissions($path, $oct);
    }
}
