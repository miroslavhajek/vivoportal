<?php
namespace Vivo\CMS\Api;

use Vivo\UI\AbstractForm;

use Zend\Db\TableGateway\TableGateway;
use Zend\Json\Json;
use Zend\Session\Container as SessionContainer;

use DateTime;

/**
 * FormUtil
 */
class FormUtil
{
    /**
     * Name of the current site
     * @var string
     */
    protected $siteName;

    /**
     * Save form table gateway
     * @var TableGateway
     */
    protected $tgwSavedForm;

    /**
     * Session container
     * @var SessionContainer
     */
    protected $session;

    /**
     * Data structure saved for a form in session
     * @var array
     */
    protected $sessionFormInfoTemplate  = array(
        'ident'         => null,
        'isSubmitted'   => null,
    );

    /**
     * Constructor
     * @param TableGateway $tgwSavedForm
     * @param \Zend\Session\Container $session
     * @param string $siteName
     */
    public function __construct(TableGateway $tgwSavedForm, SessionContainer $session, $siteName)
    {
        $this->siteName     = $siteName;
        $this->tgwSavedForm = $tgwSavedForm;
        $this->session      = $session;
    }

    /**
     * Saves form state
     * @param string $formClass
     * @param string $formName
     * @param string $formIdent
     * @param array $formData
     * @param array $context
     */
    public function saveFormState($formClass, $formName, $formIdent, array $formData, array $context = null)
    {
        $now                = date('Y-m-d H:i:s');
        $dataSerialized     = Json::encode($formData);
        $contextSerialized  = Json::encode($context);
        if ($this->formStateExists($formClass, $formName, $formIdent)) {
            //Update
            $affected       = $this->tgwSavedForm->update(array(
                'time_saved'    => $now,
                'form_data'     => $dataSerialized,
                'context'       => $contextSerialized,
            ), array(
                'site_name'     => $this->siteName,
                'form_class'    => $formClass,
                'form_name'     => $formName,
                'form_ident'    => $formIdent,
            ));
        } else {
            //Insert
            $affected   = $this->tgwSavedForm->insert(array(
                'site_name'     => $this->siteName,
                'form_class'    => $formClass,
                'form_name'     => $formName,
                'form_ident'    => $formIdent,
                'time_saved'    => $now,
                'form_data'     => $dataSerialized,
                'context'       => $contextSerialized,
            ));
        }
    }

    /**
     * Loads form state
     * If form state is not found, throws an exception
     * Returns array with the following elements:
     * array('form_data' => <form data>, 'context' => <context>, 'time_saved' => DateTime)
     * @param string $formClass
     * @param string $formName
     * @param string $formIdent
     * @throws Exception\RuntimeException
     * @return array
     */
    public function loadFormState($formClass, $formName, $formIdent)
    {
        $rowSet = $this->tgwSavedForm->select(array(
            'site_name'     => $this->siteName,
            'form_class'    => $formClass,
            'form_name'     => $formName,
            'form_ident'    => $formIdent,
        ));
        if ($rowSet->count() != 1) {
            //Form state not found
            throw new Exception\RuntimeException(
                sprintf("%s: Saved form state not found for siteName = '%s', formName = '%s', formIdent = '%s'",
                    __METHOD__, $this->siteName, $formName, $formIdent));
        }
        $row    = $rowSet->current();
        $retVal = array(
            'form_data'     => Json::decode($row['form_data'], Json::TYPE_ARRAY),
            'context'       => Json::decode($row['context'], Json::TYPE_ARRAY),
            'time_saved'    => new DateTime($row['time_saved']),
        );
        return $retVal;
    }

    /**
     * Returns if a saved form state exists for the given form
     * @param string $formClass
     * @param string $formName
     * @param string $formIdent
     * @return bool
     */
    public function formStateExists($formClass, $formName, $formIdent)
    {
        $rowSet = $this->tgwSavedForm->select(array(
            'form_class'    => $formClass,
            'site_name'     => $this->siteName,
            'form_name'     => $formName,
            'form_ident'    => $formIdent,
        ));
        if ($rowSet->count() == 1) {
            $exists = true;
        } else {
            $exists = false;
        }
        return $exists;
    }

