<?php
namespace Vivo\CMS\Model;

use DateTime;

/**
 * The document represents a folder in tree. The document contains functions for working with content and sub-documents
 */
class Document extends Folder
{

    const ALL = 0;
    const PUBLISHED = 1;
    const AVAILABLE = 2;

    /**
     * URI of the document. If there is not specifically specified, URI is always a straight path to the document.
     * (relative to the root document ROOT).
     *
     * @var string
     */
    protected $uri;

    /**
     * @var bool URI takes precedence.
     *
     * Specific URI takes precedence (is primary)
     */
    protected $uriPrecedence;

    /**
     * @var string Page header.
     */
    protected $heading;

    /**
     * @var string Title which can be seen in overview
     */
    protected $overviewTitle;

    /**
     * @var string Title which can be seen in navigation
     */
    protected $navigationTitle;

    /**
     * Keywords are used to describe content of the document.
     * Keywords could make fulltext searches faster and more effective.
     * Please separate each word by comma.
     *
     * @var string
     */
    protected $keywords;

    /**
     * Is used to display the contents of the document as a page, when you open the URL on the front-end.
     * Settings (assignment) of the layout makes sense only for documents whose content is displayed in HTML format.
     *
     * @var string
     * @example Layouts/page/subpage
     */
    protected $layout;

    /**
     * @var array Panels in layout.
     */
    protected $layoutPanels = array();

    /**
     * If this property is set, the document will appear in the lists of sub-documents (subpages)
     * on the front-end (overviews, sitemaps, menu, navigation, etc.)
     * @var bool
     */
    protected $navigable;

    /**
     * @var bool If it's set, changes in contents of the document is automatically saved as a new version.
     */
    protected $autoVersioning = false;

    /**
     * @var bool Secured (HTTPS required)
     */
    protected $secured;

    /**
     * Attributes for link tag (A).
     * @var array
     */
    protected $linkAttributes = array();

    /**
     * @var array Use document as vocabulary term in Vocalbulary content types
     */
    protected $vocabularies;

    /**
     * Expiration of the contents of the document - if set, the output display of the contents of the document
     * is saved to cache and will be displayed within the expiration period from there.
     * It allows you to accelerate the display of documents,
     * containing programming which are time-consuming for processing (eg, presenting data from an external database).
     * @var int Expiration (in seconds)
     */
    protected $expiration;

    /**
     * @var string Forkflow class full name.
     * @example Vivo\CMS\Workflow\Basic
     */
    protected $workflow;

    /**
     * Resource image name.
     * Image could be shown for instance in document listings if template of the listings supports it.
     *
     * @var string
     * @example image.jpg
     */
    protected $image;

    /**
     * Date and time when the document was published.
     * Typically it is used for articles, newsletters and press releases.
     * Unless explicitly specified otherwise, the system fills in the date of creation of the document in the system.
     *
     * @var DateTime
     */
    protected $published;

    /**
     * Name of the person who actually created the document.
     * It is used typically for articles, newsletters and press releases.
     * Unless explicitly specified otherwise, the system fills in a name of the logged editor.
     * @var string
     */
    protected $author;

    /**
     * Internal publising notices
     *
     * @var string
     */
    protected $internalNotice;

    /**
     * Template used to render the content container
     * If not set, uses the default template
     * @var string
     */
    protected $contentContainerTemplate;

    /**
     * Should the view model of the corresponding front UI component be injected into the Layout view model?
     * This is useful when direct manipulation of the front component view model vars is necessary in the
     * layout template (e.g. when placing individual logical contents in specific places in the layout)
     * @var bool
     */
    protected $injectComponentViewModelToLayout     = false;

    /**
     * @param string $path Repository path.
     * @param null $security
     */
    public function __construct($path = null, $security = null)
    {
        parent::__construct($path, $security);
    }

    /**
     * Sets heading property which is the same as title by default
     *
     * @param string $heading
     */
    public function setHeading($heading)
    {
        $this->heading = $heading;
    }

    /**
     * Page header. If heading is not set, default document name will be returned.
     *
     * @return string
     */
    public function getHeading()
    {
        return $this->heading;
    }

    /**
     * Returns heading otherwise document title.
     *
     * @return string
     */
    public function getHeadingSafe()
    {
        return $this->getHeading() ? : $this->getTitle();
    }

    /**
     * Sets title which can be seen in overview.
     *
     * @param string $overviewTitle
     */
    public function setOverviewTitle($overviewTitle)
    {
        $this->overviewTitle = $overviewTitle;
    }

    /**
     * Document overview title. If overview title is not set, document title will be returned.
     *
     * @return string
     */
    public function getOverviewTitle()
    {
        return $this->overviewTitle;
    }

    /**
     * Returns overview title otherwise document title.
     *
     * @return string
     */
    public function getOverviewTitleSafe()
    {
        return $this->getOverviewTitle() ? : $this->getTitle();
    }

