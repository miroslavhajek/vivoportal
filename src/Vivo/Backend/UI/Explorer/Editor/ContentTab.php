<?php
namespace Vivo\Backend\UI\Explorer\Editor;

use Vivo\UI\AbstractForm;
use Vivo\UI\TabContainerItemInterface;
use Vivo\Form\Form;
use Vivo\CMS\Api;
use Vivo\CMS\Model;
use Vivo\UI\ComponentEventInterface;
use Vivo\CMS\Model\Content;

class ContentTab extends AbstractForm implements TabContainerItemInterface
{
    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    private $sm;

    /**
     * @var \Vivo\CMS\Model\ContentContainer
     */
    private $contentContainer;

    /**
     * @var array
     */
    private $availableContents = array();

    /**
     * @var array
     */
    private $contents = array();

    /**
     * @var \Vivo\CMS\Api\Document
     */
    private $documentApi;

    /**
     * @param \Zend\ServiceManager\ServiceManager $sm
     * @param \Vivo\CMS\Api\Document $documentApi
     */
    public function __construct(\Zend\ServiceManager\ServiceManager $sm, Api\Document $documentApi)
    {
        $this->sm = $sm;
        $this->documentApi = $documentApi;
        $this->autoAddCsrf = false;
    }

    public function init()
    {
        $this->loadContents();
    }

    /**
     * @param \Vivo\CMS\Model\ContentContainer $contentContainer
     */
    public function setContentContainer(Model\ContentContainer $contentContainer)
    {
        $this->contentContainer = $contentContainer;
    }

    /**
     * @param array $contents
     */
    public function setAvailableContents(array $contents)
    {
        $this->availableContents = $contents;
    }

    /**
     * Load from request post listener
     * Changes content version to the version specified in the form
     */
    public function loadFromRequestPostListenerChangeVersion()
    {
        $version = $this->getForm()->get('version')->getValue();
        $this->doChangeVersion($version);
    }

    public function initForm()
    {
        $this->loadContents();
        $version = $this->getForm()->get('version')->getValue();
        $this->doChangeVersion($version);
    }

    /**
     * Attaches listeners
     */
    public function attachListeners()
    {
        parent::attachListeners();
        $eventManager   = $this->getEventManager();
        $eventManager->attach(AbstractForm::EVENT_LOAD_FROM_REQUEST_POST,
            array($this, 'loadFromRequestPostListenerChangeVersion'));
    }

    private function loadContents()
    {
        try {
            $this->contents = $this->documentApi->getContentVersions($this->contentContainer);
        }
        catch(\Vivo\Repository\Exception\ExceptionInterface $e) {
            $this->contents = array();
        }
    }

    /**
     * Extracts label name from content config array
     * @param array $contentConfig
     * @return string
     */
    private function extractLabel($contentConfig) {
        return isset($contentConfig['label']) ? $contentConfig['label'] : $contentConfig['class'];
    }

    /**
     * Extracts mime type from content config array
     * @param array $contentConfig
     * @return string|null
     */
    private function extractMimeType($contentConfig) {
        return isset($contentConfig['options']['mimeType']) ? $contentConfig['options']['mimeType'] : null;
    }

    /**
     * Get Label of Content Type Class
     * @param \Vivo\CMS\Model\Content $content
     * @return string
     */
    private function getContentLabel(Content $content)
    {
        $contentLabel = null;
        $contentClass = get_class($content);

        foreach ($this->availableContents as $ac) {
            if ($ac['class'] === $contentClass) {
                // File content type must be checked for mime type
                // to get correct label
                if ($contentClass === 'Vivo\CMS\Model\Content\File') {
                    // when 'text/html' is the case
                    // exctracted mime type must also be text/html
                    if ($content->getMimeType() === 'text/html') {
                        if ($this->extractMimeType($ac) === 'text/html') {
                            $contentLabel = $this->extractLabel($ac);
                        }
                    } else {
                        $contentLabel = $this->extractLabel($ac);
                    }
                } else {
                    $contentLabel = $this->extractLabel($ac);
                }
                // not null labels are immediatelly returned
                if ($contentLabel !== null)
                    return $contentLabel;
            }
        }
        // if no label is matched, return class name
        return $contentClass;
    }