    /**
     * Removes saved form state from storage
     * It's ok if the specified form's state is not saved
     * @param string $formClass
     * @param string $formName
     * @param string $formIdent
     */
    public function removeFormState($formClass, $formName, $formIdent)
    {
        $this->tgwSavedForm->delete(array(
            'site_name'     => $this->siteName,
            'form_class'    => $formClass,
            'form_name'     => $formName,
            'form_ident'    => $formIdent,
        ));
    }

    /**
     * Sets form identifier stored in session
     * @param string $formClass
     * @param string $formName
     * @param string $formIdent
     */
    public function setSessionFormIdent($formClass, $formName, $formIdent)
    {
        $formInfo           = $this->getSessionFormInfo($formClass, $formName);
        $formInfo['ident']  = $formIdent;
        $this->setSessionFormInfo($formClass, $formName, $formInfo);
    }

    /**
     * Returns form identifier stored in session
     * @param string $formClass
     * @param string $formName
     * @return string
     */
    public function getSessionFormIdent($formClass, $formName)
    {
        $formInfo       = $this->getSessionFormInfo($formClass, $formName);
        if (!isset($formInfo['ident'])) {
            $formIdent          = uniqid();
            $formInfo['ident']  = $formIdent;
            $this->setSessionFormIdent($formClass, $formName, $formIdent);
        }
        return $formInfo['ident'];
    }

    /**
     * Sets flag indicating if the form has been submitted into session
     * @param string $formClass
     * @param string $formName
     * @param bool $isSubmitted
     */
    public function setSessionIsFormSubmitted($formClass, $formName, $isSubmitted)
    {
        $formInfo                   = $this->getSessionFormInfo($formClass, $formName);
        $formInfo['isSubmitted']    = $isSubmitted;
        $this->setSessionFormInfo($formClass, $formName, $formInfo);
    }

    /**
     * Returns flag indicating if the form has been submitted from session
     * @param string $formClass
     * @param string $formName
     * @return bool
     */
    public function getSessionIsFormSubmitted($formClass, $formName)
    {
        $formInfo   = $this->getSessionFormInfo($formClass, $formName);
        return $formInfo['isSubmitted'];
    }

    /**
     * Returns if form info is present in session
     * @param string $formClass
     * @param string $formName
     * @return bool
     */
    public function hasSessionFormInfo($formClass, $formName)
    {
        $fullFormName   = $this->getFullFormName($formClass, $formName);
        return isset($this->session->form[$fullFormName]);
    }

    /**
     * Returns form info stored in session
     * @param string $formClass
     * @param string $formName
     * @return array
     */
    public function getSessionFormInfo($formClass, $formName)
    {
        $fullFormName   = $this->getFullFormName($formClass, $formName);
        if (!isset($this->session->form[$fullFormName])) {
            $this->setSessionFormInfo($formClass, $formName, $this->sessionFormInfoTemplate);
        }
        return $this->session->form[$fullFormName];
    }

    /**
     * Sets form info into session
     * @param string $formClass
     * @param string $formName
     * @param array $formInfo
     */
    public function setSessionFormInfo($formClass, $formName, array $formInfo)
    {
        $fullFormName   = $this->getFullFormName($formClass, $formName);
        if (!isset($this->session->form)) {
            $this->session->form  = array();
        }
        $this->session->form[$fullFormName] = $formInfo;
    }

    /**
     * Removes form info stored in session
     * @param string $formClass
     * @param string $formName
     */
    public function removeSessionFormInfo($formClass, $formName)
    {
        $fullFormName   = $this->getFullFormName($formClass, $formName);
        if ($this->hasSessionFormInfo($formClass, $formName)) {
            unset($this->session->form[$fullFormName]);
        }
    }

    /**
     * Returns full form name
     * @param string $formClass
     * @param string $formName
     * @return string
     */
    protected function getFullFormName($formClass, $formName)
    {
        $fullFormName   = $formClass . '.' . $formName;
        return $fullFormName;
    }

    /**
     * Returns form name from form class
     * @param AbstractForm $form
     * @return string
     */
    protected function getFormName(AbstractForm $form)
    {
        $name   = $form->getForm()->getName();
        return $name;
    }
}
