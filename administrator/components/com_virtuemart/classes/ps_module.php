<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: ps_module.php 1760 2009-05-03 22:58:57Z Aravot $
* @package VirtueMart
* @subpackage classes
* @copyright Copyright (C) 2004-2007 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/
/**
 * VirtueMart "Core Module" Management
 *
 */
class ps_module {


	/**
	 * Validates the Input Parameters onBeforeModuleAdd
	 *
	 * @param array $d
	 * @return boolean
	 */
	function validate_add(&$d) {
		global $db, $vmLogger;

		if ( empty($d[ 'module_name' ] )) {
			$vmLogger->err ( JText::_('VM_MODULE_ERR_NAME') );
			return False;
		}
		else {
			$q = "SELECT count(*) as rowcnt from #__{vm}_module where module_name='" . $db->getEscaped( $d[ 'module_name' ] ) . "'";
			$db->query($q);
			$db->next_record();
			if ($db->f("rowcnt") > 0) {
				$vmLogger->err( JText::_('VM_MODULE_ERR_EXISTS') );
				return False;
			}
		}

		if ( empty($d[ 'module_perms' ]) ) {
			$vmLogger->err( JText::_('VM_MODULE_ERR_PERMS') );
			return false;
		}
		if (empty( $d[ 'list_order' ] ) ) {
			$d[ 'list_order' ] = "99";
		}
		return True;
	}


	/**
	 * Validates the Input Parameters onBeforeModuleUpdate
	 *
	 * @param array $d
	 * @return boolean
	 */
	function validate_update(&$d) {
		global $vmLogger;
		

		if ( empty($d[ 'module_name' ] )) {
			$vmLogger->err ( JText::_('VM_MODULE_ERR_NAME') );
			return False;
		}
		else {
			$db = new ps_DB();
			$q = "SELECT COUNT(*) AS rowcnt FROM #__{vm}_module WHERE module_name='" . $db->getEscaped( $d[ 'module_name' ] ) . "' AND module_id <> ".(int)$d['module_id'];
			$db->query($q);
			$db->next_record();
			if ($db->f("rowcnt") > 0) {
				$vmLogger->err( JText::_('VM_MODULE_ERR_EXISTS') );
				return False;
			}
		}

		if ( empty($d[ 'module_perms' ]) ) {
			$vmLogger->err( JText::_('VM_MODULE_ERR_PERMS') );
			return false;
		}
		if (empty( $d[ 'list_order' ] ) ) {
			$d[ 'list_order' ] = "99";
		}
		return True;
	}
	
	
	/**
	 * Validates the Input Parameters onBeforeModuleDelete
	 *
	 * @param array $d
	 * @return boolean
	 */
	function validate_delete($module_id) {
		global $db, $vmLogger;

		if (empty($module_id)) {
			$vmLogger->err( JText::_('VM_MODULE_ERR_DELETE_SELECT') );
			return False;
		}

		$db->query( 'SELECT module_name FROM #__{vm}_module WHERE module_id='.(int)$module_id );
		$db->next_record();
		$name = $db->f("module_name");
		if( $this->is_core( $name ) ) {
			$vmLogger->err( str_replace('{name}',$name,JText::_('VM_MODULE_ERR_DELETE_CORE')) );
			return false;
		}
		return True;

	}

	/**
	 * Adds a new module into the core module register
	 *
	 * @param array $d
	 * @return boolean
	 */
	function add(&$d) {
		global $db;

		$timestamp = time();

		if (!$this->validate_add($d)) {
			$d[ 'error' ] = $this->error;
			return False;
		}
		if( is_array( $d[ 'module_perms' ] )) {			
			$d[ 'module_perms' ] = implode( ',', $d[ 'module_perms' ] );
		}
		$fields = array( 'module_name' => vmGet( $d, 'module_name' ),
			            'module_perms' => vmGet( $d, 'module_perms' ),
						'module_description' => vmGet( $d, 'module_description' ),
						'module_publish' => vmGet( $d, 'module_publish'),
						'list_order' => vmRequest::getInt('list_order')
					);
			
		$db->buildQuery( 'INSERT',  '#__{vm}_module', $fields );

		if( $db->query() !== false ) {
			$_REQUEST['module_id'] = $db->last_insert_id();
			$GLOBALS['vmLogger']->info( JText::_('VM_MODULE_ADDED') );
			return True;
		}
		return false;

	}

	/**
	 * Updates information about a core module
	 *
	 * @param array $d
	 * @return boolean
	 */
	function update(&$d) {
		global $db;

		$timestamp = time();

		if (!$this->validate_update($d)) {
			$d[ 'error' ] = $this->error;
			return False;
		}
		if( is_array( $d[ 'module_perms' ] )) {			
			$d[ 'module_perms' ] = implode( ',', $d[ 'module_perms' ] );
		}
		
		$fields = array( 'module_name' => vmGet( $d, 'module_name' ),
			            'module_perms' => vmGet( $d, 'module_perms' ),
						'module_description' => vmGet( $d, 'module_description' ),
						'module_publish' => vmGet( $d, 'module_publish'),
						'list_order' => vmRequest::getInt('list_order')
					);
			
		$db->buildQuery( 'UPDATE',  '#__{vm}_module', $fields, ' WHERE module_id='.intval( $d[ 'module_id' ] ) );

		if( $db->query() !== false ) {
			$GLOBALS['vmLogger']->info( JText::_('VM_MODULE_UPDATED') );
			return True;
		}

		return false;
	}

