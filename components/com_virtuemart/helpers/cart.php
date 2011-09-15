<?php

/**
 *
 * Category model for the cart
 *
 * @package	VirtueMart
 * @subpackage Cart
 * @author RolandD
 * @author Max Milbers
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id$
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');


/**
 * Model class for the cart
 * Very important, use ALWAYS the getCart function, to get the cart from the session
 * @package	VirtueMart
 * @subpackage Cart
 * @author RolandD
 * @author Max Milbers
 */
class VirtueMartCart {

	//	var $productIds = array();
	var $products = array();
	private $_inCheckOut = false;
	private $_dataValidated = false;
	private $_confirmDone = false;
	private $_lastError = null; // Used to pass errmsg to the cart using addJS()
	//todo multivendor stuff must be set in the add function, first product determins ownership of cart, or a fixed vendor is used
	var $vendorId = 1;
	var $lastVisitedCategoryId = 0;
	var $virtuemart_shippingcarrier_id = 0;
	var $virtuemart_paymentmethod_id = 0;
	var $automaticSelectedShipping = false;
	var $automaticSelectedPayment  = false;
	var $BT = 0;
	var $ST = 0;
	var $tosAccepted = null;
	var $customer_comment = '';
	var $couponCode = '';
	var $cartData = null;
	var $lists = null;
// 	var $user = null;
	var $prices = null;

	private static $_cart = null;

	var $useSSL = 1;
	// 	static $first = true;

	private function __construct() {
		$this->useSSL = VmConfig::get('useSSL',0);
		$this->useXHTML = true;
	}

	/**
	 * Get the cart from the session
	 *
	 * @author Max Milbers
	 * @access public
	 * @param array $cart the cart to store in the session
	 */
	public static function getCart($deleteValidation=true,$setCart=true) {

		//What does this here? for json stuff?
		if (!class_exists('JTable')
		)require(JPATH_VM_LIBRARIES . DS . 'joomla' . DS . 'database' . DS . 'table.php');
		JTable::addIncludePath(JPATH_VM_ADMINISTRATOR . DS . 'tables');

		if(empty(self::$_cart)){
			$session = JFactory::getSession();
			$cartSession = $session->get('vmcart', 0, 'vm');

			if (!empty($cartSession)) {
				$cartData = unserialize( $cartSession );

				// 				if(!class_exists('VirtueMartModelProduct')) require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'product.php');
				// 				$productModel = new VirtueMartModelProduct();

				// 				$sessionProducts = array();
				// 				vmdebug('my $cartData CartSessionData ',$cartData);
				// 				foreach($cartData->products as $product){

				// 					$sessionProducts[] = $productModel->getProduct($product);
				// 				}
				// 				$cartData->products = $sessionProducts;

				self::$_cart = new VirtueMartCart;

				self::$_cart->products = $cartData->products;
				// 		echo '<pre>'.print_r($products,1).'</pre>';die;
				self::$_cart->vendorId	 							= $cartData->vendorId;
				self::$_cart->lastVisitedCategoryId	 			= $cartData->lastVisitedCategoryId;
				self::$_cart->virtuemart_shippingcarrier_id	= $cartData->virtuemart_shippingcarrier_id;
				self::$_cart->virtuemart_paymentmethod_id 	= $cartData->virtuemart_paymentmethod_id;
				self::$_cart->automaticSelectedShipping 		= $cartData->automaticSelectedShipping;
				self::$_cart->automaticSelectedPayment 		= $cartData->automaticSelectedPayment;
				self::$_cart->BT 										= $cartData->BT;
				self::$_cart->ST 										= $cartData->ST;
				self::$_cart->tosAccepted 							= $cartData->tosAccepted;
				self::$_cart->customer_comment 					= base64_decode($cartData->customer_comment);
				self::$_cart->couponCode 							= $cartData->couponCode;
				self::$_cart->cartData 								= $cartData->cartData;
				self::$_cart->lists 									= $cartData->lists;
				// 				self::$_cart->user 									= $cartData->user;
				self::$_cart->prices 								= $cartData->prices;

				self::$_cart->_inCheckOut 							= $cartData->_inCheckOut;
				self::$_cart->_dataValidated						= $cartData->_dataValidated;
				self::$_cart->_confirmDone							= $cartData->_confirmDone;

				// 				vmdebug('my cart generated with CartSessionData ',self::$_cart);
				//$cart = unserialize($cartTemp);
				if (!empty(self::$_cart) && $deleteValidation) {
					self::$_cart->setDataValidation();
				}

		}

	}

	if(empty(self::$_cart)){
		self::$_cart = new VirtueMartCart;
	}

	if ( $setCart == true ) {
		self::$_cart->setPreferred();
		self::$_cart->setCartIntoSession();
	}

	return self::$_cart;
	// 		return '';
}
/*
 * Set non product info in object
*/
public function setPreferred() {

	if (!class_exists('VirtueMartModelUser'))
	require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'user.php');
	$usermodel = new VirtueMartModelUser();
	$usermodel->setCurrent();
	$user = $usermodel->getUser();

	if (empty($this->BT) || (!empty($this->BT) && count($this->BT) <=1) ) {
		foreach ($user->userInfo as $address) {
			if ($address->address_type == 'BT') {
				$this->saveAddressInCart((array) $address, $address->address_type,false);
			}
		}
	}

	if (empty($this->virtuemart_shippingcarrier_id) && !empty($user->virtuemart_shippingcarrier_id)) {
		$this->virtuemart_shippingcarrier_id = $user->virtuemart_shippingcarrier_id;
	}

	if (empty($this->virtuemart_paymentmethod_id) && !empty($user->virtuemart_paymentmethod_id)) {
		$this->virtuemart_paymentmethod_id = $user->virtuemart_paymentmethod_id;
	}

	if(isset($user->agreed) && !VmConfig::get('agree_to_tos_onorder') && $this->tosAccepted===null){
		$this->tosAccepted = $user->agreed;
	}
}

/**
 * Set the cart in the session
 *
 * @author RolandD
 *
 * @access public
 * @param array $cart the cart to store in the session
 */
