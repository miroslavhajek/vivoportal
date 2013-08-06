<?php
namespace Vivo\Validator;

use Vivo\Service\Initializer\ValidatorPluginManagerAwareInterface;

use Zend\ServiceManager\AbstractPluginManager;
use Zend\Validator\AbstractValidator;
use Zend\Validator\InArray;

/**
 * InArrayDynamic
 * Checks if value is in a set of values which are retrieved dynamically based on the value of another field in context
 * The Zend\Validator\InArray is used for the actual validation
 *
 * Supported options:
 *
 * 'controlField' (mandatory)
 * 'haystacks' (mandatory)
 * 'resultOnMissingHaystack',
 * 'strict'
 *
 * See the respective properties for explanation
 */
class InArrayDynamic extends AbstractValidator implements ValidatorPluginManagerAwareInterface
{
    /**
     * Name of control field in context whose value governs which part of haystacks will be used as the actual haystack
     * @var string
     */
    protected $controlField             = null;

    /**
     * All haystacks used for validation
     * Array of arrays
     * array(
     *      'key1'  => array(<haystack values>),
     *      'key2'  => array(<haystack values>),...
     * )
     * key1, key2,... are values of the control field
     * @var array
     */
    protected $haystacks                = array();

    /**
     * What should be the result of the validation when the haystack for the current value of the control field
     * is not defined
     * When set to null, an exception will be thrown on missing haystack
     * @var bool|null
     */
    protected $resultOnMissingHaystack  = false;

    /**
     * Should strict comparison be made?
     * @var int
     */
    protected $strict                   = InArray::COMPARE_NOT_STRICT_AND_PREVENT_STR_TO_INT_VULNERABILITY;

    /**
     * Validator Plugin Manager
     * @var AbstractPluginManager
     */
    protected $validatorPluginManager;

    /**
     * Returns true if and only if $value meets the validation requirements
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     * @param  mixed $value
     * @param array|null $context Other form fields when invoked from form context
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
     * @throws Exception\ConfigException
     * @return bool
     */
    public function isValid($value, $context = null)
    {
        $this->setValue($value);
        if (!is_array($context)) {
            throw new Exception\InvalidArgumentException(sprintf("%s: context must be an array", __METHOD__));
        }
        if (is_null($this->controlField)) {
            throw new Exception\ConfigException(sprintf("%s: Control field must be set", __METHOD__));
        }
        if (!array_key_exists($this->controlField, $context)) {
            throw new Exception\RuntimeException(
                sprintf("%s: Control field '%s' missing in context", __METHOD__, $this->controlField));
        }
        $cfValue    = $context[$this->controlField];
        if (!array_key_exists($cfValue, $this->haystacks)) {
            //Haystack is missing (not defined) for the current value of the control field
            if (is_null($this->resultOnMissingHaystack)) {
                throw new Exception\RuntimeException(
                    sprintf("%s: Haystack is not defined for control field '%s' with value '%s'",
                        __METHOD__, $this->controlField, $cfValue));
            }
            $retVal = $this->resultOnMissingHaystack;
        } else {
            //Haystack is defined for the current value of the control field
            $haystack           = $this->haystacks[$cfValue];
            /** @var $inArrayValidator InArray */
            $inArrayValidator   = $this->validatorPluginManager->get('inArray', array(
                'haystack'  => $haystack,
                'recursive' => false,
                'strict'    => $this->strict,
            ));
            $retVal             = $inArrayValidator->isValid($value);
        }
        return $retVal;
    }

    /**
     * Sets control field name
     * @param string $controlField
     */
    public function setControlField($controlField)
    {
        $this->controlField = $controlField;
    }

    /**
     * Sets haystacks
     * @see $haystacks
     * @param array $haystacks
     */
    public function setHaystacks(array $haystacks)
    {
        $this->haystacks = $haystacks;
    }

    /**
     * Sets the strict option
     * @param int $strict
     */
    public function setStrict($strict)
    {
        $this->strict = $strict;
    }

    /**
     * Sets the desired result on missing haystack
     * Null causes an exception to be thrown
     * @param bool|null $resultOnMissingHaystack
     */
    public function setResultOnMissingHaystack($resultOnMissingHaystack)
    {
        $this->resultOnMissingHaystack = $resultOnMissingHaystack;
    }

    /**
     * Sets Validator plugin manager
     * @param AbstractPluginManager $validatorPluginManager
     * @return void
     */
    public function setValidatorPluginManager(AbstractPluginManager $validatorPluginManager)
    {
        $this->validatorPluginManager   = $validatorPluginManager;
    }
}
