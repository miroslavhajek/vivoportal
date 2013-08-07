<?php
namespace Vivo\View\Helper;

use Vivo\Transliterator\TransliteratorInterface;

use Zend\View\Helper\AbstractHelper;
use Zend\Stdlib\ArrayUtils;

/**
 * Transliterate
 * Transliterator view helper
 */
class Transliterate extends AbstractHelper
{
    /**
     * Transliterators
     * @var TransliteratorInterface[]
     */
    protected $transliterators  = array();

    /**
     * Transliterator view helper options
     * @var array
     */
    protected $options          = array(
        'default_transliterator'    => 'title_to_path',
    );

    /**
     * Constructor
     * @param array $transliterators
     * @param array $options
     */
    public function __construct(array $transliterators = array(), array $options = array())
    {
        $this->transliterators  = $transliterators;
        $this->options          = ArrayUtils::merge($this->options, $options);
    }

    /**
     * Invoke as a function
     * @param string|null $str
     * @param string|null $transliterator
     * @return $this|string
     */
    public function __invoke($str = null, $transliterator = null)
    {
        if (is_null($str)) {
            return $this;
        }
        $transliterated = $this->render($str, $transliterator);
        return $transliterated;
    }

    /**
     * Returns transliterated string
     * @param string $str String to transliterate
     * @param string|null $transliterator Name of transliterator to use
     * @return string
     * @throws Exception\InvalidArgumentException
     */
    public function render($str, $transliterator = null)
    {
        if (is_null($transliterator)) {
            $transliterator = $this->options['default_transliterator'];
        }
        if (!array_key_exists($transliterator, $this->transliterators)) {
            throw new Exception\InvalidArgumentException(
                sprintf("%s: Unknown transliterator '%s'", __METHOD__, $transliterator));
        }
        $transliterated     = $this->transliterators[$transliterator]->transliterate($str);
        return $transliterated;
    }
}
