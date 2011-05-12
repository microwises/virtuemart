<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage
* @author RickG
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

// Load the model framework
jimport( 'joomla.application.component.model');

/**
 * Model for Macola
 *
 * @package		VirtueMart
 */
class VirtueMartModelVirtueMart extends JModel {

    /**
	 * creates a bulleted of the childen of this category if they exist
	 * @author pablo
	 * @param int $virtuemart_category_id
	 * @return string The HTML code
	 */
	function GetVendorDetails($virtuemart_vendor_id)
	{
		$db = JFactory::getDBO();

		$query = "SELECT virtuemart_category_id, category_child_id, category_name ";
		$query .= "FROM #__virtuemart_categories, #__virtuemart_category_categories ";
		$query .= "WHERE #__virtuemart_category_categories.category_parent_id = '$virtuemart_category_id' ";
		$query .= "AND #__virtuemart_categories.virtuemart_category_id = #__virtuemart_category_categories.category_child_id ";
		//$query .= "AND #__virtuemart_categories.virtuemart_vendor_id = '$hVendor_id' ";
		$query .= "AND #__virtuemart_categories.virtuemart_vendor_id = '1' ";
		$query .= "AND #__virtuemart_categories.published = '1' ";
		$query .= "ORDER BY #__virtuemart_categories.list_order, #__virtuemart_categories.category_name ASC";

		$childList = $this->_getList( $query );
		return $childList;
	}


	/**
	 * Gets the total number of customers
	 *
     * @author RickG
	 * @return int Total number of customers in the database
	 */
	function getTotalCustomers() {
		$query = 'SELECT `virtuemart_user_id`  FROM `#__virtuemart_userinfos` WHERE `address_type` = "BT"';
        return $this->_getListCount($query);
    }

	/**
	 * Gets the total number of active products
	 *
     * @author RickG
	 * @return int Total number of active products in the database
	 */
	function getTotalActiveProducts() {
		$query = 'SELECT `virtuemart_product_id` FROM `#__virtuemart_products` WHERE `published`="1"';
        return $this->_getListCount($query);
    }

	/**
	 * Gets the total number of inactive products
	 *
     * @author RickG
	 * @return int Total number of inactive products in the database
	 */
	function getTotalInActiveProducts() {
		$query = 'SELECT `virtuemart_product_id` FROM `#__virtuemart_products` WHERE  `published`="0"';
        return $this->_getListCount($query);
    }

	/**
	 * Gets the total number of featured products
	 *
     * @author RickG
	 * @return int Total number of featured products in the database
	 */
	function getTotalFeaturedProducts() {
		$query = 'SELECT `virtuemart_product_id` FROM `#__virtuemart_products` WHERE `product_special`="Y"';
        return $this->_getListCount($query);
    }


	/**
	 * Gets the total number of orders with the given status
	 *
     * @author RickG
	 * @return int Total number of orders with the given status
	 */
	function getTotalOrdersByStatus() {
		$query = 'SELECT `#__virtuemart_orderstates`.`order_status_name`, `#__virtuemart_orderstates`.`order_status_code`, ';
		$query .= '(SELECT count(virtuemart_order_id) FROM `#__virtuemart_orders` WHERE `#__virtuemart_orders`.`order_status` = `#__virtuemart_orderstates`.`order_status_code`) as order_count ';
 		$query .= 'FROM `#__virtuemart_orderstates`';
        return $this->_getList($query);
    }


	/**
	 * Gets a list of recent orders
	 *
     * @author RickG
	 * @return ObjectList List of recent orders.
	 */
	function getRecentOrders($nbrOrders=5) {
		$query = 'SELECT `virtuemart_order_id`, `order_total` FROM `#__virtuemart_orders` ORDER BY `created_on` desc';
        return $this->_getList($query, 0, $nbrOrders);
    }


	/**
	 * Gets a list of recent customers
	 *
     * @author RickG
	 * @return ObjectList List of recent orders.
	 */
	function getRecentCustomers($nbrCusts=5) {
		$query = 'SELECT `id` as `virtuemart_user_id`, `first_name`, `last_name`, `virtuemart_order_id` FROM `#__users` as `u` ';
		$query .= 'JOIN `#__virtuemart_users` as uv ON u.id = uv.virtuemart_user_id ';
		$query .= 'JOIN `#__virtuemart_userinfos` as ui ON u.id = ui.virtuemart_user_id ';
		$query .= 'JOIN `#__virtuemart_orders` as uo ON u.id = uo.virtuemart_user_id ';
		$query .= 'WHERE `perms` <> "admin" ';
        $query .= 'AND `perms` <> "storeadmin" ';
        $query .= 'AND INSTR(`usertype`, "administrator") = 0 AND INSTR(`usertype`, "Administrator") = 0 ';
        $query .= 'ORDER BY uo.`created_on` DESC';
        return $this->_getList($query, 0, $nbrCusts);
    }
}

//pure php no tag