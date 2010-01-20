<?php
/**
*
* Product discount table
*
* @package	VirtueMart
* @subpackage Discount
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
 * Product discount class
 *
 * @package	VirtueMart
 * @subpackage Discount
 * @author RolandD
 */
class TableProduct_discount extends JTable {

	/** @var int Primary key */
	var $discount_id = 0;
	/** @var int Discount amount */
	var $amount = 0;
	/** @var int Sets whether the discount is an amount or percentage */
	var $is_percent = '';
	/** @var int Start date of discount */
	var $start_date = '';
    /** @var int End date of discount */
	var $end_date = '';

	/**
	 * @author RolandD
	 * @param $db A database connector object
	 */
	function __construct(&$db) {
		parent::__construct('#__vm_product_discount', 'discount_id', $db);
	}
}
?>
