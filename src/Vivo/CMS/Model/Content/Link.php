<?php
namespace Vivo\CMS\Model\Content;

use Vivo\CMS\Model;

/**
 * Class Link is the same as the Linux symlink.
 * Returns document by saved URL and displays it on the current URL.
 * Link has no UI component. Link is handled in ComponentFactory.
 */
class Link extends Model\Content
{
    /**
     * @var string Document relPath
     */
    public $relPath;

    /**
     * Returns linked document relative path.
     * @return string
     */
    public function getRelPath()
    {
        return $this->relPath;
    }

    /**
     * Sets relative path of linked document.
     * @param type $relPath
     */
    public function setRelPath($relPath)
    {
        $this->relPath = $relPath;
    }
}
