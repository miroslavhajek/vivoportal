<?php
namespace Vivo\Session;

use Vivo\Filter\AsciiAlphaNum;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * SiteSessionContainerFactory
 * Creates session container for the current site
 */
class SiteSessionContainerFactory implements FactoryInterface
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
        if (is_null($currentSite)) {
            throw new Exception\RuntimeException(sprintf("%s: Site model not available set", __METHOD__));
        }
        $siteName       = $currentSite->getName();
        if (is_null($siteName)) {
            throw new Exception\RuntimeException(sprintf("%s: Site name not available", __METHOD__));
        }
        $filter         = new AsciiAlphaNum();
        $siteNameMod    = $filter->filter($siteName);
        $containerName  = 'site_' . $siteNameMod;
        $container  = new \Zend\Session\Container($containerName);
        return $container;
    }
}
