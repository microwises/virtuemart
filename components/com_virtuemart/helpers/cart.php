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
//jimport( 'joomla.application.component.view');

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
    //var $shipper_id = 0;
    var $virtuemart_paymentmethod_id = 0;
    var $BT = 0;
    var $ST = 0;
    var $tosAccepted = null;
    var $customer_comment = '';
    var $couponCode = '';
    var $cartData = null;

    private function __construct() {

    }

    /**
     * Get the cart from the session
     *
     * @author Max Milbers
     * @access public
     * @param array $cart the cart to store in the session
     */
    public static function getCart($deleteValidation=true) {

        if (!class_exists('JTable')
            )require(JPATH_LIBRARIES . DS . 'joomla' . DS . 'database' . DS . 'table.php');
        JTable::addIncludePath(JPATH_VM_ADMINISTRATOR . DS . 'tables');

        $session = JFactory::getSession();
        $cartTemp = $session->get('vmcart', 0, 'vm');
        //dump($cartTemp->cartData,'hmm');
        if (!empty($cartTemp)) {
            $cart = unserialize($cartTemp);
            if ($deleteValidation) {
				$cart->setDataValidation();
            }
        } else {
            $cart = new VirtueMartCart;
        }
		$cart->setPreferred();
		$cart->setCartIntoSession();
        return $cart;
    }

    public function setPreferred() {

        if (!class_exists('VirtueMartModelUser'))
            require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'user.php');
        $usermodel = new VirtueMartModelUser();
        $usermodel->setCurrent();
        $user = $usermodel->getUser();

        if (!empty($this->BT)) {
            foreach ($user->userInfo as $address) {
                if ($address->address_type == 'BT') {
                    $this->saveAddressInCart((array) $address, $address->address_type);
                }
            }
        }

        if (!empty($this->ST)) {
            foreach ($user->userInfo as $address) {
                if ($address->address_type == 'ST') {
                    $this->saveAddressInCart($address, $address->address_type);
                    break;
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

    public function setDataValidation($valid=false) {
        $this->_dataValidated = $valid;
        $this->setCartIntoSession();
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
    public function add() {
        $mainframe = JFactory::getApplication();

        $post = JRequest::get('default');
        $total_quantity = 0;
        $total_updated = 0;
        $total_deleted = 0;
        $virtuemart_product_ids = JRequest::getVar('virtuemart_product_id', array(), 'default', 'array'); //is sanitized then

        if (empty($virtuemart_product_ids)) {
            $mainframe->enqueueMessage(JText::_('COM_VIRTUEMART_CART_ERROR_NO_PRODUCT_IDS', false));
            return false;
        }

        if (!class_exists('calculationHelper')
            )require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'calculationh.php');

        //Iterate through the prod_id's and perform an add to cart for each one
        foreach ($virtuemart_product_ids as $p_key => $virtuemart_product_id) {

            $product = $this->getProduct((int) $virtuemart_product_id);

            /* Check if we have a product */
            if ($product) {
                $quantityPost = (int) $post['quantity'][$p_key];
                $virtuemart_category_idPost = (int) $post['virtuemart_category_id'][$p_key];

                $product->virtuemart_category_id = $virtuemart_category_idPost;
                $productKey = $product->virtuemart_product_id;
// INDEX NOT FOUND IN JSON HERE
// changed name field you know exactly was this is
                if (isset($post['customPrice'])) {
                    $product->customPrices = $post['customPrice'];
                    $productKey.= '::';
                    foreach ($product->customPrices as $customPrice) {
                        foreach ($customPrice as $customId => $custom_fieldId) {
                            $productKey .= $customId . ':' . $custom_fieldId . ';';
                        }
                    }
                }

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
            $prod_id = JRequest::getInt('cart_virtuemart_product_id');
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
//		if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');
        $calculator = calculationHelper::getInstance();
        $prices = $calculator->getCheckoutPrices($this);
        $this->cartData->cartData = $calculator->getCartData();
        $this->setCartIntoSession();
//		dump($this->cartData,' getCartPrices hmm');
//		dump($prices,' getCartPrices hmm');

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
        if (count($request_stock) != 0) {
            foreach ($request_stock as $rstock) {
                $error = JText::_('COM_VIRTUEMART_CART_PRODUCT_OUT_OF_STOCK');
                $this->setError($error); // Private error retrieved with getError is used only by addJS, so only the latest is fine
                $mainframe->enqueueMessage($error, 'error');
            }
            return false;
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
            $mainframe->redirect('index.php?option=com_virtuemart&view=cart', JText::_('COM_VIRTUEMART_CART_CHECKOUT_DATA_NOT_VALID'));
        }
    }

    function checkout() {
        if ($this->checkoutData()) {
            $mainframe = JFactory::getApplication();
            $mainframe->redirect('index.php?option=com_virtuemart&view=cart', JText::_('COM_VIRTUEMART_CART_CHECKOUT_DONE_CONFIRM_ORDER'));
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
            } elseif ($retVal === false) { // Missing data, ask for it (again)
                $mainframe->redirect('index.php?option=com_virtuemart&view=cart&task=edit_shipping');
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
            $mainframe->redirect('index.php?option=com_virtuemart', JText::_('COM_VIRTUEMART_CART_NO_PRODUCT'));
        } else {
            foreach ($this->products as $product) {
                $redirectMsg = $this->checkForQuantities($product, $product->quantity);
                if (!$redirectMsg) {
//					$this->setCartIntoSession();
                    $mainframe->redirect('index.php?option=com_virtuemart&view=cart', $redirectMsg);
                }
            }
        }

        // Check if a minimun purchase value is set
        if (($msg = $this->checkPurchaseValue()) != null) {
            $mainframe->redirect('index.php?option=com_virtuemart&view=cart', $msg);
        }

        //But we check the data again to be sure
        if (empty($this->BT)) {
            $mainframe->redirect('index.php?option=com_virtuemart&view=user&task=editaddresscheckout&addrtype=BT');
        } else {
            $redirectMsg = self::validateUserData();
            if ($redirectMsg) {
                $mainframe->redirect('index.php?option=com_virtuemart&view=user&task=editaddresscheckout&addrtype=BT', $redirectMsg);
            }
        }
        //Only when there is an ST data, test if all necessary fields are filled
        if (!empty($this->ST)) {
            $redirectMsg = self::validateUserData('ST');
            if ($redirectMsg) {
//				$this->setCartIntoSession();
                $mainframe->redirect('index.php?option=com_virtuemart&view=user&task=editaddresscheckout&addrtype=ST', $redirectMsg);
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
                $mainframe->redirect('index.php?option=com_virtuemart&view=cart&task=edit_coupon', $redirectMsg);
            }
        }

        /*        //Test Shipment
          //		if($this->virtuemart_shippingcarrier_id == 0){
          //			$this->setCartIntoSession();
          //			$mainframe->redirect('index.php?option=com_virtuemart&view=cart&task=edit_shipping',$redirectMsg);
          //        }
          // Ok, a shipper was selected, now make sure we can find a matching shipping rate for
          // the current order shipto and weight
          if(!class_exists('vmShipperPlugin')) require(JPATH_VM_SITE.DS.'helpers'.DS.'vmshipperplugin.php');
          JPluginHelper::importPlugin('vmshipper');
          $dispatcher = JDispatcher::getInstance();plgVmOnCheckoutCheckPaymentData
          $retValues = $dispatcher->trigger('plgVmOnCheckoutCheckShipperData', array('cart'=>$this));
          $this->virtuemart_shippingcarrier_id = -1;
          //dump($retValues,'$retValues');
          foreach ($retValues as $retVal) {
          if ($retVal !== null) {
          $this->virtuemart_shippingcarrier_id = $retVal;
          break; // When we've got a value, it's always a valid one, so we're done now
          }
          }
          if ($this->virtuemart_shippingcarrier_id < 0) {
          $this->virtuemart_shippingcarrier_id = 0;
          $this->setCartIntoSession();
          $mainframe->redirect('index.php?option=com_virtuemart&view=cart&task=edit_shipping',$redirectMsg);
          } */

        //Test Shipment and show shipment plugin
        if (empty($this->virtuemart_shippingcarrier_id)) {
            $mainframe->redirect('index.php?option=com_virtuemart&view=cart&task=edit_shipping', $redirectMsg);
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
                } elseif ($retVal === false) { // Missing data, ask for it (again)
                    $mainframe->redirect('index.php?option=com_virtuemart&view=cart&task=edit_shipping', $redirectMsg);
                    // 	NOTE: inactive plugins will always return null, so that value cannot be used for anything else!
                }
            }
        }
        //echo 'hier ';
        //Test Payment and show payment plugin
        if (empty($this->virtuemart_paymentmethod_id)) {
            $mainframe->redirect('index.php?option=com_virtuemart&view=cart&task=editpayment', $redirectMsg);
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
                } elseif ($retVal === false) { // Missing data, ask for it (again)
                    $mainframe->redirect('index.php?option=com_virtuemart&view=cart&task=editpayment', $redirectMsg);
                    // 	NOTE: inactive plugins will always return null, so that value cannot be used for anything else!
                }
            }
        }

        if ($this->tosAccepted !== 1) {
            $mainframe->redirect('index.php?option=com_virtuemart&view=cart', JText::_('COM_VIRTUEMART_CART_PLEASE_ACCEPT_TOS'));
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
                        , array('delimiter_userinfo', 'username', 'password', 'password2', 'address_type_name', 'address_type', 'user_is_vendor', 'agreed'));

        $redirectMsg = 0;
        foreach ($neededFields as $field) {
            if ($obj !== null && is_array($this->{$type})) {
                $this->{$type}[$field->name] = $obj->{$field->name};
            }
/*            if (empty($this->{$type}[$field->name]) && $field->name != 'virtuemart_state_id') {
            	$app = JFactory::getApplication();
            	$app->enqueueMessage('NOTICE: Enter for "' . $type . '" "' . $field->name . '" title: ' . JText::_($field->title) . ' and value: ' . $this->{$type}[$field->name] . ' but ' . $this->BT['first_name']);
               //$redirectMsg = 'Enter for "' . $type . '" "' . $field->name . '" title: ' . JText::_($field->title) . ' and value: ' . $this->{$type}[$field->name] . ' but ' . $this->BT['first_name'];
            } else {*/
                //This is a special test for the virtuemart_state_id. There is the speciality that the virtuemart_state_id could be 0 but is valid.
                if ($field->name == 'virtuemart_state_id') {

                    if (!class_exists('VirtueMartModelState'))
                        require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'state.php');
                    if (!$msg = VirtueMartModelState::testStateCountry($this->{$type}['virtuemart_country_id'], $this->{$type}['virtuemart_state_id'])) {
                        $redirectMsg = $msg;
                    }
                }
                //We may add here further Tests. Like if the email has the form a@b.xxx and so on
//             }
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
    private function confirmedOrder() {

        //Just to prevent direct call
        if ($this->_dataValidated && $this->_confirmDone) {
            if (!class_exists('VirtueMartModelOrders'))
                require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );

            $order = new VirtueMartModelOrders();
            if (($orderID = $order->createOrderFromCart($this)) === false) {
                $mainframe = JFactory::getApplication();
                JError::raiseWarning(500, $order->getError());
                $mainframe->redirect('index.php?option=com_virtuemart&view=cart');
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

            // TODO valerie TO DO  -- not finished
            $cart = $this->getCart();
            $dispatcher = JDispatcher::getInstance();
//             $returnValues = $dispatcher->trigger('plgVmAfterCheckoutDoPayment', array($orderID, 'cart' => $cart));
            $returnValues = $dispatcher->trigger('plgVmAfterCheckoutDoPayment', array($orderID, $cart));
            /*
             *  may be redirect is done by the payment plugin (eg: paypal) so we do not come back here
             *  if payment plugin echos a form, false = nothing happen, true= echo form ,
             */
/*
                 $fp = fopen("paypal.text", "a");
      foreach ($returnValues as $returnValue) {
        fwrite($fp, "Retourn plgVmAfterCheckoutDoPayment" .   $returnValue. "\n");
      }
          fclose($fp);
 * */

            $activeplugin = false;
            foreach ($returnValues as $returnValue) {
                if ($returnValue) {
                    $order->handleStockAFterStatusChanged($returnValue);
                    $activeplugin = true;
                    break;
                }
                // Returnvalue 'null' must be ignored; it's an inactive plugin so look for the next one
            }

            if (!$activeplugin)   {
                $mainframe = JFactory::getApplication();
                $mainframe->redirect('index.php?option=com_virtuemart&view=cart&layout=order_done',JText::_('COM_VIRTUEMART_CART_ORDERDONE_THANK_YOU'));
				}
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
     * Initialise the cart, ATTENTION, started by Roland but not finished, when someone needs it, dont be shy ;-) note by Max Milbers
     *
     * @author RolandD
     * @todo Make sure this gets called when a user is logged in
     * @access public
     */
/*    public function initCart() {
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
                    $products_in_cart[$contents[$i]['virtuemart_product_id']] = (int)($contents[$i]['virtuemart_product_id']);
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
						if (in_array((int)($contents[$i]['virtuemart_product_id']), $remove_products)) self::removeProductCart(array($i));
                    }
                }
            }
        }

        $this->setCartIntoSession();
    }*/

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

	function getCartAdressData($type){

        $userAddressData = new stdClass();
		if(!empty($this->$type)){
            $data = $this->$type;
            foreach ($data as $k => $v) {
                $userAddressData->{$k} = $v;
            }
        }
        return $userAddressData;
    }

    function saveAddressInCart($data, $type) {

        // VirtueMartModelUserfields::getUserFields() won't work
		if(!class_exists('VirtueMartModelUserfields')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'userfields.php' );
        $userFieldsModel = new VirtueMartModelUserfields();
        if ($type == 'ST') {
            $prepareUserFields = $userFieldsModel->getUserFields(
                            'shipping'
                            , array() // Default toggles
            );
        } else { // BT
            // The user is not logged in (anonymous), so we need tome extra fields
            $prepareUserFields = $userFieldsModel->getUserFields(
                            'account'
                            , array() // Default toggles
                            , array('delimiter_userinfo', 'name', 'username', 'password', 'password2', 'user_is_vendor') // Skips
            );

        }
        $address = array();

		if(is_array($data)){
            foreach ($prepareUserFields as $fld) {
                if(!empty($fld->name)){
                	$name = $fld->name;
					if(!empty($data[$name]))$address[$name] = $data[$name];
                }
            }

        } else {
            foreach ($prepareUserFields as $fld) {
                if(!empty($fld->name))$name = $fld->name; else  $name = '';
                $address[$name] = $data->{$name};
            }
        }

        $this->{$type} = $address;
        $this->setCartIntoSession();
    }

    function CheckAutomaticSelectedShipping() {

        $nbShipping = 0;
        if (!class_exists('vmShipperPlugin'))
            require(JPATH_VM_SITE . DS . 'helpers' . DS . 'vmshipperplugin.php');
        JPluginHelper::importPlugin('vmshipper');
        $dispatcher = JDispatcher::getInstance();
        $returnValues = $dispatcher->trigger('plgVmOnCheckAutomaticSelectedShipping', array('cart' => $this));
        foreach ($returnValues as $returnValue) {
            $nbShipping += $returnValue;
            if ((int)$returnValue )
                $virtuemart_shippingcarrier_id = $returnValue;
        }
        if ($nbShipping) {
            $this->virtuemart_shippingcarrier_id = $virtuemart_shippingcarrier_id;
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

}
