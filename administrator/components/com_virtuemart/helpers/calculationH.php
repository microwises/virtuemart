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

//	public $basePrice;		//simular to costprice, basePrice is calculated in the shopcurrency
//	public $salesPrice;		//end Price in the product currency
//	public $discountedPrice;  //amount of effecting discount
//	public $salesPriceCurrency;
//	public $discountAmount;
	
	function __construct(){
		$this->_db = &JFactory::getDBO();
		$jnow		=& JFactory::getDate();
		$this -> _now			= $jnow->toMySQL();
		$this -> _nullDate		= $this->_db->getNullDate();
	}
	
	/**
	* Calcualte the checkout price
	*
	* @param array $productIds An array or product IDs to calculate the checkout price for
	*/
	public function getCheckoutPrices($productIds) {
		$pricesPerId = array();
		$prices = array();
		$resultWithTax=0.0;
		foreach ($productIds as $productId){
//			echo '$productId '.$productId;
			if (!array_key_exists($productId,$pricesPerId)){
				$pricesPerId[$productId] = $this -> getProductPrices($productId);
				echo '$productId Calculated '.$productId;
			}
			$prices[] = $pricesPerId[$productId];
			$resultWithTax = $resultWithTax + $pricesPerId[$productId]['salesPrice'];
		}
//		echo print_r($prices);
		echo '<br />';
		for ($x = 0; $x < sizeof($prices); ++$x){
			echo "key: ".key($prices)."  value: ".current($prices)."<br />";
			$steps = current($prices);
			for ($y = 0; $y < sizeof($steps); ++$y){
				echo "   key: ".key($steps)."  value: ".current($steps)."<br />";
				next($steps);
			}
			next($prices);
		}
		echo '<br />';
		$dBTaxRules= $this->gatherEffectingRulesForBill('DBTaxBill');
		$taxRules  = $this->gatherEffectingRulesForBill('TaxBill');
		$dATaxRules= $this->gatherEffectingRulesForBill('DATaxBill');
		
		$discountBeforeTax = $this->roundDisplay($this -> executeCalculation($dBTaxRules, $resultWithTax));
		$discountWithTax = $this->roundDisplay($this -> executeCalculation($taxRules, $discountBeforeTax));
		$discountAfterTax = $this->roundDisplay($this -> executeCalculation($dATaxRules, $discountWithTax));
		echo '$discountBeforeTax: '.$discountBeforeTax.'<br />';
		echo '$discountWithTax: '.$discountWithTax.'<br />';
		echo '$discountAfterTax: '.$discountAfterTax.'<br />';
	}
	
	/** 
	* function to start the calculation, here it is the product
	* 
	* @param integer The ID of the product to get the price for
	* @return array containing the different price details
	*/
	public function getProductPrices($productId){

		$this->_db->setQuery( 'SELECT `product_price`,`product_currency` FROM #__vm_product_price  WHERE `product_id`="'.$productId.'" ');

		$row=$this->_db->loadRow();
		$basePrice = $row[0];
		$this->productCurrency=$row[1];
		
		$this->_db->setQuery( 'SELECT `vendor_id` FROM #__vm_product  WHERE `product_id`="'.$productId.'" ');
		$single = $this->_db->loadResult();
		$this->productVendorId = $single;

		$this->_db->setQuery( 'SELECT `vendor_currency` FROM #__vm_vendor  WHERE `vendor_id`="'.$this->productVendorId.'" ');
		$single = $this->_db->loadResult();
		$this->vendorCurrency = $single;
				
		$this->_db->setQuery( 'SELECT `category_id` FROM #__vm_product_category_xref  WHERE `product_id`="'.$productId.'" ');
		$this->_cats=$this->_db->loadResultArray();

		$user = JFactory::getUser();
		if (isset($user->id)){
			$this->_db->setQuery( 'SELECT `shopper_group_id` FROM #__vm_shopper_vendor_xref  WHERE `user_id` = "'.$user->id.'" ');
			$this->_shopperGroupId = $this->_db->loadResultArray();			
		}
		$dBTaxRules= $this->gatherEffectingRulesForProductPrice('DBTax');
		$taxRules = $this->gatherEffectingRulesForProductPrice('Tax');
		$dATaxRules = $this->gatherEffectingRulesForProductPrice('DATax');
		
		$basePriceShopCurrency = $this->convertCurrencyToShopDefault($this->productCurrency, $basePrice);		
		$basePriceWithTax = $this->roundDisplay($this -> executeCalculation($taxRules, $basePriceShopCurrency));
			
		$unroundeddiscountedPrice = $this -> executeCalculation($dBTaxRules, $this -> roundInternal($basePriceShopCurrency));	
		$discountedPrice = $this->roundDisplay($unroundeddiscountedPrice);

		$unroundedSalesPrice = $this -> executeCalculation($taxRules, $discountedPrice);	
		$unroundedSalesPrice = $this -> executeCalculation($dATaxRules, $unroundedSalesPrice);
		$salesPrice = $this->roundDisplay($unroundedSalesPrice);

		$discountAmount = $this->roundDisplay($basePriceWithTax - $salesPrice);
		$priceWithoutTax = $this->roundDisplay($basePrice + ($salesPrice - $discountedPrice));
		
		$prices = array(
				'basePrice'  => $basePriceShopCurrency,
				'basePriceWithTax' => $basePriceWithTax,
				'discountedPrice'   => $discountedPrice,
				'priceWithoutTax'   => $priceWithoutTax,
				'discountAmount'   => $discountAmount,
				'salesPrice'   => $salesPrice
				);
		return $prices;
	}
	
	/**
	* Converts a price to the vendor currency
	*
	* @todo make it work :)
	* @param string $currency The name of the target currency
	* @param float $price The value to be converted
	* @return float The converted value
	*/
	public function convertCurrencyToShopDefault($currency, $price){
		return $price;
		if(empty($currency)){
			return $price;
		}
//		if(!strcmp($this->vendorCurrency, $currency)){
			$price = $GLOBALS['CURRENCY']->convert( $price, $currency,$this->vendorCurrency);
//		}
		return $price;
	}

	function executeCalculation($rules, $price){
		if(empty($rules))return $price;
		$rulesEffSorted = $this -> record_sort($rules, 'ordering');
		if(isset($rulesEffSorted)){
			foreach($rulesEffSorted as $rule){
				$price = $this -> interpreteMathOp($rule['calc_value_mathop'],$rule['calc_value'],$rule['calc_currency'],$price);
//				echo 'RulesEffecting '.$rule['calc_name'].' and value '.$rule['calc_value'].' currency '.$rule['calc_currency'].' and '.$price.'<br />';
			}
		}
		return $price;
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
	function gatherEffectingRulesForBill($entrypoint){

//		$cats = $this -> writeRulePartEffectingQuery($this->_cats,'calc_categories');
		$shoppergrps = $this -> writeRulePartEffectingQuery($this->_shopperGroupId,'calc_shopper');
		$countries = $this -> writeRulePartEffectingQuery($this->_countries,'calc_country');
		$states = $this -> writeRulePartEffectingQuery($this->_states,'calc_state');

		//Test if calculation affects the current entry point
		//shared rules counting for every vendor seems to be not necessary
		$q= 'SELECT * FROM #__vm_calc WHERE ' .
		'`calc_kind`="'.$entrypoint.'" ' .
		' AND `published`="1" ' .
		' AND (`calc_vendor_id`="'.$this->cartVendorId.'" OR `shared`="1" )'.
		' AND ( publish_up = '.$this->_db->Quote($this ->_nullDate).' OR publish_up <= '.$this->_db->Quote($this ->_now).' )' .
		' AND ( publish_down = '.$this->_db->Quote($this ->_nullDate).' OR publish_down >= '.$this->_db->Quote($this ->_now).' )'.
		$shoppergrps . $countries . $states ;
		$this->_db->setQuery($q);
		$rules = $this->_db->loadAssocList();

		//Just for developing
//		foreach($rules as $rule){
//			echo '<br /> Add rule '.$rule['calc_name'];
//		}
		return $rules;
	}

	function gatherEffectingRulesForProductPrice($entrypoint){

		$cats = $this -> writeRulePartEffectingQuery($this->_cats,'calc_categories');
		$shoppergrps = $this -> writeRulePartEffectingQuery($this->_shopperGroupId,'calc_shopper');
		/** @todo make this work for products */
		//$countries = $this -> writeRulePartEffectingQuery($this->_countries,'calc_country');
		//$states = $this -> writeRulePartEffectingQuery($this->_states,'calc_state');

		//Test if calculation affects the current entry point
		//shared rules counting for every vendor seems to be not necessary
		$q= 'SELECT * FROM #__vm_calc WHERE ' .
		'`calc_kind`="'.$entrypoint.'" ' .
		' AND `published`="1" ' .
		' AND (`calc_vendor_id`="'.$this->productVendorId.'" OR `shared`="1" )'.
		' AND ( publish_up = '.$this->_db->Quote($this ->_nullDate).' OR publish_up <= '.$this->_db->Quote($this ->_now).' )' .
		' AND ( publish_down = '.$this->_db->Quote($this ->_nullDate).' OR publish_down >= '.$this->_db->Quote($this ->_now).' )'.
		// $cats . $shoppergrps . $countries . $states ;
		$cats . $shoppergrps ;
		$this->_db->setQuery($q);
		$rules = $this->_db->loadAssocList();

		//Just for developing
		foreach($rules as $rule){
//			echo '<br /> Add rule '.$rule['calc_name'];
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
	function interpreteMathOp($mathop,$value,$currency, $price){

		$sign = substr($mathop,0,1);
		if(!strcmp($sign,'+')){
			if(strlen($mathop)>1){
				$second = substr($mathop,1,2);
				if(strcmp($sign,"%")){
					return $price * (1+$value/100.0);
				}
			} else {

				$value = $this->convertCurrencyToShopDefault($currency, $value);
				return $price + $value ;
			}
			
		}else if(!strcmp($sign,'-')){
			if(strlen($mathop)>1){
				$second = substr($mathop,1,2);
				if(strcmp($sign,"%")){
					return $price * (1-$value/100.0);
				}
			} else {
				$value = $this->convertCurrencyToShopDefault($currency, $value);
				return $price - $value ;
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