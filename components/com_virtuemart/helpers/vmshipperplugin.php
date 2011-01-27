<?php
/**
 * Abstract class for shipper plugins
 *
 * @package	VirtueMart
 * @subpackage Plugins
 * @author Oscar van Eijk
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2011 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id$
 */
 
// Load the helper functions that are needed by all plugins
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'shopfunctions.php');
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'dbscheme.php');

// Get the plugin library
jimport('joomla.plugin.plugin');

/**
* Abstract class for shipper plugins.
* This class provides some standard and abstract methods that can or must be reimplemented.
* 
* @tutorial All methods are documented, but to make life easier, here's a short overview
* how the methods can be used in the process order.
* Methods marked with (*) are required.
* 	* _createTable()(*) is called by the constructor. Use this method to create or alter the database table.
* 	* When a shopper selects a shipper, plgOnSelectShipper()(*) is fired. It displays the shipper and can be used
* 	for collecting extra - shipper specific - info.
* 	* After selecting, plgVmShipperSelected() can be used to store extra shipper info in the cart. The selected shipper
* 	ID will be stored in the cart by the checkout process before this method is fired.
* 	* plgOnConfirmShipper() is fired when the order is confirmed and stored to the database. It is called
* 	before the rest of the order or stored, when reimplemented, it *must* include a call to parent::plgOnConfirmShipper()
* 	(or execute the same steps to put all data in the cart)
* 
* When a stored order is displayed in the backend, the following events are used:
* 	* plgVmOnShowOrderShipperBE()(*) collects and displays specific data about (a) shipment(s)
* 	* plgVmOnShowOrderLineShipperBE() can be used to show information about a single orderline, e.g.
* 	add or display a package code at line level when more packages are shipped.
* 	* plgVmOnUpdateOrder() is fired from the backend after the order has been saved. If one of the
* 	show methods above have to option to add or edit info, this method must be used to save the data.
* 
* The frontend has 2 similar show methods:
* 	* plgVmOnShowOrderShipperFE()(*) collects and displays specific data about (a) shipment(s)
* 	* plgVmOnShowOrderLineShipperFE() can be used to show information about a single orderline, e.g.
* 	display a package code at line level when more packages are shipped.
* 
* @package	VirtueMart
* @subpackage Plugins
* @author Oscar van Eijk
*/
abstract class vmShipperPlugin extends JPlugin
{
	/**
	 * Shipper. This var must be overwritten by all plugins, by adding this code to the constructor:
	 * $this->_selement = basename(__FILE, '.php');
	 * @var string Identification of the shipper
	 */
	protected $_selement = '';

