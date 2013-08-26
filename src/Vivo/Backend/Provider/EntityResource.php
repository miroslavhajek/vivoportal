<?php
namespace Vivo\Backend\Provider;

use Vivo\CMS\Api;
use Vivo\CMS\Model\Entity;
use Vivo\LookupData\LookupDataProviderInterface;

class EntityResource implements LookupDataProviderInterface
{
    /**
     * @var \Vivo\CMS\Api\CMS
     */
    private $cmsApi;

    public function __construct(Api\CMS $cmsApi)
    {
        $this->cmsApi = $cmsApi;
    }

    /**
     * @return array
     */
    public function getLookupData($property, array $propertyMetadata, Entity $entity)
    {
        $return = array('' => '');

        foreach ($this->cmsApi->scanResources($entity) as $name) {
            $return[$name] = $name;
        }

        if(isset($propertyMetadata['field_options']['allowed_types'])
        && count($propertyMetadata['field_options']['allowed_types']))
        {
            $allowedTypes = $propertyMetadata['field_options']['allowed_types'];

            foreach ($return as $key => $fileName) {
                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                if(!in_array($ext, $allowedTypes)) {
                    unset($return[$key]);
                }
            }
        }

        return $return;
    }

}
