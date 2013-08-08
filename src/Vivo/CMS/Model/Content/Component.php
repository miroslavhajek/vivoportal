<?php
namespace Vivo\CMS\Model\Content;

use Vivo\CMS\Model;

/**
 * User defined ui component
 */
class Component extends Model\Content implements ProvideFrontComponentInterface
{

    /**
     * @var string Front component FQCN
     */
    protected $frontComponent  = 'Vivo\CMS\UI\Blank';

    /**
     * Returns front component FQCN.
     * @return string
     */
    public function getFrontComponent()
    {
        return $this->frontComponent;
    }

    /**
     * @param string $frontComponent
     */
    public function setFrontComponent($frontComponent) {
        $this->frontComponent = $frontComponent;
    }
}
