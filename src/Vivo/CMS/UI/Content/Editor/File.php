<?php
namespace Vivo\CMS\UI\Content\Editor;

use Vivo\CMS\Model\Content;
use Vivo\CMS\UI\Content\Editor\ResourceEditorInterface;
use Vivo\CMS\Api;
use Vivo\CMS\Model;
use Vivo\CMS\UI\AbstractForm;
use Vivo\Form\Form;
use Vivo\Repository\Exception\PathNotSetException;
use Vivo\CMS\RefInt\SymRefConvertorInterface;
use Vivo\CMS\Model\ContentContainer;

use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;

class File extends AbstractForm implements EditorInterface, AdapterAwareInterface
{
    const ADAPTER_COMPONENT_NAME    = 'resourceAdapter';

//    /**
//     * @var \Vivo\CMS\Model\Content\File
//     */
//    private $content;

    /**
     * @var \Vivo\CMS\Api\Content\File
     */
    private $fileApi;

    /**
     * @var \Vivo\CMS\Api\Document
     */
    private $documentApi;

    /**
     * Symbolic reference convertor
     * @var SymRefConvertorInterface
     */
    protected $symRefConvertor;

    /**
     * Constructor
     * @param \Vivo\CMS\Api\Content\File $fileApi
     * @param Api\Document $documentApi
     * @param SymRefConvertorInterface $symRefConvertor
     */
    public function __construct(Api\Content\File $fileApi, Api\Document $documentApi, SymRefConvertorInterface $symRefConvertor)
    {
        $this->fileApi          = $fileApi;
        $this->documentApi      = $documentApi;
        $this->symRefConvertor  = $symRefConvertor;
        $this->autoAddCsrf      = false;
    }

//    /**
//     * (non-PHPdoc)
//     * @see Vivo\CMS\UI\Content\Editor.EditorInterface::setContent()
//     */
//    public function setContent(Model\Content $content)
//    {
//        $this->content = $content;
//    }

    /**
    * Sets the editor adapter
    * @param AdapterInterface $adapter
    * @return void
    */
    public function setAdapter(AdapterInterface $adapter = null)
    {
        if($adapter) {
            $this->addComponent($adapter, self::ADAPTER_COMPONENT_NAME);
        }
    }

    /**
     * Returns instance of resource adapter
     * @return child of AbstractAdapter
     */
    public function getAdapter()
    {
        return $this->getComponent(self::ADAPTER_COMPONENT_NAME);
    }

    /**
     * (non-PHPdoc)
     * @see Vivo\CMS\UI\Content\Editor.EditorInterface::save()
     */
    public function save(Model\ContentContainer $contentContainer)
    {
        //TODO - Remove!
        \Zend\Debug\Debug::dump($_POST, 'POST');

        if($this->isValid()) {
            $formData   = $this->getData();

            //TODO - Remove!
            \Zend\Debug\Debug::dump($formData, 'Form data');
            die(sprintf("%s: Debug die", __METHOD__));

            $data = $formData['upload-file'];
            if ($data["tmp_name"] != "") {
                $this->fileApi->saveFileWithUploadedFile($this->content, $data, $contentContainer);
            }
            else {
                $mimeType = $this->content->getMimeType();
                $ext = $this->fileApi->getExt($mimeType);

                $this->content->setMimeType($mimeType);
                $this->content->setExt($ext);

                $adapter = $this->getAdapter();

                //TODO - Remove!
                echo 'A';

                if ($adapter instanceof ResourceEditorInterface && $adapter->dataChanged()) {
                    //TODO - Remove!
                    echo 'B';
                    $data = $adapter->getData();

                    //TODO - Remove!
                    \Zend\Debug\Debug::dump($data, 'adapter data');
                    die(sprintf("%s: Debug die", __METHOD__));

                    $this->content->setSize(mb_strlen($data, 'UTF-8'));

                    $this->saveContent($contentContainer);
                    $this->saveResource($data);
                }
                else {
                    $this->saveContent($contentContainer);
                }
            }
        }
    }

    /**
     * Saves content
     * @param ContentContainer $contentContainer
     */
    private function saveContent(ContentContainer $contentContainer)
    {
        if($this->content->getUuid()) {
            $this->documentApi->saveContent($this->content);
        }
        else {
            $this->documentApi->createContent($contentContainer, $this->content);
        }
    }

    /**
     * Saves resource file
     * @param string $data
     */
    private function saveResource($data)
    {
        $this->fileApi->removeAllResources($this->content);
        $this->fileApi->saveResource($this->content, $data);
    }

    /**
     * (non-PHPdoc)
     * @see Vivo\UI.AbstractForm::doGetForm()
     */
    public function doGetForm()
    {
        $form = new Form('content-resource-form'.$this->content->getUuid());
        $form->setWrapElements(true);
        $form->setHydrator(new ClassMethodsHydrator(false));
        $form->setOptions(array('use_as_base_fieldset' => true));
        $form->add(array(
                    'name' => 'upload-file',
                    'type' => 'Vivo\Form\Element\File',
                    'attributes' => array(
                        'id'   => 'content-resource-upload-'.$this->content->getUuid(),
                    ),
                    'options' => array(
                        'label' => 'resource',
                    ),
        ));
        return $form;
    }

    /**
     * Returns key under which an editor adapter is searched in configuration
     * @return string
     */
    public function getAdapterKey()
    {
        return $this->content->getMimeType();
    }
}
