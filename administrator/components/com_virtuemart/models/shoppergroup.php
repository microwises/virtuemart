<?php
/**
*
* Data model for shopper group
*
* @package	VirtueMart
* @subpackage ShopperGroup
* @author Markus Öhler
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
 * Model class for shopper group
 *
 * @package	VirtueMart
 * @subpackage ShopperGroup
 * @author Markus Öhler
 */
class VirtueMartModelShopperGroup extends VmModel {

	/**
	 * constructs a VmModel
	 * setMainTable defines the maintable of the model
	 * @author Max Milbers
	 */
	function __construct() {
		parent::__construct();
		$this->setMainTable('shoppergroups');
	}

//    /** @var integer Primary key */
//    private $_cid;
//    /** @var integer Primary key */
//    private $_id;
//    /** @var objectlist Shopper group data */
//    private $_data;
//    /** @var integer Total number of shopper groups in the database */
//    private $_total;
//    /** @var pagination Pagination for shopper group list */
//    private $_pagination;


//    /**
//     * Constructor for the shopper group model.
//     *
//     * @author Markus Öhler
//     */
//    function __construct() {
//	    parent::__construct();
//
//		// Get the pagination request variables
//		$mainframe = JFactory::getApplication() ;
//		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
//		$limitstart = $mainframe->getUserStateFromRequest(JRequest::getVar('option').JRequest::getVar('view') . '.limitstart', 'limitstart', 0, 'int');
//
//	    // Set the state pagination variables
//	    $this->setState('limit', $limit);
//	    $this->setState('limitstart', $limitstart);
//
//	    // Get the shopper group id or array of ids.
//	    $idArray = JRequest::getVar('cid', 0, '', 'array');
//	    $this->setId((int)$idArray[0]);
//    }

//    /**
//     * Resets the shopper group id and data
//     *
//     * @author Markus Öhler
//     */
//    function setId($id) {
//	    $this->_id = $id;
//	    $this->_data = null;
//    }
//
//    /**
//     * Loads the pagination for the shopper group table
//     *
//     * @author Markus Öhler
//     * @return JPagination Pagination for the current list of shopper groups
//     */
//    function getPagination() {
//	    if (empty($this->_pagination)) {
//	      jimport('joomla.html.pagination');
//	      $this->_pagination = new JPagination($this->_getTotal(), $this->getState('limitstart'), $this->getState('limit'));
//	    }
//	    return $this->_pagination;
//    }


//    /**
//     * Gets the total number of countries
//     *
//     * @author Markus Öhler
//     * @return int Total number of countries in the database
//     */
//    function _getTotal() {
//
//	    if (empty($this->_total)) {
//	    	$db = JFactory::getDBO();
//	      $query = 'SELECT ' . $db->nameQuote('virtuemart_shoppergroup_id')
//	        . ' FROM ' . $db->nameQuote('#__virtuemart_shoppergroups');
//	      $this->_total = $this->_getListCount($query);
//	    }
//
//	    return $this->_total;
//    }


    /**
     * Retrieve the detail record for the current $id if the data has not already been loaded.
     *
     * @author Markus Öhler
     */
    function getShopperGroup() {
	    $db = JFactory::getDBO();

	    if (empty($_data)) {
	      $this->_data = $this->getTable('shoppergroups');
	      $this->_data->load((int) $this->_id);
	    }

	    if (!$this->_data) {
	      $this->_data = new stdClass();
	      $this->_id = 0;
	      $this->_data = null;
	    }

	    return $this->_data;
    }


    /**
     * Retireve a list of shopper groups from the database.
     *
     * @author Markus Öhler
     * @param string $noLimit True if no record count limit is used, false otherwise
     * @return object List of shopper group objects
     */
    function getShopperGroups($onlyPublished=false, $noLimit = false) {
    	$db = JFactory::getDBO();

	    $query = 'SELECT * FROM '
	      . $db->nameQuote('#__virtuemart_shoppergroups')
	      . 'ORDER BY '
	      . $db->nameQuote('virtuemart_vendor_id')
	      . ','
	      . $db->nameQuote('shopper_group_name')
		;
		if ($noLimit) {
			$this->_data = $this->_getList($query);
		}
		else {
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}

	    return $this->_data;
    }

    /**
     * Bind the post data to the shoppergroup table and save it
     *
     * @author Markus Öhler, Max Milbers
     * @return boolean True is the save was successful, false otherwise.
     */
//    function store() {
//	    $table = $this->getTable('shoppergroup');
//
//	    $data = JRequest::get('post');
//
//	    // Bind the form fields to the shoppergroup table
//	    if (!$table->bind($data)) {
//	      $this->setError($table->getError());
//	      return false;
//	    }
//
//	    // Make sure the shoppergroup record is valid
//	    if (!$table->check()) {
//	      $this->setError($table->getError());
//	      return false;
//	    }
//
//	    // Save the shoppergroup record to the database
//	    if (!$table->store()) {
//	      $this->setError($table->getError());
//	      return false;
//	    }
//
//	    return $table->virtuemart_shoppergroup_id;
//    }


//    /**
//     * Delete all records specified by the cid request parameter.
//     *
//     * @author Markus Öhler
//     * @return boolean True is the remove was successful, false otherwise.
//     */
//    function remove() {
//	    $ids = JRequest::getVar('cid',  0, '', 'array');
//	    $table = $this->getTable('shoppergroup');
//
//	    foreach($ids as $id) {
//		    if (!$table->remove($id)) {
//		       $this->setError($table->getError());
//		       return false;
//		    }
//	    }
//
//	    return true;
//    }


}
// pure php no closing tag