<?php
/**
 *
 * List/add/edit/remove Users
 *
 * @package	VirtueMart
 * @subpackage User
 * @author Oscar van Eijk
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
 */
class VirtuemartViewUser extends JView {

	
	private $_model;
	private $_uid = 0;
	private $_currentUser=0;
	private $_userDetails = 0;
	private $_userFieldsModel = 0;
	private $_userInfoID = 0;
	
	private $_list=0;
	
	private $_orderList=0;
	private $_openTab=0;
	
	/*
	 * Okey I try now a completly new idea.
	 * 
	 * We make a function for every tab and the display is getting the right tabs by an own function
	 * putting that in an array and after that we call the preparedataforlayoutBlub
	 */
	 
	function display($tpl = null) {

		$layoutName = JRequest::getVar('layout', $this->getLayout());

		$this->_model = $this->getModel('user', 'VirtuemartModel');
		$editor = JFactory::getEditor();
		$this->_currentUser =& JFactory::getUser();
		$this->_uid = $this->_lists['current_id'] = $this->_currentUser->get('id');
		
		$this->loadHelper('permissions');
		$this->loadHelper('shoppergroup');
		$this->loadHelper('shopfunctions');
		$this->loadHelper('vendorhelper');
		$this->loadHelper('currencydisplay');
		$this->loadHelper('image');

		$this->_userFieldsModel = $this->getModel('userfields', 'VirtuemartModel');

		if(!empty($this->_model)){
			$this->_model->setId($this->_uid);
		}else{
			echo 'Model user is empty in user/view.html.php';
		}
		$this->_userDetails = $this->_model->getUser();
		$this->assignRef('userDetails', $this->_userDetails);
		
		//Prepare the data for the user
		if($layoutName=='edit'){
			$this->lUser();
		}
		$userFields = $this->setUserFieldsForView();

		$this->getUserInfoId();
		
		if($layoutName=='edit'){
			$this->shopper($userFields);
		}
		$this->generateStAddressList();
		
		$this->lshipto();
		
		$this->payment();
		
		if($layoutName=='edit'){
			$this->lOrderlist();
			$this->lVendor();	
		}

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

		// Make sure this address is written to the cart as selected.
		// TODO Should this code be moved to the cart helper?

		$_cart = cart::getCart();
		if ($_cart) {
			if (($_shipto = JRequest::getVar('shipto', '')) != '') {
				$_cart['address_shipto_id'] = $_shipto;
			} else {
				$_cart['address_shipto_id'] = $this->_userInfoID;
			}
			$_cart['address_billto_id'] = $this->_userInfoID;
			cart::setCart($_cart);
		}


		$this->assignRef('lists', $this->_lists);
		
		//TODO we have shipToID and shipto?	
		$this->assignRef('shipto', $_shipto);
			
		$this->assignRef('editor', $editor);
		$this->assignRef('pane', $pane);
		parent::display($tpl);
	}

	/**
	 * This sets the userfields we wanna have for the view
	 * We may move that later to an helper and use a switch to fine grain it
	 * 
	 * @author Oscar van Eijk, Max Milbers
	 */
	function setUserFieldsForView($switch=0){
		$type = JRequest::getVar('addrtype', 'BT');
		$this->assignRef('address_type', $type);

		if ($type == 'BT') {
			$_userFields = $this->_userFieldsModel->getUserFields(
					 'account'
					, array() // Default toggles
					, array('delimiter_userinfo', 'delimiter_billto', 'username', 'password', 'password2'
						, 'agreed', 'address_type', 'bank') // Skips
			);
		} else {
			$_userFields = $this->_userFieldsModel->getUserFields(
				 'shipping'
				, array() // Default toggles
				, array('delimiter_userinfo', 'username', 'email', 'password', 'password2'
					, 'agreed', 'address_type', 'bank') // Skips
			);
		}
		require_once(JPATH_COMPONENT.DS.'helpers'.DS.'user_info.php');
		
		$userFields = user_info::getAddress(
			 $this->_userFieldsModel
			,$_userFields
			,$type
		);
		
		$this->assignRef('userFields', $userFields);
		return $userFields;
	}
	
	/** Gets the userInfoId and the userDetailsList
	 * TODO there is a problem with the userDetailsList, it is used in different places, sorry do not see through it
	 */
	function getUserInfoId(){
		
		if (($_addressCount = count($this->_userDetails->userInfo)) == 0) {
			$_userDetailsList = null;
			$userInfoID = null;
		} else {
			$userDetailsList = current($this->_userDetails->userInfo);
			for ($_i = 0; $_i < $_addressCount; $_i++) {
				if ($userDetailsList->address_type == 'BT') {
					$userInfoID = $userDetailsList->user_info_id;
					reset($this->_userDetails->userInfo);
					break;
				}
				$userDetailsList = next($this->_userDetails->userInfo);
			}
		}
		$this->_userInfoID = $userInfoID;
		$this->assignRef('userInfoID', $userInfoID);
		
		$this->_userDetailsList = $userDetailsList ;
		$this->assignRef('userDetailsList', $userDetailsList);
		
	}
	
