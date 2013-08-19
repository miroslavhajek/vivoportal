<?php
namespace Vivo\Backend\UI;

use Vivo\UI\AbstractForm;
use Vivo\Backend\Form\Cache as ModuleFormCache;
use Vivo\CMS\Api\Cache as CacheApi;

use Vivo\UI\ComponentEventInterface;
use Zend\Form\Form as ZfForm;

class Cache extends AbstractForm
{
    /**
     * Cache API
     * @var CacheApi
     */
    protected $cacheApi;

    /**
     * Constructor
     * @param CacheApi $cacheApi
     */
    public function __construct(CacheApi $cacheApi)
    {
        $this->cacheApi = $cacheApi;
    }

    public function attachListeners()
    {
        parent::attachListeners();
        $eventManager   = $this->getEventManager();
        $eventManager->attach(ComponentEventInterface::EVENT_VIEW, array($this, 'viewListenerSetCacheInfo'));
    }

    /**
     * View Listener - sets cache info into view model
     */
    public function viewListenerSetCacheInfo()
    {
        $viewModel  = $this->getView();
        $viewModel->cacheSpaceInfo  = $this->cacheApi->getCacheSpaceInfo();
    }

    /**
     * Creates ZF form and returns it
     * Factory method
     * @return ZfForm
     */
    protected function doGetForm()
    {
        $form   = new ModuleFormCache();
        $form->add(array(
            'name'          => 'act',
            'type'          => 'Vivo\Form\Element\Hidden',
            'attributes'    => array(
                'value'         =>  $this->getPath('submit'),
            ),
        ));
        return $form;
    }

    /**
     * Submit action
     */
    public function submit()
    {
        if ($this->isValid()) {
            $data   = $this->getData();
            if (!is_null($data['flush_all_subject_caches'])) {
                //Flush all subject caches
                $result = $this->cacheApi->flushAllSubjectCaches();
                $this->getView()->flushAllSubjectCachesResult   = $result;
            }
        }
    }
}
