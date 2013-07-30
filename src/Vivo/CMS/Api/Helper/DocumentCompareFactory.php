<?php
namespace Vivo\CMS\Api\Helper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for Document compare helper
 */
class DocumentCompareFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $translitMbStringCmp    = $serviceLocator->get('Vivo\Transliterator\MbStringCompare');
        $service = new DocumentCompare($translitMbStringCmp);
        return $service;
    }
}
