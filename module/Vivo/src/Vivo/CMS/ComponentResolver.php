<?php
namespace Vivo\CMS;

use Vivo\CMS\Exception\Exception;
use Vivo\CMS\Exception\InvalidArgumentException;
use Vivo\CMS\Model\Content;

/**
 * Resolver is responsible for mapping model of content to aproptiate UI component.
 */
class ComponentResolver
{
    const FRONT_COMPONENT = 'front_component';
    const EDITOR_COMPONENT = 'editor_component';

    private $mappings = array();

    /**
     * @param array $options
     */
    public function __construct($config = array())
    {
        if (isset($config['vivo']['component_mapping'])) {
            $this->mappings = $config['vivo']['component_mapping'];
        }
    }

    /**
     * @param Content|string $modelOrClassname
     * @param unknown_type $type
     * @throws InvalidArgumentException
     * @throws Exception
     * @return unknown
     */
    public function resolve($modelOrClassname, $type = self::FRONT_COMPONENT)
    {
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
                    "%s: Could not determine UI component class for model %s. It isn't defined in mappings",
                    __METHOD__, $class));
        }

        $componentClass = $this->mappings[$type][$class];

        if (!class_exists($componentClass)) {
            throw new Exception(
                sprintf(
                    "%s: Class '%s' does not exists. Propably there is a mistake in mapping configuration.",
                    __METHOD__, $componentClass));
        }
        return $componentClass;
    }
}
