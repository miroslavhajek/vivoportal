<?php
namespace Vivo\Backend\UI\Explorer;

use Vivo\UI\Alert;
use Vivo\Form\Fieldset;
use Vivo\UI\ComponentEventInterface;
use Vivo\Util\RedirectEvent;
use Vivo\CMS\Model\Folder;

use Zend\ServiceManager\ServiceManager;

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

    public function attachListeners()
    {
        parent::attachListeners();
        $eventManager   = $this->getEventManager();
        //Detach the listener inherited from Editor
        $eventManager->detach($this->listeners['initListenerEditor']);
        $eventManager->attach(ComponentEventInterface::EVENT_INIT_EARLY, array($this, 'initEarlyListenerCreator'));
        $eventManager->attach(ComponentEventInterface::EVENT_INIT, array($this, 'initListenerCreator'));
    }

    public function initEarlyListenerCreator()
    {
        $this->explorer = $this->getParent('Vivo\Backend\UI\Explorer\ExplorerInterface');
        $this->parentFolder = $this->explorer->getEntity();
    }

    public function initListenerCreator()
    {
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
                    'Vivo\CMS\Model\Document'   => 'Vivo\CMS\Model\Document',
                    'Vivo\CMS\Model\Folder'     => 'Vivo\CMS\Model\Folder',
                )
            )
        ));

        $form->add(array(
            'type' => 'Vivo\Form\Element\Text',
            'name' => 'name_in_path',
            'options' => array(
                'label' => 'Name in path',
            ),
        ));

        $parentEntity = $this->explorer->getEntity();

        $form->getInputFilter()->add(
            array(
                'name' => 'name_in_path',
                'required' => true,
                'validators' => array(
                    array(
                        'name' => 'vivo_unique_entity_path',
                        'options' => array(
                            'parentDocumentPath' => $parentEntity->getPath(),
                        ),
                    ),
                ),
            )
        );

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

    /**
     * Stores new entity to repository
     */
    public function save()
    {
        if ($this->getForm()->isValid()) {
            $parent = $this->explorer->getEntity();
            $nameInPath = $this->getForm()->get('name_in_path')->getValue();
            $this->entity = $this->documentApi->createDocument($parent, $this->entity, $nameInPath);
            $this->explorer->setEntity($this->entity);
            $this->saveProcess();
            $routeParams = array(
                'path' => $this->entity->getUuid(),
                'explorerAction' => 'editor',
            );
            $url = $this->urlHelper->fromRoute('backend/explorer', $routeParams);
            $this->getEventManager()->trigger(new RedirectEvent($url));
            $this->addAlertMessage('Created...', Alert::TYPE_SUCCESS);
        }
        else {
            $this->addAlertMessage('Error...', Alert::TYPE_ERROR);
        }
    }
}
