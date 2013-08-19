<?php
namespace Vivo\InputFilter\Condition;

use Vivo\InputFilter\Exception;

use Zend\Stdlib\ArrayUtils;

use Traversable;

/**
 * InArray
 * Condition returning true when the value in the field is among the specified set of values
 */
class InArray extends Input
{
    /**
     * Input preset to configure the Input condition to function as Identical validator
     * @var array
     */
    protected $inputPreset  = array(
        'validators'    => array(
            'inArray'     => array(
                'name'  => 'inArray',
                'options'   => array(
                    //'haystack'   => array(),
                    //'strict'    => false,
                    //'recursive' => false,
                ),
            ),
        ),
    );

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
        if (!isset($options['haystack'])) {
            throw new Exception\ConfigException(sprintf("%s: Haystack not set", __METHOD__));
        }
        $this->inputPreset['validators']['inArray']['options']['haystack'] = $options['haystack'];
        unset($options['haystack']);
        if (isset($options['strict'])) {
            $this->inputPreset['validators']['inArray']['options']['strict'] = $options['strict'];
            unset($options['strict']);
        }
        if (isset($options['recursive'])) {
            $this->inputPreset['validators']['inArray']['options']['recursive'] = $options['recursive'];
            unset($options['recursive']);
        }
        $options['inputConfig'] = $this->inputPreset;
        parent::__construct($options);
    }
}
