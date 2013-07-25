<?php
namespace Vivo\Form\View;

use Vivo\Form\Exception;

use Zend\Form\ElementInterface;

/**
 * Class HelperUtils
 */
class HelperUtils
{
    /**
     * If the 'id' attribute of the element is not defined, it is set to equal the element's name value
     * //TODO - escape html attr?
     * @param ElementInterface $element
     */
    public function addIdAttributeIfMissing(ElementInterface $element)
    {
        if (!$element->getAttribute('id')) {
            $element->setAttribute('id', $element->getName());
        }
    }

    /**
     * Adds the specified class to the element
     * Removes duplicate words from the class attribute
     * @param string|array|null $class
     * @param ElementInterface $element
     * @throws \Vivo\Form\Exception\InvalidArgumentException
     */
    public function addClass($class, ElementInterface $element)
    {
        if (is_array($class)) {
            //A single element in the array might contain multiple words - process the array the same way as a string
            $class  = implode(' ', $class);
        } elseif (is_null($class)) {
            $class  = '';
        }
        if (!is_string($class)) {
            throw new Exception\InvalidArgumentException(sprintf("%s: class may be either string or an array",
                __METHOD__));
        }
        $class              = $this->getUniqueWords($class);
        $currentClass       = $element->getAttribute('class');
        $currentClassWords  = $this->getUniqueWords($currentClass);
        $merged             = array_merge($currentClassWords, $class);
        if (count($merged)) {
            $mergedUnique       = array_values($merged);
            $mergedClass        = implode(' ', $mergedUnique);
            $element->setAttribute('class', $mergedClass);
        }
    }

    /**
     * Splits the text into individual unique words and returns them in an array
     * @param string $text
     * @return array
     */
    protected function getUniqueWords($text)
    {
        $words  = explode(' ', $text);
        $words  = array_values($words);
        $key    = array_search('', $words);
        if ($key !== false) {
            unset($words[$key]);
        }
        return $words;
    }
}
