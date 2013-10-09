<?php
namespace Vivo\Service;

use Vivo\Http\StreamResponse;

use Zend\Console\Response as ConsoleResponse;
use Zend\Console\Console;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ResponseFactory implements FactoryInterface
{
    /**
     * Create and return a response instance, according to current environment.
     * @param  ServiceLocatorInterface $serviceLocator
     * @throws Exception\ConfigException
     * @return \Zend\Stdlib\Message
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (Console::isConsole()) {
            $response   = new ConsoleResponse();
        } else {
            $response   = new StreamResponse();
        }
        return $response;
    }
}
