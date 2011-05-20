<?php
/**
*
* Product table
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

if(!class_exists('VmTable'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmtable.php');

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * Product table class
 * The class is is used to manage the products in the shop.
 *
 * @package	VirtueMart
 * @author RolandD
 * @author Max Milbers
 */
class TableProducts extends VmTable {

	/** @var int Primary key */
	var $virtuemart_product_id	 = 0;
	/** @var integer Product id */
	var $virtuemart_vendor_id = 0;
	/** @var string File name */
	var $product_parent_id		= 0;
	/** @var string File title */
	var $product_sku= '';
    /** @var string Name of the product */
	var $product_name	= '';
	var $slug			= '';
    /** @var string File description */
	var $product_s_desc		= '';
    /** @var string File extension */
	var $product_desc			= '';
	/** @var int File is an image or other */
	var $product_weight			= 0;
	/** @var int File image height */
	var $product_weight_uom		= '';
	/** @var int File image width */
	var $product_length		= 0;
	/** @var int File thumbnail image height */
	var $product_width = 0;
	/** @var int File thumbnail image width */
	var $product_height	= 0;
	/** @var int File thumbnail image width */
	var $product_lwh_uom	= '';
	/** @var int File thumbnail image width */
	var $product_url	= '';
	/** @var int File thumbnail image width */
	var $product_in_stock	= 0;
	/** @var int File thumbnail image width */
	var $low_stock_notification	= 0;
	/** @var int File thumbnail image width */
	var $product_available_date	= null;
	/** @var int File thumbnail image width */
	var $product_availability	= null;
	/** @var int File thumbnail image width */
	var $product_special	= null;
//	/** @var int File thumbnail image width */
//	var $product_discount_id	= null;
	/** @var int File thumbnail image width */
	var $ship_code_id	= 0;

	/** @var int File thumbnail image width */
	var $product_sales	= 0;
//	/** @var int File thumbnail image width */
//	var $attribute	= null;
//	/** @var int File thumbnail image width */
//	var $custom_attribute	= 0;
//	/** @var int File thumbnail image width */
//	var $product_tax_id	= null;
	/** @var int File thumbnail image width */
	var $product_unit	= null;
	/** @var int File thumbnail image width */
	var $product_packaging	= null;
	/** @var int File thumbnail image width */
	var $product_order_levels	= '0,0';
	/** @var string Internal note for product */
	var $intnotes = '';
	/** @var string Meta description */
	var $metadesc	= '';
	/** @var string Meta keys */
	var $metakey	= '';
	/** @var string Meta robot */
	var $metarobot	= '';
	/** @var string Meta author */
	var $metaauthor	= '';
	/** @var string Name of the details page to use for showing product details in the front end */
	var $layout = '';
       /** @var int published or unpublished */
	var $published 		        = 1;



	/**
	 * @author RolandD
	 * @param $db A database connector object
	 */
	function __construct($db) {
		parent::__construct('#__virtuemart_products', 'virtuemart_product_id', $db);

		$this->setPrimaryKey('virtuemart_product_id');
		$this->setObligatoryKeys('product_name');
		$this->setLoggable();
		$this->setSlug('product_name');
	}

//    /**
//     * @author Max Milbers
//     * @param
//     */
//    function check() {
//
//        if (empty($this->virtuemart_vendor_id)) {
//            $this->setError('Serious error cant save product without vendor id');
//            return false;
//        }
//
//       	$date = JFactory::getDate();
//		$today = $date->toMySQL();
//		if(empty($this->created_on)){
//			$this->created_on = $today;
//		}
//     	$this->modified_on = $today;
//
//        return true;
//    }
}
// pure php no closing tag
