<?php
namespace Vivo\Service\Initializer;

use Vivo\Transliterator\TransliteratorInterface;

/**
 * Interface for injecting DocTitleToPathTransliterator
 */
interface DocTitleToPathTransliteratorAwareInterface
{
    public function setTransliterator(TransliteratorInterface $transliterator);
}
