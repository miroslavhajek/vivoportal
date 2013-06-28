<?php
namespace Vivo\Indexer;

/**
 * Field
 * Represents a document field
 */
class Field
{
    /**
     * Field name
     * @var string
     */
    protected $name;

    /**
     * Field value
     * @var mixed|array
     */
    protected $value;
    
    /**
     * Field is extractable
     * Determinates if field is extractable. Used for files like PDF, DOC and so on.
     * @var bool
     */
    protected $extractable = false;

    /**
     * Constructor
     * @param string $name
     * @param mixed|array $value
     * @param bool $extractable
     */
    public function __construct($name, $value, $extractable = false)
    {
        $this->name        = $name;
        $this->value       = $value;
        $this->extractable = $extractable;
    }

    /**
     * Returns the name of the field
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return value of the field
     * @return mixed|array
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Returns if field is extractable
     * @return bool
     */
    public function getExtractable()
    {
        return $this->extractable;
    }
    
    /**
     * Returns if the field is multivalued
     * @return bool
     */
    public function isMultiValued()
    {
        return is_array($this->value);
    }
}
