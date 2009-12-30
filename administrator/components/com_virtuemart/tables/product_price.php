<?php
/**
 * Product table
 *
 * @package	VirtueMart
 * @subpackage Product
 * @author RolandD
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Product table class
 * The class is is used to manage the products in the shop.
 *
 * @package	VirtueMart
 * @author RolandD
 */
class TableProduct_price extends JTable
{
	/** @var int Primary key */
	var $product_price_id = 0;
	/** @var int Product id */
	var $product_id	= 0;
	/** @var string Product price */
	var $product_price = null;
	/** @var string Product currency */
	var $product_currency = null;
    /** @var string Creation date */
	var $cdate = null;
    /** @var string Modified date */
	var $mdate = null;
	/** @var int Shopper group ID */
	var $shopper_group_id = null;
	/** @var int Price quantity start */
	var $price_quantity_start = null;
	/** @var int Price quantity end */
	var $price_quantity_end = null;
	
	/**
	 * @author RolandD
	 * @param $db A database connector object
	 */
	function __construct(&$db) {
		parent::__construct('#__vm_product_price', 'product_price_id', $db);
	}
}
?>
