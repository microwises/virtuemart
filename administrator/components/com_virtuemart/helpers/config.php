<?php
/**
 * Configuration helper class
 *
 * This class provides some functions that are used throughout the VirtueMart shop to access confgiuration values.
 *
 * @package	VirtueMart
 * @subpackage Helpers
 * @author RickG
 * @copyright Copyright (c) 2004-2008 Soeren Eberhardt-Biermann, 2009 VirtueMart Team. All rights reserved.
 */
defined('_JEXEC') or die('Restricted access');

class VmConfig 
{
	/**
	 * Load the configuration values from the database into a session variable.
	 * This step is done to prevent accessing the database for every configuration variable lookup.
	 *
	 * @author RickG
	 */
	function loadConfig() {  
		$db = JFactory::getDBO();
		$query = "SELECT `config` FROM `#__vm_config` WHERE `config_id` = 1";
		$db->setQuery($query);
		$config = $db->loadResult();
	
		$session = JFactory::getSession();
		$session->set("vmconfig", $config);
	}
	
	
	/**
	 * Find the configuration value for a given key
	 *
	 * @author RickG
	 * @param string $key Key name to lookup
	 * @return Value for the given key name
	 */	
	function getVar($key = '', $default='')
	{
		$value = '';
		if ($key) {
			$session = JFactory::getSession();
			$config = $session->get('vmconfig', '');
			
			if (!$config) { 
				VmConfig::loadConfig();
				$config = $session->get('vmconfig', '');
			}			
				
			if ($config) {
				$params = new JParameter($config);
				$value = $params->get($key);				
			}

			if ($value == '') {
			    $params->set($key, $default);
			    $value = $default;
			}
		}	
		
		return $value;
	}
	
	
	/**
	 * Find the currenlty installed version
	 *
	 * @author RickG
	 * @param boolean $includeDevStatus True to include the development status
	 * @return String of the currently installed version
	 */	
	function getInstalledVersion($includeDevStatus=false)
	{
		// Get the installed version from the XML file
		$xmlParser = JFactory::getXMLParser('Simple');
		$xmlParser->loadFile(JPATH_COMPONENT_ADMINISTRATOR.DS.'virtuemart.xml');
		$version = $xmlParser->document->getElementByPath('version')->data();
		if ($includeDevStatus) {
			$version .= ' ' . $xmlParser->document->getElementByPath('version')->attributes('status');
		}
		
		return $version;	
	}	
	
}
?>
