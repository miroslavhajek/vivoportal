<?php
namespace Vivo\Backend\UI\Explorer\Editor;

use Vivo\UI\AbstractFieldset;
use Vivo\Form\Form;
use Vivo\Backend\Form\Fieldset\EntityEditor;
use Vivo\Backend\Exception\ConfigException;
use Vivo\CMS\Model;
use Vivo\CMS\ComponentResolver;
use Vivo\CMS\Exception\InvalidArgumentException;
use Vivo\CMS\UI\Content\Editor\EditorInterface;
use Vivo\CMS\UI\Content\Editor\AdapterAwareInterface as EditorAdapterAwareInterface;
use Vivo\LookupData\LookupDataManager;
use Vivo\UI\ComponentEventInterface;

use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;

class Content extends AbstractFieldset
{
    /**
     * @var \Vivo\Backend\UI\Explorer\ExplorerInterface
     */
    private $explorer;

    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    private $sm;

    /**
     * @var \Vivo\CMS\Api\Document
     */
    private $documentApi;

    /**
     * @var \Vivo\Metadata\MetadataManager
     */
    private $metadataManager;

    /**
     * @var LookupDataManager
     */
    private $lookupDataManager;

    /**
     * @var \Vivo\CMS\Model\ContentContainer
     */
    private $contentContainer;

    /**
     * @var \Vivo\CMS\Model\Content
     */
    private $content;

    /**
     * @param \Zend\ServiceManager\ServiceManager $sm
     * @param \Vivo\CMS\Api\Document $documentApi
     * @param \Vivo\Metadata\MetadataManager $metadataManager
     * @param \Vivo\LookupData\LookupDataManager $lookupDataManager
     */
    public function __construct(
        \Zend\ServiceManager\ServiceManager $sm,
        \Vivo\CMS\Api\Document $documentApi,
        \Vivo\Metadata\MetadataManager $metadataManager,
        LookupDataManager $lookupDataManager)
    {
        $this->sm = $sm;
        $this->documentApi = $documentApi;
        $this->metadataManager = $metadataManager;
        $this->lookupDataManager = $lookupDataManager;
    }

    /**
     * @param \Vivo\CMS\Model\ContentContainer $contentContainer
     */
    public function setContentContainer(Model\ContentContainer $contentContainer)
    {
        $this->contentContainer = $contentContainer;
    }

    /**
     * @param \Vivo\CMS\Model\Content $content
     */
    public function setContent(Model\Content $content = null)
    {
        $this->content = $content;
    }

    public function attachListeners()
    {
        parent::attachListeners();
        $eventManager   = $this->getEventManager();
        $eventManager->attach(ComponentEventInterface::EVENT_INIT, array($this, 'initListenerContent'));
    }

    public function initListenerContent()
    {
        $this->explorer = $this->getParent('Vivo\Backend\UI\Explorer\ExplorerInterface');

        if($this->content) {
            $fieldset   = $this->getFieldset();
            $fieldset->setObject($this->content);
            $fieldset->extractAndPopulate();

            if(!$this->content->getUuid()) {
                $fieldset->get('state')->setValue('NEW');
            }

            try {
                $contentClass   = get_class($this->content);
                $resolver = new ComponentResolver($this->sm->get('cms_config'));
                $editorClass = $resolver->resolve($contentClass, ComponentResolver::EDITOR_COMPONENT);

                /* @var $editor \Vivo\CMS\UI\Content\Editor\EditorInterface */
                $editor = $this->sm->create($editorClass);
                if(!$editor instanceof EditorInterface) {
                    throw new ConfigException(sprintf("%s: Registered editor class '%s' is not instance of %s",
                        __METHOD__, $editorClass, 'Vivo\CMS\UI\Content\Editor\EditorInterface'));
                }
                $editor->setContent($this->content);
                //Set editor adapter
                if ($editor instanceof EditorAdapterAwareInterface) {
                    /** @var $editor EditorAdapterAwareInterface */
                    $cmsConfig          = $this->sm->get('cms_config');
                    $adaptersConfig     = $cmsConfig['contents']['adapters'];
                    if (!array_key_exists($contentClass, $adaptersConfig)) {
                        throw new ConfigException(
                            sprintf("%s: Key '%s' missing in cms_config['contents']['adapters']",
                                __METHOD__, $contentClass));
                    }
                    $contentAdaptersConfig  = $adaptersConfig[$contentClass];
                    $adapterKey             = $editor->getAdapterKey();
                    if (isset($contentAdaptersConfig['service_map'][$adapterKey])) {
                        $adapterServiceName = $contentAdaptersConfig['service_map'][$adapterKey];
                        $adapter            = $this->sm->create($adapterServiceName);
                    } elseif (array_key_exists('default', $contentAdaptersConfig)) {
                        $adapterServiceName = $contentAdaptersConfig['default'];
                        $adapter            = $this->sm->create($adapterServiceName);
                    } else {
                        //Adapter not found
                        throw new ConfigException(
                            sprintf("%s: Content adapter not found for content class '%s', adapterKey: '%s'",
                                __METHOD__, $contentClass, $adapterKey));
                    }
                    $adapter->setContent($this->content);
                    $editor->setAdapter($adapter);
                }
                $this->addComponent($editor, 'editorComponent');
                //Trigger already passed phases of init on the editor
                $editorEventManager = $editor->getEventManager();
                $editorEvent        = $editor->getEvent();
                $editorEventManager->trigger(ComponentEventInterface::EVENT_INIT_EARLY, $editorEvent);
                $editorEventManager->trigger(ComponentEventInterface::EVENT_INIT, $editorEvent);
            }
            catch(InvalidArgumentException $e)
            {
                // Could not determine editor_component class for model
            }
        }
    }

    protected function doGetFieldset()
    {
        if($this->content) {
            $id = $this->content->getUuid();
            $metadata = $this->metadataManager->getMetadata(get_class($this->content));
            $lookupData = $this->lookupDataManager->injectLookupData($metadata, $this->content);
        }
        else {
            $id = 0;
            $lookupData = array();
        }

        // Available workflow states
        $states = array();
        foreach ($this->documentApi->getWorkflowStates() as $state => $groups) {
            $states[$state] = ucfirst(strtolower($state));
        }

        // Fieldset
        $fieldset = new EntityEditor('content', $lookupData);
        $fieldset->setHydrator(new ClassMethodsHydrator(false));
        $fieldset->setOptions(array('use_as_base_fieldset' => true));
        $fieldset->add(array(
            'name' => 'state',
            'type' => 'Vivo\Form\Element\Radio',
            'attributes' => array(
                'options' => $states,
            ),
        ));
        return $fieldset;
    }

    /**
     * @return boolean
     */
    public function save()
    {
        $fieldsetData   = $this->getFieldsetData();
        if ($fieldsetData) {
            if(!$this->contentContainer->getUuid()) {
                $this->contentContainer = $this->documentApi->createContentContainer($this->explorer->getEntity());
            }
            $this->content->setState($fieldsetData['state']);
            $this->editorComponent->save($this->contentContainer);
            return true;
        }
        return false;
    }
}
