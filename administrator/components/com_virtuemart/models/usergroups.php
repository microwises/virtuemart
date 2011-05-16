<?php

/**
*
* Data module for shop extensions
*
* @package	VirtueMart
* @subpackage Extensions
* @author StephanieS
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
 * Model class for shop Currencies
 *
 * @package	VirtueMart
 * @subpackage Extensions
 * @author StephanieS, Max Milbers
 */

class VirtueMartModelUsergroups extends VmModel {


	/**
	 * constructs a VmModel
	 * setMainTable defines the maintable of the model
	 * @author Max Milbers
	 */
	function __construct() {
		parent::__construct();
		$this->setMainTable('usergroups');
	}

//    /** @var integer Primary key */
//    var $_id;
//
//    /** @var objectlist extensions data */
//    var $_data;
//
//    /** @var integer Total number of extensions in the database */
//    var $_total;
//
//    /** @var pagination Pagination for extensions list */
//    var $_pagination;


//    function __construct() {
//
//		parent::__construct();
//
//		// Get the pagination request variables
//		$mainframe = JFactory::getApplication() ;
//		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
//		$limitstart = $mainframe->getUserStateFromRequest(JRequest::getVar('option').JRequest::getVar('view').'.limitstart', 'limitstart', 0, 'int');
//
//		// Set the state pagination variables
//		$this->setState('limit', $limit);
//		$this->setState('limitstart', $limitstart);
//
//		// Get the extensions id or array of ids.
//		$idArray = JRequest::getVar('cid',  0, '', 'array');
//		$this->setId((int)$idArray[0]);
//
//    }



//    function setId($id) {
//		$this->_id = $id;
//		$this->_data = null;
//    }
//
//
//
//    function getPagination() {
//
//		if (empty($this->_pagination)) {
//		    jimport('joomla.html.pagination');
//		    $this->_pagination = new JPagination($this->_getTotal(), $this->getState('limitstart'), $this->getState('limit'));
//		}
//		return $this->_pagination;
//
//    }
//
//
//
//    function _getTotal() {
//
//		if (empty($this->_total)) {
//		    $query = 'SELECT `virtuemart_shoppergroup_id` FROM `#__virtuemart_shoppergroups`';
//		    $this->_total = $this->_getListCount($query);
//		}
//		return $this->_total;
//
//    }


    function getUsergroup() {

		$db = JFactory::getDBO();

		if (empty($this->_data)) {
		    $this->_data = $this->getTable('usergroups');
		    $this->_data->load((int)$this->_id);
		}

		if (!$this->_data) {
		    $this->_data = new stdClass();
		    $this->_id = 0;
		    $this->_data = null;
		}

		return $this->_data;
    }


    function getUsergroups($onlyPublished=false, $noLimit=false) {

		$db = JFactory::getDBO();
		$query = 'SELECT * FROM `#__virtuemart_shoppergroups` ';
//		if ($onlyPublished) {
//			$query .= 'WHERE `#__virtuemart_shoppergroups`.`published` = 1';
//		}
		$query .= ' ORDER BY `#__virtuemart_shoppergroups`.`group_name`';
		if ($noLimit) {
			$this->_data = $this->_getList($query);
		}
	 	else {
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}
		return $this->_data;

    }



//    function store() {
//
//		$table = $this->getTable('usergroups');
//
//		$data = JRequest::get( 'post' );
//
//		// Bind the form fields to the extensions table
//
//		if (!$table->bind($data)) {
//		    $this->setError($table->getError());
//		    return false;
//		}
//
//		// Make sure the extensions record is valid
//		if (!$table->check()) {
//		    $this->setError($table->getError());
//		    return false;
//		}
//
//		// Save the extensions record to the database
//		if (!$table->store()) {
//		    $this->setError($table->getError());
//		    return false;
//		}
//		return true;
//
//    }



//    function delete() {
//
//		if(!class_exists('modelfunctions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'modelfunctions.php');
//		return modelfunctions::delete('virtuemart_shoppergroup_id','usergroups');
//
//    }
//
//
//	function publish($publishId = false) {
//
//		if(!class_exists('modelfunctions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'modelfunctions.php');
//		return modelfunctions::publish('virtuemart_shoppergroup_id','usergroups',$publishId);
//
//    }


}
