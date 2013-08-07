<?php
namespace Vivo\Backend\UI;

use Vivo\UI\Component;
use Vivo\CMS\Model\Site as SiteModel;

/**
 * Component for selecting site for editing.
 */
class SiteSelector extends Component
{

    /**
     * Current site
     * @var SiteModel
     */
    protected $site;

    /**
     * Manageable sites
     * @var SiteModel[]
     */
    protected $sites;

    /**
     * Constructor.
     * @param SiteModel $site
     * @param SiteModel[] $sites
     */
    public function __construct(SiteModel $site, array $sites)
    {
        $this->sites = $sites;
        $this->site = $site;
    }

    /**
     * (non-PHPdoc)
     * @see \Vivo\UI\Component::view()
     */
    public function view()
    {
        $this->view->selectedSite = $this->site;
        $this->view->availableSites = $this->sites;
        return parent::view();
    }

    /**
     * Returns site
     * @return \Vivo\CMS\Model\Site
     */
    public function getSite()
    {
        return $this->site;
    }
}
