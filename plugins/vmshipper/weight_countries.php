<?php

if (!defined('_JEXEC'))
    die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');

/**
 * Shipper plugin for weight_countries shippers, like regular postal services
 *
 * @version $Id: weight_countries.php 3220 2011-05-12 20:09:14Z Milbo $
 * @package VirtueMart
 * @subpackage Plugins - shippper
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
if (!class_exists('ShopFunctions'))
    require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'shopfunctions.php');

class plgVmShipperWeight_countries extends vmShipperPlugin {

    /**
     * Constructor
     *
     * For php4 compatability we must not use the __constructor as a constructor for plugins
     * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
     * This causes problems with cross-referencing necessary for the observer design pattern.
     *
     * @param object $subject The object to observe
     * @param array  $config  An array that holds the plugin configuration
     */
    function plgVmShipperWeight_countries(&$subject, $config) {
	$this->_selement = basename(__FILE__, '.php');
	$this->_tablename = '#__virtuemart_order_shipper_' . $this->_selement;
	$this->_createTable();
	parent::__construct($subject, $config);
    }

    /**
     * Create the table for this plugin if it does not yet exist.
     * @author Oscar van Eijk
     */
    protected function _createTable() {
	$scheme = DbScheme::get_instance();
	$scheme->create_scheme($this->_tablename);
	$schemeCols = array(
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
	    , 'order_number' => array(
		'type' => 'varchar'
		, 'length' => 32
		, 'null' => false
	    )
	    , 'virtuemart_shippingcarrier_id' => array(
		'type' => 'bigint'
		, 'length' => 20
		, 'null' => false
	    )
	    , 'shipper_name' => array(
		'type' => 'text'
		, 'null' => false
	    )
	    , 'order_weight' => array(
		'type' => 'int'
		, 'length' => 11
		, 'null' => false
	    )
	    , 'shipping_weight_unit' => array(
		'type' => 'varchar '
		, 'length' => 3
		, 'null' => false
	    )
	    , 'shipper_cost' => array(
		'type' => 'text'
		, 'null' => false
	    )
	    , 'shipper_package_fee' => array(
		'type' => 'int'
		, 'length' => 11
		, 'null' => false
	    )
	    , 'tax_id' => array(
		'type' => 'int'
		, 'length' => 11
		, 'null' => false
	    )
	);
	$schemeIdx = array(
	    'idx_order_shipper' => array(
		'columns' => array('virtuemart_order_id')
		, 'primary' => false
		, 'unique' => false
		, 'type' => null
	    )
	);
	$scheme->define_scheme($schemeCols);
	$scheme->define_index($schemeIdx);
	if (!$scheme->scheme(true)) {
	    JError::raiseWarning(500, 'DbScheme _createTable' . $scheme->get_db_error());
	}
	$scheme->reset();
    }

    /**
     * Get the total weight for the order, based on which the proper shipping rate
     * can be selected.
     * @param object $cart Cart object
     * @return float Total weight for the order
     * @author Oscar van Eijk
     */
    protected function getOrderWeight(VirtueMartCart $cart, $to_weight_unit) {
	$weight = 0;
	foreach ($cart->products as $product) {
	    $weight += ( ShopFunctions::convertWeigthUnit($product->product_weight, $product->product_weight_uom, $to_weight_unit) * $product->quantity);
	}
	return $weight;
    }

    /**
     * This event is fired after the shipping method has been selected. It can be used to store
     * additional shipper info in the cart.
     *
     * @param object $cart Cart object
     * @param integer $selected ID of the shipper selected
     * @return boolean True on succes, false on failures, null when this plugin was not selected.
     * On errors, JError::raiseWarning (or JError::raiseError) must be used to set a message.
     * @author Valérie Isaksen
     */
    public function plgVmOnShipperSelected(VirtueMartCart $cart, $selectedShipper = 0) {
	if (!$this->selectedThisShipper($this->_selement, $selectedShipper)) {
	    return null; // Another shipper was selected, do nothing
	} else {
	    return true;
	}
    }

    /**
     * This method is fired when showing the order details in the frontend.
     * It displays the shipper-specific data.
     *
     * @param integer $order_number The order Number
     * @return mixed Null for shippers that aren't active, text (HTML) otherwise
     * @author Valérie Isaksen
     */
    public function plgVmOnShowOrderShipperFE($virtuemart_order_id) {

	$db = JFactory::getDBO();
	$q = 'SELECT * FROM `' . $this->_tablename . '` '
		. 'WHERE `virtuemart_order_id` = ' . $virtuemart_order_id;
	$db->setQuery($q);
	if (!($shipinfo = $db->loadObject())) {
	    JError::raiseWarning(500, $q . " " . $db->getErrorMsg());
	    return '';
	}
	if (!($this->selectedThisShipper($this->_selement, $shipinfo->virtuemart_shippingcarrier_id))) {
	    return null;
	}
	return $shipinfo->shipper_name;
    }

    /**
     * Select the shipping rate ID, based on the selected shipper in combination with the
     * shipto address (country and zipcode) and the total order weight.
     * @param object $cart Cart object
     * @param int $shipperID Shipper ID, by default taken from the cart
     * @return int Shipping rate ID, -1 when no match is found. Only 1 selected ID will be returned;
     * if more ID's match, the cheapest will be selected.
     */
    protected function selectShippingRate(VirtueMartCart $cart, $selectedShipper = 0) {
	$shipping_carrier_params = $this->getVmShipperParams($cart->vendorId, $cart->virtuemart_shippingcarrier_id);
	$params = new JParameter($shipping_carrier_params);

	if ($selectedShipper == 0) {
	    $selectedShipper = $cart->virtuemart_shippingcarrier_id;
	}
	$address = (($cart->ST == 0) ? $cart->BT : $cart->ST);

	$shipping_carrier_params = $this->getVmShipperParams($cart->vendorId, $selectedShipper);

	$shipping->shipping_name = $params->get('shipping_name');
	$shipping->shipping_rate_vat_id = $params->get('shipping_tax_id');
	$shipping->shipping_value = $this->_getShippingCost($params, $cart);
	return $selectedShipper;
    }

    /**
     * This event is fired after the order has been stored; it gets the shipping method-
     * specific data.
     *
     * @param int $order_id The order_id being processed
     * @param object $cart  the cart
     * @param array $priceData Price information for this order
     * @return mixed Null when this method was not selected, otherwise true
     * @author Valerie Isaksen
     */
    function plgVmOnConfirmedOrderStoreShipperData($orderID, VirtueMartCart $cart, $priceData) {

	if (!($this->selectedThisShipper($this->_selement, $cart->virtuemart_shippingcarrier_id))) {
	    return null;
	}
	$shipping_carrier_params = $this->getVmShipperParams($cart->vendorId, $cart->virtuemart_shippingcarrier_id);
	if (!class_exists('JParameter'))
	    require(JPATH_LIBRARIES . DS . 'joomla' . DS . 'html' . DS . 'parameter.php' );
	$params = new JParameter($shipping_carrier_params);
	if (!class_exists('VirtueMartModelOrders'))
	    require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );
	$values['order_number'] = VirtueMartModelOrders::getOrderNumber($orderID);
	$values['virtuemart_order_id'] = $orderID;
	$values['virtuemart_shippingcarrier_id'] = $cart->virtuemart_shippingcarrier_id;
	$values['shipper_name'] = $this->getThisShipperNameById($cart->virtuemart_shippingcarrier_id);
	$values['order_weight'] = $this->getOrderWeight($cart, $params->get('weight_unit'));
	$values['shipping_weight_unit'] = $params->get('weight_unit');
	$values['shipper_cost'] = $params->get('rate_value');
	$values['shipper_package_fee'] = $params->get('package_fee');
	$values['tax_id'] = $params->get('shipping_tax_id');

	$this->writeShipperData($values, $this->_tablename);
	return true;
    }

    /**
     * This method is fired when showing the order details in the backend.
     * It displays the shipper-specific data.
     * NOTE, this plugin should NOT be used to display form fields, since it's called outside
     * a form! Use plgVmOnUpdateOrderBE() instead!
     *
     * @param integer $_orderId The order ID
     * @param integer $_vendorId Vendor ID
     * @param object $_shipInfo Object with the properties 'carrier' and 'name'
     * @return mixed Null for shippers that aren't active, text (HTML) otherwise
     * @author Valerie Isaksen
     */
    public function plgVmOnShowOrderShipperBE($virtuemart_order_id, $vendorId, $ship_method_id) {
	if (!($this->selectedThisShipper($this->_selement, $ship_method_id))) {
	    return null;
	}
	$html = $this->getOrderShippingHtml($virtuemart_order_id);
	return $html;
    }

    function getOrderShippingHtml($virtuemart_order_id) {

	$db = JFactory::getDBO();
	$q = 'SELECT * FROM `' . $this->_tablename . '` '
		. 'WHERE `virtuemart_order_id` = ' . $virtuemart_order_id;
	$db->setQuery($q);
	if (!($shipinfo = $db->loadObject())) {
	    JError::raiseWarning(500, $q . " " . $db->getErrorMsg());
	    return '';
	}
	if (!class_exists('ShopFunctions'))
	    require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'shopfunctions.php');
	if (!class_exists('CurrencyDisplay')
	)
	    require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');
	$currency = CurrencyDisplay::getInstance();
	$tax = ShopFunctions::getTaxByID($shipinfo->tax_id);
	$taxDisplay = is_array($tax) ? $tax['calc_value'] . ' ' . $tax['calc_value_mathop'] : $shipinfo->tax_id;
	$taxDisplay = ($taxDisplay == -1 ) ? JText::_('COM_VIRTUEMART_PRODUCT_TAX_NONE') : $taxDisplay;
	$html = '<table class="admintable">' . "\n"
		. '	<thead>' . "\n"
		. '		<tr>' . "\n"
		. '			<td class="key" style="text-align: center;" colspan="2">' . JText::_('COM_VIRTUEMART_ORDER_PRINT_SHIPPING_LBL') . '</td>' . "\n"
		. '		</tr>' . "\n"
		. '	</thead>' . "\n"
		. '	<tr>' . "\n"
		. '		<td class="key">' . JText::_('VMSHIPPER_WEIGHT_COUNTRIES_SHIPPING_NAME') . ': </td>' . "\n"
		. '		<td align="left">' . $shipinfo->shipper_name . '</td>' . "\n"
		. '	</tr>' . "\n"
		. '	<tr>' . "\n"
		. '		<td class="key">' . JText::_('VMSHIPPER_WEIGHT_COUNTRIES_WEIGHT') . ': </td>' . "\n"
		. '		<td>' . $shipinfo->order_weight . ' ' . ShopFunctions::renderWeightUnit($shipinfo->shipping_weight_unit) . '</td>' . "\n"
		. '	</tr>' . "\n"
		. '	<tr>' . "\n"
		. '		<td class="key">' . JText::_('VMSHIPPER_WEIGHT_COUNTRIES_RATE_VALUE') . ': </td>' . "\n"
		. '		<td>' . $currency->priceDisplay($shipinfo->shipper_cost, '', false) . '</td>' . "\n"
		. '	</tr>' . "\n"
		. '	<tr>' . "\n"
		. '		<td class="key">' . JText::_('VMSHIPPER_WEIGHT_COUNTRIES_PACKAGE_FEE') . ': </td>' . "\n"
		. '		<td>' . $currency->priceDisplay($shipinfo->shipper_package_fee, '', false) . '</td>' . "\n"
		. '	</tr>' . "\n"
		. '	<tr>' . "\n"
		. '		<td class="key">' . JText::_('VMSHIPPER_WEIGHT_COUNTRIES_TAX') . ': </td>' . "\n"
		. '		<td>' . $taxDisplay . '</td>' . "\n"
		. '	</tr>' . "\n"
		. '</table>' . "\n"
	;
	return $html;
    }

    function _getShippingCost($params, VirtueMartCart $cart) {
	$value = $this->getShippingValue($params, $cart->pricesUnformatted);
	$shipping_tax_id = $this->getShippingTaxId($params, $cart);
	$tax = ShopFunctions::getTaxByID($shipping_tax_id);
	$taxDisplay = is_array($tax) ? $tax['calc_value'] . ' ' . $tax['calc_value_mathop'] : $shipping_tax_id;
	$taxDisplay = ($taxDisplay == -1 ) ? JText::_('COM_VIRTUEMART_PRODUCT_TAX_NONE') : $taxDisplay;
    }

    function getShippingValue($params, $cart_prices) {
	$free_shipping = $params->get('free_shipping', 0);
	if ($free_shipping && $cart_prices['salesPrice'] >= $free_shipping) {
	    return 0;
	} else {
	    return $params->get('rate_value', 0) + $params->get('package_fee', 0);
	}
    }

    function getShippingTaxId($params) {
	return $params->get('shipping_tax_id', 0);
    }

    /*
     * This method returns the logo image form the shipper
     */

    function _getShipperLogo($shipper_logo, $alt_text) {


	$img = "";
	/* TODO: chercher chemin dynamique */
	$path = JURI::root() . 'images' . DS . 'stories' . DS . 'virtuemart' . DS . 'shipper' . DS;
	$img = "";
	if (!(empty($shipper_logo))) {
	    $img = '<img align="middle" src="' . $path . $shipper_logo . '"  alt="' . $alt_text . '" > ';
	}
	return $img;
    }

    function checkShippingConditions($cart, $shipper) {

	$params = new JParameter($shipper->shipping_carrier_params);
	$orderWeight = $this->getOrderWeight($cart, $params->get('weight_unit'));
	$address = (($cart->ST == 0) ? $cart->BT : $cart->ST);

	$nbShipper = 0;
	$countries = array();
	if (!class_exists('JParameter'))
	    require(JPATH_LIBRARIES . DS . 'joomla' . DS . 'html' . DS . 'parameter.php' );

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
	    $address['zip'] = 0;
	    $address['virtuemart_country_id'] = 0;
	}
	$weight_cond = $this->_weightCond($orderWeight, $params);

	if (isset($address['zip'])) {
	    $zip_cond = $this->_zipCond($address['zip'], $params);
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

    function _weightCond($orderWeight, $params) {
	if ($orderWeight) {

	    $weight_cond = ($orderWeight >= $params->get('weight_start', 0) AND $orderWeight <= $params->get('weight_stop', 0)
		    OR
		    ($params->get('weight_start', 0) <= $orderWeight AND ($params->get('weight_stop', '') == '') ));
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
    function _zipCond($zip, $params) {
	if (!empty($zip)) {
	    $zip_cond = (( $zip >= $params->get('zip_start', 0) AND $zip <= $params->get('zip_stop', 0) )
		    OR
		    ($params->get('zip_start', 0) <= $zip AND ($params->get('zip_stop', '') == '') ) );
	} else {
	    $zip_cond = true;
	}
	return $zip_cond;
    }

    /**
     * Get the name of the shipper
     * @param int $shipping The Shipper ID
     * @author Valérie Isaksen
     * @return string Shipper name
     */
    function getShippingName($shipping) {
	$params = new JParameter($shipping->shipping_carrier_params);
	$logo = $this->_getShipperLogo($params->get('shipper_logo'), $shipping->shipping_carrier_name);

	return $logo . " " . $shipping->shipping_carrier_name;
    }

}

// No closing tag
