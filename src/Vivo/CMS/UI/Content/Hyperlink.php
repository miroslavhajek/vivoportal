<?php
namespace Vivo\CMS\UI\Content;

use Vivo\CMS\UI;
use Vivo\Util\RedirectEvent;

/**
 * UI to redirects to the URL.
 */
class Hyperlink extends UI\Component
{
    /**
     * Hyperlink initialization.
     */
    public function init()
    {
        $url = $this->content->getUrl();
        $this->getEventManager()->trigger(new RedirectEvent($url));

        //$this->redirector->redirect($url);
//TODO add meta redirect
//        $this->parent('Vivo\UI\Page')->metas[] = array(
//                'http-equiv' => 'Refresh', 'content' => '0;url=' . $this->url);
    }
}

