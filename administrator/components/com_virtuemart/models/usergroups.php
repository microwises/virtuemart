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

/**
 * Model class for shop Currencies
 *
 * @package	VirtueMart
 * @subpackage Extensions
 * @author StephanieS, Max Milbers
 */

class VirtueMartModelUsergroups extends JModel {

    /** @var integer Primary key */
    var $_id;

    /** @var objectlist extensions data */
    var $_data;

    /** @var integer Total number of extensions in the database */
    var $_total;

    /** @var pagination Pagination for extensions list */
    var $_pagination;


    function __construct() {

		parent::__construct();

		// Get the pagination request variables
		$mainframe = JFactory::getApplication() ;
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest(JRequest::getVar('option').'.limitstart', 'limitstart', 0, 'int');

		// Set the state pagination variables
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		// Get the extensions id or array of ids.
		$idArray = JRequest::getVar('cid',  0, '', 'array');
		$this->setId((int)$idArray[0]);

    }



    function setId($id) {
		$this->_id = $id;
		$this->_data = null;
    }



    function getPagination() {

		if (empty($this->_pagination)) {
		    jimport('joomla.html.pagination');
		    $this->_pagination = new JPagination($this->_getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}
		return $this->_pagination;

    }



    function _getTotal() {

		if (empty($this->_total)) {
		    $query = 'SELECT `group_id` FROM `#__vm_perm_groups`';
		    $this->_total = $this->_getListCount($query);
		}
		return $this->_total;

    }


    function getUsergroup() {

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


    function getUsergroups($onlyPublished=false, $noLimit=false) {

		$db = JFactory::getDBO();
		$query = 'SELECT * FROM `#__vm_perm_groups` ';
//		if ($onlyPublished) { 
//			$query .= 'WHERE `#__vm_perm_groups`.`published` = 1';			
//		}
		$query .= ' ORDER BY `#__vm_perm_groups`.`group_name`';
		if ($noLimit) {
			$this->_data = $this->_getList($query);
		}
	 	else {
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}
		return $this->_data;

    }



    function store() {

		$table = $this->getTable('usergroups');
	
		$data = JRequest::get( 'post' );
		echo 'store that <pre>'.$data.'</pre>';
		// Bind the form fields to the extensions table
	
		if (!$table->bind($data)) {
		    $this->setError($table->getError());
		    return false;
		}

		// Make sure the extensions record is valid
		if (!$table->check()) {
		    $this->setError($table->getError());
		    return false;
		}

		// Save the extensions record to the database
		if (!$table->store()) {
		    $this->setError($table->getError());
		    return false;
		}
		return true;

    }



    function delete() {

		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'modelfunctions.php');
		return modelfunctions::delete('group_id','usergroups');

    }


	function publish($publishId = false) {

		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'modelfunctions.php');
		return modelfunctions::publish('group_id','usergroups',$publishId);
		
    }


}
