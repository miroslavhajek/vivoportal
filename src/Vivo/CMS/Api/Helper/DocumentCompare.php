<?php
namespace Vivo\CMS\Api\Helper;

use Vivo\CMS\Model\Folder;
use Vivo\Transliterator\TransliteratorInterface;

/**
 * Document helper for document comparison of two documents.
 */
class DocumentCompare
{

    /**
     * Transliterator for unicode string comparison
     * @var TransliteratorInterface
     */
    protected $transliteratorMbStringCompare;

    /**
     * Constructor
     * @param \Vivo\Transliterator\TransliteratorInterface $transliteratorMbStringCompare
     */
    public function __construct(TransliteratorInterface $transliteratorMbStringCompare)
    {
        $this->transliteratorMbStringCompare = $transliteratorMbStringCompare;
    }


    /**
     * Returns document/folder
     * @param array|Folder $document
     * @return Folder
     */
    protected function getDocument($document)
    {
        return is_array($document) ? $document['doc'] : $document;
    }

    /**
     * Return properties of document pair
     * @param array|Folder $doc1
     * @param array|Folder $doc2
     * @param string $propertyName
     * @return array
     */
    protected function getPropertiesToCompare($doc1, $doc2, $propertyName)
    {
        return array(
            $this->getPropertyByName($this->getDocument($doc1), $propertyName),
            $this->getPropertyByName($this->getDocument($doc2), $propertyName),
        );
    }


    /**
     * Parses criteria string into array.
     * 'property_name' and 'sort_direction' properties are extracted
     * @param string $criteriaString
     * @return array
     */
    protected function parseCriteriaString($criteriaString)
    {
        $criteria = array();
        if(strpos($criteriaString, ":") !== false) {
            $criteria['property_name'] = substr($criteriaString, 0,  strpos($criteriaString,':'));
            $criteria['sort_direction'] = substr($criteriaString, strpos($criteriaString,':')+1);
        } else {
            $criteria['property_name'] = $criteriaString;
            $criteria['sort_direction'] = 'asc';
        }
        $criteria['sort_direction'] = $criteria['sort_direction'] == 'desc' ? SORT_DESC : SORT_ASC;
        return $criteria;
    }

    /**
     * Returns document property (generic getter)
     * @param Folder $document
     * @param string $property
     * @return mixed
     */
    protected function getPropertyByName(Folder $document, $property)
    {
        $getter = sprintf('get%s', ucfirst($property));
        return method_exists($document, $getter) ? $document->$getter() : null;
    }

    /**
     * Compares two documents based on given criteria
     * @param array|Folder $doc1
     * @param array|Folder $doc2
     * @param string $criteriaString
     * @return int Standard comparison output
     * @see http://cz2.php.net/manual/en/function.strcmp.php
     */
    public function compare($doc1, $doc2, $criteriaString)
    {
        $criteria = $this->parseCriteriaString($criteriaString);

        if($criteria['property_name'] === 'random') {
            return rand(-1, 1);
        }

        list($doc1Prop, $doc2Prop) = $this->getPropertiesToCompare($doc1, $doc2, $criteria['property_name']);
        //comparison functions

        if(($doc1Prop instanceof \DateTime) && ($doc2Prop instanceof \DateTime)) {
            //DateTime
            $comparisonResult =  $doc1Prop->getTimestamp() - $doc2Prop->getTimestamp();
        } elseif ((is_int($doc1Prop) || is_float($doc1Prop) || is_null($doc1Prop)) &&
                (is_int($doc2Prop) || is_float($doc2Prop) || is_null($doc2Prop))) {
            //Integer/Float/Null
            $comparisonResult = $doc1Prop - $doc2Prop;
        } else {
            //Others (String)
            $comparisonResult = strcmp(
                $this->transliteratorMbStringCompare->transliterate($doc1Prop),
                $this->transliteratorMbStringCompare->transliterate($doc2Prop)
            );
        }
        return ($criteria['sort_direction'] == SORT_ASC) ? $comparisonResult : -$comparisonResult;
    }

}