<?php
/**
 * Order item table
 *
 * @package	VirtueMart
 * @subpackage Orders
 * @author RolandD
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Order item table class
 * The class is is used to manage the order items in the shop.
 *
 * @package	VirtueMart
 * @author RolandD
 */
class TableOrder_item extends JTable {
	/** @var int Primary key */
	var $order_item_id = 0;
	/** @var int User ID */
	var $order_id = NULL;
	/** @var int User info ID */
	var $user_info_id = NULL;
	/** @var int Vendor ID */
	var $vendor_id = NULL;
	/** @var int Product ID */
	var $product_id = NULL;
	/** @var string Order item SKU */
	var $order_item_sku = NULL;
	/** @var string Order item name */
	var $order_item_name = NULL;
	/** @var int Product Quantity */
	var $product_quantity = NULL;
	/** @var decimal Product item price */
	var $product_item_price = 0.00000;
	/** @var decimal Product final price */
	var $product_final_price = 0.00000;
	/** @var string Order item currency */
	var $order_item_currency = NULL;
	/** @var char Order status */
	var $order_status = NULL;
	/** @var int Creation date */
	var $cdate = NULL;
	/** @var int Last modified date */
	var $mdate = NULL;
	/** @var text Product attribute */
	var $product_attribute = NULL;

	/**
	 * @param $db Class constructor; connect to the database
	 */
	function __construct($db) {
		parent::__construct('#__vm_order_item', 'order_item_id', $db);
	}
}
?>
