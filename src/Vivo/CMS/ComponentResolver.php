<?php
namespace Vivo\CMS;

use Vivo\CMS\Exception\Exception;
use Vivo\CMS\Exception\InvalidArgumentException;
use Vivo\CMS\Model\Content;
use Vivo\CMS\Model\Content\ProvideFrontComponentInterface;

/**
 * Resolver is responsible for mapping model of content to appropriate UI component.
 */
class ComponentResolver
{
    /**
     * Component types - also keys in map.
     * @var string
     */
    const FRONT_COMPONENT = 'front_component';
    const EDITOR_COMPONENT = 'editor_component';

    /**
     * Model-component map.
     * @var array
     */
    protected $mappings = array();

    /**
     * Constructor.
     * @param array $config
     */
    public function __construct($config = array())
    {
        if (isset($config['component_mapping'])) {
            $this->mappings = $config['component_mapping'];
        }
    }

    /**
     * @param Content|string $modelOrClassname
     * @param string $type
     * @throws InvalidArgumentException
     * @throws Exception
     * @return string
     */
    public function resolve($modelOrClassname, $type = self::FRONT_COMPONENT)
    {
        if ($modelOrClassname instanceof ProvideFrontComponentInterface
                && $componentClass = $modelOrClassname->getFrontComponent()) {
            //when model provide own front component we don't use mapping.
        } else {
            if ($modelOrClassname instanceof Content) {
                $class = get_class($modelOrClassname);
            } elseif (!is_string($modelOrClassname) || $modelOrClassname === '') {
                throw new InvalidArgumentException(
                    sprintf(
                        '%s: Argument must be instance of Vivo\CMS\Model\Content or string.',
                        __METHOD__));
            } else {
                $class = $modelOrClassname;
            }

            if (!isset($this->mappings[$type][$class])) {
                throw new InvalidArgumentException(
                    sprintf(
                        "%s: Could not determine %s class for model %s. It isn't defined in mappings.",
                        __METHOD__, $type, $class));
            }
            $componentClass = $this->mappings[$type][$class];
        }

        if (!class_exists($componentClass)) {
            throw new Exception(
                sprintf(
                    "%s: Class '%s' does not exists. Propably there is a mistake in mapping configuration.",
                    __METHOD__, $componentClass));
        }
        return $componentClass;
    }
}
