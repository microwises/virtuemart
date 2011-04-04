<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );



/**
 *
 * @version $Id$
 * @package VirtueMart
 * @subpackage classes
 *
 * @author Max Milbers
 * @copyright Copyright (C) 2011 Virtuemart - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.org
 */

class CurrencyDisplay {

	static $_instance;

    var $id      		= "udef";		// string ID related with the currency (ex : language)
    var $symbol    		= "udef";	// Printable symbol
    var $nbDecimal 		= 2;	// Number of decimals past colon (or other)
    var $decimal   		= ",";	// Decimal symbol ('.', ',', ...)
    var $thousands 		= " "; 	// Thousands separator ('', ' ', ',')
    var $positivePos	= '*sig**numb**symb*';	// Currency symbol position with Positive values :
    var $negativePos	= '*sig**numb**symb*';	// Currency symbol position with Negative values :

    private function __construct (){

	}

	/**
	 *
	 * Gives back the formate of the currency, gets $style if none is set, with the currency Id, when nothing is found it tries the vendorId.
	 * When no param is set, you get the format of the mainvendor
	 *
	 * @author Max Milbers
	 * @param int 		$currencyId Id of the currency
	 * @param int 		$vendorId Id of the vendor
	 * @param string 	$style The vendor_currency_display_code
	*   FORMAT:
    1: id,
    2: CurrencySymbol,
    3: NumberOfDecimalsAfterDecimalSymbol,
    4: DecimalSymbol,
    5: Thousands separator
    6: Currency symbol position with Positive values :
    7: Currency symbol position with Negative values :

    	EXAMPLE: ||&euro;|2|,||1|8
	* @return string
	*/
	public function getCurrencyDisplay($vendorId=0, $currencyId=0, $style=0){

		if(empty(self::$_instance)){
			if(empty($style)){
				$db = JFactory::getDBO();
				if(!empty($currencyId)){
					$q = 'SELECT `display_style` FROM `#__vm_currency` WHERE `currency_id`="'.$currencyId.'"';
					$db->setQuery($q);
					$style = $db->loadResult();
				}
				if(empty($style)){
					if(empty($vendorId)){
						$vendorId = 1;		//Map to mainvendor
					}
					$q = 'SELECT `vendor_currency` FROM `#__vm_vendor` WHERE `vendor_id`="'.$vendorId.'"';
					$db->setQuery($q);
					$currencyId = $db->loadResult();

					$q = 'SELECT `display_style` FROM `#__vm_currency` WHERE `currency_id`="'.$currencyId.'"';
					$db->setQuery($q);
					$style = $db->loadResult();
				}
			}
			self::$_instance = new CurrencyDisplay();

			if(!empty($style)){
				self::$_instance->setCurrencyDisplayToStyleStr($style);
			} else {
				$app =& JFactory::getApplication('administrator');
				$uri =& JFactory::getURI();

				if(empty($currencyId)){
					$link = $uri->root().'administrator/index.php?option=com_virtuemart&view=user&task=editshop';
					JError::raiseWarning('1', JText::sprintf('COM_VIRTUEMART_CONF_WARN_NO_CURRENCY_DEFINED','<a href="'.$link.'">'.$link.'</a>'));
				} else{
					$link = $uri->root().'administrator/index.php?option=com_virtuemart&view=currency&task=edit&cid[]='.$currencyId;
					JError::raiseWarning('1', JText::sprintf('COM_VIRTUEMART_CONF_WARN_NO_FORMAT_DEFINED','<a href="'.$link.'">'.$link.'</a>'));
				}
				//would be nice to automatically unpublish the product/currency or so
			}
		}

		return self::$_instance;
	}

    /**
     * Parse the given currency display string into the currency diplsy values.
     *
     * This function takes the currency style string as saved in the vendor
     * record and parses it into its appropriate values.  An example style
     * string would be 1|&euro;|2|,|.|0|0
     *
     * @author Max Milbers
     * @param String $currencyStyle String containing the currency display settings
     */
    public function setCurrencyDisplayToStyleStr($currencyStyle='') {

		if ($currencyStyle) {
		    $array = explode("|", $currencyStyle);
		    if(!empty($array[0])) $this->id = $array[0];
		    if(!empty($array[1])) $this->symbol = $array[1];
		    if(!empty($array[2])) $this->nbDecimal = $array[2];
		    if(!empty($array[3])) $this->decimal = $array[3];
		    if(!empty($array[4])) $this->thousands = $array[4];
		    if(!empty($array[5])) $this->positivePos = $array[5];
		    if(!empty($array[6])) $this->negativePos = $array[6];
		}
    }

	/**
	 * Get the formatted and rounded value for display
	 * @deprecated Use CurrencyDisplay::getFullValue() instead
	 * @param fload $nb Amount
	 * @param integer $decimals r. of decimals
	 */
	public function getValue($nb, $decimals='')
	{
		return self::getFullValue($nb, $decimals);
	}

    /**
     * Format, Round and Display Value
     * @author Max Milbers
     * @param val number
     */
    public function getFullValue($nb,$nbDecimal=0 ){

    	if(empty($nbDecimal)) $nbDecimal = $this->nbDecimal;
    	if($nb>=0){
    		$format = $this->positivePos;
    		$sign = '+';
    	} else {
    		$format = $this->negativePos;
    		$sign = '-';
    	}

    	$res = $this->formatNumber($nb, $nbDecimal, $this->thousands, $this->decimal);
    	$search = array('*sig*', '*numb*', '*symb*');
    	$replace = array($sign, $res, $this->symbol);
    	$formattedRounded = str_replace ($search,$replace,$format);

    	return $formattedRounded;
    }

    /**
     * @author Horvath, Sandor [HU] http://de.php.net/manual/de/function.number-format.php
     * Enter description here ...
     * @param double $number
     * @param int $decimals
     * @param string $thousand_separator
     * @param string $decimal_point
     */
    function formatNumber($number, $decimals = 2, $thousand_separator = '&nbsp;', $decimal_point = '.'){

    	$tmp1 = round((float) $number, $decimals);

		while (($tmp2 = preg_replace('/(\d+)(\d\d\d)/', '\1 \2', $tmp1)) != $tmp1){
			$tmp1 = $tmp2;
		}

		return strtr($tmp1, array(' ' => $thousand_separator, '.' => $decimal_point));
    }

    /**
     * Return the currency symbol
     */
    public function getSymbol() {
		return($this->symbol);
    }

    /**
     * Return the currency ID
     */
    public function getId() {
		return($this->id);
    }

    /**
     * Return the number of decimal places
     *
     * @author RickG
     * @return int Number of decimal places
     */
    public function getNbrDecimals() {
		return($this->nbDecimal);
    }

    /**
     * Return the decimal symbol
     *
     * @author RickG
     * @return string Decimal place symbol
     */
    public function getDecimalSymbol() {
		return($this->decimal);
    }

    /**
     * Return the decimal symbol
     *
     * @author RickG
     * @return string Decimal place symbol
     */
    public function getThousandsSeperator() {
		return($this->thousands);
    }

    /**
     * Return the positive format
     *
     * @author RickG
     * @return string Positive number format
     */
    public function getPositiveFormat() {
		return($this->positivePos);
    }

     /**
     * Return the negative format
     *
     * @author RickG
     * @return string Negative number format
     */
    public function getNegativeFormat() {
		return($this->negativePos);
    }



}
// pure php no closing tag
