<?php
namespace Vivo\View\Helper;

use Vivo\CMS\Api\CMS;
use Vivo\Util\MIMEInterface;
use Zend\Form\View\Helper\AbstractHelper;

class ResourceInfo extends AbstractHelper
{
    /**
     * @var \Vivo\CMS\Api\CMS
     */
    private $cmsApi;

    /**
     * @var \Vivo\Util\MIMEInterface
     */
    private $mime;

    /**
     * Constructor.
     *
     * @param \Vivo\CMS\Api\CMS $cmsApi
     * @param \Vivo\Util\MIMEInterface $mime
     */
    public function __construct(CMS $cmsApi, MIMEInterface $mime)
    {
        $this->cmsApi = $cmsApi;
        $this->mime = $mime;
    }

    public function __invoke($resourcePath = null, $source = null)
    {
        if($resourcePath == null) {
            return $this;
        }

        $return = array();
        $return['mime'] = $this->mime($resourcePath, $source);
        $return['size'] = $this->size($resourcePath, $source);

        return $return;
    }

    /**
     * Returns mime
     * @param string $name
     * @param string $entity
     * @return string
     */
    public function mime($resourcePath, $entity)
    {
        return $this->mime->detectByExtension(pathinfo($resourcePath, PATHINFO_EXTENSION));
    }

    /**
     * Returns resource size in bytes
     * @param string $name
     * @param string $entity
     * @return int
     */
    public function size($resourcePath, $entity)
    {
        return $this->cmsApi->getResourceSize($entity, $resourcePath);
    }
}
