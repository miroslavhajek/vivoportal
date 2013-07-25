<?php
namespace Vivo\Repository;

use Vivo\CMS\Model\PathInterface;
use Vivo\Repository\Exception;

/**
 * Watcher
 * Identity Map implementation for the Repository
 */
class Watcher
{
    /**
     * Array of all entities added so far
     * Keys are entity paths
     * @var PathInterface[]
     */
    protected $entities		    = array();

    /**
     * Is the watcher active?
     * When not active, the watcher does not store/return/remove the objects
     * @var bool
     */
    protected $active           = true;

    /**
     * Adds an entity to the object watcher
     * @param \Vivo\CMS\Model\PathInterface $entity
     * @throws Exception\Exception
     */
    public function add(PathInterface $entity)
    {
        if ($this->isActive()) {
            $path   = $entity->getPath();
            if (!$path) {
                throw new Exception\Exception(sprintf('%s: The entity (%s) has no path', __METHOD__, get_class($entity)));
            }
            $this->entities[$path]      = $entity;
        }
    }

    /**
     * Removes entity from the watcher
     * @param string $path
     */
    public function remove($path)
    {
        if ($this->isActive() && array_key_exists($path, $this->entities)) {
            unset($this->entities[$path]);
        }
    }

    /**
     * Returns an entity from watcher
     * If there is no entity under this path, returns null
     * @param string $path
     * @return null|\Vivo\CMS\Model\Entity
     */
    public function get($path)
    {
        if ($this->isActive() && array_key_exists($path, $this->entities)) {
            $entity = $this->entities[$path];
        } else {
            $entity = null;
        }
        return $entity;
    }

    /**
     * Clears all entities from watcher
     */
    public function clear()
    {
        $this->entities = array();
    }

    /**
     * Gets / sets the active flag
     * @param bool|null $active
     * @return bool
     */
    public function isActive($active = null)
    {
        if (!is_null($active)) {
            //Setter functionality
            $this->active   = (bool)$active;
        }
        return $this->active;
    }
}
