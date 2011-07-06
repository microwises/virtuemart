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

	private $_cart;
	private $_user;
	private $_userDetails;
	public $lists;

	public function display($tpl = null) {
		$mainframe = JFactory::getApplication();
		$pathway = $mainframe->getPathway();
		$document = JFactory::getDocument();

		$layoutName = $this->getLayout();
		if(!$layoutName) $layoutName = JRequest::getWord('layout', 'default');
		$this->assignRef('layoutName', $layoutName);
		$format = JRequest::getWord('format');

		if(!class_exists('VirtueMartCart')) require(JPATH_VM_SITE.DS.'helpers'.DS.'cart.php');
		$this->_cart = VirtueMartCart::getCart(false);
		$this->assignRef('cart', $this->_cart);


		if ($format == 'raw') {
			$this->prepareCartData();
			JRequest::setVar( 'layout', 'mini_cart'  );
			$this->setLayout('mini_cart');
			$this->prepareContinueLink();
		}

		if($layoutName=='edit_coupon'){

			$this->prepareCartData();
			$this->lSelectCoupon();

			$pathway->addItem(JText::_('COM_VIRTUEMART_CART_SELECTCOUPON'));
			$document->setTitle(JText::_('COM_VIRTUEMART_CART_SELECTCOUPON'));

		} else if($layoutName=='select_shipper'){
			if(!class_exists('vmShipperPlugin')) require(JPATH_VM_SITE.DS.'helpers'.DS.'vmshipperplugin.php');
			JPluginHelper::importPlugin('vmshipper');
			$this->lSelectShipper();

			$pathway->addItem(JText::_('COM_VIRTUEMART_CART_SELECTSHIPPER'));
			$document->setTitle(JText::_('COM_VIRTUEMART_CART_SELECTSHIPPER'));

		} else if($layoutName=='select_payment'){

			/* Load the cart helper */
//			$cartModel = $this->getModel('cart');
			if(!class_exists('vmPaymentPlugin')) require(JPATH_VM_SITE.DS.'helpers'.DS.'vmpaymentplugin.php');
			JPluginHelper::importPlugin('vmpayment');

			$this->lSelectPayment();

			$pathway->addItem(JText::_('COM_VIRTUEMART_CART_SELECTPAYMENT'));
			$document->setTitle(JText::_('COM_VIRTUEMART_CART_SELECTPAYMENT'));

		} else if($layoutName=='order_done'){

			$this->lOrderDone();

			$pathway->addItem(JText::_('COM_VIRTUEMART_CART_THANKYOU'));
			$document->setTitle(JText::_('COM_VIRTUEMART_CART_THANKYOU'));

		} else if($layoutName=='default' ){

			$this->prepareCartData();

			$this->prepareUserData();

			$this->prepareAddressRadioSelection();

			$this->prepareAddressDataInCart();

			$this->prepareVendor();

			$pathway->addItem(JText::_('COM_VIRTUEMART_CART_OVERVIEW'));
			$document->setTitle(JText::_('COM_VIRTUEMART_CART_OVERVIEW'));

			$this->prepareContinueLink();


			if($this->_cart->getDataValidated()){
				$text = JText::_('COM_VIRTUEMART_ORDER_CONFIRM_MNU');
				$checkout_task = 'confirm';
			} else {
				$text = JText::_('COM_VIRTUEMART_CHECKOUT_TITLE');
				$checkout_task = 'checkout';
			}
			$this->assignRef('checkout_task', $checkout_task);

			if(!VmConfig::get('use_as_catalog')){
				$checkout_link_html = '<a class="checkout_link" href="javascript:document.checkoutForm.submit();" /><span>'.$text.'</span></a>';
			} else {
				$checkout_link_html = '';
			}
			$this->assignRef('checkout_link_html', $checkout_link_html);
		}

		dump($this->_cart,'my cart in view');

		//This should be solved later within the cart, but for now quickndirty
//		$this->_cart->setCartIntoSession();

		$this->assignRef('lists', $this->lists);
                // @max: quicknirty
$this->_cart->setCartIntoSession();
		shopFunctionsF::setVmTemplate($this,0,0,$layoutName);

		parent::display($tpl);
	}

	public function renderMail ($doVendor=false) {
		$tpl = ($doVendor) ? 'mail_html_vendor' : 'mail_html_shopper';
		if(!class_exists('VirtueMartCart')) require(JPATH_VM_SITE.DS.'helpers'.DS.'cart.php');

		$this->_cart = VirtueMartCart::getCart(false);
		$this->assignRef('cart', $this->_cart);
		$this->assignRef('lists', $this->lists);
		$this->prepareCartData();
		$this->prepareUserData();
		$this->prepareAddressDataInCart();
		$this->prepareMailData();

		$this->subject = ($doVendor) ? JText::sprintf('COM_VIRTUEMART_NEW_ORDER_CONFIRMED',	$this->shopperName, $this->order['details']['BT']->order_total, $this->order['details']['BT']->order_number) : JText::sprintf('COM_VIRTUEMART_NEW_ORDER_CONFIRMED', $this->vendor->vendor_store_name, $this->order['details']['BT']->order_total, $this->order['details']['BT']->order_number, $this->order['details']['BT']->order_pass);

		$this->doVendor = true;
		$vendorModel = $this->getModel('vendor');
		$this->vendorEmail = $vendorModel->getVendorEmail($this->vendor->virtuemart_vendor_id);
		$this->layoutName = $tpl;
		$this->setLayout($tpl);
		parent::display();
	}

	private function prepareContinueLink(){
		// Get a continue link */
		$virtuemart_category_id = shopFunctionsF::getLastVisitedCategoryId();
		$categoryLink='';
		if($virtuemart_category_id){
			$categoryLink='&virtuemart_category_id='.$virtuemart_category_id;
		}
		$continue_link = JRoute::_('index.php?option=com_virtuemart&view=category'.$categoryLink);

		$continue_link_html = '<a class="continue_link" href="'.$continue_link.'" />'.JText::_('COM_VIRTUEMART_CONTINUE_SHOPPING').'</a>';
		$this->assignRef('continue_link_html', $continue_link_html);
		$this->assignRef('continue_link', $continue_link);
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
			$_addressBT[0]->address_type_name = JText::_('COM_VIRTUEMART_ACC_BILL_DEF');

			$_addressST = $this->_user->getUserAddressList($this->_userDetails->JUser->get('id') , 'ST');

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

		if($this->_user){
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

	private function prepareUserData(){

		//For User address
		$_currentUser = JFactory::getUser();
		$this->lists['current_id'] = $_currentUser->get('id');
//		$this->assignRef('virtuemart_user_id', $this->lists['current_id']);
		if($this->lists['current_id']){
			$this->_user = $this->getModel('user');
			$this->_user->setCurrent();
			if(!$this->_user){

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
                $this->_cart->CheckShippingIsValid( );
                $automaticSelectedShipping =$this->_cart->CheckAutomaticSelectedShipping( );
		/* Get the products for the cart */
		$prepareCartData = $this->_cart->prepareCartData();
                $this->assignRef('automaticSelectedShipping', $automaticSelectedShipping);
		$this->assignRef('prices', $prepareCartData->prices);

		$this->assignRef('cartData',$prepareCartData->cartData);
		$this->assignRef('calculator',$prepareCartData->calculator);

	}

	private function prepareVendor(){
             if (!class_exists('VirtueMartModelVendor'))
            require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'vendor.php');
		  $vendorModel = new VirtueMartModelVendor();
                 $vendor = $vendorModel->getVendor();
		$vendorModel->addImages($vendor);
		$this->assignRef('vendor',$vendor);
	}

	private function prepareMailData(){

		if(empty($this->vendor)) $this->prepareVendor();
		//TODO add orders, for the orderId
		//TODO add registering userdata
		// In general we need for every mail the shopperdata (with group), the vendor data, shopperemail, shopperusername, and so on
	}

	private function lSelectCoupon(){
		$_couponCode = (isset($this->cartData['couponCode']) ? $this->cartData['couponCode'] : '');
		$this->assignRef('couponCode',$_couponCode);
	}

	private function lSelectShipper(){
		$_selectedShipper = (empty($this->_cart->virtuemart_shippingcarrier_id) ? 0 : $this->_cart->virtuemart_shippingcarrier_id);
		$this->assignRef('selectedShipper',$_selectedShipper);
	}

	private function lSelectPayment(){

		//For the selection of the payment method we need the total amount to pay.
		$paymentModel = $this->getModel('paymentmethod');

		$selectedPaym = empty($this->_cart->virtuemart_paymentmethod_id) ? 0 : $this->_cart->virtuemart_paymentmethod_id;
		$this->assignRef('selectedPaym',$selectedPaym);

		$payments = $paymentModel->getPayments(false,true);
		if(empty($payments)){

			$text ='';
			if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');
			if (Permissions::getInstance()->check("admin,storeadmin")) {
				$uri = JFactory::getURI();
				$link = $uri->root().'administrator/index.php?option=com_virtuemart&view=paymentmethod';
				$text = JText::sprintf('COM_VIRTUEMART_NO_PAYMENT_METHODS_CONFIGURED_LINK','<a href="'.$link.'">'.$link.'</a>');
			}
			$app = JFactory::getApplication();
			$app -> enqueueMessage(JText::sprintf('COM_VIRTUEMART_NO_PAYMENT_METHODS_CONFIGURED',$text));
		}

		$this->assignRef('paymentModel',$paymentModel);
		$this->assignRef('payments',$payments);

	}

	private function lOrderDone(){

		//Show Thank you page or error due payment plugins like paypal express


	}

	private function prepareAddressDataInCart(){


		$userFieldsModel = $this->getModel('userfields', 'VirtuemartModel');

		$BTaddress['fields']= array();
		if(!empty($this->_cart->BT)){
			$userFieldsBT = $userFieldsModel->getUserFieldsFor('cart','BT');
			$userAddressData = $this->_cart->getCartAdressData('BT');
			$BTaddress = $userFieldsModel->getUserFieldsByUser(
							 $userFieldsBT
							,$userAddressData
							);

		}
		$this->assignRef('BTaddress',$BTaddress['fields']);

		$STaddress['fields']= array();
		if(!empty($this->_cart->ST)){
			$userFieldsST = $userFieldsModel->getUserFieldsFor('cart','ST');
			$userAddressData = $this->_cart->getCartAdressData('ST');
			$STaddress = $userFieldsModel->getUserFieldsByUser(
							 $userFieldsST
							,$userAddressData
							);

		}
		$this->assignRef('STaddress',$STaddress['fields']);

/*		$BTaddress['fields']= array();
		if(!empty($this->_cart->BT)){

			//Here we get the fields
			$userFieldsBT = $userFieldsModel->getUserFields(
				 'account'
				, array() // Default toggles
				,  $skips// Skips
			);
			if(!class_exists('VirtueMartCart')) require(JPATH_VM_SITE.DS.'helpers'.DS.'cart.php');
			$cart = VirtueMartCart::getCart(false);
			$BTaddress = $cart->getAddress(
				 $userFieldsModel
				,$userFieldsBT
				,'BT'
			);
		}

		$this->assignRef('BTaddress',$BTaddress['fields']);

		$STaddress['fields']= array();
		if(!empty($this->_cart->ST)){

			$userFieldsST = $userFieldsModel->getUserFields(
				'shipping'
				, array() // Default toggles
				, $skips
			);
			if(!class_exists('VirtueMartCart')) require(JPATH_VM_SITE.DS.'helpers'.DS.'cart.php');
			$cart = VirtueMartCart::getCart(false);
			$STaddress = $cart->getAddress(
				 $userFieldsModel
				,$userFieldsST
				,'ST'
			);

		}

		$this->assignRef('STaddress',$STaddress['fields']);*/

	}

}

//no closing tag
