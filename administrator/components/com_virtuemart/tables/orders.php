<?php
/**
 * Orders table
 *
 * @package	VirtueMart
 * @subpackage Orders
 * @author RolandD
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Orders table class
 * The class is is used to manage the orders in the shop.
 *
 * @package	VirtueMart
 * @author RolandD
 */
class TableOrders extends JTable {
	/** @var int Primary key */
	var $order_id = 0;
	/** @var int User ID */
	var $user_id = 0;
	/** @var int Vendor ID */
	var $vendor_id = 0;
	/** @var int Order number */
	var $order_number = NULL;
	/** @var int User info ID */
	var $user_info_id = NULL;
	/** @var decimal Order total */
	var $order_total = 0.00000;
	/** @var decimal Order subtotal */
	var $order_subtotal = 0.00000;
	/** @var decimal Order tax */
	var $order_tax = 0.00000;
	/** @var text Serialized tax details */
	var $order_tax_details = null;
	/** @var decimal Shipping costs */
	var $order_shipping = 0.00000;
	/** @var decimal Shipping cost tax */
	var $order_shipping_tax = 0.00000;
	/** @var decimal Coupon value */
	var $coupon_discount = 0.00000;
	/** @var string Coupon code */
	var $coupon_code = NULL;
	/** @var decimal Order discount */
	var $order_discount = 0.00000;
	/** @var string Order currency */
	var $order_currency = NULL;
	/** @var char Order status */
	var $order_status = NULL;
	/** @var int Creation date */
	var $cdate = NULL;
	/** @var int Last modified date */
	var $mdate = NULL;
	/** @var int Shipping method ID */
	var $ship_method_id = NULL;
	/** @var text Customer note */
	var $customer_note = 0;
	/** @var string Users IP Address */
	var $ip_address = 0;
	

	/**
	 * @param $db Class constructor; connect to the database
	 */
	function __construct($db) {
		parent::__construct('#__vm_orders', 'order_id', $db);
	}
}
?>
