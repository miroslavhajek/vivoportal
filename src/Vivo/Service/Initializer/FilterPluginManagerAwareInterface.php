<?php
namespace Vivo\Service\Initializer;

use Zend\ServiceManager\AbstractPluginManager;

/**
 * FilterPluginManagerAwareInterface
 * Classes implementing this interface accept Filter plugin manager
 */
interface FilterPluginManagerAwareInterface
{
    /**
     * Sets Filter plugin manager
     * @param AbstractPluginManager $filterPluginManager
     * @return void
     */
    public function setFilterPluginManager(AbstractPluginManager $filterPluginManager);
}
