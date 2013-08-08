<?php
namespace Vivo\CMS\Model\Content;

use Vivo\CMS\Model;

/**
 * Model Layout represents page container. Layout carries information about the appearance of the page. Defines the layout of the components and their interdependence.
 */
class Layout extends Model\Content implements Model\SymRefDataExchangeInterface
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

    /**
     * Exchange internal values containing symbolic refs / URLs from provided array
     * @param  array $data
     * @return void
     */
    public function exchangeArraySymRef(array $data)
    {
        //Panels
        if (array_key_exists('panels', $data)) {
            $this->setPanels($data['panels']);
        }
    }

    /**
     * Return an array representation of the object's properties containing symbolic refs / URLs
     * @return array
     */
    public function getArrayCopySymRef()
    {
        return array('panels' => $this->getPanels());
    }
}
