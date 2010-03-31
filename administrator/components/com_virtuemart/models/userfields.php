<?php
/**
*
* Data module for user fields
*
* @package	VirtueMart
* @subpackage Userfields
* @author RolandD
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

// Load the helper
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'paramhelper.php');


/**
 * Model class for user fields
 *
 * @package	VirtueMart
 * @subpackage Userfields
 * @author RolandD
 */
class VirtueMartModelUserfields extends JModel {

	/** @var integer Primary key */
	var $_id;
	/** @var objectlist userfield data */
	var $_data;
	/** @var object paramater parsers */
	var $_params;
	/** @var array type=>fieldname with formfields that are saved as parameters */
	var $reqParam;
	/** @var integer Total number of userfields in the database */
	var $_total;
	/** @var pagination Pagination for userfieldlist */
	var $_pagination;

	/**
	 * Constructor for the userfields model.
	 *
	 * The userfield ID is read and detmimined if it is an array of ids or just one single id.
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

		// Instantiate the Helper class
		$this->_params = new ParamHelper();

		// Get the (array of) order status ID(s)
		$idArray = JRequest::getVar('cid',  0, '', 'array');
		$this->setId((int)$idArray[0]);
		
		// Form fields that must be translated to parameters
		$this->reqParam = array (
			 'age_verification' => 'minimum_age'
			,'euvatid'          => 'shopper_group_id'
			,'webaddress'       => 'webaddresstype'
		);
	}
	
	/**
	* Prepare a user field for database update
	*/
	public function prepareFieldDataSave($fieldType, $fieldName, $value=null) {
		$post = JRequest::get('post');
		switch(strtolower($fieldType)) {
			case 'webaddress':
				if (isset($post[$fieldName."Text"]) && ($post[$fieldName."Text"])) {
					$oValuesArr = array();
					$oValuesArr[0] = str_replace(array('mailto:','http://','https://'),'', $value);
					$oValuesArr[1] = str_replace(array('mailto:','http://','https://'),'', $post[$fieldName."Text"]);
					$value = implode("|*|",$oValuesArr);
				}
				else {
					$value = str_replace(array('mailto:','http://','https://'),'', $value);
				}
				break;
			case 'email':
				$value = str_replace(array('mailto:','http://','https://'),'', $value);
				break;
			case 'multiselect':
			case 'multicheckbox':
			case 'select':
				if (is_array($value)) $value = implode("|*|",$value);
				break;
			case 'age_verification':
				$value = JRequest::getInt('birthday_selector_year')
							.'-'.JRequest::getInt('birthday_selector_month')
							.'-'.JRequest::getInt('birthday_selector_day');
				break;
			default:
				break;
		}
		return $value;
	}

	/**
	 * Resets the userfield id and data
	 */
	function setId($id)
	{
		$this->_id = $id;
		$this->_data = null;
	}

	/**
	 * Loads the pagination for the userfields table
	 *
	 * @return JPagination Pagination for the current list of userfields
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
	 * Gets the total number of userfields
	 *
	 * @return int Total number of userfields in the database
	 */
	function _getTotal()
	{
		if (empty($this->_total)) {
			$query = $this->_getListQuery();
			$this->_total = $this->_getListCount($query);
	}
		return $this->_total;
	}

	/**
	 * Retrieve the detail record for the current $id if the data has not already been loaded.
	 */
	function getUserfield()
	{
		if (empty($this->_data)) {
			$this->_data = $this->getTable('userfields');
			$this->_data->load((int)$this->_id);
		}

		if (!$this->_data) {
			$this->_data = new stdClass();
			$this->_id = 0;
			$this->_data = null;
		}

		// Parse the parameters, if any
		$this->_params->parseParam($this->_data->params);

		return $this->_data;
	}

	/**
	 * Retrieve the value records for the current $id if available for the current type
	 * 
	 * @return array List wil values, or an empty array if none exist
	 */
	function getUserfieldValues()
	{
		$this->_data = $this->getTable('userfields_values');
		if ($this->_id > 0) {
			$query = 'SELECT * FROM `#__vm_userfield_values` WHERE `fieldid` = ' . $this->_id
				. ' ORDER BY `ordering`';
			$_userFieldValues = $this->_getList($query);
			return $_userFieldValues;
		} else {
			return array();
		}
	}
	
	/**
	 * Bind the post data to the userfields table and save it
	 *
	 * @return boolean True is the save was successful, false otherwise.
	 */
	function store()
	{
		$field =& $this->getTable('userfields');

		$data = JRequest::get('post');

		$isNew = ($data['fieldid'] < 1) ? true : false;
		if ($isNew) {
			$reorderRequired = false;
		} else {
			$field->load($data['fieldid']);

			if ($field->ordering == $data['ordering']) {
				$reorderRequired = false;
			} else {
				$reorderRequired = true;
			}
		}

		// Put the parameters, if any, in the correct format
		if (array_key_exists($data['type'], $this->reqParam)) {
			$this->_params->set($this->reqParam[$data['type']], $data[$this->reqParam[$data['type']]]);
			$data['params'] = $this->_params->paramString();
		}

		// Store the fieldvalues, if any, in a correct array
		$fieldValues = $this->postData2FieldValues($data['vNames'], $data['vValues'], $data['fieldid']);

		if (!$field->bind($data)) { // Bind data
			$this->setError($field->getError());
			return false;
		}

		if (!$field->check(count($fieldValues))) { // Perform data checks
			$this->setError($field->getError());
			return false; 
		}

		// if new item, order last in appropriate group
		if ($isNew) {
			$field->ordering = $field->getNextOrder();
		}

		if (($_id = $field->store()) === false) { // Write data to the DB
			$this->setError($field->getError());
			return false;
		}

		if (!$this->storeFieldValues($fieldValues, $_id)) {
			return false;
		}
					
		if ($reorderRequired) {
			$field->reorder();
		}

		return true;
	}

