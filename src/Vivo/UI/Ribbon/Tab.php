<?php
namespace Vivo\UI\Ribbon;

use Vivo\UI\TabContainerItemInterface;

use Vivo\UI;

/**
 * Ribbon Tab
 */
class Tab extends UI\ComponentContainer implements TabContainerItemInterface
{

    /**
     * Index of unnamed groups.
     * @var integer
     */
    protected $groupIndex = 0;

    /**
     * Tab label
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

    public function init()
    {
        $this->getView()->components = array_keys($this->components);
        $this->getView()->name = $this->getName();
        parent::init();
    }

    /**
     * Adds riboon group.
     * @param Group $group
     */
    public function addGroup(Group $group)
    {
        if ($name = $group->getName()) {
            $this->addComponent($group, $name);
        } else {
            $this->addComponent($group, 'group' . $this->groupIndex++);
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Vivo\UI\TabContainerItemInterface::select()
     */
    public function select()
    {
    }

    /**
     * (non-PHPdoc)
     * @see \Vivo\UI\TabContainerItemInterface::isDisabled()
     */
    public function isDisabled()
    {
        return false;
    }

    /**
     * (non-PHPdoc)
     * @see \Vivo\UI\TabContainerItemInterface::getLabel()
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Sets the tab label
     * @param string $label
     */
    public function setLabel($label = null)
    {
        $this->label = $label;
    }
}
