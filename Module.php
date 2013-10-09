<?php
namespace Vivo;

use Vivo\View\Helper as ViewHelper;
use Vivo\SiteManager\Event\SiteEventInterface;

use VpLogger\Log\Logger;

use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\ModuleManager\Feature\ConsoleBannerProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;

class Module implements ConsoleBannerProviderInterface, ConsoleUsageProviderInterface
{
    /**
     * Module bootstrap method.
     * @param MvcEvent $e
     */
    public function onBootstrap(MvcEvent $e)
    {
        //Get basic objects
        /** @var $sm ServiceManager */
        $sm             = $e->getApplication()->getServiceManager();
        $eventManager   = $e->getApplication()->getEventManager();
//        $config         = $sm->get('config');

        $eventManager->attach(MvcEvent::EVENT_DISPATCH, array($this, 'setHttpHeaders'), 1000);

        //Register custom navigation view helpers
        $navHelperRegistrar   = new \Vivo\Service\NavigationHelperRegistrar();
        $navHelperRegistrar->registerNavigationHelpers($sm);
        //Performance log
        $eventManager->trigger('log', $this,
            array ('message'    => 'Vivo Portal Module bootstrapped',
                   'priority'   => Logger::PERF_BASE));
    }

    public function getConfig()
    {
        $config = include __DIR__ . '/config/module.config.php'; //main vivo config
        $config['cms'] = include __DIR__ . '/config/cms.config.php'; //CMS config
        return $config;
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConsoleBanner(Console $console)
    {
        return "Vivo 2 CLI\n";
    }

    public function getConsoleUsage(Console $console)
    {
        return array('Available commands:',
                array ('indexer', 'Perform operations on indexer..'),
                array ('info','Show information about CMS instance.'),
                array ('module', 'Manage modules.'),
                array ('cms', 'CMS functions.'),
                array ('setup', 'System setup'),
                array ('util', 'Utilities'),
        );
    }

    /**
     * Listener setting HTTP headers
     * @param MvcEvent $e
     * @throws \Exception
     */
    public function setHttpHeaders(MvcEvent $e)
    {
        $sm                 = $e->getApplication()->getServiceManager();
        $response           = $sm->get('response');
        /** @var $status \Vivo\Service\Status */
        $status             = $sm->get('Vivo\status');
        if ($response instanceof \Vivo\Http\StreamResponse) {
            $responseHeaders    = $response->getHeaders();
            if ($status->isBackend()) {
                //This is backend
                $config             = $sm->get('config');
                if (isset($config['response']['headers']['backend'])) {
                    $this->doSetHttpHeaders($responseHeaders, $config['response']['headers']['backend']);
                }
            } else {
                //This is front end
                $config             = $sm->get('cms_config');
                if (isset($config['response_headers_frontend'])) {
                    $this->doSetHttpHeaders($responseHeaders, $config['response_headers_frontend']);
                }
            }
        }
    }

    /**
     * Actual procedure to set the http headers according to config
     * @param \Zend\Http\Headers $responseHeaders
     * @param array $headerConfig
     * @throws \Exception
     */
    protected function doSetHttpHeaders(\Zend\Http\Headers $responseHeaders, array $headerConfig)
    {
        //Static HTTP headers
        if (isset($headerConfig['static'])) {
            $staticHeaders      = $headerConfig['static'];
            foreach ($staticHeaders as $header => $value) {
                if (!is_null($value)) {
                    $responseHeaders->addHeaderLine($header, $value);
                }
            }
        }
        //Dynamic HTTP headers
        if (isset($headerConfig['dynamic'])) {
            $dynamicHeaders = $headerConfig['dynamic'];
            foreach ($dynamicHeaders as $header => $enabled) {
                if ($enabled) {
                    switch ($header) {
                        //X-Generated-At
                        case 'X-Generated-At':
                            $value  = gmdate('D, d M Y H:i:s', time()) . ' GMT';
                            break;
                        //Unsupported
                        default:
                            throw new \Exception(
                                sprintf("%s: Unsupported dynamic header '%s'", __METHOD__, $header));
                            break;
                    }
                    $responseHeaders->addHeaderLine($header, $value);
                }
            }
        }
    }
}
