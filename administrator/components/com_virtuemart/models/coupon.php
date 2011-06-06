<?php
/**
*
* Data module for shop coupons
*
* @package	VirtueMart
* @subpackage Coupon
* @author RickG
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
 * Model class for shop coupons
 *
 * @package	VirtueMart
 * @subpackage Coupon
 * @author RickG
 */
class VirtueMartModelCoupon extends VmModel {

	/**
	 * constructs a VmModel
	 * setMainTable defines the maintable of the model
	 * @author Max Milbers
	 */
	function __construct() {
		parent::__construct();
		$this->setMainTable('coupons');
	}

    /**
     * Retrieve the detail record for the current $id if the data has not already been loaded.
     *
     * @author RickG
     */
	function getCoupon()
	{
		$db = JFactory::getDBO();

  		if (empty($this->_data)) {
   			$this->_data = $this->getTable('coupons');
   			$this->_data->load((int)$this->_id);
  		}

  		if (!$this->_data) {
   			$this->_data = new stdClass();
   			$this->_id = 0;
   			$this->_data = null;
  		}

  		return $this->_data;
	}


	/**
	 * Bind the post data to the coupon table and save it
     *
     * @author RickG, Oscar van Eijk
     * @return mixed False if the save was unsuccessful, the coupon ID otherwise.
	 */
    function store()
	{
		$table = $this->getTable('coupons');
		$data = JRequest::get('post');

		// Convert selected dates to MySQL format for storing.
		$startDate = JFactory::getDate($data['coupon_start_date']);
		$data['coupon_start_date'] = $startDate->toMySQL();
		$expireDate = JFactory::getDate($data['coupon_expiry_date']);
		$data['coupon_expiry_date'] = $expireDate->toMySQL();

		parent::store();
//		// Bind the form fields to the coupon table
//		if (!$table->bind($data)) {
//			$this->setError($table->getError());
//			return false;
//		}
//
//		// Make sure the coupon record is valid
//		if (!$table->check()) {
//			$this->setError($table->getError());
//			return false;
//		}
//
//		// Save the coupon record to the database
//		if (!$table->store()) {
//			$this->setError($table->getError());
//			return false;
//		}
//
//		return $table->virtuemart_coupon_id;
	}


//	/**
//	 * Delete all record ids selected
//     *
//     * @author RickG
//     * @return boolean True is the remove was successful, false otherwise.
//     */
//	function remove()
//	{
//		$couponIds = JRequest::getVar('cid',  0, '', 'array');
//    	$table = $this->getTable('coupon');
//
//    	foreach($couponIds as $couponId) {
//        	if (!$table->remove($couponId)) {
//            	$this->setError($table->getError());
//            	return false;
//        	}
//    	}
//
//    	return true;
//	}


	/**
	 * Retireve a list of coupons from the database.
	 *
     * @author RickG
	 * @return object List of coupon objects
	 */
	function getCoupons()
	{
		$query = 'SELECT * FROM `#__virtuemart_coupons` ';
		$query .= 'ORDER BY `#__virtuemart_coupons`.`virtuemart_coupon_id`';
		$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		return $this->_data;
	}
}

// pure php no closing tag