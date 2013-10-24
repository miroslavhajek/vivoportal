<?php
namespace Vivo\UI;

use Vivo\UI\ComponentInterface;

use Zend\Http\Response;
use Zend\View\Helper\Doctype;

/**
 * UI component that represents HTML page.
 */
class Page extends ComponentContainer
{

    const MAIN_COMPONENT_NAME = 'main';

    /**
     * @var string
     */
    protected $doctype = Doctype::HTML5;

    /**
     * @var string Page title.
     */
    protected $title;

    /**
     * @var array
     * @todo
     */
    protected $htmlAttributes = array();

    /**
     * @var array HTML attributes
     */
    protected $bodyAttributes = array();

    /**
     * @var array HTML metas.
     */
    protected $metas = array();

    /**
     * @var array HTML Links.
     */
    protected $links = array();

    /**
     * @var array HTML Links with conditions.
     * @example
     * <!--[if IE 6]><link rel="stylesheet" href="/Styles/ie6.css" type="text/css" media="screen, projection"/><![endif]-->
     * @todo
     */
    protected $conditional_links = array();

    /**
     * @var array
     */
    protected $scripts = array();

    /**
     * @var HelperPluginManager
     */
    protected $viewHelpers;

    /**
     * @param ComponentInterface $component
     * @param array $options
     */
    public function __construct(Response $response, $options = array())
    {
        $this->response = $response;
        $this->configure($options);
    }

    /**
     * Configures doctype, links, scripts, metas from options.
     * @param $options
     */
    public function configure(array $options) {
        if (isset($options['links'])) {
            $this->setLinks($options['links']);
        }
        if (isset($options['metas'])) {
            $this->setMetas($options['metas']);
        }
        if (isset($options['scripts'])) {
            $this->setScripts($options['scripts']);
        }
        if (isset($options['doctype'])) {
            $this->setDoctype($options['doctype']);
        }
    }

    public function init() {
        $this->response->getHeaders()->addHeaderLine('Content-Type: text/html');
        parent::init();
    }

    /**
     * Sets main UI component of the page.
     * @param ComponentInterface $component
     */
    public function setMain(ComponentInterface $component)
    {
        $this->addComponent($component, self::MAIN_COMPONENT_NAME);
    }

    /**
     * Sets HTML doctype of page.
     *
     * Also accept names of constants defined in Zend\View\Helper\Doctype.
     * @param string $doctype
     * @see \Zend\View\Helper\Doctype
     */
    public function setDoctype($doctype)
    {
        if (defined('DocType::'.$doctype)) {
            $this->doctype = Doctype::$doctype;
        } else {
            $this->doctype = $doctype;
        }
    }

    /**
     * Sets title.
     * @param unknown $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Returns title.
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets head metas.
     * @param array $metas
     */
    public function setMetas(array $metas)
    {
        $this->metas = $metas;
    }

    /**
     * Add meta to page.
     * @param array $meta
     */
    public function addMeta(array $meta)
    {
        $this->metas[] = $meta;
    }

    /**
     * Sets head links.
     * @param array $links
     */
    public function setLinks(array $links)
    {
        $this->links = $links;
    }

    /**
     * Add link to page.
     * @param array $link
     */
    public function addLink(array $link)
    {
        $this->links[] = $link;
    }

    /**
     * Sets head scripts.
     * @param array $scripts
     */
    public function setScripts(array $scripts)
    {
        $this->scripts = $scripts;
    }

    /**
     * Add script to page.
     * @param array
     */
    public function addScript(array $script)
    {
        $this->scripts[] = $script;
    }

    /**
     * Sets HTML element attributes
     * @param array $htmlAttributes
     */
    public function setHtmlAttributes(array $htmlAttributes)
    {
        $this->htmlAttributes = $htmlAttributes;
    }

    /**
     * Sets HTML body attributes
     * @param array $bodyAttributes
     */
    public function setBodyAttributes(array $bodyAttributes)
    {
        $this->bodyAttributes = $bodyAttributes;
    }

    /**
     * Prepare data for view
     */
    protected function prepareView()
    {
        //prepare links into HeadLink helper format
        $maxOffset  = 0;
        $links      = array();
        foreach ($this->links as $link) {
            if (array_key_exists('offset', $link)) {
                $offset = $link['offset'];
                unset($link['offset']);
                if ($offset > $maxOffset) {
                    $maxOffset  = $offset;
                }
            } else {
                $maxOffset++;
                $offset = $maxOffset;
            }
            $links[$offset] = (object) $link;
        }
        ksort($links);
        $this->view->links = array_reverse($links);

        //prepare scripts into HeadScript helper format
        $maxOffset  = 0;
        $scripts    = array();
        foreach ($this->scripts as $script) {
            if (array_key_exists('offset', $script)) {
                $offset = $script['offset'];
                unset($script['offset']);
                if ($offset > $maxOffset) {
                    $maxOffset  = $offset;
                }
            } else {
                $maxOffset++;
                $offset = $maxOffset;
            }
            $scriptObj          = new \stdClass();
            $scriptObj->type    = $script['type'];
            $scriptObj->source  = null;
            unset($script['type']);
            $scriptObj->attributes = $script;
            $scripts[$offset]   = $scriptObj;
        }
        ksort($scripts);
        $this->view->scripts = array_reverse($scripts);

        $this->view->metas = $this->metas;
        $this->view->doctype = $this->doctype;
        $this->view->title = $this->title;
        $this->view->htmlAttributes = $this->htmlAttributes;
        $this->view->bodyAttributes = $this->bodyAttributes;

//TODO: should we validate metas?
//        /* @var $headMeta \Zend\View\Helper\HeadMeta */
//         foreach ($this->metas as $meta) {
//             if (isset($meta['name'])) {
//                 $headMeta->appendName($meta['name'], $meta['content']);
//             } elseif (isset($meta['http-equiv'])){
//                 $headMeta->appendHttpEquiv($meta['http-equiv'], $meta['content']);
//             } elseif (isset($meta['charset']) && $this->doctype == Doctype::HTML5) {
//                 $headMeta->setCharset($meta['charset']);
//             }
//         }
    }

    public function view()
    {
        $this->prepareView();
        return parent::view();
    }
}
