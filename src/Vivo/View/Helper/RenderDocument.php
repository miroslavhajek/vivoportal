<?php
namespace Vivo\View\Helper;

use Vivo\CMS\Model;
use Vivo\CMS\Api\Document as DocumentApi;
use Vivo\CMS\ComponentFactory;
use Vivo\UI\ComponentTreeController;
use Vivo\UI\ComponentEventInterface;

use Zend\View\Helper\AbstractHelper;
use Zend\View\View;

/**
 * RenderDocument
 * View helper - returns rendered document
 */
class RenderDocument extends AbstractHelper
{
    /**
     * Document API
     * @var DocumentApi
     */
    protected $documentApi;

    /**
     * Component factory
     * @var ComponentFactory
     */
    protected $componentFactory;

    /**
     * View object
     * @var View
     */
    protected $viewManager;

    /**
     * Component Tree Controller
     * @var ComponentTreeController
     */
    protected $componentTreeController;

    /**
     * Constructor
     * @param DocumentApi $documentApi
     * @param ComponentFactory $componentFactory
     * @param View $viewManager
     * @param ComponentTreeController $componentTreeController
     */
    public function __construct(DocumentApi $documentApi,
                                ComponentFactory $componentFactory,
                                View $viewManager,
                                ComponentTreeController $componentTreeController)
    {
        $this->documentApi              = $documentApi;
        $this->componentFactory         = $componentFactory;
        $this->viewManager              = $viewManager;
        $this->componentTreeController  = $componentTreeController;
    }

    /**
     * Invoke the view helper as the PHPRenderer method call
     * @param Model\Document $document
     * @return $this|string
     */
    public function __invoke(Model\Document $document = null)
    {
        if (is_null($document)) {
            return $this;
        }
        $output = $this->render($document);
        return $output;
    }

    /**
     * Returns rendered document
     * @param Model\Document $document
     * @return string
     */
    public function render(Model\Document $document)
    {
        $contents       = $this->documentApi->getPublishedContents($document);
        $hyperlink      = false;
        //Check if there is Hyperlink content among contents
        foreach ($contents as $content) {
            if ($content instanceof Model\Content\Hyperlink) {
                //One of the published contents is a hyperlink
                $hyperlink  = true;
                break;
            }
        }
        if ($hyperlink) {
            //Return hyperlink information
            $documentVh     = $this->view->plugin('document');
            $escaper        = $this->view->plugin('escapeHtml');
            $translator     = $this->view->plugin('translate');
            $rendered       = sprintf('<a href="%s">%s</a>',
                                      $documentVh->__invoke($document),
                                      $escaper->__invoke($translator->__invoke('Show more')));
        } else {
            //Return rendered document
            $frontComponent = $this->componentFactory->getFrontComponent($document, array('noLayout' => true));
            $this->componentTreeController->setRoot($frontComponent);
            $this->componentTreeController->init(); //replace by lazy init
            $viewModel      = $this->componentTreeController->view();
            $this->componentTreeController->done();
            $this->viewManager->render($viewModel);
            $response       = $this->viewManager->getResponse();
            $rendered       = $response->getContent();
        }
        return $rendered;
    }
}
