<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: ps_manufacturer_category.php 1760 2009-05-03 22:58:57Z Aravot $
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

/****************************************************************************
*
* CLASS DESCRIPTION
*
* ps_manufacturer_category
*
* The class is is used to manage the manufacturer categories in your store.
*
* properties:
*
*       error - the error message returned by validation if any
* methods:
*       validate_add()
*	validate_delete()
*	validate_update()
*       add()
*       update()
*       delete()
*
*
*************************************************************************/
class ps_manufacturer_category {

	/**
	 * Validate the Input Parameters onBeforeManufacturerCategoryAdd
	 *
	 * @param array $d
	 * @return boolean
	 */
	function validate_add($d) {
		
		$db = new ps_DB;

		if (!$d["mf_category_name"]) {
			$GLOBALS['vmLogger']->err( JText::_('VM_MANUF_CAT_ERR_NAME') );
			return False;
		}

		else {
			$q = "SELECT count(*) as rowcnt from #__{vm}_manufacturer_category where";
			$q .= " mf_category_name='" .  $db->getEscaped($d["mf_category_name"]) . "'";
			$db->query($q);
			$db->next_record();
			if ($db->f("rowcnt") > 0) {
				$GLOBALS['vmLogger']->err( JText::_('VM_MANUF_CAT_ERR_EXISTS') );
				return False;
			}
		}
		return True;
	}

	/**
	 * Validate the Input Parameters onBeforeManufacturerCategoryUpdate
	 *
	 * @param array $d
	 * @return boolean
	 */
	function validate_update($d) {
		
		if (empty($d["mf_category_name"])) {
			$GLOBALS['vmLogger']->err( JText::_('VM_MANUF_CAT_ERR_NAME') );
			return false;
		}

		return true;
	}

	/**
	 * Validate the Input Parameters onBeforeManufacturerCategoryDelete
	 *
	 * @param array $d
	 * @return boolean
	 */
	function validate_delete($d) {
		
		if (empty($d["mf_category_id"])) {
			$GLOBALS['vmLogger']->err( JText::_('VM_MANUF_CAT_ERR_DELETE_SELECT') );
			return False;
		}
		else {
			return True;
		}
	}

	/**
	 * creates a new manufacturer record
	 *
	 * @param array $d
	 * @return boolean
	 */
	function add(&$d) {
		
		
		$db = new ps_DB;
		$GLOBALS['vmInputFilter']->safeSQL( $d );
		
		if (!$this->validate_add($d)) {
			return false;
		}
		$fields = array('mf_category_name' => vmGet($d, 'mf_category_name' ), 
								'mf_category_desc' => vmGet($d, 'mf_category_desc' )
							);
		$db->buildQuery('INSERT', '#__{vm}_manufacturer_category', $fields );

		if( $db->query() !== false ) {
			$_REQUEST['mf_category_id'] = $db->last_insert_id();
			$GLOBALS['vmLogger']->info( JText::_('VM_MANUF_CAT_ADDED') );
			return True;
		}
		return false;

	}

	/**
	 * updates manufacturer information
	 *
	 * @param array $d
	 * @return boolean
	 */
	function update(&$d) {
		
		
		$db = new ps_DB;
		
		$GLOBALS['vmInputFilter']->safeSQL( $d );
		
		if (!$this->validate_update($d)) {
			return False;
		}
		$fields = array('mf_category_name' => vmGet($d, 'mf_category_name' ), 
								'mf_category_desc' => vmGet($d, 'mf_category_desc' )
							);
		$db->buildQuery('UPDATE', '#__{vm}_manufacturer_category', $fields, "WHERE mf_category_id=".(int)$d["mf_category_id"]);

		if( $db->query() !== false ) {
			$_REQUEST['mf_category_id'] = $db->last_insert_id();
			$GLOBALS['vmLogger']->info( JText::_('VM_MANUF_CAT_UPDATED') );
			return True;
		}
		return false;
	}

	/**
	 * deletes a manufacturer record.
	 *
	 * @param unknown_type $d
	 * @return unknown
	 */
	function delete(&$d) {

		if (!$this->validate_delete($d)) {
			return False;
		}


		$record_id = $d["mf_category_id"];

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

		$q = 'DELETE from #__{vm}_manufacturer_category where mf_category_id='.(int)$record_id.' LIMIT 1';
		$db->query($q);
		return True;
	}

	/**
	 * Creates a list of Manufacturer Categories to be used in a drop down list
	 *
	 * @param int $mf_category_id
	 */
	function list_category($mf_category_id='0') {
		

		$db = new ps_DB;

		$q = "SELECT count(*) as rowcnt FROM #__{vm}_manufacturer_category ORDER BY mf_category_name";
		$db->query($q);
		$db->next_record();
		$rowcnt = $db->f("rowcnt");


		$q = "SELECT * FROM #__{vm}_manufacturer_category ORDER BY mf_category_name";
		$db->query($q);
		$array = array();
		if ( $rowcnt > 1) {
			$array[0] = JText::_('VM_SELECT');
		}
		while ($db->next_record()) {
			$array[$db->f("mf_category_id")] = $db->f("mf_category_name");
		}
		ps_html::dropdown_display('mf_category_id', $mf_category_id, $array );

	}

}

?>
