<?php
namespace Vivo\View\Helper;

use Vivo\CMS\Util\IconUrlHelper;
use Zend\View\Helper\AbstractHelper;
use Zend\Stdlib\ArrayUtils;

/**
 * IconUrl
 * getting url for icon from document, content or mime-type
 */
class IconUrl extends AbstractHelper
{

    /**
     * @var array
     */
    private $options = array();

    /**
     * @var IconUrlHelper
     */
    private $iconUrlHelper;

    public function __construct(IconUrlHelper $iconUrlHelper, array $options = array())
    {
        $this->iconUrlHelper    = $iconUrlHelper;
        $this->options          = array_merge($this->options, $options);
    }
    
    /**
     * Return URL for icon from Folder
     * 
     * @param \Vivo\CMS\Model\Folder $folder
     * @return string url
     */
    public function getByFolder(Folder $folder)
    {
        return $this->iconUrlHelper->getByFolder($folder);
    }
    
    /**
     * 
     * @param string contentType class
     * @return string url
     */
    public function getByContentType($contentType)
    {
        return $this->iconUrlHelper->getByContentType($contentType);
    }
    
    /**
     * Returns Contents icon
     * 
     * @param \Vivo\CMS\Model\Content $content
     * @return string url
     */
    public function getByContent(Content $content)
    {
        return $this->iconUrlHelper->getByContent($content);
    }
    
    /**
     * Returns url for Icon by Mime-Type
     * 
     * @param string $mimeType
     * @return string url
     */
    public function getByMimeType($mimeType)
    {
        return $this->iconUrlHelper->getByMimeType($mimeType);
    }
    
    public function __invoke($mixed = null)
    {
        return $this->iconUrlHelper->getIconUrl($mixed);
    }
    
}