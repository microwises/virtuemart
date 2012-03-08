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
	var $_priceConfig	= array();	//holds arrays of 0 and 1 first is if price should be shown, second is rounding
	var $exchangeRateShopper = 1.0;

	private function __construct ($vendorId = 0){

		$this->_app = JFactory::getApplication();
		if(empty($vendorId)) $vendorId = 1;

		$this->_db = JFactory::getDBO();
		$q = 'SELECT `vendor_currency` FROM `#__virtuemart_vendors` WHERE `virtuemart_vendor_id`="'.(int)$vendorId.'"';
		$this->_db->setQuery($q);
		$this->_vendorCurrency = $this->_db->loadResult();

		$converterFile  = VmConfig::get('currency_converter_module');

		if (file_exists( JPATH_VM_ADMINISTRATOR.DS.'plugins'.DS.'currency_converter'.DS.$converterFile )) {
			$module_filename=substr($converterFile, 0, -4);
			require_once(JPATH_VM_ADMINISTRATOR.DS.'plugins'.DS.'currency_converter'.DS.$converterFile);
			if( class_exists( $module_filename )) {
				$this->_currencyConverter = new $module_filename();
			}
		} else {
			if($this->_vendorCurrency===47){
				if(!class_exists('convertECB')) require(JPATH_VM_ADMINISTRATOR.DS.'plugins'.DS.'currency_converter'.DS.'convertECB.php');
				$this->_currencyConverter = new convertECB();
			} else {
				vmWarn('Cant use fallback method, using ECB. Your vendor currency is not euro.');
			}

		}

		$this->setPriceArray();
	}

	/**
	 *
	 * Gives back the format of the currency, gets $style if none is set, with the currency Id, when nothing is found it tries the vendorId.
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

// 		vmdebug('hmmmmm getInstance given $currencyId '.$currencyId,self::$_instance->_currency_id);
// 		if(empty(self::$_instance) || empty(self::$_instance->_currency_id) || ($currencyId!=self::$_instance->_currency_id && !empty($currencyId)) ){
			if(empty(self::$_instance)  || (!empty($currencyId) and $currencyId!=self::$_instance->_currency_id) ){

			self::$_instance = new CurrencyDisplay($vendorId);

			if(empty($currencyId)){

				if(self::$_instance->_app->isSite()){
					self::$_instance->_currency_id = self::$_instance->_app->getUserStateFromRequest( "virtuemart_currency_id", 'virtuemart_currency_id',JRequest::getInt('virtuemart_currency_id', 0));
				}
				if(empty(self::$_instance->_currency_id)){
					self::$_instance->_currency_id = self::$_instance->_vendorCurrency;
				}

			} else {
				self::$_instance->_currency_id = $currencyId;
			}


			$q = 'SELECT * FROM `#__virtuemart_currencies` WHERE `virtuemart_currency_id`="'.(int)self::$_instance->_currency_id.'"';
			self::$_instance->_db->setQuery($q);
			$style = self::$_instance->_db->loadObject();

			if(!empty($style)){
				self::$_instance->setCurrencyDisplayToStyleStr($style);
			} else {
				$uri = JFactory::getURI();

				if(empty(self::$_instance->_currency_id)){
					$link = $uri->root().'administrator/index.php?option=com_virtuemart&view=user&task=editshop';
					JError::raiseWarning('1', JText::sprintf('COM_VIRTUEMART_CONF_WARN_NO_CURRENCY_DEFINED','<a href="'.$link.'">'.$link.'</a>'));
				} else{
					if(JRequest::getWord('view')!='currency'){
						$link = $uri->root().'administrator/index.php?option=com_virtuemart&view=currency&task=edit&cid[]='.self::$_instance->_currency_id;
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
	 * This function sets an array, which holds the information if
	 * a price is to be shown and the number of rounding digits
	 *
	 * @author Max Milbers
	 */
	function setPriceArray(){

		if(!class_exists('JParameter')) require(JPATH_VM_LIBRARIES.DS.'joomla'.DS.'html'.DS.'parameter.php' );

		$user = JFactory::getUser();

		//Not working for a user which is registered and should be in the standard group, but isnt (automap)
/*		if(!empty($user->id)){

			$q = 'SELECT `price_display`,`custom_price_display` FROM `#__virtuemart_vmusers` as `u`
							LEFT OUTER JOIN `#__virtuemart_vmuser_shoppergroups` AS `vx` ON `u`.`virtuemart_user_id`  = `vx`.`virtuemart_user_id`
							LEFT OUTER JOIN `#__virtuemart_shoppergroups` AS `sg` ON `vx`.`virtuemart_shoppergroup_id` = `sg`.`virtuemart_shoppergroup_id`
							WHERE `u`.`virtuemart_user_id` = "'.$user->id.'" ';

			$this->_db->setQuery($q);
			$result = $this->_db->loadRow();
// 			vmdebug('setPriceArray',$result);
			if(!empty($result[0])){
				$result[0] = unserialize($result[0]);
			}
		} else {
			$q = 'SELECT `price_display`,`custom_price_display` FROM `#__virtuemart_shoppergroups` AS `sg`
					WHERE `sg`.`default` = "2" ';

			$this->_db->setQuery($q);
			$result = $this->_db->loadRow();
// 			vmdebug('setPriceArray',$result);
			if(!empty($result[0])){
				$result[0] = unserialize($result[0]);
			}
		}*/

		$result = false;
		if(!empty($user->id)){
			$q = 'SELECT `vx`.`virtuemart_shoppergroup_id` FROM `#__virtuemart_vmusers` as `u`
									LEFT OUTER JOIN `#__virtuemart_vmuser_shoppergroups` AS `vx` ON `u`.`virtuemart_user_id`  = `vx`.`virtuemart_user_id`
									LEFT OUTER JOIN `#__virtuemart_shoppergroups` AS `sg` ON `vx`.`virtuemart_shoppergroup_id` = `sg`.`virtuemart_shoppergroup_id`
									WHERE `u`.`virtuemart_user_id` = "'.$user->id.'" ';
			$this->_db->setQuery($q);
			$result = $this->_db->loadResult();
		}

		if(!$result){
			$q = 'SELECT `price_display`,`custom_price_display` FROM `#__virtuemart_shoppergroups` AS `sg`
							WHERE `sg`.`default` = "'.($user->guest+1).'" ';

			$this->_db->setQuery($q);
			$result = $this->_db->loadRow();
		} else {
			$q = 'SELECT `price_display`,`custom_price_display` FROM `#__virtuemart_shoppergroups` AS `sg`
										WHERE `sg`.`virtuemart_shoppergroup_id` = "'.$result.'" ';

			$this->_db->setQuery($q);
			$result = $this->_db->loadRow();
		}

		if(!empty($result[0])){
			$result[0] = unserialize($result[0]);
		}

		$custom_price_display = 0;
		if(!empty($result[1])){
			$custom_price_display = $result[1];
		}

		if($custom_price_display && !empty($result[0])){
			$show_prices = $result[0]->get('show_prices',VmConfig::get('show_prices', 1));
// 			vmdebug('$result[0]',$result[0],$show_prices);
		} else {
			$show_prices = VmConfig::get('show_prices', 1);
		}



		$priceFields = array('basePrice','variantModification','basePriceVariant',
											'basePriceWithTax','discountedPriceWithoutTax',
											'salesPriceWithDiscount','salesPrice','priceWithoutTax',
											'discountAmount','taxAmount');

		if($show_prices==1){
			foreach($priceFields as $name){
				$show = 0;
				$round = 0;
				$text = 0;

				//Here we check special settings of the shoppergroup
// 				$result = unserialize($result);
				if($custom_price_display==1){
					$show = (int)$result[0]->get($name);
					$round = (int)$result[0]->get($name.'Rounding');
					$text = $result[0]->get($name.'Text');
// 					vmdebug('$custom_price_display');
				} else {
					$show = VmConfig::get($name,0);
					$round = VmConfig::get($name.'Rounding',2);
					$text = VmConfig::get($name.'Text',0);
// 					vmdebug('$config_price_display');
				}


				$this->_priceConfig[$name] = array($show,$round,$text);
			}
		} else {
			foreach($priceFields as $name){
				$this->_priceConfig[$name] = array(0,0,0);
			}
		}

// 		vmdebug('$this->_priceConfig',$this->_priceConfig);
	}

	/**
	 * getCurrencyDisplay: get The actual displayed Currency
	 * Use this only in a view, plugin or modul, never in a model
	 *
	 * @param integer $currencyId
	 * return integer $currencyId: displayed Currency
	 *
	 */
	public function getCurrencyDisplay( $currencyId=0 ){

		if(empty($currencyId)){
			$currencyId = (int)$this->_app->getUserStateFromRequest( 'virtuemart_currency_id', 'virtuemart_currency_id',$this->_vendorCurrency );
			if(empty($currencyId)){
				$currencyId = $this->_vendorCurrency;
			}
		}

		return $currencyId;
	}

	/**
	 * This function is for the gui only!
	 * Use this only in a view, plugin or modul, never in a model
	 *
	 * @param float $price
	 * @param integer $currencyId
	 * return string formatted price
	 */
	public function priceDisplay($price=0, $currencyId=0,$inToShopCurrency = false,$nb = 2){
		// if($price ) Outcommented (Oscar) to allow 0 values to be formatted too (e.g. free shipment)
		/*
		 if(empty($currencyId)){
		$currencyId = (int)$this->_app->getUserStateFromRequest( 'virtuemart_currency_id', 'virtuemart_currency_id',$this->_vendorCurrency );
		if(empty($currencyId)){
		$currencyId = $this->_vendorCurrency;
		}
		}
		*/
		$currencyId = $this->getCurrencyDisplay($currencyId);
		$price = $this->convertCurrencyTo($currencyId,$price,$inToShopCurrency);
		return $this->getFormattedCurrency($price,$nb);
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
	public function createPriceDiv($name,$description,$product_price,$priceOnly=false,$switchSequel=false){

		if(empty($product_price)) return '';

		//This could be easily extended by product specific settings

		if(!empty($this->_priceConfig[$name][0])){
			if(!empty($product_price[$name])){
				$vis = "block";
				//if(!class_exists('CurrencyDisplay')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'currencydisplay.php');

				$product_price[$name] = $this->priceDisplay($product_price[$name],0,false,$this->_priceConfig[$name][1]);

			} else {
				$vis = "none";
			}
			if($priceOnly){
				return $product_price[$name];
			}
			$descr = '';
			if($this->_priceConfig[$name][2]) $descr = JText::_($description);
// 			vmdebug('createPriceDiv $name '.$name.' '.$product_price[$name]);
			if(!$switchSequel){
				return '<div class="Price'.$name.'" style="display : '.$vis.';" >'.$descr.'<span class="Price'.$name.'" >'.$product_price[$name].'</span></div>';
			} else {
				return '<div class="Price'.$name.'" style="display : '.$vis.';" ><span class="Price'.$name.'" >'.$product_price[$name].'</span>'.$descr.'</div>';
			}


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
// 			vmdebug('empty  $currency ',$price);
			return $price;
		}

		// If both currency codes match, do nothing
		if( $currency == $this->_vendorCurrency ) {
// 			vmdebug('  $currency == $this->_vendorCurrency ',$price);
			return $price;
		}

/*		if($shop){
			// TODO optimize this... the exchangeRate cant be cached, there are more than one currency possible
			//			$exchangeRate = &$this->exchangeRateVendor;
			$exchangeRate = 0;
		} else {
			//caches the exchangeRate between shopper and vendor
			$exchangeRate = &$this->exchangeRateShopper;
		}
*/
		if(empty($exchangeRate)){
			if(is_Object($currency)){
				$exchangeRate = $currency->_vendorCurrency;
				vmdebug('convertCurrencyTo OBJECT '.$exchangeRate);
			}
			else {
				//				$this->_db = JFactory::getDBO();
				$q = 'SELECT `currency_exchange_rate`
				FROM `#__virtuemart_currencies` WHERE `virtuemart_currency_id` ="'.(int)$currency.'" ';
				$this->_db->setQuery($q);
				$exch = $this->_db->loadResult();
// 				vmdebug('begin convertCurrencyTo '.$exch);
				if(!empty($exch) and $exch !== '0.00000'){
					$exchangeRate = $exch;
				} else {
					$exchangeRate = FALSE;
				}
			}
		}
		$this->exchangeRateShopper = $exchangeRate;
// 		vmdebug('convertCurrencyTo my currency ',$exchangeRate,$currency);
		if(!empty($exchangeRate) && $exchangeRate!=FALSE){
			$price = $price * $exchangeRate;
// 			vmdebug('!empty($exchangeRate) && $exchangeRate!=FALSE '.$price.' '.$exchangeRate);
		} else {
			$currencyCode = self::ensureUsingCurrencyCode($currency);
			$vendorCurrencyCode = self::ensureUsingCurrencyCode($this->_vendorCurrency);
			$globalCurrencyConverter=JRequest::getVar('globalCurrencyConverter');
			if($shop){
				$price = $this ->_currencyConverter->convert( $price, $currencyCode, $vendorCurrencyCode);
				if(!empty($globalCurrencyConverter[$currencyCode])){
					$this->exchangeRateShopper = $globalCurrencyConverter[$vendorCurrencyCode]/$globalCurrencyConverter[$currencyCode];
				} else {
					$this->exchangeRateShopper = 1;
				}
			} else {
				$price = $this ->_currencyConverter->convert( $price , $vendorCurrencyCode, $currencyCode);
				if(!empty($globalCurrencyConverter[$vendorCurrencyCode])){
					$this->exchangeRateShopper = $globalCurrencyConverter[$currencyCode]/$globalCurrencyConverter[$vendorCurrencyCode];
				} else {
					$this->exchangeRateShopper = 1;
				}
			}
// 			vmdebug('convertCurrencyTo my currency ',$this->exchangeRateShopper);
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

		if(is_numeric($curr) and $curr!=0){
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
	private function getFormattedCurrency( $nb, $nbDecimal=-1){

		if($nbDecimal===-1) $nbDecimal = $this->_nbDecimal;
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
