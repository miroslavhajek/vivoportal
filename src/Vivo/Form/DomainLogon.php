<?php
namespace Vivo\Form;

/**
 * Domain logon form
 *
 * Adds domain field to standard logon form.
 */
class DomainLogon extends Logon
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct('logon');

        $this->get('logon')->add(array('name' => 'domain',
            'options' => array(
                'label' => gettext_noop('Domain'),
            ),
            'attributes' => array(
                'type' => 'text',
            ),));

        $if = $this->getInputFilter()->get('logon')->get('domain');
        $if->setAllowEmpty(false);
        $if->setRequired(true);
    }
}
