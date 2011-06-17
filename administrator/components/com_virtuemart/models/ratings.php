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
		$this->setMainTable('ratings');

	}

    /**
     * Select the products to list on the product list page
     */
    public function getRatings() {

     	$q = 'SELECT p.*,pr.* FROM `#__virtuemart_ratings` AS `pr` JOIN `#__virtuemart_products` AS `p`
     			ON `pr`.`virtuemart_product_id` = `p`.`virtuemart_product_id` ORDER BY `pr`.`modified_on` ';
	    $this->_data = $this->_getList($q, $this->getState('limitstart'), $this->getState('limit'));
		// set total for pagination
		$this->_total = $this->_getListCount($q) ;
     	return $this->_data;
    }

    /**
    * List of tables to include for the product query
    * @author RolandD
    */
    private function getRatingsListQuery() {
    	return 'FROM #__virtuemart_rating_reviews
			LEFT JOIN #__virtuemart_products
			ON #__virtuemart_rating_reviews.virtuemart_product_id = #__virtuemart_products.virtuemart_product_id
			LEFT JOIN #__users
			ON #__virtuemart_rating_reviews.virtuemart_user_id = #__users.id';
    }

    /**
    * Collect the filters for the query
    * @author RolandD
    */
    private function getRatingsFilter() {


    	/* Check some filters */
     	$filters = array();
     	if (JRequest::getVar('filter_ratings', false)) $filters[] = '(#__virtuemart_products.`product_name` LIKE '.$this->_db->Quote('%'.JRequest::getVar('filter_ratings').'%').' OR #__virtuemart_rating_reviews.comment LIKE '.$this->_db->Quote('%'.JRequest::getVar('filter_ratings').'%').')';

     	if (count($filters) > 0) $filter = ' WHERE '.implode(' AND ', $filters);
     	else $filter = '';

     	return $filter.$query .= $this->_getOrdering('product_name');
    }

    /**
    * Load a single rating
    * @author RolandD
    */
    public function getRating($cids) {
		
		if(empty($cids)) return;
		
		/* First copy the product in the product table */
		$ratings_data = $this->getTable('ratings');

		/* Load the rating */
		if ($cids) $ratings_data->load($cids[0]);

		/* Add some variables for a new rating */
		if (JRequest::getWord('task') == 'add') {
			/* Product ID */
			$ratings_data->virtuemart_product_id = JRequest::getInt('virtuemart_product_id',0);

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


    function getReviews($virtuemart_product_id){

    	if(empty($virtuemart_product_id)) return null;
     	/* Pagination */
     	$this->getPagination();

       	$q = 'SELECT `u`.*,`pr`.*,`p`.`product_name`,`rv`.`vote`, `u`.`name` AS customer FROM `#__virtuemart_rating_reviews` AS `pr`
		LEFT JOIN `#__users` AS `u`	ON `pr`.`created_by` = `u`.`id`
		LEFT JOIN `#__virtuemart_products` AS `p` ON `p`.`virtuemart_product_id` = `pr`.`virtuemart_product_id`
		LEFT JOIN `#__virtuemart_rating_votes` AS `rv` on `rv`.`virtuemart_product_id`=`pr`.`virtuemart_product_id` and `rv`.`created_by`=`u`.`id`
		WHERE  `p`.`virtuemart_product_id` = "'.$virtuemart_product_id.'"
		ORDER BY `pr`.`modified_on` ';
     	$this->_db->setQuery($q, $this->_pagination->limitstart, $this->_pagination->limit);

     	if(!$result = $this->_db->loadObjectList()){
     		$this->setError($this->_db->getErrorMsg());
     	}

     	return $result;
    }

	function getReview($cids){
	/*	$cids = array();
		$cids = JRequest::getVar('cid', false);
		if (empty($cids)) {
			$cids= JRequest::getVar('virtuemart_rating_review_id',false);
		}
		if ($cids && !is_array($cids)) $cids = array($cids);
*/
       	$q = 'SELECT `u`.*,`pr`.*,`p`.`product_name`,`rv`.`vote`,CONCAT_WS(" ",`u`.`title`,u.`last_name`,`u`.`first_name`) as customer FROM `#__virtuemart_rating_reviews` AS `pr`
		LEFT JOIN `#__virtuemart_userinfos` AS `u`
     	ON `pr`.`created_by` = `u`.`virtuemart_user_id`
		LEFT JOIN `#__virtuemart_products` AS `p`
     	ON `p`.`virtuemart_product_id` = `pr`.`virtuemart_product_id` and  virtuemart_rating_review_id='.(int)$cids[0].'
		LEFT JOIN `#__virtuemart_rating_votes` as `rv` on `rv`.`virtuemart_product_id`=`pr`.`virtuemart_product_id` and `rv`.`created_by`=`u`.`virtuemart_user_id`' ;
		$this->_db->setQuery($q);

		return $this->_db->loadObject();
    }


    /**
     * gets a rating by a product id
     *
     * @author Max Milbers
     * @param int $product_id
     */

    function getRatingByProduct($product_id){
    	$q = 'SELECT * FROM `#__virtuemart_ratings` WHERE `virtuemart_product_id` = "'.(int)$product_id.'" ';
		$this->_db->setQuery($q);
		return $this->_db->loadObject();

    }

    /**
     * gets a review by a product id
     *
     * @author Max Milbers
     * @param int $product_id
     */

    function getReviewByProduct($product_id,$userId=0){
   		if(empty($userId)){
			$user = JFactory::getUser();
			$userId = $user->id;
    	}
		$q = 'SELECT * FROM `#__virtuemart_rating_reviews` WHERE `virtuemart_product_id` = "'.(int)$product_id.'" AND `created_by` = "'.(int)$userId.'" ';
		$this->_db->setQuery($q);
		return $this->_db->loadObject();
    }

    /**
     * gets a reviews by a product id
     *
     * @author Max Milbers
     * @param int $product_id
     */

	function getReviewsByProduct($product_id){
   		if(empty($userId)){
			$user = JFactory::getUser();
			$userId = $user->id;
    	}
		$q = 'SELECT * FROM `#__virtuemart_rating_reviews` WHERE `virtuemart_product_id` = "'.(int)$product_id.'" ';
		$this->_db->setQuery($q);
		return $this->_db->loadObjectList();
    }

    /**
     * gets a vote by a product id and userId
     *
     * @author Max Milbers
     * @param int $product_id
     */

    function getVoteByProduct($product_id,$userId=0){

    	if(empty($userId)){
			$user = JFactory::getUser();
			$userId = $user->id;
    	}
		$q = 'SELECT * FROM `#__virtuemart_rating_votes` WHERE `virtuemart_product_id` = "'.(int)$product_id.'" AND `created_by` = "'.(int)$userId.'" ';
		$this->_db->setQuery($q);
		return $this->_db->loadObject();

    }

    /**
    * Save a rating
    * @author  Max Milbers
    */
    public function saveRating($data) {

		//Check user_rating
		$maxrating = VmConfig::get('vm_maximum_rating_scale',5);
		$user = JFactory::getUser();
		$userId = $user->id;
		if ( !empty($data['virtuemart_product_id']) && !empty($userId)){

			//normalize the rating
			if ($data['vote'] < 0 ) $data['vote'] = 0 ;
			if ($data['vote'] > ($maxrating+1) ) $data['vote'] = $maxrating;

			$data['lastip'] = $_SERVER['REMOTE_ADDR'];

			$rating = $this->getRatingByProduct($data['virtuemart_product_id']);

			$vote = $this->getVoteByProduct($data['virtuemart_product_id'],$userId);

			$data['virtuemart_rating_vote_id'] = empty($vote->virtuemart_rating_vote_id)? 0: $vote->virtuemart_rating_vote_id;

			if(isset($data['vote'])){
				$votesTable = $this->getTable('rating_votes');
		        $data = $votesTable->bindChecknStore($data);
		    	$errors = $votesTable->getErrors();
				foreach($errors as $error){
					$this->setError(get_class( $this ).'::Error store votes '.$error);
				}
			}


			if(!empty($rating->rates) && empty($vote) ){
				$data['rates'] = $rating->rates + $data['vote'];
				$data['ratingcount'] = $rating->ratingcount+1;
			} else if(!empty($rating->rates) && !empty($vote->vote)){
				$data['rates'] = $rating->rates - $vote->vote + $data['vote'];
				$data['ratingcount'] = $rating->ratingcount;
			} else {
				$data['rates'] = $data['vote'];
				$data['ratingcount'] = 1;
			}

			if(empty($data['rates']) || empty($data['ratingcount']) ){
				$data['rating'] = 0;
			} else {
				$data['rating'] = $data['rates']/$data['ratingcount'];
			}

			$data['virtuemart_rating_id'] = empty($rating->virtuemart_rating_id)? 0: $rating->virtuemart_rating_id;

			$rating = $this->getTable('ratings');
			$data = $rating->bindChecknStore($data);
	    	$errors = $rating->getErrors();
			foreach($errors as $error){
				$this->setError(get_class( $this ).'::Error store rating '.$error);
			}


			if(!empty($data['comment'])){
				$data['comment'] = substr($data['comment'], 0, VmConfig::get('vm_reviews_maximum_comment_length', 2000)) ;
				//set to defaut value not used (prevent hack)
				$data['review_ok'] = 0;
				$data['review_rating'] = 0;

				/* Check if ratings are auto-published (set to 0 prevent injected by user)*/
				if (VmConfig::get('reviews_autopublish',1)) $data['published'] = 1;

				$review = $this->getReviewByProduct($data['virtuemart_product_id'],$userId);

				if(!empty($review->review_rates)){
					$data['review_rates'] = $review->review_rates + $data['review_rate'];
				} else {
					$data['review_rates'] = $data['vote'];
				}

				if(!empty($review->review_ratingcount)){
					$data['review_ratingcount'] = $review->review_ratingcount+1;
				} else {
					$data['review_ratingcount'] = 1;
				}

				$data['review_rating'] = $data['review_rates']/$data['review_ratingcount'];

				$data['virtuemart_rating_review_id'] = empty($review->virtuemart_rating_review_id)? 0: $review->virtuemart_rating_review_id;

				$reviewTable = $this->getTable('rating_reviews');
		        $data = $reviewTable->bindChecknStore($data);
				$errors = $reviewTable->getErrors();
				foreach($errors as $error){
					$this->setError(get_class( $this ).'::Error store review '.$error);
				}
			}
			return true;
		} else{
			$this->setError('Cant save rating/review/vote without vote/product_id');
			return false;
		}

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
			FROM #__virtuemart_rating_reviews
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

	/**
	 * Decides if the rating/review should be shown on the FE 
	 * @author Max Milbers
	 */
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
