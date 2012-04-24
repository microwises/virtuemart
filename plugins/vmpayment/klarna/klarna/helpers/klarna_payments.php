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
class klarna_payments {

    // Estore ID
    private $eid;
    // Estore Shared Secret
    private $secret;
    // LIVE or BETA
    private $mode;
    // SSL or not
    private $ssl;
    // Klarna API instance
    private $klarna;
    // Klarna Checkout instance
    private $kCheckout;
    // Country
    private $country;
    private $country_code_3;
    //lang
    private $lang;
    // Currency
    private $currency;
    private $virtuemart_currency_id;
    // Web Root directory
    private $web_root;
    // Title
    private $title;
    // Description
    private $description;
    private $code;
    // Enabled modules
    private $enabled;
    // User information from Virtuemart & Joomla
    private $shipTo;
    // Variables for the the html page.
    private $klarna_addr;
    private $klarna_first_name;
    private $klarna_last_name;
    private $klarna_gender;
    private $klarna_street;
    private $klarna_houseNr;
    private $klarna_houseExt;
    private $klarna_phone;
    private $klarna_email;
    private $klarna_reference;
    private $payment_charge_link;
    private $klarna_year_salary;
    private $splitAddress;
    private $klarna_bday;

    function __construct($method, $country, $cart) {
	$this->shipTo = KlarnaHandler::getShipToAddress($cart);

	// Set country and currency set in the store.

	$cData = KlarnaHandler::countryData($method, $country);

	$this->country = $cData['country_code'];
	$this->country_code_3 = $cData['country_code_3'];
	$this->currency = $cData['country_code_3'];
	$this->virtuemart_currency_id = $cData['virtuemart_currency_id'];
	//$this->currency = $vendor_currency;
	// Get EID and Secret
	$this->eid = $cData['eid'];
	$this->secret = $cData['secret'];
	$this->lang = $cData['language_code'];
	// Is Invoice enabled?
	$this->enabled = true; //(in_array('klarna_invoice', $method->klarna_modules) ? true : false);
	// Set modes
	$this->mode = KlarnaHandler::getKlarnaMode($method);
	$this->ssl = KlarnaHandler::getKlarnaSSL($this->mode);

	$this->web_root = JURI::base();
	try {
	    $this->klarna = new Klarna_virtuemart();
	    //$this->klarna->config($this->cData['eid'], $this->cData['secret'], $this->cData['country_code'], $this->cData['language'], $this->cData['currency'], $this->mode , VMKLARNA_PC_TYPE, KlarnaHandler::getKlarna_pc_type(), false);

	    $this->klarna->config($this->eid, $this->secret, $this->country, $this->lang, $this->currency, $this->mode, VMKLARNA_PC_TYPE, KlarnaHandler::getKlarna_pc_type(), $this->ssl);
	} catch (Exception $e) {
	    VmDebug('klarna_payments', $e);
	    unset($this->klarna);
	}
    }

