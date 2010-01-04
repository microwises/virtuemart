<?php
/**
 * Calculation helper class
 *
 * This class provides the functions for the calculatoins
 *
 * @package	VirtueMart
 * @subpackage Helpers
 * @author Max Milbers
 * @copyright Copyright (c) 2004-2008 Soeren Eberhardt-Biermann, 2009 VirtueMart Team. All rights reserved.
 */
 
 // Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class calculationHelper{
	
	private $_db;
	private $_shopperGroupId;
	private $_cats;
	private $_now ;
	private $_nullDate;
	
	public $productVendorId;
	public $productCurrency;

	public $basePrice;		//simular to costprice, basePrice is calculated in the shopcurrency
	public $salesPrice;		//end Price in the product currency
	public $discountedPrice;  //amount of effecting discount
	public $salesPriceCurrency;
	public $discount_info;
	
	function __construct(){
		$this->_db = &JFactory::getDBO();
	}
	
	/** function to start the calculation, here it is the product
	 * 
	 */
	function getProductPrices($product_id){

		$jnow		=& JFactory::getDate();
		$this -> _now			= $jnow->toMySQL();
		$this -> _nullDate		= $this->_db->getNullDate();
		
		$this->_db->setQuery( 'SELECT `product_price`,`product_currency` FROM #__vm_product_price  WHERE `product_id`="'.$product_id.'" ');

		$row=$this->_db->loadRow();
		$this->basePrice = $row[0];
		$this->productCurrency=$row[1];
		
		$this->_db->setQuery( 'SELECT `vendor_id` FROM #__vm_product  WHERE `product_id`="'.$product_id.'" ');
		$single = $this->_db->loadResult();
		$this->productVendorId = $single;

		$this->_db->setQuery( 'SELECT `vendor_currency` FROM #__vm_vendor  WHERE `vendor_id`="'.$this->productVendorId.'" ');
		$single = $this->_db->loadResult();
		$this->vendorCurrency = $single;
				
		$this->_db->setQuery( 'SELECT `category_id` FROM #__vm_product_category_xref  WHERE `product_id`="'.$product_id.'" ');
		$this->_cats=$this->_db->loadResultArray();

		$user = JFactory::getUser();
		if(isset($user->id)){
			$this->_db->setQuery( 'SELECT `shopper_group_id` FROM #__vm_shopper_vendor_xref  WHERE `user_id`="'.$my->id.'" ');
			$this->_shopperGroupId=$this->_db->loadResultArray();			
		}

//		echo 'Produkt Baseprice: '.$this->basePrice.'  and currency: '.$this->productCurrency.' `user_id`="'.$my->id.'"<br />';
		$this->basePrice = $this->convertCurrencyToShopDefault($this->basePrice, $this->productCurrency);
		$taxRules = $this->gatherEffectingRulesForProductPrice('Tax');
		$this->basePriceWithTax = $this -> executeCalculation($taxRules, $this->basePrice);

		$dBTaxRules= $this->gatherEffectingRulesForProductPrice('DBTax');		
		$unroundeddiscountedPrice = $this -> executeCalculation($dBTaxRules, $this -> roundInternal($this -> basePrice));	
		$this -> discountedPrice = $this->roundDisplay($unroundeddiscountedPrice);

		$unroundedSalesPrice = $this -> executeCalculation($taxRules, $this -> discountedPrice);	
		
		$dATaxRules = $this->gatherEffectingRulesForProductPrice('DATax');
		
		$unroundedSalesPrice = $this -> executeCalculation($dATaxRules, $unroundedSalesPrice);
		$this->salesPrice = $this->roundDisplay($unroundedSalesPrice);
		
		$this -> discount_info['amount'] = $this->roundDisplay($this->basePriceWithTax - $this->salesPrice);
	}
	
	function convertCurrencyToShopDefault($value,$currency){
		if(empty($currency)){
			return $value;
		}
//		if(!strcmp($this->vendorCurrency, $currency)){
			$value = $GLOBALS['CURRENCY']->convert( $value, $currency,$this->vendorCurrency);
//		}
		return $value;
	}

	function executeCalculation($rules, $salesPrice){
		if(empty($rules))return $salesPrice;
		$rulesEffSorted = $this -> record_sort($rules, 'ordering');
		if(isset($rulesEffSorted)){
			foreach($rulesEffSorted as $rule){
				$salesPrice = $this -> interpreteMathOp($rule['calc_value_mathop'],$rule['calc_value'],$rule['calc_currency'],$salesPrice);
				echo 'RulesEffecting '.$rule['calc_name'].' and value '.$rule['calc_value'].' currency '.$rule['calc_currency'].' and '.$salesPrice.'<br />';
			}
		}
		return $salesPrice;
	}
	
	
	
	/**
	 * Gatheres the rules which affects
	 * The entrypoint how it should behave. Valid values should be 
	 * 
	 * Profit (Commission is a profit rule that is shared, maybe we remove shared and make a new entrypoint called profit)
	 * DBTax (Discount for wares, coupons)
	 * Tax
	 * DATax (Discount on money)
	 * Duty
	 */
	function gatherEffectingRulesForProductPrice($entrypoint){

		$cats = $this -> writeRulePartEffectingQuery($this->_cats,'calc_categories');
		$shoppergrps = $this -> writeRulePartEffectingQuery($this->_shopperGroupId,'calc_shopper');
		$countries = $this -> writeRulePartEffectingQuery($this->_countries,'calc_country');
		$states = $this -> writeRulePartEffectingQuery($this->_states,'calc_state');

		//Test if calculation affects the current entry point
		//shared rules counting for every vendor seems to be not necessary
		$q= 'SELECT * FROM #__vm_calc WHERE ' .
		'`calc_kind`="'.$entrypoint.'" ' .
		' AND `published`="1" ' .
		' AND (`calc_vendor_id`="'.$this->productVendorId.'" OR `shared`="1" )'.
		' AND ( publish_up = '.$this->_db->Quote($this ->_nullDate).' OR publish_up <= '.$this->_db->Quote($this ->_now).' )' .
		' AND ( publish_down = '.$this->_db->Quote($this ->_nullDate).' OR publish_down >= '.$this->_db->Quote($this ->_now).' )'.
		$cats . $shoppergrps . $countries . $states ;
		$this->_db->setQuery($q);
		$rules = $this->_db->loadAssocList();

		foreach($rules as $rule){
			echo '<br /> Add rule '.$rule['calc_name'];
		}
		return $rules;
	}

	function writeRulePartEffectingQuery($data,$field){
		$q='';
		if(!empty($data)){
			$q = ' AND ( ';
			foreach ($data as $id){
				$q = $q . '`'.$field.'`="'.$id.'" OR';
			}
			$q = $q . '`'.$field.'`="0" )';
		}
		return $q;
	}
	
	/**
	 * This functions interprets the String that is entered in the calc_value_mathop field
	 * The first char is the signum of the function. The more this function can be enhanced
	 * maybe with function that works like operators, the easier it will be to make more complex disount/commission/profit formulas
	 * progressive, nonprogressive and so on.
	 * 
	 */
	function interpreteMathOp($mathop,$value,$currency, $salesPrice){

		$sign = substr($mathop,0,1);
		if(!strcmp($sign,'+')){
			if(strlen($mathop)>1){
				$second = substr($mathop,1,2);
				if(strcmp($sign,"%")){
					return $salesPrice * (1+$value/100.0);
				}
			} else {

				$value = $this->convertCurrencyToShopDefault($value, $currency);
				return $salesPrice + $value ;
			}
			
		}else if(!strcmp($sign,'-')){
			if(strlen($mathop)>1){
				$second = substr($mathop,1,2);
				if(strcmp($sign,"%")){
					return $salesPrice * (1-$value/100.0);
				}
			} else {
				$value = $this->convertCurrencyToShopDefault($value, $currency);
				return $salesPrice - $value ;
			}			
		}
	
	}


	/**
	 * Standard round function, we round every number with 6 fractionnumbers
	 * We need at least 4 to calculate something like 9.25% => 0.0925
	 * 2 digits
	 * Should be setable via config (just for the crazy case)
	 */
	function roundInternal($value){	
		return round($value,6);
	}

	/**
	 * Round function for display with 6 fractionnumbers. 
	 * For more information please read http://en.wikipedia.org/wiki/Propagation_of_uncertainty 
	 * and http://www.php.net/manual/en/language.types.float.php
	 * So in case of € or $ it is rounded in cents
	 * Should be setable via config
	 */
	function roundDisplay($value){
		return round($value,2);
	}
	
	/** Sorts indexed 2D array by a specified sub array key
	 * 
	 * Copyright richard at happymango dot me dot uk
	 * 
	 */
	 
	function record_sort($records, $field, $reverse=false){	
		if(is_array($records)){
			$hash = array();
	   
		    foreach($records as $record){
		        $hash[$record[$field]] = $record;
		    }
		    ($reverse)? krsort($hash) : ksort($hash);
		    $records = array();
		    foreach($hash as $record){
		        $records []= $record;
		    }
		}
	    return $records;
	}
	
}