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
		$this->loadHelper('adminui');
		$this->loadHelper('shopfunctions');


		$model = $this->getModel();

		$currentUser = JFactory::getUser();

		$task = JRequest::getWord('task', 'edit');
		if($task == 'editshop'){

			if(Vmconfig::get('multix','none') !=='none'){
				$model->setCurrent();
			} else {
				if(!class_exists('VirtueMartModelVendor')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'vendor.php');
				$userId = VirtueMartModelVendor::getUserIdByVendorId(1);
				$model->setId($userId);
			}

			$viewName=ShopFunctions::SetViewTitle('STORE'  );
		} else if ($task == 'add'){
			$model->setUserId(0);
		} else {
			$viewName=ShopFunctions::SetViewTitle('USER');
		}


		$this->assignRef('viewName',$viewName);

		$layoutName = JRequest::getWord('layout', 'default');
		$layoutName = $this->getLayout();

		if ($layoutName == 'edit' || $layoutName == 'edit_shipto') {

			$editor = JFactory::getEditor();

			// Get the required helpers
			$this->loadHelper('permissions');
			$this->loadHelper('shoppergroup');

			$this->loadHelper('currencydisplay');
			$this->loadHelper('image');

			$userFieldsModel = $this->getModel('userfields');
			//			$orderModel = $this->getModel('orders');

			$userDetails = $model->getUser();

			if($task == 'editshop' && $userDetails->user_is_vendor){
				$model->setCurrent();
				if(!empty($userDetails->vendor->vendor_store_name)){
					$viewName=ShopFunctions::SetViewTitle('STORE',$userDetails->vendor->vendor_store_name );
				} else {
					$viewName=ShopFunctions::SetViewTitle('STORE',JText::_('COM_VIRTUEMART_NEW_VENDOR') );
				}

			} else {
				$viewName=ShopFunctions::SetViewTitle('USER',$userDetails->JUser->get('name'));
			}


			$_new = ($userDetails->JUser->get('id') < 1);

			ShopFunctions::addStandardEditViewCommands();

			// User details
			$_contactDetails = $model->getContactDetails();
			$_groupList = $model->getGroupList();
			if (!is_array($_groupList)) {
				$lists['gid'] = '<input type="hidden" name="gid" value="'. $userDetails->JUser->get('gid') .'" /><strong>'. JText::_($_groupList) .'</strong>';
			} else {
				$lists['gid'] 	= JHTML::_('select.genericlist', $_groupList, 'gid', 'size="10"', 'value', 'text', $userDetails->JUser->get('gid'));
			}

			$lists['canBlock'] = ($currentUser->authorize('com_users', 'block user')
			&& ($userDetails->JUser->get('id') != $currentUser->get('id'))); // Can't block myself
			$lists['canSetMailopt'] = $currentUser->authorize('workflow', 'email_events');
			$lists['block'] = JHTML::_('select.booleanlist', 'block',      'class="inputbox"', $userDetails->JUser->get('block'),     'COM_VIRTUEMART_YES', 'COM_VIRTUEMART_NO');
			$lists['sendEmail'] = JHTML::_('select.booleanlist', 'sendEmail',  'class="inputbox"', $userDetails->JUser->get('sendEmail'), 'COM_VIRTUEMART_YES', 'COM_VIRTUEMART_NO');
			$lists['params'] = $userDetails->JUser->getParameters(true);

			// Shopper info
			$lists['shoppergroups'] = ShopFunctions::renderShopperGroupList($userDetails->shopper_groups);
			$lists['vendors'] = ShopFunctions::renderVendorList($userDetails->virtuemart_vendor_id);
			$lists['custnumber'] = $model->getCustomerNumberById($userDetails->JUser->get('id'));

			// Shipping address(es)
			$lists['shipTo'] = ShopFunctions::generateStAddressList($model,'addST');

			$new = false;
			if(JRequest::getInt('new','0')===1){
				$new = true;
			}

			$virtuemart_userinfo_id = JRequest::getString('virtuemart_userinfo_id', '0','');
			if($new){
				$virtuemart_userinfo_id = 0;
			} else {
				if(empty($virtuemart_userinfo_id)){
					$virtuemart_userinfo_id = $model->getBTuserinfo_id();
				}

			}
			$userFieldsArray = $model->getUserInfoInUserFields($layoutName,'BT',$virtuemart_userinfo_id,false);

			$userFields = $userFieldsArray[$virtuemart_userinfo_id];

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

				$this->assignRef('shipToFields', $userFields);
				$this->assignRef('shipToID', $virtuemart_userinfo_id);


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

			}



			$this->assignRef('lists', $lists);
			$this->assignRef('userDetails', $userDetails);
			$this->assignRef('shipto', $_shipto);
			$this->assignRef('userFields', $userFields);
			$this->assignRef('userInfoID', $_userInfoID);
			//			$this->assignRef('vendor', $vendor);
			$this->assignRef('orderlist', $orderList);
			$this->assignRef('contactDetails', $_contactDetails);
			$this->assignRef('editor', $editor);

		} else {

			JToolBarHelper::divider();
			JToolBarHelper::custom('toggle.user_is_vendor.1', 'publish','','COM_VIRTUEMART_USER_ISVENDOR');
			JToolBarHelper::custom('toggle.user_is_vendor.0', 'unpublish','','COM_VIRTUEMART_USER_ISNOTVENDOR');
			JToolBarHelper::divider();
			JToolBarHelper::deleteList();
			JToolBarHelper::editListX();

			//This is intentionally, creating new user via BE is buggy and can be done by joomla
			//JToolBarHelper::addNewX();
			$userList = $model->getUserList();
			$this->assignRef('userList', $userList);

			$pagination = $model->getPagination();
			$this->assignRef('pagination', $pagination);

			$lists = ShopFunctions::addStandardDefaultViewLists($model,'ju.id');

			$this->assignRef('lists', $lists);

			if(!class_exists('VirtueMartModelShopperGroup')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'shoppergroup.php');
			$shoppergroupmodel = new VirtueMartModelShopperGroup();
			$defaultShopperGroup = $shoppergroupmodel->getDefault()->shopper_group_name;
			$this->assignRef('defaultShopperGroup', $defaultShopperGroup);
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
		if ($toggle == 'published') {
			// Stay compatible with grid.published
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
