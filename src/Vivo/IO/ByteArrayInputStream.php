<?php
namespace Vivo\IO;

use Vivo\IO\Exception\InvalidArgumentException;
use Vivo\IO\InputStreamInterface;

/**
 * ByteArrayInputStream
 * Reads data from a string in memory
 */
class ByteArrayInputStream implements InputStreamInterface
{
    /**
     * @var integer
     */
    private $position = 0;

    /**
     * @var string
     */
    private $data;

    /**
     *
     * @param string $data
     */
    public function __construct(&$data)
    {
        $this->data = &$data;
    }

    /**
     * @param int $bytes
     * @return string
     * @throws InvalidArgumentException
     */
    public function read($bytes = 1)
    {
        if (!is_int($bytes) || $bytes < 1) {
            throw new InvalidArgumentException(
                'Parameter $bytes must be integer.');
        }
        $data = substr($this->data, $this->position, $bytes);
        $this->position += $bytes;
        return $data;
    }
}
