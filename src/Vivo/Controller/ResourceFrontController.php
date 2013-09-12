<?php
namespace Vivo\Controller;

use Vivo\CMS\Api;
use Vivo\CMS\Model\Content\File;
use Vivo\Http\HeaderHelper;
use Vivo\IO\Exception\ExceptionInterface as IOException;
use Vivo\IO\FileInputStream;
use Vivo\Module\Exception\ResourceNotFoundException as ModuleResourceNotFoundException;
use Vivo\Module\ResourceManager\ResourceManager;
use Vivo\SiteManager\Event\SiteEvent;

use VpLogger\Log\Logger;

use Zend\EventManager\EventInterface as Event;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\InjectApplicationEventInterface;
use Zend\Stdlib\DispatchableInterface;
use Zend\Stdlib\RequestInterface as Request;
use Zend\Stdlib\ResponseInterface as Response;
use Zend\EventManager\EventManager;

/**
 * Controller for giving all resource files
 */
class ResourceFrontController implements DispatchableInterface,
        InjectApplicationEventInterface
{

    /**
     * @var Api\CMS
     */
    protected $cmsApi;

    /**
     * @var Event
     */
    protected $event;

    /**
     * @var \Vivo\Util\MIMEInterface
     */
    protected $mime;

    /**
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * @var SiteEvent
     */
    protected $siteEvent;

    /**
     * Event Manager
     * @var EventManager
     */
    private $eventManager;

    public function dispatch(Request $request, Response $response = null)
    {
        $pathToResource = $this->event->getRouteMatch()->getParam('path');
        $source = $this->event->getRouteMatch()->getParam('source');
        try {
            if ($source === 'Vivo') {
                //it's vivo core resource
                $resourceStream = new FileInputStream(__DIR__ . '/../../../resource/' . $pathToResource);
                $filename       = pathinfo($pathToResource, PATHINFO_BASENAME);
            } elseif ($source === 'entity') {
                //it's entity resource
                $entityPath = $this->event->getRouteMatch()->getParam('entity');
                $entity = $this->cmsApi->getSiteEntity($entityPath, $this->siteEvent->getSite());

                if ($entity instanceof File) {
                    //TODO match interface instead of the concrete class File
                    $filename = $entity->getFilename();
                } else {
                    $filename = pathinfo($pathToResource, PATHINFO_BASENAME);
                }

                $resourceStream = $this->cmsApi->readResource($entity, $pathToResource);
            } else {
                //it's module resource
                $resourceStream = $this->resourceManager->readResource($source, $pathToResource);
                $filename       = pathinfo($pathToResource, PATHINFO_BASENAME);
            }
            $ext        = pathinfo($filename, PATHINFO_EXTENSION);
            //Set headers
            $headers    = $response->getHeaders();
            $mimeType   = $this->mime->detectByExtension($ext);
            if (is_null($mimeType)) {
                //Mime type not found
                $this->getEventManager()->trigger('log', $this, array(
                    'priority'  => Logger::WARN,
                    'message'   => sprintf("Mime type for extension '%s' not found", $ext),
                ));
            }
            $headers->addHeaderLine('Content-Type: ' . $mimeType);
            $headers->addHeaderLine('Content-Disposition: inline; filename="' . $filename . '"');
            $this->headerHelper->setExpirationByMimeType($headers, $mimeType);

            $response->setInputStream($resourceStream);

            //Log matched resource path
            $eventManager   = new \Zend\EventManager\EventManager();
            $eventManager->trigger('log', $this,
                array ('message'    => sprintf("Path to resource: '%s'", $pathToResource),
                    'priority'   => Logger::DEBUG));

        } catch (\Exception $e) {
            if ($e instanceof IOException ||
                    $e instanceof ModuleResourceNotFoundException ||
                    $e instanceof CMSResourceNotFoundException) {
                $response->setStatusCode(HttpResponse::STATUS_CODE_404);
            } else {
                throw $e;
            }
        }
        return $response;
    }

    /**
     * @param Api\CMS $cms
     */
    public function setCMS(Api\CMS $cmsApi) {
        $this->cmsApi = $cmsApi;
    }

    /* (non-PHPdoc)
     * @see Zend\Mvc.InjectApplicationEventInterface::setEvent()
     */
    public function setEvent(Event $event)
    {
        $this->event = $event;
    }

    /* (non-PHPdoc)
     * @see Zend\Mvc.InjectApplicationEventInterface::getEvent()
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param ResourceManager $resourceManager
     */
    public function setResourceManager(ResourceManager $resourceManager)
    {
        $this->resourceManager = $resourceManager;
    }

    /**
     * @param SiteEvent $sitEevent
     */
    public function setSiteEvent(SiteEvent $siteEvent)
    {
        $this->siteEvent = $siteEvent;
    }

    /**
     * @param HeaderHelper $headerHelper
     */
    public function setHeaderHelper(HeaderHelper $headerHelper)
    {
        $this->headerHelper = $headerHelper;
    }

    /**
     * Inject MIME.
     * @param \Vivo\Util\MIMEInterface $mime
     */
    public function setMime(\Vivo\Util\MIMEInterface $mime)
    {
        $this->mime = $mime;
    }

    /**
     * Returns Event Manager
     * @return \Zend\EventManager\EventManager
     */
    public function getEventManager()
    {
        if (is_null($this->eventManager)) {
            $this->eventManager = new EventManager();
        }
        return $this->eventManager;
    }
}
