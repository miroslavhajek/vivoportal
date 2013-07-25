<?php
namespace Vivo\View\Resolver;

use Vivo\IO\InputStreamWrapper;
use Vivo\Module\ResourceManager\ResourceManager;
use Vivo\Util\Path\PathParser;
use Vivo\View\Exception\TemplateNotFoundException;

use VpLogger\Log\Logger;

use Zend\Config\Config;
use Zend\Mvc\Application;
use Zend\View\Renderer\RendererInterface;
use Zend\View\Resolver\ResolverInterface;
use Zend\EventManager\EventManagerInterface;

/**
 * Resolver determines which template file should be used for rendering.
 */
class TemplateResolver implements ResolverInterface
{
    const STATE_NOT_FOUND_ACTION_THROW		= 'throw';
    const STATE_NOT_FOUND_ACTION_COMMENT	= 'comment';
    const STATE_NOT_FOUND_ACTION_HIDE		= 'hide';

    const EVENT_TEMPLATE_NOT_FOUND			= 'template_not_found';

    /**
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * Array of already resolved templates
     * @var array
     */
    protected $resolved = array();

    /**
     * @var array
     */
    private $templateMap = array();

    /**
     * Config options
     * @var array
     */
    private $configOptions = array();

    /**
     * Event Manager
     * @var \Zend\EventManager\EventManagerInterface
     */
    private $eventManager;

    /**
     * @param ResourceManager $resourceManager
     * @param PathParser $parser
     * @param array $options
     * @param array $configOptions
     */
    public function __construct(ResourceManager $resourceManager,
                                PathParser $parser,
                                array $options = array(),
                                array $configOptions = array())
    {
        $this->configure($options);
        $this->resourceManager = $resourceManager;
        $this->parser = $parser;
        $this->configOptions = $configOptions;
    }

    /**
     * Configures template resolver.
     * @param array $config
     */
    public function configure($config = array())
    {
        if (isset($config['template_map'])) {
            $this->templateMap = array_merge($this->templateMap,
                                             $config['template_map']);
        }
    }

    /**
     * Method resolves the given name to the path, that could be included or opened.
     *
     * The path in this context also means url for registered stream wrappers.
     * If the name is existing path, the name is returned. Otherwise, method
     * searches the template map and find the path.
     *
     * @see \Zend\View\Resolver\ResolverInterface::resolve()
     */
    public function resolve($name, RendererInterface $renderer = null)
    {
        if (isset($this->resolved[$name])) {
            return $this->resolved[$name];
        }

        if (file_exists($name)) {
            $this->resolved[$name] = $name;
            return $name;
        }

        if (isset($this->templateMap[$name])) {
            $path = $this->templateMap[$name];
        } else {
            return false;
            throw new TemplateNotFoundException(
                    sprintf("%s: Template for '%s' can't be resolved.",
                            __METHOD__, $name));
        }

        if (file_exists($path)) {
            $this->resolved[$name] = $path;
        } else {
            try {
                //load template from module
                $parts  = $this->parser->parse($path);
                $is     = $this->resourceManager->readResource($parts['module'], $parts['path'], 'view');
                $path   = InputStreamWrapper::registerInputStream($is, $path);
                $this->resolved[$name] = $path;
            } catch (\Exception $e) {
                if ($e instanceof \Vivo\Module\Exception\ExceptionInterface
                        || $e instanceof \Vivo\Util\Exception\CanNotParsePathException) {

                    $eventParams = array(
                        'templateName' => $name,
                        'log' => array(
                            'message' => sprintf(
                                "%s: Template '%s' ,that is defined in template map for '%s', not found.",
                                __METHOD__, $path, $name),
                            'priority' => Logger::ERR,
                        ),
                    );
                    $this->getEventManager()->trigger(self::EVENT_TEMPLATE_NOT_FOUND, $this, $eventParams);

                    switch ($this->configOptions['template_not_found_action']) {
                        case self::STATE_NOT_FOUND_ACTION_THROW:
                            throw new TemplateNotFoundException(
                                sprintf(
                                        "%s: Template '%s' ,that is defined in template map for '%s', not found.",
                                        __METHOD__, $path, $name), null, $e);
                            break;
                        case self::STATE_NOT_FOUND_ACTION_COMMENT:
                            echo sprintf(
                                    "<!-- %s: Template '%s' ,that is defined in template map for '%s', not found. -->",
                                    __METHOD__, $path, $name);
                            $this->resolved[$name] = $this->templateMap['Vivo\TemplateNotFound'];
                            break;
                        default:
                        case self::STATE_NOT_FOUND_ACTION_HIDE:
                            $this->resolved[$name] = $this->templateMap['Vivo\Blank'];
                            break;
                    }
                } else {
                    //rethrow other exceptions
                    throw $e;
                }
            }
        }
        return $this->resolved[$name];
    }

    /**
     * Returns Event Manager
     * @return \Zend\EventManager\EventManagerInterface
     */
    public function getEventManager()
    {
        if (!$this->eventManager) {
            $this->eventManager = new \Zend\EventManager\EventManager();
        }

        return $this->eventManager;
    }

    /**
     * Sets Event Manager
     * @param \Zend\EventManager\EventManagerInterface $eventManager
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
    }
}
