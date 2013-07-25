<?php
namespace Vivo\UI;

use Vivo\Service\Initializer\InputFilterFactoryAwareInterface;
use Vivo\Service\Initializer\RequestAwareInterface;
use Vivo\Service\Initializer\RedirectorAwareInterface;
use Vivo\Service\Initializer\TranslatorAwareInterface;
use Vivo\Util\Redirector;

use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Form\FormInterface;
use Zend\Form\Form as ZfForm;
use Zend\Http\PhpEnvironment\Request;
use Zend\Stdlib\RequestInterface;
use Zend\I18n\Translator\Translator;
use Zend\InputFilter\Factory as InputFilterFactory;
use Zend\Stdlib\ArrayUtils;

/**
 * Form
 * Base abstract Vivo Form
 */
abstract class AbstractForm extends ComponentContainer implements RequestAwareInterface,
                                                                  RedirectorAwareInterface,
                                                                  EventManagerAwareInterface,
                                                                  TranslatorAwareInterface,
                                                                  InputFilterFactoryAwareInterface
{
    /**
     * @var ZfForm
     */
    private $form;

    /**
     * @var Request
     */
    protected $request;

    /**
     * Redirector instance
     * @var Redirector
     */
    protected $redirector;

    /**
     * Has the data been loaded from request?
     * @var bool
     */
    private $dataLoaded             = false;

    /**
     * When set to true, the form will be automatically prepared in view() method
     * Redefine in descendant if necessary
     * @var bool
     */
    protected $autoPrepareForm      = true;

    /**
     * When set to true, CSRF protection will be automatically added to the form
     * Redefine in descendant if necessary
     * @var bool
     */
    protected $autoAddCsrf          = true;

    /**
     * When set to true, data will be automatically loaded to the form from request
     * Redefine in descendant if necessary
     * @var bool
     */
    protected $autoLoadFromRequest  = true;

    /**
     * When set to true, the customized InputFilterFactory will be injected into the form
     * @var bool
     */
    protected $autoInjectInputFilterFactory = true;

    /**
     * TTL for CSRF token
     * Redefine in descendant if necessary
     * @var int|null
     */
    protected $csrfTimeout          = 300;

    /**
     * If set to true, data will be reloaded from request every time the loadFromRequest() method is called
     * Otherwise the data is not reloaded from request on subsequent calls to loadFromRequest()
     * @var bool
     */
    protected $forceLoadFromRequest = true;

    /**
     * Event Manager
     * @var EventManagerInterface
     */
    protected $events;

    /**
     * Translator instance
     * @var Translator
     */
    protected $translator;

    /**
     * Input filter factory
     * @var InputFilterFactory
     */
    protected $inputFilterFactory;

    public function init()
    {
        parent::init();
        //Load form data from request
        if ($this->autoLoadFromRequest) {
            $this->loadFromRequest();
        }
    }

    /**
     * Returns view model or string to display directly
     * @return \Zend\View\Model\ModelInterface|string
     */
    public function view()
    {
        $form = $this->getForm();
        //Prepare the form
        if ($this->autoPrepareForm) {
            $form->prepare();
        }
        //Set form to view
        $this->getView()->form = $form;
        return parent::view();
    }

    /**
     * Get ZF form
     * @throws Exception\InvalidArgumentException
     * @return ZfForm
     */
    protected function getForm()
    {
        if($this->form == null) {
            $this->form = $this->doGetForm();
            // TODO remove enctype setting when forms are refactored to fieldsets
            $this->form->setAttribute('enctype', 'multipart/form-data');
            if ($this->autoInjectInputFilterFactory) {
                $this->injectInputFilterFactory($this->form);
            }
            if ($this->autoAddCsrf) {
                //Add CSRF field
                $formName           = $this->form->getName();
                if (!$formName) {
                    throw new Exception\InvalidArgumentException(sprintf("%s: Form name not set", __METHOD__));
                }
                $elementName        = 'csrf';
                /* Csrf validator name is used as a part of session container name; This is to prevent csrf session
                container mix-up when there are more forms with csrf field on the same page. The unique Csrf element
                name (using $form->setWrapElements(true) is of no use here, as the session container name is constructed
                PRIOR to element preparation, thus the session container name is always constructed from the bare
                element name, which is not unique. Explicitly setting the csrf validator name to unique value solves
                this problem.
                */
                $csrfValidatorName  = 'csrf' . md5($formName . '_' . $elementName);
                $csrf               = new \Vivo\Form\Element\Csrf($elementName);
                $csrfValidator      = $csrf->getCsrfValidator();
                $csrfValidator->setName($csrfValidatorName);
                $csrfValidator->setTimeout($this->csrfTimeout);
                $this->form->add($csrf);
            }
        }
        return $this->form;
    }

    protected function resetForm()
    {
        $this->form = null;
    }

    /**
     * Creates ZF form and returns it
     * Factory method
     * @return ZfForm
     */
    abstract protected function doGetForm();

    public function setRequest(RequestInterface $request)
    {
        $this->request  = $request;
    }

    /**
     * Injects redirector
     * @param \Vivo\Util\Redirector $redirector
     * @return void
     */
    public function setRedirector(Redirector $redirector)
    {
        $this->redirector   = $redirector;
    }

    /**
     * Loads data into the form from the HTTP request
     * Loads GET as well as POST data (POST wins)
     */
    public function loadFromRequest()
    {
        if ($this->dataLoaded && !$this->forceLoadFromRequest) {
            return;
        }
        $data   = $this->request->getQuery()->toArray();
        $data   = ArrayUtils::merge($data, $this->request->getPost()->toArray());
        $data   = ArrayUtils::merge($data, $this->request->getFiles()->toArray());

        //Unset act field to prevent mix up with an unrelated act field
        unset($data['act']);

        $form   = $this->getForm();

        //If form elements are wrapped with the form name, extract only this part of the GET/POST data
        if ($form->wrapElements()) {
            $formName   = $form->getName();
            if (!$formName) {
                throw new Exception\LogicException(sprintf("%s: Form name not set", __METHOD__));
            }
            if (isset($data[$formName])) {
                $data   = $data[$formName];
            } else {
                $data   = array();
            }
        }

        $form->setData($data);
        $this->dataLoaded   = true;
    }

    /**
     * Validates a single form field
     * Facilitates single field AJAX validations
     * @param string $fieldName in array notation (eg 'fieldset1[fieldset2][fieldname]')
     * @param string $fieldValue Value being tested
     * @param array $messages Validation messages are returned in this array
     * @return boolean
     */
    public function isFieldValid($fieldName, $fieldValue, array &$messages)
    {
        $form       = $this->getForm();
        $fieldSpec  = $this->getFormDataFromArrayNotation($fieldName);
        $data       = $this->getFormDataFromArrayNotation($fieldName, $fieldValue);
        $form->setValidationGroup($fieldSpec);
        $form->setData($data);
        //Store the current bind on validate flag setting and set it to manual
        $bindOnValidateFlag = $form->bindOnValidate();
        $form->setBindOnValidate(FormInterface::BIND_MANUAL);
        //Validate the field
        $valid  = $form->isValid();
        //Restore the original bind on validate setting
        $form->setBindOnValidate($bindOnValidateFlag);
        if (!$valid) {
            $formMessages   = $form->getMessages();
            $fieldMessages  = $this->getFieldMessages($formMessages, $fieldName);
            $messages   = array_merge($messages, $fieldMessages);
        }
        //Reset the validation group to whole form
        $form->setValidationGroup(FormInterface::VALIDATE_ALL);
        return $valid;
    }

    /**
     * Returns field specification or field data from an array notation of a field
     * getFormDataFromArrayNotation('fieldset1[fieldset2][fieldname]', 'valueX') returns
     * array(
     *      'fieldset1' => array(
     *          'fieldset2' => array('fieldname' => 'valueX')
     *      )
     * )
     * getFormDataFromArrayNotation('fieldset1[fieldset2][fieldname]') returns
     * array(
     *      'fieldset1' => array(
     *          'fieldset2' => array('fieldname')
     *      )
     * )
     * For 'fieldname' returns array('fieldname')
     * @param string $arrayNotation Field name in array notation
     * @param string $value Field value
     * @return array
     */
    protected function getFormDataFromArrayNotation($arrayNotation, $value = null)
    {
        $parts          = $this->getPartsFromArrayNotation($arrayNotation);
        $fieldName      = array_pop($parts);
        if (is_null($value)) {
            $fieldSpec      = array($fieldName);
        } else {
            $fieldSpec      = array($fieldName => $value);
        }
        $reversed   = array_reverse($parts);
        foreach ($reversed as $fieldset) {
            $fieldSpec  = array($fieldset => $fieldSpec);
        }
        return $fieldSpec;
    }

    /**
     * Returns error messages related to the specified field
     * @param array $messages Error messages for the whole form
     * @param string $fieldName In array notation ('fieldset1[fieldset2][fieldname]')
     * @return array
     */
    protected function getFieldMessages(array $messages, $fieldName)
    {
        $parts  = $this->getPartsFromArrayNotation($fieldName);
        foreach ($parts as $part) {
            if (!array_key_exists($part, $messages)) {
                return array();
            }
            $messages   = $messages[$part];
        }
        return $messages;
    }

    /**
     * Returns array of parts exploded from array notation
     * For 'fieldset1[fieldset2][fieldname]' returns array('fieldset1', 'fieldset2', 'fieldname')
     * @param string $arrayNotation
     * @return array
     */
    protected function getPartsFromArrayNotation($arrayNotation)
    {
        $arrayNotation  = str_replace(']', '', $arrayNotation);
        $parts          = explode('[', $arrayNotation);
        return $parts;
    }

    /**
     * Sets eventmanager
     * @param EventManagerInterface $eventManager
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $this->events = $eventManager;
    }

    /**
     * Returns eventmanager.
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        return $this->events;
    }

    /**
     * Injects translator
     * @param \Zend\I18n\Translator\Translator $translator
     */
    public function setTranslator(Translator $translator)
    {
        $this->translator   = $translator;
    }

    /**
     * Sets the input filter factory
     * @param InputFilterFactory $inputFilterFactory
     * @return void
     */
    public function setInputFilterFactory(InputFilterFactory $inputFilterFactory)
    {
        $this->inputFilterFactory   = $inputFilterFactory;
    }

    /**
     * Performs validity check on a single form field, returns JSON
     * @return \Zend\View\Model\JsonModel
     */
    public function ajaxValidateFormField()
    {
        $field      = $this->request->getPost('field');
        $value      = $this->request->getPost('value');
        $messages   = array();
        $jsonModel  = new \Zend\View\Model\JsonModel();
        $isValid    = $this->isFieldValid($field, $value, $messages);
        if ($isValid) {
            //Form field is valid
            $jsonModel->setVariable('valid', true);
        } else {
            //Form field not valid
            $jsonModel->setVariable('valid', false);
        }
        $jsonModel->setVariable('messages', $messages);
        return $jsonModel;
    }

    /**
     * Injects input filter factory into the form's form factory
     * @param ZfForm $form
     */
    protected function injectInputFilterFactory(ZfForm $form)
    {
        $formFactory    = $form->getFormFactory();
        $formFactory->setInputFilterFactory($this->inputFilterFactory);
    }
}
