<?php

/**
 * Abstract class for shipment plugins
 *
 * @package	VirtueMart
 * @subpackage Plugins
 * @author Oscar van Eijk
 * @author Valérie Isaksen
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2011 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: vmshipmentplugin.php 4599 2011-11-02 18:29:04Z alatak $
 */
// Load the helper functions that are needed by all plugins
if (!class_exists('vmPSPlugin'))
require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');

/**
 * Abstract class for shipment plugins.
 * This class provides some standard and abstract methods that can or must be reimplemented.
 *
 * @tutorial All methods are documented, but to make life easier, here's a short overview
 * how the methods can be used in the process order.
 * 	* _createTable() is called by the constructor. Use this method to create or alter the database table.
 * 	* When a shopper selects a shipment, plgOnSelectShipment() is fired. It displays the shipment and can be used
 * 	for collecting extra - shipment specific - info.
 * 	* After selecting, plgVmpluginSelected() can be used to store extra shipment info in the cart. The selected shipment
 * 	ID will be stored in the cart by the checkout process before this method is fired.
 * 	* plgOnConfirmShipment() is fired when the order is confirmed and stored to the database. It is called
 * 	before the rest of the order or stored, when reimplemented, it *must* include a call to parent::plgOnConfirmShipment()
 * 	(or execute the same steps to put all data in the cart)
 *
 * When a stored order is displayed in the backend, the following events are used:
 * 	* plgVmOnShowOrderBE() displays specific data about (a) shipment(s) (NOTE: this plugin is
 * 	OUTSIDE any form!)
 * 	* plgVmOnShowOrderLineShipmentBE() can be used to show information about a single orderline, e.g.
 * 	display a package code at line level when more packages are shipped.
 * 	* plgVmOnEditOrderLineBE() can be used add a package code for an order line when more
 * 	packages are shipped.
 * 	* plgVmOnUpdateOrderBE is fired inside a form. It can be used to add shipment data, like package code.
 * 	* plgVmOnSaveOrderShipmentBE() is fired from the backend after the order has been saved. If one of the
 * 	show methods above have to option to add or edit info, this method must be used to save the data.
 * 	* plgVmOnUpdateOrderLine() is fired from the backend after an order line has been saved. This method
 * 	must be reimplemented if plgVmOnEditOrderLineBE() is used.
 *
 * The frontend 1 show method:
 * 	* plgVmOnShowOrderFE() collects and displays specific data about (a) shipment(s)
 *
 * @package	VirtueMart
 * @subpackage Plugins
 * @author Oscar van Eijk
 * @author Valérie Isaksen
 *
 */
abstract class vmShipmentPlugin extends vmPSPlugin {

	//private $_virtuemart_shipmentmethod_id = 0;

// 	protected $shipments;

	/**
	 * Method to create te plugin specific table; must be reimplemented.
	 * @example
	 * 	$_scheme = DbScheme::get_instance();
	 * 	$_scheme->create_scheme('#__vm_order_shipment_'.$this->_name);
	 * 	$_schemeCols = array(
	 * 		 'id' => array (
	 * 				 'type' => 'int'
	 * 				,'length' => 11
	 * 				,'auto_inc' => true
	 * 				,'null' => false
	 * 		)
	 * 		,'virtuemart_order_id' => array (
	 * 				 'type' => 'int'
	 * 				,'length' => 11
	 * 				,'null' => false
	 * 		)
	 * 		,    'order_number' => array (
	 * 				 'type' => 'varchar'
	 * 				,'length' => 32
	 * 				,'null' => false
	 * 		)
	 * 		,'shipment_id' => array (
	 * 				 'type' => 'text'
	 * 				,'null' => false
	 * 		)
	 * 	);
	 * 	$_schemeIdx = array(
	 * 		 'idx_order_s' => array(
	 * 				 'columns' => array ('virtuemart_order_id')
	 * 				,'primary' => false
	 * 				,'unique' => false
	 * 				,'type' => null
	 * 		)
	 * 	);
	 * 	$_scheme->define_scheme($_schemeCols);
	 * 	$_scheme->define_index($_schemeIdx);
	 * 	if (!$_scheme->scheme()) {
	 * 		JError::raiseWarning(500, $_scheme->get_db_error());
	 * 	}
	 * 	$_scheme->reset();
	 * @author Oscar van Eijk
	 */
	abstract protected function _createTable();

	/**
	 * plgVmOnCheckoutCheckShipmentData
	 * This event is fired after the payment has been processed; it selects the actual shipment rate
	 * based on the shipto (country, zip) and/or order weight, and optionally writes extra info
	 * to the database (in which case this method must be reimplemented).
	 * Reimplementation is not required, but when done, the following check MUST be made:
	 * 	if (!$this->selectedThis($this->_name, $_cart->shipment_id)) {
	 * 		return null;
	 * 	}
	 *
	 * Returing parent::plgVmOnCheckoutCheckShipmentData($_cart) is valid but will produce extra overhead!
	 *
	 * @param object $cart Cart object
	 * @return integer The shipment rate ID
	 * @author Oscar van Eijk
	 */
	public function plgVmOnCheckoutCheckShipmentData(VirtueMartCart $cart) {
		return $this->selectShipmentRate($cart);
	}

