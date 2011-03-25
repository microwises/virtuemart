<?php
/**
*
* Configuration table
*
* @package	VirtueMart
* @subpackage Config
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
 * @package	VirtueMart
 * @subpackage Config
 * @author RickG
 */
class TableConfig extends JTable {

	/** @var int Primary key */
	var $config_id			 		= 0;
	/** @var tinyint Shop Offline flag */
	var $shop_is_offline       		= 0;
	/** @var text Shop Offline message */
	var $offline_message       		= '';
	/** @var tinyint Usa as catalog flag */
	var $use_as_catalog       		= 0;
	/** @var tinyint Show prices flag */
	var $show_prices	       		= 1;
	/** @var tinyint Price access level enabled */
	var $price_access_level_enabled = 0;
	/** @var varchar Price access level */
	var $price_access_level    		= '';
	/** @var tinyint Show prices with tax flag */
	var $show_prices_with_tax  		= 0;
	/** @var tinyint Show excluding tax note flag */
	var $show_excluding_tax_note	= 0;
	/** @var tinyint Show including tax note flag */
	var $show_including_tax_note    = 0;
	/** @var tinyint Show price for packaging flag */
	var $show_price_for_packaging  	= 0;
	/** @var tinyint Enable content plugins flag */
	var $enable_content_plugins    	= 0;
	/** @var tinyint Enable coupons flag */
	var $enable_coupons   		 	= 1;
	/** @var tinyint Enable reviews flag */
	var $enable_reviews    			= 0;
	/** @var tinyint Autopublish reviews flag */
	var $autopublish_reviews		= 0;
	/** @var int Autopublish reviews flag */
	var $reviews_minimum_comment_length			= 100;
	/** @var int Autopublish reviews flag */
	var $reviews_maximum_comment_length			= 2000;
	/** @var tinyint Virtual tax flag */
	var $virtual_tax				= 1;
	/** @var tinyint Tax mode id */
	var $tax_mode					= 0;
	/** @var tinyint Multiple tax rate flag */
	var $enable_multiple_taxrates	= 0;
	/** @var tinyint Subtract payment before discounts flag */
	var $subtract_payment_before_discount = 0;
	/** @var varchar Registration type */
	var $registration_type			= 'NORMAL_REGISTRATION';
	/** @var tinyint Subtract payment before discounts flag */
	var $show_remember_me_box		 = 0;
	/** @var tinyint Agree to terms of service on order flag */
	var $agree_tos_onorder			 = 0;
	/** @var tinyint Show legal informaiton on checkout flag */
	var $oncheckout_show_legal_info	 = 0;
	/** @var text OnCheckout legal information short text. */
	var $oncheckout_legalinfo_shorttext	 = '';
	/** @var tinyint Show products with no stock flag */
	var $show_out_of_stock_products	 = 0;
	/** @var tinyint Check cookie flag */
	var $enable_cookie_check		 = 1;
	/** @var tinyint Mail format index */
	var $mail_format				 = 0;
	/** @var tinyint Debug flag */
	var $debug						 = 0;
	/** @var tinyint Debug by ip address flag */
	var $debug_by_ip				 = 0;
	/** @var varchar Ip address to debug. */
	var $debug_ip_address			 = '';
	/** @var tinyint Logfile enabled flag */
	var $enable_logfile				 = 0;
	/** @var varchar Logfile name. */
	var $logfile_name				 = '';
	/** @var varchar Logfile level. */
	var $logfile_level				 = '';
	/** @var varchar Logfile format. */
	var $logfile_format				 = '';

	/**
	 * @author RickG
	 * @param $db A database connector object
	 */
	function __construct(&$db)
	{
		parent::__construct('#__vm_config', 'config_id', $db);
	}


	/**
	 * Validates the config record fields.
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