	function lOrderlist(){
		// Check for existing orders for this user
		$orders = $this->getModel('orders');

		if ($this->_uid == 0) {
			// getOrdersList() returns all orders when no userID is set (admin function),
			// so explicetly define an empty array when not logged in.
			$this->_orderList = array();
		} else {
			$this->_orderList = $orders->getOrdersList($this->_uid, true);
		}
		$this->assignRef('orderlist', $this->_orderList);
	}

	function payment(){
		
	}
	
	/**
	 * This generates the list when the user have different ST addresses saved
	 * 
	 */
	function generateStAddressList (){
		
		// Shipping address(es)
		$_addressList = $this->_model->getUserAddressList($this->_uid , 'ST');
		if (($_c = count($_addressList)) == 0) {
			$this->_lists['shipTo'] = JText::_('VM_USER_NOSHIPPINGADDR');
		} else {
			$_shipTo = array();
			for ($_i = 0; $_i < $_c; $_i++) {
				$_shipTo[] = '<li>'.'<a href="index.php'
				.'?option=com_virtuemart'
				.'&view=user'
				.'&layout=edit'
				.'&cid[]='.$_addressList[$_i]->user_id
				.'&shipto='.$_addressList[$_i]->user_info_id
				. '">'.$_addressList[$_i]->address_type_name.'</a>'.'</li>';
					
			}
			$this->_lists['shipTo'] = '<ul>' . join('', $_shipTo) . '</ul>';
		}
	}
	
	function lshipto(){
		
		// The ShipTo address if selected
		$_shipto_id = JRequest::getVar('shipto', -1);
		if ($_shipto_id == -1) {
			$_shipto = 0;
			$this->_openTab = JRequest::getVar('tab', -1);
		} else {
			// Contains 0 for new, otherwise a user_info_id
			$_shipto = $this->_model->getUserAddress($this->_uid, $_shipto_id, 'ST');
			$this->_openTab = 3;
			$_shiptoFields = $this->_userFieldsModel->getUserFields(
				 'shipping'
				,array() // Default toggles
			);
			if ($_shipto_id === 0) {
				$_userDetailsList = null;
			} else {
				// Find the correct record
				$_userDetailsList = current($this->_userDetails->userInfo);
				for ($_i = 0; $_i < count($this->_userDetails->userInfo); $_i++) {
					if ($_userDetailsList->user_info_id == $_shipto_id) {
						reset($this->_userDetails->userInfo);
						break;
					}
					$_userDetailsList = next($this->_userDetails->userInfo);
				}
			}
			$shipToFields = $this->_userFieldsModel->getUserFieldsByUser(
				 $_shiptoFields
				,$_userDetailsList
				,'shipto_'
			);
			$this->assignRef('shipToFields', $shipToFields);
			$this->assignRef('shipToID', $_shipto_id);
		}
	}
	
	function shopper($userFields){
		
		// Shopper info
		$_shoppergroup = ShopperGroup::getShoppergroupById ($this->_uid);
		if(Permissions::getInstance()->check("admin,storeadmin")){
			$this->_lists['shoppergroups'] = ShopFunctions::renderShopperGroupList($_shoppergroup['shopper_group_id']);
			$this->_lists['vendors'] = ShopFunctions::renderVendorList($this->_userDetails->vendor_id->vendor_id); //TODO Max This is strange, this should be reworked in the model
		} else {
			$this->_lists['shoppergroups'] = $_shoppergroup['shopper_group_name'];
			if(empty($this->_lists['shoppergroups'])){
				$this->_lists['shoppergroups']='unregistered';
			}
			$this->_lists['shoppergroups'] .= '<input type="hidden" name="shopper_group_id" value = "' . $_shoppergroup['shopper_group_id'] . '" />';
			//			echo 'Test <pre>'.print_r($this->_userDetails->vendor_id).'</pre>';
				
			if(!empty($this->_userDetails->vendor_id)){
				$this->_lists['vendors'] = $this->_userDetails->vendor_id->vendor_id;
			}
				
			if(empty($this->_lists['vendors'])){
				$this->_lists['vendors'] = JText::_('VM_USER_NOT_A_VENDOR');
			}
		}
		
		if(Permissions::getInstance()->check("admin,storeadmin")){
			$this->_lists['perms'] = JHTML::_('select.genericlist', Permissions::getUserGroups(), 'perms', '', 'group_name', 'group_name', $_userDetailsList->perms);
		} else {
			if(!empty($_userDetailsList->perms)){
				$this->_lists['perms'] = $_userDetailsList->perms;
			}
			if(empty($this->_lists['perms'])){
				$this->_lists['perms'] = 'shopper'; // TODO Make this default configurable
			}
			$_hiddenInfo = '<input type="hidden" name="perms" value = "' . $this->_lists['perms'] . '" />';
			$this->_lists['perms'] .= $_hiddenInfo;
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
		&& ($this->_uid != $this->_uid)); // Can't block myself TODO I broke that sorry
		$this->_lists['canSetMailopt'] = $this->_currentUser->authorize('workflow', 'email_events');
		$this->_lists['block']     = JHTML::_('select.booleanlist', 'block',     0, $this->_userDetails->JUser->get('block'),     'VM_ADMIN_CFG_YES', 'VM_ADMIN_CFG_NO');
		$this->_lists['sendEmail'] = JHTML::_('select.booleanlist', 'sendEmail', 0, $this->_userDetails->JUser->get('sendEmail'), 'VM_ADMIN_CFG_YES', 'VM_ADMIN_CFG_NO');

		$this->_lists['params'] = $this->_userDetails->JUser->getParameters(true);

		$this->_lists['custnumber'] = $this->_model->getCustomerNumberById($this->_uid);
		
		if ($this->_uid < 1) {
			$this->_lists['register_new'] = 1;
		} else {
			$this->_lists['register_new'] = 0;
		}

	}
	
