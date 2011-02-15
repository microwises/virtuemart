<?php
/**
*
* Data module for shop countries
*
* @package	VirtueMart
* @subpackage Country
* @author RickG, Max Milbers, jseros
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
 * Model class for shop countries
 *
 * @package	VirtueMart
 * @subpackage State
 * @author RickG, Max Milbers
 */
class VirtueMartModelState extends JModel {

	/** @var array Array of Primary keys */
    var $_cid;
	/** @var integer Primary key */
    var $_id;
	/** @var objectlist State data */
    var $_data;
//	/** @var integer Total number of state in the database */
//	var $_total;
	/** @var pagination Pagination for state list */
	var $_pagination;


    /**
     * Constructor for the state model.
     *
     * The state id is read and detmimined if it is an array of ids or just one single id.
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

        // Get the state id or array of ids.
		$idArray = JRequest::getVar('cid',  0, '', 'array');
    	$this->setId((int)$idArray[0]);

    }


    /**
     * Resets the state id and data
     *
     * @author RickG
     */
    function setId($id)
    {
        $this->_id = $id;
        $this->_data = null;
    }


	/**
	 * Loads the pagination for the state table
	 *
     * @author RickG
     * @return JPagination Pagination for the current list of countries
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
	 * Gets the total number of countries
	 *
     * @author RickG
	 * @return int Total number of countries in the database
	 */
	function _getTotal()
	{
    	if (empty($this->_total)) {
			$query = 'SELECT `state_id` FROM `#__vm_state`';
			$this->_total = $this->_getListCount($query);
        }
        return $this->_total;
    }


    /**
     * Retrieve the detail record for the current $id if the data has not already been loaded.
     *
     * Renamed to getSingleState to avoid overwriting by jseros
     *
     * @author Max Milbers
     */
	function getSingleState(){

		if (empty($this->_data)) {
   			$this->_data = $this->getTable();
   			$this->_data->load((int)$this->_id);
  		}
  		
		return $this->_data;
	}


    /**
     * Retreive a state record given a state code.
     *
     * @author RickG
     * @param string $code State code to lookup
     * @return object State object from database
     */
    function &getStateByCode($code)
    {
		$db =& JFactory::getDBO();

		$stateCodeLength = strlen($code);
		switch ($stateCodeLength) {
			case 2:
				$stateCodeFieldname = 'state_2_code';
				break;
			case 3:
				$stateCodeFieldname = 'state_3_code';
				break;
			default:
				return false;
		}

		$query = 'SELECT *';
		$query .= ' FROM `#__vm_state`';
		$query .= ' WHERE `' . $stateCodeFieldname . '` = ' . (int)$code;
		$db->setQuery($query);

        return $db->loadObject();
	}


	/**
	 * Bind the post data to the state table and save it
     *
     * @author RickG
     * @return boolean True is the save was successful, false otherwise.
	 */
    function store()
	{
		$table =& $this->getTable('state');

		$data = JRequest::get( 'post' );
		// Bind the form fields to the state table
		if (!$table->bind($data)) {
			$this->setError($this->getError());
			return false;
		}

		// Make sure the state record is valid
		if (!$table->check()) {
			$this->setError($this->getError());
			return false;
		}

		// Save the state record to the database
		if (!$table->store()) {
			$this->setError($this->getError());
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
	function delete()
	{
		$stateIds = JRequest::getVar('cid',  0, '', 'array');
    	$table =& $this->getTable('state');

    	foreach($stateIds as $stateId) {
        	if (!$table->delete($stateId)) {
            	$this->setError($table->getError());
            	return false;
        	}
    	}

    	return true;
	}

	/**
	 * Publish/Unpublish all the ids selected
     *
     * @author Max Milbers
     * @param boolean $publishId True is the ids should be published, false otherwise.
     * @return boolean True is the delete was successful, false otherwise.
     */
	function publish($publishId = false)
	{
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'modelfunctions.php');
		return modelfunctions::publish('cid','state',$publishId);
	}


	/**
	 * Retireve a list of countries from the database.
	 *
     * @author RickG, Max Milbers
	 * @return object List of state objects
	 */
	public function getStates($countryId)
	{
		$query = 'SELECT * FROM `#__vm_state`  WHERE `country_id`= "'.$countryId.'" ';
		$query .= 'ORDER BY `#__vm_state`.`state_name`';
		$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		return $this->_data;
	}
	
	/**
	 * Tests if a state and country fits together and if they are published
	 * 
	 * @author Max Milbers
	 * @return String Attention, this function gives a 0=false back in case of success
	 */
	public function testStateCountry($countryId,$stateId)
	{
		//Test if id is published
					
		$db =& JFactory::getDBO();
		$q = 'SELECT * FROM `#__vm_country` WHERE `country_id`= "'.$countryId.'" AND `published`="1"';
		$db->setQuery($q);
		if($db->loadResult()){
			//Test if country has states 
			$q = 'SELECT * FROM `#__vm_state`  WHERE `country_id`= "'.$countryId.'" ';
			$db->setQuery($q);
			if($db->loadResult()){
				//Test if state_id fits to country_id
				$q = 'SELECT * FROM `#__vm_state` WHERE `country_id`= "'.$countryId.'" AND `state_id`="'.$stateId.'" and `published`="1"';
				$db->setQuery($q);
				if($db->loadResult()){
					return 0;
				} else {
					return 'state_id';
				}
			} else {
				return 0;
			}

		} else {
			return 'country_id';
		}
	}
	
	/**
	 * Retireve a full list of countries from the database.
	 *
     * @author jseros
	 * @return object List of state objects
	 */
	public function getFullStates($countryId)
	{
		$this->setState('limitstart', 0);
		$this->setState('limit', 5000);
		return $this->getStates($countryId);
	}
}
// pure php no closing tag