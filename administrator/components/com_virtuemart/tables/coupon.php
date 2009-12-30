<?php
/**
 * Coupon table
 *
 * @package	VirtueMart
 * @subpackage Coupon
 * @author RickG 
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Coupon table class
 * The class is is used to manage the coupons in the shop.
 *
 * @author RickG
 * @package		VirtueMart
 */
class TableCoupon extends JTable
{
	/** @var int Primary key */
	var $coupon_id			 	= 0;
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

	/**
	 * @author RickG
	 * @param $db A database connector object
	 */
	function __construct(&$db)
	{
		parent::__construct('#__vm_coupons', 'coupon_id', $db);
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
?>
