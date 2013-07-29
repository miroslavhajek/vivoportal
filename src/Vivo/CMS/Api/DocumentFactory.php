<?php
namespace Vivo\CMS\Api;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * CmsApiDocumentFactory
 */
class DocumentFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $cms                    = $serviceLocator->get('Vivo\CMS\Api\CMS');
        $repository             = $serviceLocator->get('repository');
        $pathBuilder            = $serviceLocator->get('path_builder');
        $uuidGenerator          = $serviceLocator->get('uuid_generator');
        $translitDocTitleToPath = $serviceLocator->get('Vivo\Transliterator\DocTitleToPath');
        $translitMbStringCmp    = $serviceLocator->get('Vivo\Transliterator\MbStringCompare');
        $config                 = $serviceLocator->get('config');
        $options                = $this->prepareOptions($config['cms']);
        $api                    = new Document($cms,
                                       $repository,
                                       $pathBuilder,
                                       $uuidGenerator,
                                       $translitDocTitleToPath,
                                       $translitMbStringCmp,
                                       $options);
        return $api;
    }

    /**
     * @param array $options
     * @return array
     */
    private function prepareOptions($config)
    {
        $options = array(
            'languages' => $config['languages'],
        );
        foreach ($config['workflow']['states'] as $row) {
            $options['workflow']['states'][$row['state']] = $row['groups'];
        }

        return $options;
    }
}
