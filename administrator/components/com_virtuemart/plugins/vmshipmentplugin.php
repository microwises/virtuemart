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
if (!class_exists('ShopFunctions'))
require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'shopfunctions.php');
if (!class_exists('DbScheme'))
require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'dbscheme.php');
if (!class_exists('vmPlugin'))
require(JPATH_VM_PLUGINS . DS . 'vmplugin.php');

// Get the plugin library
jimport('joomla.plugin.plugin');

/**
 * Abstract class for shipment plugins.
 * This class provides some standard and abstract methods that can or must be reimplemented.
 *
 * @tutorial All methods are documented, but to make life easier, here's a short overview
 * how the methods can be used in the process order.
 * 	* _createTable() is called by the constructor. Use this method to create or alter the database table.
 * 	* When a shopper selects a shipment, plgOnSelectShipment() is fired. It displays the shipment and can be used
 * 	for collecting extra - shipment specific - info.
 * 	* After selecting, plgVmShipmentSelected() can be used to store extra shipment info in the cart. The selected shipment
 * 	ID will be stored in the cart by the checkout process before this method is fired.
 * 	* plgOnConfirmShipment() is fired when the order is confirmed and stored to the database. It is called
 * 	before the rest of the order or stored, when reimplemented, it *must* include a call to parent::plgOnConfirmShipment()
 * 	(or execute the same steps to put all data in the cart)
 *
 * When a stored order is displayed in the backend, the following events are used:
 * 	* plgVmOnShowOrderShipmentBE() displays specific data about (a) shipment(s) (NOTE: this plugin is
 * 	OUTSIDE any form!)
 * 	* plgVmOnShowOrderLineShipmentBE() can be used to show information about a single orderline, e.g.
 * 	display a package code at line level when more packages are shipped.
 * 	* plgVmOnEditOrderLineShipmentBE() can be used add a package code for an order line when more
 * 	packages are shipped.
 * 	* plgVmOnUpdateOrderShipmentBE is fired inside a form. It can be used to add shipment data, like package code.
 * 	* plgVmOnSaveOrderShipmentBE() is fired from the backend after the order has been saved. If one of the
 * 	show methods above have to option to add or edit info, this method must be used to save the data.
 * 	* plgVmOnUpdateOrderLine() is fired from the backend after an order line has been saved. This method
 * 	must be reimplemented if plgVmOnEditOrderLineShipmentBE() is used.
 *
 * The frontend 1 show method:
 * 	* plgVmOnShowOrderShipmentFE() collects and displays specific data about (a) shipment(s)
 *
 * @package	VirtueMart
 * @subpackage Plugins
 * @author Oscar van Eijk
 * @author Valérie Isaksen
 *
 */
abstract class vmShipmentPlugin extends vmPlugin {

	//private $_virtuemart_shipmentmethod_id = 0;
	/**
	* @var string Identification of the shipment. This var must be overwritten by all plugins,
	* by adding this code to the constructor:
	* $this->_pelement = basename(__FILE, '.php');
	*/
	protected $_pelement = '';
	protected $_tablename = '';

	/**
	 * @var array List with all carriers the have been implemented with the plugin in the format
	 * id => name
	 */
	protected $shipments;

