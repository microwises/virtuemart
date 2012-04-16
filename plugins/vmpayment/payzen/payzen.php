<?php

/*
  #####################################################################################################
  #
  #					Module pour la plateforme de paiement PayZen
  #						Version : 1.2 (révision 33398)
  #									########################
  #					Développé pour VirtueMart
  #						Version : 2.0.0
  #						Compatibilité plateforme : V2
  #									########################
  #					Développé par Lyra Network
  #						http://www.lyra-network.com/
  #						20/02/2012
  #						Contact : support@payzen.eu
  #
  #####################################################################################################
 */
defined('_JEXEC') or die('Restricted access');

if (!class_exists('vmPSPlugin'))
    require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');
if (JVM_VERSION === 2) {
    define('JPATH_VMPAYMENTPLUGIN', JPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'payzen');
} else {
    define('JPATH_VMPAYMENTPLUGIN', JPATH_ROOT . DS . 'plugins' . DS . 'vmpayment');
}

class plgVMPaymentPayzen extends vmPSPlugin {

    // instance of class
    public static $_this = false;

    function __construct(& $subject, $config) {
	//if (self::$_this)
	//   return self::$_this;
	parent::__construct($subject, $config);

	$this->_loggable = true;
	$this->tableFields = array_keys($this->getTableSQLFields());
	$this->_tablepkey = 'id'; //virtuemart_payzen_id';
	$this->_tableId = 'id'; //'virtuemart_payzen_id';
	$varsToPush = $this->getVarsToPush();
	$this->setConfigParameterable($this->_configTableFieldName, $varsToPush);

	//self::$_this = $this;
    }

    protected function getVmPluginCreateTableSQL() {
	return $this->createTableSQL('Payment ' . $this->_name . ' Table');
    }

