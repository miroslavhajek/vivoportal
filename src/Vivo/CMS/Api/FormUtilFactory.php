<?php
namespace Vivo\CMS\Api;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * FormUtilFactory
 */
class FormUtilFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @throws Exception\RuntimeException
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var $currentSite \Vivo\CMS\Model\Site */
        $currentSite    = $serviceLocator->get('Vivo\current_site');
        $siteName       = $currentSite->getName();
        if (is_null($siteName)) {
            throw new Exception\RuntimeException(sprintf("%s: Site name not available", __METHOD__));
        }
        /** @var $tgwProvider \Vivo\Db\DbTableGatewayProvider */
        $tgwProvider    = $serviceLocator->get('Vivo\db_table_gateway_provider');
        $tgwSavedForm   = $tgwProvider->get('saved_form');
        $session        = $serviceLocator->get('Vivo\site_session');
        $api            = new FormUtil($tgwSavedForm, $session, $siteName);
        return $api;
    }
}
