<?php

namespace Vivo\Validator;

use Zend\Validator\AbstractValidator;
use Zend\Validator\Date;
use Zend\Validator\Exception;

class PersonalNumberCZ extends AbstractValidator
{

    /**
     * Service Locator class
     * @var \Zend\ServiceManager\ServiceLocatorAwareInterface
     */
    protected $sm;

    const INVALID = 'PersonalNumberCZInvalid';

    protected $messageTemplates = array(
        self::INVALID => "Invalid Personal Number",
    );

    public function isValid($value, $context = null)
    {

        $pn1 = substr($value, 0, 6);

        // Get Date from Personal Number (+ woman's modulo)
        $date1 = $this->getDate($pn1);

        // Date check
        $date = new Date();

        $date->setFormat('d.m.Y');
        if (!$date->isValid($date1)) {
            $this->error(self::INVALID);
            return false;
        }

        // Check of the Number behind the Slash and of the complete Personal Number
        $rok = (int) substr($value, 0, 2);

        if ($rok >= 54) {
            // Check division by 11
            if ((floor($value/11) != $value/11)) {
                $this->error(self::INVALID);
                return false;
            }
        } else {
            // Check of the Number behind the Slash - must have only 3 Numbers, PN then 9 Numbers
            if (strlen($value) != 9) {
                $this->error(self::INVALID);
                return false;
            }
        }

        return true;
    }


    /**
     * Returns Date from Czech Personal Number in Format "d.m.Y"
     * @param string $rc
     * @return string
     */
    public function getDate($pn)
    {
        $date = preg_replace_callback('/^([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{3,4})?/',
            function($matches) {
                return sprintf('%02d.%02d.%04d', $matches[3], ($matches[2]%50), "19{$matches[1]}");
            }, $pn);

        return $date;
    }

}