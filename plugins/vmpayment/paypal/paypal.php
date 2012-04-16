<?php

defined('_JEXEC') or die('Restricted access');

/**
 *
 * a special type of 'paypal ':
 * @author Max Milbers
 * @author Valérie Isaksen
 * @version $Id: paypal.php 5177 2011-12-28 18:44:10Z alatak $
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
 * http://virtuemart.org
 */
if (!class_exists('vmPSPlugin'))
    require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');

class plgVmPaymentPaypal extends vmPSPlugin {

    // instance of class
    public static $_this = false;

    function __construct(& $subject, $config) {
	//if (self::$_this)
	//   return self::$_this;
	parent::__construct($subject, $config);

	$this->_loggable = true;
	$this->tableFields = array_keys($this->getTableSQLFields());
	$this->_tablepkey = 'id'; //virtuemart_paypal_id';
	$this->_tableId = 'id'; //'virtuemart_paypal_id';
	$varsToPush = array('paypal_merchant_email' => array('', 'char'),
	    'paypal_verified_only' => array('', 'int'),
	    'payment_currency' => array('', 'int'),
	    'sandbox' => array(0, 'int'),
	    'sandbox_merchant_email' => array('', 'char'),
	    'payment_logos' => array('', 'char'),
	    'debug' => array(0, 'int'),
	    'status_pending' => array('', 'char'),
	    'status_success' => array('', 'char'),
	    'status_canceled' => array('', 'char'),
	    'countries' => array('', 'char'),
	    'min_amount' => array('', 'int'),
	    'max_amount' => array('', 'int'),
	    'secure_post' => array('', 'int'),
	    'ipn_test' => array('', 'int'),
	    'no_shipping' => array('', 'int'),
	    'address_override' => array('', 'int'),
	    'cost_per_transaction' => array('', 'int'),
	    'cost_percent_total' => array('', 'int'),
	    'tax_id' => array(0, 'int')
	);

	$this->setConfigParameterable($this->_configTableFieldName, $varsToPush);

	//self::$_this = $this;
    }

    public function getVmPluginCreateTableSQL() {

	return $this->createTableSQL('Payment Paypal Table');
    }

    function getTableSQLFields() {

	$SQLfields = array(
	    'id' => 'int(11) UNSIGNED NOT NULL AUTO_INCREMENT',
	    'virtuemart_order_id' => 'int(1) UNSIGNED',
	    'order_number' => ' char(64)',
	    'virtuemart_paymentmethod_id' => 'mediumint(1) UNSIGNED',
	    'payment_name' => 'varchar(5000)',
	    'payment_order_total' => 'decimal(15,5) NOT NULL DEFAULT \'0.00000\'',
	    'payment_currency' => 'char(3) ',
	    'cost_per_transaction' => 'decimal(10,2)',
	    'cost_percent_total' => 'decimal(10,2)',
	    'tax_id' => ' smallint(1)',
	    'paypal_custom' => ' varchar(255)',
	    'paypal_response_mc_gross' => 'decimal(10,2)',
	    'paypal_response_mc_currency' => 'char(10)',
	    'paypal_response_invoice' => 'char(32)',
	    'paypal_response_protection_eligibility' => 'char(128)',
	    'paypal_response_payer_id' => 'char(13)',
	    'paypal_response_tax' => 'decimal(10,2)',
	    'paypal_response_payment_date' => 'char(28)',
	    'paypal_response_payment_status' => 'char(50)',
	    'paypal_response_pending_reason' => 'char(50)',
	    'paypal_response_mc_fee' => 'decimal(10,2) ',
	    'paypal_response_payer_email' => 'char(128)',
	    'paypal_response_last_name' => 'char(64)',
	    'paypal_response_first_name' => 'char(64)',
	    'paypal_response_business' => 'char(128)',
	    'paypal_response_receiver_email' => 'char(128)',
	    'paypal_response_transaction_subject' => 'char(128)',
	    'paypal_response_residence_country' => 'char(2)',
	    'paypalresponse_raw' => 'varchar(512)'
	);
	return $SQLfields;
    }

