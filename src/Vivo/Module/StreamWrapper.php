<?php
namespace Vivo\Module;

use Zend\View\Stream as ZendViewStream;
use Vivo\Storage\StorageInterface;
use Vivo\Module\Exception\StreamException;

/**
 * Stream wrapper to read module source files from storage
 * Based on Zend\View\Stream
 */
class StreamWrapper extends ZendViewStream
{
    /**
     * Name of the stream (protocol) for Vmodule source access
     * @var string
     */
    protected static $streamName;

    /**
     * Storage with Vmodules
     * @var StorageInterface
     */
    protected static $storage;

    /**
     * Loads file data from storage
     */
    public function stream_open($path, $mode, $options, &$opened_path)
    {
        //Get the source
        $path        = $this->getBarePath($path);
        if (self::$storage->isObject($path)) {
            $this->data   = self::$storage->get($path);
        }
        if (($this->data === false) || is_null($this->data)) {
            $this->stat = false;
            return false;
        }
        //Update stat info
        $fileSize   = strlen($this->data);
        $this->stat = array(
            'size'      => $fileSize,
            'mtime'     => self::$storage->mtime($path),
        );
        return true;
    }

    /**
     * Retrieve information about a file
     * @link http://cz.php.net/manual/en/streamwrapper.url-stat.php
     * @param string $path
     * @param int $flags
     * @throws Exception\StreamException
     * @return array
     */
    public function url_stat($path = null, $flags = null)
    {
        if (is_null($path)) {
            throw new StreamException(sprintf('%s: Path cannot be null', __METHOD__));
        }
        $path   = $this->getBarePath($path);
        if (self::$storage->isObject($path)) {
            $stat   = array(
                'mtime'     => self::$storage->mtime($path),
                'mode'      => self::$storage->getPermissions($path),
            );
        } else {
            $stat   = false;
        }
        return $stat;
    }

    /**
     * When this method is not implemented, the include/require functions generate warning:
     * 'Warning: include_once(): StreamWrapper::stream_cast is not implemented!'
     * @see http://php.net/manual/en/streamwrapper.stream-cast.php
     * @see https://github.com/mikey179/vfsStream/issues/3
     * @param int $castAs
     * @return resource|bool
     */
    public function stream_cast($castAs)
    {
        return false;
    }

    /**
     * Returns the path without the stream name and ://
     * @param string $path
     * @return string
     */
    protected function getBarePath($path)
    {
        $barePath   = str_replace(self::$streamName . '://', '', $path);
        return $barePath;
    }

    /**
     * Registers this stream wrapper
     * @param string $streamName Name of the stream (protocol) used to access the Vmodule source
     * @param \Vivo\Storage\StorageInterface $storage
     * @throws Exception\StreamException
     * @return void
     */
    public static function register($streamName, StorageInterface $storage)
    {
        if (!$streamName) {
            throw new StreamException(sprintf("%s: Stream name not set", __METHOD__));
        }
        self::$streamName   = $streamName;
        self::$storage      = $storage;
        if (!stream_wrapper_register($streamName, __CLASS__)) {
            throw new StreamException(sprintf("%s: Registration of stream '%s' failed.", __METHOD__, $streamName));
        }
    }
}
