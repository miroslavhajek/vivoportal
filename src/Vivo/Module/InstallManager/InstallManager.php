<?php
namespace Vivo\Module\InstallManager;

use Vivo\Module\StorageManager\StorageManager as ModuleStorageManager;
use Vivo\CMS\Api\CMS;
use Vivo\CMS\Api\Site as SiteApi;
use Vivo\CMS\Model\Site;
use Vivo\IO\InputStreamWrapper;
use Vivo\Module\Feature\SiteInstallableInterface;
use Vivo\Module\Feature\SiteUninstallableInterface;
use Vivo\Db\DbProviderFactory;

/**
 * InstallManager
 * Performs module installation tasks
 */
class InstallManager implements InstallManagerInterface
{
    /**
     * Module storage manager
     * @var ModuleStorageManager
     */
    protected $moduleStorageManager;

    /**
     * CMS
     * @var CMS
     */
    protected $cms;


    /**
     * @var SiteApi
     */
    protected $siteApi;

    /**
     * DbProviderFactory
     * @var DbProviderFactory
     */
    protected $dbProviderFactory;

    /**
     * Configuration options
     * @var array
     */
    protected $options  = array(
        //'default_db_source'     => '',
    );

    /**
     * Constructor
     * @param ModuleStorageManager $moduleStorageManager
     * @param CMS $cms
     * @param SiteApi $siteApi
     * @param DbProviderFactory $dbProviderFactory
     * @param array $options
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(ModuleStorageManager $moduleStorageManager,
                                CMS $cms,
                                SiteApi $siteApi,
                                DbProviderFactory $dbProviderFactory,
                                array $options)
    {
        $this->moduleStorageManager = $moduleStorageManager;
        $this->cms                  = $cms;
        $this->siteApi              = $siteApi;
        $this->dbProviderFactory    = $dbProviderFactory;
        $this->options              = array_merge($this->options, $options);
        if (!isset($this->options['default_db_source'])) {
            throw new Exception\InvalidArgumentException(
                sprintf("%s: The 'default_db_source' option not set", __METHOD__));
        }
    }

    /**
     * Checks if a module is installed in a site | globally (a core module)
     * @param string $module Module name (ie module namespace)
     * @param Site|string|null $site If null, checks if the module is installed globally
     * @return bool
     */
    public function isInstalled($module, $site = null)
    {
        $modules        = $this->getInstalledModules($site);
        $isInstalled    = in_array($module, $modules);
        return $isInstalled;
    }

    /**
     * Checks if a module is enabled in a site | globally (a core module)
     * @param string $module Module name (ie module namespace)
     * @param Site|string|null $site If null, checks if the module is enabled globally
     * @return bool
     */
    public function isEnabled($module, $site = null)
    {
        $modules    = $this->getEnabledModules($site);
        $isEnabled  = in_array($module, $modules);
        return $isEnabled;
    }

    /**
     * Returns a list of modules installed for a site or globally (core modules)
     * @param Site|string|null $site Null = core modules
     * @return array
     */
    public function getInstalledModules($site = null)
    {
        $site   = $this->getSite($site);
        if (is_null($site)) {
            return $this->getInstalledModulesInCore();
        } else {
            return $this->getInstalledModulesInSite($site);
        }
    }

    /**
     * Returns a list of modules installed in a site
     * @param Site $site
     * @return array
     */
    protected function getInstalledModulesInSite(Site $site)
    {
        $config = $this->siteApi->getSiteConfig($site);
        if (isset($config['modules']['site_modules'])) {
            $modules    = array_keys($config['modules']['site_modules']);
        } else {
            $modules    = array();
        }
        return $modules;
    }

    /**
     * Returns a list of modules installed in core
     * @return array
     */
    protected function getInstalledModulesInCore()
    {
        //TODO - implement getInstalledModulesInCore()
        throw new \Exception(sprintf('%s not implemented', __METHOD__));
    }

    /**
     * Returns a list of modules enabled for a site or globally (core modules)
     * @param Site|string|null $site Null = core modules
     * @return array
     */
    public function getEnabledModules($site = null)
    {
        $site   = $this->getSite($site);
        if (is_null($site)) {
            return $this->getEnabledModulesInCore();
        } else {
            return $this->getEnabledModulesInSite($site);
        }
    }

    /**
     * Returns an array of modules (module names) which are enabled for the site
     * @param Site $site
     * @return array
     */
    protected function getEnabledModulesInSite(Site $site)
    {
        $config     = $this->siteApi->getSiteConfig($site);
        $modules    = array();
        if (isset($config['modules']['site_modules'])) {
            foreach ($config['modules']['site_modules'] as $moduleName => $moduleConfig) {
                if ($moduleConfig['enabled']) {
                    $modules[]  = $moduleName;
                }
            }
        }
        return $modules;
    }

    /**
     * Returns an array of modules (module names) which are enabled in core
     * @return array
     */
    protected function getEnabledModulesInCore()
    {
        //TODO - implement getEnabledModulesInCore()
        throw new \Exception(sprintf('%s not implemented', __METHOD__));
    }

