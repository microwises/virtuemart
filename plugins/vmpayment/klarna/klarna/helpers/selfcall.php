<?php

defined('_JEXEC') or die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');

/**
 *
 * SelfCall to plugins(ajax)
 * @author Valérie Isaksen
 * @version $Id:
 * @package VirtueMart
 * @subpackage payment
 * @copyright Copyright (C) 2004-2008 soeren - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */
class KlarnaSelfCall {
    /*
     * Ajax call to get Pclasses
     * and create table if not exist
     * only called from BE when adding a new country/code ...
     * Click on update/Fetch PClasses
     * @author Patrick Kohl
     *
     */

    function getPclasses() {
	jimport('phpxmlrpc.xmlrpc');
	$jlang = JFactory::getLanguage();
	$jlang->load('plg_vmpayment_klarna', JPATH_ADMINISTRATOR, 'en-GB', true);
	$jlang->load('plg_vmpayment_klarna', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
	$jlang->load('plg_vmpayment_klarna', JPATH_ADMINISTRATOR, null, true);
	$handler = new KlarnaHandler();
	// call klarna server for pClasses
	//$methodid = jrequest::getInt('methodid');
	if (!class_exists('VmModel'))
	    require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'vmmodel.php');
	$model = VmModel::getModel('paymentmethod');
	$payment = $model->getPayment();
	if (!class_exists('vmParameters'))
	    require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'parameterparser.php');
	$parameters = new vmParameters($payment, $payment->payment_element, 'plugin', 'vmpayment');
	$data = $parameters->getParamByName('data');
	// echo "<pre>";print_r($data);
	$json = $handler->fetchPClasses($data);
	ob_start();
	require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'pclasses_html.php');
	$json['pclasses'] = ob_get_clean();
	$document = JFactory::getDocument();
	$document->setMimeEncoding('application/json');
	//echo json_encode($json, true);
	echo json_encode($json);
	jexit();
	// echo result with tmpl ?
    }

    /*
     * @author Valérie Isaksen
     *
     */

    function checkOrderStatus() {
	if (!class_exists('VirtueMartModelOrders'))
	    require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );

	$payment_methodid = JRequest::getInt('payment_methodid');
	$invNo = JRequest::getInt('invNo');
	$country = JRequest::getInt('country');
	$orderNumber = JRequest::getString('order_number');
	$orderPass = JRequest::getString('order_pass');

	if (!($method = $this->getVmPluginMethod($payment_methodid))) {
	    return null; // Another method was selected, do nothing
	}

	$modelOrder = VmModel::getModel('orders');
	// If the user is not logged in, we will check the order number and order pass
	$orderId = $modelOrder->getOrderIdByOrderPass($orderNumber, $orderPass);
	if (empty($orderId)) {
	    echo 'Invalid order_number/password ' . JText::_('COM_VIRTUEMART_RESTRICTED_ACCESS');
	    return 0;
	}
	//$orderDetails = $modelOrder->getOrder($orderId);
	$klarna_order_status = KlarnaHandler::checkOrderStatus($payment_methodid, $invNo, $country);
	if ($klarna_order_status == KlarnaFlags::ACCEPTED) {
	    /* if Klarna's order status is pending: add it in the history */
	    /* The order is under manual review and will be accepted or denied at a later stage.
	      Use cronjob with checkOrderStatus() or visit Klarna Online to check to see if the status has changed.
	      You should still show it to the customer as it was accepted, to avoid further attempts to fraud. */
	    $order['order_status'] = $method->status_success;
	} else {
	    $order['order_status'] = $method->status_canceled;
	}
	$order['customer_notified'] = 0;
	$order['comments'] = $log;
	$modelOrder->updateStatusForOneOrder($order['details']['BT']->virtuemart_order_id, $order, true);
	//jexit();
	// echo result with tmpl ?
    }

}

