<?php
namespace Vivo\Controller\CLI;

use Vivo\CMS\Api\Util as UtilApi;
use Vivo\CMS\Event\CMSEvent;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\EventManager\EventInterface;

/**
 * Vivo CLI controller for command 'util'
 */
class UtilController extends AbstractCliController
{
    const COMMAND = 'util';

    /**
     * Name of interface the entity processor used in crawl action must implement
     * @var string
     */
    protected $entityProcessorInterfaceName = 'Vivo\Service\EntityProcessorInterface';

    /**
     * Main service manager
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * Util API
     * @var UtilApi
     */
    protected $utilApi;

    /**
     * CMS Event
     * @var CmsEvent
     */
    protected $cmsEvent;

    /**
     * Number of successfully processed entities (crawl action)
     * @var int
     */
    protected $processedSuccessfully    = 0;

    /**
     * Number of unsuccessfully processed entities (crawl action)
     * @var int
     */
    protected $processedWithErrors      = 0;

    /**
     * Number of not processed entities (crawl action)
     * @var int
     */
    protected $notProcessed             = 0;

    /**
     * Constructor
     * @param ServiceLocatorInterface $serviceLocator
     * @param UtilApi $utilApi
     * @param CMSEvent $cmsEvent
     */
    public function __construct(ServiceLocatorInterface $serviceLocator, UtilApi $utilApi, CMSEvent $cmsEvent)
    {
        $this->serviceLocator   = $serviceLocator;
        $this->utilApi          = $utilApi;
        $this->cmsEvent         = $cmsEvent;
        $this->attachListeners();
    }

    /**
     * Attaches listeners
     */
    protected function attachListeners()
    {
        $utilEvents = $this->utilApi->getEventManager();
//        $utilEvents->attach(UtilApi::EVENT_PROCESS_PRE, array($this, 'processEntityPreListener'));
        $utilEvents->attach(UtilApi::EVENT_PROCESS_POST, array($this, 'processEntityPostListener'));
    }

    /**
     * Returns information about this CLI command
     * @return string
     */
    public function getConsoleUsage()
    {
        $output = PHP_EOL . 'Util usage:';
        $output .= PHP_EOL . PHP_EOL . 'util crawl <host> <service>';
        $output .= PHP_EOL . 'Crawls the site specified by <host> and passes all entities to the <service> '
                           . 'to process them.';
        $output .= PHP_EOL . sprintf('The <service> must implement %s.', $this->entityProcessorInterfaceName);
        $output .= PHP_EOL;
        return $output;
    }

    /**
     * Crawls the site and processes all entities with processor specified as <service>
     */
    public function crawlAction()
    {
        echo PHP_EOL . 'Crawl' . PHP_EOL;
        //Prepare params
        $request        = $this->getRequest();
        /* @var $request \Zend\Console\Request */
        $host           = $request->getParam('host');
        echo 'Host: ' . $host . PHP_EOL;
        $serviceName    = $request->getParam('service');
        echo 'Service name: ' . $serviceName . PHP_EOL;
        try {
            $entityProcessor    = $this->serviceLocator->get($serviceName);
        } catch (\Exception $e) {
            echo sprintf("ERROR: Service '%s' could not be retrieved from the Service Locator", $serviceName) . PHP_EOL;
            echo $e->getMessage() . PHP_EOL;
            return;
        }
        if (!$entityProcessor instanceof $this->entityProcessorInterfaceName) {
            echo sprintf("ERROR: Service '%s' must implement the %s",
                    $serviceName, $this->entityProcessorInterfaceName) . PHP_EOL;
            return;
        }
        $site       = $this->cmsEvent->getSite();
        if (!$site) {
            echo sprintf("ERROR: No Site object found in CmsEvent") . PHP_EOL;
            return;
        }
        $this->processedSuccessfully    = 0;
        $this->processedWithErrors      = 0;
        $this->notProcessed             = 0;
        $crawled    = $this->utilApi->crawl($site, $entityProcessor);
        echo PHP_EOL . PHP_EOL . sprintf("Crawled %s entities", $crawled) . PHP_EOL;
        echo sprintf("Processed successfully: %s", $this->processedSuccessfully) . PHP_EOL;
        echo sprintf("Processed with errors: %s", $this->processedWithErrors) . PHP_EOL;
        echo sprintf("Not processed: %s", $this->notProcessed) . PHP_EOL;
        echo PHP_EOL;
    }

    /**
     * Listener for UtilApi::EVENT_PROCESS_PRE event
     * @param EventInterface $e
     */
    public function processEntityPreListener(EventInterface $e)
    {
        /** @var $entity \Vivo\CMS\Model\Entity */
        $entity = $e->getParam('entity');
        echo sprintf("%sProcess pre: %s (%s)", PHP_EOL, $entity->getPath(), $entity->getUuid());
    }

    /**
     * Listener for UtilApi::UtilApi::EVENT_PROCESS_POST event
     * @param EventInterface $e
     */
    public function processEntityPostListener(EventInterface $e)
    {
        /** @var $entity \Vivo\CMS\Model\Entity */
        $entity = $e->getParam('entity');
        $success    = $e->getParam('success');
        if ($success === true) {
            $this->processedSuccessfully++;
            echo sprintf("%s+ OK:            %s (%s)", PHP_EOL, $entity->getPath(), $entity->getUuid());
        } elseif ($success === false) {
            $this->processedWithErrors++;
            echo sprintf("%s- ERROR:         %s (%s)", PHP_EOL, $entity->getPath(), $entity->getUuid());
        } else {
            $this->notProcessed++;
            echo sprintf("%s0 Not processed: %s (%s)", PHP_EOL, $entity->getPath(), $entity->getUuid());
        }
    }
}
