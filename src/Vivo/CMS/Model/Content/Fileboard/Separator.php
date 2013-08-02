<?php
namespace Vivo\CMS\Model\Content\Fileboard;

use Vivo\CMS\Model\Content;
use Vivo\Stdlib\OrderableInterface;

class Separator extends Content\File implements OrderableInterface
{
    /**
     * @var int Order.
     */
    protected $order = 0;

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
