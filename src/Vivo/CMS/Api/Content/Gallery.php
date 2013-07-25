<?php
namespace Vivo\CMS\Api\Content;

use Vivo\CMS\Model\Content;
use Vivo\CMS\Model\Content\Gallery\Media;
use Vivo\Indexer\QueryBuilder;
use Vivo\IO\FileInputStream;
use Vivo\CMS\Api\Exception\InvalidPathException;

class Gallery extends AbstractOrderableContentApi
{
    /**
     * Returns all gallery items.
     * Max items is 9999.
     *
     * @param \Vivo\CMS\Model\Content\Gallery $model
     * @throws \Vivo\CMS\Api\Exception\InvalidPathException
     * @return array
     */
    public function getList(Content\Gallery $model)
    {
        $return = array();

        if(!$model->getPath()) {
            throw new InvalidPathException('Entity path is not set');
        }

        $qb = new QueryBuilder();
        $condition = $qb->cond($model->getPath().'/*', '\path');
        $hits      = $this->indexer
                          ->find($condition, array('sort'=>array('\order'), 'page_size'=>9999))
                          ->getHits();

        foreach ($hits as $hit) {
            $path     = $hit->getDocument()->getFieldValue('\path');
            $return[] = $this->cmsApi->getEntity($path);
        }

        return $return;
    }

    /**
     * Creates fileboard media file.
     *
     * @param \Vivo\CMS\Model\Content\Gallery $gallery
     * @param array $file Array with uploaded file informations ($_FILE)
     * @param array $options {name, description, order}
     * @return \Vivo\CMS\Model\Content\Fileboard\Media
     */
    public function createMediaWithUploadedFile(Content\Gallery $gallery, array $file, array $options)
    {
        $stream = new FileInputStream($file['tmp_name']);

        $imageData = getimagesize($file['tmp_name']);

        $media = new Media();
        $media->setOrder($options['order']);
        $media->setName($options['name']);
        $media->setDescription($options['description']);
        $media->setOriginalWidth($imageData[0]);
        $media->setOriginalHeight($imageData[1]);

        $media = $this->fileApi->prepareFileForSaving($media, $file);
        $media = $this->prepareMediaForSaving($gallery, $media);

        $this->cmsApi->saveEntity($media, true);
        $this->fileApi->writeResource($media, $stream);

        return $media;
    }

    /**
     * Removes all gallery's content.
     *
     * @param \Vivo\CMS\Model\Content\Gallery $gallery
     */
    public function removeAllFiles(Content\Gallery $gallery)
    {
        $this->cmsApi->removeChildren($gallery);
    }

    /**
     * Returns main item.
     *
     * @param \Vivo\CMS\Model\Content\Gallery $gallery
     * @param bool $first Returns first item if main not exist. Otherwise returns NULL.
     * @return \Vivo\CMS\Model\Entity
     */
    public function getMain(Content\Gallery $gallery, $first = true)
    {
        $return = null;

        $qb = new QueryBuilder();
        $con1  = $qb->cond($gallery->getPath().'/*', '\path');
        $con2  = $qb->cond(1, '\Vivo\CMS\Model\Content\Gallery\Media\main');
        $query = $qb->andX($con1, $con2);

        $hit = $this->indexer->find($query, array('page_size' => 1))->getFirstHit();

        if(false) {
            $path    = $hit->getDocument()->getFieldValue('\path');
            $return  = $this->cmsApi->getEntity($path);
        }
        else if($first) {
            $hit = $this->indexer->find($con1, array('page_size' => 1))->getFirstHit();

            if($hit) {
                $path    = $hit->getDocument()->getFieldValue('\path');
                $return  = $this->cmsApi->getEntity($path);
            }
        }

        return $return;
    }

    /**
     * Sets media file as main item.
     *
     * @param Content\Gallery $gallery
     * @param Media $media
     */
    public function setAsMain(Content\Gallery $gallery, Media $media)
    {
        foreach ($this->getList($gallery) as $file) { /* @var $file \Vivo\CMS\Model\Content\Gallery\Media */
            if($file->getMain()) {
                $file->setMain(false);
                $this->saveEntity($file);
            }
        }

        $media->setMain();
        $this->saveEntity($media);
    }

    /**
     * Returns gallery image sizes (...) as array.
     *
     * @param \Vivo\CMS\Model\Content\Gallery $gallery
     * @return array
     */
    public function getInformationsAsArray(Content\Gallery $gallery)
    {
        $data = array(
            'image_preview_width' => null,
            'image_preview_height' => null,
            'image_thumbnail_width' => null,
            'image_thumbnail_height' => null,
            'image_quality' => null,
        );

        $data['image_quality'] = $gallery->getImageQuality();

        if(strpos($gallery->getImagePreviewSize(), 'x') !== false) {
            $preview = explode('x', $gallery->getImagePreviewSize());
            $data['image_preview_width'] = $preview[0];
            $data['image_preview_height'] = $preview[1];
        }

        if(strpos($gallery->getImageThumbnailSize(), 'x') !== false) {
            $preview = explode('x', $gallery->getImageThumbnailSize());
            $data['image_thumbnail_width'] = $preview[0];
            $data['image_thumbnail_height'] = $preview[1];
        }

        return $data;
    }

}
