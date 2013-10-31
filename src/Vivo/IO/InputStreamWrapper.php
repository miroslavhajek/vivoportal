<?php
namespace Vivo\IO;

/**
 * Wrapper for input streams. It is useful when you need include file from InputStream.
 * @todo must be checked and refactored
 */
class InputStreamWrapper {

	const STREAM_NAME = 'vivo.iostream';

	private static $registeredInputStreams = array();

	private static $lastStreamId = 0;

	private static $registered = false;

    protected $pos = 0;

    /**
     * Stream stats.
     *
     * @var array
     */
    protected $stat;

    public function stream_open($path, $mode, $options, &$opened_path)
    {
        $path        = str_replace(self::STREAM_NAME.'://', '', $path);
        $this->is = self::$registeredInputStreams[$path];

        return true;
    }

    /**
     * Included so that __FILE__ returns the appropriate info
     *
     * @return array
     */
    public function url_stat($path)
    {
        $path        = str_replace(self::STREAM_NAME.'://', '', $path);
        if (isset(self::$registeredInputStreams[$path])) {

            $fileStat = array('dev' => 0,
                          'ino' => 0,
                          'mode' => 'r',
                          'nlink' => 0,
                          'uid' => 0,
                          'gid' => 0,
                          'rdev' => 0,
                          'size' => 0,
                          'atime' => 0,
                          'mtime' => 0,
                          'ctime' => 0,
                          'blksize' => -1,
                          'blocks' => -1
                    );

            return $fileStat;
        }
        return false;    }

    /**
     * Reads from the stream.
     */
    public function stream_read($count)
    {
        $ret = $this->is->read($count);
        $this->pos += strlen($ret);
        $this->eof = strlen($ret) < $count;
        return $ret;
    }


    /**
     * Tells the current position in the stream.
     */
    public function stream_tell()
    {
        return $this->pos;
    }


    /**
     * Tells if we are at the end of the stream.
     */
    public function stream_eof()
    {
        //return $this->pos >= strlen($this->data);
    }


    /**
     * Stream statistics.
     */
    public function stream_stat()
    {
            $fileStat = array('dev' => 0,
                          'ino' => 0,
                          'mode' => 'r',
                          'nlink' => 0,
                          'uid' => 0,
                          'gid' => 0,
                          'rdev' => 0,
                          'size' => 0,
                          'atime' => 0,
                          'mtime' => 0,
                          'ctime' => 0,
                          'blksize' => -1,
                          'blocks' => -1
                    );

            return $fileStat;
    }

    /**
     * Seek to a specific point in the stream.
     */
    public function stream_seek($offset, $whence)
    {
        //not allowed
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

    public static function register() {
		return stream_wrapper_register(self::STREAM_NAME, __CLASS__);
	}

	public static function registerInputStream($is, $path = null) {
        if (!self::$registered) {
            self::$registered = self::register();
        }
	    if (isset(self::$registeredInputStreams[$path])) {
	        throw new \Exception('Path is already used.');
	    }

	    self::$registeredInputStreams[$path] = $is;
        return self::STREAM_NAME.'://'.$path;
	}
}
