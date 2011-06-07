<?php
/**
*
* Data module for shipping rates
*
* @package	VirtueMart
* @subpackage ShippingRate
* @author RickG
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id$
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the model framework
jimport( 'joomla.application.component.model');

if(!class_exists('VmModel'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmmodel.php');

/**
 * Model class for shop shipping carriers
 *
 * @package	VirtueMart
 * @subpackage ShippingRate
 * @author RickG
 */
class VirtueMartModelShippingRate extends VmModel {

	/**
	 * constructs a VmModel
	 * setMainTable defines the maintable of the model
	 * @author Max Milbers
	 */
	function __construct() {
		parent::__construct();
		$this->setMainTable('shippingrates');
	}

    /**
     * Retrieve the detail record for the current $id if the data has not already been loaded.
     *
     * @author RickG
     */
	function getShippingRate()
	{
  		if (empty($this->_data)) {
   			$this->_data = $this->getTable('shippingrates');
   			$this->_data->load((int)$this->_id);

   			// Convert ; separated string into array
   			$this->_data->shipping_rate_country = explode(';', $this->_data->shipping_rate_country);
   			if(empty($this->_data->shipping_rate_virtuemart_currency_id)){
   				$vendorId=1;
   				if(!class_exists('VirtueMartModelVendor')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'vendor.php');
   				$this->_data->shipping_rate_virtuemart_currency_id = VirtueMartModelVendor::getVendorCurrency($vendorId)->virtuemart_currency_id;
   			}
  		}

  		if (!$this->_data) {
   			$this->_data = new stdClass();
   			$this->_id = 0;
   			$this->_data = null;
  		}

  		return $this->_data;
	}

	/**
	 * Get the prices for a given shipping rate
	 * @param integer $_id Shipping rate ID
	 * @param boolean $_checkFree true if the order total should be checked for free shipping, default is false
	 * @return Indexed array with the keys shipping_rate_value and shipping_rate_package_fee
	 * @author Oscar van Eijk, Max Milbers
	 */
	public function getShippingRatePrices ($_id, $_checkFree = false)
	{
		$_q = 'SELECT * '
			. 'FROM  `#__virtuemart_shippingrates` AS r '
			. ',     `#__virtuemart_shippingcarriers` AS c '
			. 'WHERE `virtuemart_shippingrate_id` = ' . $_id . ' '
			. 'AND   r.shipping_rate_carrier_id = c.virtuemart_shippingcarrier_id '
		;
		$this->_db->setQuery($_q);
		$_rates = $this->_db->loadAssoc();
		if ($_checkFree === true) {
			if ($this->freeShipping()) {
				$_rates['shipping_rate_value'] = 0.00;
				$_rates['shipping_rate_package_fee'] = 0.00;
			}
		}
		if(!isset($_rates['shipping_rate_value'])) $_rates['shipping_rate_value'] = 0.0;
		if(!isset($_rates['shipping_rate_package_fee'])) $_rates['shipping_rate_package_fee'] = 0.0;
		if(!isset($_rates['shipping_carrier_name'])) $_rates['shipping_carrier_name'] = 'No carrier name given, please configure your shipper properly';
		if(!isset($_rates['shipping_rate_name'])) $_rates['shipping_rate_name'] = 'No shipping rate name given, please configure your shipping rate properly';
		if(!isset($_rates['shipping_rate_vat_id'])) $_rates['shipping_rate_vat_id'] = 0;

		return $_rates;
	}

	/**
	 * Check the order total to see if this order is valid for free shipping.
	 * @access private
	 * @return boolean; true when shipping is free
	 * @author Oscar van Eijk
	 */
	private function freeShipping()
	{
		if(!class_exists('VirtueMartCart')) require(JPATH_VM_SITE.DS.'helpers'.DS.'cart.php');
		$_cart = VirtueMartCart::getCart();
		if (!$_cart) {
			return false;
		}
		if(!class_exists('VirtueMartModelVendor')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'vendor.php');
		$_vendor = new VirtueMartModelVendor();
		$_vendor->setId($_cart->vendorId);
		$_store = $_vendor->getVendor();

		if ($_store->vendor_freeshipping > 0) {
			if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');
			$_calc = calculationHelper::getInstance();
			$_prices = $_calc->getCartPrices();
			if ($_prices == null) {
				// When called from the plugin
				$_prices = $_cart->getCartPrices(); // ... but causes a loop from the cart itself!
			}
			if ($_prices['salesPrice'] > $_store->vendor_freeshipping) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Bind the post data to the shipping rate table and save it
     *
     * @author RickG, Max Milbers, Oscar van Eijk
     * @return mixed False if the save was unsuccessful, the shiping rate ID otherwise.
	 */
    function store()
	{
		$table = $this->getTable('shippingrates');

		$data = JRequest::get( 'post' );

		// Store multiple selectlist entries as a ; separated string
		if (key_exists('shipping_rate_country', $data) && is_array($data['shipping_rate_country'])) {
		    $data['shipping_rate_country'] = implode(';', $data['shipping_rate_country']);
		}

		// Bind the form fields to the shipping rate table
		if (!$table->bind($data)) {
			$this->setError($table->getError());
			return false;
		}

		// Make sure the shipping rate record is valid
		if (!$table->check()) {
			$this->setError($table->getError());
			return false;
		}

		// Save the shipping rate record to the database
		if (!$table->store()) {
			$this->setError($table->getError());
			return false;
		}

		return $table->virtuemart_shippingrate_id;
	}


	/**
	 * Retireve a list of shipping rates from the database.
	 *
     * @author RickG
	 * @return object List of shipping rate objects
	 */
	function getShippingRates()
	{
		$query = 'SELECT sr.*, sc.shipping_carrier_name FROM `#__virtuemart_shippingrates` AS sr ';
		$query .= 'JOIN `#__virtuemart_shippingcarriers` AS sc ON `sc`.`virtuemart_shippingcarrier_id` = `sr`.`shipping_rate_carrier_id`';
		$query .= 'ORDER BY `sr`.`virtuemart_shippingrate_id`';
		$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		/* Total for pagination */
		$this->_total = $this->_getListCount($query);
		return $this->_data;
	}
}
// pure php no closing tag