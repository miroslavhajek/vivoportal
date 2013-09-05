<?php
namespace Vivo\Backend;

use Vivo\Backend\ModuleResolver;
use Vivo\CMS\Api;
use Vivo\CMS\Security\Manager\AbstractManager;
use Vivo\IO\InputStreamInterface;
use Vivo\SiteManager\Event\SiteEvent;
use Vivo\UI\Component;
use Vivo\UI\ComponentTreeController;
use Vivo\Util\RedirectEvent;
use Vivo\Util\Redirector;
use Vivo\Util\UrlHelper;
use Vivo\UI\ComponentEventInterface;

use Zend\EventManager\EventInterface as Event;
use Zend\Mvc\InjectApplicationEventInterface;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\Stdlib\DispatchableInterface;
use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\RequestInterface as Request;
use Zend\Stdlib\ResponseInterface as Response;
use Zend\View\Model\ModelInterface;

/**
 * The front controller which is responsible for dispatching all requests for documents and files in CMS repository.
 */
class BackendController implements DispatchableInterface,
    InjectApplicationEventInterface, ServiceManagerAwareInterface
{

    /**
     * @var MvcEvent
     */
    protected $mvcEvent;

    /**
     * @var SiteEvent
     */
    protected $siteEvent;

    /**
     * @var ComponentTreeController
     */
    protected $tree;

    /**
     *
     * @var Redirector
     */
    protected $redirector;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * Security manager.
     * @var AbstractManager
     */
    protected $securityManager;

    /**
     *
     * @var ModuleResolver
     */
    protected $moduleResolver;

    /**
     * @var UrlHelper
     */
    protected $urlHelper;

    /**
     * @var Api\Site
     */
    protected $siteApi;

    /**
     * Constructor.
     * @param AbstractManager $securityManager
     */
    public function __construct(AbstractManager $securityManager)
    {
        $this->securityManager = $securityManager;
    }

    /**
     * @param SiteEvent $site
     */
    public function setSiteEvent(SiteEvent $siteEvent)
    {
        $this->siteEvent = $siteEvent;
    }

    /**
     * Dispatches backend request
     * @param Request $request
     * @param Response $response
     * @todo should we render UI in controller dispatch action?
     */
    public function dispatch(Request $request, Response $response = null)
    {

        $sm = $this->sm;

        //redirect bad backend urls
        $host = $this->mvcEvent->getRouteMatch()->getParam('host');
        if ($this->securityManager->getUserPrincipal()) {
            if ($this->mvcEvent->getRouteMatch()->getMatchedRouteName() == 'backend/other'
                || $this->mvcEvent->getRouteMatch()->getMatchedRouteName() == 'backend/default'
                || !$this->mvcEvent->getRouteMatch()->getParam('module')
                || !$host) {
                $url = $this->urlHelper->fromRoute('backend/modules', array('host' => $host?:$this->getDefaultHost()));
                $this->redirector->redirect(new RedirectEvent($url));
                return $response;
            }
        }

        //Create UI component tree for backend.
        $root = $sm->get('Vivo\CMS\UI\Root');
        $page = $sm->get('Vivo\UI\Page');
        if (!$this->securityManager->getUserPrincipal()) {
            $page->setMain($this->sm->get('Vivo\Backend\UI\Logon'));
        } else {
            $backend = $this->sm->get('Vivo\Backend\UI\Backend');
            $moduleName = $this->mvcEvent->getRouteMatch()->getParam('module');
            $backend->setModuleComponent($this->moduleResolver->createComponent($moduleName));
            $page->setMain($backend);
        }
        $root->setMain($page);

        // Perform tree operations
        $this->tree->setRoot($root);
        $this->tree->loadState();

        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->tree->init(); //replace by lazy init
            //if request is  ajax call, we use result of method
            $result = $this->handleAction();
        } else {
            $this->tree->init();
            $result = $this->handleAction();
            if($result != null) {
                $type = is_object($result) ? get_class($result) : gettype($result);
                throw new Exception\RuntimeException(sprintf("%s: Action returns not null result; returns '%s'",
                        __METHOD__, $type));
            }
            if (!$this->redirector->isRedirect()) {
                $result = $this->tree->view();
            }
        }
        $this->tree->saveState();
        $this->tree->done();

        // Return response
        if($this->redirector->isRedirect()){
            return $response;
        }
        if ($result instanceof ModelInterface) {
            $this->mvcEvent->setViewModel($result);
        } elseif ($result instanceof InputStreamInterface) {
            //skip rendering phase
            $response->setInputStream($result);
            return $response;
        } elseif (is_string($result)) {
            //skip rendering phase
            $response->setContent($result);
            return $response;
        }
    }

    /**
     * Returns hostname of site.
     * @return string
     */
    public function getDefaultHost()
    {
        $sites = $this->siteApi->getManageableSites();
        $hosts = reset($sites)->getHosts();
        return reset($hosts);
    }

    /**
     * Handles action on component.
     */
    protected function handleAction()
    {
        $request = $this->getRequest();
        if (!$action = $request->getQuery('act')) {
            if (!$action = $request->getPost('act')) {
                return;
            } else {
                $params = $request->getPost('args', array());
            }
        } else {
            $params = $request->getQuery('args', array());
        }

        $parts = explode(Component::COMPONENT_SEPARATOR, $action);
        $action = array_pop($parts);
        $path = implode(Component::COMPONENT_SEPARATOR, $parts);
        return $this->tree->invokeAction($path, $action, $params);
    }

    /**
     * @param Event $event
     */
    public function setEvent(Event $event)
    {
        $this->mvcEvent = $event;
    }

    /**
     * @return MvcEvent
     */
    public function getEvent()
    {
        return $this->mvcEvent;
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

    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    public function setSM(ServiceManager $sm)
    {
        //TODO use ServiceManagerAwareInitializer instead this method
        $this->sm = $sm;
    }

    /**
     * Sets redirector.
     * @param Redirector $redirector
     */
    public function setRedirector(Redirector $redirector)
    {
        $this->redirector = $redirector;
    }

    /**
     * Inject ModuleResolver
     * @param ModuleResolver $moduleResolver
     */
    public function setModuleResolver(ModuleResolver $moduleResolver)
    {
        $this->moduleResolver = $moduleResolver;
    }

    /**
     * Inject urlHelper.
     * @param UrlHelper $urlHelper
     */
    public function setUrlHelper(UrlHelper $urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }

    /**
     *
     * @param Api\Site $siteApi
     */
    public function setSiteApi(Api\Site $siteApi)
    {
        $this->siteApi = $siteApi;
    }
}
