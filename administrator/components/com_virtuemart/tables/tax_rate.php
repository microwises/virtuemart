<?php
/**
 * Tax rate table
 *
 * @package	VirtueMart
 * @subpackage Tax
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
 * @subpackage Tax
 * @author RolandD
 */
class TableTax_rate extends JTable
{
	/** @var int Primary key */
	var $tax_rate_id = 0;
	/** @var int Vendor id */
	var $vendor_id = 0;
	/** @var string Tax state */
	var $tax_state = '';
	/** @var string File title */
	var $tax_country = '';
    /** @var int Modified date */
	var $mdate = '';
    /** @var string Tax rate */
	var $tax_rate = '';
	
	/**
	 * @author RolandD
	 * @param $db A database connector object
	 */
	function __construct(&$db) {
		parent::__construct('#__vm_tax_rate', 'tax_rate_id', $db);
	}
}
?>
