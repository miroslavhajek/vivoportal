<?php
namespace Vivo\CMS\Model\Content\Fileboard;

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

    /**
     * Sets media description text
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Returns media description text
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
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
