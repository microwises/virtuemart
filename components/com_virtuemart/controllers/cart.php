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
* @author Max Milbers
*/
class VirtueMartControllerCart extends JController {

    /**
    * Construct the cart
    *
    * @access public
    * @author Max Milbers
    */
	public function __construct() {
		parent::__construct();
		if (VmConfig::get('use_as_catalog',0)) {
			$app = JFactory::getApplication();
		    $app->redirect('index.php');
		} else {
			if(!class_exists('VirtueMartCart')) require(JPATH_VM_SITE.DS.'helpers'.DS.'cart.php');
			if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');
		}

	}


	/**
	* Show the main page for the cart
	*
	* @author Max Milbers
	* @author RolandD
	* @access public
	*/
	public function Cart() {
		/* Create the view */
		$view = $this->getView('cart', 'html');
		/* Add the default model */
		$this->addModelPath( JPATH_VM_ADMINISTRATOR.DS.'models' );
		$view->setModel( $this->getModel( 'user', 'VirtuemartModel' ), false );
		$view->setModel( $this->getModel( 'vendor', 'VirtuemartModel' ), false );
		$view->setModel( $this->getModel( 'userfields', 'VirtuemartModel' ), true );
		$view->setModel( $this->getModel( 'country', 'VirtuemartModel' ), true );
		$view->setModel( $this->getModel( 'state', 'VirtuemartModel' ), true );

		/* Set the layout */
		$layoutName = JRequest::getWord('layout', 'default');
		$view->setLayout($layoutName);

		/* Display it all */
		$view->display();
	}

