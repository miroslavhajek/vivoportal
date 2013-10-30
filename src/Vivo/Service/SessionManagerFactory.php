<?php
namespace Vivo\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\Container;

use DateTime;

/**
 * SessionManagerFactory
 */
class SessionManagerFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config                 = $serviceLocator->get('config');
        $sessionConfigOptions   = $config['session_config']['options'];
        //Use secure options when appropriate
        if ($config['session_config']['security']['enabled']) {
            $request    = $serviceLocator->get('request');
            if ($request instanceof \Zend\Http\PhpEnvironment\Request) {
                $headerName = $config['session_config']['security']['security_http_header'];
                if ($request->getHeader($headerName) || $_SERVER['HTTPS']) {
                    $sessionConfigOptions   = $config['session_config']['secure_options'];
                }
            }
        }
        $sessionConfigObj   = new \Zend\Session\Config\SessionConfig();
        $sessionConfigObj->setOptions($sessionConfigOptions);
        $sessionManager     = new \Zend\Session\SessionManager($sessionConfigObj);

        //Start session
        $sessionManager->start();
        //Regenerate ID
        if ($config['session_config']['regenerate_id']) {
            $sessionManager->regenerateId(true);
        }
        Container::setDefaultManager($sessionManager);

        //Expire the session if too long inactivity
        $now        = time();
        $session    = new Container('vivo_session_management');
        if (isset($session->lastActivity)
                && ($now - $session->lastActivity > $config['session_config']['inactivity_expiration']['timeout'])) {
            $sessionManager->destroy($config['session_config']['inactivity_expiration']['destroy_options']);
        }
        $session->lastActivity  = $now;

        return $sessionManager;
    }
}
