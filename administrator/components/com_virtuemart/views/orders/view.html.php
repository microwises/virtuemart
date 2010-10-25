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
 * @version $Id$
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
		$option = JRequest::getVar('option');
		$lists = array();

		/* Load helpers */
		$this->loadHelper('adminMenu');
		$this->loadHelper('currencydisplay');
		$this->loadHelper('shopFunctions');
		$this->loadHelper('html');
		$this->loadHelper('vendorhelper');

		$curTask = JRequest::getVar('task');
		if ($curTask == 'edit') {

			// Load addl models
			$userFieldsModel = $this->getModel('userfields');
			$productModel = $this->getModel('product');

			/* Get the data */
			$order = $this->get('Order');
			$_orderID = $order['details']['BT']->order_id;
			$orderbt = $order['details']['BT'];
			$orderst = (array_key_exists('ST', $order['details'])) ? $order['details']['ST'] : $orderbt;

			$_vendorData = Vendor::getVendorFields($order['details']['BT']->vendor_id, array('vendor_currency_display_style'));
			if (!empty($_vendorData)) {
				$_currencyDisplayStyle = Vendor::get_currency_display_style($order['details']['BT']->vendor_id
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

			$_userFields = $userFieldsModel->getUserFields(
					 'registration'
					, array('captcha' => true, 'delimiters' => true) // Ignore these types
					, array('delimiter_userinfo','user_is_vendor' ,'username', 'email', 'password', 'password2', 'agreed', 'address_type') // Skips
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
			$_orderStats = $this->get('OrderStatusList');
			$_orderStatusList = array();
			foreach ($_orderStats as $_ordStat) {
				$_orderStatusList[$_ordStat->value] = $_ordStat->text;
			}

			$_itemStatusUpdateFields = array();
			$_itemAttributesUpdateFields = array();
			foreach($order['items'] as $_item) {
				$_itemStatusUpdateFields[$_item->order_item_id] = JHTML::_('select.genericlist', $_orderStats, 'order_status_'.$_item->order_item_id, '', 'value', 'text', $_item->order_status, 'order_item_status');
				if (!empty($_item->product_attribute)) {
					$_attribs = preg_split('/\s?<br\s*\/?>\s?/i', $_item->product_attribute);

					$product = $productModel->getProduct($_item->product_id);
					$_productAttributes = array();
					$_prodAttribs = explode(';', $product->attribute);
					foreach ($_prodAttribs as $_pAttr) {
						$_list = explode(',', $_pAttr);
						$_name = array_shift($_list);
						$_productAttributes[$_item->order_item_id][$_name] = array();
						foreach ($_list as $_opt) {
							$_optObj = new stdClass();
							$_optObj->option = $_opt;
							$_productAttributes[$_item->order_item_id][$_name][] = $_optObj;
						}
					}

					foreach ($_attribs as $_attrib) {
						$_attr = preg_split('/:\s*/', $_attrib);
						$_itemAttributesUpdateFields[$_item->order_item_id][] = array(
							 'lbl' => $_attr[0]
							,'fld' => JHTML::_('select.genericlist'
									, $_productAttributes[$_item->order_item_id][$_attr[0]]
									, 'product_attribute_'.$_item->order_item_id.'['.$_attr[0].']'
									, null
									, 'option'
									, 'option'
									, $_attr[1])
						);
					}
				}
			}

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

			JHTML::_('behavior.modal');
			$this->setLayout('order');

			/* Data for the Edit Status form popup */
			$_currentOrderStat = $order['details']['BT']->order_status;
			$_orderStatusSelect = JHTML::_('select.genericlist', $_orderStats, 'order_status['.$_orderID.']', '', 'value', 'text', $_currentOrderStat, 'order_status');
			$this->assignRef('orderStatSelect', $_orderStatusSelect);
			$this->assignRef('currentOrderStat', $_currentOrderStat);
			
			/* Toolbar */
			JToolBarHelper::title(JText::_( 'VM_ORDER_EDIT_LBL' ), 'vm_orders_48');
			JToolBarHelper::back();
		}
//		else if ($curTask == 'editOrderStatus') {
//			/* Set the layout */
//			$this->setLayout('orders_editstatus');
//
//			/* Get the data */
//			$order = $this->get('Order');
//
//			/* Get order statuses */
//			$orderstatuses = $this->get('OrderStatusList');
//			$this->assignRef('orderstatuses', $orderstatuses);
//			$this->assignRef('order_id', $order['details']['BT']->order_id);
//			$this->assignRef('cur_order_status', $order['details']['BT']->order_status);
//			$_lo = 0; // Use a var; must be passed by reference
//			$this->assignRef('line_only', $_lo);
//		}
		else if ($curTask == 'editOrderItem') {
			$this->loadHelper('calculationHelper');

			/* Get order statuses */
			$orderstatuses = $this->get('OrderStatusList');
			$this->assignRef('orderstatuses', $orderstatuses);

			$model = $this->getModel();
			$orderId = JRequest::getVar('orderId', '');
			$orderLineItem = JRequest::getVar('orderLineId', '');
			$this->assignRef('order_id', $orderId);
			$this->assignRef('order_item_id', $orderLineItem);
			
			$orderItem = $model->getOrderLineDetails($orderId, $orderLineItem);
			$this->assignRef('orderitem', $orderItem);
		}
//		else if ($curTask == 'updateOrderItemStatus') {
//			$this->setLayout('orders_editstatus');
//
//			/* Get order statuses */
//			$orderstatuses = $this->get('OrderStatusList');
//			$this->assignRef('orderstatuses', $orderstatuses);
//
//			$model = $this->getModel();
//			$orderId = JRequest::getVar('orderId', '');
//			$orderLineItem = JRequest::getVar('orderLineId', '');
//			$this->assignRef('order_id', $orderId);
//			$this->assignRef('order_item_id', $orderLineItem);
//
//			$orderItem = $model->getOrderLineDetails($orderId, $orderLineItem);
//			$this->assignRef('orderitem', $orderItem);
//			// Following is here for syntactical reasons only (allows us to reuse the same template) 
//			$this->assignRef('cur_order_status', $orderItem->order_status);
//			$_lo = 1; // Use a var; must be passed by reference
//			$this->assignRef('line_only', $_lo);
//		}
		else {
			$this->setLayout('orders');

			/* Get the data */
			$orderslist = $this->get('OrdersList');

			/* Get order statuses */
			$orderstatuses = $this->get('OrderStatusList');
			$this->assignRef('orderstatuses', $orderstatuses);

			/* Apply currency This must be done per order since it's vendor specific */
			$_currencies = array(); // Save the currency data during this loop for performance reasons
			foreach ($orderslist as $order_id => $order) {
				if (!array_key_exists('v'.$order->vendor_id, $_currencies)) {
					$_vendorData = Vendor::getVendorFields($order->vendor_id, array('vendor_currency_display_style'));
					if (!empty($_vendorData)) {
						$_currencyDisplayStyle = Vendor::get_currency_display_style($order->vendor_id
							, $_vendorData->vendor_currency_display_style);
						$_currencies['v'.$order->vendor_id] = new CurrencyDisplay($_currencyDisplayStyle['id'], $_currencyDisplayStyle['symbol']
							, $_currencyDisplayStyle['nbdecimal'], $_currencyDisplayStyle['sdecimal']
							, $_currencyDisplayStyle['thousands'], $_currencyDisplayStyle['positive']
							, $_currencyDisplayStyle['negative']
						);
					} else {
						$_currencies['v'.$order->vendor_id] = new CurrencyDisplay();
					}
				}
				$order->order_total = $_currencies['v'.$order->vendor_id]->getFullValue($order->order_total);
			}

			/* Get the pagination */
			$pagination = $this->get('Pagination');
			$lists['filter_order'] = $mainframe->getUserStateFromRequest($option.'filter_order', 'filter_order', '', 'cmd');
			$lists['filter_order_Dir'] = $mainframe->getUserStateFromRequest($option.'filter_order_Dir', 'filter_order_Dir', '', 'word');

			/* Toolbar */
			JToolBarHelper::title(JText::_( 'VM_ORDER_LIST_LBL' ), 'vm_orders_48');
			/*
			 * UpdateStatus removed from the toolbar; don't understand how this was intented to work but
			 * the order ID's aren't properly passed. Might be readded later; the controller needs to handle
			 * the arguments.
			 */
			JToolBarHelper::save('editOrderStatus', JText::_('VM_UPDATE_STATUS'));
			JToolBarHelper::deleteListX();

			/* Assign the data */
			$this->assignRef('orderslist', $orderslist);
			$this->assignRef('pagination',	$pagination);
			$this->assignRef('lists',	$lists);
		}

		/* Assign general statuses */


		parent::display($tpl);
	}

}

