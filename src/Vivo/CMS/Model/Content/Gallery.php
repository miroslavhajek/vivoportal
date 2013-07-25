<?php
namespace Vivo\CMS\Model\Content;

use Vivo\CMS\Model;

class Gallery extends Model\Content
{
    /**
     * @var string Thumbnail size.
     * @example 120x120
     */
    protected $imageThumbnailSize;

    /**
     * @var string Preview image size.
     * @example 640x640
     */
    protected $imagePreviewSize;

    /**
     * @var int
     */
    protected $imageQuality = 80;

    /**
     * @return string
     */
    public function getImageThumbnailSize()
    {
        return $this->imageThumbnailSize;
    }

    /**
     * @param string $size
     * @example 120x120
     */
    public function setImageThumbnailSize($size)
    {
        $this->imageThumbnailSize = $size;
    }

    /**
     * @return string
     */
    public function getImagePreviewSize()
    {
        return $this->imagePreviewSize;
    }

    /**
     * @param string $size
     * @example 640x640
     */
    public function setImagePreviewSize($size)
    {
        $this->imagePreviewSize = $size;
    }

    /**
     * @return int
     */
    public function getImageQuality()
    {
        return $this->imageQuality;
    }

    /**
     * @param int $quality
     */
    public function setImageQuality($quality)
    {
        $this->imageQuality = intval($quality);
    }
}
