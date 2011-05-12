<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage
* @author RolandD, Max Milbers
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
     	$q = "SELECT 	`virtuemart_product_review_id`,
     			#__virtuemart_products.`virtuemart_product_id`,
     			#__virtuemart_products.`product_parent_id`,
     			`product_name`,
     			`username`,
     			`comment`,
     			user_rating,
     			time,
     			#__virtuemart_product_reviews.userid,
			#__virtuemart_product_reviews.published
     			".$this->getRatingsListQuery().$this->getRatingsFilter();
     	$db->setQuery($q, $this->_pagination->limitstart, $this->_pagination->limit);
     	return $db->loadObjectList();
    }

    /**
    * List of tables to include for the product query
    * @author RolandD
    */
    private function getRatingsListQuery() {
    	return 'FROM #__virtuemart_product_reviews
			LEFT JOIN #__virtuemart_products
			ON #__virtuemart_product_reviews.virtuemart_product_id = #__virtuemart_products.virtuemart_product_id
			LEFT JOIN #__users
			ON #__virtuemart_product_reviews.userid = #__users.id';
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
     	if (JRequest::getVar('filter_ratings', false)) $filters[] = '(#__virtuemart_products.`product_name` LIKE '.$db->Quote('%'.JRequest::getVar('filter_ratings').'%').' OR #__virtuemart_product_reviews.comment LIKE '.$db->Quote('%'.JRequest::getVar('filter_ratings').'%').')';

     	if (count($filters) > 0) $filter = ' WHERE '.implode(' AND ', $filters);
     	else $filter = '';

     	return $filter.' ORDER BY '.$filter_order." ".$filter_order_Dir;
    }

    /**
    * Load a single rating
    * @author RolandD
    */
    public function getRating() {
		/* Get the review IDs to retrieve (input variable may be cid, cid[] or virtuemart_product_review_id */
		$cids = array();
		$cids = JRequest::getVar('cid', false);
		if (empty($cids)) {
			$cids= JRequest::getVar('virtuemart_product_review_id',false);
		}
		if ($cids && !is_array($cids)) $cids = array($cids);

		/* First copy the product in the product table */
		$ratings_data = $this->getTable('ratings');

		/* Load the rating */
		if ($cids) $ratings_data->load($cids[0]);

		/* Add some variables for a new rating */
		if (JRequest::getVar('task') == 'add') {
			/* Product ID */
			$ratings_data->virtuemart_product_id = JRequest::getInt('virtuemart_product_id');

			/* User ID */
			$user = JFactory::getUser();
			$ratings_data->userid = $user->id;
		}

		/* Get the product name */
		$db = JFactory::getDBO();
		$q = "SELECT product_name FROM #__virtuemart_products WHERE virtuemart_product_id = ".$ratings_data->virtuemart_product_id;
		$db->setQuery($q);
		$ratings_data->product_name = $db->loadResult();

		return $ratings_data;
    }

    /**
    * Set the publish/unpublish state
    */
    public function setPublish() {
     $cid = JRequest::getVar('cid', false);
     	if (is_array($cid)) {
     		$db = JFactory::getDBO();
     		$cids = implode( ',', $cid );
	} else {
		$cids = $cid;
	}

	if (JRequest::getVar('task') == 'publish') $state =  '1'; else $state = '0';
	$q = "UPDATE #__virtuemart_product_reviews
		SET published = ".$db->Quote($state)."
		WHERE virtuemart_product_review_id IN (".$cids.")";
	$db->setQuery($q);
	if ($db->query()) return true;
	else return false;
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
    * @author  Max Milbers
    */
    public function saveRating() {

		$ratings_data = $this->getTable('ratings');

		/* Get the posted data */
		$data = JRequest::get('post');

		/* Check if we have a timestamp */
		/* if ($data['time'] == 0) $data['time'] = time(); */
		/* Timestamps are always kept up to the latest modification */
		$data['time'] = time();
		$user =& JFactory::getUser();
		if(empty($user->id)) {
			$this->setError(JText::_('COM_VIRTUEMART_REVIEW_LOGIN'));
			return false;
		}
		//Check user_rating  
		$maxrating = VmConfig::get('vm_maximum_rating_scale',5);
		if ($data['user_rating'] < 0 ) $data['user_rating'] = 0 ;
		if ($data['user_rating'] > $maxrating ) $data['user_rating'] = $maxrating ;
		if ( !$data['virtuemart_product_review_id'] )  $data['userid'] = $user->id;
		$data['comment'] = substr($data['comment'], 0, VmConfig::get('vm_reviews_maximum_comment_length', 2000)) ;
		//set to defaut value not used (prevent hack)
		$data['review_ok'] = 0 ; 
		$data['review_votes'] = 0 ;
		

		/* Check if ratings are auto-published (set to 0 prevent injected by user)*/
		if (VmConfig::get('reviews_autopublish',0)) $data['published'] = 1;
		else $data['published'] = 0;

    	// Bind the form fields to the table
		if (!$ratings_data->bind($data)) {
			$this->setError($ratings_data->getError());
			return false;
		}

		// Make sure the record is valid
		if (!$ratings_data->check()) {
			$this->setError($ratings_data->getError());
			return false;
		}

		// Save the record to the database
		if (!$ratings_data->store()) {
			$this->setError($ratings_data->getError());
			return false;
		}

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
			FROM #__virtuemart_product_reviews
			WHERE virtuemart_product_id=".$pid;
		$db->setQuery($q);
		$reviews = $db->loadResult();
		return $reviews;
	}
}
// pure php no closing tag
