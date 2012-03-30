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

class klarna_invoice  {

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
    // Currency
    private $currency;
    // Web Root directory
    private $web_root;
    // Title
    private $title;
    // Description
    private $description;
    private $code = "klarna_invoice";
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
    private $splitAddress;
    private $klarna_bday;

    function __construct($method, $cart, $vendor_currency ) {
	$this->shipTo = KlarnaHandler::getShipToAddress($cart);
	// Set country and currency set in the store.
	$this->country = $this->shipTo['country'];
	$this->currency = $vendor_currency;
	// Get EID and Secret
	$this->eid = KlarnaHandler::getEid($method, $this->shipTo['country']);
	$this->secret = KlarnaHandler::getSecret($method, $this->shipTo['country']);

	// Is Invoice enabled?
	$this->enabled = (in_array('klarna_invoice', $method->klarna_modules) ? true : false);

	// Set modes
	$this->mode = ($method->klarna_mode == 'klarna_live') ? Klarna::LIVE : Klarna::BETA;
	$this->ssl = ($this->mode == Klarna::LIVE);

	$this->web_root = JURI::base();
	try {
	    $this->klarna = new Klarna_virtuemart();
	    $this->klarna->config($this->eid, $this->secret, $this->country, null, $this->currency, $this->mode, $method->klarna_pc_type, $method->klarna_pc_uri, $this->ssl);
	} catch (Exception $e) {
	    unset($this->klarna);
	}
    }

    private function getParams() {
	$aParams = array();
	// Params specific for:
	// ---- Sweden, Denmark, Norway, Finland
	$c = strtolower($this->country);
	if ($c == "se" || $c == "dk" || $c == "no" || $c == "fi") {
	    $aParams["socialNumber"] = "klarna_pnum";
	}
	// Params needed for non-swedish customers
	if ($c != "se") {
	    $aParams["firstName"] = "klarna_first_name";
	    $aParams["lastName"] = "klarna_last_name";
	    $aParams["street"] = "klarna_street";
	    $aParams["city"] = "klarna_city";
	    $aParams["zipcode"] = "klarna_zip";
	    $aParams["companyName"] = "klarna_company_name";

	    // Specific for Germany and Netherlands
	    if ($c == "de" || $c == "nl") {    // Germany && Netherlands
		$aParams["gender"] = "klarna_gender";
		$aParams["homenumber"] = "klarna_house";
		$aParams["birth_year"] = "klarna_birth_year";
		$aParams["birth_month"] = "klarna_birth_month";
		$aParams["birth_day"] = "klarna_birth_day";
	    }
	    if ($c == "nl") {    // Netherlands only
		$aParams["house_extension"] = "klarna_house_extension";
	    }
	}
	// Params that are the same for all countries
	$aParams["phoneNumber"] = "klarna_phone";
	$aParams["emailAddress"] = "klarna_email";
	$aParams["invoiceType"] = "klarna_invoice_type";
	$aParams["reference"] = "klarna_reference";
	$aParams["shipmentAddressInput"] = "klarna_shipment_address";
	$aParams["type"] = "klarna_invoice";

	return $aParams;
    }

    private function getValues() {
	$aValues = array();
	$c = strtolower($this->country);
	// Values for non-swedish customers.
	if ($c != "se") {
	    $aValues["firstName"] = $this->shipTo['first_name'];
	    $aValues["lastName"] = $this->shipTo['last_name'];
	    $aValues["street"] = $this->klarna_addr;
	    $aValues["city"] = $this->shipTo['city'];
	    $aValues["zipcode"] = $this->shipTo['zip'];
	    $aValues["companyName"] = $this->shipTo['company_name'];

	    if ($c == "de" || $c == "nl") {    // Germany && Netherlands
		$aValues["gender"] = $this->klarna_gender;
		$aValues["homenumber"] = $this->klarna_houseNr;
		$aValues["birth_year"] = $this->klarna_bday['year'];
		$aValues["birth_month"] = $this->klarna_bday['month'];
		$aValues["birth_day"] = $this->klarna_bday['day'];
	    }
	    if ($c == "nl") {    // Netherlands only
		$aValues["house_extension"] = $this->klarna_houseExt;
	    }
	}

	// Values that are the same for all countries
	$aValues["phoneNumber"] = $this->klarna_phone;
	$aValues["emailAddress"] = $this->klarna_email;
	$aValues["reference"] = $this->klarna_reference;
	$aValues["agreement_link"] = $this->payment_charge_link;
	return $aValues;
    }