	/**
	 * Constructor
	 *
	 * @param object $subject The object to observe
	 * @param array  $config  An array that holds the plugin configuration
	 * @since 1.5
	 */
	function __construct(& $subject, $config) {
		$this->_vmplugin = 'shipment';
		parent::__construct($subject, $config);
		$lang = JFactory::getLanguage();
		$filename = 'plg_vm' . $this->_vmplugin . '_' . $this->_pelement;
		$lang->load($filename, JPATH_ADMINISTRATOR);

		if (!class_exists('JParameter'))
		require(JPATH_VM_LIBRARIES . DS . 'joomla' . DS . 'html' . DS . 'parameter.php' );
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
	protected function getVmShipmentParams($vendorId=0, $shipment_id=0) {

		if (!$vendorId)
		$vendorId = 1;
		$db = JFactory::getDBO();

		$q = 'SELECT   `shipment_params` FROM #__virtuemart_shipments WHERE `virtuemart_shipment_id` = "' . $shipment_id . '" AND `virtuemart_vendor_id` = "' . $vendorId . '" AND `published`="1" ';
		$db->setQuery($q);
		return $db->loadResult();
	}

	/**
	 * getOrderWeight
	 * Get the total weight for the order, based on which the proper shipment rate
	 * can be selected.
	 * @param object VirtueMartCart $cart Cart object
	 * @param $to_weight_unit : weight unit
	 * @return float Total weight for the order
	 * @author Oscar van Eijk
	 */
	protected function getOrderWeight(VirtueMartCart $cart, $to_weight_unit) {
		$weight = 0;
		foreach ($cart->products as $prod) {
			$weight += ( ShopFunctions::convertWeigthUnit($prod->product_weight, $prod->product_weight_unit, $to_weight_unit) * $prod->quantity);
		}
		return $weight;
	}
	/**
	 *  @author Valerie Isaksen
	 * @param int $shipment_id The shipment method ID

	 * @return shipment table
	 */
	final protected function getShipment ( $shipment_id) {
		$db = JFactory::getDBO();

		$q = 'SELECT * FROM #__virtuemart_shipments
        		WHERE `virtuemart_shipment_id`="' . $shipment_id . '" AND `shipment_element` = "'.$this->_pelement.'"';

		$db->setQuery($q);
		return  $db->loadObject();
	}
	/**
	 * This method checks if the selected payment method matches the current plugin
	 * @param string $_pelement Element name, taken from the plugin filename
	 * @param int $_pid The payment method ID
	 * @author Oscar van Eijk
	 * @author Max Milbers
	 * @return True if the calling plugin has the given payment ID
	 */

	protected function getShipments($vendorId) {
		if (!class_exists('VirtueMartModelUser'))
		require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'user.php');

		$usermodel = new VirtueMartModelUser();
		$user = $usermodel->getUser();
		$user->shopper_groups = (array) $user->shopper_groups;

		if (VmConfig::isJ15()) {
			$extPlgTable = '#__plugins';
			$extField1 = 'id';
			$extField2 = 'element';
		} else {
			$extPlgTable = '#__extensions';
			$extField1 = 'extension_id';
			$extField2 = 'element';
		}

		$db = JFactory::getDBO();

		$select = 'SELECT v.*,j.*,s.virtuemart_shoppergroup_id ';

		$q = $select . ' FROM   #__virtuemart_shipments AS v ';

		$q.= 'LEFT JOIN ' . $extPlgTable . ' as j ON j.`' . $extField1 . '` =  v.`shipment_jplugin_id` ';

		$q.= 'LEFT OUTER JOIN #__virtuemart_shipment_shoppergroups AS s ON v.`virtuemart_shipment_id` = s.`virtuemart_shipment_id` ';

		$q.= ' WHERE v.`published` = "1"  AND j.`' . $extField2 . '` = "' . $this->_pelement . '"
					AND  (v.`virtuemart_vendor_id` = "' . $vendorId . '" OR   v.`virtuemart_vendor_id` = "0")
					AND  (';

		foreach ($user->shopper_groups as $groups) {
			$q .= 's.`virtuemart_shoppergroup_id`= "' . (int) $groups . '" OR';
		}
		$q .= ' ISNULL(s.`virtuemart_shoppergroup_id`) ) ORDER BY v.`ordering`';

		$db->setQuery($q);
		if (!$results = $db->loadObjectList()) {
			// 			vmdebug(JText::_('COM_VIRTUEMART_CART_NO_CARRIER'),$db->getQuery());
			return false;
		}
		$this->shipments = $results;
		return true;
	}

	/**
	 * validateVendor
	 * Check if this shipment has carriers for the current vendor.
	 * @author Oscar van Eijk
	 * @param integer $_vendorId The vendor ID taken from the cart.
	 * @return True when a shipment_id was found for this vendor, false otherwise
	 */
	protected function validateVendor($_vendorId) {

		if (!$_vendorId) {
			$_vendorId = 1;
		}

		$_db = JFactory::getDBO();

		if (VmConfig::isJ15()) {
			$_q = 'SELECT 1 '
			. 'FROM   #__virtuemart_shipments v '
			. ',      #__plugins             j '
			. 'WHERE j.`element` = "' . $this->_pelement . '" '
			. 'AND   v.`shipment_jplugin_id` = j.`id` '
			. 'AND   v.`virtuemart_vendor_id` = "' . $_vendorId . '" '
			. 'AND   v.`published` = 1 '
			;
		} else {
			$_q = 'SELECT 1 '
			. 'FROM   #__virtuemart_shipments AS v '
			. ',      #__extensions   AS     j '
			. 'WHERE j.`folder` = "vmshipment" '
			. 'AND j.`element` = "' . $this->_pelement . '" '
			. 'AND   v.`shipment_jplugin_id` = j.`extension_id` '
			. 'AND   v.`virtuemart_vendor_id` = "' . $_vendorId . '" '
			. 'AND   v.`published` = 1 '
			;
		}

		$_db->setQuery($_q);
		$_r = $_db->loadAssoc();

		if ($_r) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Method to create te plugin specific table; must be reimplemented.
	 * @example
	 * 	$_scheme = DbScheme::get_instance();
	 * 	$_scheme->create_scheme('#__vm_order_shipment_'.$this->_pelement);
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
	 * This event is fired during the checkout process. It allows the shopper to select
	 * one of the available shipments.
	 * It should display a radio button (name: shipment_id) to select the shipment. In the description,
	 * the shipment cost can also be displayed, based on the total order weight and the shipto
	 * country (this wil be calculated again during order confirmation)
	 *
	 * @param object $cart the cart object
	 * @param integer $selectedShipment ID of the shipment currently selected
	 * @return HTML array. Each row contains the code to display in the form
	 * @author Oscar van Eijk
	 */
	public function plgVmOnSelectShipment(VirtueMartCart $cart, $selectedShipment = 0) {

		if ($this->getShipments($cart->vendorId) === false) {
			return false;
		}
		$html = array();
		foreach ($this->shipments as $shipment) {
			//vmdebug('plgVmOnSelectShipment', $shipment->shipment_params);
			if ($this->checkShipmentConditions($cart, $shipment)) {
				$params = new JParameter($shipment->shipment_params);
				$salesPrice = $this->calculateSalesPriceShipment($this->getShipmentValue($params, $cart->pricesUnformatted), $this->getShipmentTaxId($params));
				$shipment->shipment_name = $this->getShipmentName($shipment);

				$html[] = $this->getShipmentHtml($shipment, $selectedShipment, $salesPrice);
			}
		}
		return $html;
	}

	/**
	 * plgVmOnShipmentSelected
	 * This event is fired after the shipment method has been selected. It can be used to store
	 * additional shipment info in the cart.
	 *
	 * @param object $cart Cart object
	 * @param integer $selectedShipment ID of the shipment selected
	 * @return boolean True on succes, false on failures, null when this plugin was not selected.
	 * On errors, JError::raiseWarning (or JError::raiseError) must be used to set a message.
	 * @author Oscar van Eijk
	 */
	public function plgVmOnShipmentSelected(VirtueMartCart $cart, $selectedShipment = 0) {

		if (!$this->selectedThisShipment($this->_pelement, $selectedShipment)) {
			return null; // Another shipment was selected, do nothing
		}
		// should return $shipment rates for this
		$cart->setShipmentRate($this->selectShipmentRate($cart));
		return true;
	}

	/**
	 * plgVmOnCheckoutCheckShipmentData
	 * This event is fired after the payment has been processed; it selects the actual shipment rate
	 * based on the shipto (country, zip) and/or order weight, and optionally writes extra info
	 * to the database (in which case this method must be reimplemented).
	 * Reimplementation is not required, but when done, the following check MUST be made:
	 * 	if (!$this->selectedThisShipment($this->_pelement, $_cart->shipment_id)) {
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
	public function plgVmOnShowOrderShipmentBE($_orderId, $_vendorId, $_shipInfo) {
		if (!($this->selectedThisShipment($this->_pelement, $this->getShipmentIDForOrder($_orderId)))) {
			return null;
		}
		/*
	  if (!class_exists('CurrencyDisplay')

		)require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');
		$_currency = CurrencyDisplay::getInstance();  //Todo, set currency of shopper or user?
		//		$_currency = VirtueMartModelVendor::getCurrencyDisplay($_vendorId);
		$_html = '<table class="admintable">' . "\n"
		. '	<thead>' . "\n"
		. '		<tr>' . "\n"
		. '			<td class="key" style="text-align: center;" colspan="2">' . JText::_('COM_VIRTUEMART_ORDER_PRINT_SHIPPING_LBL') . '</td>' . "\n"
		. '		</tr>' . "\n"
		. '	</thead>' . "\n"
		. '	<tr>' . "\n"
		. '		<td class="key">' . JText::_('COM_VIRTUEMART_ORDER_PRINT_SHIPPING_CARRIER_LBL') . ': </td>' . "\n"
		. '		<td align="left">' . $_shipInfo->carrier . '</td>' . "\n"
		. '	</tr>' . "\n"
		. '	<tr>' . "\n"
		. '		<td class="key">' . JText::_('COM_VIRTUEMART_ORDER_PRINT_SHIPPING_MODE_LBL') . ': </td>' . "\n"
		. '		<td>' . $_shipInfo->name . '</td>' . "\n"
		. '	</tr>' . "\n"
		. '	<tr>' . "\n"
		. '		<td class="key">' . JText::_('COM_VIRTUEMART_ORDER_PRINT_SHIPPING_PRICE_LBL') . ': </td>' . "\n"
		. '		<td align="left">' . $_currency->priceDisplay($this->getShipmentRate($this->getShipmentRateIDForOrder($_orderId))) . '</td>' . "\n"
		. '	</tr>' . "\n"
		. '</table>' . "\n"
		;
	 *
	 *
	 */
		return $_html;
	}

	/**
	 * plgVmOnEditOrderLineShipmentBE
	 * This method is fired when editing the order line details in the backend.
	 * It can be used to add line specific package codes
	 *
	 * @param integer $_orderId The order ID
	 * @param integer $_lineId
	 * @return mixed Null for shipments that aren't active, text (HTML) otherwise
	 * @author Oscar van Eijk
	 */
	public function plgVmOnEditOrderLineShipmentBE($_orderId, $_lineId) {
		return null;
	}

	/**
	 * Save updated order data to the shipment specific table
	 *
	 * @param array $_formData Form data
	 * @return mixed, True on success, false on failures (the rest of the save-process will be
	 * skipped!), or null when this shipment is not actived.
	 * @author Oscar van Eijk
	 */
	public function plgVmOnUpdateOrderShipment($_formData) {
		return null;
	}

	/**
	 * Save updated orderline data to the shipment specific table
	 *
	 * @param array $_formData Form data
	 * @return mixed, True on success, false on failures (the rest of the save-process will be
	 * skipped!), or null when this shipment is not actived.
	 * @author Oscar van Eijk
	 */
	public function plgVmOnUpdateOrderLineShipment($_formData) {
		return null;
	}

	/**
	 * This method is fired when showing the order details in the frontend.
	 * It displays the shipment-specific data.
	 *
	 * @param integer $order_id The order ID
	 * @return mixed Null for shipments that aren't active, text (HTML) otherwise
	 * @author Oscar van Eijk
	 */
	public function plgVmOnShowOrderShipmentFE($order_id) {
		return;
	}

	/**
	 * This method is fired when showing the order details in the frontend, for every orderline.
	 * It can be used to display line specific package codes, e.g. with a link to external tracking and
	 * tracing systems
	 *
	 * @param integer $_orderId The order ID
	 * @param integer $_lineId
	 * @return mixed Null for shipments that aren't active, text (HTML) otherwise
	 * @author Oscar van Eijk
	 */
	public function plgVmOnShowOrderLineShipmentFE($_orderId, $_lineId) {
		return null;
	}

	/**
	 * Get the shipment rate ID for a given order number
	 * @access protected
	 * @author Oscar van Eijk
	 * @param int $_id The order ID
	 * @return int The shipment rate ID, or -1 when not found
	 */
	protected function getShipmentRateIDForOrder($_id) {
		$_db = JFactory::getDBO();
		$_q = 'SELECT `ship_method_id` '
		. 'FROM #__virtuemart_orders '
		. "WHERE virtuemart_order_id = $_id";
		$_db->setQuery($_q);
		if (!($_r = $_db->loadAssoc())) {
			return -1;
		}
		return $_r['ship_method_id'];
	}

	/**
	 * Display stored payment data for an order
	 * @see components/com_virtuemart/helpers/vmPaymentPlugin::plgVmOnShowOrderPaymentBE()
	 */
	function plgVmOnShowOrderPrintShipment($order_number, $shipment_method_id) {
		if (! ($order_shipment_name= $this->getOrderShipmentName($order_number, $shipment_method_id)) ) {
			return null;
		}

		JFactory::getLanguage()->load('com_virtuemart');
		$html = '<table class="admintable">' . "\n"
		. '	<thead>' . "\n"
		. '		<tr>' . "\n"
		. '			<td class="key" style="text-align: center;" colspan="2">' . JText::_('COM_VIRTUEMART_ORDER_PRINT_SHIPPING_CARRIER_LBL') . '</td>' . "\n"
		. '		</tr>' . "\n"
		. '	</thead>' . "\n"
		. '	<tr>' . "\n"
		. '		<td class="key">' . JText::_('COM_VIRTUEMART_ORDER_PRINT_' . $this->_vmplugin . '_LBL')  . ': </td>' . "\n"
		. '		<td align="left">' . $order_shipment_name . '</td>' . "\n"
		. '	</tr>' . "\n";

		$html .= '</table>' . "\n";
		return $html;
	}
	/**
	 * Check the order total to see if this order is valid for free shipment.
	 * @access protected
	 * @final
	 * @return boolean; true when shipment is free
	 * @author Oscar van Eijk
	 * @deprecated
	 */
	final protected function freeShipment() {
		if (!class_exists('VirtueMartCart'))
		require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
		$_cart = VirtueMartCart::getCart();
		if (!class_exists('VirtueMartModelVendor'))
		require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'vendor.php');
		$_vendor = new VirtueMartModelVendor();
		$_vendor->setId($_cart->vendorId);
		$_store = $_vendor->getVendor();

		if ($_store->vendor_freeshipment > 0) {
			$_prices = $_cart->getCartPrices();
			if ($_prices['salesPrice'] > $_store->vendor_freeshipment) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Get the shipment ID for a given order number
	 * @access protected
	 * @author Oscar van Eijk
	 * @param int $_id The order ID
	 * @return int The shipment ID, or -1 when not found
	 */
	protected function getShipmentIDForOrder($order_id) {
		/*
	  $_db = &JFactory::getDBO();
		$_q = 'SELECT s.`shipment_rate_carrier_id` AS shipment_id '
		. 'FROM #__virtuemart_orders        AS o '

		. "WHERE o.`virtuemart_order_id` = $_id "
		. 'AND   o.`ship_method_id` = s.`virtuemart_shipmentrate_id`';
		$_db->setQuery($_q);
		if (!($_r = $_db->loadAssoc())) {
		return -1;
		}
		return $_r['shipment_id'];
	 * */
	}

	/**
	 * Select the shipment rate ID, based on the selected shipment in combination with the
	 * shipto address (country and zipcode)  .
	 * @param object $_cart Cart object
	 * @param int $_shipmentID Shipment ID, by default taken from the cart
	 * @return int Shipment rate ID, -1 when no match is found. Only 1 selected ID will be returned;
	 * if more ID's match, the cheapest will be selected. ????
	 */
	protected function selectShipmentRate(VirtueMartCart $_cart, $_shipmentId = 0) {

	}

	/**
	 * This method checks if the selected shipment matches the current plugin
	 * @param string $_pelement Element name, taken from the plugin filename
	 * @param int $_sid The shipment ID
	 * @author Oscar van Eijk
	 * @return True if the calling plugin has the given payment ID
	 *
	 */
	final protected function selectedThisShipment($pelement, $sid) {
		$db = JFactory::getDBO();

		if (VmConfig::isJ15()) {
			$q = 'SELECT COUNT(*) AS c '
			. 'FROM #__virtuemart_shipments AS vm '
			. ',    #__plugins AS j '
			. "WHERE vm.virtuemart_shipment_id = '$sid' "
			. 'AND   vm.shipment_jplugin_id = j.id '
			. "AND   j.element = '$pelement'";
		} else {
			$q = 'SELECT COUNT(*) AS c '
			. 'FROM #__virtuemart_shipments AS vm '
			. ',      #__extensions    AS      j '
			. 'WHERE j.`folder` = "vmshipment" '
			. "AND vm.virtuemart_shipment_id = '$sid' "
			. 'AND   vm.shipment_jplugin_id = j.extension_id '
			. "AND   j.element = '$pelement'";
		}


		$db->setQuery($q);
		return $db->loadResult(); // TODO Error check
	}

	/*
	 * ShipmentSelected
	* @param int $virtuemart_shipment_id
	* return $shipment if found
	* return null otherwise
	*
	* @author Valérie Isaksen
	*/

	function ShipmentSelected($virtuemart_shipment_id) {
		foreach ($this->shipments as $shipment) {
			if ($shipment->virtuemart_shipment_id == $virtuemart_shipment_id) {
				return $shipment;
			}
		}
		return null;
	}

	/**
	 * getThisShipmentNameById
	 * Get the name of the shipment
	 * @param int $id The Shipment ID
	 * @author Valérie Isaksen
	 * @return string Shipment name
	 */
	final protected function getThisShipmentName($virtuemart_shipment_id) {
		$db = JFactory::getDBO();
		$q = 'SELECT `shipment_name` '
		. 'FROM #__virtuemart_shipments '
		. "WHERE virtuemart_shipment_id ='$virtuemart_shipment_id' ";
		$db->setQuery($q);
		return $db->loadResult(); // TODO Error check
	}



	/*
	 * calculateSalesPriceShipment
	* @param $shipment_value
	* @param $tax_id: tax id
	* @return $salesPriceShipment
	*/

	protected function calculateSalesPriceShipment($shipment_value, $tax_id) {

		if (!class_exists('calculationHelper'))
		require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'calculationh.php');
		if (!class_exists('CurrencyDisplay'))
		require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');

		if (!class_exists('VirtueMartModelVendor'))
		require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'vendor.php');
		$vendor_id = 1;
		$vendor_currency = VirtueMartModelVendor::getVendorCurrency($vendor_id);


		$db = JFactory::getDBO();
		$calculator = calculationHelper::getInstance();
		$currency = CurrencyDisplay::getInstance();

		$shipment_value = $currency->convertCurrencyTo($vendor_currency->virtuemart_currency_id, $shipment_value);

		$taxrules = array();
		if (!empty($tax_id)) {
			$q = 'SELECT * FROM #__virtuemart_calcs WHERE `virtuemart_calc_id`="' . $tax_id . '" ';
			$db->setQuery($q);
			$taxrules = $db->loadAssocList();
		}

		if (count($taxrules) > 0) {
			$salesPriceShipment = $calculator->roundDisplay($calculator->executeCalculation($taxrules, $shipment_value));
		} else {
			$salesPriceShipment = $shipment_value;
		}

		return $salesPriceShipment;
	}

