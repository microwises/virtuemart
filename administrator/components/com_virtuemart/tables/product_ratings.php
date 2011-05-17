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

if(!class_exists('VmTableData'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmtabledata.php');

/**
 * Product review table class
 * The class is is used to manage the reviews in the shop.
 *
 * @package		VirtueMart
 * @author RolandD
 * @author Max Milbers
 */
class TableProduct_ratings extends VmTableData {

	/** @var int Primary key */
//	var $virtuemart_product_rating_id	= 0;
	/** @var int Product ID */
	var $virtuemart_product_id           = 0;
	/** @var int The ID of the user who made comment */
	var $virtuemart_user_id         	= 0;

	/** @var int No idea what this is for */
	var $rates         			= 0;
	/** @var int No idea what this is for */
	var $ratingcount      		= 0;
	/** @var int No idea what this is for */
	var $rating      					= 0;
	var $lastip      					= 0;

	/** @var int State of the review */
	var $published         		= 0;


	/**
	* @author RolandD
	* @param $db A database connector object
	*/
	function __construct(&$db) {
		parent::__construct('#__virtuemart_product_ratings', 'virtuemart_product_id', $db);
		$this->setPrimaryKey('virtuemart_product_id');
//		$this->setObligatoryKeys('virtuemart_product_id','COM_VIRTUEMART_PRODUCT_RATINGS_RECORDS_MUST_CONTAIN_PRODUCT_ID');

		$this->setLoggable();

	}
}
// pure php no closing tag
