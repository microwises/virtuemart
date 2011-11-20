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
	protected $_loggable = false;
	/**
	 * Constructor
	 *
	 * @param object $subject The object to observe
	 * @param array  $config  An array that holds the plugin configuration
	 * @since 1.5
	 */
	function __construct(& $subject, $config) {

		parent::__construct($subject, $config);

		$this->_psType = substr($this->_type, 2);

		$lang = JFactory::getLanguage();
		$filename = 'plg_' . $this->_type . '_' . $this->_name;
		$lang->load($filename, JPATH_ADMINISTRATOR);
		if (!class_exists('JParameter')) require(JPATH_VM_LIBRARIES . DS . 'joomla' . DS . 'html' . DS . 'parameter.php' );

		$this->_tablename = '#__virtuemart_'.$this->_psType .'_plg_'. $this->_name;
		$this->_tableChecked = false;
	}

/**
	 * This method checks if the selected method matches the current plugin
	 * @param int $id The method ID
	 * @param pssType : shipment or payment
	 * @author Oscar van Eijk
	 * @return True if the calling plugin has the given method ID, and psType
	 *
	 */
	final protected function selectedThis($id,$psType) {
		$db = JFactory::getDBO();

		if (VmConfig::isJ15()) {
			$q = 'SELECT COUNT(*) AS c FROM #__virtuemart_'.$psType.'methods AS vm,
			#__plugins AS j WHERE vm.virtuemart_'.$psType.'method_id = "'.$id.'"
			AND   vm.'.$psType.'_jplugin_id = j.id
			AND   j.element = "'.$this->_name.'"';
		} else {
			$q = 'SELECT COUNT(*) AS c FROM #__virtuemart_'.$psType.'methods AS vm
			, #__extensions AS j WHERE j.`folder` = "'.$this->_type.'" AND vm.virtuemart_'.$psType.'method_id = "'.$id.'"
				AND   vm.'.$psType.'_jplugin_id = j.extension_id AND   j.element = "'.$this->_name.'"';
		}

		$db->setQuery($q);
		return $db->loadResult(); // TODO Error check
	}

	/**
	 * This stores the data of the plugin, attention NOT the configuration of the pluginmethod
	 *
	 * @param array $values array or object with the data to store
	 * @param string $tableName When different then the default of the plugin, provid it here
	 * @param string $tableKey an additionally unique key
	 */
	protected function storePluginInternalData(&$values, $primaryKey='', $tableName=0){

		if(!class_exists('VmTableData'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmtabledata.php');
		if(empty($tableName)) $tableName = $this->_tablename;

		$db = JFactory::getDBO();
		$pluginTable = new VmTableData($tableName,'id',$db);
		$pluginTable -> loadFields();

		if(empty($primaryKey)) $primaryKey = $this->_tablepkey;
		$pluginTable->setPrimaryKey($primaryKey);

		if($this->_loggable)	$pluginTable->setLoggable();

		$pluginTable->bindChecknStore($values);

		return $values;

	}

	protected function getPluginInternalData($id, $primaryKey=0, $tableName=0){

		if(!class_exists('VmTableData'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmtabledata.php');
		if(empty($tableName)) $tableName = $this->_tablename;

		$db = JFactory::getDBO();
		$pluginTable = new VmTableData($tableName,'id',$db);
		$pluginTable -> loadFields();

		if(empty($primaryKey)) $primaryKey = $this->_tablepkey;
		$pluginTable->setPrimaryKey($primaryKey);

		if($this->_loggable)	$pluginTable->setLoggable();

		return $pluginTable->load($id);
	}

}
