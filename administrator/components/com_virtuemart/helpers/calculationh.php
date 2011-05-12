<?php
/**
 * Calculation helper class
 *
 * This class provides the functions for the calculatoins
 *
 * @package	VirtueMart
 * @subpackage Helpers
 * @author Max Milbers
 * @copyright Copyright (c) 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class calculationHelper {

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
	private $_cartPrices;
	private $_cartData;

	public $override=0;
	public $productVendorId;
	public $productCurrency;

	private $vendorCurrency = 0;
	private $exchangeRateVendor = 0;
	private $exchangeRateShopper= 0;

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
	private function __construct()
	{
		$this->_db = &JFactory::getDBO();
		$jnow		=& JFactory::getDate();
		$this->_app = JFactory::getApplication();
		$this -> _now			  = $jnow->toMySQL();
		$this -> _nullDate		  = $this->_db->getNullDate();
		$this -> _currency 		  = $this->_getCurrencyObject();


		if(!class_exists('CurrencyDisplay'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'currencydisplay.php');
	    $this -> _currencyDisplay = CurrencyDisplay::getCurrencyDisplay();
		$this -> _debug           = false;
	}

	public function getInstance(){
		if(!is_object(self::$_instance)){
			self::$_instance = new calculationHelper();
		}else {
			$jnow			=& JFactory::getDate();
			$this -> _now 	= $jnow->toMySQL();
		}
		return self::$_instance;
	}

	public function setVendorCurrency($id){
		$this->vendorCurrency = $id;
	}

	/**
	 * This function is for the gui only!
	 * Use this only in a view, plugin or modul, never in a model
	 *
	 * @param float $price
	 * @param integer $currencyId
	 * return string formatted price
	 */
	public function priceDisplay($price=0, $currencyId=0,$shop = false){
		// if($price ) Outcommented (Oscar) to allow 0 values to be formatted too (e.g. free shipping)

		if(empty($currencyId)){
			$currencyId = $this->_app->getUserStateFromRequest( 'currency_id', 'currency_id',$this->vendorCurrency );
			if(empty($currencyId)){
				$currencyId = $this->vendorCurrency;
			}
		}

		$vendorId = 1 ;
//		if($this->_currencyDisplay->id!=$currencyId){
			 $this -> _currencyDisplay = CurrencyDisplay::getCurrencyDisplay($vendorId,$currencyId);
//		}

		$price = $this->convertCurrencyTo($currencyId,$price,$shop);
		return $this -> _currencyDisplay->getFullValue($price);
	}


	public function getCartPrices(){
		return $this->_cartPrices;
	}

	public function getCartData(){
		return $this->_cartData;
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
	public function getProductPrices($productId,$catIds=0,$variant=0.0,$amount=0,$ignoreAmount=true,$currencydisplay=true){

		if(!VmConfig::get('show_prices',0)){
			return array();
		}
		if(VmConfig::get('price_access_level_published',0)){
			//Todo check for ACL groups
			return array();
		}

		$costPrice = 0;
		//Use it as productId
//		if(is_Int($productId)){
			$this->_db->setQuery( 'SELECT * FROM #__virtuemart_product_prices  WHERE `product_id`="'.$productId.'" ');
			$row=$this->_db->loadAssoc();
			if($row){
				if(!empty($row['product_price'])){
					$costPrice = $row['product_price'];
					$this->productCurrency=$row['product_currency'];
					$this->override=$row['override'];
					$this->product_override_price=$row['product_override_price'];
					$this->product_tax_id=$row['product_tax_id'];
					$this->product_discount_id=$row['product_discount_id'];

				} else{
					$app = Jfactory::getApplication();
					$app->enqueueMessage('cost Price empty, if child, everything okey, this is just a dev note');
					return false;
				}
			}
			$this->_db->setQuery( 'SELECT `vendor_id` FROM #__virtuemart_products  WHERE `product_id`="'.$productId.'" ');
			$single = $this->_db->loadResult();
			$this->productVendorId = $single;
			if(empty($this->productVendorId)){
				$this->productVendorId=1;
			}

			if(empty($catIds)){
				$this->_db->setQuery( 'SELECT `virtuemart_category_id` FROM #__virtuemart_product_categories  WHERE `product_id`="'.$productId.'" ');
				$this->_cats=$this->_db->loadResultArray();
			}else{
				$this->_cats=$catIds;
			}
//		}
		//We already have the productobject, no need for extra sql, this idea does not work, because the product object is not completed
//		else {
////			`product_price`,`product_currency`,`product_discount_id`,`product_tax_id`,`sales_price`,`override`
//			$basePrice = $productId->product_price;
//			$this->productCurrency = $productId->product_currency;
//			$this->product_discount_id = $productId->product_discount_id;
//			$this->product_tax_id = $productId->product_tax_id;
//			$this->product_override_price = $productId->product_override_price;
//			$this->override = $productId->override;
//
//			$this->productVendorId = $productId->vendor_id;
//			if(empty($this->productVendorId)){
//				$this->productVendorId=1;
//			}
//			$this->_cats = $productId->categories;
//
//		}

		$this->_db->setQuery( 'SELECT `vendor_currency` FROM #__virtuemart_vendors  WHERE `vendor_id`="'.$this->productVendorId.'" ');
		$single = $this->_db->loadResult();
		$this->vendorCurrency = $single;

		if(empty($this->_shopperGroupId)){
			$user = JFactory::getUser();
			if(!empty($user->id)){
				$this->_db->setQuery( 'SELECT `usgr`.`shopper_group_id` FROM #__virtuemart_user_shoppergroups as `usgr`
 JOIN `#__virtuemart_shoppergroups` as `sg` ON (`usgr`.`shopper_group_id`=`sg`.`shopper_group_id`) WHERE `usgr`.`user_id`="'.$user->id.'" AND `sg`.`vendor_id`="'.$this->productVendorId.'" ');
				$this->_shopperGroupId=$this->_db->loadResult();  //todo load as array and test it
			}
			if(empty($this->_shopperGroupId)){
				$this->_db->setQuery( 'SELECT `shopper_group_id` FROM #__virtuemart_shoppergroups
				WHERE `default`="1" AND `vendor_id`="'.$this->productVendorId.'"');
//				$this->_db->setQuery( 'SELECT `shopper_group_id` FROM #__virtuemart_user_shoppergroups
//				WHERE `default`="1" AND `vendor_id`="'.$this->productVendorId.'" ');
				$this->_shopperGroupId = $this->_db->loadResult();
			}
		}

		if(!empty($amount)){
//			$this->_amount = $amount;
		}

//		$calcRules = $this->gatherEffectingRulesForProductPrice('Calc');
		if(empty($this->product_tax_id)){
			$taxRules = $this->gatherEffectingRulesForProductPrice('Tax');
		} else {
			if (!is_array($this->product_tax_id)) $this->product_tax_id = array($this->product_tax_id);
			$taxRules = $this->product_tax_id;
		}
		$this->rules['tax'] = $taxRules;
		//This is a bit nasty, atm we assume for the discount override that it is meant for discounts after Tax
		if(empty($this->product_discount_id)){
			$dBTaxRules= $this->gatherEffectingRulesForProductPrice('DBTax');
			$dATaxRules = $this->gatherEffectingRulesForProductPrice('DATax');
		} else {
			if (!is_array($this->product_discount_id)) $this->product_discount_id = array($this->product_discount_id);
			$dBTaxRules = array();
			$dATaxRules = $this->product_discount_id;
		}
		$this->rules['dBTax'] = $dBTaxRules;
		$this->rules['dATax'] = $dATaxRules;


		$prices['costPrice']  = $costPrice;
		$basePriceShopCurrency = $this->roundDisplay($this->convertCurrencyTo($this->productCurrency, $costPrice));
		$prices['basePrice']=$basePriceShopCurrency;

//		if(isset($variant)){
//			if (strpos($variant, '=') !== false) {
//	//		   $variant=substr($variant,1);
//			   $basePriceShopCurrency = doubleval(substr($variant,1));
//			} else {
//				$basePriceShopCurrency = $basePriceShopCurrency + doubleval($variant);
//			}
//			$prices['basePrice'] = $prices['basePriceVariant'] = $basePriceShopCurrency;
//		}

		if(!empty($variant)){
			$basePriceShopCurrency = $basePriceShopCurrency + doubleval($variant);
			$prices['basePrice'] = $prices['basePriceVariant'] = $basePriceShopCurrency;
		}
		if(empty($prices['basePrice'])){
			return $this->fillVoidPrices($prices);
		}
		if(empty($prices['basePriceVariant'])){
			$prices['basePriceVariant'] = $prices['basePrice'] ;
		}
		//For Profit, margin, and so on
//		if(count($calcRules)!==0){
//			$prices['profit'] =
//		}

//		$basePrice = !empty($prices['basePriceVariant'])?$prices['basePriceVariant']:$prices['basePrice'];
		$prices['basePriceWithTax'] = $this->roundDisplay($this -> executeCalculation($taxRules, $prices['basePrice'],true));
		$prices['discountedPriceWithoutTax']=$this->roundDisplay($this -> executeCalculation($dBTaxRules, $prices['basePrice']));

		$priceBeforeTax = !empty($prices['discountedPriceWithoutTax'])?$prices['discountedPriceWithoutTax']:$prices['basePrice'];
		$prices['priceBeforeTax'] = $priceBeforeTax;
		$prices['salesPrice'] = $this->roundDisplay($this -> executeCalculation($taxRules, $priceBeforeTax,true));

		$salesPrice = !empty($prices['salesPrice'])?$prices['salesPrice']:$priceBeforeTax;
		$prices['salesPriceTemp']=$salesPrice;
		$prices['taxAmount'] = $this->roundDisplay($salesPrice-$priceBeforeTax);

		$prices['salesPriceWithDiscount'] = $this->roundDisplay($this -> executeCalculation($dATaxRules, $salesPrice));

		$prices['salesPrice'] = !empty($prices['salesPriceWithDiscount'])?$prices['salesPriceWithDiscount']:$salesPrice;

//Okey, this may not the best place, but atm we handle the override price as salesPrice
		if($this->override){
			$prices['salesPrice'] = $this->product_override_price;
		}

		//The whole discount Amount
//		$prices['discountAmount'] = $this->roundDisplay($prices['basePrice'] + $prices['taxAmount'] - $prices['salesPrice']);
		$basePriceWithTax = !empty($prices['basePriceWithTax']) ? $prices['basePriceWithTax'] : $prices['basePrice'];

		//changed
//		$prices['discountAmount'] = $this->roundDisplay($basePriceWithTax - $salesPrice);
		$prices['discountAmount'] = $this->roundDisplay($basePriceWithTax - $prices['salesPrice']);

		//price Without Tax but with calculated discounts AFTER Tax. So it just shows how much the shopper saves, regardless which kind of tax
//		$prices['priceWithoutTax'] = $this->roundDisplay($salesPrice - ($salesPrice - $discountedPrice));
		$prices['priceWithoutTax'] = $salesPrice - $prices['taxAmount'];

		$prices['variantModification']=$variant;


		return $prices;
	}

	private function fillVoidPrices(){

		if(!isset($prices['basePrice'])) $prices['basePrice'] = null;
		if(!isset($prices['basePriceVariant'])) $prices['basePriceVariant'] = null;
		if(!isset($prices['basePriceWithTax'])) $prices['basePriceWithTax'] = null;
		if(!isset($prices['discountedPriceWithoutTax'])) $prices['discountedPriceWithoutTax'] = null;
		if(!isset($prices['priceBeforeTax'])) $prices['priceBeforeTax'] = null;
		if(!isset($prices['taxAmount'])) $prices['taxAmount'] = null;
		if(!isset($prices['salesPriceWithDiscount'])) $prices['salesPriceWithDiscount'] = null;
		if(!isset($prices['salesPrice'])) $prices['salesPrice'] = null;
		if(!isset($prices['discountAmount'])) $prices['discountAmount'] = null;
		if(!isset($prices['priceWithoutTax'])) $prices['priceWithoutTax'] = null;
		if(!isset($prices['variantModification'])) $prices['variantModification'] = null;
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
	 * 		Subtotal + Tax + Discount =	Total
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
	public function getCheckoutPrices($cart){

//		echo '<br />cart: <pre>'.print_r($cart).'</pre><br />';
//		echo '<br />shipping_rate_id '.$cart->shipping_rate_id.'<br />';
		$pricesPerId = array();
		$this->_cartPrices = array();
		$this->_cartData = array();
		$resultWithTax=0.0;
		$resultWithOutTax=0.0;

		$this->_cartPrices['basePrice']= 0;
		$this->_cartPrices['basePriceWithTax']= 0;
		$this->_cartPrices['discountedPriceWithoutTax']= 0;
		$this->_cartPrices['salesPrice']= 0;
		$this->_cartPrices['taxAmount']= 0;
		$this->_cartPrices['salesPriceWithDiscount']= 0;
		$this->_cartPrices['discountAmount']= 0;
		$this->_cartPrices['priceWithoutTax']= 0;
		$this->_cartPrices['subTotalProducts'] = 0;
		$this->_cartData['duty'] = 1;

		$this->_cartData['payment'] = 0; //could be automatically set to a default set in the globalconfig
		$this->_cartData['paymentName'] = '';
		$cartpaymentTax = 0;

		foreach ($cart->products as $name=>$product){
			$productId = $product->product_id;
			if (empty($product->quantity) || empty( $product->product_id )){
				JError::raiseWarning(710,'Error the quantity of the product for calculation is 0, please notify the shopowner, the product id '.$product->product_id);
				continue;
			}

			$variantmods = $this->parseModifier($name);
			$variantmod = $this->calculateModificators($product,$variantmods);

			$cartproductkey = $name ; //$product->product_id.$variantmod;
			$product->prices = $pricesPerId[$cartproductkey] = $this -> getProductPrices($product->product_id,0,$variantmod,$product->quantity,true,false);
			$this->_cartPrices[$cartproductkey] = $product->prices;

			$this->_cartPrices['basePrice'] +=  $product->prices['basePrice']*$product->quantity;
//				$this->_cartPrices['basePriceVariant'] = $this->_cartPrices['basePriceVariant'] + $pricesPerId[$product->product_id]['basePriceVariant']*$product->quantity;
			$this->_cartPrices['basePriceWithTax'] = $this->_cartPrices['basePriceWithTax'] + $product->prices['basePriceWithTax']*$product->quantity;
			$this->_cartPrices['discountedPriceWithoutTax'] = $this->_cartPrices['discountedPriceWithoutTax'] + $product->prices['discountedPriceWithoutTax']*$product->quantity;
			$this->_cartPrices['salesPrice'] = $this->_cartPrices['salesPrice'] + $product->prices['salesPrice']*$product->quantity;
			$this->_cartPrices['taxAmount'] = $this->_cartPrices['taxAmount'] + $product->prices['taxAmount']*$product->quantity;
			$this->_cartPrices['salesPriceWithDiscount'] = $this->_cartPrices['salesPriceWithDiscount'] + $product->prices['salesPriceWithDiscount']*$product->quantity;
			$this->_cartPrices['discountAmount'] = $this->_cartPrices['discountAmount'] + $product->prices['discountAmount']*$product->quantity;
			$this->_cartPrices['priceWithoutTax'] = $this->_cartPrices['priceWithoutTax'] + $product->prices['priceWithoutTax']*$product->quantity;

			$this->_cartPrices[$cartproductkey]['subtotal'] = $product->prices['basePrice'] * $product->quantity;
			$this->_cartPrices[$cartproductkey]['subtotal_tax_amount'] = $product->prices['taxAmount'] * $product->quantity;
			$this->_cartPrices[$cartproductkey]['subtotal_discount'] = $product->prices['discountAmount'] * $product->quantity;
			$this->_cartPrices[$cartproductkey]['subtotal_with_tax'] = $product->prices['salesPrice'] * $product->quantity;

//			if(empty($this->_cartPrices['priceWithoutTax'])){ //before tax
//				$this->_cartPrices['subTotalProducts'] += $product->prices['discountedPriceWithoutTax']*$product->quantity;
//			} else {
//				$this->_cartPrices['subTotalProducts'] += $product->prices['basePrice']*$product->quantity;
//			}
		}



		if(empty($this->_shopperGroupId)){
			$user = JFactory::getUser();
			if(isset($user->id)){
				$this->_db->setQuery( 'SELECT `shopper_group_id` FROM #__virtuemart_user_shoppergroups  WHERE `user_id`="'.$user->id.'" ');
				$this->_shopperGroupId=$this->_db->loadResultArray();
			}
		}

		//todo fill with data
		if(!empty($cart->ST['virtuemart_country_id'])){
			$this ->_deliveryCountry = $cart->ST['virtuemart_country_id'];
		}else if(!empty($cart->BT['virtuemart_country_id'])){
			$this ->_deliveryCountry = $cart->BT['virtuemart_country_id'];
		}

		if(!empty($cart->ST['virtuemart_state_id'])){
			$this ->_deliveryState = $cart->ST['virtuemart_state_id'];
		}else if(!empty($cart->BT['virtuemart_state_id'])){
			$this ->_deliveryState = $cart->BT['virtuemart_state_id'];
		}


		$this->_cartData['dBTaxRulesBill'] = $dBTaxRules= $this->gatherEffectingRulesForBill('DBTaxBill');
//		$cBRules = $this->gatherEffectingRulesForCoupon($couponId);

		$shippingRateId = empty($cart->shipping_rate_id) ? 0 : $cart->shipping_rate_id;

		$this->calculateShipmentPrice($shippingRateId);

//		$pBRules = $this->gatherEffectingRulesForPayment($paymId);
		$this->_cartData['taxRulesBill'] = $taxRules  = $this->gatherEffectingRulesForBill('TaxBill');
		$this->_cartData['dATaxRulesBill'] = $dATaxRules= $this->gatherEffectingRulesForBill('DATaxBill');

//		$cBRules = $this->gatherEffectingRulesForCoupon();

		$this->_cartPrices['discountBeforeTaxBill'] = $this->roundDisplay($this -> executeCalculation($dBTaxRules, $this->_cartPrices['salesPrice']));
		$toTax = !empty($this->_cartPrices['discountBeforeTaxBill']) ? $this->_cartPrices['discountBeforeTaxBill']:$this->_cartPrices['salesPrice'];

		//We add the price of the Shipment before the tax. The tax per bill is meant for all services. In the other case people should use taxes per
		//  product or method
		$toTax = $toTax + $this->_cartPrices['salesPriceShipping'];

		$this->_cartPrices['withTax'] = $discountWithTax = $this->roundDisplay($this -> executeCalculation($taxRules, $toTax, true));
		$toDisc = !empty($this->_cartPrices['withTax']) ? $this->_cartPrices['withTax']:$toTax;


		$discountAfterTax = $this->roundDisplay($this -> executeCalculation($dATaxRules, $toDisc));
		$this->_cartPrices['withTax']=$this->_cartPrices['discountAfterTax']=!empty($discountAfterTax) ?$discountAfterTax:$toDisc;

		$paymentId = empty($cart->paym_id) ? 0 : $cart->paym_id;
		$creditId = empty($cart->creditcard_id) ? 0 : $cart->creditcard_id;

		$this->calculatePaymentPrice($paymentId,$creditId,$this->_cartPrices['withTax']);

//		$sub =!empty($this->_cartPrices['discountedPriceWithoutTax'])? $this->_cartPrices['discountedPriceWithoutTax']:$this->_cartPrices['basePrice'];
		$this->_cartPrices['billSub']  = $this->_cartPrices['basePrice'] + $this->_cartPrices['shippingValue'] + $this->_cartPrices['paymentValue'];
//		$this->_cartPrices['billSub']  = $sub + $this->_cartPrices['shippingValue'] + $this->_cartPrices['paymentValue'];
		$this->_cartPrices['billDiscountAmount'] = $this->_cartPrices['discountAmount'] + $this->_cartPrices['paymentDiscount'];
		$this->_cartPrices['billTaxAmount'] = $this->_cartPrices['taxAmount'] + $this->_cartPrices['withTax'] - $toTax;
		$this->_cartPrices['billTotal'] = $this->_cartPrices['salesPricePayment'] + $this->_cartPrices['withTax'];

		// Last step is handling a coupon, if given
		if (!empty($cart->couponCode)) {
			$this->couponHandler($cart->couponCode);
		}

		//		echo '<br />The prices:<br />';
//		echo '<pre>'.print_r($this->_cartPrices).'</pre>';

		return $this->_cartPrices;
	}

	/**
	 * Get coupon details and calculate the value
	 * @author Oscar van Eijk
	 * @param $_code Coupon code
	 */
	private function couponHandler($_code)
	{
		if(!class_exists('CouponHelper')) require(JPATH_VM_SITE.DS.'helpers'.DS.'coupon.php');
		if (!($_data = CouponHelper::getCouponDetails($_code))) {
			return; // TODO give some error here
		}
		$_value_is_total = ($_data->percent_or_total == 'total');
		$this->_cartData['couponCode'] = $_code;
		$this->_cartData['couponDescr'] = ($_value_is_total
				? $this->priceDisplay($_data->coupon_value)
				: (round($_data->coupon_value).'%')
			);
		$this->_cartPrices['salesPriceCoupon'] = ($_value_is_total
			? $_data->coupon_value
			: ($this->_cartPrices['salesPrice'] * ($_data->coupon_value / 100))
		);
		// TODO Calculate the tax
		$this->_cartPrices['couponTax'] = 0;
		$this->_cartPrices['couponValue'] = $this->_cartPrices['salesPriceCoupon'] - $this->_cartPrices['couponTax'];
		$this->_cartPrices['billTotal'] -= $this->_cartPrices['salesPriceCoupon'];
	}

	/**
	 * Function to execute the calculation of the gathered rules Ids.
	 *
	 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
	 * @author Max Milbers
	 * @param 		$rules 		The Ids of the products
	 * @param 		$price 		The input price, if no rule is affecting, 0 gets returned
	 * @return int 	$price  	the endprice
	 */
	function executeCalculation($rules, $baseprice,$relateToBaseAmount=false){

		if(empty($rules))return 0;
		$rulesEffSorted = $this -> record_sort($rules, 'ordering');

		$price = $baseprice;
		$finalprice = $baseprice;
		if(isset($rulesEffSorted)){
			foreach($rulesEffSorted as $rule){

				if($relateToBaseAmount){
					$cIn=$baseprice;
				} else {
					$cIn=$price;
				}
				$cOut = $this -> interpreteMathOp($rule['calc_value_mathop'],$rule['calc_value'],$cIn,$rule['calc_currency']);
				$this->_cartPrices[$rule['virtuemart_calc_id'].'Diff'] = $this->roundDisplay($this->roundDisplay($cOut) - $cIn);

				//okey, this is a bit flawless logic, but should work
				if($relateToBaseAmount){
					$finalprice = $finalprice + $this->_cartPrices[$rule['virtuemart_calc_id'].'Diff'];
				} else {
					$price = $cOut;
				}
			}
		}

		//okey done with it
		if(!$relateToBaseAmount){
			$finalprice = $price;
		}

		return $finalprice;
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

		//virtuemart_calc_id 	virtuemart_vendor_id	calc_shopper_published	calc_vendor_published	published 	shared calc_amount_cond
		$countries = '';
		$states = '';
		$shopperGroup = '';
		//Test if calculation affects the current entry point
		//shared rules counting for every vendor seems to be not necessary
		$q= 'SELECT * FROM #__virtuemart_calcs WHERE ' .
		'`calc_kind`="'.$entrypoint.'" ' .
		' AND `published`="1" ' .
		' AND (`virtuemart_vendor_id`="'.$this->productVendorId.'" OR `shared`="1" )'.
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

			$q = 'SELECT `virtuemart_category_id` FROM #__virtuemart_calc_categories WHERE `virtuemart_calc_id`="'.$rule['virtuemart_calc_id'].'"';
			$this->_db->setQuery($q);
			$cats = $this->_db->loadResultArray();dump($q,'$q');

			$q= 'SELECT `virtuemart_shoppergroup_id` FROM #__virtuemart_calc_shoppergroups WHERE `virtuemart_calc_id`="'.$rule['virtuemart_calc_id'].'"';
			$this->_db->setQuery($q);
			$shoppergrps = $this->_db->loadResultArray();

			$hitsCategory = true;
			dump($this->_cats,'$this->_cats');
			dump($cats,'$cats');
			if(isset($this->_cats)){
				$hitsCategory = $this->testRulePartEffecting($cats,$this->_cats);
				dump($hitsCategory,'$hitsCategory');
			}
			$hitsShopper = true;
			if(isset($this->_shopperGroupId)){
				$hitsShopper = $this->testRulePartEffecting($shoppergrps,$this->_shopperGroupId);
			}

			$hitsAmount = true;
			if(!empty($this->_amount)){
				//Test
			}
//			dump($hitsShopper,'$hitsShopper');
//if ($this -> _debug	) echo '<br/ >foreach '.$rule["virtuemart_calc_id"].' and hitsCat '.$hitsCategory.' and hitsS '.$hitsShopper.' and '.$entrypoint;
			if( $hitsCategory && $hitsShopper ){
				if ($this -> _debug	) echo '<br/ >Add rule ForProductPrice '.$rule["virtuemart_calc_id"];
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
//		$countries = $this -> writeRulePartEffectingQuery($this->_countries,'virtuemart_country_id',true);
//		$states = $this -> writeRulePartEffectingQuery($this->_states,'virtuemart_state_id',true);

		//Test if calculation affects the current entry point
		//shared rules counting for every vendor seems to be not necessary
		$q= 'SELECT * FROM #__virtuemart_calcs WHERE ' .
			'`calc_kind`="'.$entrypoint.'" ' .
			' AND `published`="1" ' .
			' AND (`virtuemart_vendor_id`="'.$cartVendorId.'" OR `shared`="1" )'.
			' AND ( publish_up = '.$this->_db->Quote($this ->_nullDate).' OR publish_up <= '.$this->_db->Quote($this ->_now).' )' .
			' AND ( publish_down = '.$this->_db->Quote($this ->_nullDate).' OR publish_down >= '.$this->_db->Quote($this ->_now).' ) ';
//			$shoppergrps .  $countries . $states ;
		$this->_db->setQuery($q);
		$rules = $this->_db->loadAssocList();
		$testedRules= array();
		foreach($rules as $rule){

			$q= 'SELECT `virtuemart_country_id` FROM #__virtuemart_calc_countries WHERE `virtuemart_calc_id`="'.$rule["virtuemart_calc_id"].'"';
			$this->_db->setQuery($q);
			$countries = $this->_db->loadResultArray();

			$q= 'SELECT `virtuemart_state_id` FROM #__virtuemart_state_ids WHERE `virtuemart_calc_id`="'.$rule["virtuemart_calc_id"].'"';
			$this->_db->setQuery($q);
			$states = $this->_db->loadResultArray();

			$q= 'SELECT `virtuemart_shoppergroup_id` FROM #__virtuemart_calc_shoppergroups WHERE `virtuemart_calc_id`="'.$rule["virtuemart_calc_id"].'"';
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
				if ($this -> _debug	) echo '<br/ >Add Checkout rule '.$rule["virtuemart_calc_id"].'<br/ >';
				$testedRules[]=$rule;
			}
		}

//		if (empty($rules)) return;
		//Just for developing
		foreach($testedRules as $rule){
//			echo '<br /> Add rule Entrypoint '.$entrypoint.'  and '.$rule['calc_name'].' query: '.$q;
		}
		return $testedRules;
	}

//	/**
//	 * Gathers the effecting coupons for the calculation
//	 *
//	 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
//	 * @author Max Milbers
//	 * @param 	$code 	The Id of the coupon
//	 * @return 	$rules 	ids of the coupons
//	 */
//	function calculateCouponPrices($code=array()){
//		if (empty($code)) return;
//		$couponCodesQuery = $this -> writeRulePartEffectingQuery($code,'coupon_code');
//		$q= 'SELECT * FROM #__virtuemart_coupons WHERE ' .
//			$couponCodesQuery .
//			' AND ( coupon_start_date = '.$this->_db->Quote($this ->_nullDate).' OR coupon_start_date <= '.$this->_db->Quote($this ->_now).' )' .
//			' AND ( coupon_expiry_date = '.$this->_db->Quote($this ->_nullDate).' OR coupon_expiry_date >= '.$this->_db->Quote($this ->_now).' )';
//		$this->_db->setQuery($q);
//		$rules = $this->_db->loadAssocList();
//		return $rules;
//	}

	/**
	 * Calculates the effecting Shipment prices for the calculation
	 * @todo
	 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
	 * @author Max Milbers
	 * @param 	$code 	The Id of the coupon
	 * @return 	$rules 	ids of the coupons
	 */
	function calculateShipmentPrice($ship_id){

		$this->_cartData['shippingName'] = JText::_('COM_VIRTUEMART_CART_NO_SHIPMENT_SELECTED');
		$this->_cartPrices['shippingValue'] = 0; //could be automatically set to a default set in the globalconfig
		$this->_cartPrices['shippingTax'] = 0;
		$this->_cartPrices['shippingTotal'] = 0;
		$this->_cartPrices['salesPriceShipping'] = 0;
		if (empty($ship_id)) return ;

//		$_dispatcher = JDispatcher::getInstance();
//		$_retValues = $_dispatcher->trigger('plgVmOnShipperSelected',
//			array('_selectedRate' => $ship_id));

		if (!class_exists('VirtueMartModelShippingRate')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'shippingrate.php');
		$_sRate = new VirtueMartModelShippingRate();
		$shipping = $_sRate->getShippingRatePrices($ship_id, true);

		// Outcommented (Oscar); use th model instead
//		$q= 'SELECT * FROM `#__virtuemart_shippingrates` AS `r`, `#__virtuemart_shippingcarriers` AS `c`  WHERE `shipping_rate_id` = "'.$ship_id.'" ';
//		$this->_db->setQuery($q);
//		$shipping = $this->_db->loadAssoc();

		$this->_cartPrices['shipping_rate_value'] = $shipping['shipping_rate_value']; //could be automatically set to a default set in the globalconfig
		$this->_cartPrices['shipping_rate_package_fee'] = $shipping['shipping_rate_package_fee'];
		$this->_cartPrices['shippingValue'] =  $shipping['shipping_rate_value'] + $shipping['shipping_rate_package_fee'];
		$this->_cartData['shippingName'] = $shipping['shipping_carrier_name'].': '. $shipping['shipping_rate_name'];

		$taxrules = array();
		if(!empty($shipping['shipping_rate_vat_id'])){
			$q= 'SELECT * FROM #__virtuemart_calcs WHERE `virtuemart_calc_id`="'.$shipping['shipping_rate_vat_id'].'" ' ;
			$this->_db->setQuery($q);
			$taxrules = $this->_db->loadAssocList();
		}

		if(count($taxrules)>0){
			$this->_cartPrices['salesPriceShipping'] = self::roundDisplay(self::executeCalculation($taxrules, $this->_cartPrices['shippingValue']));
			$this->_cartPrices['shippingTax'] = self::roundDisplay($this->_cartPrices['salesPriceShipping'])-$this->_cartPrices['shippingValue'];
		} else {
			$this->_cartPrices['salesPriceShipping'] = $this->_cartPrices['shippingValue'];
			$this->_cartPrices['shippingTax'] = 0;
		}

		return $this->_cartPrices;
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
	function calculatePaymentPrice($paym_id=0,$cc_id=0,$value=0.0,$cartVendorId=1){
//		if (empty($code)) return 0.0;

//		$code=4;
		$paymentCosts = 0.0;

		if(!class_exists('VirtueMartModelPaymentmethod')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'paymentmethod.php');

		$model = new VirtueMartModelPaymentmethod();
		$model->setId($paym_id);
		$paym = $model->getPaym();

		$this->_cartData['paymentName'] = !empty ($paym->paym_name) ? $paym->paym_name : JText::_('COM_VIRTUEMART_CART_NO_PAYM_SELECTED');
		$this->_cartPrices['paymentValue'] = 0;
		$this->_cartPrices['paymentTax'] = 0;
		$this->_cartPrices['paymentDiscount'] = 0;
//		$this->_cartPrices['paymentTotal'] = 0;

//		echo '<pre>'.print_r($paym).'</pre>';
		if(!empty($paym->discount)){

			if($paym->discount>0){
				$toggle = 'paymentDiscount';
			} else {
				$toggle = 'paymentValue';
			}

			if($paym->discount_min_amount <= $value){

				if($paym->discount_max_amount == 0 || $value <=$paym->discount_max_amount){

					//Attention the minus is due the strange logic by entering discount instead of a fee, maybe changed, but later
					if($paym->discount_is_percentage){
						$this->_cartPrices[$toggle] = - $value * ($paym->discount/100);
					}else{
						$this->_cartPrices[$toggle] = - $paym->discount;
					}
				}
			}
		} else {
//			echo '<br />$paymFields->discount was EMPTY';
			$toggle = 'paymentValue';
		}

		//Strange thing here, yes. The amount to pay for the payment is solved via the discount.
		//a negative discount is like a payment method which costs
//		$this->_cartPrices['paymentValue'] = $this->_cartPrices['paymentDiscount'];
		$this->_cartPrices['salesPricePayment'] = self::roundDisplay($this->_cartPrices[$toggle] + $this->_cartPrices['paymentTax']);

//		echo '<pre>'.print_r($this->_cartPrices[$toggle]).'</pre>';

		return $this->_cartPrices;
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

				$value = $this->convertCurrencyTo($currency, $value);
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
				$value = $this->convertCurrencyTo($currency, $value);
				return $price - $value ;
			}
		}else if(!strcmp($sign,'=')){
			return $value;
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
		if( $currency == $this->vendorCurrency ) {
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
//			if(is_Int($currency)){
				$q = 'SELECT `exchange_rate`
				FROM `#__virtuemart_currencies` WHERE `currency_id` ="'.$currency.'" ';
				$this->_db->setQuery($q);
				if(	$exch = $this->_db->loadResult()){
					$exchangeRate = $this->_db->loadResult();
				} else {
					$exchangeRate = FALSE;
				}

//			}
		}

		if(!empty($exchangeRate) && $exchangeRate!=FALSE){
			$price = $price * $exchangeRate;
		} else {
			if($shop){
				$price = $this ->_currency->convert( $price, self::ensureUsingCurrencyCode($currency),self::ensureUsingCurrencyCode($this->vendorCurrency));
			} else {
				$price = $this ->_currency->convert( $price , self::ensureUsingCurrencyCode($this->vendorCurrency),  self::ensureUsingCurrencyCode($currency));
			}

		}

		return $price;
	}



	/**
	 * Changes the currency_id into the right currency_code
	 * For exampel 47 => EUR
	 *
	 * @author Max Milbers
	 * @author Frederic Bidon
	 */
	function ensureUsingCurrencyCode($curr){

		if(is_numeric($curr)){
			$this->_db = JFactory::getDBO();
			$q = 'SELECT `currency_code` FROM `#__virtuemart_currencies` WHERE `currency_id`="'.$curr.'"';
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

				$keyToUse = $record[$field];
				while(array_key_exists($keyToUse,$hash)){
					$keyToUse = $keyToUse + 1;
				}
		        $hash[$keyToUse] = $record;
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

	public function calculateModificators($product,$dummy){

		$modificatorSum=0.0;

		foreach($dummy as $variants){
			foreach($variants as $variant=>$selected){
				if (!empty($selected)) {
					$query='SELECT  field.`custom_field_id` ,field.`custom_value`,field.`custom_price`
						FROM `#__virtuemart_customs` AS C
						LEFT JOIN `#__virtuemart_customfields` AS field ON C.`custom_id` = field.`custom_id`
						LEFT JOIN `#__virtuemart_product_customfields` AS xref ON xref.`custom_field_id` = field.`custom_field_id`
						WHERE xref.`product_id` ='.$product->product_id;
					$query .=' and is_cart_attribute = 1 and field.`custom_field_id`='.$selected ;
					$this->_db->setQuery($query);
					$productCustomsPrice = $this->_db->loadObject();
					$app = JFactory::getApplication();
					if(!empty($productCustomsPrice->custom_price)){
						//TODO adding % and more We should use here $this->interpreteMathOp
						$modificatorSum = $modificatorSum + $productCustomsPrice->custom_price;
					}
				}
			}
		}
		return $modificatorSum;
	}

	public function parseModifier($name){

		$items = explode(';',$name);

		$return = array();
		$variants = array();
		foreach($items as $item){
			if(!empty($item)){
				$index = strpos($item,'::');
				$product_id = substr($item,0,$index);
				$item = substr($item,$index+2);

				$index2 = strpos($item,':');
				$variant = substr($item,0,$index2);
				$selected = substr($item,$index2+1);
				$variants[$variant] = $selected;
			}
		}
		$return[] = $variants;
		return $return;
	}


		/**
	 * Load the currency object
	 * @access private
	 * @author Oscar van Eijk, Max Milbers
	 * @return object
	 *
	 */
	private function _getCurrencyObject()
	{

		$converterFile  = VmConfig::get('currency_converter_module');

		if (file_exists( JPATH_VM_ADMINISTRATOR.DS.'plugins'.DS.'currency_converter'.DS.$converterFile.'.php' )) {
			$module_filename = $converterFile;
			require_once(JPATH_VM_ADMINISTRATOR.DS.'plugins'.DS.'currency_converter'.DS.$converterFile.'.php');
			if( class_exists( $module_filename )) {
				$_currency = new $module_filename();
			}
		} else {
			if(!class_exists('convertECB')) require(JPATH_VM_ADMINISTRATOR.DS.'plugins'.DS.'currency_converter'.DS.'convertECB.php');
			$_currency = new convertECB();
		}
		return $_currency;
	}


}
