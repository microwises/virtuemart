
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
class klarna_productPrice {

    private $klarna;
    private $cData;
    private $path;
    private $webroot;
    private $method;

    public function __construct($method, $product, $cart) {

	$this->path = JPATH_VMKLARNAPLUGIN . '/klarna/';
	// $this->webroot = JURI::Base() ;
	$this->method = $method;

	if (!class_exists('ShopFunctions'))
	    require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'shopfunctions.php');

	// $product->product_currency is always the vendor currency
	//$this->pCurr = ShopFunctions::getCurrencyByID($product->product_currency, 'currency_code_3');
	$this->cData = KlarnaHandler::getcData($method, $cart);
	//$this->currencyId = ShopFunctions::getCurrencyIDByName($this->cData['currency_code']);
	vmdebug('klarna_productPrice', $this->cData);
	try {
	    $iMode = KlarnaHandler::getKlarnaMode($method);
	    $this->klarna = new Klarna_virtuemart();
	    $this->klarna->config($this->cData['eid'], $this->cData['secret'], $this->cData['country'], $this->cData['language'], $this->cData['currency'], $iMode, VMKLARNA_PC_TYPE, KlarnaHandler::getKlarna_pc_type(), false);
	} catch (Exception $e) {
	    vmDebug('klarna_productPrice', $e->getMessage(), $this->cData);
	    unset($this->klarna);
	}
    }

    private function showPP($product) {

	if (!isset($this->klarna) || !($this->klarna instanceof Klarna_virtuemart)) {
	    return false;
	}
	// the price is in the vendor currency
// convert price in NLD currency= euro

	$price = KlarnaHandler::convertPrice($product->prices['basePriceWithTax'], 'EUR');
	$min_amount = 'klarna_min_amount_part_' . strtolower($this->cData['country_code_3']);

	if (strtolower($this->cData['country_code']) == 'nl' && $price > 250) {
	    vmDebug('showPP', 'dont show price for NL', $this->cData['country_code'], $price);
	    return false;
	}

	if ($product->prices['basePriceWithTax'] <= $this->method->$min_amount OR !empty($this->method->$min_amount)) {
	    return false;
	}
	return true;
    }

    /*
     * not used
     *
     */

    public function showCartPrice($order_total) {
	$html = '<div style="float: right; width: 200px; top: -30px; position: relative;">';
	$html .= $this->showProductPrice("", $order_total, KlarnaFlags::CHECKOUT_PAGE);
	$html .= '</div>';
	return $html;
    }

    public function showProductPrice($product, $page = KlarnaFlags::PRODUCT_PAGE) {
	if (!$this->showPP($product)) {
	    return null;
	}

	$html = $this->getHTML($product, $page);
	return $html;
    }

    private function getHTML($product, $page = null) {

	if (!class_exists('KlarnaAPI'))
	    require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarnaapi.php');
	if (!class_exists('VirtueMartModelCurrency'))
	    require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'currency.php');

	$price = $product->prices['basePriceWithTax'];
	//$currency = shopFunctions::getCurrencyByID($product->product_currency, 'currency_code_3');
	$country = $this->cData['country'];
	$lang = $this->cData['language_code'];

	// we will always use the language for the country to get the correct
	// terms and conditions aswell as the correct name for 'Klarna Konto'
	//$lang = KlarnaLanguage::getCode($this->klarna->getLanguageForCountry($country));


	if ($page === null || ($page != KlarnaFlags::PRODUCT_PAGE && $page != KlarnaFlags::CHECKOUT_PAGE)) {
	    $page = KlarnaFlags::PRODUCT_PAGE;
	}
	/*
	  if (!$this->klarna->checkCountryCurrency($country, $currency)) {
	  //return false;
	  }
	 */
	$types = array(KlarnaPClass::CAMPAIGN, KlarnaPClass::ACCOUNT, KlarnaPClass::FIXED);

	$kCheckout = new KlarnaAPI($country, $lang, 'part', $price, $page, $this->klarna, $types, $this->path);
	// $kCheckout->addSetupValue('web_root', VMKLARNAPLUGINWEBROOT);
	// $kCheckout->addSetupValue('path_img', $this->webroot . VMKLARNAPLUGINWEBROOT . 'klarna/assets/images/');
	// $kCheckout->addSetupValue('path_js', VMKLARNAPLUGINWEBROOT . 'klarna/assets/js/');
	// $kCheckout->addSetupValue('path_css', VMKLARNAPLUGINWEBROOT . 'klarna/assets/css/');
	if ($country == KlarnaCountry::DE) {
	    $kCheckout->addSetupValue('asterisk', '*');
	} else
	    $kCheckout->addSetupValue('asterisk', '');
	$kCheckout->setCurrency($this->cData['currency']);
	// TODO : Not top to get setup  values here!
	$this->settings = $kCheckout->getSetupValues();
	if ($price > 0 && count($kCheckout->aPClasses) > 0) {

	    $currencydisplay = CurrencyDisplay::getInstance();
	    $monthlyCost = array();
	    $minRequired = array();

	    $sMonthDefault = null;

	    $sTableHtml = "";
	    // either in vendor's currency, or shipTo Currency

	    $countryCurrencyId = ShopFunctions::getCurrencyIDByName($this->cData['currency_code']);
	    $currency = CurrencyDisplay::getInstance($countryCurrencyId);
	    $fromCurrency = $currency->getCurrencyForDisplay();

	    //$paymentCurrency = CurrencyDisplay::getInstance($this->cart->paymentCurrency);
	    //$totalInPaymentCurrency = $paymentCurrency->priceDisplay( $this->cart->pricesUnformatted['billTotal'],$this->cart->paymentCurrency) ;
	    //$currencyDisplay = CurrencyDisplay::getInstance($this->cart->pricesCurrency);

	    foreach ($kCheckout->aPClasses as $pclass) {
		if ($sMonthDefault === null || $pclass['monthlyCost'] < $sMonthDefault) {

		    $sMonthDefault = $currency->priceDisplay($pclass['monthlyCost'], $countryCurrencyId);
		}

		if ($pclass['pclass']->getType() == KlarnaPClass::ACCOUNT) {
		    $pp_title = JText::_('VMPAYMENT_KLARNA_PPBOX_ACCOUNT');
		} else {
		    $pp_title = $pclass['pclass']->getMonths() . " " . JText::_('VMPAYMENT_KLARNA_PPBOX_TH_MONTH');
		}

		$pp_price = $currency->priceDisplay($pclass['monthlyCost'], $countryCurrencyId);
		$sTableHtml .=vmPlugin::renderByLayout('pp_box_template', array('pp_title' => html_entity_decode($pp_title), 'pp_price' => $pp_price, 'country' => $country), $this->method->payment_element, 'payment');
		// $sTableHtml .= $kCheckout->retrieveHTML(null, array('pp_title' => html_entity_decode($pp_title), 'pp_price' => $pp_price), JPATH_VMKLARNAPLUGIN . '/klarna/tmpl/pp_box_template.html');
	    }

	    $cd = CurrencyDisplay::getInstance($fromCurrency);


	    $aInputValues = array();
	    $aInputValues['defaultMonth'] = $sMonthDefault;
	    $aInputValues['monthTable'] = $sTableHtml;
	    $aInputValues['eid'] = $this->cData['eid'];
	    $aInputValues['country'] = KlarnaCountry::getCode($country);
	    //$aInputValues['nlBanner'] = (($country == KlarnaCountry::NL) ? '<div class="nlBanner"><img src="' . VMKLARNAPLUGINWEBASSETS . '/images/account/' . $notice . '" /></div>' : "");
	    return vmPlugin::renderByLayout('productprice_layout', $aInputValues, $this->method->payment_element, 'payment');
	    // return $kCheckout->retrieveHTML($aInputValues, null, JPATH_VMKLARNAPLUGIN . '/klarna/tmpl/productprice_layout.html');
	}
    }

}
