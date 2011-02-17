<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );



/**
 *
 * @version $Id$
 * @package VirtueMart
 * @subpackage classes
 * @copyright Copyright (C) 2004-2007 soeren - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.org
 */

// ============================================================
// ================ CURRENCY DISPLAY ==========================
// ============================================================
// == version : 1.1	    (class_currency_display.php)
// ============================================================
// ==== Description
// == Currency display class : format money values for display
// ==== Relationships :
// == None, but may be ideally used with CurrencyConvert class
// ============================================================
// ==== History :
// == 16/11/2000 : S. Mouton	First Version
// == 29/11/2000 : S. Mouton	Added euro conversion euro
// == 27/02/2001 : S. Mouton	Full re organisation : separate between DB and non DB version
// == 14/03/2001 : S. Mouton    Minor bug in negative displays corrected
// ============================================================

class CurrencyDisplay {
    public $id      		= "euro";		// string ID related with the currency (ex : language)
    public $symbol    		= "&euro;";	// Printable symbol
    public $nbDecimal 		= 2;	// Number of decimals past colon (or other)
    public $decimal   		= ",";	// Decimal symbol ('.', ',', ...)
    public $thousands 		= " "; 	// Thousands separator ('', ' ', ',')
    public $positivePos	= 1;	// Currency symbol position with Positive values :
    // 0 = '00Symb'
    // 1 = '00 Symb'
    // 2 = 'Symb00'
    // 3 = 'Symb 00'
    public $negativePos	= 8;	// Currency symbol position with Negative values :
    // 0 = '(Symb00)'
    // 1 = '-Symb00'
    // 2 = 'Symb-00'
    // 3 = 'Symb00-'
    // 4 = '(00Symb)'
    // 5 = '-00Symb'
    // 6 = '00-Symb'
    // 7 = '00Symb-'
    // 8 = '-00 Symb'
    // 9 = '-Symb 00'
    // 10 = '00 Symb-'
    // 11 = 'Symb 00-'
    // 12 = 'Symb -00'
    // 13 = '00- Symb'
    // 14 = '(Symb 00)'
    // 15 = '(00 Symb)'
    // ================
    function __construct (
	    $id			="euro",  //id is a string?
	    $symbol		="&euro;",
	    $nbDecimal	= 2,
	    $decimal   	= ",",
	    $thousands 	= " ",
	    $positivePos= 1,
	    $negativePos= 8) {
	$this->id		 = $id;
	$this->symbol    = $symbol;
	$this->nbDecimal = $nbDecimal;
	$this->decimal   = $decimal;
	$this->thousands = $thousands;
	$this->positivePos = $positivePos;
	$this->negativePos = $negativePos;

    }

	/**
	 * 
	 * Gives back the formate of the currency, gets $style if none is set, with the currency Id, when nothing is found it tries the vendorId.
	 * When no param is set, you get the format of the mainvendor
	 * 
	 * @author unknown
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
									// 0 = '00Symb'
									// 1 = '00 Symb'
									// 2 = 'Symb00'
									// 3 = 'Symb 00'
    7: Currency symbol position with Negative values :
									// 0 = '(Symb00)'
									// 1 = '-Symb00'
									// 2 = 'Symb-00'
									// 3 = 'Symb00-'
									// 4 = '(00Symb)'
									// 5 = '-00Symb'
									// 6 = '00-Symb'
									// 7 = '00Symb-'
									// 8 = '-00 Symb'
									// 9 = '-Symb 00'
									// 10 = '00 Symb-'
									// 11 = 'Symb 00-'
									// 12 = 'Symb -00'
									// 13 = '00- Symb'
									// 14 = '(Symb 00)'
									// 15 = '(00 Symb)'
    	EXAMPLE: ||&euro;|2|,||1|8
	* @return string
	*/
	public function getCurrencyDisplay($vendorId=0, $currencyId=0, $style=0){

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
		if(!empty($style)){
			$array = explode( "|", $style );
			$_currencyDisplayStyle = Array();
			$_currencyDisplayStyle['id'] = !empty($array[0]) ? $array[0] : 0;
			$_currencyDisplayStyle['symbol'] = !empty($array[1]) ? $array[1] : '';
			$_currencyDisplayStyle['nbdecimal'] = !empty($array[2]) ? $array[2] : '';
			$_currencyDisplayStyle['sdecimal'] = !empty($array[3]) ? $array[3] : '';
			$_currencyDisplayStyle['thousands'] = !empty($array[4]) ? $array[4] : '';
			$_currencyDisplayStyle['positive'] = !empty($array[5]) ? $array[5] : '';
			$_currencyDisplayStyle['negative'] = !empty($array[6]) ? $array[6] : '';	
			$currency = new CurrencyDisplay($_currencyDisplayStyle['id'], $_currencyDisplayStyle['symbol']
				, $_currencyDisplayStyle['nbdecimal'], $_currencyDisplayStyle['sdecimal']
				, $_currencyDisplayStyle['thousands'], $_currencyDisplayStyle['positive']
				, $_currencyDisplayStyle['negative']
			);				
		} else {
			JError::raiseWarning('1', JText::_('VM_CONF_WARN_NO_CURRENCY_DEFINED'));
			//would be nice to automatically unpublish the product or so
			$currency = new CurrencyDisplay();
		}
		
		return $currency;
	}

