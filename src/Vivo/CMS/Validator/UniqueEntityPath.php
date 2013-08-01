<?php
namespace Vivo\CMS\Validator;

use Vivo\Storage\PathBuilder\PathBuilderInterface;
use Vivo\Transliterator\TransliteratorInterface;
use Vivo\Repository\RepositoryInterface;
use Vivo\Service\Initializer\PathBuilderAwareInterface;
use Vivo\Service\Initializer\DocTitleToPathTransliteratorAwareInterface;
use Vivo\Service\Initializer\RepositoryAwareInterface;

use Zend\Validator\AbstractValidator;

/**
 * Unique entity path validator
 */
class UniqueEntityPath extends AbstractValidator implements PathBuilderAwareInterface,
                                                            DocTitleToPathTransliteratorAwareInterface,
                                                            RepositoryAwareInterface
{
    const UNIQUE = 'unique';

    /**
     * Array of message templates
     * @var array
     */
    protected $messageTemplates = array(
        self::UNIQUE => "Name '%value%' must form unique path",
    );

    /**
     * Path Builder
     * @var PathBuilderInterface
     */
    protected $pathBuilder;

    /**
     * Transliterator for document title to path conversion
     * @var TransliteratorInterface
     */
    protected $transliteratorDocTitleToPath;

    /**
     * Repository
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * Parent document path
     * @var string
     */
    protected $parentDocumentPath;

    /**
     * Sets parent document path
     * @param string $parentDocumentPath
     */
    public function setParentDocumentPath($parentDocumentPath)
    {
        $this->parentDocumentPath = $parentDocumentPath;
    }

    /**
     * Sets path builder
     * @param \Vivo\Storage\PathBuilder\PathBuilderInterface $pathBuilder
     */
    public function setPathBuilder(PathBuilderInterface $pathBuilder)
    {
        $this->pathBuilder = $pathBuilder;
    }

    /**
     * Sets repository
     * @param \Vivo\Repository\RepositoryInterface $repository
     */
    public function setRepository(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Sets transliterator
     * @param \Vivo\Transliterator\TransliteratorInterface $transliteratorDocTitleToPath
     */
    public function setTransliterator(TransliteratorInterface $transliteratorDocTitleToPath)
    {
        $this->transliteratorDocTitleToPath = $transliteratorDocTitleToPath;
    }

    /**
     * Returns true if and only if the path of the currently created entity does not exist
     * @param  string $value Last part of document path
     * @return boolean
     */
    public function isValid($value)
    {
        $isValid = true;
        $value = trim($value);
        $this->setValue($value);

        $valueTranslit  = $this->transliteratorDocTitleToPath->transliterate($value);
        $path = $this->pathBuilder->buildStoragePath(array($this->parentDocumentPath, $valueTranslit));

        if ($this->repository->hasEntity($path)) {
            $isValid = false;
            $this->error(self::UNIQUE);
        }
        return $isValid;
    }

}
