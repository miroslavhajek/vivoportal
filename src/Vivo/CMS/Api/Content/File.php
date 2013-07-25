<?php
namespace Vivo\CMS\Api\Content;

use Vivo\CMS\Model\Content;
use Vivo\CMS\Model\ContentContainer;
use Vivo\CMS\Api;
use Vivo\Util\MIMEInterface;
use Vivo\CMS\Exception\InvalidArgumentException;
use Vivo\IO\InputStreamInterface;
use Vivo\IO\FileInputStream;

class File
{
    /**
     * @var \Vivo\CMS\Api\CMS
     */
    private $cmsApi;

    /**
     * @var \Vivo\CMS\Api\Document
     */
    private $documentApi;

    /**
     * @var \Vivo\Util\MIMEInterface
     */
    private $mime;

    /**
     * @param \Vivo\CMS\Api\CMS $cmsApi
     * @param \Vivo\CMS\Api\Document $documentApi
     * @param \Vivo\Util\MIMEInterface $mime
     */
    public function __construct(Api\CMS $cmsApi, Api\Document $documentApi, MIMEInterface $mime)
    {
        $this->cmsApi = $cmsApi;
        $this->documentApi = $documentApi;
        $this->mime = $mime;
    }

    /**
     * @param \Vivo\CMS\Model\Content\File $file
     * @param array $data
     * @return \Vivo\CMS\Model\Content\File
     */
    public function prepareFileForSaving(Content\File $file, array $data)
    {
        $ext = strtolower(pathinfo($data['name'], PATHINFO_EXTENSION));
        $mime = $this->mime->detectByExtension($ext);

        $file->setFilename($data['name']);
        $file->setSize($data['size']);
        $file->setExt($ext);
        $file->setMimeType($mime);

        return $file;
    }

    /**
     * @param \Vivo\CMS\Model\Content\File $file
     * @param array $data $_FILE array
     * @param ContentContainer $contentContainer
     */
    public function saveFileWithUploadedFile(Content\File $file, array $data, ContentContainer $contentContainer)
    {
        $file = $this->prepareFileForSaving($file, $data);

        if($file->getUuid()) {
            $this->documentApi->saveContent($file);
        }
        else {
            $this->documentApi->createContent($contentContainer, $file);
        }

        $this->removeAllResources($file);
        $this->writeResource($file, new FileInputStream($data['tmp_name']));
    }

    /**
     * @param string $mimeType
     * @return string
     */
    public function getExt($mimeType)
    {
        return $this->mime->getExt($mimeType);
    }

    private function checkFileProperties(Content\File $file)
    {
        if(!$file->getExt()) {
            throw new InvalidArgumentException(sprintf("Unknown file extension for file '%s'", $file->getFilename()));
        }

        if(!$file->getMimeType()) {
            throw new InvalidArgumentException(sprintf("Unknown file mime type for file '%s'", $file->getFilename()));
        }
    }

    /**
     * @param \Vivo\CMS\Model\Content\File $file
     * @param string $data
     */
    public function saveResource(Content\File $file, $data)
    {
        $this->checkFileProperties($file);
        $this->cmsApi->saveResource($file, $this->cmsApi->getResourceName($file), $data);
    }

    /**
     * Writes resource file to repository.
     *
     * @param \Vivo\CMS\Model\Content\File $file
     * @param \Vivo\IO\InputStreamInterface $inputStream
     */
    public function writeResource(Content\File $file, InputStreamInterface $inputStream)
    {
        $this->checkFileProperties($file);
        $this->cmsApi->writeResource($file, $this->cmsApi->getResourceName($file), $inputStream);
    }

    /**
     * @param \Vivo\CMS\Model\Content\File $file
     */
    public function removeResource(Content\File $file)
    {
        $this->cmsApi->removeResource($file, $this->cmsApi->getResourceName($file));
    }

    /**
     * Returns content of entity resource.
     *
     * @param \Vivo\CMS\Model\Content\File $file
     * @return string
     */
    public function getResource(Content\File $file)
    {
        return $this->cmsApi->getResource($file, $this->cmsApi->getResourceName($file));
    }

    /**
     * Returns input stream for resource of entity.
     *
     * @param \Vivo\CMS\Model\Content\File $file
     * @param string $resourcePath
     * @return \Vivo\IO\InputStreamInterface
     */
    public function readResource(Content\File $file)
    {
        return $this->cmsApi->readResource($file, $this->cmsApi->getResourceName($file));
    }

    /**
     * Remove all resources.
     *
     * @param \Vivo\CMS\Model\Content\File $file
     */
    public function removeAllResources(Content\File $file)
    {
        $resources = $this->cmsApi->scanResources($file);
        foreach ($resources as $resource) {
            $this->cmsApi->removeResource($file, $resource);
        }
    }

    /**
     * @param \Vivo\CMS\Model\Content\File $file
     */
    public function download(Content\File $file)
    {
        $mimeType = $file->getMimeType();
        $fileName = $file->getFilename();

        $inputStream  = $this->readResource($file);

        header('Content-type: '.$mimeType);
        header('Content-Disposition: attachment; filename="'.$fileName.'"');
        while(($b = $inputStream->read(4096)) !== false) {
            echo $b;
        }
        die();
    }
}