    private function getParams() {
	if ($this->code == "klarna_invoice") {
	    $kIndex = '';
	} elseif ($this->code == "klarna_partPayment") {
	    $kIndex = 'part_';
	    $aParams["paymentPlan"] = "klarna_part_paymentPlan";
	} elseif ($this->code == "klarna_SpecCamp") {
	    $kIndex = 'spec_';
	    $aParams["paymentPlan"] = "klarna_spec_paymentPlan";
	}
	$aParams = array();
	// Params specific for:
	// ---- Sweden, Denmark, Norway, Finland

	if ($this->country == "se" || $this->country == "dk" || $this->country == "no" || $this->country == "fi") {
	    $aParams["socialNumber"] = "klarna_" . $kIndex . "pnum";
	}
	// Params needed for non-swedish customers
	if ($this->country != "se") {
	    $aParams["firstName"] = "klarna_" . $kIndex . "first_name";
	    $aParams["lastName"] = "klarna_" . $kIndex . "last_name";
	    $aParams["street"] = "klarna_" . $kIndex . "street";
	    $aParams["city"] = "klarna_" . $kIndex . "city";
	    $aParams["zipcode"] = "klarna_" . $kIndex . "zip";
	    $aParams["companyName"] = "klarna_" . $kIndex . "company_name";

	    // Specific for Germany and Netherlands
	    if ($this->country == "de" || $this->country == "nl") {    // Germany && Netherlands
		$aParams["gender"] = "klarna_" . $kIndex . "gender";
		$aParams["homenumber"] = "klarna_" . $kIndex . "house";
		$aParams["birth_year"] = "klarna_" . $kIndex . "birth_year";
		$aParams["birth_month"] = "klarna_" . $kIndex . "birth_month";
		$aParams["birth_day"] = "klarna_" . $kIndex . "birth_day";
	    }
	    if ($this->country == "nl") {    // Netherlands only
		$aParams["house_extension"] = "klarna_" . $kIndex . "house_extension";
	    }
	    if ($this->country == "dk") {    // Denmark only
		$aParams["year_salary"] = "klarna_spec_ysalary";
	    }
	}
	// Params that are the same for all countries
	$aParams["phoneNumber"] = "klarna_" . $kIndex . "phone";
	$aParams["emailAddress"] = "klarna_" . $kIndex . "email";
	$aParams["invoiceType"] = "klarna_invoice_type";
	$aParams["reference"] = "klarna_" . $kIndex . "reference";
	$aParams["shipmentAddressInput"] = "klarna_" . $kIndex . "shipment_address";
	$aParams["type"] = "klarna_invoice";

	return $aParams;
    }

    private function getValues() {
	$aValues = array();
	$c = strtolower($this->country);
	// Values for non-swedish customers.
	$aValues["firstName"] = $this->shipTo['first_name'];
	$aValues["lastName"] = $this->shipTo['last_name'];
	$aValues["street"] = $this->shipTo['street'];
	$aValues["city"] = $this->shipTo['city'];
	$aValues["zipcode"] = $this->shipTo['zip'];
	$aValues["companyName"] = $this->shipTo['company_name'];

	$aValues["gender"] = $this->shipTo['gender'];
	$aValues["homenumber"] = $this->shipTo['houseNr'];
	$aValues["birth_year"] = @$this->shipTo['year'];
	$aValues["birth_month"] = @$this->shipTo['month'];
	$aValues["birth_day"] = @$this->shipTo['day'];
	$aValues["house_extension"] = $this->shipTo['houseExt'];
	$aValues["year_salary"] = $this->klarna_year_salary;

	// Values that are the same for all countries
	$aValues["phoneNumber"] = $this->shipTo['phone'];
	$aValues["socialNumber"] = $this->shipTo['socialNumber'];
	$aValues["emailAddress"] = $this->shipTo['email'];
	$aValues["reference"] = $this->shipTo['reference'];
	$aValues["agreement_link"] = $this->payment_charge_link;
	return $aValues;
    }

    /**
     * Attempt to fill in some of what we've already filled in if we
     * come back after failing a purchase.
     */
    private function setPreviouslyFilledIn($klarna_data) {
	if (($this->country == "nl" || $this->country == "de") && isset($klarna_data['pno'])) {
	    $pno = $klarna_data['pno'];
	    $this->klarna_bday['year'] = substr($pno, 4, 4);
	    $this->klarna_bday['month'] = substr($pno, 2, 2);
	    $this->klarna_bday['day'] = substr($pno, 0, 2);
	}
	$this->klarna_street = ( (isset($klarna_data['street']) &&
		!isset($this->klarna_street)) ? $klarna_data['street'] :
			$this->klarna_street );
	$this->klarna_houseNr = ( (isset($klarna_data['house_no']) &&
		!isset($this->klarna_houseNr)) ? $klarna_data['house_no'] :
			$this->klarna_houseNr );
	$this->klarna_houseExt = ( (isset($klarna_data['house_ext']) &&
		!isset($this->klarna_houseExt)) ? $klarna_data['house_ext'] :
			$this->klarna_houseExt );
	$this->klarna_gender = ( (isset($klarna_data['gender']) &&
		!isset($this->klarna_gender)) ? $klarna_data['gender'] :
			$this->klarna_gender );
	$this->klarna_year_salary = ( (isset($klarna_data['year_salary']) && !isset($this->klarna_year_salary)) ? $klarna_data['year_salary'] : $this->klarna_year_salary );
    }

