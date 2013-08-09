<?php
namespace Vivo\CMS\Model\Content;

use Vivo\CMS\Model;

/**
 * Class Link is the same as the Linux symlink.
 * Returns document by saved URL and displays it on the current URL.
 * Link has no UI component. Link is handled in ComponentFactory.
 */
class Link extends Model\Content implements Model\SymRefDataExchangeInterface
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

    /**
     * Exchange internal values containing symbolic refs / URLs from provided array
     * @param  array $data
     * @return void
     */
    public function exchangeArraySymRef(array $data)
    {
        //RelPath
        if (array_key_exists('rel_path', $data)) {
            $this->setRelPath($data['rel_path']);
        }
    }

    /**
     * Return an array representation of the object's properties containing symbolic refs / URLs
     * @return array
     */
    public function getArrayCopySymRef()
    {
        return array('rel_path' => $this->getRelPath());
    }
}
