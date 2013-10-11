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
     *
     * @param string $formClass
     * @param string $formName
     * @param string $formIdent
     * @param array $formData
     * @param array $context
     * @param string|null $password When null, does not updated already stored password
     * @param int $status 1 = auto saved, 2 = user saved, 3 = sent to telem
     */
    public function saveFormState($formClass,
                                  $formName,
                                  $formIdent,
                                  array $formData,
                                  array $context = null,
                                  $password = null,
                                  $status)
    {
        $now                = date('Y-m-d H:i:s');
        $dataSerialized     = Json::encode($formData);
        $contextSerialized  = Json::encode($context);
        if ($this->formStateExists($formClass, $formName, $formIdent)) {
            //Update
            $data           = array(
                'time_saved'    => $now,
                'form_data'     => $dataSerialized,
                'context'       => $contextSerialized,
                'status'        => (int) $status,
            );
            if (!is_null($password)) {
                $data['password']   = $password;
            }
            $affected       = $this->tgwSavedForm->update($data, array(
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
                'password'      => $password,
                'time_created'  => $now,
                'time_saved'    => $now,
                'form_data'     => $dataSerialized,
                'context'       => $contextSerialized,
                'status'        => (int) $status,
            ));
        }
    }

    /**
     * Loads form state
     * Returns array with the following elements:
     * array('form_data' => <form data>, 'context' => <context>, 'password' => <password>, 'time_saved' => DateTime)
     * If form state is not found, returns null
     * @param string $formClass
     * @param string $formName
     * @param string $formIdent
     * @return array|null
     */
    public function loadFormState($formClass, $formName, $formIdent)
    {
        $rowSet = $this->tgwSavedForm->select(array(
            'site_name'     => $this->siteName,
            'form_class'    => $formClass,
            'form_name'     => $formName,
            'form_ident'    => $formIdent,
        ));
        if ($rowSet->count() == 1) {
            //Form state found
            $row    = $rowSet->current();
            $retVal = array(
                'form_data'     => Json::decode($row['form_data'], Json::TYPE_ARRAY),
                'context'       => Json::decode($row['context'], Json::TYPE_ARRAY),
                'password'      => $row['password'],
                'time_created'  => new DateTime($row['time_created']),
                'time_saved'    => new DateTime($row['time_saved']),
                'status'        => $row['status'],
            );
        } else {
            //Form state not found
            $retVal = null;
        }
        return $retVal;
    }

    /**
     * Modifies status of the saved form
     * Returns number of updated rows
     * @param string $formClass
     * @param string $formName
     * @param string $formIdent
     * @param int $status 1 = auto saved, 2 = user saved, 3 = sent to telem
     * @return int
     */
    public function modifyFormStatus($formClass, $formName, $formIdent, $status)
    {
        $affected       = $this->tgwSavedForm->update(array(
            'status'        => (int) $status,
        ), array(
            'site_name'     => $this->siteName,
            'form_class'    => $formClass,
            'form_name'     => $formName,
            'form_ident'    => $formIdent,
        ));
        return $affected;
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
        $formState  = $this->loadFormState($formClass, $formName, $formIdent);
        $exists     = is_array($formState);
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
}
