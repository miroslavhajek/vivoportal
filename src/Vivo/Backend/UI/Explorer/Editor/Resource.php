<?php
namespace Vivo\Backend\UI\Explorer\Editor;

use Vivo\UI\AbstractForm;
use Vivo\UI\Alert;
use Vivo\Form\Form;
use Vivo\CMS\Model;
use Vivo\Util\RedirectEvent;

class Resource extends AbstractForm
{
    /**
     * @var \Vivo\CMS\Api\CMS
     */
    private $cmsApi;

    /**
     * @var \Vivo\CMS\Model\Entity
     */
    private $entity;

    /**
     * @var array
     */
    private $resources = array();

    /**
     * @var \Vivo\UI\Alert
     */
    private $alert;

    /**
     * TTL for CSRF token
     * Redefine in descendant if necessary
     * @var int|null
     */
    protected $csrfTimeout          = 3600;

    /**
     * @param \Vivo\CMS\Api\CMS $cmsApi
     */
    public function __construct(\Vivo\CMS\Api\CMS $cmsApi)
    {
        $this->cmsApi = $cmsApi;
    }

    public function init()
    {
        $this->resources = $this->cmsApi->scanResources($this->entity);

        parent::init();
    }

    public function setEntity(Model\Entity $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @param \Vivo\UI\Alert $alert
     */
    public function setAlert(Alert $alert)
    {
        $this->alert = $alert;
    }

    /**
     * @param string $message
     * @param string $type
     */
    private function addAlertMessage($message, $type)
    {
        if($this->alert) {
            $this->alert->addMessage($message, $type);
        }
    }

    protected function doGetForm()
    {
        $id = $this->entity->getUuid();
        $action = $this->request->getUri()->getPath();

        $form = new Form('resource-'.$id);
        $form->setAttribute('action', $action);
        $form->setAttribute('enctype', 'multipart/form-data');
        $form->add(array(
            'name' => 'resource',
            'type' => 'Vivo\Form\Element\File',
        ));
        $form->add(array(
            'name' => 'save',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Upload',
                'class' => 'btn',
            ),
        ));
        $form->add(array(
            'name' => 'act',
            'attributes' => array(
                'type' => 'hidden',
                'value' => $this->getPath('save'),
            ),
        ));

        return $form;
    }

    /**
     * Saves new resource.
     */
    public function save()
    {
        $form = $this->getForm();

        if ($this->request->isPost()) {
            // Make certain to merge the files info!
            $post = array_merge_recursive($this->request->getPost()->toArray(), $this->request->getFiles()->toArray());
            $form->setData($post);

            if($form->isValid()) {
                $data = $form->getData();
                $data = $data['resource'];

                //TODO: replace ýžřčš -> - in file name
                $name = strtolower($data['name']);

                try {
                    $this->cmsApi->getResource($this->entity, $name);
                    $exists = true;
                }
                catch (\Vivo\Storage\Exception\IOException $e) {
                    // File not exists
                    $exists = false;
                }

                if($exists) {
                    $this->addAlertMessage(sprintf('Resource \'%s\' already exists', $name), Alert::TYPE_WARNING);
                }
                else {
                    $this->cmsApi->saveResource($this->entity, $name, file_get_contents($data['tmp_name']));
                    $this->addAlertMessage('Upload ok', Alert::TYPE_SUCCESS);
                }

                $this->getEventManager()->trigger(new RedirectEvent());
            } else {
                $this->addAlertMessage('Form not valid', Alert::TYPE_ERROR);
            }

            $form->get('resource')->setValue('');
        }
    }

    /**
     * Delete.
     *
     * @param string $name Resource name.
     */
    public function delete($name)
    {
        $this->cmsApi->removeResource($this->entity, $name);
        $this->addAlertMessage(sprintf('Resource %s has been deleted', $name), Alert::TYPE_SUCCESS);
        $this->getEventManager()->trigger(new RedirectEvent());
    }

    public function view()
    {
        $view = parent::view();
        $view->resources = $this->resources;

        return $view;
    }
}
