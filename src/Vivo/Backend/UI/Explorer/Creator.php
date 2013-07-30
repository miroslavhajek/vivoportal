<?php
namespace Vivo\Backend\UI\Explorer;

use Vivo\UI\Alert;
use Vivo\Util\RedirectEvent;
use Vivo\CMS\Model\Folder;

/**
 * Creator
 */
class Creator extends Editor
{
    /**
     * @var \Vivo\Backend\UI\Explorer\ExplorerInterface
     */
    private $explorer;

    /**
     * Parent folder for the entity being created
     * @var Folder
     */
    protected $parentFolder;

    public function init()
    {
        $this->explorer = $this->getParent('Vivo\Backend\UI\Explorer\ExplorerInterface');
        $this->parentFolder = $this->explorer->getEntity();
        $this->doCreate();

        $this->initForm();
    }

    public function create()
    {
    }

    /**
     * Returns path which should be passed to availableContentsProvider
     * @return string
     */
    protected function getPathForContentsProvider()
    {
        return $this->parentFolder->getPath();
    }

    protected function doGetForm()
    {
        $form = parent::doGetForm();
        $form->add(array(
            'name' => '__type',
            'type' => 'Vivo\Form\Element\Select',
            'attributes' => array(
                'options' => array(
                    'Vivo\CMS\Model\Document' => 'Vivo\CMS\Model\Document',
                    'Vivo\CMS\Model\Folder' => 'Vivo\CMS\Model\Folder',
                )
            )
        ));
        return $form;
    }

    private function doCreate()
    {
        $entityClass = $this->request->getPost('__type', 'Vivo\CMS\Model\Document');
        $this->entity = new $entityClass;
        $this->resetForm();
        $this->getForm()->bind($this->entity);
        $this->getForm()->get('__type')->setValue($entityClass);
        $this->loadFromRequest();
    }

    public function save()
    {
        if($this->getForm()->isValid()) {
            $parent = $this->explorer->getEntity();
            $this->entity = $this->documentApi->createDocument($parent, $this->entity);
            $this->explorer->setEntity($this->entity);
            $this->saveProcess();
            $routeParams = array(
                'path' => $this->entity->getUuid(),
                'explorerAction' => 'editor',
            );
            $url = $this->urlHelper->fromRoute('backend/explorer', $routeParams);
            $this->events->trigger(new RedirectEvent($url));
            $this->addAlertMessage('Created...', Alert::TYPE_SUCCESS);
        }
        else {
            $this->addAlertMessage('Error...', Alert::TYPE_ERROR);
        }
    }
}
