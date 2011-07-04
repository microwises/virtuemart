<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );



/**
 *
 * @version $Id$
 * @package VirtueMart
 * @subpackage classes
 *
 * @author Max Milbers
 * @copyright Copyright (C) 2004-2008 Soeren Eberhardt-Biermann - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */

class CurrencyDisplay {

	static $_instance;
	private $_currencyConverter;

    private $_currency_id   = '0';		// string ID related with the currency (ex : language)
    private $_symbol    		= 'udef';	// Printable symbol
    private $_nbDecimal 		= 2;	// Number of decimals past colon (or other)
    private $_decimal   		= ',';	// Decimal symbol ('.', ',', ...)
    private $_thousands 		= ' '; 	// Thousands separator ('', ' ', ',')
    private $_positivePos	= '{number}{symbol}';	// Currency symbol position with Positive values :
    private $_negativePos	= '{sign}{number}{symbol}';	// Currency symbol position with Negative values :

    private function __construct ($vendorId = 0){

		$converterFile  = VmConfig::get('currency_converter_module');

		if (file_exists( JPATH_VM_ADMINISTRATOR.DS.'plugins'.DS.'currency_converter'.DS.$converterFile.'.php' )) {
			$module_filename = $converterFile;
			require_once(JPATH_VM_ADMINISTRATOR.DS.'plugins'.DS.'currency_converter'.DS.$converterFile.'.php');
			if( class_exists( $module_filename )) {
				$this->_currencyConverter = new $module_filename();
			}
		} else {
			if(!class_exists('convertECB')) require(JPATH_VM_ADMINISTRATOR.DS.'plugins'.DS.'currency_converter'.DS.'convertECB.php');
			$this->_currencyConverter = new convertECB();
		}

		$this->_app = JFactory::getApplication();
		if(empty($vendorId)) $vendorId = 1;

		$this->_db = JFactory::getDBO();
		$q = 'SELECT `vendor_currency` FROM `#__virtuemart_vendors` WHERE `virtuemart_vendor_id`="'.(int)$vendorId.'"';
		$this->_db->setQuery($q);
		$this->_vendorCurrency = $this->_db->loadResult();
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
	public function getInstance($currencyId=0,$vendorId=0){

		if(empty(self::$_instance) || empty(self::$_instance->_currency_id) || $currencyId!=self::$_instance->_currency_id ){

			self::$_instance = new CurrencyDisplay($vendorId);

			if(empty($currencyid)){

				if(self::$_instance->_app->isSite()){
					$this->_currency_id = self::$_instance->_app->getUserStateFromRequest( "virtuemart_currency_id", 'virtuemart_currency_id',JRequest::getInt('virtuemart_currency_id', 0));
				}
				if(empty($this->_currency_id)){
					$this->_currency_id = self::$_instance->_vendorCurrency;
				}

			} else {
				$this->_currency_id = $currencyId;
			}

			$q = 'SELECT * FROM `#__virtuemart_currencies` WHERE `virtuemart_currency_id`="'.(int)$this->_currency_id.'"';
			self::$_instance->_db->setQuery($q);
			$style = self::$_instance->_db->loadObject();

			if(!empty($style)){
				self::$_instance->setCurrencyDisplayToStyleStr($style);
			} else {
				$uri = JFactory::getURI();

				if(empty($currencyId)){
					$link = $uri->root().'administrator/index.php?option=com_virtuemart&view=user&task=editshop';
					JError::raiseWarning('1', JText::sprintf('COM_VIRTUEMART_CONF_WARN_NO_CURRENCY_DEFINED','<a href="'.$link.'">'.$link.'</a>'));
				} else{
					if(JRequest::getWord('view')!='currency'){
						$link = $uri->root().'administrator/index.php?option=com_virtuemart&view=currency&task=edit&cid[]='.$currencyId;
						JError::raiseWarning('1', JText::sprintf('COM_VIRTUEMART_CONF_WARN_NO_FORMAT_DEFINED','<a href="'.$link.'">'.$link.'</a>'));
					}
				}

//				self::$_instance->setCurrencyDisplayToStyleStr($currencyId);
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
    private function setCurrencyDisplayToStyleStr($style) {

    	$this->_currency_id = $style->virtuemart_currency_id;
		$this->_symbol = $style->currency_symbol;
		$this->_nbDecimal = $style->currency_decimal_place;
		$this->_decimal = $style->currency_decimal_symbol;
		$this->_thousands = $style->currency_thousands;
		$this->_positivePos = $style->currency_positive_style;
		$this->_negativePos = $style->currency_negative_style;

    }

	/**
	 * This function is for the gui only!
	 * Use this only in a view, plugin or modul, never in a model
	 *
	 * @param float $price
	 * @param integer $currencyId
	 * return string formatted price
	 */
	public function priceDisplay($price=0, $currencyId=0,$inToShopCurrency = false){
		// if($price ) Outcommented (Oscar) to allow 0 values to be formatted too (e.g. free shipping)

		if(empty($currencyId)){
			$currencyId = (int)$this->_app->getUserStateFromRequest( 'virtuemart_currency_id', 'virtuemart_currency_id',$this->_vendorCurrency );
			if(empty($currencyId)){
				$currencyId = $this->_vendorCurrency;
			}
		}

		$price = $this->convertCurrencyTo($currencyId,$price,$inToShopCurrency);
		return $this->getFormattedCurrency($price);
	}

	/**
	* function to create a div to show the prices, is necessary for JS
	*
	* @author Max Milbers
	* @author Patrick Kohl
	* @param string name of the price
	* @param String description key
	* @param array the prices of the product
	* return a div for prices which is visible according to config and have all ids and class set
	*/
	public function createPriceDiv($name,$description,$product_price){

		if(empty($product_price)) return '';


		//This could be easily extended by product specific settings
		if(VmConfig::get($name) =='1'){
			if(!empty($product_price[$name])){
				$vis = "block";
				//if(!class_exists('CurrencyDisplay')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'currencydisplay.php');

				$product_price[$name] = $this->priceDisplay($product_price[$name]);
			} else {
				$vis = "none";
			}
			$descr = '';
			if(VmConfig::get($name.'Text',true)) $descr = JText::_($description);
			//	 	if(!empty($product_price[$name])){
			return '<div style="display : '.$vis.';" >'.$descr.'<span class="Price'.$name.'" >'.$product_price[$name].'</span></div>';
			//	 	}
		}
	}

	/**
	 *
	 * @author Max Milbers
	 * @param unknown_type $currency
	 * @param unknown_type $price
	 * @param unknown_type $shop
	 */
	function convertCurrencyTo($currency,$price,$shop=true){

		if(empty($currency)){
			return $price;
		}

		// If both currency codes match, do nothing
		if( $currency == $this->_vendorCurrency ) {
			return $price;
		}

		if($shop){
			// TODO optimize this... the exchangeRate cant be cached, there are more than one currency possible
//			$exchangeRate = &$this->exchangeRateVendor;
			$exchangeRate = 0;
		} else {
			//caches the exchangeRate between shopper and vendor
			$exchangeRate = &$this->exchangeRateShopper;
		}

		if(empty($exchangeRate)){
			if(is_Int($currency)){
//				$this->_db = JFactory::getDBO();
				$q = 'SELECT `currency_exchange_rate`
				FROM `#__virtuemart_currencies` WHERE `virtuemart_currency_id` ="'.(int)$currency.'" ';
				$this->_db->setQuery($q);
				if(	$exch = $this->_db->loadResult()){
					$exchangeRate = $this->_db->loadResult();
				} else {
					$exchangeRate = FALSE;
				}
			} else {

				$exchangeRate = $currency->_vendorCurrency;
			}
		}

		if(!empty($exchangeRate) && $exchangeRate!=FALSE){
			$price = $price * $exchangeRate;
		} else {
			if($shop){
				$price = $this ->_currencyConverter->convert( $price, self::ensureUsingCurrencyCode($currency),self::ensureUsingCurrencyCode($this->_vendorCurrency));
			} else {
				$price = $this ->_currencyConverter->convert( $price , self::ensureUsingCurrencyCode($this->_vendorCurrency),  self::ensureUsingCurrencyCode($currency));
			}

		}

		return $price;
	}


	/**
	 * Changes the virtuemart_currency_id into the right currency_code
	 * For exampel 47 => EUR
	 *
	 * @author Max Milbers
	 * @author Frederic Bidon
	 */
	function ensureUsingCurrencyCode($curr){

		if(is_numeric($curr)){
			$this->_db = JFactory::getDBO();
			$q = 'SELECT `currency_code_3` FROM `#__virtuemart_currencies` WHERE `virtuemart_currency_id`="'.(int)$curr.'"';
			$this->_db->setQuery($q);
			$currInt = $this->_db->loadResult();
			if(empty($currInt)){
				JError::raiseWarning(E_WARNING,'Attention, couldnt find currency code in the table for id = '.$curr);
			}
			return $currInt;
		}
		return $curr;
	}

    /**
     * Format, Round and Display Value
     * @author Max Milbers
     * @param val number
     */
    private function getFormattedCurrency( $nb, $nbDecimal=0){

    	if(empty($nbDecimal)) $nbDecimal = $this->_nbDecimal;
    	if($nb>=0){
    		$format = $this->_positivePos;
    		$sign = '+';
    	} else {
    		$format = $this->_negativePos;
    		$sign = '-';
			$nb = abs($nb);
    	}

    	//$res = $this->formatNumber($nb, $nbDecimal, $this->_thousands, $this->_decimal);
		$res = number_format((float)$nb,$nbDecimal,$this->_decimal,$this->_thousands);
    	$search = array('{sign}', '{number}', '{symbol}');
    	$replace = array($sign, $res, $this->_symbol);
    	$formattedRounded = str_replace ($search,$replace,$format);

    	return $formattedRounded;
    }

    /**
     *
     * @author Horvath, Sandor [HU] http://de.php.net/manual/de/function.number-format.php
     * @author Max Milbers
     * @param double $number
     * @param int $decimals
     * @param string $thousand_separator
     * @param string $decimal_point
     */
    function formatNumber($number, $decimals = 2, $decimal_point = '.', $thousand_separator = '&nbsp;' ){

//    	$tmp1 = round((float) $number, $decimals);

    	return number_format($number,$decimals,$decimal_point,$thousand_separator);
//		while (($tmp2 = preg_replace('/(\d+)(\d\d\d)/', '\1 \2', $tmp1)) != $tmp1){
//			$tmp1 = $tmp2;
//		}
//
//		return strtr($tmp1, array(' ' => $thousand_separator, '.' => $decimal_point));
    }

    /**
     * Return the currency symbol
     */
    public function getSymbol() {
		return($this->_symbol);
    }

    /**
     * Return the currency ID
     */
    public function getId() {
		return($this->_currency_id);
    }

    /**
     * Return the number of decimal places
     *
     * @author RickG
     * @return int Number of decimal places
     */
    public function getNbrDecimals() {
		return($this->_nbDecimal);
    }

    /**
     * Return the decimal symbol
     *
     * @author RickG
     * @return string Decimal place symbol
     */
    public function getDecimalSymbol() {
		return($this->_decimal);
    }

    /**
     * Return the decimal symbol
     *
     * @author RickG
     * @return string Decimal place symbol
     */
    public function getThousandsSeperator() {
		return($this->_thousands);
    }

    /**
     * Return the positive format
     *
     * @author RickG
     * @return string Positive number format
     */
    public function getPositiveFormat() {
		return($this->_positivePos);
    }

     /**
     * Return the negative format
     *
     * @author RickG
     * @return string Negative number format
     */
    public function getNegativeFormat() {
		return($this->_negativePos);
    }



}
// pure php no closing tag
