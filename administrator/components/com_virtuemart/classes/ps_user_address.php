<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: ps_user_address.php 1755 2009-05-01 22:45:17Z rolandd $
* @package VirtueMart
* @subpackage classes
* @copyright Copyright (C) 2004-2008 soeren - All rights reserved.
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
* This class is used for managing Shipping Addresses
*
* @author Edikon Corp., pablo
*/
class ps_user_address {
	

	/**
	 * Validates all input parameters onBeforeAdd
	 *
	 * @param array $d
	 * @return boolean
	 */
	function validate_add(&$d) {
		global $auth, $vmLogger, $vmInputFilter;
		$valid = true;

		$d['missing'] = "";

		if (empty($auth['user_id'])) {
			$vmLogger->err( JText::_('MUST_NOT_USE') );
			$valid = false;
			return $valid;
		}

		require_once( CLASSPATH . 'ps_userfield.php' );
		$shippingFields = ps_userfield::getUserFields( 'shipping', false, '', true );
		$skipFields = ps_userfield::getSkipFields();
		
		foreach( $shippingFields as $field )  {
			if( $field->required == 0 ) continue;
			if( in_array( $field->name, $skipFields )) {
				continue;
			}
			if ( empty( $d[$field->name])) {
				$valid = false;
				$vmLogger->err(JText::_('VM_ENTER_VALUE_FIELD').' "'.(JText::_($field->title) != '' ? JText::_($field->title) : $field->title ).'"');
			}
		}
		if(empty($d['user_info_id'])) {
			$db = new ps_DB;
			$q  = "SELECT user_id from #__{vm}_user_info ";
			$q .= "WHERE address_type_name='" . $db->getEscaped($d["address_type_name"]) . "' ";
			$q .= "AND address_type='" . $db->getEscaped($d["address_type"]) . "' ";
			$q .= "AND user_id = " .(int)$d["user_id"];
			$db->query($q);
	
			if ($db->next_record()) {
				$d['missing'] .= "address_type_name";
				$vmLogger->warning( JText::_('VM_USERADDRESS_ERR_LABEL_EXISTS') );
				$valid = false;
			}
		}
		
		return $valid;
	}

	/**
	 * Validates all input parameters onBeforeUpdate
	 *
	 * @param array $d
	 * @return boolean
	 */
	function validate_update(&$d) {

		return $this->validate_add( $d );
	}

	/**
	 * Validates all input parameters onBeforeDelete
	 *
	 * @param array $d
	 * @return boolean
	 */
	function validate_delete(&$d) {
		global $vmLogger;
		if (empty($d["user_info_id"])) {
			$vmLogger->err( JText::_('VM_USERADDRESS_DELETE_SELECT') );
			return false;
		}
		else {
			return true;
		}
	}

	/**
	 * Adds a new Shipping Adress for the specified user
	 *
	 * @param array $d
	 * @return boolean
	 */
	function add(&$d) {
		global $perm, $page;
		$hash_secret = "VirtueMartIsCool";
		$db = new ps_DB;
		$timestamp = time();

		if (!$this->validate_add($d)) {
			return false;
		}

		// Get all fields which where shown to the user
		$shippingFields = ps_userfield::getUserFields( 'shipping', false, '', true );
		$skip_fields = ps_userfield::getSkipFields();
		
		foreach( $shippingFields as $userField ) {
			if( !in_array($userField->name, $skip_fields )) {			
				$fields[$userField->name] = ps_userfield::prepareFieldDataSave( $userField->type, $userField->name, vmGet( $d, $userField->name, strtoupper($userField->name) ));				
			}
		}
		// These are pre-defined fields.
		$fields['user_id'] = !$perm->check("admin,storeadmin") ? $_SESSION['auth']['user_id'] : (int)$d["user_id"];
		$fields['user_info_id'] = md5( uniqid( $hash_secret ));
		$fields['address_type'] = 'ST';
		$fields['cdate'] = $timestamp;
		$fields['mdate'] = $timestamp;

		$db->buildQuery('INSERT', '#__{vm}_user_info', $fields  );
		if( $db->query() === false ) {
			$GLOBALS['vmLogger']->err(JText::_('VM_USERADDRESS_ADD_FAILED'));
			return false;
		}
		$GLOBALS['vmLogger']->info(JText::_('VM_USERADDRESS_ADDED'));
		
		vmRequest::setVar( 'ship_to_info_id', $fields['user_info_id'] );
		
		return true;
	}
	
	/**
	 * Updates a Shipping Adress for the specified user info ID
	 *
	 * @param array $d
	 * @return boolean
	 */
	function update(&$d) {
		global $perm;
		require_once( CLASSPATH.'ps_userfield.php');
		$db = new ps_DB;
		$timestamp = time();

		if (!$this->validate_update($d)) {
			return false;
		}
		// Get all fields which where shown to the user
		$shippingFields = ps_userfield::getUserFields( 'shipping', false, '', true );
		$skip_fields = ps_userfield::getSkipFields();

		
		foreach( $shippingFields as $userField ) {
			if( !in_array($userField->name, $skip_fields )) {
				
				$fields[$userField->name] = ps_userfield::prepareFieldDataSave( $userField->type, $userField->name, vmGet($d, $userField->name, strtoupper($userField->name) ));
				
			}
		}
		// These are pre-defined fields.
		$fields['user_id'] = !$perm->check("admin,storeadmin") ? $_SESSION['auth']['user_id'] : (int)$d["user_id"];
		$fields['address_type'] = 'ST';
		$fields['mdate'] = time();

		$db->buildQuery('UPDATE', '#__{vm}_user_info', $fields, "WHERE user_info_id='" . $db->getEscaped($d["user_info_id"]) . "'".(!$perm->check("admin,storeadmin") ? " AND user_id=".$_SESSION['auth']['user_id'] : '') );	
		if( $db->query() === false ) {
			$GLOBALS['vmLogger']->err(JText::_('VM_USERADDRESS_UPDATED_FAILED'));
			return false;
		}
		$GLOBALS['vmLogger']->info(JText::_('VM_USERADDRESS_UPDATED'));
		
		vmRequest::setVar( 'ship_to_info_id', $d['user_info_id'] );
		
		return true;
		
	}

	/**
	 * Deletes the Shipping Adress of the specified user info ID
	 *
	 * @param array $d
	 * @return boolean
	 */
	function delete(&$d) {
		global $perm;

		$db = new ps_DB;

		if (!$this->validate_delete($d)) {
			return false;
		}

		$q  = "DELETE FROM #__{vm}_user_info ";
		$q .= "WHERE user_info_id='" . $d["user_info_id"] . "'";
		if (!$perm->check("admin,storeadmin")) {
			$q .= " AND user_id=".$_SESSION['auth']['user_id'];
		}
		$q .= ' LIMIT 1';
		$db->query($q);

		return true;
	}

}
?>