	/*
	 * plgVmOnShipmentSelectedCalculatePrice
	* Calculate the price (value, tax_id) of the selected Shipment
	* It is called by the calculator
	* This function does NOT to be reimplemented. If not reimplemented, then the default values from this function are taken.
	* @author Valerie Isaksen
	* @cart: VirtueMartCart the current cart
	* @cart_prices: array the new cart prices
	* @shipmentTable Shipments: shipment carrier rate description
	* @return null if the shipment was not selected, false if the shiiping rate is not valid any more, true otherwise
	*
	*
	*/

	public function plgVmOnShipmentSelectedCalculatePrice(VirtueMartCart $cart, array $cart_prices, $shipment_name) {

		if (!$this->selectedThisShipment($this->_pelement, $cart->virtuemart_shipment_id)) {
			return null; // Another shipment was selected, do nothing
		}

		$shipment = $this->getthisShipmentData($cart->virtuemart_shipment_id);
		if (!$shipment) {
			return null;
		}

		$shipment_name = '';
		$cart_prices['shipment_tax_id'] = 0;
		$cart_prices['shipment_value'] = 0;

		if (!$this->checkShipmentConditions($cart, $shipment)) {
			return false;
		}
		$params = new JParameter($shipment->shipment_params);
		$shipment_name = $this->getShipmentName($shipment);
		$shipment_value = $this->getShipmentValue($params, $cart_prices);
		$shipment_tax_id = $this->getShipmentTaxId($params);

		$this->setCartPrices($cart_prices, $shipment_value, $shipment_tax_id);

		return true;
	}

