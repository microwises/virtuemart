<?php

defined('_JEXEC') or die();

/**
 *
 * a special type of Klarna
 * @author Val√©rie Isaksen
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
if (!class_exists('vmPSPlugin'))
    require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');
if (JVM_VERSION === 2) {
    define('JPATH_VMKLARNAPLUGIN', JPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'klarna');
    define('VMKLARNAPLUGINWEBROOT', 'plugins/vmpayment/klarna/');
} else {
    define('JPATH_VMKLARNAPLUGIN', JPATH_ROOT . DS . 'plugins' . DS . 'vmpayment');
    define('VMKLARNAPLUGINWEBROOT', 'plugins/vmpayment/');
}
if (!class_exists('Klarna'))
    require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'api' . DS . 'klarna.php');
if (!class_exists('klarna_virtuemart'))
    require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarna_virtuemart.php');
if (!class_exists('PCStorage'))
    require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'api' . DS . 'pclasses' . DS . 'storage.intf.php');

if (!class_exists('KlarnaConfig'))
    require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'api' . DS . 'klarnaconfig.php');
if (!class_exists('KlarnaPClass'))
    require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'api' . DS . 'klarnapclass.php');
if (!class_exists('KlarnaCalc'))
    require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'api' . DS . 'klarnacalc.php');

if (!class_exists('KlarnaProductPrice'))
    require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarnaproductprice.php');

if (!class_exists('KlarnaHandler'))
    require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarnahandler.php');

if (!class_exists('Klarna_invoice'))
    require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarna_invoice.php');

class plgVmPaymentKlarna extends vmPSPlugin {

    // instance of class
    public static $_this = false;
    var $_vendor_currency = '';
    var $klarna_image_path = '';


    function __construct(& $subject, $config) {

	parent::__construct($subject, $config);

	$this->_loggable = true;
	$this->tableFields = array_keys($this->getTableSQLFields());
	$this->_tablepkey = 'id';
	$this->_tableId = 'id';
	$varsToPush = $this->getVarsToPush();
	$this->setConfigParameterable($this->_configTableFieldName, $varsToPush);
	$this->klarna_image_path = JURI::root() . VMKLARNAPLUGINWEBROOT . 'assets/images';
// Get vendor currency ???
	$this->_vendor_currency = $this->_getVendorCurrency();


	//self::$_this = $this;
    }

    public function getVmPluginCreateTableSQL() {

	return $this->createTableSQL('Payment Klarna Table');
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
	    'tax_id' => 'smallint(1)',
	    'eid' => 'int(10)',
	    'klarna_order_status' => 'tinyint(4)',
	    'klarna_order_title' => 'varchar(255)',
	    'klarna_invoice_no' => 'varchar(255)',
	    'klarna_log' => 'varchar(255)',
	);
	return $SQLfields;
    }

    function plgVmDeclarePluginParamsPayment($name, $id, &$data) {
	return $this->declarePluginParams('payment', $name, $id, $data);
    }

    function plgVmSetOnTablePluginParamsPayment($name, $id, &$table) {

	return $this->setOnTablePluginParams($name, $id, $table);
    }

    function plgVmOnProductDisplayPayment($product, &$productDisplay) {

	$vendorId = 1;
	if ($this->getPluginMethods($vendorId) === 0) {
	    return false;
	}
	if (!class_exists('klarna_productPrice'))
	    require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarna_productprice.php');

	$cart_country_code_3 = $this->_getCartAddressCountryCode3();
	$html = array();
	foreach ($this->methods as $method) {
	    if (in_array('klarna_partpay', $method->klarna_modules)) {
		$productPrice = new klarna_productPrice($method, $product, $cart_country_code_3 );
		$productDisplay [] = $productPrice->showProductPrice($method, $html, $product->prices['basePriceWithTax']);
	    }
	}
	return true;
    }

    /*
     * TODO: check if ST or BT address
     */

    function _getCartAddressCountryCode3(VirtueMartCart $cart = null) {
	if ($cart == '') {
	    if (!class_exists('VirtueMartCart'))
		require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
	    $cart = VirtueMartCart::getCart();
	}
	$address = (($cart->ST == 0) ? $cart->BT : $cart->ST);
	if (!isset($address['virtuemart_country_id'])) {
	    return null;
	}
	$db = JFactory::getDBO();
	$query = 'SELECT country_3_code
	FROM `#__virtuemart_countries`
	WHERE virtuemart_country_id = ' . $address['virtuemart_country_id'];
	$db->setQuery($query);
	return $db->loadResult();
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
     */
    public function plgVmDisplayListFEPayment(VirtueMartCart $cart, $selected = 0, &$htmlIn) {

	if ($this->getPluginMethods($cart->vendorId) === 0) {
	    if (empty($this->_name)) {
		$app = JFactory::getApplication();
		$app->enqueueMessage(JText::_('COM_VIRTUEMART_CART_NO_' . strtoupper($this->_psType)));
		return false;
	    } else {
		return false;
	    }
	}
	JHTML::script('klarna_general.js', VMKLARNAPLUGINWEBROOT . 'klarna/assets/js/', false);
	$html = array();
	$method_name = $this->_psType . '_name';
	foreach ($this->methods as $method) {
	    //$methodSalesPrice = $this->calculateSalesPrice($cart, $method, $cart->pricesUnformatted);
	    $temp = $this->displayListFEPayment($cart, $method);
	    if (!empty($temp)) {
		$html[] = $temp;
	    }
	}
	$htmlIn[] = $html;
    }

    /*
     * @param $plugin plugin
     */

    protected function displayListFEPayment(VirtueMartCart $cart, $method) {
	$return = '';
	$plugin_name = $this->_psType . '_name';
	$plugin_desc = $this->_psType . '_desc';
	$description = '';
	//vmdebug('$this->methods',$method);
	if (!empty($plugin->$plugin_desc)) {
	    $description = '<span class = "' . $this->_type . '_description">' . $method->$plugin_desc . '</span>';
	}
	$pluginName = $return . '<span class = "' . $this->_type . '_name">' . $method->$plugin_name . '</span>' . $description;

	// accepted by Klarna
	$currency_code = ShopFunctions::getCurrencyByID($cart->pricesCurrency, 'currency_code_3');

	$country_code = $this->_getCartAddressCountryCode3($cart);
	if (!( $countrySettings = $this->checkCountryCondition($method, $country_code, $cart) )) {
	    return null;
	}
	$vendor_currency = $this->_getVendorCurrency();
	// @TODO: if the country is not set in the cart .. redirect to the get address?
	// or just put a warning?
//$countrysettings = KlarnaHandler::countryData($method,$country_code);
	// Check if we should display anything at all
	// Do not display Klarnas payment option if we do not
	// accept the country/currency combination
	$accepted = $this->_getLangTag($country_code, $currency_code, $langTag);
	if (!$accepted) {
	    if ($method->klarna_mode == 'klarna_live') {
		vmError("Currency / Country mismatch. Please check your settings.");
		vmError("</ br> " . $langTag . " : " . $currency_code);
	    }
	    return null;
	}

	$pclasses = KlarnaHandler::getPClasses(null, $country_code, $method, $countrySettings );
	$this->getNbPClasses($pclasses, $speccamp, $partpay);

	$session = JFactory::getSession();
	$sessionKlarna = $session->get('Klarna', 0, 'vm');
	if ($sessionKlarna) {
	    $sessionKlarnaData = unserialize($sessionKlarna);
	}
	$klarna_paymentmethod="";
	if (isset($sessionKlarnaData->KLARNA_DATA['klarna_paymentmethod'])) {
	    $klarna_paymentmethod = $sessionKlarnaData->KLARNA_DATA['klarna_paymentmethod'];
	}
	$virtuemart_paymentmethod_id = $method->virtuemart_paymentmethod_id;
	$html_invoice ='';
	if (in_array('klarna_invoice', (array) $method->klarna_modules)) {
	    if (!class_exists('Klarna_invoice'))
		require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarna_invoice.php');
	    $invoice = new klarna_invoice($method, $cart, $vendor_currency);
	    $klarna_pm = $invoice->invoice($method);
	    $html_invoice = $this->renderByLayout('displaypayment',array('klarna_pm' => $klarna_pm,'virtuemart_paymentmethod_id' =>  $virtuemart_paymentmethod_id,'klarna_paymentmethod' =>  $klarna_paymentmethod ) );
	}
	$html_partpay='';
	if (in_array('klarna_partpay', (array) $method->klarna_modules) && $partpay > 0) { // Show only if partpayment is enabled and we have pclasses.
	    if (!class_exists('Klarna_partpay'))
		require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarna_partpay.php');
	    $partPay = new klarna_partpay($method, $cart, $vendor_currency);
	    $klarna_pm = $partPay->partPay($method);
	    $html_partpay = $this->renderByLayout('displaypayment',array('klarna_pm' => $klarna_pm,'virtuemart_paymentmethod_id' =>  $virtuemart_paymentmethod_id,'klarna_paymentmethod' =>  $klarna_paymentmethod ) );
	}
	$html_speccamp='';
	if (in_array('klarna_speccamp', (array) $method->klarna_modules) && $speccamp > 0) { // Show only if campaigns are enabled and we have pclasses.
	    if (!class_exists('Klarna_speccamp'))
		require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarna_speccamp.php');
	    $specCamp = new klarna_speccamp($method, $cart, $vendor_currency);
	    $klarna_pm = $specCamp->specCamp($method);
	    $html_speccamp = $this->renderByLayout('displaypayment',array('klarna_pm' => $klarna_pm,'virtuemart_paymentmethod_id' =>  $virtuemart_paymentmethod_id,'klarna_paymentmethod' =>  $klarna_paymentmethod ) );
	}

	$html = '<script type="text/javascript">
			// console.log( "clarna' . $klarna_paymentmethod . ' " );
            setTimeout(\'jQuery(":radio[value=' . $klarna_paymentmethod . ']").click();\', 200);
        </script>';

// TO DO add html:
	$pluginHtml = $html_invoice . $html_partpay . $html_speccamp . $html;


	return $pluginHtml;
    }

    function getNbPClasses($pclasses, &$speccamp, &$partpay) {

	$speccamp = 0;
	$partpay = 0;
	foreach ($pclasses as $pclass) {
	    if ($pclass->getType() == KlarnaPClass::SPECIAL)
		$speccamp += 1;
	    if ($pclass->getType() == KlarnaPClass::CAMPAIGN ||
		    $pclass->getType() == KlarnaPClass::ACCOUNT ||
		    $pclass->getType() == KlarnaPClass::FIXED ||
		    $pclass->getType() == KlarnaPClass::DELAY)
		$partpay += 1;
	}
    }

    function checkCountryCondition($method, $country_code, $cart) {
	if (empty($country_code)) {
	    $app = JFactory::getApplication();
	    $msg = JText::_('VMPAYMENT_KLARNA_GET_BT_ADDRESS');
	    vmWarn($msg);
	    return false;
	} else if (!in_array($country_code, (array) $method->klarna_countries)) {
	    return false;
	}
	if (strtolower($country_code) == 'nld' && $cart->pricesUnformatted['salesPrice'] > 250) {
	    // We can't show our payment options for Dutch customers
	    // if price exceeds 250 euro. Will be replaced with ILT in
	    // the future.
	    return false;
	}
	// Get the country settings
	if (!class_exists('KlarnaHandler'))
	    require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarnahandler.php');

	$countrysettings = KlarnaHandler::getCountryData($method, $country_code);
	if ($countrysettings['eid'] == '' || $countrysettings['eid'] == 0) {
	    return false;
	}


	return $countrysettings;
    }

    function _getLangTag($country_code, $currency_code, &$langTag) {
	$accepted = false;
	$langTag = 'en';
	switch ($country_code) {
	    case 'SWE':
		$langTag = 'se';
		$accepted = ($currency_code == "SEK");
		break;
	    case 'NOR':
		$langTag = 'no';
		$accepted = ($currency_code == "NOK");
		break;
	    case 'DNK':
		$langTag = 'dk';
		$accepted = ($currency_code == "DKK");
		break;
	    case 'FIN':
		$langTag = 'fi';
		$accepted = ($currency_code == "EUR");
		break;
	    case 'DEU':
		$langTag = 'de';
		$accepted = ($currency_code == "EUR");
		break;
	    case 'NLD':
		$langTag = 'nl';
		$accepted = ($currency_code == "EUR");
		break;
	}
	return $accepted;
    }

    function plgVmConfirmedOrder($cart, $order) {

	if (!($method=$this->getVmPluginMethod($order['details']['BT']->virtuemart_paymentmethod_id))) {
	    return null; // Another method was selected, do nothing
	}
	if (!$this->selectedThisElement($method->payment_element)) {
	    return false;
	}
	if (!class_exists('KlarnaLanguagePack'))
	    require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarnalanguagepack.php');
	$session = JFactory::getSession();
	$sessionKlarna = $session->get('Klarna', 0, 'vm');
	$sessionKlarnaData = unserialize($sessionKlarna);

	try {
	    $result = KlarnaHandler::addTransaction($method, $cart, $order );
	    $invoiceno = $result[1];
	} catch (Exception $e) {
	    KlarnaHandler::redirectPaymentMethod('error', $e->getMessage() . ' #' . $e->getCode());
	}

	if ($invoiceno && is_numeric($invoiceno)) {
	    $kLang = new KlarnaLanguagePack(JPATH_VMKLARNAPLUGIN . '/klarna/language/klarna_language.xml');

	    //Get address id used for this order.
	    $country = $sessionKlarnaData->KLARNA_DATA['COUNTRY'];
	    $lang = KlarnaHandler::getLanguageForCountry($method, $country);

	    $d['order_payment_name'] = $kLang->fetch('MODULE_INVOICE_TEXT_TITLE', $lang);
	    // Add a note in the log
	    $log = str_replace('(xx)', $invoiceno, $kLang->fetch('INVOICE_CREATED_SUCCESSFULLY', $lang));

// Prepare data that should be stored in the database
	    $dbValues['order_number'] = $order['details']['BT']->order_number;
	    $dbValues['payment_name'] = $this->renderPluginName($method, $order);
	    $dbValues['virtuemart_paymentmethod_id'] = $cart->virtuemart_paymentmethod_id;
	    $dbValues['cost_per_transaction'] = $method->cost_per_transaction;
	    $dbValues['cost_percent_total'] = $method->cost_percent_total;
	    $dbValues['payment_currency'] = $method->payment_currency;
	    $dbValues['payment_order_total'] = $totalInPaymentCurrency;
	    $dbValues['tax_id'] = $method->tax_id;
	    $dbValues['klarna_invoice_no'] = $invoiceno;
	    $dbValues['Klarna_log'] = $log;
	    $dbValues['Klarna_eid'] = $result['eid'];
	    $dbValues['klarna_order_status'] = $result['order_status'];
	    $dbValues['Klarna_order_title'] = $result['text'];

	    $this->storePSPluginInternalData($dbValues);
	    // Delete all Klarna data
	    unset($sessionKlarnaData->KLARNA_DATA, $_SESSION['SSN_ADDR']);

	    $html = '<table class="vmorder-done">' . "\n";
	    $html .= $this->getHtmlRow('KLARNA_PAYMENT_INFO', $dbValues['payment_name'], "vmorder-done-payinfo");
	    $html .= '</table>' . "\n";

	    $modelOrder = VmModel::getModel('orders');
	    $order['order_status'] = 'P';
	    $order['customer_notified'] = 1;
	    $order['comments'] = '';
	    $modelOrder->updateStatusForOneOrder($order['details']['BT']->virtuemart_order_id, $order, true);

	    //We delete the old stuff
	    $cart->emptyCart();
	    JRequest::setVar('html', $html);
	    return true;
	}

	//KlarnaHandler::redirectPaymentMethod('error', $invoiceno);
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

	// the payment itself should send the parameter needed.
	$virtuemart_paymentmethod_id = JRequest::getInt('pm', 0);
	$order_number = JRequest::getVar('on', 0);
	$vendorId = 0;
	if (!($method = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
	    return null; // Another method was selected, do nothing
	}
	if (!$this->selectedThisElement($method->payment_element)) {
	    return false;
	}
	if (!class_exists('VirtueMartCart'))
	    require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
	if (!class_exists('shopFunctionsF'))
	    require(JPATH_VM_SITE . DS . 'helpers' . DS . 'shopfunctionsf.php');
	if (!class_exists('VirtueMartModelOrders'))
	    require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );

	$paypal_data = JRequest::get('post');
	$payment_name = $this->renderPluginName($method);

	if (!empty($paypal_data)) {
	    vmdebug('plgVmOnPaymentResponseReceived', $paypal_data);
	    $order_number = $paypal_data['invoice'];
	    $return_context = $paypal_data['custom'];
	    if (!class_exists('VirtueMartModelOrders'))
		require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );
	    $virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($order_number);
	    $payment_name = $this->renderPluginName($method);
	    if ($virtuemart_order_id) {
		$order['customer_notified'] = 0;
		$order['order_status'] = $this->_getPaymentStatus($method, $paypal_data['payment_status']);
		$order['comments'] = JText::sprintf('VMPAYMENT_PAYPAL_PAYMENT_STATUS_CONFIRMED', $order_number);
		// send the email ONLY if payment has been accepted
		$modelOrder = VmModel::getModel('orders');
		$orderitems = $modelOrder->getOrder($virtuemart_order_id);
		$nb_history = count($orderitems['history']);
		if ($orderitems['history'][$nb_history - 1]->order_status_code != $order['order_status']) {
		    $this->_storePaypalInternalData($method, $paypal_data, $virtuemart_order_id);
		    $this->logInfo('plgVmOnPaymentResponseReceived, sentOrderConfirmedEmail ' . $order_number, 'message');
		    $order['virtuemart_order_id'] = $virtuemart_order_id;
		    $order['comments'] = JText::sprintf('VMPAYMENT_PAYPAL_EMAIL_SENT');
		    $modelOrder->updateStatusForOneOrder($virtuemart_order_id, $order, true);
		}
	    } else {
		vmError('Klarna data received, but no order number');
		return;
	    }
	} else {
	    $virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($order_number);
	}
	if (!($paymentTable = $this->_getPaypalInternalData($virtuemart_order_id, $order_number) )) {
	    // JError::raiseWarning(500, $db->getErrorMsg());
	    return '';
	}
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

	$order_number = JRequest::getVar('on');
	if (!$order_number)
	    return false;
	$db = JFactory::getDBO();
	$query = 'SELECT ' . $this->_tablename . '.`virtuemart_order_id` FROM ' . $this->_tablename . " WHERE  `order_number`= '" . $order_number . "'";

	$db->setQuery($query);
	$virtuemart_order_id = $db->loadResult();

	if (!$virtuemart_order_id) {
	    return null;
	}
	$this->handlePaymentUserCancel($virtuemart_order_id);

	//JRequest::setVar('paymentResponse', $returnValue);
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

    }

    function plgVmOnSelfCallFE($type, $name, &$render) {

	//refresh captcha code
	//Klarna Ajax
	require (JPATH_VMKLARNAPLUGIN.'/klarna/helpers/klarna_ajax.php');

	if (!class_exists( 'VmModel' )) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmmodel.php');
	$model = VmModel::getModel('paymentmethod');
	$payment = $model->getPayment();
	if (!class_exists( 'vmParameters' )) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'parameterparser.php');
	$parameters = new vmParameters($payment,  $payment->payment_element , 'plugin' ,'vmpayment');
	$method = $parameters->getParamByName('data');
	//print_r($method);
	$country = jrequest::getword('country');
	$country = KlarnaHandler::convertToThreeLetterCode( $country);
	// $eid = KlarnaHandler::getEid($method, $country);
	if (!class_exists( 'klarna_virtuemart' )) require (JPATH_VMKLARNAPLUGIN.'/klarna/helpers/klarna_virtuemart.php');
	//KlarnaAjax($api  , $eid, $path, $webroot)  ;
	// $klarnaVM= new  klarna_virtuemart ;
	
	$settings = KlarnaHandler::getCountryData($method, $country);

  $klarna = new Klarna_virtuemart();
  $klarna->config($settings['eid'], $settings['secret'], $settings['country'], $settings['language'], $settings['currency'], (($method->klarna_mode == 'klarna_live') ?Klarna::LIVE : Klarna::BETA), $method->klarna_pc_type, $method->klarna_pc_uri, true);
	
	
	
	$SelfCall= new KlarnaAjax($klarna,(int)$settings['eid'], JPATH_VMKLARNAPLUGIN,Juri::base()) ;
	$action = jrequest::getWord('action');

	echo $SelfCall->$action ();
	//echo 'klarna reponse'.$action;
	jexit();
    }

    function plgVmOnSelfCallBE($type, $name, &$render) {

	//refresh captcha code
	// fetches PClasses
	$call = jrequest::getWord('call');
	require (JPATH_VMKLARNAPLUGIN.'/klarna/helpers/selfcall.php');
	$SelfCall= new KlarnaSelfCall ;
	$SelfCall->$call ();
	jexit();
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


	if (!($paymentTable = $this->_getKlarnaInternalData($virtuemart_order_id) )) {
	    // JError::raiseWarning(500, $db->getErrorMsg());
	    return '';
	}
	$this->getPaymentCurrency($paymentTable);
	$q = 'SELECT `currency_code_3` FROM `#__virtuemart_currencies` WHERE `virtuemart_currency_id`="' . $paymentTable->payment_currency . '" ';
	$db = &JFactory::getDBO();
	$db->setQuery($q);
	$currency_code_3 = $db->loadResult();
	$html = '<table class="adminlist">' . "\n";
	$html .=$this->getHtmlHeaderBE();
	$html .= $this->getHtmlRowBE('KARNA_PAYMENT_NAME', $paymentTable->payment_name);
	//$html .= $this->getHtmlRowBE('PAYPAL_PAYMENT_TOTAL_CURRENCY', $paymentTable->payment_order_total.' '.$currency_code_3);
	$code = "klarna_";
	foreach ($paymentTable as $key => $value) {
	    if (substr($key, 0, strlen($code)) == $code) {
		$html .= $this->getHtmlRowBE($key, $value);
	    }
	}
	$html .= '</table>' . "\n";
	return $html;
    }

    function _getKlarnaInternalData($virtuemart_order_id, $order_number = '') {
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

    function getCosts(VirtueMartCart $cart, $method, $cart_prices) {
	$country_code = $this->_getCartAddressCountryCode3($cart);
	return KlarnaHandler::getInvoiceFee($method, $country_code);


    }

    /**
     * Create the table for this plugin if it does not yet exist.
     * This functions checks if the called plugin is active one.
     * When yes it is calling the standard method to create the tables
     * @author Val√©rie Isaksen
     *
     */
    function plgVmOnStoreInstallPaymentPluginTable($jplugin_id) {

	return $this->onStoreInstallPluginTable($jplugin_id);
    }

    /**
     * This event is fired after the payment method has been selected. It can be used to store
     * additional payment info in the cart.

     * @author Val√©rie isaksen
     *
     * @param VirtueMartCart $cart: the actual cart
     * @return null if the payment was not selected, true if the data is valid, error message if the data is not vlaid
     *
     */
    public function plgVmOnSelectCheckPayment(VirtueMartCart $cart) {

	if (!$this->selectedThisByMethodId($cart->virtuemart_paymentmethod_id)) {
	    return null; // Another method was selected, do nothing
	}
	if (!($method = $this->getVmPluginMethod($cart->virtuemart_paymentmethod_id))) {
	    return null; // Another method was selected, do nothing
	}
if (!class_exists('KlarnaAddr')) require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'api' . DS . 'klarnaaddr.php');
if (!class_exists('KlarnaLanguagePack'))
	    require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarnalanguagepack.php');
	$session = JFactory::getSession();
	$sessionKlarna = new stdClass();
	$klarna_paymentmethod=  JRequest::getVar('klarna_paymentmethod');
	if ($klarna_paymentmethod == 'klarna_invoice') {
	    $kIndex = "klarna_";
	    $klarna_payment = "klarna_invoice";
	    $sessionKlarna->klarna_option = 'invoice';
	} elseif ($klarna_paymentmethod == 'klarna_partPayment') {
	    $kIndex = "klarna_partPayment";
	    $klarna_payment = "klarna_partpay";
	    $sessionKlarna->klarna_option = 'part';
	} elseif ($klarna_paymentmethod == 'klarna_speccamp') {
	    $kIndex = "klarna_spec_";
	    $klarna_payment = "klarna_speccamp";
	    $sessionKlarna->klarna_option = 'spec';
	} else {
	    return;
	}

	// Store payment_method_id so we can activate the
	// right payment in case something goes wrong.
	$sessionKlarna->virtuemart_payment_method_id = $cart->virtuemart_paymentmethod_id;
	$sessionKlarna->klarna_paymentmethod = $klarna_paymentmethod;

	$user_country = $this->_getCartAddressCountryCode3($cart);


	$kLang = new KlarnaLanguagePack(JPATH_VMKLARNAPLUGIN . '/klarna/language/klarna_language.xml');

	$country = KlarnaHandler::convertCountry($method, $user_country);
	$lang = KlarnaHandler::getLanguageForCountry($method, $country);

	// Get the correct data
	//Removes spaces, tabs, and other delimiters.
	//$klarna_pno = preg_replace('/[ \t\,\.\!\#\;\:\r\n\v\f]/', '', JRequest::getVar($kIndex . 'pnum'));
	$klarna_pno = preg_replace('/[ \t\,\.\!\#\;\:\r\n\v\f]/', '', JRequest::getVar( 'socialNumber'));
	$klarna_phone = JRequest::getVar($kIndex . 'phone');
	$klarna_email = JRequest::getVar($kIndex . 'email');
	$klarna_gender = JRequest::getVar($kIndex . 'gender');
	$klarna_street = JRequest::getVar($kIndex . 'street');
	$klarna_house_no = JRequest::getVar($kIndex . 'house');
	$klarna_house_ext = JRequest::getVar($kIndex . 'house_extension');
	$klarna_year_salary = JRequest::getVar($kIndex . 'ysalary');
	$klarna_reference = JRequest::getVar($kIndex . 'reference');
	$klarna_city = JRequest::getVar($kIndex . 'city');
	$klarna_zip = JRequest::getVar($kIndex . 'zip');
	$klarna_first_name = JRequest::getVar($kIndex . 'first_name');
	$klarna_last_name = JRequest::getVar($kIndex . 'last_name');
	$klarna_invoice_type = JRequest::getVar('klarna_invoice_type');
	$klarna_ilt_questions = JRequest::getVar($kIndex . 'ilt_questions');
	$klarna_company_name = JRequest::getVar('klarna_company_name');

	if (!isset($klarna_pno) || $klarna_pno == '') {
	    $klarna_pno = JRequest::getVar($kIndex . 'birth_day') .
		    JRequest::getVar($kIndex . 'birth_month') .
		    JRequest::getVar($kIndex . 'birth_year');
	}



	// If it is a swedish customer we use the information from getAddress
	if (strtoupper($user_country) == "SWE") {
	    // Get the address. The address should be sent from the payment
	    // form split in pieces with | as separator.
	    // It is already called in the standardregister, so we don't
	    // have to call it again.
	    $address = explode('|', JRequest::getVar($kIndex . 'shipment_address'));

	    if ($klarna_invoice_type != 'company') {
		$klarna_first_name = $address[0];
		$klarna_last_name = $address[1];
		$klarna_street = $address[2];
		$klarna_zip = $address[3];
		$klarna_city = $address[4];
		$klarna_company_name = '';
	    } else {
		$name = explode(' ', $klarna_reference, 2);
		$klarna_first_name = $name[0];
		$klarna_last_name = $name[1];
		$klarna_company_name = $address[0];
		$klarna_street = $address[1];
		$klarna_zip = $address[2];
		$klarna_city = $address[3];
	    }
	}

	if ($cart->ST != 0) {
	    // Update the Shipping Address to what is specified in the register.
	    $update_data = array(
		'company' => html_entity_decode($klarna_company_name),
		'first_name' => html_entity_decode($klarna_first_name),
		'last_name' => html_entity_decode($klarna_last_name),
		'address_1' => html_entity_decode($klarna_street) .
		(isset($klarna_house_no) ? ' ' . $klarna_house_no : '') .
		(isset($klarna_house_ext) ? ' ' . $klarna_house_ext : ''),
		'zip' => html_entity_decode($klarna_zip),
		'city' => html_entity_decode($klarna_city),
		'country' => $user_country,
		'state' => '-',
		'phone_1' => $klarna_phone,
		'user_email' => $klarna_email
	    );
// TODO update delivry address
	    /*
	      $db->buildQuery('UPDATE', '#__{vm}_user_info', $update_data, 'WHERE
	      `user_info_id`=\''.$db->getEscaped(
	      $d['ship_to_info_id']). '\'');
	      $db->query();
	      if (strtolower($user_country) == "swe") {
	      $vmLogger->info($kLang->fetch('address_updated_notice', $lang));
	      }
	     * */
	}

	// Store the Klarna data in a session variable so
	// we can retrevie it later when we need it
	$sessionKlarna->KLARNA_DATA = array(
	    'PAYMENT_METHOD_ID' => $cart->virtuemart_paymentmethod_id,
	    'PNO' => $klarna_pno,
	    'FIRST_NAME' => $klarna_first_name,
	    'LAST_NAME' => $klarna_last_name,
	    'PHONE' => $klarna_phone,
	    'EMAIL' => $klarna_email,
	    'PCLASS' => ($klarna_paymentmethod == 'klarna_invoice' ? -1 : intval(JRequest::getVar($kIndex . "paymentPlan"))), //???
	    'STREET' => $klarna_street,
	    'ZIP' => $klarna_zip,
	    'CITY' => $klarna_city,
	    'COUNTRY' => KlarnaHandler::convertCountry($method, $user_country),
	    'INVOICE_TYPE' => $klarna_invoice_type
	);
	if ($klarna_invoice_type == 'company') {
	    $sessionKlarna->KLARNA_DATA['COMPANY_NAME'] = $klarna_company_name;
	    $sessionKlarna->KLARNA_DATA['REFERENCE'] = $klarna_reference;
	}
	if (isset($klarna_gender)) {
	    $sessionKlarna->KLARNA_DATA['GENDER'] = $klarna_gender;
	}
	if (isset($klarna_street)) {
	    $sessionKlarna->KLARNA_DATA['STREET'] = $klarna_street;
	}
	if (isset($klarna_house_no)) {
	    $sessionKlarna->KLARNA_DATA['HOUSE_NO'] = $klarna_house_no;
	}
	if (isset($klarna_house_ext)) {
	    $sessionKlarna->KLARNA_DATA['HOUSE_EXT'] = $klarna_house_ext;
	}
	if (isset($klarna_year_salary)) {
	    $sessionKlarna->KLARNA_DATA['YEAR_SALARY'] = $klarna_year_salary;
	}

	// Check if we need the customer to answer ILT questions:

	$klarna_ilt = array();
	if (isset($_POST['klarna_ilt'])) {
	    foreach ($_POST['klarna_ilt'] as $param => $answer) {
		$klarna_ilt[$param] = $answer;
	    }
	}

	$settings = KlarnaHandler::getCountryData($method, $user_country);

	try {
	    $addr = new KlarnaAddr(
			    $klarna_email,
			    $klarna_phone,
			    "",
			    $klarna_first_name,
			    $klarna_last_name, '',
			    $klarna_street,
			    $klarna_zip,
			    $klarna_city,
			    $settings['country'],
			    $klarna_house_no,
			    $klarna_house_ext
	    );
	} catch (Exception $e) {
	    KlarnaHandler::redirectPaymentMethod('message', $e->getMessage());
	}
	// ILT Disabled for now.
	// $kIlt =  KlarnaHandler::checkILT(&$d, $klarna_pno,
	//                                  $klarna_gender, $addr);
	$kDiff = array();
	if (isset($_SESSION['prev_asked'])) {
	    foreach ($_SESSION['prev_asked'] as $key => $value) {
		$klarna_ilt[$key] = $value;
	    }
	    $kDiff = array_diff_key($kIlt, $klarna_ilt);
	}
	if (count($kDiff) > 0) {
	    $kDiff = KlarnaHandler::transformILTnames($kDiff);
	    $_SESSION['show_ilt']['questions'] = $kDiff;
	    $_SESSION['prev_asked'] = $klarna_ilt;
	    KlarnaHandler::redirectPaymentMethod('error', $kLang->fetch('ilt_title', $lang));
	}

	if (isset($klarna_ilt)) {
	    $sessionKlarna->KLARNA_DATA['ILT'] = $klarna_ilt;
	}

	if (isset($errors) && count($errors) > 0) {
	    $msg = $kLang->fetch('error_title_1', $lang);
	    foreach ($errors as $error) {
		$msg .= "<li> -" . $error . "</li>";
	    }
	    $msg .= $kLang->fetch('error_title_2', $lang);
	    unset($errors);
	    KlarnaHandler::redirectPaymentMethod('error', $msg);
	}
	$session->set('Klarna', serialize($sessionKlarna), 'vm');
	return true;
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
     * Reimplementation of vmPaymentPlugin::checkPaymentConditions()
     * @param array $cart_prices all cart prices
     * @param object $payment payment parameters object
     * @return bool true if conditions verified
     */
    function checkConditions($cart, $method, $cart_prices) {
	return true;
    }

    /*
     * @param $plugin plugin
     */

    protected function renderPluginName($method) {
	$return = '';
	$plugin_name = $this->_psType . '_name';
	$plugin_desc = $this->_psType . '_desc';
	$description = '';

	$return = $this->displayLogos($method) . ' ';

	if (!empty($method->$plugin_desc)) {
	    $description = '<span class="' . $this->_type . '_description">' . $method->$plugin_desc . '</span>';
	}
	$pluginName = $return . '<span class="' . $this->_type . '_name">' . $method->$plugin_name . '</span>' . $description;
	return $pluginName;
    }

    function displayLogos($method) {
	if (!class_exists('KlarnaLanguagePack'))
		    require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarnalanguagepack.php');

	$logo = '<img src="' . JURI::base() . VMKLARNAPLUGINWEBROOT . 'klarna/assets/images/logo/';
	$session = JFactory::getSession();
	$sessionKlarna = $session->get('Klarna', 0, 'vm');
	$sessionKlarnaData = unserialize($sessionKlarna);

	$country = KlarnaHandler::convertCountry($method, $this->_getCartAddressCountryCode3());

	switch ($sessionKlarnaData->klarna_option) {
	    case 'invoice':
		$logo .= $country . '/klarna_invoice.png';
		$method = "";
		break;
	    case 'partpayment':
	    case 'part':
		$logo .= $country . '/klarna_account.png';
		$method = "";
		break;
	    case 'speccamp':
		$logo .= 'klarna_logo.png';
		$lang = KlarnaHandler::getLanguageForCountry($method, $country);

		$kLang = new KlarnaLanguagePack(JPATH_VMKLARNAPLUGIN . '/klarna/language/klarna_language.xml');
		$method = $kLang->fetch('MODULE_SPEC_TEXT_TITLE', $lang);
		break;
	    default:
		$logo = '';
		$method = '';
		break;
	}
	$logo .= '"/>';

	$html = '  <div class="klarna_info">
        <span style="">
            <a href="http://www.klarna.com/">' . $logo . '</a><br>
            ' . $method . '
        </span>
    </div>

    <div class="clear"></div>';
	return $html;
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
    function plgVmOnCheckAutomaticSelectedPayment(VirtueMartCart $cart, array $cart_prices = array()) {
	 return null;
	return $this->onCheckAutomaticSelected($cart, $cart_prices);
    }

    /**
     * This method is fired when showing the order details in the frontend.
     * It displays the method-specific data.
     *
     * @param integer $order_id The order ID
     * @return mixed Null for methods that aren't active, text (HTML) otherwise
     * @author Valerie Isaksen
     */
    public function plgVmOnShowOrderFEPayment($virtuemart_order_id, $virtuemart_paymentmethod_id, &$payment_name) {
	$this->onShowOrderFE($virtuemart_order_id, $virtuemart_paymentmethod_id, $payment_name);
    }

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

    function _getVendorCurrency() {
	if (!class_exists('VirtueMartModelVendor'))
	    require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'vendor.php');
	$vendor_id = 1;
	$vendor_currency = VirtueMartModelVendor::getVendorCurrency($vendor_id);
	return $vendor_currency->currency_code_3;
    }


}

// No closing tag
