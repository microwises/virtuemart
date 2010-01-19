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

/**
 * Model for VirtueMart Products
 *
 * @package VirtueMart
 * @author RolandD
 */
class VirtueMartModelInventory extends JModel {

	var $_total;
	var $_pagination;

	function __construct() {
		parent::__construct();

		// Get the pagination request variables
		$mainframe = JFactory::getApplication() ;
		$limit = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$limitstart = $mainframe->getUserStateFromRequest( JRequest::getVar('option').'.limitstart', 'limitstart', 0, 'int' );

		// In case limit has been changed, adjust limitstart accordingly
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}

	/**
	 * Loads the pagination
	 */
    public function getPagination() {
		if ($this->_pagination == null) {
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}
		return $this->_pagination;
	}

	/**
	 * Gets the total number of products
	 */
	private function getTotal() {
    	if (empty($this->_total)) {
    		$db = JFactory::getDBO();
			$q = "SELECT COUNT(*) ".$this->getInventoryListQuery().$this->getInventoryFilter();
			$db->setQuery($q);
			$this->_total = $db->loadResult();
        }

        return $this->_total;
    }

    /**
     * Select the products to list on the product list page
     */
    public function getInventory() {
     	$db = JFactory::getDBO();
     	/* Pagination */
     	$this->getPagination();

     	/* Build the query */
     	$q = "SELECT #__vm_product.`product_id`,
     				#__vm_product.`product_parent_id`,
     				`product_name`,
     				`product_sku`,
     				`product_in_stock`,
     				`product_weight`,
     				`published`,
     				`product_price`
     				".$this->getInventoryListQuery().$this->getInventoryFilter();
     	$db->setQuery($q, $this->_pagination->limitstart, $this->_pagination->limit);
     	return $db->loadObjectList('product_id');
    }

    /**
    * List of tables to include for the product query
    * @author RolandD
    */
    private function getInventoryListQuery() {
    	return 'FROM #__vm_product
			LEFT JOIN #__vm_product_price
			ON #__vm_product.product_id = #__vm_product_price.product_id
			LEFT JOIN #__vm_shopper_group
			ON #__vm_product_price.shopper_group_id = #__vm_shopper_group.shopper_group_id';
    }

    /**
    * Collect the filters for the query
    * @author RolandD
    */
    private function getInventoryFilter() {
    	$db = JFactory::getDBO();
    	$filter_order = JRequest::getCmd('filter_order', 'product_name');
		if ($filter_order == '') $filter_order = 'product_name';
		$filter_order_Dir = JRequest::getWord('filter_order_Dir', 'desc');
		if ($filter_order_Dir == '') $filter_order_Dir = 'desc';

    	/* Check some filters */
     	$filters = array();
     	if (JRequest::getVar('filter_inventory', false)) $filters[] = '#__vm_product.`product_name` LIKE '.$db->Quote('%'.JRequest::getVar('filter_inventory').'%');
     	if (JRequest::getInt('stockfilter', 0) == 1) $filters[] = '#__vm_product.`product_in_stock` > 0';
     	if (JRequest::getInt('category_id', 0) > 0) $filters[] = '#__vm_category.`category_id` = '.JRequest::getInt('category_id');
     	$filters[] = '(#__vm_shopper_group.default = 1 OR #__vm_shopper_group.default is NULL)';

     	return ' WHERE '.implode(' AND ', $filters).' ORDER BY '.$filter_order." ".$filter_order_Dir;
    }
}
?>