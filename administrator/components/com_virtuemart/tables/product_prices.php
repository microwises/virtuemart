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
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

if(!class_exists('VmTableData'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmtabledata.php');

/**
 * Product table class
 * The class is is used to manage the products in the shop.
 *
 * @package	VirtueMart
 * @author RolandD
 * @author Max Milbers
 */
class TableProduct_prices extends VmTableData {

    /** @var int Primary key */
    var $virtuemart_product_price_id = 0;
    /** @var int Product id */
    var $virtuemart_product_id = 0;
    /** @var int Shopper group ID */
    var $virtuemart_shoppergroup_id = null;

    /** @var string Product price */
    var $product_price = null;
    var $override = 0;
    var $product_override_price = 0;
    var $product_tax_id = 0;
    var $product_discount_id = 0;

    /** @var string Product currency */
    var $product_currency = 0;

    var $product_price_vdate = null;
    var $product_price_edate = null;

    /** @var int Price quantity start */
    var $price_quantity_start = null;
    /** @var int Price quantity end */
    var $price_quantity_end = null;

    /**
     * @author RolandD
     * @param $db A database connector object
     */
    function __construct(&$db) {
        parent::__construct('#__virtuemart_product_prices', 'virtuemart_product_price_id', $db);

//       $this->setPrimaryKey('virtuemart_product_id');
		$this->setLoggable();
		$this->setTableShortCut('pp');
    }

    /**
     * @author Max Milbers
     * @param
     */

	function check(){

		if(!empty($this->product_price)){
			$this->product_price = str_replace(array(',',' '),array('.',''),$this->product_price);
		} else {
			$this->product_price = null;
		}



		return parent::check();
	}

}

// pure php no closing tag