	/**
	* Controller for Deleting Records.
	*/
	function delete(&$d) {

		$record_id = $d["module_id"];

		if( is_array( $record_id)) {
			foreach( $record_id as $record) {
				if( !$this->delete_record( $record, $d ))
				return false;
			}
			return true;
		}
		else {
			return $this->delete_record( $record_id, $d );
		}
	}
	/**
	* Deletes one Record.
	*/
	function delete_record( $record_id, &$d ) {

		global $db;

		if (!$this->validate_delete($record_id)) {
			$d[ 'error' ]=$this->error;
			return False;
		}

		$q = 'DELETE from #__{vm}_function WHERE module_id='.(int)$record_id;
		$db->query($q);

		$q = 'DELETE FROM #__{vm}_module WHERE module_id='.(int)$record_id;
		$db->query($q);
		return true;

	}
	
	function update_permissions( &$d ) {
		$db = new ps_DB;
		$i = 0;
		foreach( $d['module_perms'] as $module ) {
			$modules = implode(',', array_keys($module) );
			$module_id=(int)$d['module_id'][$i];
			$db->buildQuery('UPDATE', '#__{vm}_module', array('module_perms' => $modules ), 'WHERE module_id='.$module_id );
			$db->query();
			$i++;
		}
		return true;
	}
	
	function is_core( $module ) {
		return( $module == "shop" || $module == "vendor" || $module == "product" || $module == "store" || $module == "order" || $module == "admin"
		|| $module == "checkout" || $module == "account" );

	}
	/**
	 * Returns the permissions for a module
	 *
	 * @param string $basename
	 * @return mixed
	 */
	function get_dir($basename) {
		$datab = new ps_DB;

		$results = array();

		$q = "SELECT module_perms FROM #__{vm}_module where module_name='".$basename."'";
		$datab->query($q);

		if ($datab->next_record()) {
			$results[ 'perms' ] = $datab->f("module_perms");
			return $results;
		}
		else {
			return false;
		}
	}
	function get_modules( $order_by='module_name' ) {
		switch ($order_by) {
			case'module_name':
			case'module_id':
			case'list_order':
				break;
			default:
				$order_by = 'module_name';
		}
		$db = new ps_DB();
		$db->query('SELECT module_id, module_name FROM #__{vm}_module ORDER BY '.$order_by);
		return $db;
	}
	/**
	 * This function returns a drop down list of all available core modules in VirtueMart
	 * @since 1.1.0
	 * @param string $list_name
	 * @param mixed $module
	 * @param boolean $multiple
	 * @return string
	 */
	function list_modules( $list_name, $module='', $multiple=false ) {
		$db = ps_module::get_modules();
		$array = array();
		while( $db->next_record() ) {
			$array[$db->f('module_name')] = $db->f('module_name');
		}
		if( $multiple ) {
			return ps_html::selectList( $list_name, $module, $array, 4, 'multiple="multiple"' );
		} else {
			return ps_html::selectList( $list_name, $module, $array );
		}
	}
	/**
	 * Lists all available files from the /classes directory
	 *
	 * @param string $name
	 * @param string $preselected
	 * @return string
	 */
	function list_classes( $name, $preselected ) {
		global $mosConfig_absolute_path;
		$classes = vmReadDirectory( CLASSPATH, '\.php$', false, true );
		$array = array();
		foreach ($classes as $class ) {
			if( is_dir( $class ) || $class[0] == '.' ) continue;
			$classname = basename( $class, '.php' );
			if( $classname != 'ps_main' && $classname != 'ps_ini' ) {
				$array[$classname] = $classname;
			}
		}
		return ps_html::selectList( $name, $preselected, $array, 1, '', 'id="'.$name.'"' );
	}
	
	function checkModulePermissions( $calledPage ) {

		global $page, $error_type, $vmLogger, $perm;

		// "shop.browse" => module: shop, page: browse
		$my_page= explode ( '.', $page );
		if( empty( $my_page[1] )) {
			return false;
		}
		$modulename = $my_page[0];
		$pagename = $my_page[1];


		$dir_list = $this->get_dir($modulename);

		if ($dir_list) {

			// Load MODULE-specific CLASS-FILES
			include_class( $modulename );

			if ($perm->check( $dir_list[ 'perms' ]) ) {

				if ( !file_exists(PAGEPATH.$modulename.".".$pagename.".php") ) {
					define( '_VM_PAGE_NOT_FOUND', 1 );
					$error = JText::_('VM_PAGE_404_1');
					$error .= ' '.JText::_('VM_PAGE_404_2') ;
					$error .= ' "'.$modulename.".".$pagename.'.php"';
					$vmLogger->err( $error );
					return false;
				}
				return true;
			}
			else {
				define( '_VM_PAGE_NOT_AUTH', 1 );
				$vmLogger->err( JText::_('VM_MOD_NO_AUTH') );
				return false;
			}
		}
		else {
			$error = JText::_('VM_MOD_NOT_REG');
			$error .= '"'.$modulename .'" '. JText::_('VM_MOD_ISNO_REG');
			$vmLogger->err( $error );
			return false;
		}

	}

}

?>
