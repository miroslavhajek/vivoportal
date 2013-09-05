<?php
namespace Vivo\CMS\Indexer;

use Vivo\Metadata\MetadataManager;
use Vivo\Storage\PathBuilder\PathBuilder;
use Vivo\Indexer\IndexerInterface;

/**
 * FieldHelper
 */
class FieldHelper implements FieldHelperInterface
{
    /**
     * Default options used when metadata does not specify them
     * The property metadata is merged with these defaults
     * @var array
     */
    protected $defaultIndexingOptions   = array(
        'type'          => IndexerInterface::FIELD_TYPE_STRING,
        'indexed'       => true,
        //TODO - change default indexing option 'stored' to false?
        'stored'        => true,
        'tokenized'     => false,
        'multi'         => false,
    );

    /**
     * Indexing options presets
     * These are NOT merged with $defaultIndexingOptions
     * @var array
     */
    protected $presets  = array();

    /**
     * Indexer configurations for individual properties
     * array(
     *      'entity_class_name' => array(
     *          'propertyName'  => array(
     *              'type'      => 'field_type',            //Indexer field type
     *              'indexed'   => true|false,
     *              'stored'    => true|false,
     *              'tokenized' => true|false,
     *              'multi'     => true|false,              //Is this property multi-valued?
     *          ),
     *      ),
     * )
     * @var array
     */
    protected $indexerConfigs         = array();

    /**
     * Metadata manager
     * @var MetadataManager
     */
    protected $metadataManager;

    /**
     * Path builder
     * @var PathBuilder
     */
    protected $pathBuilder;

    /**
     * Constructor
     * @param \Vivo\Metadata\MetadataManager $metadataManager
     * @param \Vivo\Storage\PathBuilder\PathBuilder $pathBuilder
     * @param array $defaultIndexingOptions
     * @param array $presets
     */
    public function __construct(MetadataManager $metadataManager, PathBuilder $pathBuilder,
                                array $defaultIndexingOptions = array(), array $presets = array())
    {
        $this->metadataManager          = $metadataManager;
        $this->pathBuilder              = $pathBuilder;
        $this->defaultIndexingOptions   = array_merge($this->defaultIndexingOptions, $defaultIndexingOptions);
        $this->presets                  = array_merge($this->presets, $presets);
    }

    /**
     * Returns indexer configuration for the specified property or for the whole entity class
     * @param string $entityClass
     * @param string|null $property
     * @throws Exception\InvalidArgumentException
     * @return array
     */
    public function getIndexerConfig($entityClass, $property = null)
    {
        $this->loadIndexerConfigs($entityClass);
        if (!array_key_exists($entityClass, $this->indexerConfigs)) {
            throw new Exception\InvalidArgumentException(
                sprintf("%s: Indexer config for entity '%s' not found", __METHOD__, $entityClass));
        }
        $indexerConfigs = $this->indexerConfigs[$entityClass];
        if (is_null($property)) {
            return $indexerConfigs;
        }
        if (!isset($indexerConfigs[$property])) {
            throw new Exception\InvalidArgumentException(
                sprintf("%s: Property '%s' not defined for entity '%s'", __METHOD__, $property, $entityClass));
        }
        return $indexerConfigs[$property];
    }

    /**
     * Returns indexer config for an index field name
     * @param string $fieldName Either \ClassName\property or preset name
     * @throws Exception\CannotDecomposeFieldnameException
     * @return array
     */
    public function getIndexerConfigForFieldName($fieldName)
    {
        if ($this->hasPreset($fieldName)) {
            return $this->getPreset($fieldName);
        }
        $parts      = explode('\\', $fieldName);
        $property   = array_pop($parts);
        if (empty($property)) {
            throw new Exception\CannotDecomposeFieldnameException(
                sprintf("%s: Property name could not be assessed from field name '%s'", __METHOD__, $fieldName));
        }
        $class      = trim(implode('\\', $parts), '\\');
        if (empty($class)) {
            throw new Exception\CannotDecomposeFieldnameException(
                sprintf("%s: Class name could not be assessed from field name '%s'", __METHOD__, $fieldName));
        }
        $indexerConfig  = $this->getIndexerConfig($class, $property);
        return $indexerConfig;
    }

