<?php
/**
*
* Description
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

// Load the model framework
jimport( 'joomla.application.component.model');

if(!class_exists('VmModel'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmmodel.php');

/**
 * Model for VirtueMart Products
 *
 * @package VirtueMart
 * @author RolandD
 */
class VirtueMartModelInventory extends VmModel {

	/**
	 * constructs a VmModel
	 * setMainTable defines the maintable of the model
	 * @author Max Milbers
	 */
	function __construct() {
		parent::__construct('virtuemart_product_id');
		$this->setMainTable('products');
	}


	/**
	 * Gets the total number of products
	 */
	// function getTotal() {
    	// if (empty($this->_total)) {
    		// $db = JFactory::getDBO();
			// $q = "SELECT COUNT(*) ".$this->getInventoryListQuery().$this->getInventoryFilter();
			// $db->setQuery($q);
			// $this->_total = $db->loadResult();
        // }

        // return $this->_total;
    // }

    /**
     * Select the products to list on the product list page
     */
    public function getInventory() {

     	/* Build the query */
     	$q = "SELECT `#__virtuemart_products`.`virtuemart_product_id`,
     				`#__virtuemart_products`.`product_parent_id`,
     				`product_name`,
     				`product_sku`,
     				`product_in_stock`,
     				`product_weight`,
     				`published`,
     				`product_price`
     				".$this->getInventoryListQuery().$this->getInventoryFilter();
     	$this->_data = $this->_getList($q, $this->getState('limitstart'), $this->getState('limit'));
		$this->_total = $this->_getListCount($this->_query) ;
		return $this->data ;
    }

    /**
    * List of tables to include for the product query
    * @author RolandD
    */
    private function getInventoryListQuery() {
    	return 'FROM `#__virtuemart_products`
			LEFT JOIN `#__virtuemart_product_prices`
			ON `#__virtuemart_products`.`virtuemart_product_id` = `#__virtuemart_product_prices`.`virtuemart_product_id`
			LEFT JOIN `#__virtuemart_shoppergroups`
			ON `#__virtuemart_product_prices`.`virtuemart_shoppergroup_id` = `#__virtuemart_shoppergroups`.`virtuemart_shoppergroup_id`';
    }

    /**
    * Collect the filters for the query
    * @author RolandD
    */
    private function getInventoryFilter() {
    	/* Check some filters */
     	$filters = array();
     	if (JRequest::getVar('filter_inventory', false)) $filters[] = '`#__virtuemart_products`.`product_name` LIKE '.$this->_db->Quote('%'.JRequest::getVar('filter_inventory').'%');
     	if (JRequest::getInt('stockfilter', 0) == 1) $filters[] = '`#__virtuemart_products`.`product_in_stock` > 0';
     	if (JRequest::getInt('virtuemart_category_id', 0) > 0) $filters[] = '`#__virtuemart_categories`.`virtuemart_category_id` = '.JRequest::getInt('virtuemart_category_id');
     	$filters[] = '(`#__virtuemart_shoppergroups`.`default` = 1 OR `#__virtuemart_shoppergroups`.`default` is NULL)';

     	return ' WHERE '.implode(' AND ', $filters).$this->_getOrdering('product_name');
    }
}
// pure php no closing tag