<?php
namespace Vivo\CMS\Api;

use Vivo\CMS\Model;
use Vivo\CMS\Exception;

/**
 * DocumentInterface
 * Document API interface
 */
interface DocumentInterface
{
    /**
     * Returns array of published contents of given document.
     * @param Model\Document $document
     * @return Model\Content[]
     */
    public function getPublishedContents(Model\Document $document);

    /**
     * Returns array of published content types (class names of published contents)
     * If there are no published contents, returns an empty array
     * @param \Vivo\CMS\Model\Document $document
     * @return string[]
     */
    public function getPublishedContentTypes(Model\Document $document);

    /**
     * Finds published content in ContentContainer,
     * @param Model\ContentContainer $container
     * @return Model\Content|boolean
     * @throws Exception\LogicException when there is more than one published content
     */
    public function getPublishedContent(Model\ContentContainer $container);

    /**
     * @param Model\Content $content
     */
    public function publishContent(Model\Content $content);

    /**
     * @return array
     */
    public function getWorkflowStates();

    /**
     * @return array
     */
    public function getWorkflowAvailableStates();

    /**
     * Sets a workflow state to the content
     * @param Model\Content $content
     * @param string $state
     * @throws \Vivo\CMS\Exception\InvalidArgumentException
     */
    public function setWorkflowState(Model\Content $content, $state);

    /**
     * Returns document for the given content
     * @param Model\Content $content
     * @return Model\Document
     */
    public function getContentDocument(Model\Content $content);

    public function addDocumentContent(Model\Document $document, Model\Content $content, $index = 0);

    /**
     * @param Model\Document $document
     * @return array <\Vivo\CMS\Model\ContentContainer>
     */
    public function getContentContainers(Model\Document $document);

    /**
     * @param Model\Document $document
     * @param int $index
     * @throws \Vivo\CMS\Exception\InvalidArgumentException
     * @return array
     */
    public function getDocumentContents(Model\Document $document, $index/*, $state {PUBLISHED}*/);

    /**
     * @param Model\Folder $parent
     * @param Model\Folder $document
     * @param string $nameInPath
     * @return \Vivo\CMS\Model\Document
     */
    public function createDocument(Model\Folder $parent, Model\Folder $document, $nameInPath = null);

    /**
     * Copies document to a new location
     * @param \Vivo\CMS\Model\Folder $document
     * @param \Vivo\CMS\Model\Site $site
     * @param string $targetUrl
     * @param string $targetName
     * @param string $title
     * @return Model\Document
     */
    public function copyDocument(Model\Folder $document, Model\Site $site, $targetUrl, $targetName, $title);

    /**
     * @param Model\Folder $document
     * @param \Vivo\CMS\Model\Site $site
     * @param string $targetUrl
     * @param string $targetName
     * @param string $title
     * @param boolean $createHyperlink
     * @return Model\Folder
     */
    public function moveDocument(Model\Folder $document, Model\Site $site, $targetUrl, $targetName, $title,
                                 $createHyperlink);

    /**
     * Saves document
     * @param Model\Folder $document
     * @return Model\Folder
     */
    public function saveDocument(Model\Folder $document);

    /**
     * Returns child documents.
     * @param \Vivo\CMS\Model\Folder $document
     * @return Model\Document[]
     */
    public function getChildDocuments(Model\Folder $document);

    /**
     * Returns number of contents the document has
     * @param \Vivo\CMS\Model\Document $document
     * @return integer
     */
    public function getContentCount(Model\Document $document);

    /**
     * @param Model\ContentContainer $container
     * @return array <\Vivo\CMS\Model\Content>
     */
    public function getContentVersions(Model\ContentContainer $container);

    /**
     * Returns if the document has any child documents
     * @param \Vivo\CMS\Model\Folder $document
     * @return boolean
     */
    public function hasChildDocuments(Model\Folder $document);

    /**
     * @return array
     */
    public function getAvailableLanguages();

    /**
     * Returns an array of documents on the branch from $rootPath to $document
     * If root path is not on the branch, returns an empty array
     * @param \Vivo\CMS\Model\Document $leaf
     * @param string $rootPath
     * @param bool $includeRoot
     * @param bool $includeLeaf
     * @return Model\Document[]
     */
    public function getDocumentsOnBranch(Model\Document $leaf, $rootPath = '/', $includeRoot = true,
                                         $includeLeaf = true);

    /**
     * Sort array of documents/folders by specified criteria.
     * Structure of input array may be as follows:
     * 1) Model\Folder[]
     * 2) structured array:
     * array(
     *     'doc' => Model\Folder,
     *     'children' => array(...)
     * )
     *
     * @param array $documents Array of documents/folders
     * @param string $criteriaString Criteria determinates how to sort given documents Example('title:asc')
     * @return array Sorted array of documents structured the same way as input array
     */
    public function sortDocumentsByCriteria(array $documents, $criteriaString);
}
