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

	function display($tpl = null) {

		//todo this must be removed somehow by Max Milbers
		global $option;

		$layoutName = JRequest::getVar('layout', $this->getLayout());

		$model = $this->getModel('user');
		$editor = JFactory::getEditor();
		$_currentUser =& JFactory::getUser();
		$lists['current_id'] = $_currentUser->get('id');
		
		// Get the required helpers
		if($layoutName=='edit'){
			$this->loadHelper('permissions');
			$this->loadHelper('shoppergroup');
			$this->loadHelper('shopfunctions');
			$this->loadHelper('vendorhelper');
			$this->loadHelper('currencydisplay');
			$this->loadHelper('image');		
		}

		$userFieldsModel = $this->getModel('userfields', 'VirtuemartModel');
		$model->setId($_currentUser->get('id'));
		$userDetails = $model->getUser();
//		echo 'UserDetails: <pre>'.print_r($userDetails).'</pre>';
		if($layoutName=='edit'){
			$orderModel = $this->getModel('orders');
			$vendor = new Vendor;
		}

		$_new = ($_currentUser->get('id') < 1);

		// User details
		$_contactDetails = $model->getContactDetails();
		
	if($layoutName=='edit'){
		$_groupList = $model->getGroupList();

		if (!is_array($_groupList)) {
			$lists['gid'] = '<input type="hidden" name="gid" value="'. $userDetails->JUser->get('gid') .'" /><strong>'. JText::_($_groupList) .'</strong>';
		} else {
			$lists['gid'] 	= JHTML::_('select.genericlist', $_groupList, 'gid', 'size="10"', 'value', 'text', $userDetails->JUser->get('gid'));
		}

		$lists['canBlock']      = ($_currentUser->authorize('com_users', 'block user')
						&& ($userDetails->JUser->get('id') != $_currentUser->get('id'))); // Can't block myself
		$lists['canSetMailopt'] = $_currentUser->authorize('workflow', 'email_events');
		$lists['block']     = JHTML::_('select.booleanlist', 'block',     0, $userDetails->JUser->get('block'),     'VM_ADMIN_CFG_YES', 'VM_ADMIN_CFG_NO');
		$lists['sendEmail'] = JHTML::_('select.booleanlist', 'sendEmail', 0, $userDetails->JUser->get('sendEmail'), 'VM_ADMIN_CFG_YES', 'VM_ADMIN_CFG_NO');

		$lists['params'] = $userDetails->JUser->getParameters(true);

		// Shopper info
		$_shoppergroup = ShopperGroup::getShoppergroupById ($userDetails->JUser->get('id'));
		if(Permissions::getInstance()->check("admin,storeadmin")){		
			$lists['shoppergroups'] = ShopFunctions::renderShopperGroupList($_shoppergroup['shopper_group_id']);
			$lists['vendors'] = ShopFunctions::renderVendorList($userDetails->vendor_id->vendor_id);		
		} else {
			$lists['shoppergroups'] = $_shoppergroup['shopper_group_name'];
			if(empty($lists['shoppergroups'])){
				$lists['shoppergroups']='unregistered';
			}
			$lists['shoppergroups'] .= '<input type="hidden" name="shopper_group_id" value = "' . $_shoppergroup['shopper_group_id'] . '" />';
//			echo 'Test <pre>'.print_r($userDetails->vendor_id).'</pre>';
			
			if(!empty($userDetails->vendor_id)){
				$lists['vendors'] = $userDetails->vendor_id->vendor_id;
			}
			
			if(empty($lists['vendors'])){
				$lists['vendors'] = JText::_('VM_USER_NOT_A_VENDOR');
			}
		}

		$lists['custnumber'] = $model->getCustomerNumberById($userDetails->JUser->get('id'));
		if ($_new) {
			$lists['register_new'] = 1;
		} else {
			$lists['register_new'] = 0;
		}
	}
		// Shipping address(es)
		$_addressList = $model->getUserAddressList($userDetails->JUser->get('id') , 'ST');
		if (($_c = count($_addressList)) == 0) {
			$lists['shipTo'] = JText::_('VM_USER_NOSHIPPINGADDR');
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
			$lists['shipTo'] = '<ul>' . join('', $_shipTo) . '</ul>';
		}

		$_userFields = $userFieldsModel->getUserFields(
				 'account'
				, array() // Default toggles
				, array('delimiter_userinfo', 'username', 'email', 'password', 'password2'
					, 'agreed', 'address_type', 'bank') // Skips
		);
		if (($_addressCount = count($userDetails->userInfo)) == 0) {
			$_userDetailsList = null;
			$_userInfoID = null;
		} else {
			$_userDetailsList = current($userDetails->userInfo);
			for ($_i = 0; $_i < $_addressCount; $_i++) {
				if ($_userDetailsList->address_type == 'BT') {
					$_userInfoID = $_userDetailsList->user_info_id;
					reset($userDetails->userInfo);
					break;
				}
				$_userDetailsList = next($userDetails->userInfo);
			}
		}
		$userFields = $userFieldsModel->getUserFieldsByUser(
				 $_userFields
				,$_userDetailsList
		);

		// Bank details, reuse the current $_userDetailsList pointer that holds the BT info
		$_bankFields = $userFieldsModel->getUserFields(
			 'bank'
			, array() // Default toggles
		);
		$_bankInfo = $userFieldsModel->getUserFieldsByUser(
			 $_bankFields
			,$_userDetailsList
		);

	if($layoutName=='edit'){
		if(Permissions::getInstance()->check("admin,storeadmin")){
			$lists['perms'] = JHTML::_('select.genericlist', Permissions::getUserGroups(), 'perms', '', 'group_name', 'group_name', $_userDetailsList->perms);	
		} else {
			if(!empty($_userDetailsList->perms)){
				$lists['perms'] = $_userDetailsList->perms;
			}
			if(empty($lists['perms'])){
				$lists['perms'] = 'shopper'; // TODO Make this default configurable
			}
			$_hiddenInfo = '<input type="hidden" name="perms" value = "' . $lists['perms'] . '" />';
			$lists['perms'] .= $_hiddenInfo;
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
		// The ShipTo address if selected
		$_shipto_id = JRequest::getVar('shipto', -1);
		if ($_shipto_id == -1) {
			$_shipto = 0;
			$_openTab = JRequest::getVar('tab', -1);
		} else {
			// Contains 0 for new, otherwise a user_info_id
			$_shipto = $model->getUserAddress($userDetails->JUser->get('id'), $_shipto_id, 'ST');
			$_openTab = 3;
			$_shiptoFields = $userFieldsModel->getUserFields(
				 'shipping'
				, array() // Default toggles
			);
			if ($_shipto_id === 0) {
				$_userDetailsList = null;
			} else {
				// Find the correct record
				$_userDetailsList = current($userDetails->userInfo);
				for ($_i = 0; $_i < count($userDetails->userInfo); $_i++) {
					if ($_userDetailsList->user_info_id == $_shipto_id) {
						reset($userDetails->userInfo);
						break;
					}
					$_userDetailsList = next($userDetails->userInfo);
				}
			}
			$shipToFields = $userFieldsModel->getUserFieldsByUser(
				 $_shiptoFields
				,$_userDetailsList
				,'shipto_'
			);
			$this->assignRef('shipToFields', $shipToFields);
			$this->assignRef('shipToID', $_shipto_id);
		}

		if ($_openTab < 0) {
			$_paneOffset = array();
		} else {
			if (__VM_USER_USE_SLIDERS) {
				$_paneOffset = array('startOffset' => $_openTab, 'startTransition' => 1, 'allowAllClose' => true);
			} else {
				$_paneOffset = array('startOffset' => $_openTab);
			}
		}


	if($layoutName=='edit'){
		// Check for existing orders for this user
		$orders = $this->getModel('orders');

		if ($userDetails->JUser->get('id') == 0) {
			// getOrdersList() returns all orders when no userID is set (admin function),
			// so explicetly define an empty array when not logged in. 
			$orderList = array();
		} else {
			$orderList = $orders->getOrdersList($userDetails->JUser->get('id'), true);
		}


		
		// If the current user is a vendor, load the store data
		if ($vendor->isVendor($userDetails->JUser->get('id'))) {
			
			$_vendorData = Vendor::getVendorFields($userDetails->vendor_id->vendor_id, array('vendor_currency_display_style'));
			if (count($orderList) > 0) {
				if (!empty($_vendorData)) {
					$_currencyDisplayStyle = Vendor::get_currency_display_style($userDetails->vendor_id->vendor_id
						, $_vendorData->vendor_currency_display_style);
					$currency = new CurrencyDisplay($_currencyDisplayStyle['id'], $_currencyDisplayStyle['symbol']
						, $_currencyDisplayStyle['nbdecimal'], $_currencyDisplayStyle['sdecimal']
						, $_currencyDisplayStyle['thousands'], $_currencyDisplayStyle['positive']
						, $_currencyDisplayStyle['negative']
					);
				} else {
//					$currency = new CurrencyDisplay();
				}
				$this->assignRef('currency', $currency);
			}
		
			$storeModel = $this->getModel('store');
			$storeModel->setId($vendor->getVendorIdByUserId($userDetails->JUser->get('id')));
			$_store = $storeModel->getStore();
			$this->assignRef('store', $_store);
			$currencyModel = $this->getModel('currency');
			$_currencies = $currencyModel->getCurrencies();
			$this->assignRef('currencies', $_currencies);
			$_vendorCats = JHTML::_('select.genericlist', $vendor->getVendorCategories(), 'vendor_category_id', '', 'vendor_category_id', 'vendor_category_name', $this->store->vendor_category_id);
			$this->assignRef('vendorCategories', $_vendorCats);
			$_currencyDisplayStyle = Vendor::get_currency_display_style($vendor->getVendorIdByUserId($userDetails->JUser->get('id'))
				, $_vendorData->vendor_currency_display_style);
			$_vendorCurrency = new CurrencyDisplay($_currencyDisplayStyle['id'], $_currencyDisplayStyle['symbol']
				, $_currencyDisplayStyle['nbdecimal'], $_currencyDisplayStyle['sdecimal']
				, $_currencyDisplayStyle['thousands'], $_currencyDisplayStyle['positive']
				, $_currencyDisplayStyle['negative']
			);
			$this->assignRef('vendorCurrency', $_vendorCurrency);
		}
		
		if(empty($currency)){
			$currency = new CurrencyDisplay();
			$this->assignRef('currency', $currency);
		}
	}
		if($layoutName=='edit_address'){
			$setForm = true;
		}else {
			$setForm = false;
		}
		$this->assignRef('setForm', $setForm);
		
		// Implement the Joomla panels. If we need a ShipTo tab, make it the active one.
		// In tmpl/edit.php, this is the 4th tab (0-based, so set to 3 above)
		jimport('joomla.html.pane');
		$pane = JPane::getInstance((__VM_USER_USE_SLIDERS?'Sliders':'Tabs'), $_paneOffset);

		$this->assignRef('lists', $lists);
		$this->assignRef('userDetails', $userDetails);
		$this->assignRef('shipto', $_shipto);
		$this->assignRef('bankInfo', $_bankInfo);
		$this->assignRef('userFields', $userFields);
		$this->assignRef('userInfoID', $_userInfoID);
		$this->assignRef('vendor', $vendor);
		$this->assignRef('orderlist', $orderList);
		$this->assignRef('contactDetails', $_contactDetails);
		$this->assignRef('editor', $editor);
		$this->assignRef('pane', $pane);
		parent::display($tpl);
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
