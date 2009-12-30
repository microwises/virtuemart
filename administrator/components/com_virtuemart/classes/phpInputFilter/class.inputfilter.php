<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
* @version $Id: class.inputfilter.php 1755 2009-05-01 22:45:17Z rolandd $
* @package VirtueMart
* @subpackage phpInputFilter
* @copyright Copyright (C) 2004-2008 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/

/** @class: InputFilter (PHP4 & PHP5, with comments)
  * @project: PHP Input Filter
  * @date: 10-05-2005
  * @version: 1.2.2_php4/php5
  * @author: Daniel Morris
  * @contributors: Gianpaolo Racca, Ghislain Picard, Marco Wandschneider, Chris Tobin and Andrew Eddie.
  * @copyright: Daniel Morris
  * @email: dan@rootcube.com
  * @license: GNU General Public License (GPL)
  */
class vmInputFilter {
	var $tagsArray;			// default = empty array
	var $safehtmlTags = array('a','abbr','acronym','address','b','bdo','big','blockquote','br','button','caption','center','cite','code','col','colgroup','dd','del','dfn','dir','div','dl','dt','em','fieldset','font','form','h1','h2','h3','h4','h5','h6','hr','i','iframe','img','input','ins','isindex','kbd','label','legend','li','link','map','menu','ol','optgroup','option','p','pre','q','s','samp','select','small','span','strike','strong','style','sub','sup','table','tbody','td','textarea','tfoot','th','thead','title','tr','tt','u','ul','var');
	var $attrArray;			// default = empty array

	var $tagsMethod;		// default = 0
	var $attrMethod;		// default = 0

	var $xssAuto;           // default = 1
	var $tagBlacklist = array('applet', 'body', 'bgsound', 'base', 'basefont', 'embed', 'frame', 'frameset', 'head', 'html', 'id', 'iframe', 'ilayer', 'layer', 'link', 'meta', 'name', 'object', 'script', 'style', 'title', 'xml');
	var $attrBlacklist = array('action', 'background', 'codebase', 'dynsrc', 'lowsrc');  // also will strip ALL event handlers

