<?php
namespace Vivo\CMS\Model;

/**
 * Represents folder in tree.
 */
class Folder extends Entity
{
    /**
     * @var string Folder name.
     */
    protected $title;

    /**
     * @var string Language.
     */
    protected $language;

    /**
     * @var string
     */
    protected $description;

    /**
     * Allows listing documents in navigation
     * @var bool
     */
    protected $allowListingInNavigation = true;

    /**
     * Allows listing documents in overview
     * @var bool
     */
    protected $allowListingInOverview = true;

    /**
     * Allows listing documents in sitemap
     * @var bool
     */
    protected $allowListingInSitemap = true;

    /**
     * @var int Position of the document in layer. This property could be used as sorting option of the document.
     */
    protected $position;

    /**
     * @var string Specifies which criteria will classify sub-documents in the lists (newsletters, sitemap, menu, etc.)
     */
    protected $sorting = 'title:asc';

    /**
     * @var string Replication id;
     */
    protected $replicationGroupId;

    /**
     * Absolute last path to entity stored in repository before move to trash.
     * @var string
     */
    protected $lastPath;

    /**
     * Sets folder title.
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Returns title.
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function setLanguage($language)
    {
        $this->language = $language;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Returns bool value determinates if document can be included to overview.
     * @return bool
     */
    public function getAllowListingInOverview()
    {
        return $this->allowListingInOverview;
    }

    /**
     * Returns bool value determinates if document can be included to navigation.
     * @return bool
     */
    public function getAllowListingInNavigation()
    {
        return $this->allowListingInNavigation;
    }

    /**
     * Returns bool value determinates if document can be included to sitemap.
     * @return bool
     */
    public function getAllowListingInSitemap()
    {
        return $this->allowListingInSitemap;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function getSorting()
    {
        return $this->sorting;
    }

    public function setSorting($sorting)
    {
        $this->sorting = $sorting;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * Sets bool property determining if document can be listed in overview
     * @param bool $allowListingInOverview
     */
    public function setAllowListingInOverview($allowListingInOverview = true)
    {
        $this->allowListingInOverview = (bool)$allowListingInOverview;
    }

    /**
     * Sets bool property determining if document can be listed in navigation
     * @param bool $allowListingInNavigation
     */
    public function setAllowListingInNavigation($allowListingInNavigation = true)
    {
        $this->allowListingInNavigation = (bool)$allowListingInNavigation;
    }

    /**
     * Sets bool property determining if document can be listed in sitemap
     * @param bool $allowListingInSitemap
     */
    public function setAllowListingInSitemap($allowListingInSitemap = true)
    {
        $this->allowListingInSitemap = (bool)$allowListingInSitemap;
    }
}