	/**
	* Add the product to the cart
	*
	* @author RolandD
	* @author Max Milbers
	* @access public
	*/
	public function add() {
		$mainframe = JFactory::getApplication();
		if (VmConfig::get('use_as_catalog',0)) {
			$msg = JText::_('COM_VIRTUEMART_PRODUCT_NOT_ADDED_SUCCESSFULLY');
			$type = 'error';
		    $mainframe->redirect('index.php',$msg,$type);
		}
		$cart = VirtueMartCart::getCart();
		if($cart){
			if ($cart->add()) {
				$msg = JText::_('COM_VIRTUEMART_PRODUCT_ADDED_SUCCESSFULLY');
				$mainframe->enqueueMessage($msg);
				$type = '';
			} else {
				$msg = JText::_('COM_VIRTUEMART_PRODUCT_NOT_ADDED_SUCCESSFULLY');
				$type = 'error';
			}
//			if (JRequest::getVar('format','') =='raw' ) {
//				JRequest::setVar('layout','minicart','POST');
//				$this->cart();
//				//$view->display();
//				return ;
//			} else {
				$mainframe->enqueueMessage($msg, $type );
				$mainframe->redirect('index.php?option=com_virtuemart&view=cart');
//			}
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

		//maybe we should use $mainframe->close(); or jexit();instead of die;
		/* Load the cart helper */
		//require_once(JPATH_VM_SITE.DS.'helpers'.DS.'cart.php');
		$cart = VirtueMartCart::getCart();
		if($cart){
			// Get a continue link */
			$virtuemart_category_id = shopFunctionsF::getLastVisitedCategoryId();
			$categoryLink='';
			if($virtuemart_category_id>0){
				$categoryLink='&view=category&virtuemart_category_id='.$virtuemart_category_id;
			} else $categoryLink='';
			$continue_link = JRoute::_('index.php?option=com_virtuemart'.$categoryLink);

			if($cart->add()){
				$text = '<a href="'.$continue_link.'" >'.JText::_('COM_VIRTUEMART_CONTINUE_SHOPPING').'</a>';
				$text .= '<a style ="float:right;" href="'.JRoute::_("index.php?option=com_virtuemart&view=cart").'">'.JText::_('COM_VIRTUEMART_CART_SHOW').'</a>';
				echo json_encode (array('stat'=>1,'msg'=>$text));
			} else {
				$text = '<p>'.$cart->getError().'</p>';
				$text .= '<a href="'.$continue_link.'" >'.JText::_('COM_VIRTUEMART_CONTINUE_SHOPPING').'</a>';
				echo json_encode (array('stat'=>0,'msg'=>$text));
			}
		} else {
			echo (0);
		}
		jExit();
		$mainframe = JFactory::getApplication();
		$mainframe->close();

	}
	/**
	* Add the product to the cart, with JS
	*
	* @author Max Milbers
	* @access public
	*/
	public function viewJS(){

		/* Create the view */
		$view = $this->getView('cart', 'raw');
		/* Add the default model */
		$this->addModelPath( JPATH_VM_ADMINISTRATOR.DS.'models' );
		$view->setModel( $this->getModel( 'user', 'VirtuemartModel' ), false );
		$view->setModel( $this->getModel( 'vendor', 'VirtuemartModel' ), false );
		$view->setModel( $this->getModel( 'userfields', 'VirtuemartModel' ), true );
		$view->setModel( $this->getModel( 'country', 'VirtuemartModel' ), true );
		$view->setModel( $this->getModel( 'state', 'VirtuemartModel' ), true );

		/* Set the layout */
		$layoutName = JRequest::getWord('layout', 'default');
		$view->setLayout($layoutName);

		/* Display it all */
		$view->display();

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

		$this->addModelPath( JPATH_VM_ADMINISTRATOR.DS.'models' );
		$view->setModel($this->getModel('coupon', 'VirtuemartModel'), true);

		/* Display it all */
		$view->display();
	}

	/**
	 * Store the coupon code in the cart
	 * @author Oscar van Eijk
	 */
	public function setcoupon(){
		$mainframe = JFactory::getApplication();
		/* Get the coupon_code of the cart */
		$coupon_code= JRequest::getInt('coupon_code', '');
		if($coupon_code){
			//Now set the shipping rate into the cart
			$cart = VirtueMartCart::getCart();
			if($cart){
				$msg = $cart->setCouponCode($coupon_code);
				if (!empty($msg)) {
					$mainframe->enqueueMessage($msg, 'error');
				}
//				$cart->setDataValidation(); //Not needed already done in the getCart function
				if($cart->getInCheckOut()){
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
	public function edit_shipping(){

		/* Create the view */
		$view = $this->getView('cart', 'html');
		$view->setLayout('select_shipper');

		$this->addModelPath( JPATH_VM_ADMINISTRATOR.DS.'models' );
		$view->setModel($this->getModel('shippingcarrier', 'VirtuemartModel'), true);

		$view->setModel( $this->getModel( 'user', 'VirtuemartModel' ), false );
		$view->setModel( $this->getModel( 'userfields', 'VirtuemartModel' ), true );

		/* Display it all */
		$view->display();
	}

	/**
	 * Sets a selected shipper to the cart
	 *
	 * @author Max Milbers
	 */
	public function setshipping(){

		/* Get the shipper ID from the cart */
		$shipper_id = JRequest::getInt('shipper_id', '0');
		if($shipper_id){
			//Now set the shipper ID into the cart
			$cart = VirtueMartCart::getCart();
			if($cart){
				$cart->setShipper($shipper_id);
				if($cart->getInCheckOut()){
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
		$view->setLayout('select_payment');

		$this->addModelPath( JPATH_VM_ADMINISTRATOR.DS.'models' );
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
	function setpayment(){

		/* Get the payment id of the cart */
		//Now set the shipping rate into the cart
		$cart = VirtueMartCart::getCart();
		if($cart){
			if(!class_exists('vmPaymentPlugin')) require(JPATH_VM_SITE.DS.'helpers'.DS.'vmpaymentplugin.php');
			JPluginHelper::importPlugin('vmpayment');
			//Some Paymentmethods needs extra Information like
			$virtuemart_paymentmethod_id= JRequest::getInt('virtuemart_paymentmethod_id', '0');
			$cart->setPaymentMethod( $virtuemart_paymentmethod_id );

			//Add a hook here for other payment methods, checking the data of the choosed plugin
			$_dispatcher = JDispatcher::getInstance();
			$_retValues = $_dispatcher->trigger('plgVmOnPaymentSelectCheck', array('cart'=>$cart));

			foreach ($_retValues as $_retVal) {
				if ($_retVal === true || is_null($_retVal)) {
					break; // Plugin completed succesful; nothing else to do
				} elseif ($_retVal === false) {
					// TODO Max; what todo of the plugin failed? Just nothing we can set here a msg
//					if ($redirect) { self::Cart(); } else { return false; } // Plugin failed
					$msg = JText::_('COM_VIRTUEMART_CART_SETPAYMENT_PLUGIN_FAILED');
				} elseif (is_array($_retVal)) {
					// We got modified cart data back from the plugin
//					$cart = $_retVal;		This seems to be a bit evil, does the plugin actually returns a cart?
					break;
// NOTE: inactive plugins will always return null, so that value cannot be used for anything else!
				} elseif (is_null($_retVal)) {
					continue; // This plugin was skipped
				} else {
					continue; // Other values not yet implemented
				}
			}
//			$cart->setDataValidation();	//Not needed already done in the getCart function
			if($cart->getInCheckOut()){
				$mainframe = JFactory::getApplication();
				$mainframe->redirect('index.php?option=com_virtuemart&view=cart&task=checkout',$msg);
			}
		}
		self::Cart();

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
		if ($cart->removeProductCart()) $mainframe->enqueueMessage(JText::_('COM_VIRTUEMART_PRODUCT_REMOVED_SUCCESSFULLY'));
		else $mainframe->enqueueMessage(JText::_('COM_VIRTUEMART_PRODUCT_NOT_REMOVED_SUCCESSFULLY'), 'error');

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
		if ($cartModel->updateProductCart()) $mainframe->enqueueMessage(JText::_('COM_VIRTUEMART_PRODUCT_UPDATED_SUCCESSFULLY'));
		else $mainframe->enqueueMessage(JText::_('COM_VIRTUEMART_PRODUCT_NOT_UPDATED_SUCCESSFULLY'), 'error');

		$mainframe->redirect('index.php?option=com_virtuemart&view=cart');
	}

	/**
	 * Checks for the data that is needed to process the order
	 *
	 * @author Max Milbers
	 *
	 *
	 */
	public function checkout(){
		//Tests step for step for the necessary data, redirects to it, when something is lacking

		$cart = VirtueMartCart::getCart();
		if($cart && !VmConfig::get('use_as_catalog',0)){
			$cart->checkout();
		}
	}

	/**
	 * Executes the confirmDone task,
	 * cart object checks itself, if the data is valid
	 *
	 * @author Max Milbers
	 *
	 *
	 */
	public function confirm(){

		//Use false to prevent valid boolean to get deleted
		$cart = VirtueMartCart::getCart(false);
		if($cart){
			$cart->confirmDone();
		} else {
	 		$mainframe = JFactory::getApplication();
	 		$mainframe->redirect('index.php?option=com_virtuemart&view=cart','Cart data not valid');
		}
	}

	function cancel(){
	 	$mainframe = JFactory::getApplication();
	 	$mainframe->redirect('index.php?option=com_virtuemart&view=cart','Cancelled');
	}




}
 //pure php no Tag
