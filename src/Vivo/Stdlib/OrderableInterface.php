<?php
namespace Vivo\Stdlib;

interface OrderableInterface
{
    /**
     * @param int $order
     */
    public function setOrder($order);

    /**
     * @return int
     */
    public function getOrder();
}
