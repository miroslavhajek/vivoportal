<?php
namespace Vivo\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\Container;

/**
 * SessionManagerFactory
 */
class SessionManagerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config         = $serviceLocator->get('config');
        if (isset($config['session_config']['options'])) {
            $sessionConfigOptions  = $config['session_config']['options'];
        } else {
            $sessionConfigOptions  = array();
        }
        //Configure cookie according to the security
        if (isset($config['session_config']['security']['enabled'])
                && $config['session_config']['security']['enabled']) {
            if (isset($config['session_config']['security']['secure_ports'])
                && in_array($_SERVER['SERVER_PORT'], $config['session_config']['security']['secure_ports'])) {
                //Access via secure port
                $sessionConfigOptions['cookie_httponly']    = false;
                $sessionConfigOptions['cookie_secure']      = true;
                $sessionConfigOptions['name']
                    = $config['session_config']['security']['secure_session_name'];
            } else {
                //Access via 'normal' port
                $sessionConfigOptions['cookie_httponly']    = true;
                $sessionConfigOptions['cookie_secure']      = false;
            }
        }
        $sessionConfigObj   = new \Zend\Session\Config\SessionConfig();
        $sessionConfigObj->setOptions($sessionConfigOptions);
        $sessionManager     = new \Zend\Session\SessionManager($sessionConfigObj);
        //Start session
        if (isset($config['session_config']['start_session']) && $config['session_config']['start_session']) {
            $sessionManager->start();
        }
        //Regenerate ID
        if (isset($config['session_config']['regenerate_id']) && $config['session_config']['regenerate_id']) {
            $sessionManager->regenerateId(false);
        }
        Container::setDefaultManager($sessionManager);
        return $sessionManager;
    }
}
