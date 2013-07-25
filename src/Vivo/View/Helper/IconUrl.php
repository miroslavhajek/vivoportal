<?php
namespace Vivo\View\Helper;

use Vivo\Security\Principal\UserInterface;
use Vivo\CMS\Model\Folder;
use Vivo\CMS\Model\Document;
use Vivo\CMS\Api\Document as DocumentApi;
use Vivo\Metadata\MetadataManager;
use Vivo\CMS\Model\Content;
use Vivo\CMS\Model\Content\File;
use Vivo\Util\MIME;

use Zend\View\Helper\AbstractHelper;
use Zend\Stdlib\ArrayUtils;

/**
 * IconUrl
 * getting url for icon from document, content or mime-type
 */
class IconUrl extends AbstractHelper
{
    
    protected $options = array(
        'icon_path'    => 'backend/img/icons/16x16/',
        'ext'          => '.png',
        'default_icon' => 'Document',
    );
    
    /**
     *
     * @var MetadataManager
     */
    protected $metadataManager;
    
    /**
     *
     * @var DocumentApi
     */
    protected $documentApi;
    
    /**
     *
     * @var MIME
     */
    protected $mime;
    
    /**
     * 
     * @param \Vivo\Metadata\MetadataManager $metadataManager
     */
    public function __construct(MetadataManager $metadataManager,
                                DocumentApi $documentApi,
                                MIME $mime,
                                array $options = array())
    {
        $this->options         = array_merge($this->options, $options);
        $this->metadataManager = $metadataManager;
        $this->documentApi     = $documentApi;
        $this->mime            = $mime;
    }
    
    /**
     * Return URL for icon from Folder
     * 
     * @param \Vivo\CMS\Model\Folder $folder
     * @return string url
     */
    public function getByFolder(Folder $folder)
    {
        if ($folder instanceof Document) {
            $contents = $this->documentApi->getPublishedContents($folder);
            
            if ($contents) {
                $content = reset($contents);
                
                if ($content instanceof File) {
                    return $this->getByMimeType($content->getMimeType());
                }
                
                return $this->getByContentType(get_class($content));
            } else {
                return $this->getIconUrl($this->options['default_icon']);
            }
        } elseif ($folder instanceof Folder) {
            return $this->getByContentType(get_class($folder));
        } else {
            return $this->getIconUrl($this->options['default_icon']);
        }
    }
    
    /**
     * 
     * @param string contentType class
     * @return string url
     */
    public function getByContentType($contentType) {
        $md = $this->metadataManager->getMetadata($contentType);
        
        if (isset($md['icon']['file'])) {
            return $this->getIconUrl($md['icon']['file']);
        } else {
            return $this->getIconUrl($this->options['default_icon']);
        }
    }
    
    /**
     * Returns Contents icon
     * 
     * @param \Vivo\CMS\Model\Content $content
     * @return string url
     */
    public function getByContent(Content $content)
    {
        if ($content instanceof File) {
            return $this->getByMimeType($content->getMimeType());
        } else {
            return $this->getByContentType(get_class($content));
        }
    }
    
    /**
     * Returns url for Icon by Mime-Type
     * 
     * @param string $mimeType
     * @return string url
     */
    public function getByMimeType($mimeType)
    {
        return $this->getIconUrl($this->mime->getIconBaseName($mimeType));
    }
    
    /**
     * 
     * @param string $icon
     * @return string url
     */
    private function getIconUrl($icon) {
        $resourceHelper = $this->view->plugin('resource');
        return $resourceHelper->__invoke($this->options['icon_path'].$icon.$this->options['ext'], 'Vivo');
    }
    
    public function __invoke($mixed = null)
    {
        if (is_null($mixed)) {
            return $this;
        } elseif ($mixed instanceof Folder) {
            return $this->getByFolder($mixed);
        } elseif ($mixed instanceof Content) {
            return $this->getByContent($mixed);
        } elseif (is_string($mixed)) {
            return $this->getByMimeType($mixed);
        } else {
            return $this;
        }
    }
    
}