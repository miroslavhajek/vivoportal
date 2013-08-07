<?php
namespace Vivo\Backend\UI\Explorer;

use Vivo\Backend\UI\Explorer\Exception\Exception;
use Vivo\CMS\Api\CMS;
use Vivo\CMS\Model;
use Vivo\Backend\UI\SiteSelector;
use Vivo\Service\Initializer\RequestAwareInterface;
use Vivo\UI\ComponentContainer;
use Vivo\UI\PersistableInterface;
use Vivo\UI\ComponentEventInterface;
use Vivo\Util\UrlHelper;
use Vivo\Util\RedirectEvent;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\Event;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\RequestInterface;

/**
 * Explorer component.
 */
class Explorer extends ComponentContainer implements RequestAwareInterface, PersistableInterface, ExplorerInterface
{
    /**
     * Entity being explored.
     * @var \Vivo\CMS\Model\Entity
     */
    protected $entity;

    /**
     * Current component name.
     * @var string
     */
    protected $explorerAction = 'browser';

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var SiteSelector
     */
    protected $siteSelector;

    /**
     * @var ExplorerComponentFactory
     */
    protected $explorerComponentFactory;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * Array of classes of explorer components.
     * @var array
     */
    protected $explorerComponents = array(
        'creator'   => 'Vivo\Backend\UI\Explorer\Creator',
        'editor'    => 'Vivo\Backend\UI\Explorer\Editor',
        'viewer'    => 'Vivo\Backend\UI\Explorer\Viewer',
        'browser'   => 'Vivo\Backend\UI\Explorer\Browser',
        'inspect'   => 'Vivo\Backend\UI\Explorer\Inspect',
        'delete'    => 'Vivo\Backend\UI\Explorer\Delete',
        'copy'      => 'Vivo\Backend\UI\Explorer\Copy',
        'move'      => 'Vivo\Backend\UI\Explorer\Move',
    );

    protected  $tree;

    /**
     *
     * @var \Vivo\Util\UrlHelper
     */
    protected $urlHelper;

    /**
     * UUID of current document
     * @var string
     */
    protected $uuid;


    /**
     * Constructor.
     * @param CMS $cmsApi
     * @param SiteSelector $siteSelector
     * @param \Zend\ServiceManager\ServiceManager $serviceManager
     * @param \Vivo\Util\UrlHelper $urlHelper
     * @param string $uuid
     * @param $explorerAction
     * @internal param \Vivo\Backend\UI\Explorer\ExplorerComponentFactory $explorerComponentFactory
     */
    public function __construct(CMS $cmsApi,
                                \Vivo\Backend\UI\SiteSelector $siteSelector,
                                ServiceManager $serviceManager,
                                UrlHelper $urlHelper,
                                $uuid, $explorerAction)
    {
        $this->cmsApi           = $cmsApi;
        $this->siteSelector     = $siteSelector;
        $this->serviceManager   = $serviceManager;
        $this->urlHelper        = $urlHelper;
        $this->uuid             = $uuid;
        $this->explorerAction   = $explorerAction;
    }

    /**
     * (non-PHPdoc)
     * @see \Vivo\UI\PersistableInterface::loadState()
     */
    public function loadState($state)
    {
        $this->loadEntity();
        $this->updateRibbon();
    }