public function setCartIntoSession() {

	$session = JFactory::getSession();

	$sessionCart = new stdClass();
	// 		vmdebug('setCartIntoSession ids',$this);

	$products = array();
        if ($this->products) {
            foreach($this->products as $key =>$product){
                    $product->prices = null;

            }
        }
	// 		$sessionCart->products = $products;
	$sessionCart->products = $this->products;
	// 		echo '<pre>'.print_r($products,1).'</pre>';die;
	$sessionCart->vendorId	 							= $this->vendorId;
	$sessionCart->lastVisitedCategoryId	 			= $this->lastVisitedCategoryId;
	$sessionCart->virtuemart_shippingcarrier_id	= $this->virtuemart_shippingcarrier_id;
	$sessionCart->virtuemart_paymentmethod_id 	= $this->virtuemart_paymentmethod_id;
	$sessionCart->automaticSelectedShipping 		= $this->automaticSelectedShipping;
	$sessionCart->automaticSelectedPayment 		= $this->automaticSelectedPayment;
	$sessionCart->BT 										= $this->BT;
	$sessionCart->ST 										= $this->ST;
	$sessionCart->tosAccepted 							= $this->tosAccepted;
	$sessionCart->customer_comment 					= base64_encode($this->customer_comment);
	$sessionCart->couponCode 							= $this->couponCode;
	$sessionCart->cartData 								= $this->cartData;
	$sessionCart->lists 									= $this->lists;
	// 		$sessionCart->user 									= $this->user;
	$sessionCart->prices 								= $this->prices;

	//private variables
	$sessionCart->_inCheckOut 							= $this->_inCheckOut;
	$sessionCart->_dataValidated						= $this->_dataValidated;
	$sessionCart->_confirmDone							= $this->_confirmDone;


	// 		[_inCheckOut:VirtueMartCart:private] =>
	// 		[_dataValidated:VirtueMartCart:private] =>
	// 		[_confirmDone:VirtueMartCart:private] =>
	// 		[_lastError:VirtueMartCart:private] =>
	// 		[_now] => 2011-09-06 14:56:37


	// 		$session->set('vmcart', base64_encode(serialize($this)),'vm');
	$session->set('vmcart', serialize($sessionCart),'vm');

	// Idea to save only a Part of object in session($this->cart)
	// $session->set('vmcart', base64_encode(serialize($this->cart)),'vm');
	// $session->set('vmcart', serialize($this), 'vm');

}

/**
 * Remove the cart from the session
 *
 * @author Max Milbers
 * @access public
 */
public function removeCartFromSession() {
	$session = JFactory::getSession();
	$session->set('vmcart', 0, 'vm');
}

public function setDataValidation($valid=false) {
	$this->_dataValidated = $valid;
	// 		$this->setCartIntoSession();
}

public function getDataValidated() {
	return $this->_dataValidated;
}

public function getInCheckOut() {
	return $this->_inCheckOut;
}

/**
 * Set the last error that occured.
 * This is used on error to pass back to the cart when addJS() is invoked.
 * @param string $txt Error message
 * @author Oscar van Eijk
 */
private function setError($txt) {
	$this->_lastError = $txt;
}

/**
 * Retrieve the last error message
 * @return string The last error message that occured
 * @author Oscar van Eijk
 */
public function getError() {
	return ($this->_lastError);
}

/**
 * Add a product to the cart
 *
 * @author RolandD
 * @author Max Milbers
 * @access public
 */
public function add($virtuemart_product_ids=null) {
	$mainframe = JFactory::getApplication();

	$post = JRequest::get('default');
	//$total_quantity = 0;
	//$total_updated = 0;
	//$total_deleted = 0;
	if(empty($virtuemart_product_ids)){
		$virtuemart_product_ids = JRequest::getVar('virtuemart_product_id', array(), 'default', 'array'); //is sanitized then
	}

	if (empty($virtuemart_product_ids)) {
		$mainframe->enqueueMessage(JText::_('COM_VIRTUEMART_CART_ERROR_NO_PRODUCT_IDS', false));
		return false;
	}

	// 		if (!class_exists('calculationHelper')
	// 		)require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'calculationh.php');
	//  if (empty ($this->cartData->products))$this->cartData->products = array();
	//Iterate through the prod_id's and perform an add to cart for each one
	foreach ($virtuemart_product_ids as $p_key => $virtuemart_product_id) {

		$tmpProduct = $this->getProduct((int) $virtuemart_product_id);
		//			dump($tmpProduct,'my product add to cart before');
		// trying to save some space in the session table
		$product = new stdClass();
		$product -> virtuemart_manufacturer_id = $tmpProduct -> virtuemart_manufacturer_id;
		$product -> mf_name = $tmpProduct -> mf_name;
		$product -> slug = $tmpProduct -> slug;
		$product -> mf_desc = $tmpProduct -> mf_desc;
		$product -> mf_url = $tmpProduct -> mf_url;
		$product -> published = $tmpProduct -> published;
		// 			$product -> created_on = $tmpProduct -> created_on;
		// 			$product -> created_by = $tmpProduct -> created_by;
		// 			$product -> modified_on = $tmpProduct -> modified_on;
		// 			$product -> modified_by = $tmpProduct -> modified_by;

		$product -> virtuemart_product_price_id = $tmpProduct -> virtuemart_product_price_id;
		$product -> virtuemart_product_id = $tmpProduct -> virtuemart_product_id;
		$product -> virtuemart_shoppergroup_id = $tmpProduct -> virtuemart_shoppergroup_id;
		$product -> product_price = $tmpProduct -> product_price;
		$product -> override = $tmpProduct -> override;
		$product -> product_override_price = $tmpProduct -> product_override_price;

		$product -> product_tax_id = $tmpProduct -> product_tax_id;
		$product -> product_discount_id = $tmpProduct -> product_discount_id;
		$product -> product_currency = $tmpProduct -> product_currency;
		$product -> product_price_vdate = $tmpProduct -> product_price_vdate;
		$product -> product_price_edate = $tmpProduct -> product_price_edate;
		$product -> virtuemart_vendor_id = $tmpProduct -> virtuemart_vendor_id;
		$product -> product_parent_id = $tmpProduct -> product_parent_id;
		$product -> product_sku = $tmpProduct -> product_sku;
		$product -> product_name = $tmpProduct -> product_name;
		$product -> product_s_desc = $tmpProduct -> product_s_desc;
		//			$product -> product_desc = $tmpProduct -> product_desc;

		$product -> product_weight = $tmpProduct -> product_weight;
		$product -> product_weight_uom = $tmpProduct -> product_weight_uom;
		$product -> product_length = $tmpProduct -> product_length;
		$product -> product_width = $tmpProduct -> product_width;
		$product -> product_lwh_uom = $tmpProduct -> product_lwh_uom;
		$product -> product_url = $tmpProduct -> product_url;
		$product -> product_in_stock = $tmpProduct -> product_in_stock;
		$product -> product_ordered = $tmpProduct -> product_ordered;
		// 			$product -> low_stock_notification = $tmpProduct -> low_stock_notification;
		// 			$product -> product_available_date = $tmpProduct -> product_available_date;
		$product -> ship_code_id = $tmpProduct -> ship_code_id;
		$product -> product_sales = $tmpProduct -> product_sales;
		$product -> product_unit = $tmpProduct -> product_unit;
		$product -> product_packaging = $tmpProduct -> product_packaging;
		$product -> product_order_levels = $tmpProduct -> product_order_levels;
		$product -> virtuemart_media_id = $tmpProduct -> virtuemart_media_id;

		if(!empty($tmpProduct -> image)) $product -> image =  $tmpProduct -> image;

		$product -> categories = $tmpProduct -> categories;
		$product -> virtuemart_category_id = $tmpProduct -> virtuemart_category_id;
		$product -> category_name = $tmpProduct -> category_name;
		$product -> canonical = $tmpProduct -> canonical;
		$product -> link = $tmpProduct -> link;
		$product -> packaging = $tmpProduct -> packaging;
		$product -> customfields = empty($tmpProduct -> customfields)? array():$tmpProduct -> customfields ;
		$product -> customfieldsCart = empty($tmpProduct -> customfieldsCart)? array(): $tmpProduct -> customfieldsCart;
		$product -> customsChilds = empty($tmpProduct -> customsChilds)? array(): $tmpProduct -> customsChilds;

		//			echo '<pre>'.print_r($tmpProduct,1).'<pre>';
		//			die;
		//			$product = $tmpProduct;

		//			vmdebug('my product add to cart after',$product);
		//TODO Why reloading the product wiht same name $product ?
		// passed all from $tmpProduct and relaoding it second time ????
		// $tmpProduct = $this->getProduct((int) $virtuemart_product_id); seee before !!!
		// $product = $this->getProduct((int) $virtuemart_product_id);
		/* Check if we have a product */
		if ($product) {
			$quantityPost = (int) $post['quantity'][$p_key];

			if(!empty( $post['virtuemart_category_id'][$p_key])){
				$virtuemart_category_idPost = (int) $post['virtuemart_category_id'][$p_key];
				$product->virtuemart_category_id = $virtuemart_category_idPost;
			}

			// $virtuemart_category_idPost = (int) $post['virtuemart_category_id'][$p_key];


			$productKey = $product->virtuemart_product_id;
			// INDEX NOT FOUND IN JSON HERE
			// changed name field you know exactly was this is
			if (isset($post['customPrice'])) {
				$product->customPrices = $post['customPrice'];
				$productKey.= '::';
				foreach ($product->customPrices as $customPrice) {
					foreach ($customPrice as $customId => $custom_fieldId) {

						if ( is_array($custom_fieldId) ) {
							foreach ($custom_fieldId as $userfieldId => $userfield) {
								$productKey .= $customId . ':' . $userfieldId . ';';
								$product->userfield[$customId . '-' . $userfieldId] = $userfield;
							}
						} else {
							$productKey .= $customId . ':' . $custom_fieldId . ';';
						}

					}
				}

			}
			// print_r($product->customPrices);
			// print_r($product->userfield);
			
			// jExit();

			if (array_key_exists($productKey, $this->products)) {
				$this->products[$productKey]->quantity += $quantityPost;
				if ($this->checkForQuantities($product, $this->products[$productKey]->quantity)) {
					$mainframe->enqueueMessage(JText::_('COM_VIRTUEMART_CART_PRODUCT_UPDATED'));
				} else {
					return false;
				}
			} else {
				$this->products[$productKey] = $product;
				$product->quantity = $quantityPost;
				if ($this->checkForQuantities($product, $quantityPost)) {
					$mainframe->enqueueMessage(JText::_('COM_VIRTUEMART_CART_PRODUCT_ADDED'));
				} else {
					return false;
				}
			}
		} else {
			$mainframe->enqueueMessage(JText::_('COM_VIRTUEMART_PRODUCT_NOT_FOUND', false));
			return false;
		}
	}

	// End Iteration through Prod id's
	$this->setCartIntoSession();
	return true;
}

