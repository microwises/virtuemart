<?php
/**
 * Attributes table
 *
 * @package	VirtueMart
 * @subpackage Country
 * @author RolandD 
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Attributes table class
 *
 * @author RolandD
 * @package		VirtueMart
 */
class TableAttributes extends JTable
{
	/** @var int Primary key */
	var $attribute_sku_id = 0;
	/** @var integer Product id */
	var $product_id = 0;
	/** @var string Attribute name */
	var $attribute_name = '';	
	/** @var int Listing order of attribute */
	var $attribute_list = 0;				
	
	/**
	 * @author RolandD
	 * @param $db A database connector object
	 */
	function __construct(&$db)
	{
		parent::__construct('#__vm_product_attribute_sku', 'attribute_sku_id', $db);
	}
}
?>
