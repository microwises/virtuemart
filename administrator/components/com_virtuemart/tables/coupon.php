<?php
/**
*
* Coupon table
*
* @package	VirtueMart
* @subpackage Coupon
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

/**
 * Coupon table class
 * The class is is used to manage the coupons in the shop.
 *
 * @package		VirtueMart
 * @author RickG
 */
class TableCoupon extends JTable {

	/** @var int Primary key */
	var $virtuemart_coupon_id			 	= 0;
	/** @var varchar Coupon name */
	var $coupon_code         	= '';
	/** @var string Coupon percentage or total */
	var $percent_or_total    	= 'percent';
	/** @var string Coupon type */
	var $coupon_type		    = 'permanent';
	/** @var Decimal Coupon value */
	var $coupon_value 			= '';
	/** @var datetime Coupon start date */
	var $coupon_start_date 		= '';
	/** @var datetime Coupon expiry date */
	var $coupon_expiry_date 	= '';
	/** @var decimal Coupon valid value */
	var $coupon_value_valid 	= 0;
               /** @var boolean */
	var $locked_on	= 0;
	/** @var time */
	var $locked_by	= 0;
	/**
	 * @author RickG
	 * @param $db A database connector object
	 */
	function __construct(&$db)
	{
		parent::__construct('#__virtuemart_coupons', 'virtuemart_coupon_id', $db);
	}


	/**
	 * Validates the coupon record fields.
	 *
	 * @author RickG
	 * @return boolean True if the table buffer is contains valid data, false otherwise.
	 */
	function check()
	{
		return true;
	}

}
// pure php no closing tag
