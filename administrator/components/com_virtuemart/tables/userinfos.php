<?php
/**
*
* User Info Table
*
* @package	VirtueMart
* @subpackage User
* @author 	RickG, RolandD
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

if(!class_exists('VmTable'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmtable.php');

/**
 * User Info table class
 * The class is is used to manage the user_info table.
 *
 * @package	VirtueMart
 * @author 	RickG, RolandD, Max Milbers
 */
class TableUserinfos extends VmTable {


	/** @var int Primary key */
	var $virtuemart_user_id = 0;

	/** @var int hidden userkey */
	var $virtuemart_userinfo_id = 0;
	var $virtuemart_state_id = '';
	var $virtuemart_country_id = '';

//	var $user_is_vendor = 0;
	var $address_type = '';
	var $address_type_name;
	var $company = '';
	var $title ='';
 	var $last_name = '';
	var $first_name = '';
	var $middle_name = '';
	var $phone_1 = '';
	var $phone_2 = '';
	var $fax = '';
	var $address_1 = '';
	var $address_2 = '';
	var $city = '';

	var $zip = '';
	var $extra_field_1 = '';
	var $extra_field_2 = '';
	var $extra_field_3 = '';
	var $extra_field_4 = '';
	var $extra_field_5 = '';

	/**
	 * @author RickG
	 * @param $db A database connector object
	 */
	function __construct($db) {
		/* Make sure the custom fields are added */
		self::addUserFields();
		parent::__construct('#__virtuemart_userinfos', 'virtuemart_userinfo_id', $db);

		$this->setObligatoryKeys('virtuemart_user_id','COM_VIRTUEMART_USERINFO_RECORDS_MUST_CONTAIN_USER_ID');

		$this->setLoggable();
	}

	/**
	* Add the user fields to the table to make sure all gets updated
	*
	* @author RolandD
	*/
	private function addUserFields() {
		$db = JFactory::getDBO();
		/* Collect the table names for the product types */
		$customfields = array();
		$q = "SHOW COLUMNS FROM ".$db->nameQuote('#__virtuemart_userinfos');
		$db->setQuery($q);
		$fields = $db->loadObjectList();
		if (count($fields) > 0) {
			foreach ($fields as $key => $field) {
				$customfields[$field->Field] = $field->Default;
			}
			$this->setProperties($customfields);
		}
	}

	/**
	 * Add, change or drop userfields
	 *
	 * @param string $_act Action: ADD, DROP or CHANGE (synonyms available, see the switch cases)
	 * @param string $_col Column name
	 * @param string $_type Fieldtype
	 * @return boolean True on success
	 * @author Oscar van Eijk
	 */
	function _modifyColumn ($_act, $_col, $_type = '')
	{
		$_sql = "ALTER TABLE `#__virtuemart_userinfos` ";

		$_check_act = strtoupper(substr($_act, 0, 3));
		switch ($_check_act) {
			case 'ADD':
			case 'CRE': // Create
				$_sql .= "ADD $_col $_type ";
				break;
			case 'DRO': // Drop
			case 'DEL': // Delete
				$_sql .= "DROP $_col ";
				break;
			case 'MOD': // Modify
			case 'UPD': // Update
			case 'CHA': // Change
				$_sql .= "CHANGE $_col $_col $_type ";
				break;
		}

		$this->_db->setQuery($_sql);
		$this->_db->query();
		if ($this->_db->getErrorNum() != 0) {
			$this->setError(get_class( $this ).'::modify table - '.$this->_db->getErrorMsg());
			return false;
		}
		return true;
	}

	/**
	* Stores/Updates a tax rate
	*
	*/
	public function store() {
		$k = $this->check();

		if ($k) $ret = $this->_db->updateObject( $this->_tbl, $this, $this->_tbl_key, false );
		else $ret = $this->_db->insertObject( $this->_tbl, $this, $this->_tbl_key);

		if (!$ret){
			$this->setError(get_class( $this ).'::store failed - '.$this->_db->getErrorMsg());
			return false;
		}
		else return true;
	}

	/**
	* Validates the user info record fields.
	*
	* @author RickG, RolandD, Max Milbers
	* @return boolean True if the table buffer is contains valid data, false otherwise.
	*/
	public function check(){

//		$date = JFactory::getDate();
//		$today = $date->toMySQL();
//		if(empty($this->created_on)){
//			$this->created_on = $today;
//		}
//     	$this->modified_on = $today;

		if (!empty($this->virtuemart_userinfo_id)) {
			return parent::check();
		}

		/* Check if a record exists */
		$q = "SELECT virtuemart_userinfo_id
			FROM #__virtuemart_userinfos
			WHERE virtuemart_user_id = ".$this->virtuemart_user_id."
			AND address_type = ".$this->_db->Quote($this->address_type)."
			AND address_type_name = ".$this->_db->Quote($this->address_type_name);
		$this->_db->setQuery($q);
		$total = $this->_db->loadResultArray();

		if (count($total) > 0) {
			$this->virtuemart_userinfo_id = $total[0];
			return parent::check();
		} else {
			$this->virtuemart_userinfo_id = md5(uniqid($this->virtuemart_user_id));
//			$this->created_on = time();
			return false;
		}


	}

	/**
	 * Overloaded delete() to delete a list of virtuemart_userinfo_id's based on the user id
	 * @var mixed id
	 * @return boolean True on success
	 * @author Oscar van Eijk
	 */
	function delete($id)
	{
		// TODO If $id is not numeric, assume it's a virtuemart_userinfo_id. Validate if this is safe enough
		if (!is_numeric($id)) {
			return (parent::delete($id));
		}
		// Implicit else
		$this->_db->setQuery('DELETE from `#__virtuemart_userinfos` WHERE `virtuemart_user_id` = ' . $id);
		if ($this->_db->query() === false) {
			$this->setError($this->_db->getError());
			return false;
		}
		return true;
	}

}

// No Closing tag
