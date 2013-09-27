<?php
namespace Vivo\Service\Initializer;

use Vivo\CMS\Api\FormUtil as FormUtilApi;

/**
 * FormUtilAwareInterface
 */
interface FormUtilAwareInterface
{
    /**
     * Sets FormUtil API
     * @param FormUtilApi $formUtilApi
     * @return void
     */
    public function setFormUtilApi(FormUtilApi $formUtilApi);
}