	/**
	  * Constructor for inputFilter class. Only first parameter is required.
	  * @access constructor
	  * @param Array $tagsArray - list of user-defined tags
	  * @param Array $attrArray - list of user-defined attributes
	  * @param int $tagsMethod - 0= allow just user-defined, 1= allow all but user-defined
	  * @param int $attrMethod - 0= allow just user-defined, 1= allow all but user-defined
	  * @param int $xssAuto - 0= only auto clean essentials, 1= allow clean blacklisted tags/attr
	  */
	function vmInputFilter($tagsArray = array(), $attrArray = array(), $tagsMethod = 0, $attrMethod = 0, $xssAuto = 1) {
		// make sure user defined arrays are in lowercase
		for ($i = 0; $i < count($tagsArray); $i++) $tagsArray[$i] = strtolower($tagsArray[$i]);
		for ($i = 0; $i < count($attrArray); $i++) $attrArray[$i] = strtolower($attrArray[$i]);
		// assign to member vars
		$this->tagsArray = (array) $tagsArray;
		$this->attrArray = (array) $attrArray;
		$this->tagsMethod = $tagsMethod;
		$this->attrMethod = $attrMethod;
		$this->xssAuto = $xssAuto;
	}
	/**
	 * Returns a reference to an input filter object, only creating it if it doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>  $filter = & vmInputFilter::getInstance();</pre>
	 *
	 * @static
	 * @param	array	$tagsArray	list of user-defined tags
	 * @param	array	$attrArray	list of user-defined attributes
	 * @param	int		$tagsMethod	WhiteList method = 0, BlackList method = 1
	 * @param	int		$attrMethod	WhiteList method = 0, BlackList method = 1
	 * @param	int		$xssAuto	Only auto clean essentials = 0, Allow clean blacklisted tags/attr = 1
	 * @return	object	The JFilterInput object.
	 * @since	1.1
	 */
	function & getInstance($tagsArray = array(), $attrArray = array(), $tagsMethod = 0, $attrMethod = 0, $xssAuto = 1) {
		static $instances;

		$sig = md5(serialize(array($tagsArray,$attrArray,$tagsMethod,$attrMethod,$xssAuto)));

		if (!isset ($instances)) {
			$instances = array();
		}

		if (empty ($instances[$sig])) {
			$instances[$sig] = new vmInputFilter($tagsArray, $attrArray, $tagsMethod, $attrMethod, $xssAuto);
		}

		return $instances[$sig];
	}
	/**
	 * Method to be called by another php script. Processes for XSS and
	 * specified bad code.
	 *
	 * @access	public
	 * @param	mixed	$source	Input string/array-of-string to be 'cleaned'
	 * @param	string	$type	Return type for the variable (INT, FLOAT, BOOLEAN, WORD, ALNUM, CMD, BASE64, STRING, ARRAY, PATH, NONE)
	 * @return	mixed	'Cleaned' version of input parameter
	 * @since	1.5
	 */
	function clean($source, $type='string')
	{
		// Handle the type constraint
		switch (strtoupper($type))
		{
			case 'INT' :
			case 'INTEGER' :
				// Only use the first integer value
				preg_match('/-?[0-9]+/', (string) $source, $matches);
				$result = @ (int) $matches[0];
				break;

			case 'FLOAT' :
			case 'DOUBLE' :
				// Only use the first floating point value
				preg_match('/-?[0-9]+(\.[0-9]+)?/', (string) $source, $matches);
				$result = @ (float) $matches[0];
				break;

			case 'BOOL' :
			case 'BOOLEAN' :
				$result = (bool) $source;
				break;

			case 'WORD' :
				$result = (string) preg_replace( '/[^A-Z_]/i', '', $source );
				break;

			case 'ALNUM' :
				$result = (string) preg_replace( '/[^A-Z0-9]/i', '', $source );
				break;

			case 'CMD' :
				$result = (string) preg_replace( '/[^A-Z0-9_\.-]/i', '', $source );
				$result = ltrim($result, '.');
				break;

			case 'BASE64' :
				$result = (string) preg_replace( '/[^A-Z0-9\/+=]/i', '', $source );
				break;

			case 'STRING' :
				$result = (string) $this->remove($this->decode((string) $source));
				break;

			case 'ARRAY' :
				$result = (array) $source;
				break;

			case 'PATH' :
				$pattern = '/^[A-Za-z0-9_-]+[A-Za-z0-9_\.-]*([\\\\\/][A-Za-z0-9_-]+[A-Za-z0-9_\.-]*)*$/';
				preg_match($pattern, (string) $source, $matches);
				$result = @ (string) $matches[0];
				break;

			case 'USERNAME' :
				$result = (string) preg_replace( '/[\x00-\x1F\x7F<>"\'%&]/', '', $source );
				break;

			default :
				// Are we dealing with an array?
				if (is_array($source)) {
					foreach ($source as $key => $value)
					{
						// filter element for XSS and other 'bad' code etc.
						if (is_string($value)) {
							$source[$key] = $this->remove($this->decode($value));
						}
					}
					$result = $source;
				} else {
					// Or a string?
					if (is_string($source) && !empty ($source)) {
						// filter source for XSS and other 'bad' code etc.
						$result = $this->remove($this->decode($source));
					} else {
						// Not an array or string.. return the passed parameter
						$result = $source;
					}
				}
				break;
		}
		return $result;
	}
	/**
	  * Method to be called by another php script. Processes for XSS and specified bad code.
	  * @access public
	  * @param Mixed $source - input string/array-of-string to be 'cleaned'
	  * @return String $source - 'cleaned' version of input parameter
	  */
	function process($source) {
		// clean all elements in this array
		if (is_array($source)) {
			foreach($source as $key => $value)
				// filter element for XSS and other 'bad' code etc.
				if (is_string($value)) $source[$key] = $this->remove($this->decode($value));
			return $source;
		// clean this string
		} else if (is_string($source)) {
			// filter source for XSS and other 'bad' code etc.
			return $this->remove($this->decode($source));
		// return parameter as given
		} else return $source;
	}

	/**
	 * Internal method to iteratively remove all unwanted tags and attributes
	 *
	 * @access	protected
	 * @param	string	$source	Input string to be 'cleaned'
	 * @return	string	$source	'cleaned' version of input parameter
	 */
	function remove($source)
	{
		$loopCounter = 0;
		/*
		 * Iteration provides nested tag protection
		 */
		while ($source != $this->filterTags($source))
		{
			$source = $this->filterTags($source);
			$loopCounter ++;
		}
		return $source;
	}

