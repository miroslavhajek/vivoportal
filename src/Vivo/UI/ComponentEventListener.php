<?php
namespace Vivo\UI;

use Vivo\UI\ComponentEventInterface;

/**
 * ComponentEventListener
 */
class ComponentEventListener
{
    /**
     * If the component has init() method, runs it
     * This provides backward compatibility for UI components utilizing the init() method
     * Instead of using the init() method attach a listener for an appropriate component event
     * @param ComponentEventInterface $event
     */
    public function initListenerInitMethod(ComponentEventInterface $event)
    {
        $component  = $event->getComponent();
        if (method_exists($component, 'init')) {
            $component->init();
        }
    }

    /**
     * If the component has view() method, runs it
     * This provides backward compatibility for UI components utilizing the view() method
     * Instead of using the view() method attach a listener for an appropriate component event
     * @param ComponentEventInterface $event
     */
    public function viewListenerViewMethod(ComponentEventInterface $event)
    {
        $component  = $event->getComponent();
        if (method_exists($component, 'view')) {
            $component->view();
        }
    }

    /**
     * If the component has done() method, runs it
     * This provides backward compatibility for UI components utilizing the done() method
     * Instead of using the done() method attach a listener for an appropriate component event
     * @param ComponentEventInterface $event
     */
    public function doneListenerDoneMethod(ComponentEventInterface $event)
    {
        $component  = $event->getComponent();
        if (method_exists($component, 'done')) {
            $component->done();
        }
    }
}
