<?php
namespace Vivo\Backend\UI\Explorer;

use Vivo\CMS\Model\Entity;
use Vivo\CMS\UI\AbstractForm;
use Vivo\CMS\Api\CMS;
use Vivo\CMS\Api\DocumentInterface as DocumentApiInterface;
use Vivo\Backend\Form\Move as MoveForm;
use Vivo\Storage\PathBuilder\PathBuilderInterface;
use Vivo\CMS\Model\Document;
use Vivo\Util\RedirectEvent;
use Vivo\UI\Alert;
use Vivo\Service\Initializer\TranslatorAwareInterface;
use Vivo\CMS\Exception\EntityAlreadyExistsException;

use Zend\I18n\Translator\Translator;

/**
 * Move
 */
class Move extends AbstractForm implements TranslatorAwareInterface
{
    /**
     * CMS API
     * @var CMS
     */
    protected $cmsApi;

    /**
     * Document API
     * @var DocumentApiInterface
     */
    protected $documentApi;

    /**
     * Path Builder
     * @var PathBuilderInterface
     */
    protected $pathBuilder;

    /**
     * Alert UI Component
     * @var Alert
     */
    protected $alert;

    /**
     * Explorer
     * @var Explorer
     */
    protected $explorer;

    /**
     * Current entity
     * @var Entity
     */
    protected $entity;

    /**
     * Translator
     * @var Translator
     */
    protected $translator;

    /**
     * Constructor
     * @param \Vivo\CMS\Api\CMS $cmsApi
     * @param \Vivo\CMS\Api\DocumentInterface $documentApi
     * @param \Vivo\Storage\PathBuilder\PathBuilderInterface $pathBuilder
     * @param \Vivo\UI\Alert $alert
     */
    public function __construct(CMS $cmsApi, DocumentApiInterface $documentApi, PathBuilderInterface $pathBuilder,
                                Alert $alert)
    {
        $this->cmsApi       = $cmsApi;
        $this->documentApi  = $documentApi;
        $this->pathBuilder  = $pathBuilder;
        $this->alert        = $alert;
    }

    public function init()
    {
        $this->explorer = $this->getParent();
        $this->entity   = $this->explorer->getEntity();
        parent::init();
    }

    public function view()
    {
        $this->getView()->entity = $this->entity;
        $this->getView()->entityRelPath = $this->cmsApi->getEntityRelPath($this->entity);
        return parent::view();
    }

    /**
     * Move action
     */
    public function move()
    {
        $form   = $this->getForm();
        if ($form->isValid()) {
            $validData  = $form->getData();
            //Move - and redirect
            $doc        = $this->entity;
            $docRelPath = $this->cmsApi->getEntityRelPath($doc);
            try {
                if (isset($validData['create_hyperlink']) && $validData['create_hyperlink']) {
                    $createHyperlink    = true;
                } else {
                    $createHyperlink    = false;
                }
                $movedDoc   = $this->documentApi->moveDocument($doc, $this->explorer->getSite(), $validData['path'],
                    $validData['name_in_path'], $validData['name'], $createHyperlink);
                $movedDocRelPath   = $this->cmsApi->getEntityRelPath($movedDoc);
                $this->explorer->setEntity($movedDoc);
                $this->explorer->setCurrent('editor');
                $message = sprintf($this->translator->translate(
                    "Document at path '%s' has been moved to path '%s'"), $docRelPath, $movedDocRelPath);
                $this->alert->addMessage($message, Alert::TYPE_SUCCESS);
                $this->events->trigger(new RedirectEvent());
            } catch (EntityAlreadyExistsException $e) {
                $message = $this->translator->translate("An entity already exists at the target path");
                $this->alert->addMessage($message, Alert::TYPE_ERROR);
            }
        } else {
            $message = $this->translator->translate("Form data is not valid");
            $this->alert->addMessage($message, Alert::TYPE_ERROR);
        }

    }

    /**
     * Creates form and returns it
     * Factory method
     * @return MoveForm
     */
    protected function doGetForm()
    {
        $form   = new MoveForm($this->entity);
        $form->setAttribute('action', $this->request->getUri()->getPath());
        $form->add(array(
            'name'  => 'act',
            'attributes'    => array(
                'type'  => 'hidden',
                'value' => $this->getPath('move'),
            ),
        ));
        $relPath    = $this->cmsApi->getEntityRelPath($this->entity);
        $path       = $this->pathBuilder->dirname($relPath);
        $baseName   = $this->pathBuilder->basename($relPath);

        $form->get('path')->setValue($path);
        $form->get('name')->setValue($this->entity->getTitle());
        $form->get('name_in_path')->setValue($baseName);
        return $form;
    }

    /**
     * Injects translator
     * @param \Zend\I18n\Translator\Translator $translator
     */
    public function setTranslator(Translator $translator)
    {
        $this->translator   = $translator;
    }
}
