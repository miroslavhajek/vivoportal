<?php
namespace Vivo\Validator;

use Vivo\InputFilter\Condition\ConditionAwareInterface;

use Zend\Validator\AbstractValidator;
use Zend\Validator\Exception;

/**
 * VivoInvalid
 * VivoInvalid validator always returns false
 * It is typically used in conjunction with the AllEmpty condition to invalidate inputs when all of them are empty
 */
class VivoInvalid extends AbstractValidator implements ConditionAwareInterface
{
    /**
     * Message key
     */
    const INVALID   = 'invalid';

    /**
     * Error message templates
     * Templates are set in constructor to enable xgettext parsing (gettext_noop)
     * @var array
     */
    protected $messageTemplates;

    /**
     * Constructor
     * @param array|Traversable|null $options
     */
    public function __construct($options = null)
    {
        $this->messageTemplates = array(
            self::INVALID => gettext_noop("The input is invalid"),
        );
        parent::__construct($options);
    }

    /**
     * Value of the condition
     * @var boolean
     */
    private $conditionValue;

    /**
     * Sets the current value of the condition
     * @param bool $condition
     * @return void
     */
    public function setConditionValue($condition)
    {
        $this->conditionValue   = (bool) $condition;
    }

    /**
     * Returns true if and only if $value meets the validation requirements
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     * @param  mixed $value
     * @return bool
     * @throws Exception\RuntimeException If validation of $value is impossible
     */
    public function isValid($value)
    {
        $this->setValue($value);
        if ($this->conditionValue) {
            $valid  = false;
            $this->error(self::INVALID);
        } else {
            $valid  = true;
        }
        return $valid;
    }
}
