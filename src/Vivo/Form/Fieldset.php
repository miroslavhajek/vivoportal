<?php
namespace Vivo\Form;

use Vivo\Form\Factory;

use Zend\Form\Fieldset as ZendFieldset;

class Fieldset extends ZendFieldset
{
    public function getFormFactory()
    {
        if (null === $this->factory) {
            $this->setFormFactory(new Factory());
        }

        return $this->factory;
    }

    /**
     * Extracts data from bound object and populates values
     */
    public function extractAndPopulate()
    {
        $values = $this->extract();
        $this->populateValues($values);
    }
}
