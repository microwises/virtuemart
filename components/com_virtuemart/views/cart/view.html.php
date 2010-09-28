<?php
/**
*
* View for the shopping cart
*
* @package	VirtueMart
* @subpackage 
* @author Max Milbers
* @author Oscar van Eijk
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

// Load the view framework
jimport( 'joomla.application.component.view');

/**
* View for the shopping cart
* @package VirtueMart
* @author Max Milbers
* @author Oscar van Eijk
*/
class VirtueMartViewCart extends JView {
	
	private $_user;
	private $_userDetails;
	public $lists;
	
	public function display($tpl = null) {	  	    
		$mainframe = JFactory::getApplication();
		$pathway = $mainframe->getPathway();
		
//		$layoutName = JRequest::getVar('layout', $this->getLayout());
//		$layoutName = JRequest::getVar('layout', 0);
		$layoutName = $this->getLayout();
		if(!$layoutName) $layoutName = JRequest::getVar('layout', 'cart');
		$this->assignRef('layoutName', $layoutName);

		if($layoutName=='editcoupon'){
		
			$this->lSelectCoupon();
			
			$pathway->addItem(JText::_('VM_CART_SELECTCOUPON'));
			$mainframe->setPageTitle(JText::_('VM_CART_SELECTCOUPON'));	
				
		} else if($layoutName=='selectshipper'){
			
			$this->lSelectShipper();
			
			$pathway->addItem(JText::_('VM_CART_SELECTSHIPPER'));
			$mainframe->setPageTitle(JText::_('VM_CART_SELECTSHIPPER'));
			
		} else if($layoutName=='selectpayment'){

			/* Load the cart helper */
//			$cartModel = $this->getModel('cart');
			$cart = VirtueMartCart::getCart(false);
			$this->assignRef('cart', $cart);
			
			JPluginHelper::importPlugin('vmpayment');

			$this->lSelectPayment();
			
			$pathway->addItem(JText::_('VM_CART_SELECTPAYMENT'));
			$mainframe->setPageTitle(JText::_('VM_CART_SELECTPAYMENT'));
		
		} else if($layoutName=='orderdone'){
			
			$this->lOrderDone();
			
			$pathway->addItem(JText::_('VM_CART_THANKYOU'));
			$mainframe->setPageTitle(JText::_('VM_CART_THANKYOU'));
		
		} else if($layoutName=='cart' || $layoutName=='default' ){
			
			$this->prepareCartData();
			
			$this->prepareUserData();
			
			$this->prepareAddressRadioSelection();
			
			$this->prepareAddressDataInCart();
			
			$pathway->addItem(JText::_('VM_CART_TITLE'));
			$mainframe->setPageTitle(JText::_('VM_CART_TITLE'));
			
		} else if($layoutName=='mailshopper' || $layoutName=='mailvendor'){

			$this->prepareCartData();
			
			$this->prepareUserData();
			
			$this->prepareMailData();
			
			//If this is necessary must be tested, I dont know if it would change the look of the email, or has other advantages
//			$pathway->addItem(JText::_('VM_CART_TITLE'));
//			$mainframe->setPageTitle(JText::_('VM_CART_TITLE'));
			
		}		
		
		/* Get a continue link */
		$category_id = JRequest::getInt('category_id');
		$product_id = JRequest::getInt('product_id');
		$manufacturer_id = JRequest::getInt('manufacturer_id');
		
		if (!empty($category_id)) $continue_link = JRoute::_('index.php?option=com_virtuemart&view=category&category_id='.$category_id);
		elseif (empty($category_id) && !empty($product_id)) $continue_link = JRoute::_('index.php?option=com_virtuemart&view=category&category_id='.$this->get('categoryid'));
		elseif (!empty($manufacturer_id)) $continue_link = JRoute::_('index.php?option=com_virtuemart&view=manufacturer&manufacturer_id='.$manufacturer_id);
		else $continue_link = JRoute::_('index.php?option=com_virtuemart');
		
		$this->assignRef('continue_link', $continue_link);
		
		$this->assignRef('lists', $this->lists);
		
		parent::display($tpl);
	}
	
	
	private function prepareAddressRadioSelection(){
		
		//Just in case
		if(!$this->_user){
			$this->prepareUserData();	
		}

		// Shipping address(es)
		if($this->_user){
			$_addressBT = $this->_user->getUserAddressList($this->_userDetails->JUser->get('id') , 'BT');
			
			// Overwrite the address name for display purposes
			$_addressBT[0]->address_type_name = JText::_('VM_ACC_BILL_DEF');
			
			$_addressST = $this->_user->getUserAddressList($this->_userDetails->JUser->get('id') , 'ST');
			
		} else {
			$_addressBT[0]->address_type_name = '<a href="index.php'
					.'?option=com_virtuemart'
					.'&view=user'
					.'&task=editaddresscart'
					.'&addrtype=BT'
				. '">'.JText::_('VM_ACC_BILL_DEF').'</a>'.'<br />';
				$_addressST = array();
		}

		$addressList = array_merge(
			array($_addressBT[0])// More BT addresses can exist for shopowners :-(
			, $_addressST );
		
		if($this->_user){
			for ($_i = 0; $_i < count($addressList); $_i++) {
				$addressList[$_i]->address_type_name = '<a href="index.php'
									.'?option=com_virtuemart'
									.'&view=user'
									.'&task=editaddresscart'
									.'&addrtype='.(($_i == 0) ? 'BT' : 'ST')
									.'&user_info_id='.(empty($addressList[$_i]->user_info_id)? 0 : $addressList[$_i]->user_info_id)
									. '">'.$addressList[$_i]->address_type_name.'</a>'.'<br />';
			}
		
			$_selectedAddress = (
				empty($cart->address_shipto_id)
					? $addressList[0]->user_info_id // Defaults to 1st BillTo
					: $cart->address_shipto_id
				);
				
			$this->lists['shipTo'] = JHTML::_('select.radiolist', $addressList, 'shipto', null, 'user_info_id', 'address_type_name', $_selectedAddress);
		} else {
			$_selectedAddress = 0;
			$this->lists['shipTo'] = '';
		}
//		dump($addressList,'my AddressList');
//		$this->lists['shipTo'] = JHTML::_('select.radiolist', $addressList, 'shipto', null, 'user_info_id', 'address_type_name', $_selectedAddress);
//		$this->lists['shipTo'] = JHTML::_('select.radiolist', $addressList, 'shipto', null, 'user_info_id', 'address_type_name', $_selectedAddress);

//		$this->lists['billTo'] = $addressList[0]->user_info_id;
		$this->lists['billTo'] = empty($addressList[0]->user_info_id)? 0 : $addressList[0]->user_info_id;

	}
	
