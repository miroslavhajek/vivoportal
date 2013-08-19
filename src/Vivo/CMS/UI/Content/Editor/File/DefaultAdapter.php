<?php
namespace Vivo\CMS\UI\Content\Editor\File;

use Vivo\CMS\Api;
use Vivo\Form\Fieldset;
use Vivo\CMS\UI\Content\Editor\AbstractAdapter;
use Vivo\Repository\Exception\PathNotSetException;
use Vivo\UI\ComponentEventInterface;

/**
 * Editor Adapter for general files
 */
class DefaultAdapter extends AbstractAdapter
{
    /**
     * @var \Vivo\CMS\Api\Content\File
     */
    private $fileApi;

    /**
     * Shows download button
     * @var bool
     */
    protected $showDownload = false;

    /**
     * Constructs Adapter
     * @param \Vivo\CMS\Api\Content\File $fileApi
     */
    public function __construct(Api\Content\File $fileApi)
    {
        $this->fileApi = $fileApi;
    }

    public function attachListeners()
    {
        parent::attachListeners();
        $eventManager   = $this->getEventManager();
        $eventManager->attach(ComponentEventInterface::EVENT_INIT, array($this, 'initListenerSetShowDownload'));
        $eventManager->attach(ComponentEventInterface::EVENT_VIEW, array($this, 'viewListenerSetViewModelVars'));
    }


    /**
     * Initializes Adapter
    */
    public function initListenerSetShowDownload()
    {
        try {
            if($this->content->getFileName()) {
                $this->showDownload = true;
            }
        }
        catch (PathNotSetException $e) {
        }
    }

    public function viewListenerSetViewModelVars()
    {
        $this->getView()->showDownload = $this->showDownload;
    }

    /**
     * Creates form
     */
    protected function doGetFieldset()
    {
        // NOT USED
        return new Fieldset('download-resource'.$this->content->getUuid());
    }

    /**
     * Downloads file
     */
    public function downloadFile()
    {
        $this->fileApi->download($this->content);
    }
}
