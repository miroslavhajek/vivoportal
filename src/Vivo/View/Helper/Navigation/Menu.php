<?php
namespace Vivo\View\Helper\Navigation;

use Zend\View\Helper\Navigation\Menu as ZfViewHelperMenu;
use Zend\Navigation\AbstractContainer;
use Zend\Navigation\Page\AbstractPage;

use RecursiveIteratorIterator;

/**
 * Helper for rendering menus from navigation containers
 */
class Menu extends ZfViewHelperMenu
{
    /**
     * Renders a normal menu (called from {@link renderMenu()})
     *
     * @param  AbstractContainer         $container    container to render
     * @param  string                    $ulClass      CSS class for first UL
     * @param  string                    $indent       initial indentation
     * @param  int|null                  $minDepth     minimum depth
     * @param  int|null                  $maxDepth     maximum depth
     * @param  bool                      $onlyActive   render only active branch?
     * @param  bool                      $escapeLabels Whether or not to escape the labels
     * @return string
     */
    protected function renderNormalMenu(AbstractContainer $container,
                                   $ulClass,
                                   $indent,
                                   $minDepth,
                                   $maxDepth,
                                   $onlyActive,
                                   $escapeLabels
    ) {
        $html = '';

        // find deepest active
        $found = $this->findActive($container, $minDepth, $maxDepth);
        if ($found) {
            $foundPage  = $found['page'];
            $foundDepth = $found['depth'];
        } else {
            $foundPage = null;
        }

        // create iterator
        $iterator = new RecursiveIteratorIterator($container,
                            RecursiveIteratorIterator::SELF_FIRST);
        if (is_int($maxDepth)) {
            $iterator->setMaxDepth($maxDepth);
        }

        // iterate container
        $prevDepth = -1;
        foreach ($iterator as $page) {
            $depth = $iterator->getDepth();
            $isActive = $page->isActive(true);
            if ($depth < $minDepth || !$this->accept($page)) {
                // page is below minDepth or not accepted by acl/visibility
                continue;
            } elseif ($onlyActive && !$isActive) {
                // page is not active itself, but might be in the active branch
                $accept = false;
                if ($foundPage) {
                    if ($foundPage->hasPage($page)) {
                        // accept if page is a direct child of the active page
                        $accept = true;
                    } elseif ($foundPage->getParent()->hasPage($page)) {
                        // page is a sibling of the active page...
                        if (!$foundPage->hasPages() ||
                            is_int($maxDepth) && $foundDepth + 1 > $maxDepth) {
                            // accept if active page has no children, or the
                            // children are too deep to be rendered
                            $accept = true;
                        }
                    }
                }

                if (!$accept) {
                    continue;
                }
            }

            // make sure indentation is correct
            $depth -= $minDepth;
            $myIndent = $indent . str_repeat('        ', $depth);

            if ($depth > $prevDepth) {
                // start new ul tag
                if ($ulClass && $depth ==  0) {
                    $ulClass = ' class="' . $ulClass . '"';
                } else {
                    $ulClass = '';
                }
                $html .= $myIndent . '<ul' . $ulClass . '>' . self::EOL;
            } elseif ($prevDepth > $depth) {
                // close li/ul tags until we're at current depth
                for ($i = $prevDepth; $i > $depth; $i--) {
                    $ind = $indent . str_repeat('        ', $i);
                    $html .= $ind . '    </li>' . self::EOL;
                    $html .= $ind . '</ul>' . self::EOL;
                }
                // close previous li tag
                $html .= $myIndent . '    </li>' . self::EOL;
            } else {
                // close previous li tag
                $html .= $myIndent . '    </li>' . self::EOL;
            }

            // render li tag and page
            $liClass    = $this->buildLiClass($page, $isActive);
            $html .= $myIndent . '    <li' . $liClass . '>' . self::EOL
                   . $myIndent . '        ' . '<span>' . $this->htmlify($page, $escapeLabels) . '</span>' . self::EOL;

            // store as previous depth for next iteration
            $prevDepth = $depth;
        }

        if ($html) {
            // done iterating container; close open ul/li tags
            for ($i = $prevDepth+1; $i > 0; $i--) {
                $myIndent = $indent . str_repeat('        ', $i-1);
                $html .= $myIndent . '    </li>' . self::EOL
                       . $myIndent . '</ul>' . self::EOL;
            }
            $html = rtrim($html, self::EOL);
        }

        return $html;
    }

    /**
     * Renders the deepest active menu within [$minDepth, $maxDepth], (called
     * from {@link renderMenu()})
     *
     * @param  AbstractContainer         $container    container to render
     * @param  string                    $ulClass      CSS class for first UL
     * @param  string                    $indent       initial indentation
     * @param  int|null                  $minDepth     minimum depth
     * @param  int|null                  $maxDepth     maximum depth
     * @param  bool                      $escapeLabels Whether or not to escape the labels
     * @return string                                  rendered menu
     */
    protected function renderDeepestMenu(AbstractContainer $container,
                                         $ulClass,
                                         $indent,
                                         $minDepth,
                                         $maxDepth,
                                         $escapeLabels
    ) {
        if (!$active = $this->findActive($container, $minDepth - 1, $maxDepth)) {
            return '';
        }

        // special case if active page is one below minDepth
        if ($active['depth'] < $minDepth) {
            if (!$active['page']->hasPages()) {
                return '';
            }
        } elseif (!$active['page']->hasPages()) {
            // found pages has no children; render siblings
            $active['page'] = $active['page']->getParent();
        } elseif (is_int($maxDepth) && $active['depth'] +1 > $maxDepth) {
            // children are below max depth; render siblings
            $active['page'] = $active['page']->getParent();
        }

        $ulClass = $ulClass ? ' class="' . $ulClass . '"' : '';
        $html = $indent . '<ul' . $ulClass . '>' . self::EOL;

        foreach ($active['page'] as $subPage) {
            if (!$this->accept($subPage)) {
                continue;
            }
            $isActive   = $subPage->isActive(true);
            $liClass    = $this->buildLiClass($subPage, $isActive);
            $html .= $indent . '    <li' . $liClass . '>' . self::EOL;
            $html .= $indent . '        ' . $this->htmlify($subPage, $escapeLabels) . self::EOL;
            $html .= $indent . '    </li>' . self::EOL;
        }

        $html .= $indent . '</ul>';

        return $html;
    }

    /**
     * Builds and returns class for the <li> tag
     * @param AbstractPage $page
     * @param boolean $isActive
     * @return string
     */
    protected function buildLiClass(AbstractPage $page, $isActive)
    {
        $liClasses  = array();
        //Class set with page
        if ($page->get('li_class')) {
            $liClasses[]    = $page->get('li_class');
        }
        //Active
        if ($isActive) {
            $liClasses[]    = 'active';
        }
        if (count($liClasses)) {
            $liClass    = sprintf(' class="%s"', implode(' ', $liClasses));
        } else {
            $liClass    = '';
        }
        return $liClass;
    }
}
