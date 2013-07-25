<?php
namespace Vivo\CMS\UI\Content\Editor;

use Vivo\CMS\Api;
use Vivo\CMS\Api\Content\Fileboard as FileboardApi;
use Vivo\CMS\Model;
use Vivo\CMS\Model\Content\Fileboard\Separator;
use Vivo\UI\AbstractForm;
use Vivo\Form\Form;
use Vivo\Form\Fieldset;
use Vivo\Util\RedirectEvent;

class Fileboard extends AbstractForm implements EditorInterface
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
     * @var \Vivo\CMS\Api\Content\Fileboard
     */
    private $fileboardApi;

    /**
     * @var array
     */
    private $files = array();

    /**
     * Constructor
     *
     * @param \Vivo\CMS\Api\Document $documentApi
     * @param \Vivo\CMS\Api\Content\Fileboard $fileboardApi
     */
    public function __construct(Api\Document $documentApi, FileboardApi $fileboardApi)
    {
        $this->autoAddCsrf = false; //FIXME: remove after fieldsets
        $this->documentApi = $documentApi;
        $this->fileboardApi = $fileboardApi;
    }

    public function init()
    {
        try {
            $this->files = $this->fileboardApi->getList($this->content);
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
                if($form->get('fb-new')) {
                    $fieldset = $form->get('fb-new');

                    // Upload new file
                    $file = $fieldset->get('file')->getValue();
                    $name = $fieldset->get('name')->getValue();
                    $desc = $fieldset->get('desc')->getValue();

                    if($file['error'] != UPLOAD_ERR_NO_FILE && $file['error'] != UPLOAD_ERR_OK) {
                        throw new \Exception(sprintf('%s: File upload error %s', __METHOD__, $file['error']));
                    }
                    if($file['error'] == UPLOAD_ERR_OK) {
                        $order = $this->getMaxOrderBy() + 1;
                        $this->fileboardApi->createMediaWithUploadedFile($this->content, $file,
                                array(
                                    'name' => trim($name),
                                    'description' => trim($desc),
                                    'order' => $order,
                                ));
                    }

                    // Create new separator
                    $html = $fieldset->get('separator')->getValue();

                    if($html) {
                        $order = $this->getMaxOrderBy() + 1;
                        $this->fileboardApi->createSeparator($this->content, $html, array('order' => $order));
                    }
                }

                // Update current contents
                if($form->get('fb-media-container')) {
                    foreach ($form->get('fb-media-container')->getFieldsets() as $uuid=>$fieldset) {
                        $type = $fieldset->get('type')->getValue();

                        switch($type) {
                            case 'media':
                                $media = $this->fileboardApi->getEntity($uuid);
                                $media->setName(trim($fieldset->get('name')->getValue()));
                                $media->setDescription(trim($fieldset->get('desc')->getValue()));

                                $this->fileboardApi->saveEntity($media);
                                break;

                            case 'separator':
                                $separator = $this->fileboardApi->getEntity($uuid);

                                $this->fileboardApi->saveSeparator($separator, $fieldset->get('separator')->getValue());
                                break;
                        }
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
        $media = $this->fileboardApi->getEntity($uuid);
        $this->fileboardApi->removeEntity($media);
        $this->events->trigger(new RedirectEvent());
    }

    /**
     * Deletes all fileboard files.
     */
    public function deleteAll()
    {
        $this->fileboardApi->removeAllFiles($this->content);
        $this->events->trigger(new RedirectEvent());
    }

    /**
     * Move up action.
     *
     * @param string $uuid Entity UUID.
     */
    public function moveUp($uuid)
    {
        $entity = $this->fileboardApi->getEntity($uuid);
        $this->fileboardApi->moveUp($this->files, $entity);
        $this->events->trigger(new RedirectEvent());
    }

    /**
     * Move down action.
     *
     * @param string $uuid Entity UUID.
     */
    public function moveDown($uuid)
    {
        $entity = $this->fileboardApi->getEntity($uuid);
        $this->fileboardApi->moveDown($this->files, $entity);
        $this->events->trigger(new RedirectEvent());
    }

    /**
     * Returns the maximum value of the order.
     *
     * @return int
     */
    private function getMaxOrderBy() {
        return $this->fileboardApi->getMaxOrder($this->files);
    }

    /**
     * (non-PHPdoc)
     * @see Vivo\UI.AbstractForm::doGetForm()
     */
    public function doGetForm()
    {
        $form = new Form('fileboard-editor-'.$this->content->getUuid());
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
        $fieldset = new Fieldset('fb-new');
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
        $fieldset->add(array(
            'name' => 'separator',
            'type' => 'Vivo\Form\Element\Textarea',
            'options' => array(
                'label' => 'new separator',
            ),
        ));

        return $fieldset;
    }

    /**
     * Returns editor for current fileboard files.
     *
     * @param array $files
     * @return \Vivo\Form\Fieldset
     */
    private function getEditorFieldsetMedia(array $files)
    {
        $container = new Fieldset('fb-media-container');

        foreach ($files as $file) {
            $fieldset = new Fieldset($file->getUuid());

            if($file instanceof Separator) {
                $fieldset->add(array(
                    'name' => 'type',
                    'type' => 'Vivo\Form\Element\Hidden',
                    'attributes' => array('value' => 'separator'),
                ));
                $fieldset->add(array(
                    'name' => 'separator',
                    'type' => 'Vivo\Form\Element\Textarea',
                    'attributes' => array(
                        'value' => $this->fileboardApi->getResource($file)
                    ),
                    'options' => array(
                        'label' => 'separator',
                    ),
                ));
            }
            else {
                $fieldset->add(array(
                    'name' => 'type',
                    'type' => 'Vivo\Form\Element\Hidden',
                    'attributes' => array('value' => 'media'),
                ));
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
            }

            $container->add($fieldset);
        }

        return $container;
    }

    public function view()
    {
        $separators = array();
        foreach ($this->files as $file) {
            if($file instanceof Separator) {
                $separators[$file->getUuid()] = $this->fileboardApi->getResource($file);
            }
        }

        $view = parent::view();
        $view->files = $this->files;
        $view->separators = $separators;

        return $view;
    }

}