    function plgVmConfirmedOrder($cart, $order) {

	if (!($method = $this->getVmPluginMethod($order['details']['BT']->virtuemart_paymentmethod_id))) {
	    return null; // Another method was selected, do nothing
	}
	if (!$this->selectedThisElement($method->payment_element)) {
	    return false;
	}
	$session = JFactory::getSession();
	$return_context = $session->getId();
	$this->_debug = $method->debug;
	$this->logInfo('plgVmConfirmedOrder order number: ' . $order['details']['BT']->order_number, 'message');

	if (!class_exists('VirtueMartModelOrders'))
	    require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );
	if (!class_exists('VirtueMartModelCurrency'))
	    require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'currency.php');

	//$usr = JFactory::getUser();
	$new_status = '';

	$usrBT = $order['details']['BT'];
	$address = ((isset($order['details']['ST'])) ? $order['details']['ST'] : $order['details']['BT']);


	if (!class_exists('TableVendors'))
	    require(JPATH_VM_ADMINISTRATOR . DS . 'table' . DS . 'vendors.php');
	$vendorModel = VmModel::getModel('Vendor');
	$vendorModel->setId(1);
	$vendor = $vendorModel->getVendor();
	$vendorModel->addImages($vendor, 1);
	$this->getPaymentCurrency($method);
	$q = 'SELECT `currency_code_3` FROM `#__virtuemart_currencies` WHERE `virtuemart_currency_id`="' . $method->payment_currency . '" ';
	$db = JFactory::getDBO();
	$db->setQuery($q);
	$currency_code_3 = $db->loadResult();

	$paymentCurrency = CurrencyDisplay::getInstance($method->payment_currency);
	$totalInPaymentCurrency = round($paymentCurrency->convertCurrencyTo($method->payment_currency, $order['details']['BT']->order_total, false), 2);
	$cd = CurrencyDisplay::getInstance($cart->pricesCurrency);
