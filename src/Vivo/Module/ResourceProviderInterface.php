<?php
namespace Vivo\Module;

use Vivo\IO\FileInputStream;

/**
 * ResourceProviderInterface
 * Modules implementing this interface control access to their resources themselves
 */
interface ResourceProviderInterface
{
    /**
     * Returns the resource data
     * @param string $type
     * @param string $pathToResource
     * @return string
     * @throws \Vivo\Module\Exception\ResourceNotFoundException
     */
    public function getResource($type, $pathToResource);

    /**
     * Returns an input stream for the resource
     * @param string $type
     * @param string $pathToResource
     * @return FileInputStream
     * @throws \Vivo\Module\Exception\ResourceNotFoundException
     */
    public function getResourceStream($type, $pathToResource);

    /**
     * Returns the resource mtime
     * @param string $type
     * @param string $pathToResource
     * @return int
     */
    public function getResourceMtime($type, $pathToResource);
}