	/**
	 * Internal method to strip a string of certain tags
	 *
	 * @access	protected
	 * @param	string	$source	Input string to be 'cleaned'
	 * @return	string	$source	'cleaned' version of input parameter
	 */
	function filterTags($source)
	{
		/*
		 * In the beginning we don't really have a tag, so everything is
		 * postTag
		 */
		$preTag		= null;
		$postTag	= $source;

		/*
		 * Is there a tag? If so it will certainly start with a '<'
		 */
		$tagOpen_start	= strpos($source, '<');

		while ($tagOpen_start !== false)
		{

			/*
			 * Get some information about the tag we are processing
			 */
			$preTag		   .= substr($postTag, 0, $tagOpen_start);
			$postTag		= substr($postTag, $tagOpen_start);
			$fromTagOpen	= substr($postTag, 1);
			$tagOpen_end	= strpos($fromTagOpen, '>');

			/*
			 * Let's catch any non-terminated tags and skip over them
			 */
			if ($tagOpen_end === false)
			{
				$postTag		= substr($postTag, $tagOpen_start +1);
				$tagOpen_start	= strpos($postTag, '<');
				continue;
			}

			/*
			 * Do we have a nested tag?
			 */
			$tagOpen_nested = strpos($fromTagOpen, '<');
			//$tagOpen_nested_end	= strpos(substr($postTag, $tagOpen_end), '>');
			if (($tagOpen_nested !== false) && ($tagOpen_nested < $tagOpen_end))
			{
				$preTag		   .= substr($postTag, 0, ($tagOpen_nested +1));
				$postTag		= substr($postTag, ($tagOpen_nested +1));
				$tagOpen_start	= strpos($postTag, '<');
				continue;
			}


			/*
			 * Lets get some information about our tag and setup attribute pairs
			 */
			$tagOpen_nested	= (strpos($fromTagOpen, '<') + $tagOpen_start +1);
			$currentTag		= substr($fromTagOpen, 0, $tagOpen_end);
			$tagLength		= strlen($currentTag);
			$tagLeft		= $currentTag;
			$attrSet		= array ();
			$currentSpace	= strpos($tagLeft, ' ');

			/*
			 * Are we an open tag or a close tag?
			 */
			if (substr($currentTag, 0, 1) == "/")
			{
				// Close Tag
				$isCloseTag		= true;
				list ($tagName)	= explode(' ', $currentTag);
				$tagName		= substr($tagName, 1);
			} else
			{
				// Open Tag
				$isCloseTag		= false;
				list ($tagName)	= explode(' ', $currentTag);
			}

			/*
			 * Exclude all "non-regular" tagnames
			 * OR no tagname
			 * OR remove if xssauto is on and tag is blacklisted
			 */
			if ((!preg_match("/^[a-z][a-z0-9]*$/i", $tagName)) || (!$tagName) || ((in_array(strtolower($tagName), $this->tagBlacklist)) && ($this->xssAuto)))
			{
				$postTag		= substr($postTag, ($tagLength +2));
				$tagOpen_start	= strpos($postTag, '<');
				// Strip tag
				continue;
			}

			/*
			 * Time to grab any attributes from the tag... need this section in
			 * case attributes have spaces in the values.
			 */
			while ($currentSpace !== false)
			{
				$fromSpace		= substr($tagLeft, ($currentSpace +1));
				$nextSpace		= strpos($fromSpace, ' ');
				$openQuotes		= strpos($fromSpace, '"');
				$closeQuotes	= strpos(substr($fromSpace, ($openQuotes +1)), '"') + $openQuotes +1;

				/*
				 * Do we have an attribute to process? [check for equal sign]
				 */
				if (strpos($fromSpace, '=') !== false)
				{
					/*
					 * If the attribute value is wrapped in quotes we need to
					 * grab the substring from the closing quote, otherwise grab
					 * till the next space
					 */
					if (($openQuotes !== false) && (strpos(substr($fromSpace, ($openQuotes +1)), '"') !== false))
					{
						$attr = substr($fromSpace, 0, ($closeQuotes +1));
					} else
					{
						$attr = substr($fromSpace, 0, $nextSpace);
					}
				} else
				{
					/*
					 * No more equal signs so add any extra text in the tag into
					 * the attribute array [eg. checked]
					 */
					$attr = substr($fromSpace, 0, $nextSpace);
				}

				// Last Attribute Pair
				if (!$attr)
				{
					$attr = $fromSpace;
				}

				/*
				 * Add attribute pair to the attribute array
				 */
				$attrSet[] = $attr;

				/*
				 * Move search point and continue iteration
				 */
				$tagLeft		= substr($fromSpace, strlen($attr));
				$currentSpace	= strpos($tagLeft, ' ');
			}

			/*
			 * Is our tag in the user input array?
			 */
			$tagFound = in_array(strtolower($tagName), $this->tagsArray);

			/*
			 * If the tag is allowed lets append it to the output string
			 */
			if ((!$tagFound && $this->tagsMethod) || ($tagFound && !$this->tagsMethod))
			{
				/*
				 * Reconstruct tag with allowed attributes
				 */
				if (!$isCloseTag)
				{
					// Open or Single tag
					$attrSet = $this->filterAttr($attrSet);
					$preTag .= '<'.$tagName;
					for ($i = 0; $i < count($attrSet); $i ++)
					{
						$preTag .= ' '.$attrSet[$i];
					}

					/*
					 * Reformat single tags to XHTML
					 */
					if (strpos($fromTagOpen, "</".$tagName))
					{
						$preTag .= '>';
					} else
					{
						$preTag .= ' />';
					}
				} else
				{
					// Closing Tag
					$preTag .= '</'.$tagName.'>';
				}
			}

			/*
			 * Find next tag's start and continue iteration
			 */
			$postTag		= substr($postTag, ($tagLength +2));
			$tagOpen_start	= strpos($postTag, '<');
		}

		/*
		 * Append any code after the end of tags and return
		 */
		if ($postTag != '<')
		{
			$preTag .= $postTag;
		}
		return $preTag;
	}

