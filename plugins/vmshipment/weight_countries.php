<?php

if (!defined('_JEXEC'))
    die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');

/**
 * Shipment plugin for weight_countries shipments, like regular postal services
 *
 * @version $Id: weight_countries.php 3220 2011-05-12 20:09:14Z Milbo $
 * @package VirtueMart
 * @subpackage Plugins - shipment
 * @copyright Copyright (C) 2004-2011 VirtueMart Team - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.org
 * @author Valerie Isaksen
 *
 */
if (!class_exists('vmPSPlugin'))
    require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');

class plgVmShipmentWeight_countries extends vmPSPlugin {

    // instance of class
    public static $_this = false;

    function __construct(& $subject, $config) {
	if (self::$_this)
	    return self::$_this;
	parent::__construct($subject, $config);

	$this->_loggable = true;
	$this->tableFields = array('id', 'virtuemart_order_id', 'order_number', 'virtuemart_shipmentmethod_id', 'shipment_name', 'order_weight', 'shipment_weight_unit',
	    'shipment_cost', 'shipment_package_fee', 'tax_id'); //,'created_on','created_by','modified_on','modified_by','locked_on');

	$varsToPush = array('shipment_logos'=>array('','char'),
							  	'countries'=>array(0,'char'),
							  	'zip_start'=>array(0,'int'),
								'zip_stop'=>array(0,'int'),
								'weight_start'=>array(0,'int'),
								'weight_stop'=>array(0,'int'),
								'weight_unit'=>array(0,'char'),
								'cost'=>array(0,'int'),
								'package_fee'=>array(0,'int'),
								'tax_id'=>array(0,'int'),
								'free_shipment'=>array(0,'int')
	);

	$this->setConfigParameterable($this->_configTableFieldName,$varsToPush);

// 		self::$_this
	//$this->createPluginTable($this->_tablename);
	self::$_this = $this;
    }

