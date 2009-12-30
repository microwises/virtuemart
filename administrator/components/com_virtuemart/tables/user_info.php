<?php
/**
 * User Info Table
 *
 * @package	VirtueMart
 * @subpackage User
 * @author RickG 
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * User Info table class
 * The class is is used to manage the user_info table.
 *
 * @author 	RickG, RolandD
 * @package	VirtueMart
 */
class TableUser_info extends JTable {
	/**
	 * @author RickG
	 * @param $db A database connector object
	 */
	function __construct($db) {
		/* Make sure the custom fields are added */
		self::addUserFields();
		parent::__construct('#__vm_user_info', 'user_info_id', $db);
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
		$q = "SHOW COLUMNS FROM ".$db->nameQuote('#__vm_user_info');
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
	* @author RickG, RolandD
	* @return boolean True if the table buffer is contains valid data, false otherwise.
	*/
	public function check() {
		$db = JFactory::getDBO();
		
		/* Check if a record exists */
		$q = "SELECT user_info_id
			FROM #__vm_user_info
			WHERE user_id = ".$this->user_id."
			AND address_type = ".$db->Quote($this->address_type)."
			AND address_type_name = ".$db->Quote($this->address_type_name);
		$db->setQuery($q);
		$total = $db->loadResultArray();
		if (count($total) > 0) {
			$this->user_info_id = $total[0];
			return true;
		}
		else {
			$this->user_info_id = md5(uniqid($this->user_id));
			$this->cdate = time();
			return false;
		}
	}
	
	
	

}
?>
