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

/**
 * Model class for the order status
 *
 * @package	VirtueMart
 * @subpackage OrderStatus
 * @author Oscar van Eijk
 */
class VirtueMartModelOrderstatus extends JModel {

	/** @var integer Primary key */
	var $_id;
	/** @var objectlist order status data */
	var $_data;
	/** @var integer Total number of order statuses in the database */
	var $_total;
	/** @var pagination Pagination for order status list */
	var $_pagination;

	/**
	 * Constructor for the order status model.
	 *
	 * The order status id id is read and detmimined if it is an array of ids or just one single id.
	 */
	function __construct()
	{
		parent::__construct();

		// Get the pagination request variables
		$mainframe = JFactory::getApplication() ;
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest(JRequest::getVar('option').'.limitstart', 'limitstart', 0, 'int');

		// Set the state pagination variables
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		// Get the (array of) order status ID(s)
		$idArray = JRequest::getVar('cid',  0, '', 'array');
		$this->setId((int)$idArray[0]);
	}

	/**
	 * Resets the order status id and data
	 */
	function setId($id)
	{
		$this->_id = $id;
		$this->_data = null;
	}

	/**
	 * Loads the pagination for the order status table
	 *
	 * @return JPagination Pagination for the current list of order statuses
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
	 * Gets the total number of order statusses
	 *
	 * @return int Total number of order statusses in the database
	 */
	function _getTotal()
	{
		if (empty($this->_total)) {
			$query = 'SELECT `order_status_id` FROM `#__vm_order_status`';
			$this->_total = $this->_getListCount($query);
		}
		return $this->_total;
	}

	/**
	 * Retrieve the detail record for the current $id if the data has not already been loaded.
	 */
	function getOrderStatus()
	{
		$db = JFactory::getDBO();

		if (empty($this->_data)) {
			$this->_data = $this->getTable('order_status');
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
		$table =& $this->getTable('order_status');

		$data = JRequest::get('post');
		$isNew = ($data['order_status_id'] < 1) ? true : false;
		if ($isNew) {
			$reorderRequired = false;
		} else {
			$table->load($data['order_status_id']);

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

		return true;
	}


	/**
	 * Delete all record ids selected
	 *
	 * @return boolean True is the delete was successful, false otherwise.
	 */
	function delete()
	{
		$orderStatIds = JRequest::getVar('cid',  0, '', 'array');
		$table =& $this->getTable('order_status');

		foreach($orderStatIds as $orderStatId) {
			if (!$table->delete($orderStatId)) {
				$this->setError($table->getError());
				return false;
			}
		}
		return true;
	}

	/**
	 * Retrieve a list of order statuses from the database.
	 *
	 * @return object List of order status objects
	 */
	function getOrderStatusList()
	{
		$query = 'SELECT * FROM `#__vm_order_status` ';
		$query .= $this->_getOrdering();
		$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		return $this->_data;
	}

	/**
	 * Get the SQL Ordering statement
	 *
	 * @return string text to add to the SQL statement
	 */
	function _getOrdering()
	{
		global $mainframe, $option;

		$filter_order_Dir = $mainframe->getUserStateFromRequest( $option.'filter_order_Dir', 'filter_order_Dir', 'asc', 'word' );
		$filter_order     = $mainframe->getUserStateFromRequest( $option.'filter_order', 'filter_order', 'ordering', 'cmd' );

		return (' ORDER BY '.$filter_order.' '.$filter_order_Dir);
	}

	/**
	 * Change the ordering of an Order status
	 *
	 * @return boolean True on success
	 */
	function move($direction)
	{
		$table =& $this->getTable('order_status');
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
		$table =& $this->getTable('order_status');

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
