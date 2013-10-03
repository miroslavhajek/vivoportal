<?php
namespace Vivo\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Highlight
 */
class Highlight extends AbstractHelper
{
    public function __invoke($haystack, $needle)
    {
        $needle = preg_quote($needle);

        $escape   = $this->view->plugin('escapeHtml');
        $haystack = $escape($haystack);
        $needle   = $escape($needle);
        $haystack = preg_replace("/($needle)/ui", "<span class=\"match\">\${1}</span>", $haystack);

        return $haystack;
    }
}
