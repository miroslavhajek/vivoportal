<?php
namespace Vivo\CMS\Model;

/**
 * ContentContainer wrap all version of one content in document.
 */
class ContentContainer extends Entity
{
    /**
     * @var string
     */
    protected $containerName;

    /**
     * @var integer
     */
    protected $order;

    /**
     * @return string
     */
    public function getContainerName()
    {
        return $this->containerName;
    }

    /**
     * @param string $containerName
     */
    public function setContainerName($containerName)
    {
        $this->containerName = $containerName;
    }

    /**
     * @return integer
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param integer $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }
}
