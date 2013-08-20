<?php
namespace Vivo\CMS\RefInt;

/**
 * Interface SymRefConvertorInterface
 */
interface SymRefConvertorInterface
{
    /**
     * Pattern describing URL
     */
    const PATTERN_URL   = '\/[\w\d\-\/\.]+\/';

    /**
     * Pattern describing UUID
     */
    const PATTERN_UUID  = '[\d\w]{32}';

    /**
     * Converts URLs to symbolic references
     * @param string|array|object $value
     * @return string|array|object The same object / value
     */
    public function convertUrlsToReferences($value);

    /**
     * Converts symbolic references to URLs
     * @param string|array|object $value
     * @return string|array|object $value The same object / value
     */
    public function convertReferencesToURLs($value);
}
