<?php
namespace Vivo\CMS\UI\Content\Editor;

use Vivo\UI\AbstractFieldset;
use \Vivo\CMS\Model\Content;
use Vivo\CMS\UI\Content\Editor\AdapterInterface;

/**
 * Abstract class for Resource Editor Adapters
 */
abstract class AbstractAdapter extends AbstractFieldset implements AdapterInterface
{

    /**
     * Content
     * @var Content
     */
    protected $content;

    /**
     * Sets Content
     * @param Content $content
     */
    public function setContent(Content $content)
    {
        $this->content = $content;
    }
}
