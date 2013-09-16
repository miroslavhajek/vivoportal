<?php
namespace Vivo\Module\Feature;

use Vivo\CMS\Model\Site;
use Vivo\CMS\Api\CMS;
use Vivo\Db\DbProviderInterface;

/**
 * SiteInstallableInterface
 * Classes implementing this interface provide installation script for module installation into a site
 */
interface SiteInstallableInterface
{
    /**
     * Runs installation script
     * @param string $moduleName
     * @param string $siteName
     * @param Site $site
     * @param CMS $cms
     * @param DbProviderInterface $dbProvider
     * @return void
     */
    public function installIntoSite($moduleName, $siteName, Site $site, CMS $cms, DbProviderInterface $dbProvider);
}