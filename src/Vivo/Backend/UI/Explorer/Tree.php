<?php
namespace Vivo\Backend\UI\Explorer;

use Vivo\CMS\Api;
use Vivo\CMS\Model\Folder;
use Vivo\UI\Component;
use Vivo\Util\DataTree;

/**
 * Tree of documents.
 */
class Tree extends Component
{
    /**
     * @var ExplorerInterface
     */
    protected $explorer;

    /**
     * @var Api\CMS
     */
    protected $cmsApi;

    /**
     * @var Api\Document
     */
    protected $documentApi;

    /**
     * Tree options
     * @var array
     */
    protected $options  = array(
        'max_items'     => 20,
    );

    /**
     * Constuctor.
     * @param Api\CMS $cmsApi
     * @param Api\Document $documentApi
     * @param array $options
     */
    public function __construct(Api\CMS $cmsApi, Api\Document $documentApi, $options = array())
    {
        $this->cmsApi       = $cmsApi;
        $this->documentApi  = $documentApi;
        $this->options      = array_merge($this->options, $options);
    }

    /**
     * Returns root document of site.
     * @return \Vivo\CMS\Model\Folder
     */
    protected function getSiteRoot()
    {
        $site = $this->explorer->getSite();
        return $this->cmsApi->getSiteEntity('', $site);
    }

    /**
     * Inject Explorer.
     * @param ExplorerInterface $explorer
     */
    public function setExplorer(ExplorerInterface $explorer)
    {
        $this->explorer = $explorer;
    }

    /**
     * (non-PHPdoc)
     * @see \Vivo\UI\Component::view()
     */
    public function view()
    {
        $this->view->tree = $this->getDocumentTree($this->getSiteRoot(), $this->explorer->getEntity()->getPath());
        $this->view->explorerAction = $this->explorer->getExplorerAction();
        return parent::view();
    }

    /**
     * Action for get subtree by AJAX.
     * @param string $relPath
     * @return \Zend\View\Model\ViewModel
     */
    public function getSubtree($relPath)
    {
        $this->view->setTemplate(__CLASS__.':Subtree');
         $folder = $this->cmsApi->getSiteEntity($relPath, $this->explorer->getSite());
        $tree = $this->getDocumentTree($folder, $folder->getPath());
        $nodes = $tree->getChildrenNodes();
        $this->view->tree = reset($nodes);
        $this->view->explorerAction = $this->explorer->getExplorerAction();
        return parent::view();
    }

    /**
     * Returs tree of document using DataTree structure.
     *
     * @param Folder $rootFolder
     * @param string $expandedPath
     * @return \Vivo\Util\DataTree
     */
    protected function getDocumentTree(Folder $rootFolder, $expandedPath = '')
    {
        $que = new \SplQueue();
        $tree = new DataTree($rootFolder);
        $root = new DataTree();
        $root->addChild($tree);
        $que->push($tree);
        while(!$que->isEmpty()){
            /* @var $node  DataTree */
            $node = $que->pop();
            $child = $node->value;
            if (!$child) continue;
            $children = $this->documentApi->getChildDocuments($child);
            $children = $this->documentApi->sortDocumentsByCriteria($children, $child->getSorting());
            $a = array ();
            $a['document'] = $child;
            if ($child instanceof \Vivo\CMS\Model\Document) {
                $a['published'] = $this->documentApi->isPublished($child);
            } else { // Folder
                $a['published'] = true;
            }
            $a['level'] = $node->getDeep();
            $a['rel_path'] = $this->cmsApi->getEntityRelPath($child);
            $a['active'] = $child->getPath() == $expandedPath;
            $a['active_path'] = (strpos($expandedPath, $child->getPath()) !== false);
            $a['expandable'] = (boolean) count($children);
            $a['count'] = count($children);

            if ($node->getParent() && isset($node->getParent()->value['document'])) {
                $a['parent_rel_path'] = $this->cmsApi->getEntityRelPath(
                        $node->getParent()->value['document']);
            }
            $node->value = $a;
            $i = 0;
            $expand = $expandedPath == $child->getPath() ||
                    (strpos($expandedPath, $child->getPath(). '/') !== false);
            if ($expand) {
                foreach ($children as $child) {
                    if (++$i > $this->options['max_items']) {
                        break;
                    }
                    $childNode = new DataTree($child);
                    $node->addChild($childNode);
                    $que->push($childNode);
                }
            }
        }
        return $root;
    }
}