/**
 * Remove a product from the cart
 *
 * @author RolandD
 * @param array $cart_id the cart IDs to remove from the cart
 * @access public
 */
public function removeProductCart($prod_id=0) {
	/* Check for cart IDs */
	if (empty($prod_id))
	$prod_id = JRequest::getVar('cart_virtuemart_product_id');
	//		$prod_id = JRequest::get();
	//		/* Check if the product ID is ok */
	//		if (!$prod_ids || !is_array($prod_ids) || empty($prod_ids)) return;
	//
	//		if (empty($prod_variants)) $cart_ids = array(JRequest::getInt('variants'));
	//		if (!$prod_variants || !is_array($prod_variants) || empty($prod_variants)) return;
	//
	//		if (empty($prod_customvariants)) $cart_ids = array(JRequest::getInt('customvariants'));
	//		if (!$prod_customvariants || !is_array($prod_customvariants) || empty($prod_customvariants)) return;
	//
	//		/* Load the cart */
	//		$cart = $this->getCart();
	//
	//		/* Remove the product */
	//		foreach ($cart_ids as $cart_id) {
	//			'P'.$product->virtuemart_product_id.$product->variants.$product->customvariants
		unset($this->products[$prod_id]);
		//		}

		/* Save the cart */
		$this->setCartIntoSession();
		return true;
	}

	/**
	 * Update a product in the cart
	 *
	 * @author Max Milbers
	 * @param array $cart_id the cart IDs to remove from the cart
	 * @access public
	 */
	public function updateProductCart($cart_virtuemart_product_id=0) {

		if (empty($cart_virtuemart_product_id))
		$cart_virtuemart_product_id = JRequest::getInt('cart_virtuemart_product_id');
		if (empty($quantity))
		$quantity = JRequest::getInt('quantity');

		//		foreach($cart_virtuemart_product_ids as $cart_virtuemart_product_id){
		if (array_key_exists($cart_virtuemart_product_id, $this->products)) {
			if (!empty($quantity)) {
				if ($this->checkForQuantities($this->products[$cart_virtuemart_product_id], $quantity)) {
					$this->products[$cart_virtuemart_product_id]->quantity = $quantity;
					$updated = true;
				}
			} else {
				//Todo when quantity is 0,  the product should be removed, maybe necessary to gather in array and execute delete func
				unset($this->products[$cart_virtuemart_product_id]);
				$updated = true;
			}
		}
		//		}

		/* Save the cart */
		$this->setCartIntoSession();
		if ($updated)
		return true;
		else
		return false;
	}

	/**
	 * Function Description
	 *
	 * @author Max Milbers
	 * @access public
	 * @param array $cart the cart to get the products for
	 * @return array of product objects
	 */
	public function getCartPrices() {
		if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');
		$calculator = calculationHelper::getInstance();
		$prices = $calculator->getCheckoutPrices($this);
		//$this->cartData->cartData = $calculator->getCartData();
		// $this->setCartIntoSession();

		return $prices;
	}

	/**
	 * Proxy function for getting a product object
	 *
	 * @author Max Milbers
	 * @todo Find out if the include path belongs here? For now it works.
	 * @param int $virtuemart_product_id The product ID to get the object for
	 * @return object The product details object
	 */
	private function getProduct($virtuemart_product_id) {
		JModel::addIncludePath(JPATH_VM_ADMINISTRATOR . DS . 'models');
		$model = JModel::getInstance('Product', 'VirtueMartModel');
		$product = $model->getProduct($virtuemart_product_id, true, false);
		if ( VmConfig::get('oncheckout_show_images')){

			$db =& JFactory::getDBO();
			$db->setQuery('SELECT * from #__virtuemart_medias where virtuemart_media_id='. $product->virtuemart_media_id[0] );
			$data = $db->loadObject();
			$product->image = VmMediaHandler::createMedia($data,'product');

		}
		return $product;
	}


	/**
	 * Get the category ID from a product ID
	 *
	 * @author RolandD
	 * @access public
	 * @return mixed if found the category ID else null
	 * @deprecated
	 */
	public function getCategoryId() {
		$db = JFactory::getDBO();
		$virtuemart_product_id = JRequest::getInt('virtuemart_product_id', 0);
		$q = 'SELECT `virtuemart_category_id` FROM `#__virtuemart_product_categories` WHERE `virtuemart_product_id` = ' . (int) $virtuemart_product_id . ' LIMIT 1';
		$db->setQuery($q);
		return $db->loadResult();
	}

	/**

	* Get the category ID from a product ID
	*
	* @author Patrick Kohl
	* @access public
	* @return mixed if found the category ID else null
	*/
	public function getCardCategoryId($virtuemart_product_id) {
		$db = JFactory::getDBO();
		$q = 'SELECT `virtuemart_category_id` FROM `#__virtuemart_product_categories` WHERE `virtuemart_product_id` = ' . (int) $virtuemart_product_id . ' LIMIT 1';
		$db->setQuery($q);
		return $db->loadResult();
	}

	/**
	 * Checks if the quantity is correct
	 *
	 * @author Max Milbers
	 */
	private function checkForQuantities($product, $quantity=0) {

		$mainframe = JFactory::getApplication();
		/* Check for a valid quantity */
		if (!preg_match("/^[0-9]*$/", $quantity)) {
			$error = JText::_('COM_VIRTUEMART_CART_ERROR_NO_VALID_QUANTITY', false);
			//			$this->_error[] = 'Quantity was not a number';
			$this->setError($error);
			$mainframe->enqueueMessage();
			return false;
		}

		/* Check for negative quantity */
		if ($quantity < 0) {
			//			$this->_error[] = 'Quantity under zero';
			$error = JText::_('COM_VIRTUEMART_CART_ERROR_NO_NEGATIVE', false);
			$this->setError($error);
			$mainframe->enqueueMessage($error);
			return false;
		}

		/* Check for the minimum and maximum quantities */
		list($min, $max) = explode(',', $product->product_order_levels);
		if ($min != 0 && $quantity < $min) {
			//			$this->_error[] = 'Quantity reached not minimum';
			$error = JText::sprintf('COM_VIRTUEMART_CART_MIN_ORDER', $min);
			$this->setError($error);
			$mainframe->enqueueMessage($error, 'error');
			return false;
		}
		if ($max != 0 && $quantity > $max) {
			//			$this->_error[] = 'Quantity reached over maximum';
			$error = JText::sprintf('COM_VIRTUEMART_CART_MAX_ORDER', $max);
			$this->setError($error);
			$mainframe->enqueueMessage($error, 'error');
			return false;
		}

		// 			$ci = 0;


		/* Check to see if checking stock quantity */
		if (VmConfig::get('check_stock', false)) {
			$request_stock = array();
			if ($quantity > ($product->product_in_stock - $product->product_ordered) ){

				$error = JText::_('COM_VIRTUEMART_CART_PRODUCT_OUT_OF_STOCK');
				$this->setError($error); // Private error retrieved with getError is used only by addJS, so only the latest is fine
				vmInfo($error,$product->product_name);
				return false;
			}
			/*					// Create an array for out of stock items and continue to next item
			 $request_stock[$ci]['virtuemart_product_id'] = $product->virtuemart_product_id;
			$request_stock[$ci]['quantity'] = $quantity;
			// 					$ci++;
			//				$this->_error[] = 'Quantity reached stock limit '.$product->virtuemart_product_id;
			continue;
			}

			if (count($request_stock) != 0) {
			foreach ($request_stock as $rstock) {
			$error = JText::_('COM_VIRTUEMART_CART_PRODUCT_OUT_OF_STOCK');
			$this->setError($error); // Private error retrieved with getError is used only by addJS, so only the latest is fine
			$mainframe->enqueueMessage($error, 'error');
			}
			return false;
			}*/
		}

		return true;
	}

	function confirmDone() {

		$this->checkoutData();
		if ($this->_dataValidated) {
			$this->_confirmDone = true;
			$this->confirmedOrder();
		} else {
			$mainframe = JFactory::getApplication();
			$mainframe->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart'), JText::_('COM_VIRTUEMART_CART_CHECKOUT_DATA_NOT_VALID'));
		}
	}

	function checkout() {

		if ($this->checkoutData()) {
			$mainframe = JFactory::getApplication();
			//This is dangerous, we may add it as option, direclty calling the confirm is in most countries illegal and can lead to confusion. notice by Max
			// 			$mainframe->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart&task=confirm'), JText::_('COM_VIRTUEMART_CART_CHECKOUT_DONE_CONFIRM_ORDER'));
			$mainframe->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart'), JText::_('COM_VIRTUEMART_CART_CHECKOUT_DONE_CONFIRM_ORDER'));
		}
	}

	/**
	 * Validate the coupon code. If ok,. set it in the cart
	 * @param string $coupon_code Coupon code as entered by the user
	 * @author Oscar van Eijk
	 * TODO Change the coupon total/used in DB ?
	 * @access public
	 * @return string On error the message text, otherwise an empty string
	 */
	public function setCouponCode($coupon_code) {
		if (!class_exists('CouponHelper')) {
			require(JPATH_VM_SITE . DS . 'helpers' . DS . 'coupon.php');
		}
		$prices = $this->getCartPrices();
		$msg = CouponHelper::ValidateCouponCode($coupon_code, $prices['salesPrice']);
		if (!empty($msg)) {
			$this->couponCode = '';
			$this->setCartIntoSession();
			return $msg;
		}
		$this->couponCode = $coupon_code;
		$this->setCartIntoSession();
		return '';
	}

	/**
	 * Check the selected shipper data and store the info in the cart
	 * @param integer $shipper_id Shipper ID taken from the form data
	 * @author Oscar van Eijk
	 */
	public function setShipper($shipper_id) {

		if (!class_exists('vmShipperPlugin'))
		require(JPATH_VM_SITE . DS . 'helpers' . DS . 'vmshipperplugin.php');
		JPluginHelper::importPlugin('vmshipper');

		$dispatcher = JDispatcher::getInstance();
		$retValues = $dispatcher->trigger('plgVmOnShipperSelected',
		array('cart' => $this, '_selectedShipper' => $shipper_id));
		foreach ($retValues as $retVal) {
			if ($retVal === true) {
				$this->virtuemart_shippingcarrier_id = $shipper_id;
				$this->setCartIntoSession();
				break; // Plugin completed succesful; nothing else to do
			} elseif ($retVal === false) {
				// Missing data, ask for it (again)
				$mainframe->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart&task=edit_shipping',$this->useXHTML,$this->useSSL));
				//	Remove comments if newchecks need to be implemented.
				//	NOTE: inactive plugins will always return null, so that value cannot be used for anything else!
			}
		}

		//$this->virtuemart_shippingcarrier_id=$shipper_id;
	}

	public function setPaymentMethod($virtuemart_paymentmethod_id) {
		$this->virtuemart_paymentmethod_id = $virtuemart_paymentmethod_id;
		$this->setCartIntoSession();
	}

	private function checkoutData() {

		$this->_inCheckOut = true;

		$this->tosAccepted = JRequest::getInt('tosAccepted', $this->tosAccepted);
		$this->customer_comment = JRequest::getWord('customer_comment', $this->customer_comment);

		if (($this->selected_shipto = JRequest::getVar('shipto', null)) !== null) {
			JModel::addIncludePath(JPATH_VM_ADMINISTRATOR . DS . 'models');
			$userModel = JModel::getInstance('user', 'VirtueMartModel');
			$stData = $userModel->getUserAddressList(0, 'ST', $this->selected_shipto);
			$this->validateUserData('ST', $stData[0]);
		}

		$this->setCartIntoSession();

		$mainframe = JFactory::getApplication();
		if (count($this->products) == 0) {
			$mainframe->redirect(JRoute::_('index.php?option=com_virtuemart'), JText::_('COM_VIRTUEMART_CART_NO_PRODUCT'));
		} else {
			foreach ($this->products as $product) {
				$redirectMsg = $this->checkForQuantities($product, $product->quantity);
				if (!$redirectMsg) {
					//					$this->setCartIntoSession();
					$mainframe->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart'), $redirectMsg);
				}
			}
		}

		// Check if a minimun purchase value is set
		if (($msg = $this->checkPurchaseValue()) != null) {
			$mainframe->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart'), $msg);
		}

		//But we check the data again to be sure
		if (empty($this->BT)) {
			$mainframe->redirect(JRoute::_('index.php?option=com_virtuemart&view=user&task=editaddresscheckout&addrtype=BT') );
		} else {
			$redirectMsg = self::validateUserData();
			if ($redirectMsg) {
				$mainframe->redirect(JRoute::_('index.php?option=com_virtuemart&view=user&task=editaddresscheckout&addrtype=BT'), $redirectMsg);
			}
		}
		//Only when there is an ST data, test if all necessary fields are filled
		if (!empty($this->ST)) {
			$redirectMsg = self::validateUserData('ST');
			if ($redirectMsg) {
				//				$this->setCartIntoSession();
				$mainframe->redirect(JRoute::_('index.php?option=com_virtuemart&view=user&task=editaddresscheckout&addrtype=ST'), $redirectMsg);
			}
		}

		// Test Coupon
		if (!empty($this->couponCode)) {
			$prices = $this->getCartPrices();
			if (!class_exists('CouponHelper')) {
				require(JPATH_VM_SITE . DS . 'helpers' . DS . 'coupon.php');
			}
			$redirectMsg = CouponHelper::ValidateCouponCode($this->couponCode, $prices['salesPrice']);
			if (!empty($redirectMsg)) {
				$this->couponCode = '';
				//				$this->setCartIntoSession();
				$mainframe->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart&task=edit_coupon',$this->useXHTML,$this->useSSL), $redirectMsg);
			}
		}

		//Test Shipment and show shipment plugin
		if (empty($this->virtuemart_shippingcarrier_id)) {
			$mainframe->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart&task=edit_shipping',$this->useXHTML,$this->useSSL), $redirectMsg);
		} else {
			if (!class_exists('vmShipperPlugin'))
			require(JPATH_VM_SITE . DS . 'helpers' . DS . 'vmshipperplugin.php');
			JPluginHelper::importPlugin('vmshipper');
			//Add a hook here for other shipment methods, checking the data of the choosed plugin
			$dispatcher = JDispatcher::getInstance();
			$retValues = $dispatcher->trigger('plgVmOnCheckoutCheckShipperData', array('cart' => $this));

			foreach ($retValues as $retVal) {
				if ($retVal === true) {
					break; // Plugin completed succesful; nothing else to do
				} elseif ($retVal === false) {
					// Missing data, ask for it (again)
					$mainframe->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart&task=edit_shipping',$this->useXHTML,$this->useSSL), $redirectMsg);
					// 	NOTE: inactive plugins will always return null, so that value cannot be used for anything else!
				}
			}
		}
		//echo 'hier ';
		//Test Payment and show payment plugin
		if (empty($this->virtuemart_paymentmethod_id)) {
			$mainframe->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart&task=editpayment',$this->useXHTML,$this->useSSL), $redirectMsg);
		} else {
			if (!class_exists('vmPaymentPlugin'))
			require(JPATH_VM_SITE . DS . 'helpers' . DS . 'vmpaymentplugin.php');
			JPluginHelper::importPlugin('vmpayment');
			//Add a hook here for other payment methods, checking the data of the choosed plugin
			$dispatcher = JDispatcher::getInstance();
			$retValues = $dispatcher->trigger('plgVmOnCheckoutCheckPaymentData', array('cart' => $this));

			foreach ($retValues as $retVal) {
				if ($retVal === true) {
					break; // Plugin completed succesful; nothing else to do
				} elseif ($retVal === false) {
					// Missing data, ask for it (again)
					$mainframe->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart&task=editpayment',$this->useXHTML,$this->useSSL), $redirectMsg);
					// 	NOTE: inactive plugins will always return null, so that value cannot be used for anything else!
				}
			}
		}

		if ($this->tosAccepted !== 1) {
			$mainframe->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart'), JText::_('COM_VIRTUEMART_CART_PLEASE_ACCEPT_TOS'));
		}
		/* 		if(VmConfig::get('agree_to_tos_onorder') && !$this->tosAccepted) {
		 } */

		//Show cart and checkout data overview
		$this->_inCheckOut = false;
		$this->_dataValidated = true;

		$this->setCartIntoSession();

		return true;
	}

	/**
	 * Check if a minimum purchase value for this order has been set, and if so, if the current
	 * value is equal or hight than that value.
	 * @author Oscar van Eijk
	 * @return An error message when a minimum value was set that was not eached, null otherwise
	 */
	private function checkPurchaseValue() {
		if (!class_exists('VirtueMartModelVendor'))
		require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'vendor.php');
		$vendor = new VirtueMartModelVendor();
		$vendor->setId($this->vendorId);
		$store = $vendor->getVendor();
		if ($store->vendor_min_pov > 0) {
			$prices = $this->getCartPrices();
			if ($prices['salesPrice'] < $store->vendor_min_pov) {
				if (!class_exists('CurrencyDisplay'))
				require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');
				$currency = CurrencyDisplay::getInstance();
				$minValue = $currency->priceDisplay($min);
				return JText::sprintf('COM_VIRTUEMART_CART_MIN_PURCHASE', $currency->priceDisplay($store->vendor_min_pov));
			}
		}
		return null;
	}

	/**
	 * Test userdata if valid
	 *
	 * @author Max Milbers
	 * @param String if BT or ST
	 * @param Object If given, an object with data address data that must be formatted to an array
	 * @return redirectMsg, if there is a redirectMsg, the redirect should be executed after
	 */
	private function validateUserData($type='BT', $obj = null) {

		if (!class_exists('VirtueMartModelUserfields'))
		require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'userfields.php');
		$userFieldsModel = new VirtueMartModelUserfields();

		if ($type == 'BT')
		$fieldtype = 'account'; else
		$fieldtype = 'shipping';

		$neededFields = $userFieldsModel->getUserFields(
		$fieldtype
		, array('required' => true, 'delimiters' => true, 'captcha' => true, 'system' => false)
		, array('delimiter_userinfo', 'name','username', 'password', 'password2', 'address_type_name', 'address_type', 'user_is_vendor', 'agreed'));

		$redirectMsg = false;

		$i = 0 ;

		/*		if(empty($obj)){
			$obj = $this->$type;
		}*/

		foreach ($neededFields as $field) {

			if($field->required && empty($this->{$type}[$field->name]) && $field->name != 'virtuemart_state_id'){
				$redirectMsg = JText::sprintf('COM_VIRTUEMART_MISSING_VALUE_FOR_FIELD',$field->name) ;
				$i++;
				//more than four fields missing, this is not a normal error (should be catche by js anyway, so show the address again.
				if($i>2 && $type=='BT'){
					$redirectMsg = JText::_('COM_VIRTUEMART_CHECKOUT_PLEASE_ENTER_ADDRESS');
				}
			}

			if ($obj !== null && is_array($this->{$type})) {
				$this->{$type}[$field->name] = $obj->{$field->name};
			}

			//This is a special test for the virtuemart_state_id. There is the speciality that the virtuemart_state_id could be 0 but is valid.
			if ($field->name == 'virtuemart_state_id') {
				if (!class_exists('VirtueMartModelState')) require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'state.php');
				if(!empty($this->{$type}['virtuemart_country_id']) && !empty($this->{$type}['virtuemart_state_id']) ){
					if (!$msg = VirtueMartModelState::testStateCountry($this->{$type}['virtuemart_country_id'], $this->{$type}['virtuemart_state_id'])) {
						$redirectMsg = $msg;
					}
				}

			}

		}
		/*            if (empty($this->{$type}[$field->name]) && $field->name != 'virtuemart_state_id') {
		 $app = JFactory::getApplication();
		$app->enqueueMessage('NOTICE: Enter for "' . $type . '" "' . $field->name . '" title: ' . JText::_($field->title) . ' and value: ' . $this->{$type}[$field->name] . ' but ' . $this->BT['first_name']);
		//$redirectMsg = 'Enter for "' . $type . '" "' . $field->name . '" title: ' . JText::_($field->title) . ' and value: ' . $this->{$type}[$field->name] . ' but ' . $this->BT['first_name'];
		} else {*/

		//We may add here further Tests. Like if the email has the form a@b.xxx and so on
		//             }
		// 			} else {
		// 				if($type=='ST')
		// 				$redirectMsg = 'No '.$type.' address set';
		// 			}

		return $redirectMsg;
	}

	/**
	 * This function is called, when the order is confirmed by the shopper.
	 *
	 * Here are the last checks done by payment plugins.
	 * The mails are created and send to vendor and shopper
	 * will show the orderdone page (thank you page)
	 *
	 */
	private function confirmedOrder() {

		//Just to prevent direct call
		if ($this->_dataValidated && $this->_confirmDone) {
			if (!class_exists('VirtueMartModelOrders'))
			require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );

			$order = new VirtueMartModelOrders();
			if (($orderID = $order->createOrderFromCart($this)) === false) {
				$mainframe = JFactory::getApplication();
				JError::raiseWarning(500, $order->getError());
				$mainframe->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart') );
			}
			$this->virtuemart_order_id = $orderID;
                        // Email sent when Payment reposnse received
			//$this->sentOrderConfirmedEmail($order->getOrder($orderID));

			//We delete the old stuff
			$this->products = array();
			$this->_inCheckOut = false;
			$this->_dataValidated = false;
			$this->_confirmDone = false;
			$this->customer_comment = '';
			$this->couponCode = '';
			$this->tosAccepted = false;

			$this->setCartIntoSession();

			// TODO valerie TO DO  -- not finished
			$cart = $this->getCart();
			$dispatcher = JDispatcher::getInstance();

			$returnValues = $dispatcher->trigger('plgVmAfterCheckoutDoPayment', array($orderID, $cart));

			// may be redirect is done by the payment plugin (eg: paypal) so we do not come back here
			// if payment plugin echos a form, false = nothing happen, true= echo form ,

			// 			$activeplugin = false;
			// 			foreach ($returnValues as $returnValue) {
			// 				if ($returnValue == 'P' || ) {
				// 					$order->handleStockAfterStatusChanged($returnValue);
				// 					$activeplugin = true;
				// 					break;
				// 				}
				// 				Returnvalue 'null' must be ignored; it's an inactive plugin so look for the next one
				// 			}

			// 			if ($activeplugin)   {
			$mainframe = JFactory::getApplication();
			$mainframe->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart&layout=order_done'),JText::_('COM_VIRTUEMART_CART_ORDERDONE_THANK_YOU'));
			// 			}
		} else {
			$mainframe = JFactory::getApplication();
			$mainframe->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart'),JText::_('COM_VIRTUEMART_CART_ORDERDONE_DATA_NOT_VALID'));

		}

	}

	/**
	 * Used for new payment handling, not implemented yet. Idea is to use the token and the stored session to refer
	 * to a cart and so not to create an order for payment attempts
	 *
	 * @author Valerie
	 * @author Max Milbers
	 *
	 */
	public function executeConfirmedOrder(){

		$order = new VirtueMartModelOrders();
		if (($orderID = $order->createOrderFromCart($this)) === false) {
			$mainframe = JFactory::getApplication();
			JError::raiseWarning(500, $order->getError());
			$mainframe->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart'));
		}

		$this->virtuemart_order_id = $orderID;
		$this->sentOrderConfirmedEmail($order->getOrder($orderID));

		//We delete the old stuff
		$this->products = array();
		$this->_inCheckOut = false;
		$this->_dataValidated = false;
		$this->_confirmDone = false;
		$this->customer_comment = '';
		$this->couponCode = '';
		$this->tosAccepted = false;

		$this->setCartIntoSession();
	}


	/**
	 * Prepares the body for shopper and vendor, renders them and sends directly the emails
	 *
	 * @author Max Milbers
	 * @author Christopher Roussel
	 *
	 * @param int $orderID
	 *
	 */
	private function sentOrderConfirmedEmail ($order) {
		if(!class_exists('shopFunctionsF')) require(JPATH_VM_SITE.DS.'helpers'.DS.'shopfunctionsf.php');

		$vars = array('order' => $order);
		$vars['shopperName'] =  $order['details']['BT']->title.' '.$order['details']['BT']->first_name.' '.$order['details']['BT']->last_name;

		return shopFunctionsF::renderMail('cart', $order['details']['BT']->email, $vars);
	}




	/**
	 * prepare display of cart
	 *
	 * @author RolandD
	 * @author Max Milbers
	 * @access public
	 */
	public function prepareCartData(){

		/* Get the products for the cart */
		$prices = array();
		$product_prices = $this->getCartPrices();

		if(!class_exists('CurrencyDisplay')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'currencydisplay.php');
		$currency = CurrencyDisplay::getInstance();

		if(!empty($product_prices)){
			foreach($product_prices as $k=>$price){

				if(is_array($price)){
					foreach($price as $sk=>$sprice){
						$prices[$k][$sk] = $currency->priceDisplay($sprice);
					}

				} else {
					$prices[$k] = $currency->priceDisplay($price);
				}
			}
		} else {
			$prices = array();
		}


		if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');
		$calculator = calculationHelper::getInstance();

		$this->pricesUnformatted = $product_prices;
		$this->prices = $prices;

		$cartData = $calculator->getCartData();

		$this->setCartIntoSession();
		return $cartData ;
	}


	function saveAddressInCart($data, $type, $putIntoSession = true) {

		// VirtueMartModelUserfields::getUserFields() won't work
		if(!class_exists('VirtueMartModelUserfields')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'userfields.php' );
		$userFieldsModel = new VirtueMartModelUserfields();
		$prefix = '';

		$prepareUserFields = $userFieldsModel->getUserFieldsFor('cart',$type);

		if ($type == 'STaddress') {
			$prefix = 'shipto_';

		} else { // BT
			if(!empty($data['agreed'])){
				$this->tosAccepted = $data['agreed'];
			} else if (!empty($data->agreed)){
				$this->tosAccepted = $data->agreed;
			}

			if(empty($data['email'])){
				$address->email = JFactory::getUser()->email;
			}
		}

		$address = array();

		if(is_array($data)){
			foreach ($prepareUserFields as $fld) {
				if(!empty($fld->name)){
					$name = $fld->name;
					if(!empty($data[$prefix.$name])) $address[$name] = $data[$prefix.$name];
				}
			}

		} else {
			foreach ($prepareUserFields as $fld) {
				if(!empty($fld->name)){
					$name = $fld->name;
					if(!empty($data->{$prefix.$name})) $address[$name] = $data->{$prefix.$name};
				}

			}
		}

		$this->{$type} = $address;


		if($putIntoSession){
			$this->setCartIntoSession();
		}

	}

	function CheckAutomaticSelectedShipping() {

		$nbShipping = 0;
		if (!class_exists('vmShipperPlugin'))
		require(JPATH_VM_SITE . DS . 'helpers' . DS . 'vmshipperplugin.php');
		JPluginHelper::importPlugin('vmshipper');
		$dispatcher = JDispatcher::getInstance();
		$returnValues = $dispatcher->trigger('plgVmOnCheckAutomaticSelectedShipping', array('cart' => $this));
		foreach ($returnValues as $returnValue) {
			if ((int)$returnValue ) {
				$nbShipping ++;
				$virtuemart_shippingcarrier_id = $returnValue;
			}
		}
		if ($nbShipping==1) {
			$this->virtuemart_shippingcarrier_id = $virtuemart_shippingcarrier_id;
			$this->setCartIntoSession();
			return true;
		} else {
			return false;
		}


	}
	function CheckAutomaticSelectedPayment() {

		$nbPayment = 0;
		if (!class_exists('vmPaymentPlugin'))
		require(JPATH_VM_SITE . DS . 'helpers' . DS . 'vmpaymentplugin.php');
		JPluginHelper::importPlugin('vmpayment');
		$dispatcher = JDispatcher::getInstance();
		$returnValues = $dispatcher->trigger('plgVmOnCheckAutomaticSelectedPayment', array('cart' => $this));
		foreach ($returnValues as $returnValue) {
			if ((int)$returnValue ) {
				$nbPayment ++;
				$virtuemart_paymentmethod_id = $returnValue;
			}
		}
		if ($nbPayment==1) {
			$this->virtuemart_paymentmethod_id = $virtuemart_paymentmethod_id;
			$this->setCartIntoSession();
			return true;
		} else {
			return false;
		}


	}
	function CheckShippingIsValid() {
		if ($this->virtuemart_shippingcarrier_id===0)
		return;
		$shippingValid = false;
		if (!class_exists('vmShipperPlugin'))
		require(JPATH_VM_SITE . DS . 'helpers' . DS . 'vmshipperplugin.php');
		JPluginHelper::importPlugin('vmshipper');
		$dispatcher = JDispatcher::getInstance();
		$returnValues = $dispatcher->trigger('plgVmOnCheckShippingIsValid', array('cart' => $this));
		foreach ($returnValues as $returnValue) {
			$shippingValid += $returnValue;
		}
		if (!$shippingValid) {
			$this->virtuemart_shippingcarrier_id = 0;
			$this->setCartIntoSession();
		}
	}

	/*
	 * Prepare the datas for cart/mail views
	* set product, price, user, adress and vendor as Object
	* @author Patrick Kohl
	*/
	function prepareCartViewData(){
		$data = new stdClass();
		$this->CheckShippingIsValid( );
		$this->automaticSelectedShipping = $this->CheckAutomaticSelectedShipping( );
		$this->automaticSelectedPayment =   $this->CheckAutomaticSelectedPayment( );
		/* Get the products for the cart */
		$this->cartData = $this->prepareCartData();
		$this->prepareCartPrice( $this->prices ) ;
		$this->prepareUserData();

		$this->prepareAddressDataInCart();
		$this->prepareVendor();
	}

	private function prepareCartPrice( $prices ){

		foreach ($this->products as $cart_item_id=>&$product){
			$product->virtuemart_category_id = $this->getCardCategoryId($product->virtuemart_product_id);
			// No full link because Mail want absolute path and in shop is better relative path
			$product->url = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$product->virtuemart_product_id.'&virtuemart_category_id='.$product->virtuemart_category_id);//JHTML::link($url, $product->product_name);
			if(!empty($product->customfieldsCart)){
				$product->customfields = ShopFunctions::customFieldInCartDisplay($cart_item_id,$product);
			} else {
				$product->customfields ='';
			}
			$product->salesPrice = empty($prices[$cart_item_id]['salesPrice'])? 0:$prices[$cart_item_id]['salesPrice'];
			$product->basePriceWithTax = empty($prices[$cart_item_id]['salesPrice'])? 0:$prices[$cart_item_id]['basePriceWithTax'];
			//			$product->basePriceWithTax = $prices[$cart_item_id]['basePriceWithTax'];
			$product->subtotal = $prices[$cart_item_id]['subtotal'];
			$product->subtotal_tax_amount = $prices[$cart_item_id]['subtotal_tax_amount'];
			$product->subtotal_discount = $prices[$cart_item_id]['subtotal_discount'];
			$product->subtotal_with_tax = $prices[$cart_item_id]['subtotal_with_tax'];
			$product->cart_item_id = $cart_item_id ;
		}
	}

	function prepareAddressDataInCart($type='BT'){


		if(!class_exists('VirtuemartModelUserfields')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'userfields.php');
		$userFieldsModel =new VirtueMartModelUserfields;

		$addresstype = $type.'address';
		$userFieldsBT = $userFieldsModel->getUserFieldsFor('cart',$type);
		$this->$addresstype = $userFieldsModel->getUserFieldsByUser(
		$userFieldsBT
		,(object)$this->$type
		);

		if(!empty($this->ST) && $type!=='ST'){
			$userFieldsST = $userFieldsModel->getUserFieldsFor('cart','ST');
			$this->STaddress = $userFieldsModel->getUserFieldsByUser(
			$userFieldsST
			,(object)$this->ST
			);
		}

	}

	function prepareUserData(){

		/*		//For User address
		 $currentUser = JFactory::getUser();
		//$this->lists['current_id'] = $currentUser->get('id');
		//		$this->assignRef('virtuemart_user_id', $this->lists['current_id']);
		if($this->lists['current_id']  = $currentUser->get('id') ){
		if(!class_exists('VirtuemartModelUser')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'user.php');
		$this->user = new VirtueMartModelUser;
		$this->user->setCurrent();
		if(!$this->user){

		} else {
		$this->userDetails = & $this->user->getUser();

		//This are other contact details, like used in CB or so.
		//			$_contactDetails = $this->_user->getContactDetails();
		}
		}*/
	}

	function prepareAddressRadioSelection(){

		//Just in case
// 		if(!$this->user){
// 			$this->prepareUserData();
// 		}
		if(!class_exists('VirtuemartModelUser')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'user.php');
		$this->user = new VirtueMartModelUser;
		$this->user->setCurrent();
		$this->userDetails = $this->user->getUser();

		// Shipping address(es)
		if($this->user){
			$_addressBT = $this->user->getUserAddressList($this->userDetails->JUser->get('id') , 'BT');

			// Overwrite the address name for display purposes
			$_addressBT[0]->address_type_name = JText::_('COM_VIRTUEMART_ACC_BILL_DEF');

			$_addressST = $this->user->getUserAddressList($this->userDetails->JUser->get('id') , 'ST');

		} else {
			$_addressBT[0]->address_type_name = '<a href="index.php'
			.'?option=com_virtuemart'
			.'&view=user'
			.'&task=editaddresscart'
			.'&addrtype=BT'
			. '">'.JText::_('COM_VIRTUEMART_ACC_BILL_DEF').'</a>'.'<br />';
			$_addressST = array();
		}

		$addressList = array_merge(
		array($_addressBT[0])// More BT addresses can exist for shopowners :-(
		, $_addressST );

		if($this->user){
			for ($_i = 0; $_i < count($addressList); $_i++) {
				$addressList[$_i]->address_type_name = '<a href="index.php'
				.'?option=com_virtuemart'
				.'&view=user'
				.'&task=editaddresscart'
				.'&addrtype='.(($_i == 0) ? 'BT' : 'ST')
				.'&virtuemart_userinfo_id='.(empty($addressList[$_i]->virtuemart_userinfo_id)? 0 : $addressList[$_i]->virtuemart_userinfo_id)
				. '">'.$addressList[$_i]->address_type_name.'</a>'.'<br />';
			}

			if(!empty($addressList[0]->virtuemart_userinfo_id)){
				$_selectedAddress = (
				empty($this->_cart->selected_shipto)
				? $addressList[0]->virtuemart_userinfo_id // Defaults to 1st BillTo
				: $this->_cart->selected_shipto
				);
				$this->lists['shipTo'] = JHTML::_('select.radiolist', $addressList, 'shipto', null, 'virtuemart_userinfo_id', 'address_type_name', $_selectedAddress);
			}else{
				$_selectedAddress = 0;
				$this->lists['shipTo'] = '';
			}


		} else {
			$_selectedAddress = 0;
			$this->lists['shipTo'] = '';
		}

		$this->lists['billTo'] = empty($addressList[0]->virtuemart_userinfo_id)? 0 : $addressList[0]->virtuemart_userinfo_id;

	}
	function prepareMailData(){

		if(empty($this->vendor)) $this->prepareVendor();
		//TODO add orders, for the orderId
		//TODO add registering userdata
		// In general we need for every mail the shopperdata (with group), the vendor data, shopperemail, shopperusername, and so on
	}

	// add vendor for cart
	function prepareVendor(){
		if (!class_exists('VirtueMartModelVendor')) require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'vendor.php');
		$vendorModel = new VirtueMartModelVendor();
		$this->vendor = & $vendorModel->getVendor();
		$vendorModel->addImages($this->vendor);
		return $this->vendor;
	}

	// Render the code for Ajax Cart
	function prepareAjaxData(){
		// Added for the zone shipping module
		//$vars["zone_qty"] = 0;
		$this->prepareCartData();
		$weight_total = 0;
		$weight_subtotal = 0;

		//of course, some may argue that the $this->data->products should be generated in the view.html.php, but
		//
		$this->data->products = array();
		$this->data->totalProduct = 0;
		$i=0;
		foreach ($this->products as $priceKey=>$product){

			//$vars["zone_qty"] += $product["quantity"];
			$category_id = $this->getCardCategoryId($product->virtuemart_product_id);
			//Create product URL
			$url = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$product->virtuemart_product_id.'&virtuemart_category_id='.$category_id);

			// @todo Add variants
			$this->data->products[$i]['product_name'] = JHTML::link($url, $product->product_name);
			$this->data->products[$i]['customfieldsCart'] ='';
			//			/* Add the variants */
			if (!is_int($priceKey)) {
				if (empty($calculator)) {
					if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');
					$calculator = calculationHelper::getInstance();
				}
				$variantmods = $calculator->parseModifier($priceKey);
				$row = 0 ;

				foreach ($variantmods as $variant=>$selected){

					if ($product->customfieldsCart[$row]->field_type == "U") {
						foreach ($product->userfield as $pKey => $puser) {
							$this->data->products[$i]['customfieldsCart'] .= '<br/ > <b>'.$product->customfieldsCart[$row]->custom_title.' : </b>'.$puser.' '.$product->customfieldsCart[$row]->custom_field_desc;
						}
					} else {

						$this->data->products[$i]['customfieldsCart'] .= '<br/ ><b>'.$product->customfieldsCart[$row]->custom_title.' : </b>'.$product->customfieldsCart[$row]->options[$selected]->custom_value;
					}
					$row++;
				}
			}
			$this->data->products[$i]['product_name'] .= $this->data->products[$i]['customfieldsCart'] ;
			$this->data->products[$i]['product_sku'] = $product->product_sku;

			//** @todo WEIGHT CALCULATION
			//$weight_subtotal = vmShippingMethod::get_weight($product["virtuemart_product_id"]) * $product->quantity'];
			//$weight_total += $weight_subtotal;


			// product Price total for ajax cart
			$this->data->products[$i]['prices'] = $this->prices[$priceKey]['subtotal_with_tax'];
			// other possible option to use for display
			$this->data->products[$i]['subtotal'] = $this->prices[$priceKey]['subtotal'];
			$this->data->products[$i]['subtotal_tax_amount'] = $this->prices[$priceKey]['subtotal_tax_amount'];
			$this->data->products[$i]['subtotal_discount'] = $this->prices[$priceKey]['subtotal_discount'];
			$this->data->products[$i]['subtotal_with_tax'] = $this->prices[$priceKey]['subtotal_with_tax'];

			// UPDATE CART / DELETE FROM CART
			$this->data->products[$i]['quantity'] = $product->quantity;
			$this->data->totalProduct += $product->quantity ;

			$i++;
		}
		$this->data->billTotal = $this->prices['billTotal'];
		$this->data->dataValidated = $this->_dataValidated ;
		return $this->data ;
	}
}
