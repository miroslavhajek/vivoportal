<?php
namespace Vivo\CMS;

use Vivo\CMS\Api;
use Vivo\CMS\Api\CMS;
use Vivo\CMS\Event\CMSEvent;
use Vivo\Controller\Exception;
use Vivo\IO\InputStreamInterface;
use Vivo\SiteManager\Event\SiteEvent;
use Vivo\UI\Component;
use Vivo\UI\ComponentTreeController;
use Vivo\Util\RedirectEvent;
use Vivo\Util\Redirector;
use Vivo\Util\UrlHelper;
use Vivo\UI\ComponentEventInterface;
use VpLogger\Log\Logger;

use Zend\EventManager\EventInterface as Event;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\InjectApplicationEventInterface;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\DispatchableInterface;
use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\RequestInterface as Request;
use Zend\Stdlib\ResponseInterface as Response;
use Zend\Stdlib\ArrayUtils;
use Zend\View\Model\ModelInterface;
use Zend\Stdlib\ParametersInterface;

/**
 * The front controller which is responsible for dispatching all requests for documents in CMS repository.
 */
class FrontController implements DispatchableInterface,
                                 InjectApplicationEventInterface,
                                 EventManagerAwareInterface,
                                 ServiceLocatorAwareInterface
{
    /**
     * @var array
     */
    protected $options = array(
        'listeners' => array(),
    );

    /**
     * @var MvcEvent
     */
    protected $mvcEvent;

    /**
     * @var CMSEvent
     */
    protected $cmsEvent;

    /**
     * @var SiteEvent
     */
    protected $siteEvent;

    /**
     * @var EventManagerInterface
     */
    protected $events;

    /**
     * @var CMS
     */
    protected $cmsApi;

    /**
     * @var Api\Document
     */
    protected $documentApi;

    /**
     * @var ComponentTreeController
     */
    protected $tree;

    /**
     * @var Redirector
     */
    protected $redirector;

    /**
     * @var \Zend\Http\PhpEnvironment\Request
     */
    protected $request;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @var UrlHelper
     */
    protected $urlHelper;

    /**
     * @param array $options Configuration.
     */
    public function __construct(array $options)
    {
        $this->options = ArrayUtils::merge($this->options, $options);
    }

    /**
     * Attaches default listeners.
     */
    private function attachListeners()
    {
        //fetch listeners
        foreach ($this->options['listeners'] as $service => $priority) {
            $this->events->attachAggregate($this->serviceManager->get($service), $priority);
        }

        //redirect
        $this->events->attach(CMSEvent::EVENT_REDIRECT, array($this, 'performRedirects'), 200);

        //render
        $this->events->attach(CMSEvent::EVENT_RENDER, array($this, 'render'), 100);
    }

    /**
     * Dispatches CMS request.
     * @param Request $request
     * @param Response $response
     */
    public function dispatch(Request $request, Response $response = null)
    {
        //Performance log
        $this->events->trigger('log', $this,
            array ('message'    => 'FrontController - Dispatch start',
                   'priority'   => Logger::PERF_BASE));

        $this->request = $request;
        $this->response = $response;
        $this->attachListeners();

        if (!$this->siteEvent->getSite()) {
            throw new Exception\SiteNotFoundException(
                    sprintf("%s: Site not found for hostname '%s'",
                            __METHOD__ , $this->siteEvent->getHost()));
        }

        //dispatch resource files
        $resourceResponse = $this->dispatchResource($this->cmsEvent);
        if ($resourceResponse instanceof Response) {
            return $resourceResponse;
        }

        //dispatch document
        $redirector = $this->redirector;

        try {
            //fetch document
            $eventResult = $this->events->trigger(CMSEvent::EVENT_FETCH_DOCUMENT, $this->getCmsEvent(),
                    function ($result) {
                        //stop event propagation when document is fetched
                        return ($result instanceof Model\Document);
                    });
            $document = $eventResult->last();

            $this->cmsEvent->setDocument($document);

            //Performance log
            if($document instanceof Model\Document) {
                $this->events->trigger('log', $this,
                    array ('message'    => sprintf("FrontController - Document fetched (%s)", $document->getPath()),
                           'priority'   => Logger::PERF_BASE));
            }

            //perform redirects
            $this->events->trigger(CMSEvent::EVENT_REDIRECT, $this->getCmsEvent(),
                    function () use ($redirector) {
                        //stop event propagation when redirect
                        return $redirector->isRedirect();
                    });
            if ($redirector->isRedirect()) {
                return $response;
            }

            //throw exception when document is not fetched
            if (!$document) {
                throw new \Exception(sprintf('%s: Document for requested path `%s` can not be fetched',
                            __METHOD__,
                            $this->cmsEvent->getRequestedPath()),
                        \Zend\Http\Response::STATUS_CODE_404);
            }
            //throw exception when document hasn't any published content
            if (!$this->documentApi->isPublished($document)) {
                throw new \Exception(sprintf('%s: Document `%s` is not published',
                            __METHOD__,
                            $document->getPath()),
                        \Zend\Http\Response::STATUS_CODE_404);
            }

            //create ui component tree
            $eventResult = $this->events->trigger(CMSEvent::EVENT_CREATE, $this->cmsEvent,
                    function ($result) {
                        //stop event propagation when UI is fetched
                        return ($result instanceof Component);
                    });
            $this->cmsEvent->setRoot($eventResult->last());

            //Performance log
            $this->events->trigger('log', $this,
                array ('message'    => 'FrontController - UI component tree created',
                       'priority'   => Logger::PERF_BASE));

            //perform tree operations
            $result = $this->dispatchTree($this->cmsEvent);

            //Performance log
            $this->events->trigger('log', $this,
                array ('message'    => 'FrontController - UI Component tree dispatched',
                       'priority'   => Logger::PERF_BASE));


            if ($redirector->isRedirect()) {
                return $response;
            }

            if ($result instanceof ModelInterface) {
                //render view model
                $this->events->trigger(CMSEvent::EVENT_RENDER, $this->cmsEvent);

                //Performance log
                $this->events->trigger('log', $this,
                    array ('message'    => 'FrontController - View model rendered',
                           'priority'   => Logger::PERF_BASE));

            } elseif ($result instanceof InputStreamInterface) {
                //skip rendering phase
                $response->setInputStream($result);
            } elseif (is_string($result)) {
                //skip rendering phase
                $response->setContent($result);
            }

        } catch (\Exception $e) {
            $this->cmsEvent->setException($e);

            //trigger error event
            $this->events->trigger(CMSEvent::EVENT_ERROR, $this->getCmsEvent());

            //fetch error document
            $eventResult = $this->events->trigger(CMSEvent::EVENT_FETCH_ERRORDOCUMENT, $this->getCmsEvent(),
                    function ($result) {
                        return ($result instanceof Model\Document);
                    });
            $this->cmsEvent->setDocument($eventResult->last());

            //throw exception when error document not found
            if (!$this->cmsEvent->getDocument()) {
                throw new \Exception(sprintf('%s: Error document can not be fetched for site `%s`',
                            __METHOD__,
                            $this->siteEvent->getSite()->getName()),
                        \Zend\Http\Response::STATUS_CODE_500, $e);
            }

            //create ui component tree
            $eventResult = $this->events->trigger(CMSEvent::EVENT_CREATE, $this->cmsEvent,
                    function ($result) {
                        //stop event propagation when UI is fetched
                        return ($result instanceof Component);
                    });
            $this->cmsEvent->setRoot($eventResult->last());

            //perform tree operations
            $result = $this->dispatchTree($this->cmsEvent);
            if ($redirector->isRedirect()) {
                return $response;
            }

            if ($result instanceof ModelInterface) {
                //render view model
                $this->events->trigger(CMSEvent::EVENT_RENDER, $this->cmsEvent);
            } elseif ($result instanceof InputStreamInterface) {
                //skip rendering phase
                $response->setInputStream($result);
            } elseif (is_string($result)) {
                //skip rendering phase
                $response->setContent($result);
            }

            if ($e->getCode()) {
                $response->setStatusCode($e->getCode());
            } else {
                $response->setStatusCode(\Zend\Http\Response::STATUS_CODE_500);
            }
        }

        //Performance log
        $this->events->trigger('log', $this,
            array ('message'    => 'FrontController - Dispatch end',
                   'priority'   => Logger::PERF_BASE));

        return $response;
    }

    /**
     * Dispatches resource file.
     *
     * Searches a requested path in site resource.map file and dispatches resource if finds matching row.
     * Dispatching of the resource is forwarded to the ResourceFrontController.
     * Format of line in resource.map file is:
     * <requestedPath> <source> <resourcePath>
     *
     * @param \Vivo\CMS\Event\CMSEvent $cmsEvent
     * @return null|Response
     */
    public function dispatchResource(CMSEvent $cmsEvent)
    {
        try {
            $resourceMap = $this->cmsApi->getResource($cmsEvent->getSite(), 'resource.map');
        } catch (\Vivo\Storage\Exception\IOException $e){
            return null;
        }
        $lines  = explode("\n", $resourceMap);
        $foundTarget = null;

        //parse resource map file
        foreach ($lines as $line) {
            if (($line = trim($line))) { //skip empty rows
                $lineColums = array_values(array_filter(explode(' ', $line)));
                if (count($lineColums) == 3) {
                    list($path, $source, $resource) = $lineColums;
                    if ($cmsEvent->getRequestedPath() == $path) {
                        $foundTarget = true;
                        break;
                    }
                }
            }
        }

        if ($foundTarget) {
            //use ResourceFrontController for dispatching resource
            /* @var $controller \Vivo\Controller\ResourceFrontController */
            $controller = $this->serviceManager->get('ControllerLoader')->get('resource_front_controller');
            $this->mvcEvent->getRouteMatch()->setParam('path'  , $resource);
            $this->mvcEvent->getRouteMatch()->setParam('source', $source);
            $controller->setEvent($this->mvcEvent);
            return $controller->dispatch($this->mvcEvent->getRequest(), $this->mvcEvent->getResponse());
        }
        return null;
    }

    /**
     * Dispatch operation on UI component tree.
     * @param \Vivo\CMS\Event\CMSEvent $cmsEvent
     * @return ModelInterface | InputStreaInterface | string
     * @todo Do not handle action on error
     * @todo split method by actions(load, init, save, done)
     */
    protected function dispatchTree(CMSEvent $cmsEvent)
    {
        $result = null;
        $handleAction = !(bool) $cmsEvent->getException();

        $this->tree->setRoot($cmsEvent->getRoot());
        $this->tree->loadState();
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->tree->init(); //replace by lazy init
            //if request is  ajax call, we use result of method
            if ($handleAction) {
                $result = $this->handleAction();
            }
        } else {
            $this->tree->init(); //replace by lazy init
            if ($handleAction) {
                $result = $this->handleAction();
                if($result != null) {
                    $type = is_object($result) ? get_class($result) : gettype($result);
                    throw new Exception\RuntimeException(sprintf("%s: Action returns not null result; returns '%s'",
                            __METHOD__, $type));
                }
            }
            if (!$this->redirector->isRedirect()) {
                $result = $this->tree->view();
            }
        }

        $this->tree->saveState();
        $this->tree->done();
        $cmsEvent->setResult($result);
        return $result;
    }

    /**
     * Render view model.
     * @param \Vivo\CMS\Event\CMSEvent $cmsEvent
     */
    public function render(CMSEvent $cmsEvent)
    {
        if ($cmsEvent->getResult() instanceof ModelInterface) {
            $view = $this->serviceManager->get('view');
            $view->setResponse($this->response);
            $view->render($cmsEvent->getResult());
        }
    }

    /**
     * Performs redirects based on document properties.
     * @param Model\Document $document
     * @return null
     */
    public function performRedirects(CMSEvent $cmsEvent)
    {

        if (!$document = $cmsEvent->getDocument()) {
            return;
        }
        //redirect secured documents to https
        if ($document->getSecured() && $this->request->getUri()->getScheme() !== 'https') {
            $uri = clone $this->request->getUri();
            $uri->setScheme('https');
            $uri->setPort(null);
            $this->events->trigger(new RedirectEvent((string) $uri));
            return;
        }

        //redirect document with specific url
        if ($document->getUri()) {
            if ($document->getUriPrecedence() == true) {
                if($document->getUri() != $this->cmsEvent->getRequestedPath()) {
                    $url = $this->urlHelper->fromRoute(null, array('path' => $document->getUri()));
                    $this->events->trigger(new RedirectEvent($url));
                    return;
                } else {
                    //Do not redirect if the document url==requestedurl, doesn't matter whether ends by slash or not
                    return;
                }
            } else {
                if ($document->getUri() == $this->cmsEvent->getRequestedPath()) {
                    $path = $this->cmsApi->getEntityRelPath($document);
                    $url = $this->urlHelper->fromRoute(null, array('path' => $path));
                    $this->events->trigger(new RedirectEvent($url));
                    return;
                }
            }
        }
        //Redirect when path does not end with a slash.
        /** @var $routeMatch \Zend\Mvc\Router\Http\RouteMatch */
        $routeMatch = $this->getEvent()->getRouteMatch();
        if ($routeMatch->getMatchedRouteName() == 'vivo/cms') {
            $path   = $cmsEvent->getRequestedPath();
            $lastChar   = substr($path, -1);
            if ($lastChar != '/') {
                $routeParams    = $routeMatch->getParams();
                $routeParams['path']    = $path . '/';
                $routeOptions   = array(
                    'query'     => $this->request->getQuery()->toArray(),
                );
                $url            = $this->urlHelper->fromRoute('vivo/cms', $routeParams, $routeOptions);
                $params         = array('status_code' => 301, 'immediately' => false);
                $this->events->trigger(new RedirectEvent($url, $params));
            }
        }

        //redirect to backend if query param 'edit' is present
        $query = $this->getRequest()->getQuery();
        if (isset($query['edit'])) {
                $url  = $this->urlHelper->fromRoute('backend/modules',
                        array('module'=>'explorer', 'path'=>''),
                        array('query' => array('url' => $path)) );
                $params         = array('status_code' => 301, 'immediately' => true);
                $this->events->trigger(new RedirectEvent($url, $params));
        }
    }

    /**
     * Handles action on component.
     * @return mixed|null Result of component action method or null when no action is required
     */
    protected function handleAction()
    {
        //TODO is there a better way how to obtain params?
        //TODO create router for assembling and matching path of action
        $request = $this->getRequest();
        //Try getting action from query
        $paramsContainer    = $request->getQuery();
        $action = $this->getActionFromParams($paramsContainer);
        if ($action) {
            //Action found in query string
            $actionParams   = $paramsContainer->get('args', array());
            return $this->doHandleAction($action, $actionParams);
        }
        //Try getting action from POST
        $paramsContainer    = $request->getPost();
        $action = $this->getActionFromParams($paramsContainer);
        if ($action) {
            //Action found in POST data
            $actionParams   = $paramsContainer->get('args', array());
            return $this->doHandleAction($action, $actionParams);
        }
        //No action required
        return null;
    }

    /**
     * The actual handling of a component action
     * @param string $actionPath
     * @param array $actionParams
     * @return mixed
     */
    protected function doHandleAction($actionPath, array $actionParams)
    {
        $parts  = explode(Component::COMPONENT_SEPARATOR, $actionPath);
        $action = array_pop($parts);
        $path   = implode(Component::COMPONENT_SEPARATOR, $parts);
        return $this->tree->invokeAction($path, $action, $actionParams);

    }

    /**
     * Returns 'act' parameter found in params or null when not present
     * @param ParametersInterface $params
     * @return string|null
     */
    protected function getActionFromParams(ParametersInterface $params)
    {
        //Variant 1: 'act' parameter is in the root of the data (Wrap elements == false)
        $action = $params->get('act');
        if (is_string($action)) {
            return $action;
        }
        //Variant 2: there is only one item in params data and 'act' parameter is under it (Wrap elements == true)
        if ($params->count() == 1) {
            $paramsArray    = $params->toArray();
            $first          = reset($paramsArray);
            if (isset($first['act']) && is_string($first['act'])) {
                $action = $first['act'];
                return $action;
            }
        }
        //'act' parameter not found
        return null;
    }

    /**
     * Returns CMS event
     * @return CMSEvent
     */
    public function getCmsEvent()
    {
        return $this->cmsEvent;
    }

    /**
     * @param Event $event
     */
    public function setEvent(Event $mvcEvent)
    {
        $this->mvcEvent = $mvcEvent;
    }

    /**
     * @return MvcEvent
     */
    public function getEvent()
    {
        return $this->mvcEvent;
    }

    /**
     * @param CMS $cms
     */
    public function setCMS(CMS $cmsApi)
    {
        $this->cmsApi = $cmsApi;
    }

    /**
     * @param SiteEvent $site
     */
    public function setSiteEvent(SiteEvent $siteEvent)
    {
        $this->siteEvent = $siteEvent;
    }

    /**
     * Sets ComponentTreeController
     * @param ComponentTreeController $tree
     */
    public function setComponentTreeController(ComponentTreeController $tree)
    {
        $this->tree = $tree;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest() {
        return $this->mvcEvent->getRequest();
    }

    /**
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
       return $this->events;
    }

    /**
     * Sets event manager
     * @param EventManagerInterface $eventManager
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $this->events = $eventManager;
        $this->events->addIdentifiers(__CLASS__);
    }

    /**
     * Returns redirector.
     * @return Redirector
     */
    public function getRedirector()
    {
        return $this->redirector;
    }

    /**
     * Inject redirector.
     * @param Redirector $redirector
     */
    public function setRedirector(Redirector $redirector)
    {
        $this->redirector = $redirector;
    }

    /**
     *
     * @return ServiceManager
     */
    public function getServiceLocator()
    {
        return $this->serviceManager;
    }

    /**
     * Inject Service Manager.
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
        if ($serviceLocator instanceof ServiceManager) {
            $this->serviceManager = $serviceLocator;
        }
    }

    /**
     * Inject UrlHelper.
     * @param UrlHelper $urlHelper
     */
    public function setUrlHelper (UrlHelper $urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }

    /**
     * Sets the CMS event
     * @param CMSEvent $cmsEvent
     * @return void
     */
    public function setCmsEvent(CMSEvent $cmsEvent)
    {
        $this->cmsEvent = $cmsEvent;
    }

    /**
     * Inject Document Api
     * @param \Vivo\CMS\Api\Document $documentApi
     */
    public function setDocumentApi(Api\Document $documentApi)
    {
        $this->documentApi = $documentApi;
    }
}
