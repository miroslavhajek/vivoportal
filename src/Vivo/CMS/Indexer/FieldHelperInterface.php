<?php
namespace Vivo\CMS\Indexer;

/**
 * FieldHelperInterface
 */
interface FieldHelperInterface
{
    /**
     * Returns indexer configuration for the specified property or for the whole entity class
     * @param string $entityClass
     * @param string|null $property
     * @return array
     */
    public function getIndexerConfig($entityClass, $property = null);

    /**
     * Returns indexer config for a full property name (\ClassName\property)
     * @param string $fieldName
     * @return array
     */
    public function getIndexerConfigForFieldName($fieldName);

    /**
     * Returns indexer field name for the specified property
     * @param string $entityClass
     * @param string $property
     * @return string
     */
    public function getName($entityClass, $property);

    /**
     * Returns indexer field type for the specified property
     * @param string $entityClass
     * @param string $property
     * @return mixed
     */
    public function getType($entityClass, $property);

    /**
     * Returns true when the specified property is indexed
     * @param string $entityClass
     * @param string $property
     * @return bool
     */
    public function isIndexed($entityClass, $property);

    /**
     * Returns if the specified property is stored in the index
     * @param string $entityClass
     * @param string $property
     * @return bool
     */
    public function isStored($entityClass, $property);

    /**
     * Returns if the specified property is tokenized
     * @param string $entityClass
     * @param string $property
     * @return boolean
     */
    public function isTokenized($entityClass, $property);

    /**
     * Returns if the specified property is a multi-value
     * @param string $entityClass
     * @param string $property
     * @return boolean
     */
    public function isMultiValue($entityClass, $property);

    /**
     * Returns full property name derived from entity and the bare property name
     * @param string $entityClass
     * @param string $property
     * @return string
     */
    public function getFullPropertyName($entityClass, $property);
}
