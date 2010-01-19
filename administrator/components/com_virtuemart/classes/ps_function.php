<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id$
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
 * This class is is used to manage the function register.
 *
 */
class ps_function extends vmAbstractObject {
	
	var $_table_name = "#__{vm}_function";
	var $_key = 'function_id';

	function ps_function() {
		$this->addRequiredField( array('function_name', 'module_id', 'function_class', 'function_method', 'function_perms') );
		$this->addUniqueField( 'function_name' );
	}
	/**
    * Validates adding a function to a module.
    *
    * @param array $d
    * @return boolean
    */
	function validate_add( &$d ) {

		return $this->validate( $d );
	}

	/**
	 * Validates updating a module function
	 *
	 * @param array $d
	 * @return boolean
	 */
	function validate_update($d) {

		return $this->validate( $d );
	}
	
	/**
	 * Validates deleting a function record
	 *
	 * @param array $d
	 * @return boolean
	 */
	function validate_delete($d) {
		global $perm, $vmLogger;
		
		if (empty($d["function_id"])) {
			$vmLogger->err( JText::_('VM_FUNCTION_ERR_DELETE_SELECT') );
			return False;
		}
		else {
			$db = new ps_DB();
			if( is_array( $d["function_id"] )) {
				foreach( $d["function_id"] as $function ) {
					$db->query( 'SELECT module_perms, function_perms FROM `#__{vm}_function` f, `#__{vm}_module` m 
							WHERE `function_id` = '.(int)$function
							. ' AND `f`.`module_id` = `m`.`module_id`' );
					$db->next_record();
				
					$module_perms = explode(',', $db->f('module_perms') );
					$function_perms = explode(',', $db->f('function_perms') );
					foreach( $module_perms as $permisson ) {
						if( !$perm->hashigherPerms( $permisson )) {
							$err_msg = JText::_('VM_FUNCTION_ERR_DELETE_NOTALLOWED_MOD');
							$err_msg = str_replace('{module_perms}',$db->f('module_perms'),$err_msg);
							$err_msg = str_replace('{perms}',$_SESSION['auth']['perms'],$err_msg);
							$vmLogger->err( $err_msg );
							return false;
						}
					}
					foreach( $function_perms as $permisson ) {
						if( !$perm->hashigherPerms( $permisson )) {
							$err_msg = JText::_('VM_FUNCTION_ERR_DELETE_NOTALLOWED_FUNC');
							$err_msg = str_replace('{function_perms}',$db->f('function_perms'),$err_msg);
							$err_msg = str_replace('{perms}',$_SESSION['auth']['perms'],$err_msg);
							$vmLogger->err( $err_msg );
							return false;
						}
					}
				}
			} else {
				$db->query( 'SELECT module_perms, function_perms FROM `#__{vm}_function` f, `#__{vm}_module` m 
							WHERE `function_id` = '.(int)$d["function_id"]
							. ' AND `f`.`module_id` = `m`.`module_id`' );
							
				$db->next_record();
				$module_perms = explode(',', $db->f('module_perms') );
				$function_perms = explode(',', $db->f('function_perms') );
				foreach( $module_perms as $permisson ) {
					if( !$perm->hashigherPerms( $permisson )) {
						$err_msg = JText::_('VM_FUNCTION_ERR_DELETE_NOTALLOWED_MOD');
						$err_msg = str_replace('{module_perms}',$db->f('module_perms'),$err_msg);
						$err_msg = str_replace('{perms}',$_SESSION['auth']['perms'],$err_msg);
						$vmLogger->err( $err_msg );
						return false;
					}
				}
				foreach( $function_perms as $permisson ) {
					if( !$perm->hashigherPerms( $permisson )) {
						$err_msg = JText::_('VM_FUNCTION_ERR_DELETE_NOTALLOWED_FUNC');
						$err_msg = str_replace('{function_perms}',$db->f('function_perms'),$err_msg);
						$err_msg = str_replace('{perms}',$_SESSION['auth']['perms'],$err_msg);
						$vmLogger->err( $err_msg );
						return false;
					}
				}
			}
		}
		return true;
	}


	/**
	 * Creates a new function record
	 * @author pablo, soeren
	 *
	 * @param array $d
	 * @return boolean
	 */
	function add(&$d) {
			
		$db = new ps_DB;
		$timestamp = time();

		if (!$this->validate_add($d)) {
			return False;
		}
		if( is_array( $d[ 'function_perms' ] )) {			
			$d[ 'function_perms' ] = implode( ',', $d[ 'function_perms' ] );
		}
		$fields = array( 'function_name' => vmGet( $d, 'function_name' ),
						'function_class'=> vmGet( $d, 'function_class' ),
						'function_method' => vmGet( $d, 'function_method' ),
						'function_perms' => vmGet( $d, 'function_perms' ),
						'module_id' => vmRequest::getInt('module_id'),
						'function_description'=> vmGet( $d, 'function_description' ) );
		$db->buildQuery( 'INSERT', '#__{vm}_function', $fields );
		
		$db->query();
		
		$_REQUEST['function_id'] = $db->last_insert_id();
		return True;

	}

