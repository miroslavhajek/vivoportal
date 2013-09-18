<?php
namespace Vivo\Metadata;

use Vivo\Module\ResourceManager\ResourceManager;
use Vivo\Module\ModuleNameResolver;
use Vivo\Module\Exception\ResourceNotFoundException;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Config\Reader\Ini as ConfigReader;
use Zend\Config\Config;
use Zend\Stdlib\ArrayUtils;
use Zend\Feed\Reader\Reader;

/**
 * MetadataManager
 */
class MetadataManager
{
    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $serviceManager;

    /**
     * @var \Vivo\Module\ResourceManager\ResourceManager
     */
    protected $resourceManager;

    /**
     * @var \Vivo\Module\ModuleNameResolver
     */
    protected $moduleNameResolver;

    /**
     * @var \Zend\Config\Reader\Ini
     */
    protected $reader;

    /**
     * @var array
     */
    protected $options = array(
        'config_path'       => null,
        'custom_properties' => array(),
    );

    /**
     * @var array
     */
    protected $cache = array(
        'rawmeta'   => array(),
        'meta'      => array(),
    );

    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceManager
     * @param \Vivo\Module\ResourceManager\ResourceManager $resourceManager
     * @param \Vivo\Module\ModuleNameResolver $moduleNameResolver
     * @param array $options
     */
    public function __construct(
            ServiceLocatorInterface $serviceManager,
            ResourceManager $resourceManager,
            ModuleNameResolver $moduleNameResolver,
            array $options = array())
    {
        $this->serviceManager       = $serviceManager;
        $this->resourceManager      = $resourceManager;
        $this->moduleNameResolver   = $moduleNameResolver;
        $this->reader = new ConfigReader();
        $this->mergeOptions($options);
    }

    /**
     * Merges array of options into the current options
     * @param array $options
     */
    public function mergeOptions(array $options)
    {
        $this->options = ArrayUtils::merge($this->options, $options);
    }

    /**
     * Returns custom properties definitions
     * @param null|string $className
     * @return array
     */
    public function getCustomPropertiesDefs($className = null)
    {
        if($className == null) {
            return $this->options['custom_properties'];
        }
        else {
            return isset($this->options['custom_properties'][$className])
                    ? $this->options['custom_properties'][$className]
                    : array();
        }
    }

    /**
     * Returns custom properties by class name
     * @param string $className
     * @return array
     */
    public function getCustomProperties($className)
    {
        return $this->getCustomPropertiesConfig($className)->toArray();
    }

    /**
     * Returns custom properties by class name
     * @param string $className
     * @return \Zend\Config\Config
     */
    private function getCustomPropertiesConfig($className)
    {
        $config = new Config(array());
        $defs = $this->getCustomPropertiesDefs($className);
        foreach ($defs as $vmodule => $path) {
            $path = sprintf('%s.ini', str_replace('\\', DIRECTORY_SEPARATOR, $path));

            $resource = $this->resourceManager->getResource($vmodule, $path, 'metadata');
            $entityConfig = new Config($this->reader->fromString($resource));

            $config = $config->merge($entityConfig);
        }

        return $config;
    }

    /**
     * Returns raw metadata
     * @param string $entityClass
     * @return array
     */
    public function getRawMetadata($entityClass) {
        if(isset($this->cache['rawmeta'][$entityClass])) {
            return $this->cache['rawmeta'][$entityClass];
        }

        $config = new Config(array());

        $parent = $entityClass;
        $parents = array($parent);
        while(($parent = get_parent_class($parent)) && $parent !== false) {
            $parents[] = $parent;
        }
        $parents = array_reverse($parents);

        foreach ($parents as $class) {
            // Vivo CMS model, other is a module model
            if(strpos($class, 'Vivo\\') === 0) {
                $path = realpath(sprintf('%s/%s.ini',
                    $this->options['config_path'],
                    str_replace('\\', DIRECTORY_SEPARATOR, $class))
                );

                if($path) {
                    $resource = file_get_contents($path);
                    $entityConfig = new Config($this->reader->fromString($resource));

                    $config = $config->merge($entityConfig);
                }
            }
            else {
                $moduleName = $this->moduleNameResolver->fromFqcn($class);
                $path = sprintf('%s.ini', str_replace('\\', DIRECTORY_SEPARATOR, $class));

                try {
                    $resource = $this->resourceManager->getResource($moduleName, $path, 'metadata');
                    $entityConfig = new Config($this->reader->fromString($resource));

                    $config = $config->merge($entityConfig);
                }
                catch (ResourceNotFoundException $e) { }
            }

            if($this->getCustomPropertiesDefs($class)) {
                $entityConfig = $this->getCustomPropertiesConfig($class);

                // Modifications is not allowed
                $config = $entityConfig->merge($config);
            }
        }

        $descriptors = $config->toArray();

        uasort($descriptors, function($a, $b) {
            return (isset($a['order']) ? intval($a['order']) : 0) > (isset($b['order']) ? intval($b['order']) : 0);
        });

        $this->cache['rawmeta'][$entityClass] = $descriptors;

        return $descriptors;
    }

    /**
     * Returns metadata
     * @param string $entityClass
     * @return array
     */
    public function getMetadata($entityClass) {
        if(isset($this->cache['meta'][$entityClass])) {
            return $this->cache['meta'][$entityClass];
        }

        $config = $this->getRawMetadata($entityClass);
        $this->applyProvider($entityClass, $config);
        $this->cache['meta'][$entityClass] = $config;

        return $config;
    }

    /**
     * Applies metadata provider for all classes defined in config.
     *
     * @param string $entityClass
     * @param array $config
     */
    private function applyProvider($entityClass, &$config) {
        foreach ($config as &$value) {
            if(is_array($value)) {
                $this->applyProvider($entityClass, $value);
            }
            elseif (strpos($value, '\\') && $this->serviceManager->has($value)) {
                $provider   = $this->serviceManager->get($value);
                if($provider instanceof MetadataValueProviderInterface) {
                    $value  = $provider->getValue($entityClass);
                }
            }
        }
    }
}
