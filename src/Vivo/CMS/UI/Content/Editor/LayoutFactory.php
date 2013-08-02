<?php
namespace Vivo\CMS\UI\Content\Editor;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class LayoutFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $docApi = $serviceLocator->get('Vivo\CMS\Api\Document');
        $newFormFactory = $serviceLocator->get('Vivo\new_form_factory');
        $editor = new Layout($docApi, $newFormFactory);

        return $editor;
    }

}