    /**
     * Create explorer component.
     * @param boolean $needInit
     * @throws Exception
     */
    protected function createComponent($needInit = false)
    {
        if (!isset($this->explorerComponents[$this->explorerAction])) {
            throw new Exception(
                    sprintf("%s: Component for '%s' is not defined",
                            __METHOD__, $this->explorerAction));
        }
        $name = $this->explorerComponents[$this->explorerAction];
        /** @var $component \Vivo\UI\ComponentInterface */
        $component = $this->serviceManager->create($name);
        $this->addComponent($component, $this->explorerAction);
        if($needInit) {
            $componentEventManager  = $component->getEventManager();
            $componentEvent         = $component->getEvent();
            $componentEventManager->trigger(ComponentEventInterface::EVENT_INIT_EARLY, $componentEvent);
            $componentEventManager->trigger(ComponentEventInterface::EVENT_INIT, $componentEvent);
            $componentEventManager->trigger(ComponentEventInterface::EVENT_INIT_LATE, $componentEvent);
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Vivo\UI\PersistableInterface::saveState()
     */
    public function saveState()
    {
    }

    /**
     * Sets component tree controller.
     * @param \Vivo\UI\ComponentTreeController $tree
     */
    public function setComponentTreeController(\Vivo\UI\ComponentTreeController $tree)
    {
        $this->tree  = $tree;
    }

    /**
     * @param string $site
     */
    private function redirectToHomepage($site)
    {
        $entity = $this->cmsApi->getSiteEntity('/', $site);
        $routeParams = array(
            'path' => $entity->getUuid(),
            'explorerAction' => $this->explorerAction,
        );
        $url = $this->urlHelper->fromRoute('backend/explorer', $routeParams);
        $this->getEventManager()->trigger(new RedirectEvent($url));
    }

    /**
     * Loads entity from url
     */
    protected function loadEntity()
    {
        $site = $this->getSite();
        if ($site) {
            $entity = NULL;
            // 'url' param can come from frontend via /?edit
            $relPath = $this->request->getQuery('url', false);
            if ($relPath) {
                // redirect to backend/explorer route
                // try to load entity from repository
                try {
                    $entity = $this->cmsApi->getSiteEntity($relPath, $site);

                    $routeParams = array(
                        'path' => $entity->getUuid(),
                        'explorerAction' => 'editor',
                    );
                    $url = $this->urlHelper->fromRoute('backend/explorer', $routeParams);
                    $this->getEventManager()->trigger(new RedirectEvent($url));
                } catch (\Exception $ex) {
                    // provided $relPath is not valid
                    // redirect to homepage (/) editor
                    $this->explorerAction = 'editor';
                    $this->redirectToHomepage($site);
                }
            } else {
                // try to load entity from repository
                // if no exception id thrown, uuid is valid
                try {
                    /* @var $entity Model\Entity */
                    $entity = $this->cmsApi->getEntity($this->uuid);
                    // ensure the entity is in given site
                    // TODO do not get entity twice from repository
                    $this->cmsApi->getSiteEntity($entity->getPath(), $site);
                } catch (\Exception $ex) {
                    // provided UUID is not valid
                    // redirect to homepage (/)
                    $this->redirectToHomepage($site);
                }
            }
            // load entity and create explorer component
            $this->entity = $entity;
            $this->createComponent();
        }
    }

    /**
     * Returns entity beeing explored.
     * @return \Vivo\CMS\Model\Entity
     */
    public function getEntity()
    {
        return $this->entity;
    }
    
    /**
     * Sets entity
     * @param \Vivo\CMS\Model\Entity
     */
    public function setEntity(\Vivo\CMS\Model\Entity $entity)
    {
        $this->entity = $entity;
    }

    /**
     * (non-PHPdoc)
     * @see \Vivo\UI\ComponentContainer::view()
     */
    public function view()
    {
        $this->view->explorerAction = $this->explorerAction;
        return parent::view();
    }

    /**
     * Returns site being explored.
     * @return \Vivo\CMS\Model\Site
     */
    public function getSite()
    {
        return $this->siteSelector->getSite();
    }

    /**
     * @param RequestInterface $request
     */
    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Returns current name
     * @return string
     */
    public function getExplorerAction() {
        return $this->explorerAction;
    }

    /**
     * Updates ribbon - sets the currently active item
     */
    protected function updateRibbon()
    {
        /** @var $ribbon \Vivo\Backend\UI\Explorer\Ribbon */
        $ribbon = $this->getComponent('ribbon');
        $ribbon->deactivateAll();
        if ($this->explorerAction) {
            $ribbon->setActive($this->explorerAction);
        }
    }
}
