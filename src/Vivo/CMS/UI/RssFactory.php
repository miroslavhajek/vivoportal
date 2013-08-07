<?php
namespace Vivo\CMS\UI;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RssFactory implements FactoryInterface
{
    /**
     * (non-PHPdoc)
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     * @return \Vivo\CMS\UI\Rss
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new Rss($serviceLocator->get('Vivo\CMS\Api\Document'), $serviceLocator->get('response'));
    }

}
