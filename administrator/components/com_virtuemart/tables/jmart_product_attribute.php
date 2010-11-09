<?php
/**
*
* Product attribute table
*
* @package	VirtueMart
* @subpackage Product
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
 * Country table class
 * The class is is used to manage the countries in the shop.
 *
 * @package		VirtueMart
 * @author RolandD
 */
class TableVirtuemart_product_attribute extends JTable {

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
// pure php no closing tag
