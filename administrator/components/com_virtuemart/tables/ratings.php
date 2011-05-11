<?php
/**
*
* Product reviews table
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

/**
 * Product review table class
 * The class is is used to manage the reviews in the shop.
 *
 * @package		VirtueMart
 * @author RolandD
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
	var $enabled         		= 0;
               /** @var boolean */
	var $locked_on	= 0;
	/** @var time */
	var $locked_by	= 0;

	/**
	* @author RolandD
	* @param $db A database connector object
	*/
	function __construct(&$db) {
		parent::__construct('#__vm_product_reviews', 'review_id', $db);
	}
}
// pure php no closing tag
