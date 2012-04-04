
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
    private $pCurr;
    private $path;
    private $webroot;
    private $method;

    public function __construct($method, $product, $cart_country_code_3) {

	$this->path = JPATH_VMKLARNAPLUGIN . '/klarna/';
	// $this->webroot = JURI::Base() ;
	$this->method = $method;

	if (!class_exists('ShopFunctions'))
	    require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'shopfunctions.php');

	$this->pCurr = ShopFunctions::getCurrencyByID($product->product_currency, 'currency_code_3');
	$this->getcData($this->pCurr, $cart_country_code_3);

	try {
	    $iMode = ($method->klarna_mode == 'klarna_live') ? Klarna::LIVE : Klarna::BETA;
	    $this->klarna = new Klarna_virtuemart();
	    $this->klarna->config($this->cData['eid'], $this->cData['secret'], $this->cData['country'], $this->cData['language'], $this->cData['currency'], $iMode, $method->klarna_pc_type, $method->klarna_pc_uri, false);
	} catch (Exception $e) {
	    unset($this->klarna);
	}
    }

    private function showPP($price) {

	if (!isset($this->klarna) || !($this->klarna instanceof Klarna_virtuemart)) {
	    return false;
	}

	if (strtolower($this->cData['country_code']) == 'nl' && $price > 250) {
	    return false;
	}
	return true;
    }

    public function showCartPrice($order_total) {
	$html = '<div style="float: right; width: 200px; top: -30px; position: relative;">';
	$html .= $this->showProductPrice("", $order_total, KlarnaFlags::CHECKOUT_PAGE);
	$html .= '</div>';
	return $html;
    }

    public function showProductPrice($method, $html, $price, $page = KlarnaFlags::PRODUCT_PAGE) {
	if (!$this->showPP($method, $price)) {
	    return false;
	}

	$js = '<script type="text/javascript">jQuery(document).find(".product_price").width("25%");</script>';
	$js .= '<style>';
	$js .= 'div.klarna_PPBox{z-index: 200 !important;}';
	$js .= 'div.cbContainer{z-index: 10000 !important;}';
	$js .= 'div.klarna_PPBox_bottomMid{overflow: visible !important;}';
	$js .= '</style>';
	//$html .= '<br>';
	if ($this->cData['country_code'] == 'nl') {
	    $js .= '<style>.klarna_PPBox_topMid{width: 81%;}</style>';
	}
	$document = &JFactory::getDocument();
	// TODO
	//$document->addScriptDeclaration($js);
	$html = $this->getHTML($price, $this->pCurr, $this->cData['country'], $this->cData['language_code'], $page);
	return $html;
    }

    private function getHTML($price, $currency, $country, $lang = null, $page = null) {
	if (!class_exists('KlarnaAPI'))
	    require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarnaapi.php');
	if (!class_exists('VirtueMartModelCurrency'))
	    require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'currency.php');
//$country= $this->cData['country']; // numeric code
	/*
	  if (!is_numeric($country)) {
	  $country = KlarnaCountry::fromCode($country);
	  } else {
	  $country = intval($country);
	  }
	 * */

	// we will always use the language for the country to get the correct
	// terms and conditions aswell as the correct name for 'Klarna Konto'
	$lang = KlarnaLanguage::getCode($this->klarna->getLanguageForCountry($country));


	if ($page === null || ($page != KlarnaFlags::PRODUCT_PAGE && $page != KlarnaFlags::CHECKOUT_PAGE)) {
	    $page = KlarnaFlags::PRODUCT_PAGE;
	}
	/*
	  if (!$this->klarna->checkCountryCurrency($country, $currency)) {
	  return false;
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
	} else $kCheckout->addSetupValue('asterisk', '');
	$kCheckout->setCurrency($this->cData['currency']);
	// TODO : Not top to get setup  values here!
	$this->settings = $kCheckout->getSetupValues();
	if ($price > 0 && count($kCheckout->aPClasses) > 0) {

	    $currencydisplay = CurrencyDisplay::getInstance();
	    $monthlyCost = array();
	    $minRequired = array();

	    $sMonthDefault = null;

	    $sTableHtml = "";
	    foreach ($kCheckout->aPClasses as $pclass) {
		if ($sMonthDefault === null || $pclass['monthlyCost'] < $sMonthDefault) {
		    $sMonthDefault = $currencydisplay->priceDisplay($pclass['monthlyCost'], '', false);
		}

		if ($pclass['pclass']->getType() == KlarnaPClass::ACCOUNT) {
		    $pp_title = JText::_('VMPAYMENT_KLARNA_PPBOX_ACCOUNT');
		} else {
		    $pp_title = $pclass['pclass']->getMonths() . " " . JText::_('VMPAYMENT_KLARNA_PPBOX_TH_MONTH');
		}

		$pp_price = $currencydisplay->priceDisplay($pclass['monthlyCost'], '', false);
		$sTableHtml .=vmPlugin::renderByLayout('pp_box_template',array('pp_title' => html_entity_decode($pp_title), 'pp_price' => $pp_price),$this->method->payment_element,'payment');
		// $sTableHtml .= $kCheckout->retrieveHTML(null, array('pp_title' => html_entity_decode($pp_title), 'pp_price' => $pp_price), JPATH_VMKLARNAPLUGIN . '/klarna/tmpl/pp_box_template.html');
	    }

	    $notice = "notice_nl.jpg";

	    $aInputValues = array();
	    $aInputValues['defaultMonth'] = $sMonthDefault;
	    $aInputValues['monthTable'] = $sTableHtml;
	    $aInputValues['eid'] = $this->cData['eid'];
	    $aInputValues['country'] = KlarnaCountry::getCode($country);
	    $aInputValues['nlBanner'] = (($country == KlarnaCountry::NL) ? '<div class="nlBanner"><img src="' . $this->webroot . 'images/account/' . $notice . '" /></div>' : "");
		return vmPlugin::renderByLayout('productprice_layout',$aInputValues,$this->method->payment_element,'payment');
	    // return $kCheckout->retrieveHTML($aInputValues, null, JPATH_VMKLARNAPLUGIN . '/klarna/tmpl/productprice_layout.html');
	}
    }

    function _getVendorCountry() {
	if (!class_exists('VirtueMartModelVendor'))
	    JLoader::import('vendor', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'models');
	$virtuemart_vendor_id = 1;
	$model = VmModel::getModel('vendor');
	$vendor = $model->getVendor($virtuemart_vendor_id);

	$vendorAdress = $model->getVendorAdressBT($virtuemart_vendor_id);

	$vendor_country = ShopFunctions::getCountryByID($vendorAdress->virtuemart_country_id, 'country_code_3');

	return $vendor_country;
    }

    /*
     * if client has not given address then get cdata depending on the currency
     * otherwise get info depending on the country
     */

    function getcData($product_currency, $cart_country_code_3 = '') {
	$product_currency = strtoupper($product_currency);
	if (!$cart_country_code_3) {
	    if ($product_currency == 'NOK') {
		$this->cData = KlarnaHandler::countryData($this->method, 'NOR');
	    } else if ($product_currency == 'SEK') {
		$this->cData = KlarnaHandler::countryData($this->method, 'SWE');
	    } else if ($product_currency == 'DKK') {
		$this->cData = KlarnaHandler::countryData($this->method, 'DNK');
	    } else {
		$vendor_country = $this->_getVendorCountry();
		$this->cData = KlarnaHandler::countryData($this->method, $vendor_country);
	    }
	} else {
	    // the user gave an address, get info according to his country
	    $this->cData = KlarnaHandler::countryData($this->method, $cart_country_code_3);
	}
    }

}
