<?php
namespace Vivo\Backend\Form;

use Vivo\Form\Form;

/**
 * Cache
 * Form used by the Cache backend module
 */
class Cache extends Form
{
    /**
     * Constructor
     * @param string|null $name Form name
     */
    public function __construct($name = null)
    {
        if (is_null($name)) {
            $name   = 'cache_module';
        }
        parent::__construct($name);
        $this->setAttribute('method', 'post');

        //Flush all subject caches
        $this->add(array(
            'name'          => 'flush_all_subject_caches',
            'type'          => 'Vivo\Form\Element\Submit',
            'attributes'    => array(
                'value'         => 'Flush all caches',
            ),
        ));
    }
}
