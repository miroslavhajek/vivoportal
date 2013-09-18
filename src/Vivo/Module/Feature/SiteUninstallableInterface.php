<?php
namespace Vivo\Module\Feature;

use Vivo\CMS\Model\Site;
use Vivo\CMS\Api\CMS;
use Vivo\Db\DbProviderInterface;

/**
 * SiteUninstallableInterface
 * Classes implementing this interface provide uninstallation script for module uninstallation from a site
 */
interface SiteUninstallableInterface
{
    /**
     * Runs uninstallation script
     * @param string $moduleName
     * @param string $siteName
     * @param Site $site
     * @param CMS $cms
     * @param DbProviderInterface $dbProvider
     * @return void
     */
    public function uninstallFromSite($moduleName, $siteName, Site $site, CMS $cms, DbProviderInterface $dbProvider);
}
