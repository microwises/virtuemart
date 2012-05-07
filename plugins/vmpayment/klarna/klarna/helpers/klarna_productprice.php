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
class klarna_productPrice
{

	private $klarna;
	private $cData;
	private $path;

	public function __construct($cData)
	{

		$this->path = JPATH_VMKLARNAPLUGIN . '/klarna/';
		if (!class_exists('ShopFunctions'))
			require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'shopfunctions.php');

		$this->cData = $cData;
		//$this->currencyId = ShopFunctions::getCurrencyIDByName($this->cData['currency_code']);
		vmdebug('klarna_productPrice', $this->cData);
		try {
			$this->klarna = new Klarna_virtuemart();
			$this->klarna->config($this->cData['eid'], $this->cData['secret'], $this->cData['country'], $this->cData['language'], $this->cData['currency'], $this->cData['mode'], VMKLARNA_PC_TYPE, KlarnaHandler::getKlarna_pc_type(), false);
		} catch (Exception $e) {
			vmDebug('klarna_productPrice', $e->getMessage(), $this->cData);
			unset($this->klarna);
		}
	}

	private function showPP($product)
	{

		if (!isset($this->klarna) || !($this->klarna instanceof Klarna_virtuemart)) {
			return false;
		}
		if (!VMKLARNA_SHOW_PRODUCTPRICE) {
			return false;
		}
		// the price is in the vendor currency
// convert price in NLD currency= euro

		$price = KlarnaHandler::convertPrice($product->prices['salesPrice'], 'EUR');

		if (strtolower($this->cData['country_code']) == 'nl' && $price > 250) {
			vmDebug('showPP', 'dont show price for NL', $this->cData['country_code'], $price);
			return false;
		}

		if ($price <= $this->cData['min_amount'] AND !empty($this->cData['min_amount'])) {
			return false;
		}
		return true;
	}


	public function showProductPrice($product, $payment_element)
	{
		if (!$this->showPP($product)) {
			return null;
		}

		$html = $this->getHTML($product, $payment_element);
		return $html;
	}

	private function getHTML($product, $payment_element)
	{

		if (!class_exists('KlarnaAPI'))
			require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarnaapi.php');
		if (!class_exists('VirtueMartModelCurrency'))
			require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'currency.php');

		$price = $product->prices['salesPrice'];
		$country = $this->cData['country'];
		$lang = $this->cData['language_code'];

		$types = array(KlarnaPClass::CAMPAIGN, KlarnaPClass::ACCOUNT, KlarnaPClass::FIXED);

		$kCheckout = new KlarnaAPI($country, $lang, 'part', $price, KlarnaFlags::PRODUCT_PAGE, $this->klarna, $types, $this->path);

		if ($country == KlarnaCountry::DE) {
			$kCheckout->addSetupValue('asterisk', '*');
		} else
			$kCheckout->addSetupValue('asterisk', '');
		$kCheckout->setCurrency($this->cData['currency']);
		// TODO : Not top to get setup  values here!
		$this->settings = $kCheckout->getSetupValues();
		if ($price > 0 && count($kCheckout->aPClasses) > 0) {
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
				$sTableHtml .= vmPlugin::renderByLayout('pp_box_template', array('pp_title' => html_entity_decode($pp_title), 'pp_price' => $pp_price, 'country' => $country), $payment_element, 'payment');
				// $sTableHtml .= $kCheckout->retrieveHTML(null, array('pp_title' => html_entity_decode($pp_title), 'pp_price' => $pp_price), JPATH_VMKLARNAPLUGIN . '/klarna/tmpl/pp_box_template.html');
			}

			$aInputValues = array();
			$aInputValues['defaultMonth'] = $sMonthDefault;
			$aInputValues['monthTable'] = $sTableHtml;
			$aInputValues['eid'] = $this->cData['eid'];
			$aInputValues['country'] = KlarnaCountry::getCode($country);
			//$aInputValues['nlBanner'] = (($country == KlarnaCountry::NL) ? '<div class="nlBanner"><img src="' . VMKLARNAPLUGINWEBASSETS . '/images/account/' . $notice . '" /></div>' : "");
			return vmPlugin::renderByLayout('productprice_layout', $aInputValues, $payment_element, 'payment');
			// return $kCheckout->retrieveHTML($aInputValues, null, JPATH_VMKLARNAPLUGIN . '/klarna/tmpl/productprice_layout.html');
		}
	}

}
