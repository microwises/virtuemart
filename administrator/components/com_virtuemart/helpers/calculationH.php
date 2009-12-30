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
	
	private $_rules;
	private $_rulesEff;
	private $_db;
	private $_shopperGroupId;
	private $_cats;
	
	public $productVendorId;
	public $productCurrency;

	public $basePrice;		//simular to costprice
	public $salesPrice;
	public $discountedPrice;
	public $salesPriceCurrency;
	public $discount_info;
	
	function __construct(){
		$this->_db = &JFactory::getDBO();
	}
	
	/** function to start the calculation, here it is the product
	 * 
	 */
	function getProductPrices($product_id){

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
//		echo 'Produkt Baseprice: '.$this->basePrice.'  and currency: '.$this->vendorCurrency.'<br />';
		$this->gatherEffectingRulesForProductPrice('DBTax');
		
//		if($this -> _rulesEff){
//			$discount_info['amount'] = $value;
//		}
		
		$unroundeddiscountedPrice = $this -> executeCalculation($this -> roundInternal($this -> basePrice));
		$this -> discountedPrice = $this->roundDisplay($unroundeddiscountedPrice);
		$this -> discount_info['amount'] = $this->roundDisplay($this->basePrice - $this->discountedPrice);
		
		$this->gatherEffectingRulesForProductPrice('Tax');
		
		$unroundedSalesPrice = $this -> executeCalculation($unroundeddiscountedPrice);
		
//		$this->salesPriceCurrency = $this->roundDisplay($unroundedSalesPrice);		
		
		$this->gatherEffectingRulesForProductPrice('DATax');
		
		$unroundedSalesPrice = $this -> executeCalculation($unroundedSalesPrice);
		$this->salesPrice = $this->roundDisplay($unroundedSalesPrice);
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

	function executeCalculation($salesPrice){
//		echo 'Exe: '.$salesPrice;
		$rulesEffSorted = $this -> record_sort($this -> _rulesEff, 'ordering');
		if(isset($rulesEffSorted)){
			foreach($rulesEffSorted as $rule){
				$salesPrice = $this -> interpreteMathOp($rule['calc_value_mathop'],$rule['calc_value'],$rule['calc_currency'],$salesPrice);
				echo 'RulesEffecting '.$rule['calc_name'].' and value '.$rule['calc_value'].' currency '.$rule['calc_currency'].' and '.$salesPrice.'<br />';
			}
		}
		unset($this -> _rulesEff);
//		echo '<br />return: '.$salesPrice;
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

		//Test if calculation affects the current entry point
		//shared rules counting for every vendor seems to be not necessary
$this->_db->setQuery( 'SELECT * FROM #__vm_calc WHERE (`calc_kind`="'.$entrypoint.'" AND `published`="1" AND (`calc_vendor_id`="'.$this->productVendorId.'" OR `shared`="1" ))'); // )');
//$this->_db->setQuery( 'SELECT * FROM #__vm_calc WHERE (`calc_kind`="'.$entrypoint.'" AND `published`="1" AND `calc_vendor_id`="'.$this->productVendorId.'")'); // )');
		$this -> _rules = $this->_db->loadAssocList();

		foreach($this -> _rules as $rule){

			//Categories normal Tax or Discount
			$addCalc = $this->testRulePartEffecting($rule,$this->_cats,'calc_categories');
			
			//Discount
			$addDisc = $this->testRuleTimeEffecting();
			
			//Shoppergroups
			$addShopper=true;
			$addShopper = $this->testRulePartEffecting($rule,$this->_shopperGroupId,'calc_shopper');
			//Just add whatever condition here
			
			//Country
			//Todo for duty, get address of loggedUser.
			//$this->testRulePartEffecting($rule,'calc_country');
			//$this->testRulePartEffecting($rule,'calc_state');
			
			if($addCalc && $addDisc && $addShopper){
//				echo '<br /> Add rule '.$rule['calc_name'];
				$this->_rulesEff[] = $rule;
			}
		}
//		echo '<br />';
	}

	/*
	 * Can test the tablefields Category, Country, State
	 *  If the the data is 0 false is returned
	 */
	 
	function testRulePartEffecting($rule,$data,$field){
		if(isset($rule[$field])){
			if(is_array($rule[$field])){
				$intersect = array_intersect($rule[$field],$data);
				if($intersect){
					return true;
				}else{
					return false;
				}
			}else{
				return true;
			}
		}else{
			return true;
		}
		
	}
	
	//Test for time window
	function testRuleTimeEffecting(){
		
		return true;
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