<?php
namespace Vivo\UI;

use Vivo\UI\Exception\ComponentNotExists;

use Zend\View\Model\ModelInterface;

/**
 * @todo implement ArrayAccess?
 */

class ComponentContainer extends Component implements ComponentContainerInterface
{

    /**
     * @var array of ComponentInterface
     */
    protected $components = array();

    public function __get($name)
    {
        if ($this->hasComponent($name)) {
            return $this->getComponent($name);
        }
        return $this->$name; //notice if property doesn't exist
    }

    public function __isset($name)
    {
        return $this->hasComponent($name);
    }

    public function __unset($name)
    {
        if ($this->hasComponent($name))
            $this->removeComponent($name);
    }

    public function __set($name, $object)
    {
        if ($object instanceof ComponentInterface) {
            $this->addComponent($object, $name);
        } else {
            $this->$name = $object;
        }
    }

    /* (non-PHPdoc)
     * @see Vivo\UI.ComponentContainerInterface::addComponent()
     */
    public function addComponent(ComponentInterface $component, $name)
    {
        //TODO check cycles in component tree
        $component->setParent($this, $name);
        $this->components[$name] = $component;
    }

    public function addComponents(array $components)
    {
        foreach ($components as $name => $component) {
            $this->addComponent($component, $name);
        }
    }

    /* (non-PHPdoc)
     * @see Vivo\UI.ComponentContainerInterface::removeComponent()
     */
    public function removeComponent($name)
    {
        //TODO also accept component object as parameter

        if (!$this->hasComponent($name)) {
            throw new ComponentNotExists(
                "Component `$name` doesn't exist in container `"
                    . $this->getPath() . "`.");
        }
        $this->getComponent($name)->setParent(null, null);
        unset($this->components[$name]);
    }

    public function removeAllComponents()
    {
        foreach (array_keys($this->components) as $name) {
            $this->removeComponent($name);
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Vivo\UI\ComponentContainerInterface::getComponent()
     */
    public function getComponent($name)
    {
        if (!$this->hasComponent($name)) {
            throw new ComponentNotExists(
                "Component `$name` doesn't exist in container `"
                    . $this->getPath() . "`.");
        }
        return $this->components[$name];
    }

    /**
     * Returns components.
     * @return array
     */
    public function getComponents()
    {
        return $this->components;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasComponent($name)
    {
        return isset($this->components[$name]);
    }

    public function view()
    {
        $viewModel = parent::view();
        foreach ($this->components as $name => $component) {
            $model = $component->view();

            if ($model instanceof ModelInterface) {
                $viewModel->addChild($model, $name);
            } else {
                $viewModel->setVariable($name, $model);
            }
        }

        $container = array (
            'components' => array_keys($this->components),
        );
        $viewModel->setVariable('container', $container);
        return $viewModel;
    }
}
