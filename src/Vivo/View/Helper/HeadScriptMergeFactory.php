<?php
namespace Vivo\View\Helper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * HeadScriptMergeFactory
 */
class HeadScriptMergeFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @throws Exception\ConfigException
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm                 = $serviceLocator->getServiceLocator();
        $config             = $sm->get('config');
        //Get cache for merged files
        if (!isset($config['cache']['head_script_merge'])) {
            throw new Exception\ConfigException(
                sprintf("%s: Config option ['cache']['head_script_merge'] defining cache used for merged "
                . "script files is not set", __METHOD__));
        }
        $cacheManager       = $sm->get('cache_manager');
        $cache              = $cacheManager->get($config['cache']['head_script_merge']);
        $urlHelper          = $sm->get('Vivo\Util\UrlHelper');
        $resourceManager    = $sm->get('Vivo\module_resource_manager');
        $helper             = new HeadScriptMerge($cache, $urlHelper, $resourceManager);
        return $helper;
    }
}