    /**
     * German and dutch purchases need a different structure
     * on the address, and they need gender specified.
     */
    private function setGermanDutchData() {
	$splitAddress = array('', '', '');
	$splitAddress = KlarnaHandler::splitAddress($this->shipTo['street']);
	$this->klarna_addr = $splitAddress[0];
	$this->klarna_houseNr = $splitAddress[1];
	switch (strtolower($this->shipTo['title'])) {
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
	if ($this->country == "nl") {
	    $this->klarna_houseExt = $splitAddress[2];
	}
    }

    /**
     * Attempt to fill in some of what we've already filled in if we
     * come back after failing a purchase.
     */
    private function setPreviouslyFilledIn($klarna_data) {
	if (($this->country == "nl" || $this->country == "de") && isset($klarna_data['PNO'])) {
	    $pno = $klarna_data['PNO'];
	    $this->klarna_bday['year'] = substr($pno, 4, 4);
	    $this->klarna_bday['month'] = substr($pno, 2, 2);
	    $this->klarna_bday['day'] = substr($pno, 0, 2);
	}
	$this->klarna_street = ( (isset($klarna_data['STREET']) &&
		!isset($this->klarna_street)) ? $klarna_data['STREET'] :
			$this->klarna_street );
	$this->klarna_houseNr = ( (isset($klarna_data['HOUSE_NO']) &&
		!isset($this->klarna_houseNr)) ? $klarna_data['HOUSE_NO'] :
			$this->klarna_houseNr );
	$this->klarna_houseExt = ( (isset($klarna_data['HOUSE_EXT']) &&
		!isset($this->klarna_houseExt)) ? $klarna_data['HOUSE_EXT'] :
			$this->klarna_houseExt );
	$this->klarna_gender = ( (isset($klarna_data['GENDER']) &&
		!isset($this->klarna_gender)) ? $klarna_data['GENDER'] :
			$this->klarna_gender );
    }

    /**
     * Build the Invoice module.
     */
    public function invoice($method) {
	// If module isn't enabled, don't do anything.
	if ($this->enabled == false) {
	    return null;
	}
	if (!isset($this->klarna) || !($this->klarna instanceof Klarna_virtuemart)) {
	    return null;
	}


	$klarna_fee = KlarnaHandler::getInvoiceFee($method, $this->country);

	$this->payment_link = "https://online.klarna.com/villkor.yaws?eid=" . $this->eid . "&charge=$klarna_fee";
	if (!class_exists('KlarnaAPI'))
	    require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'KlarnaAPI.php');
	$kCheckout = new KlarnaAPI($this->country, null, 'invoice', 0, KlarnaFlags::CHECKOUT_PAGE, $this->klarna, null, VMKLARNAPLUGINWEBROOT );
	$kCheckout->addSetupValue('eid', $this->eid);
	$kCheckout->addSetupValue('sum', $klarna_fee);
	$kCheckout->setInvoiceFee($klarna_fee);
	$kCheckout->addSetupValue('ajax_path', $this->web_root . VMKLARNAPLUGINWEBROOT . '/klarna/helpers/klarnaAjax.php');
	$kCheckout->addSetupValue('payment_id', 'virtuemart_paymentmethod_id');
	if (strtolower($this->country) == 'de') {
	    $vendor_id=1;
	    $link = JROUTE::_('index.php?option=com_virtuemart&view=vendor&layout=tos&virtuemart_vendor_id=' . $vendor_id);
	    $kCheckout->addSetupValue('agb_link', $link);
	}
	$kCheckout->addMultipleSetupValues(
		array("web_root" => $this->web_root,
		    "path_js" => $this->web_root . VMKLARNAPLUGINWEBROOT . "/klarna/assets/js/",
		    "path_img" => $this->web_root . VMKLARNAPLUGINWEBROOT . '/klarna/assets/images/',
		    "path_css" => $this->web_root . VMKLARNAPLUGINWEBROOT . '/klarna/assets/css/'));

	$lang = KlarnaHandler::getLanguageForCountry($method, $this->country);
	$symbol = KlarnaHandler::getCurrencySymbolForCountry($method, $this->country);
	if (!class_exists('CurrencyDisplay')
		)require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');
	$currency = CurrencyDisplay::getInstance();
	$sFee = $currency->priceDisplay($klarna_fee, '', false);

	$title = str_replace('(+XX)', '(+' . $sFee . ')', $kCheckout->fetchFromLanguagePack('INVOICE_TITLE', $lang, JPATH_VMKLARNAPLUGIN ));

	$description = '<div style="float: right; right: 10px; margin-top: -30px; position: absolute">' .
		'<img src="' . $this->web_root . $cPath . 'checkout/images/logo/logo_small.png" border="0" /></div>' .
		$kCheckout->fetchFromLanguagePack('INVOICE_TEXT_DESCRIPTION', $lang, $cPath_absolute . 'checkout/') . '<br/><br/>' .
		'<img src="images/icon_popup.gif" border="0">&nbsp;<a href="https://www.klarna.com" target="_blank" style="text-decoration: underline; font-weight: bold;">Visit Klarna\'s Website</a>';

	$this->klarna_phone = $this->shipTo['phone'];
	$this->klarna_email = JFactory::getUser()->email;
	$this->klarna_reference = $this->shipTo['first_name'] . ' ' . $this->shipTo['last_name'];

	// Get some extra info for Germany and The Netherlands
	if (strtolower($this->country) == "nl" || strtolower($this->country) == "de") {
	    $this->setGermanDutchData();
	} else {
	    $this->klarna_addr = $this->shipTo['street'];
	}

	if (isset($_SESSION['klarna_error'])) {
	    $kCheckout->addSetupValue('red_baloon_content', $_SESSION['klarna_error']);
	    $kCheckout->addSetupValue('red_baloon_paymentBox', 'klarna_box_' . $_SESSION['klarna_option']);
	    unset($_SESSION['klarna_error']);
	}

	// Something went wrong, refill what we can.
	if (isset($_SESSION['KLARNA_DATA'])) {
	    $this->setPreviouslyFilledIn($_SESSION['KLARNA_DATA']);
	}

	$aParams = $this->getParams();
	$aValues = $this->getValues();

	if (isset($_SESSION['show_ilt'])) {
	    $kilt = $_SESSION['show_ilt']['questions'];
	    $kCheckout->setIltQuestions($kilt);
	}
	// Create the html for the register.
	$fields = array();
	$fields[] = array('title' => "", 'field' => $kCheckout->retrieveHTML($aParams, $aValues));

	return array('id' => $this->code, 'module' => $title, 'fields' => $fields);
    }

}

