<?php
/**
 *
 * List/add/edit/remove Users
 *
 * @package	VirtueMart
 * @subpackage User
 * @author Oscar van Eijk
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

// Load the view framework
jimport( 'joomla.application.component.view');

// Set to '0' to use tabs i.s.o. sliders
// Might be a config option later on, now just here for testing.
define ('__VM_USER_USE_SLIDERS', 0);

/**
 * HTML View class for maintaining the list of users
 *
 * @package	VirtueMart
 * @subpackage User
 * @author Oscar van Eijk
 * @author Max Milbers
 */
class VirtuemartViewUser extends JView {


	private $_model;
	private $_currentUser=0;
	private $_cuid = 0;
	private $_userDetails = 0;
	private $_userFieldsModel = 0;
	private $_userInfoID = 0;

	private $_list=0;

	private $_orderList=0;
	private $_openTab=0;

	/**
	 * Displays the view, collects needed data for the different layouts
	 *
	 * Okey I try now a completly new idea.
	 * We make a function for every tab and the display is getting the right tabs by an own function
	 * putting that in an array and after that we call the preparedataforlayoutBlub
	 *
	 * @author Oscar van Eijk
	 * @author Max Milbers
	 */
	function display($tpl = null) {

		$useSSL = VmConfig::get('useSSL',0);
		$useXHTML = true;
		$this->assignRef('useSSL', $useSSL);
		$this->assignRef('useXHTML', $useXHTML);

		$layoutName = $this->getLayout();
		if(empty($layoutName)){
			$layoutName = JRequest::getWord('layout','edit');
		}

		if(!class_exists('ShopFunctions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'shopfunctions.php');

		if(!class_exists('VirtuemartModelUser')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'user.php');
		$this->_model = new VirtuemartModelUser();


		//$this->_model = $this->getModel('user', 'VirtuemartModel');
		//		$this->_model->setCurrent(); //without this, the administrator can edit users in the FE, permission is handled in the usermodel, but maybe unsecure?
		$editor = JFactory::getEditor();

		//the cuid is the id of the current user
		$this->_currentUser = JFactory::getUser();
		$this->_cuid = $this->_lists['current_id'] = $this->_currentUser->get('id');

		$this->_userDetails = $this->_model->getUser();
		$this->assignRef('userDetails', $this->_userDetails);

		$type = JRequest::getWord('addrtype', 'BT');
		$this->assignRef('address_type', $type);

		$new = false;
		$new = JRequest::getInt('new', '0','','int');
		if(!empty($new)){
			$new = true;
		}

		$virtuemart_userinfo_id = JRequest::getString('virtuemart_userinfo_id', '0','');
		$userFields = null;
		if((strpos($this->fTask,'cart') || strpos($this->fTask,'checkout')) && empty($virtuemart_userinfo_id) ){
// 			vmdebug('I came from the cart and new address',$this->fTask,$type,$new);
			//New Address is filled here with the data of the cart (we are in the cart)
			if(!class_exists('VirtueMartCart')) require(JPATH_VM_SITE.DS.'helpers'.DS.'cart.php');
			$cart = VirtueMartCart::getCart();

			$fieldtype = $type.'address';
			$cart->prepareAddressDataInCart($type,$new);

			$userFields = $cart->$fieldtype;

			$task = JRequest::getWord('task','');

		} else {
			$userFields  = $this->_model->getUserDataInFields($layoutName,$type,$this->userDetails->JUser->id);
			if(!empty($virtuemart_userinfo_id)){
				$userFields = $userFields[$virtuemart_userinfo_id];
			}
			$task = 'editAddressSt';
// 			vmdebug('I came from the user view',$this->fTask);
		}

		$this->assignRef('userFields', $userFields);

		if($layoutName=='edit'){


			if($this->_model->getId()==0 && $this->_cuid==0){
				$button_lbl = JText::_('COM_VIRTUEMART_REGISTER');
			} else {
				$button_lbl = JText::_('COM_VIRTUEMART_SAVE');
			}

			$this->assignRef('button_lbl', $button_lbl);
			$this->lUser();
			$this->shopper($userFields);

			$userFieldsST  = $this->_model->getUserDataInFields($layoutName,'ST',$this->userDetails->JUser->id);
			$this->lshipto($userFieldsST);

			$this->payment();
			$this->lOrderlist();
			$this->lVendor();

		}

		if($new || empty($this->_cuid)){
			$virtuemart_userinfo_id = 0;
		} else{
			$virtuemart_userinfo_id = current($this->userDetails->userInfo)->virtuemart_userinfo_id;
		}
		$this->assignRef('virtuemart_userinfo_id', $virtuemart_userinfo_id);


		$this->_lists['shipTo'] = ShopFunctions::generateStAddressList($this->_model,$task);


		if ($this->_openTab < 0) {
			$_paneOffset = array();
		} else {
			if (__VM_USER_USE_SLIDERS) {
				$_paneOffset = array('startOffset' => $this->_openTab, 'startTransition' => 1, 'allowAllClose' => true);
			} else {
				$_paneOffset = array('startOffset' => $this->_openTab);
			}
		}

		// Implement the Joomla panels. If we need a ShipTo tab, make it the active one.
		// In tmpl/edit.php, this is the 4th tab (0-based, so set to 3 above)
		jimport('joomla.html.pane');
		$pane = JPane::getInstance((__VM_USER_USE_SLIDERS?'Sliders':'Tabs'), $_paneOffset);

		$this->assignRef('lists', $this->_lists);

		$this->assignRef('editor', $editor);
		$this->assignRef('pane', $pane);

		if($layoutName=='mailregisteruser'){
			$vendorModel = $this->getModel('vendor');
			//			$vendorModel->setId($this->_userDetails->virtuemart_vendor_id);
			$vendor = $vendorModel->getVendor();
			$this->assignRef('vendor', $vendor);
		}

		shopFunctionsF::setVmTemplate($this,0,0,$layoutName);

		parent::display($tpl);
	}


	/**
	 * For the edit_shipto layout
	 *
	 */
	function lshipto($staddress){

		// The ShipTo address if selected
		$virtuemart_userinfo_id = JRequest::getInt('virtuemart_userinfo_id', 0);

		$this->assignRef('virtuemart_userinfo_id', $virtuemart_userinfo_id);
// 		vmdebug('lshipto $virtuemart_userinfo_id',$virtuemart_userinfo_id);

		$new = false;
		$new = JRequest::getVar('new', '0','','int');
		if(!empty($new)){
			$new = true;
		}


		if(!class_exists('VirtuemartModelUserfields')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'userfields.php');
		$this->_userFieldsModel = new VirtuemartModelUserfields();

		$_shiptoFields = $this->_userFieldsModel->getUserFields(
			 'shipping'
		,array() // Default toggles
		);


		$_userDetailsList = null;

		if(!empty($virtuemart_userinfo_id)){
			// Contains 0 for new, otherwise a virtuemart_userinfo_id
			$_shipto = $this->_model->getUserAddressList($this->_model->getId(), 'ST', $_shipto_id);
			$this->_openTab = 3;

			// Find the correct record
			$_userDetailsList = current($this->_userDetails->userInfo);
			for ($_i = 0; $_i < count($this->_userDetails->userInfo); $_i++) {
				if ($_userDetailsList->virtuemart_userinfo_id == $virtuemart_userinfo_id) {
					reset($this->_userDetails->userInfo);
					break;
				}
				$_userDetailsList = next($this->_userDetails->userInfo);

			}
			//			}
		} else {
				if(!empty($staddress)){
					$_userDetailsList = (object)$staddress;
					$this->_openTab = 3;
				}
		}

		$shipToFields = $this->_userFieldsModel->getUserFieldsByUser(
		$_shiptoFields
		,$_userDetailsList
		,'shipto_'
		);

		$this->assignRef('shipToFields', $shipToFields);

	}


	function payment(){

	}

	function lOrderlist(){
		// Check for existing orders for this user
		$orders = $this->getModel('orders');

		if ($this->_model->getId() == 0) {
			// getOrdersList() returns all orders when no userID is set (admin function),
			// so explicetly define an empty array when not logged in.
			$this->_orderList = array();
		} else {
			$this->_orderList = $orders->getOrdersList($this->_model->getId(), true);

			if(empty($this->currency)){
				if (!class_exists('CurrencyDisplay')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'currencydisplay.php');

				$currency = CurrencyDisplay::getInstance();
				$this->assignRef('currency', $currency);
			}
		}
		$this->assignRef('orderlist', $this->_orderList);
	}

	function shopper($userFields){

		$this->loadHelper('permissions');
		$this->loadHelper('shoppergroup');

		// Shopper info
		if (!class_exists('VirtueMartModelShopperGroup')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'shoppergroup.php');

		$_shoppergroup = VirtueMartModelShopperGroup::getShoppergroupById ($this->_model->getId());

		if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');

		if(Permissions::getInstance()->check('admin,storeadmin')){
			$this->_lists['shoppergroups'] = ShopFunctions::renderShopperGroupList($_shoppergroup['virtuemart_shoppergroup_id']);
			$this->_lists['vendors'] = ShopFunctions::renderVendorList($this->_userDetails->virtuemart_vendor_id);

		} else {
			$this->_lists['shoppergroups'] = $_shoppergroup['shopper_group_name'];

			//I dont think that makes sense anymore
			// 			if(empty($this->_lists['shoppergroups'])){
			// 				$this->_lists['shoppergroups']='unregistered';
			// 			} else {
				$this->_lists['shoppergroups'] .= '<input type="hidden" name="virtuemart_shoppergroup_id" value = "' . $_shoppergroup['virtuemart_shoppergroup_id'] . '" />';
				// 			}

				if(!empty($this->_userDetails->virtuemart_vendor_id)){
					$this->_lists['vendors'] = $this->_userDetails->virtuemart_vendor_id;
				}

				if(empty($this->_lists['vendors'])){
					$this->_lists['vendors'] = JText::_('COM_VIRTUEMART_USER_NOT_A_VENDOR');// . $_setVendor;
				}
			}

			//todo here is something broken we use $_userDetailsList->perms and $this->_userDetailsList->perms and perms seems not longer to exist
			if(Permissions::getInstance()->check("admin,storeadmin")){
				$this->_lists['perms'] = JHTML::_('select.genericlist', Permissions::getUserGroups(), 'perms', '', 'group_name', 'group_name', $this->_userDetails->perms);
			} else {
				if(!empty($this->_userDetails->perms)){
					$this->_lists['perms'] = $this->_userDetails->perms;

					/* This now done in the model, so it is unnecessary here, notice by Max Milbers
					 if(empty($this->_lists['perms'])){
					$this->_lists['perms'] = 'shopper'; // TODO Make this default configurable
					}
					*/
					$_hiddenInfo = '<input type="hidden" name="perms" value = "' . $this->_lists['perms'] . '" />';
					$this->_lists['perms'] .= $_hiddenInfo;
				}
			}

			// Load the required scripts
			if (count($userFields['scripts']) > 0) {
				foreach ($userFields['scripts'] as $_script => $_path) {
					JHTML::script($_script, $_path);
				}
			}
			// Load the required styresheets
			if (count($userFields['links']) > 0) {
				foreach ($userFields['links'] as $_link => $_path) {
					JHTML::stylesheet($_link, $_path);
				}
			}
		}

		function lUser(){

			$_groupList = $this->_model->getGroupList();

			if (!is_array($_groupList)) {
				$this->_lists['gid'] = '<input type="hidden" name="gid" value="'. $this->_userDetails->JUser->get('gid') .'" /><strong>'. JText::_($_groupList) .'</strong>';
			} else {
				$this->_lists['gid'] 	= JHTML::_('select.genericlist', $_groupList, 'gid', 'size="10"', 'value', 'text', $this->_userDetails->JUser->get('gid'));
			}

			$this->_lists['canBlock']      = ($this->_currentUser->authorize('com_users', 'block user')
			&& ($this->_model->getId() != $this->_cuid)); // Can't block myself TODO I broke that, please retest if it is working again
			$this->_lists['canSetMailopt'] = $this->_currentUser->authorize('workflow', 'email_events');
			$this->_lists['block']     = JHTML::_('select.booleanlist', 'block',    'class="inputbox"', $this->_userDetails->JUser->get('block'),     'COM_VIRTUEMART_YES', 'COM_VIRTUEMART_NO');
			$this->_lists['sendEmail'] = JHTML::_('select.booleanlist', 'sendEmail','class="inputbox"', $this->_userDetails->JUser->get('sendEmail'), 'COM_VIRTUEMART_YES', 'COM_VIRTUEMART_NO');

			$this->_lists['params'] = $this->_userDetails->JUser->getParameters(true);

			$this->_lists['custnumber'] = $this->_model->getCustomerNumberById($this->_model->getId());

			//TODO I do not understand for what we have that by Max.
			if ($this->_model->getId() < 1) {
				$this->_lists['register_new'] = 1;
			} else {
				$this->_lists['register_new'] = 0;
			}

		}

		function lVendor(){

			// If the current user is a vendor, load the store data
			if ($this->_userDetails->user_is_vendor) {

				$currencymodel = $this->getModel('currency', 'VirtuemartModel');
				$currencies = $currencymodel->getCurrencies();
				$this->assignRef('currencies', $currencies);

				if(!$this->_orderList){
					$this->lOrderlist();
				}

				$vendorModel = $this->getModel('vendor');

				if(Vmconfig::get('multix','none')==='none'){
					$vendorModel->setId(1);
				} else {
					$vendorModel->setId($this->_userDetails->virtuemart_vendor_id);
				}
				$vendor = $vendorModel->getVendor();
				$vendorModel->addImages($vendor);
				$this->assignRef('vendor', $vendor);

			}

		}


	}

	//No Closing Tag
