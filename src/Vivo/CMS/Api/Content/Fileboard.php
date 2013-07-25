<?php
namespace Vivo\CMS\Api\Content;

use Vivo\CMS\Model\Content;
use Vivo\CMS\Model\Content\Fileboard\Media;
use Vivo\CMS\Model\Content\Fileboard\Separator;
use Vivo\Indexer\QueryBuilder;
use Vivo\IO\FileInputStream;
use Vivo\CMS\Api\Exception\InvalidPathException;

class Fileboard extends AbstractOrderableContentApi
{
    /**
     * Returns all fileboard items.
     * Max items is 9999.
     *
     * @param \Vivo\CMS\Model\Content\Fileboard $model
     * @throws \Vivo\CMS\Api\Exception\InvalidPathException
     * @return array
     */
    public function getList(Content\Fileboard $model)
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
     * Saves fileboard separator.
     *
     * @param \Vivo\CMS\Model\Content\Fileboard\Separator $separator
     * @param string $html HTML content
     */
    public function saveSeparator(Separator $separator, $html)
    {
        $separator->setMimeType('text/html');
        $separator->setExt('html');
        $separator->setSize(mb_strlen($html, 'UTF-8'));

        $this->fileApi->saveResource($separator, $html);
        $this->cmsApi->saveEntity($separator, true);
    }

    /**
     * Creates fileboard media file.
     *
     * @param \Vivo\CMS\Model\Content\Fileboard $fileboard
     * @param array $file Array with uploaded file informations ($_FILE)
     * @param array $options {name, description, order}
     * @return \Vivo\CMS\Model\Content\Fileboard\Media
     */
    public function createMediaWithUploadedFile(Content\Fileboard $fileboard, array $file, array $options)
    {
        $stream = new FileInputStream($file['tmp_name']);

        $media = new Media();
        $media->setOrder($options['order']);
        $media->setName($options['name']);
        $media->setDescription($options['description']);
        $media = $this->fileApi->prepareFileForSaving($media, $file);
        $media = $this->prepareMediaForSaving($fileboard, $media);

        $this->cmsApi->saveEntity($media, true);
        $this->fileApi->writeResource($media, $stream);

        return $media;
    }

    /**
     * Creates fileboard separator.
     *
     * @param \Vivo\CMS\Model\Content\Fileboard $fileboard
     * @param string $html HTML content.
     * @param array $options {order}
     * @return \Vivo\CMS\Model\Content\Fileboard\Separator
     */
    public function createSeparator(Content\Fileboard $fileboard, $html, array $options)
    {
        $separator = new Separator();
        $separator->setOrder($options['order']);
        $separator->setMimeType('text/html');
        $separator->setExt('html');
        $separator->setSize(mb_strlen($html, 'UTF-8'));
        $separator = $this->prepareMediaForSaving($fileboard, $separator);

        $this->fileApi->saveResource($separator, $html);
        $this->cmsApi->saveEntity($separator, true);

        return $separator;
    }

    /**
     * Removes all fileboard's content.
     *
     * @param \Vivo\CMS\Model\Content\Fileboard $gallery
     */
    public function removeAllFiles(Content\Fileboard $fileboard)
    {
        $this->cmsApi->removeChildren($fileboard);
    }
}