    function getTableSQLFields() {



	$SQLfields = array(
	    'id' => 'int(11) UNSIGNED NOT NULL AUTO_INCREMENT',
	    'virtuemart_order_id' => 'int(1) UNSIGNED',
	    'order_number' => ' char(64)',
	    'virtuemart_paymentmethod_id' => 'mediumint(1) UNSIGNED',
	    'payment_name' => 'varchar(5000)',
	    'payment_order_total' => 'decimal(15,5) NOT NULL DEFAULT \'0.00000\'',
	    'payment_currency' => 'char(3)',
	    'cost_per_transaction' => 'decimal(10,2)',
	    'cost_percent_total' => 'decimal(10,2)',
	    'tax_id' => 'smallint(1)',
	    'payzen_custom' => 'varchar(255)',
	    'payzen_response_payment_amount' => 'char(15)',
	    'payzen_response_auth_number' => 'char(10)',
	    'payzen_response_payment_currency' => 'char(3)',
	    'payzen_response_auth_number' => 'char(10)',
	    'payzen_response_payment_mean' => 'char(255)',
	    'payzen_response_payment_date' => 'char(20)',
	    'payzen_response_payment_status' => 'char(3)',
	    'payzen_response_payment_message' => 'char(255)',
	    'payzen_response_card_number' => 'char(50)',
	    'payzen_response_trans_id' => 'char(6)',
	    'payzen_response_expiry_month' => 'char(2)',
	    'payzen_response_expiry_year' => 'char(4)',
	);

	return $SQLfields;
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
     * Reimplementation of vmPaymentPlugin::checkPaymentConditions()
     * @param array $cart_prices all cart prices
     * @param object $payment payment parameters object
     * @return bool true if conditions verified
     */
    function checkConditions($cart, $method, $cart_prices) {
	$this->convert($method);
	$amount = $cart_prices['salesPrice'];
	$amount_cond = ($amount >= $method->min_amount && $amount <= $method->max_amount
		|| ($amount >= $method->min_amount && empty($method->max_amount) ) );

	return $amount_cond;
    }

    function convert($method) {

	$method->min_amount = (float) $method->min_amount;
	$method->max_amount = (float) $method->max_amount;
    }

    /**
     * Prepare data and redirect to PayZen payment platform
     * @param string $order_number
     * @param object $orderData
     * @param string $return_context the session id
     * @param string $html the form to display
     * @param bool $new_status false if it should not be changed, otherwise new staus
     * @return NULL
     */
    function plgVmConfirmedOrder($cart, $order) {

	if (!($method = $this->getVmPluginMethod($order['details']['BT']->virtuemart_paymentmethod_id))) {
	    return null; // Another method was selected, do nothing
	}
	if (!$this->selectedThisElement($method->payment_element)) {
	    return false;
	}

	$this->_debug = $method->debug; // enable debug
	$session = JFactory::getSession();
	$return_context = $session->getId();
	$this->logInfo('plgVmOnConfirmedOrderGetPaymentForm -- order number: ' . $order['details']['BT']->order_number, 'message');

	if (!class_exists('VadsApi')) {
	    require(JPATH_VMPAYMENTPLUGIN . DS . 'payzen' . DS . 'payzen_api.php');
	}

	$api = new VadsApi('UTF-8');

	// set config parameters
	$paramNames = array(
	    'platform_url', 'key_test', 'key_prod', 'capture_delay', 'ctx_mode', 'site_id',
	    'validation_mode', 'redirect_enabled', 'redirect_success_timeout', 'redirect_success_message',
	    'redirect_error_timeout', 'redirect_error_message', 'return_mode'
	);
	foreach ($paramNames as $name) {
	    $api->set($name, $method->$name);
	}

	// Set urls
	$uri = & JURI::getInstance($method->url_return);
	//$uri->setVar('pelement', $this->payment_element);
	$uri->setVar('pm', $order['details']['BT']->virtuemart_paymentmethod_id);
	$api->set('url_return', $uri->toString());

	$uri = & JURI::getInstance($method->url_success);
	//$uri->setVar('pelement', $this->payment_element);
	$uri->setVar('pm', $order['details']['BT']->virtuemart_paymentmethod_id);
	$api->set('url_success', $uri->toString());

	$uri = & JURI::getInstance($method->url_cancel);
	$uri->setVar('on', $order['details']['BT']->order_number);
	$uri->setVar('pm', $order['details']['BT']->virtuemart_paymentmethod_id);
	$api->set('url_cancel', $uri->toString());

	// Set the language code
	$lang = JFactory::getLanguage();
	$lang->load('plg_vmpayment_' . $this->_name, JPATH_ADMINISTRATOR);

	$tag = substr($lang->get('tag'), 0, 2);
	$language = in_array($tag, $api->getSupportedLanguages()) ? $tag : ($method->language ? $method->language : 'fr');
	$api->set('language', $language);

	// Set currency
	if (!class_exists('VirtueMartModelCurrency')) {
	    require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'currency.php');
	}
	$currencyModel = new VirtueMartModelCurrency();
	$currencyObj = $currencyModel->getCurrency($order['details']['BT']->order_currency);

	$currency = $api->findCurrencyByNumCode($currencyObj->currency_numeric_code);
	if ($currency == null) {
	    $this->logInfo('plgVmOnConfirmedOrderGetPaymentForm -- Could not find currency numeric code for currency : ' . $currencyObj->currency_numeric_code, 'error');
	    vmInfo(JText::_('VMPAYMENT_'.$this->_name.'_CURRENCY_NOT_SUPPORTED'));
	    return null;
	}
	$api->set('currency', $currency->num);

	// payment_cards may be one value or array
	$cards = $method->payment_cards;
	$cards = !is_array($cards) ? $cards : (in_array("", $cards) ? "" : implode(";", $cards));
	$api->set('payment_cards', $cards);

	// available_languages may be one value or array
	$available_languages = $method->available_languages;
	$available_languages = !is_array($available_languages) ? $available_languages : (in_array("", $available_languages) ? "" : implode(";", $available_languages));
	$api->set('available_languages', $available_languages);

	$api->set('contrib', 'VirtueMart2.0.0_1.2');

	// Set customer info
	// $usr = JFactory::getUser();
	$usrBT = $order['details']['BT'];
	$usrST = ((isset($order['details']['ST'])) ? $order['details']['ST'] : $order['details']['BT']);

	$api->set('cust_email', $usrBT->email);
	// $api->set('cust_id', '');
	$api->set('cust_title', @$usrBT->title);
	$api->set('cust_first_name', $usrBT->first_name);
	$api->set('cust_last_name', $usrBT->last_name);
	$api->set('cust_address', $usrBT->address_1 . ' ' . $usrBT->address_2);
	$api->set('cust_zip', $usrBT->zip);
	$api->set('cust_city', $usrBT->city);
	$api->set('cust_state', @ShopFunctions::getStateByID($usrBT->virtuemart_state_id));
	$api->set('cust_country', @ShopFunctions::getCountryByID($usrBT->virtuemart_country_id, 'country_2_code'));
	$api->set('cust_phone', $usrBT->phone_1);
	$api->set('cust_cell_phone', $usrBT->phone_2);

	$api->set('ship_to_first_name', $usrST->first_name);
	$api->set('ship_to_last_name', $usrST->last_name);
	$api->set('ship_to_city', $usrST->city);
	$api->set('ship_to_street', $usrST->address_1);
	$api->set('ship_to_street2', $usrST->address_2);
	$api->set('ship_to_state', @ShopFunctions::getStateByID($usrST->virtuemart_state_id));
	$api->set('ship_to_country', @ShopFunctions::getCountryByID($usrST->virtuemart_country_id, 'country_2_code'));
	$api->set('ship_to_phone_num', $usrST->phone_1);
	$api->set('ship_to_zip', $usrST->zip);

	// Set order_id
	$api->set('order_id', $order['details']['BT']->order_number);

	// Set the amount to pay
	$api->set('amount', round($order['details']['BT']->order_total * 100));

	// Prepare data that should be stored in the database
	$dbValues['order_number'] = $order['details']['BT']->order_number;
	$dbValues['payment_name'] = $this->renderPluginName($method, $order);
	$dbValues['virtuemart_paymentmethod_id'] = $cart->virtuemart_paymentmethod_id;
	$dbValues[$this->_name . '_custom'] = $return_context;
	$this->storePSPluginInternalData($dbValues);

	$this->logInfo('plgVmOnConfirmedOrderGetPaymentForm -- payment data saved to table ' . $this->_tablename, 'message');

	// echo the redirect form
	$form = '<html><head><title>Redirection</title></head><body><div style="margin: auto; text-align: center;">';
	$form .= '<p>' . JText::_('VMPAYMENT_'.$this->_name.'_PLEASE_WAIT') . '</p>';
	$form .= '<p>' . JText::_('VMPAYMENT_'.$this->_name.'_CLICK_BUTTON_IF_NOT_REDIRECTED') . '</p>';
	$form .= '<form action="' . $api->platformUrl . '" method="POST" name="vm_' . $this->_name . '_form" >';
	$form .= '<input type="image" name="submit" src="' . JURI::base(true) . '/images/stories/virtuemart/payment/' . $this->_name . '.jpg" alt="' . JText::_('VMPAYMENT_'.$this->_name.'_BTN_ALT') . '" title="' . JText::_('VMPAYMENT_PAYZEN_BTN_ALT') . '"/>';
	$form .= $api->getRequestFieldsHtml();
	$form .= '</form></div>';
	$form .= '<script type="text/javascript">document.forms[0].submit();</script></body></html>';

	$this->logInfo('plgVmOnConfirmedOrderGetPaymentForm -- user redirected to '.$this->_name, 'message');

	echo $form;

	$cart->_confirmDone = false;
	$cart->_dataValidated = false;
	$cart->setCartIntoSession();
	die(); // not save order, not send mail, do redirect
    }

