<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
 *
 * @version $Id: ps_vendor_category.php 1760 2009-05-03 22:58:57Z Aravot $
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

class ps_vendor_category extends vmAbstractObject {
	var $_key = 'vendor_category_id';
	var $_table_name = '#__{vm}_vendor_category';
	var $_required_fields = array( 'vendor_category_name' );
	
	/**
	 * Adds a new Vendor Category
	 *
	 * @param array $d
	 * @return boolean
	 */
	function add( &$d ) {
		$db = new ps_DB( ) ;
		
		if( ! $this->validate_add( $d ) ) {
			return False ;
		}
		$fields = array('vendor_category_name' => $d["vendor_category_name"],
									'vendor_category_desc' => $d["vendor_category_desc"]
								);

		$db->buildQuery('INSERT', '#__{vm}_vendor_category', $fields );
		$res = $db->query();
		
		if( $res !== false ) {
			$_REQUEST['vendor_category_id'] = $db->last_insert_id() ;
			$GLOBALS['vmLogger']->info( 'The Vendor Category has been added.');
			return true;
		}
		$GLOBALS['vmLogger']->err( 'Failed to add the Vendor Category.');
		return false;
	}
	
	/**
	 * Updates a Vendor Category
	 *
	 * @param array $d
	 * @return boolean
	 */
	function update( &$d ) {
		$db = new ps_DB( ) ;
		
		if( ! $this->validate_update( $d ) ) {
			return False ;
		}
		
		$fields = array('vendor_category_name' => $d["vendor_category_name"],
									'vendor_category_desc' => $d["vendor_category_desc"]
								);

		$db->buildQuery('UPDATE', '#__{vm}_vendor_category', $fields, ' WHERE vendor_category_id=' .(int)$d["vendor_category_id"] );
		$res = $db->query();
		
		if( $res !== false ) {
			$GLOBALS['vmLogger']->info( 'The Vendor Category has been updated.');
			return true;
		}
		$GLOBALS['vmLogger']->err( 'Failed to update the Vendor Category.');
		return false;
	}
	
	/**
	 * Controller for Deleting Records.
	 */
	function delete( &$d ) {
		
		$record_id = $d["vendor_category_id"] ;
		
		if( ! $this->validate_delete( $d ) ) {
			return False ;
		}
		if( is_array( $record_id ) ) {
			foreach( $record_id as $record ) {
				if( ! $this->delete_record( $record, $d ) )
					return false ;
			}
			return true ;
		} else {
			return $this->delete_record( $record_id, $d ) ;
		}
	}
	/**
	 * Deletes one Record.
	 */
	function delete_record( $record_id, &$d ) {
		global $db ;
		
		$q = 'DELETE FROM #__{vm}_vendor_category WHERE vendor_category_id='.(int)$record_id.' LIMIT 1';
		$db->query( $q ) ;
		return True ;
	}
	
	/**
	 * Creates a list of Vendor Categories in a HTML SELECT LIST.
	 *
	 * @param int $vendor_category_id
	 */
	function list_category( $vendor_category_id = 0 ) {
		
		$db = new ps_DB( ) ;
		
		$q = "SELECT count(*) as rowcnt FROM #__{vm}_vendor_category ORDER BY vendor_category_name" ;
		$db->query( $q ) ;
		$db->next_record() ;
		$rowcnt = $db->f( "rowcnt" ) ;
		
		$q = "SELECT vendor_category_id,vendor_category_name 
				FROM #__{vm}_vendor_category 
				ORDER BY vendor_category_name" ;
		$db->query( $q ) ;
		$array = array('0' => JText::_('VM_SELECT'));

		while( $db->next_record() ) {
			$array[$db->f( "vendor_category_id" )] = $db->f( "vendor_category_name" );
		}
		ps_html::dropdown_display('vendor_category_id', $vendor_category_id, $array );
	}

}

?>