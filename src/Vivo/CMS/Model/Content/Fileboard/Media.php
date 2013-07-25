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

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setOrder($order)
    {
        $this->order = intval($order);
    }

    public function getOrder()
    {
        return $this->order;
    }
}
