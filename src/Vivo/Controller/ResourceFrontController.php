<?php
namespace Vivo\Controller;

use Vivo\CMS\Api;
use Vivo\CMS\Model\Content\File;
use Vivo\Http\HeaderHelper;
use Vivo\IO\Exception\ExceptionInterface as IOException;
use Vivo\IO\FileInputStream;
use Vivo\IO\ByteArrayInputStream;
use Vivo\Module\Exception\ResourceNotFoundException as ModuleResourceNotFoundException;
use Vivo\Module\ResourceManager\ResourceManager;
use Vivo\SiteManager\Event\SiteEvent;
use Vivo\Cache\CacheManager;

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

    /**
     * Cache manager
     * @var CacheManager
     */
    protected $cacheManager;

    //TODO - get allowed caches from config?
    /**
     * Array of symbolic cache names which have allowed access
     * @var array
     */
    protected $allowedCaches    = array(
        'head_script',
    );

    /**
     * Dispatches the request
     * @param Request $request
     * @param Response $response
     * @return mixed|Response
     * @throws \Exception
     */
    public function dispatch(Request $request, Response $response = null)
    {
        /** @var $routeMatch \Zend\Mvc\Router\Http\RouteMatch */
        $routeMatch     = $this->event->getRouteMatch();
        $pathToResource = $routeMatch->getParam('path');
        $source         = $routeMatch->getParam('source');
        try {
            if ($source === 'Vivo') {
                //It's vivo core resource
                $type   = $routeMatch->getParam('type');
                switch ($type) {
                    //Cache
                    case 'cache':
                        $parts  = explode('/', $pathToResource);
                        if (count($parts) != 2) {
                            throw new Exception\RuntimeException(
                                sprintf("%s: Invalid cache item specification", __METHOD__));
                        }
                        $cacheName  = $parts[0];
                        $cacheKey   = $parts[1];
                        if (!($this->cacheManager->has($cacheName) && $this->cacheAccessAllowed($cacheName))) {
                            throw new Exception\RuntimeException(
                                sprintf("%s: Access to cache '%s' denied or cache does not exist",
                                    __METHOD__, $cacheName));
                        }
                        /** @var $cache \Zend\Cache\Storage\StorageInterface */
                        $cache      = $this->cacheManager->get($cacheName);
                        if (!$cache->hasItem($cacheKey)) {
                            throw new Exception\RuntimeException(
                                sprintf("%s: Item with key '%s' not found in cache '%s'",
                                    __METHOD__, $cacheKey, $cacheName));
                        }
                        $success    = null;
                        $cacheItem  = $cache->getItem($cacheKey, $success);
                        if (!$success) {
                            throw new Exception\RuntimeException(
                                sprintf("%s: Reading item '%s' from cache '%s' failed",
                                    __METHOD__, $cacheKey, $cacheName));
                        }
                        $resourceStream = new ByteArrayInputStream($cacheItem);
                        $filename       = $cacheKey;
                        break;
                    //Normal file resource
                    case 'resource':
                    default:
                        $resourceStream = new FileInputStream(__DIR__ . '/../../../resource/' . $pathToResource);
                        $filename       = pathinfo($pathToResource, PATHINFO_BASENAME);
                        break;
                }
            } elseif ($source === 'entity') {
                //it's entity resource
                $entityPath = $routeMatch->getParam('entity');
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

    /**
     * Sets cache manager
     * @param \Vivo\Cache\CacheManager $cacheManager
     */
    public function setCacheManager(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * Returns if access for resources to the specified cache is allowed
     * @param string $symbolicCacheName
     * @return bool
     */
    protected function cacheAccessAllowed($symbolicCacheName)
    {
        if (in_array($symbolicCacheName, $this->allowedCaches)) {
            return true;
        }
        return false;
    }
}
