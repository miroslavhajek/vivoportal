<?php
namespace Vivo\UI;

use Vivo\UI\Ribbon\Group;
use Vivo\UI\Ribbon\Tab;
use Vivo\UI\Ribbon\Item;

/**
 * Ribbon
 */
class Ribbon extends TabContainer
{
    /**
     * @var integer
     */
    protected $tabIndex = 0;

	/**
	 * (non-PHPdoc)
	 * @see \Vivo\UI\TabContainer::view()
	 */
	public function view() {
        $this->view->name = $this->getName();
        $this->view->components = array_keys($this->components);
        return parent::view();
    }

    /**
     * Trigges event after click on ribbon item.
     * @param unknown $name
     */
    public function itemClick($name)
    {
        $this->getEventManager()->trigger(__FUNCTION__, $this, array('itemName' => $name));
    }

    /**
     * Adds ribbon tab.
     * @param Tab $tab
     */
    public function addTab(Tab $tab)
    {
        if ($name = $tab->getName()) {
            $this->addComponent($tab, $name);
        } else {
            $this->addComponent($tab, 'tab'.$this->tabIndex++);
        }
    }

    /**
     * Deactivates all ribbon items
     */
    public function deactivateAll()
    {
        $items  = $this->getItemsFlat();
        foreach ($items as $item) {
            $item->setActive(false);
        }
    }

    /**
     * Sets the specified item as active
     * @param string $name Item name
     * @throws Exception\InvalidArgumentException
     */
    public function setActive($name)
    {
        $items  = $this->getItemsFlat();
        if (!array_key_exists($name, $items)) {
            throw new Exception\InvalidArgumentException(sprintf("%s: Item '%s' does not exist", __METHOD__, $name));
        }
        $item   = $items[$name];
        $item->select();
    }

    /**
     * Returns a flat array of ribbon items
     * @return RibbonItemInterface[]
     */
    public function getItemsFlat()
    {
        $ribbonItems    = array();
        $components     = $this->getComponents();
        foreach ($components as $component) {
            if ($component instanceof Tab) {
                $itemsOrGroups  = $component->getComponents();
                foreach ($itemsOrGroups as $itemOrGroup) {
                    if ($itemOrGroup instanceof Group) {
                        //Ribbon group
                        $items  = $itemOrGroup->getComponents();
                        foreach ($items as $item) {
                            if ($item instanceof RibbonItemInterface) {
                                $ribbonItems[$item->getName()] = $item;
                            }
                        }
                    } elseif ($itemOrGroup instanceof RibbonItemInterface) {
                        //Ribbon item
                        $ribbonItems[$itemOrGroup->getName()] = $itemOrGroup;
                    }
                }
            }
        }
        return $ribbonItems;
    }
}
