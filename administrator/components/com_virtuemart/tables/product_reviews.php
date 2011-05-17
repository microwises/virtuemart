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
* @version $Id: ratings.php 3267 2011-05-16 22:51:49Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

if(!class_exists('VmTable'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmtable.php');

/**
 * Product review table class
 * The class is is used to manage the reviews in the shop.
 *
 * @package		VirtueMart
 * @author RolandD
 * @author Max Milbers
 */
class TableRatings extends VmTable {

	/** @var int Primary key */
	var $virtuemart_product_review_id				= 0;
	/** @var int Product ID */
	var $virtuemart_product_id           	= null;
	/** @var int The ID of the user who made comment */
	var $virtuemart_user_id         		= null;
	/** @var string The user comment */
	var $comment         		= null;
	/** @var int The number of stars awared */
	var $review_ok       		= null;
	/** @var int No idea what this is for */
	var $review_rate         		= null;
	/** @var int No idea what this is for */
	var $review_ratingcount      		= null;
	/** @var int No idea what this is for */
	var $rating      		= null;
	var $lastip      		= null;

	/** @var int State of the review */
	var $published         		= 0;


	/**
	* @author RolandD
	* @param $db A database connector object
	*/
	function __construct(&$db) {
		parent::__construct('#__virtuemart_product_reviews', 'virtuemart_product_review_id', $db);

//		$this->setUniqueName('country_name','COM_VIRTUEMART_COUNTRY_NAME_ALREADY_EXISTS');
//		$this->setObligatoryKeys('country_2_code','COM_VIRTUEMART_COUNTRY_RECORDS_MUST_CONTAIN_2_SYMBOL_CODE');
//		$this->setObligatoryKeys('country_3_code','COM_VIRTUEMART_COUNTRY_RECORDS_MUST_CONTAIN_3_SYMBOL_CODE');

		$this->setLoggable();

	}
}
// pure php no closing tag
