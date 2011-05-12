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
* RAW text view for the shopping cart
* @package VirtueMart
* @author Max Milbers
* @author Oscar van Eijk
* @author Christopher Roussel
*/
class VirtueMartViewCart extends JView {

	private $_cart;
	private $_user;
	private $_userDetails;
	public $lists;

	public function renderMail ($doVendor=false) {
		$tpl = ($doVendor) ? 'mail_raw_vendor' : 'mail_raw_shopper';
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
		$this->vendorEmail = $vendorModel->getVendorEmail($this->vendor->vendor_id);
		$this->layoutName = $tpl;
		$this->setLayout($tpl);
		parent::display();
	}

	private function prepareUserData(){

		//For User address
		$_currentUser =& JFactory::getUser();
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

		/* Get the products for the cart */
		$prepareCartData = $this->_cart->prepareCartData();

		$this->assignRef('prices', $prepareCartData->prices);

		$this->assignRef('cartData',$prepareCartData->cartData);
		$this->assignRef('calculator',$prepareCartData->calculator);

	}

	private function prepareAddressDataInCart(){

		$userFieldsModel = $this->getModel('userfields');

		//Here we define the fields to skip
		$skips = array('delimiter_userinfo', 'delimiter_billto', 'username', 'password', 'password2'
						, 'agreed', 'address_type', 'bank');

		$BTaddress['fields']= array();
		if(!empty($this->_cart->BT)){
			if(!class_exists('user_info'))require(JPATH_VM_SITE.DS.'helpers'.DS.'user_info.php');
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

		$this->assignRef('BTaddress',$BTaddress['fields']);

		$STaddress['fields']= array();
		if(!empty($this->_cart->ST)){
			if(!class_exists('user_info'))require(JPATH_VM_SITE.DS.'helpers'.DS.'user_info.php');
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

		}

		$this->assignRef('STaddress',$STaddress['fields']);
	}

	private function prepareVendor(){

		$vendor = $this->getModel('vendor','VirtuemartModel');
		$vendor->setId($this->_cart->vendorId);
		$_vendor = $vendor->getVendor();
		$vendor->addImagesToVendor($_vendor);
		$this->assignRef('vendor',$_vendor);
	}

	private function prepareMailData(){

		if(empty($this->vendor)) $this->prepareVendor();
		//TODO add orders, for the orderId
		//TODO add registering userdata
		// In general we need for every mail the shopperdata (with group), the vendor data, shopperemail, shopperusername, and so on
	}

}

//no closing tag