    /**
     * Enables module for a site or globally (core modules)
     * @param string $module
     * @param Site|string|null $site
     */
    public function enable($module, $site = null)
    {
        $site   = $this->getSite($site);
        if (is_null($site)) {
            $this->setEnableModuleInCore($module, true);
        } else {
            $this->setEnableModuleInSite($module, $site, true);
        }
    }

    /**
     * Disables module for a site or globally (core modules)
     * @param string $module
     * @param Site|string|null $site
     */
    public function disable($module, $site = null)
    {
        $site   = $this->getSite($site);
        if (is_null($site)) {
            $this->setEnableModuleInCore($module, false);
        } else {
            $this->setEnableModuleInSite($module, $site, false);
        }
    }

    /**
     * Enables a module in a site
     * @param string $module
     * @param Site $site
     * @param boolean $enabled
     * @throws Exception\ModuleNotInstalledException
     * @return void
     */
    protected function setEnableModuleInSite($module, Site $site, $enabled)
    {
        $config = $this->siteApi->getSiteConfig($site);
        if (isset($config['modules']['site_modules'][$module])) {
            //OK, the module is installed
            $config['modules']['site_modules'][$module]['enabled']  = (bool)$enabled;
            $this->siteApi->setSiteConfig($config, $site);
        } else {
            //The module is not installed
            throw new Exception\ModuleNotInstalledException(
                sprintf("%s: Module '%s' not installed", __METHOD__, $module));
        }
    }

    /**
     * Enables a module in core
     * @param string $module
     * @param boolean $enabled
     */
    protected function setEnableModuleInCore($module, $enabled)
    {
        //TODO - implement enableModuleInCore()
        throw new \Exception(sprintf('%s not implemented', __METHOD__));
    }

    /**
     * Installs module into site or globally (core modules)
     * @param string $module Module name (ie module namespace)
     * @param string|null $siteName
     * @param array $config Module configuration
     * @throws Exception\InstallCoreModuleToSiteException
     * @throws Exception\ModuleAlreadyInstalledException
     * @throws Exception\NoSiteSpecifiedException
     * @throws Exception\ModuleNotFoundInStorageException
     * @throws Exception\SiteDoesNotExistException
     * @return void
     */
    public function install($module, $siteName = null, array $config = array())
    {
        $site   = null;
        //Verify the site exists and get the Site model object
        if (!is_null($siteName)) {
            if (!$this->siteApi->siteExists($siteName)) {
                throw new Exception\SiteDoesNotExistException(
                    sprintf("%s: Site '%' does not exist", __METHOD__, $siteName));
            }
            $site   = $this->getSite($siteName);
        }
        //Verify the module has not been installed yet
        if ($this->isInstalled($module, $site)) {
            throw new Exception\ModuleAlreadyInstalledException(
                sprintf("%s: Module '%s' is already installed", __METHOD__, $module));
        }
        //Verify the module has been added to the storage
        if (!$this->moduleStorageManager->moduleExists($module)) {
            throw new Exception\ModuleNotFoundInStorageException(
                sprintf("%s: Module '%s' not found in storage", __METHOD__, $module));
        }
        //Verify that site module is not being installed into core and vice versa
        $moduleInfo = $this->moduleStorageManager->getModuleInfo($module);
        $moduleType = $moduleInfo['descriptor']['type'];
        if (!is_null($site) && $moduleType  == 'core') {
            //Installing a core module to a site
            throw new Exception\InstallCoreModuleToSiteException(
                sprintf("%s: Cannot install the core module '%s' to a site", __METHOD__, $module));

        } elseif (is_null($site) && $moduleType == 'site') {
            //Installing site module without site specification
            throw new Exception\NoSiteSpecifiedException(
                sprintf("%s: Site specification missing when installing site module '%s'", __METHOD__, $module));
        }
        //Prepare module config which will be written to the site config
        //Use default db if not specified in config
        if (!isset($config['db_source'])) {
            $dbSource               = $this->options['default_db_source'];
            $config['db_source']    = $dbSource;
        } else {
            $dbSource               = $config['db_source'];
        }
        $config['enabled']  = false;
        //Do the actual installation
        $this->runInstallationScript($module, $siteName, $dbSource);
        if (is_null($site)) {
            $this->installModuleToCore($module, $config);
        } else {
            $this->installModuleToSite($module, $site, $config);
        }
    }

    /**
     * Installs the module to the site
     * @param string $module
     * @param Site $site
     * @param array $config
     */
    protected function installModuleToSite($module, Site $site, array $config = array())
    {
        $siteConfig         = $this->siteApi->getSiteConfig($site);
        $siteConfig['modules']['site_modules'][$module] = $config;
        $this->siteApi->setSiteConfig($siteConfig, $site);
    }

    /**
     * Installs a module to the core
     * @param string $module
     * @param array $config
     */
    protected function installModuleToCore($module,  array $config = array())
    {
        //TODO - implement installModuleToCore
        throw new \Exception(sprintf('%s not implemented', __METHOD__));
    }