	function lVendor(){
		
		$vendor = new Vendor;
		if(!$this->_orderList){
			$this->lOrderlist();
		}
		// If the current user is a vendor, load the store data
		if ($vendor->isVendor($this->_uid)) {
			$_vendorData = Vendor::getVendorFields($this->_userDetails->vendor_id->vendor_id, array('vendor_currency_display_style'));
			if (count($this->_orderList) > 0) {
				if (!empty($_vendorData)) {
					$_currencyDisplayStyle = Vendor::get_currency_display_style(
						 $this->_userDetails->vendor_id->vendor_id
						,$_vendorData->vendor_currency_display_style
					);
					$currency = new CurrencyDisplay(
						 $_currencyDisplayStyle['id']
						,$_currencyDisplayStyle['symbol']
						,$_currencyDisplayStyle['nbdecimal']
						,$_currencyDisplayStyle['sdecimal']
						,$_currencyDisplayStyle['thousands']
						,$_currencyDisplayStyle['positive']
						,$_currencyDisplayStyle['negative']
					);
				} else {
					//					$currency = new CurrencyDisplay();
				}
				$this->assignRef('currency', $currency);
			}

			$storeModel = $this->getModel('store');
			$storeModel->setId($vendor->getVendorIdByUserId($this->_uid));
			$_store = $storeModel->getStore();
			$this->assignRef('store', $_store);
			$currencyModel = $this->getModel('currency');
			$_currencies = $currencyModel->getCurrencies();
			$this->assignRef('currencies', $_currencies);
			$_vendorCats = JHTML::_('select.genericlist', $vendor->getVendorCategories(), 'vendor_category_id', '', 'vendor_category_id', 'vendor_category_name', $this->store->vendor_category_id);
			$this->assignRef('vendorCategories', $_vendorCats);
			$_currencyDisplayStyle = Vendor::get_currency_display_style(
				 $vendor->getVendorIdByUserId($this->_uid)
				,$_vendorData->vendor_currency_display_style
			);
			$_vendorCurrency = new CurrencyDisplay(
				 $_currencyDisplayStyle['id']
				,$_currencyDisplayStyle['symbol']
				,$_currencyDisplayStyle['nbdecimal']
				,$_currencyDisplayStyle['sdecimal']
				,$_currencyDisplayStyle['thousands']
				,$_currencyDisplayStyle['positive']
				,$_currencyDisplayStyle['negative']
			);
			$this->assignRef('vendorCurrency', $_vendorCurrency);
		}

		if(empty($currency)){
			$currency = new CurrencyDisplay();
			$this->assignRef('currency', $currency);
		}
		$this->assignRef('vendor', $vendor);
	}
	
	/**
	 * Additional grid function for custom toggles
	 *
	 * @return string HTML code to write the toggle button
	 */
	function toggle( $field, $i, $toggle, $imgY = 'tick.png', $imgX = 'publish_x.png', $prefix='' )
	{

		$img 	= $field ? $imgY : $imgX;
		if ($toggle == 'published') { // Stay compatible with grid.published
			$task 	= $field ? 'unpublish' : 'publish';
			$alt 	= $field ? JText::_( 'Published' ) : JText::_( 'Unpublished' );
			$action = $field ? JText::_( 'Unpublish Item' ) : JText::_( 'Publish item' );
		} else {
			$task 	= $field ? 'disable_'.$toggle : 'enable_'.$toggle;
			$alt 	= $field ? JText::_( 'Enabled' ) : JText::_( 'Disabled' );
			$action = $field ? JText::_( 'Disable Item' ) : JText::_( 'Enable item' );
		}

		return ('<a href="javascript:void(0);" onclick="return listItemTask(\'cb'. $i .'\',\''. $task .'\')" title="'. $action .'">'
		.'<img src="images/'. $img .'" border="0" alt="'. $alt .'" /></a>');
	}


}

//No Closing Tag
