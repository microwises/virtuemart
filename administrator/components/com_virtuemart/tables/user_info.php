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

/**
 * User Info table class
 * The class is is used to manage the user_info table.
 *
 * @package	VirtueMart
 * @author 	RickG, RolandD
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
