<?php
/**
 * Discounts table
 *
 * @package	VirtueMart
 * @author RolandD 
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Discounts table class
 * The class is is used to manage the discounts in the shop.
 *
 * @author RolandD
 * @package		VirtueMart
 */
class TableDiscounts extends JTable {
	/** @var int Primary key */
	var $discount_id			= 0;
	/** @var int Discount amount */
	var $amount     	      	= null;	
	/** @var boolean If discount is percentage or not */
	var $is_percent        		= null;
	/** @var int Start date of the discount */
	var $start_date        		= null;
	/** @var int End date of the discount */
	var $end_date        		= null;
	
	/**
	* @author RolandD
	* @param $db A database connector object
	*/
	function __construct(&$db) {
		parent::__construct('#__vm_product_discount', 'discount_id', $db);
	}
}
?>
