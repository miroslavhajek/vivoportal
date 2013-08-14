<?php
namespace Vivo\Text;

use Zend\EventManager\EventManager;

/**
 * Text class provides methods which work with texts
 */
class Text {

	const EMPTY_CHARS = " \n\r\t";

    /**
     * Event Manager
     * @var EventManager
     */
    protected $eventManager;

    /**
	 * @param string $str
	 * @param int $pos
	 * @return string
	 */
	public function readChar(&$str, &$pos) {
		$len = strlen($str);
		while (($pos < $len) && (strpos(self::EMPTY_CHARS, $str{$pos}) !== false))
			$pos++;
        $retVal = ($pos < $len) ? $str{$pos++} : false;
		return $retVal;
	}

	/**
	 * @param string $str
	 * @param string $pos
	 * @param bool $allowed_chars
	 * @return string
	 */
	public function readWord(&$str, &$pos, $allowed_chars = false) {
        $len = strlen($str);
		while (($pos < $len) && (strpos(self::EMPTY_CHARS, $str{$pos}) !== false))
			$pos++;
		$start = $pos;
		if ($allowed_chars) {
			while (($pos < $len) && (strpos($allowed_chars, $str{$pos}) === false))
				$pos++;
		} else {
			while (($pos < $len) && (strpos(self::EMPTY_CHARS, $str{$pos}) === false))
				$pos++;
		}
        $retVal = substr($str, $start, $pos - $start);
		return $retVal;
	}

	/**
	 * @param string $char
	 * @param string $str
	 * @param int $pos
	 * @throws \Exception
	 */
	public function expectChar($char, &$str, &$pos) {
		if (($char2 = $this->readChar($str, $pos)) != $char) {
			throw new \Exception("Expecting $char at position $pos (got $char2)");
        }
	}

	/**
	 * Truncate a string to a certain length if necessary,
	 * optionally splitting in the middle of a word, and
	 * appending the $etc string or inserting $etc into the middle.
	 *
	 * @since >1.1.2
	 * @param string $string
	 * @param integer $length
	 * @param string $etc
	 * @param bool $break_words
	 * @param bool $middle
	 * @return string
	 */
// 	static function truncate($string, $length = 100, $etc = '...', $break_words = false, $middle = false) {
// 		if ($length == 0)
// 			return '';

// 		if (strlen($string) > $length) {
// 			$length -= min($length, strlen($etc));
// 			$en = mb_detect_encoding($string);
// 			if (!$break_words && !$middle) {
// 				$string = preg_replace('/\s+?(\S+)?$/', '', mb_substr($string, 0, $length+1, $en));
// 			}
// 			if(!$middle) {
// 				return mb_substr($string, 0, $length, $en).$etc;
// 			} else {
// 				return mb_substr($string, 0, $length/2, $en).$etc.mb_substr($string, -$length/2, null, $en);
// 			}
// 		} else {
// 			return $string;
// 		}
// 	}

	/**
	 * @var array Conversion table
	 */
// 	static $utf8table =
// 		array(
// 			"\xc3\xa1" => "a",
// 			"\xc3\xa4" => "a",
// 			"\xc4\x8d" => "c",
// 			"\xc4\x8f" => "d",
// 			"\xc3\xa9" => "e",
// 			"\xc4\x9b" => "e",
// 			"\xc3\xad" => "i",
// 			"\xc4\xbe" => "l",
// 			"\xc4\xba" => "l",
// 			"\xc5\x88" => "n",
// 			"\xc3\xb3" => "o",
// 			"\xc3\xb6" => "o",
// 			"\xc5\x91" => "o",
// 			"\xc3\xb4" => "o",
// 			"\xc5\x99" => "r",
// 			"\xc5\x95" => "r",
// 			"\xc5\xa1" => "s",
// 			"\xc5\xa5" => "t",
// 			"\xc3\xba" => "u",
// 			"\xc5\xaf" => "u",
// 			"\xc3\xbc" => "u",
// 			"\xc5\xb1" => "u",
// 			"\xc3\xbd" => "y",
// 			"\xc5\xbe" => "z",
// 			"\xc3\x81" => "A",
// 			"\xc3\x84" => "A",
// 			"\xc4\x8c" => "C",
// 			"\xc4\x8e" => "D",
// 			"\xc3\x89" => "E",
// 			"\xc4\x9a" => "E",
// 			"\xc3\x8d" => "I",
// 			"\xc4\xbd" => "L",
// 			"\xc4\xb9" => "L",
// 			"\xc5\x87" => "N",
// 			"\xc3\x93" => "O",
// 			"\xc3\x96" => "O",
// 			"\xc5\x90" => "O",
// 			"\xc3\x94" => "O",
// 			"\xc5\x98" => "R",
// 			"\xc5\x94" => "R",
// 			"\xc5\xa0" => "S",
// 			"\xc5\xa4" => "T",
// 			"\xc3\x9a" => "U",
// 			"\xc5\xae" => "U",
// 			"\xc3\x9c" => "U",
// 			"\xc5\xb0" => "U",
// 			"\xc3\x9d" => "Y",
// 			"\xc5\xbd" => "Z"
// 		);

	/**
	 * Translate accented utf8 characters over to non-accented.
	 * @param string The input string.
	 * @return int|false
	 */
// 	static function utf8_ascii($str) {
// 		return strtr($str, self::$utf8table);
// 	}

	/**
	 * Convert UTF8 text with diacritics to uniq id attribute
	 * Must be same result as JavaScript function.
	 * @param string $str
	 * @return string
	 */
// 	static function idAttributeFromUTF8($str) {
// 		$id = strtr(rawurlencode($str), array('x' => 'x78'));
// 		$id = strtr($id, '%', 'x');
// 		return $id;
// 	}

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

