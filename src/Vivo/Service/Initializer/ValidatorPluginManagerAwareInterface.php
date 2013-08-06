<?php
namespace Vivo\Service\Initializer;

use Zend\ServiceManager\AbstractPluginManager;

/**
 * ValidatorPluginManagerAwareInterface
 * Classes implementing this interface accept Validator plugin manager
 */
interface ValidatorPluginManagerAwareInterface
{
    /**
     * Sets Validator plugin manager
     * @param AbstractPluginManager $validatorPluginManager
     * @return void
     */
    public function setValidatorPluginManager(AbstractPluginManager $validatorPluginManager);
}
