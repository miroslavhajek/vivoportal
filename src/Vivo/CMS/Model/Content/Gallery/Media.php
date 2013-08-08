<?php
namespace Vivo\CMS\Model\Content\Gallery;

use Vivo\CMS\Model\Content;
use Vivo\Stdlib\OrderableInterface;

class Media extends Content\File implements OrderableInterface
{
    /**
     * @var string Media name.
     */
    protected $name;

    /**
     * @var string Media description.
     */
    protected $description;

    /**
     * @var bool
     */
    protected $main = false;

    /**
     * @var int Size.
     */
    protected $originalWidth;

    /**
     * @var int Size.
     */
    protected $originalHeight;

    /**
     * @var int Order.
     */
    protected $order = 0;

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param bool $main
     */
    public function setMain($main = true)
    {
        $this->main = (bool)$main;
    }

    /**
     * @return boolean
     */
    public function getMain()
    {
        return $this->main;
    }

    /**
     * Image size.
     *
     * @param int $width
     */
    public function setOriginalWidth($width)
    {
        $this->originalWidth = intval($width);
    }

    /**
     * Image size.
     *
     * @return int
     */
    public function getOriginalWidth()
    {
        return $this->originalWidth;
    }

    /**
     * Image size.
     *
     * @param int $height
     */
    public function setOriginalHeight($height)
    {
        $this->originalHeight = intval($height);
    }

    /**
     * Image size.
     *
     * @return int
     */
    public function getOriginalHeight()
    {
        return $this->originalHeight;
    }

    /**
     * @return bool
     */
    public function isLandscape()
    {
        return $this->originalWidth > $this->originalHeight;
    }

    /**
     * (non-PHPdoc)
     * @see \Vivo\Stdlib\OrderableInterface::setOrder()
     */
    public function setOrder($order)
    {
        $this->order = intval($order);
    }

    /**
     * (non-PHPdoc)
     * @see \Vivo\Stdlib\OrderableInterface::getOrder()
     */
    public function getOrder()
    {
        return $this->order;
    }
}
