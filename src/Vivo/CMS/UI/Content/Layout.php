<?php
namespace Vivo\CMS\UI\Content;

use Vivo\CMS\Api\CMS;
use Vivo\CMS\UI\Component;
use Vivo\IO\InputStreamWrapper;
use Vivo\Metadata\Provider\SelectableTemplatesProvider;
use Vivo\UI\ComponentInterface;

/**
 * Layout UI component wraps the underlaying component to layout.
 */
class Layout extends Component
{
    /**
     * Name of the main child component.
     */
    const MAIN_COMPONENT_NAME = 'param';

    /**
     * Name of the contents resource file with layout template.
     */
    const LAYOUT_FILENAME = 'Layout.phtml';

    /**
     * @var CMS
     */
    protected $cmsApi;

    /**
     * Constructor
     * @param CMS $cmsApi
     */
    public function __construct(CMS $cmsApi)
    {
        $this->cmsApi = $cmsApi;
    }

    /**
     * Sets main component.
     *
     * Main component is usually UI component that represents current document or child layout.
     * @param ComponentInterface $component
     */
    public function setMain(ComponentInterface $component)
    {
        $this->addComponent($component, self::MAIN_COMPONENT_NAME);
    }

    /**
     * Initialize component
     */
    public function init()
    {
        parent::init();
        $this->prepareViewModel();
    }

    /**
     * Prepares view model for layout component.
     *
     * Method selects layout template. If content::$template is set, then its value is used.
     * Otherwise template resource file is used.
     *
     * @return void
     */
    protected function prepareViewModel()
    {
        $template = $this->content->getTemplate();
        if ($template != null && $template != SelectableTemplatesProvider::DEFAULT_TEMPLATE) {
            $this->getView()->setTemplate($this->content->getTemplate());
            return;
        }
        //TODO use child model to enable global template for layout component
        $is = $this->cmsApi->readResource($this->content, self::LAYOUT_FILENAME);
        $path = $this->content->getPath() . '/'. self::LAYOUT_FILENAME;
        $filename = InputStreamWrapper::registerInputStream($is, $path);
        $this->getView()->setTemplate($filename);
    }

    /**
     * Returns array of panels documents paths.
     * @return array
     * @see \Vivo\CMS\Model\Content\Layout::getPanels()
     */
    public function getPanels()
    {
        return $this->content->getPanels();
    }
}
