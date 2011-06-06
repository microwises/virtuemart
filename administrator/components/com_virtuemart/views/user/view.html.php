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
jimport('joomla.version');

/**
 * HTML View class for maintaining the list of users
 *
 * @package	VirtueMart
 * @subpackage User
 * @author Oscar van Eijk
 */
class VirtuemartViewUser extends JView {

	function display($tpl = null) {

		$option = JRequest::getCmd( 'option');
		$mainframe = JFactory::getApplication() ;

		// Load the helper(s)
		$this->loadHelper('adminMenu');
		$this->loadHelper('shopfunctions');
		

		$model = $this->getModel();

		$_currentUser =& JFactory::getUser();

		$task = JRequest::getVar('task', 'edit');
		if($task == 'editshop'){
			$model->setCurrent();
			$viewName=ShopFunctions::SetViewTitle('vm_shop_users_48','STORE'  );
		} else {
			$viewName=ShopFunctions::SetViewTitle('vm_shop_users_48','USER');
		}
		
		$this->assignRef('viewName',$viewName);

		$layoutName = JRequest::getVar('layout', 'default');
		if ($layoutName == 'edit') {
			$editor = JFactory::getEditor();

			// Get the required helpers
			$this->loadHelper('permissions');
			$this->loadHelper('shoppergroup');

			$this->loadHelper('currencydisplay');
			$this->loadHelper('image');

			$userFieldsModel = $this->getModel('userfields');
			//			$orderModel = $this->getModel('orders');

			$userDetails = $model->getUser();

                        if($task == 'editshop'){
			$model->setCurrent();
                            $viewName=ShopFunctions::SetViewTitle('vm_shop_users_48','STORE',$userDetails->vendor->vendor_store_name );
                        } else {
                            $viewName=ShopFunctions::SetViewTitle('vm_shop_users_48','USER',$userDetails->JUser->get('name'));
                        }


			$_new = ($userDetails->JUser->get('id') < 1);
			// In order for the Form validator to work, we're creating our own buttons here.
			$_saveButton = '<a class="toolbar" class="button validate" type="submit" onclick="javascript:return myValidator(adminForm, \'save\');" href="#">'
			. '<span title="' . JText::_('COM_VIRTUEMART_SAVE') . '" class="icon-32-save"></span>' . JText::_('COM_VIRTUEMART_SAVE') . '</a>';
			$_applyButton = '<a class="toolbar" class="button validate" type="submit" onclick="javascript:return myValidator(adminForm, \'apply\');" href="#">'
			. '<span title="' . JText::_('COM_VIRTUEMART_APPLY') . '" class="icon-32-apply"></span>' . JText::_('COM_VIRTUEMART_APPLY') . '</a>';
			$_toolBar =& JToolBar::getInstance('toolbar');

			// if ($_new) { // Insert new user
				// if($task=='editshop'){
					// JToolBarHelper::title(  JText::_('COM_VIRTUEMART_STORE_FORM_LBL').JText::_('COM_VIRTUEMART_FORM_NEW'), 'vm_user_48.png');
				// } else {
					// JToolBarHelper::title(  JText::_('COM_VIRTUEMART_USER_FORM_LBL').JText::_('COM_VIRTUEMART_FORM_NEW'), 'vm_user_48.png');
				// }
			// } else { // Update existing user
				// if($task=='editshop'){
					// JToolBarHelper::title( JText::_('COM_VIRTUEMART_STORE_FORM_LBL').JText::_('COM_VIRTUEMART_FORM_EDIT'), 'vm_user_48.png');
				// } else {
					// JToolBarHelper::title( JText::_('COM_VIRTUEMART_USER_FORM_LBL').JText::_('COM_VIRTUEMART_FORM_EDIT'), 'vm_user_48.png');
				// }
			// }

			JToolBarHelper::divider();
			$_toolBar->appendButton('Custom', $_applyButton);
			$_toolBar->appendButton('Custom', $_saveButton);
			JToolBarHelper::cancel();

			// User details
			$_contactDetails = $model->getContactDetails();
			$_groupList = $model->getGroupList();
			if (!is_array($_groupList)) {
				$lists['gid'] = '<input type="hidden" name="gid" value="'. $userDetails->JUser->get('gid') .'" /><strong>'. JText::_($_groupList) .'</strong>';
			} else {
				$lists['gid'] 	= JHTML::_('select.genericlist', $_groupList, 'gid', 'size="10"', 'value', 'text', $userDetails->JUser->get('gid'));
			}

			$lists['canBlock'] = ($_currentUser->authorize('com_users', 'block user')
			&& ($userDetails->JUser->get('id') != $_currentUser->get('id'))); // Can't block myself
			$lists['canSetMailopt'] = $_currentUser->authorize('workflow', 'email_events');
			$lists['block'] = JHTML::_('select.booleanlist', 'block',     0, $userDetails->JUser->get('block'),     'COM_VIRTUEMART_YES', 'COM_VIRTUEMART_NO');
			$lists['sendEmail'] = JHTML::_('select.booleanlist', 'sendEmail', 0, $userDetails->JUser->get('sendEmail'), 'COM_VIRTUEMART_YES', 'COM_VIRTUEMART_NO');
			$lists['params'] = $userDetails->JUser->getParameters(true);

			// Shopper info
			$lists['shoppergroups'] = ShopFunctions::renderShopperGroupList($userDetails->shopper_groups);
			$lists['vendors'] = ShopFunctions::renderVendorList($userDetails->virtuemart_vendor_id);
			$lists['custnumber'] = $model->getCustomerNumberById($userDetails->JUser->get('id'));

			// Shipping address(es)
			$_addressList = $model->getUserAddressList($userDetails->JUser->get('id') , 'ST');
			if (($_c = count($_addressList)) == 0) {
				$lists['shipTo'] = JText::_('COM_VIRTUEMART_USER_NOSHIPPINGADDR');
			} else {
				$_shipTo = array();
				for ($_i = 0; $_i < $_c; $_i++) {
					$_shipTo[] = '<li>'.'<a href="index.php'
					.'?option=com_virtuemart'
					.'&view=user'
					.'&task=edit'
					.'&cid[]='.$_addressList[$_i]->virtuemart_user_id
					.'&shipto='.$_addressList[$_i]->virtuemart_userinfo_id
					. '">'.$_addressList[$_i]->address_type_name.'</a>'.'</li>';

				}
				$lists['shipTo'] = '<ul>' . join('', $_shipTo) . '</ul>';
			}

			$_userFields = $userFieldsModel->getUserFields(
					 'account'
					 , array() // Default toggles
					 , array('delimiter_userinfo', 'username', 'email', 'password', 'password2', 'address_type') // Skips
					 );

					 if (($_addressCount = count($userDetails->userInfo)) == 0) {
					 	$_userInfoID = null;
					 	// Set some default values
					 	$_userDetailsList = new StdClass ();
					 	$_userDetailsList->address_type = 'BT';
					 	$_userDetailsList->perms = 'shopper';
					 } else {
					 	$_userDetailsList = current($userDetails->userInfo);
					 	for ($_i = 0; $_i < $_addressCount; $_i++) {
					 		if ($_userDetailsList->address_type == 'BT') {
					 			$_userInfoID = $_userDetailsList->virtuemart_userinfo_id;
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

					 //			$lists['perms'] = JHTML::_('select.genericlist', Permissions::getUserGroups(), 'perms', '', 'group_name', 'group_name', $_userDetailsList->perms);
					 $lists['perms'] = JHTML::_('select.genericlist', Permissions::getUserGroups(), 'perms', '', 'group_name', 'group_name', $userDetails->perms);

					 // Load the required scripts
					 if (count($userFields['scripts']) > 0) {
					 	foreach ($userFields['scripts'] as $_script => $_path) {
					 		JHTML::script($_script, $_path);
					 	}
					 }
					 // Load the required stylesheets
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
					 	// Contains 0 for new, otherwise a virtuemart_userinfo_id
					 	$_shipto = $model->getUserAddress($userDetails->JUser->get('id'), $_shipto_id, 'ST');
					 	$_paneOffset = array('startOffset' => 2);
					 	$_shiptoFields = $userFieldsModel->getUserFields(
					 'shipping'
					 , array() // Default toggles
					 );
					 if ($_shipto_id === 0 || empty($userDetails->userInfo)) {
					 	$_userDetailsList = null;
					 } else {
					 	// Find the correct record
					 	$_userDetailsList = current($userDetails->userInfo);
					 	for ($_i = 0; $_i <= count($userDetails->userInfo); $_i++) {

					 		// @todo oscar, I just added that, but maybe it breaks the logic, please take a look on it
					 		if(!empty($_userDetailsList)){
						 		if ($_userDetailsList->virtuemart_userinfo_id == $_shipto_id) {
						 			reset($userDetails->userInfo);
						 			break;
						 		}
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


					 if (!$_new) {
					 	// Check for existing orders for this user
					 	$orders = new VirtueMartModelOrders();
					 	$orderList = $orders->getOrdersList($userDetails->JUser->get('id'), true);
					 } else {
					 	$orderList = null;
					 }

					 $vendorModel = $this->getModel('vendor');
					 $vendorModel->setId($userDetails->virtuemart_vendor_id);

					if (count($orderList) > 0 || !empty($userDetails->user_is_vendor)) {
						if (!class_exists('CurrencyDisplay')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'currencydisplay.php');
						$currency = CurrencyDisplay::getInstance();
						$this->assignRef('currency',$currency);
					}

					 if (!empty($userDetails->user_is_vendor)) {

					 	$vendorModel->addImages($userDetails->vendor);
					 	$this->assignRef('vendor', $userDetails->vendor);

					 	$currencyModel = $this->getModel('currency');
					 	$_currencies = $currencyModel->getCurrencies();
					 	$this->assignRef('currencies', $_currencies);

					 	//can someone explain me what that should do?
					 	//				$_vendorCats = JHTML::_('select.genericlist', $vendorModel->getVendorCategories(), 'vendor_virtuemart_category_id', '', 'vendor_virtuemart_category_id', 'vendor_category_name', $userDetails->vendor->vendor_virtuemart_category_id);
					 	//				$this->assignRef('vendorCategories', $_vendorCats);

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
					 //			$this->assignRef('vendor', $vendor);
					 $this->assignRef('orderlist', $orderList);
					 $this->assignRef('contactDetails', $_contactDetails);
					 $this->assignRef('editor', $editor);
					 $this->assignRef('pane', $pane);
		} else {

			JToolBarHelper::divider();
			JToolBarHelper::custom('toggle.user_is_vendor.1', 'publish','','COM_VIRTUEMART_USER_ISVENDOR');
			JToolBarHelper::custom('toggle.user_is_vendor.0', 'unpublish','','COM_VIRTUEMART_USER_ISNOTVENDOR');
			JToolBarHelper::divider();
			JToolBarHelper::deleteList();
			JToolBarHelper::editListX();
			JToolBarHelper::addNewX();
			$userList = $model->getUserList();
			$this->assignRef('userList', $userList);

			$pagination = $model->getPagination();
			$this->assignRef('pagination', $pagination);

			// search filter
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

	function renderMail ($doVendor=false) {
		$tpl = ($doVendor) ? 'mail_html_regvendor' : 'mail_html_reguser';
		$this->setLayout($tpl);

		$vendorModel = $this->getModel('vendor');
		$vendorId = 1;
		$vendorModel->setId($vendorId);
		$vendor = $vendorModel->getVendor();
		$vendorModel->addImages($vendor);

		$this->assignRef('subject', ($doVendor) ? JText::sprintf('COM_VIRTUEMART_NEW_USER_MESSAGE_VENDOR_SUBJECT', $this->user->get('email')) : JText::sprintf('COM_VIRTUEMART_NEW_USER_MESSAGE_SUBJECT',$vendor->vendor_store_name));
		parent::display();
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
			$alt 	= $field ? JText::_('COM_VIRTUEMART_PUBLISHED') : JText::_('COM_VIRTUEMART_UNPUBLISHED');
			$action = $field ? JText::_('COM_VIRTUEMART_UNPUBLISH_ITEM') : JText::_('COM_VIRTUEMART_PUBLISH_ITEM');
		} else {
			$task 	= $field ? $toggle.'.0' : $toggle.'.1';
			$alt 	= $field ? JText::_('COM_VIRTUEMART_PUBLISHED') : JText::_('COM_VIRTUEMART_DISABLED');
			$action = $field ? JText::_('COM_VIRTUEMART_DISABLE_ITEM') : JText::_('COM_VIRTUEMART_ENABLE_ITEM');
		}

		if (VmConfig::isAtLeastVersion('1.6.0')) {
			return ('<a href="javascript:void(0);" onclick="return listItemTask(\'cb'. $i .'\',\''. $task .'\')" title="'. $action .'">'
			.JHTML::_('image', 'admin/' .$img, $alt, null, true) .'</a>');
		}

		return ('<a href="javascript:void(0);" onclick="return listItemTask(\'cb'. $i .'\',\''. $task .'\')" title="'. $action .'">'
			.'<img src="images/'. $img .'" border="0" alt="'. $alt .'" /></a>');
	}


}

//No Closing Tag