	/**
	 * Bind and write all value records
	 * 
	 * @param array $_values
	 * @param mixed $_id If a new record is being inserted, it contains the fieldid, otherwise the value true
	 * @return boolean
	 */
	private function storeFieldValues($_values, $_id)
	{
		if (count($_values) == 0) {
			return true; //Nothing to do
		}
		$fieldvalue =& $this->getTable('userfields_values');
		for ($i = 0; $i < count($_values); $i++) {
			if (!($_id === true)) { // If $_id is true, it was not a new record
				$_values[$i]['fieldid'] = $_id;
			}

			if (!$fieldvalue->bind($_values[$i])) { // Bind data
				$this->setError($fieldvalue->getError());
				return false;
			}

			if (!$fieldvalue->check()) { // Perform data checks
				$this->setError($fieldvalue->getError());
				return false;
			}

			if (!$fieldvalue->store()) { // Write data to the DB
				$this->setError($fieldvalue->getError());
				return false;
			}
		}
		return true;
	}

	/**
	 * Translate arrays form userfield_values to the format expected by the table class.
	 * 
	 * @param array $titles List of titles from the formdata
	 * @param array $values List of values from the formdata
	 * @param int $fieldid ID of the userfield to relate
	 * @return array Data to bind to the userfield_values table
	 */
	private function postData2FieldValues($titles, $values, $fieldid)
	{
		$_values = array();
		if (is_array($titles) && is_array($values)) {
			for ($i=0; $i < count($titles) ;$i++) {
				if (empty($titles[$i])) {
					continue; // Ignore empty fields
				}
				$_values[] = array(
					 'fieldid'    => $fieldid
					,'fieldtitle' => $titles[$i]
					,'fieldvalue' => $values[$i]
					,'ordering'   => $i
				);
			}
		}
		return $_values;
	}

	/**
	 * Delete all record ids selected
	 *
	 * @return boolean True is the delete was successful, false otherwise.
	 */
	function delete()
	{
		$fieldIds = JRequest::getVar('cid',  0, '', 'array');
		$field =& $this->getTable('userfields');
		$value =& $this->getTable('userfields_values');
		
		foreach($fieldIds as $fieldId) {
			if (!$field->delete($fieldId)) {
				$this->setError($field->getError());
				return false;
			}
			if (!$value->delete($fieldId)) {
				$this->setError($field->getError());
				return false;
			}
		}
		
		return true;
	}

	/**
	 * Retrieve a list of userfields from the database.
	 *
	 * @return object List of userfield objects
	 */
	function getUserfieldsList()
	{
		if (!$this->_data) {
			$query = $this->_getListQuery();
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}
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
	 * If a filter was set, get the SQL WHERE clase
	 *
	 * @return string text to add to the SQL statement
	 */
	function _getFilter()
	{
		$db = JFactory::getDBO();
		if (JRequest::getVar('search', false)) {
			return (' WHERE `name` LIKE ' .$db->Quote('%'.JRequest::getVar('search').'%'));
		}
		return ('');
	}

	/**
	 * Build the query to list all Userfields
	 *
	 * @return string SQL query statement
	 */
	function _getListQuery ()
	{
		$query = 'SELECT * FROM `#__vm_userfield` ';
		$query .= $this->_getFilter();
		$query .= $this->_getOrdering();
		return ($query);
	}

	/**
	 * Change the ordering of an Userfield
	 *
	 * @return boolean True on success
	 */
	function move($direction)
	{
		$table =& $this->getTable('userfields');
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
	 * Reorder the Userfields
	 *
	 * @return boolean True on success
	 */
	function saveorder($cid = array(), $order)
	{
		$table =& $this->getTable('userfields');

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

	/**
	 * Switch a toggleable field on or off
	 * 
	 * @param $field string Database fieldname to toggle
	 * @param $id array list of primary keys to toggle
	 * @param $value boolean Value to set
	 * @return boolean Result
	 */
	function toggle($field, $id = array(), $value = 1)
	{
		if (count( $id ))
		{
			JArrayHelper::toInteger($id);
			$ids = implode( ',', $id );

			$query = 'UPDATE `#__vm_userfield`'
				. ' SET `' . $field . '` = '.(int) $value
				. ' WHERE fieldid IN ( '.$ids.' )'
			;
			$this->_db->setQuery( $query );
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
		return true;
	}
}

// No closing tag