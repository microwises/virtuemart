<?php
/**
*
* Attributes table
*
* @package	VirtueMart
* @subpackage Attributes
* @author RolandD
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id$
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * Attributes table class
 *
 * @package		VirtueMart
 * @author RolandD
 */
class TableAttributes extends JTable {

	/** @var int Primary key */
	var $attribute_sku_id = 0;
	/** @var integer Product id */
	var $product_id = 0;
	/** @var string Attribute name */
	var $attribute_name = '';
	/** @var int Listing order of attribute */
	var $attribute_list = 0;
        /** @var boolean */
	var $checked_out	= 0;
	/** @var time */
	var $checked_out_time	= 0;
	/**
	 * @author RolandD
	 * @param $db A database connector object
	 */
	function __construct(&$db)
	{
		parent::__construct('#__vm_product_attribute_sku', 'attribute_sku_id', $db);
	}
}
// pure php no closing tag
