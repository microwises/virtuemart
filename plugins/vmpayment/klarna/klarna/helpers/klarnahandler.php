<?php

defined('_JEXEC') or die('Restricted access');

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
jimport('phpxmlrpc.xmlrpc');

class KlarnaHandler {

    function countryData($method, $country) {
	$countryData = array(
	    'NOR' => array(
		'pno_encoding' => 3,
		'language' => 97,
		'language_code' => 'nb',
		'country' => 164,
		'currency' => 1,
		'currency_code' => 'NOK',
		'currency_symbol' => 'kr',
		'country_code' => 'no'),
	    'SWE' => array(
		'pno_encoding' => 2,
		'language' => 138,
		'language_code' => 'sv',
		'country' => 209,
		'country_code' => 'se',
		'currency' => 0,
		'currency_code' => 'SEK',
		'currency_symbol' => 'kr'),
	    'DNK' => array(
		'pno_encoding' => 5,
		'language' => 27,
		'language_code' => 'da',
		'country' => 59,
		'country_code' => 'dk',
		'currency' => 3,
		'currency_code' => 'DKK',
		'currency_symbol' => 'kr',
	    ),
	    'FIN' => array(
		'pno_encoding' => 4,
		'language' => 37,
		'language_code' => 'fi',
		'country' => 73,
		'country_code' => 'fi',
		'currency' => 2,
		'currency_code' => 'EUR',
		'currency_symbol' => '&#8364;'
	    ),
	    'NLD' => array(
		'pno_encoding' => 7,
		'language' => 101,
		'language_code' => 'nl',
		'country' => 154,
		'country_code' => 'nl',
		'currency' => 2,
		'currency_code' => 'EUR',
		'currency_symbol' => '&#8364;',
	    ),
	    'DEU' => array(
		'pno_encoding' => 6,
		'language' => 28,
		'language_code' => 'de',
		'country' => 81,
		'country_code' => 'de',
		'currency' => 2,
		'currency_code' => 'EUR',
		'currency_symbol' => '&#8364;'
		));
	$lower_country = strtolower($country);
	if (key_exists(strtoupper($country), $countryData)) {
	    $cData = $countryData[strtoupper($country)];
	    $eid = 'klarna_merchantid_' . $lower_country;
	    $secret = 'klarna_sharedsecret_' . $lower_country;
	    $invoice_fee = 'klarna_invoicefee_' . $lower_country;
	    $cData['eid'] = $method->$eid;
	    $cData['secret'] = $method->$secret;
	    $cData['invoice_fee'] = (double) $method->$invoice_fee;
	    $cData['country_code_3']=$country;
	    $cData['virtuemart_currency_id'] = ShopFunctions::getCurrencyIDByName($cData['currency_code']);;
	    return $cData;
	} else {
	    return null;
	}
    }

    public function getCountryData($method, $country) {
	//$country = self::convertToThreeLetterCode($country);
	return self::countryData($method, $country);
    }

    public function convertCountry($method, $country) {
	$country_data = self::countryData($method, $country);
	return $country_data['country_code'];
    }

    public function convertCountryInt($method, $country) {
	$country_data = self::countryData($method, $country);
	return $country_data['country'];
    }

    public function convertCountryToCountryNb($method, $country) {
	$country_data = self::countryData($method, $country);
	return $country_data['country'];
    }

    public function getEid($method, $country) {
	//$eid = 'klarna_merchantid_' . strtolower($country) . '';
	//return isset($method->$eid) ? $method->$eid : 0;
	$country = self::convertToThreeLetterCode($country);
	$country_data = self::countryData($method, $country);
	return $country_data['eid'];
    }

    public function getSecret($method, $country) {
	$country = self::convertToThreeLetterCode($country);
	$country_data = self::countryData($method, $country);
	return $country_data['secret'];
    }

    public function getLanguageForCountry($method, $country) {
	$country = self::convertToThreeLetterCode($country);
	$country_data = self::countryData($method, $country);
	return $country_data['language_code'];
    }

    public function getCurrencySymbolForCountry($method, $country) {
	$country_data = self::countryData($method, $country);
	return $country_data['currency_symbol'];
    }

    public function getInvoiceFee($method, $country) {
	$invoice_fee = 'klarna_invoicefee_' . strtolower($country);
	return $method->$invoice_fee;
    }

    public function getInvoiceTaxId($method, $country) {
	$invoice_fee_tax = 'klarna_invoice_tax_id_' . strtolower($country);
	return $method->$invoice_fee_tax;
    }

