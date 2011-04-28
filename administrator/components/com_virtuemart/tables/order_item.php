<?php
/**
*
* Order item table
*
* @package	VirtueMart
* @subpackage Orders
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
 * Order item table class
 * The class is is used to manage the order items in the shop.
 *
 * @package	VirtueMart
 * @author RolandD
 */
class TableOrder_item extends JTable {

	/** @var int Primary key */
	var $order_item_id = 0;
	/** @var int User ID */
	var $order_id = NULL;
	/** @var int User info ID */
	var $user_info_id = NULL;
	/** @var int Vendor ID */
	var $vendor_id = NULL;
	/** @var int Product ID */
	var $product_id = NULL;
	/** @var string Order item SKU */
	var $order_item_sku = NULL;
	/** @var string Order item name */
	var $order_item_name = NULL;
	/** @var int Product Quantity */
	var $product_quantity = NULL;
	/** @var decimal Product item price */
	var $product_item_price = 0.00000;
	/** @var decimal Product final price */
	var $product_final_price = 0.00000;
	/** @var string Order item currency */
	var $order_item_currency = NULL;
	/** @var char Order status */
	var $order_status = NULL;
	/** @var int Creation date */
	var $cdate = NULL;
	/** @var int Last modified date */
	var $mdate = NULL;
	/** @var text Product attribute */
	var $product_attribute = NULL;
        /** @var boolean */
	var $checked_out	= 0;
	/** @var time */
	var $checked_out_time	= 0;
	/**
	 * @param $db Class constructor; connect to the database
	 */
	function __construct($db) {
		parent::__construct('#__vm_order_item', 'order_item_id', $db);
	}

	/**
	 * For setting the time
	 *
	 * @author Max Milbers
	 */

	function check(){
		$date = JFactory::getDate();
		$today = $date->toMySQL();
		if(empty($this->cdate)){
			$this->cdate = $today;
		}
     	$this->mdate = $today;
	}
}
// pure php no closing tag
