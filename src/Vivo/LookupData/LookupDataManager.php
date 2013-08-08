<?php
namespace Vivo\LookupData;

use Vivo\CMS\Model\Entity;

use Zend\ServiceManager\ServiceManager;

/**
 * LookupDataManager
 */
class LookupDataManager
{
    /**
     * Service Manager
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * Local cache
     * @var array
     */
    private $metadata;

    /**
     * Constructor
     * @param \Zend\ServiceManager\ServiceManager $serviceManager
     */
    public function __construct(ServiceManager $serviceManager)
    {
        $this->serviceManager   = $serviceManager;
    }

    /**
     * Returns data for an entity
     * @param array $metadata
     * @param \Vivo\CMS\Model\Entity $entity
     * @return array
     */
    public function injectLookupData(array $metadata, Entity $entity)
    {
        $this->metadata = $metadata;

        foreach ($metadata as $property => $data) {
            $metadata[$property] = $this->getData($property, $data, $entity);
        }

        return $metadata;
    }

    /**
     * @param string $propertyName
     * @param array $metadata
     * @param \Vivo\CMS\Model\Entity $entity
     * @return array
     */
    private function getData($propertyName, array &$metadata, Entity $entity)
    {
        foreach ($metadata as $key => &$value) {
            if (is_array($value)) {
                $this->getData($propertyName, $value, $entity);
            } elseif (strpos($value, '\\') && $this->serviceManager->has($value)) {
                $provider   = $this->serviceManager->get($value);
                if ($provider instanceof LookupDataProviderInterface) {
                    $value = $provider->getLookupData($propertyName, $this->metadata[$propertyName], $entity);
                }
            }
        }
        return $metadata;
    }
}