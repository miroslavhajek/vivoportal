<?php
namespace Vivo\Util;

interface MIMEInterface
{
    public function getExt($type);

    public function detectByExtension($ext);
}
