<?php
namespace Vivo\View\Helper;

use Zend\Cache\Storage\StorageInterface;
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
        /** @var $cache StorageInterface */
        $cache              = $cacheManager->get($config['cache']['head_script_merge']);
        $urlHelper          = $sm->get('Vivo\Util\UrlHelper');
        $resourceManager    = $sm->get('Vivo\module_resource_manager');
        if (isset($config['view_helper_options']['Vivo\head_script_merge'])) {
            $options    = $config['view_helper_options']['Vivo\head_script_merge'];
        } else {
            $options    = array();
        }
        $helper             = new HeadScriptMerge($cache, $urlHelper, $resourceManager, $options);
        return $helper;
    }
}