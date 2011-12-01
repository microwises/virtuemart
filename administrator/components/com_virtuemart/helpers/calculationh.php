<?php

/**
 * Calculation helper class
 *
 * This class provides the functions for the calculations
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
	private $_now;
	private $_nullDate;
	//	private $_currency;
	private $_debug;

	private $_deliveryCountry;
	private $_deliveryState;
	private $_currencyDisplay;
	private $_cart = null;
	private $_cartPrices;
	private $_cartData;

	public $_amount;

	public $override = 0;
	public $productVendorId;
	public $productCurrency;
	public $product_tax_id = 0;
	public $product_discount_id = 0;
	public $product_marge_id = 0;
	public $vendorCurrency = 0;
	private $exchangeRateVendor = 0;
	private $exchangeRateShopper = 0;
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
	private function __construct() {
		$this->_db = JFactory::getDBO();
		$jnow = JFactory::getDate();
		$this->_app = JFactory::getApplication();
		$this->_now = $jnow->toMySQL();
		$this->_nullDate = $this->_db->getNullDate();

		//Attention, this is set to the mainvendor atm.
		//This means also that atm for multivendor, every vendor must use the shopcurrency as default
		//         $this->vendorCurrency = 1;
		$this->productVendorId = 1;

		if (!class_exists('CurrencyDisplay')
		)require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');
		$this->_currencyDisplay = CurrencyDisplay::getInstance();
		$this->_debug = false;

		if(!empty($this->_currencyDisplay->_vendorCurrency)){
			$this->vendorCurrency = $this->_currencyDisplay->_vendorCurrency;
		}
		else if(VmConfig::get('multix','none')!='none'){
			$this->_db->setQuery('SELECT `vendor_currency` FROM #__virtuemart_vendors  WHERE `virtuemart_vendor_id`="1" ');
			$single = $this->_db->loadResult();
			$this->vendorCurrency = $single;
		}

		$this->setShopperGroupIds();

// 		if (!empty($id)) {
// 			$q = 'SELECT * FROM #__virtuemart_calcs WHERE `virtuemart_calc_id` = "' . $id . '" AND `calc_kind`="' . $entrypoint . '" ';
// 		} else {

		$epoints = array('Marge','Tax','DBTax','DATax');
		$this->allrules = array();
		foreach($epoints as $entrypoint){
			$q = 'SELECT * FROM #__virtuemart_calcs WHERE
					                    `calc_kind`="' . $entrypoint . '"
					                     AND `published`="1"
					                     AND (`virtuemart_vendor_id`="' . $this->productVendorId . '" OR `shared`="1" )
					                     AND ( publish_up = "' . $this->_db->getEscaped($this->_nullDate) . '" OR publish_up <= "' . $this->_db->getEscaped($this->_now) . '" )
					                     AND ( publish_down = "' . $this->_db->getEscaped($this->_nullDate) . '" OR publish_down >= "' . $this->_db->getEscaped($this->_now) . '" ) ';

			$this->_db->setQuery($q);
			$this->allrules[$entrypoint] = $this->_db->loadAssocList();

		}

// 		vmdebug('my rules ',$this->allrules);
		$this->rules['Marge'] = array();
		$this->rules['Tax'] 	= array();
		$this->rules['DBTax'] = array();
		$this->rules['DATax'] = array();
	}

	public function getInstance() {
		if (!is_object(self::$_instance)) {
			self::$_instance = new calculationHelper();
		} else {
			$jnow = JFactory::getDate();
			$this->_now = $jnow->toMySQL();
		}
		return self::$_instance;
	}

	public function setVendorCurrency($id) {
		$this->vendorCurrency = $id;
	}

	public function getCartPrices() {
		return $this->_cartPrices;
	}

	public function getCartData() {
		return $this->_cartData;
	}

	private function setShopperGroupIds($shopperGroupIds=0, $vendorId=1) {

		if (!empty($shopperGroupIds)) {
			$this->_shopperGroupId = $shopperGroupIds;
		} else {
			$user = JFactory::getUser();
			if (!empty($user->id)) {
				$this->_db->setQuery('SELECT `usgr`.`virtuemart_shoppergroup_id` FROM #__virtuemart_vmuser_shoppergroups as `usgr`
 										JOIN `#__virtuemart_shoppergroups` as `sg` ON (`usgr`.`virtuemart_shoppergroup_id`=`sg`.`virtuemart_shoppergroup_id`)
 										WHERE `usgr`.`virtuemart_user_id`="' . $user->id . '" AND `sg`.`virtuemart_vendor_id`="' . (int) $vendorId . '" ');
				$this->_shopperGroupId = $this->_db->loadResultArray();  //todo load as array and test it
				if (empty($this->_shopperGroupId)) {
					$this->_db->setQuery('SELECT `virtuemart_shoppergroup_id` FROM #__virtuemart_shoppergroups
								WHERE `default`="1" AND `virtuemart_vendor_id`="' . (int) $vendorId . '"');
					$this->_shopperGroupId = $this->_db->loadResultArray();
				}
			}
			else if (empty($this->_shopperGroupId)) {
				//We just define the shoppergroup with id = 1 to anonymous default shoppergroup
				$this->_shopperGroupId[] = 1;
			}
		}
	}

	private function setCountryState($cart=0) {

		if ($this->_app->isAdmin())
		return;

		if (empty($cart)) {
			if (!class_exists('VirtueMartCart'))
			require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
			$cart = VirtueMartCart::getCart();
		}
		$this->_cart = $cart;

		if (!empty($this->_cart->ST['virtuemart_country_id'])) {
			$this->_deliveryCountry = $this->_cart->ST['virtuemart_country_id'];
		} else if (!empty($this->_cart->BT['virtuemart_country_id'])) {
			$this->_deliveryCountry = $this->_cart->BT['virtuemart_country_id'];
		}

		if (!empty($this->_cart->ST['virtuemart_state_id'])) {
			$this->_deliveryState = $this->_cart->ST['virtuemart_state_id'];
		} else if (!empty($cart->BT['virtuemart_state_id'])) {
			$this->_deliveryState = $this->_cart->BT['virtuemart_state_id'];
		}
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
	public function getProductPrices($productId, $catIds=0, $variant=0.0, $amount=0, $ignoreAmount=true, $currencydisplay=true) {

/*		if (!VmConfig::get('show_prices', 0)) {
		 return array();
		}
/*		if (VmConfig::get('price_access_level_published', 0)) {
			$user = JFactory::getUser();
			if(empty($user->id)){
				return array();
			}
			//Todo check for virtuemart shoppergroups
		}*/

		$costPrice = 0;

		//We already have the productobject, no need for extra sql
		if (is_object($productId)) {
			$costPrice = $productId->product_price;
			$this->productCurrency = $productId->product_currency;
			$this->override = $productId->override;
			$this->product_override_price = $productId->product_override_price;
			$this->product_tax_id = $productId->product_tax_id;
			$this->product_discount_id = $productId->product_discount_id;
			$this->productVendorId = $productId->virtuemart_vendor_id;
			if (empty($this->productVendorId)) {
				$this->productVendorId = 1;
			}
			$this->_cats = $productId->categories;
		} //Use it as productId
		else {
			vmSetStartTime('getProductCalcs');
			$this->_db->setQuery('SELECT * FROM #__virtuemart_product_prices  WHERE `virtuemart_product_id`="' . $productId . '" ');
			$row = $this->_db->loadAssoc();
			if ($row) {
				if (!empty($row['product_price'])) {
					$costPrice = $row['product_price'];
					$this->productCurrency = $row['product_currency'];
					$this->override = $row['override'];
					$this->product_override_price = $row['product_override_price'];
					$this->product_tax_id = $row['product_tax_id'];
					$this->product_discount_id = $row['product_discount_id'];
				} else {
					$app = Jfactory::getApplication();
					$app->enqueueMessage('cost Price empty, if child, everything okey, this is just a dev note');
					return false;
				}
			}
			$this->_db->setQuery('SELECT `virtuemart_vendor_id` FROM #__virtuemart_products  WHERE `virtuemart_product_id`="' . $productId . '" ');
			$single = $this->_db->loadResult();
			$this->productVendorId = $single;
			if (empty($this->productVendorId)) {
				$this->productVendorId = 1;
			}

			if (empty($catIds)) {
				$this->_db->setQuery('SELECT `virtuemart_category_id` FROM #__virtuemart_product_categories  WHERE `virtuemart_product_id`="' . $productId . '" ');
				$this->_cats = $this->_db->loadResultArray();
			} else {
				$this->_cats = $catIds;
			}
			vmTime('getProductPrices no object given query time','getProductCalcs');
		}

		if(VmConfig::get('multix','none')!='none' and empty($this->vendorCurrency )){
			$this->_db->setQuery('SELECT `vendor_currency` FROM #__virtuemart_vendors  WHERE `virtuemart_vendor_id`="' . $this->productVendorId . '" ');
			$single = $this->_db->loadResult();
			$this->vendorCurrency = $single;
		}

		if (!empty($amount)) {
			$this->_amount = $amount;
		}

		$this->setCountryState($this->_cart);

		$this->rules['Tax'] = $this->gatherEffectingRulesForProductPrice('Tax', $this->product_tax_id);
		$this->rules['DBTax'] = $this->gatherEffectingRulesForProductPrice('DBTax', $this->product_discount_id);
		$this->rules['DATax'] = $this->gatherEffectingRulesForProductPrice('DATax', $this->product_discount_id);

		$prices['costPrice'] = $costPrice;
		$basePriceShopCurrency = $this->roundDisplay($this->_currencyDisplay->convertCurrencyTo((int) $this->productCurrency, $costPrice));
		//         vmdebug('my pure $basePriceShopCurrency',$basePriceShopCurrency);

		//For Profit, margin, and so on
		$this->rules['Marge'] = $this->gatherEffectingRulesForProductPrice('Marge', $this->product_marge_id);

		$basePriceMargin = $this->roundDisplay($this->executeCalculation($this->rules['Marge'], $basePriceShopCurrency));
		$basePriceShopCurrency = $prices['basePrice'] = !empty($basePriceMargin) ? $basePriceMargin : $basePriceShopCurrency;

		//         vmdebug('my $basePriceShopCurrency after Marge',$basePriceShopCurrency);

		//         $prices['basePrice'] = $basePriceShopCurrency;

		if (!empty($variant)) {
			$basePriceShopCurrency = $basePriceShopCurrency + doubleval($variant);
			$prices['basePrice'] = $prices['basePriceVariant'] = $basePriceShopCurrency;
		}
		if (empty($prices['basePrice'])) {
			return $this->fillVoidPrices($prices);
		}
		if (empty($prices['basePriceVariant'])) {
			$prices['basePriceVariant'] = $prices['basePrice'];
		}


		$prices['basePriceWithTax'] = $this->roundDisplay($this->executeCalculation($this->rules['Tax'], $prices['basePrice'], true));
		$prices['discountedPriceWithoutTax'] = $this->roundDisplay($this->executeCalculation($this->rules['DBTax'], $prices['basePrice']));

		$priceBeforeTax = !empty($prices['discountedPriceWithoutTax']) ? $prices['discountedPriceWithoutTax'] : $prices['basePrice'];
		$prices['priceBeforeTax'] = $priceBeforeTax;
		$prices['salesPrice'] = $this->roundDisplay($this->executeCalculation($this->rules['Tax'], $priceBeforeTax, true));

		$salesPrice = !empty($prices['salesPrice']) ? $prices['salesPrice'] : $priceBeforeTax;
		$prices['salesPriceTemp'] = $salesPrice;
		$prices['taxAmount'] = $this->roundDisplay($salesPrice - $priceBeforeTax);

		$prices['salesPriceWithDiscount'] = $this->roundDisplay($this->executeCalculation($this->rules['DATax'], $salesPrice));

		$prices['salesPrice'] = !empty($prices['salesPriceWithDiscount']) ? $prices['salesPriceWithDiscount'] : $salesPrice;

		//Okey, this may not the best place, but atm we handle the override price as salesPrice
		if ($this->override) {
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

		$prices['variantModification'] = $variant;

		return $prices;
	}

	private function fillVoidPrices() {

		if (!isset($prices['basePrice']))
		$prices['basePrice'] = null;
		if (!isset($prices['basePriceVariant']))
		$prices['basePriceVariant'] = null;
		if (!isset($prices['basePriceWithTax']))
		$prices['basePriceWithTax'] = null;
		if (!isset($prices['discountedPriceWithoutTax']))
		$prices['discountedPriceWithoutTax'] = null;
		if (!isset($prices['priceBeforeTax']))
		$prices['priceBeforeTax'] = null;
		if (!isset($prices['taxAmount']))
		$prices['taxAmount'] = null;
		if (!isset($prices['salesPriceWithDiscount']))
		$prices['salesPriceWithDiscount'] = null;
		if (!isset($prices['salesPrice']))
		$prices['salesPrice'] = null;
		if (!isset($prices['discountAmount']))
		$prices['discountAmount'] = null;
		if (!isset($prices['priceWithoutTax']))
		$prices['priceWithoutTax'] = null;
		if (!isset($prices['variantModification']))
		$prices['variantModification'] = null;
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
	public function getCheckoutPrices($cart) {

		$this->_cart = $cart;

		$pricesPerId = array();
		$this->_cartPrices = array();
		$this->_cartData = array();
		$resultWithTax = 0.0;
		$resultWithOutTax = 0.0;

		$this->_cartPrices['basePrice'] = 0;
		$this->_cartPrices['basePriceWithTax'] = 0;
		$this->_cartPrices['discountedPriceWithoutTax'] = 0;
		$this->_cartPrices['salesPrice'] = 0;
		$this->_cartPrices['taxAmount'] = 0;
		$this->_cartPrices['salesPriceWithDiscount'] = 0;
		$this->_cartPrices['discountAmount'] = 0;
		$this->_cartPrices['priceWithoutTax'] = 0;
		$this->_cartPrices['subTotalProducts'] = 0;
		$this->_cartData['duty'] = 1;

		$this->_cartData['payment'] = 0; //could be automatically set to a default set in the globalconfig
		$this->_cartData['paymentName'] = '';
		$cartpaymentTax = 0;

		$this->setCountryState($cart);


		foreach ($cart->products as $name => $product) {
			//$product = $productModel->getProduct($product->virtuemart_product_id,false,false,true);
			$productId = $product->virtuemart_product_id;
			if (empty($product->quantity) || empty($product->virtuemart_product_id)) {
				JError::raiseWarning(710, 'Error the quantity of the product for calculation is 0, please notify the shopowner, the product id ' . $product->virtuemart_product_id);
				continue;
			}

			$variantmods = $this->parseModifier($name);
			$variantmod = $this->calculateModificators($product, $variantmods);

			$cartproductkey = $name; //$product->virtuemart_product_id.$variantmod;
			$product->prices = $pricesPerId[$cartproductkey] = $this->getProductPrices($product, 0, $variantmod, $product->quantity, true, false);
			$this->_cartPrices[$cartproductkey] = $product->prices;

			if($this->_currencyDisplay->_priceConfig['basePrice']) $this->_cartPrices['basePrice'] += $product->prices['basePrice'] * $product->quantity;
			//				$this->_cartPrices['basePriceVariant'] = $this->_cartPrices['basePriceVariant'] + $pricesPerId[$product->virtuemart_product_id]['basePriceVariant']*$product->quantity;
			if($this->_currencyDisplay->_priceConfig['basePriceWithTax']) $this->_cartPrices['basePriceWithTax'] = $this->_cartPrices['basePriceWithTax'] + $product->prices['basePriceWithTax'] * $product->quantity;
			if($this->_currencyDisplay->_priceConfig['discountedPriceWithoutTax']) $this->_cartPrices['discountedPriceWithoutTax'] = $this->_cartPrices['discountedPriceWithoutTax'] + $product->prices['discountedPriceWithoutTax'] * $product->quantity;
			if($this->_currencyDisplay->_priceConfig['salesPrice']) $this->_cartPrices['salesPrice'] = $this->_cartPrices['salesPrice'] + $product->prices['salesPrice'] * $product->quantity;
			if($this->_currencyDisplay->_priceConfig['taxAmount']) $this->_cartPrices['taxAmount'] = $this->_cartPrices['taxAmount'] + $product->prices['taxAmount'] * $product->quantity;
			if($this->_currencyDisplay->_priceConfig['salesPriceWithDiscount']) $this->_cartPrices['salesPriceWithDiscount'] = $this->_cartPrices['salesPriceWithDiscount'] + $product->prices['salesPriceWithDiscount'] * $product->quantity;
			if($this->_currencyDisplay->_priceConfig['discountAmount']) $this->_cartPrices['discountAmount'] = $this->_cartPrices['discountAmount'] - $product->prices['discountAmount'] * $product->quantity;
			if($this->_currencyDisplay->_priceConfig['priceWithoutTax']) $this->_cartPrices['priceWithoutTax'] = $this->_cartPrices['priceWithoutTax'] + $product->prices['priceWithoutTax'] * $product->quantity;

// 			if($this->_currencyDisplay_priceConfig['basePrice']) $this->_cartPrices[$cartproductkey]['subtotal'] = $product->prices['basePrice'] * $product->quantity;
			if($this->_currencyDisplay->_priceConfig['priceWithoutTax']) $this->_cartPrices[$cartproductkey]['subtotal'] = $product->prices['priceWithoutTax'] * $product->quantity;
			if($this->_currencyDisplay->_priceConfig['taxAmount']) $this->_cartPrices[$cartproductkey]['subtotal_tax_amount'] = $product->prices['taxAmount'] * $product->quantity;
			if($this->_currencyDisplay->_priceConfig['discountAmount']) $this->_cartPrices[$cartproductkey]['subtotal_discount'] = - $product->prices['discountAmount'] * $product->quantity;
			if($this->_currencyDisplay->_priceConfig['salesPrice']) $this->_cartPrices[$cartproductkey]['subtotal_with_tax'] = $product->prices['salesPrice'] * $product->quantity;

			//			if(empty($this->_cartPrices['priceWithoutTax'])){ //before tax
			//				$this->_cartPrices['subTotalProducts'] += $product->prices['discountedPriceWithoutTax']*$product->quantity;
			//			} else {
			//				$this->_cartPrices['subTotalProducts'] += $product->prices['basePrice']*$product->quantity;
			//			}
		}

		$this->_cartData['DBTaxRulesBill'] = $DBTaxRules = $this->gatherEffectingRulesForBill('DBTaxBill');
		//		$cBRules = $this->gatherEffectingRulesForCoupon($couponId);
		//
		$shipment_id = empty($cart->virtuemart_shipmentmethod_id) ? 0 : $cart->virtuemart_shipmentmethod_id;

		//$this->calculateShipmentPrice($cart, $shipmentRateId);
		$this->calculateShipmentPrice($cart,  $shipment_id);

		//		$pBRules = $this->gatherEffectingRulesForPayment($paymId);
		$this->_cartData['taxRulesBill'] = $taxRules = $this->gatherEffectingRulesForBill('TaxBill');
		$this->_cartData['DATaxRulesBill'] = $DATaxRules = $this->gatherEffectingRulesForBill('DATaxBill');

		//		$cBRules = $this->gatherEffectingRulesForCoupon();

		$this->_cartPrices['discountBeforeTaxBill'] = $this->roundDisplay($this->executeCalculation($DBTaxRules, $this->_cartPrices['salesPrice']));
		$toTax = !empty($this->_cartPrices['discountBeforeTaxBill']) ? $this->_cartPrices['discountBeforeTaxBill'] : $this->_cartPrices['salesPrice'];

		//We add the price of the Shipment before the tax. The tax per bill is meant for all services. In the other case people should use taxes per
		//  product or method
		$toTax = $toTax + $this->_cartPrices['salesPriceShipment'];

		$this->_cartPrices['withTax'] = $discountWithTax = $this->roundDisplay($this->executeCalculation($taxRules, $toTax, true));
		$toDisc = !empty($this->_cartPrices['withTax']) ? $this->_cartPrices['withTax'] : $toTax;


		$discountAfterTax = $this->roundDisplay($this->executeCalculation($DATaxRules, $toDisc));
		$this->_cartPrices['withTax'] = $this->_cartPrices['discountAfterTax'] = !empty($discountAfterTax) ? $discountAfterTax : $toDisc;

		$paymentId = empty($cart->virtuemart_paymentmethod_id) ? 0 : $cart->virtuemart_paymentmethod_id;
		//$creditId = empty($cart->virtuemart_creditcard_id) ? 0 : $cart->virtuemart_creditcard_id;

		$this->calculatePaymentPrice($cart, $paymentId);

		//		$sub =!empty($this->_cartPrices['discountedPriceWithoutTax'])? $this->_cartPrices['discountedPriceWithoutTax']:$this->_cartPrices['basePrice'];
		if($this->_currencyDisplay->_priceConfig['salesPrice']) $this->_cartPrices['billSub'] = $this->_cartPrices['basePrice'] + $this->_cartPrices['shipmentValue'] + $this->_cartPrices['paymentValue'];
		//		$this->_cartPrices['billSub']  = $sub + $this->_cartPrices['shipmentValue'] + $this->_cartPrices['paymentValue'];
		if($this->_currencyDisplay->_priceConfig['discountAmount']) $this->_cartPrices['billDiscountAmount'] = $this->_cartPrices['discountAmount']  ;
		if($this->_currencyDisplay->_priceConfig['taxAmount']) $this->_cartPrices['billTaxAmount'] = $this->_cartPrices['taxAmount'] + $this->_cartPrices['withTax'] - $toTax + $this->_cartPrices['shipmentTax'] + $this->_cartPrices['paymentTax'];
		if($this->_currencyDisplay->_priceConfig['salesPrice']) $this->_cartPrices['billTotal'] = $this->_cartPrices['salesPricePayment'] + $this->_cartPrices['withTax'];

		// Last step is handling a coupon, if given
		if (!empty($cart->couponCode)) {
			$this->couponHandler($cart->couponCode);
		}

		return $this->_cartPrices;
	}

	/**
	 * Get coupon details and calculate the value
	 * @author Oscar van Eijk
	 * @param $_code Coupon code
	 */
	private function couponHandler($_code) {

		JPluginHelper::importPlugin('vmcoupon');
		$dispatcher = JDispatcher::getInstance();
		$returnValues = $dispatcher->trigger('plgVmCouponHandler', array($_code,&$this->_cartData, &$this->_cartPrices));
		if(!empty($returnValues)){
			foreach ($returnValues as $returnValue) {
				if ($returnValue !== null  ) {
					return $returnValue;
				}
			}
		}

		if (!class_exists('CouponHelper'))
		require(JPATH_VM_SITE . DS . 'helpers' . DS . 'coupon.php');
		if (!($_data = CouponHelper::getCouponDetails($_code))) {
			return; // TODO give some error here
		}
		$_value_is_total = ($_data->percent_or_total == 'total');
		$this->_cartData['couponCode'] = $_code;
		$this->_cartData['couponDescr'] = ($_value_is_total ? '' : (round($_data->coupon_value) . '%')
		);
		$this->_cartPrices['salesPriceCoupon'] = ($_value_is_total ? $_data->coupon_value : ($this->_cartPrices['salesPrice'] * ($_data->coupon_value / 100))
		);
		// TODO Calculate the tax
		$this->_cartPrices['couponTax'] = 0;
		$this->_cartPrices['couponValue'] = $this->_cartPrices['salesPriceCoupon'] - $this->_cartPrices['couponTax'];
		$this->_cartPrices['billTotal'] -= $this->_cartPrices['salesPriceCoupon'];
		if($this->_cartPrices['billTotal'] < 0){
			$this->_cartPrices['billTotal'] = 0.0;
		}
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
	function executeCalculation($rules, $baseprice, $relateToBaseAmount=false) {

		if (empty($rules))return 0;

		$rulesEffSorted = $this->record_sort($rules, 'ordering');

		$price = $baseprice;
		$finalprice = $baseprice;
		if (isset($rulesEffSorted)) {

			foreach ($rulesEffSorted as $rule) {

				if ($relateToBaseAmount) {
					$cIn = $baseprice;
				} else {
					$cIn = $price;
				}
				$cOut = $this->interpreteMathOp($rule['calc_value_mathop'], $rule['calc_value'], $cIn, $rule['calc_currency']);
				$this->_cartPrices[$rule['virtuemart_calc_id'] . 'Diff'] = $this->roundDisplay($this->roundDisplay($cOut) - $cIn);

				//okey, this is a bit flawless logic, but should work
				if ($relateToBaseAmount) {
					$finalprice = $finalprice + $this->_cartPrices[$rule['virtuemart_calc_id'] . 'Diff'];
				} else {
					$price = $cOut;
				}
			}
		}

		//okey done with it
		if (!$relateToBaseAmount) {
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
	function gatherEffectingRulesForProductPrice($entrypoint, $id) {

		if ($id === -1) return ;
		//virtuemart_calc_id 	virtuemart_vendor_id	calc_shopper_published	calc_vendor_published	published 	shared calc_amount_cond
		$countries = '';
		$states = '';
		$shopperGroup = '';

		$testedRules = array();
		//Cant be done with Leftjoin afaik, because both conditions could be arrays.
		foreach ($this->allrules[$entrypoint] as $i => $rule) {
// 			vmdebug('gatherEffectingRulesForProductPrice '.$entrypoint,$this->allrules[$entrypoint]);
			if(!empty($id) && $rule['virtuemart_calc_id']!==$id){
				vmdebug('Price override set '.$id);
				continue;
			}
			if(!isset($this->allrules[$entrypoint][$i]['cats'])){
				$q = 'SELECT `virtuemart_category_id` FROM #__virtuemart_calc_categories WHERE `virtuemart_calc_id`="' . $rule['virtuemart_calc_id'] . '"';
				$this->_db->setQuery($q);
				$this->allrules[$entrypoint][$i]['cats'] = $this->_db->loadResultArray();
			}

			$hitsCategory = true;
			if (isset($this->_cats)) {
				$hitsCategory = $this->testRulePartEffecting($this->allrules[$entrypoint][$i]['cats'], $this->_cats);
			}

			if(!isset($this->allrules[$entrypoint][$i]['shoppergrps'])){
				$q = 'SELECT `virtuemart_shoppergroup_id` FROM #__virtuemart_calc_shoppergroups WHERE `virtuemart_calc_id`="' . $rule['virtuemart_calc_id'] . '"';
				$this->_db->setQuery($q);
				$this->allrules[$entrypoint][$i]['shoppergrps'] = $this->_db->loadResultArray();
			}

			$hitsShopper = true;
			if (isset($this->_shopperGroupId)) {
				$hitsShopper = $this->testRulePartEffecting($this->allrules[$entrypoint][$i]['shoppergrps'], $this->_shopperGroupId);
			}

			if(!isset($this->allrules[$entrypoint][$i]['countries'])){
				$q = 'SELECT `virtuemart_country_id` FROM #__virtuemart_calc_countries WHERE `virtuemart_calc_id`="' . $rule["virtuemart_calc_id"] . '"';
				$this->_db->setQuery($q);
				$this->allrules[$entrypoint][$i]['countries'] = $this->_db->loadResultArray();
			}

			if(!isset($this->allrules[$entrypoint][$i]['states'])){
				$q = 'SELECT `virtuemart_state_id` FROM #__virtuemart_calc_states WHERE `virtuemart_calc_id`="' . $rule["virtuemart_calc_id"] . '"';
				$this->_db->setQuery($q);
				$this->allrules[$entrypoint][$i]['states'] = $this->_db->loadResultArray();
			}

			$hitsDeliveryArea = true;
			if (!empty($this->_deliveryCountry) && !empty($this->allrules[$entrypoint][$i]['countries']) && empty($this->allrules[$entrypoint][$i]['states'])) {
				$hitsDeliveryArea = $this->testRulePartEffecting($this->allrules[$entrypoint][$i]['countries'], $this->_deliveryCountry);
			} else if (!empty($this->_deliveryState) && !empty($this->allrules[$entrypoint][$i]['states'])) {
				$hitsDeliveryArea = $this->testRulePartEffecting($this->allrules[$entrypoint][$i]['states'], $this->_deliveryState);
			}

			$hitsAmount = true;
			if (!empty($this->_amount)) {
				//Test
			}
			if ($hitsCategory && $hitsShopper && $hitsDeliveryArea) {
				if ($this->_debug)
				echo '<br/ >Add rule ForProductPrice ' . $rule["virtuemart_calc_id"];

				$testedRules[] = $rule;
			}
		}

// 		vmdebug('$testedRules before plugins',$testedRules);

		//Test rules in plugins
		if(!empty($testedRules)){
			JPluginHelper::importPlugin('vmcalculation');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('plgVmInGatherEffectRulesProduct',array(&$this,&$testedRules));
		}

// 		vmdebug('$testedRules after plugins',$testedRules);
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
	function gatherEffectingRulesForBill($entrypoint, $cartVendorId=1) {

		//Test if calculation affects the current entry point
		//shared rules counting for every vendor seems to be not necessary
		$q = 'SELECT * FROM #__virtuemart_calcs WHERE
                `calc_kind`="' . $entrypoint . '"
                AND `published`="1"
                AND (`virtuemart_vendor_id`="' . $cartVendorId . '" OR `shared`="1" )
				AND ( publish_up = "' . $this->_db->getEscaped($this->_nullDate) . '" OR publish_up <= "' . $this->_db->getEscaped($this->_now) . '" )
				AND ( publish_down = "' . $this->_db->getEscaped($this->_nullDate) . '" OR publish_down >= "' . $this->_db->getEscaped($this->_now) . '" ) ';
		//			$shoppergrps .  $countries . $states ;
		$this->_db->setQuery($q);
		$rules = $this->_db->loadAssocList();
		$testedRules = array();
		foreach ($rules as $rule) {

			$q = 'SELECT `virtuemart_country_id` FROM #__virtuemart_calc_countries WHERE `virtuemart_calc_id`="' . $rule["virtuemart_calc_id"] . '"';
			$this->_db->setQuery($q);
			$countries = $this->_db->loadResultArray();

			$q = 'SELECT `virtuemart_state_id` FROM #__virtuemart_calc_states WHERE `virtuemart_calc_id`="' . $rule["virtuemart_calc_id"] . '"';
			$this->_db->setQuery($q);
			$states = $this->_db->loadResultArray();

			$hitsDeliveryArea = true;
			if (!empty($countries) && empty($states)) {
				$hitsDeliveryArea = $this->testRulePartEffecting($countries, $this->_deliveryCountry);
			} else if (!empty($states)) {
				$hitsDeliveryArea = $this->testRulePartEffecting($states, $this->_deliveryState);
			}

			//$hitsCountry = $this->testRulePartEffecting($countries,$this->_deliveryCountry);
			//$hitsStates = $this->testRulePartEffecting($states,$this->_deliveryState);

			$q = 'SELECT `virtuemart_shoppergroup_id` FROM #__virtuemart_calc_shoppergroups WHERE `virtuemart_calc_id`="' . $rule["virtuemart_calc_id"] . '"';
			$this->_db->setQuery($q);
			$shoppergrps = $this->_db->loadResultArray();

			$hitsShopper = true;
			if (isset($this->_shopperGroupId)) {
				$hitsShopper = $this->testRulePartEffecting($shoppergrps, $this->_shopperGroupId);
			}

			//			$hitsAmount = 1;
			//			if(!empty($this->_amount)){
			//				//Test
			//			}

				if ($hitsDeliveryArea && $hitsShopper) {
					if ($this->_debug)
					echo '<br/ >Add Checkout rule ' . $rule["virtuemart_calc_id"] . '<br/ >';
					$testedRules[] = $rule;
				}
			}

			//Test rules in plugins
			if(!empty($testedRules)){
				JPluginHelper::importPlugin('vmcalculation');
				$dispatcher = JDispatcher::getInstance();
				$dispatcher->trigger('plgVmInGatherEffectRulesBill', array(&$this, &$testedRules));
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
		 * @author Valerie Isaksen
		 * @param 	$code 	The Id of the coupon
		 * @return 	$rules 	ids of the coupons
		 */
		function calculateShipmentPrice(  $cart, $ship_id) {

			$this->_cartData['shipmentName'] = JText::_('COM_VIRTUEMART_CART_NO_SHIPMENT_SELECTED');
			$this->_cartPrices['shipmentValue'] = 0; //could be automatically set to a default set in the globalconfig
			$this->_cartPrices['shipmentTax'] = 0;
			$this->_cartPrices['shipmentTotal'] = 0;
			$this->_cartPrices['salesPriceShipment'] = 0;
			// check if there is only one possible shipment method

			$automaticSelectedShipment =   $cart->CheckAutomaticSelectedShipment($this->_cartPrices);
			if ($automaticSelectedShipment) $ship_id=$cart->virtuemart_shipmentmethod_id;
			if (empty($ship_id)) return;

			// Handling shipment plugins
			if (!class_exists('vmPSPlugin')) require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');
			JPluginHelper::importPlugin('vmshipment');
			$dispatcher = JDispatcher::getInstance();
			$returnValues = $dispatcher->trigger('plgVmOnSelectedCalculatePrice',array('shipment', $cart, &$this->_cartPrices, &$this->_cartData['shipmentName']  ));

			/*
			* Plugin return true if shipment rate is still valid
			* false if not any more
			*/
			$shipmentValid=0;
			foreach ($returnValues as $returnValue) {
				    $shipmentValid += $returnValue;
			 }
			 if (!$shipmentValid) {
				    $cart->virtuemart_shipmentmethod_id = 0;
				    $cart->setCartIntoSession();
			 }


			return $this->_cartPrices;
		}

		/**
		 * Calculates the effecting Payment prices for the calculation
		 * @todo
		 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
		 * @author Max Milbers
		 * @author Valerie Isaksen
		 * @param 	$code 	The Id of the paymentmethod
		 * @param	$value	amount of the money to transfere
		 * @param	$value	$cartVendorId
		 * @return 	$paymentCosts 	The amount of money the customer has to pay. Calculated in shop currency
		 */
		function calculatePaymentPrice($cart,   $payment_id ) {
			//		if (empty($code)) return 0.0;
			//		$code=4;
			$this->_cartData['paymentName'] = JText::_('COM_VIRTUEMART_CART_NO_PAYMENT_SELECTED');
			$this->_cartPrices['paymentValue'] = 0; //could be automatically set to a default set in the globalconfig
			$this->_cartPrices['paymentTax'] = 0;
			$this->_cartPrices['paymentTotal'] = 0;
			$this->_cartPrices['salesPricePayment'] = 0;

			// check if there is only one possible payment method
			$cart->automaticSelectedPayment =   $cart->CheckAutomaticSelectedPayment( $this->_cartPrices);
			if ($cart->automaticSelectedPayment) $payment_id=$cart->virtuemart_paymentmethod_id;
			if (empty($payment_id)) return;

/*
			// either there is only one payment method, either the old one is still valid
			if (!class_exists('TablePaymentmethods'))
			require(JPATH_VM_ADMINISTRATOR . DS . 'tables' . DS . 'paymentmethods.php');

			$payment = new TablePaymentmethods($this->_db); /// we need that?
			$payment->load($payment_id);
*/

			if (!class_exists('vmPSPlugin')) require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');
			JPluginHelper::importPlugin('vmpayment');
			$dispatcher = JDispatcher::getInstance();
			$returnValues = $dispatcher->trigger('plgVmOnSelectedCalculatePrice',array('payment', $cart, &$this->_cartPrices, &$this->_cartData['paymentName']  ));


			/*
			* Plugin return true if payment plugin is  valid
			* false if not  valid anymore
			* only one value is returned
			*/
			foreach ($returnValues as $returnValue) {
				   if ($returnValue === false) {
				       return;
				   }
			 }
			$paymentValid=0;
			foreach ($returnValues as $returnValue) {
				    $paymentValid += $returnValue;
			 }
			 if (!$paymentValid) {
				    $cart->virtuemart_paymentmethod_id = 0;
				    $cart->setCartIntoSession();
			 }
			return $this->_cartPrices;
		}

		function calculateCustomPriceWithTax($price, $override_id=0) {

			$taxRules = $this->gatherEffectingRulesForProductPrice('Tax', $override_id);
			if(!empty($taxRules)){
				$price = $this->executeCalculation($taxRules, $price, true);
			}

			$price = $this->roundDisplay($price);

			return $price;
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
		function writeRulePartEffectingQuery($data, $field, $setAnd=0) {
			$q = '';
			if (!empty($data)) {
				if ($setAnd) {
					$q = ' AND (';
				} else {
					$q = ' (';
				}
				foreach ($data as $id) {
					$q = $q . '`' . $field . '`="' . $id . '" OR';
				}
				$q = $q . '`' . $field . '`="0" )';
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
		function interpreteMathOp($mathop, $value, $price, $currency='') {

			$coreMathOp = array('+','-','+%','-%');

			if(in_array($mathop,$coreMathOp)){
				$sign = substr($mathop, 0, 1);

				$calculated = false;
				if (strlen($mathop) == 2) {
					$cmd = substr($mathop, 1, 2);
					if ($cmd == '%') {
						$calculated = $price * $value / 100.0;
					}
				} else if (strlen($mathop) == 1){
					$calculated = $this->_currencyDisplay->convertCurrencyTo($currency, $value);
				}

				if($sign == '+'){
					return $price + (float)$calculated;
				} else if($sign == '-'){
					return $price - (float)$calculated;
				} else {
					VmWarn('Unrecognised mathop '.$mathop.' in calculation rule found');
					return $price;
				}
			} else {

				JPluginHelper::importPlugin('vmcalculation');
				$dispatcher = JDispatcher::getInstance();
				$calculated = $dispatcher->trigger('interpreteMathOp', array($this, $mathop, $value, $price, $currency));
				if($calculated){
					foreach($calculated as $calc){
						if($calc) return $calc;
					}
				} else {
					VmWarn('Unrecognised mathop '.$mathop.' in calculation rule found, seems you created this rule with plugin not longer accesible (deactivated, uninstalled?)');
					return $price;
				}
			}

		}

		/**
		 * Standard round function, we round every number with 6 fractionnumbers
		 * We need at least 4 to calculate something like 9.25% => 0.0925
		 * 2 digits
		 * Should be setable via config (just for the crazy case)
		 */
		function roundInternal($value) {
			return round($value, 6);
		}

		/**
		 * Round function for display with 6 fractionnumbers.
		 * For more information please read http://en.wikipedia.org/wiki/Propagation_of_uncertainty
		 * and http://www.php.net/manual/en/language.types.float.php
		 * So in case of € or $ it is rounded in cents
		 * Should be setable via config
		 */
		function roundDisplay($value) {
			return round($value, 2);
		}

		/**
		 * Can test the tablefields Category, Country, State
		 *  If the the data is 0 false is returned
		 */
		function testRulePartEffecting($rule, $data) {

			if (!isset($rule))
			return true;
			if (!isset($data))
			return true;

			if (is_array($rule)) {
				if (count($rule) == 0)
				return true;
			} else {
				$rule = array($rule);
			}
			if (!is_array($data))
			$data = array($data);

			$intersect = array_intersect($rule, $data);
			if ($intersect) {
				return true;
			} else {
				return false;
			}
		}

		/** Sorts indexed 2D array by a specified sub array key
		 *
		 * Copyright richard at happymango dot me dot uk
		 * @author Max Milbers
		 */
		function record_sort($records, $field, $reverse=false) {
			if (is_array($records)) {
				$hash = array();

				foreach ($records as $record) {

					$keyToUse = $record[$field];
					while (array_key_exists($keyToUse, $hash)) {
						$keyToUse = $keyToUse + 1;
					}
					$hash[$keyToUse] = $record;
				}
				($reverse) ? krsort($hash) : ksort($hash);
				$records = array();
				foreach ($hash as $record) {
					$records [] = $record;
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
		 * @param int $virtuemart_product_id the product ID the attribute price should be calculated for
		 * @param array $variantnames the value of the variant
		 * @return array The adjusted price modificator
		 */
		public function calculateModificators($product, $variants) {

			$modificatorSum = 0.0;
			$row = 0;
			foreach ($variants as $variant => $selected) {
				if (!empty($selected)) {
					$query = 'SELECT  C.* , field.*
						FROM `#__virtuemart_customs` AS C
						LEFT JOIN `#__virtuemart_product_customfields` AS field ON C.`virtuemart_custom_id` = field.`virtuemart_custom_id`
						WHERE `virtuemart_product_id` =' . $product->virtuemart_product_id;
					$query .=' and is_cart_attribute = 1 and field.`virtuemart_customfield_id`=' . $selected;
					$this->_db->setQuery($query);
					$productCustomsPrice = $this->_db->loadObject();
					if ($productCustomsPrice->field_type =='E') {
						if(!class_exists('vmCustomPlugin')) require(JPATH_VM_PLUGINS.DS.'vmcustomplugin.php');
						JPluginHelper::importPlugin('vmcustom');
						$dispatcher = JDispatcher::getInstance();
						$dispatcher->trigger('plgVmCalculateCustomVariant',array($product, &$productCustomsPrice,$selected,$row));
					}
					//$app = JFactory::getApplication();
					if (!empty($productCustomsPrice->custom_price)) {
						//TODO adding % and more We should use here $this->interpreteMathOp
						$modificatorSum = $modificatorSum + $productCustomsPrice->custom_price;
					}
					$row++;
				}
			}
			return $modificatorSum;
		}

		public function parseModifier($name) {

			$variants = array();
			if ($index = strpos($name, '::')) {
				$virtuemart_product_id = substr($name, 0, $index);
				$allItems = substr($name, $index + 2);
				$items = explode(';', $allItems);

				foreach ($items as $item) {
					if (!empty($item)) {
						$index2 = strpos($item, ':');
						$variant = substr($item, 0, $index2);
						$selected = substr($item, $index2 + 1);
						$variants[$variant] = $selected;
					}
				}
			}
			return $variants;
		}

}
