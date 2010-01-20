<?php
/**
*
* Discounts table
*
* @package	VirtueMart
* @subpackage
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
 * Discounts table class
 * The class is is used to manage the discounts in the shop.
 *
 * @package		VirtueMart
 * @author RolandD
 */
class TableDiscounts extends JTable {

	/** @var int Primary key */
	var $discount_id			= 0;
	/** @var int Discount amount */
	var $amount     	      	= null;
	/** @var boolean If discount is percentage or not */
	var $is_percent        		= null;
	/** @var int Start date of the discount */
	var $start_date        		= null;
	/** @var int End date of the discount */
	var $end_date        		= null;

	/**
	* @author RolandD
	* @param $db A database connector object
	*/
	function __construct(&$db) {
		parent::__construct('#__vm_product_discount', 'discount_id', $db);
	}
}
?>
