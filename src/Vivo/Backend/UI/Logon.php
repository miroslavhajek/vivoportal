<?php
namespace Vivo\Backend\UI;

use Vivo\Form\DomainLogon;
use Vivo\Security\Manager\AbstractManager;
use Vivo\UI\AbstractForm;
use Vivo\Util\RedirectEvent;

/**
 * Backend logon component
 */
class Logon extends AbstractForm
{
    /**
     * @var AbstractManager
     */
    protected $securityManager;

    /**
     * Constructor.
     * @param AbstractManager $securityManager
     */
    public function __construct(AbstractManager $securityManager)
    {
        $this->securityManager = $securityManager;
    }

    /**
     * Logon action.
     */
    public function logon()
    {
        $form = $this->getForm();
        if ($form->isValid()) {
            $validatedData = $form->getData();
            $result = $this->securityManager->authenticate(
                    $validatedData['logon']['domain'],
                    $validatedData['logon']['username'],
                    $validatedData['logon']['password']
                    );

            if ($result) {
                $this->getEventManager()->trigger(new RedirectEvent());
            } else {
                $this->view->logonError = 'Unable to login (wrong username or password or account is not active or no longer valid)';
            }
        }
    }

    /**
     * Logoff action.
     */
    public function logoff()
    {
        $this->securityManager->removeUserPrincipal();
        $this->getEventManager()->trigger(new RedirectEvent());
    }

    /**
     * Creates form.
     * @return DomainLogon
     */
    protected function doGetForm()
    {
        $form = new DomainLogon();
        $form->add(array(
            'name' => 'act',
            'attributes' => array(
                'type' => 'hidden',
                'value' => $this->getPath('logon'),
            ),
        ));
        return $form;
    }

    /**
     * Prepare view.
     */
    public function view()
    {
        $this->view->user = $this->securityManager->getUserPrincipal();
        return parent::view();
    }
}