if ($totalInPaymentCurrency <= 0) {
     vmInfo(JText::_('VMPAYMENT_PAYPAL_PAYMENT_AMOUNT_INCORRECT'));
	    return false;
}
	$merchant_email = $this->_getMerchantEmail($method);
	if (empty($merchant_email)) {
	    vmInfo(JText::_('VMPAYMENT_PAYPAL_MERCHANT_EMAIL_NOT_SET'));
	    return false;
	}

	$testReq = $method->debug == 1 ? 'YES' : 'NO';
	$post_variables = Array(
	    'cmd' => '_ext-enter',
	    'redirect_cmd' => '_xclick',
	    'upload' => '1', //Indicates the use of third-party shopping cart
	    'business' => $merchant_email, //Email address or account ID of the payment recipient (i.e., the merchant).
	    'receiver_email' => $merchant_email, //Primary email address of the payment recipient (i.e., the merchant
	    'order_number' => $order['details']['BT']->order_number,
	    "invoice" => $order['details']['BT']->order_number,
	    'custom' => $return_context,
	    'item_name' => JText::_('VMPAYMENT_PAYPAL_ORDER_NUMBER') . ': ' . $order['details']['BT']->order_number,
	    "amount" => $totalInPaymentCurrency,
	    "currency_code" => $currency_code_3,
	    /*
	     * 1 – L'adresse spécifiée dans les variables pré-remplies remplace l'adresse de livraison enregistrée auprès de PayPal.
	     * Le payeur voit l'adresse qui est transmise mais ne peut pas la modifier.
	     * Aucune adresse n'est affichée si l'adresse n'est pas valable
	     * (par exemple si des champs requis, tel que le pays, sont manquants) ou pas incluse.
	     * Valeurs autorisées : 0, 1. Valeur par défaut : 0
	     */
	    "address_override" => isset($method->address_override) ? $method->address_override : 0, // 0 ??   Paypal does not allow your country of residence to ship to the country you wish to
	    "first_name" => $address->first_name,
	    "last_name" => $address->last_name,
	    "address1" => $address->address_1,
	    "address2" => isset($address->address_2) ? $address->address_2 : '',
	    "zip" => $address->zip,
	    "city" => $address->city,
	    "state" => isset($address->virtuemart_state_id) ? ShopFunctions::getStateByID($address->virtuemart_state_id) : '',
	    "country" => ShopFunctions::getCountryByID($address->virtuemart_country_id, 'country_2_code'),
	    "email" => $order['details']['BT']->email,
	    "night_phone_b" => $address->phone_1,
	    "return" => JROUTE::_(JURI::root() . 'index.php?option=com_virtuemart&view=pluginresponse&task=pluginresponsereceived&on=' . $order['details']['BT']->order_number . '&pm=' . $order['details']['BT']->virtuemart_paymentmethod_id),
	    //"return" => JROUTE::_(JURI::root() . 'index.php?option=com_virtuemart&view=pluginresponse&task=pluginnotification&tmpl=component'),
	    "notify_url" => JROUTE::_(JURI::root() . 'index.php?option=com_virtuemart&view=pluginresponse&task=pluginnotification&tmpl=component'),
	    "cancel_return" => JROUTE::_(JURI::root() . 'index.php?option=com_virtuemart&view=pluginresponse&task=pluginUserPaymentCancel&on=' . $order['details']['BT']->order_number . '&pm=' . $order['details']['BT']->virtuemart_paymentmethod_id),
	    //"undefined_quantity" => "0",
	    "ipn_test" => $method->debug,
	    "rm" => '2', // the buyer’s browser is redirected to the return URL by using the POST method, and all payment variables are included
	    //"pal" => "NRUBJXESJTY24",
	    "image_url" => JURI::root() . $vendor->images[0]->file_url,
	    "no_shipping" => isset($method->no_shipping) ? $method->no_shipping : 0,
	    "no_note" => "1");

	/*
	  $i = 1;
	  foreach ($cart->products as $key => $product) {
	  $post_variables["item_name_" . $i] = substr(strip_tags($product->product_name), 0, 127);
	  $post_variables["item_number_" . $i] = $i;
	  $post_variables["amount_" . $i] = $cart->pricesUnformatted[$key]['salesPrice'];
	  $post_variables["quantity_" . $i] = $product->quantity;
	  $i++;
	  }
	  if ($cart->pricesUnformatted ['shipmentValue']) {
	  $post_variables["item_name_" . $i] = JText::_('VMPAYMENT_PAYPAL_SHIPMENT_PRICE');
	  $post_variables["item_number_" . $i] = $i;
	  $post_variables["amount_" . $i] = $cart->pricesUnformatted ['shipmentValue'];
	  $post_variables["quantity_" . $i] = 1;
	  $i++;
	  }
	  if ($cart->pricesUnformatted ['paymentValue']) {
	  $post_variables["item_name_" . $i] = JText::_('VMPAYMENT_PAYPAL_PAYMENT_PRICE');
	  $post_variables["item_number_" . $i] = $i;
	  $post_variables["amount_" . $i] = $cart->pricesUnformatted ['paymentValue'];
	  $post_variables["quantity_" . $i] = 1;
	  $i++;
	  }
	  if (!empty($order->cart->coupon)) {
	  $post_variables["discount_amount_cart"] = $cart->pricesUnformatted['discountAmount'];
	  }
	 */



	// Prepare data that should be stored in the database
	$dbValues['order_number'] = $order['details']['BT']->order_number;
	$dbValues['payment_name'] = $this->renderPluginName($method, $order);
	$dbValues['virtuemart_paymentmethod_id'] = $cart->virtuemart_paymentmethod_id;
	$dbValues['paypal_custom'] = $return_context;
	$dbValues['cost_per_transaction'] = $method->cost_per_transaction;
	$dbValues['cost_percent_total'] = $method->cost_percent_total;
	$dbValues['payment_currency'] = $method->payment_currency;
	$dbValues['payment_order_total'] = $totalInPaymentCurrency;
	$dbValues['tax_id'] = $method->tax_id;
	$this->storePSPluginInternalData($dbValues);

	$url = $this->_getPaypalUrlHttps($method);

	// add spin image
	$html = '<html><head><title>Redirection</title></head><body><div style="margin: auto; text-align: center;">';
	$html .= '<form action="' . "https://" . $url . '" method="post" name="vm_paypal_form" >';
	$html.= '<input type="submit"  value="' . JText::_('VMPAYMENT_PAYPAL_REDIRECT_MESSAGE') . '" />';
	foreach ($post_variables as $name => $value) {
	    $html.= '<input type="hidden" name="' . $name . '" value="' . htmlspecialchars($value) . '" />';
	}
	$html.= '</form></div>';
	$html.= ' <script type="text/javascript">';
	$html.= ' document.vm_paypal_form.submit();';
	$html.= ' </script></body></html>';

	// 	2 = don't delete the cart, don't send email and don't redirect
	$cart->_confirmDone = false;
	$cart->_dataValidated = false;
	$cart->setCartIntoSession();
	JRequest::setVar('html', $html);




	/*

	  $qstring = '?';
	  foreach ($post_variables AS $k => $v) {
	  $qstring .= ( empty($qstring) ? '' : '&')
	  . urlencode($k) . '=' . urlencode($v);
	  }
	  // we can display the logo, or do the redirect
	  $mainframe = JFactory::getApplication();
	  $mainframe->redirect("https://" . $url . $qstring);


	  return false; // don't delete the cart, don't send email
	 */
    }

    function plgVmgetPaymentCurrency($virtuemart_paymentmethod_id, &$paymentCurrencyId) {

	if (!($method = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
	    return null; // Another method was selected, do nothing
	}
	if (!$this->selectedThisElement($method->payment_element)) {
	    return false;
	}
	$this->getPaymentCurrency($method);
	$paymentCurrencyId = $method->payment_currency;
    }

    function plgVmOnPaymentResponseReceived(&$html) {
	if (!class_exists('VirtueMartCart'))
	    require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
	if (!class_exists('shopFunctionsF'))
	    require(JPATH_VM_SITE . DS . 'helpers' . DS . 'shopfunctionsf.php');
	if (!class_exists('VirtueMartModelOrders'))
	    require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );
	$paypal_data = JRequest::get('post');
	vmdebug('PAYPAL plgVmOnPaymentResponseReceived', $paypal_data);
	// the payment itself should send the parameter needed.
	$virtuemart_paymentmethod_id = JRequest::getInt('pm', 0);
	$order_number = JRequest::getString('on', 0);
	$vendorId = 0;
	if (!($method = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
	    return null; // Another method was selected, do nothing
	}
	if (!$this->selectedThisElement($method->payment_element)) {
	    return null;
	}

	if (!($virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($order_number) )) {
	    return null;
	}
	if (!($paymentTable = $this->getDataByOrderId($virtuemart_order_id) )) {
	    // JError::raiseWarning(500, $db->getErrorMsg());
	    return '';
	}
	$payment_name = $this->renderPluginName($method);
	$html = $this->_getPaymentResponseHtml($paymentTable, $payment_name);

	//We delete the old stuff
	// get the correct cart / session
	$cart = VirtueMartCart::getCart();
	$cart->emptyCart();
	return true;
    }

    function plgVmOnUserPaymentCancel() {

	if (!class_exists('VirtueMartModelOrders'))
	    require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );

	$order_number = JRequest::getString('on', '');
	$virtuemart_paymentmethod_id = JRequest::getInt('pm', '');
	if (empty($order_number) or empty($virtuemart_paymentmethod_id) or !$this->selectedThisByMethodId($virtuemart_paymentmethod_id)) {
	    return null;
	}
	if (!($virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($order_number))) {
		return null;
	}
	if (!($paymentTable = $this->getDataByOrderId($virtuemart_order_id))) {
	    return null;
	}

	VmInfo(Jtext::_('VMPAYMENT_PAYPAL_PAYMENT_CANCELLED'));
	$session = JFactory::getSession();
	$return_context = $session->getId();
	if (strcmp($paymentTable->paypal_custom, $return_context) === 0) {
	    $this->handlePaymentUserCancel($virtuemart_order_id);
	}
	return true;
    }

    /*
     *   plgVmOnPaymentNotification() - This event is fired by Offline Payment. It can be used to validate the payment data as entered by the user.
     * Return:
     * Parameters:
     *  None
     *  @author Valerie Isaksen
     */

    function plgVmOnPaymentNotification() {

	if (!class_exists('VirtueMartModelOrders'))
	    require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );
	$paypal_data = JRequest::get('post');
	if (!isset($paypal_data['invoice'])) {
	    return;
	}
	$order_number = $paypal_data['invoice'];
	if (!($virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($paypal_data['invoice']))) {
	    return;
	}

	$vendorId = 0;
	if (!($payment = $this->getDataByOrderId($virtuemart_order_id))) {
	    return;
	}

	$method = $this->getVmPluginMethod($payment->virtuemart_paymentmethod_id);
	if (!$this->selectedThisElement($method->payment_element)) {
	    return false;
	}

	$this->_debug = $method->debug;
	if (!$payment) {
	    $this->logInfo('getDataByOrderId payment not found: exit ', 'ERROR');
	    return null;
	}
	$this->logInfo('paypal_data ' . implode('   ', $paypal_data), 'message');

	$this->_storePaypalInternalData($method, $paypal_data, $virtuemart_order_id);
	$modelOrder = VmModel::getModel('orders');
	$order = array();
	$error_msg = $this->_processIPN($paypal_data, $method, $virtuemart_order_id);
	$this->logInfo('process IPN ' . $error_msg, 'message');

	if (!(empty($error_msg) )) {
	    $order['customer_notified'] = 0;
	    $order['order_status'] = $method->status_canceled;
	    $order['comments'] = 'process IPN ' . $error_msg;
	    $modelOrder->updateStatusForOneOrder($virtuemart_order_id, $order, true);
	    $this->logInfo('process IPN ' . $error_msg . ' ' . $new_status, 'ERROR');
	} else {
	    $this->logInfo('process IPN OK', 'message');
	}
	/*
	 * https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_html_IPNandPDTVariables
	 * The status of the payment:
	 * Canceled_Reversal: A reversal has been canceled. For example, you won a dispute with the customer, and the funds for the transaction that was reversed have been returned to you.
	 * Completed: The payment has been completed, and the funds have been added successfully to your account balance.
	 * Created: A German ELV payment is made using Express Checkout.
	 * Denied: You denied the payment. This happens only if the payment was previously pending because of possible reasons described for the pending_reason variable or the Fraud_Management_Filters_x variable.
	 * Expired: This authorization has expired and cannot be captured.
	 * Failed: The payment has failed. This happens only if the payment was made from your customer’s bank account.
	 * Pending: The payment is pending. See pending_reason for more information.
	 * Refunded: You refunded the payment.
	 * Reversed: A payment was reversed due to a chargeback or other type of reversal. The funds have been removed from your account balance and returned to the buyer. The reason for the reversal is specified in the ReasonCode element.
	 * Processed: A payment has been accepted.
	 * Voided: This authorization has been voided.
	 *
	 */
	if (empty($paypal_data['payment_status']) || ($paypal_data['payment_status'] != 'Completed' && $paypal_data['payment_status'] != 'Pending')) {
	    //return false;
	}
	$lang = JFactory::getLanguage();
	$order['customer_notified'] = 1;

	if (strcmp($paypal_data['payment_status'], 'Completed') == 0) {
	    $order['order_status'] = $method->status_success;
	    $order['comments'] = JText::sprintf('VMPAYMENT_PAYPAL_PAYMENT_STATUS_CONFIRMED', $order_number);
	} elseif (strcmp($paypal_data['payment_status'], 'Pending') == 0) {
	    $key = 'VMPAYMENT_PAYPAL_PENDING_REASON_FE_' . strtoupper($paypal_data['pending_reason']);
	    if (!$lang->hasKey($key)) {
		$key = 'VMPAYMENT_PAYPAL_PENDING_REASON_FE_DEFAULT';
	    }
	    $order['comments'] = JText::sprintf('VMPAYMENT_PAYPAL_PAYMENT_STATUS_PENDING', $order_number) . JText::_($key);
	    $order['order_status'] = $method->status_pending;
	} else {
	    $order['order_status'] = $method->status_canceled;
	}

	$this->logInfo('plgVmOnPaymentNotification return new_status:' . $order['order_status'], 'message');

	$modelOrder->updateStatusForOneOrder($virtuemart_order_id, $order, true);

	//// remove vmcart
	$this->emptyCart($return_context);
	//die();
    }

    function _storePaypalInternalData($method, $paypal_data, $virtuemart_order_id) {

	// get all know columns of the table
	$db = JFactory::getDBO();
	$query = 'SHOW COLUMNS FROM `' . $this->_tablename . '` ';
	$db->setQuery($query);
	$columns = $db->loadResultArray(0);
	$post_msg = '';
	foreach ($paypal_data as $key => $value) {
	    $post_msg .= $key . "=" . $value . "<br />";
	    $table_key = 'paypal_response_' . $key;
	    if (in_array($table_key, $columns)) {
		$response_fields[$table_key] = $value;
	    }
	}

	//$response_fields[$this->_tablepkey] = $this->_getTablepkeyValue($virtuemart_order_id);
	$response_fields['payment_name'] = $this->renderPluginName($method);
	$response_fields['paypalresponse_raw'] = $post_msg;
	$return_context = $paypal_data['custom'];
	$response_fields['order_number'] = $paypal_data['invoice'];
	$response_fields['virtuemart_order_id'] = $virtuemart_order_id;
	//$preload=true   preload the data here too preserve not updated data
	$this->storePSPluginInternalData($response_fields, 'virtuemart_order_id', true);
    }

    function _getTablepkeyValue($virtuemart_order_id) {
	$db = JFactory::getDBO();
	$q = 'SELECT ' . $this->_tablepkey . ' FROM `' . $this->_tablename . '` '
		. 'WHERE `virtuemart_order_id` = ' . $virtuemart_order_id;
	$db->setQuery($q);

	if (!($pkey = $db->loadResult())) {
	    JError::raiseWarning(500, $db->getErrorMsg());
	    return '';
	}
	return $pkey;
    }

    /**
     * Display stored payment data for an order
     * @see components/com_virtuemart/helpers/vmPSPlugin::plgVmOnShowOrderBEPayment()
     */
    function plgVmOnShowOrderBEPayment($virtuemart_order_id, $payment_method_id) {

	if (!$this->selectedThisByMethodId($payment_method_id)) {
	    return null; // Another method was selected, do nothing
	}


	if (!($paymentTable = $this->_getPaypalInternalData($virtuemart_order_id) )) {
	    // JError::raiseWarning(500, $db->getErrorMsg());
	    return '';
	}
	$this->getPaymentCurrency($paymentTable);
	$q = 'SELECT `currency_code_3` FROM `#__virtuemart_currencies` WHERE `virtuemart_currency_id`="' . $paymentTable->payment_currency . '" ';
	$db = JFactory::getDBO();
	$db->setQuery($q);
	$currency_code_3 = $db->loadResult();
	$html = '<table class="adminlist">' . "\n";
	$html .=$this->getHtmlHeaderBE();
	$html .= $this->getHtmlRowBE('PAYPAL_PAYMENT_NAME', $paymentTable->payment_name);
	//$html .= $this->getHtmlRowBE('PAYPAL_PAYMENT_TOTAL_CURRENCY', $paymentTable->payment_order_total.' '.$currency_code_3);
	$code = "paypal_response_";
	foreach ($paymentTable as $key => $value) {
	    if (substr($key, 0, strlen($code)) == $code) {
		$html .= $this->getHtmlRowBE($key, $value);
	    }
	}
	$html .= '</table>' . "\n";
	return $html;
    }

    function _getPaypalInternalData($virtuemart_order_id, $order_number = '') {
	$db = JFactory::getDBO();
	$q = 'SELECT * FROM `' . $this->_tablename . '` WHERE ';
	if ($order_number) {
	    $q .= " `order_number` = '" . $order_number . "'";
	} else {
	    $q .= ' `virtuemart_order_id` = ' . $virtuemart_order_id;
	}

	$db->setQuery($q);
	if (!($paymentTable = $db->loadObject())) {
	    // JError::raiseWarning(500, $db->getErrorMsg());
	    return '';
	}
	return $paymentTable;
    }

    /**
     * Get ipn data, send verification to PayPal, run corresponding handler
     *
     * @param array $data
     * @return string Empty string if data is valid and an error message otherwise
     * @access protected
     */
    function _processIPN($paypal_data, $method) {
	$secure_post = $method->secure_post;
	$paypal_url = $this->_getPaypalURL($method);
	// read the post from PayPal system and add 'cmd'
	$post_msg = 'cmd=_notify-validate';
	foreach ($paypal_data as $key => $value) {
	    if ($key != 'view' && $key != 'layout') {
		$value = urlencode($value);
		$post_msg .= "&$key=$value";
	    }
	}

	$this->checkPaypalIps($paypal_data['ipn_test']);

	// post back to PayPal system to validate
	$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
	$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$header .= "Content-Length: " . strlen($post_msg) . "\r\n\r\n";

	if ($secure_post) {
	    // If possible, securely post back to paypal using HTTPS
	    // Your PHP server will need to be SSL enabled
	    $fps = fsockopen('ssl://' . $paypal_url, 443, $errno, $errstr, 30);
	} else {
	    $fps = fsockopen($paypal_url, 80, $errno, $errstr, 30);
	}

	if (!$fps) {
	    $this->sendEmailToVendorAndAdmins("error with paypal", JText::sprintf('VMPAYMENT_PAYPAL_ERROR_POSTING_IPN', $errstr, $errno));
	    return JText::sprintf('VMPAYMENT_PAYPAL_ERROR_POSTING_IPN', $errstr, $errno); // send email
	} else {
	    fputs($fps, $header . $post_msg);
	    while (!feof($fps)) {
		$res = fgets($fps, 1024);

		if (strcmp($res, 'VERIFIED') == 0) {
		    return '';
		} elseif (strcmp($res, 'INVALID') == 0) {
		    $this->sendEmailToVendorAndAdmins("error with paypal IPN NOTIFICATION", JText::_('VMPAYMENT_PAYPAL_ERROR_IPN_VALIDATION') . $res);
		    return JText::_('VMPAYMENT_PAYPAL_ERROR_IPN_VALIDATION') . $res;
		}
	    }
	}

	fclose($fps);
	return '';
    }

    function _getMerchantEmail($method) {
	return $method->sandbox ? $method->sandbox_merchant_email : $method->paypal_merchant_email;
    }

    function _getPaypalUrl($method) {

	$url = $method->sandbox ? 'www.sandbox.paypal.com' : 'www.paypal.com';

	return $url;
    }

    function _getPaypalUrlHttps($method) {
	$url = $this->_getPaypalUrl($method);
	$url = $url . '/cgi-bin/webscr';

	return $url;
    }

    /*
     * CheckPaypalIPs
     * Cannot be checked with Sandbox
     * From VM1.1
     */

    function checkPaypalIps($test_ipn) {
	return;
	// Get the list of IP addresses for www.paypal.com and notify.paypal.com
	$paypal_iplist = array();
	$paypal_iplist = gethostbynamel('www.paypal.com');
	$paypal_iplist2 = array();
	$paypal_iplist2 = gethostbynamel('notify.paypal.com');
	$paypal_iplist3 = array();
	$paypal_iplist3 = array('216.113.188.202', '216.113.188.203', '216.113.188.204', '66.211.170.66');
	$paypal_iplist = array_merge($paypal_iplist, $paypal_iplist2, $paypal_iplist3);

	$paypal_sandbox_hostname = 'ipn.sandbox.paypal.com';
	$remote_hostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);

	$valid_ip = false;

	if ($paypal_sandbox_hostname == $remote_hostname) {
	    $valid_ip = true;
	    $hostname = 'www.sandbox.paypal.com';
	} else {
	    $ips = "";
	    // Loop through all allowed IPs and test if the remote IP connected here
	    // is a valid IP address
	    if (in_array($_SERVER['REMOTE_ADDR'], $paypal_iplist)) {
		$valid_ip = true;
	    }
	    $hostname = 'www.paypal.com';
	}

	if (!$valid_ip) {


	    $mailsubject = "PayPal IPN Transaction on your site: Possible fraud";
	    $mailbody = "Error code 506. Possible fraud. Error with REMOTE IP ADDRESS = " . $_SERVER['REMOTE_ADDR'] . ".
                        The remote address of the script posting to this notify script does not match a valid PayPal ip address\n
            These are the valid IP Addresses: $ips

            The Order ID received was: $invoice";
	    $this->sendEmailToVendorAndAdmins($mailsubject, $mailbody);


	    exit();
	}

	if (!($hostname == "www.sandbox.paypal.com" && $test_ipn == 1 )) {
	    $res = "FAILED";
	    $mailsubject = "PayPal Sandbox Transaction";
	    $mailbody = "Hello,
		A fatal error occured while processing a paypal transaction.
		----------------------------------
		Hostname: $hostname
		URI: $uri
		A Paypal transaction was made using the sandbox without your site in Paypal-Debug-Mode";
	    //vmMail($mosConfig_mailfrom, $mosConfig_fromname, $debug_email_address, $mailsubject, $mailbody );
	    $this->sendEmailToVendorAndAdmins($mailsubject, $mailbody);
	}
    }

    function _getPaymentResponseHtml($paypalTable, $payment_name) {

	$html = '<table>' . "\n";
	$html .= $this->getHtmlRow('PAYPAL_PAYMENT_NAME', $payment_name);
	if (!empty($paypalTable)) {
	    $html .= $this->getHtmlRow('PAYPAL_ORDER_NUMBER', $paypalTable->order_number);
	    //$html .= $this->getHtmlRow('PAYPAL_AMOUNT', $paypalTable->payment_order_total. " " . $paypalTable->payment_currency);
	}
	$html .= '</table>' . "\n";

	return $html;
    }

    function getCosts(VirtueMartCart $cart, $method, $cart_prices) {
	if (preg_match('/%$/', $method->cost_percent_total)) {
	    $cost_percent_total = substr($method->cost_percent_total, 0, -1);
	} else {
	    $cost_percent_total = $method->cost_percent_total;
	}
	return ($method->cost_per_transaction + ($cart_prices['salesPrice'] * $cost_percent_total * 0.01));
    }

    /**
     * Check if the payment conditions are fulfilled for this payment method
     * @author: Valerie Isaksen
     *
     * @param $cart_prices: cart prices
     * @param $payment
     * @return true: if the conditions are fulfilled, false otherwise
     *
     */
    protected function checkConditions($cart, $method, $cart_prices) {
	$this->convert($method);

	$address = (($cart->ST == 0) ? $cart->BT : $cart->ST);

	$amount = $cart_prices['salesPrice'];
	$amount_cond = ($amount >= $method->min_amount AND $amount <= $method->max_amount
		OR
		($method->min_amount <= $amount AND ($method->max_amount == 0) ));

	$countries = array();
	if (!empty($method->countries)) {
	    if (!is_array($method->countries)) {
		$countries[0] = $method->countries;
	    } else {
		$countries = $method->countries;
	    }
	}
	// probably did not gave his BT:ST address
	if (!is_array($address)) {
	    $address = array();
	    $address['virtuemart_country_id'] = 0;
	}

	if (!isset($address['virtuemart_country_id']))
	    $address['virtuemart_country_id'] = 0;
	if (in_array($address['virtuemart_country_id'], $countries) || count($countries) == 0) {
	    if ($amount_cond) {
		return true;
	    }
	}

	return false;
    }

    function convert($method) {

	$method->min_amount = (float) $method->min_amount;
	$method->max_amount = (float) $method->max_amount;
    }

    /**
     * We must reimplement this triggers for joomla 1.7
     */

    /**
     * Create the table for this plugin if it does not yet exist.
     * This functions checks if the called plugin is active one.
     * When yes it is calling the standard method to create the tables
     * @author Valérie Isaksen
     *
     */
    function plgVmOnStoreInstallPaymentPluginTable($jplugin_id) {

	return $this->onStoreInstallPluginTable($jplugin_id);
    }

    /**
     * This event is fired after the payment method has been selected. It can be used to store
     * additional payment info in the cart.
     *
     * @author Max Milbers
     * @author Valérie isaksen
     *
     * @param VirtueMartCart $cart: the actual cart
     * @return null if the payment was not selected, true if the data is valid, error message if the data is not vlaid
     *
     */
    public function plgVmOnSelectCheckPayment(VirtueMartCart $cart) {
	return $this->OnSelectCheck($cart);
    }

    /**
     * plgVmDisplayListFEPayment
     * This event is fired to display the pluginmethods in the cart (edit shipment/payment) for exampel
     *
     * @param object $cart Cart object
     * @param integer $selected ID of the method selected
     * @return boolean True on succes, false on failures, null when this plugin was not selected.
     * On errors, JError::raiseWarning (or JError::raiseError) must be used to set a message.
     *
     * @author Valerie Isaksen
     * @author Max Milbers
     */
    public function plgVmDisplayListFEPayment(VirtueMartCart $cart, $selected = 0, &$htmlIn) {
	return $this->displayListFE($cart, $selected, $htmlIn);
    }

    /*
     * plgVmonSelectedCalculatePricePayment
     * Calculate the price (value, tax_id) of the selected method
     * It is called by the calculator
     * This function does NOT to be reimplemented. If not reimplemented, then the default values from this function are taken.
     * @author Valerie Isaksen
     * @cart: VirtueMartCart the current cart
     * @cart_prices: array the new cart prices
     * @return null if the method was not selected, false if the shiiping rate is not valid any more, true otherwise
     *
     *
     */

    public function plgVmonSelectedCalculatePricePayment(VirtueMartCart $cart, array &$cart_prices, &$cart_prices_name) {
	return $this->onSelectedCalculatePrice($cart, $cart_prices, $cart_prices_name);
    }

    /**
     * plgVmOnCheckAutomaticSelectedPayment
     * Checks how many plugins are available. If only one, the user will not have the choice. Enter edit_xxx page
     * The plugin must check first if it is the correct type
     * @author Valerie Isaksen
     * @param VirtueMartCart cart: the cart object
     * @return null if no plugin was found, 0 if more then one plugin was found,  virtuemart_xxx_id if only one plugin is found
     *
     */
    function plgVmOnCheckAutomaticSelectedPayment(VirtueMartCart $cart, array $cart_prices = array(),   &$paymentCounter) {
	return $this->onCheckAutomaticSelected($cart, $cart_prices,  $paymentCounter);
    }

    /**
     * This method is fired when showing the order details in the frontend.
     * It displays the method-specific data.
     *
     * @param integer $order_id The order ID
     * @return mixed Null for methods that aren't active, text (HTML) otherwise
     * @author Max Milbers
     * @author Valerie Isaksen
     */
    public function plgVmOnShowOrderFEPayment($virtuemart_order_id, $virtuemart_paymentmethod_id, &$payment_name) {
	$this->onShowOrderFE($virtuemart_order_id, $virtuemart_paymentmethod_id, $payment_name);
    }

    /**
     * This event is fired during the checkout process. It can be used to validate the
     * method data as entered by the user.
     *
     * @return boolean True when the data was valid, false otherwise. If the plugin is not activated, it should return null.
     * @author Max Milbers

      public function plgVmOnCheckoutCheckDataPayment($psType, VirtueMartCart $cart) {
      return null;
      }
     */

    /**
     * This method is fired when showing when priting an Order
     * It displays the the payment method-specific data.
     *
     * @param integer $_virtuemart_order_id The order ID
     * @param integer $method_id  method used for this order
     * @return mixed Null when for payment methods that were not selected, text (HTML) otherwise
     * @author Valerie Isaksen
     */
    function plgVmonShowOrderPrintPayment($order_number, $method_id) {
	return $this->onShowOrderPrint($order_number, $method_id);
    }

    /**
     * Save updated order data to the method specific table
     *
     * @param array $_formData Form data
     * @return mixed, True on success, false on failures (the rest of the save-process will be
     * skipped!), or null when this method is not actived.
     * @author Oscar van Eijk

      public function plgVmOnUpdateOrderPayment(  $_formData) {
      return null;
      }
     */
    /**
     * Save updated orderline data to the method specific table
     *
     * @param array $_formData Form data
     * @return mixed, True on success, false on failures (the rest of the save-process will be
     * skipped!), or null when this method is not actived.
     * @author Oscar van Eijk

      public function plgVmOnUpdateOrderLine(  $_formData) {
      return null;
      }
     */
    /**
     * plgVmOnEditOrderLineBE
     * This method is fired when editing the order line details in the backend.
     * It can be used to add line specific package codes
     *
     * @param integer $_orderId The order ID
     * @param integer $_lineId
     * @return mixed Null for method that aren't active, text (HTML) otherwise
     * @author Oscar van Eijk

      public function plgVmOnEditOrderLineBE(  $_orderId, $_lineId) {
      return null;
      }
     */

    /**
     * This method is fired when showing the order details in the frontend, for every orderline.
     * It can be used to display line specific package codes, e.g. with a link to external tracking and
     * tracing systems
     *
     * @param integer $_orderId The order ID
     * @param integer $_lineId
     * @return mixed Null for method that aren't active, text (HTML) otherwise
     * @author Oscar van Eijk

      public function plgVmOnShowOrderLineFE(  $_orderId, $_lineId) {
      return null;
      }
     */
    function plgVmDeclarePluginParamsPayment($name, $id, &$data) {
	return $this->declarePluginParams('payment', $name, $id, $data);
    }

    function plgVmSetOnTablePluginParamsPayment($name, $id, &$table) {
	return $this->setOnTablePluginParams($name, $id, $table);
    }

}

// No closing tag
