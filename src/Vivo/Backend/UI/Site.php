<?php
namespace Vivo\Backend\UI;

use Vivo\UI\AbstractForm;
use Vivo\UI\Alert;
use Vivo\Form;
use Vivo\Backend\UI\Form\Fieldset\EntityEditor as EntityEditorFieldset;
use Vivo\CMS\Model\Site as SiteModel;
use Vivo\CMS\Api\Document as DocumentApi;
use Vivo\Util\RedirectEvent;
use Vivo\Util\UrlHelper;
use Vivo\LookupData\LookupDataManager;
use Vivo\Service\Initializer\TranslatorAwareInterface;
use Vivo\CMS\Event\CMSEvent;

use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;
use Zend\I18n\Translator\Translator;

class Site extends AbstractForm implements TranslatorAwareInterface
{
    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    private $sm;

    /**
     * CMS event
     * @var CMSEvent
     */
    protected $cmsEvent;

    /**
     * @var \Vivo\Metadata\MetadataManager
     */
    private $metadataManager;

    /**
     * @var LookupDataManager
     */
    protected $lookupDataManager;

    /**
     * @var \Vivo\CMS\AvailableContentsProvider
     */
    private $availableContentsProvider;

    /**
     * @var \Vivo\UI\Alert
     */
    private $alert;

    /**
     * Translator
     * @var Translator
     */
    protected $translator;

    /**
     * Url helper
     * @var UrlHelper
     */
    protected $urlHelper;

    /**
     * TTL for CSRF token
     * Redefine in descendant if necessary
     * @var int|null
     */
    protected $csrfTimeout          = 3600;

    /**
     * Site entity
     * @var SiteModel
     */
    protected $siteEntity;

    /**
     * Document Api
     * @var DocumentApi
     */
    protected $documentApi;

    /**
     * Constructor
     * @param \Zend\ServiceManager\ServiceManager $sm
     * @param \Vivo\Metadata\MetadataManager $metadataManager
     * @param \Vivo\LookupData\LookupDataManager $lookupDataManager
     * @param \Vivo\CMS\Api\DocumentInterface $documentApi
     */
    public function __construct(
        \Zend\ServiceManager\ServiceManager $sm,
        \Vivo\Metadata\MetadataManager $metadataManager,
        LookupDataManager $lookupDataManager,
        CMSEvent $cmsEvent,
        UrlHelper $urlHelper,
        DocumentApi $documentApi)
    {
        $this->sm = $sm;
        $this->metadataManager = $metadataManager;
        $this->lookupDataManager = $lookupDataManager;
        $this->cmsEvent = $cmsEvent;
        $this->urlHelper = $urlHelper;
        $this->documentApi = $documentApi;
    }

    public function init()
    {
        $this->siteEntity = $this->cmsEvent->getSite();

        $this->getForm()->bind($this->siteEntity);
        parent::init();
        //$this->initForm();
    }

//    protected function initForm()
//    {
//        if($this->siteEntity instanceof Site) {
//            try {
//                $containers = $this->documentApi->getContentContainers($this->siteEntity);
//            }
//            catch(\Vivo\Repository\Exception\PathNotSetException $e) {
//            }
//        }
//        foreach ($containers as $index => $contentContainer) {
//            $contentTab = $this->createContentTab($contentContainer);
//            $this->contentTab->addComponent($contentTab, "content_$index");
//            //Init the component
//            $contentTab->init();
//        }
//        if (count($this->availableContents) > 0) {
//            $contentTab = $this->createContentTab(new ContentContainer());
//            $count = count($containers);
//            $this->contentTab->addComponent($contentTab, "content_$count");
//            //Init the component
//            $contentTab->init();
//        }
//    }

    /**
     * @param Alert $alert
     */
    public function setAlert(Alert $alert)
    {
        $this->alert = $alert;
    }

    /**
     * Injects translator
     * @param \Zend\I18n\Translator\Translator $translator
     */
    public function setTranslator(Translator $translator)
    {
        $this->translator = $translator;
    }

    protected function doGetForm()
    {
        $metadata = $this->metadataManager->getMetadata(get_class($this->siteEntity));
        $lookupData = $this->lookupDataManager->injectLookupData($metadata, $this->siteEntity);
        $action = $this->request->getUri()->getPath();

        // Entity fieldset
        $editorFieldset = new EntityEditorFieldset('entity', $lookupData);
        $editorFieldset->setHydrator(new ClassMethodsHydrator(false));
        $editorFieldset->setOptions(array('use_as_base_fieldset' => true));

        // Buttons
        $buttonsFieldset = new Form\Fieldset('buttons');
        $buttonsFieldset->add(array(
            'name' => 'save',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Save',
                'class' => 'btn',
            ),
        ));

        $form = new Form\Form('entity-' . $this->siteEntity->getUuid());
        $form->setAttribute('action', $action);
        $form->setAttribute('method', 'post');
        $form->add(array(
            'name' => 'act',
            'attributes' => array(
                'type' => 'hidden',
                'value' => $this->getPath('save'),
            ),
        ));

        $form->add($editorFieldset);
        $form->add($buttonsFieldset);

        return $form;
    }

    /**
     * @param string $message
     * @param string $type
     */
    protected function addAlertMessage($message, $type)
    {
        if($this->alert) {
            $this->alert->addMessage($message, $type);
        }
    }

    /**
     * Save action.
     * @throws \Exception
     */
    public function save()
    {
        $form = $this->getForm();

        if ($form->isValid()) {
            //Save contents first, otherwise published contents are not updated in indexer
            //$successContents    = $this->saveContents();
            $this->documentApi->saveDocument($this->siteEntity);
            $this->events->trigger(new RedirectEvent());
            $this->addAlertMessage('Saved...', Alert::TYPE_SUCCESS);
        }
        else {
            $message = $this->translator->translate("Document data is not valid");
            $this->alert->addMessage($message, Alert::TYPE_ERROR);
        }
    }

    /**
     * @return boolean
     */
    private function saveContents()
    {
        $success = $this->saveProcess();

        if($success) {
            $this->addAlertMessage('Saved...', Alert::TYPE_SUCCESS);
        }
        else {
            $this->addAlertMessage('Error...', Alert::TYPE_ERROR);
        }

        return $success;
    }

    /**
     * @return boolean
     */
    protected function saveProcess()
    {
        $success = true;
        /** @var $tabContainer TabContainer */
        $tabContainer   = $this->getComponent('contentTab');
        if ($tabContainer->hasComponent($tabContainer->getSelected())) {
            /* @var $component \Vivo\Backend\UI\Explorer\Editor\ContentTab */
            $component  = $tabContainer->getSelectedComponent();
            $success    = $success && $component->save();
        }
        if($success) {
            $tabContainer->removeAllComponents();
            $this->initForm();
            foreach ($tabContainer->getComponents() as $component) {
                $component->initForm();
            }
        }
        return $success;
    }
}