    /**
     * Returns indexer field name for the specified property
     * @param string $entityClass
     * @param string $property
     * @return string
     */
    public function getName($entityClass, $property)
    {
        $config = $this->getIndexerConfig($entityClass, $property);
        return $config['name'];
    }

    /**
     * Returns indexer field type for the specified property
     * @param string $entityClass
     * @param string $property
     * @return mixed
     */
    public function getType($entityClass, $property)
    {
        $config = $this->getIndexerConfig($entityClass, $property);
        return $config['type'];
    }

    /**
     * Returns true when the specified property is indexed
     * @param string $entityClass
     * @param string $property
     * @return bool
     */
    public function isIndexed($entityClass, $property)
    {
        $config = $this->getIndexerConfig($entityClass, $property);
        return $config['indexed'];
    }

    /**
     * Returns if the specified property is stored in the index
     * @param string $entityClass
     * @param string $property
     * @return bool
     */
    public function isStored($entityClass, $property)
    {
        $config = $this->getIndexerConfig($entityClass, $property);
        return $config['stored'];
    }

    /**
     * Returns if the specified property is tokenized
     * @param string $entityClass
     * @param string $property
     * @return boolean
     */
    public function isTokenized($entityClass, $property)
    {
        $config = $this->getIndexerConfig($entityClass, $property);
        return $config['tokenized'];
    }

    /**
     * Returns if the specified property is a multi-value
     * @param string $entityClass
     * @param string $property
     * @return boolean
     */
    public function isMultiValue($entityClass, $property)
    {
        $config = $this->getIndexerConfig($entityClass, $property);
        return $config['multi'];
    }

    /**
     * Returns full property name derived from entity class and the bare property name
     * @param string $entityClass
     * @param string $property
     * @return string
     */
    public function getFullPropertyName($entityClass, $property)
    {
        $fullPropName   = '\\' . $entityClass . '\\' . $property;
        return $fullPropName;
    }

    /**
     * Returns indexing options preset with the given name
     * @param string $name
     * @throws Exception\InvalidArgumentException
     * @return array
     */
    protected function getPreset($name)
    {
        if (!array_key_exists($name, $this->presets)) {
            throw new Exception\InvalidArgumentException(
                sprintf("%s: Indexing options preset '%s' not found", __METHOD__, $name));
        }
        return $this->presets[$name];
    }

    /**
     * Returns true if the specified preset exists
     * @param string $name
     * @return bool
     */
    protected function hasPreset($name)
    {
        if (array_key_exists($name, $this->presets)) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * Looks up indexer configurations in entity metadata and adds it to property definition array
     * @param string $entityClass
     * @return void
     */
    protected function loadIndexerConfigs($entityClass)
    {
        if (!array_key_exists($entityClass, $this->indexerConfigs)) {
            $entityMetadata = $this->metadataManager->getMetadata($entityClass);
            foreach ($entityMetadata as $propertyName => $metadata) {
                if (isset($metadata['index'])) {
                    $indexOptions   = $metadata['index'];
                    if (is_array($indexOptions)) {
                        //Explicitly set indexing options
                        $indexerConfig          = array_merge($this->defaultIndexingOptions, $indexOptions);
                        //Indexer field name is set to the full property name
                        $indexerConfig['name']  = $this->getFullPropertyName($entityClass, $propertyName);
                        $this->indexerConfigs[$entityClass][$propertyName] = $indexerConfig;
                    } elseif (is_string($indexOptions) && $this->hasPreset($indexOptions)) {
                        //Index preset
                        $indexerConfig  = $this->getPreset($indexOptions);
                        //Indexer field name is set to the preset name
                        $indexerConfig['name']  = $indexOptions;
                        $this->indexerConfigs[$entityClass][$propertyName] = $indexerConfig;
                    } elseif ($indexOptions) {
                        //Index options evaluates as a boolean true - use defaults
                        $indexerConfig  = $this->defaultIndexingOptions;
                        //Indexer field name is set to the full property name
                        $indexerConfig['name']  = $this->getFullPropertyName($entityClass, $propertyName);
                        $this->indexerConfigs[$entityClass][$propertyName] = $indexerConfig;
                    }
                }
            }
        }
    }
}
