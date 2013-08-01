<?php
namespace Vivo\CMS\UI\Content\Editor\File;

use Vivo\CMS\Api;
use Vivo\Form\Factory as FormFactory;
use Vivo\CMS\RefInt\SymRefConvertorInterface;
use Vivo\CMS\UI\Content\Editor\AbstractAdapter;
use Vivo\CMS\UI\Content\Editor\ResourceEditorInterface;
use Vivo\Repository\Exception\PathNotSetException;
use Vivo\Storage\Exception\IOException;

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

    /**
     * Initializes Adapter
    */
    public function init()
    {
        parent::init();

        $form = $this->getForm();
        $form->setAttribute('method', 'post');
        try {
            if($this->content->getUuid()) {
                $data = $this->fileApi->getResource($this->content);
                $this->data = $data;

                $data = $this->symRefConvertor->convertReferencesToURLs($data);
                $form->get("resource")->setValue($data);
            }
        }
        catch (IOException $e) {
            // First open WYSIWYG (create action)
        }
        catch (PathNotSetException $e) {

        }
    }

    /**
     * Creates form
     */
    protected function doGetForm()
    {
        return $this->formFactory->createForm(array(
            'name' => 'editor-'.$this->content->getUuid(),
            'hydrator' => 'Zend\Stdlib\Hydrator\ArraySerializable',
                'elements' => array(
                   array('spec' => array(
                            'type' => 'Vivo\Form\Element\Textarea',
                            'name' => 'resource',
                            'attributes' => array(
                                'rows' => 10,
                                'cols' => 30,
                                'id'   => 'content-resource-'.$this->content->getUuid(),
                            ),
                            'options' => array(
                                'label' => 'Wysiwig',
                            ),
                        ),
                    ),
                ),
        ));
    }

    /**
     * (non-PHPdoc)
     * @see Vivo\CMS\UI\Content\Editor\File.DataEditorInterface::dataChanged()
     */
    public function dataChanged()
    {
        // using of !== is important
        // returns true on nonexisting resource
        // causes inital creation of empty resource
        return ($this->data !== $this->getData());
    }

    /**
     * (non-PHPdoc)
     * @see Vivo\CMS\UI\Content\Editor\File.DataEditorInterface::getData()
     */
    public function getData()
    {
        $this->loadFromRequest();
        $form = $this->getForm();
        if($form->isValid()) {
            $data = $form->get("resource")->getValue();
            $data = $this->symRefConvertor->convertUrlsToReferences($data);

            return $data;
        }
    }

}