    /**
     * Parse the given currency display string into the currency diplsy values.
     * 
     * This function takes the currency style string as saved in the vendor
     * record and parses it into its appropriate values.  An example style
     * string would be 1|&euro;|2|,|.|0|0
     *
     * @author RickG
     * @param String $currencyStyle String containing the currency display settings
     */
    public function setCurrencyDisplayToStyleStr($currencyStyle='') {
	if ($currencyStyle) {
	    $array = explode("|", $currencyStyle);
	    $this->id = $array[0];
	    $this->symbol = $array[1];
	    $this->nbDecimal = $array[2];
	    $this->decimal   = $array[3];
	    $this->thousands = $array[4];
	    $this->positivePos = $array[5];
	    $this->negativePos = $array[6];	    
	}
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

    /**
     * Get the price value
     */
    public function getValue($nb, $decimals='') {
	$res = "";
	// Warning ! number_format function performs implicit rounding
	// Rounding is not handled in this DISPLAY class
	// that's why you have to use the right decimal value.
	// Workaround :number_format accepts either 1, 2 or 4 parameters.
	// this cause problem when no thousands separator is given : in this
	// case, an unwanted ',' is displayed.
	// That's why we have to do the work ourserlve.
	// Note : when no decimal il given (i.e. 3 parameters), everything works fine
	
	if(is_string($nb)) $nb = floatval($nb);
	if( $decimals === '') {
	    $decimals = $this->nbDecimal;
	}
	if ($this->thousands != '') {
	    $res=number_format($nb,$decimals,$this->decimal,$this->thousands);
	} else {
	    // If decimal is equal to defaut thousand separator, apply a trick
	    if ($this->decimal==',') {
		$res=number_format($nb,$decimals,$this->decimal,'|');
		$res=str_replace('|','',$res);
	    } else {
		// Else a simple substitution is enough
		$res=number_format($nb,$decimals,$this->decimal,$this->thousands);
		$res=str_replace(',','',$res);
	    }
	}
	return($res);
    }

    /**
     * Create the full price
     */
    public function getFullValue($nb, $decimals='', $symbol = '') {

	$res = "";
	if( $symbol != ''  ) {
	    $old_symbol = $this->symbol;
	    $this->symbol = $symbol;
	}
	// Currency symbol position
	if ($nb == abs($nb)) {
	    $res=$this->getValue($nb, $decimals);
	    // Positive number
	    switch ($this->positivePos) {
		case 0:
		// 0 = '00Symb'
		    $res=$res.$this->symbol;
		    break;
		case 2:
		// 2 = 'Symb00'
		    $res=$this->symbol.$res;
		    break;
		case 3:
		// 3 = 'Symb 00'
		    $res=$this->symbol.' '.$res;
		    break;
		case 1:
		default :
		// 1 = '00 Symb'
		    $res=$res.' '.$this->symbol;
		    break;
	    }
	} else {
	    // Negative number
	    $res=$this->getValue(abs($nb), $decimals);
	    switch ($this->negativePos) {
		case 0:
		// 0 = '(Symb00)'
		    $res='('.$this->symbol.$res.')';
		    break;
		case 1:
		// 1 = '-Symb00'
		    $res='-'.$this->symbol.$res;
		    break;
		case 2:
		// 2 = 'Symb-00'
		    $res=$this->symbol.'-'.$res;
		    break;
		case 3:
		// 3 = 'Symb00-'
		    $res=$this->symbol.$res.'-';
		    break;
		case 4:
		// 4 = '(00Symb)'
		    $res='('.$res.$this->symbol.')';
		    break;
		case 5:
		// 5 = '-00Symb'
		    $res='-'.$res.$this->symbol;
		    break;
		case 6:
		// 6 = '00-Symb'
		    $res=$res.'-'.$this->symbol;
		    break;
		case 7:
		// 7 = '00Symb-'
		    $res=$res.$this->symbol.'-';
		    break;
		case 9:
		// 9 = '-Symb 00'
		    $res='-'.$this->symbol.' '.$res;
		    break;
		case 10:
		// 10 = '00 Symb-'
		    $res=$res.' '.$this->symbol.'-';
		    break;
		case 11:
		// 11 = 'Symb 00-'
		    $res=$this->symbol.' '.$res.'-';
		    break;
		case 12:
		// 12 = 'Symb -00'
		    $res=$this->symbol.' -'.$res;
		    break;
		case 13:
		// 13 = '00- Symb'
		    $res=$res.'- '.$this->symbol;
		    break;
		case 14:
		// 14 = '(Symb 00)'
		    $res='('.$this->symbol.' '.$res.')';
		    break;
		case 15:
		// 15 = '(00 Symb)'
		    $res='('.$res.' '.$this->symbol.')';
		    break;
		case 8:
		default :
		// 8 = '-00 Symb'
		    $res='-'.$res.' '.$this->symbol;
		    break;
	    }
	}
	if( $symbol != '' ) {
	    $this->symbol = $old_symbol;
	}
	return($res);
    }
    // ================ /CURRENCY DISPLAY =========================
    // ============================================================
} // end class
// pure php no closing tag
