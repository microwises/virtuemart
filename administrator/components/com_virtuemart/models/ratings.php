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
class VirtueMartModelRatings extends JModel {

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
			$q = "SELECT COUNT(*) ".$this->getRatingsListQuery().$this->getRatingsFilter();
			$db->setQuery($q);
			$this->_total = $db->loadResult();
        }

        return $this->_total;
    }

    /**
     * Select the products to list on the product list page
     */
    public function getRatings() {
     	$db = JFactory::getDBO();
     	/* Pagination */
     	$this->getPagination();

     	/* Build the query */
     	$q = "SELECT `review_id`,
     				#__vm_product.`product_id`,
     				#__vm_product.`product_parent_id`,
     				`product_name`,
     				`username`,
     				`comment`,
     				user_rating,
     				time,
     				IF (`published` = 'Y', 1, 0) AS `published`
     				".$this->getRatingsListQuery().$this->getRatingsFilter();
     	$db->setQuery($q, $this->_pagination->limitstart, $this->_pagination->limit);
     	return $db->loadObjectList('product_id');
    }

    /**
    * List of tables to include for the product query
    * @author RolandD
    */
    private function getRatingsListQuery() {
    	return 'FROM #__vm_product_reviews
			LEFT JOIN #__vm_product
			ON #__vm_product_reviews.product_id = #__vm_product.product_id
			LEFT JOIN #__users
			ON #__vm_product_reviews.userid = #__users.id';
    }

    /**
    * Collect the filters for the query
    * @author RolandD
    */
    private function getRatingsFilter() {
    	$db = JFactory::getDBO();
    	$filter_order = JRequest::getCmd('filter_order', 'product_name');
		if ($filter_order == '') $filter_order = 'product_name';
		$filter_order_Dir = JRequest::getWord('filter_order_Dir', 'desc');
		if ($filter_order_Dir == '') $filter_order_Dir = 'desc';

    	/* Check some filters */
     	$filters = array();
     	if (JRequest::getVar('filter_ratings', false)) $filters[] = '(#__vm_product.`product_name` LIKE '.$db->Quote('%'.JRequest::getVar('filter_ratings').'%').' OR #__vm_product_reviews.comment LIKE '.$db->Quote('%'.JRequest::getVar('filter_ratings').'%').')';

     	if (count($filters) > 0) $filter = ' WHERE '.implode(' AND ', $filters);
     	else $filter = '';

     	return $filter.' ORDER BY '.$filter_order." ".$filter_order_Dir;
    }

    /**
    * Load a single rating
    * @author RolandD
    */
    public function getRating() {
		/* Get the product IDs to remove */
		$cids = array();
		$cids = JRequest::getVar('cid', false);
		if ($cids && !is_array($cids)) $cids = array($cids);

		/* First copy the product in the product table */
		$ratings_data = $this->getTable('ratings');

		/* Load the rating */
		if ($cids) $ratings_data->load($cids[0]);

		/* Add some variables for a new rating */
		if (JRequest::getVar('task') == 'add') {
			/* Product ID */
			$ratings_data->product_id = JRequest::getInt('product_id');

			/* User ID */
			$user = JFactory::getUser();
			$ratings_data->userid = $user->id;
		}

		/* Get the product name */
		$db = JFactory::getDBO();
		$q = "SELECT product_name FROM #__vm_product WHERE product_id = ".$ratings_data->product_id;
		$db->setQuery($q);
		$ratings_data->product_name = $db->loadResult();

		/* Fix the published setting */
		$ratings_data->published = ($ratings_data->published == 'Y') ? 1 : 0;

		return $ratings_data;
    }

    /**
    * Set the publish/unpublish state
    */
    public function getPublish() {
     	$cid = JRequest::getVar('cid', false);
     	if (is_array($cid)) {
     		$db = JFactory::getDBO();
     		$cids = implode( ',', $cid );
			if (JRequest::getVar('task') == 'publish') $state =  '1'; else $state = '0';
			$q = "UPDATE #__vm_product_reviews
				SET published = ".$db->Quote($state)."
				WHERE review_id IN (".$cids.")";
			$db->setQuery($q);
			if ($db->query()) return true;
			else return false;
		}
    }

    /**
    * Delete a rating
    * @author RolandD
    */
    public function removeRating() {
		/* Get the product IDs to remove */
		$cids = array();
		$cids = JRequest::getVar('cid');
		if (!is_array($cids)) $cids = array($cids);

		/* Start removing */
		foreach ($cids as $key => $ratings_id) {
			/* First copy the product in the product table */
			$ratings_data = $this->getTable('ratings');

			/* Load the product details */
			$ratings_data->delete($ratings_id);
		}
		return true;
    }

    /**
    * Save a rating
    * @author RolandD
    */
    public function saveRating() {
		/* Get the product IDs to remove */
		$cids = array();
		$cids = JRequest::getVar('cid');
		if (!is_array($cids)) $cids = array($cids);

		/* First copy the product in the product table */
		$ratings_data = $this->getTable('ratings');

		/* Get the posted data */
		$data = JRequest::get('post', 4);

		/* Check if we have a timestamp */
		if ($data['time'] == 0) $data['time'] = time();

		/* Bind the rating details */
		$ratings_data->bind($data);

		/* Check if ratings are auto-published */
		if (VmConfig::get('autopublish_reviews') == 1) $data['published'] = 1;

		/* Fix the published field */
		$ratings_data->published = ($data['published'] == 1) ? 'Y' : 'N';

		/* Store the rating */
		$ratings_data->store();

		return true;
    }

    /**
	* Returns the number of reviews assigned to a product
	*
	* @author RolandD
	* @param int $pid Product ID
	* @return int
	*/
	public function countReviewsForProduct($pid) {
		$db = JFactory::getDBO();
		$q = "SELECT COUNT(*) AS total
			FROM #__vm_product_reviews
			WHERE product_id=".$pid;
		$db->setQuery($q);
		$reviews = $db->loadResult();
		return $reviews;
	}
}
?>