<?php
namespace Vivo\Storage\PathBuilder;

/**
 * PathBuilderInterface
 */
interface PathBuilderInterface
{
    /**
     * Returns character used as a separator in storage paths
     * @return string
     */
    public function getStoragePathSeparator();

    /**
     * Builds storage path from submitted elements
     * @param array $elements
     * @param bool $leadingSeparator If true, prepends storage path separator
     * @param bool $trailingSeparator If true, appends storage path separator
     * @param bool $transliterate Should the resulting path be transliterated to contain only allowed chars?
     * @return string
     */
    public function buildStoragePath(array $elements,
                                     $leadingSeparator = true,
                                     $trailingSeparator = false,
                                     $transliterate = true);

    /**
     * Returns an array of 'atomic' storage path components
     * @param string $path
     * @return array
     */
    public function getStoragePathComponents($path);

    /**
     * Returns sanitized path (trimmed, no double separators, etc.)
     * @param string $path
     * @return string
     */
    public function sanitize($path);

    /**
     * Returns true when the $path denotes an absolute path
     * @param string $path
     * @return boolean
     */
    public function isAbsolute($path);

    /**
     * Returns directory name for the given path
     * If there is no parent directory for the given $path, returns storage path separator
     * @param string $path
     * @return string
     */
    public function dirname($path);

    /**
     * Returns trailing component of the path
     * @param string $path
     * @return string
     */
    public function basename($path);
}