    /**
     * Uninstalls a module from a site or from global scope (core modules)
     * @param string $module
     * @param string|null $siteName
     * @throws Exception\ModuleNotInstalledException
     * @throws Exception\ModuleEnabledException
     * @throws Exception\ModuleEnabledException
     */
    public function uninstall($module, $siteName = null)
    {
        $site   = $this->getSite($siteName);
        //Check that the module is installed
        if (!$this->isInstalled($module, $site)) {
            throw new Exception\ModuleNotInstalledException(
                sprintf("%s: Module '%s' not installed", __METHOD__, $module));
        }
        //Check that the module is disabled
        if ($this->isEnabled($module, $site)) {
            throw new Exception\ModuleEnabledException(
                sprintf("%s: Cannot uninstall enabled module (%s)", __METHOD__, $module));
        }
        if (is_null($site)) {
            $this->uninstallFromCore($module);
        } else {
            $this->uninstallFromSite($module, $site);
        }
        $this->runUninstallationScript($module, $siteName);
    }

    /**
     * Uninstalls module from a site
     * @param string $module
     * @param Site $site
     */
    protected function uninstallFromSite($module, Site $site)
    {
        $siteConfig         = $this->siteApi->getSiteConfig($site);
        unset($siteConfig['modules']['site_modules'][$module]);
        $this->siteApi->setSiteConfig($siteConfig, $site);
    }

    /**
     * Uninstalls module from core
     * @param string $module
     */
    protected function uninstallFromCore($module)
    {
        //TODO - implement uninstallFromCore()
        throw new \Exception(sprintf('%s not implemented', __METHOD__));
    }

    /**
     * Runs installation script
     * @param string $module
     * @param string $siteName
     * @param string $dbSource
     */
    protected function runInstallationScript($module, $siteName = null, $dbSource) {
        $site       = $this->getSite($siteName);
        $installer  = $this->getInstaller($module);
        if ($installer) {
            if (is_null($site)) {
                //Installation into core
                //TODO - implement running install script in core
                throw new \Exception(sprintf('%s: implement running install script in core', __METHOD__));
            } else {
                //Site installation
                /** @var $installer SiteInstallableInterface */
                if ($installer instanceof SiteInstallableInterface) {
                    $dbProvider = $this->dbProviderFactory->getDbProvider($dbSource);
                    $installer->installIntoSite($module, $siteName, $site, $this->cms, $dbProvider);
                }
            }
        }
    }

    /**
     * Runs uninstallation script
     * @param string $module
     * @param string|null $siteName
     */
    protected function runUninstallationScript($module, $siteName = null)
    {
        $site       = $this->getSite($siteName);
        $installer  = $this->getInstaller($module);
        if ($installer) {
            if (is_null($site)) {
                //Uninstallation from core
                //TODO - implement running uninstall script from core
                throw new \Exception(sprintf('%s: implement running uninstall script from core', __METHOD__));
            } else {
                //Site uninstallation
                /** @var $installer SiteUninstallableInterface */
                if ($installer instanceof SiteUninstallableInterface) {
                    $siteConfig = $this->siteApi->getSiteConfig($site);
                    if (!isset($siteConfig['modules']['site_modules'][$module]['db_source'])) {
                        throw new Exception\DbSourceMissingInConfigException(
                            sprintf("%s: Db source missing in config of site '%s' when uninstalling module '%s'",
                                __METHOD__, $siteName, $module));
                    }
                    $dbSource   = $siteConfig['modules']['site_modules'][$module]['db_source'];
                    $dbProvider = $this->dbProviderFactory->getDbProvider($dbSource);
                    $installer->uninstallFromSite($module, $siteName, $site, $this->cms, $dbProvider);
                }
            }
        }
    }

    /**
     * Returns a Site object (model) for the specified site name
     * @param Site|string|null $site
     * @throws Exception\InvalidArgumentException
     * @throws Exception\SiteDoesNotExistException
     * @return \Vivo\CMS\Model\Site|null
     */
    protected function getSite($site = null)
    {
        if (!is_null($site)) {
            if (is_string($site)) {
                if (!$this->siteApi->siteExists($site)) {
                    //The site does not exist
                    throw new Exception\SiteDoesNotExistException(
                        sprintf("%s: Site with name '%s' does not exist", __METHOD__, $site));
                }
                $site   = $this->siteApi->getSite($site);
            }
            if (!$site instanceof Site) {
                throw new Exception\InvalidArgumentException(
                    sprintf('%s: $site must be either a Site model or a site name (string)', __METHOD__));
            }
        }
        return $site;
    }

    /**
     * Instantiates and returns module Installer object
     * If Installer does not exist in the module, returns null
     * @param string $module
     * @return object|null
     */
    protected function getInstaller($module)
    {
        $installerStream    = $this->moduleStorageManager->getFileStream($module, 'Installer.php');
        $path               = 'module-installer.' . $module;
        $installerClass     = $module . '\Installer';
        $installerUrl       = InputStreamWrapper::registerInputStream($installerStream, $path);
        $installer          = null;
        if (file_exists($installerUrl)) {
            include_once $installerUrl;
            if (class_exists($installerClass)) {
                $installer  = new $installerClass();
            }
        }
        return $installer;
    }
}