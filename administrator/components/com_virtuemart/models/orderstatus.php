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

	function getVMCoreStatusCode(){
		return array( 'P','S');
	}

	public function store(&$data){

		$table = $this->getTable($this->_maintablename);

		$data = $table->bindChecknStore($data,true);

		$errors = $table->getErrors();
		foreach($errors as $error){
			$this->setError( get_class( $this ).'::store '.$error);
		}
		if(is_object($data)){
			$_idName = $this->_idName;
			return $data->$_idName;
		} else {
			return $data[$this->_idName];
		}

	}


	/**
	 * Retrieve a list of order statuses from the database.
	 *
	 * @return object List of order status objects
	 */
	function getOrderStatusList()
	{

		if (JRequest::getWord('view') !== 'orderstatus') $ordering = ' order by `ordering` ';
		else $ordering = $this->_getOrdering();
		$this->_noLimit=true;
		$this->_data = $this->exeSortSearchListQuery(0,'*',' FROM `#__virtuemart_orderstates`','','',$ordering);
		// 		vmdebug('order data',$this->_data);
		return $this->_data ;
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
	 * @deprecated
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
		} elseif ($_oldStat == 'C' || $_oldStat == 'S') {
			// Status Shipped shouldn't be changeble...
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