    /**
     * Build the Invoice module.
     */
    public function invoice($method) {
	if (!class_exists('CurrencyDisplay'))
	    require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');
	$this->code = "klarna_invoice";
	// If module isn't enabled, don't do anything.
	if ($this->enabled == false) {
	    return null;
	}
	if (!isset($this->klarna) || !($this->klarna instanceof Klarna_virtuemart)) {
	    return null;
	}

	$klarna_invoice_fee = KlarnaHandler::getInvoiceFeeInclTax($method, $this->country_code_3);

	$this->payment_link = "https://online.klarna.com/villkor.yaws?eid=" . $this->eid . "&charge=$klarna_invoice_fee";

	$kCheckout = new KlarnaVm2API($this->country, $this->lang, 'invoice', 0, KlarnaFlags::CHECKOUT_PAGE, $this->klarna, null, VMKLARNAPLUGINWEBROOT);
	$kCheckout->addSetupValue('eid', $this->eid);
	$kCheckout->addSetupValue('sum', $klarna_invoice_fee);
	$kCheckout->setInvoiceFee($klarna_invoice_fee);
	$kCheckout->addSetupValue('payment_id', 'virtuemart_paymentmethod_id');
	if (strtolower($this->country) == 'de') {
	    $vendor_id = 1;
	    $link = JRoute::_('index.php?option=com_virtuemart&view=vendor&layout=tos&virtuemart_vendor_id=' . $vendor_id);
	    $kCheckout->addSetupValue('agb_link', $link);
	}

	$lang = KlarnaHandler::getLanguageForCountry($method, $this->country);
	$symbol = KlarnaHandler::getCurrencySymbolForCountry($method, $this->country);

	$currency = CurrencyDisplay::getInstance();
	$display_fee = $currency->priceDisplay($klarna_invoice_fee);

	//$title = str_replace('(+XX)', '(+' . $sFee . ')', $kCheckout->fetchFromLanguagePack('INVOICE_TITLE', $lang, JPATH_VMKLARNAPLUGIN ));
	$title = JText::sprintf('VMPAYMENT_KLARNA_INVOICE_TITLE', $display_fee);
	$description = '<div style="float: right; right: 10px; margin-top: -30px; position: absolute">' .
		'<img src="' . JURI::base() . VMKLARNAPLUGINWEBROOT . '/assets/images/logo/logo_small.png" border="0" /></div>' .
		JText::_('VMPAYMENT_KLARNA_INVOICE_TEXT_DESCRIPTION') . '<br/><br/>' .
		'<img src="images/icon_popup.gif" border="0">&nbsp;<a href="https://www.klarna.com" target="_blank" style="text-decoration: underline; font-weight: bold;">Visit Klarna\'s Website</a>';

	if (KlarnaHandler::getKlarnaError($klarnaError, $klarnaOption)) {
	    $kCheckout->addSetupValue('red_baloon_content', $klarnaError);
	    $kCheckout->addSetupValue('red_baloon_paymentBox', 'klarna_box_' . $klarnaOption);
	    KlarnaHandler::clearKlarnaError();
	}

	// Something went wrong, refill what we can.
	$session = JFactory::getSession();
	$sessionKlarna = $session->get('Klarna', 0, 'vm');

	if (!empty($sessionKlarna)) {
	    $sessionKlarnaData = unserialize($sessionKlarna);
	    $klarnaData = $sessionKlarnaData->KLARNA_DATA;
	    $this->setPreviouslyFilledIn($klarnaData);
	}

	$aParams = $this->getParams();
	$aValues = $this->getValues();

	// Create the html for the register.
	$fields = array();
	$fields[] = array('title' => "", 'field' => $kCheckout->retrieveLayout($aParams, $aValues));

	return array('id' => 'klarna_invoice', 'module' => $title, 'fields' => $fields);
    }

