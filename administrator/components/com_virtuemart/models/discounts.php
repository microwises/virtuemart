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
 * Model for VirtueMart Discounts
 *
 * @package VirtueMart
 * @author RolandD
 */
class VirtueMartModelDiscounts extends JModel {

	var $_total;
	var $_pagination;

	function __construct() {
		parent::__construct();

		// Get the pagination request variables
		$mainframe = JFactory::getApplication() ;
		$limit = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$limitstart = $mainframe->getUserStateFromRequest( JRequest::getVar('option').JRequest::getVar('view').'.limitstart', 'limitstart', 0, 'int' );

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
			$q = "SELECT COUNT(*) ".$this->getDiscountsListQuery().$this->getDiscountsFilter();
			$db->setQuery($q);
			$this->_total = $db->loadResult();
        }

        return $this->_total;
    }

    /**
     * Select the products to list on the product list page
     */
    public function getDiscounts() {
     	$db = JFactory::getDBO();
     	/* Pagination */
     	$this->getPagination();

     	/* Build the query */
     	$q = "SELECT * ".$this->getDiscountsListQuery().$this->getDiscountsFilter();
     	$db->setQuery($q, $this->_pagination->limitstart, $this->_pagination->limit);
     	return $db->loadObjectList('discount_id');
    }

    /**
    * List of tables to include for the product query
    * @author RolandD
    */
    private function getDiscountsListQuery() {
    	return 'FROM `#__vm_product_discount`';
    }

    /**
    * Collect the filters for the query
    * @author RolandD
    */
    private function getDiscountsFilter() {
    	$db = JFactory::getDBO();
    	$filter_order = JRequest::getCmd('filter_order', 'amount');
		if ($filter_order == '') $filter_order = 'amount';
		$filter_order_Dir = JRequest::getWord('filter_order_Dir', 'desc');
		if ($filter_order_Dir == '') $filter_order_Dir = 'desc';

    	/* Check some filters */
     	$filters = array();
     	if (JRequest::getVar('filter_discounts', false)) $filters[] = '#__vm_product_discount.`amount` LIKE '.$db->Quote('%'.JRequest::getVar('filter_discounts').'%');

     	if (count($filters) > 0) $filter = ' WHERE '.implode(' AND ', $filters);
     	else $filter = '';

     	return $filter.' ORDER BY '.$filter_order." ".$filter_order_Dir;
    }

    /**
    * Load a single discount
    * @author RolandD
    */
    public function getDiscount() {
		/* Get the product IDs to remove */
		$cids = array();
		$cids = JRequest::getVar('cid', false);
		if ($cids && !is_array($cids)) $cids = array($cids);

		/* First copy the product in the product table */
		$discounts_data = $this->getTable('discounts');

		/* Load the rating */
		if ($cids) $discounts_data->load($cids[0]);

		return $discounts_data;
    }

    /**
    * Delete a discount
    * @author RolandD
    */
    public function removeDiscount() {
		/* Get the product IDs to remove */
		$cids = array();
		$cids = JRequest::getVar('cid');
		if (!is_array($cids)) $cids = array($cids);

		/* Start removing */
		foreach ($cids as $key => $discounts_id) {
			/* First copy the product in the product table */
			$discounts_data = $this->getTable('discounts');

			/* Load the product details */
			$discounts_data->delete($discounts_id);
		}
		return true;
    }

    /**
    * Save a discount
    * @author RolandD
    */
    public function saveDiscount() {
		/* Get the product IDs to remove */
		$cids = array();
		$cids = JRequest::getVar('cid');
		if (!is_array($cids)) $cids = array($cids);

		/* First copy the product in the product table */
		$discounts_data = $this->getTable('discounts');

		/* Get the posted data */
		$data = JRequest::get('post', 4);

		/* Fix the dates */
		jimport('joomla.utilities.date');
		$date = new JDate($data['start_date']);
		$data['start_date'] = $date->toUnix();
		$date = new JDate($data['end_date']);
		$data['end_date'] = $date->toUnix();

		/* Bind the rating details */
		$discounts_data->bind($data);

		/* Store the rating */
		$discounts_data->store();

		return true;
    }
}
// pure php no closing tag