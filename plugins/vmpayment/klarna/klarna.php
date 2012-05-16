<?php

defined('_JEXEC') or die();

/**
 *
 * Klarna
 * @author     Valérie Isaksen
 * @version    $Id:
 * @package    VirtueMart
 * @subpackage payment
 * @copyright  Copyright (C) 2004-2008 soeren - All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *             VirtueMart is free software. This version may have been modified pursuant
 *             to the GNU General Public License, and as distributed it includes or
 *             is derivative of works licensed under the GNU General Public License or
 *             other free or open source software licenses.
 *             See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */
if (!class_exists('vmPSPlugin')) {
	require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');
}

if (JVM_VERSION === 2) {
	require (JPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'klarna' . DS . 'klarna' . DS . 'helpers' . DS . 'define.php');
} else {
	require (JPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'klarna' . DS . 'helpers' . DS . 'define.php');
}
if (!class_exists('Klarna')) {
	require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'api' . DS . 'klarna.php');
}
if (!class_exists('klarna_virtuemart')) {
	require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarna_virtuemart.php');
}
if (!class_exists('PCStorage')) {
	require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'api' . DS . 'pclasses' . DS . 'storage.intf.php');
}

if (!class_exists('KlarnaConfig')) {
	require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'api' . DS . 'klarnaconfig.php');
}
if (!class_exists('KlarnaPClass')) {
	require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'api' . DS . 'klarnapclass.php');
}
if (!class_exists('KlarnaCalc')) {
	require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'api' . DS . 'klarnacalc.php');
}

if (!class_exists('KlarnaHandler')) {
	require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarnahandler.php');
}

require_once (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'api' . DS . 'transport' . DS . 'xmlrpc-3.0.0.beta' . DS . 'lib' . DS . 'xmlrpc.inc');
require_once (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'api' . DS . 'transport' . DS . 'xmlrpc-3.0.0.beta' . DS . 'lib' . DS . 'xmlrpc_wrappers.inc');

if (is_file(VMKLARNA_CONFIG_FILE)) {
	require_once (VMKLARNA_CONFIG_FILE);
}

class plgVmPaymentKlarna extends vmPSPlugin {

	// instance of class
	public static $_this = false;

