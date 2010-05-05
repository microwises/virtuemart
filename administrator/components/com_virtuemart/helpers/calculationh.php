<?php
/**
 * Calculation helper class
 *
 * This class provides the functions for the calculatoins
 *
 * @package	VirtueMart
 * @subpackage Helpers
 * @author Max Milbers
 * @copyright Copyright (c) 2010 Soeren Eberhardt-Biermann, Max Milbers 2009 VirtueMart Team. All rights reserved.
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
	private $_debug;
	private $_amount;
	private $_deliveryCountry;
	private $_deliveryState;
	private $_currencyDisplay;
	
	public $productVendorId;
	public $productCurrency;

	static $_instance;

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
		$this -> _now			  = $jnow->toMySQL();
		$this -> _nullDate		  = $this->_db->getNullDate();
		$this -> _currency 		  = JRequest::getVar('currency');
		$this -> _currencyDisplay = JRequest::getVar('currencyDisplay');
		$this -> _debug           = false;
	}
	
	function getInstance(){ 
		if(!is_object(self::$_instance)){
			self::$_instance = new calculationHelper();
		}else {
			
		}
 		return self::$_instance;
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
	function getProductPrices($productId,$catIds=0,$variant=0.0,$amount=0,$ignoreAmount=true){
		
//		Console::logSpeed('getProductPrices START: ');
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

		if(empty($this->_shopperGroupId)){
			$user = JFactory::getUser();
			if(!empty($user->id)){
				$this->_db->setQuery( 'SELECT `shopper_group_id` FROM #__vm_shopper_vendor_xref  WHERE `user_id`="'.$user->id.'" AND `vendor_id`="'.$this->productVendorId.'"');
				$this->_shopperGroupId=$this->_db->loadResult();
			} 
			if(empty($this->_shopperGroupId)){
				$this->_db->setQuery( 'SELECT `shopper_group_id` FROM #__vm_shopper_group  WHERE `default`="1" AND `vendor_id`="'.$this->productVendorId.'"');				
				$this->_shopperGroupId = $this->_db->loadResult();
			}
		}

		if(!empty($amount)){
			$this->_amount;
		}
		
//		$calcRules = $this->gatherEffectingRulesForProductPrice('Calc');
		$dBTaxRules= $this->gatherEffectingRulesForProductPrice('DBTax');
		$taxRules = $this->gatherEffectingRulesForProductPrice('Tax');
		$dATaxRules = $this->gatherEffectingRulesForProductPrice('DATax');


		$basePriceShopCurrency = $this->roundDisplay($this->convertCurrencyToShopDefault($this->productCurrency, $basePrice));
		$prices['basePrice']=$basePriceShopCurrency;
		

		
		if(isset($variant)){
			if (strpos($variant, '=') !== false) {
	//		   $variant=substr($variant,1);
			   $basePriceShopCurrency = doubleval(substr($variant,1));
			} else {
				$basePriceShopCurrency = $basePriceShopCurrency + doubleval($variant);
			}
			$prices['basePrice'] = $prices['basePriceVariant'] = $basePriceShopCurrency;
			
		}
		
		//For Profit, margin, and so on
//		if(count($calcRules)!==0){
//			
//			$prices['profit'] = 
//		}

//		$basePrice = !empty($prices['basePriceVariant'])?$prices['basePriceVariant']:$prices['basePrice'];
		$prices['basePriceWithTax'] = $this->roundDisplay($this -> executeCalculation($taxRules, $prices['basePrice']));
		$prices['discountedPriceWithoutTax']=$this->roundDisplay($this -> executeCalculation($dBTaxRules, $prices['basePrice']));
		
		$priceBeforeTax = !empty($prices['discountedPriceWithoutTax'])?$prices['discountedPriceWithoutTax']:$prices['basePrice'];
		$prices['priceBeforeTax']=$priceBeforeTax;
		$prices['salesPrice'] = $this->roundDisplay($this -> executeCalculation($taxRules, $priceBeforeTax));
		
		$salesPrice = !empty($prices['salesPrice'])?$prices['salesPrice']:$priceBeforeTax;
		$prices['salesPriceTemp']=$salesPrice;
		$prices['taxAmount'] = $this->roundDisplay($salesPrice-$priceBeforeTax);
		
		$prices['salesPriceWithDiscount'] = $this->roundDisplay($this -> executeCalculation($dATaxRules, $salesPrice));
		
		$prices['salesPrice'] = !empty($prices['salesPriceWithDiscount'])?$prices['salesPriceWithDiscount']:$salesPrice;
		

		//The whole discount Amount
//		$prices['discountAmount'] = $this->roundDisplay($prices['basePrice'] + $prices['taxAmount'] - $prices['salesPrice']);
		$basePriceWithTax = !empty($prices['basePriceWithTax'])?$prices['basePriceWithTax']:$prices['basePrice'];
		$prices['discountAmount'] = $this->roundDisplay($basePriceWithTax - $salesPrice);
		
		//price Without Tax but with calculated discounts AFTER Tax. So it just shows how much the shopper saves, regardless which kind of tax
//		$prices['priceWithoutTax'] = $this->roundDisplay($salesPrice - ($salesPrice - $discountedPrice));	
		$prices['priceWithoutTax'] = $salesPrice - $prices['taxAmount'];
		
		//As last step the prices gets adjusted to the user choosen currency
		foreach($prices as $price){
			$price = $this -> _currencyDisplay->getFullValue($price);
		}
	
		$prices['variantModification']=$variant;
	
//		echo '<br />The prices:<br />';
//		echo '<pre>'.print_r($prices).'</pre>';

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
//	function getCheckoutPrices($productIds,$variantMods=array(), $cartVendorId=1,$couponId=0,$shipId=0,$paymId=0){
	function getCheckoutPrices($cart){

//		echo '<br />cart: <pre>'.print_r($cart).'</pre><br />';
		$pricesPerId = array();
		$prices = array();
		$resultWithTax=0.0;
		$resultWithOutTax=0.0;
		$productIdsCount = $cart['idx'];

		$prices['basePrice']= 0;
		$prices['basePriceWithTax']= 0;
		$prices['discountedPriceWithoutTax']= 0;
		$prices['salesPrice']= 0;
		$prices['taxAmount']= 0;
		$prices['salesPriceWithDiscount']= 0;
		$prices['discountAmount']= 0;
		$prices['priceWithoutTax']= 0;
		$prices['coupons'] = $this->existCoupons();
		$prices['couponValue'] = 0;
		$prices['duty'] = 1;
		$prices['shipping'] = 0; //could be automatically set to a default set in the globalconfig
		$prices['shippingTax'] = 0;
		$prices['payment'] = 0; //could be automatically set to a default set in the globalconfig
		$prices['paymentTax'] = 0;
		
		$prices['couponName'] = 'Coupon Number 777';
		$prices['couponTax'] = '';
		$prices['couponValue'] = '';
		$prices['salesPriceCoupon'] = '';
		
		for ($i = 0; $i<$productIdsCount;$i++){
			$productId = $cart[$i]['product_id'];
			$variantmod = $this->parseModifier($cart[$i]['variants']);
			$pricesPerId[(int)$productId] = $this -> getProductPrices($productId,0,$variantmod,$cart[$i]['quantity']);	
			$prices[] = $pricesPerId[(int)$productId];

			$prices['basePrice'] = $prices['basePrice'] + $pricesPerId[$productId]['basePrice']*$cart[$i]['quantity'];
//				$prices['basePriceVariant'] = $prices['basePriceVariant'] + $pricesPerId[$productId]['basePriceVariant']*$cart[$i]['quantity'];
			$prices['basePriceWithTax'] = $prices['basePriceWithTax'] + $pricesPerId[$productId]['basePriceWithTax']*$cart[$i]['quantity'];
			$prices['discountedPriceWithoutTax'] = $prices['discountedPriceWithoutTax'] + $pricesPerId[$productId]['discountedPriceWithoutTax']*$cart[$i]['quantity'];
			$prices['salesPrice'] = $prices['salesPrice'] + $pricesPerId[$productId]['salesPrice']*$cart[$i]['quantity'];
			$prices['taxAmount'] = $prices['taxAmount'] + $pricesPerId[$productId]['taxAmount']*$cart[$i]['quantity'];
			$prices['salesPriceWithDiscount'] = $prices['salesPriceWithDiscount'] + $pricesPerId[$productId]['salesPriceWithDiscount']*$cart[$i]['quantity'];
			$prices['discountAmount'] = $prices['discountAmount'] + $pricesPerId[$productId]['discountAmount']*$cart[$i]['quantity'];
			$prices['priceWithoutTax'] = $prices['priceWithoutTax'] + $pricesPerId[$productId]['priceWithoutTax']*$cart[$i]['quantity'];
		}

		
		if(empty($this->_shopperGroupId)){
			$user = JFactory::getUser();
			if(isset($user->id)){
				$this->_db->setQuery( 'SELECT `shopper_group_id` FROM #__vm_shopper_vendor_xref  WHERE `user_id`="'.$user->id.'" ');
				$this->_shopperGroupId=$this->_db->loadResultArray();			
			}
		}
		
		//todo fill with data
		$this ->_deliveryCountry;
		$this ->_deliveryState;
	
		$dBTaxRules= $this->gatherEffectingRulesForBill('DBTaxBill');
//		$cBRules = $this->gatherEffectingRulesForCoupon($couponId);
//		$sBRules = $this->gatherEffectingRulesForShipment($shipId);
//		$pBRules = $this->gatherEffectingRulesForPayment($paymId);
		$taxRules  = $this->gatherEffectingRulesForBill('TaxBill');
		$dATaxRules= $this->gatherEffectingRulesForBill('DATaxBill');
//		$cBRules = $this->gatherEffectingRulesForCoupon();
		
		$prices['discountBeforeTax']=$discountBeforeTax = $this->roundDisplay($this -> executeCalculation($dBTaxRules, $prices['salesPrice']));
		$toTax = !empty($prices['discountBeforeTax']) ? $prices['discountBeforeTax']:$prices['salesPrice'];

		$prices['withTax']=$discountWithTax = $this->roundDisplay($this -> executeCalculation($taxRules, $toTax));
		$toDisc = !empty($prices['withTax']) ? $prices['withTax']:$toTax;
		
		$discountAfterTax = $this->roundDisplay($this -> executeCalculation($dATaxRules, $toDisc));
		$prices['withTax']=$prices['discountAfterTax']=!empty($discountAfterTax) ?$discountAfterTax:$toDisc;

		return $prices;
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
//		if(empty($rules))return $price;
		if(empty($rules))return 0;
		$rulesEffSorted = $this -> record_sort($rules, 'ordering');
		if(isset($rulesEffSorted)){
			foreach($rulesEffSorted as $rule){
				$price = $this -> interpreteMathOp($rule['calc_value_mathop'],$rule['calc_value'],$price,$rule['calc_currency']);
if($this -> _debug)	echo '<br />RulesEffecting '.$rule['calc_name'].' and value '.$rule['calc_value'].' currency '.$rule['calc_currency'].' and '.$price;
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

		//calc_id 	calc_vendor_id	calc_shopper_published	calc_vendor_published	published 	shared calc_amount_cond	
		$countries = '';
		$states = '';
		$shopperGroup = '';
		//Test if calculation affects the current entry point
		//shared rules counting for every vendor seems to be not necessary
		$q= 'SELECT * FROM #__vm_calc WHERE ' .
		'`calc_kind`="'.$entrypoint.'" ' .
		' AND `published`="1" ' .
		' AND (`calc_vendor_id`="'.$this->productVendorId.'" OR `shared`="1" )'.
		' AND ( publish_up = '.$this->_db->Quote($this ->_nullDate).' OR publish_up <= '.$this->_db->Quote($this ->_now).' )' .
		' AND ( publish_down = '.$this->_db->Quote($this ->_nullDate).' OR publish_down >= '.$this->_db->Quote($this ->_now).' ) ';
		if(!empty($this->_amount)){
			$q .=' AND (`calc_amount_cond` <= "'.$this->_amount.'" OR calc_amount_cond="0" )';
		}
//		' AND ( calc_amount_cond = '.$this->_db->Quote($this ->_nullDate).' OR publish_down >= '.$this->_db->Quote($this ->_now).' ) ';

		$this->_db->setQuery($q);
		$rules = $this->_db->loadAssocList();

		
		$testedRules= array();
		//Cant be done with Leftjoin afaik, because both conditions could be arrays.
		foreach($rules as $rule){
			
			$q= 'SELECT `calc_category` FROM #__vm_calc_category_xref WHERE `calc_rule_id`="'.$rule["calc_id"].'"';
			$this->_db->setQuery($q);
			$cats = $this->_db->loadResultArray();

			$q= 'SELECT `calc_shopper_group` FROM #__vm_calc_shoppergroup_xref WHERE `calc_rule_id`="'.$rule["calc_id"].'"';
			$this->_db->setQuery($q);
			$shoppergrps = $this->_db->loadResultArray();

			$hitsCategory = true;
			if(isset($this->_cats)){
				$hitsCategory = $this->testRulePartEffecting($cats,$this->_cats);
			}
			$hitsShopper = true;
			if(isset($this->_shopperGroupId)){
				$hitsShopper = $this->testRulePartEffecting($shoppergrps,$this->_shopperGroupId);
			}
			
			$hitsAmount = true;
			if(!empty($this->_amount)){
				//Test 
			}
//if ($this -> _debug	) echo '<br/ >foreach '.$rule["calc_id"].' and hitsCat '.$hitsCategory.' and hitsS '.$hitsShopper.' and '.$entrypoint;
			if( $hitsCategory && $hitsShopper ){
				if ($this -> _debug	) echo '<br/ >Add rule ForProductPrice '.$rule["calc_id"];
				$testedRules[]=$rule;
			}
		}
		return $testedRules;
		
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

//		$shoppergrps = $this -> writeRulePartEffectingQuery($this->_shopperGroupId,'calc_shopper',true);
//		$countries = $this -> writeRulePartEffectingQuery($this->_countries,'calc_country',true);
//		$states = $this -> writeRulePartEffectingQuery($this->_states,'calc_state',true);

		//Test if calculation affects the current entry point
		//shared rules counting for every vendor seems to be not necessary
		$q= 'SELECT * FROM #__vm_calc WHERE ' .
			'`calc_kind`="'.$entrypoint.'" ' .
			' AND `published`="1" ' .
			' AND (`calc_vendor_id`="'.$cartVendorId.'" OR `shared`="1" )'.
			' AND ( publish_up = '.$this->_db->Quote($this ->_nullDate).' OR publish_up <= '.$this->_db->Quote($this ->_now).' )' .
			' AND ( publish_down = '.$this->_db->Quote($this ->_nullDate).' OR publish_down >= '.$this->_db->Quote($this ->_now).' ) ';
//			$shoppergrps .  $countries . $states ;
		$this->_db->setQuery($q);
		$rules = $this->_db->loadAssocList();
		$testedRules= array();
		foreach($rules as $rule){

			$q= 'SELECT `calc_country` FROM #__vm_calc_country_xref WHERE `calc_rule_id`="'.$rule["calc_id"].'"';
			$this->_db->setQuery($q);
			$countries = $this->_db->loadResultArray();
			
			$q= 'SELECT `calc_state` FROM #__vm_calc_state_xref WHERE `calc_rule_id`="'.$rule["calc_id"].'"';
			$this->_db->setQuery($q);
			$states = $this->_db->loadResultArray();
			
			$q= 'SELECT `calc_shopper_group` FROM #__vm_calc_shoppergroup_xref WHERE `calc_rule_id`="'.$rule["calc_id"].'"';
			$this->_db->setQuery($q);
			$shoppergrps = $this->_db->loadResultArray();
				
			$hitsCountry = $this->testRulePartEffecting($countries,$this->_deliveryCountry);	
			$hitsStates = $this->testRulePartEffecting($states,$this->_deliveryState);	
			
			$hitsShopper=true;
			if(isset($this->_shopperGroupId)){
				$hitsShopper = $this->testRulePartEffecting($shoppergrps,$this->_shopperGroupId);
			}
			
//			$hitsAmount = 1;
//			if(!empty($this->_amount)){
//				//Test 
//			}

			if($hitsCountry && $hitsStates && $hitsShopper ){
				if ($this -> _debug	) echo '<br/ >Add Checkout rule '.$rule["calc_id"].'<br/ >';
				$testedRules[]=$rule;
			}
		}

//		if (empty($rules)) return;	
		//Just for developing
		foreach($testedRules as $rule){
			echo '<br /> Add rule Entrypoint '.$entrypoint.'  and '.$rule['calc_name'].' query: '.$q;
		}
		return $testedRules;
	}
	
	/**
	 * Gathers the effecting coupons for the calculation
	 * 
	 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
	 * @author Max Milbers
	 * @param 	$code 	The Id of the coupon
	 * @return 	$rules 	ids of the coupons
	 */
	function calculateCouponPrices($code=array()){
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

	private function existCoupons(){
	
		return 1;
	}
	/**
	 * Calculates the effecting Shipment prices for the calculation
	 * @todo
	 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
	 * @author Max Milbers
	 * @param 	$code 	The Id of the coupon
	 * @return 	$rules 	ids of the coupons
	 */
	function calculateShipmentPrice($code=0){
		if (empty($code)) return;
		$q= 'SELECT * FROM #__vm_coupons WHERE ' .
//			$couponCod$codeesQuery .
			' AND ( coupon_start_date = '.$this->_db->Quote($this ->_nullDate).' OR coupon_start_date <= '.$this->_db->Quote($this ->_now).' )' .
			' AND ( coupon_expiry_date = '.$this->_db->Quote($this ->_nullDate).' OR coupon_expiry_date >= '.$this->_db->Quote($this ->_now).' )';
		$this->_db->setQuery($q);
		$rules = $this->_db->loadAssocList();
		return $rules;
	}

	/**
	 * Calculates the effecting Shipment prices for the calculation
	 * @todo
	 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
	 * @author Max Milbers
	 * @param 	$code 	The Id of the paymentmethod
	 * @param	$value	amount of the money to transfere
	 * @param	$value	$cartVendorId
	 * @return 	$paymentCosts 	The amount of money the customer has to pay. Calculated in shop currency
	 */
	function calculatePaymentPrice($code=0,$value=0.0,$cartVendorId=1){
//		if (empty($code)) return 0.0; 
		
		$code=4;
		$paymentCosts = 0.0;
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'models'.DS.'paymentmethod.php');

		$model = new VirtueMartModelPaymentmethod;
		$model->setId($code);
		$paym = $model->getPaym();
//		$q= 'SELECT `discount`, `discount_is_percentage`, `discount_max_amount`, `discount_min_amount` FROM (#__vm_payment_method p, #__vm_payment_method_shoppergroup_xref s)  WHERE '.
//			' `p`.`paym_id` = "'.$code.'" '.
//			' AND `p`.`published`="1" ' .
//			' AND (`p`.`paym_vendor_id`="'.$cartVendorId.'" OR `p`.`shared`="1" ) ';
//			if(!empty($this->_shopperGroupId)){
////				' AND `s`.`paym_id`= "'.$this->_shopperGroupId.'" AND `s`.`paym_shopper_group`= "'.$this->_shopperGroupId.'" ';
//				$q .=' AND `s`.`paym_shopper_group`= "'.$this->_shopperGroupId.'"  AND `s`.`paym_id` = "'.$code.'" ';
//				echo 'Shoppergruppe: '.$this->_shopperGroupId;
//			}
//		$this->_db->setQuery($q);
//		$paymFields = $this->_db->loadAssocList();
//		echo 'hmm '.print_r($paymFields);
//		$discmax = 0.0;
//		if(isset($paymFields['discount_max_amount'])) $discmax = $paymFields['discount_max_amount'];
//		if(isset($paymFields['discount_min_amount'])) $discmin = $paymFields['discount_min_amount'];

		if(!empty($paym->discount)){
			echo '<br />$paymFields->discount is NOT empty';
			
			$discmax = $paym->discount_max_amount;
			$discmin = $paym->discount_min_amount;
			//This must be secured and handled before the checkout can be finished
			if($discmin <= $value){
				if(isset($paym->discount_max_amount) && $value<=$paym->discount_max_amount){
					if($paym->discount_is_percentage){
						$paymentCosts = $value * (1 -$paym->discount/100);
					}else{
						$paymentCosts = $value - $paym->discount;
					}
				}
			}
		} else {
			echo '<br />$paymFields->discount was EMPTY'; 
		}
		
		return $paymentCosts;
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
	function interpreteMathOp($mathop,$value, $price, $currency=''){

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
//					if($this -> _debug)	echo '"grmbl "'. $price.' * (1-'.$value.'/100.0) '.$price * (1-$value/100.0);
					return $price * (1-$value/100.0);
				}
			} else {
				$value = $this->convertCurrencyToShopDefault($currency, $value);
				return $price - $value ;
			}			
		}else if(!strcmp($sign,'=')){
			return $value;
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
	
		
	/**
	 * Can test the tablefields Category, Country, State
	 *  If the the data is 0 false is returned
	 */
	 
	function testRulePartEffecting($rule,$data){

		if(!isset ($rule)) return true;
		if(!isset ($data)) return true;

		if (is_array($rule)) {
			if(count($rule)==0) return true;
		} else {
			$rule = array($rule);
		}
		if (!is_array($data)) $data = array($data);
		
		$intersect = array_intersect($rule,$data);
		if($intersect){
			return true;
		}else{
			return false;
		}

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
	

	/**
	 * Calculate a pricemodification for a variant
	 *
	 * Variant values can be in the following format:
	 * Array ( [Size] => Array ( [XL] => +1 [M] => [S] => -2 ) [Power] => Array ( [strong] => [middle] => [poor] => =24 ) ) 
	 * 
	 * In the post is the data for the chosen variant, when there is a hit, it gets calculated
	 * 
	 * Returns all variant modifications summed up or the highest price set with '='
	 * 
	 * @todo could be slimmed a bit down, using smaller array for variantnames, this could be done by using the parseModifiers method, needs to adjust the post
	 * @author Max Milbers
	 * @param int $product_id the product ID the attribute price should be calculated for
	 * @param array $variantnames the value of the variant
	 * @return array The adjusted price modificator
	 */
	 
	function calculateModificators($product_id,$variants){
		
		
		$modificatorSum=0.0;
		$max=array();
		foreach ($variants as $variant_name => $variant) {	
			$value = JRequest::getVar($product_id.$variant_name,0);
//			echo '<br />The Value is now  <pre>'.print_r($value).'</pre>';
			if(strpos($value,'(')){

				$bundle=strrchr($value,'(') ;
				$modificator=substr($bundle,1,strlen($bundle)-2);
				if(strpos($bundle,'=')){
					$max[]=$modificator;		
				}else{
					if(count($max)==0) $modificatorSum = $modificatorSum+$modificator;
				}
			}
		}
		if(count($max)==0){
			return $modificatorSum;
		} else {
			return max($max);
		}
	}
	
	/**
	 * Calculate a pricemodification for a variant, this is only an internal function for the cartprices
	 *
	 * Variant values can be in the following format, for exampel:
	 *	Array ( [Size] => XL (+1) [Power] => poor (=24) ) 
	 * 
	 * The amount is directly calculated
	 * 
	 * Returns all variant modifications summed up or the highest price set with '='
	 * 
	 * @todo could be slimmed a bit down, using smaller array for variantnames
	 * @author Max Milbers
	 * @param int $product_id the product ID the attribute price should be calculated for
	 * @param array $variantnames the value of the variant
	 * @return array The adjusted price modificator
	 */
	 
	private function parseModifier($variants){
//		echo '<br />Lets see what we have here for variant? <pre>'.print_r($variants).'</pre>';
		$modificatorSum=0.0;
		$max=array();
		foreach ($variants as $variant_name => $value) {		
			if(strpos($value,'(')){
				$bundle=strrchr($value,'(') ;
				$modificator=substr($bundle,1,strlen($bundle)-2);
				if(strpos($bundle,'=')){
					$max[]=$modificator;		
				}else{
					if(count($max)==0) $modificatorSum = $modificatorSum+$modificator;
				}
			}
		}
		if(count($max)==0){
			return $modificatorSum;
		} else {
			return max($max);
		}
	}
}