    /**
     * Check PayZen response, save order if not done by server call and redirect to response page
     *  when client comes back from payment platform.
     * @param int $virtuemart_order_id virtuemart order primary key concerned by payment
     * @param string $html message to show as result
     * @return
     */
    function plgVmOnPaymentResponseReceived(&$html) {
	// the payment itself should send the parameter needed.
	$virtuemart_paymentmethod_id = JRequest::getInt('pm', 0);

	$vendorId = 0;
	if (!($method = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
	    return null; // Another method was selected, do nothing
	}
	if (!$this->selectedThisElement($method->payment_element)) {
	    return false;
	}

	//$this->_debug = true; // enable debug
	$this->logInfo('plgVmOnPaymentResponseReceived -- user returned back from '.$this->_name, 'message');

	$data = JRequest::get('request');

	// Load API
	if (!class_exists('VadsApi')) {
	    require(JPATH_VMPAYMENTPLUGIN . DS . 'payzen' . DS . 'payzen_api.php');
	}

	$api = new VadsApi();
	$resp = $api->getResponse(
		$data, $method->ctx_mode, $method->key_test, $method->key_prod
	);

	if (!$resp->isAuthentified()) {
	    $this->logInfo('plgVmOnPaymentResponseReceived -- suspect request sent to plgVmOnPaymentResponseReceived, IP : ' . $_SERVER['REMOTE_ADDR'], 'error');
	    $html = $this->_getHtmlPaymentResponse('VMPAYMENT_'.$this->_name.'_ERROR_MSG', false);
	    return null;
	}

	// Retrieve order info from database
	if (!class_exists('VirtueMartModelOrders')) {
	    require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );
	}

	$virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($resp->get('order_id'));

	// Order not found
	if (!$virtuemart_order_id) {
	    vmdebug('plgVmOnPaymentResponseReceived '.$this->_name, $data, $resp->get('order_id'));
	    $this->logInfo('plgVmOnPaymentResponseReceived -- payment check attempted on non existing order : ' . $resp->get('order_id'), 'error');
	    $html = $this->_getHtmlPaymentResponse('VMPAYMENT_'.$this->_name.'_ERROR_MSG', false);
// 	    JRequest::setVar('paymentResponseHtml', $html, 'post');
	    return null;
	}

	$order = VirtueMartModelOrders::getOrder($virtuemart_order_id);
	$order_status_code = $order['items'][0]->order_status;

	if ($resp->isAcceptedPayment()) {
	    $currency = $api->findCurrencyByNumCode($resp->get('currency'))->alpha3;
	    $amount = ($resp->get('amount') / 100) . ' ' . $currency;
	    $html = $this->_getHtmlPaymentResponse('VMPAYMENT_'.$this->_name.'_SUCCESS_MSG', true, $resp->get('order_id'), $amount);
	    //JRequest::setVar('paymentResponseHtml', $html, 'post');

	    $new_status = $method->order_success_status;
	} else {
	    $html = $this->_getHtmlPaymentResponse('VMPAYMENT_'.$this->_name.'_FAILURE_MSG', false);
// 	    JRequest::setVar('paymentResponseHtml', $html, 'post');
	    $new_status = $method->order_failure_status;
	}

	// Order not processed yet
	if ($order_status_code == 'P') {
	    $this->logInfo('plgVmOnPaymentResponseReceived -- check url does not work.', 'warning');
	    if ($method->site_id == '56790135') {
		// Mode TEST DEFAULT VALUE: The plugin use default value.
		vmWarn(JText::_('VMPAYMENT_'.$this->_name.'_CHECK_URL_WARN_VIRTUEMART'), '');
	    } elseif ($method->ctx_mode == 'TEST') {
		//Mode TEST warning : Check URL not correctly called.
		vmWarn(JText::_('VMPAYMENT_'.$this->_name.'_CHECK_URL_WARN'), '');
	    }
	    $this->managePaymentResponse($virtuemart_order_id, $resp, $new_status);
	}

	return null;
    }

