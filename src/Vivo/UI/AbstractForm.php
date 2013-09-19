<?php
namespace Vivo\UI;

use Vivo\Service\Initializer\InputFilterFactoryAwareInterface;
use Vivo\Service\Initializer\RequestAwareInterface;
use Vivo\Service\Initializer\RedirectorAwareInterface;
use Vivo\Service\Initializer\TranslatorAwareInterface;
use Vivo\Util\Redirector;
use Vivo\Form\Multistep\MultistepStrategyInterface;
use Vivo\UI\ZfFieldsetProviderInterface;

use Zend\Form\Fieldset as ZfFieldset;
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
                                                                  TranslatorAwareInterface,
                                                                  InputFilterFactoryAwareInterface,
                                                                  ZfFieldsetProviderInterface
{
    /**#@+
     * Events
     */
    const EVENT_LOAD_FROM_REQUEST_PRE   = 'load_from_request_pre';
    const EVENT_LOAD_FROM_REQUEST_POST  = 'load_from_request_post';
    /**#@-*/

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
     * When set to true, a hidden field containing URL of ajax validation action will be added to the form
     * @see $autoActAjaxValidationFieldName
     * @var bool
     */
    protected $autoAddActAjaxValidation = true;

    /**
     * When set to true, a hidden field indication if the form has been already submitted ('0'|'1')
     * @see $autoFormSubmittedFieldName
     * @var bool
     */
    protected $autoAddFormSubmitted     = true;

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
     * Translator instance
     * @var Translator
     */
    protected $translator;

    /**
     * Input filter factory
     * @var InputFilterFactory
     */
    protected $inputFilterFactory;

    /**
     * Multistep strategy
     * @var MultistepStrategyInterface
     */
    protected $multistepStrategy;

    /**
     * Name of the field containing the automatically added CSRF element
     * @var string
     */
    protected $autoCsrfFieldName                = 'csrf';

    /**
     * Name of the field containing the automatically added element for ajax validation action url
     * @see $autoAddActAjaxValidation
     * @var string
     */
    protected $autoActAjaxValidationFieldName   = 'act_ajax_validation';

    /**
     * Name of the field containing the automatically added element for ajax validation action url
     * @see $autoAddFormSubmitted
     * @var string
     */
    protected $autoFormSubmittedFieldName       = 'form_submitted';

    /**
     * Form data or an empty array when validation has not been performed yet
     * @var array
     */
    protected $formData     = array();

    /**
     * Whether or not validation has occurred
     * @var bool
     */
    protected $hasValidated = false;

    /**
     * Result of last validation operation
     * @var bool
     */
    protected $isValid      = false;

    /**
     * Has the form been submitted?
     * This property is initialized in loadFromRequest()
     * @var bool
     */
    protected $isFormSubmitted;

    /**
     * Value of act field in data obtained from POST/GET - used to assess if the form has been submitted or not
     * @var string
     */
    protected $actFieldInPostGet;

    /**
     * Get ZF form
     * @throws Exception\InvalidArgumentException
     * @return ZfForm
     */
    public function getForm()
    {
        if($this->form == null) {
            $this->form = $this->doGetForm();
            // TODO remove enctype setting when forms are refactored to fieldsets
            $this->form->setAttribute('enctype', 'multipart/form-data');
            if ($this->autoInjectInputFilterFactory) {
                $this->injectInputFilterFactory($this->form);
            }
            //Auto add CSRF field
            if ($this->autoAddCsrf) {
                $formName           = $this->form->getName();
                if (!$formName) {
                    throw new Exception\InvalidArgumentException(sprintf("%s: Form name not set", __METHOD__));
                }
                /* Csrf validator name is used as a part of session container name; This is to prevent csrf session
                container mix-up when there are more forms with csrf field on the same page. The unique Csrf element
                name (using $form->setWrapElements(true) is of no use here, as the session container name is constructed
                PRIOR to element preparation, thus the session container name is always constructed from the bare
                element name, which is not unique. Explicitly setting the csrf validator name to unique value solves
                this problem.
                */
                $csrfValidatorName  = 'csrf' . md5($formName . '_' . $this->autoCsrfFieldName);
                $csrf               = new \Vivo\Form\Element\Csrf($this->autoCsrfFieldName);
                $csrfValidator      = $csrf->getCsrfValidator();
                $csrfValidator->setName($csrfValidatorName);
                $csrfValidator->setTimeout($this->csrfTimeout);
                $this->form->add($csrf);
            }
            //Multistep strategy
            if ($this->multistepStrategy) {
                $this->multistepStrategy->setForm($this->form);
                $this->multistepStrategy->modifyForm();
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

    /**
     * Sets request instance
     * @param RequestInterface $request
     */
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
        $eventManager   = $this->getEventManager();
        $eventManager->trigger(self::EVENT_LOAD_FROM_REQUEST_PRE, $this);

        $form   = $this->getForm();
        $method = $this->getFormMethod();

        if($method == 'post') {
            $data = $this->request->getPost()->toArray();
        } else {
            $data = $this->request->getQuery()->toArray();
        }
        $data = ArrayUtils::merge($data, $this->request->getFiles()->toArray());

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
        //Store value of the act field
        $this->actFieldInPostGet    = null;
        if (isset($data['act'])) {
            $this->actFieldInPostGet    = $data['act'];
            //Unset act field to prevent mix up with an unrelated act field
            unset($data['act']);
        }

        //Remove form submitted flag from the data
        if ($this->autoAddFormSubmitted) {
            unset($data[$this->autoFormSubmittedFieldName]);
        }
        //Remove act_ajax_validation from the data
        if ($this->autoAddActAjaxValidation) {
            unset($data[$this->autoAddActAjaxValidation]);
        }
        $form->setData($data);
        $this->dataLoaded   = true;
        $eventManager->trigger(self::EVENT_LOAD_FROM_REQUEST_POST, $this, array('data' => $data));
    }

    /**
     * Returns method of the form
     * @return string
     */
    public function getFormMethod()
    {
        $method = strtolower($this->getForm()->getAttribute('method'));
        return $method;
    }

    /**
     * Returns if the form has been submitted or not
     * This method returns valid result only after a call to loadFromRequest() - if called earlier, returns null
     * @return bool|null
     */
    public function isFormSubmitted()
    {
        if (is_null($this->isFormSubmitted) && $this->dataLoaded) {
            $this->isFormSubmitted  = false;
            if (!is_null($this->actFieldInPostGet)) {
                $actParts   = explode('->', $this->actFieldInPostGet);
                if (count($actParts) >= 2) {
                    array_pop($actParts);
                    $formPath   = implode('->', $actParts);
                    if ($formPath == $this->getPath()) {
                        $this->isFormSubmitted  = true;
                    }
                }
            }
        }
        return $this->isFormSubmitted;
    }

    /**
     * Check if the form has been validated
     * @return bool
     */
    public function hasValidated()
    {
        return $this->hasValidated;
    }

    /**
     * Returns validation group which should be used for validation
     * Descendants may redefine if needed
     * @return mixed
     */
    protected function getValidationGroup()
    {
        if ($this->multistepStrategy) {
            $zfForm             = $this->getForm();
            $validationGroup    = $this->multistepStrategy->getValidationGroup($zfForm);
            if ($this->autoAddCsrf && ($validationGroup != FormInterface::VALIDATE_ALL) && (
                    (is_array($validationGroup) && !in_array($this->autoCsrfFieldName, $validationGroup))
                    || (is_string($validationGroup) && $validationGroup != $this->autoCsrfFieldName))) {
                //Csrf field is missing in validation group, add it
                if (!is_array($validationGroup)) {
                    $validationGroup    = array($validationGroup);
                }
                $validationGroup[]  = $this->autoCsrfFieldName;
            }
        } else {
            $validationGroup    = FormInterface::VALIDATE_ALL;
        }
        return $validationGroup;
    }

    /**
     * Validates the form and returns the validation result
     * @param bool $revalidate Force revalidation of the form even though it has been validated before
     * @param mixed $validationGroup If not set, $this->getValidationGroup() will be used to provide the VG
     * @return bool
     */
    public function isValid($revalidate = false, $validationGroup = null)
    {
        if ($revalidate) {
            $this->hasValidated = false;
        }
        if ($this->hasValidated) {
            return $this->isValid;
        }
        if (is_null($validationGroup)) {
            $validationGroup    = $this->getValidationGroup();
        }
        $this->isValid      = false;
        $this->formData     = array();
        $form               = $this->getForm();
        $form->setValidationGroup($validationGroup);
        $this->isValid      = $form->isValid();
        $this->hasValidated = true;
        $formData           = $form->getData(FormInterface::VALUES_AS_ARRAY);
        $this->setFormData($formData);
        return $this->isValid;
    }

    /**
     * Returns form data
     * @param bool $recursive Return also data from child fieldsets?
     * @throws Exception\DomainException
     * @return array
     */
    public function getData($recursive = true)
    {
//        if (!$this->hasValidated) {
//            throw new Exception\DomainException(sprintf('%s: cannot return data as validation has not yet occurred',
//                __METHOD__));
//        }
        $data   = $this->formData;
        if ($recursive) {
            foreach ($this->components as $component) {
                if ($component instanceof AbstractFieldset) {
                    $childName  = $component->getUnwrappedZfFieldsetName();
                    $childData  = $component->getFieldsetData($recursive);
                    $data[$childName]   = $childData;
                }
            }
        }
        return $data;
    }

    /**
     * Sets form data
     * @param array $formData
     */
    protected function setFormData(array $formData)
    {
        foreach ($this->components as $component) {
            if ($component instanceof AbstractFieldset) {
                $unwrapped          = $component->getUnwrappedZfFieldsetName();
                if (isset($formData[$unwrapped])) {
                    $childData  = $formData[$unwrapped];
                    //Remove the child data
                    unset($formData[$unwrapped]);
                } else {
                    $childData  = array();
                }
                $component->setFieldsetData($childData);
            }
        }
        $this->formData    = $formData;
    }

    /**
     * Validates a single form field
     * Facilitates single field AJAX validations
     * @param string $fieldName in array notation (eg 'fieldset1[fieldset2][fieldname]')
     * @param string $formData the whole form data
     * @param array $messages Validation messages are returned in this array
     * @return boolean
     */
    public function isFieldValid($fieldName, $formData, array &$messages)
    {
        $form       = $this->getForm();
        $fieldSpec  = $this->getFormDataFromArrayNotation($fieldName);
        $form->setValidationGroup($fieldSpec);
        if ($this->getForm()->wrapElements()) {
            $formData = reset($formData);
        }
        $form->setData($formData);
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
        if ($this->getForm()->wrapElements()) {
            array_shift($parts);
        }
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
        if ($this->getForm()->wrapElements()) {
            array_shift($parts);
        }
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
        $formData   = $this->request->getPost('formData');
        // parse data from request string if needed
        if (is_string($formData)) {
            parse_str($formData, $formData);
        }
        $messages   = array();
        $jsonModel  = new \Zend\View\Model\JsonModel();
        $isValid    = $this->isFieldValid($field, $formData, $messages);
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

    /**
     * Returns ZF Fieldset
     * @return ZfFieldset
     */
    public function getZfFieldset()
    {
        return $this->getForm();
    }

    /**
     * Returns ZF Form
     * @return ZfForm
     */
    public function getZfForm()
    {
        return $this->getForm();
    }

    /**
     * Sets name of this ZfForm
     * Use to override the underlying form name
     * @param string $zfFormName
     */
    public function setZfFormName($zfFormName)
    {
        $form   = $this->getForm();
        $form->setName($zfFormName);
    }

    /**
     * Returns name of this ZfForm
     * @return string
     */
    public function getZfFormName()
    {
        $form   = $this->getForm();
        return $form->getName();
    }

    /**
     * Sets multistep strategy
     * @param MultistepStrategyInterface $multistepStrategy
     */
    public function setMultistepStrategy(MultistepStrategyInterface $multistepStrategy = null)
    {
        $this->multistepStrategy = $multistepStrategy;
    }

    /**
     * Returns multistep strategy used on this form
     * @return MultistepStrategyInterface
     */
    public function getMultistepStrategy()
    {
        return $this->multistepStrategy;
    }

    /**
     * InitLate listener which loads data from request
     * This listener must run late to let subcomponents attach their fieldsets to the form before loading data
     */
    public function initLateLoadFromRequest()
    {
        if ($this->autoLoadFromRequest) {
            $this->loadFromRequest();
        }
    }

    /**
     * View listener
     * Prepares the form and passes it to the view model
     */
    public function viewListenerPrepareAndSetForm()
    {
        $form = $this->getForm();
        //By adding special fields (which are to be consumed only on front-end) here in a view listener,
        //they are not stored together with the form state
        //Auto add AJAX validation act field
        if ($this->autoAddActAjaxValidation) {
            $actAjaxValidation  = new \Vivo\Form\Element\Hidden($this->autoActAjaxValidationFieldName);
            $componentPath      = $this->getPath('ajaxValidateFormField');
            $actAjaxValidation->setValue($componentPath);
            $this->form->add($actAjaxValidation);
        }
        //Auto add form submitted field
        if ($this->autoAddFormSubmitted) {
            $formSubmitted      = new \Vivo\Form\Element\Hidden($this->autoFormSubmittedFieldName);
            $formSubmitted->setAttribute('value', $this->isFormSubmitted() ? '1' : '0');
            $this->form->add($formSubmitted);
        }
        //Prepare the form
        if ($this->autoPrepareForm) {
            $form->prepare();
        }
        $viewModel          = $this->getView();
        //Set form to view
        $viewModel->form    = $form;
        //Set current step name
        if ($this->getMultistepStrategy()) {
            $msStrategy = $this->getMultistepStrategy();
            $viewModel->multistepInfo = array(
                'currentStep' => $msStrategy->getStep(),
            );
        } else {
            //No multistep strategy available, set multistep info to null
            $viewModel->multistepInfo = null;
        }
    }

    /**
     * Attaches listeners
     * @return void
     */
    public function attachListeners()
    {
        parent::attachListeners();
        $eventManager   = $this->getEventManager();
        //Init LATE
        $eventManager->attach(ComponentEventInterface::EVENT_INIT_LATE, array($this, 'initLateLoadFromRequest'));
        //View
        $eventManager->attach(ComponentEventInterface::EVENT_VIEW, array($this, 'viewListenerPrepareAndSetForm'));
    }
}