	/**
	 * Internal method to strip a tag of certain attributes
	 *
	 * @access	protected
	 * @param	array	$attrSet	Array of attribute pairs to filter
	 * @return	array	$newSet		Filtered array of attribute pairs
	 */
	function filterAttr($attrSet)
	{
		/*
		 * Initialize variables
		 */
		$newSet = array ();

		/*
		 * Iterate through attribute pairs
		 */
		for ($i = 0; $i < count($attrSet); $i ++)
		{
			/*
			 * Skip blank spaces
			 */
			if (!$attrSet[$i])
			{
				continue;
			}

			/*
			 * Split into name/value pairs
			 */
			$attrSubSet = explode('=', trim($attrSet[$i]), 2);
			list ($attrSubSet[0]) = explode(' ', $attrSubSet[0]);

			/*
			 * Remove all "non-regular" attribute names
			 * AND blacklisted attributes
			 */
			if ((!eregi("^[a-z]*$", $attrSubSet[0])) || (($this->xssAuto) && ((in_array(strtolower($attrSubSet[0]), $this->attrBlacklist)) || (substr($attrSubSet[0], 0, 2) == 'on'))))
			{
				continue;
			}

			/*
			 * XSS attribute value filtering
			 */
			
			if ($attrSubSet[1])
			{
				// strips unicode, hex, etc
				$attrSubSet[1] = str_replace('&#', '', $attrSubSet[1]);
				// strip normal newline and multiple space chars within attr value
				$attrSubSet[1] = str_replace(array("\r\n", "\r", "\n"), array(' ', ' ',' '), $attrSubSet[1]);
				$attrSubSet[1] = preg_replace('/\s\s+/', ' ', $attrSubSet[1]);
				// strip slashes
				$attrSubSet[1] = stripslashes($attrSubSet[1]);
				// strip double quotes
				$attrSubSet[1] = str_replace('"', '', $attrSubSet[1]);
				// [requested feature] convert single quotes from either side to doubles (Single quotes shouldn't be used to pad attr value)
				if ((substr($attrSubSet[1], 0, 1) == "'") && (substr($attrSubSet[1], (strlen($attrSubSet[1]) - 1), 1) == "'"))
				{
					$attrSubSet[1] = substr($attrSubSet[1], 1, (strlen($attrSubSet[1]) - 2));
				}
			}

			/*
			 * Autostrip script tags
			 */
			if (vmInputFilter::badAttributeValue($attrSubSet))
			{
				continue;
			}

			/*
			 * Is our attribute in the user input array?
			 */
			$attrFound = in_array(strtolower($attrSubSet[0]), $this->attrArray);

			/*
			 * If the tag is allowed lets keep it
			 */
			if ((!$attrFound && $this->attrMethod) || ($attrFound && !$this->attrMethod))
			{
				/*
				 * Does the attribute have a value?
				 */
				if ($attrSubSet[1])
				{
					$newSet[] = $attrSubSet[0].'="'.$attrSubSet[1].'"';
				}
				elseif ($attrSubSet[1] == "0")
				{
					/*
					 * Special Case
					 * Is the value 0?
					 */
					$newSet[] = $attrSubSet[0].'="0"';
				} else
				{
					$newSet[] = $attrSubSet[0].'="'.$attrSubSet[0].'"';
				}
			}
		}
		return $newSet;
	}


