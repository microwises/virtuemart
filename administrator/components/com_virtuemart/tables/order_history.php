<?php
/**
 * Order history table
 *
 * @package	VirtueMart
 * @subpackage Orders
 * @author RolandD
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Order history table class
 * The class is is used to manage the order history in the shop.
 *
 * @package	VirtueMart
 * @author RolandD
 */
class TableOrder_history extends JTable {
	/** @var int Primary key */
	var $order_status_history_id = 0;
	/** @var int Order ID */
	var $order_id = 0;
	/** @var char Order status code */
	var $order_status_code = 0;
	/** @var datetime Date added */
	var $date_added = '0000-00-00 00:00:00';
	/** @var int Customer notified */
	var $customer_notified = 0;
	/** @var text Comments */
	var $comments = NULL;	

	/**
	 * @param $db Class constructor; connect to the database
	 */
	function __construct($db) {
		parent::__construct('#__vm_order_history', 'order_status_history_id', $db);
	}
}
?>
