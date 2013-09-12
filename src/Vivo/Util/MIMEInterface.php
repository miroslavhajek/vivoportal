<?php
namespace Vivo\Util;

interface MIMEInterface
{
    public function getExt($type);

    /**
     * Returns the Content-type for file extension
     * If file extension is not found, returns null
     * @param string $ext
     * @return null|string
     */
    public function detectByExtension($ext);
}