	/*
	 * plgVmOnCheckAutomaticSelectedShipment
	* Checks how many shipment rates are available. If only one, the user will not have the choice. Enter edit_shipment page
	* @author Valerie Isaksen
	* @param VirtueMartCart cart: the cart object
	* @return null if no shipment was found, 0 if more then one shipment rate was found,  virtuemart_shipment if only one shipment rate is found
	*
	*
	*/

	function plgVmOnCheckAutomaticSelectedShipment(VirtueMartCart $cart) {

		$nbShipment = 0;
		$virtuemart_shipment_id = 0;
		$nbShipment = $this->getSelectableShipment($cart, $virtuemart_shipment_id);
		if ($nbShipment == null)
		return null;
		return ($nbShipment == 1) ? $virtuemart_shipment_id : 0;
	}

	/*
	 * CheckShipmentIsValid
	* @author Valérie Isaksen
	* @deprecated
	*/

	function CheckShipmentIsValid(VirtueMartCart $cart) {
		if (!$this->selectedThisShipment($this->_pelement, $cart->virtuemart_shipment_id)) {
			return null; // Another shipment was selected, do nothing
		}
		$shipment = $this->getThisShipmentData($cart->virtuemart_shipment_id);
		return $this->checkShipmentConditions($cart, $shipment);
	}

