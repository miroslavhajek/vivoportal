<?php
namespace Vivo\CMS\UI\Content\Editor;

use Vivo\Stdlib\OrderableInterface;

use Vivo\CMS\Api;
use Vivo\CMS\Api\Content\Gallery as GalleryApi;
use Vivo\CMS\Model;
use Vivo\UI\AbstractForm;
use Vivo\Form\Form;
use Vivo\Form\Fieldset;
use Vivo\Util\RedirectEvent;

class Gallery extends AbstractForm implements EditorInterface
{
    /**
     * @var \Vivo\CMS\Model\Content\File
     */
    private $content;

    /**
     * @var \Vivo\CMS\Api\Document
     */
    private $documentApi;

    /**
     * @var \Vivo\CMS\Api\Content\Gallery
     */
    private $galleryApi;

    /**
     * @var array
     */
    private $files = array();

    /**
     * Constructor
     *
     * @param \Vivo\CMS\Api\Document $documentApi
     * @param \Vivo\CMS\Api\Content\Gallery $galleryApi
     */
    public function __construct(Api\Document $documentApi, GalleryApi $galleryApi)
    {
        $this->autoAddCsrf = false; //FIXME: remove after fieldsets
        $this->documentApi = $documentApi;
        $this->galleryApi = $galleryApi;
    }

    public function init()
    {
        try {
            $this->files = $this->galleryApi->getList($this->content);
        }
        catch (Api\Exception\InvalidPathException $e) {
            $this->files = array();
        }

        parent::init();
    }

    /**
     * (non-PHPdoc)
     * @see Vivo\CMS\UI\Content\Editor.EditorInterface::setContent()
     */
    public function setContent(Model\Content $content)
    {
        $this->content = $content;
    }

    /**
     * @see \Vivo\CMS\UI\Content\Editor\EditorInterface::save()
     */
    public function save(Model\ContentContainer $container)
    {
        $form = $this->getForm();

        if($form->isValid()) {
            if($this->content->getUuid()) {
                $this->documentApi->saveContent($this->content);
            }
            else {
                $this->documentApi->createContent($container, $this->content);
            }

            if($this->content->getCreated()) {
                if($form->get('gl-new')) {
                    $fieldset = $form->get('gl-new');

                    // Upload new file
                    $file = $fieldset->get('file')->getValue();
                    $name = $fieldset->get('name')->getValue();
                    $desc = $fieldset->get('desc')->getValue();

                    if($file['error'] != UPLOAD_ERR_NO_FILE && $file['error'] != UPLOAD_ERR_OK) {
                        throw new \Exception(sprintf('%s: File upload error %s', __METHOD__, $file['error']));
                    }
                    if($file['error'] == UPLOAD_ERR_OK) {
                        $order = $this->getMaxOrderBy() + 1;
                        $this->galleryApi->createMediaWithUploadedFile($this->content, $file,
                                array(
                                    'name' => trim($name),
                                    'description' => trim($desc),
                                    'order' => $order,
                                ));
                    }
                }

                // Update current contents
                if($form->get('gl-file-container')) {
                    foreach ($form->get('gl-file-container')->getFieldsets() as $uuid=>$fieldset) {
                        $media = $this->galleryApi->getEntity($uuid);
                        $media->setName(trim($fieldset->get('name')->getValue()));
                        $media->setDescription(trim($fieldset->get('desc')->getValue()));

                        $this->galleryApi->saveEntity($media);
                    }
                }
            }
        }
    }

    /**
     * Delete action.
     *
     * @param string $uuid
     */
    public function delete($uuid)
    {
        $file = $this->galleryApi->getEntity($uuid);
        $this->galleryApi->removeEntity($file);
        $this->getEventManager()->trigger(new RedirectEvent());
    }

    /**
     * Deletes all gallery files.
     */
    public function deleteAll()
    {
        $this->galleryApi->removeAllFiles($this->content);
        $this->getEventManager()->trigger(new RedirectEvent());
    }

    /**
     * Move up action.
     *
     * @param string $uuid Entity UUID.
     */
    public function moveUp($uuid)
    {
        $entity = $this->galleryApi->getEntity($uuid);
        $this->galleryApi->moveUp($this->files, $entity);
        $this->getEventManager()->trigger(new RedirectEvent());
    }

    /**
     * Move down action.
     *
     * @param string $uuid Entity UUID.
     */
    public function moveDown($uuid)
    {
        $entity = $this->galleryApi->getEntity($uuid);
        $this->galleryApi->moveDown($this->files, $entity);
        $this->getEventManager()->trigger(new RedirectEvent());
    }

    /**
     * Sets as main.
     *
     * @param string $uuid Entity UUID.
     */
    public function setAsMain($uuid)
    {
        $entity = $this->galleryApi->getEntity($uuid);
        $this->galleryApi->setAsMain($this->content, $entity);
        $this->getEventManager()->trigger(new RedirectEvent());
    }

    /**
     * Returns the maximum value of the order.
     *
     * @return int
     */
    private function getMaxOrderBy() {
        return $this->galleryApi->getMaxOrder($this->files);
    }

    /**
     * (non-PHPdoc)
     * @see Vivo\UI.AbstractForm::doGetForm()
     */
    public function doGetForm()
    {
        $form = new Form('gallery-editor-'.$this->content->getUuid());
        $form->setWrapElements(true);

        if($this->content->getCreated()) {
            $fieldset = $this->getEditorFieldset();
            $form->add($fieldset);
        }
        if(count($this->files)) {
            $fieldset = $this->getEditorFieldsetMedia($this->files);
            $form->add($fieldset);
        }

        return $form;
    }

    /**
     * Returns editor fieldset.
     *
     * @return \Vivo\Form\Fieldset
     */
    private function getEditorFieldset()
    {
        $fieldset = new Fieldset('gl-new');
        $fieldset->add(array(
            'name' => 'file',
            'type' => 'Vivo\Form\Element\File',
            'options' => array(
                'label' => 'new media',
            ),
        ));
        $fieldset->add(array(
            'name' => 'name',
            'type' => 'Vivo\Form\Element\Text',
            'options' => array(
                'label' => 'new media name',
            ),
        ));
        $fieldset->add(array(
            'name' => 'desc',
            'type' => 'Vivo\Form\Element\Textarea',
            'options' => array(
                'label' => 'new media description',
            ),
        ));

        return $fieldset;
    }

    /**
     * Returns editor for current gallery files.
     *
     * @param array $files
     * @return \Vivo\Form\Fieldset
     */
    private function getEditorFieldsetMedia(array $files)
    {
        $container = new Fieldset('gl-file-container');

        foreach ($files as $file) {
            $fieldset = new Fieldset($file->getUuid());
            $fieldset->add(array(
                'name' => 'name',
                'type' => 'Vivo\Form\Element\Text',
                'attributes' => array(
                    'value' => $file->getName()
                ),
                'options' => array(
                    'label' => 'name',
                ),
            ));
            $fieldset->add(array(
                'name' => 'desc',
                'type' => 'Vivo\Form\Element\Textarea',
                'attributes' => array(
                    'value' => $file->getDescription()
                ),
                'options' => array(
                    'label' => 'description',
                ),
            ));

            $container->add($fieldset);
        }

        return $container;
    }

    public function view()
    {
        $view = parent::view();
        $view->files = $this->files;

        return $view;
    }

}
