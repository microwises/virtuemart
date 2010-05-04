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
* @version $Id:$
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the view framework
jimport( 'joomla.application.component.view');

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

		// Load the helper(s)
		$this->loadHelper('adminMenu');
		
		$layoutName = JRequest::getVar('layout', 'default');
		$model = $this->getModel();

		if ($layoutName == 'edit' || $layoutName='account') {
			$editor = JFactory::getEditor();

			$_currentUser =& JFactory::getUser();

			// Get the required helpers
			$this->loadHelper('permissions');
			$this->loadHelper('shoppergroup');
			$this->loadHelper('shopfunctions');
			$this->loadHelper('vendorhelper');
			$this->loadHelper('currencydisplay');
			$this->loadHelper('image');
			
//			$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR .DS.'models' );
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'userfields.php');
$userFieldsModel = new VirtueMartModelUserfields;
//			$userFieldsModel = $this->getModel('userfields');
//			$orderModel = $this->getModel('orders');
			$vendor = new Vendor;
			
			$userDetails = $model->getUser();
			$_new = ($userDetails->JUser->get('id') < 1);
			
			if ($layoutName == 'edit'){
				// In order for the Form validator to work, we're creating our own buttons here.
//				$_saveButton = '<a class="toolbar" class="button validate" type="submit" onclick="javascript:return myValidator(adminForm, \'save\');" href="#">'
//					. '<span title="' . JText::_('Save' ) . '" class="icon-32-save"></span>' . JText::_('Save' ) . '</a>';
//				$_applyButton = '<a class="toolbar" class="button validate" type="submit" onclick="javascript:return myValidator(adminForm, \'apply\');" href="#">'
//					. '<span title="' . JText::_('Apply' ) . '" class="icon-32-apply"></span>' . JText::_('Apply' ) . '</a>';
////				$_toolBar =& JToolBar::getInstance('toolbar');
//				if ($_new) { // Insert new user
//					JToolBarHelper::title(  JText::_('VM_USER_FORM_LBL' ).': <small><small>[ New ]</small></small>', 'vm_user_48.png');
//					JToolBarHelper::divider();
//					$_toolBar->appendButton('Custom', $_saveButton);
//					$_toolBar->appendButton('Custom', $_applyButton);
//					JToolBarHelper::cancel();
//				} else { // Update existing user
//					JToolBarHelper::title( JText::_('VM_USER_FORM_LBL' ).': <small><small>[ Edit ]</small></small>', 'vm_user_48.png');
//					JToolBarHelper::divider();
//					$_toolBar->appendButton('Custom', $_saveButton);
//					$_toolBar->appendButton('Custom', $_applyButton);
//					JToolBarHelper::cancel('cancel', 'Close');
//				}
			}
			// User details
			$_contactDetails = $model->getContactDetails();
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
			$lists['shoppergroups'] = ShopFunctions::renderShopperGroupList($_shoppergroup['shopper_group_id']);
			$lists['vendors'] = ShopFunctions::renderVendorList($userDetails->vendor_id->vendor_id);
			$lists['custnumber'] = $model->getCustomerNumberById($userDetails->JUser->get('id'));

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
										.'&task=edit'
										.'&cid[]='.$_addressList[$_i]->user_id
										.'&shipto='.$_addressList[$_i]->user_info_id
									. '">'.$_addressList[$_i]->address_type_name.'</a>'.'</li>';
				
				}
				$lists['shipTo'] = '<ul>' . join('', $_shipTo) . '</ul>';
			}

			$_userFields = $userFieldsModel->getUserFields(
					 'account'
					, array() // Default toggles
					, array('delimiter_userinfo', 'username', 'email', 'password', 'password2', 'agreed', 'address_type') // Skips
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

			$lists['perms'] = JHTML::_('select.genericlist', Permissions::getUserGroups(), 'perms', '', 'group_id', 'group_name', $_userDetailsList->perms);
			
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

			// The ShipTo address if selected
			$_shipto_id = JRequest::getVar('shipto', -1);
			if ($_shipto_id == -1) {
				$_shipto = 0;
				$_paneOffset = array();
			} else {
				// Contains 0 for new, otherwise a user_info_id
				$_shipto = $model->getUserAddress($userDetails->JUser->get('id'), $_shipto_id, 'ST');
				$_paneOffset = array('startOffset' => 2);
				$_shiptoFields = $userFieldsModel->getUserFields(
					 'shipping'
					, array() // Default toggles
				);
				if ($_shipto_id === 0) {
					$_userDetailsList = null;
				} else {
					// Find the correct record
					$_userDetailsList = current($userDetails->userInfo);
					for ($_i = 0; $_i <= count($userDetails->userInfo); $_i++) {
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


			// Check for existing orders for this user
			require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'orders.php');
//			$userFieldsModel = new VirtueMartModelUserfields;
			$orders = new VirtueMartModelOrders();
			$orderList = $orders->getOrdersList($userDetails->JUser->get('id'), true);
			if (count($orderList) > 0) {
				$_vendorData = Vendor::getVendorFields($userDetails->vendor_id->vendor_id, array('vendor_currency_display_style'));
				if (!empty($_vendorData)) {
					$_currencyDisplayStyle = Vendor::get_currency_display_style($userDetails->vendor_id->vendor_id
						, $_vendorData->vendor_currency_display_style);
					$currency = new CurrencyDisplay($_currencyDisplayStyle['id'], $_currencyDisplayStyle['symbol']
						, $_currencyDisplayStyle['nbdecimal'], $_currencyDisplayStyle['sdecimal']
						, $_currencyDisplayStyle['thousands'], $_currencyDisplayStyle['positive']
						, $_currencyDisplayStyle['negative']
					);
				} else {
					$currency = new CurrencyDisplay();
				}
				$this->assignRef('currency', $currency);
			}

			// If the current user is a vendor, load the store data
			if ($vendor->isVendor($userDetails->JUser->get('id'))) {
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

			// Implement the Joomla panels. If we need a ShipTo tab, make it the active one.
			// In tmpl/edit.php, this is the 3th tab (0-based, so set to 2 above)
			jimport('joomla.html.pane');
			$pane = JPane::getInstance('Tabs', $_paneOffset);

			$this->assignRef('lists', $lists);
			$this->assignRef('userDetails', $userDetails);
			$this->assignRef('shipto', $_shipto);
			$this->assignRef('userFields', $userFields);
			$this->assignRef('userInfoID', $_userInfoID);
			$this->assignRef('vendor', $vendor);
			$this->assignRef('orderlist', $orderList);
			$this->assignRef('contactDetails', $_contactDetails);
			$this->assignRef('editor', $editor);
			$this->assignRef('pane', $pane);
		} else {
			JToolBarHelper::title( JText::_('VM_USER_LIST_LBL'), 'vm_user_48.png');
			JToolBarHelper::addNewX();
			JToolBarHelper::editListX();
			JToolBarHelper::divider();
			JToolBarHelper::custom('enable_vendor', 'publish','','VM_USER_ISVENDOR');
			JToolBarHelper::custom('disable_vendor', 'unpublish','','VM_USER_ISNOTVENDOR');
			JToolBarHelper::divider();
			JToolBarHelper::deleteList('', 'remove', 'Delete');

			$userList = $model->getUserList();
			$this->assignRef('userList', $userList);

			$pagination = $model->getPagination();
			$this->assignRef('pagination', $pagination);

			// search filter
			$mainframe = JFactory::getApplication('site');
			$search = $mainframe->getUserStateFromRequest( $option.'search', 'search', '', 'string');
			$search = JString::strtolower( $search );
			$lists['search']= $search;

			// Get the ordering
			$lists['order']     = $mainframe->getUserStateFromRequest( $option.'filter_order', 'filter_order', 'id', 'cmd' );
			$lists['order_Dir'] = $mainframe->getUserStateFromRequest( $option.'filter_order_Dir', 'filter_order_Dir', '', 'word' );
			$this->assignRef('lists', $lists);
		}

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
