<?php
/**
*
* Data module for the order status
*
* @package	VirtueMart
* @subpackage OrderStatus
* @author Oscar van Eijk
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
 * Model class for the order status
 *
 * @package	VirtueMart
 * @subpackage OrderStatus
 * @author Oscar van Eijk
 */
class VirtueMartModelOrderstatus extends VmModel {

	/**
	 * constructs a VmModel
	 * setMainTable defines the maintable of the model
	 * @author Max Milbers
	 */
	function __construct() {
		parent::__construct();
		$this->setMainTable('orderstates');
	}

	/**
	 * Retrieve the detail record for the current $id if the data has not already been loaded.
	 */
	function getOrderStatus()
	{
		$db = JFactory::getDBO();

		if (empty($this->_data)) {
			$this->_data = $this->getTable('orderstates');
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
	 * Bind the post data to the order status table and save it
	 *
	 * @return boolean True is the save was successful, false otherwise.
	 */
	function store()
	{
		$table = $this->getTable('orderstates');

		$data = JRequest::get('post');
		$isNew = ($data['virtuemart_orderstate_id'] < 1) ? true : false;
		if ($isNew) {
			$reorderRequired = false;
		} else {
			$table->load($data['virtuemart_orderstate_id']);

			if ($table->ordering == $data['ordering']) {
				$reorderRequired = false;
			} else {
				$reorderRequired = true;
			}
		}
		if (!$table->bind($data)) { // Bind data
			$this->setError($table->getError());
			return false;
		}

		if (!$table->check()) { // Perform data checks
			$this->setError($table->getError());
			return false;
		}

		// if new item, order last in appropriate group
		if ($isNew) {
			$table->ordering = $table->getNextOrder();
		}

		if (!$table->store()) { // Write data to the DB
			$this->setError($table->getError());
			return false;
		}

		if ($reorderRequired) {
			$table->reorder();
		}

		return $table->virtuemart_orderstate_id;
	}


//	/**
//	 * Delete all record ids selected
//	 *
//	 * @return boolean True is the remove was successful, false otherwise.
//	 */
//	function remove()
//	{
//		$orderStatIds = JRequest::getVar('cid',  0, '', 'array');
//		$table = $this->getTable('orderstates');
//
//		foreach($orderStatIds as $orderStatId) {
//			if (!$table->remove($orderStatId)) {
//				$this->setError($table->getError());
//				return false;
//			}
//		}
//		return true;
//	}

	/**
	 * Retrieve a list of order statuses from the database.
	 *
	 * @return object List of order status objects
	 */
	function getOrderStatusList()
	{
		$query = 'SELECT * FROM `#__virtuemart_orderstates` ';
		$query .= $this->_getOrdering();
		$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		// set total for pagination
		$this->_total = $this->_getListCount($query);
		return $this->_data;
	}


	/**
	 * Change the ordering of an Order status
	 *
	 * @return boolean True on success
	 */
	function move($direction)
	{
		$table = $this->getTable('orderstates');
		if (!$table->load($this->_id)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		if (!$table->move($direction)){
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return true;
	}

	/**
	 * Check if stock should be updated when an order status changes/
	 * TODO This must be implemented in an orderstatus flow in a future release
	 *
	 * @author Oscar van Eijk, null if the order is new (default)
	 * @param char $_newStat New order status
	 * @param char $_oldStat Old order status, null if the order is new (default)
	 * @return integer <0: decrease stock, 0: do nothing, >0 increase stock
	 */
	function updateStockAfterStatusChange($_newStat, $_oldStat = null)
	{
		if ($_oldStat == null || $_oldStat == 'P' || $_oldStat == 'X' || $_oldStat == 'R') {
			if ($_newStat == 'C' || $_newStat == 'S') {
				return -1; // Decrease stock
			} else {
				return 0;
			}
		} elseif ($_oldStat == 'C' || $_oldStat == 'S') { // Status Shipped shouldn't be changeble...
			if ($_newStat == 'X' || $_newStat == 'R' || $_newStat == 'P') {
				return 1; // Increase stock
			} else {
				return 0;
			}
		} else {
			return 0;
		}
	}

	/**
	 * Reorder the Order statusus
	 *
	 * @return boolean True on success
	 */
	function saveorder($cid = array(), $order)
	{
		$table = $this->getTable('orderstates');

		// update ordering values
		for( $i=0; $i < count($cid); $i++ )
		{
			$table->load( (int) $cid[$i] );
			if ($table->ordering != $order[$i])
			{
				$table->ordering = $order[$i];
				if (!$table->store()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
			}
		}

		return true;
	}


}

//No Closing tag