	function getParamShipments(VirtueMartCart $cart, &$nbShipment, &$virtuemart_shipment_id, $selectedShipment=0) {
		return null;
	}

	/*
	 * getSelectableShipment
	* This method returns the number of shipment methods valid
	* @param VirtueMartCart cart: the cart object
	* @param $virtuemart_shipment_id
	*
	*/

	function getSelectableShipment(VirtueMartCart $cart, &$virtuemart_shipment_id) {
		$nbShipment = 0;
		if ($this->getShipments($cart->vendorId) === false) {
			return false;
		}

		foreach ($this->shipments as $shipment) {
			if ($this->checkShipmentConditions($cart, $shipment)) {
				$nbShipment++;
				$virtuemart_shipment_id = $shipment->virtuemart_shipment_id;
			}
		}
		return $nbShipment;
	}

	/*
	 * displayTaxRule
	* @param int $tax_id
	* @return string $html:
	*/

	function displayTaxRule($tax_id) {
		$html = '';
		$db = JFactory::getDBO();
		if (!empty($tax_id)) {
			$q = 'SELECT * FROM #__virtuemart_calcs WHERE `virtuemart_calc_id`="' . $tax_id . '" ';
			$db->setQuery($q);
			$taxrule = $db->loadObject();

			$html = $taxrule->calc_name . '(' . $taxrule->calc_kind . ':' . $taxrule->calc_value_mathop . $taxrule->calc_value . ')';
		}
		return $html;
	}

