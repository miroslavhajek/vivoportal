<?php
namespace Vivo\Util;

use Zend\Stdlib\ArrayUtils;

/**
 * MIME provides methods to works with Content-types and MIME types.
 */
class MIME implements MIMEInterface
{
    /**
     * @var array
     */
    protected $options = array(
        'types'         => array(),
        'icons'         => array(),
    );

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->options = ArrayUtils::merge($this->options, $options);
        if (!isset($this->options['default_icon']) || !$this->options['default_icon']) {
            throw new Exception\ConfigException(__METHOD__ . ": Default Icon is not set.");
        }
    }

    /**
     * Returns the Content-type for file extension.
     * @param string $ext
     * @return string
     */
    protected function getType($ext)
    {
        $ext = strtolower($ext);
        foreach ($this->options['types'] as $type => $exts) {
            if (in_array($ext, $exts)) {
                return $type;
            }
        }
        return null;
    }

    /**
     * Returns the file extension for Content-type. It is reverse method to self::getType().
     * @param string $type
     * @return string|null
     */
    public function getExt($type)
    {
        $type = strtolower($type);
        return isset($this->options['types'][$type][0]) ?
                     $this->options['types'][$type][0] : null;
    }

    public function detectByExtension($ext)
    {
        return $this->getType($ext);
    }

    public function detectByFileContent($fileName)
    {
        //TODO , detect using finfo php extension
    }
    
    /**
     * @return string Icon Base Name (Without path or extension)
     */
    public function getIconBaseName($mimeType)
    {
        return isset($this->options['icons'][$mimeType]) ?
               $this->options['icons'][$mimeType] : $this->options['default_icon'];
    }

}
