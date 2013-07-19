<?php
namespace Vivo\CMS\Util;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * Document url helper factory
 */
class DocumentUrlHelperFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return DocumentUrlHelper
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /* @var $cmsApi \Vivo\CMS\Api\CMS */
        $cmsApi = $serviceLocator->get('Vivo\CMS\Api\CMS');
        /* @var $urlHelper \Vivo\Util\UrlHelper */
        $urlHelper = $serviceLocator->get('Vivo\Util\UrlHelper');

        $service = new DocumentUrlHelper($cmsApi, $urlHelper);
        return $service;
    }
}
