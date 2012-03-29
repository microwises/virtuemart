<?php
defined('_JEXEC') or die('Restricted access');

/**
 * The Klarna Language Pack class. This class fetches translations from a
 * language pack.
 *
 * @package     Klarna Standard Kassa API
 * @version     2.0.0
 * @since       2011-10-10
 * @link        http://integration.klarna.com/
 * @copyright   Copyright (c) 2011 Klarna AB (http://klarna.com)
 */

class KlarnaLanguagePack {
    public static $charset = "ISO-8859-1";
    private $xml;

    public function __construct($path = 'language/klarna_language.xml') {
	if (file_exists($path)) {
        $this -> xml = simplexml_load_file($path);
	} else {
	    vmError('KlarnaLanguagePack ' . $path. 'does not exist');
	    return false;
	}
	return true;
    }

    /**
     * Get a translated text from the language pack
     *
     * @param  string  $text  the string to be translated
     * @param  string|int  $ISO  target language, iso code or KlarnaLanguage
     * @return string  the translated text
     */
    public function fetch($text, $ISO) {
        if (is_numeric ($ISO)) {
            $ISO = KlarnaLanguage::getCode($ISO);
        } else {
            $ISO = strtolower ($ISO);
        }

        // XPath query to get translation
        $xpath = "//string[@id='$text']/$ISO";
        $aResult = (array) @$this -> xml -> xpath ($xpath);
        $aResult = (array) @$aResult[0];

        return htmlentities(utf8_decode(@$aResult[0]));
    }

    /**
     * Get a translated text from the language pack
     * same as fetch but with a implicit creation of the instance
     */
    public static function fetch_from_file($text, $ISO, $path = null) {
        if ($path !== null) {
            $pack = new KlarnaLanguagePack($path);
        } else {
            $pack = new KlarnaLanguagePack();
        }

        return $pack -> fetch($text, $ISO);
    }
}
