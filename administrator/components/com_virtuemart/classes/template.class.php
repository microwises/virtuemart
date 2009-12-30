<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: template.class.php 1755 2009-05-01 22:45:17Z rolandd $
* @package VirtueMart
* @subpackage core
* @copyright Copyright (c) 2003 Brian E. Lozier (brian@massassi.net)
* @copyright Copyright (C) 2007 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*
* set_vars() method contributed by Ricardo Garcia (Thanks!)
*
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to
* deal in the Software without restriction, including without limitation the
* rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
* sell copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in
* all copies or substantial portions of the Software.
*/
require_once( CLASSPATH . 'parameters.class.php');

class vmTemplate {
	var $vars; /// Holds all the template variables
	var $path; /// Path to the templates
	/**
	 * Stores the theme configuration
	 *
	 * @var $config
	 */
	var $config;
	var $cache_filename;
	var $expire;
	var $cached = false;
	
	/**
    * Constructor.
    *
    * @param string $path path to template files
    * @param string $cache_id unique cache identifier
    * @param int $expire number of seconds the cache will live
    *
    * @return void
    */
	function vmTemplate($path='', $expire = 0 ) {
		global $mosConfig_absolute_path, $mosConfig_cachepath, $mosConfig_cachetime;
			
		$this->path = empty($path) ?  VM_THEMEPATH.'templates/' : $path;
		$this->default_path = $mosConfig_absolute_path.'/components/'.VM_COMPONENT_NAME.'/themes/default/templates/';
		
		$globalsArray = vmGetGlobalsArray();
		foreach( $globalsArray as $global ) {
			global $$global;
			$this->vars[$global] = $GLOBALS[$global];
		}
		$this->cache_filename = $mosConfig_cachepath.'/' . $GLOBALS['cache_id'];
		$this->expire   = $expire == 0 ? $mosConfig_cachetime : $expire;
		
		// the theme configuration needs to be available to the templates! (so you can use $this->get_cfg('showManufacturerLink') for example )
		if( empty( $GLOBALS['vmThemeConfig'] ) || !empty( $_REQUEST['vmThemeConfig'])) {
			$GLOBALS['vmThemeConfig'] =& new vmParameters( @file_get_contents(VM_THEMEPATH.'theme.config.php'), VM_THEMEPATH.'theme.xml', 'theme');

		}
		$this->config =& $GLOBALS['vmThemeConfig'];
		
	}
	/**
	 * Returns a unique Cache ID
	 * @static 
	 * @return string
	 */
	function getCacheId() {
		global $modulename, $pagename, $product_id, $category_id, $manufacturer_id, $auth, $limitstart, $limit;
		return 'vm_' . @md5( 'vm_' . @md5( $modulename. $pagename. $product_id. $category_id .$manufacturer_id. $auth["shopper_group_id"]. $limitstart. $limit. @$_REQUEST['orderby']. @$_REQUEST['DescOrderBy'] ). @$_REQUEST['orderby']. @$_REQUEST['DescOrderBy'] );
	}
	/**
	 * @static 
	 *
	 * @return vmTheme
	 */
	function getInstance() {
		return new $GLOBALS['VM_THEMECLASS']();
	}
	/**
    * Test to see whether the currently loaded cache_id has a valid
    * corresponding cache file.
    * @param string the name of the template file (relative path to the theme dir)
    * @return array
    */
	function get_cached( $templateFile ) {
		global $mosConfig_cachepath;
		
		// Passed a cache_id?
		if(!$GLOBALS['cache_id']) {
			$GLOBALS['cache_id'] = $this->getCacheId();
		}
		
		$returnArr['cache_file_id'] = md5($templateFile . $GLOBALS['cache_id']);
		$returnArr['cache_file_name'] = $mosConfig_cachepath.'/'.$returnArr['cache_file_id'];
		// Cache file exists?
		if(!@file_exists($returnArr['cache_file_name'])) {
			$returnArr['isCached'] = false;
			return $returnArr;
		}
		if( @filesize($returnArr['cache_file_name']) == 0) {
			$returnArr['isCached'] = false;
			return $returnArr;
		}

		// Can get the time of the file?
		if(!($mtime = filemtime($returnArr['cache_file_name']))) {
			$returnArr['isCached'] = false;
			return $returnArr;
		}

		// Cache expired?
		if(($mtime + $this->expire) < time()) {
			@unlink($returnArr['cache_file_name']);
			$returnArr['isCached'] = false;
			return $returnArr;
		}
		$returnArr['isCached'] = true;
		return $returnArr;
		
	}
	
	/**
    * Set the path to the template files.
    *
    * @param string $path path to template files
    *
    * @return void
    */
	function set_path($path) {
		$this->path = $path;
	}

	/**
    * Set a template variable.
    *
    * @param string $name name of the variable to set
    * @param mixed $value the value of the variable
    *
    * @return void
    */
	function set($name, $value) {
		$this->vars[$name] = $value;
	}

	/**
    * Set a bunch of variables at once using an associative array.
    *
    * @param array $vars array of vars to set
    * @param bool $clear whether to completely overwrite the existing vars
    *
    * @return void
    */
	function set_vars($vars, $clear = false) {
		if($clear) {
			$this->vars = $vars;
		}
		else {
			if(is_array($vars)) {
				$this->vars = array_merge($this->vars, $vars);
			}
		}
	}
	/**
	 * Returns the value of a configuration parameter of this theme
	 *
	 * @param string $var
	 * @param mixed $default
	 * @return mixed
	 */
	function get_cfg( $var, $default='' ) {

		return $this->config->get( $var, $default );
	}
	
	/**
	 * Sets the configuration parameter of this theme
	 *
	 * @param string $var
	 * @param mixed $value
	 */
	function set_cfg( $var, $value ) {
		if( is_a( $this->config, 'vmParameters' )) {
			$this->config->set( $var, $value );
		}
	}
	
	/**
    * Open, parse, and return the template file.
    *
    * @param string string the template file name
    *
    * @return string
    */
	function fetch($file) {
		extract($this->vars);          // Extract the vars to local namespace
		ob_start();                    // Start output buffering
		if( file_exists( $this->path . $file ) ) {
			include($this->path . $file);  // Include the file
		} elseif( file_exists( $this->default_path . $file ) ) {
			include( $this->default_path . $file );
		}
		$contents = ob_get_contents(); // Get the contents of the buffer
		ob_end_clean();                // End buffering and discard
		return $contents;              // Return the contents
	}

	/**
    * This function returns a cached copy of a template (if it exists),
    * otherwise, it parses it as normal and caches the content.
    *
    * @param $file string the template file
    *
    * @return string
    */
	function fetch_cache($file) {
		global $mosConfig_caching;
		
		$cacheFileArr = $this->get_cached( $file );
		
		if( $cacheFileArr['isCached'] !== false ) {
			
			$contents = file_get_contents($cacheFileArr['cache_file_name']);
			return $contents;
		}
		else {
			$contents = $this->fetch($file);
			if( $mosConfig_caching ) {
				// Write the cache
				if( !file_put_contents($cacheFileArr['cache_file_name'], $contents ) ) {
					$vmLogger->crit('Failed to write to Cache!');
				}				
			}
			return $contents;
		}
	}
}


?>