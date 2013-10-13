<?php
namespace Vivo\Storage;

use Vivo\Storage\Exception;
use Vivo\IO;

use Zend\EventManager\EventManager;

/**
 * Implementation of the virtual file system over local filesystem.
 */
class LocalFileSystemStorage extends AbstractStorage {

	/**
	 * Root path.
	 * @var string $root
	 */
	private $root;

    /**
     * Event Manager
     * @var EventManager
     */
    protected $eventManager;

    /**
     * Storage path separator
     * @var string
     */
    protected $storagePathSeparator;

	/**
	 * @param array $options Options.
	 * @throws \Vivo\Storage\Exception\InvalidArgumentException
	 */
	public function __construct(array $options) {
		if (!isset($options['root'])) {
			throw new Exception\InvalidArgumentException(sprintf('%s: Root is not defined', __METHOD__));
		}
        if (!isset($options['path_builder'])) {
            throw new Exception\InvalidArgumentException(sprintf('%s: PathBuilder object is not defined', __METHOD__));
        }
        $this->setPathBuilder($options['path_builder']);
        $this->storagePathSeparator = $this->pathBuilder->getStoragePathSeparator();
		$root               = $this->normalizePath($options['root']);
		if(!is_dir($root)) {
			throw new Exception\InvalidArgumentException(sprintf('Root %s is not a directory', $root));
		}
		$this->root = $root;
	}

    /**
     * Converts backslashes to forward slashes and removes trailing slashes
     * @param string $path
     * @return string
     */
    protected function normalizePath($path)
    {
        //Convert backslashes to forward slashes
        $path   = str_replace('\\', '/', $path);
        //Remove trailing slash(es)
        $path   = rtrim($path, '/');
        return $path;
    }

    /**
     * Returns an absolute file system path for the given absolute storage path
     * @param string $storagePath
     * @throws Exception\InvalidArgumentException
     * @return string
     */
    protected function getFsPath($storagePath) {
        //For empty path return root
        if ($storagePath == '') {
            return $this->root;
        }
        //Only paths starting with the path separator (i.e. explicitly starting from the Storage root)
        //are currently supported
        if (substr($storagePath, 0, 1) != $this->storagePathSeparator) {
            throw new Exception\InvalidArgumentException(
                sprintf('%s: Only absolute paths supported (%s)', __METHOD__, $storagePath));
        }
        $fsPath     = $this->root . $storagePath;
        return $fsPath;
	}

	/**
	 * @todo prepsat telo na dirname($path) ???
	 *
	 * Returns dirname.
	 * @param string $path
	 * @return string
	 */
	private function dirname($path) {
		return ($p = strrpos($path, '/')) ? substr($path, 0, $p) : false;
	}

	/**
	 * Creates dir recursive
	 * @param string $path Path in Storage (this is a storage path, not a file system path!)
	 * @throws Exception\IOException Cannot create directory.
	 */
	private function mkdir($path) {
		$absPath = $this->getFsPath($path);
		clearstatcache(true);
		if (!is_dir($absPath)) {
			clearstatcache(true);
			if (!@mkdir($absPath, 0777, true) && !is_dir($absPath)) {
				$error = null;
				$lastError = error_get_last();
				if ($lastError && isset($lastError['message'])) {
					$error = $lastError['message'];
				}
				throw new Exception\IOException("Cannot create directory '{$absPath}' for '{$path}' ({$error})");
			}
		}
		clearstatcache(true);
        //TODO - FIXME - chmod probably not necessary and may be safely removed
        //Without @ causes warnings when directory is owned by someone else and thus chmod cannot be executed
        @chmod($absPath, 0777);
	}

	/**
	 * Checks whether a file or directory exists.
	 *
	 * @param string $path Path to the file.
     * @return bool
     */
	public function contains($path) {
		$absPath = $this->getFsPath($path);
		return file_exists($absPath);
	}

	/**
	 * @param string $path
	 * @return bool
	 */
	public function isObject($path) {
		return is_file($this->getFsPath($path));
	}

	/**
	 * Gets file modification time if exists.
	 * @param string $path Path to the file.
	 * @return int|false
	 */
	public function mtime($path) {
		$absPath = $this->getFsPath($path);
		return file_exists($absPath) ? filemtime($absPath) : false;
	}

	/**
	 * Reads entire file into a string.
	 * @param string $path
	 * @throws \Vivo\Storage\Exception\IOException File not exists.
	 * @return string
	 */
	public function get($path) {
		if ($this->isObject($path)) {
			$absPath    = $this->getFsPath($path);
			return file_get_contents($absPath);
		}
		else {
			throw new Exception\IOException(sprintf("%s: Path '%s' does not exist", __METHOD__, $path));
		}
	}

