<?php
namespace Vivo\CMS\UI\Content\Editor\File;

use Vivo\CMS\Api;
use Vivo\Form\Factory as FormFactory;
use Vivo\CMS\RefInt\SymRefConvertorInterface;
use Vivo\CMS\UI\Content\Editor\AbstractAdapter;
use Vivo\CMS\UI\Content\Editor\ResourceEditorInterface;
use Vivo\Repository\Exception\PathNotSetException;
use Vivo\Storage\Exception\IOException;
use Vivo\UI\ComponentEventInterface;

/**
 * Editor Adapter for editing HTML code via WYSIWYG Editor
 */
class WysiwygAdapter extends AbstractAdapter implements ResourceEditorInterface
{
    /**
     * @var \Vivo\CMS\Api\Content\File
     */
    private $fileApi;

    /**
     * Edited html code
     * @var string
     */
    protected $data;

    /**
     * @var \Vivo\Form\Factory
     */
    protected $formFactory;

    /**
     * Constructor
     * @param \Vivo\CMS\Api\Content\File $fileApi
     * @param \Vivo\CMS\RefInt\SymRefConvertorInterface $symRefConvertor
     * @param \Vivo\Form\Factory $formFactory
     */
    public function __construct(Api\Content\File $fileApi, SymRefConvertorInterface $symRefConvertor, FormFactory $formFactory)
    {
        $this->fileApi          = $fileApi;
        $this->symRefConvertor  = $symRefConvertor;
        $this->formFactory      = $formFactory;
    }

    public function attachListeners()
    {
        parent::attachListeners();
        $eventManager   = $this->getEventManager();
        $eventManager->attach(ComponentEventInterface::EVENT_INIT, array($this, 'initListenerLoadResourceData'));
    }

    public function initListenerLoadResourceData()
    {
        try {
            if($this->content->getUuid()) {
                $data = $this->fileApi->getResource($this->content);
                $this->data = $data;
                $data = $this->symRefConvertor->convertReferencesToURLs($data);
                $fieldset   = $this->getFieldset();
                $fieldset->get('resource')->setValue($data);
            }
        }
        catch (IOException $e) {
            // First open WYSIWYG (create action)
        }
        catch (PathNotSetException $e) {

        }
    }

    /**
     * Creates fieldset
     */
    protected function doGetFieldset()
    {
        if ($this->content->getUuid()) {
            $idSuffix   = $this->content->getUuid();
        } else {
            /** @var $parentContentTab \Vivo\Backend\UI\Explorer\Editor\ContentTab */
            $parentContentTab   = $this->getParent('Vivo\Backend\UI\Explorer\Editor\ContentTab');
            $idSuffix   = 'new-' . $parentContentTab->getName();
        }
        $fieldset   = $this->formFactory->createFieldset(array(
//            'name' => 'editor-'.$this->content->getUuid(),
            'name' => 'html_wysiwyg_edit',
            'elements' => array(
                array('spec' => array(
                    'type' => 'Vivo\Form\Element\Textarea',
                    'name' => 'resource',
                    'attributes' => array(
                        'rows' => 10,
                        'cols' => 30,
                        'id'   => 'content-resource-' . $idSuffix,
                    ),
                    'options' => array(
                        'label' => 'Wysiwig',
                    ),
                ),
                ),
            ),
        ));
        return $fieldset;
    }

    /**
     * (non-PHPdoc)
     * @see Vivo\CMS\UI\Content\Editor\File.DataEditorInterface::dataChanged()
     */
    public function dataChanged()
    {
        // using of !== is important
        // returns true on non-existing resource
        // causes initial creation of empty resource
        return ($this->data !== $this->getData());
    }

    /**
     * (non-PHPdoc)
     * @see Vivo\CMS\UI\Content\Editor\File.DataEditorInterface::getData()
     */
    public function getData()
    {
        $data   = $this->getFieldsetData(false);
        if (isset($data['resource'])) {
            $resourceData   = $this->symRefConvertor->convertUrlsToReferences($data['resource']);
        } else {
            $resourceData   = null;
        }
        return $resourceData;
    }
}
