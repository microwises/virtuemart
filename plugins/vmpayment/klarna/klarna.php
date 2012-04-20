<?php

defined('_JEXEC') or die();

/**
 *
 * Klarna
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
    require ( JPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'klarna' . DS . 'klarna' . DS . 'helpers' . DS . 'define.php');
} else {
    require ( JPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'klarna' . DS . 'helpers' . DS . 'define.php');
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

if (!class_exists('KlarnaHandler'))
    require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarnahandler.php');


define('KLARNA_INVOICE_ACTIVE', 4);

class plgVmPaymentKlarna extends vmPSPlugin {

    // instance of class
    public static $_this = false;
    var $_vendor_currency = '';

    function __construct(& $subject, $config) {

	parent::__construct($subject, $config);

	$this->_loggable = true;
	$this->tableFields = array_keys($this->getTableSQLFields());
	$this->_tablepkey = 'id';
	$this->_tableId = 'id';
	$varsToPush = $this->getVarsToPush();
	$this->setConfigParameterable($this->_configTableFieldName, $varsToPush);
// Get vendor currency ???
	$this->_vendor_currency = $this->_getVendorCurrency();

	$jlang = JFactory::getLanguage();
	$jlang->load('plg_vmpayment_klarna', JPATH_ADMINISTRATOR, 'en-GB', true);
	$jlang->load('plg_vmpayment_klarna', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
	$jlang->load('plg_vmpayment_klarna', JPATH_ADMINISTRATOR, null, true);
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
	    'payment_fee' => 'decimal(10,2)',
	    'tax_id' => 'smallint(1)',
	    'klarna_eid' => 'int(10)',
	    'klarna_status_code' => 'tinyint(4)',
	    'klarna_status_text' => 'varchar(255)',
	    'klarna_invoice_no' => 'varchar(255)',
	    'klarna_log' => 'varchar(255)',
	    'klarna_pclass' => 'int(1)',
	    'klarna_pdf_invoice_url' => 'varchar(512)',
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

	if (!class_exists('VirtueMartCart'))
	    require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
	$cart = VirtueMartCart::getCart();

	$html = array();
	foreach ($this->methods as $method) {
	    //if (in_array('klarna_partpay', (array) $method->klarna_modules)) {
	    $productPrice = new klarna_productPrice($method, $product, $cart);
	    //vmdebug('plgVmOnProductDisplayPayment', $product);
	    $productDisplay [] = $productPrice->showProductPrice($product);
	    // }
	}
	JHTML::stylesheet('style.css', VMKLARNAPLUGINWEBROOT . '/klarna/assets/css/', false);
	return true;
    }

    /*
     * TODO: check if ST or BT address
     */

    function _getCartAddressCountryCode(VirtueMartCart $cart = null, &$countryCode, &$countryId, $fld = 'country_3_code') {
	if ($cart == '') {
	    if (!class_exists('VirtueMartCart'))
		require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
	    $cart = VirtueMartCart::getCart();
	}
	$address = (($cart->ST == 0 or empty($cart->ST)) ? $cart->BT : $cart->ST);
	if (!isset($address['virtuemart_country_id'])) {
	    if ($fld == 'country_3_code') {
		$countryCode = 'swe';
	    } else {
		$countryCode = 'se';
	    }
	    $countryId = ShopFunctions::getCountryIDByName($countryCode);
	} else {
	    $countryId = $address['virtuemart_country_id'];
	    $countryCode = shopFunctions::getCountryByID($address['virtuemart_country_id'], $fld);
	}
    }

    function _getCartAddressCountryId(VirtueMartCart $cart, $fld = 'country_3_code') {

	$address = (($cart->ST == 0 or empty($cart->ST)) ? $cart->BT : $cart->ST);
	if (!isset($address['virtuemart_country_id'])) {
	    return null;
	}

	return $address['virtuemart_country_id'];
    }

    /*
     * TODO: check if ST or BT address
     */

    function _getOrderAddressCountryCode($virtuemart_order_id, $fld = 'country_3_code') {
	$db = JFactory::getDBO();
	$q = 'SELECT `virtuemart_country_id`,  `address_type` FROM #__virtuemart_order_userinfos  WHERE virtuemart_order_id=' . $virtuemart_order_id;

	$db->setQuery($q);
	$results = $db->loadObjectList();
	if (count($results) == 1) {
	    $virtuemart_country_id = $results[0]->virtuemart_country_id;
	} else {
	    foreach ($results as $result) {
		if ($result->address_type == 'ST') {
		    $virtuemart_country_id = $result->virtuemart_country_id;
		    break;
		}
	    }
	}


	return shopFunctions::getCountryByID($virtuemart_country_id, $fld);
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

	$html = $this->displayListFEPayment($cart);
	if (!empty($html)) {
	    $htmlIn[] = $html;
	}
    }

    /*
     * @param $plugin plugin
     */

    protected function displayListFEPayment(VirtueMartCart $cart) {
	if (!class_exists('Klarna_payments'))
	    require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarna_payments.php');
	if (!class_exists('KlarnaVm2API'))
	    require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarna_vm2api.php');
	if ($this->getPluginMethods($cart->vendorId) === 0) {
	    if (empty($this->_name)) {
		$app = JFactory::getApplication();
		$app->enqueueMessage(JText::_('COM_VIRTUEMART_CART_NO_' . strtoupper($this->_psType)));
		return false;
	    } else {
		return false;
	    }
	}

	$html = array();
	$method_name = $this->_psType . '_name';
	foreach ($this->methods as $method) {
	    $temp = $this->getListFEPayment($cart, $method);
	    if (!empty($temp)) {
		$html[] = $temp;
	    }
	}
	if (!empty($html)) {
	    $this->loadScriptAndCss();
	}
	return $html;
    }

    /*
     * @param $plugin plugin
     */

    protected function getListFEPayment(VirtueMartCart $cart, $method) {

	$currency_code = ShopFunctions::getCurrencyByID($cart->pricesCurrency, 'currency_code_3');
	$this->_getCartAddressCountryCode($cart, $country_code, $countryId);
	if (!( $countrySettings = $this->checkCountryCondition($method, $country_code, $cart) )) {
	    return null;
	}

	// @TODO: if the country is not set in the cart .. redirect to the get address?
	// or just put a warning?
//$countrysettings = KlarnaHandler::countryData($method,$country_code);
	// Check if we should display anything at all
	// Do not display Klarnas payment option if we do not
	// accept the country/currency combination
	$todo = false;
	// really ? we don't care
	if ($todo) {
	    $accepted = $this->_getLangTag($country_code, $currency_code, $langTag);
	    if (!$accepted) {
		if ($method->klarna_mode == 'klarna_live') {
		    vmError("Currency / Country mismatch. Please check your settings.");
		    vmError("</ br> " . $langTag . " : " . $currency_code);
		}
		vmDebug('displayListFEPayment', "Currency / Country mismatch. Please check your settings.", "LANGUE=" . $langTag, "CURRENCY=" . $currency_code);
		return null;
	    }
	}
	$pclasses = KlarnaHandler::getPClasses(null, $country_code, $method, $countrySettings);
	$this->getNbPClasses($pclasses, $speccamp, $partpay);
	$sessionKlarnaData = $this->getKlarnaSessionData();

	$klarna_paymentmethod = "";
	if (isset($sessionKlarnaData->KLARNA_DATA['klarna_paymentmethod'])) {
	    $klarna_paymentmethod = $sessionKlarnaData->KLARNA_DATA['klarna_paymentmethod'];
	}

	$html = '';

	$payments = new klarna_payments($method, $country_code, $cart);
	$klarna_pm = $payments->invoice($method);
	$html .= $this->renderByLayout('displaypayment', array('klarna_pm' => $klarna_pm, 'virtuemart_paymentmethod_id' => $method->virtuemart_paymentmethod_id, 'klarna_paymentmethod' => $klarna_paymentmethod));
	if ($partpay > 0) {
	    if ($klarna_pm = $payments->partPay($method, $cart)) {
		$html .= $this->renderByLayout('displaypayment', array('klarna_pm' => $klarna_pm, 'virtuemart_paymentmethod_id' => $method->virtuemart_paymentmethod_id, 'klarna_paymentmethod' => $klarna_paymentmethod));
	    }
	}
	if ($speccamp > 0) {
	    if ($klarna_pm = $payments->specCamp($method, $cart)) {
		$html .= $this->renderByLayout('displaypayment', array('klarna_pm' => $klarna_pm, 'virtuemart_paymentmethod_id' => $method->virtuemart_paymentmethod_id, 'klarna_paymentmethod' => $klarna_paymentmethod));
	    }
	}

	$html_js = '<script type="text/javascript">
            setTimeout(\'jQuery(":radio[value=' . $klarna_paymentmethod . ']").click();\', 200);
        </script>';

// TO DO add html:
	$pluginHtml = $html . $html_js;

	return $pluginHtml;
    }

    /*
      function getHtmlInvoice($method, $cart, $vendor_currency, $klarna_paymentmethod) {
      if (!class_exists('Klarna_invoice'))
      require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarna_invoice.php');
      if (!class_exists('KlarnaVm2API'))
      require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarna_vm2api.php');
      $invoice = new klarna_invoice($method, $cart, $vendor_currency);
      $klarna_pm = $invoice->invoice($method);
      return $this->renderByLayout('displaypayment', array('klarna_pm' => $klarna_pm, 'virtuemart_paymentmethod_id' => $method->virtuemart_paymentmethod_id, 'klarna_paymentmethod' => $klarna_paymentmethod));
      }

      function getHtmlPartPay($method, $cart, $vendor_currency, $klarna_paymentmethod) {
      if (!class_exists('Klarna_partpay'))
      require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarna_partpay.php');
      if (!class_exists('klarnahandler'))
      require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'KlarnaHandler.php');
      if (!class_exists('KlarnaAPI'))
      require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarnaapi.php');
      if (!class_exists('KlarnaVm2API'))
      require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarna_vm2api.php');
      $partPay = new klarna_partpay($method, $cart, $vendor_currency);
      if ($klarna_pm = $partPay->partPay($method, $cart)) {
      return $this->renderByLayout('displaypayment', array('klarna_pm' => $klarna_pm, 'virtuemart_paymentmethod_id' => $method->virtuemart_paymentmethod_id, 'klarna_paymentmethod' => $klarna_paymentmethod));
      } else
      return null;
      }

      function getHtmlSpecCamp($method, $cart, $vendor_currency, $klarna_paymentmethod) {
      if (!class_exists('Klarna_speccamp'))
      require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarna_speccamp.php');
      $specCamp = new klarna_speccamp($method, $cart, $vendor_currency);
      if ($klarna_pm = $specCamp->specCamp($method, $cart)) {
      return $this->renderByLayout('displaypayment', array('klarna_pm' => $klarna_pm, 'virtuemart_paymentmethod_id' => $method->virtuemart_paymentmethod_id, 'klarna_paymentmethod' => $klarna_paymentmethod));
      }
      return null;
      }
     */

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

    function getKlarnaSessionData() {
	$session = JFactory::getSession();
	$sessionKlarna = $session->get('Klarna', 0, 'vm');
	if ($sessionKlarna) {
	    $sessionKlarnaData = unserialize($sessionKlarna);
	    return $sessionKlarnaData;
	}
	return null;
    }

    function checkCountryCondition($method, $country_code, $cart) {

	$active_country = "klarna_active_" . strtolower($country_code);
	if (empty($country_code)) {
	    $app = JFactory::getApplication();
	    $msg = JText::_('VMPAYMENT_KLARNA_GET_SWEDISH_ADDRESS');
	    $country_code = "swe";
	    vmWarn($msg);
	    //return false;
	} else if (!$method->$active_country) {
	    return false;
	}
	// convert price in euro
	$currency = CurrencyDisplay::getInstance();
	//$euro_currency_id = ShopFunctions::getCurrencyByName( 'EUR');
	$price = KlarnaHandler::convertPrice($cart->pricesUnformatted['salesPrice'], 'EUR');

	if (strtolower($country_code) == 'nld' && $price > 250) {
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

    /*
     * @depredecated
     */

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
	$testing = true;
	if (!($method = $this->getVmPluginMethod($order['details']['BT']->virtuemart_paymentmethod_id))) {
	    return null; // Another method was selected, do nothing
	}
	if (!$this->selectedThisElement($method->payment_element)) {
	    return false;
	}
	if (!class_exists('VirtueMartModelOrders'))
	    require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );

	$sessionKlarnaData = $this->getKlarnaSessionData();

	try {
	    $result = KlarnaHandler::addTransaction($method, $cart, $order);
	} catch (Exception $e) {
	    $log = $e->getMessage();
	    vmError($e->getMessage() . ' #' . $e->getCode());
	    //KlarnaHandler::redirectPaymentMethod('error', $e->getMessage() . ' #' . $e->getCode());
	}
	vmdebug('addTransaction result', $result);
	// Delete all Klarna data
	//unset($sessionKlarnaData->KLARNA_DATA, $_SESSION['SSN_ADDR']);
	$modelOrder = VmModel::getModel('orders');
	if ($result['status_code'] == KlarnaFlags::DENIED) {
	    $order['customer_notified'] = 0;
	    $order['order_status'] = $method->status_denied;
	    $order['comments'] = JText::sprintf('VMPAYMENT_KLARNA_PAYMENT_KLARNA_STATUS_DENIED');
	    if ($method->remove_order) {
		$order['comments'] .= "<br />" . $result['status_text'];
	    }
	    $modelOrder->updateStatusForOneOrder($order['details']['BT']->virtuemart_order_id, $order, true);
	    vmdebug('addTransaction remove order?', $method->remove_order);
	    if ($method->remove_order) {
		$modelOrder->remove(array('virtuemart_order_id' => $virtuemart_order_id));
	    } else {
		$dbValues['order_number'] = $order['details']['BT']->order_number;
		$dbValues['payment_name'] = $this->renderPluginName($method, $order);
		$dbValues['virtuemart_paymentmethod_id'] = $order['details']['BT']->virtuemart_paymentmethod_id;
		$dbValues['order_payment'] = $order['details']['BT']->order_payment;
		$dbValues['klarna_pclass'] = $sessionKlarnaData->KLARNA_DATA['PCLASS'];
		$dbValues['klarna_log'] = $log;
		$dbValues['klarna_status_code'] = $result['status_code'];
		$dbValues['klarna_status_text'] = $result['status_text'];
		$this->storePSPluginInternalData($dbValues);
	    }
	    $app = JFactory::getApplication();
	    $app->enqueueMessage($result['status_text']);
	    $app->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart&task=editpayment'), JText::_('COM_VIRTUEMART_CART_ORDERDONE_DATA_NOT_VALID'));
	} else {
	    $invoiceno = $result[1];
	    if ($invoiceno && is_numeric($invoiceno)) {
		//Get address id used for this order.
		$country = $sessionKlarnaData->KLARNA_DATA['COUNTRY'];
		// $lang = KlarnaHandler::getLanguageForCountry($method, KlarnaHandler::convertToThreeLetterCode($country));
		// $d['order_payment_name'] = $kLang->fetch('MODULE_INVOICE_TEXT_TITLE', $lang);
		// Add a note in the log
		$log = Jtext::sprintf('VMPAYMENT_KLARNA_INVOICE_CREATED_SUCCESSFULLY', $invoiceno);

// Prepare data that should be stored in the database
		$dbValues['order_number'] = $order['details']['BT']->order_number;
		$dbValues['payment_name'] = $this->renderPluginName($method, $order);
		$dbValues['virtuemart_paymentmethod_id'] = $order['details']['BT']->virtuemart_paymentmethod_id;
		$dbValues['order_payment'] = $order['details']['BT']->order_payment;
		$dbValues['order_payment_tax'] = $order['details']['BT']->order_payment_tax;
		$dbValues['klarna_pclass'] = $sessionKlarnaData->KLARNA_DATA['PCLASS'];
		$dbValues['klarna_invoice_no'] = $invoiceno;
		$dbValues['klarna_log'] = $log;
		$dbValues['klarna_eid'] = $result['eid'];
		$dbValues['klarna_status_code'] = $result['status_code'];
		$dbValues['klarna_status_text'] = $result['status_text'];

		$this->storePSPluginInternalData($dbValues);

		/*
		 * Klarna's order status
		 *  Integer - 1,2 or 3.
		 *  1 = OK: KlarnaFlags::ACCEPTED
		 *  2 = Pending: KlarnaFlags::PENDING
		 *  3 = Denied: KlarnaFlags::DENIED
		 */
		if ($result['status_code'] == KlarnaFlags::PENDING) {
		    /* if Klarna's order status is pending: add it in the history */
		    /* The order is under manual review and will be accepted or denied at a later stage.
		      Use cronjob with checkOrderStatus() or visit Klarna Online to check to see if the status has changed.
		      You should still show it to the customer as it was accepted, to avoid further attempts to fraud. */
		    $order['order_status'] = $method->status_pending;
		} else {
		    $order['order_status'] = $method->status_success;
		}
		$order['customer_notified'] = 1;
		$order['comments'] = $log;
		$modelOrder->updateStatusForOneOrder($order['details']['BT']->virtuemart_order_id, $order, true);

		$html = $this->renderByLayout('orderdone', array('payment_name' => $dbValues['payment_name'], 'klarna_invoiceno' => $invoiceno));

		//We delete the old stuff
		if (!$testing) {
		    $session->clear('Klarna', 'vm');
		    $cart->emptyCart();
		}

		JRequest::setVar('html', $html);

		return true;
	    } else {
		vmError('Error with invoice number');
	    }
	}
    }

    function plgVmOnUserInvoice($orderDetails, &$data) {
	if (!($method = $this->getVmPluginMethod($orderDetails['virtuemart_paymentmethod_id']))) {
	    return null; // Another method was selected, do nothing
	}
	if (!$this->selectedThisElement($method->payment_element)) {
	    return false;
	}
	if (!($payments = $this->_getKlarnaInternalData($orderDetails['virtuemart_order_id']) )) {
	    vmError(JText::sprintf('VMPAYMENT_KLARNA_ERROR_NO_DATA', $orderDetails['virtuemart_order_id']));
	    return null;
	}
	if (!($klarna_invoice_no = $this->_getKlarnaInvoiceNo($payments) )) {
	    return null;
	}
	$data['invoice_number'] = $klarna_invoice_no;
    }

    function plgVmgetPaymentCurrency($virtuemart_paymentmethod_id, &$paymentCurrencyId) {

	if (!($method = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
	    return null; // Another method was selected, do nothing
	}
	if (!$this->selectedThisElement($method->payment_element)) {
	    return false;
	}
	$paymentCurrencyId = $this->getKlarnaPaymentCurrency($method);
    }

    function getKlarnaPaymentCurrency(&$method) {
	if (!class_exists('VirtueMartCart'))
	    require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
	$cart = VirtueMartCart::getCart();

	$this->_getCartAddressCountryCode($cart, $country, $countrId);
	$cData = KlarnaHandler::countryData($method, $country);
	return shopFunctions::getCurrencyIDByName($cData['currency_code']);
    }

    function plgVmOnPaymentResponseReceived(&$html) {
	return null;
    }

    function plgVmOnUserPaymentCancel() {
	return null;
    }

    /*
     *
     * An order gets cancelled, because order status='X'
     */

    function plgVmOnCancelPayment($order, $old_order_status) {
	if (!$this->selectedThisByMethodId($order->virtuemart_paymentmethod_id)) {
	    return null; // Another method was selected, do nothing
	}

	if (!($method = $this->getVmPluginMethod($order->virtuemart_paymentmethod_id))) {
	    return null; // Another method was selected, do nothing
	}
	if (!($payments = $this->_getKlarnaInternalData($order->virtuemart_order_id) )) {
	    vmError(JText::sprintf('VMPAYMENT_KLARNA_ERROR_NO_DATA', $order->virtuemart_order_id));
	    return null;
	}
	// Status code is === 3==> active invoice. Cannot be be deleted
	// the invoice is active
	if ($order->order_status == $method->status_success) {
	    if ($invNo = $this->_getKlarnaInvoiceNo($payments)) {
		$country = $this->_getOrderAddressCountryCode($order->virtuemart_order_id);
		$klarna = new Klarna_virtuemart();
		$cData = KlarnaHandler::countryData($method, $country);
		$mode = KlarnaHandler::getKlarnaMode($method);
		$klarna->config($cData['eid'], $cData['secret'], $cData['country_code'], null, $cData['currency_code'], $mode);

		try {
		    //remove a passive invoice from Klarna.
		    $result = $klarna->deleteInvoice($invNo);
		    if ($result) {
			$message = Jtext::_('VMPAYMENT_KLARNA_INVOICE_DELETED', $invNo);
		    } else {
			$message = Jtext::_('VMPAYMENT_KLARNA_INVOICE_NOT_DELETED', $invNo);
		    }
		    $dbValues['order_number'] = $order->order_number;
		    $dbValues['virtuemart_order_id'] = $order->virtuemart_order_id;
		    $dbValues['virtuemart_paymentmethod_id'] = $order->virtuemart_paymentmethod_id;
		    $dbValues['klarna_invoice_no'] = 0; // it has been deleted
		    $dbValues['klarna_pdf_invoice_url'] = 0; // it has been deleted
		    $dbValues['klarna_log'] = $message;
		    $dbValues['klarna_eid'] = $cData['eid'];
		    $this->storePSPluginInternalData($dbValues);
		    VmInfo($message);
		} catch (Exception $e) {
		    $log = $e->getMessage() . " (#" . $e->getCode() . ")";
		    $this->_updateKlarnaInternalData($order, $log, $invNo);
		    VmError($e->getMessage() . " (#" . $e->getCode() . ")");
		    return false;
		}
	    }
	} else {
	    VmError('VMPAYMENT_KLARNA_CANNOT_DELETE');
	    return false;
	}
	return true;
    }

    function _getKlarnaInvoiceNo($payments) {
	$nb = count($payments);
	return $payments[$nb - 1]->klarna_invoice_no;
    }

    function _getKlarnaPlcass($payments) {
	$nb = count($payments);
	return $payments[$nb - 1]->klarna_pclass;
    }

    function _getKlarnaStatusCode($payments) {

	$nb = count($payments);
	return $payments[$nb - 1]->klarna_status_code;
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

	$payment_methodid = JRequest::getInt('payment_methodid');
	$invNo = JRequest::getInt('invNo');
	$country = JRequest::getInt('country');
	KlarnaHandler::checkOrderStatus($payment_methodid, $invNo, $country );
	$vendorId = 0;
	if (!($payment = $this->getDataByOrderId($virtuemart_order_id))) {
	    return;
	}

	$method = $this->getVmPluginMethod($payment->virtuemart_paymentmethod_id);
	if (!$this->selectedThisElement($method->payment_element)) {
	    return false;
	}
 $invNo = JRequest::getInt('invNo');
	$country = JRequest::getInt('country');
	$order_status= KlarnaHandler::checkOrderStatus($payment_methodid, $invNo, $country );

	$order['customer_notified'] = 1;

	if ($order_status == 0) {
	    $order['order_status'] = $method->status_success;
	    $order['comments'] = JText::sprintf('VMPAYMENT_KLARNA_PAYMENT_STATUS_CONFIRMED', $order_number);
	} else {
	    $order['order_status'] = $method->status_canceled;
	}

	$this->logInfo('plgVmOnPaymentNotification return new_status:' . $order['order_status'], 'message');

	$modelOrder->updateStatusForOneOrder($virtuemart_order_id, $order, true);
	//// remove vmcart
	$this->emptyCart($paypal_data['custom']);
	//die();
    }



    /*
     * @author Patrick Kohl
     */

    function plgVmOnSelfCallFE($type, $name, &$render) {


	//Klarna Ajax
	require (JPATH_VMKLARNAPLUGIN . '/klarna/helpers/klarna_ajax.php');

	if (!class_exists('VmModel'))
	    require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'vmmodel.php');
	$model = VmModel::getModel('paymentmethod');
	$payment = $model->getPayment();
	if (!class_exists('vmParameters'))
	    require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'parameterparser.php');
	$parameters = new vmParameters($payment, $payment->payment_element, 'plugin', 'vmpayment');
	$method = $parameters->getParamByName('data');

	$country = JRequest::getWord('country');
	$country = KlarnaHandler::convertToThreeLetterCode($country);

	if (!class_exists('klarna_virtuemart'))
	    require (JPATH_VMKLARNAPLUGIN . '/klarna/helpers/klarna_virtuemart.php');

	$settings = KlarnaHandler::getCountryData($method, $country);

	$klarna = new Klarna_virtuemart();
	$klarna->config($settings['eid'], $settings['secret'], $settings['country'], $settings['language'], $settings['currency'], KlarnaHandler::getKlarnaMode($method), VMKLARNA_PC_TYPE, VMKLARNA_PC_URI, true);

	$SelfCall = new KlarnaAjax($klarna, (int) $settings['eid'], JPATH_VMKLARNAPLUGIN, Juri::base());
	$action = JRequest::getWord('action');

	echo $SelfCall->$action();
	jexit();
    }

    /*
     * @author Patrick Kohl
     */

    function plgVmOnSelfCallBE($type, $name, &$render) {

	// fetches PClasses From XML file
	$call = jrequest::getWord('call');
// 	require (JPATH_VMKLARNAPLUGIN . '/klarna/helpers/selfcall.php');
// 	$SelfCall = new KlarnaSelfCall;
	$this->$call();
// 	jexit();
    }

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
    function plgVmOnShowOrderBEPayment($virtuemart_order_id, $payment_method_id, $order) {

	if (!( $this->selectedThisByMethodId($payment_method_id))) {
	    return null; // Another method was selected, do nothing
	}

	if (!($payments = $this->_getKlarnaInternalData($virtuemart_order_id) )) {
	    // JError::raiseWarning(500, $db->getErrorMsg());
	    return '';
	}

	$html = '<table class="adminlist">' . "\n";
	$html .=$this->getHtmlHeaderBE();

	$code = "klarna_";
	$class = 'class="row1"';
	foreach ($payments as $payment) {

	    $html .= '<tr class="row1"><td>' . JText::_('VMPAYMENT_KLARNA_DATE') . '</td><td align="left">' . $payment->created_on . '</td></tr>';
	    //$html .= $this->getHtmlRow('KLARNA_DATE', "<strong>".$payment->created_on."</strong>", $class);
	    if ($payment->payment_name) {
		$html .= $this->getHtmlRowBE('KLARNA_PAYMENT_NAME', $payment->payment_name);
	    }
	    foreach ($payment as $key => $value) {
		if ($value) {
		    if (substr($key, 0, strlen($code)) == $code) {
			if ($key == 'klarna_pdf_invoice_url' and !empty($value)) {
			    $value = '<a target="_blank" href="' . $value . '">' . JText::_('VMPAYMENT_KLARNA_GET_INVOICE') . '</a>';
			}
			$html .= $this->getHtmlRowBE($key, $value);
		    }
		}
	    }
	}

	$nb = count($payments);
	if ($payments[$nb - 1]->klarna_status_code == KlarnaFlags::PENDING) {
	    if (!class_exists('VirtueMartModelOrders'))
		require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );

	    $country = $this->_getOrderAddressCountryCode($virtuemart_order_id);
	    $invNo = $payments[$nb - 1]->klarna_invoice_no;
	    $checkOrderStatus = JURI::root() . 'administrator/index.php?option=com_virtuemart&view=plugin&type=vmpayment&name=klarna&call=checkOrderStatus&payment_methodid=' . (int) $payment_method_id . '&order_number=' . $order->order_number.'&order_pass=' . $order->order_pass . '&country=' . $country . '&invNo=' . $invNo;

	    $link = '<a href="' . $checkOrderStatus . '">' . JText::_('VMPAYMENT_KLARNA_GET_NEW_STATUS') . '</a>';
	    $html .= $this->getHtmlRowBE('KLARNA_PAYMENT_CHECK_ORDER_STATUS', $link);
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
	if (!($payments = $db->loadObjectList())) {
	    // JError::raiseWarning(500, $db->getErrorMsg());
	    return '';
	}
	return $payments;
    }

    function getCosts(VirtueMartCart $cart, $method, $cart_prices) {
	$this->_getCartAddressCountryCode($cart, $country_code, $countryId);
	return KlarnaHandler::getInvoiceFee($method, $country_code);
    }

    /**
     * Save updated order data to the method specific table
     *
     * @param array $order Form data
     * @return mixed, True on success, false on failures (the rest of the save-process will be
     * skipped!), or null when this method is not actived.

     */
    public function plgVmOnUpdateOrderPayment($order, $old_order_status) {
	if (!$this->selectedThisByMethodId($order->virtuemart_paymentmethod_id)) {
	    return null; // Another method was selected, do nothing
	}

	if (!($method = $this->getVmPluginMethod($order->virtuemart_paymentmethod_id))) {
	    return null; // Another method was selected, do nothing
	}
	if (!($payments = $this->_getKlarnaInternalData($order->virtuemart_order_id) )) {
	    vmError(JText::sprintf('VMPAYMENT_KLARNA_ERROR_NO_DATA', $order->virtuemart_order_id));
	    return null;
	}
	if (($klarnaStatus = $this->_getKlarnaStatusCode($payments)) != KlarnaFlags::ACCEPTED) {
	    VmInfo('VMPAYMENT_KLARNA_STATUS_' . $klarnaStatus);
	    return null;
	}
	if (!($invNo = $this->_getKlarnaInvoiceNo($payments))) {
	    return null;
	}
	// to actiavte the order
	if ($order->order_status == $method->status_shipped) {
	    $country = $this->_getOrderAddressCountryCode($order->virtuemart_order_id);
	    $klarna = new Klarna_virtuemart();
	    $cData = KlarnaHandler::countryData($method, $country);
	    /*
	     * The activateInvoice function is used to activate a passive invoice.
	     * Please note that this function call cannot activate an invoice created in test mode.
	     * It is however possible to manually activate that type of invoices.
	     */

	    $mode = KlarnaHandler::getKlarnaMode($method);
	    $klarna->config($cData['eid'], $cData['secret'], $cData['country_code'], null, $cData['currency_code'], $mode);

	    try {
//You can specify a new pclass ID if the customer wanted to change it before you activate.

		$invoice_url = $klarna->activateInvoice($invNo);


		$dbValues['order_number'] = $order->order_number;
		$dbValues['virtuemart_order_id'] = $order->virtuemart_order_id;
		$dbValues['virtuemart_paymentmethod_id'] = $order->virtuemart_paymentmethod_id;
		$dbValues['klarna_invoice_no'] = $invNo;
		$dbValues['klarna_log'] = Jtext::_('VMPAYMENT_KLARNA_ACTIVATE_INVOICE', $invNo);
		$dbValues['klarna_eid'] = $cData['eid'];
		$dbValues['klarna_status_code'] = KLARNA_INVOICE_ACTIVE; // Invoice is active
		$dbValues['klarna_status_text'] = '';
		$dbValues['klarna_pdf_invoice_url'] = $invoice_url;

		$this->storePSPluginInternalData($dbValues);

//The url points to a PDF file for the invoice.
//Invoice activated, proceed accordingly.
	    } catch (Exception $e) {

		$log = $e->getMessage() . " (#" . $e->getCode() . ")";
		$this->_updateKlarnaInternalData($order, $log);
		VmError($e->getMessage() . " (#" . $e->getCode() . ")");
		return false;
	    }

	    return true;
	}



	return null;
    }

    function _updateKlarnaInternalData($order, $log) {
	$dbValues['virtuemart_order_id'] = $order->virtuemart_order_id;
	$dbValues['order_number'] = $order->order_number;
	$dbValues['klarna_log'] = $log;
	$this->storePSPluginInternalData($dbValues);
    }

    /**
     * Create the table for this plugin if it does not yet exist.
     * This functions checks if the called plugin is active one.
     * When yes it is calling the standard method to create the tables
     * @author Val√©rie Isaksen
     *
     */
    function plgVmOnStoreInstallPaymentPluginTable($jplugin_id) {

	$result = $this->onStoreInstallPluginTable($jplugin_id);

	if (jrequest::getvar('redirect') == "no" and $result) {
	    echo ('ok');
	    jexit();
	}
	return $result;
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
	if (!class_exists('KlarnaAddr'))
	    require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'api' . DS . 'klarnaaddr.php');

	$session = JFactory::getSession();
	$sessionKlarna = new stdClass();
	//$post = JRequest::get('post');

	$klarna_paymentmethod = JRequest::getVar('klarna_paymentmethod');
	if ($klarna_paymentmethod == 'klarna_invoice') {
	    $kIndex = "klarna_";
	    $klarna_payment = "klarna_invoice";
	    $sessionKlarna->klarna_option = 'invoice';
	} elseif ($klarna_paymentmethod == 'klarna_partPayment') {
	    $kIndex = "klarna_part_"; //"klarna_partPayment";
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

	$this->_getCartAddressCountryCode($cart, $country3, $countryId, 'country_3_code');
	// $country2=  strtolower($country2);
	if (empty($country3)) {
	    $country3 = "SE";
	    $countryId = ShopFunctions::getCountryIDByName($country3);
	}

	$cData = KlarnaHandler::countryData($method, strtoupper($country3));
	//$country = $cData['country_code']; //KlarnaHandler::convertCountry($method, $country2);
	//$lang = $cData['language_code']; //KlarnaHandler::getLanguageForCountry($method, $country);
	// Get the correct data
	//Removes spaces, tabs, and other delimiters.
	$klarna_pno = preg_replace('/[ \t\,\.\!\#\;\:\r\n\v\f]/', '', JRequest::getVar($kIndex . 'pnum', ''));
	if (empty($klarna_pno)) {
	    $klarna_pno = preg_replace('/[ \t\,\.\!\#\;\:\r\n\v\f]/', '', JRequest::getVar('socialNumber'));
	}
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
	$klarna_company_name = JRequest::getVar('klarna_company_name');

	if (!isset($klarna_pno) || $klarna_pno == '') {
	    $klarna_pno = JRequest::getVar($kIndex . 'birth_day') .
		    JRequest::getVar($kIndex . 'birth_month') .
		    JRequest::getVar($kIndex . 'birth_year');
	}



	// If it is a swedish customer we use the information from getAddress
	if (strtolower($cData['country_code']) == "se") {

	    $swedish_addresses = klarnaHandler::getAddresses($klarna_pno, $method);
	    if (empty($swedish_addresses)) {
		VmInfo('VMPAYMENT_KLARNA_NO_GETADDRESS');
	    }
	    //This example only works for GA_GIVEN.
	    foreach ($swedish_addresses as $index => $addr) {
		if ($addr->isCompany) {
		    $klarna_company_name = $addr->getCompanyName();
		    $klarna_street = $addr->getStreet();
		    $klarna_zip = $addr->getZipCode();
		    $klarna_city = $addr->getCity();
		    $klarna_country = $addr->getCountryCode();
		} else {
		    $klarna_first_name = $addr->getFirstName();
		    $klarna_last_name = $addr->getLastName();
		    $klarna_street = $addr->getStreet();
		    $klarna_zip = $addr->getZipCode();
		    $klarna_city = $addr->getCity();
		    $klarna_country = $addr->getCountryCode();
		}
	    }

	    /*

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
	     * */
	}

	if ($cart->ST == 0 or empty($cart->ST)) {
	    $prefix = 'shipto_';
	    // Update the Shipping Address to what is specified in the register.
	    $update_data = array(
		$prefix . 'address_type_name' => 'Klarna',
		$prefix . 'company' => html_entity_decode($klarna_company_name),
		$prefix . 'first_name' => html_entity_decode($klarna_first_name),
		$prefix . 'last_name' => html_entity_decode($klarna_last_name),
		$prefix . 'address_1' => html_entity_decode($klarna_street) .
		(isset($klarna_house_no) ? ' ' . $klarna_house_no : '') .
		(isset($klarna_house_ext) ? ' ' . $klarna_house_ext : ''),
		$prefix . 'zip' => html_entity_decode($klarna_zip),
		$prefix . 'city' => html_entity_decode($klarna_city),
		$prefix . 'virtuemart_country_id' => $countryId,
		$prefix . 'state' => '-',
		$prefix . 'phone_1' => $klarna_phone,
		$prefix . 'user_email' => $klarna_email,
		'address_type' => 'ST'
	    );


	   // if (false) {
		$cart->saveAddressInCart($update_data, $update_data['address_type']);
		vmInfo(JText::_('VMPAYMENT_KLARNA_ADDRESS_UPDATED_NOTICE'));
	    //}
	}

	// Store the Klarna data in a session variable so
	// we can retrevie it later when we need it
	$sessionKlarna->KLARNA_DATA = array(
	    'PNO' => $klarna_pno,
	    'FIRST_NAME' => $klarna_first_name,
	    'LAST_NAME' => $klarna_last_name,
	    'PHONE' => $klarna_phone,
	    'EMAIL' => $klarna_email,
	    'PCLASS' => ($klarna_paymentmethod == 'klarna_invoice' ? -1 : intval(JRequest::getVar($kIndex . "paymentPlan"))), //???
	    'STREET' => $klarna_street,
	    'ZIP' => $klarna_zip,
	    'CITY' => $klarna_city,
	    'COUNTRY' => $cData['country_code'],
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


// 2 letters small
	//$settings = KlarnaHandler::getCountryData($method, $cart_country2);

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
			    $cData['country_code'], // $settings['country'],
			    $klarna_house_no,
			    $klarna_house_ext
	    );
	} catch (Exception $e) {
	    VmInfo($e->getMessage());
	    return false;
	    //KlarnaHandler::redirectPaymentMethod('message', $e->getMessage());
	}


	if (isset($errors) && count($errors) > 0) {
	    $msg = JText::_('VMPAYMENT_KLARNA_ERROR_TITLE_1');
	    foreach ($errors as $error) {
		$msg .= "<li> -" . $error . "</li>";
	    }
	    $msg .= JText::_('VMPAYMENT_KLARNA_ERROR_TITLE_2');
	    unset($errors);
	    VmError($msg);
	    return false;
	    //KlarnaHandler::redirectPaymentMethod('error', $msg);
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

	return $this->onKlarnaSelectedCalculatePrice($cart, $cart_prices, $cart_prices_name);
    }

    function plgVmDisplayLogin(VirtuemartViewUser $user, &$html) {
	$vendorId = 1;
	if ($this->getPluginMethods($vendorId) === 0) {
	    if (empty($this->_name)) {
		$app = JFactory::getApplication();
		$app->enqueueMessage(JText::_('COM_VIRTUEMART_CART_NO_' . strtoupper($this->_psType)));
		return false;
	    } else {
		return false;
	    }
	}
	//vmdebug('plgVmDisplayLogin', $user);
	//$html = $this->renderByLayout('displaylogin', array('klarna_pm' => $klarna_pm, 'virtuemart_paymentmethod_id' => $method->virtuemart_paymentmethod_id, 'klarna_paymentmethod' => $klarna_paymentmethod));
	$link = JRoute::_('index.php?option=com_virtuemart&view=cart&task=editpayment');
	foreach ($this->methods as $method) {
	    $html = $this->renderByLayout('displaylogin', array('editpayment_link' => $link));
	}
    }

    /*
      function plgVmDisplayLogin(VirtuemartViewUser $user, &$html) {
      if (!class_exists('VirtueMartCart'))
      require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
      $cart = VirtueMartCart::getCart();

      $html=$this->displayListFEPayment($cart);
      }
     */

    public function onKlarnaSelectedCalculatePrice(VirtueMartCart $cart, array &$cart_prices, &$cart_prices_name) {

	$id = $this->_idName;
	if (!($method = $this->selectedThisByMethodId($cart->$id))) {
	    return null; // Another method was selected, do nothing
	}

	if (!($method = $this->getVmPluginMethod($cart->$id) )) {
	    return null;
	}



	$sessionKlarnaData = $this->getKlarnaSessionData();
	if (empty($sessionKlarnaData)) {
	    return '';
	}


	$cart_prices_name = '';
	$cart_prices[$this->_psType . '_tax_id'] = 0;
	$cart_prices['cost'] = 0;

	$this->_getCartAddressCountryCode($cart, $country_code, $countryId, 'country_2_code');
	if (strcasecmp($country_code, $sessionKlarnaData->KLARNA_DATA['COUNTRY']) != 0) {
	    return false;
	}
	$paramsName = $this->_psType . '_params';
	$cart_prices_name = $this->renderPluginName($method);
	if ($sessionKlarnaData->klarna_option == 'invoice') {
	    $this->setKlarnaCartPrices($cart, $cart_prices, $method);
	}
	return true;
    }

    /*
     * update the plugin cart_prices
     *
     * @author Valérie Isaksen
     *
     * @param $cart_prices: $cart_prices['salesPricePayment'] and $cart_prices['paymentTax'] updated. Displayed in the cart.
     * @param $value :   fee
     * @param $tax_id :  tax id
     */

    function setKlarnaCartPrices(VirtueMartCart $cart, &$cart_prices, $method) {
	$this->_getCartAddressCountryCode(null, $country, $countryId);
	$invoice_fee = KlarnaHandler::getInvoiceFee($method, $country);
	$invoice_tax_id = KlarnaHandler::getInvoiceTaxId($method, $country);

	$_psType = ucfirst($this->_psType);
	$cart_prices[$this->_psType . 'Value'] = $invoice_fee;

	$taxrules = array();
	if (!empty($method->tax_id)) {
	    $db = JFactory::getDBO();
	    $q = 'SELECT * FROM #__virtuemart_calcs WHERE `virtuemart_calc_id`="' . $invoice_tax_id . '" ';
	    $db->setQuery($q);
	    $taxrules = $db->loadAssocList();
	}
	if (!class_exists('calculationHelper'))
	    require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'calculationh.php');
	$calculator = calculationHelper::getInstance();
	if (count($taxrules) > 0) {
	    $cart_prices['salesPrice' . $_psType] = $calculator->roundInternal($calculator->executeCalculation($taxrules, $cart_prices[$this->_psType . 'Value']));
	    $cart_prices[$this->_psType . 'Tax'] = $calculator->roundInternal($cart_prices['salesPrice' . $_psType]) - $cart_prices[$this->_psType . 'Value'];
	} else {
	    $cart_prices['salesPrice' . $_psType] = $invoice_fee;
	    $cart_prices[$this->_psType . 'Tax'] = 0;
	}
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

	$session = JFactory::getSession();
	$sessionKlarna = $session->get('Klarna', 0, 'vm');
	if (empty($sessionKlarna)) {
	    return '';
	}
	$sessionKlarnaData = unserialize($sessionKlarna);

	KlarnaHandler::convertCountry($method, $this->_getCartAddressCountryCode(null, $country, $countryId, 'country_2_code'));
	$country = strtolower($country);
	$logo = '<img src="' . JURI::base() . VMKLARNAPLUGINWEBROOT . '/klarna/assets/images/logo';
	switch ($sessionKlarnaData->klarna_option) {
	    case 'invoice':
		$logo .= '/klarna_invoice_' . $country . '.png';
		$method = "";
		break;
	    case 'partpayment':
	    case 'part':
		$logo .= '/klarna_account_' . $country . '.png';
		$method = "";
		break;
	    case 'speccamp':
		$logo .= 'klarna_logo.png';
		$method = JText::_('VMPAYMENT_KLARNA_MODULE_SPEC_TEXT_TITLE');
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

    	$payment_name = $this->onShowOrderFE($virtuemart_order_id, $virtuemart_paymentmethod_id, $payment_name);

    	return false;
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

    function loadScriptAndCss() {
	$assetsPath = VMKLARNAPLUGINWEBROOT . '/klarna/assets/';
	JHTML::stylesheet('style.css', $assetsPath . 'css/', false);
	JHTML::stylesheet('klarna.css', $assetsPath . 'css/', false);
	JHTML::script('klarna_general.js', $assetsPath . 'js/', false);
	JHTML::script('klarnaConsentNew.js', 'http://static.klarna.com/external/js/', false);
	$document = JFactory::getDocument();
	$document->addScriptDeclaration('
		 klarna.ajaxPath = "' . juri::root() . '/index.php?option=com_virtuemart&view=plugin&vmtype=vmpayment&name=klarna";
	');
    }

}

// No closing tag
