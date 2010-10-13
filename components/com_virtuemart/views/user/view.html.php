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
	private $_uid = 0;
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

		$layoutName = JRequest::getVar('layout', $this->getLayout());

		$this->_model = $this->getModel('user', 'VirtuemartModel');
		$this->_model->setCurrent();
		$editor = JFactory::getEditor();
		
		//the cuid is the id of the current user
		$this->_currentUser =& JFactory::getUser();
		$this->_cuid = $this->_lists['current_id'] = $this->_currentUser->get('id');
		
		//the uid is the id of the user, we wanna edit.
		$this->_uid = JRequest::getVar('cid', $this->_cuid);

		$this->_userFieldsModel = $this->getModel('userfields', 'VirtuemartModel');

		$this->_userDetails = $this->_model->getUser();
		if (function_exists('dumpTrace')) { // J!Dump is installed
			dump($this->_userDetails,'Display user userDetails');
		}
		$this->assignRef('userDetails', $this->_userDetails);

//		if($layoutName=='edit_address'){
//			$type = JRequest::getVar('addrtype', 'BT');
//			$shipTo = JRequest::getVar('?_id', 0);
//			dump($shipTo,'my shipto in user/view.html.php');
//			if($type!='BT' && !$shipTo){
//				$this->userInfoID = 0;		
//			}
//		}
		
		$userFields = $this->setUserFieldsForView($layoutName);
		
		if($layoutName=='edit'){
			if($this->_uid==0 && $this->_cuid==0){
				$button_lbl = JText::_('Register');
			} else {
				$button_lbl = JText::_('Save');
			}
			$this->assignRef('button_lbl', $button_lbl);
			$this->lUser();
			$this->shopper($userFields);
		}
		$this->generateStAddressList();
		
		$this->lshipto();

		if($layoutName=='edit'){
			$this->payment();
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

		$this->assignRef('lists', $this->_lists);

			
		$this->assignRef('editor', $editor);
		$this->assignRef('pane', $pane);
		parent::display($tpl);
	}

	/**
	 * This sets the userfields we wanna have for the view
	 * We may move that later to an helper and use a switch to fine grain it
	 * 
	 * @author Oscar van Eijk
	 */
	function setUserFieldsForView($layoutName){

		$type = JRequest::getVar('addrtype', 'BT');
		$this->assignRef('address_type', $type);
		
		//Here we define the fields to skip
		if($layoutName=='edit'){
			$skips = array('delimiter_userinfo', 'delimiter_billto', 'username', 'password', 'password2'
						, 'agreed', 'address_type', 'bank', 'email');
		} else {
			$skips = array('delimiter_userinfo', 'delimiter_billto', 'username', 'password', 'password2'
						, 'agreed', 'address_type', 'bank');
		}
		
		//Here we get the fields
		if ($type == 'BT') {
			$_userFields = $this->_userFieldsModel->getUserFields(
					 'account'
					, array() // Default toggles
					,  $skips// Skips
			);
		} else {
			$_userFields = $this->_userFieldsModel->getUserFields(
				 'shipping'
				, array() // Default toggles
				, $skips
			);
		}
		
		 //for register
		if(empty($this->_userDetailsList)){
			$this->_userDetailsList=0;
		}
		
		$preFix='';
		//Here we set the data to fill the fields
		if($type=='BT'){
			self::getUserData($type);
			$user_info_id = JRequest::getVar('user_info_id', 0);
			$userAddressData = $this->_userDetailsList;
		} else {
			$preFix='shipto_';
			$userInfoID = JRequest::getVar('user_info_id', 0);
			if(!empty($userInfoID)) $userAddressData = $this->_userDetails->userInfo[$userInfoID];
			$this->assignRef('userInfoID', $userInfoID);
		}

		if (function_exists('dumpTrace')) { // J!Dump is installed
			dump($this->_userDetailsList,'_userDetailsList');
		}
		//TODO attention, this is the function which actually loads the data into the field.
		// The values are saved in $this->_userDetailsList
		if(!empty($this->_cuid)){
			$userFields = $this->_userFieldsModel->getUserFieldsByUser(
							 $_userFields
							,$userAddressData
//							,$preFix
							);
			if (function_exists('dumpTrace')) { // J!Dump is installed
				dump($userFields,'my Userfields with getUserFieldsByUser for '.$user_info_id);
			}
		} else { //the anonymous case
			
			//We may move this to the helper of course, but for developing I just wanna get it working
			//require_once(JPATH_COMPONENT.DS.'helpers'.DS.'user_info.php');
		
			$userFields = user_info::getAddress(
				 $this->_userFieldsModel
				,$_userFields
				,$type
			);
			if (function_exists('dumpTrace')) { // J!Dump is installed
				dump($userFields,'my Userfields with getAddress');
			}
		}

		
		$this->assignRef('userFields', $userFields);
		return $userFields;
	}
	
	/** Gets the userInfoId and the userDetailsList
	 * TODO there is a problem with the userDetailsList, it is used in different places, sorry do not see through it
	 */
	function getUserData($type='BT'){
		
		$userDetailsList = 0;
		$userInfoID = 0;
		if (($addressCount = count($this->_userDetails->userInfo)) == 0) {
			//TODO I think here is maybe the right position to fill the fields with the cart values, if available

			
		} else {
			$userDetailsList = current($this->_userDetails->userInfo);
			for ($_i = 0; $_i < $addressCount; $_i++) {
				if ($userDetailsList->address_type == $type) {
					$userInfoID = $userDetailsList->user_info_id;
					reset($this->_userDetails->userInfo);
					break;
				}
				
				$userDetailsList = next($this->_userDetails->userInfo);
			}
			$this->_userInfoID = $userInfoID;
			
			$this->_userDetailsList = $userDetailsList ;
			
		}
		
		$this->assignRef('userInfoID', $userInfoID);
		
		if (function_exists('dumpTrace')) { // J!Dump is installed
			dump($userDetailsList,'getUserDataBT the userDetailsList');
		}	
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
	 * @author Oscar van Eijk
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
//				.'&layout=edit'
				.'&task=editAddressSt'
				.'&addrtype=ST'
				.'&cid[]='.$_addressList[$_i]->user_id
				.'&user_info_id='.$_addressList[$_i]->user_info_id
				. '">'.$_addressList[$_i]->address_type_name.'</a>'.'</li>';
					
			}
			$this->_lists['shipTo'] = '<ul>' . join('', $_shipTo) . '</ul>';
		}
	}	
	/**
	 * For the edit_shipto layout
	 * 
	 */
	function lshipto(){
		
		// The ShipTo address if selected
		$_shipto_id = JRequest::getVar('user_info_id', 0);
//		$_shipto_id = JRequest::getVar('shipto', -1);

		$_shiptoFields = $this->_userFieldsModel->getUserFields(
			 'shipping'
			,array() // Default toggles
		);
		
		$_userDetailsList = null;
		
		if(!empty($_shipto_id)){
			// Contains 0 for new, otherwise a user_info_id
			$_shipto = $this->_model->getUserAddress($this->_uid, $_shipto_id, 'ST');
			$this->_openTab = 3;

//			if ($_shipto_id === 0) {
//				$_userDetailsList = null;
//			} else {
				// Find the correct record
				$_userDetailsList = current($this->_userDetails->userInfo);
				for ($_i = 0; $_i < count($this->_userDetails->userInfo); $_i++) {
					if ($_userDetailsList->user_info_id == $_shipto_id) {
						reset($this->_userDetails->userInfo);
						break;
					}
					$_userDetailsList = next($this->_userDetails->userInfo);
				}
//			}
		}
		
		$shipToFields = $this->_userFieldsModel->getUserFieldsByUser(
			 $_shiptoFields
			,$_userDetailsList
//			,'shipto_'
			);
		
		$this->assignRef('shipToFields', $shipToFields);
		$this->assignRef('shipToID', $_shipto_id);
	}
	
	function shopper($userFields){
		
		$this->loadHelper('permissions');
		$this->loadHelper('shoppergroup');
		$this->loadHelper('shopfunctions');
		
		// Shopper info
		$_shoppergroup = ShopperGroup::getShoppergroupById ($this->_uid);
		if(Permissions::getInstance()->check("admin,storeadmin")){
			$this->_lists['shoppergroups'] = ShopFunctions::renderShopperGroupList($_shoppergroup['shopper_group_id']);
			$this->_lists['vendors'] = ShopFunctions::renderVendorList($this->_userDetails->vendor_id);

		} else {
			$this->_lists['shoppergroups'] = $_shoppergroup['shopper_group_name'];
			if(empty($this->_lists['shoppergroups'])){
				$this->_lists['shoppergroups']='unregistered';
			}
			$this->_lists['shoppergroups'] .= '<input type="hidden" name="shopper_group_id" value = "' . $_shoppergroup['shopper_group_id'] . '" />';

				
			if(!empty($this->_userDetails->vendor_id)){
				$this->_lists['vendors'] = $this->_userDetails->vendor_id;
			}
				
			if(empty($this->_lists['vendors'])){
				$this->_lists['vendors'] = JText::_('VM_USER_NOT_A_VENDOR');
			}
		}
		
		if(Permissions::getInstance()->check("admin,storeadmin")){
			$this->_lists['perms'] = JHTML::_('select.genericlist', Permissions::getUserGroups(), 'perms', '', 'group_name', 'group_name', $this->_userDetailsList->perms);
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
		&& ($this->_uid != $this->_cuid)); // Can't block myself TODO I broke that, please retest if it is working again
		$this->_lists['canSetMailopt'] = $this->_currentUser->authorize('workflow', 'email_events');
		$this->_lists['block']     = JHTML::_('select.booleanlist', 'block',     0, $this->_userDetails->JUser->get('block'),     'VM_ADMIN_CFG_YES', 'VM_ADMIN_CFG_NO');
		$this->_lists['sendEmail'] = JHTML::_('select.booleanlist', 'sendEmail', 0, $this->_userDetails->JUser->get('sendEmail'), 'VM_ADMIN_CFG_YES', 'VM_ADMIN_CFG_NO');

		$this->_lists['params'] = $this->_userDetails->JUser->getParameters(true);

		$this->_lists['custnumber'] = $this->_model->getCustomerNumberById($this->_uid);
		
		//TODO I do not understand for what we have that by Max.
		if ($this->_uid < 1) {
			$this->_lists['register_new'] = 1;
		} else {
			$this->_lists['register_new'] = 0;
		}

	}
	
	function lVendor(){

		$vendor = new Vendor;

		// If the current user is a vendor, load the store data
		if ($vendor->isVendor($this->_uid)) {
			$this->loadHelper('vendorhelper');
			$this->loadHelper('currencydisplay');
			if(!$this->_orderList){
				$this->lOrderlist();
			}
			$_vendorData = Vendor::getVendorFields($this->_userDetails->vendor_id, array('vendor_currency_display_style'));
			if (count($this->_orderList) > 0) {
				if (!empty($_vendorData)) {
					$_currencyDisplayStyle = Vendor::get_currency_display_style(
						 $this->_userDetails->vendor_id
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
dump($this, 'VD');
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