	/**
	 * Write a string to a file.
	 * @param string $path
	 * @param mixed $data
	 * @throws Exception\IOException Cannot create directory.
	 */
	public function set($path, $data) {
		$this->mkdir($this->dirname($path));
		$absPath = $this->getFsPath($path);

		$result = file_put_contents($absPath, $data);
		if ($result === false) {
			$error = null;
			$lastError = error_get_last();
			if ($lastError && isset($lastError['message'])) {
				$error = $lastError['message'];
			}

			throw new Exception\IOException("Cannot write data to '$absPath' for '$path' ($error)");
		}
	}

	/**
	 * Sets access and modification time of file.
     * Creates the file if it does not exist
	 * @param string $path The name of the file being touched.
	 */
	public function touch($path) {
		$absPath    = $this->getFsPath($path);
        $storageDir = $this->pathBuilder->dirname($path);
        if ($storageDir) {
            $this->mkdir($storageDir);
        }
		touch($absPath);
		clearstatcache(true, $absPath);
	}

	/**
	 * Move.
	 * @param string $path Source path.
	 * @param string $target Destination path.
	 * @return bool
	 */
	public function move($path, $target) {
		if (stripos(getenv('OS'), 'windows') !== false) {
			// solves problems with php rename function on Windows (Access denied sometimes)
			if ($this->copy($path, $target)) {
				return (bool)$this->remove($path);
			} else {
				return false;
			}
		} else {
			$this->mkdir($this->dirname($target));
			return rename($this->getFsPath($path), $this->getFsPath($target));
		}
	}

	/**
	 * Copy.
	 * @param string $path Source path.
	 * @param string $target Destination path.
	 * @return int
	 */
	public function copy($path, $target) {
		$count = 0;

		if (is_dir($this->getFsPath($path))) {
			$this->mkdir($target);
			foreach ($this->scan($path) as $name) {
				$count += $this->copy("$path/$name", "$target/$name");
			}
		} else {
			$dirName = $this->pathBuilder->dirname($target);
			$this->mkdir($dirName);
			$count += copy($this->getFsPath($path), $this->getFsPath($target));
		}
		return $count;
	}

	/**
	 * Returns array with file paths.
	 * @param string $path
	 * @return array
	 */
	public function scan($path) {
		$names = array();
		$absPath = $this->getFsPath($path);
		if ($dir = @scandir($absPath)) {
			foreach ($dir as $name) {
				if ($name != '.' && $name != '..') {
					$names[] = $name;
				}
			}
		}
		return $names;
	}

	/**
	 * Removes directory.
	 * @param string $path
	 * @return int Count of deleted directories.
	 */
	public function remove($path) {
		$count = 0;
		$absPath = $this->getFsPath($path);
		if (is_dir($absPath)) {
			foreach ($this->scan($path) as $name)
				$count += $this->remove("$path/$name");
			rmdir($absPath);
			$count++;
		} else if (is_file($absPath) || is_link($absPath)) {
			unlink($absPath);
			$count++;
		}
		return $count;
	}

    /**
     * Returns input stream for reading resource.
     * @param string $path
     * @return \Vivo\IO\InputStreamInterface
     */
	public function read($path) {
		return new IO\FileInputStream($this->getFsPath($path));
	}

    /**
     * Returns output stream for writing resource.
     * @param string $path
     * @return \Vivo\IO\OutputStreamInterface
     */
	public function write($path) {
        //The directory must exist prior to instantiating the output stream, otherwise stream opening fails
        //The file will be also created beforehand for consistency reasons
        $components = $this->pathBuilder->getStoragePathComponents($path);
        array_pop($components);
        $storageDir = $this->pathBuilder->buildStoragePath($components, true, false, false);
        $fsFullPath = $this->getFsPath($path);
        $this->mkdir($storageDir);
        $this->touch($path);
		return new IO\FileOutputStream($fsFullPath);
	}

    /**
     * Returns size of the file in bytes
     * If $path is not a file, returns null
     * @param string $path
     * @return integer
     */
    public function size($path)
    {
        if ($this->isObject($path)) {
            $fsPath     = $this->getFsPath($path);
            $size       = filesize($fsPath);
        } else {
            $size   = null;
        }
        return $size;
    }

    /**
     * Returns Event Manager
     * @return \Zend\EventManager\EventManager
     */
    public function getEventManager()
    {
        if (!$this->eventManager) {
            $this->setEventManager(new EventManager());
        }
        return $this->eventManager;
    }

    /**
     * Sets Event manager
     * @param \Zend\EventManager\EventManager $eventManager
     */
    public function setEventManager(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * Returns object permissions
     * @param string $path
     * @param bool $oct Returns octal number when set to true, otherwise decimal
     * @throws Exception\IOException
     * @return int|string
     */
    public function getPermissions($path, $oct = false)
    {
        if (!$this->isObject($path)) {
            throw new Exception\IOException(sprintf("%s: No object found at path '%s'", __METHOD__, $path));
        }
        $fsPath     = $this->getFsPath($path);
        $permsDec   = fileperms($fsPath);
        if ($oct) {
            $permsDec   = decoct($permsDec);
        }
        return $permsDec;
    }
}