	/**
	 * updates function information
	 * @author pablo, soeren
	 * 
	 * @param array $d
	 * @return boolean
	 */
	function update(&$d) {
		
		$db = new ps_DB;
		$timestamp = time();

		if (!$this->validate_update($d)) {
			return False;
		}
		if( is_array( $d[ 'function_perms' ] )) {			
			$d[ 'function_perms' ] = implode( ',', $d[ 'function_perms' ] );
		}
		$fields = array( 'function_name' => vmGet( $d, 'function_name' ),
						'function_class'=> vmGet( $d, 'function_class' ),
						'function_method' => vmGet( $d, 'function_method' ),
						'function_perms' => vmGet( $d, 'function_perms' ),
						'function_description'=> vmGet( $d, 'function_description' ) );
		$db->buildQuery( 'UPDATE', '#__{vm}_function', $fields, 'WHERE function_id='.(int)$d["function_id"] );
		$db->query();
		
		return True;
	}

	/**
	 * Delete a function, but check permissions before
	 *
	 * @param array $d
	 * @return boolean
	 */
	function delete(&$d) {
		$db = new ps_DB;

		if (!$this->validate_delete($d)) {
			return False;
		}

		$record_id = $d["function_id"];

		if( is_array( $record_id)) {
			foreach( $record_id as $record) {
				if( !$this->delete_record( (int)$record, $d ))
				return false;
			}
			return true;
		}
		else {
			return $this->delete_record( (int)$record_id, $d );
		}
	}
	/**
	* Deletes one Record.
	*/
	function delete_record( $record_id, &$d ) {
		global $db;
		$q = 'DELETE from #__{vm}_function where function_id='.(int)$record_id;
		return $db->query($q);
	}

	/**
	 * Returns an information array about the function $func
	 *
	 * @param string $func
	 * @return mixed
	 */
	function get_function($func) {
		$db = new ps_DB;
		$result = array();

		$q = "SELECT `function_perms`, `function_class`, `function_method` 
				FROM `#__{vm}_function` 
				WHERE LOWER(`function_name`)='".$db->getEscaped(strtolower($func))."'";
		
		$db->query( $q );
		
		if ($db->next_record()) {
			$result["perms"] = $db->f("function_perms");
			$result["class"] = $db->f("function_class");
			$result["method"] = $db->f("function_method");
			return $result;
		}
		else {
			return False;
		}
	}

	/**
	 * Check Function Permissions
	 * returns true if the function $func is registered
	 * and user has permission to run it
	 * Displays error if function is not registered
	 *
	 * @param string $func the function name
	 * @return mixed
	 */
	function getFuncPermissions( $func ) {

		global $page, $perm, $vmLogger;
		if (!empty($func)) {

			$funcParams = $this->get_function($func);
			if ($funcParams) {
				if ($perm->check($funcParams["perms"])) {
//					$vmLogger->info( "ps_function.php getFuncPermissions get_function ".$funcParams);
					return $funcParams;
				}
				else {
					$vmLogger->error( "ps_function.php getFuncPermissions get_function no permission");
					$error = JText::_('VM_PAGE_403').'. ';
					$error .= JText::_('VM_FUNC_NO_EXEC') . $func;
					$vmLogger->err( $error );
					return false;
				}
			}
			else {
				$error = JText::_('VM_FUNC_NOT_REG').'. ';
				$error .= $func . JText::_('VM_FUNC_ISNO_REG') ;
				$vmLogger->err( $error );
				return false;
			}
		}
		
		return true;
		
	}
	/**
	 * Checks if the currently logged in user is allowed to execute the function specified by $func
	 *
	 * @param string $func
	 * @return boolean
	 */
	function userCanExecuteFunc($func) {
		global $perm;
		if (!empty($func)) {
			// Retrieve the function attributes
			$funcParams = $this->get_function($func);
			if (is_array($funcParams) && $perm->check($funcParams["perms"])) {
				return true;
			}
		}
		return false;
	}
	/**
	 * Updates the function permissions for all functions given
	 *
	 * @param array $d
	 * @return boolean
	 */
	function update_permissions( &$d ) {
		$db = new ps_DB;
		$i = 0;
		foreach( $d['function_perms'] as $function ) {
			$functions = implode(',', array_keys($function) );
			$function_id=(int)$d['function_id'][$i];
			$db->buildQuery('UPDATE', '#__{vm}_function', array('function_perms' => $functions ), 'WHERE function_id='.$function_id );
			$db->query();
			$i++;
		}
		return true;
	}
}

?>
