<?php
namespace Vivo\CMS\UI\Content\Editor;

use Vivo\CMS\Model\Content;
use Vivo\CMS\Model\ContentContainer;

interface EditorInterface
{
    /**
     * Save action.
     * @param \Vivo\CMS\Model\ContentContainer $container
     */
    public function save(ContentContainer $container);

}
