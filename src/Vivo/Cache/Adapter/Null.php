<?php
namespace Vivo\Cache\Adapter;

use Zend\Cache\Exception;
use Zend\Cache\Storage\Adapter\AbstractAdapter as AbstractCacheAdapter;
use Zend\Cache\Storage\Capabilities;

use stdClass;

/**
 * Null
 * Null cache adapter always returns true on set/remove and always returns false on get
 * Can be used in place of a real cache adapter to effectively turn caching off
 */
class Null extends AbstractCacheAdapter
{
    /**
     * Internal method to get an item.
     *
     * @param  string $normalizedKey
     * @param  bool $success
     * @param  mixed $casToken
     * @return mixed Data on success, null on failure
     * @throws Exception\ExceptionInterface
     */
    protected function internalGetItem(& $normalizedKey, & $success = null, & $casToken = null)
    {
        $success = false;
        return null;
    }

    /**
     * Internal method to store an item.
     *
     * @param  string $normalizedKey
     * @param  mixed $value
     * @return bool
     * @throws Exception\ExceptionInterface
     */
    protected function internalSetItem(& $normalizedKey, & $value)
    {
        return true;
    }

    /**
     * Internal method to remove an item.
     *
     * @param  string $normalizedKey
     * @return bool
     * @throws Exception\ExceptionInterface
     */
    protected function internalRemoveItem(& $normalizedKey)
    {
        return true;
    }

    /**
     * Internal method to get capabilities of this adapter
     * @return Capabilities
     */
    protected function internalGetCapabilities()
    {
        if ($this->capabilities === null) {
            $marker  = new stdClass();
            $capabilities = new Capabilities(
                $this,
                $marker,
                array(
                    'supportedDatatypes' => array(
                        'NULL'     => true,
                        'boolean'  => true,
                        'integer'  => true,
                        'double'   => true,
                        'string'   => true,
                        'array'    => true,
                        'object'   => true,
                        'resource' => true,
                    ),
//                    'supportedMetadata'  => $metadata,
//                    'minTtl'             => 1,
//                    'maxTtl'             => 0,
//                    'staticTtl'          => false,
//                    'ttlPrecision'       => 1,
//                    'expiredRead'        => true,
//                    'maxKeyLength'       => 251, // 255 - strlen(.dat | .tag)
//                    'namespaceIsPrefix'  => true,
//                    'namespaceSeparator' => $options->getNamespaceSeparator(),
                )
            );
            $this->capabilityMarker = $marker;
            $this->capabilities     = $capabilities;
        }
        return $this->capabilities;
    }
}
