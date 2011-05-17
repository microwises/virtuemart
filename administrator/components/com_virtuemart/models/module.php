<?php
/**
*
* Data model for shopper group
*
* NOTICE: With the joomla native plugin and module system this page is obsolete.
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

//// Check to ensure this file is included in Joomla!
//defined('_JEXEC') or die('Restricted access');
//
//// Load the model framework
//jimport( 'joomla.application.component.model');
//
///**
// * Model class for shopper group
// *
// * @package	VirtueMart
// * @subpackage Module
// * @author Markus Öhler
// */
//class VirtueMartModelModule extends JModel {
//
//    /** @var integer Primary key */
//    var $_id;
//    /** @var objectlist Shopper group data */
//    var $_data;
//    /** @var integer Total number of shopper groups in the database */
//    var $_total;
//    /** @var pagination Pagination for shopper group list */
//    var $_pagination;
//
//
//    /**
//     * Constructor for the shopper group model.
//     *
//     * @author Markus Öhler
//     */
//    function __construct() {
//	    parent::__construct();
//
//			// Get the pagination request variables
//			$mainframe = JFactory::getApplication() ;
//			$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
//			$limitstart = $mainframe->getUserStateFromRequest(JRequest::getVar('option') . '.limitstart', 'limitstart', 0, 'int');
//
//	    // Set the state pagination variables
//	    $this->setState('limit', $limit);
//	    $this->setState('limitstart', $limitstart);
//
//	    // Get the shopper group id or array of ids.
//	    $idArray = JRequest::getVar('cid', 0, '', 'array');
//	    $this->setId((int)$idArray[0]);
//    }
//
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
//     * Loads the pagination for the module table
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
//
//
//    /**
//     * Gets the total number of modules
//     *
//     * @author Markus Öhler
//     * @return int Total number of modules in the database
//     */
//    function _getTotal() {
//
//	    if (empty($this->_total)) {
//	    	$db = JFactory::getDBO();
//	      $query = 'SELECT ' . $db->nameQuote('module_id')
//	        . ' FROM ' . $db->nameQuote('#__virtuemart_modules');
//	      $this->_total = $this->_getListCount($query);
//	    }
//
//	    return $this->_total;
//    }
//
//
//    /**
//     * Retrieve the detail record for the current $id if the
//     * data has not already been loaded.
//     *
//     * @author Markus Öhler
//     */
//    function getModule() {
//	    $db = JFactory::getDBO();
//
//	    if (empty($_data)) {
//	      $this->_data = $this->getTable('modules');
//	      $this->_data->load((int) $this->_id);
//	    }
//
//	    if (!$this->_data) {
//	      $this->_data = new stdClass();
//	      $this->_id = 0;
//	      $this->_data = null;
//	    }
//
//	    return $this->_data;
//    }
//
//
//    /**
//     * Bind the post data to the module table and save it
//     *
//     * @author Markus Öhler
//     * @return boolean True is the save was successful, false otherwise.
//     */
//    function store() {
//	    $table = $this->getTable('module');
//
//	    $data = JRequest::get('post');
//
//	    // Bind the form fields to the module table
//	    if (!$table->bind($data)) {
//	      $this->setError($table->getError());
//	      return false;
//	    }
//
//	    // Make sure the module record is valid
//	    if (!$table->check()) {
//	      $this->setError($table->getError());
//	      return false;
//	    }
//
//	    // Save the module record to the database
//	    if (!$table->store()) {
//	      $this->setError($table->getError());
//	      return false;
//	    }
//
//	    return true;
//    }
//
//
//    /**
//     * Delete all records specified by the cid request parameter.
//     *
//     * @author Markus Öhler
//     * @return boolean True is the remove was successful, false otherwise.
//     */
//    function remove() {
//	    $ids = JRequest::getVar('cid',  0, '', 'array');
//	    $table = $this->getTable('module');
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
//
//
//    /**
//     * Retireve a list of modules from the database.
//     *
//     * @author Markus Öhler
//     * @param string $noLimit True if no record count limit is used, false otherwise
//     * @return object List of module objects
//     */
//    function getModules($noLimit = false) {
//    	$db = JFactory::getDBO();
//	    $query = 'SELECT * FROM '
//	      . $db->nameQuote('#__virtuemart_modules')
//	      . 'ORDER BY '
//	      . $db->nameQuote('module_id');
//
//	    if ($noLimit) {
//	      $this->_data = $this->_getList($query);
//	    } else {
//	      $this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
//     	}
//
//	    return $this->_data;
//    }
//}
//// pure php no closing tag