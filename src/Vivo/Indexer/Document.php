<?php
namespace Vivo\Indexer;

use \ArrayObject;

/**
 * Document
 * Document stored into index / retrieved from index
 */
class Document implements DocumentInterface
{
    /**
     * Document ID
     * @var string
     */
    protected $docId;

    /**
     * Array of document fields
     * @var Field[]
     */
    protected $fields           = array();

    /**
     * Constructor
     * @param string|null $docId
     * @param Field[]|array|null $fields Either array of Fields or array of mappings fieldName => fieldValue
     */
    public function __construct($docId = null, array $fields = null)
    {
        $this->setDocId($docId);
        if ($fields) {
            foreach ($fields as $key => $field) {
                if (!($field instanceof Field)) {
                    //fieldName => fieldValue mapping
                    $field = new Field($key, $field);
                }
                $this->addField($field);
            }
        }
    }

    /**
     * Adds a field to the document
     * @param Field $field
     */
    public function addField(Field $field)
    {
        $this->fields[$field->getName()]            = $field;
    }

    /**
     * Returns a field from the document
     * @param string $fieldName
     * @return Field
     * @throws Exception\InvalidArgumentException
     */
    public function getField($fieldName)
    {
        if (!array_key_exists($fieldName, $this->fields)) {
            throw new Exception\InvalidArgumentException(sprintf("%s: Field name '%s' not found in document",
                                                            __METHOD__, $fieldName));
        }
        return $this->fields[$fieldName];

    }

    /**
     * Returns array of filed names present in the document
     * @return string[]
     */
    public function getFieldNames()
    {
        return array_keys($this->fields);
    }

    /**
     * Returns field value
     * @param string $filedName
     * @return string
     */
    public function getFieldValue($filedName)
    {
        $field  = $this->getField($filedName);
        return $field->getValue();
    }

    /**
     * IteratorAggregate implementation function. Allows usage:
     * <code>
     * foreach ($document as $key => $value)
     * {
     * 	...
     * }
     * </code>
     */
    public function getIterator()
    {
        $arrayObject = new ArrayObject($this->fields);
        return $arrayObject->getIterator();
    }
    
    /**
     * Get extractable fields
     * @return array
     */
    public function getExtractableFields()
    {
        $extractableFields = array();
        foreach ($this->fields as $key => $field) {
            if($field->getExtractable()){
                $extractableFields[$key]['value'] = $field->getValue();
                $extractableFields[$key]['name'] = $field->getName();
            }
        }
        return $extractableFields;
    }

    /**
     * Sets unique document ID
     * @param string|null $docId
     */
    public function setDocId($docId = null)
    {
        $this->docId    = $docId;
    }

    /**
     * Returns unique document ID
     * @return string
     */
    public function getDocId()
    {
        return $this->docId;
    }

    /**
     * Returns if the document has an ID set
     * @return bool
     */
    public function hasDocId()
    {
        return !is_null($this->docId);
    }
}
