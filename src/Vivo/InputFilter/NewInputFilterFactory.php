<?php
namespace Vivo\InputFilter;

use Vivo\InputFilter\VivoInputFilter;

/**
 * NewInputFilterFactory
 * Creates new instances of properly configured Vivo input filters
 */
class NewInputFilterFactory
{
    /**
     * @var Factory
     */
    protected $inputFilterFactory;

    /**
     * Constructor
     * @param Factory $inputFilterFactory
     */
    public function __construct(Factory $inputFilterFactory)
    {
        $this->inputFilterFactory   = $inputFilterFactory;
    }

    /**
     * Creates, configures and returns a new instance of Vivo Input Filter
     * @return VivoInputFilter
     */
    public function create()
    {
        $inputFilter    = new VivoInputFilter();
        $inputFilter->setFactory($this->inputFilterFactory);
        return $inputFilter;
    }
}