    /**
     * Create the table for this plugin if it does not yet exist.
     * @author Valérie Isaksen
     */
    protected function getVmPluginCreateTableSQL() {

	return "CREATE TABLE IF NOT EXISTS `" . $this->_tablename . "` (
	    `id` tinyint(1) unsigned NOT NULL AUTO_INCREMENT ,
	    `virtuemart_order_id` int(11) UNSIGNED DEFAULT NULL,
	    `order_number` char(32) DEFAULT NULL,
	    `virtuemart_shipmentmethod_id` mediumint(1) UNSIGNED DEFAULT NULL,
	    `shipment_name` char(255) NOT NULL DEFAULT '',
	    `order_weight` decimal(10,4) DEFAULT NULL,
	    `shipment_weight_unit` char(3) DEFAULT 'KG',
	    `shipment_cost` decimal(10,2) DEFAULT NULL,
	    `shipment_package_fee` decimal(10,2) DEFAULT NULL,
	    `tax_id` smallint(1) DEFAULT NULL,
	    `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
	    `created_by` int(11) NOT NULL DEFAULT 0,
	    `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	    `modified_by` int(11) NOT NULL DEFAULT 0,
	    `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	    `locked_by` int(11) NOT NULL DEFAULT 0,
	      PRIMARY KEY (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Weight Countries Table' AUTO_INCREMENT=1 ;";
    }

    /**
     * This method is fired when showing the order details in the frontend.
     * It displays the shipment-specific data.
     *
     * @param integer $order_number The order Number
     * @return mixed Null for shipments that aren't active, text (HTML) otherwise
     * @author Valérie Isaksen
     * @author Max Milbers
     */
    public function plgVmOnShowOrderFE($psType, $virtuemart_order_id) {

	$db = JFactory::getDBO();
	$q = 'SELECT * FROM `' . $this->_tablename . '` '
		. 'WHERE `virtuemart_order_id` = ' . $virtuemart_order_id;
	$db->setQuery($q);
	if (!($pluginInfo = $db->loadObject())) {
	    JError::raiseWarning(500, $q . " " . $db->getErrorMsg());
	    return '';
	}
	$idName = $this->_idName;
	if (!($this->selectedThis($psType, $this->_name))) {
	    return null;
	}
	return $pluginInfo->$idName;
    }

    /**
     * This event is fired after the order has been stored; it gets the shipment method-
     * specific data.
     *
     * @param int $order_id The order_id being processed
     * @param object $cart  the cart
     * @param array $priceData Price information for this order
     * @return mixed Null when this method was not selected, otherwise true
     * @author Valerie Isaksen
     */
    function plgVmOnConfirmedOrderStoreData($psType, $orderID, VirtueMartCart $cart, $priceData) {
	if (!$this->selectedThisType($psType)) {
	    return null;
	}
	if (!($shipment = $this->getVmPluginMethod($cart->virtuemart_shipmentmethod_id))) {
	    return null; // Another method was selected, do nothing
	}
// 	if (!class_exists('JParameter'))
// 	    require(JPATH_LIBRARIES . DS . 'joomla' . DS . 'html' . DS . 'parameter.php' );

	if (!class_exists('VirtueMartModelOrders'))
	    require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );

// 	$params = new JParameter($shipment->shipment_params);

	$values['order_number'] = VirtueMartModelOrders::getOrderNumber($orderID);
	$values['virtuemart_order_id'] = $orderID;
	$values['shipment_id'] = $cart->virtuemart_shipmentmethod_id;
	$values['shipment_name'] = parent::renderPluginName($shipment);
	$values['order_weight'] = $this->getOrderWeight($cart, $shipment->weight_unit);
	$values['shipment_weight_unit'] = $shipment->weight_unit;
	$values['shipment_cost'] = $shipment->cost;
	$values['shipment_package_fee'] = $shipment->package_fee;
	$values['tax_id'] = $shipment->tax_id;

// 		$this->writeData($values, $this->_tablename);

// 	$this->storePluginInternalData($shipment);
	$this->storePluginInternalData($values);
	return true;
    }

    /**
     * This method is fired when showing the order details in the backend.
     * It displays the shipment-specific data.
     * NOTE, this plugin should NOT be used to display form fields, since it's called outside
     * a form! Use plgVmOnUpdateOrderBE() instead!
     *
     * @param integer $virtuemart_order_id The order ID
     * @param integer $vendorId Vendor ID
     * @param object $_shipInfo Object with the properties 'shipment' and 'name'
     * @return mixed Null for shipments that aren't active, text (HTML) otherwise
     * @author Valerie Isaksen
     */
    public function plgVmOnShowOrderBE($psType, $virtuemart_order_id, $virtuemart_shipmentmethod_id) {
	if (!($this->selectedThisByMethodId($psType,   $virtuemart_shipmentmethod_id))) {
	    return null;
	}
	$html = $this->getOrderShipmentHtml($virtuemart_order_id);
	return $html;
    }

    function getOrderShipmentHtml($virtuemart_order_id) {

	$db = JFactory::getDBO();
	$q = 'SELECT * FROM `' . $this->_tablename . '` '
		. 'WHERE `virtuemart_order_id` = ' . $virtuemart_order_id;
	$db->setQuery($q);
	if (!($shipinfo = $db->loadObject())) {
	    JError::raiseWarning(500, $q . " " . $db->getErrorMsg());
	    return '';
	}

	if (!class_exists('CurrencyDisplay'))
	    require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');

	$currency = CurrencyDisplay::getInstance();
	$tax = ShopFunctions::getTaxByID($shipinfo->tax_id);
	$taxDisplay = is_array($tax) ? $tax['calc_value'] . ' ' . $tax['calc_value_mathop'] : $shipinfo->tax_id;
	$taxDisplay = ($taxDisplay == -1 ) ? JText::_('COM_VIRTUEMART_PRODUCT_TAX_NONE') : $taxDisplay;

	$html = '<table class="admintable">' . "\n";
	$html .=$this->getHtmlHeaderBE();
	$html .= $this->getHtmlRowBE('WEIGHT_COUNTRIES_SHIPPING_NAME', $shipinfo->shipment_name);
	$html .= $this->getHtmlRowBE('WEIGHT_COUNTRIES_WEIGHT', $shipinfo->order_weight . ' ' . ShopFunctions::renderWeightUnit($shipinfo->shipment_weight_unit));
	$html .= $this->getHtmlRowBE('WEIGHT_COUNTRIES_COST', $currency->priceDisplay($shipinfo->shipment_cost, '', false));
	$html .= $this->getHtmlRowBE('WEIGHT_COUNTRIES_PACKAGE_FEE', $currency->priceDisplay($shipinfo->shipment_package_fee, '', false));
	$html .= $this->getHtmlRowBE('WEIGHT_COUNTRIES_TAX', $taxDisplay);
	$html .= '</table>' . "\n";

	return $html;
    }

    function getCosts(VirtueMartCart $cart, $method, $cart_prices) {

	if ($method->free_shipment && $cart_prices['salesPrice'] >= $method->free_shipment) {
	    return 0;
	} else {
	    $orderWeight = parent::getOrderWeight($cart, $method->weight_unit );
	    return ($orderWeight*$method->cost) + $method->package_fee;
	}
    }

    protected function checkConditions($cart, $method, $cart_prices) {


	$orderWeight = parent::getOrderWeight($cart, $method->weight_unit );
	$address = (($cart->ST == 0) ? $cart->BT : $cart->ST);

	$nbShipment = 0;
	$countries = array();
	if (!empty($method->countries)) {
	    if (!is_array($method->countries)) {
		$countries[0] = $method->countries;
	    } else {
		$countries = $method->countries;
	    }
	}
	// probably did not gave his BT:ST address
	if (!is_array($address)) {
	    $address = array();
	    $address['zip'] = 0;
	    $address['virtuemart_country_id'] = 0;
	}
	$weight_cond = $this->_weightCond($orderWeight, $method);

	if (isset($address['zip'])) {
	    $zip_cond = $this->_zipCond($address['zip'], $method);
	} else {
	    //no zip in address data normally occurs only, when it is removed from the form by the shopowner
	    //Todo for  valerie, you may take a look, maybe should be false, or configurable.
	    $zip_cond = true;
	}

	if (!isset($address['virtuemart_country_id']))
	    $address['virtuemart_country_id'] = 0;
	if (in_array($address['virtuemart_country_id'], $countries) || count($countries) == 0) {
	    if ($weight_cond AND $zip_cond) {
		return true;
	    }
	}

	return false;
    }

    private function _weightCond($orderWeight, $method) {
	if ($orderWeight) {

	    $weight_cond = ($orderWeight >= $method->weight_start  AND $orderWeight <= $method->weight_stop
		    OR
		    ($method->weight_start  <= $orderWeight AND ($method->weight_stop == 0) ));
	} else
	    $weight_cond = true;
	return $weight_cond;
    }

    /**
     * Check the conditions on Zip code
     * @param int $zip : zip code
     * @param $params paremters for this specific shiper
     * @author Valérie Isaksen
     * @return string if Zip condition is ok or not
     */
    private function _zipCond($zip, $method) {
	if (!empty($zip)) {
	    $zip_cond = (( $zip >= $method->zip_start  AND $zip <= $method->zip_stop )
		    OR
		    ($method->zip_start <= $zip AND ($method->zip_stop  == 0) ));
	} else {
	    $zip_cond = true;
	}
	return $zip_cond;
    }

    function plgVmOnUpdateOrder($_formData) {

    }

    function plgVmOnUpdateOrderLine($_formData) {

    }

    function plgVmOnEditOrderLineBE($_orderId, $_lineId) {

    }

    function plgVmOnShowOrderLineFE($_orderId, $_lineId) {

    }

}

// No closing tag
