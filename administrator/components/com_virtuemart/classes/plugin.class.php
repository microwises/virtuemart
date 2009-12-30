<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
 * @version		$Id: event.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		classes
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @copyright	Copyright (C) 2008 soeren -. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

/**
 *
 * @abstract
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	Event
 * @since		1.5
 */
class vmEvent {
	
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @since 1.2.0
	 */
	function vmEvent( & $subject ) {
		if( !is_object( $subject)) return;
		// Register the observer ($this) so we can be notified
		$subject->attach( $this ) ;
		
		// Set the subject to observe
		$this->_subject = & $subject ;
	}
	
	/**
	 * Method to trigger events
	 *
	 * @access public
	 * @param array Arguments
	 * @return mixed Routine return value
	 * @since 1.5
	 */
	function update( & $args ) {
		/*
		 * First lets get the event from the argument array.  Next we will unset the
		 * event argument as it has no bearing on the method to handle the event.
		 */
		$event = $args['event'] ;
		unset( $args['event'] ) ;
		
		/*
		 * If the method to handle an event exists, call it and return its return
		 * value.  If it does not exist, return null.
		 */
		if( method_exists( $this, $event ) ) {
			return call_user_func_array( array( $this , $event ), $args ) ;
		} else {
			return null ;
		}
	}
}
/**
 * vmPlugin Class
 *
 * @abstract
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	Plugin
 * @since		1.5
 */
class vmPlugin extends vmEvent {
	/**
	 * The plugin ID
	 *
	 * @var		int
	 * @access	protected
	 */
	var $_id = null ;
	/**
	 * A vmParameters object holding the parameters for the plugin
	 *
	 * @var		A JParameter object
	 * @access	public
	 * @since	1.5
	 */
	var $params = null ;
	
	/**
	 * The name of the plugin
	 *
	 * @var		sring
	 * @access	protected
	 */
	var $_name = null ;
	
	/**
	 * The plugin type
	 *
	 * @var		string
	 * @access	protected
	 */
	var $_type = null ;
	
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @param array  $config  An optional associative array of configuration settings.
	 * Recognized key values include 'name', 'group', 'params'
	 * (this list is not meant to be comprehensive).
	 * @since 1.5
	 */
	function vmPlugin( & $subject, $config = array() ) {
		parent::__construct( $subject ) ;
	}
	
	/**
	 * Constructor
	 */
	function __construct( & $subject, $config = array() ) {
		//Set the parameters
		if( isset( $config['params'] ) ) {
			
			if( is_a( $config['params'], 'vmParameters' ) ) {
				$this->params = $config['params'] ;
			} else {
				$this->params = new vmParameters( $config['params'] ) ;
			}
		}
			if( isset( $config['id'] ) ) {
			$this->_id = $config['id'] ;
		}
		if( isset( $config['name'] ) ) {
			$this->_name = $config['name'] ;
		}
		
		if( isset( $config['type'] ) ) {
			$this->_type = $config['type'] ;
		}
		
		parent::__construct( $subject ) ;
	}
}

/**
 * Plugin helper class
 *
 * @static
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	Plugin
 * @since		1.2.0
 */
class vmPluginHelper {
	/**
	 * Get the plugin data of a specific type if no specific plugin is specified
	 * otherwise only the specific plugin data is returned
	 *
	 * @access public
	 * @param string 	$type 	The plugin type, relates to the sub-directory in the plugins directory
	 * @param string 	$plugin	The plugin name
	 * @param string 	$condition	A SQL condition to filter plugins
	 * @return mixed 	An array of plugin data objects, or a plugin data object
	 */
	function &getPlugin( $type, $plugin = null, $condition = '' ) {
		$result = array() ;
		
		$plugins = vmPluginHelper::_load($type, $condition) ;
		
		$total = count( $plugins ) ;
		for( $i = 0 ; $i < $total ; $i ++ ) {
			if( is_null( $plugin ) ) {
				if( $plugins[$i]->type == $type ) {
					$result[] = $plugins[$i] ;
				}
			} else {
				if( $plugins[$i]->type == $type && $plugins[$i]->name == $plugin ) {
					$result = $plugins[$i] ;
					break ;
				}
			}
		
		}
		
		return $result ;
	}
	/**
	 * Retrieves the plugin with the ID $id
	 *
	 * @param string $type
	 * @param int $id
	 * @return mixed
	 */
	function &getPluginById($type, $id ) {
		$id = (int)$id;
		return vmPluginHelper::getPlugin($type, null, " AND id=$id " );
	}
	/**
	 * Checks if a plugin is enabled
	 *
	 * @access	public
	 * @param string 	$type 	The plugin type, relates to the sub-directory in the plugins directory
	 * @param string 	$plugin	The plugin name
	 * @return	boolean
	 */
	function isEnabled( $type, $plugin = null ) {
		$result = &vmPluginHelper::getPlugin( $type, $plugin ) ;
		return (! empty( $result )) ;
	}
	
