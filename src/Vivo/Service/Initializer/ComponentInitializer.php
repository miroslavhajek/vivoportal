<?php
namespace Vivo\Service\Initializer;

use Vivo\UI\ComponentEventInterface;

use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Initializer for UI components.
 */
class ComponentInitializer implements InitializerInterface
{
    /**
     * (non-PHPdoc)
     * @see \Zend\ServiceManager\InitializerInterface::initialize()
     */
    public function initialize($instance, ServiceLocatorInterface $serviceLocator)
    {
        if ($instance instanceof \Vivo\UI\Component) {
            //Inject view model
            $viewModel  = $serviceLocator->get('view_model');
            $instance->setView($viewModel);
            //Attach listeners to run the deprecated init(), view() and done() methods
            /** @var $componentEventListener \Vivo\UI\ComponentEventListener */
            $componentEventListener = $serviceLocator->get('Vivo\component_event_listener');
            $componentEvents        = $instance->getEventManager();
            $componentEvents->attach(ComponentEventInterface::EVENT_INIT,
                                     array($componentEventListener, 'initListenerInitMethod'));
            $componentEvents->attach(ComponentEventInterface::EVENT_VIEW,
                                     array($componentEventListener, 'viewListenerViewMethod'));
            $componentEvents->attach(ComponentEventInterface::EVENT_DONE,
                                     array($componentEventListener, 'doneListenerDoneMethod'));
            //Attach listeners the component provides
            $instance->attachListeners($serviceLocator);
        }
    }
}