	private function prepareUserData(){
		
		//For User address
		$_currentUser =& JFactory::getUser();
		$this->lists['current_id'] = $_currentUser->get('id');
//		$this->assignRef('user_id', $this->lists['current_id']);
		if($this->lists['current_id']){
			$this->_user = $this->getModel('user');
			$this->_user->setCurrent();
			if(!$this->_user){
				dump($this,'The user model is not defined');
			}else{
				$this->assignRef('user', $this->_user);
				
				$this->_userDetails = $this->_user->getUser();
				
				//This are other contact details, like used in CB or so. 
	//			$_contactDetails = $this->_user->getContactDetails();
				
				$this->assignRef('userDetails', $this->_userDetails);
			}
		}
	}
		
	private function prepareCartData(){

		/* Get the products for the cart */
		$cart = VirtueMartCart::getCart();
		$this->assignRef('cart', $cart);
		
		dump($cart,'cart');
//		$this->assignRef('products', $products);
				
		$prices = $cart->getCartPrices();
		$this->assignRef('prices', $prices);

		//Add names of country/state
		if(!empty($cart->BT)){
			if($cart->BT['country_id']){
				$countryModel = self::getModel('country');
				$countryModel->setId($cart->BT['country_id']);
				$country = $countryModel->getCountry();
				if($country) $cart->BT['country_name'] = $country->country_name;
			}
			
			if($cart->BT['state_id']){
				$stateModel = self::getModel('state');
				$stateModel->setId($cart->BT['state_id']);
				$state = $stateModel->getState();
				if($state) $cart->BT['state_name'] = $state->state_name;	
			}	
		}
		
		if(!empty($cart->ST)){
			if($cart->ST['country_id']){
				$countryModel = self::getModel('country');
				$countryModel->setId($cart->ST['country_id']);
				$country = $countryModel->getCountry();
				if($country) $cart->ST['country_name'] = $country->country_name;
			}
			
			if($cart->ST['state_id']){
				$stateModel = self::getModel('state');
				$stateModel->setId($cart->ST['state_id']);
				$state = $stateModel->getState();
				if($state) $cart->ST['state_name'] = $state->state_name;	
			}
		}
	}
	
