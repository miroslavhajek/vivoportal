<?php
namespace Vivo\Form;

/**
 * ValidationResultAwareInterface
 */
interface ValidationResultAwareInterface
{
    /**
     * Sets result of the last validation, null means validation not performed
     * @param bool|null $validationResult
     * @return void
     */
    public function setValidationResult($validationResult);

    /**
     * Returns result of the last validation, null means validation not performed
     * @return bool|null
     */
    public function getValidationResult();
}