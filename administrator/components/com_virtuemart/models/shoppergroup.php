<?php
/**
*
* Data model for shopper group
*
* @package	VirtueMart
* @subpackage ShopperGroup
* @author Markus �hler
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
 * Model class for shopper group
 *
 * @package	VirtueMart
 * @subpackage ShopperGroup
 * @author Markus �hler
 */
class VirtueMartModelShopperGroup extends JModel {

    /** @var integer Primary key */
    var $_id;
    /** @var objectlist Shopper group data */
    var $_data;
    /** @var integer Total number of shopper groups in the database */
    var $_total;
    /** @var pagination Pagination for shopper group list */
    var $_pagination;


    /**
     * Constructor for the shopper group model.
     *
     * @author Markus �hler
     */
    function __construct() {
	    parent::__construct();

			// Get the pagination request variables
			$mainframe = JFactory::getApplication() ;
			$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
			$limitstart = $mainframe->getUserStateFromRequest(JRequest::getVar('option') . '.limitstart', 'limitstart', 0, 'int');

	    // Set the state pagination variables
	    $this->setState('limit', $limit);
	    $this->setState('limitstart', $limitstart);

	    // Get the shopper group id or array of ids.
	    $idArray = JRequest::getVar('cid', 0, '', 'array');
	    $this->setId((int)$idArray[0]);
    }

    /**
     * Resets the shopper group id and data
     *
     * @author Markus �hler
     */
    function setId($id) {
	    $this->_id = $id;
	    $this->_data = null;
    }

    /**
     * Loads the pagination for the shopper group table
     *
     * @author Markus �hler
     * @return JPagination Pagination for the current list of shopper groups
     */
    function getPagination() {
	    if (empty($this->_pagination)) {
	      jimport('joomla.html.pagination');
	      $this->_pagination = new JPagination($this->_getTotal(), $this->getState('limitstart'), $this->getState('limit'));
	    }
	    return $this->_pagination;
    }


    /**
     * Gets the total number of countries
     *
     * @author Markus �hler
     * @return int Total number of countries in the database
     */
    function _getTotal() {

	    if (empty($this->_total)) {
	    	$db = JFactory::getDBO();
	      $query = 'SELECT ' . $db->nameQuote('shopper_group_id')
	        . ' FROM ' . $db->nameQuote('#__vm_shopper_group');
	      $this->_total = $this->_getListCount($query);
	    }

	    return $this->_total;
    }


    /**
     * Retrieve the detail record for the current $id if the data has not already been loaded.
     *
     * @author Markus �hler
     */
    function getShopperGroup() {
	    $db = JFactory::getDBO();

	    if (empty($_data)) {
	      $this->_data = $this->getTable();
	      $this->_data->load((int) $idArray[0]);
	    }

	    if (!$this->_data) {
	      $this->_data = new stdClass();
	      $this->_id = 0;
	      $this->_data = null;
	    }

	    return $this->_data;
    }


    /**
     * Bind the post data to the shoppergroup table and save it
     *
     * @author Markus �hler
     * @return boolean True is the save was successful, false otherwise.
     */
    function store() {
	    $table = $this->getTable('shoppergroup');

	    $data = JRequest::get('post');

	    // Bind the form fields to the shoppergroup table
	    if (!$table->bind($data)) {
	      $this->setError($table->getError());
	      return false;
	    }

	    // Make sure the shoppergroup record is valid
	    if (!$table->check()) {
	      $this->setError($table->getError());
	      return false;
	    }

	    // Save the shoppergroup record to the database
	    if (!$table->store()) {
	      $this->setError($table->getError());
	      return false;
	    }

	    return true;
    }


    /**
     * Delete all records specified by the cid request parameter.
     *
     * @author Markus �hler
     * @return boolean True is the delete was successful, false otherwise.
     */
    function delete() {
	    $ids = JRequest::getVar('cid',  0, '', 'array');
	    $table = $this->getTable('shoppergroup');

	    foreach($ids as $id) {
		    if (!$table->delete($id)) {
		       $this->setError($table->getError());
		       return false;
		    }
	    }

	    return true;
    }


    /**
     * Retireve a list of shopper groups from the database.
     *
     * @author Markus �hler
     * @param string $noLimit True if no record count limit is used, false otherwise
     * @return object List of shopper group objects
     */
    function getShopperGroups($noLimit = false) {
    	$db = JFactory::getDBO();
	    $query = 'SELECT * FROM '
	      . $db->nameQuote('#__vm_shopper_group')
	      . 'ORDER BY '
	      . $db->nameQuote('vendor_id')
	      . ','
	      . $db->nameQuote('shopper_group_name');

	    if ($noLimit) {
	      $this->_data = $this->_getList($query);
	    } else {
	      $this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
     	}

	    return $this->_data;
    }
}
?>