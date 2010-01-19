<?php
/**
*
* Data module for shop currencies
*
* @package	VirtueMart
* @subpackage Currency
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
 * Model class for shop Currencies
 *
 * @package	VirtueMart
 * @subpackage Currency
 * @author RickG
 */
class VirtueMartModelCurrency extends JModel {

    /** @var integer Primary key */
    var $_id;
    /** @var objectlist currency data */
    var $_data;
    /** @var integer Total number of currencies in the database */
    var $_total;
    /** @var pagination Pagination for currency list */
    var $_pagination;


    /**
     * Constructor for the currency model.
     *
     * The currency id is read and detmimined if it is an array of ids or just one single id.
     *
     * @author RickG
     */
    function __construct() {
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
     * Resets the currency id and data
     *
     * @author RickG
     */
    function setId($id) {
	$this->_id = $id;
	$this->_data = null;
    }


    /**
     * Loads the pagination for the currency table
     *
     * @author RickG
     * @return JPagination Pagination for the current list of currencies
     */
    function getPagination() {
	if (empty($this->_pagination)) {
	    jimport('joomla.html.pagination');
	    $this->_pagination = new JPagination($this->_getTotal(), $this->getState('limitstart'), $this->getState('limit'));
	}
	return $this->_pagination;
    }


    /**
     * Gets the total number of currencies
     *
     * @author RickG
     * @return int Total number of currencies in the database
     */
    function _getTotal() {
	if (empty($this->_total)) {
	    $query = 'SELECT `currency_id` FROM `#__vm_currency`';
	    $this->_total = $this->_getListCount($query);
	}
	return $this->_total;
    }


    /**
     * Retrieve the detail record for the current $id if the data has not already been loaded.
     *
     * @author RickG
     */
    function getCurrency() {
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
     * Bind the post data to the currency table and save it
     *
     * @author RickG
     * @return boolean True is the save was successful, false otherwise.
     */
    function store() {
	$table =& $this->getTable('currency');

	$data = JRequest::get( 'post' );
	// Bind the form fields to the currency table
	if (!$table->bind($data)) {
	    $this->setError($table->getError());
	    return false;
	}

	// Make sure the currency record is valid
	if (!$table->check()) {
	    $this->setError($table->getError());
	    return false;
	}

	// Save the currency record to the database
	if (!$table->store()) {
	    $this->setError($table->getError());
	    return false;
	}

	return true;
    }


    /**
     * Delete all record ids selected
     *
     * @author RickG
     * @return boolean True is the delete was successful, false otherwise.
     */
    function delete() {
	$currencyIds = JRequest::getVar('cid',  0, '', 'array');
	$table =& $this->getTable('currency');

	foreach($currencyIds as $currencyId) {
	    if (!$table->delete($currencyId)) {
		$this->setError($table->getError());
		return false;
	    }
	}

	return true;
    }


    /**
     * Retireve a list of currencies from the database.
     *
     * @author RickG
     * @return object List of currency objects
     */
    function getCurrenciesList() {
	$query = 'SELECT * FROM `#__vm_currency` ';
	$query .= 'ORDER BY `#__vm_currency`.`currency_id`';
	$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
	return $this->_data;
    }

    /**
     * Retireve a list of currencies from the database.
     *
     * @author RolandD
     * @return object List of currency objects
     */
    function getCurrencies() {
	$db = JFactory::getDBO();
	$q = 'SELECT * FROM `#__vm_currency`';
	$db->setQuery($q);
	return $db->loadObjectList();
    }
}
?>