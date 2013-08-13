<?php
namespace Vivo\Transliterator;

use Zend\Stdlib\ArrayUtils;
use Zend\EventManager\EventManager;
use Zend\Cache\Storage\StorageInterface as Cache;

/**
 * Transliterator
 * General transliterator implementation
 */
class Transliterator implements TransliteratorInterface
{
    /**#@+
     * Constant signalling required case change
     */
    const CASE_CHANGE_TO_LOWER  = -1;
    const CASE_CHANGE_TO_UPPER  = 1;
    const CASE_CHANGE_NONE      = 0;
    /**#@-*/

    /**
     * Already transliterated results
     * @var array
     */
    protected $transliterated   = array();

    /**
     * Event Manager
     * @var EventManager
     */
    protected $eventManager;

    /**
     * Cache
     * @var Cache
     */
    protected $cache;

    /**
     * Transliterator options
     * @var array
     */
    protected $options  = array(
        //Transliteration map
        'map'               => array(),
        //String with all allowed characters
        'allowedChars'      => 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_',
        //Character used to replace illegal characters
        'replacementChar'   => '-',
        //Change case before processing
        'caseChangePre'     => self::CASE_CHANGE_NONE,
        //Change case after processing
        'caseChangePost'    => self::CASE_CHANGE_NONE,
    );

    /**
     * Constructor
     * @param \Zend\Cache\Storage\StorageInterface $cache
     * @param array $options
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(Cache $cache = null, array $options = array())
    {
        $this->cache    = $cache;
        $this->options  = ArrayUtils::merge($this->options, $options);
        if (mb_strpos($this->options['allowedChars'], $this->options['replacementChar']) === false) {
            throw new Exception\InvalidArgumentException(
                sprintf("%s: Replacement character '%s' missing in allowed characters '%s'",
                        __METHOD__, $this->options['replacementChar'], $this->options['allowedChars']));
        }
    }

    /**
     * Transliterates string
     * @param string $str
     * @return string
     */
    public function transliterate($str)
    {
        //Get from already transliterated
        $hash           = hash('md4', $str);
        //Get transliterated string from results cached in memory (in this instance)
        if (array_key_exists($hash, $this->transliterated)) {
            $transliterated = $this->transliterated[$hash];
            return $transliterated;
        }
        //Get transliterated string from standard cache
        if ($this->cache) {
            $cacheSuccess   = null;
            $transliterated = $this->cache->getItem($hash, $cacheSuccess);
            if ($cacheSuccess) {
                return $transliterated;
            }
        }
        //Perform transliteration
        $translit   = $str;
        //Change case PRE
        switch ($this->options['caseChangePre']) {
            case self::CASE_CHANGE_TO_LOWER:
                $translit   = mb_strtolower($translit);
                break;
            case self::CASE_CHANGE_TO_UPPER:
                $translit   = mb_strtoupper($translit);
                break;
        }

        //Replace according to the map
        //The RE processing replaced with strtr to optimize performance
//        foreach ($this->options['map'] as $from => $to) {
//            $re     = sprintf('\\%s', $from);
//            $str    = mb_ereg_replace($re, $to, $str);
//        }
        foreach ($this->options['map'] as $from => $to) {
            $translit   = strtr($translit, array($from => $to));
        }

        $replacementCharLength  = mb_strlen($this->options['replacementChar']);

        //Replace illegal chars with the replacement char
        $cleaned    = '';
        $len        = mb_strlen($translit);
        for ($i = 0; $i < $len; $i++) {
            $chr        = mb_substr($translit, $i, 1);
            $cleaned    .= (mb_strpos($this->options['allowedChars'], $chr) !== false)
                ? $chr : $this->options['replacementChar'];
        }
        $translit   = $cleaned;

        //Remove duplicated replacement chars
        $re         = sprintf('\\%s+', $this->options['replacementChar']);
        $translit   = mb_ereg_replace($re, $this->options['replacementChar'], $translit);


        //Remove leading replacement char
        //The RE processing replaced with mb_... functions to optimize performance
//        $re         = sprintf('^\\%s', $this->options['replacementChar']);
//        $translit   = mb_ereg_replace($re, '', $translit);
        if (mb_substr($translit, 0, $replacementCharLength) == $this->options['replacementChar']) {
            $translit   = mb_substr($translit, $replacementCharLength);
        }


        //Remove trailing replacement char
        //The RE processing replaced with mb_... functions to optimize performance
//        $re         = sprintf('\\%s$', $this->options['replacementChar']);
//        $translit   = mb_ereg_replace($re, '', $translit);
        $translitLength = mb_strlen($translit);
        if (mb_substr($translit, $translitLength - $replacementCharLength) == $this->options['replacementChar']) {
            $translit   = mb_substr($translit, 0, -$replacementCharLength);
        }

        //Change case POST
        switch ($this->options['caseChangePost']) {
            case self::CASE_CHANGE_TO_LOWER:
                $translit   = mb_strtolower($translit);
                break;
            case self::CASE_CHANGE_TO_UPPER:
                $translit   = mb_strtoupper($translit);
                break;
        }

        //Store the result to the local cache and standard cache
        $this->transliterated[$hash] = $translit;
        if ($this->cache) {
            $this->cache->setItem($hash, $translit);
        }
        return $translit;
    }

    /**
     * Returns Event Manager
     * @return \Zend\EventManager\EventManager
     */
    public function getEventManager()
    {
        if (!$this->eventManager) {
            $this->setEventManager(new EventManager());
        }
        return $this->eventManager;
    }

    /**
     * Sets Event manager
     * @param \Zend\EventManager\EventManager $eventManager
     */
    public function setEventManager(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }
}
