<?php
/**
* Virtuemart Product Type table
*
* @package Virtuemart
* @author RolandD
* @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
*/

/* No direct access */
defined('_JEXEC') or die('Restricted access');

/**
* @package Virtuemart
 */
class TableProduct_type extends JTable {
	
	/** @var int Primary key */
	var $product_type_id = 0;
	/** @var string Product type name */
	var $product_type_name = null;	
	/** @var string Description */
	var $product_type_description = null;
	/** @var int Published */
	var $published = null;
	/** @var string Name of the browsepage */
	var $product_type_browsepage = null;
	/** @var string Name of the flypage to use */
	var $product_type_flypage = null;
	/** @var int The order to list the product types in */
	var $product_type_list_order = null;
	
	/**
	* @param database A database connector object
	 */
	function __construct($db) {
		parent::__construct('#__vm_product_type', 'product_type_id', $db );
	}
}
?>