    /**
     * Build the PartPayment module.
     */
    public function partPay($method, $cart) {
	// If module isn't enabled, don't do anything.
	$this->code = "klarna_partPayment";
	if ($this->enabled == false) {
	    return null;
	}
	if (!isset($this->klarna) || !($this->klarna instanceof Klarna_virtuemart)) {
	    return null;
	}

	/* Should contain the shipment Fee */
	/* this price is in vendor currency==> should be converted to shopper currency */
	$totalSum = $cart->pricesUnformatted['salesPrice'];
	if ($totalSum <= 0) {
	    return null;
	}
	$this->paymeny_charge_link = "https://online.klarna.com/villkor.yaws?eid=" . $this->eid . "&charge=0";

	$lang = KlarnaHandler::getLanguageForCountry($method, $this->country);

	$kCheckout = new KlarnaVm2API($this->country, $this->lang, 'part', $totalSum, KlarnaFlags::CHECKOUT_PAGE, $this->klarna, array(KlarnaPClass::ACCOUNT, KlarnaPClass::CAMPAIGN, KlarnaPClass::FIXED), JPATH_VMKLARNAPLUGIN);
	$kCheckout->addSetupValue('payment_id', 'virtuemart_paymentmethod_id');
	$kCheckout->addSetupValue('eid', $this->eid);
	if (strtolower($this->country) == 'de') {
	    $vendor_id = 1;
	    $link = JRoute::_('index.php?option=com_virtuemart&view=vendor&layout=tos&virtuemart_vendor_id=' . $vendor_id);
	    $kCheckout->addSetupValue('agb_link', $link);
	}
	$kCheckout->addMultipleSetupValues(array("web_root" => $this->web_root, "path_js" => $this->web_root . VMKLARNAPLUGINWEBROOT . "/klarna/assets/js/", "path_img" => $this->web_root . VMKLARNAPLUGINWEBROOT . '/klarna/assets/images/', "path_css" => $this->web_root . VMKLARNAPLUGINWEBROOT . '/klarna/assets/css/'));

	if ($totalSum > 0) {
	    $pclasses = $kCheckout->aPClasses;
	    if (empty($pclasses)) {
		$this->enabled = false;
	    }

	    $cheapest = 0;
	    $minimum = '';
	    foreach ($pclasses as $pclass) {
		if ($cheapest == 0 || $pclass['monthlyCost'] < $cheapest) {
		    $cheapest = $pclass['monthlyCost'];
		}
		if ($pclass['pclass']->getMinAmount() < $minimum || $minimum === '') {
		    $minimum = $pclass['pclass']->getMinAmount();
		}
	    }

	    if ($totalSum < $minimum) {
		$this->enabled = false;
	    }
	    if (!class_exists('VirtueMartModelCurrency'))
		require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'currency.php');
	    // Cheapest is in the Klarna country currency, convert it to the current currency display
	    //$currencyDisplay = CurrencyDisplay::getInstance( );
	    //$countryCurrencyId = $this->virtuemart_currency_id;
	    //$sFee = $currencyDisplay->priceDisplay($cheapest, 0, 1,false);
	    $sFee = $kCheckout->getPresentableValuta($cheapest);
	    $title = JText::sprintf('VMPAYMENT_KLARNA_PARTPAY_TITLE', $sFee);
	} else {
	    $title = JText::_('VMPAYMENT_KLARNA_PARTPAY_TITLE_NOSUM');
	}
	$description = '<div style="float: right; right: 10px; margin-top: -30px; position: absolute">' .
		'<img src="' . JURI::base() . VMKLARNAPLUGINWEBROOT . '/images/logo/logo_small.png" border="0" /></div>' .
		JText::_('VMPAYMENT_KLARNA_PARTPAY_TEXT_DESCRIPTION') . '<br/><br/>' .
		'<img src="images/icon_popup.gif" border="0">&nbsp;<a href="https://www.klarna.com" target="_blank" style="text-decoration: underline; font-weight: bold;">Visit Klarna\'s Website</a>';



	if (KlarnaHandler::getKlarnaError($klarnaError, $klarnaOption)) {
	    $kCheckout->addSetupValue('red_baloon_content', $klarnaError);
	    $kCheckout->addSetupValue('red_baloon_paymentBox', 'klarna_box_' . $klarnaOption);
	    KlarnaHandler::clearKlarnaError();
	}

