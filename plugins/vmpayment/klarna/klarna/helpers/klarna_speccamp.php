<?php

defined('_JEXEC') or die('Restricted access');

/**
 *
 * a special type of Klarna
 * @author Valérie Isaksen
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

class klarna_speccamp {

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
    private $code = "klarna_SpecCamp";
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

    function __construct($method, $cart, $vendor_currency) {
	$this->shipTo = KlarnaHandler::getShipToAddress($cart);

	// Set country and currency set in the store.
	$this->country = $this->shipTo['country'];
	 //$this->country = KlarnaHandler::convertCountryInt($this->shipTo['country']);
	$country2 = KlarnaHandler::convertCountry($method, $this->shipTo['country']);
	$this->currency = $vendor_currency; // TODO$_SESSION['vendor_currency'];
	// Get EID and Secret
	$this->eid = KlarnaHandler::getEid($method, $this->country);
	$this->secret = KlarnaHandler::getSecret($method, $this->country);

	// Is Partpay enabled?
	$this->enabled = (in_array('klarna_speccamp', $method->klarna_modules) ? true : false);

	// Set modes
	$this->mode = ($method->klarna_mode == 'klarna_live') ? Klarna::LIVE : Klarna::BETA;
	$this->ssl = ($this->mode == Klarna::LIVE);

	$this->web_root = JURI::base();

	try {
	    $this->klarna = new Klarna_virtuemart();
	    $this->klarna->config($this->eid, $this->secret, $country2, null, $this->currency, $this->mode, $method->klarna_pc_type, $method->klarna_pc_uri, $this->ssl);
	} catch (Exception $e) {
	    unset($this->klarna);
	}
    }

    private function getParams() {
	$aParams = array();
	// Params specific for:
	// ---- Sweden, Denmark, Norway, Finland
	$c = strtolower($this->country);
	if ($c == "swe" || $c == "dnk" || $c == "nor" || $c == "fin") {
	    $aParams["socialNumber"] = "klarna_spec_pnum";
	}
	// Params needed for non-swedish customers
	if ($c != "swe") {
	    $aParams["firstName"] = "klarna_spec_first_name";
	    $aParams["lastName"] = "klarna_spec_last_name";
	    $aParams["street"] = "klarna_spec_street";
	    $aParams["city"] = "klarna_spec_city";
	    $aParams["zipcode"] = "klarna_spec_zip";
	    $aParams["companyName"] = "klarna_spec_company_name";

	    // Specific for Germany and Netherlands
	    if ($c == "deu" || $c == "nld") {    // Germany && Netherlands
		$aParams["gender"] = "klarna_spec_gender";
		$aParams["homenumber"] = "klarna_spec_house";
		$aParams["birth_year"] = "klarna_spec_birth_year";
		$aParams["birth_month"] = "klarna_spec_birth_month";
		$aParams["birth_day"] = "klarna_spec_birth_day";
	    }
	    if ($c == "nld") {    // Netherlands only
		$aParams["house_extension"] = "klarna_spec_house_extension";
	    }
	    if ($c == "dnk") {    // Denmark only
		$aParams["year_salary"] = "klarna_spec_ysalary";
	    }
	}
	// Params that are the same for all countries
	$aParams["phoneNumber"] = "klarna_spec_phone";
	$aParams["emailAddress"] = "klarna_spec_email";
	$aParams["invoice_type"] = "klarna_invoice_type";
	$aParams["reference"] = "klarna_spec_reference";
	$aParams["shipmentAddressInput"] = "klarna_spec_shipment_address";
	$aParams["type"] = "klarna_spec_invoice";
	$aParams["paymentPlan"] = "klarna_spec_paymentPlan";

	return $aParams;
    }

    private function getValues() {
	$aValues = array();
	$c = strtolower($this->country);
	// Values for non-swedish customers.
	if ($c != "swe") {
	    $aValues["firstName"] = $this->shipTo['first_name'];
	    $aValues["lastName"] = $this->shipTo['last_name'];
	    $aValues["street"] = $this->klarna_addr;
	    $aValues["city"] = $this->shipTo['city'];
	    $aValues["zipcode"] = $this->shipTo['zip'];
	    $aValues["companyName"] = $this->shipTo['company_name'];

	    if ($c == "deu" || $c == "nld") {    // Germany && Netherlands
		$aValues["gender"] = $this->klarna_gender;
		$aValues["homenumber"] = $this->klarna_houseNr;
		$aValues["birth_year"] = $this->klarna_bday['year'];
		$aValues["birth_month"] = $this->klarna_bday['month'];
		$aValues["birth_day"] = $this->klarna_bday['day'];
	    }
	    if ($c == "nld") {    // Netherlands only
		$aValues["house_extension"] = $this->klarna_houseExt;
	    }
	    if ($c == "dnk") {
		$aValues["year_salary"] = $this->klarna_year_salary;
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
	if ($this->country == "nld") {
	    $this->klarna_houseExt = $splitAddress[2];
	}
    }

    /**
     * Attempt to fill in some of what we've already filled in if we
     * come back after failing a purchase.
     */
    private function setPreviouslyFilledIn($klarna_data) {
	if (($this->country == "nld" || $this->country == "deu") && isset($klarna_data['PNO'])) {
	    $pno = $klarna_data['PNO'];
	    $this->klarna_bday['year'] = substr($pno, 4, 4);
	    $this->klarna_bday['month'] = substr($pno, 2, 2);
	    $this->klarna_bday['day'] = substr($pno, 0, 2);
	}
	$this->klarna_street = ( (isset($klarna_data['STREET']) && !isset($this->klarna_street)) ? $klarna_data['STREET'] : $this->klarna_street );
	$this->klarna_houseNr = ( (isset($klarna_data['HOUSE_NO']) && !isset($this->klarna_houseNr)) ? $klarna_data['HOUSE_NO'] : $this->klarna_houseNr );
	$this->klarna_houseExt = ( (isset($klarna_data['HOUSE_EXT']) && !isset($this->klarna_houseExt)) ? $klarna_data['HOUSE_EXT'] : $this->klarna_houseExt );
	$this->klarna_gender = ( (isset($klarna_data['GENDER']) && !isset($this->klarna_gender)) ? $klarna_data['GENDER'] : $this->klarna_gender );
	$this->klarna_year_salary = ( (isset($klarna_data['YEAR_SALARY']) && !isset($this->klarna_year_salary)) ? $klarna_data['YEAR_SALARY'] : $this->klarna_year_salary );
    }

    /**
     * Build the Special Campaign module.
     */
    public function specCamp($method, $cart) {
	// If module isn't enabled, don't do anything.
	if ($this->enabled == false) {
	    return 0;
	}
	if (!isset($this->klarna) || !($this->klarna instanceof Klarna_virtuemart)) {
	    return 0;
	}
	if (!class_exists('KlarnaAPI'))
	    require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarnaapi.php');

	$totalSum = $cart->pricesUnformatted['salesPrice'] ;
	$this->paymeny_charge_link = "https://online.klarna.com/villkor.yaws?eid=" . $this->eid . "&charge=0";

	if (!class_exists('KlarnaVm2API'))
	    require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarna_vm2api.php');
	$kCheckout = new KlarnaVm2API($this->country, null, 'spec', $totalSum, KlarnaFlags::CHECKOUT_PAGE, $this->klarna, array(KlarnaPClass::SPECIAL), JPATH_VMKLARNAPLUGIN);
	$kCheckout->addSetupValue('eid', $this->eid);
	$kCheckout->addSetupValue('payment_id', 'virtuemart_paymentmethod_id');
	if (strtolower($this->country) == 'deu') {
	    $vendor_id = 1;
	    $link = JRoute::_('index.php?option=com_virtuemart&view=vendor&layout=tos&virtuemart_vendor_id=' . $vendor_id);
	    $kCheckout->addSetupValue('agb_link', $link);
	}
	$kCheckout->addSetupValue('agreementLink', $this->getTermsLink());
 	$title = JText::_('VMPAYMENT_KLARNA_SPEC_TITLE');

	$description = '<div style="float: right; right: 10px; margin-top: -30px; position: absolute">' .
		'<img src="' . VMKLARNAPLUGINWEBASSETS.'/images/logo/logo_small.png" border="0" /></div>' .
		JText::_('VMPAYMENT_KLARNA_SPEC_TEXT_DESCRIPTION') . '<br/><br/>' .
		'<img src="images/icon_popup.gif" border="0">&nbsp;<a href="https://www.klarna.com" target="_blank" style="text-decoration: underline; font-weight: bold;">Visit Klarna\'s Website</a>';

	$this->klarna_phone = $this->shipTo['phone'];
	$this->klarna_email = JFactory::getUser()->email;
	$this->klarna_reference = $this->shipTo['first_name'] . ' ' . $this->shipTo['last_name'];

	// Get some extra info for Germany and The Netherlands
	if (strtolower($this->country) == "nld" || strtolower($this->country) == "deu") {
	    $this->setGermanDutchData();
	} else {
	    $this->klarna_addr = $this->shipTo['street'];
	}


if (KlarnaHandler::getKlarnaError($klarnaError, $klarnaOption) ) {
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
	if (isset($_SESSION['show_ilt'])) {
	    $kilt = $_SESSION['show_ilt']['questions'];
	    $kCheckout->setIltQuestions($kilt);
	}

	// If module isn't enabled, don't show it.
	if ($this->enabled == false) {
	    return false;
	}

	// Create the html for the register.
	$fields = array();
	$fields[] = array('title' => "", 'field' => $kCheckout->retrieveLayout($aParams, $aValues));
	// $fields[] = array('title' => "", 'field' => $kCheckout->retrieveHTML($aParams, $aValues, null, KlarnaHandler::getLocalTemplate(KLARNA_SPEC_ACTIVE_TEMPLATE)));
	return array('id' => $this->code, 'module' => $title, 'fields' => $fields);
    }

    public function getTermsLink() {
	return 'https://static.klarna.com/external/html/' . KLARNA_SPECIAL_CAMPAIGN . '_' . strtolower($this->country) . '.html';
    }

}

?>
