<?php
namespace Vivo\CMS\Listener;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RssListenerFactory implements FactoryInterface
{
    /**
     * (non-PHPdoc)
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     * @return Vivo\CMS\Listener\RssListener
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new RssListener($serviceLocator->get('Vivo\CMS\UI\Rss'), $serviceLocator->get('request'));
    }

}