    protected function doGetForm()
    {
        $options    = array();
        //$optionKey will be used to initialize the select value
        $optionKey = null;

        /** @var $content \Vivo\CMS\Model\Content */
        foreach ($this->contents as $k => $content) {
            $optionKey  = 'EDIT:' . $content->getUuid();
            $options[$optionKey] = sprintf('1.%d [%s] %s {%s}',
                    $k, $content->getState(),
                    $this->getContentLabel($content),
                    $content->getUuid());
        }

        foreach ($this->availableContents as $ctKey => $ac) {
            $options['NEW:' . $ctKey]   = (isset($ac['label']) ? $ac['label'] : $ac['class']);
        }

        if (is_null($optionKey) && !empty($options)) {
            //Only NEW contents available, preselect the first one
            $keys       = array_keys($options);
            $optionKey  = $keys[0];
        }

        $form = new Form('container-'.$this->contentContainer->getUuid());
        $form->setWrapElements(true);
        $form->add(array(
                'name' => 'version',
                'type' => 'Vivo\Form\Element\Select',
                'options'   => array(
                    'label'         => 'Content version',
                    'value_options' => $options,
                ),
                'attributes' => array(
                    'value' => $optionKey,
                ),
        ));
        return $form;
    }

    public function changeVersion() { }

    private function doChangeVersion($version)
    {
        /* @var $content \Vivo\CMS\Model\Content */
        $content = null;
        list($type, $param) = explode(':', $version);
        if($type == 'NEW') {
            $class  = $this->availableContents[$param]['class'];
            $content = new $class();
            //Set options to the newly created instance, if they have been set in config
            if (isset($this->availableContents[$param]['options'])
                    && is_array($this->availableContents[$param]['options'])) {
                foreach ($this->availableContents[$param]['options'] as $optKey => $optValue) {
                    $methodName = 'set' . ucfirst($optKey);
                    if (method_exists($content, $methodName)) {
                        $content->$methodName($optValue);
                    }
                }
            }
        }
        elseif($type == 'EDIT') {
            foreach ($this->contents as $c) {
                if($c->getUuid() == $param) {
                    $content = $c;
                    break;
                }
            }
        }
        /* @var $component \Vivo\Backend\UI\Explorer\Editor\Content */
        $component = $this->sm->create('Vivo\Backend\UI\Explorer\Editor\Content');
        $component->setContentContainer($this->contentContainer);
        $component->setContent($content);
        $this->addComponent($component, 'contentEditor');
        $componentEventManager  = $component->getEventManager();
        $componentEvent         = $component->getEvent();
        $componentEventManager->trigger(ComponentEventInterface::EVENT_INIT_EARLY, $componentEvent);
        $componentEventManager->trigger(ComponentEventInterface::EVENT_INIT, $componentEvent);
        $componentEventManager->trigger(ComponentEventInterface::EVENT_INIT_LATE, $componentEvent);
    }

    /**
     * @return boolean
     */
    public function save()
    {
        if ($this->isValid()) {
            $result = $this->contentEditor->save();
            if($result) {
                // Reload version selectbox
                $value = $this->getForm()->get('version')->getValue();
                $this->resetForm();
                $this->loadContents();
                $this->getForm()->get('version')->setValue($value);
            }
            return $result;
        } else {
            return false;
        }
    }

    public function select()
    {
    }

    public function isDisabled()
    {
        return false;
    }

    public function getLabel()
    {
        return $this->contentContainer->getContainerName() ? $this->contentContainer->getContainerName() : '+';
    }
}
