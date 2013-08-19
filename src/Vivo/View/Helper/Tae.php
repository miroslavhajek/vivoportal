<?php
namespace Vivo\View\Helper;

use Zend\View\Helper\AbstractHelper as AbstractViewHelper;

/**
 * Tae
 * Translate And Escape view helper
 */
class Tae extends AbstractViewHelper
{
    /**
     * Direct invocation of the view helper
     * @param string|null $text
     * @param array $params
     * @return $this|void
     */
    public function __invoke($text = null, array $params = array())
    {
        if (is_null($text)) {
            return $this;
        }
        return $this->render($text, $params);
    }

    /**
     * Returns translated and escaped text
     * @param $text
     * @param array $params
     * @return string
     */
    public function render($text, array $params = array())
    {
        $translate  = $this->view->plugin('translate');
        $escape     = $this->view->plugin('escapeHtml');
        $translated = $translate($text);
        if (count($params) > 0) {
            $translated = vsprintf($translated, $params);
        }
        $escaped    = $escape($translated);
        return $escaped;
    }
}
