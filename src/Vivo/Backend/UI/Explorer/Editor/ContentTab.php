<?php
namespace Vivo\Backend\UI\Explorer\Editor;

use Vivo\UI\AbstractForm;
use Vivo\UI\TabContainerItemInterface;
use Vivo\Form\Form;
use Vivo\CMS\Api;
use Vivo\CMS\Model;

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

    public function init()
    {
        $this->loadContents();
        parent::init();
        $version = $this->getForm()->get('version')->getValue();
        $this->doChangeVersion($version);
    }

    public function initForm()
    {
        $this->loadContents();
        $version = $this->getForm()->get('version')->getValue();
        $this->doChangeVersion($version);
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
     * Get Label of Content Type Class
     * @param $class
     * @return string
     */
    private function getContentLabelFromClass($class)
    {
        foreach ($this->availableContents as $ctKey => $ac) {
            if ($ac['class'] == $class) {
                return isset($ac['label']) ? $ac['label'] : $ac['class'];
            }
        }
        return $class;
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
                    $this->getContentLabelFromClass(get_class($content)),
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
        $component->init();
    }

    /**
     * @return boolean
     */
    public function save()
    {
        $result = $this->contentEditor->save();

        if($result) {
            // Reload version selectbox
            $value = $this->getForm()->get('version')->getValue();
            $this->resetForm();
            $this->loadContents();
            $this->getForm()->get('version')->setValue($value);
        }

        return $result;
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
