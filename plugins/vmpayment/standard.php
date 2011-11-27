<?php

if (!defined('_VALID_MOS') && !defined('_JEXEC'))
die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');

/**
 * @version $Id: standard.php,v 1.4 2005/05/27 19:33:57 ei
 *
 * a special type of 'cash on delivey':
 * @author Max Milbers
 * @version $Id$
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

if(!class_exists('vmPSPlugin')) require(JPATH_VM_PLUGINS.DS.'vmpsplugin.php');

class plgVmPaymentStandard extends vmPSPlugin {


	// instance of class
	public static $_this = false;

	function __construct(& $subject, $config) {
		if(self::$_this) return self::$_this;
		parent::__construct($subject, $config);

		$this->_loggable = true;
		$this->tableFields = array('id','virtuemart_order_id','order_number','virtuemart_paymentmethod_id',
						'payment_name','cost','cost','tax_id');//,'created_on','created_by','modified_on','modified_by','locked_on');

		$varsToPush = array('payment_logos'=>array('','char'),
							  	'countries'=>array(0,'char'),
							  	'min_amount'=>array(0,'int'),
								'max_amount'=>array(0,'int'),
								'cost'=>array(0,'int'),
								'tax_id'=>array(0,'int'),
								'payment_info'=>array(0,'string')
	);

	$this->setConfigParameterable($this->_configTableFieldName,$varsToPush);



		self::$_this = $this;
	}

	/**
	 * Create the table for this plugin if it does not yet exist.
	 * @author Valérie Isaksen
	 */
	protected function getVmPluginCreateTableSQL() {

		return "CREATE TABLE IF NOT EXISTS `".$this->_tablename."` (
	    `id` tinyint(1) unsigned NOT NULL AUTO_INCREMENT,
	    `virtuemart_order_id` int(11) UNSIGNED DEFAULT NULL,
	    `order_number` char(32) DEFAULT NULL,
	    `virtuemart_paymentmethod_id` mediumint(1) UNSIGNED DEFAULT NULL,
	    `payment_name` char(255) NOT NULL DEFAULT '',
	    `cost` decimal(10,2) DEFAULT NULL ,
	    `tax_id` int(11) DEFAULT NULL,
	    `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
	    `created_by` int(11) NOT NULL DEFAULT 0,
	    `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	    `modified_by` int(11) NOT NULL DEFAULT 0,
	    `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	    `locked_by` int(11) NOT NULL DEFAULT 0,
	      PRIMARY KEY (`id`)
	    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Payment Standard Table' AUTO_INCREMENT=1 ;";

	}


	/**
	 * Reimplementation of vmPaymentPlugin::plgVmConfirmedOrderRenderPaymentForm()
	 *
	 * @author Valérie Isaksen
	 */
	function plgVmConfirmedOrderRenderForm($psType, $order_number, VirtueMartCart $cart, $return_context, &$html, &$new_status) {
		if (!$this->selectedThisType($psType)) {
			return null;
		}
		if (!($payment = $this->getVmPluginMethod($cart->virtuemart_paymentmethod_id))) {
			return null; // Another method was selected, do nothing
		}
// 		$params = new JParameter($payment->payment_params);
		$lang = JFactory::getLanguage();
		$filename = 'com_virtuemart';
		$lang->load($filename, JPATH_ADMINISTRATOR);
		$vendorId = 0;

		$payment_info = $payment->payment_info;

		$html = "";
		$new_status = false;

		if (!class_exists('VirtueMartModelOrders'))
		require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );

		// END printing out HTML Form code (Payment Extra Info)

		$this->_virtuemart_paymentmethod_id = $cart->virtuemart_paymentmethod_id;
		$dbValues['payment_name'] = parent::renderPluginName($payment);
		$dbValues['order_number'] = $order_number;
		$dbValues['virtuemart_paymentmethod_id'] = $this->_virtuemart_paymentmethod_id;
		$dbValues['cost'] = $payment->cost;
		$dbValues['tax_id'] = $payment->tax_id;
		$this->storePSPluginInternalData($dbValues);

		$html = '<table>' . "\n";
		$html .= $this->getHtmlRow('STANDARD_PAYMENT_INFO', $dbValues['payment_name']);
		if (!empty($payment_info)) {
			$html .= $this->getHtmlRow('STANDARD_INFO', $payment_info);
		}

		$html .= $this->getHtmlRow('STANDARD_ORDER_NUMBER', $order_number);
		$html .= $this->getHtmlRow('STANDARD_AMOUNT', $cart->prices['billTotal']);


		$html .= '</table>' . "\n";

		return true;  // empty cart, send order
	}

	/**
	 * Display stored payment data for an order
	 * @see components/com_virtuemart/helpers/vmPaymentPlugin::plgVmOnShowOrderBE()
	 */
	function plgVmOnShowOrderBE($psType, $virtuemart_order_id, $virtuemart_payment_id) {
		if (!$this->selectedThisByMethodId($psType, $virtuemart_payment_id)) {
			return null; // Another method was selected, do nothing
		}
		$db = JFactory::getDBO();
		$q = 'SELECT * FROM `' . $this->_tablename . '` '
		. 'WHERE `virtuemart_order_id` = ' . $virtuemart_order_id;
		$db->setQuery($q);
		if (!($paymentTable = $db->loadObject())) {
			JError::raiseWarning(500, $db->getErrorMsg());
			return '';
		}

		$html = '<table class="admintable">' . "\n";
		$html .=$this->getHtmlHeaderBE();
		$html .= $this->getHtmlRowBE('STANDARD_PAYMENT_NAME', $paymentTable->payment_name);

		$html .= '</table>' . "\n";
		return $html;
	}

	 function getCosts(VirtueMartCart $cart, $method, $cart_prices) {
		return $method->cost;
	}

	/**
	 * Check if the payment conditions are fulfilled for this payment method
	 * @author: Valerie Isaksen
	 *
	 * @param $cart_prices: cart prices
	 * @param $payment
	 * @return true: if the conditions are fulfilled, false otherwise
	 *
	 */
	protected function checkConditions($cart, $method, $cart_prices) {


// 		$params = new JParameter($payment->payment_params);
		$address = (($cart->ST == 0) ? $cart->BT : $cart->ST);

		$amount = $cart_prices['salesPrice'];
		$amount_cond = ($amount >= $method->min_amount AND $amount <= $method->max_amount
		OR
		($method->min_amount <= $amount AND ($method->max_amount == '') ));

		$countries = array();
		$country_list = $method->countries;
		if (!empty($country_list)) {
			if (!is_array($country_list)) {
				$countries[0] = $country_list;
			} else {
				$countries = $country_list;
			}
		}
		// probably did not gave his BT:ST address
		if (!is_array($address)) {
			$address = array();
			$address['virtuemart_country_id'] = 0;
		}

		if (!isset($address['virtuemart_country_id']))
		$address['virtuemart_country_id'] = 0;
		if (in_array($address['virtuemart_country_id'], $countries) || count($countries) == 0) {
			if ($amount_cond) {
				return true;
			}
		}

		return false;
	}

}

// No closing tag
