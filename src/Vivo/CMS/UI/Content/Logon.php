<?php
namespace Vivo\CMS\UI\Content;

use Vivo\Form\Logon as ZfFormLogon;
use Vivo\CMS\UI\AbstractForm;
use Vivo\CMS\Security\Manager\AbstractManager as AbstractSecurityManager;
use Vivo\Util\RedirectEvent;

use Zend\Form\Form as ZfForm;

/**
 * Logon UI component
 * UI for client authentication
 */
class Logon extends AbstractForm
{
    /**
     * Security Manager
     * @var AbstractSecurityManager
     */
    protected $securityManager;

    /**
     * Security domain
     * @var string
     */
    protected $securityDomain;

    /**
     * Constructor
     * @param AbstractSecurityManager $securityManager
     * @param $securityDomain
     */
    public function __construct(AbstractSecurityManager $securityManager, $securityDomain)
    {
        $this->securityManager  = $securityManager;
        $this->securityDomain   = $securityDomain;
    }

    public function view()
    {
        $this->view->user   = $this->securityManager->getUserPrincipal();
        return parent::view();
    }

    /**
     * Logon action
     */
    public function logon()
    {
        $form   = $this->getForm();
        if ($form->isValid()) {
            //Form is valid
            $validatedData  = $form->getData();
            $result = $this->securityManager->authenticate($this->securityDomain,
                                                           $validatedData['logon']['username'],
                                                           $validatedData['logon']['password']);
            /** @var $model \Vivo\CMS\Model\Content\Logon */
            $model      = $this->content;
            $redirUrl   = null;
            if ($result) {
                //Authentication successful
                if ($model->getLogonUrl()) {
                    $redirUrl   = $model->getLogonUrl();
                }
            } else {
                //Authentication failed
                if ($model->getErrorUrl()) {
                    $redirUrl   = $model->getErrorUrl();
                }
            }
        $this->getEventManager()->trigger(new RedirectEvent($redirUrl));
        }
    }

    /**
     * Logoff action
     */
    public function logoff()
    {
        $this->securityManager->removeUserPrincipal();
        //TODO - Destroy session?
        /** @var $model \Vivo\CMS\Model\Content\Logon */
        $model      = $this->content;
        $redirUrl   = null;
        if ($model->getLogoffUrl()) {
            $redirUrl   = $model->getLogoffUrl();
        }

        $this->getEventManager()->trigger(new RedirectEvent($redirUrl));
    }

    /**
     * Creates ZF form and returns it
     * Factory method
     * @return ZfForm
     */
    protected function doGetForm()
    {
        $form   = new ZfFormLogon();
        //Set form name if needed
//        $form->setName('logon_form');
        //Prepend form name to elements if needed
//        $form->setWrapElements(true);
        return $form;
    }
}
