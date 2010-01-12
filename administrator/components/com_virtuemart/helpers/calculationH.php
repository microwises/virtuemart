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
	private $_currency;
	
	public $productVendorId;
	public $productCurrency;

//	public $basePrice;		//simular to costprice, basePrice is calculated in the shopcurrency
//	public $salesPrice;		//end Price in the product currency
//	public $discountedPrice;  //amount of effecting discount
//	public $salesPriceCurrency;
//	public $discountAmount;
	
	/** Constructor,... sets the actual date and current currency
	 * 
	 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
	 * @author Max Milbers
	 */
	function __construct(){
		$this->_db = &JFactory::getDBO();
		$jnow		=& JFactory::getDate();
		$this -> _now			= $jnow->toMySQL();
		$this -> _nullDate		= $this->_db->getNullDate();
		$this -> _currency 		= JRequest::getVar('currency');
	}
	
	/** function to start the calculation, here it is for the product
	 * 
	 * The function first gathers the information of the product (maybe better done with using the model)
	 * After that the function gatherEffectingRulesForProductPrice writes the queries and gets the ids of the rules which affect the product
	 * The function executeCalculation makes the actual calculation according to the rules 
	 * 
	 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
	 * @author Max Milbers
	 * @param int $productId 	The Id of the product
	 * @param int $catIds 		When the category is already determined, then it makes sense to pass it, if not the function does it for you
	 * @return int $prices		An array of the prices
	 * 							'basePrice'  		basePrice calculated in the shopcurrency
	 * 							'basePriceWithTax'	basePrice with Tax
	 * 							'discountedPrice'	before Tax
	 * 							'priceWithoutTax'	price Without Tax but with calculated discounts AFTER Tax. So it just shows how much the shopper saves, regardless which kind of tax
	 * 							'discountAmount'	the "you save X money"
	 * 							'salesPrice'		The final price, with all kind of discounts and Tax, except stuff that is only in the checkout
	 * 
	 */
	function getProductPrices($productId,$catIds=0){
		
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
				
		if(empty($catIds)){
			$this->_db->setQuery( 'SELECT `category_id` FROM #__vm_product_category_xref  WHERE `product_id`="'.$productId.'" ');
			$this->_cats=$this->_db->loadResultArray();
		}else{
			$this->_cats=$catIds;
		}

		$user = JFactory::getUser();
		if(isset($user->id)){
			$this->_db->setQuery( 'SELECT `shopper_group_id` FROM #__vm_shopper_vendor_xref  WHERE `user_id`="'.$my->id.'" ');
			$this->_shopperGroupId=$this->_db->loadResultArray();			
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
				'basePrice'  => $basePriceShopCurrency,	//basePrice calculated in the shopcurrency
				'basePriceWithTax' => $basePriceWithTax, //basePrice with Tax
				'discountedPrice'   => $discountedPrice, //before Tax
				'priceWithoutTax'   => $priceWithoutTax, //price Without Tax but with calculated discounts AFTER Tax. So it just shows how much the shopper saves, regardless which kind of tax
				'discountAmount'   => $discountAmount, //The "you save X money"
				'salesPrice'   => $salesPrice 		//The endprice, with all kind of discounts and Tax
				);
		return $prices;
	}
	
	/** function to start the calculation, here it is for the invoice in the checkout
	 * This function is partly implemented !
	 * 
	 * The function calls getProductPrices for every product except it is already known (maybe changed and adjusted with product amount value
	 * The single prices gets added in an array and already summed up.
	 * 
	 * Then simular to getProductPrices first the effecting rules are determined and calculated.
	 * Ah function to determine the coupon that effects the calculation is already implemented. But not completly in the calculation.
	 * 
	 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
	 * @author Max Milbers
	 * @param int $productIds 	The Ids of the products
	 * @param int $cartVendorId The Owner of the cart, this can be ignored in vm1.5
	 * @return int $prices		An array of the prices
	 * 							'resultWithOutTax'	The summed up baseprice of all products
	 * 							'resultWithTax'  	The final price of all products with their tax, discount and so on
	 * 							'discountBeforeTax'	discounted price without tax which affects only the checkout (the tax of the products is in it)
	 * 							'discountWithTax'	discounted price taxed
	 * 							'discountAfterTax'	final result
	 * 
	 */
	function getCheckoutPrices($productIds,$cartVendorId=1){
		$pricesPerId = array();
		$prices = array();
		$resultWithTax=0.0;
		foreach ($productIds as $productId){
//			echo '$productId '.$productId;
			if (!array_key_exists($productId,$pricesPerId)){
				$pricesPerId[$productId] = $this -> getProductPrices($productId);
//				echo '$productId Calculated '.$productId;
			}
			$prices[] = $pricesPerId[$productId];
			$resultWithTax = $resultWithTax + $pricesPerId[$productId]['salesPrice'];
			$resultWithOutTax = $resultWithOutTax + $pricesPerId[$productId]['basePrice'];
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
		$cBRules = $this->gatherEffectingRulesForCoupon();
		$taxRules  = $this->gatherEffectingRulesForBill('TaxBill');
		$dATaxRules= $this->gatherEffectingRulesForBill('DATaxBill');
		$cBRules = $this->gatherEffectingRulesForCoupon();
		
		$discountBeforeTax = $this->roundDisplay($this -> executeCalculation($dBTaxRules, $resultWithTax));
		$discountWithTax = $this->roundDisplay($this -> executeCalculation($taxRules, $discountBeforeTax));
		$discountAfterTax = $this->roundDisplay($this -> executeCalculation($dATaxRules, $discountWithTax));
		echo '$resultWithOutTax: '.$resultWithOutTax.'<br />';
		echo '$basePrice: '.$resultWithTax.'<br />';
		echo '$discountBeforeTax: '.$discountBeforeTax.'<br />';
		echo '$discountWithTax: '.$discountWithTax.'<br />';
		echo '$discountAfterTax: '.$discountAfterTax.'<br />';
	}

	/**
	 * Function to execute the calculation of the gathered rules Ids.
	 * 
	 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
	 * @author Max Milbers
	 * @param 		$rules 		The Ids of the products
	 * @param 		$price 		The input price, if no rule is affecting the price just gets returned
	 * @return int 	$price  	the endprice	
	 */
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
	 * Gatheres the rules which affects the product.
	 * 
	 * 
	 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
	 * @author Max Milbers
	 * @param	$entrypoint The entrypoint how it should behave. Valid values should be 
	 * 						Profit (Commission is a profit rule that is shared, maybe we remove shared and make a new entrypoint called profit)
	 * 						DBTax (Discount for wares, coupons)
	 * 						Tax
	 * 						DATax (Discount on money)
	 * 						Duty
	 * @return	$rules The rules that effects the product as Ids
	 */ 
	function gatherEffectingRulesForProductPrice($entrypoint){

		$cats = $this -> writeRulePartEffectingQuery($this->_cats,'calc_categories',true);
		$shoppergrps = $this -> writeRulePartEffectingQuery($this->_shopperGroupId,'calc_shopper',true);
		$countries = ''; //$this -> writeRulePartEffectingQuery($this->_countries,'calc_country',true);
		$states = ''; // $this -> writeRulePartEffectingQuery($this->_states,'calc_state',true);

		//Test if calculation affects the current entry point
		//shared rules counting for every vendor seems to be not necessary
		$q= 'SELECT * FROM #__vm_calc WHERE ' .
		'`calc_kind`="'.$entrypoint.'" ' .
		' AND `published`="1" ' .
		' AND (`calc_vendor_id`="'.$this->productVendorId.'" OR `shared`="1" )'.
		' AND ( publish_up = '.$this->_db->Quote($this ->_nullDate).' OR publish_up <= '.$this->_db->Quote($this ->_now).' )' .
		' AND ( publish_down = '.$this->_db->Quote($this ->_nullDate).' OR publish_down >= '.$this->_db->Quote($this ->_now).' ) '.
		$cats . $shoppergrps . $countries . $states ;
		$this->_db->setQuery($q);
		$rules = $this->_db->loadAssocList();
		JError::raiseNOtice(1, 'query '.$q);
		if (empty($rules)) return;
		//Just for developing
//		foreach($rules as $rule){
//			echo '<br /> Entrypoint: '.$entrypoint.' Add rule '.$rule['calc_name'];
//		}
		return $rules;
	}
	
	/**
	 * Gathers the effecting rules for the calculation of the bill
	 * 
	 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
	 * @author Max Milbers
	 * @param	$entrypoint
	 * @param	$cartVendorId
	 * @return $rules The rules that effects the Bill as Ids
	 */
	function gatherEffectingRulesForBill($entrypoint, $cartVendorId=1){

//		$cats = $this -> writeRulePartEffectingQuery($this->_cats,'calc_categories',true);
		$shoppergrps = $this -> writeRulePartEffectingQuery($this->_shopperGroupId,'calc_shopper',true);
		$countries = $this -> writeRulePartEffectingQuery($this->_countries,'calc_country',true);
		$states = $this -> writeRulePartEffectingQuery($this->_states,'calc_state',true);

		//Test if calculation affects the current entry point
		//shared rules counting for every vendor seems to be not necessary
		$q= 'SELECT * FROM #__vm_calc WHERE ' .
			'`calc_kind`="'.$entrypoint.'" ' .
			' AND `published`="1" ' .
			' AND (`calc_vendor_id`="'.$cartVendorId.'" OR `shared`="1" )'.
			' AND ( publish_up = '.$this->_db->Quote($this ->_nullDate).' OR publish_up <= '.$this->_db->Quote($this ->_now).' )' .
			' AND ( publish_down = '.$this->_db->Quote($this ->_nullDate).' OR publish_down >= '.$this->_db->Quote($this ->_now).' ) '.
			$shoppergrps .  $countries . $states ;
		$this->_db->setQuery($q);
		$rules = $this->_db->loadAssocList();
//		echo ' query: '.$q;

//		if (empty($rules)) return;	
		//Just for developing
//		foreach($rules as $rule){
//			echo '<br /> Add rule '.$rule['calc_name'].' query: '.$q;
//		}
		return $rules;
	}
	
	/**
	 * Gathers the effecting coupons for the calculation
	 * 
	 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
	 * @author Max Milbers
	 * @param 	$code 	The Id of the coupon
	 * @return 	$rules 	ids of the coupons
	 */
	function gatherEffectingRulesForCoupon($code=0){
		if (empty($code)) return;
		$couponCodesQuery = $this -> writeRulePartEffectingQuery($code,'coupon_code');
		$q= 'SELECT * FROM #__vm_coupons WHERE ' .
			$couponCodesQuery .
			' AND ( coupon_start_date = '.$this->_db->Quote($this ->_nullDate).' OR coupon_start_date <= '.$this->_db->Quote($this ->_now).' )' .
			' AND ( coupon_expiry_date = '.$this->_db->Quote($this ->_nullDate).' OR coupon_expiry_date >= '.$this->_db->Quote($this ->_now).' )';
		$this->_db->setQuery($q);
		$rules = $this->_db->loadAssocList();
		return $rules;
	}
	
	/**
	 * This function just writes the query for gatherEffectingRulesForProductPrice
	 * When a condition is not set, it is handled like a set condition that affects it. So the users have only to add a value 
	 * for the conditions they want to (You dont need to enter a start or end date when the rule should count everytime).
	 * 
	 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
	 * @author Max Milbers
	 * @param $data		the ids of the rule, for exampel the ids of the categories that affect the rule
	 * @param $field	the name of the field in the db, for exampel calc_categories to write a rule that asks for the field calc_categories
	 * @return $q		The query
	 */
	function writeRulePartEffectingQuery($data,$field,$setAnd=0){
		$q='';
		if(!empty($data)){
			if($setAnd){
				$q = ' AND (';		
			}else{
				$q = ' (';
			}
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
	 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
	 * @author Max Milbers
	 * @param 	$mathop 	String reprasentation of the mathematical operation
	 * @param	$value 		The value that affects the price
	 * @param 	$currency	the currency which should be used
	 * @param	$price		The price to calculate
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


	function convertCurrencyToShopDefault($currency, $price){
		if(empty($currency)){
			return $price;
		}
		if(empty($this ->_currency)){
			$this -> _currency 		= JRequest::getVar('currency');
		}
//		if(!strcmp($this->vendorCurrency, $currency)){
			$price = $this ->_currency->convert( $price, $currency,$this->vendorCurrency);
//		}
		return $price;
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
	 * @author Max Milbers
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