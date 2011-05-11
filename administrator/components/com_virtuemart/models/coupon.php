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

/**
 * Model class for shop coupons
 *
 * @package	VirtueMart
 * @subpackage Coupon
 * @author RickG
 */
class VirtueMartModelCoupon extends JModel {

	/** @var integer Primary key */
    var $_id;
	/** @var objectlist Coupon data */
    var $_data;
	/** @var integer Total number of ccouponss in the database */
	var $_total;
	/** @var pagination Pagination for coupon list */
	var $_pagination;


    /**
     * Constructor for the coupon model.
     *
     * The coupon id is read and detmimined if it is an array of ids or just one single id.
     *
     * @author RickG
     */
    function __construct()
    {
        parent::__construct();

		// Get the pagination request variables
		$mainframe = JFactory::getApplication() ;
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest(JRequest::getVar('option').JRequest::getVar('view').'.limitstart', 'limitstart', 0, 'int');

		// Set the state pagination variables
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

        // Get the coupon id or array of ids.
		$idArray = JRequest::getVar('cid',  0, '', 'array');
    	$this->setId((int)$idArray[0]);
    }


    /**
     * Resets the coupon id and data
     *
     * @author RickG
     */
    function setId($id)
    {
        $this->_id = $id;
        $this->_data = null;
    }


	/**
	 * Loads the pagination for the coupon table
	 *
     * @author RickG
     * @return JPagination Pagination for the current list of coupons
	 */
    function getPagination()
    {
		if (empty($this->_pagination)) {
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->_getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}
		return $this->_pagination;
	}


	/**
	 * Gets the total number of coupons
	 *
     * @author RickG
	 * @return int Total number of coupons in the database
	 */
	function _getTotal()
	{
    	if (empty($this->_total)) {
			$query = 'SELECT `coupon_id` FROM `#__virtuemart_coupons`';
			$this->_total = $this->_getListCount($query);
        }
        return $this->_total;
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
   			$this->_data = $this->getTable();
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
		$table =& $this->getTable('coupon');
		$data = JRequest::get('post');

		// Convert selected dates to MySQL format for storing.
		$startDate = JFactory::getDate($data['coupon_start_date']);
		$data['coupon_start_date'] = $startDate->toMySQL();
		$expireDate = JFactory::getDate($data['coupon_expiry_date']);
		$data['coupon_expiry_date'] = $expireDate->toMySQL();

		// Bind the form fields to the coupon table
		if (!$table->bind($data)) {
			$this->setError($table->getError());
			return false;
		}

		// Make sure the coupon record is valid
		if (!$table->check()) {
			$this->setError($table->getError());
			return false;
		}

		// Save the coupon record to the database
		if (!$table->store()) {
			$this->setError($table->getError());
			return false;
		}

		return $table->coupon_id;
	}


	/**
	 * Delete all record ids selected
     *
     * @author RickG
     * @return boolean True is the delete was successful, false otherwise.
     */
	function delete()
	{
		$couponIds = JRequest::getVar('cid',  0, '', 'array');
    	$table =& $this->getTable('coupon');

    	foreach($couponIds as $couponId) {
        	if (!$table->delete($couponId)) {
            	$this->setError($table->getError());
            	return false;
        	}
    	}

    	return true;
	}


	/**
	 * Retireve a list of coupons from the database.
	 *
     * @author RickG
	 * @return object List of coupon objects
	 */
	function getCoupons()
	{
		$query = 'SELECT * FROM `#__virtuemart_coupons` ';
		$query .= 'ORDER BY `#__virtuemart_coupons`.`coupon_id`';
		$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		return $this->_data;
	}
}

// pure php no closing tag