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

//    /**
//     * Data structure saved for a form in session
//     * @var array
//     */
//    protected $sessionFormInfoTemplate  = array(
//        'ident'         => null,
//        'isSubmitted'   => null,
//    );

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
     * @param string $password
     */
    public function saveFormState($formClass,
                                  $formName,
                                  $formIdent,
                                  array $formData,
                                  array $context = null,
                                  $password = null)
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
                'password'      => $password,
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
                'password'      => $password,
                'time_saved'    => $now,
                'form_data'     => $dataSerialized,
                'context'       => $contextSerialized,
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
                'time_saved'    => new DateTime($row['time_saved']),
            );
        } else {
            //Form state not found
            $retVal = null;
        }
        return $retVal;
    }

    /**
     * Returns if a saved form state exists for the given form
     * @param string $formClass
     * @param string $formName
     * @param string $formIdent
     * @param string|null $password
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
