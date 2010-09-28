<?php
/**
*
* Controller for the cart
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

// Load the controller framework
jimport('joomla.application.component.controller');

/**
* Controller for the cart view
*
* @package VirtueMart
* @subpackage Cart
* @author RolandD
*/
class VirtueMartControllerCart extends JController {

    /**
    * Construct the cart
    *
    * @access public
    * @author RolandD
    */
	public function __construct() {
		parent::__construct();
	}

	
	/**
	* Show the main page for the cart
	*
	* @author Max Milbers
	* @author RolandD
	* @access public
	*/
	public function Cart($confirmed=false) {
		/* Create the view */
		$view = $this->getView('cart', 'html');

		/* Add the default model */
//		$cartModel = VirtueMartCart::getCart(false);
//		$view->setModel($cartModel, true);
		$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR .DS.'models' );
		$view->setModel( $this->getModel( 'user', 'VirtuemartModel' ), false );
		$view->setModel( $this->getModel( 'userfields', 'VirtuemartModel' ), true );
		$view->setModel( $this->getModel( 'country', 'VirtuemartModel' ), true );
		$view->setModel( $this->getModel( 'state', 'VirtuemartModel' ), true );

		/* Set the layout */
		$layoutName = JRequest::getVar('layout', 'cart');
		$view->setLayout($layoutName);

		if($confirmed){
			$ftask ='confirm';
		} else {
			$ftask ='checkout';
		}

		$view->assignRef('fTask', $ftask);

		/* Display it all */
		$view->display();
	}

	/**
	* Add the product to the cart
	*
	* @author RolandD
	* @access public
	*/
	public function add() {
		$mainframe = JFactory::getApplication();
		/* Load the cart helper */
		$this->getModel('productdetails');
		$cart = VirtueMartCart::getCart();
		if($cart){
			if ($cart->add()) $mainframe->enqueueMessage(JText::_('PRODUCT_ADDED_SUCCESSFULLY'));
			else $mainframe->enqueueMessage(JText::_('PRODUCT_NOT_ADDED_SUCCESSFULLY'), 'error');
			$mainframe->redirect('index.php?option=com_virtuemart&view=cart');			
		} else {
			$mainframe->enqueueMessage('Cart does not exist?', 'error');
		}

	}

	/**
	* Add the product to the cart, with JS
	*
	* @author Max Milbers
	* @access public
	*/
	public function addJS(){

		/* Load the cart helper */
		$cart = VirtueMartCart::getCart();
		if($cart){
			if($cart->add()){
				 echo (1);
				 die;
			} 
		}
		echo (0);
		die;
	}

	/**
	 * For selecting couponcode to use, opens a new layout
	 *
	 * @author Max Milbers
	 */
	public function editcoupon(){
		/* Create the view */
		$view = $this->getView('cart', 'html');
		$view->setLayout('editcoupon');

		$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR .DS.'models' );
		$view->setModel($this->getModel('coupon', 'VirtuemartModel'), true);

		/* Display it all */
		$view->display();
	}

	/**
	 *
	 *
	 */
	public function setcoupon(){

		/* Get the coupon_id of the cart */
		$coupon_id = JRequest::getVar('coupon_id', '0');
		if($coupon_id){
			//Now set the shipping rate into the cart
			$cart = VirtueMartCart::getCart();
			if($cart){
				$cart->coupon_id=$coupon_id;
				$cart->dataValidated = false;
				$cart->setCartIntoSession();
				if($cart->inCheckOut){
					$mainframe = JFactory::getApplication();
					$mainframe->redirect('index.php?option=com_virtuemart&view=cart&task=checkout');
				}
			}
		}
		self::Cart();

	}

	/**
	 * For selecting shipper, opens a new layout
	 *
	 * @author Max Milbers
	 */
	public function editshipping(){

		/* Create the view */
		$view = $this->getView('cart', 'html');
		$view->setLayout('selectshipper');

		$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR .DS.'models' );
		$view->setModel($this->getModel('shippingcarrier', 'VirtuemartModel'), true);

//		$view->setModel( VirtueMartCart::getCart());
		$view->setModel( $this->getModel( 'user', 'VirtuemartModel' ), false );
		$view->setModel( $this->getModel( 'userfields', 'VirtuemartModel' ), true );