	/**
	 * This method is fired when showing the order details in the backend.
	 * It displays the shipment-specific data.
	 * NOTE, this plugin should NOT be used to display form fields, since it's called outside
	 * a form! Use plgVmOnUpdateOrderBE() instead!
	 *
	 * @param integer $_orderId The order ID
	 * @param integer $_vendorId Vendor ID
	 * @param object $_shipInfo Object with the properties 'carrier' and 'name'
	 * @return mixed Null for shipments that aren't active, text (HTML) otherwise
	 * @author Oscar van Eijk
	 */
	public function plgVmOnShowOrderBE($_orderId, $_vendorId, $_shipInfo) {
		// 		if (!($this->selectedThis($this->_name, $this->getShipmentIDForOrder($_orderId)))) {
		// 			return null;
		// 		}
		vmWarn('You should overwrite the function plgVmOnShowOrderBE in class ' . get_class($this));
		return null;
	}


	/**
	 * Select the shipment rate ID, based on the selected shipment in combination with the
	 * shipto address (country and zipcode) and the total order weight.
	 * @param object $cart Cart object
	 * @param int $shipmentID Shipment ID, by default taken from the cart
	 * @return int Shipment rate ID, -1 when no match is found. Only 1 selected ID will be returned;
	 * if more ID's match, the cheapest will be selected.
	 */
	protected function selectShipmentRate(VirtueMartCart $cart, $selectedShipment = 0) {
		$shipment_params = $this->getVmShipmentParams($cart->vendorId, $cart->virtuemart_shipmentmethod_id);
		$params = new JParameter($shipment_params);

		if ($selectedShipment == 0) {
			$selectedShipment = $cart->virtuemart_shipmentmethod_id;
		}
		$address = (($cart->ST == 0) ? $cart->BT : $cart->ST);

		$shipment_params = $this->getVmShipmentParams($cart->vendorId, $selectedShipment);

		$shipment->shipment_name = $params->get('shipment_name');
		$shipment->shipment_rate_vat_id = $params->get('shipment_tax_id');
		$shipment->shipment_value = $this->_getShipmentCost($params, $cart);
		return $selectedShipment;
	}

	/**
	 * This functions gets the used and configured shipment method
	 * pelement of this class determines the used jplugin.
	 * The right shipment method is determined by the vendor and the jplugin id.
	 *
	 * This function sets the used shipment plugin as variable of this class
	 * @author Max Milbers
	 *
	 */
	private function getVmShipmentParams($vendorId=0, $shipment_id=0) {

		if (!$vendorId)
		$vendorId = 1;
		$db = JFactory::getDBO();

		$q = 'SELECT   `shipment_params` FROM #__virtuemart_shipmentmethods WHERE `virtuemart_shipmentmethod_id` = "' . $shipment_id . '" AND `virtuemart_vendor_id` = "' . $vendorId . '" AND `published`="1" ';
		$db->setQuery($q);
		return $db->loadResult();
	}

	private function _getShipmentCost($params, VirtueMartCart $cart) {
		$value = $this->getCosts($params, $cart->pricesUnformatted);
		$shipment_tax_id = $this->getTaxId($params, $cart);
		$tax = ShopFunctions::getTaxByID($shipment_tax_id);
		$taxDisplay = is_array($tax) ? $tax['calc_value'] . ' ' . $tax['calc_value_mathop'] : $shipment_tax_id;
		$taxDisplay = ($taxDisplay == -1 ) ? JText::_('COM_VIRTUEMART_PRODUCT_TAX_NONE') : $taxDisplay;
	}

	/**
	 * getThisShipmentNameById
	 * Get the name of the shipment
	 * @param int $id The Shipment ID
	 * @author Valérie Isaksen
	 * @return string Shipment name
	 */
	final protected function getThisShipmentName($virtuemart_shipmentmethod_id) {
		$db = JFactory::getDBO();
		$q = 'SELECT `shipment_name` '
		. 'FROM #__virtuemart_shipmentmethods '
		. "WHERE virtuemart_shipmentmethod_id ='$virtuemart_shipmentmethod_id' ";
		$db->setQuery($q);
		return $db->loadResult(); // TODO Error check
	}

	function getCosts($params, $cart_prices) {
		$free_shipment = $params->get('free_shipment', 0);
		if ($free_shipment && $cart_prices['salesPrice'] >= $free_shipment) {
			return 0;
		} else {
			return $params->get('rate_value', 0) + $params->get('package_fee', 0);
		}
	}

}
