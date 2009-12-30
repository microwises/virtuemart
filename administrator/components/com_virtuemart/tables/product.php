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
class TableProduct extends JTable
{
	/** @var int Primary key */
	var $product_id				= null;
	/** @var integer Product id */
	var $vendor_id		= null;
	/** @var string File name */
	var $product_parent_id		= null;
	/** @var string File title */
	var $product_sku				= null;
    /** @var string File description */
	var $product_s_desc		= null;
    /** @var string File extension */
	var $product_desc			= null;
	/** @var string File mime type */
	var $product_thumb_image			= null;
	/** @var string File URL */
	var $product_full_image				= null;
	/** @var int File published or not */
	var $product_publish		= null;
	/** @var int File is an image or other */
	var $product_weight			= null;
	/** @var int File image height */
	var $product_weight_uom		= null;
	/** @var int File image width */
	var $product_length		= null;
	/** @var int File thumbnail image height */
	var $product_width = null;
	/** @var int File thumbnail image width */
	var $product_height	= null;
	/** @var int File thumbnail image width */
	var $product_lwh_uom	= null;
	/** @var int File thumbnail image width */
	var $product_url	= null;
	/** @var int File thumbnail image width */
	var $product_in_stock	= null;
	/** @var int File thumbnail image width */
	var $low_stock_notification	= null;
	/** @var int File thumbnail image width */
	var $product_available_date	= null;
	/** @var int File thumbnail image width */
	var $product_availability	= null;
	/** @var int File thumbnail image width */
	var $product_special	= null;
	/** @var int File thumbnail image width */
	var $product_discount_id	= null;
	/** @var int File thumbnail image width */
	var $ship_code_id	= null;
	/** @var int File thumbnail image width */
	var $cdate	= null;
	/** @var int File thumbnail image width */
	var $mdate	= null;
	/** @var string Name of the product */
	var $product_name	= null;
	/** @var int File thumbnail image width */
	var $product_sales	= null;
	/** @var int File thumbnail image width */
	var $attribute	= null;
	/** @var int File thumbnail image width */
	var $custom_attribute	= null;
	/** @var int File thumbnail image width */
	var $product_tax_id	= null;
	/** @var int File thumbnail image width */
	var $product_unit	= null;
	/** @var int File thumbnail image width */
	var $product_packaging	= null;
	/** @var int File thumbnail image width */
	var $child_options	= null;
	/** @var int File thumbnail image width */
	var $quantity_options	= null;
	/** @var int File thumbnail image width */
	var $child_option_ids	= null;
	/** @var int File thumbnail image width */
	var $product_order_levels	= null;
	/** @var string Internal note for product */
	var $intnotes = null;
	/** @var string Meta description */
	var $metadesc	= null;
	/** @var string Meta keys */
	var $metakey	= null;
	/** @var string Meta robot */
	var $metarobot	= null;
	/** @var string Meta author */
	var $metaauthor	= null;
	
	
	/**
	 * @author RolandD
	 * @param $db A database connector object
	 */
	function __construct(&$db) {
		parent::__construct('#__vm_product', 'product_id', $db);
	}
}
?>