//		$view->setModel( $this->getModel( 'country', 'VirtuemartModel' ), true );
//		$view->setModel( $this->getModel( 'state', 'VirtuemartModel' ), true );
//		dump($view,'I am in task editshipping');
		/* Display it all */
		$view->display();
	}

	/**
	 * Sets a selected shipper to the cart
	 *
	 * @author Max Milbers
	 */
	public function setshipping(){

		/* Get the shipping rate of the cart */
		$shipping_rate_id = JRequest::getVar('shipping_rate_id', '0');

		if($shipping_rate_id){
			//Now set the shipping rate into the cart
			$cart = VirtueMartCart::getCart();
			if($cart){
				$cart->shipping_rate_id=$shipping_rate_id;
				$cart->dataValidated = false;
				$cart->setCartIntoSession();
				if($cart->inCheckOut){
					$mainframe = JFactory::getApplication();
					$mainframe->redirect('index.php?option=com_virtuemart&view=cart&task=checkout');
				}
			}
		}
		self::Cart();
	}

	/**
	 * To select a payment method
	 *
	 * @author Max Milbers
	 */
	public function editpayment(){
		/* Create the view */
		$view = $this->getView('cart', 'html');
		$view->setLayout('selectpayment');

		$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR .DS.'models' );
		$view->setModel($this->getModel('paymentmethod', 'VirtuemartModel'), true);

		/* Display it all */
		$view->display();
	}

	/**
	 * To set a payment method
	 *
	 * @author Max Milbers
	 * @author Oscar van Eijk
	 */
	function setpayment($redirect=true){

		/* Get the payment id of the cart */
		//Now set the shipping rate into the cart
		$cart = VirtueMartCart::getCart();
		if($cart){
			JPluginHelper::importPlugin('vmpayment');
			//Some Paymentmethods needs extra Information like
			$cart->paym_id= JRequest::getVar('paym_id', '0');

			//Add a hook here for other payment methods, checking the data of the choosed plugin
			$_dispatcher = JDispatcher::getInstance();
			$_retValues = $_dispatcher->trigger('plgVmOnPaymentSelectCheck', array('cart'=>$cart));
			$cart->dataValidated = false;
			$cart->setCartIntoSession();
			foreach ($_retValues as $_retVal) {
				if ($_retVal === true) {
					break; // Plugin completed succesful; nothing else to do
				} elseif ($_retVal === false) {
					// TODO Max; what todo of the plugin failed? Just nothing we can set here a msg
//					if ($redirect) { self::Cart(); } else { return false; } // Plugin failed
					$msg = 'Plugin failed';
				} elseif (is_array($_retVal)) {
					// We got modified cart data back from the plugin
					$cart = $_retVal;
					$cart->dataValidated = false;
					$cart->setCartIntoSession();
					break;
// Checks below outcommented since we're at the end of out loop anyway :-/
// Remove comments if newchecks need to be implemented.
// NOTE: inactive plugins will always return null, so that value cannot be used for anything else! 
//				} elseif ($_retVal === null) {
//					continue; // This plugin was skipped
//				} else {
//					continue; // Other values not yet implemented
				}
			}
			if($cart->inCheckOut){	
				if($redirect){
					$mainframe = JFactory::getApplication();
					$mainframe->redirect('index.php?option=com_virtuemart&view=cart&task=checkout',$msg);
				} else {
					return true;
				}
			}
		}
		if($redirect){
			self::Cart();
		} else {
			return false;
		}
	}


	/**
	* Delete a product from the cart
	*
	* @author RolandD
	* @access public
	*/
	public function delete() {
		$mainframe = JFactory::getApplication();
		/* Load the cart helper */
		$cart = VirtueMartCart::getCart();
		if ($cart->removeProductCart()) $mainframe->enqueueMessage(JText::_('PRODUCT_REMOVED_SUCCESSFULLY'));
		else $mainframe->enqueueMessage(JText::_('PRODUCT_NOT_REMOVED_SUCCESSFULLY'), 'error');

		$mainframe->redirect('index.php?option=com_virtuemart&view=cart');
	}

	/**
	* Delete a product from the cart
	*
	* @author RolandD
	* @access public
	*/
	public function update() {
		$mainframe = JFactory::getApplication();
		/* Load the cart helper */
		$cartModel = VirtueMartCart::getCart();
		if ($cartModel->updateProductCart()) $mainframe->enqueueMessage(JText::_('PRODUCT_UPDATED_SUCCESSFULLY'));
//		if ($cartModel->add()) $mainframe->enqueueMessage(JText::_('PRODUCT_UPDATED_SUCCESSFULLY'));
		else $mainframe->enqueueMessage(JText::_('PRODUCT_NOT_UPDATED_SUCCESSFULLY'), 'error');

		$mainframe->redirect('index.php?option=com_virtuemart&view=cart');
	}

	/**
	 * Checks for the data that is needed to process the order
	 *
	 * @author Max Milbers
	 *
	 *
	 */

	public function checkout($confirmDone=false){

		dump(JRequest::get('post'),'my Post data in checkout');
		//Tests step for step for the necessary data, redirects to it, when something is lacking
		$cart = VirtueMartCart::getCart(false);
        dump($cart,'My cart in the checkout');
		if($cart){

			$mainframe = JFactory::getApplication();
			if( $cart->products == 0){
				$mainframe->redirect('index.php?option=com_virtuemart',JText::_('VM_CART_NO_PRODUCT'));
			}

			//But we check the data again to be sure
			if(empty($cart->BT)){
				$mainframe->redirect('index.php?option=com_virtuemart&view=user&task=editaddresscheckout&addrtype=BT');
			}else {
				//just for testing
				$anonym = true;
				$redirectMsg = self::validateUserData($cart,$anonym);
				if($redirectMsg){
					$mainframe->redirect('index.php?option=com_virtuemart&view=user&task=editaddresscheckout&addrtype=BT',$redirectMsg);
				}
			}
			//Only when there is an ST data, test if all necessary fields are filled
			if(!empty($cart->ST)){
				$anonym = true;
				$redirectMsg = self::validateUserData($cart,$anonym,'ST');
				if($redirectMsg){
					$mainframe->redirect('index.php?option=com_virtuemart&view=user&task=editaddresscheckout&addrtype=ST',$redirectMsg);
				}
			}

			//Test Shipment
			if(empty($cart->shipping_rate_id)){
				$cart->inCheckOut = true;
				$confirmDone=false;
				$cart->setCartIntoSession();
				$this->editshipping();
				return;
			}

			//Test Payment and show payment plugin
			if(empty($cart->paym_id)){
				$cart->inCheckOut = true;
				$confirmDone=false;
				$cart->setCartIntoSession();
				$this->editpayment();
				return;
			}

			JPluginHelper::importPlugin('vmpayment');
			//Add a hook here for other payment methods, checking the data of the choosed plugin
			$_dispatcher = JDispatcher::getInstance();
			$_retValues = $_dispatcher->trigger('plgVmOnCheckoutCheckPaymentData', array('cart'=>$cart));
			foreach ($_retValues as $_retVal) {
				if ($_retVal === true) {
					break; // Plugin completed succesful; nothing else to do
				} elseif ($_retVal === false) { // Missing data, ask for it (again)
					$cart->inCheckOut = true;
					$confirmDone=false;
					$this->editpayment();
					// Checks below outcommented since we're at the end of out loop anyway :-/
// Remove comments if newchecks need to be implemented.
// NOTE: inactive plugins will always return null, so that value cannot be used for anything else! 
//				} elseif ($_retVal === null) {
//					continue; // This plugin was skipped
//				} else {
//					continue; // Other values not yet implemented
				}
			}

			//Show cart and checkout data overview
			$cart->inCheckOut = false;
			$cart->dataValidated = true;

			$cart->setCartIntoSession();
			if (function_exists('dumpTrace')) { // J!Dump is installed
				dump($cart,'Cart runned through checkout and confirmDone '.$confirmDone);
			}
			if($confirmDone){
				$this->confirmedOrder();
			} else {
				$this->Cart(true);
			}
		}
	}

	public function confirm(){

		$cart = VirtueMartCart::getCart(false);

		if($cart->dataValidated === true){
			$this->checkout(true);
		} else {
			self::Cart();
		}

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
		require_once JPATH_COMPONENT . DS . 'helpers' . DS . 'order.php';

		//Here we do the task, like storing order information
		$cart = VirtueMartCart::getCart(false);

		//Just to prevent direct call
		if($cart->dataValidated){
			$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR .DS.'models' );
			$view = $this->getView('cart', 'html');
			$view->setModel( $this->getModel( 'orders', 'VirtuemartModel' ), true );  //TODO we need the oder_number in the mail
			$order = $this->getModel( 'orders', 'VirtuemartModel' );
			$_orderID = $order->createOrderFromCart($cart);
			$cart->order_id= $_orderID;

			$this->doEmail($cart,$_orderID);

			//We save the old stuff
			$BT = $cart->BT;
			$ST = $cart->ST;
			$paym_id = $cart->paym_id;
			$shipping_rate_id = $cart->shipping_rate_id;

			//Empty the cart and fill it again.
			
			$cart = VirtueMartCart::getCart();
			$cart->emptyCart();
			$cart->BT = $BT;
			$cart->ST = $ST;
			$cart->paym_id = $paym_id;
			$cart->shipping_rate_id = $shipping_rate_id;
			$cart->dataValidated=false;
			$cart->setCartIntoSession();

			/* Display it all */
			$view = $this->getView('cart', 'html');
			$view->setLayout('orderdone');
	//		$view->display();
		} else {
			JError::raiseNotice(1, 'Validation of Data failed');
		}

	}

	/**
	 * Prepares the body for shopper and vendor, renders them and sends directly the emails
	 *
	 * @author Max Milbers
	 *
	 * @param CartArray $cart
	 * @param boolean When one email does not work, it gives a false back
	 *
	 */
	function doEmail($cart,$_orderID){

		/* Create the view */
		$view = $this->getView('cart', 'html');

//		$view->setModel(VirtueMartCart::getCart(),true);
		$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR .DS.'models' );
		$view->setModel( $this->getModel( 'user', 'VirtuemartModel' ), false );
		$view->setModel( $this->getModel( 'userfields', 'VirtuemartModel' ), true );
		$view->setModel( $this->getModel( 'orders', 'VirtuemartModel' ), true );  //TODO we need the oder_number in the mail

		$view->setModel( $this->getModel( 'country', 'VirtuemartModel' ), true );
		$view->setModel( $this->getModel( 'state', 'VirtuemartModel' ), true );

		$store = $this->getModel( 'store', 'VirtuemartModel' );
		$view->setModel( $store, true );

		$view->setLayout('mailshopper');

		$error=false;
		/* Render it all */
		ob_start();
		$view->display();
		$bodyShopper = ob_get_contents();
		ob_end_clean();
		$sendShopper = shopFunctionsF::sendMail($bodyShopper,$cart->BT['email']); //TODO MX set vendorId
		if ( $sendShopper !== true ) {
			$error=true;
			//TODO set message, must be a raising one
		}

		$view->setLayout('mailvendor');

		/* Render it all */
		ob_start();
		$view->display();
		$bodyVendor = ob_get_contents();
		ob_end_clean();

		$store->setId($cart->vendorId);
		$vendor=$store->getStore();
		$sendVendor = shopFunctionsF::sendMail($bodyVendor,$vendor->jUser->email); //TODO MX set vendorId
		if ( $sendShopper !== true ) {
			$error=true;
			//TODO set message, must be a raising one
		}


		//Just for developing
		echo '<br />$bodyShopper '.$bodyShopper;
		echo '<br />$bodyVendor '.$bodyVendor;
		return $error;
	}


	/**
	 * does not work with self::renderView($view), maybe with $this
	 * @author Max Milbers
	 */
	function renderView($view){
		ob_start();
		$view->display();
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Test userdata if valid
	 *
	 * @author Max Milbers
	 * @param String if BT or ST
	 * @return redirectMsg, if there is a redirectMsg, the redirect should be executed after
	 */
	 private function validateUserData($cart,$anonym=false,$type='BT'){

//		require_once(JPATH_COMPONENT.DS.'helpers'.DS.'user_info.php');
		$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR .DS.'models' );
		$_userFieldsModel = $this->getModel( 'userfields', 'VirtuemartModel' );
		if($type=='BT') $fieldtype = 'account'; else $fieldtype = 'shipping';
		$neededFields = $_userFieldsModel->getUserFields(
									 $fieldtype  //TODO we need, agreed also
									, array('required'=>true,'delimiters'=>true,'captcha'=>true,'system'=>false)
				, array('delimiter_userinfo', 'username', 'password', 'password2', 'address_type_name','address_type','user_is_vendor'));
//		$neededFields = user_info::getUserFields($type);  //$anonym has no function atm
		$redirectMsg=0;
		foreach($neededFields as $field){

			if(empty($cart->{$type}[$field->name]) && $field->name!='state_id'){
				$redirectMsg = 'Enter for "'.$type.'" "'.$field->name.'" title: '.JText::_($field->title).' and value: '.$cart->{$type}[$field->name].' but '.$cart->BT['first_name'];
			} else {
				//This is a special test for the state_id. There is the speciality that the state_id could be 0 but is valid.
				if($field->name=='state_id'){
					require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'models'.DS.'state.php');
					if(!$msg=VirtueMartModelState::testStateCountry($cart->{$type}['country_id'],$cart->{$type}['state_id'])){
						$redirectMsg = $msg;
					}
				}
				//We may add here further Tests. Like if the email has the form a@b.xxx and so on
			}
		}
	 	return $redirectMsg;
	 }

	 function cancel(){
	 	$mainframe = JFactory::getApplication();
	 	$mainframe->redirect('index.php?option=com_virtuemart&view=cart','Cancelled');
	}


	/**
	 * This function is just to get the userfields with name and title which are required for shopping something
	 * Of course this is not the right place todo this. Maybe it should be in the userfield model in the backend.
	 * But for simplification, developing and performance reasons, I place it here.
	 *
	 * @author Max Milbers
	 */
//	private function getRequiredUserFields(){
//
//		$db = JFactory::getDBO();
//		$q = 'SELECT `name`,`title` FROM #__vm_userfield WHERE `required`="1" AND `shipping`="1"';
//		$db->setQuery($q);
//		return $db->loadAssocList();
//	}

}
 //pure php no Tag
