<?php
namespace Vivo\InputFilter\Condition;

use Vivo\InputFilter\Exception;

use Zend\Stdlib\ArrayUtils;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Traversable;

/**
 * MultiConditionAnd
 * Condition built from multiple other conditions connected with logical AND
 */
class MultiConditionAnd extends AbstractCondition implements ServiceLocatorAwareInterface
{
    /**
     * Condition plugin manager
     * @var ConditionPluginManager
     */
    protected $conditionPluginManager;

    /**
     * Array of condition definitions
     * Without references to conditional validators (they are ignored)
     * @var array
     */
    protected $conditions = array();

    /**
     * Constructor
     * @param array|Traversable $options
     * @throws \Vivo\InputFilter\Exception\ConfigException
     */
    public function __construct($options = null)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }
        if (!is_array($options)) {
            $options    = array();
        }
        if (!isset($options['conditions'])) {
            throw new Exception\ConfigException(sprintf("%s: Conditions not set", __METHOD__));
        }
        $this->conditions = $options['conditions'];
        unset($options['conditions']);
        parent::__construct($options);
    }

    /**
     * Returns condition value
     * @param array $data
     * @return boolean
     */
    public function getConditionValue(array $data)
    {
        $result = true;
        foreach ($this->conditions as $condition) {
            $cond   = $this->getServiceLocator()->get($condition['name'], $condition['options']);
            $result = $result && $cond->getConditionValue($data);
            if ($result == false) {
                return false;
            }
        }
        return $result;
    }

    /**
     * Set service locator
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->conditionPluginManager = $serviceLocator;
    }

    /**
     * Get service locator
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->conditionPluginManager;
    }
}
