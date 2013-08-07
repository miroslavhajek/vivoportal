<?php
namespace Vivo\Repository;

use Vivo\TransactionalInterface;
use Vivo\CMS\Model\PathInterface;
use Vivo\CMS\Model\Entity;

/**
 * RepositoryInterface
 */
interface RepositoryInterface extends TransactionalInterface
{
	/**
     * Returns entity identified by path
     * When the entity is not found, throws an exception
	 * @param string $path Entity path
	 * @return \Vivo\CMS\Model\Entity|null
     * @throws Exception\EntityNotFoundException
	 */
	public function getEntity($path);

    /**
     * Returns if an entity exists in the repository at the given path
     * @param string $path
     * @return boolean
     */
    public function hasEntity($path);

    /**
     * Looks up an entity in storage and returns it
     * If the entity is not found returns null
     * @param string $path
     * @return \Vivo\CMS\Model\Entity|null
     */
    public function getEntityFromStorage($path);

    /**
     * Saves entity to repository
     * Changes become persistent when commit method is called within request
     * @param \Vivo\CMS\Model\PathInterface $entity
     * @return mixed|\Vivo\CMS\Model\PathInterface
     * @throws Exception\InvalidPathException
     */
    public function saveEntity(PathInterface $entity);

    /**
     * Schedules entity for deletion form storage
     * @param PathInterface $entity
     */
    public function deleteEntity(PathInterface $entity);

    /**
     * Deletes entity by path
     * @param string $path
     */
    public function deleteEntityByPath($path);

    /**
     * Moves entity
	 * @param PathInterface $entity
	 * @param string $target
     * @return Entity|null
	 */
	public function moveEntity(PathInterface $entity, $target);

    /**
     * Copies entity
	 * @param PathInterface $entity
	 * @param string $target
     * @return Entity|null
	 */
	public function copyEntity(PathInterface $entity, $target);

    /**
     * Returns children of an entity
     * When $deep == true, returns descendants rather than children
     * @param PathInterface|string $spec Either PathInterface object or directly a path as a string
     * @param bool|string $className
     * @param bool $deep
     * @param bool $ignoreErrors
     * @return \Vivo\CMS\Model\Entity[]
     */
    public function getChildren($spec,
                                $className = false,
                                $deep = false,
                                $ignoreErrors = false);

    /**
     * Schedules resource for deletion from storage
     * @param PathInterface $entity
     * @param string $name Name of the resource
     */
    public function deleteResource(PathInterface $entity, $name);

    /**
     *
     * @param PathInterface $entity
     * @return array
     */
    public function scanResources(PathInterface $entity);

    /**
     * Returns resource from storage
     * @param PathInterface $entity
     * @param string $name
     * @return string
     */
	public function getResource(PathInterface $entity, $name);

    /**
     * Returns an input stream for reading from the resource
     * @param PathInterface $entity
     * @param string $name Resource file name.
     * @return \Vivo\IO\InputStreamInterface
     */
	public function readResource(PathInterface $entity, $name);

    /**
     * Adds an entity resource (data) to the list of resources to be saved
     * @param PathInterface $entity
     * @param string $name Name of resource
     * @param string $data
     */
    public function saveResource(PathInterface $entity, $name, $data);

    /**
     * Adds a stream to the list of streams to be saved
     * @param PathInterface $entity
     * @param string $name
     * @param \Vivo\IO\InputStreamInterface $stream
     * @return void
     */
    public function writeResource(PathInterface $entity, $name, \Vivo\IO\InputStreamInterface $stream);

    /**
     * Returns entity resource mtime or false when the resource is not found
     * @param PathInterface $entity
     * @param string $name
     * @return int|bool
     */
    public function getResourceMtime(PathInterface $entity, $name);

    /**
     * Returns resource size in bytes
     * @param \Vivo\CMS\Model\PathInterface $entity
     * @param string $name
     * @return int
     */
    public function getResourceSize(PathInterface $entity, $name);

    /**
     * Returns parent folder
     * If there is no parent folder (ie this is a root), returns null
     * @param PathInterface $folder
     * @return \Vivo\CMS\Model\Entity
     */
	public function getParent(PathInterface $folder);

    /**
     * Returns true when the folder has children
     * @param PathInterface $folder
     * @return bool
     */
	public function hasChildren(PathInterface $folder);

    /**
     * Returns child entity paths
     * @param string $path
     * @return string[]
     */
    public function getChildEntityPaths($path);

    /**
     * Returns descendants of a specific path from storage
     * If $suppressUnserializationErrors is set to false, returns an array of entities
     * If $suppressUnserializationErrors is set to true, returns
     * array(
     *      'entities'  => array of descendants,
     *      'erroneous' => array(
     *          'child path' => Exception,...
     * )
     * @param string $path
     * @param bool $suppressUnserializationErrors
     * @return Entity[]|array
     */
    public function getDescendantsFromStorage($path, $suppressUnserializationErrors = false);
}
