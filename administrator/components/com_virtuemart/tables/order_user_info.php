<?php
/**
 *
 * Order table holding user info
 *
 * @package	VirtueMart
 * @subpackage Orders
 * @author 	Oscar van Eijk
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

class TableOrder_user_info extends JTable {

	/**
	 * Constructor
	 */
	function __construct(&$_db)
	{
		self::loadFields($_db);
		parent::__construct('#__virtuemart_order_userinfos', 'virtuemart_order_userinfo_id', $_db);
	}

	/**
	 * Load the fieldlist
	 */
	private function loadFields(&$_db)
	{
		$_fieldlist = array();
		$_q = "SHOW COLUMNS FROM `#__virtuemart_order_userinfos`";
		$_db->setQuery($_q);
		$_fields = $_db->loadObjectList();
		if (count($_fields) > 0) {
			foreach ($_fields as $key => $_f) {
				$_fieldlist[$_f->Field] = $_f->Default;
			}
			$this->setProperties($_fieldlist);
		}
	}

	/**
	 * Add, change or drop userfields
	 *
	 * @param string $_act Action: ADD, DROP or CHANGE (synonyms available, see the switch cases)
	 * @param string $_col Column name
	 * @param string $_type Fieldtype
	 * @return boolean True on success
	 */
	function _modifyColumn ($_act, $_col, $_type = '')
	{
		$_sql = "ALTER TABLE `#__virtuemart_order_userinfos` ";

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
}
// No closing tag