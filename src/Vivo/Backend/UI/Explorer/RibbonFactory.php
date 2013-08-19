<?php
namespace Vivo\Backend\UI\Explorer;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * RibbonFactory
 */
class RibbonFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $ribbon     = new Ribbon();
        /** @var $componentCreator \Vivo\UI\ComponentCreator */
        $componentCreator   = $serviceLocator->get('Vivo\component_creator');

        /** @var $tab \Vivo\UI\Ribbon\Tab */
        $tab        = $componentCreator->createComponent('Vivo\UI\Ribbon\Tab');
        $tab->setLabel('Document');
        $ribbon->addTab($tab);

        /** @var $group \Vivo\UI\Ribbon\Group */
        $group      = $componentCreator->createComponent('Vivo\UI\Ribbon\Group');
        $group->setLabel('Show');
        //Viewer
        /** @var $item \Vivo\UI\Ribbon\Item */
        $item       = $componentCreator->createComponent('Vivo\UI\Ribbon\Item');
        $item->setName('viewer');
        $item->setLabel('View');
        $item->setRibbon($ribbon);
        $group->addItem($item);
        //Browser
        $item       = $componentCreator->createComponent('Vivo\UI\Ribbon\Item');
        $item->setName('browser');
        $item->setLabel('Browse');
        $item->setRibbon($ribbon);
        $group->addItem($item);
        $tab->addGroup($group);

        $group      = $componentCreator->createComponent('Vivo\UI\Ribbon\Group');
        $group->setLabel('Editor');
        //Editor
        $item       = $componentCreator->createComponent('Vivo\UI\Ribbon\Item');
        $item->setName('editor');
        $item->setLabel('Edit');
        $item->setRibbon($ribbon);
        $group->addItem($item);
        $tab->addGroup($group);

        $group      = $componentCreator->createComponent('Vivo\UI\Ribbon\Group');
        $group->setLabel('Structure');
        //Creator
        $item       = $componentCreator->createComponent('Vivo\UI\Ribbon\Item');
        $item->setName('creator');
        $item->setLabel('Create');
        $item->setRibbon($ribbon);
        $group->addItem($item);
        //Copy
        $item       = $componentCreator->createComponent('Vivo\UI\Ribbon\Item');
        $item->setName('copy');
        $item->setLabel('Copy');
        $item->setRibbon($ribbon);
        $group->addItem($item);
        //Move
        $item       = $componentCreator->createComponent('Vivo\UI\Ribbon\Item');
        $item->setName('move');
        $item->setLabel('Move');
        $item->setRibbon($ribbon);
        $group->addItem($item);
        //Delete
        $item       = $componentCreator->createComponent('Vivo\UI\Ribbon\Item');
        $item->setName('delete');
        $item->setLabel('Delete');
        $item->setRibbon($ribbon);
        $group->addItem($item);
        $tab->addGroup($group);

        $tab        = $componentCreator->createComponent('Vivo\UI\Ribbon\Tab');
        $tab->setLabel('Advanced');
        $ribbon->addTab($tab);

        $group      = $componentCreator->createComponent('Vivo\UI\Ribbon\Group');
        $group->setLabel('Expert');
        $item       = $componentCreator->createComponent('Vivo\UI\Ribbon\Item');
        $item->setName('inspect');
        $item->setLabel('Inspect');
        $item->setRibbon($ribbon);
        $group->addItem($item);
        $tab->addGroup($group);
        return $ribbon;
    }
}
