<?php
/**
 * Product reviews table
 *
 * @package	VirtueMart
 * @author RolandD 
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Product review table class
 * The class is is used to manage the reviews in the shop.
 *
 * @author RolandD
 * @package		VirtueMart
 */
class TableRatings extends JTable {
	/** @var int Primary key */
	var $review_id				= 0;
	/** @var int Product ID */
	var $product_id           	= null;	
	/** @var string The user comment */
	var $comment         		= null;
	/** @var int The ID of the user who made comment */
	var $userid         		= null;
	/** @var date Timestamp of when the comment was made */
	var $time         			= null;
	/** @var int The number of stars awared */
	var $user_rating       		= null;
	/** @var int No idea what this is for */
	var $review_ok         		= null;
	/** @var int No idea what this is for */
	var $review_votes      		= null;
	/** @var int State of the review */
	var $published         		= 0;


	/**
	* @author RolandD
	* @param $db A database connector object
	*/
	function __construct(&$db) {
		parent::__construct('#__vm_product_reviews', 'review_id', $db);
	}
}
?>
