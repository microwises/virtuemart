<?php
/**
*
* Controller for the cart
*
* @package	VirtueMart
* @subpackage Cart
* @author RolandD
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
		// Force the default task kto cart() in order to make redirects work
		$this->registerTask('__default', 'cart');
	}

	/**
	* Show the main page for the cart 
	* 
	* @author RolandD 
	* @access public 
	*/
	public function Cart() {
		/* Create the view */
		$view = $this->getView('cart', 'html');
		
		/* Add the default model */
		$view->setModel($this->getModel('cart', 'VirtuemartModel'), true);
		$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR .DS.'models' );
		$view->setModel( $this->getModel( 'user', 'VirtuemartModel' ), false );
//		$view->setModel( $this->getModel( 'userfields', 'VirtuemartModel' ), true );
		
		/* Set the layout */
		$layoutName = JRequest::getVar('layout', 'cart');
		$view->setLayout($layoutName);
		
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
		$model = $this->getModel('cart');
		if ($model->add()) $mainframe->enqueueMessage(JText::_('PRODUCT_ADDED_SUCCESSFULLY'));
		else $mainframe->enqueueMessage(JText::_('PRODUCT_NOT_ADDED_SUCCESSFULLY'), 'error');
		$mainframe->redirect('index.php?option=com_virtuemart&view=cart');
		
	}

	/**
	* Add the product to the cart, with JS
	* 
	* @author Max Milbers 
	* @access public 
	*/	
	public function addJS(){

		/* Load the cart helper */
		$model = $this->getModel('cart');
		if($model->add()) echo (1); else echo (0);

		die;
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
			$cart = cart::getCart();
			if($cart){
				$cart['shipping_rate_id']=$shipping_rate_id;
				$cart['dataValidated'] = false;
				cart::setCart($cart);
				if($cart['inCheckOut']){
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
	 */
	function setpayment(){
		
		/* Get the payment id of the cart */
			//Now set the shipping rate into the cart
			$cart = cart::getCart();
			if($cart){
				//Some Paymentmethods needs extra Information like
				$cart['paym_id']= JRequest::getVar('paym_id', '0');

				$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR .DS.'models' );
				$paym_model = $this->getModel('paymentmethod','VirtuemartModel');
				if($paym_model->hasCreditCard($cart['paym_id'])->paym_creditcards){
					$cc_model = $this->getModel('creditcard', 'VirtuemartModel');
					$cart['creditcard_id']= JRequest::getVar('creditcard', '0');
					$cart['cc_name']= JRequest::getVar('cart_cc_name', '');
					$cart['cc_number']= JRequest::getVar('cart_cc_number', '');
					$cart['cc_code']= JRequest::getVar('cart_cc_code', '');
					$cart['cc_expire_month']= JRequest::getVar('cart_cc_expire_month', '');
					$cart['cc_expire_year']= JRequest::getVar('cart_cc_expire_year', '');
					if(!empty($cart['creditcard_id'])){
						$cc_ = $cc_model->getCreditCard($cart['creditcard_id']);
						$cc_type = $cc_->creditcard_code;
						$cc_model->validate_creditcard_data($cc_type,$cart['cc_number']);
					}

				}
				$cart['dataValidated'] = false;
				cart::setCart($cart);
				if($cart['inCheckOut']){
					$mainframe = JFactory::getApplication();
					$mainframe->redirect('index.php?option=com_virtuemart&view=cart&task=checkout');
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
		if (cart::removeProductCart()) $mainframe->enqueueMessage(JText::_('PRODUCT_REMOVED_SUCCESSFULLY'));
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
		if (cart::updateProductCart()) $mainframe->enqueueMessage(JText::_('PRODUCT_UPDATED_SUCCESSFULLY'));
		else $mainframe->enqueueMessage(JText::_('PRODUCT_NOT_UPDATED_SUCCESSFULLY'), 'error');
		
		$mainframe->redirect('index.php?option=com_virtuemart&view=cart');
	}
	
	/**
	 * Checks for the data that is needed to process the order
	 * 
	 * @author Max Milbers
	 */
	 
	public function checkout(){
		

		//Tests step for step for the necessary data, redirects to it, when something is lacking
		$cart = cart::getCart();
		
		if($cart){
			// Shipto is selected in the first cartview
			if ($_shipto = JRequest::getVar('shipto', '')) {
				$cart['adress_shipto_id'] = $_shipto;
			}
			if ($_billto = JRequest::getVar('billto', '')) {
				$cart['adress_billto_id'] = $_billto;
			}
			cart::setCart($cart);
			$mainframe = JFactory::getApplication();
			if($cart['dataValidated'] === true){
				$mainframe->redirect('index.php?option=com_virtuemart&view=cart&task=confirmedOrder');
			}
			
//			echo 'Print: <pre>'.print_r($cart).'</pre>';
			//Test Shipment and Payment addresses
			// TODO Check is we're an anynomous user without BT address
			if(empty($cart['adress_billto_id'])){
				$cart['inCheckOut'] = true;
				cart::setCart($cart);				
				$mainframe->redirect('index.php?option=com_virtuemart&view=user&task=editaddress');
			}
			
			//Test Shipment
			if(empty($cart['shipping_rate_id'])){
				$cart['inCheckOut'] = true;
				cart::setCart($cart);
//				$this->editshipping();
				$mainframe->redirect('index.php?option=com_virtuemart&view=cart&task=editshipping');	
			}

			//Test Payment and show payment plugin
			if(empty($cart['paym_id'])){
				$cart['inCheckOut'] = true;

				cart::setCart($cart);
				//Another thing oscar, can you explain me why we need a redirect? and cant call it directly?
//				$this->editpayment();
				$mainframe->redirect('index.php?option=com_virtuemart&view=cart&task=editpayment');
			}
			
			require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'models'.DS.'paymentmethod.php');
			if(VirtueMartModelPaymentmethod::hasCreditCard($cart['paym_id'])){
				if(empty($cart['creditcard_id']) || 
					empty($cart['cc_name']) || 
					empty($cart['cc_number']) || 
					empty($cart['cc_code']) || 
					empty($cart['cc_expire_month']) ||  
					empty($cart['cc_expire_year'])){
					$mainframe->redirect('index.php?option=com_virtuemart&view=cart&task=editpayment');
				}
			}
		
			//Show cart and checkout data overview
			$cart['inCheckOut'] = false;
			$cart['dataValidated'] = true;
			cart::setCart($cart);
			$mainframe = JFactory::getApplication();
			$mainframe->redirect('index.php?option=com_virtuemart&view=cart');		
		}
		$mainframe->redirect('index.php?option=com_virtuemart&view=cart');	
	}
	
	
	public function confirmedOrder(){
	
		//Here we do the task, like storing order information
		$cart = cart::getCart();
		
		$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR .DS.'models' );


//		if (!empty($cart['adress_billto_id'])){
//			$user_model = $this->getModel('user','VirtuemartModel');
//			$user_model->setId($_currentUser->get('id'));
//			$billto=$user_model->getUserAddress('','','BT');
//		}else{
//			//todo anonymous
////			$userFieldsModel = $this->getModel('userfields', 'VirtuemartModel');
////			$userFieldsModel->setId($_currentUser->get('id'));
//			for ($_i = 0, $_n = count($userFieldsModel->userFields['fields']); $_i < $_n; $_i++) {
//				//here is the loop through the userdata fields,,,,
//			}
//		}
//		
//		if (!empty($cart['adress_shipto_id'])){
//			$user_model = $this->getModel('user','VirtuemartModel');
//			$user_model->setId($_currentUser->get('id'));
//			$shipto=$user_model->getUserAddress('','','ST');
//		}else{
//			//todo anonymous
//			//if also empty, but billto is valid, take billto
//		}
		
		//Call payment plugins
		
		//Store order
		
		//send email
		$body = $this->prepareEmailBody($cart);
		echo 'The body: '.$body;
		$sentmail = $this->sendMail($cart,$body);
		
		/* Create the view */
		$view = $this->getView('cart', 'html');
		$view->setLayout('orderdone');
		
		/* Display it all */
		$view->display();
	}
	
	/**
	 * For showing the calculation of the prices only
	 * 
	 * @author Max Milbers
	 */
	public function cartPriceList(){
	
		/* Create the view */
		$view = $this->getView('cart', 'html');
		$view->setLayout('pricelist');
		
		$view->setModel($this->getModel('cart', 'VirtuemartModel'), true);
		$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR .DS.'models' );
		$view->setModel( $this->getModel( 'user', 'VirtuemartModel' ), false );
//		$view->setModel( $this->getModel( 'userfields', 'VirtuemartModel' ), true );
		
		/* Display it all */
		$view->display();
	}
	
	/**
	 * For showing the calculation of the prices only
	 * 
	 * @author Max Milbers
	 */
	public function cartHeaderMail(){
	
		/* Create the view */
		$view = $this->getView('cart', 'html');
		$view->setLayout('headermail');
		
		$view->setModel($this->getModel('cart', 'VirtuemartModel'), true);
		$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR .DS.'models' );
		$view->setModel( $this->getModel( 'user', 'VirtuemartModel' ), false );
//		$view->setModel( $this->getModel( 'userfields', 'VirtuemartModel' ), true );
		$view->setModel( $this->getModel( 'store', 'VirtuemartModel' ), true );
		
		/* Display it all */
		$view->display();
	}
	
	function prepareEmailBody($cart){
		
		$body = $this -> cartHeaderMail();
		$body .= $this -> cartPriceList();
		//We may get this path from the shop config
//		$body = self::get_output(JPATH_COMPONENT.DS.'views'.DS.'cart'.DS.'tmpl'.DS.'confirmation_email.php');  
		return $body;
	}
	
	function get_output($file){
		ob_start();
		include($file);
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}



	function sendMail($cart,$body){
		
		$mailer =& JFactory::getMailer();
		
		//This is now just without multivendor
		$config =& JFactory::getConfig();
		$sender = array( 
    		$config->getValue( 'config.mailfrom' ),
    		$config->getValue( 'config.fromname' ) ); 
 
		$mailer->setSender($sender);
		
//		$user =& JFactory::getUser();
//		$recipient = $user->email;
 		$recipient = $cart['BT']['email'];
 
		$mailer->addRecipient($recipient);
		
//		$body   = "Your body string\nin double quotes if you want to parse the \nnewlines etc";
		$mailer->setSubject('Your subject string');
		
		// Optional file attached
//		if($downloadable){
//			$mailer->addAttachment();
//		}
		
		$mailer->isHTML(true);
		$mailer->setBody($body);
		
		// Optionally add embedded image  //TODO adjust paths
		$store = $this->getModel('store','VirtuemartModel');
		$store->setId($cart['vendor_id']);
		$_store = $store->getStore();
		$mailer->AddEmbeddedImage( VmConfig::get('media_path').DS.$_store->vendor_full_image, 'base64', 'image/jpeg' );
		
		$send =& $mailer->Send();
		if ( $send !== true ) {
		    echo 'Error sending email: ' . $send->message;
		} else {
		    echo 'Mail sent';
		}
		
	}
}
 //pure php no Tag
