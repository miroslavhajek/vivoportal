<?php
namespace Vivo\UI;

/**
 * TabContainer
 */
class TabContainer extends ComponentContainer implements PersistableInterface
{
    /**
     * @var string Component / tab name.
     */
    private $selected;

    /**
     * Render all tabs?
     * @var boolean
     */
    private $viewAll = false;

    /**
     * Method for selecting a tab. Call method selected() on selected component.
     * @param strign $name Tab name.
     * @throws Exception\InvalidArgumentException
     */
    public function select($name)
    {
        if(!isset($this->components[$name])) {
            throw new Exception\InvalidArgumentException(sprintf('Tab \'%s\' not exists', $name));
        }

        $selectedComponent = $this->components[$name];

        if ($selectedComponent instanceof TabContainerItemInterface) {
            $selectedComponent->select();
        }

        $this->selected = $name;
    }

    public function addComponent(ComponentInterface $component, $name)
    {
        parent::addComponent($component, $name);

        if($this->selected == null) {
            $this->select($name);
        }
    }

    /**
     * @return string
     */
    public function getSelected()
    {
        return $this->selected;
    }

    /**
     * @return \Vivo\UI\Component
     */
    public function getSelectedComponent()
    {
        return parent::getComponent($this->selected);
    }

    /**
     * @param boolean $value
     */
    public function setViewAll($value)
    {
        $this->viewAll = (boolean)$value;
    }

    /**
     * Prepare data for view.
     * @return array
     */
    public function prepareTabs()
    {
        $tabs = array();

        foreach($this->components as $name => $component) {
            if ($component instanceof TabContainerItemInterface) {
                if(!$component->isDisabled()) {
                    $tab = array(
                        'name' => $name,
                        'label' => $component->getLabel()
                    );
                }
            } else {
                $tab = array('name' => $name, 'label' => "($name)");
            }
            $tabs[] = $tab;
        }
        return $tabs;
    }

    /**
     * (non-PHPdoc)
     * @see \Vivo\UI\ComponentContainer::view()
     */
    public function view()
    {
        $this->view->tabs = $this->prepareTabs();
        $this->view->components = $keys = array_keys($this->components);
        $this->view->selected = $this->selected ?: reset($keys);
        $this->view->viewAll = $this->viewAll;
        return parent::view();
    }

    /**
     * (non-PHPdoc)
     * @see \Vivo\UI\PersistableInterface::saveState()
     */
    public function saveState()
    {
        return $this->selected;
    }

    /**
     * (non-PHPdoc)
     * @see \Vivo\UI\PersistableInterface::loadState()
     */
    public function loadState($state)
    {
        $this->selected = $state;
    }
}
