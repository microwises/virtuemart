<?php
/**
 * Configuration helper class
 *
 * This class provides some functions that are used throughout the VirtueMart shop to access confgiuration values.
 *
 * @package	VirtueMart
 * @subpackage Helpers
 * @author RickG
 * @author Max Milbers
 * @copyright Copyright (c) 2004-2008 Soeren Eberhardt-Biermann, 2009 VirtueMart Team. All rights reserved.
 */
defined('_JEXEC') or die('Restricted access');

/**
 *
 * We need this extra paths to have always the correct path undependent by loaded application, module or plugin
 * Plugin, module developers must always include this config at start of their application
 *  $vmConfig= new VmConfig();
 *  $vmConfig -> loadConfig();
 *  $vmConfig -> jQuery();
 *  Then always use the defined paths below to ensure future stability
 */
define( 'JPATH_VM_SITE', JPATH_ROOT.DS.'components'.DS.'com_virtuemart' );
define( 'JPATH_VM_ADMINISTRATOR', JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart' );

require(JPATH_VM_ADMINISTRATOR.DS.'version.php');


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
		$query = "SELECT `config` FROM `#__vm_config` WHERE `config_id` = '1'";
		$db->setQuery($query);
		$config = $db->loadResult();

		$session = JFactory::getSession();
		$session->clear('vmconfig');
		$session->set('vmconfig', $config,'vm');
	}


	/**
	 * Find the configuration value for a given key
	 *
	 * @author RickG
	 * @param string $key Key name to lookup
	 * @return Value for the given key name
	 */
	function get($key = '', $default='')
	{
		$value = '';
		if ($key) {
			jimport('joomla.html.parameter');
			$session = JFactory::getSession();
			$config = $session->get('vmconfig', '','vm');
			if (!$config) {
				VmConfig::loadConfig();
				$config = $session->get('vmconfig', '','vm');
			}

			if ($config) {
				$params = new JParameter($config);
				$value = $params->get($key);
			}
			else {
			    $params = new JParameter('');
			    $value = '';
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
		// Get the installed version from the wmVersion class.

		return vmVersion::$RELEASE;
	}

	/**
	 * Compares two "A PHP standardized" version number against the current Joomla! version
	 * This function needs at least 3 digits, like 1.5.0,
	 * We can use it like isAtLeastVersion('1.6.0')
	 *
	 * This function returns a true if the version is equal or higher
	 * @return boolean
	 * @see http://www.php.net/version_compare
	 */
	function isAtLeastVersion ( $minimum ) {
		return (version_compare( JVERSION, $minimum, 'ge' ));
	}

	/**
	 * Return if the used joomla function is j15
	 */
	function isJ15(){
		return (strpos(JVERSION,'1.5') === 0);
	}
	/**
	 * ADD some javascript if needed
	 * Prevent duplicate load of script
	 * @ Author KOHL Patrick
	 */
		function jQuery()
	{
		static $jquery;
		// If exist exit
		if ($jquery) return;
		JHTML::script('jquery.js', 'components/com_virtuemart/assets/js/', false);
		$loaded = true;
		return;
	}
	// Virtuemart price script
	function jPrice()
	{
		static $jPrice;
		// If exist exit
		if ($jPrice) return;
		JHTML::script('vmprices.js', 'components/com_virtuemart/assets/js/', false);
		$jPrice = true;
		return;
	}
	// Virtuemart main Js script
	function jVm()
	{
		static $jVm;
		// If exist exit
		if ($jVm) return;
		JHTML::script('vm.js', 'components/com_virtuemart/assets/js/', false);
		$jVm = true;
		return;
	}
	// Virtuemart Site Js script
	function jSite()
	{
		static $jSite;
		// If exist exit
		if ($jSite) return;
		JHTML::script('vmsite.js', 'components/com_virtuemart/assets/js/', false);
		$jSite = true;
		return;
	}

	/*	function cssSite()
	{
		static $jSite;
		// If exist exit
		if ($jSite) return;
		JHTML::script('vmsite.js', 'components/com_virtuemart/assets/js/', false);
		$jSite = true;
		return;
	}*/

	/**
	 * ADD some CSS if needed
	 * Prevent duplicate load of CSS stylesheet
	 * @ Author KOHL Patrick
	 */

	function cssSite()
	{
		static $cssSite;
		// If exist exit
		if ($cssSite) return;
		JHTML::stylesheet('vmsite.css', 'components/com_virtuemart/assets/css/', false);
		$cssSite = true;
		return;
	}
}
// pure php no closing tag
