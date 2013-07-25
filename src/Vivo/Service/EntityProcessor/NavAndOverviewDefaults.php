<?php
namespace Vivo\Service\EntityProcessor;

use Vivo\CMS\Api\CMS as CmsApi;
use Vivo\CMS\Model\Entity;
use Vivo\Service\EntityProcessorInterface;
use Vivo\CMS\Model\Folder;
use Vivo\CMS\Model\Content\Navigation;
use Vivo\CMS\Model\Content\Overview;

/**
 * NavAndOverviewDefaults
 * Sets navigation and overview defaults and stores the entity
 */
class NavAndOverviewDefaults implements EntityProcessorInterface
{
    /**
     * CMS Api
     * @var CmsApi
     */
    protected $cmsApi;

    /**
     * Default sorting
     * @var string
     */
    protected $defaultSorting   = 'title:asc';

    /**
     * Constructor
     * @param CmsApi $cmsApi
     */
    public function __construct(CmsApi $cmsApi)
    {
        $this->cmsApi   = $cmsApi;
    }

    /**
     * Processes the entity
     * Returns true on successful processing, false on errors or null when the entity has not been processed
     * @param Entity $entity
     * @return bool|null
     */
    public function processEntity(Entity $entity)
    {
        $success    = null;
        $store      = false;
        //Folder
        if ($entity instanceof Folder) {
            $entity->setAllowListing(true);
            $entity->setAllowListingInNavigation(true);
            $entity->setAllowListingInSitemap(true);
            $entity->setSorting($this->defaultSorting);
            $store  = true;
        }
        //Navigation
        if ($entity instanceof Navigation) {
            $entity->setNavigationSorting($this->defaultSorting);
            $store  = true;
        }
        //Overview
        if ($entity instanceof Overview) {
            $entity->setOverviewSorting($this->defaultSorting);
            $store  = true;
        }
        //Save
        if ($store) {
            $this->cmsApi->saveEntity($entity);
            $success    = true;
        }
        return $success;
    }
}
