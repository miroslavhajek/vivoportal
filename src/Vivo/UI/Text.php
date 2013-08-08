<?php
namespace Vivo\UI;

/**
 * Text component only display your content.
 */
class Text extends Component
{
    /**
     * @var string
     */
    protected $text;

    /**
     * Constructor.
     * @param string
     */
    public function __construct($text = '')
    {
        $this->setText($text);
    }

    /**
     * Returns view model of the Component or string to display directly
     * @return \Zend\View\Model\ModelInterface|string
     */
    public function getView()
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }
}
