<?php
/**
 *
 * Description
 *
 * @package	VirtueMart
 * @subpackage
 * @author
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the view framework
jimport( 'joomla.application.component.view');

/**
 * HTML View class for the VirtueMart Component
 *
 * @package		VirtueMart
 * @author
 */
class VirtuemartViewOrders extends JView {

	function display($tpl = null) {

		$mainframe = JFactory::getApplication();
		$option = JRequest::getWord('option');
		$lists = array();

		/* Load helpers */
		$this->loadHelper('adminui');
		$this->loadHelper('currencydisplay');
		$this->loadHelper('shopFunctions');
		$this->loadHelper('html');

//		require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'vendor.php'); Obsolete now??
		if(!class_exists('vmOrderPlugin')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmorderplugin.php');
		if(!class_exists('vmPaymentPlugin')) require(JPATH_VM_SITE.DS.'helpers'.DS.'vmpaymentplugin.php');
		if(!class_exists('vmShipperPlugin')) require(JPATH_VM_SITE.DS.'helpers'.DS.'vmshipperplugin.php');
		if(!class_exists('VirtueMartModelOrderstatus')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'orderstatus.php');
		$orderStatusModel=new VirtueMartModelOrderstatus();
		$orderStates = $orderStatusModel->getOrderStatusList();

		$viewName=ShopFunctions::SetViewTitle( 'ORDER');
		$this->assignRef('viewName',$viewName);

		$curTask = JRequest::getWord('task');
		if ($curTask == 'edit') {

			// Load addl models
			$orderModel = $this->getModel('orders');
			$userFieldsModel = $this->getModel('userfields');
			$productModel = $this->getModel('product');

			// Get the data
			$virtuemart_order_id = JRequest::getInt('virtuemart_order_id');
			$order = $orderModel->getOrder($virtuemart_order_id);
			$_orderID = $order['details']['BT']->virtuemart_order_id;
			$orderbt = $order['details']['BT'];
			$orderst = (array_key_exists('ST', $order['details'])) ? $order['details']['ST'] : $orderbt;

			$currency = CurrencyDisplay::getInstance('',$order['details']['BT']->virtuemart_vendor_id);
			$this->assignRef('currency', $currency);

			$_userFields = $userFieldsModel->getUserFields(
					 'account'
					, array('captcha' => true, 'delimiters' => true) // Ignore these types
					, array('delimiter_userinfo','user_is_vendor' ,'username','password', 'password2', 'agreed', 'address_type') // Skips
			);

			$userfields = $userFieldsModel->getUserFieldsByUser(
					 $_userFields
					,$orderbt
			);

			$_userFields = $userFieldsModel->getUserFields(
					 'shipping'
					, array() // Default switches
					, array('delimiter_userinfo', 'username', 'email', 'password', 'password2', 'agreed', 'address_type') // Skips
			);

			$shippingfields = $userFieldsModel->getUserFieldsByUser(
					 $_userFields
					,$orderst
			);

			// Create an array to allow orderlinestatuses to be translated
			// We'll probably want to put this somewhere in ShopFunctions...
			$_orderStatusList = array();
			foreach ($orderStates as $orderState) {
				//$_orderStatusList[$orderState->virtuemart_orderstate_id] = $orderState->order_status_name;
				//When I use update, I have to use this?
				$_orderStatusList[$orderState->order_status_code] = JText::_($orderState->order_status_name);
			}

			$_itemStatusUpdateFields = array();
			$_itemAttributesUpdateFields = array();
			foreach($order['items'] as $_item) {
				$_itemStatusUpdateFields[$_item->virtuemart_order_item_id] = JHTML::_('select.genericlist', $orderStates, "cid[".$_item->virtuemart_order_item_id."][order_status]", 'class="selectItemStatusCode"', 'order_status_code', 'order_status_name', $_item->order_status, 'order_item_status'.$_item->virtuemart_order_item_id,true);

			}

                       // $_shippingInfo = ShopFunctions::getShippingRateDetails($orderbt->ship_method_id);

			/* Assign the data */
			$this->assignRef('order', $order);
			$this->assignRef('orderID', $_orderID);
			$this->assignRef('userfields', $userfields);
			$this->assignRef('shippingfields', $shippingfields);
			$this->assignRef('orderstatuslist', $_orderStatusList);
			$this->assignRef('itemstatusupdatefields', $_itemStatusUpdateFields);
			$this->assignRef('itemattributesupdatefields', $_itemAttributesUpdateFields);
			$this->assignRef('orderbt', $orderbt);
			$this->assignRef('orderst', $orderst);
			$this->assignRef('ship_method_id', $orderbt->ship_method_id);

			/* Data for the Edit Status form popup */
			$_currentOrderStat = $order['details']['BT']->order_status;
			// used to update all item status in one time
			$_orderStatusSelect = JHTML::_('select.genericlist', $orderStates, 'order_status', '', 'order_status_code', 'order_status_name', $_currentOrderStat, 'order_items_status',true);
			$this->assignRef('orderStatSelect', $_orderStatusSelect);
			$this->assignRef('currentOrderStat', $_currentOrderStat);

			/* Toolbar */
			JToolBarHelper::custom( 'prev', 'edit','','COM_VIRTUEMART_ITEM_PREVIOUS',false);
			JToolBarHelper::custom( 'next', 'edit','','COM_VIRTUEMART_ITEM_NEXT',false);
			JToolBarHelper::divider();
			JToolBarHelper::custom( 'cancel', 'back','back','back',false,false);
		}
		else if ($curTask == 'editOrderItem') {
			$this->loadHelper('calculationHelper');

			$this->assignRef('orderstatuses', $orderStates);

			$model = $this->getModel();
			$orderId = JRequest::getString('orderId', '');
			$orderLineItem = JRequest::getVar('orderLineId', '');
			$this->assignRef('virtuemart_order_id', $orderId);
			$this->assignRef('virtuemart_order_item_id', $orderLineItem);

			$orderItem = $model->getOrderLineDetails($orderId, $orderLineItem);
			$this->assignRef('orderitem', $orderItem);
		}
		else {
			$this->setLayout('orders');

			/* Get the data */
			$orderslist = $this->get('OrdersList');

			$this->assignRef('orderstatuses', $orderStates);

			if(!class_exists('CurrencyDisplay'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'currencydisplay.php');

			/* Apply currency This must be done per order since it's vendor specific */
			$_currencies = array(); // Save the currency data during this loop for performance reasons
			foreach ($orderslist as $virtuemart_order_id => $order) {

				//This is really interesting for multi-X, but I avoid to support it now already, lets stay it in the code
				if (!array_key_exists('v'.$order->virtuemart_vendor_id, $_currencies)) {
					$_currencies['v'.$order->virtuemart_vendor_id] = CurrencyDisplay::getInstance('',$order->virtuemart_vendor_id);
				}
				$order->order_total = $_currencies['v'.$order->virtuemart_vendor_id]->priceDisplay($order->order_total,'',false);
			}

			/*
			 * UpdateStatus removed from the toolbar; don't understand how this was intented to work but
			 * the order ID's aren't properly passed. Might be readded later; the controller needs to handle
			 * the arguments.
			 */

			 /* Toolbar */
			JToolBarHelper::save('updatestatus', JText::_('COM_VIRTUEMART_UPDATE_STATUS'));
			JToolBarHelper::deleteListX();

			/* Assign the data */
			$this->assignRef('orderslist', $orderslist);

		/* Assign general statuses */
			$model = $this->getModel();
			$lists = ShopFunctions::addStandardDefaultViewLists($model);
            $this->assignRef('lists', $lists);
		}
		parent::display($tpl);
	}

	function renderMail () {
		$tpl = isset($this->layoutName) ? 'mail_html_' . $this->layoutName : 'mail_html_updorder';
		$this->setLayout($tpl);
		$vendorModel = $this->getModel('vendor');
		$virtuemart_vendor_id = $vendorModel->getVendorId('order', $this->order['virtuemart_order_id']);
		if(!class_exists('VirtueMartModelOrders')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'orders.php');
		$orderModel=new VirtueMartModelOrders();
		$this->orderdata = $orderModel->getOrder($this->order['virtuemart_order_id']);
		$vendorModel->setId($virtuemart_vendor_id);
		$this->vendor = $vendorModel->getVendor();
		$this->vendor->email = $vendorModel->getVendorEmail($this->vendor->virtuemart_vendor_id);
		$this->vendorEmail = $this->vendor->email ;
		$this->subject = ($tpl == 'mail_html_download') ? JText::_('COM_VIRTUEMART_DOWNLOADS_SEND_SUBJ') : JText::sprintf('COM_VIRTUEMART_ORDER_STATUS_CHANGE_SEND_SUBJ',$this->orderdata['details']['BT']->order_number);
		$this->doVendor = true;
		parent::display();
	}

}

