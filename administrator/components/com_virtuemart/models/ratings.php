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

if(!class_exists('VmModel'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmmodel.php');

/**
 * Model for VirtueMart Products
 *
 * @package VirtueMart
 * @author RolandD
 */
class VirtueMartModelRatings extends VmModel {

	var $_productBought = 0;

	/**
	 * constructs a VmModel
	 * setMainTable defines the maintable of the model
	 * @author Max Milbers
	 */
	function __construct() {
		parent::__construct();
		$this->setMainTable('product_ratings');

	}

    /**
     * Select the products to list on the product list page
     */
    public function getRatings() {
     	$db = JFactory::getDBO();
     	/* Pagination */
     	$this->getPagination();

//     	$q = 'SELECT * FROM `#__virtuemart_product_ratings`  ORDER BY `modified_on`';
     	$q = 'SELECT p.*,pr.* FROM `#__virtuemart_product_ratings` AS `pr` JOIN `#__virtuemart_products` AS `p`
     			ON `pr`.`virtuemart_product_id` = `p`.`virtuemart_product_id` ORDER BY `pr`.`modified_on` ';

//     	/* Build the query */
//     	$q = "SELECT 	`virtuemart_product_review_id`,
//     			#__virtuemart_products.`virtuemart_product_id`,
//     			#__virtuemart_products.`product_parent_id`,
//     			`product_name`,
//     			`username`,
//     			`comment`,
//     			user_rating,
//     			created_on,
//     			#__virtuemart_product_reviews.userid,
//			#__virtuemart_product_reviews.published
//     			".$this->getRatingsListQuery().$this->getRatingsFilter();
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
			ON #__virtuemart_product_reviews.virtuemart_user_id = #__users.id';
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
		$ratings_data = $this->getTable('product_ratings');

		/* Load the rating */
		if ($cids) $ratings_data->load($cids[0]);

		/* Add some variables for a new rating */
		if (JRequest::getVar('task') == 'add') {
			/* Product ID */
			$ratings_data->virtuemart_product_id = JRequest::getInt('virtuemart_product_id');

			/* User ID */
			$user = JFactory::getUser();
			$ratings_data->virtuemart_user_id = $user->id;
		}

		/* Get the product name */
		$db = JFactory::getDBO();
		$q = "SELECT product_name FROM #__virtuemart_products WHERE virtuemart_product_id = ".$ratings_data->virtuemart_product_id;
		$db->setQuery($q);
		$ratings_data->product_name = $db->loadResult();

		return $ratings_data;
    }


    function getReviews(){

    	$db = JFactory::getDBO();
     	/* Pagination */
     	$this->getPagination();

       	$q = 'SELECT vm.*,pr.* FROM `#__virtuemart_product_reviews` AS `pr` LEFT JOIN `#__virtuemart_userinfos` AS `vm`
     	ON `pr`.`virtuemart_user_id` = `p`.`virtuemart_user_id` ORDER BY `pr`.`modified_on` ';

     	$db->setQuery($q, $this->_pagination->limitstart, $this->_pagination->limit);
     	return $db->loadObjectList();
    }


    /**
    * Save a rating
    * @author  Max Milbers
    */
    public function saveRating($data) {


		//Check user_rating
		$maxrating = VmConfig::get('vm_maximum_rating_scale',5);
		if (empty($data['rate']) || $data['rate'] < 0 ) $data['rate'] = 0 ;
		if ($data['rate'] > $maxrating ) $data['rate'] = $maxrating ;


		$rating = $this->getTable('product_ratings');
		$rating->load($data['virtuemart_product_id']);

		if(!empty($rating->rates)){
			$data['rates'] = $rating->rates + $data['rate'];
		} else {
			$data['rates'] = $data['rate'];
		}

		if(!empty($rating->ratingcount)){
			$data['ratingcount'] = $rating->ratingcount+1;	// ++ does NOT work !
		} else {
			$data['ratingcount'] = 1;
		}

		$data['rating'] = $data['rates']/$data['ratingcount'];

		if(empty($rating->virtuemart_user_id)){
			$user = JFactory::getUser();
			$data['virtuemart_user_id'] = $user->id;
		}

        if($data = $rating->bindChecknStore($data)){

		} else {
			$app = JFactory::getApplication();
			$app->enqueueMessage($table->getError());
		}

		if(!empty($data['comment'])){
			$data['comment'] = substr($data['comment'], 0, VmConfig::get('vm_reviews_maximum_comment_length', 2000)) ;
			//set to defaut value not used (prevent hack)
			$data['review_ok'] = 0;
			$data['review_votes'] = 0;

			/* Check if ratings are auto-published (set to 0 prevent injected by user)*/
			if (VmConfig::get('reviews_autopublish',0)) $data['published'] = 1;

	    	$review = $this->getTable('product_reviews');
			$review->load($data['virtuemart_product_id']);

			if(!empty($review->review_rates)){
				$data['review_rates'] = $review->review_rates + $data['review_rate'];
			} else {
				$data['review_rates'] = $data['rate'];
			}

			if(!empty($review->review_ratingcount)){
				$data['review_ratingcount'] = $review->review_ratingcount+1;	// ++ does NOT work !
			} else {
				$data['review_ratingcount'] = 1;
			}

			$data['review_rating'] = $data['review_rates']/$data['review_ratingcount'];

			if(empty($review->virtuemart_user_id)){
				$user = JFactory::getUser();
				$data['virtuemart_user_id'] = $user->id;
			}

	        if($data = $review->bindChecknStore($data)){

			} else {
				$app = JFactory::getApplication();
				$app->enqueueMessage($review->getError());
			}
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

	public function showReview($product_id){

		return $this->show($product_id, VmConfig::get('showReviewFor',2));
	}

	public function showRating($product_id){

		return $this->show($product_id, VmConfig::get('showRatingFor',2));
	}

	public function allowReview($product_id){
		return $this->show($product_id, VmConfig::get('reviewMode',2));
	}

	public function allowRating($product_id){
		return $this->show($product_id, VmConfig::get('reviewMode',2));
	}

	private function show($product_id, $show){

		//dont show
		if($show == 0){
			return false;
		}
		//show all
		else if ($show == 3){
			return true;
		}
		//show only registered
		else if ($show == 2){
			$user = JFactory::getUser();
			return !empty($user->id);
		}
		//show only registered && who bought the product
		else if ($show == 1){
			if(!empty($this->_productBought)) return true;

			$user = JFactory::getUser();
			if(empty($product_id)) return false;

			$db = JFactory::getDBO();
			$q = 'SELECT COUNT(*) as total FROM `#__virtuemart_orders` AS o LEFT JOIN `#__virtuemart_order_items` AS oi ';
			$q .= 'ON `o`.`virtuemart_order_id` = `oi`.`virtuemart_order_id` ';
			$q .= 'WHERE o.virtuemart_user_id = "'.$user->id.'" AND oi.virtuemart_product_id = "'.$product_id.'" ';

			$db->setQuery($q);
			$count = $db->loadResult();
			if($count){
				$this->_productBought = true;
				return true;
			} else {
				return false;
			}
		}
	}
}
// pure php no closing tag
