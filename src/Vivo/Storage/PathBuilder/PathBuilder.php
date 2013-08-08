<?php
namespace Vivo\Storage\PathBuilder;

use Vivo\Storage\Exception;
use Vivo\Transliterator\TransliteratorInterface;

/**
 * PathBuilder
 * Storage paths manipulation
 */
class PathBuilder implements PathBuilderInterface
{
    /**
     * Character used as a separator for paths in storage
     * @var string
     */
    protected $separator;

    /**
     * Path Transliterator
     * @var TransliteratorInterface
     */
    protected $pathTransliterator;
//
//    /**
//     * Character used to replace illegal path characters
//     * @var string
//     */
//    protected $replacementChar    = '-';

    /**
     * Constructor
     * @param string $separator Path components separator
     * @param \Vivo\Transliterator\TransliteratorInterface $pathTransliterator
     * @throws \Vivo\Storage\Exception\InvalidArgumentException
     */
    public function __construct($separator, TransliteratorInterface $pathTransliterator)
    {
        if (strlen($separator) != 1) {
            throw new Exception\InvalidArgumentException(
                sprintf("%s: Only single character separators supported; '%s' given", __METHOD__, $separator));
        }
        $this->separator            = $separator;
        $this->pathTransliterator   = $pathTransliterator;
    }

    /**
     * Returns character used as a separator in storage paths
     * @return string
     */
    public function getStoragePathSeparator()
    {
        return $this->separator;
    }

    /**
     * Builds storage path from submitted elements
     * @param array $elements
     * @param bool $leadingSeparator If true, prepends storage path separator
     * @param bool $trailingSeparator If true, appends storage path separator
     * @return string
     */
    public function buildStoragePath(array $elements, $leadingSeparator = true, $trailingSeparator = false)
    {
        $components = array();
        $separator  = $this->getStoragePathSeparator();
        //Get atomic components
        foreach ($elements as $element) {
            $elementComponents  = $this->getStoragePathComponents($element);
            $components         = array_merge($components, $elementComponents);
        }
        $path   = implode($separator, $components);
        if ($leadingSeparator) {
            $path   = $separator . $path;
        }
        if ($trailingSeparator && ($path != $separator)) {
            $path   = $path . $separator;
        }
        $path   = $this->pathTransliterator->transliterate($path);
        return $path;
    }

    /**
     * Returns an array of 'atomic' storage path components
     * @param string $path
     * @return array
     */
    public function getStoragePathComponents($path)
    {
        $return = array();
        foreach (explode($this->getStoragePathSeparator(), $path) as $value) {
            $value = trim($value);
            if ($value != '') {
                $return[] = $value;
            }
        }

        return $return;
    }

    /**
     * Returns sanitized path (trimmed, no double separators, etc.)
     * @param string $path
     * @return string
     */
    public function sanitize($path)
    {
        $absolute   = $this->isAbsolute($path);
        $components = $this->getStoragePathComponents($path);
        $separator  = $this->getStoragePathSeparator();
        $sanitized  = implode($separator, $components);
        if ($absolute) {
            $sanitized  = $separator . $sanitized;
        }
        $sanitized  = $this->pathTransliterator->transliterate($sanitized);
        return  $sanitized;
    }

    /**
     * Returns directory name for the given path
     * If there is no parent directory for the given $path, returns storage path separator
     * @param string $path
     * @return string
     */
    public function dirname($path)
    {
        $components = $this->getStoragePathComponents($path);
        array_pop($components);
        if (count($components) > 0) {
            $absolute   = $this->isAbsolute($path);
            $dir        = $this->buildStoragePath($components, $absolute);
        } else {
            $dir        = $this->getStoragePathSeparator();
        }
        return $dir;
    }

    /**
     * Returns trailing component of the path
     * @param string $path
     * @return string
     */
    public function basename($path)
    {
        $components = $this->getStoragePathComponents($path);
        $basename   = array_pop($components);
        return $basename;
    }

    /**
     * Returns true when the $path denotes an absolute path
     * @param string $path
     * @return boolean
     */
    public function isAbsolute($path)
    {
        $path       = trim($path);
        $firstChar  = substr($path, 0, 1);
        return $firstChar == $this->getStoragePathSeparator();
    }

    /**
     * Returns $path with all illegal characters removed and replaced with replacement character
     * @param string $path
     * @return string
     */
    protected function removeIllegalCharacters($path)
    {
        $cleaned            = '';
        $len                = strlen($path);
        for ($i = 0; $i < $len; $i++) {
            $cleaned    .= (stripos('abcdefghijklmnopqrstuvwxyz0123456789-_/.', $path{$i}) !== false)
                            ? $path[$i] : $this->replacementChar;
        }
        return $cleaned;
    }
}
