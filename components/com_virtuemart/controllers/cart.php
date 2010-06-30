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
//		$this->registerTask('__default', 'cart');
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
		$view->setModel( $this->getModel( 'userfields', 'VirtuemartModel' ), true );
		
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
	function setpayment($redirect=true){
		
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
					if($redirect){
						$mainframe->redirect('index.php?option=com_virtuemart&view=cart&task=checkout');
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
	 * 
	 * 
	 */
	 
	public function checkout(){
		
		//Tests step for step for the necessary data, redirects to it, when something is lacking
		$cart = cart::getCart(false);

		if($cart){
			$mainframe = JFactory::getApplication();
			
			//When the data is already validated, then the confirmation was done,
			//so set confirmdone true,  this checks again all data for the final contract
			if($cart['dataValidated'] === true){
				$confirmDone=true;
			}else{
				$confirmDone=false;
			}
			
			//But we check the data again to be sure
			if(empty($cart['BT'])){
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
			if(!empty($cart['ST'])){
				$anonym = true;
				$redirectMsg = self::validateUserData($cart,$anonym,'ST');
				if($redirectMsg){
					$mainframe->redirect('index.php?option=com_virtuemart&view=user&task=editaddresscheckout&addrtype=ST',$redirectMsg);		
				}			
			}

			//Test Shipment
			if(empty($cart['shipping_rate_id'])){
				$cart['inCheckOut'] = true;
				$confirmDone=false;
				cart::setCart($cart);
				$this->editshipping();
				return;
			}
			
			//Test Payment and show payment plugin
			if(empty($cart['paym_id'])){
				$cart['inCheckOut'] = true;
				$confirmDone=false;
				cart::setCart($cart);
				$this->editpayment();
				return;
			}
			
			require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'models'.DS.'paymentmethod.php');
			if(VirtueMartModelPaymentmethod::hasCreditCard($cart['paym_id'])){
				if(empty($cart['creditcard_id']) || 
					empty($cart['cc_name']) || 
					empty($cart['cc_number']) || 
					empty($cart['cc_code']) || 
					empty($cart['cc_expire_month']) ||  
					empty($cart['cc_expire_year'])){
						$cart['inCheckOut'] = true;
						$confirmDone=false;
						$this->editpayment();
						return;
				}
				$this->setpayment(false);
			}
		
			//Show cart and checkout data overview
			$cart['inCheckOut'] = false;
			$cart['dataValidated'] = true;
			cart::setCart($cart);
			
			if($confirmDone){
				$this->confirmedOrder();
			} else {
				$this->Cart();
			}
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
	public function confirmedOrder(){
	
		//Here we do the task, like storing order information
		$cart = cart::getCart(false);

		//Just to prevent direct call
		if($cart['dataValidated']){
			//TODO Call payment plugins
		
			//TODO Store the order and do inventory
			
			$this->doEmail($cart);

			//Empty cart, now for developing only dataValidated
			$cart['dataValidated']=false;
			cart::setCart($cart);
			
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
	 */
	function doEmail($cart){

		/* Create the view */
		$view = $this->getView('cart', 'html');
		
		$view->setModel($this->getModel('cart', 'VirtuemartModel'), true);
		$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR .DS.'models' );
		$view->setModel( $this->getModel( 'user', 'VirtuemartModel' ), false );
		$view->setModel( $this->getModel( 'userfields', 'VirtuemartModel' ), true );	
		$view->setModel( $this->getModel( 'orders', 'VirtuemartModel' ), true );  //TODO we need the oder_number in the mail
		$store = $this->getModel( 'store', 'VirtuemartModel' );
		$view->setModel( $store, true );

		$view->setLayout('mailshopper');
		
		/* Render it all */
		ob_start();
		$view->display();
		$bodyShopper = ob_get_contents();
		ob_end_clean();
		$sentmail = $this->sendMail($cart,$bodyShopper,$cart['BT']['email']);
		
		
		$view->setLayout('mailvendor');
		
		/* Render it all */
		ob_start();
		$view->display();
		$bodyVendor = ob_get_contents();
		ob_end_clean();
		
		$store->setId($cart['vendor_id']);
		$vendor=$store->getStore();
		$sentmail = $this->sendMail($cart,$bodyVendor,$vendor->jUser->email); //TODO 
		
		//Just for developing
		echo '<br />$bodyShopper '.$bodyShopper;
		echo '<br />$bodyVendor '.$bodyVendor;
	}

	
	/**
	 * does not work with self::renderView($view)
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
	 * Sends the mail joomla conform
	 * 
	 * @param $cart the cart in the session
	 * @param $body the html body to send, the content of the email
	 * @param $recipient the recipients of the mail, can be array also 
	 */
	function sendMail($cart,$body,$recipient,$subject='TODO set subject'){
		
		$mailer =& JFactory::getMailer();
		
		//This is now just without multivendor
		$config =& JFactory::getConfig();
		$sender = array( 
    		$config->getValue( 'config.mailfrom' ),
    		$config->getValue( 'config.fromname' ) ); 
 
		$mailer->setSender($sender);

		$mailer->addRecipient($recipient);
				
		$mailer->setSubject($subject);  
		
		// Optional file attached  //this information must come from the cart
//		if($downloadable){
//			$mailer->addAttachment();
//		}
		
		$mailer->isHTML(true);
		$mailer->setBody($body);
		
		// Optionally add embedded image  //TODO Test it
		$store = $this->getModel('store','VirtuemartModel');
		if(empty($cart['vendor_id'])) $cart['vendor_id']=1;
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
	
	/**
	 * Test userdata if valid
	 * 
	 * @author Max Milbers
	 * @param String if BT or ST
	 * @return redirectMsg, if there is a redirectMsg, the redirect should be executed after
	 */
	 private function validateUserData($cart,$anonym=false,$type='BT'){
	 	
	 
		require_once(JPATH_COMPONENT.DS.'helpers'.DS.'user_info.php');
		$neededFields = user_info::getTestUserFields($anonym);
		$redirectMsg=0;
		foreach($neededFields as $field){
			
			if(empty($cart[$type][$field->name]) && $field->name!='state_id'){
				$redirectMsg = 'Enter for '.$type.' '.$field->name.' title: '.JText::_($field->title).' and value: '.$cart[$type][$field->name].' but '.$cart['BT']['country_id'];
			} else {
				//This is a special test for the state_idd. There is the speciality that the state_id could be 0 but is valid.
				if($field->name=='state_id'){
					require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'models'.DS.'state.php');
					if(!$msg=VirtueMartModelState::testStateCountry($cart[$type]['country_id'],$cart[$type]['state_id'])){
						$redirectMsg = $msg;
					}
				}
				
				//We may add here further Tests. Like if the email has the form a@b.xxx and so on
			}
		}
	 	return $redirectMsg;
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