	private function prepareMailData(){
		

		$store = $this->getModel('store','VirtuemartModel');
		if(empty($cart->vendorId)) $cart->vendorId=1;
		$store->setId($cart->vendorId);
		$_store = $store->getStore();
		$this->assignRef('store',$_store);
		
		//TODO add orders, for the orderId
		//TODO add registering userdata
		// In general we need for every mail the shopperdata (with group), the vendor data, shopperemail, shopperusername, and so on	
	}
	
	private function lSelectCoupon(){
		
		//TODO Oscar coupon
	}
	
	private function lSelectShipper(){
		//For the selection of the shipper we need the weight and maybe the dimension.
		//Just for developing
		$cartweight= '2';
		$this->assignRef('cartweight', $cartweight);
		
		$shippingCarrierModel = $this->getModel('shippingcarrier');
		$shippingCarriers = $shippingCarrierModel->getShippingCarrierRates($cartweight);
		
		$this->assignRef('shippingCarriers',$shippingCarriers);
		$this->loadHelper('shopfunctions');

	}
	
	private function lSelectPayment(){
		
//		$cartModel = $this->getModel('cart');
		$cart = VirtueMartCart::getCart(false);
		
		//For the selection of the payment method we need the total amount to pay.
		$paymentModel = $this->getModel('paymentmethod');
		
		$selectedPaym = empty($cart->paym_id) ? 0 : $cart->paym_id;
		$this->assignRef('selectedPaym',$selectedPaym);

//		Done by the plugin, shouldnt be used anylonger
//		$selectedCC = empty($cart->creditcard_id']) ? 0 : $cart->creditcard_id'];
//		$this->assignRef('selectedCC',$selectedCC);

		$payments = $paymentModel->getPayms(false,true);
		$withCC=false;
		foreach($payments as $item){
			if(isset($item->accepted_creditcards)){
				$withCC=true;
			}
		}
		$this->assignRef('withCC',$withCC);

		$this->assignRef('paymentModel',$paymentModel);
		$this->assignRef('payments',$payments);

	}
	
	private function lOrderDone(){
		
		//Show Thank you page or error due payment plugins like paypal express
		

	}
	
	private function prepareAddressDataInCart(){

//		$cartModel = $this->getModel('cart');
		$cart = VirtueMartCart::getCart(false);
		
		$userFieldsModel = $this->getModel('userfields', 'VirtuemartModel');
		
		//Here we define the fields to skip
		$skips = array('delimiter_userinfo', 'delimiter_billto', 'username', 'password', 'password2'
						, 'agreed', 'address_type', 'bank');

		$BTaddress['fields']= array();
		if(!empty($cart->BT)){
			require_once(JPATH_COMPONENT.DS.'helpers'.DS.'user_info.php');
			//Here we get the fields
			$_userFieldsBT = $userFieldsModel->getUserFields(
				 'account'
				, array() // Default toggles
				,  $skips// Skips
			);
					
			$BTaddress = user_info::getAddress(
				 $userFieldsModel
				,$_userFieldsBT
				,'BT'
			);
		}
		dump($BTaddress,'My BT');
		$this->assignRef('BTaddress',$BTaddress['fields']);
		
		$STaddress['fields']= array();
		if(!empty($cart->ST)){
			require_once(JPATH_COMPONENT.DS.'helpers'.DS.'user_info.php');
			$_userFieldsST = $userFieldsModel->getUserFields(
				'shipping'
				, array() // Default toggles
				, $skips
			);
			
			$STaddress = user_info::getAddress(
				 $userFieldsModel
				,$_userFieldsST
				,'ST'
			);
			dump($STaddress,'My ST');
		}
		
		$this->assignRef('STaddress',$STaddress['fields']);
	}
	
}

//no closing tag