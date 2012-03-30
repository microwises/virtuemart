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
	    $cData = $countryData[$country];
	    $eid = 'klarna_' . $lower_country . '_merchantid';
	    $secret = 'klarna_' . $lower_country . '_sharedsecret';
	    $invoice_fee = 'klarna_' . $lower_country . '_invoicefee';
	    $cData['eid'] = $method->$eid;
	    $cData['secret'] = $method->$secret;
	    $cData['invoice_fee'] = (double) $method->$invoice_fee;
	    return $cData;
	} else {
	    return null;
	}
	/*
	  switch (strtoupper($country)) {
	  case 'NOR':
	  return array(
	  'pno_encoding' => 3,
	  'language' => 97,
	  'language_code' => 'nb',
	  'country' => 164,
	  'currency' => 1,
	  'currency_code' => 'NOK',
	  'currency_symbol' => 'kr',
	  'country_code' => 'no',
	  'eid' => $method->klarna_norway_merchantid,
	  'secret' => $method->klarna_norway_sharedsecret,
	  'invoice_fee' => (double) $method->klarna_norway_invoicefee
	  );
	  case 'SWE':
	  return array(
	  'pno_encoding' => 2,
	  'language' => 138,
	  'language_code' => 'sv',
	  'country' => 209,
	  'country_code' => 'se',
	  'currency' => 0,
	  'currency_code' => 'SEK',
	  'currency_symbol' => 'kr',
	  'eid' => $method->klarna_sweden_merchantid,
	  'secret' => $method->klarna_sweden_sharedsecret,
	  'invoice_fee' => (double) $method->klarna_sweden_invoicefee
	  );
	  case 'DNK':
	  return array(
	  'pno_encoding' => 5,
	  'language' => 27,
	  'language_code' => 'da',
	  'country' => 59,
	  'country_code' => 'dk',
	  'currency' => 3,
	  'currency_code' => 'DKK',
	  'currency_symbol' => 'kr',
	  'country_code' => 'dk',
	  'eid' => $method->klarna_denmark_merchantid,
	  'secret' => $method->klarna_denmark_sharedsecret,
	  'invoice_fee' => (double) $method->klarna_denmark_invoicefee
	  );
	  case 'FIN':
	  return array(
	  'pno_encoding' => 4,
	  'language' => 37,
	  'language_code' => 'fi',
	  'country' => 73,
	  'country_code' => 'fi',
	  'currency' => 2,
	  'currency_code' => 'EUR',
	  'currency_symbol' => '&#8364;',
	  'eid' => $method->klarna_finland_merchantid,
	  'secret' => $method->klarna_finland_sharedsecret,
	  'invoice_fee' => (double) $method->klarna_finland_invoicefee
	  );
	  case 'NLD':
	  return array(
	  'pno_encoding' => 7,
	  'language' => 101,
	  'language_code' => 'nl',
	  'country' => 154,
	  'country_code' => 'nl',
	  'currency' => 2,
	  'currency_code' => 'EUR',
	  'currency_symbol' => '&#8364;',
	  'eid' => $method->klarna_netherlands_merchantid,
	  'secret' => $method->klarna_netherlands_sharedsecret,
	  'invoice_fee' => (double) $method->klarna_netherlands_invoicefee
	  );
	  case 'DEU':
	  return array(
	  'pno_encoding' => 6,
	  'language' => 28,
	  'language_code' => 'de',
	  'country' => 81,
	  'country_code' => 'de',
	  'currency' => 2,
	  'currency_code' => 'EUR',
	  'currency_symbol' => '&#8364;',
	  'eid' => $method->klarna_germany_merchantid,
	  'secret' => $method->klarna_germany_sharedsecret,
	  'invoice_fee' => (double) $method->klarna_germany_invoicefee
	  );
	  default:
	  return null; // Not supported by klarna yet.
	  }
	 * */
    }

    public function getCountryData($method, $country) {
	//$country = self::convertToThreeLetterCode($country);
	return self::countryData($method, $country);
    }

    public function convertCountry($method, $country) {
	$country_data = self::countryData($method, $country);
	return $country_data['country_code'];
    }

    public function convertCountryToCountryNb($method, $country) {
	$country_data = self::countryData($method, $country);
	return $country_data['country'];
    }

    public function getEid($method, $country) {
	$eid = 'klarna_' . strtolower($country) . '_merchantid';
	return $method->$eid;
	//$country = self::convertToThreeLetterCode($country);
	//$country_data = self::countryData($method, $country);
	//return $country_data['eid'];
    }

    public function getSecret($method, $country) {
	$secret = 'klarna_' . strtolower($country) . '_sharedsecret';
	return $method->$secret;
    }

    public function getLanguageForCountry($method, $country) {
	$country_data = self::countryData($method, $country);
	return $country_data['language_code'];
    }

    public function getCurrencySymbolForCountry($method, $country) {
	$country_data = self::countryData($method, $country);
	return $country_data['currency_symbol'];
    }

    public function getInvoiceFee($method, $country) {
	$invoice_fee = 'klarna_' . strtolower($country) . '_invoicefee';
	return $method->$invoice_fee;
    }

    public function getSettingsForCountry($method, $country) {
	$settings = array();
	$settings['eid'] = self::getEid($method, $country);
	$settings['secret'] = self::getSecret($method, $country);
	$settings['lang'] = self::getLanguageForCountry($method, $country);
	$settings['invfee'] = self::getInvoiceFee($method, $country);
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
		return "Denmark";
	    case "nl":
		return "the Netherlands";
	    case "fi":
		return "Finland";
	    case "no":
		return "Norway";
	    default:
		return null;
	}
    }

    private function getBilling($method, $cart) {
	$bt = $cart->BT;
	$bill_country = "NO"; // TODO self::convertCountryCode($method,$bt['virtuemart_country_id']);
	$bill_street = $bt['address_1'];
	$bill_ext = "";
	$bill_number = "";
	if (strtolower($bill_country) == "de" || strtolower($bill_country) == "nl") {
	    $splitAddress = array('', '', '');
	    $splitAddress = self::splitAddress($bt['address_1']);
	    $bill_street = $splitAddress[0];
	    $bill_number = $splitAddress[1];
	    switch (strtolower($bt['title'])) {
		case "mr.":
		    $this->klarna_gender = KlarnaFlags::MALE;
		    break;
		case "miss.":
		case "mrs.":
		case "ms.":
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
			$bt['phone_2'],
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

    public function addTransaction($method, $cart, &$order, &$estoreOrderNo, $tablename) {
	$session = JFactory::getSession();
	$sessionKlarna = $session->get('Klarna', 0, 'vm');
	$sessionKlarnaData = unserialize($sessionKlarna);

	// No klarna data set, redirect back.
	if (!isset($sessionKlarnaData))
	    throw new Exception("No klarna Session data set");
	$klarnaData = $sessionKlarnaData->KLARNA_DATA;

	$country = self::convertToThreeLetterCode($klarnaData['COUNTRY']);
	$country = self::countryData($method, $country);

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
	$klarna_yearly_salary = 0;
	if (isset($klarnaData['YEAR_SALARY'])) {
	    $klarna_yearly_salary = $klarnaData['YEAR_SALARY'];
	}
	$klarna_gender = 0;
	if (isset($klarnaData['GENDER'])) {
	    $klarna_gender = $klarnaData['GENDER'];
	}
	$klarna_house_ext = "";
	if (isset($klarnaData['HOUSE_EXT'])) {
	    $klarna_house_ext = $klarnaData['HOUSE_EXT'];
	}
	$klarna_house_no = "";
	if (isset($klarnaData['HOUSE_NO'])) {
	    $klarna_house_no = $klarnaData['HOUSE_NO'];
	}
	$klarna_street = "";
	if (isset($klarnaData['STREET'])) {
	    $klarna_street = $klarnaData['STREET'];
	}

	// Get data which should always be set.
	$klarna_zip = $klarnaData['ZIP'];
	$klarna_city = $klarnaData['CITY'];
	$klarna_country = $klarnaData['COUNTRY'];
	$klarna_invoice_type = $klarnaData['INVOICE_TYPE'];
	$klarna_phone = $klarnaData['PHONE'];
	$klarna_pclass = $klarnaData['PCLASS'];
	$klarna_email = $klarnaData['EMAIL'];
	$klarna_first_name = $klarnaData['FIRST_NAME'];
	$klarna_last_name = $klarnaData['LAST_NAME'];
	$klarna_pno = $klarnaData['PNO'];
	$klarna_payment_id = $klarnaData['klarna_payment_id'];
	$klarna_payment_code = $order['virtuemart_paymentmethod_id'];

	// Check if it's a company invoice
	if ($klarna_invoice_type == 'company') {
	    $klarna_company_name = $klarnaData['COMPANY_NAME'];
	    $klarna_reference = $klarnaData['REFERENCE'];
	    $klarna_company = true;
	} else {
	    $klarna_company = false;
	}

	// Get settings for the selected payment method
	$product_rows = array();
	$total_price_incl_vat = 0;
	$total_price_excl_vat = 0;
	$invoice_fee_vat = 0;

	$shipping_tax = $order['details']['BT']->order_shipment_tax;

	$i = 0;
	// Fetch all items from the basket
	foreach ($order['items'] as $item) {

	    // Get SKU
	    $product_rows[$i]['product_sku'] = $item->order_item_sku;
	    $product_rows[$i]['product_name'] = $item->order_item_name;

	    // Get product parent id if exists
	    $product_parent_id = $item->virtuemart_product_id;

	    // TODO Get product attributes if available
	    $weight_subtotal = $cart->products[$item->virtuemart_product_id]->product_weight * $item->product_quantity;

	    /*
	      // TODO
	     * Get product tax rate
	      $product_rows[$i]['taxrate'] = "19.6"; // TODO
	      $total_price_excl_vat += $product_rows[$i]['product_price'];
	      $total_price_incl_vat += $product_rows[$i]['product_price'] *
	      $product_rows[$i]['taxrate'];
	     */
	    // Get quantity
	    $product_rows[$i]['quantity'] = $item->product_quantity;
	    $product_rows[$i]['taxrate'] = $item->product_tax;
	    $product_rows[$i]['product_price'] = $item->product_final_price;
	    $product_rows[$i]['product_attributes'] = ''; // TODO
	}

	$invoice_fee_vat = $order['details']['BT']->order_tax;
	$total_price_excl_vat = $order['details']['BT']->order_subtotal;
	$total_price_incl_vat = $order['details']['BT']->order_subtotal + $order['details']['BT']->order_tax;
	// Instantiate klarna object.
	$klarna = new Klarna_virtuemart();
	$mode = (($method->klarna_mode == 'klarna_live') ? Klarna::LIVE : Klarna::BETA);
	$ssl = ($mode == Klarna::LIVE);
	$klarna->config($country['eid'], $country['secret'], $country['country_code'], null, $country['currency_code'], $mode, $method->klarna_pc_type, $method->klarna_pc_uri, $ssl);

	// If ILT Questions have been asked and filled in,
	// add them to setIncomeInfo
	if (isset($klarnaData['ILT'])) {
	    foreach ($klarnaData['ILT'] as $key => $value) {
		$klarna->setIncomeInfo($key, $value);
	    }
	}

	$kLang = new KlarnaLanguagePack(JPATH_VMKLARNAPLUGIN . '/klarna/language/klarna_language.xml');


	// Fill the good list the we send to Klarna
	$goodsList = array();
	foreach ($product_rows as $product) {
	    $klarna->addArticle(
		    $product['quantity'], utf8_decode($product['product_sku']), utf8_decode(strip_tags($product['product_name'] .
				    (strlen($product['product_attributes']) > 0 ?
					    ' - ' . utf8_decode($product['product_attributes']) : ''))), ((double) (round($product['product_price'] *
			    ($product['taxrate'] ), 2))), (double) $product['taxrate'], 0, KlarnaFlags::INC_VAT
	    );
	}
	// Add shipping
	$klarna->addArticle(
		1, "shippingfee", JText::_('VMPAYMENT_KLARNA_SHIPMENT'), ((double) (round(($order['details']['BT']->order_shipment +
			$order['details']['BT']->order_shipment_tax), 2))), (double) $order['details']['BT']->order_shipment_tax, 0, KlarnaFlags::IS_SHIPMENT + KlarnaFlags::INC_VAT
	);

	// Add invoice fee
	if ($klarna_pclass === -1) { //Only for invoices!
	    $invoice_fee = (double) (round(abs(self::getInvoiceFee($method, $country['country_code'])), 2));
	    if ($invoice_fee > 0) {
		$klarna->addArticle(
			1, "invoicefee", $kLang->fetch('INVOICE_FEE_TITLE', $country['language_code']), $invoice_fee, (double) round($invoice_fee_vat, 2), 0, KlarnaFlags::IS_HANDLING + KlarnaFlags::INC_VAT
		);
	    }
	}
	// Add coupon if there is any
	if ($order['details']['BT']->coupon_discount > 0) {
	    $klarna->addArticle(
		    1, 'discount', JText::_('Discount') . ' ' . $order['details']['BT']->coupon_code, ((int) (round($order['details']['BT']->coupon_discount, 2) * -1)), (double) round($invoice_fee_vat, 2), 0, KlarnaFlags::INC_VAT
	    );
	}
	if (!class_exists('KlarnaAddr'))
	    require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'api' . DS . 'klarnaaddr.php');
	$klarna_shipping = new KlarnaAddr(
			$klarna_email,
			$klarna_phone,
			'',
			utf8_decode($klarna_first_name),
			utf8_decode($klarna_last_name), '',
			utf8_decode($klarna_street),
			$klarna_zip,
			utf8_decode($klarna_city),
			utf8_decode($klarna_country),
			$klarna_house_no,
			$klarna_house_ext
	);

	if ($klarna_company) {
	    $klarna_shipping->isCompany = true;
	    $klarna_shipping->setCompanyName($klarna_company_name);
	    $klarna_comment = $klarna_reference;

	    if ($klarna_shipping->getLastName() == "") {
		$klarna_shipping->setLastName("-");
	    }
	    if ($klarna_shipping->getFirstName() == "") {
		$klarna_shipping->setFirstName("-");
	    }
	}

	// Only allow billing and shipping to be the same for Germany and
	// the Netherlands
	if (strtolower($country['country_code']) == 'nl' ||
		strtolower($country['country_code']) == 'de') {
	    $klarna_billing = $klarna_shipping;
	} else {
	    $klarna_billing = self::getBilling($method, $cart);
	}

	$klarna_flags = KlarnaFlags::RETURN_OCR; // get ocr back from KO.
	if (!isset($klarna_comment))
	    $klarna_comment = "";
	if (!isset($klarna_reference))
	    $klarna_reference = "";
	$klarna->setComment($klarna_comment);
	$klarna->setReference($klarna_reference, "");
	try {
	    $klarna->setAddress(KlarnaFlags::IS_SHIPPING, $klarna_shipping);
	    $klarna->setAddress(KlarnaFlags::IS_BILLING, $klarna_billing);
	    if (isset($klarna_yearly_salary)) {
		$klarna->setIncomeInfo("'yearly_salary'", $klarna_yearly_salary);
	    }
	    $result = $klarna->addTransaction($klarna_pno, ($klarna->getCountry() == KlarnaCountry::DE ||
		    $klarna->getCountry() == KlarnaCountry::NL) ?
			    $klarna_gender : null, $klarna_flags, $klarna_pclass);

	    $status = self::getStatusForCode($result[2]);

	    $result['eid'] = $country['eid'];
	    $result['order_status'] = $status['code'];
	    $result['order_title'] = $status['text'];


	    return $result; //return $result;
	} catch (Exception $e) {
	    self::redirectPaymentMethod('error', htmlentities($e->getMessage()) .
		    "  (#" . $e->getCode() . ")");
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
    public function getAddresses( $pno, $method) {
	// Only available for sweden.
	$settings = self::countryData($method, 'swe');
	$addresses = array();
	$klarna = new Klarna_virtuemart();
	$klarna->config($settings['eid'], $settings['secret'], KlarnaCountry::SE, KlarnaLanguage::SV, KlarnaCurrency::SEK, KLARNA_MODE,$method->klarna_pc_type, $method->klarna_pc_uri, KLARNA_MODE);
	$addresses = $klarna->getAddresses($pno, null, KlarnaFlags::GA_GIVEN);
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
	$klarna->config($settings['eid'], $settings['secret'], $settings['country'], $settings['language'], $settings['currency'], 99, $method->klarna_pc_type, $method->klarna_pc_uri, KLARNA_MODE);
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

    /**
     * Set the ILT properties so they have a HTML valid name
     *
     * @param array $aILT
     * @return array
     */
    public function transformILTnames($ilt) {
	$aReturn = array();
	foreach ($ilt as $sType => $aData) {
	    $aReturn['klarna_ilt[' . $sType . ']'] = $aData;
	}

	return $aReturn;
    }

    /*
     * @deprecated
     */

    public function addOrderDB() {
	$db = JFactory::getDbo();
	$q = "CREATE TABLE IF NOT EXISTS `klarna_orderstatus` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `eid` int(10) NOT NULL,
                `order_id` text NOT NULL,
                `order_status` tinyint(4) NOT NULL,
                `order_title` varchar(255) NOT NULL,
                `invoice_number` text NOT NULL,
                 KEY `id` (`id`))";
	$db->query($q);
    }

    public function fetchPClasses($method) {
	$message = '';
	$success = '';
	foreach ($method->klarna_countries as $country) {
	    // country is CODE 3==> converting to 2 letter country
	    //$country = self::convertCountryCode($method, $country);
	    $lang = self::getLanguageForCountry($method, $country);
	    $flag = "<img src='". JURI::root() . VMKLARNAPLUGINWEBROOT . "/klarna/assets/images/share/flags/". $lang . ".png' />";
	    try {
		$settings = self::getCountryData($method, $country);

		$klarna = new Klarna_virtuemart();
		$klarna->config($settings['eid'], $settings['secret'], $settings['country'], $settings['language'], $settings['currency'], (($method->klarna_mode == 'klarna_live') ?Klarna::LIVE : Klarna::BETA), $method->klarna_pc_type, $method->klarna_pc_uri, true);

		$klarna->fetchPClasses($country);
		$success .= '<span style="padding: 5px;">' . $flag . " " .
			self::getCountryName($country) . '</span>';
	    } catch (Exception $e) {
		$message .= '<br><span style="font-size: 15px;">' .
			$flag . " " . self::getCountryName($country) .
			": " . $e->getMessage() . ' Error Code #' .
			$e->getCode() . '</span></br>';
	    }
	}
	if (strlen($message) > 2) {
	    echo $message;
	}
	if (strlen($success) > 2) {
	    $notice = '<br><span style="font-size: 15px;">' .
		    'PClasses fetched for : ' . $success . '</span>';
	    echo $notice;
	}
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

	if (isset($_SESSION['klarna_payment_id'])) {
	    $pid = $_SESSION['klarna_payment_id'];
	    unset($_SESSION['klarna_payment_id']);
	}
	$_SESSION['klarna_error'] = addslashes($message);
	$mainframe = JFactory::getApplication();
	$mainframe->enqueueMessage($html);
	$mainframe->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart'), JText::_('COM_VIRTUEMART_CART_ORDERDONE_DATA_NOT_VALID'));
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
	global $db;
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
			Klarna::LIVE : Klarna::BETA), $method->klarna_pc_type, $method->klarna_pc_uri, true);
	// Update Ordernumber
	$klarna->updateOrderno($invNo, $estoreOrderNo);
	unset($klarna);
    }

    /**
     * Displays the given payment option.
     */
    public function displayPayment(&$klarna_pm, &$pid, &$pm) {

	$html = ' <fieldset>
        <table cellspacing="0" cellpadding="2" border="0" width="100%">
            <tbody>
                <tr>
                    <td colspan="2">
                        <input id="' . $klarna_pm['id'] . '"
                                    type="radio" name="virtuemart_paymentmethod_id"
                                    value="' . $pid . '" />
                        <label for="' . $klarna_pm['id'] . '">
                                 ' . $klarna_pm['module'] . ' </label>
                        </ br>
                    </td>
                </tr>
                <tr>
                    <td>
                        ' . $klarna_pm['fields']['0']['field'] . '
                    <td>
                </tr>
            </tbody>
        </table>
     </fieldset>';
	return $html;
    }

    /**
     * gets Eid and Secret for activated countries.
     */
    public function getEidSecretArray($method) {
	$eid_array = array();
	if ($method->klarna_swe_merchantid != "" && $method->klarna_swe_sharedsecret != "") {
	    $eid_array['se']['secret'] = $method->klarna_swe_sharedsecret;
	    $eid_array['se']['eid'] = (int) $method->klarna_swe_merchantid;
	}

	if ($method->klarna_nor_merchantid != "" && $method->klarna_nor_sharedsecret != "") {
	    $eid_array['no']['secret'] = $method->klarna_nor_sharedsecret;
	    $eid_array['no']['eid'] = $method->klarna_nor_merchantid;
	}

	if ($method->klarna_deu_merchantid != "" && $method->klarna_deu_sharedsecret != "") {
	    $eid_array['de']['secret'] = $method->klarna_deu_sharedsecret;
	    $eid_array['de']['eid'] = $method->klarna_deu_merchantid;
	}

	if ($method->klarna_nld_merchantid != "" && $method->klarna_nld_sharedsecret != "") {
	    $eid_array['nl']['secret'] = $method->klarna_nld_sharedsecret;
	    $eid_array['nl']['eid'] = $method->klarna_nld_merchantid;
	}

	if ($method->klarna_dnk_merchantid != "" && $method->klarna_dnk_sharedsecret != "") {
	    $eid_array['dk']['secret'] = $method->klarna_dnk_sharedsecret;
	    $eid_array['dk']['eid'] = $method->klarna_dnk_merchantid;
	}

	if ($method->klarna_fin_merchantid != "" && $method->klarna_fin_sharedsecret != "") {
	    $eid_array['fi']['secret'] = $method->klarna_fin_sharedsecret;
	    $eid_array['fi']['eid'] = $method->klarna_fin_merchantid;
	}

	return $eid_array;
    }

    /**
     * Sets PCUri
     * @TODO JOOMLA OR JSON
     * @deprecated
     */
    public function getPCUri( ) {
// TO TEST
	//if ($method->klarna_pc_type == "mysql") {
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
	//    $pcURI = $method->klarna_pc_uri;
	//}
	return $pcURI;
    }

    public function getTotalSum() {
	global $vars;
	$totalSum = $vars['order_total'];
	return $totalSum;
    }

    public function getLocalTemplates() {
	$kLoc = JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'tmpl' . DS . 'campaigns' . DS;
	$aTemplates = scandir($kLoc);

	$aResult = array();

	foreach ($aTemplates as $sDir) {
	    if (($sDir != "." || $sDir != "..") &&
		    file_exists($kLoc . "/" . $sDir . "/campaign.xml")) {
		$oXml = simplexml_load_file($kLoc . "/" . $sDir .
			"/campaign.xml", 'SimpleXMLElement', LIBXML_NOCDATA);
		$aXml = self::xmlToArray($oXml, 0);

		$aResult[$aXml['name']] = $aXml;
		$aResult[$aXml['name']]['shown'] = false;
		$aResult[$aXml['name']]['name'] = $sDir;
		$aResult[$aXml['name']]['active'] =
			(KLARNA_SPEC_ACTIVE_TEMPLATE == $aXml['name']);
	    }
	}

	return $aResult;
    }

    public function getLocalTemplate($sTemplateName) {

	$kLoc = JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'tmpl' . DS . 'campaigns' . DS;
	$aTemplates = scandir($kLoc);

	$aResult = array();

	foreach ($aTemplates as $sDir) {
	    if ($sDir == $sTemplateName) {
		$oXml = simplexml_load_file($kLoc . "/" . $sDir .
			"/campaign.xml", 'SimpleXMLElement', LIBXML_NOCDATA);

		$aResult = self::xmlToArray($oXml, 0);
		$aResult['shown'] = false;
		$aResult['name'] = $sDir;
		$aResult['active'] =
			(KLARNA_SPEC_ACTIVE_TEMPLATE == $oXml['name']);
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

    public function checkVersion() {
	$kURL = 'http://static.klarna.com/external/msbo/virtuemart.latest.txt';
	$kLatest = file_get_contents($kURL);

	if ($kLatest != "") {
	    if (version_compare($kLatest, KLARNA_MODULE_VERSION, '>')) {
		$html = '
                <div class="klarna_update_box">
                    <span class="klarna_update_logo">
                        <img src="' . JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'images' . DS . 'logo' . DS . 'klarna_logo.png" border="0" />
                    </span>
                    <span class="klarna_update_icon">
                        <img src="src="' . JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'images' . DS . 'share' . 'klarna_update_available.png" border="0" />
                    </span>
                    <div class="klarna_update_info">
                        <span class="klarna_update_header">A newer version of your current module is available.</span>
                        <div class="clear"></div>
                        <span class="klarna_update_text">Please visit <a href="http://integration.klarna.com/" target="_blank" style="text-decoration: underline; font-weight: bold">http://integration.klarna.com/</a> for more details.</span>
                    </div>
                    <div class="klarna_version_box">
                        <span class="klarna_update_version_text">Your version: <span class="klarna_version_number"><?php echo KLARNA_MODULE_VERSION;?> </span></span>
                        <div class="clear"></div>
                        <span class="klarna_update_version_text">Latest version: <span class="klarna_version_number"><?php echo $kLatest; ?></span></span>
                    </div>
                    <div class="clear"></div>
                </div>';

		return $html;
	    } else {
		return null;
	    }
	} else {
	    return null;
	}
    }

    private function getStatusForCode($code) {
	$status = array();
	switch ($code) {
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
	return $status;
    }

    public function checkOrderStatus($order_id, $order_long, $method, $tabl) {
	global $vendor_country;
	$settings = self::countryData($method, $vendor_country);
	try {
	    $klarna = new Klarna_virtuemart();
	    $klarna->config($settings['eid'], $settings['secret'], $settings['country'], $settings['language'], $settings['currency'], ((KLARNA_MODE == 1) ?
			    Klarna::LIVE : Klarna::BETA), $method->klarna_pc_type, $method->klarna_pc_uri, true);
	    $os = $klarna->checkOrderStatus($order_id, 1);
	} catch (Exception $e) {
	    $msg = $e->getMessage() . ' #' . $e->getCode() . ' </br>';
	    return $msg;
	}
	$os = self::getStatusForCode($os);
	$code = mysql_real_escape_string($os['code']);
	$text = mysql_real_escape_string($os['text']);
	$order_long = mysql_real_escape_string($order_long);
	$db = JFactory::getDbo();
	$q = 'UPDATE klarna_orderstatus SET order_status = ' . $code .
		', order_title = "' . $text . '" WHERE order_id="' .
		$order_long . "'";

	$db->setQuery($q);
	if ($db->query() == false) {
	    JError::raiseWarning(1, 'INSERT INTO klarna_orderstatus Failed');
	}
	return $text;
    }

    /**
     * Get the shipToAddress which might differ from default address.
     */
    public function getShipToAddress($cart) {
	$shipTo = (($cart->ST == 0) ? $cart->BT : $cart->ST);

	$r = array();
	$r['first_name'] = $shipTo['first_name'];
	$r['last_name'] = $shipTo['last_name'];
	$r['company_name'] = $shipTo['company_name'];
	$r['phone'] = $shipTo['phone_1'];
	$r['street'] = $shipTo['address_1'];
	$r['city'] = $shipTo['city'];
	$r['country'] = @ShopFunctions::getCountryByID($shipTo['virtuemart_country_id'], 'country_3_code');

	$r['state'] = $shipTo['state'];
	$r['zip'] = $shipTo['zip'];
	$r['title'] = $shipTo['title'];
	return $r;
    }

    /**
     * Return pclasses stored in database.
     */
    public function getPClasses($type = null, $country = '', $method, $countrysettings ) {

	//$settings = self::countryData($method, $country);
	$settings = $countrysettings;
	try {
	    $klarna = new Klarna_virtuemart();
	    $klarna->config($settings['eid'], $settings['secret'], $settings['country'], $settings['language'], $settings['currency'], (($method->klarna_mode == 'klarna_live') ?
			    Klarna::LIVE : Klarna::BETA), $method->klarna_pc_type, $method->klarna_pc_uri, true);
	    return $klarna->getPClasses($type );
	} catch (Exception $e) {

	}
    }

}