	function __construct(& $subject, $config) {

		parent::__construct($subject, $config);

		$this->_loggable   = true;
		$this->tableFields = array_keys($this->getTableSQLFields());
		$this->_tablepkey  = 'id';
		$this->_tableId    = 'id';
		$varsToPush        = $this->getVarsToPush();
		$this->setConfigParameterable($this->_configTableFieldName, $varsToPush);

		$jlang = JFactory::getLanguage();
		$jlang->load('plg_vmpayment_klarna', JPATH_ADMINISTRATOR, 'en-GB', true);
		$jlang->load('plg_vmpayment_klarna', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
		$jlang->load('plg_vmpayment_klarna', JPATH_ADMINISTRATOR, NULL, true);
	}

	public function getVmPluginCreateTableSQL() {
		return $this->createTableSQL('Payment Klarna Table');
	}

	function getTableSQLFields() {

		$SQLfields = array(
			'id'                          => 'int(11) UNSIGNED NOT NULL AUTO_INCREMENT',
			'virtuemart_order_id'         => 'int(1) UNSIGNED',
			'order_number'                => ' char(64)',
			'virtuemart_paymentmethod_id' => 'mediumint(1) UNSIGNED',
			'payment_name'                => 'varchar(5000)',
			'payment_order_total'         => 'decimal(15,5) NOT NULL DEFAULT \'0.00000\'',
			'payment_fee'                 => 'decimal(10,2)',
			'tax_id'                      => 'smallint(1)',
			'klarna_eid'                  => 'int(10)',
			'klarna_status_code'          => 'tinyint(4)',
			'klarna_status_text'          => 'varchar(255)',
			'klarna_invoice_no'           => 'varchar(255)',
			'klarna_log'                  => 'varchar(255)',
			'klarna_pclass'               => 'int(1)',
			'klarna_pdf_invoice'          => 'varchar(512)',
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
		if (!class_exists('klarna_productPrice')) {
			require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarna_productprice.php');
		}
		if (!class_exists('VirtueMartCart')) {
			require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
		}
		$cart = VirtueMartCart::getCart();

		foreach ($this->methods as $method) {
			$cData = KlarnaHandler::getcData($method, $this->getCartAddress($cart, $type, false));
			if ($cData) {
				$productPrice = new klarna_productPrice($cData);
				if ($productViewData = $productPrice->showProductPrice($product)) {
					$productDisplayHtml = $this->renderByLayout('productprice_layout', $productViewData, $method->payment_element, 'payment');
					$productDisplay[]   = $productDisplayHtml;
				}
			}
		}
		return true;
	}

	/*
*
*/

	function _getCartAddressCountryCode(VirtueMartCart $cart = NULL, &$countryCode, &$countryId, $fld = 'country_3_code') {
		if ($cart == '') {
			if (!class_exists('VirtueMartCart')) {
				require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
			}
			$cart = VirtueMartCart::getCart();
		}

		$address = $this->getCartAddress($cart, $type, false);
		if (!isset($address['virtuemart_country_id']) or empty($address['virtuemart_country_id'])) {
			if ($fld == 'country_3_code') {
				$countryCode = 'swe';
			} else {
				$countryCode = 'se';
			}
			$countryId = ShopFunctions::getCountryIDByName($countryCode);
		} else {
			$countryId   = $address['virtuemart_country_id'];
			$countryCode = shopFunctions::getCountryByID($address['virtuemart_country_id'], $fld);
		}
	}

	function getCartAddress($cart, &$type, $STsameAsBT = true) {
		if (VMKLARNA_SHIPTO_SAME_AS_BILLTO) {
			$st   = $cart->BT;
			$type = 'BT';
			if ($STsameAsBT and $cart->ST and !$cart->STsameAsBT) {
				vmInfo(JText::_('VMPAYMENT_KLARNA_SHIPTO_SAME_AS_BILLTO'));
				$cart->STsameAsBT = 1;
				$cart->setCartIntoSession();
			}
		} elseif ($cart->BT == 0 or empty($cart->BT)) {
			$st   = $cart->BT;
			$type = 'BT';
		} else {
			$st   = $cart->ST;
			$type = 'ST';
		}
		return $st;
	}

	function _getCartAddressCountryId(VirtueMartCart $cart, $fld = 'country_3_code') {
		$address = $this->getCartAddress($cart, $type, false);
		if (!isset($address['virtuemart_country_id'])) {
			return NULL;
		}
		return $address['virtuemart_country_id'];
	}

	function getCountryCodeByOrderId($virtuemart_order_id, $fld = 'country_3_code') {
		$db = JFactory::getDBO();
		$q  = 'SELECT `virtuemart_country_id`,  `address_type` FROM #__virtuemart_order_userinfos  WHERE virtuemart_order_id=' . $virtuemart_order_id;

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
	 * This event is fired to display the plugin methods in the cart (edit shipment/payment) for example
	 *
	 * @param object  $cart     Cart object
	 * @param integer $selected ID of the method selected
	 * @return boolean True on success, false on failures, null when this plugin was not selected.
	 *         On errors, JError::raiseWarning (or JError::raiseError) must be used to set a message.
	 *
	 * @author Valerie Isaksen
	 */
	public function plgVmDisplayListFEPayment(VirtueMartCart $cart, $selected = 0, &$htmlIn) {

		$html = $this->displayListFEPayment($cart, $selected);
		if (!empty($html)) {
			$htmlIn[] = $html;
		}
	}

	/*
* @param $cart
*/

	protected function displayListFEPayment(VirtueMartCart $cart, $selected) {
		if (!class_exists('Klarna_payments')) {
			require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarna_payments.php');
		}
		if (!class_exists('KlarnaVm2API')) {
			require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarna_vm2api.php');
		}
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
	 * For each active methods, get the Payment form
* @param $cart
	 * @param $method
*/

	protected function getListFEPayment(VirtueMartCart $cart, $method) {

		$currency_code = ShopFunctions::getCurrencyByID($cart->pricesCurrency, 'currency_code_3');
		$this->_getCartAddressCountryCode($cart, $country_code, $countryId);
		if (!($cData = $this->checkCountryCondition($method, $country_code, $cart))) {
			return NULL;
		}

		$pclasses = KlarnaHandler::getPClasses(NULL, $country_code, KlarnaHandler::getKlarnaMode($method), $cData);
		$this->getNbPClasses($pclasses, $specCamp, $partPay);
		$sessionKlarnaData = $this->getKlarnaSessionData();

		$klarna_paymentmethod = "";
		if (isset($sessionKlarnaData->KLARNA_DATA['klarna_paymentmethod'])) {
			$klarna_paymentmethod = $sessionKlarnaData->KLARNA_DATA['klarna_paymentmethod'];
		}

		$html           = '';
		$payments       = new klarna_payments($cData, KlarnaHandler::getShipToAddress($cart));
		$payment_params = $payments->invoice($method);
		$payment_form   = $this->renderByLayout('payment_form', array(
			'checkout' => $payment_params['fields']['checkout'],
			'input'    => $payment_params['fields']['input'],
			'value'    => $payment_params['fields']['value'],
			'setup'    => $payment_params['fields']['setup'],
			'sType'    => $payment_params['fields']['sType'],
			'pClasses' => $payment_params['pClasses']), 'klarna', 'payment');
		$html .= $this->renderByLayout('displaypayment', array(
			'stype'                         => 'invoice',
			'id'                            => $payment_params['id'],
			'module'                        => $payment_params['module'],
			'klarna_form'                   => $payment_form,
			'virtuemart_paymentmethod_id'   => $method->virtuemart_paymentmethod_id,
			'klarna_paymentmethod'          => $klarna_paymentmethod));
		if ($partPay > 0) {
			if ($payment_params = $payments->partPay($cart)) {
				$payment_form = $this->renderByLayout('payment_form', array(
					'checkout' => $payment_params['fields']['checkout'],
					'input'    => $payment_params['fields']['input'],
					'value'    => $payment_params['fields']['value'],
					'setup'    => $payment_params['fields']['setup'],
					'sType'    => $payment_params['fields']['sType'],
					'pClasses' => $payment_params['pClasses']), 'klarna', 'payment');
				$html .= $this->renderByLayout('displaypayment', array(
					'stype'                         => 'part',
					'id'                            => $payment_params['id'],
					'module'                        => $payment_params['module'],
					'klarna_form'                   => $payment_form,
					'virtuemart_paymentmethod_id'   => $method->virtuemart_paymentmethod_id,
					'klarna_paymentmethod'          => $klarna_paymentmethod));
			}
		}
		if ($specCamp > 0) {
			if ($payment_params = $payments->specCamp($cart)) {
				$payment_form = $this->renderByLayout('payment_form', array(
					'checkout' => $payment_params['fields']['checkout'],
					'input'    => $payment_params['fields']['input'],
					'value'    => $payment_params['fields']['value'],
					'setup'    => $payment_params['fields']['setup'],
					'sType'    => $payment_params['fields']['sType'],
					'pClasses' => $payment_params['pClasses']), 'klarna', 'payment');
				$html .= $this->renderByLayout('displaypayment', array(
					'stype'                         => 'spec',
					'id'                            => $payment_params['id'],
					'module'                        => $payment_params['module'],
					'klarna_form'                   => $payment_form,
					'virtuemart_paymentmethod_id'   => $method->virtuemart_paymentmethod_id,
					'klarna_paymentmethod'          => $klarna_paymentmethod));
			}
		}

		// TO DO add html:
		$pluginHtml = $html;

		return $pluginHtml;
	}

	/*
* Count the number of Payment Classes: Partial Payments, and Special campaigns
*/
	function getNbPClasses($pClasses, &$specCamp, &$partPay) {

		$specCamp = 0;
		$partPay  = 0;
		foreach ($pClasses as $pClass) {
			if ($pClass->getType() == KlarnaPClass::SPECIAL) {
				$specCamp += 1;
			}
			if ($pClass->getType() == KlarnaPClass::CAMPAIGN ||
				$pClass->getType() == KlarnaPClass::ACCOUNT ||
				$pClass->getType() == KlarnaPClass::FIXED ||
				$pClass->getType() == KlarnaPClass::DELAY
			) {
				$partPay += 1;
			}
		}
	}

	function getKlarnaSessionData() {
		$session       = JFactory::getSession();
		$sessionKlarna = $session->get('Klarna', 0, 'vm');
		if ($sessionKlarna) {
			$sessionKlarnaData = unserialize($sessionKlarna);
			return $sessionKlarnaData;
		}
		return NULL;
	}

	function checkCountryCondition($method, $country_code, $cart) {
		if (!class_exists('CurrencyDisplay')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');
		}
		$active_country = "klarna_active_" . strtolower($country_code);
		if (!isset($method->$active_country) or !$method->$active_country) {
			return false;
		}
		if (empty($country_code)) {
			$msg          = JText::_('VMPAYMENT_KLARNA_GET_SWEDISH_ADDRESS');
			$country_code = "swe";
			vmWarn($msg);
			//return false;
		}
		// convert price in euro
		//$euro_currency_id = ShopFunctions::getCurrencyByName( 'EUR');
		$price = KlarnaHandler::convertPrice($cart->pricesUnformatted['salesPrice'], 'EUR');

		if (strtolower($country_code) == 'nld' && $price > 250) {
			// We can't show our payment options for Dutch customers
			// if price exceeds 250 euro. Will be replaced with ILT in
			// the future.
			return false;
		}
		// Get the country settings
		if (!class_exists('KlarnaHandler')) {
			require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarnahandler.php');
		}

		$cData = KlarnaHandler::getCountryData($method, $country_code);
		if ($cData['eid'] == '' || $cData['eid'] == 0) {
			return false;
		}


		return $cData;
	}

	function plgVmConfirmedOrder($cart, $order) {

		if (!($method = $this->getVmPluginMethod($order['details']['BT']->virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($method->payment_element)) {
			return false;
		}
		if (!class_exists('VirtueMartModelOrders')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php');
		}

		$sessionKlarnaData = $this->getKlarnaSessionData();

		try {
			$result = KlarnaHandler::addTransaction($method, $order, $sessionKlarnaData->KLARNA_DATA['pclass']);
		} catch (Exception $e) {
			$log = $e->getMessage();
			vmError($e->getMessage() . ' #' . $e->getCode());
			//KlarnaHandler::redirectPaymentMethod('error', $e->getMessage() . ' #' . $e->getCode());
		}
		//vmdebug('addTransaction result', $result);
		// Delete all Klarna data
		//unset($sessionKlarnaData->KLARNA_DATA, $_SESSION['SSN_ADDR']);
		$shipTo     = KlarnaHandler::getShipToAddress($cart);
		$modelOrder = VmModel::getModel('orders');
		if ($result['status_code'] == KlarnaFlags::DENIED) {
			$order['customer_notified'] = 0;
			$order['order_status']      = $method->status_denied;
			$order['comments']          = JText::sprintf('VMPAYMENT_KLARNA_PAYMENT_KLARNA_STATUS_DENIED');
			if ($method->delete_order) {
				$order['comments'] .= "<br />" . $result['status_text'];
			}
			$modelOrder->updateStatusForOneOrder($order['details']['BT']->virtuemart_order_id, $order, true);
			vmdebug('addTransaction remove order?', $method->delete_order);
			if ($method->delete_order) {
				$modelOrder->remove(array('virtuemart_order_id' => $order['details']['BT']->virtuemart_order_id));
			} else {
				$dbValues['order_number']                = $order['details']['BT']->order_number;
				$dbValues['payment_name']                = $this->renderKlarnaPluginName($method, $order['details']['BT']->virtuemart_country_id, $shipTo, $order['details']['BT']->order_total);
				$dbValues['virtuemart_paymentmethod_id'] = $order['details']['BT']->virtuemart_paymentmethod_id;
				$dbValues['order_payment']               = $order['details']['BT']->order_payment;
				$dbValues['klarna_pclass']               = $sessionKlarnaData->KLARNA_DATA['PCLASS'];
				$dbValues['klarna_log']                  = $log;
				$dbValues['klarna_status_code']          = $result['status_code'];
				$dbValues['klarna_status_text']          = $result['status_text'];
				$this->storePSPluginInternalData($dbValues);
			}
			$app = JFactory::getApplication();
			$app->enqueueMessage($result['status_text']);
			$app->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart&task=editpayment'), JText::_('COM_VIRTUEMART_CART_ORDERDONE_DATA_NOT_VALID'));
		} else {
			$invoiceno = $result[1];
			if ($invoiceno && is_numeric($invoiceno)) {
				//Get address id used for this order.
				$country = $sessionKlarnaData->KLARNA_DATA['country'];
				// $lang = KlarnaHandler::getLanguageForCountry($method, KlarnaHandler::convertToThreeLetterCode($country));
				// $d['order_payment_name'] = $kLang->fetch('MODULE_INVOICE_TEXT_TITLE', $lang);
				// Add a note in the log
				$log = Jtext::sprintf('VMPAYMENT_KLARNA_INVOICE_CREATED_SUCCESSFULLY', $invoiceno);

				// Prepare data that should be stored in the database
				$dbValues['order_number']                = $order['details']['BT']->order_number;
				$dbValues['payment_name']                = $this->renderKlarnaPluginName($method, $order['details']['BT']->virtuemart_country_id, $shipTo, $order['details']['BT']->order_total);
				$dbValues['virtuemart_paymentmethod_id'] = $order['details']['BT']->virtuemart_paymentmethod_id;
				$dbValues['order_payment']               = $order['details']['BT']->order_payment;
				$dbValues['order_payment_tax']           = $order['details']['BT']->order_payment_tax;
				$dbValues['klarna_pclass']               = $sessionKlarnaData->KLARNA_DATA['pclass'];
				$dbValues['klarna_invoice_no']           = $invoiceno;
				$dbValues['klarna_log']                  = $log;
				$dbValues['klarna_eid']                  = $result['eid'];
				$dbValues['klarna_status_code']          = $result['status_code'];
				$dbValues['klarna_status_text']          = $result['status_text'];

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
				$order['comments']          = $log;
				$modelOrder->updateStatusForOneOrder($order['details']['BT']->virtuemart_order_id, $order, true);

				$html    = $this->renderByLayout('orderdone', array(
					'payment_name'     => $dbValues['payment_name'],
					'klarna_invoiceno' => $invoiceno));
				$session = JFactory::getSession();
				$session->clear('Klarna', 'vm');
				//We delete the old stuff

				$cart->emptyCart();
				JRequest::setVar('html', $html);
				return true;
			} else {
				vmError('Error with invoice number');
			}
		}
	}

	function plgVmOnUserInvoice($orderDetails, &$data) {
		if (!($method = $this->getVmPluginMethod($orderDetails['virtuemart_paymentmethod_id']))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($method->payment_element)) {
			return NULL;
		}
		$data['invoice_number'] = 'reservedByPayment_' . $orderDetails['order_number']; // Nerver send the invoice via email
	}

	function copyInvoice($klarna_invoice_pdf, &$vm_invoice_name) {

		$path = VmConfig::get('forSale_path', 0);
		if ($path === 0) {
			vmError('No path set to store invoices');
			return false;
		} else {
			$path .= DS . 'invoices' . DS;
			if (!file_exists($path)) {
				vmError('Path wrong to store invoices, folder invoices does not exist ' . $path);
				return false;
			} else {
				if (!is_writable($path)) {
					vmError('Cannot store pdf, directory not writeable ' . $path);
					return false;
				}
			}
		}
		$klarna_invoice      = explode('/', $klarna_invoice_pdf);
		$klarna_invoice_name = $klarna_invoice[count($klarna_invoice) - 1];
		$vm_invoice_name     = 'klarna_' . $klarna_invoice_name;
		$path .= $vm_invoice_name;
		if (file_exists($path)) {
			// invoice has already been copied , don't do it again
			return false;
		}
		$ch = curl_init($klarna_invoice_pdf);
		$timeout=10;
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		$pdf = curl_exec($ch);
		curl_close($ch);
		$f = fopen($path, 'wb');
		if (!$f) {
			vmError('Unable to create output file: ' . $path);
			return false;
		}
		if (fwrite($f, $pdf) === false) {
			vmError('Unable to write output file: ' . $path);
			return false;
		}
		fclose($f);
		return true;
	}

	function plgVmGetPaymentCurrency($virtuemart_paymentmethod_id, &$paymentCurrencyId) {

		if (!($method = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($method->payment_element)) {
			return false;
		}
		$paymentCurrencyId = $this->getKlarnaPaymentCurrency($method);
	}

	function getKlarnaPaymentCurrency(&$method) {
		if (!class_exists('VirtueMartCart')) {
			require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
		}
		$cart = VirtueMartCart::getCart();

		$this->_getCartAddressCountryCode($cart, $country, $countrId);
		$cData = KlarnaHandler::countryData($method, $country);
		return shopFunctions::getCurrencyIDByName($cData['currency_code']);
	}

	function plgVmOnPaymentResponseReceived(&$html) {
		return NULL;
	}

	function plgVmOnUserPaymentCancel() {
		return NULL;
	}

	/*
*
* An order gets cancelled, because order status='X'
*/
	function plgVmOnCancelPayment($order, $old_order_status) {
		if (!$this->selectedThisByMethodId($order->virtuemart_paymentmethod_id)) {
			return NULL; // Another method was selected, do nothing
		}

		if (!($method = $this->getVmPluginMethod($order->virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!($payments = $this->_getKlarnaInternalData($order->virtuemart_order_id))) {
			vmError(JText::sprintf('VMPAYMENT_KLARNA_ERROR_NO_DATA', $order->virtuemart_order_id));
			return NULL;
		}
		// Status code is === 3==> active invoice. Cannot be be deleted
		// the invoice is active
		//if ($order->order_status == $method->status_success) {
		if ($invNo = $this->_getKlarnaInvoiceNo($payments)) {
			//vmDebug('order',$order);return;
			$country = $this->getCountryCodeByOrderId($order->virtuemart_order_id);
			$klarna  = new Klarna_virtuemart();
			$cData   = KlarnaHandler::countryData($method, $country);
			$mode    = KlarnaHandler::getKlarnaMode($method);
			$klarna->config($cData['eid'], $cData['secret'], $cData['country_code'], NULL, $cData['currency_code'], $mode);

			try {
				//remove a passive invoice from Klarna.
				$result = $klarna->deleteInvoice($invNo);
				if ($result) {
					$message = Jtext::_('VMPAYMENT_KLARNA_INVOICE_DELETED', $invNo);
				} else {
					$message = Jtext::_('VMPAYMENT_KLARNA_INVOICE_NOT_DELETED', $invNo);
				}
				$dbValues['order_number']                = $order->order_number;
				$dbValues['virtuemart_order_id']         = $order->virtuemart_order_id;
				$dbValues['virtuemart_paymentmethod_id'] = $order->virtuemart_paymentmethod_id;
				$dbValues['klarna_invoice_no']           = 0; // it has been deleted
				$dbValues['klarna_pdf_invoice']          = 0; // it has been deleted
				$dbValues['klarna_log']                  = $message;
				$dbValues['klarna_eid']                  = $cData['eid'];
				$this->storePSPluginInternalData($dbValues);
				VmInfo($message);
			} catch (Exception $e) {
				$log = $e->getMessage() . " (#" . $e->getCode() . ")";
				$this->_updateKlarnaInternalData($order, $log, $invNo);
				VmError($e->getMessage() . " (#" . $e->getCode() . ")");
				if ($e->getCode() == '8113') {
					VmError('invoice_not_passive');
				}
				return false;
			}
		}

		return true;
	}

	function _getKlarnaInvoiceNo($payments, &$primaryKey = '') {
		$nb         = count($payments);
		$primaryKey = $payments[$nb - 1]->id;
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
* @author Patrick Kohl
*/

	function plgVmOnSelfCallFE($type, $name, &$render) {


		//Klarna Ajax
		require (JPATH_VMKLARNAPLUGIN . '/klarna/helpers/klarna_ajax.php');

		if (!class_exists('VmModel')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'vmmodel.php');
		}
		$model   = VmModel::getModel('paymentmethod');
		$payment = $model->getPayment();
		if (!class_exists('vmParameters')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'parameterparser.php');
		}
		$parameters = new vmParameters($payment, $payment->payment_element, 'plugin', 'vmpayment');
		$method     = $parameters->getParamByName('data');

		$country = JRequest::getWord('country');
		$country = KlarnaHandler::convertToThreeLetterCode($country);

		if (!class_exists('klarna_virtuemart')) {
			require (JPATH_VMKLARNAPLUGIN . '/klarna/helpers/klarna_virtuemart.php');
		}

		$settings = KlarnaHandler::getCountryData($method, $country);

		$klarna = new Klarna_virtuemart();
		$klarna->config($settings['eid'], $settings['secret'], $settings['country'], $settings['language'], $settings['currency'], KlarnaHandler::getKlarnaMode($method), VMKLARNA_PC_TYPE, KlarnaHandler::getKlarna_pc_type(), true);

		$SelfCall    = new KlarnaAjax($klarna, (int)$settings['eid'], JPATH_VMKLARNAPLUGIN, Juri::base());
		$action      = JRequest::getWord('action');
		$jlang       = JFactory::getLanguage();
		$currentLang = substr($jlang->getDefault(), 0, 2);
		$newIso      = JRequest::getWord('newIso');
		if ($currentLang != $newIso) {
			$iso = array(
				"sv" => "sv-SE",
				"da" => "da-DK",
				"en" => "en-GB",
				"de" => "de-DE",
				"nl" => "nl-NL",
				"nb" => "nb-NO",
				"fi" => "fi-FI");
			if (array_key_exists($newIso, $iso)) {
				$jlang->load('plg_vmpayment_klarna', JPATH_ADMINISTRATOR, $iso[$newIso], true);
			}
		}
		echo $SelfCall->$action();
		jexit();
	}

	/*
* @author Patrick Kohl
*/

	function plgVmOnSelfCallBE($type, $name, &$render) {

		// fetches PClasses From XML file
		$call = jrequest::getWord('call');
		$this->$call();
		// 	jexit();
	}

	function getPclasses() {

		$jlang = JFactory::getLanguage();
		$jlang->load('plg_vmpayment_klarna', JPATH_ADMINISTRATOR, 'en-GB', true);
		$jlang->load('plg_vmpayment_klarna', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
		$jlang->load('plg_vmpayment_klarna', JPATH_ADMINISTRATOR, NULL, true);
		// call klarna server for pClasses
		//$methodid = jrequest::getInt('methodid');
		if (!class_exists('VmModel')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'vmmodel.php');
		}
		$model   = VmModel::getModel('paymentmethod');
		$payment = $model->getPayment();
		if (!class_exists('vmParameters')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'parameterparser.php');
		}
		$parameters = new vmParameters($payment, $payment->payment_element, 'plugin', 'vmpayment');
		$data       = $parameters->getParamByName('data');
		// echo "<pre>";print_r($data);
		$json = KlarnaHandler::fetchPClasses($data);
		ob_start();
		require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'pclasses_html.php');
		$json['pclasses'] = ob_get_clean();
		$document         = JFactory::getDocument();
		$document->setMimeEncoding('application/json');
		//echo json_encode($json, true);
		echo json_encode($json);
		jexit();
		// echo result with tmpl ?
	}

	/*
	 * Download Pdf Invoice
* @author Valérie Isaksen
*
*/

	function downloadInvoicePdf() {
		if (!class_exists('VirtueMartModelOrders')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php');
		}

		$payment_methodid = JRequest::getInt('payment_methodid');
		$orderNumber      = JRequest::getString('order_number');
		$orderPass        = JRequest::getString('order_pass');

		if (!($method = $this->getVmPluginMethod($payment_methodid))) {
			return NULL; // Another method was selected, do nothing
		}
		$modelOrder = VmModel::getModel('orders');
		// If the user is not logged in, we will check the order number and order pass
		$virtuemart_order_id = $modelOrder->getOrderIdByOrderPass($orderNumber, $orderPass);
		if (empty($virtuemart_order_id)) {
			VmError('Invalid order_number/password ' . JText::_('COM_VIRTUEMART_RESTRICTED_ACCESS'));
			return 0;
		}
		if (!($payments = $this->_getKlarnaInternalData($virtuemart_order_id))) {
			return '';
		}
		foreach ($payments as $payment) {
			if (!empty($payment->klarna_pdf_invoice)) {
				$path = VmConfig::get('forSale_path', 0);
				$path .= DS . 'invoices' . DS;
				$fileName = $path . $payment->klarna_pdf_invoice;
				break;
			}
		}
		if (file_exists($fileName)) {
			header("Cache-Control: public");
			header("Content-Transfer-Encoding: binary\n");
			header('Content-Type: application/pdf');
			$contentDisposition = 'attachment';
			$agent              = strtolower($_SERVER['HTTP_USER_AGENT']);
			if (strpos($agent, 'msie') !== false) {
				$fileName = preg_replace('/\./', '%2e', $fileName, substr_count($fileName, '.') - 1);
			}

			header("Content-Disposition: $contentDisposition; filename=\"$payment->klarna_pdf_invoice\"");
			$contents = file_get_contents($fileName);
			echo $contents;
		}

		return;
	}

	/*
* @author Valérie Isaksen
*
*/

	function checkOrderStatus() {
		if (!class_exists('VirtueMartModelOrders')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php');
		}

		$payment_methodid = JRequest::getInt('payment_methodid');
		$invNo            = JRequest::getInt('invNo');
		$orderNumber      = JRequest::getString('order_number');
		$orderPass        = JRequest::getString('order_pass');

		if (!($method = $this->getVmPluginMethod($payment_methodid))) {
			return NULL; // Another method was selected, do nothing
		}

		$modelOrder = VmModel::getModel('orders');
		// If the user is not logged in, we will check the order number and order pass
		$orderId = $modelOrder->getOrderIdByOrderPass($orderNumber, $orderPass);
		if (empty($orderId)) {
			VmError('Invalid order_number/password ' . JText::_('COM_VIRTUEMART_RESTRICTED_ACCESS'));
			return 0;
		}

		$country             = $this->getCountryCodeByOrderID($orderId);
		$settings            = KlarnaHandler::countryData($method, $country);
		$mode                = KlarnaHandler::getKlarnaMode($method);
		$klarna_order_status = KlarnaHandler::checkOrderStatus($settings, $mode, $invNo);
		if ($klarna_order_status == KlarnaFlags::ACCEPTED) {
			/* if Klarna's order status is pending: add it in the history */
			/* The order is under manual review and will be accepted or denied at a later stage.
			 Use cronjob with checkOrderStatus() or visit Klarna Online to check to see if the status has changed.
			 You should still show it to the customer as it was accepted, to avoid further attempts to fraud. */
			$order['order_status']      = $method->status_success;
			$order['comments']          = JText::_('VMPAYMENT_KLARNA_PAYMENT_ACCEPTED');
			$order['customer_notified'] = 0;
			$dbValues['klarna_log']     = JText::_('VMPAYMENT_KLARNA_PAYMENT_ACCEPTED');
		} elseif ($klarna_order_status == KlarnaFlags::DENIED) {
			$order['order_status']      = $method->status_canceled;
			$order['customer_notified'] = 0;
			$dbValues['klarna_log']     = JText::_('VMPAYMENT_KLARNA_PAYMENT_NOT_ACCEPTED');
		} else {
			$dbValues['klarna_log']     = $klarna_order_status;
			$order['comments']          = $klarna_order_status;
			$order['customer_notified'] = 0;
		}
		$dbValues['order_number']                = $orderNumber;
		$dbValues['virtuemart_order_id']         = $orderId;
		$dbValues['virtuemart_paymentmethod_id'] = $payment_methodid;
		$dbValues['klarna_invoice_no']           = $invNo;
		$this->storePSPluginInternalData($dbValues);


		$modelOrder->updateStatusForOneOrder($orderId, $order, true);
		$app = JFactory::getApplication();
		$app->redirect('index.php?option=com_virtuemart&view=orders&task=edit&virtuemart_order_id=' . $orderId);
		// 	jexit();
	}

	function _getTablepkeyValue($virtuemart_order_id) {
		$db = JFactory::getDBO();
		$q  = 'SELECT ' . $this->_tablepkey . ' FROM `' . $this->_tablename . '` '
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

		if (!($this->selectedThisByMethodId($payment_method_id))) {
			return NULL; // Another method was selected, do nothing
		}

		if (!($payments = $this->_getKlarnaInternalData($virtuemart_order_id))) {
			// JError::raiseWarning(500, $db->getErrorMsg());
			return '';
		}
		if (!($method = $this->getVmPluginMethod($payment_method_id))) {
			return NULL; // Another method was selected, do nothing
		}
		$html = '<table class="adminlist" width="50%">' . "\n";
		$html .= $this->getHtmlHeaderBE();

		$code  = "klarna_";
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
						if ($key == 'klarna_pdf_invoice' and !empty($value)) {
							$invoicePdfLink = JURI::root() . 'administrator/index.php?option=com_virtuemart&view=plugin&type=vmpayment&name=klarna&call=downloadInvoicePdf&payment_methodid=' . (int)$payment_method_id . '&order_number=' . $order['details']['BT']->order_number . '&order_pass=' . $order['details']['BT']->order_pass;
							$value          = '<a target="_blank" href="' . $invoicePdfLink . '">' . JText::_('VMPAYMENT_KLARNA_DOWNLOAD_INVOICE') . '</a>';
						}
						$html .= $this->getHtmlRowBE($key, $value);
					}
				}
			}
		}

		$nb = count($payments);
		if ($order['details']['BT']->order_status == $method->status_pending) {
			if (!class_exists('VirtueMartModelOrders')) {
				require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php');
			}

			$country          = $this->getCountryCodeByOrderId($virtuemart_order_id);
			$invNo            = $payments[$nb - 1]->klarna_invoice_no;
			$checkOrderStatus = JURI::root() . 'administrator/index.php?option=com_virtuemart&view=plugin&type=vmpayment&name=klarna&call=checkOrderStatus&payment_methodid=' . (int)$payment_method_id . '&order_number=' . $order['details']['BT']->order_number . '&order_pass=' . $order['details']['BT']->order_pass . '&country=' . $country . '&invNo=' . $invNo;

			$link = '<a href="' . $checkOrderStatus . '">' . JText::_('VMPAYMENT_KLARNA_GET_NEW_STATUS') . '</a>';
			$html .= $this->getHtmlRowBE('KLARNA_PAYMENT_CHECK_ORDER_STATUS', $link);
		}
		$html .= '</table>' . "\n";
		return $html;
	}

	function _getKlarnaInternalData($virtuemart_order_id, $order_number = '') {
		$db = JFactory::getDBO();
		$q  = 'SELECT * FROM `' . $this->_tablename . '` WHERE ';
		if ($order_number) {
			$q .= " `order_number` = '" . $order_number . "'";
		} else {
			$q .= ' `virtuemart_order_id` = ' . $virtuemart_order_id;
		}

		$db->setQuery($q);
		if (!($payments = $db->loadObjectList())) {
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
	public function plgVmOnUpdateOrderPayment(&$order, $old_order_status) {
		if (!$this->selectedThisByMethodId($order->virtuemart_paymentmethod_id)) {
			return NULL; // Another method was selected, do nothing
		}

		if (!($method = $this->getVmPluginMethod($order->virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!($payments = $this->_getKlarnaInternalData($order->virtuemart_order_id))) {
			vmError(JText::sprintf('VMPAYMENT_KLARNA_ERROR_NO_DATA', $order->virtuemart_order_id));
			return NULL;
		}

		if (!($invNo = $this->_getKlarnaInvoiceNo($payments))) {
			return NULL;
		}
		// to actiavte the order
		if ($order->order_status == $method->status_shipped) {
			$country = $this->getCountryCodeByOrderId($order->virtuemart_order_id);
			$klarna  = new Klarna_virtuemart();
			$cData   = KlarnaHandler::countryData($method, $country);
			/*
			* The activateInvoice function is used to activate a passive invoice.
			* Please note that this function call cannot activate an invoice created in test mode.
			* It is however possible to manually activate that type of invoices.
			*/

			$mode = KlarnaHandler::getKlarnaMode($method);
			$klarna->config($cData['eid'], $cData['secret'], $cData['country_code'], NULL, $cData['currency_code'], $mode);

			try {
				//You can specify a new pclass ID if the customer wanted to change it before you activate.

				$invoice_url = $klarna->activateInvoice($invNo);
				$this->copyInvoice($invoice_url, $vm_invoice_name);
				$dbValues['order_number']                = $order->order_number;
				$dbValues['virtuemart_order_id']         = $order->virtuemart_order_id;
				$dbValues['virtuemart_paymentmethod_id'] = $order->virtuemart_paymentmethod_id;
				$dbValues['klarna_invoice_no']           = $invNo;
				$dbValues['klarna_log']                  = Jtext::_('VMPAYMENT_KLARNA_ACTIVATE_INVOICE', $invNo);
				$dbValues['klarna_eid']                  = $cData['eid'];
				//$dbValues['klarna_status_code'] = KLARNA_INVOICE_ACTIVE; // Invoice is active
				//$dbValues['klarna_status_text'] = '';
				$dbValues['klarna_pdf_invoice'] = $vm_invoice_name;

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
		return NULL;
	}

	function _updateKlarnaInternalData($order, $log) {
		$dbValues['virtuemart_order_id'] = $order->virtuemart_order_id;
		$dbValues['order_number']        = $order->order_number;
		$dbValues['klarna_log']          = $log;
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
		/*
	   * if the file Klarna.cfg does not exist, then create it
	   */
		$filename = VMKLARNA_CONFIG_FILE;
		if (!JFile::exists($filename)) {
			$fileContents = "<?php defined('_JEXEC') or die();
	define('VMKLARNA_SHIPTO_SAME_AS_BILLTO', '1'); ?>";
			$result       = JFile::write($filename, $fileContents);
			if (!$result) {
				VmInfo(JText::sprintf('VMPAYMENT_KLARNA_CANT_WRITE_CONFIG', $filename, $result));
			}
		}

		$method = $this->getPluginMethod(JRequest::getInt('virtuemart_paymentmethod_id'));

		// we have to chek that the following Shopper fields are there
		$required_shopperfields_vm = Klarnahandler::getKlarnaVMGenericShopperFields();

		$required_shopperfields_bycountry = KlarnaHandler::getKlarnaSpecificShopperFields();

		$userFieldsModel       = VmModel::getModel('UserFields');
		$switches['published'] = true;
		$userFields            = $userFieldsModel->getUserFields('', $switches);
		// TEST that all Vm shopperfields are there
		foreach ($userFields as $userField) {
			$fields_name_vm[] = $userField->name;
		}
		$result                = array_intersect($fields_name_vm, $required_shopperfields_vm);
		$vm_required_not_found = array_diff($required_shopperfields_vm, $result);
		if (count($vm_required_not_found)) {
			VmError(JText::sprintf('VMPAYMENT_KLARNA_REQUIRED_USERFIELDS_NOT_FOUND', implode(", ", $vm_required_not_found)));
		} else {
			VmInfo(JText::_('VMPAYMENT_KLARNA_REQUIRED_USERFIELDS_OK'));
		}
		$klarna_required_not_found = array();
		// TEST that all required Klarna shopper fields are there, if not create them
		foreach ($required_shopperfields_bycountry as $key => $shopperfield_country) {
			$active = 'klarna_active_' . strtolower($key);
			if ($method->$active) {
				$resultByCountry                   = array_intersect($fields_name_vm, $shopperfield_country);
				$klarna_required_country_not_found = array_diff($shopperfield_country, $resultByCountry);
				$klarna_required_not_found         = array_merge($klarna_required_country_not_found, $klarna_required_not_found);
			}
		}
		$klarna_required_not_found = array_unique($klarna_required_not_found, SORT_STRING);
		if (count($klarna_required_not_found)) {
			VmError(JText::sprintf('VMPAYMENT_KLARNA_REQUIRED_USERFIELDS_NOT_FOUND', implode(", ", $klarna_required_not_found)));
		} else {
			VmInfo(JText::_('VMPAYMENT_KLARNA_REQUIRED_USERFIELDS_OK'));
		}
		//vmDebug('plgVmOnStoreInstallPaymentPluginTable', $create_shopperfield);
		/*
	   * TODO: create all required shopperfields
	   *
	   */
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
	 * @author Valérie isaksen
	 *
	 * @param VirtueMartCart $cart: the actual cart
	 * @return null if the payment was not selected, true if the data is valid, error message if the data is not vlaid
	 *
	 */
	public function plgVmOnSelectCheckPayment(VirtueMartCart $cart) {

		if (!$this->selectedThisByMethodId($cart->virtuemart_paymentmethod_id)) {
			return NULL; // Another method was selected, do nothing
		}
		if (!($method = $this->getVmPluginMethod($cart->virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!class_exists('KlarnaAddr')) {
			require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'api' . DS . 'klarnaaddr.php');
		}

		$session       = JFactory::getSession();
		$sessionKlarna = new stdClass();
		//$post = JRequest::get('post');

		$klarnaData_paymentmethod = JRequest::getVar('klarna_paymentmethod', '');
		if ($klarnaData_paymentmethod == 'klarna_invoice') {
			$klarnaData_payment           = "klarna_invoice";
			$sessionKlarna->klarna_option = 'invoice';
		} elseif ($klarnaData_paymentmethod == 'klarna_partPayment') {
			$klarnaData_payment           = "klarna_partpay";
			$sessionKlarna->klarna_option = 'part';
		} elseif ($klarnaData_paymentmethod == 'klarna_speccamp') {
			$klarnaData_payment           = "klarna_speccamp";
			$sessionKlarna->klarna_option = 'spec';
		} else {
			return NULL;
			;
		}

		// Store payment_method_id so we can activate the
		// right payment in case something goes wrong.
		$sessionKlarna->virtuemart_payment_method_id = $cart->virtuemart_paymentmethod_id;
		$sessionKlarna->klarna_paymentmethod         = $klarnaData_paymentmethod;

		$this->_getCartAddressCountryCode($cart, $country3, $countryId, 'country_3_code');
		// $country2=  strtolower($country2);
		if (empty($country3)) {
			$country3  = "SWE";
			$countryId = ShopFunctions::getCountryIDByName($country3);
		}

		$cData = KlarnaHandler::countryData($method, strtoupper($country3));

		$klarnaData            = KlarnaHandler::getDataFromEditPayment();
		$klarnaData['country'] = $cData['country_code'];
		//$country = $cData['country_code']; //KlarnaHandler::convertCountry($method, $country2);
		//$lang = $cData['language_code']; //KlarnaHandler::getLanguageForCountry($method, $country);
		// Get the correct data
		//Removes spaces, tabs, and other delimiters.
		// If it is a swedish customer we use the information from getAddress
		if (strtolower($cData['country_code']) == "se") {
			if (empty($klarnaData['socialNumber'])) {
				VmInfo('VMPAYMENT_KLARNA_MUST_VALID_PNO');
				return false;
			}
			$swedish_addresses = klarnaHandler::getAddresses($klarnaData['socialNumber'], $cData, $method);
			if (empty($swedish_addresses)) {
				VmInfo('VMPAYMENT_KLARNA_NO_GETADDRESS');
				return false;
			}
			//This example only works for GA_GIVEN.
			foreach ($swedish_addresses as   $address) {
				if ($address->isCompany) {
					$klarnaData['company_name'] = $address->getCompanyName();
				} else {
					$klarnaData['first_name'] = $address->getFirstName();
					$klarnaData['last_name']  = $address->getLastName();
				}
				$klarnaData['street']  = $address->getStreet();
				$klarnaData['zip']     = $address->getZipCode();
				$klarnaData['city']    = $address->getCity();
				$klarnaData['country'] = $address->getCountryCode();
				$countryId             = $klarnaData['virtuemart_country_id'] = shopFunctions::getCountryIDByName($klarnaData['country']);
			}
			foreach ($klarnaData as $key => $value) {
				$klarnaData[$key] = mb_convert_encoding($klarnaData[$key], 'UTF-8', 'ISO-8859-1');
			}
		} elseif (!KlarnaHandler::checkDataFromEditPayment($klarnaData)) {
			//VmInfo('VMPAYMENT_KLARNA_MISSING_DATA');
			//return false;
		}
		$st = $this->getCartAddress($cart, $address_type, true);
		if ($address_type == 'BT') {
			$prefix = '';
		} else {
			$prefix = 'shipto_';
		}

		// Update the Shipping Address to what is specified in the register.
		$update_data = array(
			$prefix . 'address_type_name'     => 'Klarna',
			$prefix . 'company'               => $klarnaData['company_name'],
			$prefix . 'first_name'            => $klarnaData['first_name'],
			$prefix . 'middle_name'           => $st['middle_name'],
			$prefix . 'last_name'             => $klarnaData['last_name'],
			$prefix . 'address_1'             => $klarnaData['street'],
			$prefix . 'address_2'             => $klarnaData['house_ext'],
			$prefix . 'house_no'              => $klarnaData['house_no'],
			$prefix . 'zip'                   => html_entity_decode($klarnaData['zip']),
			$prefix . 'city'                  => $klarnaData['city'],
			$prefix . 'virtuemart_country_id' => $countryId, //$klarnaData['virtuemart_country_id'],
			$prefix . 'state'                 => '',
			$prefix . 'phone_1'               => $klarnaData['phone'],
			$prefix . 'phone_2'               => $st['phone_2'],
			$prefix . 'fax'                   => $st['fax'],
			$prefix . 'birthday'              => empty($klarnaData['birthday']) ? $st['birthday'] : $klarnaData['birthday'],
			$prefix . 'social_number'         => empty($klarnaData['pno']) ? $klarnaData['socialNumber'] : $klarnaData['pno'],
			'address_type'                    => $address_type
		);
		if ($address_type == 'BT') {
			$update_data ['email'] = $klarnaData['email'];
		}
		// save address in cart if different
		// 	if (false) {
		$cart->saveAddressInCart($update_data, $update_data['address_type'], true);
		//vmdebug('plgVmOnSelectCheckPayment $cart',$cart);
		//vmInfo(JText::_('VMPAYMENT_KLARNA_ADDRESS_UPDATED_NOTICE'));
		// 	}
		//}
		// Store the Klarna data in a session variable so
		// we can retrevie it later when we need it
		//$klarnaData['pclass'] = ($klarnaData_paymentmethod == 'klarna_invoice' ? -1 : intval(JRequest::getVar($kIndex . "paymentPlan")));
		$klarnaData['pclass'] = ($klarnaData_paymentmethod == 'klarna_invoice' ? -1 : intval(JRequest::getVar("klarna_paymentPlan")));

		$sessionKlarna->KLARNA_DATA = $klarnaData;

		// 2 letters small
		//$settings = KlarnaHandler::getCountryData($method, $cart_country2);

		try {
			$address = new KlarnaAddr(
				$klarnaData['email'],
				$klarnaData['phone'],
				"", //mobile
				$klarnaData['first_name'],
				$klarnaData['last_name'], '',
				$klarnaData['street'],
				$klarnaData['zip'],
				$klarnaData['city'],
				$klarnaData['country'], // $settings['country'],
				$klarnaData['house_no'],
				$klarnaData['house_ext']
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
* plgVmOnSelectedCalculatePricePayment
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

	public function plgVmOnSelectedCalculatePricePayment(VirtueMartCart $cart, array &$cart_prices, &$cart_prices_name) {

		return $this->onKlarnaSelectedCalculatePrice($cart, $cart_prices, $cart_prices_name);
	}

	function plgVmDisplayLogin(VirtuemartViewUser $user, &$html, $from_cart = 'false') {
		// only to display it in the cart, not in list orders view
		if (!$from_cart) {
			return NULL;
		}
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
			$html .= $this->renderByLayout('displaylogin', array('editpayment_link' => $link));
		}
	}


	public function onKlarnaSelectedCalculatePrice(VirtueMartCart $cart, array &$cart_prices, &$cart_prices_name) {

		if (!($method = $this->selectedThisByMethodId($cart->virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}

		if (!($method = $this->getVmPluginMethod($cart->virtuemart_paymentmethod_id))) {
			return NULL;
		}

		$sessionKlarnaData = $this->getKlarnaSessionData();
		if (empty($sessionKlarnaData)) {
			return NULL;
		}

		$cart_prices_name                        = '';
		$cart_prices[$this->_psType . '_tax_id'] = 0;
		$cart_prices['cost']                     = 0;
		//vmdebug('cart prices',  $cart_prices);

		$this->_getCartAddressCountryCode($cart, $country_code, $countryId, 'country_2_code');
		if (strcasecmp($country_code, $sessionKlarnaData->KLARNA_DATA['country']) != 0) {
			return false;
		}
		$paramsName       = $this->_psType . '_params';
		$address          = $this->getCartAddress($cart, $type, false);
		$shipTo           = KlarnaHandler::getShipToAddress($cart);
		$cart_prices_name = $this->renderKlarnaPluginName($method, $address['virtuemart_country_id'], $shipTo, $cart_prices['withTax']);
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
		$this->_getCartAddressCountryCode(NULL, $country, $countryId);
		$invoice_fee    = KlarnaHandler::getInvoiceFee($method, $country);
		$invoice_tax_id = KlarnaHandler::getInvoiceTaxId($method, $country);

		$_psType                               = ucfirst($this->_psType);
		$cart_prices[$this->_psType . 'Value'] = $invoice_fee;

		$taxrules = array();
		if (!empty($invoice_tax_id)) {
			$db = JFactory::getDBO();
			$q  = 'SELECT * FROM #__virtuemart_calcs WHERE `virtuemart_calc_id`="' . $invoice_tax_id . '" ';
			$db->setQuery($q);
			$taxrules = $db->loadAssocList();
		}
		if (!class_exists('calculationHelper')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'calculationh.php');
		}
		$calculator = calculationHelper::getInstance();
		if (count($taxrules) > 0) {
			$cart_prices['salesPrice' . $_psType] = $calculator->roundInternal($calculator->executeCalculation($taxrules, $cart_prices[$this->_psType . 'Value']));
			$cart_prices[$this->_psType . 'Tax']  = $calculator->roundInternal($cart_prices['salesPrice' . $_psType]) - $cart_prices[$this->_psType . 'Value'];
		} else {
			$cart_prices['salesPrice' . $_psType] = $invoice_fee;
			$cart_prices[$this->_psType . 'Tax']  = 0;
		}
	}

	/*
* @param $plugin plugin
*/

	protected function renderKlarnaPluginName($method, $virtuemart_country_id, $shipTo, $total) {
		$return      = '';
		$plugin_name = $this->_psType . '_name';
		$plugin_desc = $this->_psType . '_desc';
		$description = '';

		$return = $this->displayKlarnaLogos($method, $virtuemart_country_id, $shipTo, $total) . ' ';
		if (!empty($method->$plugin_desc)) {
			$description = '<span class="' . $this->_type . '_description">' . $method->$plugin_desc . '</span>';
		}
		$pluginName = $return . '<span class="' . $this->_type . '_name">' . $method->$plugin_name . '</span>' . $description;
		return $pluginName;
	}

	function displayKlarnaLogos($method, $virtuemart_country_id, $shipTo, $total) {

		$session       = JFactory::getSession();
		$sessionKlarna = $session->get('Klarna', 0, 'vm');
		if (empty($sessionKlarna)) {
			return '';
		}
		$sessionKlarnaData                = unserialize($sessionKlarna);
		$address['virtuemart_country_id'] = $virtuemart_country_id;
		$cData                            = KlarnaHandler::getcData($method, $address);
		$country2                         = strtolower(shopFunctions::getCountryByID($virtuemart_country_id, 'country_2_code'));
		switch ($sessionKlarnaData->klarna_option) {
			case 'invoice':
				$image              = '/klarna_invoice_' . $country2 . '.png';
				$klarna_invoice_fee = KlarnaHandler::getInvoiceFeeInclTax($method, $cData['country_code_3']);
				$currency           = CurrencyDisplay::getInstance();
				$display_fee        = $currency->priceDisplay($klarna_invoice_fee);
				$text               = JText::sprintf('VMPAYMENT_KLARNA_INVOICE_TITLE_NO_PRICE', $display_fee);
				break;
			case 'partpayment':
			case 'part':
				$image                            = '/klarna_part_' . $country2 . '.png';
				$address['virtuemart_country_id'] = $virtuemart_country_id;
				$pclasses                         = KlarnaHandler::getPClasses(NULL, $country2, KlarnaHandler::getKlarnaMode($method), $cData);
				if (!class_exists('Klarna_payments')) {
					require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarna_payments.php');
				}
				if (!class_exists('KlarnaVm2API')) {
					require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarna_vm2api.php');
				}
				$payments = new klarna_payments($cData, $shipTo);
				//vmdebug('displaylogos',$cart_prices);
				$totalInPaymentCurrency = KlarnaHandler::convertPrice($total, $cData['currency_code']);
				$text                   = $payments->displayPclass($sessionKlarnaData->KLARNA_DATA['pclass'], $totalInPaymentCurrency); // .' '.$total;
				break;
			case 'speccamp':
				$image = 'klarna_logo.png';
				$text  = JText::_('VMPAYMENT_KLARNA_SPEC_TITLE');
				break;
			default:
				$image = '';
				$text  = '';
				break;
		}

		$html = $this->renderByLayout('payment_cart', array(
			'logo'        => $image,
			'description' => $text));

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
	function plgVmOnCheckAutomaticSelectedPayment(VirtueMartCart $cart, array $cart_prices = array(), &$paymentCounter) {
		$nbMethod = 0;

		if ($this->getPluginMethods($cart->vendorId) === 0) {
			return false;
		}

		foreach ($this->methods as $method) {
			$cData = KlarnaHandler::getcData($method, $this->getCartAddress($cart, $type, false));
			if ($cData) {
				if ($nb = (int)$this->checkCountryCondition($method, $cData['country_code_3'], $cart)) {
					$nbMethod = $nbMethod + $nb;
				}
			}
		}
		$paymentCounter=$paymentCounter+$nbMethod;
		if ($nbMethod == 0) {
			return NULL;
		} else {
			return 0;
		}
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

		if ($this->onShowOrderFE($virtuemart_order_id, $virtuemart_paymentmethod_id, $payment_name)) {
			return false;
		}
		return NULL;
	}

	/**
	 * This method is fired when showing when printing an Order
	 * It displays the the payment method-specific data.
	 *
	 * @param integer $order_number The order Number
	 * @param integer $method_id            method used for this order
	 * @return mixed Null when for payment methods that were not selected, text (HTML) otherwise
	 * @author Valerie Isaksen
	 */
	function plgVmOnShowOrderPrintPayment($order_number, $method_id) {
		return $this->onShowOrderPrint($order_number, $method_id);
	}

	function _getVendorCurrency() {
		if (!class_exists('VirtueMartModelVendor')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'vendor.php');
		}
		$vendor_id       = 1;
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
