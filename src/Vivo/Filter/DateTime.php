<?php
namespace Vivo\Filter;

use Zend\Filter\AbstractFilter;

class DateTime extends AbstractFilter
{
    public function filter($value)
    {
        //Locale format full
        $format = 'd.m.Y H:i:s';
        $date   = \DateTime::createFromFormat($format, $value);
        //Locale format w/o seconds
        if ($date === false) {
            $format = 'd.m.Y H:i';
            $date   = \DateTime::createFromFormat($format, $value);
        }
        //Locale format date only
        if ($date === false) {
            $format = 'd.m.Y';
            $date   = \DateTime::createFromFormat($format, $value);
        }
        //Modified ISO format
        if ($date === false) {
            $format = 'Y-m-d H:i';
            $date   = \DateTime::createFromFormat($format, $value);
        }
        //ISO format full with timezone
        if ($date === false) {
            $format = 'Y-m-d\TH:i:sP';
            $date   = \DateTime::createFromFormat($format, $value);
        }
        //ISO format full w/o timezone
        if ($date === false) {
            $format = 'Y-m-d\TH:i:s';
            $date   = \DateTime::createFromFormat($format, $value);
        }
        //ISO format full w/o timezone and seconds
        if ($date === false) {
            $format = 'Y-m-d\TH:i';
            $date   = \DateTime::createFromFormat($format, $value);
        }
        //ISO format date only
        if ($date === false) {
            $format = 'Y-m-d';
            $date   = \DateTime::createFromFormat($format, $value);
        }
        if (! $date instanceof \DateTime) {
            $date = null;
        }
        return $date;
    }
}