	/**
	 * Loads all the plugin files for a particular type if no specific plugin is specified
	 * otherwise only the specific pugin is loaded.
	 *
	 * @access public
	 * @param string 	$type 	The plugin type, relates to the sub-directory in the plugins directory
	 * @param string 	$plugin	The plugin name
	 * @return boolean True if success
	 */
	function &importPlugin( $type, $plugin = null, $autocreate = true, $dispatcher = null, $condition='' ) {
		$result = false ;
		
		$plugins = vmPluginHelper::_load($type, $condition) ;
		
		$total = count( $plugins ) ;
		for( $i = 0 ; $i < $total ; $i ++ ) {
			if( $plugins[$i]->type == $type && ($plugins[$i]->name == $plugin || $plugin === null) ) {
				vmPluginHelper::_import( $plugins[$i], $autocreate, $dispatcher ) ;
				$result = true ;
			}
		}
		
		return $result ;
	}
	function &importPluginById( $type, $id ) {
		return vmPluginHelper::importPlugin($type, null, true, null, " AND id=$id ");
	}
	/**
	 * Loads the plugin file
	 *
	 * @access private
	 * @return boolean True if success
	 */
	function _import( &$plugin, $autocreate = true, $dispatcher = null ) {
		static $paths ;
		
		if( ! $paths ) {
			$paths = array() ;
		}
		
		$result = false ;
		$plugin->type = preg_replace( '/[^A-Z0-9_\.-]/i', '', $plugin->type ) ;
		$plugin->name = preg_replace( '/[^A-Z0-9_\.-]/i', '', $plugin->name ) ;
		
		$path = ADMINPATH . 'plugins' . DS . $plugin->type . DS . $plugin->name . '.php' ;
		
		if( ! isset( $paths[$path] ) ) {
			if( file_exists( $path ) ) {
				//needed for backwards compatibility
				global $_MAMBOTS, $mainframe ;
				
				require_once ($path) ;
				$paths[$path] = true ;
				
				if( $autocreate ) {
					// Makes sure we have an event dispatcher
					if( ! is_object( $dispatcher ) ) {
						$dispatcher = & vmDispatcher::getInstance() ;
					}
					
					$className = 'plg' . $plugin->type . $plugin->name ;
					if( class_exists( $className ) ) {
						// load plugin parameters
						$plugin = & vmPluginHelper::getPlugin( $plugin->type, $plugin->name ) ;
						
						// create the plugin
						$instance = new $className( $dispatcher, (array)($plugin) ) ;
					}
				}
			} else {
				$paths[$path] = false ;
			}
		}
	}
	
	/**
	 * Loads the published plugins
	 *
	 * @access private
	 */
	function _load($type='', $condition = '') {
		static $plugins ;
		global $my;
		if( isset( $plugins ) ) {
			return $plugins ;
		}
		
		$db = new ps_DB();
		
		switch( $type ) {
			case 'payment':
				$table = 'payment_method';
				$published_clause = 'published = \'Y\'';
				$foldertype = "'payment' as type";
				break;
			default:
				$table = 'plugins';
				$published_clause = 'published >= 1';
				$foldertype = "folder AS type";
				break;
		}
		$query = 'SELECT id, '.$foldertype.', element AS name, params' . ' 
						FROM `#__{vm}_'.$table . '` 
						WHERE ' .$published_clause .'
						'. $condition .' 
						ORDER BY ordering' ;
		
		$db->setQuery( $query ) ;
		$plugins = $db->loadObjectList();
		if( $plugins === false ) {
			$GLOBALS['vmLogger']->err( "Error loading Plugins: " . $db->getErrorMsg() ) ;
			return false ;
		}
		
		return $plugins ;
	}

}

?>