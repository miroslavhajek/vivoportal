<?php
namespace Vivo\CMS\Validator;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * Unique Entity Path Factory
 */
class UniqueEntityPathFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();
        $repository             = $sm->get('repository');
        $pathBuilder            = $sm->get('path_builder');
        $translitDocTitleToPath = $sm->get('Vivo\Transliterator\DocTitleToPath');
        $routeMatch = $sm->get('site_event')->getRouteMatch();
        $uuid = $routeMatch->getParam('path');
        /* @var $cmsApi \Vivo\CMS\Api\CMS */
        $cmsApi = $sm->get('Vivo\CMS\Api\CMS');
        $parentDocument = $cmsApi->getEntity($uuid);
        $parentDocumentPath = $parentDocument->getPath();
        $service = new UniqueEntityPath($pathBuilder, $translitDocTitleToPath, $repository, $parentDocumentPath);
        return $service;
    }
}