    /**
     * Process a PayZen payment cancellation.
     * @param int $virtuemart_order_id virtuemart order primary key concerned by payment
     * @return
     */
    function plgVmOnUserPaymentCancel() {
	if (!class_exists('VirtueMartModelOrders'))
	    require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );

	$order_number = JRequest::getString('on');
	if (!$order_number) {
	    return false;
	}
	if (!$virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($order_number)) {
	    return null;
	}
	if (!($paymentTable = $this->getDataByOrderId($virtuemart_order_id))) {
	    return null;
	}


	$session = JFactory::getSession();
	$return_context = $session->getId();
	$field = $this->_name . '_custom';
	if (strcmp($paymentTable->$field, $return_context) === 0) {
	    $this->handlePaymentUserCancel($virtuemart_order_id);
	}
	//JRequest::setVar('paymentResponse', $returnValue);
	return true;
    }

    /**
     * Check PayZen response, save order and empty cart (if payment success) when server notification is received from payment platform.
     * @param string $return_context session id
     * @param int $virtuemart_order_id virtuemart order primary key concerned by payment
     * @param string $new_status new order status
     * @return
     */
    function plgVmOnPaymentNotification() {
	// platform params and payment data
	$data = JRequest::get('post');
	$this->_debug = true; // enable debug
	$this->logInfo('plgVmOnPaymentNotification START ', 'error');
	if (!array_key_exists('vads_order_id', $data) || !isset($data['vads_order_id'])) {
	    $this->logInfo('plgVmOnPaymentNotification -- Another method was selected, do nothing : ', 'error');

	    return null; // Another method was selected, do nothing
	}

	// Retrieve order info from database
	if (!class_exists('VirtueMartModelOrders')) {
	    require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );
	}

	$virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($data['vads_order_id']);
	// Order not found
	/*
	  if (!$virtuemart_order_id) {
	  $this->logInfo('plgVmOnPaymentNotification -- payment check attempted on non existing order : ' . $resp->get('order_id'), 'error');

	  $response .= '<span style="display:none">OK-';
	  $response .= $data['vads_hash'];
	  $response .= "=Impossible de retrouver la commande\n";
	  $response .= '</span>';

	  die($response);
	  }
	 */


	// Payment params
	$payment_data = $this->getDataByOrderId($virtuemart_order_id);
	/*
	  if (!$payment_data || !($payment = $this->getPaymentMethod($payment_data->payment_method_id))) {
	  $this->logInfo('plgVmOnPaymentNotification -- payment data not found: exit ', 'ERROR');

	  $response .= '<span style="display:none">OK-';
	  $response .= $data['vads_hash'];
	  $response .= "=Méthode de paiement introuvable\n";
	  $response .= '</span>';

	  die($response);
	  }
	 */
	$method = $this->getVmPluginMethod($payment->virtuemart_paymentmethod_id);
	if (!$this->selectedThisElement($method->payment_element)) {
	    return false;
	}

	$this->_debug = $method->debug;
	$custom = $this->_name . '_custom';
	$return_context = $payment_data->$custom;

	// Load API
	if (!class_exists('VadsApi')) {
	    require(JPATH_VMPAYMENTPLUGIN . DS . 'payzen' . DS . 'payzen_api.php');
	}

	$api = new VadsApi();
	$resp = $api->getResponse(
		$data, $method->ctx_mode, $method->key_test, $method->key_prod
	);

	if (!$resp->isAuthentified()) {
	    $this->logInfo('plgVmOnPaymentNotification -- suspect request sent to plgVmOnPaymentNotification, IP : ' . $_SERVER['REMOTE_ADDR'], 'error');

	    die($resp->getOutputForGateway('auth_fail'));
	}

	$order = VirtueMartModelOrders::getOrder($virtuemart_order_id);
	$order_status_code = $order['items'][0]->order_status;

	// Order not processed yet
	if ($order_status_code == 'P') {
	    if ($resp->isAcceptedPayment()) {
		$currency = $api->findCurrencyByNumCode($resp->get('currency'))->alpha3;
		$amount = ($resp->get('amount') / 100) . ' ' . $currency;

		$new_status = $method->order_success_status;


		$this->logInfo('plgVmOnPaymentNotification -- payment process OK, ' . $amount . ' paid for order ' . $resp->get('order_id') . ', new status ' . $new_status, 'message');
		echo ($resp->getOutputForGateway('payment_ok'));
	    } else {
		$new_status = $method->order_failure_status;

		$this->logInfo('plgVmOnPaymentNotification -- payment process error ' . $resp->message . ', new status ' . $new_status, 'ERROR');
		echo ($resp->getOutputForGateway('payment_ko'));
	    }

	    // Save platform response
	    $this->managePaymentResponse($virtuemart_order_id, $resp, $new_status, $return_context);
	} else {
	    // Order already processed
	    if ($resp->isAcceptedPayment()) {
		echo ($resp->getOutputForGateway('payment_ok_already_done'));
	    } else {
		echo ($resp->getOutputForGateway('payment_ko_on_order_ok'));
	    }
	}

	die();
    }

    /**
     * Display stored payment data for an order
     * @see components/com_virtuemart/helpers/vmPaymentPlugin::plgVmOnShowOrderPaymentBE()
     */
    function plgVmOnShowOrderBEPayment($virtuemart_order_id, $payment_method_id) {

	if (!$this->selectedThisByMethodId($payment_method_id)) {
	    return null; // Another method was selected, do nothing
	}
	if (!($paymentTable = $this->getDataByOrderId($virtuemart_order_id))) {
	    return null;
	}

	$html = '<table class="adminlist">' . "\n";
	$html .= $this->getHtmlHeaderBE();
	$html .= $this->getHtmlRowBE(strtoupper($this->_name) . '_PAYMENT_NAME', $paymentTable->payment_name);
	$payment_status = $this->_name . '_response_payment_status';
	$payment_response_trans_id = $this->_name . '_response_trans_id';
	$payment_response_card_number = $this->_name . '_response_card_number';
	$payment_response_payment_mean = $this->_name . '_response_payment_mean';
	$payment_response_payment_message = $this->_name . '_response_payment_message';
	$payment_response_expiry_month = $this->_name . '_response_expiry_month';
	$payment_response_expiry_year = $this->_name . '_response_expiry_year';
	$result = $paymentTable->$payment_response_payment_message . '(' . $paymentTable->$payment_status . ')';
	$expiry = str_pad($paymentTable->$payment_response_expiry_month, 2, '0', STR_PAD_LEFT) .
		' / ' . $paymentTable->$payment_response_expiry_year;

	$html .= $this->getHtmlRowBE( $this->_name.'_RESULT', $result);
	$html .= $this->getHtmlRowBE($this->_name.'_TRANS_ID', $paymentTable->$payment_response_trans_id);
	$html .= $this->getHtmlRowBE($this->_name.'_CC_NUMBER', $paymentTable->$payment_response_card_number);
	$html .= $this->getHtmlRowBE($this->_name.'_CC_EXPIRY', $expiry);
	$html .= $this->getHtmlRowBE($this->_name.'_CC_TYPE', $paymentTable->$payment_response_payment_mean);
	$html .= '</table>' . "\n";

	return $html;
    }

    function _getHtmlPaymentResponse($msg, $is_success = true, $order_id = null, $amount = null) {
	if (!$is_success) {
	    return '<p style="text-align: center;">' . JText::_($msg) . '</p>';
	} else {
	    $html = '<table>' . "\n";
	    $html .= '<thead><tr><td colspan="2" style="text-align: center;">' . JText::_($msg) . '</td></tr></thead>';
	    $html .= $this->getHtmlRow($this->_name.'_ORDER_NUMBER', $order_id, 'style="width: 90px;" class="key"');
	    $html .= $this->getHtmlRow($this->_name.'_AMOUNT', $amount, 'style="width: 90px;" class="key"');
	    $html .= '</table>' . "\n";

	    return $html;
	}
    }

    function savePaymentData($virtuemart_order_id, $resp) {
	vmdebug($this->_name.' response', $resp->raw_response);
	$response[$this->_tablepkey] = $this->_getTablepkeyValue($virtuemart_order_id);
	$response['virtuemart_order_id'] = $virtuemart_order_id;
	$response[$this->_name . '_response_payment_amount'] = $resp->get('amount');
	$response[$this->_name . '_response_payment_currency'] = $resp->get('currency');
	$response[$this->_name . '_response_auth_number'] = $resp->get('auth_number');
	$response[$this->_name . '_response_payment_mean'] = $resp->get('card_brand');
	$response[$this->_name . '_response_payment_date'] = gmdate('Y-m-d H:i:s', time());
	$response[$this->_name . '_response_payment_status'] = $resp->code;
	$response[$this->_name . '_response_payment_message'] = $resp->message;
	$response[$this->_name . '_response_card_number'] = $resp->get('card_number');
	$response[$this->_name . '_response_trans_id'] = $resp->get('trans_id');
	$response[$this->_name . '_response_expiry_month'] = $resp->get('expiry_month');
	$response[$this->_name . '_response_expiry_year'] = $resp->get('expiry_year');
	$this->storePSPluginInternalData($response, $this->_tablepkey, true);
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

    function emptyCart($session_id) {
	if ($session_id != null) {
	    $session = JFactory::getSession();
	    $session->close();

	    // Recover session in wich the payment is done
	    session_id($session_id);
	    session_start();
	}

	$cart = VirtueMartCart::getCart();
	$cart->emptyCart();
	return true;
    }

    function managePaymentResponse($virtuemart_order_id, $resp, $new_status, $return_context = NULL) {
	// Save platform response data
	$this->savePaymentData($virtuemart_order_id, $resp);

	if (!class_exists('VirtueMartModelOrders')) {
	    require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );
	}
	// save order data
	$modelOrder = new VirtueMartModelOrders();
	$order['order_status'] = $new_status;
	$order['virtuemart_order_id'] = $virtuemart_order_id;
	$order['customer_notified'] = 1;
	$date = JFactory::getDate();
	$order['comments'] = JText::sprintf('VMPAYMENT_'.$this->_name.'_NOTIFICATION_RECEVEIVED', $date->toFormat('%Y-%m-%d %H:%M:%S'));
	vmdebug($this->_name.' - managePaymentResponse', $order);

	// la fonction updateStatusForOneOrder fait l'envoie de l'email à partir de VM2.0.2
	$modelOrder->updateStatusForOneOrder($virtuemart_order_id, $order, true);

	if (!class_exists('VirtueMartCart')) {
	    require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
	}

	if ($resp->isAcceptedPayment()) {
	    // Empty cart in session
	    $this->emptyCart($return_context);
	}
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
	return $this->onCheckAutomaticSelected($cart, $cart_prices ,   $paymentCounter);
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