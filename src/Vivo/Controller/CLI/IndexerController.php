<?php
namespace Vivo\Controller\CLI;

use Vivo\Indexer\IndexerInterface;
use Vivo\CMS\Api\IndexerInterface as IndexerApiInterface;
use Vivo\SiteManager\Event\SiteEventInterface;
use Vivo\Indexer\IndexerEvent;
use Vivo\Console\LineOutput;

use Zend\EventManager\EventManagerInterface;

/**
 * Vivo CLI controller for command 'indexer'
 */
class IndexerController extends AbstractCliController
{
    const COMMAND = 'indexer';

    /**
     * Indexer
     * @var IndexerInterface
     */
    protected $indexer;

    /**
     * Indexer API
     * @var IndexerApiInterface
     */
    protected $indexerApi;

    /**
     * Site Event
     * @var SiteEventInterface
     */
    protected $siteEvent;

    /**
     * Indexer event manager
     * @var EventManagerInterface
     */
    protected $indexerEvents;

    /**
     * Array of failed paths during reindexing
     * @var array
     */
    protected $failed   = array();

    /**
     * Console output
     * @var LineOutput
     */
    protected $consoleOutput;

    /**
     * Constructor
     * @param \Vivo\Indexer\IndexerInterface $indexer
     * @param \Vivo\CMS\Api\IndexerInterface $indexerApi
     * @param \Vivo\SiteManager\Event\SiteEventInterface $siteEvent
     * @param \Zend\EventManager\EventManagerInterface $indexerEvents
     */
    public function __construct(IndexerInterface $indexer,
                                IndexerApiInterface $indexerApi,
                                SiteEventInterface $siteEvent,
                                EventManagerInterface $indexerEvents)
    {
        $this->indexer          = $indexer;
        $this->indexerApi       = $indexerApi;
        $this->siteEvent        = $siteEvent;
        $this->indexerEvents    = $indexerEvents;
    }

    public function getConsoleUsage()
    {
        $output = "\nIndexer usage:";
        $output .= "\n\nindexer clear";
        $output .= "\nindexer reindex <host>";
        return $output;
    }

    /**
     * Clears all documents from index
     * @return string
     */
    public function clearAction()
    {
        $this->indexer->deleteAllDocuments();
        $output = "Attempted deletion of all documents from index";
        return $output;
    }

    /**
     * Reindex action
     * @return string
     */
    public function reindexAction()
    {
        $this->consoleOutput    = new LineOutput(true);
        $this->failed   = array();
        //Attach listeners
        $failedListener = $this->indexerEvents->attach(IndexerEvent::EVENT_INDEX_FAILED, array($this, 'onReindexFail'));
        $postListener   = $this->indexerEvents->attach(IndexerEvent::EVENT_INDEX_POST, array($this, 'onReindexPost'));
        //Prepare params
        $request        = $this->getRequest();
        /* @var $request \Zend\Console\Request */
        $host           = $request->getParam('host');
        $stopOnErrors   = $request->getParam('stopOnErrors') || $request->getParam('soe');
        $this->consoleOutput->line('Reindex');
        $this->consoleOutput->line('Host: ' . $host);
        $site       = $this->siteEvent->getSite();
        if (!$site) {
            $this->consoleOutput->line('No site object created (invalid host?)');
            return $this->consoleOutput->toString();
        }
        $this->consoleOutput->line('Site path: '. $site->getPath());
        $numIndexed = $this->indexerApi->reindex($site, '/', true, !$stopOnErrors);
        $this->consoleOutput->line(sprintf("%s items reindexed", $numIndexed));
        if (count($this->failed)) {
            $this->consoleOutput->line('Problems:');
            foreach ($this->failed as $failedPath => $exception) {
                if ($exception instanceof \Exception) {
                    $exceptionText  = $exception->getMessage();
                } else {
                    $exceptionText  = '???';
                }
                $this->consoleOutput->line($failedPath . ' -> ' . $exceptionText);
            }
        }
        //Detach listeners
        $this->indexerEvents->detach($failedListener);
        $this->indexerEvents->detach($postListener);
        $this->consoleOutput->line('Mem used: ' . round(memory_get_peak_usage(false) / 1000000) . ' MB');
        $this->consoleOutput->line('Mem used real: ' . round(memory_get_peak_usage(true) / 1000000) . ' MB');
        //return $this->consoleOutput->toString();
    }

    /**
     * Listener for failed indexing attempts
     * @param IndexerEvent $e
     */
    public function onReindexFail(IndexerEvent $e)
    {
        $this->consoleOutput->line('Error: ' . $e->getEntityPath());
        $this->failed[$e->getEntityPath()] = $e->getException();
    }

    /**
     * Listener for index_post events
     * @param IndexerEvent $e
     */
    public function onReindexPost(IndexerEvent $e)
    {
        $this->consoleOutput->line('Reindexed: ' . $e->getEntityPath());
    }
}