	/**
	 * Constructor
	 *
	 * @param object $subject The object to observe
	 * @param array  $config  An array that holds the plugin configuration
	 * @since 1.5
	 */
	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}

	/**
	 * Get the total weight for the order, based on which the proper shipping rate
	 * can be selected.
	 * @param object $_cart Cart object
	 * @return float Total weight for the order
	 * @author Oscar van Eijk
	 */
	protected function getOrderWeight($_cart)
	{
		dump ($_cart, 'Cart in vmshipperplugin on line '.__LINE__);
	}

	/**
	 * Method to create te plugin specific table; must be reimplemented.
	 * @example 
	 * 	$_scheme = DbScheme::get_instance();
	 * 	$_scheme->create_scheme('#__vm_order_shipper_'.$this->_selement);
	 * 	$_schemeCols = array(
	 * 		 'id' => array (
	 * 				 'type' => 'int'
	 * 				,'length' => 11
	 * 				,'auto_inc' => true
	 * 				,'null' => false
	 * 		)
	 * 		,'order_id' => array (
	 * 				 'type' => 'int'
	 * 				,'length' => 11
	 * 				,'null' => false
	 * 		)
	 * 		,'shipper_id' => array (
	 * 				 'type' => 'text'
	 * 				,'null' => false
	 * 		)
	 * 	);
	 * 	$_schemeIdx = array(
	 * 		 'idx_order_payment' => array(
	 * 				 'columns' => array ('order_id')
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
	 * one of the available shippers.
	 * It should display a radio button (name: shipper_id) to select the shipper. In the description,
	 * the shipping cost can also be displayed, based on the total order weight and the shipto
	 * country.
	 * This must be made final during confirmation.
	 * 
	 * @param object $_cart the cart object
	 * @param boolean $_selected True if this shipper is currently selected in the cart
	 * @return HTML code to display the form
	 * @author Oscar van Eijk
	 */
	 
	abstract public function plgVmOnSelectShipper($_cart, $_selected = false);


	/**
	 * This event is fired after the shipping method has been selected. It can be used to store
	 * additional shipper info in the cart.
	 * 
	 * @author Oscar van Eijk
	 */
	function plgVmOnShipperSelected($cart)
	{
		return null;
	}

	/**
	 * This event is fired after the payment has been processed; it selects the actual shipping rate
	 * based on the shipto (country, zip) and/or order weight, and optionally writes extra info
	 * to the database (in which case this plugin must be reimplemented).
	 * 
	 * @param object $_cart Cart object
	 * @return boolean True on succes, false otherwise
	 * @author Oscar van Eijk
	 */
	public function plgVmOnConfirmShipper(VirtueMartCart $_cart)
	{
		$_cart->selectShippingRate($this->selectShippingRate($_cart));
	}

	/**
	 * This method is fired when showing the order details in the backend.
	 * It displays the shipper-specific data.
	 * All plugins *must* reimplement this method.
	 * 
	 * @param integer $_orderId The order ID
	 * @param integer $_shipperId Shipper ID used for this order
	 * @return mixed Null for shippers that aren't active, text (HTML) otherwise
	 * @author Oscar van Eijk
	 */
	abstract function plgVmOnShowOrderShipperBE($_orderId, $_shipperId);

	/**
	 * This method is fired when showing the order details in the backend, for every orderline.
	 * It can be used to add or display line specific package codes
	 * 
	 * @param integer $_orderId The order ID
	 * @param integer $_lineId
	 * @return mixed Null for shippers that aren't active, text (HTML) otherwise
	 * @author Oscar van Eijk
	 */
	abstract function plgVmOnShowOrderLinShipperBE($_orderId, $_lineId)
	{
		return null;
	}

	/**
	 * Save updated order(line) data to the shipper specific table
	 * 
	 * @param object $_cart Cart object
	 * @return mixed, True on success, false on failures (the rest of the save-process will be
	 * skipped!), or null when this shipper is not actived.
	 * @author Oscar van Eijk
	 */
	public function plgVmOnUpdateOrder (VirtueMartCart $_cart)
	{
		return null;
	}

	/**
	 * This method is fired when showing the order details in the frontend.
	 * It displays the shipper-specific data.
	 * All plugins *must* reimplement this method.
	 * 
	 * @param integer $_orderId The order ID
	 * @return mixed Null for shippers that aren't active, text (HTML) otherwise
	 * @author Oscar van Eijk
	 */
	abstract function plgVmOnShowOrderShipperFE($_orderId);

	/**
	 * This method is fired when showing the order details in the frontend, for every orderline.
	 * It can be used to display line specific package codes, e.g. with a link to external tracking and
	 * tracing systems
	 * 
	 * @param integer $_orderId The order ID
	 * @param integer $_lineId
	 * @return mixed Null for shippers that aren't active, text (HTML) otherwise
	 * @author Oscar van Eijk
	 */
	abstract function plgVmOnShowOrderLinShipperFE($_orderId, $_lineId)
	{
		return null;
	}
	

	/**
	 * Get the shipping rate ID for a given order number
	 * @access protected
	 * @author Oscar van Eijk
	 * @param int $_id The order ID
	 * @return int The shipping rate ID, or -1 when not found 
	 */
	protected function getShippingRateIDForOrder($_id)
	{
		$_db = &JFactory::getDBO();
		$_q = 'SELECT `ship_method_id` '
			. 'FROM #__vm_orders '
			. "WHERE order_id = $_id";
		$_db->setQuery($_q);
		if (!($_r = $_db->loadAssoc())) {
			return -1;
		}
		return $_r['ship_method_id'];
	}

	/**
	 * Get the shipper ID for a given order number
	 * @access protected
	 * @author Oscar van Eijk
	 * @param int $_id The order ID
	 * @return int The shipper ID, or -1 when not found 
	 */
	protected function getShipperIDForOrder($_id)
	{
		$_db = &JFactory::getDBO();
		$_q = 'SELECT s.`shipping_rate_carrier_id` AS shipper_id '
			. 'FROM #__vm_orders        AS o '
			. ',    #__vm_shipping_rate AS s '
			. "WHERE o.`order_id` = $_id "
			. 'AND   o.`ship_method_id` = s.`shipping_rate_id`';
		$_db->setQuery($_q);
		if (!($_r = $_db->loadAssoc())) {
			return -1;
		}
		return $_r['shipper_id'];
	}

	/**
	 * Select the shipping rate ID, based on the selected shipper in combination with the
	 * shipto address (country and zipcode) and the total order weight.
	 * @param object $_cart Cart object
	 * @param int _shipperID Shipper ID
	 * @return int Shipping rate ID. Only 1 selected ID will be returned; if more ID's match, the
	 * cheapest will be selected.
	 */
	protected function selectShippingRate(VirtueMartCart $_cart, $_shipperID)
	{
		$_db = &JFactory::getDBO();
		$_q = 'SELECT `shipping_rate_id` '
			. 'FROM #__vm_shipping_rate '
			. "WHERE `shipping_rate_carrier_id` = $_shipperID "
			. "AND   $_orderWeight BETWEEN `shipping_rate_weight_start` AND `shipping_rate_weight_end` "
			. 'AND   (`shipping_rate_country` = \'\' '
			.	 'OR `shipping_rate_country` REGEXP \'[[:<:]]'.$_stCountry.'[[:>:]]\' )'
			. 'ORDER BY (`shipping_rate_value` + `shipping_rate_package_fee`) '
			. 'LIMIT 1';
		$_db->setQuery($_q);
		if (!($_r = $_db->loadAssoc())) {
			return -1;
		}
		return $_r['shipping_rate_id'];
	}
	/**
	 * This method checks if the selected shipper matches the current plugin
	 * @param string $_selement Element name, taken from the plugin filename
	 * @param int $_sid The shipper ID
	 * @author Oscar van Eijk
	 * @return True if the calling plugin has the given payment ID
	 */
	final protected function selectedThisMethod($_selement, $_sid)
	{
		$_db = &JFactory::getDBO();
		$_q = 'SELECT COUNT(*) AS c '
			. 'FROM #__vm_shipping_carrier AS vm '
			. ',    #__plugins AS j '
			. "WHERE vm.shipping_carrier_id = '$_sid' "
			. 'AND   vm.shipping_carrier_jplugin_id = j.id '
			. "AND   j.element = '$_selement'";
		$_db->setQuery($_q);
		$_r = $_db->loadAssoc(); // TODO Error check
		return ($_r['c'] == 1);
	}

	/**
	 * Get the name of the shipper
	 * @param int $_sid The Shipper ID
	 * @author Oscar van Eijk
	 * @return string Shipper name
	 */
	final protected function getThisShipperName($_sid)
	{
		$_db = &JFactory::getDBO();
		$_q = 'SELECT `shipping_carrier_name` '
			. 'FROM #__vm_shipping_carrier '
			. "WHERE shipping_carrier_id ='$_pid' ";
		$_db->setQuery($_q);
		$_r = $_db->loadAssoc(); // TODO Error check
		return $_r['shipping_carrier_name'];
		
	}
	/**
	 * This method writes all shipper plugin specific data to the plugin's table
	 *
	 * @param array $_values Indexed array in the format 'column_name' => 'value'
	 * @param string $_table Table name
	 * @author Oscar van Eijk
	 */
	protected function writeShipperData($_values, $_table)
	{
		if (count($_values) == 0) {
			JError::raiseWarning(500, 'writeShipperData got no data to save to ' . $_table);
			return;
		}
		$_cols = array();
		$_vals = array();
		foreach ($_values as $_col => $_val) {
			$_cols[] = "`$_col`";
			$_vals[] = "'$_val'";
		}
		$_db = JFactory::getDBO();
		$_q = 'INSERT INTO `' . $_table . '` ('
			. implode(',', $_cols)
			. ') VALUES ('
			. implode(',', $_vals)
			. ')';
		$_db->setQuery($_q);
		if (!$_db->query()) {
			JError::raiseWarning(500, $_db->getErrorMsg());
		}
	}
}
