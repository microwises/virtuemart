<?php

/**
 * abstract class for payment plugins
 *
 * @package	VirtueMart
 * @subpackage Plugins
 * @author Valérie Isaksen
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2011 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: vmplugin.php 4599 2011-11-02 18:29:04Z alatak $
 */
// Load the helper functions that are needed by all plugins
if (!class_exists('ShopFunctions'))
require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'shopfunctions.php');
if (!class_exists('DbScheme'))
require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'dbscheme.php');

// Get the plugin library
jimport('joomla.plugin.plugin');

abstract class vmPlugin extends JPlugin {

	// var Must be overriden in every plugin file by adding this code to the constructor:
	// $this->_name = basename(__FILE, '.php');
	// just as note: protected can be accessed only within the class itself and by inherited and parent classes
	protected $_tablename = '';
	protected $_debug = false;

	/**
	 * Constructor
	 *
	 * @param object $subject The object to observe
	 * @param array  $config  An array that holds the plugin configuration
	 * @since 1.5
	 */
	function __construct(& $subject, $config) {

		parent::__construct($subject, $config);

		$lang = JFactory::getLanguage();
		$filename = 'plg_' . $this->_type . '_' . $this->_name;
		$lang->load($filename, JPATH_ADMINISTRATOR);
		if (!class_exists('JParameter')) require(JPATH_VM_LIBRARIES . DS . 'joomla' . DS . 'html' . DS . 'parameter.php' );

	}


	/**
	 * This stores the data of the plugin, attention NOT the configuration of the pluginmethod
	 *
	 * @param array $values array or object with the data to store
	 * @param string $tableName When different then the default of the plugin, provid it here
	 * @param string $tableKey an additionally unique key
	 */
	protected function storePluginInternalData($values, $tableName=0, $tableKey = 0){

		if(!class_exists('VmTable'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmtable.php');

		if(empty($tableName)) $tableName = $this->_tablename;

		$db = JFactory::getDBO();
		$pluginTable = new VmTable($tableName,'id',$db);
		$pluginTable -> loadFields();

		if(!empty($tableKey)) $pluginTable->setUniqueName($tableKey);

		//We should force plugins to be loggable
// 		$pluginTable->setLoggable();

		return $pluginTable->bindChecknStore($values);

	}


	/**
	 * This method writes all  plugin specific data to the plugin's table
	 *
	 * @param array $_values Indexed array in the format 'column_name' => 'value'
	 * @param string $_table Table name
	 * @author Oscar van Eijk
	 *
	protected function writeData($_values, $_table) {
		if (count($_values) == 0) {
			JError::raiseWarning(500, 'writeData got no data to save to ' . $_table);
			return;
		}

		$_cols = array();
		$_vals = array();
		foreach ($_values as $_col => $_val) {
			$_cols[] = "`$_col`";
			$_vals[] = "'$_val'";
		}
		$db = JFactory::getDBO();
		$_q = 'INSERT INTO `' . $_table . '` ('
		. implode(',', $_cols)
		. ') VALUES ('
		. implode(',', $_vals)
		. ')';
		$db->setQuery($_q);
		if (!$db->query()) {
			JError::raiseWarning(500, $db->getErrorMsg());
		}
	}

	/**
	 * This method updates all  plugin specific data to the plugin's table
	 *
	 * @param array $_values Indexed array in the format 'column_name' => 'value'
	 * @param string $_table Table name
	 * @author Valerie Isaksen
	 *
	 *
	protected function updateData($values, $table, $where_key, $where_value) {
		if (count($values) == 0) {
			JError::raiseWarning(500, 'updateData got no data to update to ' . $table);
			return;
		}
		$cols = array();
		$vals = array();
		foreach ($values as $col => $val) {
			$fields[] = "`$col`" . "=" . "'$val'";
		}
		$db = JFactory::getDBO();
		$q = 'UPDATE `' . $table . '` SET ';
		foreach ($values as $key => $value) {
			$q .= $db->getEscaped($key) . '="' . $value . '",';
		}
		$q = substr($q, 0, strlen($q) - 1);
		$q .= ' WHERE `' . $where_key . '` =' . $where_value;


		$db->setQuery($q);
		if (!$db->query()) {
			JError::raiseWarning(500, $db->getErrorMsg());
		}
	}
*/
}