    public function getInvoiceFeeInclTax($method, $country) {
	$invoice_fee = self::getInvoiceFee($method, $country);
	$invoice_tax_id = self::getInvoiceTaxId($method, $country);

	if (!class_exists('calculationHelper'))
	    require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'calculationh.php');
	if (!class_exists('CurrencyDisplay'))
	    require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');

	if (!class_exists('VirtueMartModelVendor'))
	    require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'vendor.php');
	$vendor_id = 1;
	$vendor_currency = VirtueMartModelVendor::getVendorCurrency($vendor_id);

	$db = JFactory::getDBO();
	$calculator = calculationHelper::getInstance();
	$currency = CurrencyDisplay::getInstance();

	$value = $currency->convertCurrencyTo($vendor_currency->virtuemart_currency_id, $invoice_fee);

	$taxrules = array();
	if (!empty($invoice_tax_id)) {
	    $q = 'SELECT * FROM #__virtuemart_calcs WHERE `virtuemart_calc_id`="' . $invoice_tax_id . '" ';
	    $db->setQuery($q);
	    $taxrules = $db->loadAssocList();
	}

	if (count($taxrules) > 0) {
	    $salesPrice = $calculator->roundInternal($calculator->executeCalculation($taxrules, $value));
	} else {
	    $salesPrice = $value;
	}

	return $salesPrice;
    }

    public function getSettingsForCountry($method, $country) {
	$settings = array();
	$settings['eid'] = self::getEid($method, $country);
	$settings['secret'] = self::getSecret($method, $country);
	$settings['lang'] = self::getLanguageForCountry($method, $country);
	$settings['invfee'] = self::getInvoiceFeeInclTax($method, $country);
	return $settings;
    }

    public function convertCountryCode($method, $country) {
	$country_data = self::countryData($method, $country);
	return $country_data['country_code'];
    }

    /*
     * @depredecated
     */

    public function convertToThreeLetterCode($country) {
	switch (strtolower($country)) {
	    case "se":
		return "swe";
	    case "de":
		return "deu";
	    case "dk":
		return "dnk";
	    case "nl":
		return "nld";
	    case "fi":
		return "fin";
	    case "no":
		return "nor";
	    default:
		return $country;
	}
    }

    /*
     * @depredecated
     */

    public function getCountryName($country) {
	switch (strtolower($country)) {
	    case "se":
		return "Sweden";
	    case "de":
		return "Germany";
	    case "dk":
	    case "da":
		return "Denmark";
	    case "nl":
		return "the Netherlands";
	    case "fi":
		return "Finland";
	    case "no":
		return "Norway";
	    default:
		return "";
	}
    }

    public function getCountries() {
	$klarna_countries = array("swe", "deu", "dnk", "nld", "fin", "nor");
	return $klarna_countries;
    }

    private function getBilling($method, $cart) {
	$bt = $cart->BT;
	$bill_country = shopFunctions::getCountryByID($bt['virtuemart_country_id'], 'country_2_code');
	//self::convertCountryCode($method,$bt['virtuemart_country_id']);
	$bill_street = $bt['address_1'];
	$bill_ext = "";
	$bill_number = "";
	if (strtolower($bill_country) == "de" || strtolower($bill_country) == "nl") {
	    $splitAddress = array('', '', '');
	    $splitAddress = self::splitAddress($bt['address_1']);
	    $bill_street = $splitAddress[0];
	    $bill_number = $splitAddress[1];
	    switch (strtolower($bt['title'])) {
		case "mr":
		    $this->klarna_gender = KlarnaFlags::MALE;
		    break;
		case "miss":
		case "mrs":
		case "ms":
		    $this->klarna_gender = KlarnaFlags::FEMALE;
		    break;
		default:
		    $this->klarna_gender = null;
		    break;
	    }
	    if (strtolower($bill_country) == "nl") {
		$bill_ext = $splitAddress[2];
	    }
	}
	$billing = new KlarnaAddr(
			$bt['email'],
			$bt['phone_1'],
			@$bt['phone_2'],
			utf8_decode($bt['first_name']),
			utf8_decode($bt['last_name']), '',
			utf8_decode($bill_street),
			$bt['zip'],
			utf8_decode($bt['city']),
			$bill_country,
			$bill_number,
			$bill_ext
	);

	return $billing;
    }

    public function addTransaction($method, $cart, $order) {

	if (!class_exists('KlarnaAddr'))
	    require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'api' . DS . 'klarnaaddr.php');

	$session = JFactory::getSession();
	$sessionKlarna = $session->get('Klarna', 0, 'vm');
	$sessionKlarnaData = unserialize($sessionKlarna);

	// No klarna data set, redirect back.
	if (!isset($sessionKlarnaData))
	    throw new Exception("No klarna Session data set");
	$klarnaData = $sessionKlarnaData->KLARNA_DATA;

	$country = self::convertToThreeLetterCode($klarnaData['COUNTRY']);
	$cData = self::countryData($method, $country);

	//Currency mismatch, redirect back!

	/* TODO Currency mismatch, redirect back!
	  if (!isset($_SESSION['product_currency']) ||
	  !$_SESSION['product_currency']) {
	  if ($country['currency_code'] !== $_SESSION['vendor_currency']) {
	  self::redirectPaymentMethod('error', 'currency mismatch');
	  }
	  } else if ($country['currency_code'] != $_SESSION['product_currency']) {
	  self::redirectPaymentMethod('error', 'currency mismatch');
	  }
	 */
	// Get information stored in session and unset the session variable.

	$shipping_tax = $order['details']['BT']->order_shipment_tax;

	$total_price_excl_vat = $order['details']['BT']->order_subtotal;
	$total_price_incl_vat = $order['details']['BT']->order_subtotal + $order['details']['BT']->order_tax;

	$mode =  KlarnaHandler::getKlarnaMode($method);
	$ssl = KlarnaHandler::getKlarnaSSL($mode);
	// Instantiate klarna object.
	$klarna = new Klarna_virtuemart();
	$klarna->config($cData['eid'], $cData['secret'], $cData['country_code'], null, $cData['currency_code'], $mode, VMKLARNA_PC_TYPE, VMKLARNA_PC_URI, $ssl);


	// Fill the good list the we send to Klarna
	foreach ($order['items'] as $item) {
	    $price = self::convertPrice($item->product_final_price,   $cData['currency_code'], $order['details']['BT']->order_currency);
	    $klarna->addArticle($item->product_quantity, utf8_decode($item->order_item_sku), utf8_decode(strip_tags($item->order_item_name, ((double) (round($price, 2))))), (double) $item->product_tax, 0, KlarnaFlags::INC_VAT );
	}
	// Add shipping
	$klarna->addArticle(1, "shippingfee", JText::_('VMPAYMENT_KLARNA_SHIPMENT'), ((double) (round(($order['details']['BT']->order_shipment + $order['details']['BT']->order_shipment_tax), 2))), (double) $order['details']['BT']->order_shipment_tax, 0, KlarnaFlags::IS_SHIPMENT + KlarnaFlags::INC_VAT);

	// Add invoice fee
	if ($sessionKlarnaData->klarna_option === 'invoice') { //Only for invoices!
	    //$invoice_fee = (double) (round(abs(self::getInvoiceFee($method, $country['country_code'])), 2));
	    $invoice_fee = (double) (round($order['details']['BT']->order_payment));
	    if ($invoice_fee > 0) {
		$klarna->addArticle(1, "invoicefee", JText::_('VMPAYMENT_KLARNA_INVOICE_FEE_TITLE'), $invoice_fee, (double) round($order['details']['BT']->order_payment_tax, 2), 0, KlarnaFlags::IS_HANDLING + KlarnaFlags::INC_VAT);
	    }
	    $klarna_pclass = -1;
	} else {
	    $klarna_pclass = $klarnaData['PCLASS'];
	}
	// Add coupon if there is any
	if ($order['details']['BT']->coupon_discount > 0) {
	    $klarna->addArticle(1, 'discount', JText::_('VMPAYMENT_KLARNA_DISCOUNT') . ' ' . $order['details']['BT']->coupon_code, ((int) (round($order['details']['BT']->coupon_discount, 2) * -1)), 0, 0, KlarnaFlags::INC_VAT);
	}

	$klarna_shipping = new KlarnaAddr(
			$klarnaData['EMAIL'],
			$klarnaData['PHONE'],
			'',
			utf8_decode($klarnaData['FIRST_NAME']),
			utf8_decode($klarnaData['LAST_NAME']), '',
			utf8_decode(isset($klarnaData['STREET']) ? $klarnaData['STREET'] : ''),
			$klarnaData['ZIP'],
			utf8_decode($klarnaData['CITY']),
			utf8_decode($klarnaData['COUNTRY']),
			isset($klarnaData['HOUSE_NO']) ? $klarnaData['HOUSE_NO'] : '',
			isset($klarnaData['HOUSE_EXT']) ? $klarnaData['HOUSE_EXT'] : ''
	);

	if ($klarnaData['INVOICE_TYPE'] == 'company') {
	    $klarna_shipping->isCompany = true;
	    $klarna_shipping->setCompanyName($klarna_company_name);
	    $klarna_comment = $klarnaData['REFERENCE'];

	    if ($klarna_shipping->getLastName() == "") {
		$klarna_shipping->setLastName("-");
	    }
	    if ($klarna_shipping->getFirstName() == "") {
		$klarna_shipping->setFirstName("-");
	    }
	} else {
	    $klarna_reference = "";
	    $klarna_comment = "";
	}

	// Only allow billing and shipping to be the same for Germany and
	// the Netherlands
	if (strtolower($country) == 'nld' || strtolower($country) == 'deu') {
	    $klarna_billing = $klarna_shipping;
	} else {
	    $klarna_billing = self::getBilling($method, $cart);
	}

	$klarna_flags = KlarnaFlags::RETURN_OCR; // get ocr back from KO.

	$klarna->setComment($klarna_comment);
	$klarna->setReference($klarna_reference, "");

	try {
	    $klarna->setAddress(KlarnaFlags::IS_SHIPPING, $klarna_shipping);
	    $klarna->setAddress(KlarnaFlags::IS_BILLING, $klarna_billing);
	    if (isset($klarnaData['YEAR_SALARY'])) {
		$klarna->setIncomeInfo("'yearly_salary'", $klarnaData['YEAR_SALARY']);
	    }

	    $result = $klarna->addTransaction($klarnaData['PNO'], ($klarna->getCountry() == KlarnaCountry::DE || $klarna->getCountry() == KlarnaCountry::NL) ? $klarnaData['GENDER'] : null, $klarna_flags, $klarna_pclass);


	    $status = self::getStatusForCode($result[2]);

	    $result['eid'] = $cData['eid'];
	    $result['status_code'] = $status['code'];
	    $result['status_text'] = $status['text'];

	    return $result; //return $result;
	} catch (Exception $e) {
	    $result['status_code'] = KlarnaFlags::DENIED;
	    $result['status_text'] = htmlentities($e->getMessage()) . "  (#" . $e->getCode() . ")";
	    return $result; //return $result;
	    //self::redirectPaymentMethod('error', htmlentities($e->getMessage()) .  "  (#" . $e->getCode() . ")");
	}
	throw new Exception("Something went wrong!");
    }

    /**
     * Returns a collection of addresses that are connected to the
     * supplied SSN
     *
     * @param <type> $pno The SSN of the user. This method is only available
     * for swedish customers
     * @return array
     */
    public function getAddresses($pno, $method) {
	// Only available for sweden.
	$settings = self::countryData($method, 'swe');
	$addresses = array();
	$klarna = new Klarna_virtuemart();
	$mode= KlarnaHandler::getKlarnaMode($method);
	$klarna->config($settings['eid'], $settings['secret'], KlarnaCountry::SE, KlarnaLanguage::SV, KlarnaCurrency::SEK, $mode, VMKLARNA_PC_TYPE, VMKLARNA_PC_URI, $mode);
	try {
	$addresses = $klarna->getAddresses($pno, null, KlarnaFlags::GA_GIVEN);
	} catch (Exception $e) {
	  VmInfo( $e->getMessage());
	}
	unset($klarna);
	return $addresses;
    }

    /**
     * Checks if we need to ask ILT questions.
     */
    public function checkILT(&$d, $pno, $gender, $addr, $method) {
	$amount = $d['order_subtotal_withtax'];
	$settings = self::countryData($method, $_SESSION['auth']['country']);
	$ilt = array();
	if (!isset($gender) || $gender == '') {
	    $gender = null;
	}
	$klarna = new Klarna_virtuemart();
	$klarna->config($settings['eid'], $settings['secret'], $settings['country'], $settings['language'], $settings['currency'], 99, VMKLARNA_PC_TYPE, VMKLARNA_PC_URI, KLARNA_MODE);
	$klarna->setValidator();

	$klarna->setAddress(KlarnaFlags::IS_SHIPPING, $addr);
	try {
	    $ilt = $klarna->checkILT($amount, $pno, $gender);
	} catch (Exception $e) {
	    // Do nothing, fail quietly.
	}
	unset($klarna);
	return $ilt;
    }



    public function fetchPClasses($method) {
	$message = '';
	$success = '';
	$results = array();

	$countries = $this->getCountries();

	foreach ($countries as $country) {
	    $active_country = "klarna_active_" . $country;
	    if ($method->$active_country) {
		// country is CODE 3==> converting to 2 letter country
		//$country = self::convertCountryCode($method, $country);
		$lang = self::getLanguageForCountry($method, $country);
		$flag = "<img src='" . JURI::root() . VMKLARNAPLUGINWEBROOT . "/klarna/assets/images/share/flags/" . $lang . ".png' />";
		try {
		    $settings = self::getCountryData($method, $country);

		    $klarna = new Klarna_virtuemart();
		    $klarna->config($settings['eid'], $settings['secret'], $settings['country'], $settings['language'], $settings['currency'],  KlarnaHandler::getKlarnaMode($method), VMKLARNA_PC_TYPE, VMKLARNA_PC_URI, true);
// fetch pclass from file
		    $klarna->fetchPClasses($country);
		    $success .= '<span style="padding: 5px;">' . $flag . " " .
			    self::getCountryName($lang) . '</span>';
		} catch (Exception $e) {
		    $message .= '<br><span style="font-size: 15px;">' .
			    $flag . " " . self::getCountryName($country) .
			    ": " . $e->getMessage() . ' Error Code #' .
			    $e->getCode() . '</span></br>';
		}
	    }
	}
	$results['msg'] = $message;
	$results['notice'] = 'PClasses fetched for : ' . $success;
	return $results;
	//echo $notice;
    }

    /**
     * Redirects user to payment method stage.
     *
     * @param <type> $type e.g. 'error', ...
     * @param <type> $message
     */
    public function redirectPaymentMethod($type = null, $message = null) {

	$log = utf8_encode($message);
	//Display the error.
	if (strlen($log) > 0) {
	    if ($type === null) {
		$type = 'message';
	    }
	    $app = JFactory::getApplication();
	    $app->enqueueMessage(JText::_(urldecode($log)), $type);
	}
	//Redirect to previous page.
	$session = JFactory::getSession();
	$sessionKlarna = new stdClass();
	$sessionKlarna->klarna_error = addslashes($message);
	$session->set('Klarna', serialize($sessionKlarna), 'vm');
	if (isset($_SESSION['klarna_paymentmethod'])) {
	    $pid = $_SESSION['klarna_paymentmethod'];
	    unset($_SESSION['klarna_paymentmethod']);
	}
	//$_SESSION['klarna_error'] = addslashes($message);
	$app = JFactory::getApplication();
	$app->enqueueMessage($html);
	$app->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart'), JText::_('COM_VIRTUEMART_CART_ORDERDONE_DATA_NOT_VALID'));
    }

    /**
     *
     * @param <type> $address
     * @return <type>
     */
    public function splitAddress($address) {

	$numbers = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
	$characters = array('-', '/', ' ', '#', '.', 'a', 'b', 'c', 'd', 'e',
	    'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p',
	    'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A',
	    'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L',
	    'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W',
	    'X', 'Y', 'Z');
	$specialchars = array('-', '/', ' ', '#', '.');

	//Where do the numbers start? Allow for leading numbers
	$numpos = self::strpos_arr($address, $numbers, 2);
	//Get the streetname by splitting off the from the start of the numbers
	$streetname = substr($address, 0, $numpos);
	//Strip off spaces at the end
	$streetname = trim($streetname);

	//Get the housenumber+extension
	$numberpart = substr($address, $numpos);
	//and strip off spaces
	$numberpart = trim($numberpart);

	//Get the start position of the extension
	$extpos = self::strpos_arr($numberpart, $characters, 0);

	//See if there is one, if so
	if ($extpos != '') {
	    //get the housenumber
	    $housenumber = substr($numberpart, 0, $extpos);
	    // and the extension
	    $houseextension = substr($numberpart, $extpos);
	    // and strip special characters from it
	    $houseextension = str_replace($specialchars, '', $houseextension);
	} else {
	    //Otherwise, we already have the housenumber
	    $housenumber = $numberpart;
	}

	return array($streetname, $housenumber, $houseextension);
    }

    /**
     *
     * @param <type> $haystack
     * @param <type> $needle
     * @param <type> $where
     * @return <type>
     */
    private function strpos_arr($haystack, $needle, $where) {

	$defpos = 10000;
	if (!is_array($needle))
	    $needle = array($needle);
	foreach ($needle as $what) {
	    if (($pos = strpos($haystack, $what, $where)) !== false) {
		if ($pos < $defpos)
		    $defpos = $pos;
	    }
	}
	return $defpos;
    }

    /**
     *
     * @global <type> $db
     * @param <type> $ship_to_info_id
     * @return <type>
     */
    public function getCustomerCountry($ship_to_info_id) {

	$db->query("SELECT country from #__{vm}_user_info WHERE  user_info_id=
                    '" . $db->getEscaped($ship_to_info_id) . '\'');
	$db->next_record();
	$country = $db->f('country');
	return $country;
    }

    /**
     *
     * @global <type> $vmLogger
     * @param <type> $invNo
     * @param <type> $estoreOrderNo
     */
    public function updateOrderNo($invNo, $estoreOrderNo) {
	$settings = self::countryData($method, $_SESSION['auth']['country']);
	$klarna = new Klarna_virtuemart();
	$klarna->config($settings['eid'], $settings['secret'], $settings['country'], $settings['language'], $settings['currency'], ((KLARNA_MODE == 1) ?
			Klarna::LIVE : Klarna::BETA), VMKLARNA_PC_TYPE, VMKLARNA_PC_URI, true);
	// Update Ordernumber
	$klarna->updateOrderno($invNo, $estoreOrderNo);
	unset($klarna);
    }

    /**
     * gets Eid and Secret for activated countries.
     */
    public function getEidSecretArray($method) {
	$eid_array = array();
	if (isset($method->klarna_merchantid_swe) && $method->klarna_merchantid_swe != "" && $method->klarna_sharedsecret_swe != "") {
	    $eid_array['se']['secret'] = $method->klarna_sharedsecret_swe;
	    $eid_array['se']['eid'] = (int) $method->klarna_merchantid_swe;
	}

	if (isset($method->klarna_merchantid_nor) && $method->klarna_merchantid_nor != "" && $method->klarna_sharedsecret_nor != "") {
	    $eid_array['no']['secret'] = $method->klarna_sharedsecret_nor;
	    $eid_array['no']['eid'] = $method->klarna_merchantid_nor;
	}

	if (isset($method->klarna_merchantid_deu) && $method->klarna_merchantid_deu != "" && $method->klarna_sharedsecret_deu != "") {
	    $eid_array['de']['secret'] = $method->klarna_sharedsecret_deu;
	    $eid_array['de']['eid'] = $method->klarna_merchantid_deu;
	}

	if (isset($method->klarna_nld_merchantid) && $method->klarna_nld_merchantid != "" && $method->klarna_sharedsecret_nld != "") {
	    $eid_array['nl']['secret'] = $method->klarna_sharedsecret_nld;
	    $eid_array['nl']['eid'] = $method->klarna_nld_merchantid;
	}

	if (isset($method->klarna_merchantid_dnk) && $method->klarna_merchantid_dnk != "" && $method->klarna_sharedsecret_dnk != "") {
	    $eid_array['dk']['secret'] = $method->klarna_sharedsecret_dnk;
	    $eid_array['dk']['eid'] = $method->klarna_merchantid_dnk;
	}

	if (isset($method->klarna_merchantid_fin) && $method->klarna_merchantid_fin != "" && $method->klarna_sharedsecret_fin != "") {
	    $eid_array['fi']['secret'] = $method->klarna_sharedsecret_fin;
	    $eid_array['fi']['eid'] = $method->klarna_merchantid_fin;
	}

	return $eid_array;
    }

    /**
     * Sets PCUri
     * @TODO JOOMLA OR JSON
     * @deprecated
     */
    public function getPCUri() {
// TO TEST
	//if (VMKLARNA_PC_TYPE == "mysql") {
	$config = JFactory::getConfig();
	$tablePrefix = $config->getValue('config.dbprefix');
	$prefix = '#__';
	$tablename = str_replace($prefix, $tablePrefix, $tablename);

	$pcURI = array('user' => $config->getValue('config.user'),
	    'passwd' => $config->getValue('config.password'),
	    'dsn' => $config->getValue('config.host'),
	    'db' => $config->getValue('config.db'),
	    'table' => 'rc3_virtuemart_payment_plg_klarna_pclasses'
	);
	//} else {
	//    $pcURI = VMKLARNA_PC_URI;
	//}
	return $pcURI;
    }

    /*
     * @deprecated
     */

    public function getTotalSum() {
	global $vars;
	$totalSum = $vars['order_total'];
	return $totalSum;
    }

    public function getLocalTemplates() {
	$kLoc = JPATH_VMKLARNAPLUGIN . '/klarna/tmpl/'; //spec
	$aTemplates = scandir($kLoc);

	$aResult = array();

	foreach ($aTemplates as $sDir) {
	    if (file_exists($kLoc . "/campaign.xml")) {
		$oXml = simplexml_load_file($kLoc . "/campaign.xml", 'SimpleXMLElement', LIBXML_NOCDATA);
		$aXml = self::xmlToArray($oXml, 0);

		$aResult[$aXml['name']] = $aXml;
		$aResult[$aXml['name']]['shown'] = false;
		$aResult[$aXml['name']]['name'] = $sDir;
		$aResult[$aXml['name']]['active'] = (KLARNA_SPEC_ACTIVE_TEMPLATE == $aXml['name']);
	    }
	}

	return $aResult;
    }

    public function getLocalTemplate($sTemplateName) {

	$kLoc = JPATH_VMKLARNAPLUGIN . DS . 'klarna/tmpl/'; // spec
	$aTemplates = scandir($kLoc);

	$aResult = array();

	foreach ($aTemplates as $sDir) {
	    if ($sDir == $sTemplateName) {
		$oXml = simplexml_load_file($kLoc . "/campaign.xml", 'SimpleXMLElement', LIBXML_NOCDATA);
		$aResult = self::xmlToArray($oXml, 0);
		$aResult['shown'] = false;
		$aResult['name'] = $sDir;
		$aResult['active'] = (KLARNA_SPEC_ACTIVE_TEMPLATE == $oXml['name']);
	    }
	}

	return $aResult;
    }

    public function xmlToArray($obj, $level = 0) {
	$aResult = array();

	if (!is_object($obj))
	    return $aResult;

	$aChild = (array) $obj;

	if (sizeof($aChild) > 1) {
	    foreach ($aChild as $sName => $mValue) {
		if ($sName == "@attributes") {
		    $sName = "_attributes";
		}

		if (is_array($mValue)) {
		    foreach ($mValue as $ee => $ff) {
			if (!is_object($ff)) {
			    $aResult[$sName][$ee] = $ff;
			} else if (get_class($ff) == 'SimpleXMLElement') {
			    $aResult[$sName][$ee] = self::xmlToArray($ff, $level + 1);
			}
		    }
		} else if (!is_object($mValue)) {
		    $aResult[$sName] = $mValue;
		} else if (get_class($mValue) == 'SimpleXMLElement') {
		    $aResult[$sName] = self::xmlToArray($mValue, $level + 1);
		}
	    }
	} else if (sizeof($aChild) > 0) {
	    foreach ($aChild as $sName => $mValue) {
		if ($sName == "@attributes") {
		    $sName = "_attributes";
		}

		if (!is_array($mValue) && !is_object($mValue)) {
		    $aResult[$sName] = $mValue;
		} else if (is_object($mValue)) {
		    $aResult[$sName] = self::xmlToArray($mValue, $level + 1);
		} else {
		    foreach ($mValue as $sNameTwo => $sValueTwo) {
			if (!is_object($sValueTwo)) {
			    $aResult[$obj->getName()][$sNameTwo] = $sValueTwo;
			} else if (get_class($sValueTwo) == 'SimpleXMLElement') {
			    $aResult[$obj->getName()][$sNameTwo] =
				    self::xmlToArray($sValueTwo, $level + 1);
			}
		    }
		}
	    }
	}

	return $aResult;
    }
    /*
   *  Order status can be
     * {@link KlarnaFlags::ACCEPTED} 1
     * {@link KlarnaFlags::PENDING} 2
     * {@link KlarnaFlags::DENIED} 3
   */

   private function getStatusForCode($code) {
        $status = array();
	/*
        switch ( $code ) {
            case KlarnaFlags::ACCEPTED:
                $status['code'] = $code;
                $status['text'] = 'Accepted';
                break;
            case KlarnaFlags::DENIED:
                $status['code'] = $code;
                $status['text'] = 'Denied';
                break;
            case KlarnaFlags::PENDING:
            default:
                $status['code'] = $code;
                $status['text'] = 'Pending';
                break;
        }
	 * */

	 $status['code'] = $code;
	 $status['text'] = JText::_('VMPAYMENT_KLARNA_ORDER_STATUS_TEXT_'.$code) ;
        return $status;
    }



    public function checkOrderStatus( $settings, $klarna_invoice_no, $country ) {

	//$settings = self::countryData($method, $country);

	try {
	    $klarna = new Klarna_virtuemart();
	    //$mode =  KlarnaHandler::getKlarnaMode($method);;
	    $klarna->config($settings['eid'], $settings['secret'], $settings['country'], $settings['language'], $settings['currency'], $mode, VMKLARNA_PC_TYPE, VMKLARNA_PC_URI, true);
	    $os = $klarna->checkOrderStatus($klarna_invoice_no, 1);
	} catch (Exception $e) {
	    $msg = $e->getMessage() . ' #' . $e->getCode() . ' </br>';
	    VmError($msg);
	    return $msg;
	}
	$os = self::getStatusForCode($os);
	return $os;
    }

    /**
     * Get the shipToAddress which might differ from default address.
     */
    public function getShipToAddress($cart) {
	$shipTo = (($cart->ST == 0 or empty($cart->ST)) ? $cart->BT : $cart->ST);

	$r = array();
	$r['first_name'] = $shipTo['first_name'];
	$r['last_name'] = $shipTo['last_name'];
	$r['company_name'] = @$shipTo['company_name'];
	$r['phone'] = $shipTo['phone_1'];
	$r['street'] = $shipTo['address_1'];
	$r['city'] = $shipTo['city'];
	$r['country'] = @ShopFunctions::getCountryByID($shipTo['virtuemart_country_id'], 'country_3_code');

	$r['state'] = @$shipTo['state'];
	$r['zip'] = $shipTo['zip'];
	$r['title'] = @$shipTo['title'];
	return $r;
    }

    /**
     * Return pclasses stored in database.
     */
    public function getPClasses($type = null, $country = '', $method, $settings) {

	//$settings = self::countryData($method, $country);
	try {
	    $klarna = new Klarna_virtuemart();
	    $klarna->config($settings['eid'], $settings['secret'], $settings['country'], $settings['language'], $settings['currency'],  KlarnaHandler::getKlarnaMode($method), VMKLARNA_PC_TYPE, VMKLARNA_PC_URI, true);
	    return $klarna->getPClasses($type);
	} catch (Exception $e) {

	}
    }

    function getVendorCountry($fld = 'country_3_code') {
	if (!class_exists('VirtueMartModelVendor'))
	    JLoader::import('vendor', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'models');
	$virtuemart_vendor_id = 1;
	$model = VmModel::getModel('vendor');
	$vendor = $model->getVendor($virtuemart_vendor_id);
	$vendorAdress = $model->getVendorAdressBT($virtuemart_vendor_id);
	$vendor_country = ShopFunctions::getCountryByID($vendorAdress->virtuemart_country_id, $fld);
	return $vendor_country;
    }

    function getKlarnaError(&$klarnaError, &$klarnaOption) {
	$session = JFactory::getSession();
	$sessionKlarna = $session->get('Klarna', 0, 'vm');
	if ($sessionKlarna) {
	    $sessionKlarnaData = unserialize($sessionKlarna);
	    if (isset($sessionKlarnaData->klarna_error)) {
		$klarnaError = $sessionKlarnaData->klarna_error;
		$klarnaOption = $sessionKlarnaData->klarna_option;
		return true;
	    } else {
		return false;
	    }
	}
	return false;
    }

    function clearKlarnaError() {
	$session = JFactory::getSession();
	$sessionKlarna = $session->get('Klarna', 0, 'vm');
	if ($sessionKlarna) {
	    $sessionKlarnaData = unserialize($sessionKlarna);
	    if (isset($sessionKlarnaData->klarna_error)) {
		unset($sessionKlarnaData->klarna_error);
		unset($sessionKlarnaData->klarna_option);
		$session->set('Klarna', serialize($sessionKlarnaData), 'vm');
	    }
	}
    }
 /**
     * German and dutch purchases need a different structure
     * on the address, and they need gender specified.
     */
     function setGermanDutchData(&$data) {
	$splitAddress = array('', '', '');
	$splitAddress = KlarnaHandler::splitAddress($data->shipTo['street']);
	$data->klarna_addr = $splitAddress[0];
	$data->klarna_houseNr = $splitAddress[1];
	switch (strtolower($data->shipTo['title'])) {
	    case "mr.":
		$data->klarna_gender = KlarnaFlags::MALE;
		break;
	    case "miss.":
	    case "mrs.":
	    case "ms.":
		$data->klarna_gender = KlarnaFlags::FEMALE;
		break;
	    default:
		$data->klarna_gender = null;
		break;
	}
	if ($data->country == "nl") {
	    $data->klarna_houseExt = $splitAddress[2];
	}
    }
     function getKlarnaMode($method) {
	return (($method->klarna_mode == 'klarna_live') ? Klarna::LIVE : Klarna::BETA);
    }
    function getKlarnaSSL($mode) {
	return ($mode == Klarna::LIVE);
    }

    function  convertPrice($price,$toCurrency =''){

	if (!is_int($toCurrency) && !empty($toCurrency)) {
	    $toCurrency = ShopFunctions::getCurrencyIDByName($toCurrency);
	}
	$currency = CurrencyDisplay::getInstance($toCurrency);
	$fromCurrency= $currency->getCurrencyForDisplay();
	$price = round($currency->convertCurrencyTo($toCurrency, $price, false), 2);
	$cd = CurrencyDisplay::getInstance($fromCurrency);

	return $price;
    }
 /*
     * if client has not given address then get cdata depending on the currency
     * otherwise get info depending on the country
     */

    function getcData($method, $cart) {
	$address = (($cart->ST == 0 or empty($cart->ST)) ? $cart->BT : $cart->ST);
	if (!isset($address['virtuemart_country_id'])) {
	    $vendor_country = KlarnaHandler::getVendorCountry();
	    $cData = self::countryData($method, $vendor_country);
	} else {
	    $cart_country_code_3 = ShopFunctions::getCountryByID($address['virtuemart_country_id'], 'country_3_code');
	    // the user gave an address, get info according to his country
	    $cData = self::countryData($method, $cart_country_code_3);
	}
	return $cData;
    }
}

