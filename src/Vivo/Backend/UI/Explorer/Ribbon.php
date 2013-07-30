<?php
namespace Vivo\Backend\UI\Explorer;

use Vivo\UI\Ribbon\Tab;
use Vivo\UI\Ribbon\Group;
use Vivo\UI\Ribbon\Item;

use Zend\EventManager\EventManagerAwareInterface;

/**
 * Ribbon for explorer.
 *
 */
class Ribbon extends \Vivo\UI\Ribbon implements EventManagerAwareInterface
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->setViewAll(true);

        $tab = new Tab('Document');
        $this->addTab($tab);

        $group = new Group('Show');
        //Viewer
        $item     = new Item('viewer', 'View', '', $this);
        $group->addItem($item);
        //Browser
        $item    = new Item('browser', 'Browse', '', $this);
        $group->addItem($item);
        $tab->addGroup($group);

        $group  = new Group('Editor');
        //Editor
        $item = new Item('editor', 'Edit', '', $this);
        $group->addItem($item);
        $tab->addGroup($group);

        $group = new Group('Structure');
        //Creator
        $item   = new Item('creator', 'Create', '', $this);
        $group->addItem($item);
        //Copy
        $item   = new Item('copy', 'Copy', '', $this);
        $group->addItem($item);
        //Move
        $item   = new Item('move', 'Move', '', $this);
        $group->addItem($item);
        //Delete
        $item   = new Item('delete', 'Delete', '', $this);
        $group->addItem($item);
        $tab->addGroup($group);

        $tab = new Tab('Advanced');
        $this->addTab($tab);

        $group  = new Group('Expert');
        $item   = new Item('inspect', 'Inspect', '', $this);
        $group->addItem($item);
        $tab->addGroup($group);
    }

    /**
     * (non-PHPdoc)
     * @see \Vivo\UI\Component::getDefaultTemplate()
     */
    public function getDefaultTemplate()
    {
        //use parent template
        return get_parent_class($this);
    }
}
