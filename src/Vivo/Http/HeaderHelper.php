<?php
namespace Vivo\Http;

use Zend\Http\Headers;
use Zend\Stdlib\ArrayUtils;

/**
 * Helper class for modifying http headers.
 */
class HeaderHelper
{
    /**
     * Helper options
     * @var array
     */
    protected $options  = array(
        'mime_type_expiration' => array (
            //define specific expiration time for content type, e.g.:
//            'image/*'                       => 86400,
//            'audio/*'                       => 86400,
//            'text/*'                        => 86400,
//            'font/*'                        => 86400,
//            'application/x-shockwave-flash' => 86400,
        ),
        'default_expiration'    => 86400,
    );

    /**
     * Constructor
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->options = ArrayUtils::merge($this->options, $options);
    }

    /**
     * Add expiration http headers for specified mimeType
     * @param Headers $headers
     * @param string $mimeType
     */
    public function setExpirationByMimeType(Headers $headers, $mimeType)
    {
        $parts      = explode('/', $mimeType);
        $expiration = $this->options['default_expiration'];
        if (count($parts) == 2) {
            //Mime type has two parts
            foreach ($this->options['mime_type_expiration'] as $type => $typeExpiration) {
                if (preg_match('/^('.$parts[0].'|\*)\/('.$parts[1].'|\*)$/', $type)) {
                    $expiration = $typeExpiration;
                    break;
                }
            }
        }
        $this->setExpiration($headers, $expiration);
    }

    /**
     * Add expiration headers using given time in seconds.
     *
     * @param Headers $headers
     * @param integer $expiration Expiration time in seconds
     */
    public function setExpiration(Headers $headers, $expiration)
    {
        $headers->addHeaderLine('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expiration) . ' GMT');
        $headers->addHeaderLine('Pragma: cache');
        $headers->addHeaderLine('Cache-Control: max-age=' . $expiration);
        //$headers->addHeaderLine('Last-Modified: Thu, 02 Dec 2010 16:19:38 GMT'); //FIXME: why this line?
    }
}

