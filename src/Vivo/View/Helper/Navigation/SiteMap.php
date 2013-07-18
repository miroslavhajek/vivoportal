<?php
namespace Vivo\View\Helper\Navigation;

use Zend\Navigation\Page\AbstractPage;

/**
 * Helper for rendering sitemaps from navigation containers
 */
class SiteMap extends Menu
{

    /**
     * Overrides parent's implementation.
     * If $page 'showDescription' is enabled, append page description in 'p' tag
     *
     * @param  AbstractPage $page   page to generate HTML for
     * @param bool $escapeLabel     Whether or not to escape the label
     * @return string               HTML string for the given page
     */
    public function htmlify(AbstractPage $page, $escapeLabel = true)
    {
        $html = parent::htmlify($page, $escapeLabel);

        if ($page->get('showDescription')) {
            $html .= '<p>' . $page->get('document')->getDescription() . '</p>';
        }
        return $html;
    }

}
