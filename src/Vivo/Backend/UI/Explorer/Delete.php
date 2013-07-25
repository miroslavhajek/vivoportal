<?php
namespace Vivo\Backend\UI\Explorer;

use Vivo\CMS\UI\AbstractForm;
use Vivo\CMS\Api\DocumentInterface as DocumentApiInterface;
use Vivo\CMS\Api\CMS;
use Vivo\Backend\Form\Delete as DeleteForm;
use Vivo\Form\Form;
use Vivo\Util\RedirectEvent;
use Vivo\UI\Alert;
use Vivo\Service\Initializer\TranslatorAwareInterface;

use Zend\I18n\Translator\Translator;

/**
 * Delete
 */
class Delete extends AbstractForm implements TranslatorAwareInterface
{
    /**
     * CMS API
     * @var CMS
     */
    protected $cmsApi;

    /**
     * Document API
     * @var DocumentApiInterface
     */
    protected $docApi;

    /**
     * Alert UI Component
     * @var Alert
     */
    protected $alert;

    /**
     * Translator
     * @var Translator
     */
    protected $translator;

    /**
     * Constructor
     * @param \Vivo\CMS\Api\CMS $cmsApi
     * @param \Vivo\CMS\Api\DocumentInterface $docApi
     * @param \Vivo\UI\Alert $alert
     */
    public function __construct(CMS $cmsApi, DocumentApiInterface $docApi, Alert $alert)
    {
        $this->cmsApi   = $cmsApi;
        $this->docApi   = $docApi;
        $this->alert    = $alert;
    }

    public function view()
    {
        /** @var $explorer Explorer */
        $explorer                   = $this->getParent();
        $entity                     = $explorer->getEntity();
        $this->getView()->entity    = $entity;
        $this->getView()->entityRelPath = $this->cmsApi->getEntityRelPath($entity);
        return parent::view();
    }

    /**
     * Delete action
     */
    public function delete()
    {
        $form   = $this->getForm();
        if ($form->isValid()) {
            /** @var $explorer Explorer */
            $explorer   = $this->getParent();
            //Delete - and redirect
            $doc        = $explorer->getEntity();
            $relPath    = $this->cmsApi->getEntityRelPath($doc);
            $docParent  = $this->cmsApi->getParent($doc);
            $this->cmsApi->removeEntity($doc);
            $explorer->setEntity($docParent);
            $explorer->setCurrent('viewer');
            $message = sprintf($this->translator->translate("Document at path '%s' has been deleted"), $relPath);
            $this->alert->addMessage($message, Alert::TYPE_SUCCESS);
            $this->events->trigger(new RedirectEvent());
        } else {
            $message = $this->translator->translate("Form data is not valid");
            $this->alert->addMessage($message, Alert::TYPE_ERROR);
        }
    }

    /**
     * Creates ZF form and returns it
     * Factory method
     * @return Form
     */
    protected function doGetForm()
    {
        /** @var $explorer Explorer */
        $explorer   = $this->getParent();
        $hasSubdocs = $this->docApi->hasChildDocuments($explorer->getEntity());
        $form       = new DeleteForm($hasSubdocs);
        $form->setAttribute('action', $this->request->getUri()->getPath());
        $form->add(array(
            'name'  => 'act',
            'attributes'    => array(
                'type'  => 'hidden',
                'value' => $this->getPath('delete'),
            ),
        ));
        return $form;
    }

    /**
     * Injects translator
     * @param \Zend\I18n\Translator\Translator $translator
     */
    public function setTranslator(Translator $translator)
    {
        $this->translator   = $translator;
    }
}
