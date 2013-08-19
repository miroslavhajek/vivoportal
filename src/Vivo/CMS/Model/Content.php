<?php
namespace Vivo\CMS\Model;

use Vivo\CMS\Model\Content\ProvideTemplateInterface;

use \DateTime;

/**
 * Base class for all Vivo contents models.
 * @todo remove content index functions
 */
class Content extends Entity implements ProvideTemplateInterface
{

    /**
     * Template key in template map.
     * @var string
     */
    protected $template;

    /**
     * @var string Workflow state
     */
    protected $state;

    /**
     * @var DateTime Date for automatic state change by cron.
     */
    protected $stateChange;

    /**
     * @var bool
     */
    protected $recursive;

    public function getState()
    {
        return $this->state;
    }

    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * Gets content version.
     *
     * @return int
     */
    public function getVersion()
    {
        return ($p = strrpos($this->getPath(), '/')) ? substr($this->getPath(), $p + 1) : 0;
    }

    /**
     * Sets version.
     *
     * @param int $version
     */
    public function setVersion($version)
    {
        $this->path = substr($this->getPath(), 0, strrpos($this->getPath(), '/') + 1) . $version;
    }

    /**
     * Gets content index. Index means number of a content in the Multicontent Document.
     *
     * @return int
     */
    public function getIndex()
    {
        return preg_match('~\/Contents\.(\d{1,2})\/~', $this->getPath(),
                $matches) ? intval($matches[1]) : false;
    }

    /**
     * Returns relative path of the content.
     *
     * @return string Content relative path
     */
    public function getRelativePath()
    {
        return 'Contents' . (($index = $this->getIndex()) ? '.' . $index : '')
                . '/' . $this->getVersion();
    }
    /**
     * Returns template key.
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }
    /**
     * Set template key in template map.
     * @param string $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }
}
