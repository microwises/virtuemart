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

/**
 * Product table class
 * The class is is used to manage the products in the shop.
 *
 * @package	VirtueMart
 * @author RolandD
 * @author Max Milbers
 */
class TableProduct_price extends JTable {

    /** @var int Primary key */
    var $product_price_id = 0;
    /** @var int Product id */
    var $product_id = 0;
    /** @var string Product price */
    var $product_price = null;
    var $override = 0;
    var $product_override_price = 0;
    var $product_tax_id = 0;
    var $product_discount_id = 0;
    /** @var string Product currency */
    var $product_currency = null;

    var $product_price_vdate = null;
    
    var $product_price_edate = null;
    /** @var string Creation date */
    var $cdate = null;
    /** @var string Modified date */
    var $mdate = null;
    /** @var int Shopper group ID */
    var $shopper_group_id = null;
    /** @var int Price quantity start */
    var $price_quantity_start = null;
    /** @var int Price quantity end */
    var $price_quantity_end = null;

    /**
     * @author RolandD
     * @param $db A database connector object
     */
    function __construct(&$db) {
        parent::__construct('#__vm_product_price', 'product_price_id', $db);
    }

    /**
     * @author Max Milbers
     * @param
     */
    function check() {

        if (!$this->product_id) {
            $this->setError(JText::_('VM_IMPOSSIBLE_TO_SAVE_PRODUCT_PRICES_WITHOUT_PRODUCT_ID'));
            return false;
        }

//		if (!$this->product_price) {
//			$this->setError(JText::_('VM_IMPOSSIBLE_TO_SAVE_PRODUCT_PRICES_WITHOUT_PRODUCT_PRICE'));
//			return false;
//		}
//		if (!$this->product_currency) {
//			$this->setError(JText::_('VM_IMPOSSIBLE_TO_SAVE_PRODUCT_PRICES_WITHOUT_PRODUCT_CURRENCY'));
//			return false;
//		}
        return true;
    }

    /**
     * Records in this table do not need to exist, so we might need to create a record even
     * if the primary key is set. Therefore we need to overload the store() function.
     *
     * @author Oscar van Eijk
     * @author Max Milbers
     * @see libraries/joomla/database/JTable#store($updateNulls)
     */
    public function store() {
        $_qry = 'SELECT product_id '
                . 'FROM #__vm_product_price '
                . 'WHERE product_price_id = ' . $this->product_price_id
        ;
        $this->_db->setQuery($_qry);
        $_count = $this->_db->loadResultArray();

        if (count($_count) > 0) {
            $returnCode = $this->_db->updateObject($this->_tbl, $this, $this->_tbl_key, false);
        } else {
            $returnCode = $this->_db->insertObject($this->_tbl, $this, $this->_tbl_key);
        }

        if (!$returnCode) {
            $this->setError(get_class($this) . '::store failed - ' . $this->_db->getErrorMsg());
            return false;
        }
        else
            return true;
    }

}

// pure php no closing tag
