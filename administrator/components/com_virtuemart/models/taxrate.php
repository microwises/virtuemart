<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Tax
* @author RolandD, RickG
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
 * Model for VirtueMart Products
 *
 * @package VirtueMart
 * @subpackage Tax
 * @author RolandD, RickG
 */
class VirtueMartModelTaxRate extends JModel {

	/** @var integer Primary key */
    var $_id;
	/** @var objectlist currency data */
    var $_data;
	/** @var integer Total number of tax rates in the database */
	var $_total;
	/** @var pagination Pagination for tax rate list */
	var $_pagination;


	/**
     * Constructor for the tax rate model.
     *
     * The tax rate id is read and detmimined if it is an array of ids or just one single id.
     *
     * @author RickG
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

        // Get the currency id or array of ids.
		$idArray = JRequest::getVar('cid',  0, '', 'array');
    	$this->setId((int)$idArray[0]);
    }


    /**
     * Resets the tax rate id and data
     *
     * @author RickG
     */
    function setId($id)
    {
        $this->_id = $id;
        $this->_data = null;
    }


	/**
	 * Loads the pagination for the tax rate table
	 *
     * @author RickG
     * @return JPagination Pagination for the current list of tax rates
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
	 * Gets the total number of tax rates
	 *
     * @author RickG
	 * @return int Total number of tax rates in the database
	 */
	function _getTotal()
	{
    	if (empty($this->_total)) {
//			$query = 'SELECT `tax_rate_id` FROM `#__vm_tax_rate`';
			$query = 'SELECT `calc_id` FROM `#__vm_tax_rate` WHERE `calc_kind`="TAX" OR `calc_kind`="TaxBill" ';
			$this->_total = $this->_getListCount($query);
        }
        return $this->_total;
    }


    /**
     * Retrieve the detail record for the current $id if the data has not already been loaded.
     *
     * @author RickG
     */
	function getTaxRate()
	{
		$db = JFactory::getDBO();

//  		if (empty($this->_data)) {
//  			$q = 'SELECT * FROM `#__vm_calc` WHERE `calc_id`="'.(int)$this->_id.'" AND WHERE `calc_kind`="TAX" OR `calc_kind`="TaxBill" ';
//  			$db->setQuery($q);
//			$this->_data = $db->loadResultArray();
  			
   			$this->_data = $this->getTable('tax_rate');
   			$this->_data->load((int)$this->_id);
   			echo print_r($this->_data);die;
//  		}

  		if (!$this->_data) {
   			$this->_data = new stdClass();
   			$this->_id = 0;
   			$this->_data = null;
  		}

  		return $this->_data;
	}


	function getTaxRates() {
		
		$db = JFactory::getDBO();
		
		if (empty($this->_data)) {
			$q = 'SELECT * ';
//			$q .= 'CONCAT("(", `#__vm_calc`.`calc_id`, ") ", FORMAT(`#__vm_calc`.`calc_value`*100, 2)) AS select_list_name ';
			$q .= 'FROM `#__vm_calc` WHERE `calc_kind`="TAX" OR `calc_kind`="TaxBill" AND `calc_value_mathop`="+%" OR `calc_value_mathop`="-%" ';
//  			$q = 'SELECT * FROM `#__vm_calc` WHERE `calc_kind`="TAX" OR `calc_kind`="TaxBill" ';
  			$db->setQuery($q);
			$this->_data = $db->loadObjectList();
  			
//   			$this->_data = $this->getTable('tax_rate');
//   			$this->_data->load((int)$this->_id);
  		}
		if (!$this->_data) {
   			$this->_data = new stdClass();
   			$this->_id = 0;
   			$this->_data = null;
  		}
  		
		return $this->_data;
	}

	/**
	 * Retireve a list of tax rates from the database.
	 *
     * @author RolandD, RickG
	 * @return object List of tax rates objects
	 */
//	function getTaxRates() {
//		$db = JFactory::getDBO();
//		$query = 'SELECT *, ';
//		$query .= 'CONCAT("(", `#__vm_tax_rate`.`tax_rate_id`, ") ", FORMAT(`#__vm_tax_rate`.`tax_rate`*100, 2)) AS select_list_name ';
//		$query .= 'FROM `#__vm_tax_rate`';
//
//		$db->setQuery($query);
//		return $db->loadObjectList();
//	}

	/**
	 * Bind the post data to the tax rate table and save it
     *
     * @author RickG
     * @return boolean True is the save was successful, false otherwise.
	 */
    function store()
	{
//		$table =& $this->getTable('tax_rate');
//
//		$data = JRequest::get( 'post' );
//		// Bind the form fields to the tax rate table
//		if (!$table->bind($data)) {
//			$this->setError($table->getError());
//			return false;
//		}
//
//		// Make sure the tax rate record is valid
//		if (!$table->check()) {
//			$this->setError($table->getError());
//			return false;
//		}
//
//		// Save the tax rate record to the database
//		if (!$table->store()) {
//			$this->setError($table->getError());
//			return false;
//		}

		return true;
	}


	/**
	 * Delete all record ids selected
     *
     * @author RickG
     * @return boolean True is the delete was successful, false otherwise.
     */
	function delete()
	{
//		$currencyIds = JRequest::getVar('cid',  0, '', 'array');
//    	$table =& $this->getTable('currency');
//
//    	foreach($currencyIds as $currencyId) {
//        	if (!$table->delete($currencyId)) {
//            	$this->setError($table->getError());
//            	return false;
//        	}
//    	}

    	return true;
	}



}
?>