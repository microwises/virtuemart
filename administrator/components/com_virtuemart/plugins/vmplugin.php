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

		$this->_tablename = '#__virtuemart_'.$this->_type .'_'. $this->_name;
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

}
