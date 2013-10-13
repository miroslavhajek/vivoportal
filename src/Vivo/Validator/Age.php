<?php
namespace Vivo\Validator;

use Vivo\Service\Initializer\FilterPluginManagerAwareInterface;

use Zend\ServiceManager\AbstractPluginManager;
use Zend\Validator\AbstractValidator;

use DateTime;

/**
 * Age
 * Validates age as compared to a reference or current date
 * Options:
 *
 * 'unit',
 * 'min',
 * 'max',
 * 'refDate'
 *
 * See respective properties for details
 *
 */
class Age extends AbstractValidator implements FilterPluginManagerAwareInterface
{
    /**#@+
     * Unit constant
     */
    const UNIT_SECOND   = 'second';
//    const UNIT_MINUTE   = 'minute';
//    const UNIT_HOUR     = 'hour';
//    const UNIT_DAY      = 'day';
//    const UNIT_MONTH    = 'month';
    const UNIT_YEAR     = 'year';
    /**#@-*/

    /**#@+
     * Error message keys
     */
    const ERR_MSG_TOO_OLD   = 'too_old';
    const ERR_MSG_TOO_YOUNG = 'too_young';
    /**#@-*/

    /**
     * Time units supported by this validator
     * @var array
     */
    protected $supportedUnits   = array(
        self::UNIT_SECOND,
//        self::UNIT_MINUTE,
//        self::UNIT_HOUR,
//        self::UNIT_DAY,
//        self::UNIT_MONTH,
        self::UNIT_YEAR,
    );

    /**
     * Unit to apply to min and max
     * @var string
     */
    protected $unit     = self::UNIT_YEAR;

    /**
     * Unit name translated
     * @var string
     */
    protected $unitTranslated;

    /**
     * Minimum age
     * @var int
     */
    protected $min;

    /**
     * Maximum age
     * @var int
     */
    protected $max;

    /**
     * Reference date - the age is calculated against this date
     * If not set, the current date is considered as reference date
     * @var DateTime
     */
    protected $refDate;

    /**
     * Filter plugin manager
     * @var AbstractPluginManager
     */
    protected $filterPluginManager;

    /**
     * Variables used in the error messages
     * @var array
     */
    protected $messageVariables     = array(
//        'unit'  => 'unit',
        'unit'  => 'unitTranslated',
        'min'   => 'min',
        'max'   => 'max',
    );

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
            self::ERR_MSG_TOO_YOUNG     => gettext_noop("The date/time value is younger than %min% (%unit%)"),
            self::ERR_MSG_TOO_OLD       => gettext_noop("The date/time value is older than %max% (%unit%)"),
        );
        parent::__construct($options);
    }

    /**
     * Returns true if and only if $value meets the validation requirements
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     * @param mixed $value
     * @throws Exception\InvalidArgumentException
     * @throws Exception\LogicException
     * @return bool
     */
    public function isValid($value)
    {
        $this->setValue($value);
        /** @var $dateTimeFilter \Vivo\Filter\DateTime */
        $dateTimeFilter = $this->filterPluginManager->get('Vivo\date_time');
        $valueObject    = $dateTimeFilter->filter($value);
        if (!$valueObject instanceof DateTime) {
            throw new Exception\InvalidArgumentException(
                sprintf("%s: Cannot convert datetime value '%s' to DateTime object", __METHOD__, $value));
        }
        $refDate    = $this->getRefDate();
        $interval   = $valueObject->diff($refDate, false);
        $seconds    = $refDate->getTimestamp() - $valueObject->getTimestamp();
        $valid      = true;
        switch ($this->unit) {
            case self::UNIT_SECOND:
                if (!is_null($this->min) && ($this->min > $seconds)) {
                    $this->error(self::ERR_MSG_TOO_YOUNG);
                    $valid  = false;
                }
                if (!is_null($this->max) && ($this->max < $seconds)) {
                    $this->error(self::ERR_MSG_TOO_OLD);
                    $valid  = false;
                }
                break;
            case self::UNIT_YEAR:
                if (!is_null($this->min) && ($this->min > $interval->y)) {
                    $this->error(self::ERR_MSG_TOO_YOUNG);
                    $valid  = false;
                }
                if (!is_null($this->max) && ($this->max < $interval->y)) {
                    $this->error(self::ERR_MSG_TOO_OLD);
                    $valid  = false;
                }
                break;
            default:
                throw new Exception\LogicException(
                    sprintf("%s: Processing for unit '%s' not implemented", __METHOD__, $this->unit));
        }
        return $valid;
    }

    /**
     * Sets the max allowed age
     * @param int $max
     */
    public function setMax($max)
    {
        $this->max = $max;
    }

    /**
     * Sets the minimum allowed age
     * @param int $min
     */
    public function setMin($min)
    {
        $this->min = $min;
    }

    /**
     * Sets the reference date
     * @param \DateTime $refDate
     */
    public function setRefDate(DateTime $refDate = null)
    {
        $this->refDate = $refDate;
    }

    /**
     * Sets unit used for min and max
     * @param string $unit
     * @throws Exception\InvalidArgumentException
     */
    public function setUnit($unit)
    {
        if (!in_array($unit, $this->supportedUnits)) {
            throw new Exception\InvalidArgumentException(sprintf("%s: Unit '%s' is not supported", __METHOD__, $unit));
        }
        $this->unit = $unit;
        $translator = $this->getTranslator();
        if ($translator) {
            $this->unitTranslated   = $translator->translate($unit);
        } else {
            //Translator not available
            $this->unitTranslated   = $unit;
        }
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
     * Sets Filter plugin manager
     * @param AbstractPluginManager $filterPluginManager
     * @return void
     */
    public function setFilterPluginManager(AbstractPluginManager $filterPluginManager)
    {
        $this->filterPluginManager  = $filterPluginManager;
    }
}
