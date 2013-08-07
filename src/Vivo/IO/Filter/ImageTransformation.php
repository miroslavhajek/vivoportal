<?php
namespace Vivo\IO\Filter;

use Vivo\IO\InputStreamInterface;

/**
 * IO filter for transforming images.
 * @todo implement
 *
 */
class ImageTransformation implements InputStreamInterface
{

    /**
     * @var InputStreamInterface
     */
    protected $inputStream;

    /**
     * @var InputStreamInterface
     */
    protected $transformedStream;

    /**
     * Filter options.
     * @var array
     */
    protected $options = array();

    /**
     * @var boolean
     */
    protected $transformed = false;

    /**
     * Constructor.
     * @param InputStreamInterface $inputStream
     * @param array $options
     */
    public function __construct(InputStreamInterface $inputStream, $options = array())
    {
        $this->inputStream = $inputStream;
        $this->options = $options;
    }

    /**
     * (non-PHPdoc)
     * @see \Vivo\IO\InputStreamInterface::read()
     */
    public function read($bytes = 1)
    {
        if (!$this->transformed) $this->transformImage();
        return $this->transformedStream->read($bytes);
    }

    /**
     * Transforms image.
     */
    protected function transformImage() {
        throw \Exception('Not implemented');
        //TODO transform
        $this->transformedStream = $this->inputStream;
        $this->transformed = true;
    }
}
