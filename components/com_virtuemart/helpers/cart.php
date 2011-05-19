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

// Load the model framework
//jimport( 'joomla.application.component.model');

/**
* Model class for the cart
* Very important, use ALWAYS the getCart function, to get the cart from the session
* @package	VirtueMart
* @subpackage Cart
* @author RolandD
* @author Max Milbers
*/
class VirtueMartCart  {

//	var $productIds = array();
	var $products = array();
	private $_inCheckOut = false;
	private $_dataValidated = false;
	private $_confirmDone = false;
	private $_lastError = null; // Used to pass errmsg to the cart using addJS()

	//todo multivendor stuff must be set in the add function, first product determins ownership of cart, or a fixed vendor is used
	var $vendorId = 1;
	var $lastVisitedCategoryId = 0;
	var $virtuemart_shippingrate_id = 0;
	var $shipper_id = 0;
	var $virtuemart_paymentmethod_id = 0;
	var $BT = 0;
	var $ST = 0;
	var $tosAccepted = false;
	var $customer_comment = '';
	var $couponCode = '';
	var $cartData = null ;
	private function __construct() {

		self::setCartIntoSession();

	}


	/**
	* Get the cart from the session
	*
	* @author Max Milbers
	* @access public
	* @param array $cart the cart to store in the session
	*/
	public static function getCart($deleteValidation=true) {

		$session = JFactory::getSession();
		$cartTemp = $session->get('vmcart', 0, 'vm');
		if(!empty($cartTemp) ){
			$cart = unserialize($cartTemp);
			if($deleteValidation){
				$cart->setDataValidation();
			}
		} else {
			$cart = new VirtueMartCart;
		}
		return $cart;
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
		$session->set('vmcart', serialize($this), 'vm');
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

	public function setDataValidation($valid=false){
		$this->_dataValidated = $valid;
		$this->setCartIntoSession();
	}

	public function getDataValidated(){
		return $this->_dataValidated;
	}

	public function getInCheckOut(){
		return $this->_inCheckOut;
	}

	/**
	 * Set the last error that occured.
	 * This is used on error to pass back to the cart when addJS() is invoked.
	 * @param string $txt Error message
	 * @author Oscar van Eijk
	 */
	private function setError ($txt)
	{
		$this->_lastError = $txt;
	}

	/**
	 * Retrieve the last error message
	 * @return string The last error message that occured
	 * @author Oscar van Eijk
	 */
	public function getError ()
	{
		return ($this->_lastError);
	}

	/**
	* Add a product to the cart
	*
	* @author RolandD
	* @author Max Milbers
	* @access public
	*/
	public function add() {
		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();
		$post = JRequest::get('default');
		$total_quantity = 0;
		$total_updated = 0;
		$total_deleted = 0;
		$virtuemart_product_ids = JRequest::getVar('virtuemart_product_id',array(),'default','array' ) ;

		if (empty($virtuemart_product_ids)) {
			$mainframe->enqueueMessage( JText::_('COM_VIRTUEMART_CART_ERROR_NO_PRODUCT_IDS',false) );
			return false;
		}

		if(!class_exists('calculationHelper'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');

		//Iterate through the prod_id's and perform an add to cart for each one
		foreach ($virtuemart_product_ids as $p_key => $virtuemart_product_id) {

			$product = $this->getProduct($virtuemart_product_id);

			/* Check if we have a product */
			if ($product) {
				$quantityPost = $post['quantity'][$p_key];
				$virtuemart_category_idPost = $post['virtuemart_category_id'][$p_key];

				$product->virtuemart_category_id = $virtuemart_category_idPost;
				$productKey= $product->virtuemart_product_id;
// INDEX NOT FOUND IN JSON HERE
// changed name field you know exactly was this is
				if (isset($post['customPrice'])) {
					$product->customPrices = $post['customPrice'];
					$productKey.= '::';
					foreach($product->customPrices as $customPrice){
						foreach($customPrice as $customId => $custom_fieldId){
							$productKey .= $customId.':'.$custom_fieldId.';';
						}
					}
				}

				if (array_key_exists($productKey, $this->products)) {
					$this->products[$productKey]->quantity += $quantityPost;
					if($this->checkForQuantities($product,$this->products[$productKey]->quantity)) {
						$mainframe->enqueueMessage(JText::_('COM_VIRTUEMART_CART_PRODUCT_UPDATED'));
					} else {
						return false;
					}
				} else {
					$this->products[$productKey] = $product;
					$product->quantity = $quantityPost;
					if($this->checkForQuantities($product,$quantityPost)) {
						$mainframe->enqueueMessage(JText::_('COM_VIRTUEMART_CART_PRODUCT_ADDED'));
					} else {
						return false;
					}
				}

			}
			else {
				$mainframe->enqueueMessage( JText::_('COM_VIRTUEMART_PRODUCT_NOT_FOUND',false) );
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
		if (empty($prod_id)) $prod_id = JRequest::getVar('cart_virtuemart_product_id');
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

		if (empty($cart_virtuemart_product_id)) $cart_virtuemart_product_id = JRequest::getVar('cart_virtuemart_product_id');
		if (empty($quantity)) $quantity = JRequest::getInt('quantity');

//		foreach($cart_virtuemart_product_ids as $cart_virtuemart_product_id){
			if (array_key_exists($cart_virtuemart_product_id, $this->products)) {
				if(!empty($quantity)){
					if($this->checkForQuantities($this->products[$cart_virtuemart_product_id],$quantity)){
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
		if ($updated) return true;
		else return false;
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
//		if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');
		$calculator = calculationHelper::getInstance();
		return $calculator->getCheckoutPrices($this);
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
		JModel::addIncludePath(JPATH_VM_ADMINISTRATOR.DS.'models');
		$model = JModel::getInstance('Product', 'VirtueMartModel');
		return $model->getProduct($virtuemart_product_id, true, false);
	}

//	/**
//	* Function Description
//	*
//	* @author Max Milbers
//	* @access public
//	* @param array $cart the cart to get the products for
//	* @return array of product objects
//	*/
//	public function getCartProducts() {
//		$products = array();
////		for ($i = 0; $cart['idx'] > $i; $i++) {
//		foreach($this->products as $product)
//			$products[] = $this->getProduct($product->virtuemart_product_id);
//		}
//		return $products;
//	}

	/**
	* Get the category ID from a product ID
	*
	* @author RolandD
	* @access public
	* @return mixed if found the category ID else null
	*/
	public function getCategoryId() {
		$db = JFactory::getDBO();
		$virtuemart_product_id = JRequest::getInt('virtuemart_product_id');
		$q = 'SELECT `virtuemart_category_id` FROM `#__virtuemart_product_categories` WHERE `virtuemart_product_id` = '.intval($virtuemart_product_id).' LIMIT 1';
		$db->setQuery($q);
		return $db->loadResult();
	}	/**

	* Get the category ID from a product ID
	*
	* @author Patrick Kohl
	* @access public
	* @return mixed if found the category ID else null
	*/
	public function getCardCategoryId($virtuemart_product_id) {
		$db = JFactory::getDBO();
		$q = 'SELECT `virtuemart_category_id` FROM `#__virtuemart_product_categories` WHERE `virtuemart_product_id` = '.intval($virtuemart_product_id).' LIMIT 1';
		$db->setQuery($q);
		return $db->loadResult();
	}


	/**
	 * Checks if the quantity is correct
	 *
	 * @author Max Milbers
	 */
	private function checkForQuantities($product,$quantity=0) {

		$mainframe = JFactory::getApplication();
		/* Check for a valid quantity */
		if (!preg_match("/^[0-9]*$/", $quantity)) {
			$_error = JText::_('COM_VIRTUEMART_CART_ERROR_NO_VALID_QUANTITY',false);
//			$this->_error[] = 'Quantity was not a number';
			$this->setError($_error);
			$mainframe->enqueueMessage();
			return false;
		}

		/* Check for negative quantity */
		if ($quantity < 0) {
//			$this->_error[] = 'Quantity under zero';
			$_error = JText::_('COM_VIRTUEMART_CART_ERROR_NO_NEGATIVE',false);
			$this->setError($_error);
			$mainframe->enqueueMessage($_error);
			return false;
		}

		/* Check for the minimum and maximum quantities */
		list($min,$max) = explode(',', $product->product_order_levels);
		if ($min != 0 && $quantity < $min) {
//			$this->_error[] = 'Quantity reached not minimum';
			$_error = JText::sprintf('COM_VIRTUEMART_CART_MIN_ORDER', $min);
			$this->setError($_error);
			$mainframe->enqueueMessage($_error, 'error');
			return false;
		}
		if ($max !=0 && $quantity > $max) {
//			$this->_error[] = 'Quantity reached over maximum';
			$_error = JText::sprintf('COM_VIRTUEMART_CART_MAX_ORDER', $max);
			$this->setError($_error);
			$mainframe->enqueueMessage($_error, 'error');
			return false;
		}

		$ci = 0;
		$request_stock = array();

		/* Check to see if checking stock quantity */
		if (VmConfig::get('check_stock', false)) {
			if ($quantity > $product->product_in_stock) {
				/* Create an array for out of stock items and continue to next item */
				$request_stock[$ci]['virtuemart_product_id'] = $product->virtuemart_product_id;
				$request_stock[$ci]['quantity'] = $quantity;
				$ci++;
//				$this->_error[] = 'Quantity reached stock limit '.$product->virtuemart_product_id;
				continue;
			}
		}
		if(count($request_stock)!=0){
			foreach($request_stock as $rstock){
				$_error = JText::_('COM_VIRTUEMART_CART_PRODUCT_OUT_OF_STOCK');
				$this->setError($_error); // Private error retrieved with getError is used only by addJS, so only the latest is fine
				$mainframe->enqueueMessage($_error, 'error');
			}
			return false;
		}
		return true;
	}

	function confirmDone(){

		$this -> checkoutData();
		if($this->_dataValidated){
			$this->_confirmDone = true;
			$this->confirmedOrder();
		} else {
			$mainframe = JFactory::getApplication();
			$mainframe->redirect('index.php?option=com_virtuemart&view=cart',JText::_('COM_VIRTUEMART_CART_CHECKOUT_DATA_NOT_VALID'));
		}
	}

	function checkout(){
		if($this -> checkoutData()){
			$mainframe = JFactory::getApplication();
			$mainframe->redirect('index.php?option=com_virtuemart&view=cart',JText::_('COM_VIRTUEMART_CART_CHECKOUT_DONE_CONFIRM_ORDER'));
		}
	}

	/**
	 * Validate the coupon code. If ok,. set it in the cart
	 * @param string $coupon_code Coupon code as entered by the user
	 * @author Oscar van Eijk
	 * @access public
	 * @return string On error the message text, otherwise an empty string
	 */
	public function setCouponCode($coupon_code) {
		if (!class_exists('CouponHelper')) {
			require(JPATH_VM_SITE.DS.'helpers'.DS.'coupon.php');
		}
		$_prices = $this->getCartPrices();
		$_msg = CouponHelper::ValidateCouponCode($coupon_code, $_prices['salesPrice']);
		if (!empty($_msg)) {
			$this->couponCode = '';
			$this->setCartIntoSession();
			return $_msg;
		}
		$this->couponCode = $coupon_code;
		$this->setCartIntoSession();
		return '';
	}

	public function setShippingRate($virtuemart_shippingrate_id){
		$this->virtuemart_shippingrate_id=$virtuemart_shippingrate_id;
		$this->setCartIntoSession();
	}

	/**
	 * Check the selected shipper data and store the info in the cart
	 * @param integer $shipper_id Shipper ID taken from the form data
	 * @author Oscar van Eijk
	 */
	public function setShipper($shipper_id)
	{
		if(!class_exists('vmShipperPlugin')) require(JPATH_VM_SITE.DS.'helpers'.DS.'vmshipperplugin.php');
		JPluginHelper::importPlugin('vmshipper');

		$_dispatcher = JDispatcher::getInstance();
		$_retValues = $_dispatcher->trigger('plgVmOnShipperSelected', array('cart'=>$this, '_selectedShipper' => $shipper_id));
		foreach ($_retValues as $_retVal) {
			if ($_retVal === true) {
				$this->shipper_id=$shipper_id;
				$this->setCartIntoSession();
				break; // Plugin completed succesful; nothing else to do
			} elseif ($_retVal === false) { // Missing data, ask for it (again)
				$mainframe->redirect('index.php?option=com_virtuemart&view=cart&task=editshipping');
//	Remove comments if newchecks need to be implemented.
//	NOTE: inactive plugins will always return null, so that value cannot be used for anything else!
//				} elseif ($_retVal === null) {
//					continue; // This plugin was skipped
//				} else {
//					continue; // Other values not yet implemented
			}
		}
	}

	public function setPaymentMethod($virtuemart_paymentmethod_id){
		$this->virtuemart_paymentmethod_id=$virtuemart_paymentmethod_id;
		$this->setCartIntoSession();
	}

	private function checkoutData(){

		$this->_inCheckOut = true;
//		$this->_dataValidated = true; //this is wrong, I am quite sure, the dataValidated is set at the end of the checkout process
		$this->tosAccepted = JRequest::getVar('tosAccepted', $this->tosAccepted);
		$this->customer_comment = JRequest::getVar('customer_comment', $this->customer_comment);

		if (($this->selected_shipto = JRequest::getVar('shipto', null)) !== null) {
			JModel::addIncludePath(JPATH_VM_ADMINISTRATOR.DS.'models');
			$_userModel = JModel::getInstance('user', 'VirtueMartModel');
			$_stData = $_userModel->getUserAddress(0, $this->selected_shipto, '');
			$this->validateUserData('ST', $_stData[0]);
		}

		$this->setCartIntoSession();

		$mainframe = JFactory::getApplication();
		if( count($this->products) == 0){
			$mainframe->redirect('index.php?option=com_virtuemart',JText::_('COM_VIRTUEMART_CART_NO_PRODUCT'));
		} else {
			foreach ($this->products as $product){
				$redirectMsg = $this->checkForQuantities($product,$product->quantity);
				if(!$redirectMsg){
//					$this->setCartIntoSession();
					$mainframe->redirect('index.php?option=com_virtuemart&view=cart',$redirectMsg);
				}
			}
		}

		// Check if a minimun purchase value is set
		if (($_msg = $this->checkPurchaseValue()) != null) {
			$mainframe->redirect('index.php?option=com_virtuemart&view=cart', $_msg);
		}

		//But we check the data again to be sure
		if(empty($this->BT)){
			$mainframe->redirect('index.php?option=com_virtuemart&view=user&task=editaddresscheckout&addrtype=BT');
		}else {
			$redirectMsg = self::validateUserData();
			if($redirectMsg){
				$mainframe->redirect('index.php?option=com_virtuemart&view=user&task=editaddresscheckout&addrtype=BT',$redirectMsg);
			}
		}
		//Only when there is an ST data, test if all necessary fields are filled
		if(!empty($this->ST)){
			$redirectMsg = self::validateUserData('ST');
			if($redirectMsg){
//				$this->setCartIntoSession();
				$mainframe->redirect('index.php?option=com_virtuemart&view=user&task=editaddresscheckout&addrtype=ST',$redirectMsg);
			}
		}

		// Test Coupon
		if (!empty($this->couponCode)) {
			$_prices = $this->getCartPrices();
			if (!class_exists('CouponHelper')) {
				require(JPATH_VM_SITE.DS.'helpers'.DS.'coupon.php');
			}
			$redirectMsg = CouponHelper::ValidateCouponCode($this->couponCode, $_prices['salesPrice']);
			if (!empty($redirectMsg)) {
				$this->couponCode = '';
//				$this->setCartIntoSession();
				$mainframe->redirect('index.php?option=com_virtuemart&view=cart&task=editcoupon',$redirectMsg);
			}
		}

		//Test Shipment
		if($this->shipper_id == 0){
//			$this->setCartIntoSession();
			$mainframe->redirect('index.php?option=com_virtuemart&view=cart&task=editshipping',$redirectMsg);
		}
		// Ok, a shipper was selected, now make sure we can find a matching shipping rate for
		// the current order shipto and weight
		if(!class_exists('vmShipperPlugin')) require(JPATH_VM_SITE.DS.'helpers'.DS.'vmshipperplugin.php');
		JPluginHelper::importPlugin('vmshipper');
		$_dispatcher = JDispatcher::getInstance();
		$_retValues = $_dispatcher->trigger('plgVmOnConfirmShipper', array('cart'=>$this));
		$this->virtuemart_shippingrate_id = -1;
		foreach ($_retValues as $_retVal) {
			if ($_retVal !== null) {
				$this->virtuemart_shippingrate_id = $_retVal;
				break; // When we've got a value, it's always a valid one, so we're done now
			}
		}
		if ($this->virtuemart_shippingrate_id < 0) {
			$this->shipper_id = 0;
			$this->setCartIntoSession();
			$mainframe->redirect('index.php?option=com_virtuemart&view=cart&task=editshipping',$redirectMsg);
		}

		//Test Payment and show payment plugin
		if(empty($this->virtuemart_paymentmethod_id)){

			$mainframe->redirect('index.php?option=com_virtuemart&view=cart&task=editpayment',$redirectMsg);
		} else {
			if(!class_exists('vmPaymentPlugin')) require(JPATH_VM_SITE.DS.'helpers'.DS.'vmpaymentplugin.php');
			JPluginHelper::importPlugin('vmpayment');
			//Add a hook here for other payment methods, checking the data of the choosed plugin
			$_dispatcher = JDispatcher::getInstance();
			$_retValues = $_dispatcher->trigger('plgVmOnCheckoutCheckPaymentData', array('cart'=>$this));
			foreach ($_retValues as $_retVal) {
				if ($_retVal === true) {
					break; // Plugin completed succesful; nothing else to do
				} elseif ($_retVal === false) { // Missing data, ask for it (again)

					$mainframe->redirect('index.php?option=com_virtuemart&view=cart&task=editpayment',$redirectMsg);

					// Checks below outcommented since we're at the end of out loop anyway :-/
// 	Remove comments if newchecks need to be implemented.
// 	NOTE: inactive plugins will always return null, so that value cannot be used for anything else!
//					} elseif ($_retVal === null) {
//						continue; // This plugin was skipped
//					} else {
//						continue; // Other values not yet implemented
				}
			}
		}
		if(VmConfig::get('agree_to_tos_onorder') && !$this->tosAccepted) {
			$mainframe->redirect('index.php?option=com_virtuemart&view=cart',JText::_('COM_VIRTUEMART_CART_PLEASE_ACCEPT_TOS'));
		}
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
	private function checkPurchaseValue()
	{
		if(!class_exists('VirtueMartModelVendor')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'vendor.php');
		$_vendor = new VirtueMartModelVendor();
		$_vendor->setId($this->vendorId);
		$_store = $_vendor->getVendor();
		if ($_store->vendor_min_pov > 0) {
			$_prices = $this->getCartPrices();
			if ($_prices['salesPrice'] < $_store->vendor_min_pov) {
				if (!class_exists('CurrencyDisplay')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'currencydisplay.php');
				$_currency = CurrencyDisplay::getCurrencyDisplay();
				$_minValue = $_currency->priceDisplay($_min);
				return JText::sprintf('COM_VIRTUEMART_CART_MIN_PURCHASE', $_currency->priceDisplay($_store->vendor_min_pov));
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
	 private function validateUserData($type='BT', $_obj = null){

//		$this->addModelPath( JPATH_VM_ADMINISTRATOR .DS.'models' );
		if(!class_exists('VirtueMartModelUserfields')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'userfields.php');
		$_userFieldsModel = new VirtueMartModelUserfields();
//		$_userFieldsModel = $this->getModel( 'userfields', 'VirtuemartModel' );
		if($type=='BT') $fieldtype = 'account'; else $fieldtype = 'shipping';
		$neededFields = $_userFieldsModel->getUserFields(
									 $fieldtype  //TODO we need, agreed also
									, array('required'=>true,'delimiters'=>true,'captcha'=>true,'system'=>false)
				, array('delimiter_userinfo', 'username', 'password', 'password2', 'address_type_name','address_type','user_is_vendor'));

		$redirectMsg=0;
		foreach($neededFields as $field){
			if ($_obj !== null && is_array($this->{$type})) {
				$this->{$type}[$field->name] = $_obj->{$field->name};
			}
			if(empty($this->{$type}[$field->name]) && $field->name!='virtuemart_state_id'){
				$redirectMsg = 'Enter for "'.$type.'" "'.$field->name.'" title: '.JText::_($field->title).' and value: '.$this->{$type}[$field->name].' but '.$this->BT['first_name'];
			} else {
				//This is a special test for the virtuemart_state_id. There is the speciality that the virtuemart_state_id could be 0 but is valid.
				if($field->name=='virtuemart_state_id'){

					if(!class_exists('VirtueMartModelState')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'state.php');
					if(!$msg=VirtueMartModelState::testStateCountry($this->{$type}['virtuemart_country_id'],$this->{$type}['virtuemart_state_id'])){
						$redirectMsg = $msg;
					}
				}
				//We may add here further Tests. Like if the email has the form a@b.xxx and so on
			}
		}
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
	private function confirmedOrder(){

		//Just to prevent direct call
		if($this->_dataValidated && $this->_confirmDone){

			if (!class_exists('VirtueMartModelOrders'))	require( JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'orders.php' );

			$order = new VirtueMartModelOrders();
			if (($_orderID = $order->createOrderFromCart($this)) === false) {
				$mainframe = JFactory::getApplication();
				JError::raiseWarning(500, $order->getError());
				$mainframe->redirect('index.php?option=com_virtuemart&view=cart');
			}
			$this->virtuemart_order_id= $_orderID;
			$this->sentOrderConfirmedEmail($order->getOrder($_orderID));

			//We delete the old stuff
			$this->products = array();
			$this->_inCheckOut = false;
			$this->_dataValidated = false;
			$this->_confirmDone = false;
			$this->customer_comment = '';
			$this->couponCode = '';
			$this->tosAccepted = false;

			$this->setCartIntoSession();

			$mainframe = JFactory::getApplication();
			$mainframe->redirect('index.php?option=com_virtuemart&view=cart&layout=orderdone',JText::_('COM_VIRTUEMART_CART_ORDERDONE_THANK_YOU'));

		} else {
			$mainframe = JFactory::getApplication();
			$mainframe->redirect('index.php?option=com_virtuemart&view=cart',JText::_('COM_VIRTUEMART_CART_ORDERDONE_DATA_NOT_VALID'));

		}

	}

	/**
	 * Prepares the body for shopper and vendor, renders them and sends directly the emails
	 *
	 * @author Max Milbers
	 * @author Christopher Roussel
	 *
	 * @param int $_orderID
	 *
	 */
	private function sentOrderConfirmedEmail ($order) {
		if(!class_exists('shopFunctionsF')) require(JPATH_VM_SITE.DS.'helpers'.DS.'shopfunctionsf.php');

		$vars = array('order' => $order);
		$vars['shopperName'] =  $order['details']['BT']->title.' '.$order['details']['BT']->first_name.' '.$order['details']['BT']->last_name;

		return shopFunctionsF::renderMail('cart', $order['details']['BT']->email, $vars);
	}


	/**
	* Initialise the cart, ATTENTION, started by Roland but not finished, when someone needs it, dont be shy ;-) note by Max Milbers
	*
	* @author RolandD
	* @todo Make sure this gets called when a user is logged in
	* @access public
	*/
	public function initCart() {
		$session = JFactory::getSession();
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		$cart = $this->getCart();

		// If the user is logged in, we can try to retrieve the current cart from the database
        // We store the items in a new SESSION var
		if ($user->id > 0 && empty($cart)) {
			$q = 'SELECT `cart_content`
				FROM `#__virtuemart_carts`
				WHERE `virtuemart_user_id` = '.$user->id;
			$db->setQuery($q);
			$savedcart = $db->loadObject();
			if ($savedcart) {
				// Fill the cart from the contents of the field cart_content, which holds a serialized array
				$contents = unserialize($savedcart->cart_content);

				// Now check if all products are still published and existant
				$products_in_cart = array();
				for ($i=0; $i < $contents["idx"]; $i++) {
					$products_in_cart[$contents[$i]['virtuemart_product_id']] = intval($contents[$i]['virtuemart_product_id']);
				}
				if (!empty($products_in_cart)) {
					$remove_products = array();
					$q = 'SELECT `virtuemart_product_id`
						FROM #__virtuemart_products
						WHERE `virtuemart_product_id` IN ('.implode(',', $products_in_cart ).')
						AND published = 0';
					$db->setQuery($q);
					$remove_products = $db->loadResultArray();
				}

				if (!empty($remove_products)) {
					for ($i=0; $i < $contents["idx"]; $i++) {
						if (in_array(intval($contents[$i]['virtuemart_product_id']), $remove_products)) self::removeProductCart(array($i));
					}
				}
			}
		}

		$this->setCartIntoSession();
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

		foreach($product_prices as $k=>$price){
//			if(is_int($k)){
				if(is_array($price)){
					foreach($price as $sk=>$sprice){
						$prices[$k][$sk] = $currency->priceDisplay($sprice);
					}
//				}

			} else {
				$prices[$k] = $currency->priceDisplay($price);
			}
		}

		if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');
		$calculator = calculationHelper::getInstance();

		$this->cartData->prices = $prices;
		$this->cartData->cartData = $calculator->getCartData();
		$this->cartData->calculator = $calculator;

		return $this->cartData ;
	}
	/**
	* Save the cart in the database
	*
	* @author RolandD
	* @access public
	*/
	public function saveCart() {
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		$cart = $this->getCart();
		if ($user->id > 0) {
			$cart_contents = serialize($cart);
			$q = "INSERT INTO `#__virtuemart_carts` (`virtuemart_user_id`, `cart_content` ) VALUES ( ".$user->id.", '".$cart_contents."' )
				ON DUPLICATE KEY UPDATE `cart_content` = ".$db->Quote($cart_contents);
			$db->setQuery($q);
			$db->query();
		}
	}

	/**
	* This checks if attributes values were chosen by the user
	*
	* @author RolandD
	* @access public
	* @param array $d
	* @return array $result
	*/
	/* TODO OBSELETE HAS TO BE REWRITEN
	public function cartGetAttributes( &$d ) {
		$db = JFactory::getDBO();

		// added for the advanced attributes modification
		//get listing of titles for attributes (Sean Tobin)
		$attributes = array( ) ;
		if( ! isset( $d["prod_id"] ) ) {
			$d["prod_id"] = $d["virtuemart_product_id"] ;
		}
		$q = "SELECT virtuemart_product_id, attribute, custom_attribute FROM #__{vm}_product WHERE virtuemart_product_id='" . (int)$d["prod_id"] . "'" ;
		$db->query( $q ) ;

		$db->next_record() ;

		if( ! $db->f( "attribute" ) && ! $db->f( "custom_attribute" ) ) {
			$q = "SELECT product_parent_id FROM #__{vm}_product WHERE virtuemart_product_id='" . (int)$d["prod_id"] . "'" ;

			$db->query( $q ) ;
			$db->next_record() ;
			$q = "SELECT virtuemart_product_id, attribute, custom_attribute FROM #__{vm}_product WHERE virtuemart_product_id='" . $db->f( "product_parent_id" ) . "'" ;
			$db->query( $q ) ;
			$db->next_record() ;
		}

		$advanced_attribute_list = $db->f( "attribute" ) ;
		if( $advanced_attribute_list ) {
			$fields = explode( ";", $advanced_attribute_list ) ;
			foreach( $fields as $field ) {
				$field = trim( $field ) ;
				$base = explode( ",", $field ) ;
				$title = array_shift( $base ) ;
				array_push( $attributes, $title ) ;
			}
		}
		// We need this for being able to work with attribute names and values which are using non-ASCII characters
		if( strtolower( vmGetCharset() ) != 'utf-8' ) {
			$encodefunc = 'utf8_encode' ;
			$decodefunc = 'utf8_decode' ;
		} else {
			$encodefunc = 'strval' ;
			$decodefunc = 'strval' ;
		}

		$description = "" ;
		$attribute_given = false ;
		// Loop through the simple attributes and check if one of the valid values has been provided
		foreach( $attributes as $a ) {

			$pagevar = str_replace( " ", "_", $a ) ;
			$pagevar .= $d['prod_id'] ;

			$pagevar = $encodefunc( $pagevar ) ;

			if( ! empty( $d[$pagevar] ) ) {
				$attribute_given = true ;
			}
			if( $description != '' ) {
				$description .= "; " ;
			}

			$description .= $a . ":" ;
			$description .= empty( $d[$pagevar] ) ? '' : $decodefunc( $d[$pagevar] ) ;

		}
		rtrim( $description ) ;
		$d["description"] = $description ;
		// end advanced attributes modification addition


		$custom_attribute_list = $db->f( "custom_attribute" ) ;
		$custom_attribute_given = false ;
		// Loop through the custom attribute list and check if a value has been provided
		if( $custom_attribute_list ) {
			$fields = explode( ";", $custom_attribute_list ) ;

			$description = $d["description"] ;

			foreach( $fields as $field ) {
				$pagevar = str_replace( " ", "_", $field ) ;
				$pagevar .= $d['prod_id'] ;
				$pagevar = $encodefunc( $pagevar ) ;

				if( ! empty( $d[$pagevar] ) ) {
					$custom_attribute_given = true ;
				}
				if( $description != '' ) {
					$description .= "; " ;
				}
				$description .= $field . ":" ;
				$description .= empty( $d[$pagevar] ) ? '' : $decodefunc( $d[$pagevar] ) ;

			}
			rtrim( $description ) ;
			$d["description"] = $description ;
			// END add for custom fields by denie van kleef


		}

		$result['attribute_given'] = $attribute_given ;
		$result['advanced_attribute_list'] = $advanced_attribute_list ;
		$result['custom_attribute_given'] = $custom_attribute_given ;
		$result['custom_attribute_list'] = $custom_attribute_list ;

		return $result ;
	}*/

}
