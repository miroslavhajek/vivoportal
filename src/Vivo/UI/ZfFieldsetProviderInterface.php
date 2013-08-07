<?php
namespace Vivo\UI;

use Zend\Form\Fieldset as ZfFieldset;

/**
 * ZfFieldsetProviderInterface
 * Classes implementing this interface are capable of providing a Zend Framework Fieldset object
 */
interface ZfFieldsetProviderInterface
{
    /**
     * Returns ZF Fieldset
     * @return ZfFieldset
     */
    public function getZfFieldset();
}