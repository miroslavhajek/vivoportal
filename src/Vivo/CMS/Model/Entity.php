<?php
namespace Vivo\CMS\Model;

/**
 * Base class for all CMS entities.
 */
class Entity implements PathInterface
{
    /**
     * Universally Unique Identifier (UUID) of the entity instance.
     * Value is set when entity is being instantiated. Never set or change value of this property.
     * @var string
     */
    protected $uuid;

    /**
     * Absolute path to entity stored in repository.
     * @var string
     */
    protected $path;

    /**
     * If TRUE, entity will be indexed by fulltext indexer.
     * @see Vivo\CMS\Solr\Indexer
     * @var boolean
     */
    protected $searchable = true;

    /**
     * Time of entity creation.
     * @var \DateTime
     */
    protected $created;

    /**
     * Username of entity creator.
     * @var string
     */
    protected $createdBy;

    /**
     * Time of entity last modification.
     * @var \DateTime
     */
    protected $modified;

    /**
     * Username of user who made last last modification.
     * @var string
     */
    protected $modifiedBy;

    /**
     * @var CustomProperties
     */
    protected $customProperties;

    /**
     * Constructor.
     * @param string $path Path to entity. If not set, it will be undefined and can be set later before persisting entity using saveEntity method of Repository.
     */
    public function __construct($path = null)
    {
        $this->path = $path;
    }

    /**
     * Magic method call for custom properties
     * @param string $name
     * @param mixed $params
     * @return mixed
     */
    public function __call($name, $params)
    {
        if(strpos($name, 'get') === 0) {
            $name = lcfirst(substr($name, 3));

            return $this->getCustomProperties()->get($name);
        }
        if(strpos($name, 'set') === 0) {
            $name = lcfirst(substr($name, 3));
            $this->getCustomProperties()->set($name, $params[0]);
        }
    }

    /**
     * Compare entities by path.
     * @param \Vivo\CMS\Model\Entity $entity
     * @return bool Returns true if the entity is under another entity (in the tree paths).
     */
    public function under(Entity $entity)
    {
        return (strpos($this->path . '/', $entity->getPath() . '/') === 0);
    }

    /**
     * @param string $uuid
     */
    public function setUuid($uuid)
    {
        $this->uuid = strtoupper($uuid);
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * Sets entity path.
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Gets entity path.
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Gets entity name.
     * @return string
     */
    public function getName()
    {
        return (($pos = strrpos($this->path, '/')) !== false) ? substr($this->path, $pos + 1) : '';
    }

    /**
     * Sets datetime of creation.
     * @param \DateTime $date
     */
    public function setCreated(\DateTime $date = null)
    {
        $this->created = $date;
    }

    /**
     * Returns datetime of creation.
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set searchable property, which determinates if entity will be indexed or not.
     * @param bool $searchable
     */
    public function setSearchable($searchable = true)
    {
        $this->searchable = (bool) $searchable;
    }

    /**
     * Returns searchable property, which determinates if entity will be indexed or not.
     * @return bool
     */
    public function getSearchable()
    {
        return $this->searchable;
    }

    /**
     * Set username of creator.
     * @return string $userName
     */
    public function setCreatedBy($userName)
    {
        $this->createdBy = $userName;
    }

    /**
     * Returns username of cretor.
     * @return string
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set datetime of last modification.
     * @param \DateTime $date
     */
    public function setModified(\DateTime $date = null)
    {
        $this->modified = $date;
    }

    /**
     * Returns datetime of last modification.
     * @return \DateTime
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Set username of user who modified entity.
     * @return string $userName
     */
    public function setModifiedBy($userName)
    {
        $this->modifiedBy = $userName;
    }

    /**
     * Returns username of user who modfified entity.
     * @return string
     */
    public function getModifiedBy()
    {
        return $this->modifiedBy;
    }

    /**
     * Depth in the repository tree
     * @return int
     */
    public function getDepth()
    {
        if (!$this->path) return false;
        return substr_count($this->path, '/', 1);
    }

    /**
     * @return \Vivo\CMS\Model\CustomProperties
     */
    public function getCustomProperties()
    {
        if(!$this->customProperties instanceof CustomProperties) {
            $this->customProperties = new CustomProperties();
        }

        return $this->customProperties;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return get_class($this) . '{uuid: ' . $this->uuid . ', path: ' . $this->path . '}';
    }
}