	/**
	 * Get Shipment Data for a go given Shipment ID
	 * @author Valérie Isaksen
	 * @param int $virtuemart_shipment_id The Shipment ID
	 * @return  Shipment data
	 */
	final protected function getThisShipmentData($virtuemart_shipment_id) {
		$db = JFactory::getDBO();
		$q = 'SELECT * '
		. 'FROM #__virtuemart_shipments '
		. "WHERE `virtuemart_shipment_id` ='" . $virtuemart_shipment_id . "' ";
		$db->setQuery($q);
		$result = $db->loadObject();
		return $result;
	}

	/*
	 * getShipmentHtml
	*/

	protected function getShipmentHtml($shipment, $selectedShipment, $shipmentSalesPrice) {
		if ($selectedShipment == $shipment->virtuemart_shipment_id) {
			$checked = 'checked';
		} else {
			$checked = '';
		}

		if (!class_exists('CurrencyDisplay'))
		require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');
		$currency = CurrencyDisplay::getInstance();

		$shipmentCostDisplay = $currency->priceDisplay($shipmentSalesPrice);
		$html = '<input type="radio" name="shipment_id" id="shipment_id_' . $shipment->virtuemart_shipment_id . '" value="' . $shipment->virtuemart_shipment_id . '" ' . $checked . '>'
		. '<label for="shipment_id_' . $shipment->virtuemart_shipment_id . '">' . $shipment->shipment_name . " (" . $shipmentCostDisplay . ")</label>\n";
		return $html;
	}

