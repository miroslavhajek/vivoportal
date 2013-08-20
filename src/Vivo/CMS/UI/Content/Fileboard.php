<?php
namespace Vivo\CMS\UI\Content;

use Vivo\CMS\UI\Component;
use Vivo\CMS\Api\Content\Fileboard as FileboardApi;
use Vivo\CMS\Model\Content\Fileboard\Separator;
use Vivo\UI\ComponentEventInterface;

/**
 * UI component for content fileboard.
 */
class Fileboard extends Component
{
    /**
     * @var \Vivo\CMS\Api\Content\Fileboard
     */
    private $fileboardApi;

    /**
     * @var array
     */
    private $files = array();

    /**
     * @var array
     */
    private $resources = array();

    /**
     * Constructor
     */
    public function __construct(FileboardApi $fileboardApi)
    {
        $this->fileboardApi = $fileboardApi;
    }

    public function attachListeners()
    {
        parent::attachListeners();

        $this->getEventManager()->attach(ComponentEventInterface::EVENT_INIT,
                array($this, 'initListenerFileboardInit'));
    }

    public function initListenerFileboardInit()
    {
        $this->files = $this->fileboardApi->getList($this->content);

        foreach ($this->files as $file) {
            if($file instanceof Separator) {
                $this->resources[$file->getUuid()] = $this->fileboardApi->readResource($file);
            }
        }
    }

    /**
     * @param string $uuid
     */
    public function download($uuid)
    {
        $this->fileboardApi->downloadByUuid($uuid);
    }

    public function view()
    {
        $view = parent::view();
        $view->files = $this->files;
        $view->resources = $this->resources;

        return $view;
    }
}
