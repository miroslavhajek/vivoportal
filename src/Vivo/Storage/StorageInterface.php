<?php
namespace Vivo\Storage;

use Vivo\Storage\PathBuilder\PathBuilderInterface;

/**
 * StorageInterface
 */
interface StorageInterface {

	/**
	 * Checks whether item exists (either object or folder)
	 * @param string $path to item
	 * @return boolean TRUE if item exists otherwise FALSE
	 */
	public function contains($path);

	/**
	 * Checks whether item on the given path is an object
     * Returns true if the path represents an object
     * Returns false if the path does not exist or represents a folder
	 * @param string $path Path to the item
	 * @return bool
	 */
	public function isObject($path);

	/**
	 * Returns item modification time in milliseconds.
	 * @param string $path to item
	 * @return int|bool Item modification time in milliseconds or FALSE if the item doesn't exist
	 */
	public function mtime($path);

    /**
     * Returns size of the file in bytes
     * If $path is not a file, returns null
     * @param string $path
     * @return integer
     */
    public function size($path);

	/**
	 * Retrieves an item from storage and returns it
	 * If the item doesn't exist under specified path throws an exception
	 * @param string $path to item
	 * @return mixed
     * @throws \Vivo\Storage\Exception\IOException When file does not exist
	 */
	public function get($path);

	/**
	 * Saves an item to storage
	 * If an item already exists under the specified path, it will be overwritten
	 * @param string $path to item
	 * @param mixed $data
	 */
	public function set($path, $data);

	/**
	 * Touches item in the storage
	 * Resets the item modification time to the current system time (equivalent to the standard UNIX command touch)
     * If item does not exist, creates it
	 * @param string $path to item
	 */
	public function touch($path);

	/**
	 * Renames/moves an item (equivalent to the standard UNIX command mv (move))
	 * @param string $path to item
	 * @param string $target path
     * @return bool
	 */
	public function move($path, $target);

	/**
	 * Copies an item to another location (equivalent to standard UNIX command cp (copy))
	 * @param string $path to item
	 * @param string $target path
	 */
	public function copy($path, $target);

	/**
	 * Returns list of child items
	 * @param string $path to item
     * @return array
	 */
	public function scan($path);

	/**
	 * Removes an item from specified path and returns true
     * If the item doesn't exist, returns false
	 * @param string $path to item
	 * @return boolean TRUE on success, FALSE if the item doesn't exist
	 */
	public function remove($path);
	
	/**
	 * Returns input stream for reading resource.
	 * @param string $path
	 * @return \Vivo\IO\InputStreamInterface
	 */
	public function read($path);
	
	/**
	 * Returns output stream for writing resource. 
	 * @param string $path
	 * @return \Vivo\IO\OutputStreamInterface
	 */
	public function write($path);

    /**
     * Returns PathBuilder for this storage
     * @return PathBuilderInterface
     */
    public function getPathBuilder();

    /**
     * Returns item permissions
     * @param string $path
     * @param bool $oct Returns octal number when set to true, otherwise decimal
     * @return int|string
     */
    public function getPermissions($path, $oct = false);
}
