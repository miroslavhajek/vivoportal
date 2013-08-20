<?php
namespace Vivo\CMS\Model\Content;

use Vivo\CMS\Model;

/**
 * Model Layout represents page container. Layout carries information about the appearance of the page. Defines the layout of the components and their interdependence.
 */
class Layout extends Model\Content
{
    /**
     * @var array of paths of documents for layout panels
     */
    private $panels = array();

    /**
     * Returns array of paths of documents.
     * @return array
     */
    public function getPanels()
    {
        return $this->panels;
    }

    /**
     * @param array $panels
     */
    public function setPanels(array $panels)
    {
        $this->panels = $panels;
    }
}
