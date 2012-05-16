<?php

defined('_JEXEC') or die('Restricted access');

/**
 *
 * a special type of Klarna
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
class KlarnaHandler {
	static function countriesData() {
		$countriesData = array(
			'NOR' => array(
				'pno_encoding'    => 3,
				'language'        => 97,
				'language_code'   => 'nb',
				'country'         => 164,
				'currency'        => 1,
				'currency_code'   => 'NOK',
				'currency_symbol' => 'kr',
				'country_code'    => 'no'),
			'SWE' => array(
				'pno_encoding'    => 2,
				'language'        => 138,
				'language_code'   => 'sv',
				'country'         => 209,
				'country_code'    => 'se',
				'currency'        => 0,
				'currency_code'   => 'SEK',
				'currency_symbol' => 'kr'),
			'DNK' => array(
				'pno_encoding'    => 5,
				'language'        => 27,
				'language_code'   => 'da',
				'country'         => 59,
				'country_code'    => 'dk',
				'currency'        => 3,
				'currency_code'   => 'DKK',
				'currency_symbol' => 'kr',
			),
			'FIN' => array(
				'pno_encoding'    => 4,
				'language'        => 37,
				'language_code'   => 'fi',
				'country'         => 73,
				'country_code'    => 'fi',
				'currency'        => 2,
				'currency_code'   => 'EUR',
				'currency_symbol' => '&#8364;'
			),
			'NLD' => array(
				'pno_encoding'    => 7,
				'language'        => 101,
				'language_code'   => 'nl',
				'country'         => 154,
				'country_code'    => 'nl',
				'currency'        => 2,
				'currency_code'   => 'EUR',
				'currency_symbol' => '&#8364;',
			),
			'DEU' => array(
				'pno_encoding'    => 6,
				'language'        => 28,
				'language_code'   => 'de',
				'country'         => 81,
				'country_code'    => 'de',
				'currency'        => 2,
				'currency_code'   => 'EUR',
				'currency_symbol' => '&#8364;'
			));
		return $countriesData;
	}

	static function countryData($method, $country) {
		$countriesData = self::countriesData();
		$lower_country = strtolower($country);
		if (array_key_exists(strtoupper($country), $countriesData)) {
			$cData                           = $countriesData[strtoupper($country)];
			$eid                             = 'klarna_merchantid_' . $lower_country;
			$secret                          = 'klarna_sharedsecret_' . $lower_country;
			$invoice_fee                     = 'klarna_invoicefee_' . $lower_country;
			$min_amount                      = 'klarna_min_amount_part_' . $lower_country;
			$active                          = 'klarna_active_' . $lower_country;
			$cData['eid']                    = $method->$eid;
			$cData['secret']                 = $method->$secret;
			$cData['invoice_fee']            = (double)$method->$invoice_fee;
			$cData['country_code_3']         = $country;
			$cData['virtuemart_currency_id'] = ShopFunctions::getCurrencyIDByName($cData['currency_code']);
			$cData['virtuemart_country_id']  = ShopFunctions::getCountryIDByName($country);
			$cData['mode']                   = $method->klarna_mode;
			$cData['min_amount']             = $method->$min_amount;
			$cData['active']                 = $method->$active;
			return $cData;
		} else {
			return NULL;
		}
	}

	public static function getCountryData($method, $country) {
		//$country = self::convertToThreeLetterCode($country);
		return self::countryData($method, $country);
	}

	public static function convertCountry($method, $country) {
		$country_data = self::countryData($method, $country);
		return $country_data['country_code'];
	}


	public static function getLanguageForCountry($method, $country) {
		$country      = self::convertToThreeLetterCode($country);
		$country_data = self::countryData($method, $country);
		return $country_data['language_code'];
	}

	public static function getCurrencySymbolForCountry($method, $country) {
		$country_data = self::countryData($method, $country);
		return $country_data['currency_symbol'];
	}

	public static function getInvoiceFee($method, $country) {
		$invoice_fee = 'klarna_invoicefee_' . strtolower($country);
		return $method->$invoice_fee;
	}

	public static function getInvoiceTaxId($method, $country) {
		$invoice_fee_tax = 'klarna_invoice_tax_id_' . strtolower($country);
		return $method->$invoice_fee_tax;
	}

	public static function getInvoiceFeeInclTax($method, $country) {
		$invoice_fee    = self::getInvoiceFee($method, $country);
		$invoice_tax_id = self::getInvoiceTaxId($method, $country);

		if (!class_exists('calculationHelper')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'calculationh.php');
		}
		if (!class_exists('CurrencyDisplay')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');
		}

		if (!class_exists('VirtueMartModelVendor')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'vendor.php');
		}
		$vendor_id       = 1;
		$vendor_currency = VirtueMartModelVendor::getVendorCurrency($vendor_id);

		$db         = JFactory::getDBO();
		$calculator = calculationHelper::getInstance();
		$currency   = CurrencyDisplay::getInstance();

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

	/*
* @depredecated
*/

	public static function convertToThreeLetterCode($country) {
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

	public static function getKlarnaCountries() {
		$klarna_countries = array("swe", "deu", "dnk", "nld", "fin", "nor");
		return $klarna_countries;
	}

	static function getDataFromEditPayment() {
		$kIndex = 'klarna_';
		//Removes spaces, tabs, and other delimiters.
		$klarna['pno']          = preg_replace('/[ \t\,\.\!\#\;\:\r\n\v\f]/', '', JRequest::getVar($kIndex . 'pnum', ''));
		$klarna['socialNumber'] = preg_replace('/[ \t\,\.\!\#\;\:\r\n\v\f]/', '', JRequest::getVar($kIndex . 'socialNumber'));
		$klarna['phone']        = JRequest::getVar($kIndex . 'phone');
		$klarna['email']        = JRequest::getVar($kIndex . 'emailAddress');
		$klarna['gender']       = JRequest::getVar($kIndex . 'gender');
		$klarna['street']       = JRequest::getVar($kIndex . 'street');
		$klarna['house_no']     = JRequest::getVar($kIndex . 'homenumber');
		$klarna['house_ext']    = JRequest::getVar($kIndex . 'house_extension');
		$klarna['year_salary']  = JRequest::getVar($kIndex . 'ysalary');
		$klarna['reference']    = JRequest::getVar($kIndex . 'reference');
		$klarna['city']         = JRequest::getVar($kIndex . 'city');
		$klarna['zip']          = JRequest::getVar($kIndex . 'zipcode');
		$klarna['first_name']   = JRequest::getVar($kIndex . 'firstName');
		$klarna['last_name']    = JRequest::getVar($kIndex . 'lastName');
		$klarna['invoice_type'] = JRequest::getVar('klarna_invoice_type');
		$klarna['company_name'] = JRequest::getVar('klarna_companyName');
		$klarna['phone']        = JRequest::getVar($kIndex . 'phone');
		switch (JRequest::getVar($kIndex . 'gender')) {
			case KlarnaFlags::MALE :
				$klarna['title'] = JText::_('COM_VIRTUEMART_SHOPPER_FIELD_TITLE_MR');
				break;
			case KlarnaFlags::FEMALE:
				//$this->klarna_gender = KlarnaFlags::FEMALE;
				$klarna['title']     = JText::_('COM_VIRTUEMART_SHOPPER_FIELD_TITLE_MRS');
				break;
		}
		$klarna['birth_day']   = JRequest::getVar($kIndex . 'birth_day', '');
		$klarna['birth_month'] = JRequest::getVar($kIndex . 'birth_month', '');
		$klarna['birth_year']  = JRequest::getVar($kIndex . 'birth_year', '');
		if (isset($klarna['birth_year']) and !empty($klarna['birth_year'])) {
			// due to the select list
			if ($klarna['birth_month'] != 0 and $klarna['birth_month'] != 0) {
				$klarna['birthday']         = $klarna['birth_year'] . "-" . $klarna['birth_month'] . "-" . $klarna['birth_day'];
				$klarna['pno_frombirthday'] = JRequest::getVar($kIndex . 'birth_day') .
					JRequest::getVar($kIndex . 'birth_month') .
					JRequest::getVar($kIndex . 'birth_year');
			} else {
				$klarna['birthday'] = '';
			}
		} else {
			$klarna['birthday'] = '';
		}
		return $klarna;
	}

	private static function getBilling($cData, $order) {
		$bt           = $order['BT'];
		$bill_country = shopFunctions::getCountryByID($bt['virtuemart_country_id'], 'country_2_code');

		//$cData = self::countryData($method, $country);
		$bill_street = $bt['address_1'];
		$bill_ext    = "";
		$bill_number = "";
		if (strtolower($bill_country) == "de" || strtolower($bill_country) == "nl") {
			$splitAddress = array('', '', '');
			$splitAddress = self::splitAddress($bt['address_1']);
			$bill_street  = $splitAddress[0];
			$bill_number  = $splitAddress[1];
			switch ($bt['title']) {
				case JText::_('COM_VIRTUEMART_SHOPPER_FIELD_TITLE_MR'):
					//$this->klarna_gender = KlarnaFlags::MALE;
					break;
				case JText::_('COM_VIRTUEMART_SHOPPER_FIELD_TITLE_MISS'):
				case JText::_('COM_VIRTUEMART_SHOPPER_FIELD_TITLE_MRS'):
					//$this->klarna_gender = KlarnaFlags::FEMALE;
					break;
				default:
					//$this->klarna_gender = NULL;
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

	public static function addTransaction($method, $order, $klarna_pclass) {

		if (!class_exists('KlarnaAddr')) {
			require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'api' . DS . 'klarnaaddr.php');
		}
		$session           = JFactory::getSession();
		$sessionKlarna     = $session->get('Klarna', 0, 'vm');
		$sessionKlarnaData = unserialize($sessionKlarna);
		if (!isset($sessionKlarnaData)) {
			throw new Exception("No klarna Session data set");
		}
		$klarnaData = $sessionKlarnaData->KLARNA_DATA;

		//
		$shipTo  = (!isset($order['details']['ST']) or empty($order['details']['ST']) or count($order['details']['ST']) == 0) ? $order['details']['BT'] : $order['details']['ST'];
		$billTo  = $order['details']['BT'];
		$country = shopFunctions::getCountrybyID($shipTo->virtuemart_country_id, 'country_3_code');
		$cData   = self::countryData($method, $country);

		//$total_price_excl_vat = self::convertPrice($order['details']['BT']->order_subtotal, $cData['currency_code']);
		//$total_price_incl_vat = self::convertPrice($order['details']['BT']->order_subtotal + $order['details']['BT']->order_tax, $cData['currency_code'], $order['details']['BT']->order_currency);

		$mode = KlarnaHandler::getKlarnaMode($method);
		$ssl  = KlarnaHandler::getKlarnaSSL($mode);
		// Instantiate klarna object.
		$klarna = new Klarna_virtuemart();
		$klarna->config($cData['eid'], $cData['secret'], $cData['country_code'], NULL, $cData['currency_code'], $mode, VMKLARNA_PC_TYPE, KlarnaHandler::getKlarna_pc_type(), $ssl);

		// Fill the good list the we send to Klarna
		foreach ($order['items'] as $item) {
			$item_price       = self::convertPrice($item->product_final_price, $cData['currency_code']);
			$item_price       = (double)(round($item_price, 2));
			$item_tax_percent = (double)(round(self::getTaxPercent($item->product_item_price + ($item->product_tax / $item->product_quantity), $item->product_item_price), 2));
			$item_discount    = self::convertPrice($item->product_subtotal_discount / $item->product_quantity, $cData['currency_code']);
			$item_discount    = (double)(abs(round($item_discount, 2)));
			//vmdebug('addarticle', $item->order_item_sku, $item,  $item_tax_percent);
			$klarna->addArticle($item->product_quantity, utf8_decode($item->order_item_sku), utf8_decode(strip_tags($item->order_item_name)), $item_price, (double)$item_tax_percent, $item_discount, KlarnaFlags::INC_VAT);
		}
		// Add shipping
		$shipment             = self::convertPrice($order['details']['BT']->order_shipment + $order['details']['BT']->order_shipment_tax, $cData['currency_code']);
		$shipment_tax_percent = self::getTaxPercent($order['details']['BT']->order_shipment + $order['details']['BT']->order_shipment_tax, $order['details']['BT']->order_shipment);
		$klarna->addArticle(1, "shippingfee", JText::_('VMPAYMENT_KLARNA_SHIPMENT'), ((double)(round(($shipment), 2))), (double)$shipment_tax_percent, 0, KlarnaFlags::IS_SHIPMENT + KlarnaFlags::INC_VAT);

		// Add invoice fee
		if ($klarna_pclass == -1) { //Only for invoices!
			$payment             = self::convertPrice($order['details']['BT']->order_payment + $order['details']['BT']->order_payment_tax, $cData['currency_code']);
			$payment_tax_percent = self::getTaxPercent($order['details']['BT']->order_payment + $order['details']['BT']->order_payment_tax, $order['details']['BT']->order_payment);
			if ($payment > 0) {
				//vmdebug('invoicefee', $payment, $payment_tax);
				$klarna->addArticle(1, "invoicefee", JText::_('VMPAYMENT_KLARNA_INVOICE_FEE_TITLE'), ((double)(round(($payment), 2))), (double)round($payment_tax_percent, 2), 0, KlarnaFlags::IS_HANDLING + KlarnaFlags::INC_VAT);
			}
		}
		// Add coupon if there is any
		if ($order['details']['BT']->coupon_discount > 0) {
			$coupon_discount = self::convertPrice(round($order['details']['BT']->coupon_discount, $cData['currency_code']));
			//vmdebug('discount', $coupon_discount);
			$klarna->addArticle(1, 'discount', JText::_('VMPAYMENT_KLARNA_DISCOUNT') . ' ' . $order['details']['BT']->coupon_code, ((int)(round($coupon_discount, 2) * -1)), 0, 0, KlarnaFlags::INC_VAT);
		}

		try {
			$klarna_shipping = new KlarnaAddr(
				$order['details']['BT']->email,
				$shipTo->phone_1,
				'',
				utf8_decode($shipTo->first_name),
				utf8_decode($shipTo->last_name), '',
				utf8_decode($shipTo->address_1),
				$shipTo->zip,
				utf8_decode($shipTo->city),
				utf8_decode($cData['country']),
				$shipTo->house_no,
				$shipTo->address_2
			);
		} catch (Exception $e) {
			VmInfo($e->getMessage());
			return false;
		}
		if ($klarnaData['invoice_type'] == 'company') {
			$klarna_shipping->isCompany = true;
			$klarna_shipping->setCompanyName($klarna_company_name);
			$klarna_comment = $shipTo->first_name . ' ' . $shipTo->last_name; //$klarnaData['reference'];

			if ($klarna_shipping->getLastName() == "") {
				$klarna_shipping->setLastName("-");
			}
			if ($klarna_shipping->getFirstName() == "") {
				$klarna_shipping->setFirstName("-");
			}
		} else {
			$klarna_reference = "";
			$klarna_comment   = "";
		}

		// Only allow billing and shipping to be the same for Germany and the Netherlands
		if (VMKLARNA_SHIPTO_SAME_AS_BILLTO) {
			$klarna_billing = $klarna_shipping;
		} else {
			$klarna_billing = self::getBilling($cData, $order);
		}


		$klarna_flags = KlarnaFlags::RETURN_OCR; // get ocr back from KO.

		$klarna->setComment($klarna_comment);
		$klarna->setReference($klarna_reference, "");
		$pno = self::getPNOfromOrder($billTo, $country);
		try {
			$klarna->setAddress(KlarnaFlags::IS_SHIPPING, $klarna_shipping);
			$klarna->setAddress(KlarnaFlags::IS_BILLING, $klarna_billing);
			if (isset($klarnaData['year_salary'])) {
				$klarna->setIncomeInfo("'yearly_salary'", $klarnaData['year_salary']);
			}

			$result                = $klarna->addTransaction($pno, ($klarna->getCountry() == KlarnaCountry::DE || $klarna->getCountry() == KlarnaCountry::NL) ? $klarnaData['gender'] : NULL, $klarna_flags, $klarna_pclass);
			$result['eid']         = $cData['eid'];
			$result['status_code'] = $result[2];
			$result['status_text'] = JText::_('VMPAYMENT_KLARNA_ORDER_STATUS_TEXT_' . $result[2]);
			return $result; //return $result;
		} catch (Exception $e) {
			$result['status_code'] = KlarnaFlags::DENIED;
			$result['status_text'] = htmlentities($e->getMessage()) . "  (#" . $e->getCode() . ")";
			return $result; //return $result;
			//self::redirectPaymentMethod('error', htmlentities($e->getMessage()) .  "  (#" . $e->getCode() . ")");
		}
		throw new Exception("Something went wrong!");
	}

	static function getTaxPercent($pricewithVAT, $pricewithoutVAT) {
		if ($pricewithoutVAT == 0) {
			return 0;
		}
		return (($pricewithVAT / $pricewithoutVAT) - 1) * 100;
	}

	/**
	 * Returns a collection of addresses that are connected to the
	 * supplied SSN
	 *
	 * @param <type> $pno The SSN of the user. This method is only available
	 * for swedish customers
	 * @return array
	 */
	public static function getAddresses($pno, $settings, $method) {
		// Only available for sweden.
		$addresses = array();
		$klarna    = new Klarna_virtuemart();
		$mode      = KlarnaHandler::getKlarnaMode($method);
		$klarna->config($settings['eid'], $settings['secret'], KlarnaCountry::SE, KlarnaLanguage::SV, KlarnaCurrency::SEK, $mode, VMKLARNA_PC_TYPE, KlarnaHandler::getKlarna_pc_type(), $mode);
		try {
			$addresses = $klarna->getAddresses($pno, NULL, KlarnaFlags::GA_GIVEN);
		} catch (Exception $e) {
			VmInfo($e->getMessage());
		}
		unset($klarna);
		return $addresses;
	}

	public static function fetchPClasses($method) {
		$message = '';
		$success = '';
		$results = array();

		$countries = self::getKlarnaCountries();

		foreach ($countries as $country) {
			$active_country = "klarna_active_" . $country;
			if ($method->$active_country) {
				// country is CODE 3==> converting to 2 letter country
				//$country = self::convertCountryCode($method, $country);
				$lang    = self::getLanguageForCountry($method, $country);
				$flagImg = JURI::root(true) . '/administrator/components/com_virtuemart/assets/images/flag/' . strtolower($lang) . '.png';
				$flag    = "<img src='" . $flagImg . "' />";
				try {
					$settings = self::getCountryData($method, $country);

					$klarna = new Klarna_virtuemart();
					$klarna->config($settings['eid'], $settings['secret'], $settings['country'], $settings['language'], $settings['currency'], KlarnaHandler::getKlarnaMode($method), VMKLARNA_PC_TYPE, KlarnaHandler::getKlarna_pc_type(), true);
// fetch pclass from file
					$klarna->fetchPClasses($country);
					$success .= '<span style="padding: 5px;">' . $flag . " " .
						shopFunctions::getCountryByID($settings['virtuemart_country_id']) . '</span>';
				} catch (Exception $e) {
					$message .= '<br><span style="font-size: 15px;">' .
						$flag . " " . shopFunctions::getCountryByID($settings['virtuemart_country_id']) .
						": " . $e->getMessage() . ' Error Code #' .
						$e->getCode() . '</span></br>';
				}
			}
		}
		$results['msg']    = $message;
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
	public static function redirectPaymentMethod($type = NULL, $message = NULL) {

		$log = utf8_encode($message);
		//Display the error.
		if (strlen($log) > 0) {
			if ($type === NULL) {
				$type = 'message';
			}
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_(urldecode($log)), $type);
		}
		//Redirect to previous page.
		$session                     = JFactory::getSession();
		$sessionKlarna               = new stdClass();
		$sessionKlarna->klarna_error = addslashes($message);
		$session->set('Klarna', serialize($sessionKlarna), 'vm');
		if (isset($_SESSION['klarna_paymentmethod'])) {
			$pid = $_SESSION['klarna_paymentmethod'];
			unset($_SESSION['klarna_paymentmethod']);
		}
		//$_SESSION['klarna_error'] = addslashes($message);
		$app = JFactory::getApplication();
		$app->enqueueMessage($message);
		$app->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart'), JText::_('COM_VIRTUEMART_CART_ORDERDONE_DATA_NOT_VALID'));
	}

	/**
	 *
	 * @param  <type> $address
	 * @return <type>
	 */
	public static function splitAddress($address) {

		$numbers      = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
		$characters   = array(
			'-', '/', ' ', '#', '.', 'a', 'b', 'c', 'd', 'e',
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
	 * @param  <type> $haystack
	 * @param  <type> $needle
	 * @param  <type> $where
	 * @return <type>
	 */
	private static function strpos_arr($haystack, $needle, $where) {

		$defpos = 10000;
		if (!is_array($needle)) {
			$needle = array($needle);
		}
		foreach ($needle as $what) {
			if (($pos = strpos($haystack, $what, $where)) !== false) {
				if ($pos < $defpos) {
					$defpos = $pos;
				}
			}
		}
		return $defpos;
	}



	/**
	 * gets Eid and Secret for activated countries.
	 */
	public static function getEidSecretArray($method) {
		$eid_array = array();
		if (isset($method->klarna_merchantid_swe) && $method->klarna_merchantid_swe != "" && $method->klarna_sharedsecret_swe != "") {
			$eid_array['se']['secret'] = $method->klarna_sharedsecret_swe;
			$eid_array['se']['eid']    = (int)$method->klarna_merchantid_swe;
		}

		if (isset($method->klarna_merchantid_nor) && $method->klarna_merchantid_nor != "" && $method->klarna_sharedsecret_nor != "") {
			$eid_array['no']['secret'] = $method->klarna_sharedsecret_nor;
			$eid_array['no']['eid']    = $method->klarna_merchantid_nor;
		}

		if (isset($method->klarna_merchantid_deu) && $method->klarna_merchantid_deu != "" && $method->klarna_sharedsecret_deu != "") {
			$eid_array['de']['secret'] = $method->klarna_sharedsecret_deu;
			$eid_array['de']['eid']    = $method->klarna_merchantid_deu;
		}

		if (isset($method->klarna_nld_merchantid) && $method->klarna_nld_merchantid != "" && $method->klarna_sharedsecret_nld != "") {
			$eid_array['nl']['secret'] = $method->klarna_sharedsecret_nld;
			$eid_array['nl']['eid']    = $method->klarna_nld_merchantid;
		}

		if (isset($method->klarna_merchantid_dnk) && $method->klarna_merchantid_dnk != "" && $method->klarna_sharedsecret_dnk != "") {
			$eid_array['dk']['secret'] = $method->klarna_sharedsecret_dnk;
			$eid_array['dk']['eid']    = $method->klarna_merchantid_dnk;
		}

		if (isset($method->klarna_merchantid_fin) && $method->klarna_merchantid_fin != "" && $method->klarna_sharedsecret_fin != "") {
			$eid_array['fi']['secret'] = $method->klarna_sharedsecret_fin;
			$eid_array['fi']['eid']    = $method->klarna_merchantid_fin;
		}

		return $eid_array;
	}

	public function xmlToArray($obj, $level = 0) {
		$aResult = array();

		if (!is_object($obj)) {
			return $aResult;
		}

		$aChild = (array)$obj;

		if (sizeof($aChild) > 1) {
			foreach ($aChild as $sName => $mValue) {
				if ($sName == "@attributes") {
					$sName = "_attributes";
				}

				if (is_array($mValue)) {
					foreach ($mValue as $ee => $ff) {
						if (!is_object($ff)) {
							$aResult[$sName][$ee] = $ff;
						} else {
							if (get_class($ff) == 'SimpleXMLElement') {
								$aResult[$sName][$ee] = self::xmlToArray($ff, $level + 1);
							}
						}
					}
				} else {
					if (!is_object($mValue)) {
						$aResult[$sName] = $mValue;
					} else {
						if (get_class($mValue) == 'SimpleXMLElement') {
							$aResult[$sName] = self::xmlToArray($mValue, $level + 1);
						}
					}
				}
			}
		} else {
			if (sizeof($aChild) > 0) {
				foreach ($aChild as $sName => $mValue) {
					if ($sName == "@attributes") {
						$sName = "_attributes";
					}

					if (!is_array($mValue) && !is_object($mValue)) {
						$aResult[$sName] = $mValue;
					} else {
						if (is_object($mValue)) {
							$aResult[$sName] = self::xmlToArray($mValue, $level + 1);
						} else {
							foreach ($mValue as $sNameTwo => $sValueTwo) {
								if (!is_object($sValueTwo)) {
									$aResult[$obj->getName()][$sNameTwo] = $sValueTwo;
								} else {
									if (get_class($sValueTwo) == 'SimpleXMLElement') {
										$aResult[$obj->getName()][$sNameTwo] =
											self::xmlToArray($sValueTwo, $level + 1);
									}
								}
							}
						}
					}
				}
			}
		}

		return $aResult;
	}

	public static function checkOrderStatus($settings, $mode, $klarna_invoice_no) {

		try {
			$klarna = new Klarna_virtuemart();
			$klarna->config($settings['eid'], $settings['secret'], $settings['country'], $settings['language'], $settings['currency'], $mode, VMKLARNA_PC_TYPE, KlarnaHandler::getKlarna_pc_type(), true);
			vmdebug('checkOrderStatus', $klarna);
			$os = $klarna->checkOrderStatus($klarna_invoice_no, 0);
		} catch (Exception $e) {
			$msg = $e->getMessage() . ' #' . $e->getCode() . ' </br>';
			VmError($msg);
			return $msg;
		}
		//$os = self::getStatusForCode($os);
		return $os;
	}


	/**
	 * Return pclasses stored in database.
	 */
	public function getPClasses($type = NULL, $country = '', $mode, $settings) {

		//$settings = self::countryData($method, $country);
		try {
			$klarna = new Klarna_virtuemart();
			$klarna->config($settings['eid'], $settings['secret'], $settings['country'], $settings['language'], $settings['currency'], $mode, VMKLARNA_PC_TYPE, KlarnaHandler::getKlarna_pc_type(), true);
			return $klarna->getPClasses($type);
		} catch (Exception $e) {

		}
	}

	function getVendorCountry($fld = 'country_3_code') {

		if (!class_exists('VirtueMartModelVendor')) {
			JLoader::import('vendor', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'models');
		}
		$virtuemart_vendor_id = 1;
		$model                = VmModel::getModel('vendor');
		$vendorAddress         = $model->getVendorAdressBT($virtuemart_vendor_id);
		$vendor_country       = ShopFunctions::getCountryByID($vendorAddress->virtuemart_country_id, $fld);
		return $vendor_country;
	}

	function getKlarnaError(&$klarnaError, &$klarnaOption) {
		$session       = JFactory::getSession();
		$sessionKlarna = $session->get('Klarna', 0, 'vm');
		if ($sessionKlarna) {
			$sessionKlarnaData = unserialize($sessionKlarna);
			if (isset($sessionKlarnaData->klarna_error)) {
				$klarnaError  = $sessionKlarnaData->klarna_error;
				$klarnaOption = $sessionKlarnaData->klarna_option;
				return true;
			} else {
				return false;
			}
		}
		return false;
	}

	function clearKlarnaError() {
		$session       = JFactory::getSession();
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

	static function getKlarnaMode($method) {
		return (($method->klarna_mode == 'klarna_live') ? Klarna::LIVE : Klarna::BETA);
	}

	static function getKlarnaSSL($mode) {
		return ($mode == Klarna::LIVE);
	}

	static function convertPrice($price, $toCurrency = '') {

		if (!is_int($toCurrency) && !empty($toCurrency)) {
			$toCurrency = ShopFunctions::getCurrencyIDByName($toCurrency);
		}
		$currency     = CurrencyDisplay::getInstance($toCurrency);
		$fromCurrency = $currency->getCurrencyForDisplay();
		$price        = round($currency->convertCurrencyTo($toCurrency, $price, false), 2);
		$cd           = CurrencyDisplay::getInstance($fromCurrency);

		return $price;
	}

	/*
* if client has not given address then get cdata depending on the currency
* otherwise get info depending on the country
*/

	static function getcData($method, $address) {

		if (!isset($address['virtuemart_country_id'])) {
			$vendor_country = KlarnaHandler::getVendorCountry();
			$cData          = self::countryData($method, $vendor_country);
		} else {
			$cart_country_code_3 = ShopFunctions::getCountryByID($address['virtuemart_country_id'], 'country_3_code');
			// the user gave an address, get info according to his country
			$cData = self::countryData($method, $cart_country_code_3);
		}
		return $cData;
	}

	static function getKlarna_pc_type() {
		$safePath = VmConfig::get('forSale_path', 0);
		if ($safePath) {
			return $safePath . "klarna/klarna.json";
		} else {
			return NULL;
		}
	}

	/*
* Sweden: yymmdd-nnnn, it can be sent with or without dash "-" or with or without the two first numbers in the year.
* Finland: ddmmyy-nnnn
* Denmark: ddmmyynnnn
* Norway: ddmmyynnnnn
* Germany: ddmmyyyy
* Netherlands: ddmmyyyy
*/

	static function getPNOfromOrder($billTo, $country) {
		if (($country == "NLD" || $country == "DEU")) {
			$date = explode("-", $billTo->birthday);
			$pno  = $date['2'] . $date['1'] . $date['0'];
		} else {
			$pno = $billTo->social_number;
		}

		return $pno;
	}

	function checkDataFromEditPayment($data) {
		$vm_shopperfields = array("first_name", "last_name", "address_1", "city", "zip", "company", "phone_1");

		$shopperfields_country = array(
			"socialNumber" => array("se", "dk", "no", "fin"),
			"birthday"     => array("de", "nl"),
			"house_no"     => array("nl")
		);
		$country_shopperfields = array(
			"se" => array("socialNumber"),
			"de" => array("birthday", "title"),
			"nl" => array("birthday", "house_no"),
			"no" => array("socialNumber"),
			"dk" => array("socialNumber"),
			"fi" => array("socialNumber"),
		);
		$fields                = $country_shopperfields[$data['country']];
		$found                 = 0;
		foreach ($vm_shopperfields as $vm_field) {
			foreach ($data as $key => $value) {
				if ($key == $vm_field and !empty($data[$key])) {
					$found++;
				}
			}
		}
		if ($found < count($vm_shopperfields)) {
			return false;
		}
		$fields = $country_shopperfields[$data['country']];
		$found  = 0;
		foreach ($country_shopperfields[$data['country']] as $field) {
			foreach ($data as $key => $value) {
				if ($key == $field and !empty($data[$key])) {
					$found++;
				}
			}
		}
		if ($found < count($country_shopperfields[$data['country']])) {
			return false;
		}
		return true;
	}

	static function getKlarnaSpecificShopperFields() {
		return array(
			"SWE" => array("socialNumber", "email"),
			"DNK" => array("socialNumber", "year_salary"),
			"NOR" => array("socialNumber"),
			"FIN" => array("socialNumber"),
			"NLD" => array("birthday", "address_2", "house_no"),
			"DEU" => array("birthday", "address_2")
		);
	}

	function getKlarnaVMGenericShopperFields() {

		return array("first_name", "last_name", "address_1", "city", "zip", "company", "phone_1", "virtuemart_country_id");

	}

	function getByFieds() {
		$required_shopperfields_byfields = array(
			"socialNumber" => array("SWE", "DNK", "NOR", "FIN"),
			"year_salary"  => array("DNK"),
			"address_2",
			"birthday"     => array("DEU", "NLD"),
			"house_no"     => array("NLD"),
			"email"        => array("SWE")
		);
	}

	/**
	 * Get the shipToAddress which might differ from default address.
	 * From VM shopperFields to Klarna Fields
	 */
	public static function getShipToAddress($cart) {
		//vmdebug('getShipToAddress',$cart);
		if (VMKLARNA_SHIPTO_SAME_AS_BILLTO) {
			$shipTo = $cart->BT;
		} else {
			$shipTo = (($cart->ST == 0 or empty($cart->ST)) ? $cart->BT : $cart->ST);
		}
		return self::getKlarnaFieldsFromVmShopperFields($shipTo, $cart->BT['email']);


	}

	static function getKlarnaFieldsFromVmShopperFields($from, $from_email) {
		$klarnaFields = array();
		switch (@$from['title']) {
			case JText::_('COM_VIRTUEMART_SHOPPER_FIELD_TITLE_MR'):
				$klarnaFields['gender'] = KlarnaFlags::MALE;
				break;
			case JText::_('COM_VIRTUEMART_SHOPPER_FIELD_TITLE_MISS'):
			case JText::_('COM_VIRTUEMART_SHOPPER_FIELD_TITLE_MRS'):
				$klarnaFields['gender'] = KlarnaFlags::FEMALE;
				break;
			default:
				$klarnaFields['gender'] = NULL;
				break;
		}

		$klarnaFields['email']        = $from_email;
		$klarnaFields['country']      = @ShopFunctions::getCountryByID(@$from['virtuemart_country_id'], 'country_3_code');
		$klarnaFields['socialNumber'] = @$from['social_number'];
		$klarnaFields['houseNr']      = @$from['house_no'];
		$klarnaFields['houseExt']     = @$from['address_2'];
		$klarnaFields['first_name']   = @$from['first_name'];
		$klarnaFields['last_name']    = @$from['last_name'];
		$klarnaFields['reference']    = $from['first_name'] . ' ' . $from['last_name'];
		$klarnaFields['company_name'] = @$from['company_name'];
		$klarnaFields['phone']        = @$from['phone_1'];
		$klarnaFields['street']       = @$from['address_1'];
		$klarnaFields['city']         = @$from['city'];
		$klarnaFields['country']      = @ShopFunctions::getCountryByID(@$from['virtuemart_country_id'], 'country_3_code');
		$klarnaFields['state']        = @$from['state'];
		$klarnaFields['zip']          = @$from['zip'];
		$klarnaFields['birthday']     = @$from['birthday'];
		if (isset($from['birthday']) and !empty($from['birthday'])) {
			$date = explode("-", $from['birthday']);
			if (is_array($date)) {
				$klarnaFields['year']  = $date['0'];
				$klarnaFields['month'] = $date['1'];
				$klarnaFields['day']   = $date['2'];
			}
		}
		return $klarnaFields;
	}
}

