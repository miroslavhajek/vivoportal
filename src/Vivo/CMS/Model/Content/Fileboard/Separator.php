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

    public function setOrder($order)
    {
        $this->order = intval($order);
    }

    public function getOrder()
    {
        return $this->order;
    }
}
