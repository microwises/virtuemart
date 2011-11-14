<?php

if (!defined('_VALID_MOS') && !defined('_JEXEC'))
    die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');

/**
 * @version $Id: standard.php,v 1.4 2005/05/27 19:33:57 ei
 *
 * a special type of 'cash on delivey':
 * its fee depend on total sum
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
 * http://virtuemart.org
 */

if(!class_exists('vmPSPlugin')) require(JPATH_VM_PLUGINS.DS.'vmpsplugin.php');

class plgVmPaymentStandard extends vmPSPlugin {

    /**
     * Create the table for this plugin if it does not yet exist.
     * @author Oscar van Eijk
     */
    protected function _createTable() {
	$_scheme = DbScheme::get_instance();
	$_scheme->create_scheme($this->_tablename);
	$_schemeCols = array(
	    'id' => array(
		'type' => 'int'
		, 'length' => 11
		, 'auto_inc' => true
		, 'null' => false
	    )
	    , 'virtuemart_order_id' => array(
		'type' => 'int'
		, 'length' => 11
		, 'null' => false
	    )
	    , 'payment_name' => array(
		'type' => 'text'
		, 'null' => false
	    )
	    , 'order_number' => array(
		'type' => 'varchar'
		, 'length' => 32
		, 'null' => false
	    )
	    , 'virtuemart_paymentmethod_id' => array(
		'type' => 'bigint'
		, 'length' => 20
		, 'null' => false
	    )
	, 'created_on' => array(
			'type' => 'DATETIME'
	, 'null' => false
	, 'default' =>'0000-00-00 00:00:00'
	)
	, 'created_by' => array(
			'type' => 'int'
	, 'length' => 11
	, 'null' => false
	, 'default' =>0
	)
	, 'modified_on' => array(
			'type' => 'DATETIME'
	, 'null' => false
	, 'default' =>'0000-00-00 00:00:00'
	)
	, 'modified_by' => array(
			'type' => 'int'
	, 'length' => 11
	, 'null' => false
	, 'default' =>0
	)
	);
	$_schemeIdx = array();

	$_scheme->define_scheme($_schemeCols);
	$_scheme->define_index($_schemeIdx);
	if (!$_scheme->scheme(true)) {
	    JError::raiseWarning(500, $_scheme->get_db_error());
	}
	$_scheme->reset();
    }


    /**
     * Reimplementation of vmPaymentPlugin::plgVmConfirmedOrderRenderPaymentForm()
     *
     * @author Valérie Isaksen
     */
    function plgVmConfirmedOrderRenderForm($psType, $order_number, VirtueMartCart $cart, $return_context, &$html, &$new_status) {

	if (!($payment = $this->getPluginMethod($cart->virtuemart_paymentmethod_id))) {
	    return null; // Another method was selected, do nothing
	}
	$params = new JParameter($payment->payment_params);
	$lang = JFactory::getLanguage();
	$filename = 'com_virtuemart';
	$lang->load($filename, JPATH_ADMINISTRATOR);
	$vendorId = 0;

	$payment_info = $params->get('payment_info');

	$html = "";
	$new_status = false;

	if (!class_exists('VirtueMartModelOrders'))
	    require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );

	// END printing out HTML Form code (Payment Extra Info)

	$this->_virtuemart_paymentmethod_id = $cart->virtuemart_paymentmethod_id;
	$dbValues['payment_name'] = parent::renderPluginName($payment,$params);
	$dbValues['order_number'] = $order_number;
	$dbValues['virtuemart_paymentmethod_id'] = $this->_virtuemart_paymentmethod_id;
	$this->storePluginInternalData($dbValues);

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
	if (!$this->selectedThis($virtuemart_payment_id, $psType)) {
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
function getCosts($params, $cart_prices) {
		return $params->get('cost', 0);
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
    protected function checkConditions($cart, $payment, $cart_prices) {

	$params = new JParameter($payment->payment_params);
	$address = (($cart->ST == 0) ? $cart->BT : $cart->ST);

	$amount = $cart_prices['salesPrice'];
	$amount_cond = ($amount >= $params->get('min_amount', 0) AND $amount <= $params->get('max_amount', 0)
			OR
			($params->get('min_amount', 0) <= $amount AND ($params->get('max_amount', '') == '') ));

	$countries = array();
	$country_list = $params->get('countries');
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