	/**
	 * Function to determine if contents of an attribute is safe
	 * @param Array A 2 element array for attribute [name] and [value]
	 * @return Boolean True if bad code is detected
	 */
	function badAttributeValue( $attrSubSet ) {
		$attrSubSet[0] = strtolower( $attrSubSet[0] );
		$attrSubSet[1] = strtolower( $attrSubSet[1] );
		return (
			((strpos($attrSubSet[1], 'expression') !== false) && ($attrSubSet[0]) == 'style') ||
			(strpos($attrSubSet[1], 'javascript:') !== false) ||
			(strpos($attrSubSet[1], 'behaviour:') !== false) ||
			(strpos($attrSubSet[1], 'vbscript:') !== false) ||
			(strpos($attrSubSet[1], 'mocha:') !== false) ||
			(strpos($attrSubSet[1], 'livescript:') !== false)
		);
	}

	/**
	  * Try to convert to plaintext
	  * @access protected
	  * @param String $source
	  * @return String $source
	  */
	function decode($source) {
		if( $source != "" ) { //bypass php html_entity_decode bug # 21338 on systems where unable to upgrade php
			// url decode
			$source = @html_entity_decode($source, ENT_QUOTES, vmGetCharset() );
			// convert decimal
			$source = preg_replace('/&#(\d+);/me',"chr(\\1)", $source);				// decimal notation
			// convert hex
			$source = preg_replace('/&#x([a-f0-9]+);/mei',"chr(0x\\1)", $source);	// hex notation
		}
		return $source;
	}

	/**
	  * Method to be called by another php script. Processes for SQL injection
	  * @access public
	  * @param Mixed $source - input string/array-of-string to be 'cleaned'
	  * @param Buffer $connection - An open MySQL connection
	  * @return String $source - 'cleaned' version of input parameter
	  */
	function safeSQL($source) {
		
		// clean all elements in this array
		if (is_array($source)) {
			foreach($source as $key => $value)
				// filter element for SQL injection
				if (is_string($value)) $source[$key] = $this->quoteSmart($this->decode($value));
			return $source;
		// clean this string
		} else if (is_string($source)) {
			// filter source for SQL injection
			if (is_string($source)) return $this->quoteSmart($this->decode($source));
		// return parameter as given
		} else return $source;
	}

	/**
	  * @author Chris Tobin
	  * @author Daniel Morris
	  * @access protected
	  * @param String $source
	  * @param Resource $connection - An open MySQL connection
	  * @return String $source
	  */
	function quoteSmart($source) {
		// strip slashes
		if (get_magic_quotes_gpc()) $source = stripslashes($source);
		// quote both numeric and text
		$source = $this->escapeString($source);
		return $source;
	}

	/**
	  * @author Chris Tobin
	  * @author Daniel Morris
	  * @access protected
	  * @param String $source
	  * @param Resource $connection - An open MySQL connection
	  * @return String $source
	  */
	function escapeString($string) {
		global $database;
		return $database->getEscaped( $string );
		
	}
}

?>