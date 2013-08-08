<?php
namespace Vivo\Form\Multistep;

use Vivo\Form\Exception;

use Zend\Form\Form;
use Zend\Form\FormInterface;

/**
 * MultistepStrategyInterface
 */
interface MultistepStrategyInterface
{
    /**
     * Sets the form this multistep strategy operates on
     * @param FormInterface $form
     */
    public function setForm(FormInterface $form);

    /**
     * Sets steps the form has
     * @param array $steps
     * @throws \Vivo\Form\Exception\InvalidArgumentException
     */
    public function setSteps(array $steps);

    /**
     * Modifies the form to be usable as a multi-step form
     */
    public function modifyForm();

    /**
     * Returns if the specified value is a valid step
     * @param string $step
     * @return bool
     */
    public function isStepNameValid($step);

    /**
     * Returns current step identification from form
     * @return string
     */
    public function getStep();

    /**
     * Returns if the step is before current step
     *
     * @param string $stepName name of the step
     * @return bool
     */
    public function isBeforeCurrentStep($stepName);

    /**
     * Returns if the step is after current step
     *
     * @param string $stepName name of the step
     * @return bool
     */
    public function isAfterCurrentStep($stepName);

    /**
     * Sets step identification into the form
     * @param string $step
     * @throws \Vivo\Form\Exception\RuntimeException
     */
    public function setStep($step);

    /**
     * Returns identification of the step to go to from the form
     * @throws \Vivo\Form\Exception\RuntimeException
     * @return string
     */
    public function getGotoStep();

    /**
     * Returns array of steps following after the current step
     * For last step returns an empty array
     * When $currentStep == null, returns all steps
     * @param string|null $currentStep
     * @throws \Vivo\Form\Exception\InvalidArgumentException
     * @return array()
     */
    public function getNextSteps($currentStep = null);

    /**
     * Returns next step after the specified one or the first step when $currentStep == null
     * Returns null when $currentStep is the last step
     * @param string|null $currentStep
     * @throws \Vivo\Form\Exception\InvalidArgumentException
     * @return null|string
     */
    public function getNextStep($currentStep = null);

    /**
     * Returns array of steps preceding the current step
     * For first step returns an empty array
     * When $currentStep == null, returns all steps
     * @param string|null $currentStep
     * @throws \Vivo\Form\Exception\InvalidArgumentException
     * @return array()
     */
    public function getPreviousSteps($currentStep = null);

    /**
     * Returns step preceding the specified one or the last step when $currentStep == null
     * Returns null when $currentStep is the first step
     * @param string|null $currentStep
     * @throws \Vivo\Form\Exception\InvalidArgumentException
     * @return null|string
     */
    public function getPreviousStep($currentStep = null);

    /**
     * Returns validation group for the current step
     * @param \Zend\Form\Form $form
     * @return mixed
     */
    public function getValidationGroup();

    /**
     * Resets the goto_step hidden field
     */
    public function resetGotoStep();

    /**
     * Advances one step forward
     * If there are no more steps, returns null, otherwise returns name of the next step
     * @return string|null
     */
    public function next();

    /**
     * Returns one step back
     * If there are no more steps, returns null, otherwise returns name of the previous step
     * @return string|null
     */
    public function back();
}