	/*
	 * setCartPrices
	*
	* @author Valérie Isaksen
	*/

	function setCartPrices(&$cart_prices, $shipment_value, $shipment_tax_id) {
		if (!isset($shipment_value))
		$shipment->shipment_value = '';
		$cart_prices['shipmentValue'] = $shipment_value;


		$taxrules = array();
		if (!empty($shipment_tax_id)) {
			$db = JFactory::getDBO();
			$q = 'SELECT * FROM #__virtuemart_calcs WHERE `virtuemart_calc_id`="' . $shipment_tax_id . '" ';
			$db->setQuery($q);
			$taxrules = $db->loadAssocList();
		}
		if (!class_exists('calculationHelper'))
		require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'calculationh.php');
		$calculator = calculationHelper::getInstance();
		if (count($taxrules) > 0) {
			$cart_prices['salesPriceShipment'] = $calculator->roundDisplay($calculator->executeCalculation($taxrules, $cart_prices['shipmentValue']));
			$cart_prices['shipmentTax'] = $calculator->roundDisplay($cart_prices['salesPriceShipment']) - $cart_prices['shipmentValue'];
		} else {
			$cart_prices['salesPriceShipment'] = $cart_prices['shipmentValue'];
			$cart_prices['shipmentTax'] = 0;
		}
	}

	function getShipmentValue($params, $cart_prices) {
		return 0;
	}

	function getShipmentTaxId($params) {
		return 0;
	}

	/**
	 * Get the name of the shipment method
	 *
	 * @author Valerie Isaksen
	 * @param stdClass $shipment
	 * @return string Shipment method name
	 */
	function getShipmentName($shipment) {

		$return = '';
		$params = new JParameter($shipment->shipment_params);
		$shipmentLogo = $params->get('shipment_logos');
		$shipmentDescription = $params->get('shipment_description', '');
		if (!empty($shipmentLogo)) {
			$return = $this->displayLogos( $shipmentLogo  ) . ' ';
		}
		if (!empty($shipmentDescription)) {
			$shipmentDescription = '<span class="vmshipment_description">' . $shipmentDescription . '</span>';
		}

		return $return . '<span class="vmshipment_name">' . $shipment->shipment_name . '</span>' . $shipmentDescription;
	}

	/**
	 *
	 */


	function getOrderShipmentName($order_number, $shipment_method_id) {


		$db = JFactory::getDBO();
		$q = 'SELECT * FROM `' . $this->_tablename . '` '
		. 'WHERE `order_number` = "' . $order_number . '"  AND `shipment_id` =' . $shipment_method_id;
		$db->setQuery($q);
		if (!($order_shipment = $db->loadObject())) {
			return null;
		}
		JFactory::getLanguage()->load('com_virtuemart');
	 return $order_shipment->shipment_name  ;

	}

}
