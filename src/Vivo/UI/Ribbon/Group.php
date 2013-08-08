<?php
namespace Vivo\UI\Ribbon;

use Vivo\UI;

/**
 * Ribbon Tab Group
 */
class Group extends UI\ComponentContainer
{
    /**
     * Group label
     * @var string
     */
    protected $label;

    /**
     * Constructor.
     * @param string $label
     */
    public function __construct($label = null)
    {
        $this->setLabel($label);
    }

    /**
     * (non-PHPdoc)
     * @see \Vivo\UI\ComponentContainer::init()
     */
    public function init()
    {
        $items = array();
        foreach($this->components as $component) {
            if($component->isVisible()) {
                $items[] = $component->getName();
            }
        }
        $this->getView()->components = $items;
        $this->getView()->label = $this->getLabel();

        parent::init();
    }

    /**
     * Adds ribbon item to group.
     * @param Item $item
     * @return \Vivo\UI\Ribbon\Group
     */
    public function addItem(Item $item)
    {
        $this->addComponent($item, $item->getName());
    }

    /**
     * Sets the group label
     * @param string $label
     */
    public function setLabel($label = null)
    {
        $this->label = $label;
    }

    /**
     * Returns the group label
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }
}
