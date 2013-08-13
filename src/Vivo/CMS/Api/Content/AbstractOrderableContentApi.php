<?php
namespace Vivo\CMS\Api\Content;

use Vivo\CMS\Api;
use Vivo\CMS\Api\Exception\InvalidArgumentException;
use Vivo\CMS\Model\PathInterface;
use Vivo\CMS\Model\Entity;
use Vivo\CMS\Model\Content;
use Vivo\Indexer\IndexerInterface;
use Vivo\Indexer\QueryBuilder;
use Vivo\Storage\PathBuilder\PathBuilderInterface;
use Vivo\Stdlib\OrderableInterface;

abstract class AbstractOrderableContentApi
{
    /**
     * @var \Vivo\CMS\Api\CMS
     */
    protected $cmsApi;

    /**
     * @var \Vivo\CMS\Api\Content\File
     */
    protected $fileApi;

    /**
     * @var \Vivo\Indexer\IndexerInterface
     */
    protected $indexer;

    /**
     * @var Vivo\Storage\PathBuilder\PathBuilderInterface
     */
    protected $pathBuilder;

    /**
     * Constructor.
     *
     * @param \Vivo\CMS\Api\CMS $cmsApi
     * @param \Vivo\CMS\Api\Content\File $fileApi
     * @param \Vivo\Indexer\IndexerInterface $indexer
     * @param \Vivo\Storage\PathBuilder\PathBuilderInterface $pathBuilder
     */
    public function __construct(
            Api\CMS $cmsApi,
            Api\Content\File $fileApi,
            IndexerInterface $indexer,
            PathBuilderInterface $pathBuilder)
    {
        $this->cmsApi = $cmsApi;
        $this->fileApi = $fileApi;
        $this->indexer = $indexer;
        $this->pathBuilder = $pathBuilder;
    }

    /**
     * Returns CMS entity by UUID.
     *
     * @param string $ident
     * @return \Vivo\CMS\Model\Content\Fileboard\Media
     */
    public function getEntity($ident)
    {
        return $this->cmsApi->getEntity($ident);
    }

    /**
     * Removes entity.
     *
     * @param \Vivo\CMS\Model\Entity $entity
     */
    public function removeEntity(Entity $entity)
    {
        $this->cmsApi->removeEntity($entity);
    }

    /**
     * Saves entity.
     *
     * @param \Vivo\CMS\Model\Entity $entity
     */
    public function saveEntity(Entity $entity)
    {
        $this->cmsApi->saveEntity($entity, true);
    }

    /**
     * Prepare fileboard content before saving.
     *
     * @param \Vivo\CMS\Model\PathInterface $parentModel
     * @param \Vivo\CMS\Model\PathInterface $media
     * @return \Vivo\CMS\Model\Entity
     */
    protected function prepareMediaForSaving(PathInterface $parentModel, PathInterface $media)
    {
        $qb = new QueryBuilder();
        $condition = $qb->cond($parentModel->getPath().'/*', '\path');
        $hits = $this->indexer->find($condition)->getHits();

        $i = -1;
        foreach ($hits as $hit) {
            $path = $hit->getDocument()->getField('\path')->getValue();
            $parts = $this->pathBuilder->getStoragePathComponents($path);
            $lastI = $parts[count($parts) - 1];
            $i = max($i, $lastI);
        }

        $path = $this->pathBuilder->buildStoragePath(array($parentModel->getPath(), ++$i), true, false, false);
        $media->setPath($path);

        return $media;
    }

    /**
     * Swap order.
     *
     * @param \Vivo\Stdlib\OrderableInterface $entity1
     * @param \Vivo\Stdlib\OrderableInterface $entity2
     */
    public function swap(OrderableInterface $entity1, OrderableInterface $entity2)
    {
        $tmpOrder = $entity1->getOrder();
        $entity1->setOrder($entity2->getOrder());
        $entity2->setOrder($tmpOrder);

        $this->saveEntity($entity1);
        $this->saveEntity($entity2);
    }

    /**
     * Return max order or -1 if array is empty.
     *
     * @throws \Vivo\CMS\Api\Exception\InvalidArgumentException
     * @param array <\Vivo\Stdlib\OrderableInterface>
     * @return int
     */
    public function getMaxOrder(array $files)
    {
        $max = -1;
        foreach ($files as $key=>$file) {
            if(!$file instanceof OrderableInterface) {
                throw new InvalidArgumentException(
                    sprintf('Item {%s} is not instance of \Vivo\Stdlib\OrderableInterface', $key));
            }

            $max = max($max, $file->getOrder());
        }

        return $max;
    }

    /**
     * Moves up an entity in array and returns new sortered array.
     *
     * @param array $files
     * @param \Vivo\Stdlib\OrderableInterface $entity
     * @return array
     */
    public function moveUp(array $files, OrderableInterface $entity)
    {
        $i = $this->getFileKeyById($files, $entity->getUuid());
        $j = $i - 1;

        if(isset($files[$j])) {
            $entity2 = $files[$j];

            $this->swap($entity, $entity2);

            $files[$i] = $entity2;
            $files[$j] = $entity;
        }

        return $files;
    }

    /**
     * Moves down an entity in array and returns new sortered array.
     *
     * @param array <\Vivo\CMS\Model\Entity> $files
     * @param \Vivo\Stdlib\OrderableInterface $entity
     * @return array <\Vivo\CMS\Model\Entity>
     */
    public function moveDown(array $files, OrderableInterface $entity)
    {
        $i = $this->getFileKeyById($files, $entity->getUuid());
        $j = $i + 1;

        if(isset($files[$j])) {
            $entity2 = $files[$j];

            $this->swap($entity, $entity2);

            $files[$i] = $entity2;
            $files[$j] = $entity;
        }

        return $files;
    }

    /**
     * @param array <\Vivo\CMS\Model\Entity> $files
     * @param string $uuid Entity UUID.
     * @return int
     */
    private function getFileKeyById(array $files, $uuid)
    {
        for ($i = 0; $i < count($files); $i++) {
            if ($files[$i]->getUuid() == $uuid) {
                break;
            }
        }

        return $i;
    }

    /**
     * Sends HTTP headers and print file content.
     *
     * @param \Vivo\CMS\Model\Content\File $file
     */
    public function download(Content\File $file)
    {
        $this->fileApi->download($file);
    }

    /**
     * Sends HTTP headers and print file content.
     *
     * @param string $uuid
     */
    public function downloadByUuid($uuid)
    {
        $media = $this->cmsApi->getEntity($uuid);
        $this->download($media);
    }

    /**
     * Returns content of entity resource.
     *
     * @param \Vivo\CMS\Model\Content\File $file
     * @return string
     */
    public function getResource(Content\File $file)
    {
        return $this->fileApi->getResource($file);
    }

    /**
     * Returns input stream for resource of entity.
     *
     * @param \Vivo\CMS\Model\Content\File $file
     * @return \Vivo\IO\InputStreamInterface
     */
    public function readResource(Content\File $file)
    {
        return $this->fileApi->readResource($file);
    }

}
