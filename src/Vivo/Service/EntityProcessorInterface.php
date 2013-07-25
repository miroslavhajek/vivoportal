<?php
namespace Vivo\Service;

use Vivo\CMS\Model\Entity;

/**
 * EntityProcessorInterface
 * Implementers support processing an entity
 */
interface EntityProcessorInterface
{
    /**
     * Processes the entity
     * Returns true on successful processing, false on errors or null when the entity has not been processed
     * @param Entity $entity
     * @return bool|null
     */
    public function processEntity(Entity $entity);
}