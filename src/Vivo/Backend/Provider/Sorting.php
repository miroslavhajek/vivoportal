<?php
namespace Vivo\Backend\Provider;

use Vivo\LookupData\AbstractLookupDataProvider;
use Vivo\CMS\Model\Entity;

class Sorting extends AbstractLookupDataProvider
{
    /**
     * @return array
     */
    public function getLookupData($property, array $propertyMetadata, Entity $entity)
    {
        $return = array('' => '');
        $config = $this->serviceManager->get('cms_config');
        
        if(isset($config['document_sorting'])){
            $return = $config['document_sorting']['native'];     
        }
        return $return;
    }

}
