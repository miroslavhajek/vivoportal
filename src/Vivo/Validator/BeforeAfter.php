<?php
namespace Vivo\Validator;

use Vivo\Service\Initializer\FilterPluginManagerAwareInterface;

use Zend\Validator\AbstractValidator;
use Zend\ServiceManager\AbstractPluginManager;

use DateTime;

/**
 * BeforeAfter
 * Checks if the value is before or after the reference date
 * Options:
 *
 * 'type'
 * 'refDate'
 *
 * See the respective properties for details
 */
class BeforeAfter extends AbstractValidator implements FilterPluginManagerAwareInterface
{
    /**#@+
     * Validation type constant
     */
    const TYPE_BEFORE   = 'before';
    const TYPE_AFTER    = 'after';
    /**#@-*/

    /**#@+
     * Error message keys
     */
    const ERR_MSG_NOT_BEFORE    = 'not_before';
    const ERR_MSG_NOT_AFTER     = 'not_after';
    /**#@-*/

    /**
     * Supported comparison types
     * @var array
     */
    protected $supportedTypes   = array(
        self::TYPE_BEFORE,
        self::TYPE_AFTER,
    );

    /**
     * Reference date
     * If not set, the current date is considered as reference date
     * @var DateTime
     */
    protected $refDate;

    /**
     * Type of comparison (self::TYPE_...)
     * @var string
     */
    protected $type;

    /**
     * Filter plugin manager
     * @var AbstractPluginManager
     */
    protected $filterPluginManager;

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
            self::ERR_MSG_NOT_BEFORE    => gettext_noop("The date/time value is not before the reference date/time"),
            self::ERR_MSG_NOT_AFTER     => gettext_noop("The date/time value is not after the reference date/time"),
        );
        parent::__construct($options);
    }

    /**
     * Returns true if and only if $value meets the validation requirements
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     * @param  mixed $value
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
     * @return bool
     */
    public function isValid($value)
    {
        $this->setValue($value);
        if (is_null($this->type)) {
            throw new Exception\RuntimeException(sprintf("%s: Comparison type not set", __METHOD__));
        }
        /** @var $dateTimeFilter \Vivo\Filter\DateTime */
        $dateTimeFilter = $this->filterPluginManager->get('Vivo\date_time');
        $valueObject    = $dateTimeFilter->filter($value);
        if (!$valueObject instanceof DateTime) {
            throw new Exception\InvalidArgumentException(
                sprintf("%s: Cannot convert datetime value '%s' to DateTime object", __METHOD__, $value));
        }
        $refDate    = $this->getRefDate();
        if ($this->type == self::TYPE_BEFORE && ($valueObject >= $refDate)) {
            $this->error(self::ERR_MSG_NOT_BEFORE);
            $valid  = false;
        } elseif ($this->type == self::TYPE_AFTER && ($valueObject <= $refDate)) {
            $this->error(self::ERR_MSG_NOT_AFTER);
            $valid  = false;
        } else {
            $valid  = true;
        }
        return $valid;
    }

    /**
     * Sets Filter plugin manager
     * @param AbstractPluginManager $filterPluginManager
     * @return void
     */
    public function setFilterPluginManager(AbstractPluginManager $filterPluginManager)
    {
        $this->filterPluginManager  = $filterPluginManager;
    }

    /**
     * Sets reference date
     * @param \DateTime $refDate
     */
    public function setRefDate($refDate = null)
    {
        $this->refDate = $refDate;
    }

    /**
     * Returns reference date
     * @return \DateTime
     */
    public function getRefDate()
    {
        if ($this->refDate) {
            $refDate    = $this->refDate;
        } else {
            $refDate    = new DateTime();
        }
        return $refDate;
    }

    /**
     * Sets comparison type
     * @param string $type
     */
    public function setType($type)
    {
        if (!in_array($type, $this->supportedTypes)) {
            throw new Exception\InvalidArgumentException(
                sprintf("%s: Unsupported comparison type '%s'", __METHOD__, $type));
        }
        $this->type = $type;
    }
}
