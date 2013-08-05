<?php
namespace Vivo\Backend\UI\Explorer;

use Vivo\Repository\Exception\EntityNotFoundException;
use Vivo\Service\Initializer\TranslatorAwareInterface;
use Vivo\UI\Alert;
use Vivo\UI\Component;
use Zend\EventManager\Event;
use Zend\I18n\Translator\Translator;

class Finder extends Component implements TranslatorAwareInterface
{

    /**
     * @var ExplorerInterface
     */
    protected $explorer;

    /**
     * @var \Vivo\CMS\Model\Entity
     */
    protected $entity;

    /**
     * @var Translator
     */
    protected $translator;


    public function init()
    {
        $this->entity = $this->explorer->getEntity();
    }

    /**
     * (non-PHPdoc)
     * @see \Vivo\UI\Component::view()
     */
    public function view()
    {
        $this->view->entity = $this->entity;
        return parent::view();
    }

    public function setExplorer(ExplorerInterface $explorer)
    {
        $this->explorer = $explorer;
    }

    /**
     * Return current entity.
     * @return \Vivo\CMS\Model\Entity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Sets Alert component.
     * @param Alert $alert
     */
    public function setAlert(Alert $alert)
    {
        $this->alert = $alert;
    }

    public function setTranslator(Translator $translator)
    {
        $this->translator = $translator;
    }
}
