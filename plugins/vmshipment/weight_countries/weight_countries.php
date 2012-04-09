<?php

defined('_JEXEC') or die('Restricted access');


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
	//if (self::$_this)
	//   return self::$_this;
	parent::__construct($subject, $config);

	$this->_loggable = true;
	$this->tableFields = array_keys($this->getTableSQLFields());
	$varsToPush = $this->getVarsToPush();
	$this->setConfigParameterable($this->_configTableFieldName, $varsToPush);

// 		self::$_this
	//$this->createPluginTable($this->_tablename);
	//self::$_this = $this;
    }

    /**
     * Create the table for this plugin if it does not yet exist.
     * @author Valérie Isaksen
     */
    public function getVmPluginCreateTableSQL() {

	return $this->createTableSQL('Shipment Weight Countries Table');
    }

    function getTableSQLFields() {
	$SQLfields = array(
	    'id' => 'int(1) UNSIGNED NOT NULL AUTO_INCREMENT',
	    'virtuemart_order_id' => 'int(11) UNSIGNED',
	    'order_number' => 'char(32)',
	    'virtuemart_shipmentmethod_id' => 'mediumint(1) UNSIGNED',
	    'shipment_name' => 'varchar(5000)',
	    'order_weight' => 'decimal(10,4)',
	    'shipment_weight_unit' => 'char(3) DEFAULT \'KG\'',
	    'shipment_cost' => 'decimal(10,2)',
	    'shipment_package_fee' => 'decimal(10,2)',
	    'tax_id' => 'smallint(1)'
	);
	return $SQLfields;
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
    public function plgVmOnShowOrderFEShipment($virtuemart_order_id, $virtuemart_shipmentmethod_id, &$shipment_name) {
	$this->onShowOrderFE($virtuemart_order_id, $virtuemart_shipmentmethod_id, $shipment_name);
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
    function plgVmConfirmedOrder(VirtueMartCart $cart, $order) {
	if (!($method = $this->getVmPluginMethod($order['details']['BT']->virtuemart_shipmentmethod_id))) {
	    return null; // Another method was selected, do nothing
	}
	if (!$this->selectedThisElement($method->shipment_element)) {
	    return false;
	}
	$values['virtuemart_order_id'] = $order['details']['BT']->virtuemart_order_id;
	$values['order_number'] = $order['details']['BT']->order_number;
	$values['virtuemart_shipmentmethod_id'] = $order['details']['BT']->virtuemart_shipmentmethod_id;
	$values['shipment_name'] = $this->renderPluginName($method);
	$values['order_weight'] = $this->getOrderWeight($cart, $method->weight_unit);
	$values['shipment_weight_unit'] = $method->weight_unit;
	$values['shipment_cost'] = $method->cost;
	$values['shipment_package_fee'] = $method->package_fee;
	$values['tax_id'] = $method->tax_id;
	$this->storePSPluginInternalData($values);

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
    public function plgVmOnShowOrderBEShipment($virtuemart_order_id, $virtuemart_shipmentmethod_id) {
	if (!($this->selectedThisByMethodId($virtuemart_shipmentmethod_id))) {
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
	    vmWarn(500, $q . " " . $db->getErrorMsg());
	    return '';
	}

	if (!class_exists('CurrencyDisplay'))
	    require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');

	$currency = CurrencyDisplay::getInstance();
	$tax = ShopFunctions::getTaxByID($shipinfo->tax_id);
	$taxDisplay = is_array($tax) ? $tax['calc_value'] . ' ' . $tax['calc_value_mathop'] : $shipinfo->tax_id;
	$taxDisplay = ($taxDisplay == -1 ) ? JText::_('COM_VIRTUEMART_PRODUCT_TAX_NONE') : $taxDisplay;

	$html = '<table class="adminlist">' . "\n";
	$html .=$this->getHtmlHeaderBE();
	$html .= $this->getHtmlRowBE('WEIGHT_COUNTRIES_SHIPPING_NAME', $shipinfo->shipment_name);
	$html .= $this->getHtmlRowBE('WEIGHT_COUNTRIES_WEIGHT', $shipinfo->order_weight . ' ' . ShopFunctions::renderWeightUnit($shipinfo->shipment_weight_unit));
	$html .= $this->getHtmlRowBE('WEIGHT_COUNTRIES_COST', $currency->priceDisplay($shipinfo->shipment_cost));
	$html .= $this->getHtmlRowBE('WEIGHT_COUNTRIES_PACKAGE_FEE', $currency->priceDisplay($shipinfo->shipment_package_fee));
	$html .= $this->getHtmlRowBE('WEIGHT_COUNTRIES_TAX', $taxDisplay);
	$html .= '</table>' . "\n";

	return $html;
    }

    function getCosts(VirtueMartCart $cart, $method, $cart_prices) {

	if ($method->free_shipment && $cart_prices['salesPrice'] >= $method->free_shipment) {
	    return 0;
	} else {
	    return $method->cost + $method->package_fee;
	}
    }

    protected function checkConditions($cart, $method, $cart_prices) {
	$this->convert($method);

	$orderWeight = $this->getOrderWeight($cart, $method->weight_unit);
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
	$nbproducts_cond = $this->_nbproductsCond($cart, $method);
	$orderamount_cond = $this->_orderamountCond($cart_prices, $method);
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
	    if ($weight_cond AND $zip_cond AND $nbproducts_cond AND $orderamount_cond) {
		return true;
	    }
	}

	return false;
    }

    function convert(&$method) {
	//$method->weight_start = (float) $method->weight_start;
	//$method->weight_stop = (float) $method->weight_stop;
	$method->orderamount_start = (float) $method->orderamount_start;
	$method->orderamount_stop = (float) $method->orderamount_stop;
	$method->zip_start = (int) $method->zip_start;
	$method->zip_stop = (int) $method->zip_stop;
	$method->nbproducts_start = (int) $method->nbproducts_start;
	$method->nbproducts_stop = (int) $method->nbproducts_stop;
	$method->free_shipment = (float) $method->free_shipment;
    }

    private function _weightCond($orderWeight, $method) {

	    $weight_cond = ( ($orderWeight >= $method->weight_start AND $orderWeight <= $method->weight_stop  )
		    OR
		    ($method->weight_start <= $orderWeight AND $method->weight_stop === ''   ) );

	return $weight_cond;
    }

    private function _nbproductsCond($cart, $method) {
	$nbproducts = 0;
	foreach ($cart->products as $product) {
	    $nbproducts += $product->quantity;
	}
	if (!isset($method->nbproducts_start) AND !isset($method->nbproducts_stop)) {
	    return true;
	}
	if ($nbproducts) {
	    $nbproducts_cond = ($nbproducts >= $method->nbproducts_start AND $nbproducts <= $method->nbproducts_stop
		    OR
		    ($method->nbproducts_start <= $nbproducts AND ($method->nbproducts_stop == 0) ));
	} else {
	    $nbproducts_cond = true;
	}
	return $nbproducts_cond;
    }

    private function _orderamountCond($cart_prices, $method) {
	$orderamount = 0;

	if (!isset($method->orderamount_start) AND !isset($method->orderamount_stop)) {
	    return true;
	}
	if ($cart_prices['salesPrice']) {
	    $orderamount_cond = ($cart_prices['salesPrice'] >= $method->orderamount_start AND $cart_prices['salesPrice'] <= $method->orderamount_stop
		    OR
		    ($method->orderamount_start <= $cart_prices['salesPrice'] AND ($method->orderamount_stop == 0) ));
	} else {
	    $orderamount_cond = true;
	}
	return $orderamount_cond;
    }

    /**
     * Check the conditions on Zip code
     * @param int $zip : zip code
     * @param $params paremters for this specific shiper
     * @author Valérie Isaksen
     * @return string if Zip condition is ok or not
     */
    private function _zipCond($zip, $method) {
	$zip=(int)$zip;
	if (!empty($zip)) {
	    $zip_cond = (( $zip >=   $method->zip_start AND $zip <=   $method->zip_stop )
		    OR
		    (  $method->zip_start <= $zip AND (  $method->zip_stop == 0) ));
	} else {
	    $zip_cond = true;
	}
	return $zip_cond;
    }

    /*
     * We must reimplement this triggers for joomla 1.7
     */

    /**
     * Create the table for this plugin if it does not yet exist.
     * This functions checks if the called plugin is active one.
     * When yes it is calling the standard method to create the tables
     * @author Valérie Isaksen
     *
     */
    function plgVmOnStoreInstallShipmentPluginTable($jplugin_id) {
	return $this->onStoreInstallPluginTable($jplugin_id);
    }

    /**
     * This event is fired after the shipment method has been selected. It can be used to store
     * additional payment info in the cart.
     *
     * @author Max Milbers
     * @author Valérie isaksen
     *
     * @param VirtueMartCart $cart: the actual cart
     * @return null if the payment was not selected, true if the data is valid, error message if the data is not vlaid
     *
     */
    // public function plgVmOnSelectCheck($psType, VirtueMartCart $cart) {
	// return $this->OnSelectCheck($psType, $cart);
    // }
    public function plgVmOnSelectCheckShipment(VirtueMartCart &$cart) {
	return $this->OnSelectCheck($cart);
    }
    /**
     * plgVmDisplayListFE
     * This event is fired to display the pluginmethods in the cart (edit shipment/payment) for exampel
     *
     * @param object $cart Cart object
     * @param integer $selected ID of the method selected
     * @return boolean True on succes, false on failures, null when this plugin was not selected.
     * On errors, JError::raiseWarning (or JError::raiseError) must be used to set a message.
     *
     * @author Valerie Isaksen
     * @author Max Milbers
     */
    public function plgVmDisplayListFEShipment(VirtueMartCart $cart, $selected = 0, &$htmlIn) {
	return $this->displayListFE($cart, $selected, $htmlIn);
    }

    /*
     * plgVmonSelectedCalculatePrice
     * Calculate the price (value, tax_id) of the selected method
     * It is called by the calculator
     * This function does NOT to be reimplemented. If not reimplemented, then the default values from this function are taken.
     * @author Valerie Isaksen
     * @cart: VirtueMartCart the current cart
     * @cart_prices: array the new cart prices
     * @return null if the method was not selected, false if the shiiping rate is not valid any more, true otherwise
     *
     *
     */

    public function plgVmonSelectedCalculatePriceShipment(VirtueMartCart $cart, array &$cart_prices, &$cart_prices_name) {
	return $this->onSelectedCalculatePrice($cart, $cart_prices, $cart_prices_name);
    }

    /**
     * plgVmOnCheckAutomaticSelected
     * Checks how many plugins are available. If only one, the user will not have the choice. Enter edit_xxx page
     * The plugin must check first if it is the correct type
     * @author Valerie Isaksen
     * @param VirtueMartCart cart: the cart object
     * @return null if no plugin was found, 0 if more then one plugin was found,  virtuemart_xxx_id if only one plugin is found
     *
     */
    function plgVmOnCheckAutomaticSelectedShipment(VirtueMartCart $cart, array $cart_prices = array() ,   &$shipCounter) {
	if ($shipCounter >1) return 0;
	return $this->onCheckAutomaticSelected($cart, $cart_prices, $shipCounter);
    }

    /**
     * This event is fired during the checkout process. It can be used to validate the
     * method data as entered by the user.
     *
     * @return boolean True when the data was valid, false otherwise. If the plugin is not activated, it should return null.
     * @author Max Milbers

      public function plgVmOnCheckoutCheckData($psType, VirtueMartCart $cart) {
      return null;
      }
     */

    /**
     * This method is fired when showing when priting an Order
     * It displays the the payment method-specific data.
     *
     * @param integer $_virtuemart_order_id The order ID
     * @param integer $method_id  method used for this order
     * @return mixed Null when for payment methods that were not selected, text (HTML) otherwise
     * @author Valerie Isaksen
     */
    function plgVmonShowOrderPrint($order_number, $method_id) {
	return $this->onShowOrderPrint($order_number, $method_id);
    }

    /**
     * Save updated order data to the method specific table
     *
     * @param array $_formData Form data
     * @return mixed, True on success, false on failures (the rest of the save-process will be
     * skipped!), or null when this method is not actived.
     * @author Oscar van Eijk

      public function plgVmOnUpdateOrder($psType, $_formData) {
      return null;
      }
     */
    /**
     * Save updated orderline data to the method specific table
     *
     * @param array $_formData Form data
     * @return mixed, True on success, false on failures (the rest of the save-process will be
     * skipped!), or null when this method is not actived.
     * @author Oscar van Eijk

      public function plgVmOnUpdateOrderLine($psType, $_formData) {
      return null;
      }
     */
    /**
     * plgVmOnEditOrderLineBE
     * This method is fired when editing the order line details in the backend.
     * It can be used to add line specific package codes
     *
     * @param integer $_orderId The order ID
     * @param integer $_lineId
     * @return mixed Null for method that aren't active, text (HTML) otherwise
     * @author Oscar van Eijk

      public function plgVmOnEditOrderLineBE($psType, $_orderId, $_lineId) {
      return null;
      }
     */
    /**
     * This method is fired when showing the order details in the frontend, for every orderline.
     * It can be used to display line specific package codes, e.g. with a link to external tracking and
     * tracing systems
     *
     * @param integer $_orderId The order ID
     * @param integer $_lineId
     * @return mixed Null for method that aren't active, text (HTML) otherwise
     * @author Oscar van Eijk

      public function plgVmOnShowOrderLineFE($psType, $_orderId, $_lineId) {
      return null;
      }
     */

    /**
     * plgVmOnResponseReceived
     * This event is fired when the  method returns to the shop after the transaction
     *
     *  the method itself should send in the URL the parameters needed
     * NOTE for Plugin developers:
     *  If the plugin is NOT actually executed (not the selected payment method), this method must return NULL
     *
     * @param int $virtuemart_order_id : should return the virtuemart_order_id
     * @param text $html: the html to display
     * @return mixed Null when this method was not selected, otherwise the true or false
     *
     * @author Valerie Isaksen
     *

      function plgVmOnResponseReceived($psType, &$virtuemart_order_id, &$html) {
      return null;
      }
     */
    function plgVmDeclarePluginParamsShipment($name, $id, &$data) {

	return $this->declarePluginParams('shipment', $name, $id, $data);
    }

    function plgVmSetOnTablePluginParamsShipment($name, $id, &$table) {
	return $this->setOnTablePluginParams($name, $id, $table);
    }

}

// No closing tag
