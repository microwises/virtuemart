<?php
/**
 * Product attribute table
 *
 * @package	VirtueMart
 * @subpackage Product
 * @author RolandD
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Country table class
 * The class is is used to manage the countries in the shop.
 *
 * @author RolandD
 * @package		VirtueMart
 */
class TableVirtuemart_product_attribute extends JTable
{
	/** @var int Primary key */
	var $attribute_id		= 0;
	/** @var integer Product id */
	var $product_id			= 0;
	/** @var string File name */
	var $attribute_name		= '';
	/** @var string File title */
	var $attribute_value	= '';
	
	/**
	 * @author RolandD
	 * @param $db A database connector object
	 */
	function __construct(&$db) {
		parent::__construct('#__vm_product_attribute', 'attribute_id', $db);
	}
}
?>