	// Something went wrong, refill what we can.
	if (isset($_SESSION['KLARNA_DATA'])) {
	    $this->setPreviouslyFilledIn($_SESSION['KLARNA_DATA']);
	}

	$aParams = $this->getParams();
	$aValues = $this->getValues();

	// Create the html for the register.
	$fields = array();
	$fields[] = array('title' => "", 'field' => $kCheckout->retrieveLayout($aParams, $aValues));

	// If module isn't enabled, don't show it.
	if ($this->enabled == false) {
	    return 0;
	}

	return array('id' => 'klarna_partPayment', 'module' => $title, 'fields' => $fields);
    }

    /**
     * Build the Special Campaign module.
     */
    public function specCamp($method, $cart) {
	$this->code = "klarna_SpecCamp";
	// If module isn't enabled, don't do anything.
	if ($this->enabled == false) {
	    return 0;
	}
	if (!isset($this->klarna) || !($this->klarna instanceof Klarna_virtuemart)) {
	    return 0;
	}
	if (!class_exists('KlarnaAPI'))
	    require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarnaapi.php');

	$totalSum = $cart->pricesUnformatted['salesPrice'];
	$this->paymeny_charge_link = "https://online.klarna.com/villkor.yaws?eid=" . $this->eid . "&charge=0";

	if (!class_exists('KlarnaVm2API'))
	    require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarna_vm2api.php');
	$kCheckout = new KlarnaVm2API($this->country, $this->lang, 'spec', $totalSum, KlarnaFlags::CHECKOUT_PAGE, $this->klarna, array(KlarnaPClass::SPECIAL), JPATH_VMKLARNAPLUGIN);
	$kCheckout->addSetupValue('eid', $this->eid);
	$kCheckout->addSetupValue('payment_id', 'virtuemart_paymentmethod_id');
	if (strtolower($this->country) == 'de') {
	    $vendor_id = 1;
	    $link = JRoute::_('index.php?option=com_virtuemart&view=vendor&layout=tos&virtuemart_vendor_id=' . $vendor_id);
	    $kCheckout->addSetupValue('agb_link', $link);
	}
	$kCheckout->addSetupValue('agreementLink', $this->getTermsLink());
	$title = JText::_('VMPAYMENT_KLARNA_SPEC_TITLE');

	$description = '<div style="float: right; right: 10px; margin-top: -30px; position: absolute">' .
		'<img src="' . VMKLARNAPLUGINWEBASSETS . '/images/logo/logo_small.png" border="0" /></div>' .
		JText::_('VMPAYMENT_KLARNA_SPEC_TEXT_DESCRIPTION') . '<br/><br/>' .
		'<img src="images/icon_popup.gif" border="0">&nbsp;<a href="https://www.klarna.com" target="_blank" style="text-decoration: underline; font-weight: bold;">Visit Klarna\'s Website</a>';



	if (KlarnaHandler::getKlarnaError($klarnaError, $klarnaOption)) {
	    $kCheckout->addSetupValue('red_baloon_content', $klarnaError);
	    $kCheckout->addSetupValue('red_baloon_paymentBox', 'klarna_box_' . $klarnaOption);
	    KlarnaHandler::clearKlarnaError();
	}
	// Something went wrong, refill what we can.
	if (isset($_SESSION['KLARNA_DATA'])) {
	    $this->setPreviouslyFilledIn($_SESSION['KLARNA_DATA']);
	}

	// If module isn't enabled, don't show it.
	if ($this->enabled == false) {
	    return false;
	}
	$aParams = $this->getParams();
	$aValues = $this->getValues();


	// Create the html for the register.
	$fields = array();
	$fields[] = array('title' => "", 'field' => $kCheckout->retrieveLayout($aParams, $aValues));
	return array('id' => "klarna_SpecCamp", 'module' => $title, 'fields' => $fields);
    }

    public function getTermsLink() {
	return 'https://static.klarna.com/external/html/' . KLARNA_SPECIAL_CAMPAIGN . '_' . strtolower($this->country) . '.html';
    }

}

