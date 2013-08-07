<?php
namespace Vivo\Backend\UI\Explorer;

interface ExplorerInterface
{
    public function setEntity(\Vivo\CMS\Model\Entity $entity);
    public function getEntity();
    public function getSite();
}