    /**
     * Sets title which can be seen in overview
     *
     * @param string $overviewTitle
     */
    public function setNavigationTitle($navigationTitle)
    {
        $this->navigationTitle = $navigationTitle;
    }

    /**
     * Document navigation title. If navigation title is not set, document title will be returned.
     *
     * @return string
     */
    public function getNavigationTitle()
    {
        return $this->navigationTitle;
    }

    /**
     * Returns navigation title otherwise document title.
     *
     * @return string
     */
    public function getNavigationTitleSafe()
    {
        return $this->getNavigationTitle() ? : $this->getTitle();
    }

    /**
     * @param string $keywords
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
    }

    /**
     * @return string
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * @param string $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    public function setWorkflow($workflow)
    {
        $this->workflow = $workflow;
    }

    /**
     * Returns workflow.
     * @return string
     */
    public function getWorkflow()
    {
        return $this->workflow;
    }

    /**
     * Returns a key chain for indexer. Through this chain, the document sought.
     *
     * @param array $field_names Field names will be indexed.
     * @return string
     */
    public function getTextContent($field_names = array())
    {
        return parent::getTextContent(
                array_merge($field_names, array('title', 'keywords')));
    }

    public function getType()
    {
        return get_class($this);
    }

    /**
     * Returns relPath of layout document.
     *
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * @param string $layout
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
    }

    /**
     * Returns layout panels.
     * @return array of document paths
     */
    public function getLayoutPanels()
    {
        return $this->layoutPanels;
    }

    /**
     * Returns document description.
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets document description.
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param string $uri
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }

    public function getPublished()
    {
        return $this->published;
    }

    public function setPublished(\DateTime $published = null)
    {
        $this->published = $published;
    }

    public function getUriPrecedence() {
        return $this->uriPrecedence;
    }

    public function setUriPrecedence($uriPrecedence) {
        $this->uriPrecedence = $uriPrecedence;
    }

    public function getNavigable() {
        return $this->navigable;
    }

    public function setNavigable($navigable) {
        $this->navigable = $navigable;
    }

    public function getAutoVersioning() {
        return $this->autoVersioning;
    }

    public function setAutoVersioning($autoVersioning) {
        $this->autoVersioning = $autoVersioning;
    }

    public function getSecured() {
        return $this->secured;
    }

    public function setSecured($secured) {
        $this->secured = $secured;
    }

    public function getLinkAttributes() {
        return $this->linkAttributes;
    }

    public function setLinkAttributes($linkAttributes) {
        $this->linkAttributes = $linkAttributes;
    }

    public function getVocabularies() {
        return $this->vocabularies;
    }

    public function setVocabularies($vocabularies) {
        $this->vocabularies = $vocabularies;
    }

    public function getExpiration() {
        return $this->expiration;
    }

    public function setExpiration($expiration) {
        $this->expiration = $expiration;
    }

    public function getInternalNotice() {
        return $this->internalNotice;
    }

    public function setInternalNotice($internalNotice) {
        $this->internalNotice = $internalNotice;
    }

    /**
     * Sets the content container template
     * @param string $contentContainerTemplate
     */
    public function setContentContainerTemplate($contentContainerTemplate)
    {
        $this->contentContainerTemplate = $contentContainerTemplate;
    }

    /**
     * Returns the content container template
     * @return string
     */
    public function getContentContainerTemplate()
    {
        return $this->contentContainerTemplate;
    }

    /**
     * Sets if the component view model should be injected into the layout view model
     * @param boolean $injectComponentViewModelToLayout
     */
    public function setInjectComponentViewModelToLayout($injectComponentViewModelToLayout)
    {
        $this->injectComponentViewModelToLayout = $injectComponentViewModelToLayout;
    }

    /**
     * Returns if the component view model should be injected into the layout view model
     * @return boolean
     */
    public function getInjectComponentViewModelToLayout()
    {
        return $this->injectComponentViewModelToLayout;
    }

    /**
     * Exchange internal values containing symbolic refs / URLs from provided array
     * @param  array $data
     * @return void
     */
    public function exchangeArraySymRef(array $data)
    {
        //Layout
        if (array_key_exists('layout', $data)) {
            $this->setLayout($data['layout']);
        }
        //Internal notice
        if (array_key_exists('internal_notice', $data)) {
            $this->setInternalNotice($data['internal_notice']);
        }
        //URL
        if (array_key_exists('url', $data)) {
            $this->setUri($data['url']);
        }
        parent::exchangeArraySymRef($data);
    }

    /**
     * Return an array representation of the object's properties containing symbolic refs / URLs
     * @return array
     */
    public function getArrayCopySymRef()
    {
        $data                       = parent::getArrayCopySymRef();
        $data['layout']             = $this->getLayout();
        $data['internal_notice']    = $this->getInternalNotice();
        $data['url']                = $this->getUri();
        return $data;
    }
}
