<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: ps_product_product_type.php 1755 2009-05-01 22:45:17Z rolandd $
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
 * Product Type Management Class
 *
 */
class ps_product_product_type {
  
	/**
	 * Validates the Input Parameters onBeforeProductTypeAdd
	 * @author Zdenek Dvorak
	 * @param array $d
	 * @return boolean
	 */
  function validate_add(&$d) {
  	
    
    if (empty($d["product_type_id"])) {
      $GLOBALS['vmLogger']->err( JText::_('VM_PRODUCT_TYPE_ERR_SELECT') );
      return False;
    }
    if (empty($d["product_id"])) {
      $GLOBALS['vmLogger']->err( JText::_('VM_PRODUCT_TYPE_ERR_SELECT_PRODUCT') );
      return false;
    }
    $db = new ps_DB;
    $q  = "SELECT COUNT(*) AS count FROM #__{vm}_product_product_type_xref ";
    $q .= "WHERE product_id='".$d["product_id"]."' AND product_type_id='".$d["product_type_id"]."'";
    $db->query($q);
    if ($db->f("count") != 0) {
      $GLOBALS['vmLogger']->err( JText::_('VM_PRODUCT_TYPE_ERR_ALREADY') );
      return false;
    }
    else {
      return True;    
    }
  }

  /**
   * Validates the Input Parameters onBeforeProductTypeDelete
   * @author Zdenek Dvorak
   * @param array $d
   * @return boolean
   */
  function validate_delete(&$d) {
  	

    if (empty($d["product_type_id"])) {
      $GLOBALS['vmLogger']->err( JText::_('VM_PRODUCT_PRODUCT_TYPE_DELETE_SELECT_PT') );
      return False;
    }
    if (empty($d["product_id"])) {
      $GLOBALS['vmLogger']->err( JText::_('VM_PRODUCT_PRODUCT_TYPE_DELETE_SELECT_PR') );
      return false;
    }

    return True;
  }

  /**
   * add a Product into a Product Type
   * @author Zdenek Dvorak
   *
   * @param array $d
   * @return boolean
   */
  function add(&$d) {
  	
  	
    $db = new ps_DB;
	    
    if ($this->validate_add($d)) {

      $q  = "INSERT INTO #__{vm}_product_product_type_xref (product_id, product_type_id) ";
      $q .= "VALUES ('".$d["product_id"]."','".$d["product_type_id"]."')";
      $db->query($q);
      
      $q  = "INSERT INTO #__{vm}_product_type_".$d["product_type_id"]." (product_id) ";
      $q .= "VALUES ('".$d["product_id"]."')";
      $db->query($q);
      $GLOBALS['vmLogger']->info( JText::_('VM_PRODUCT_PRODUCT_TYPE_ASSIGNED') );
      return true;
    }
    else {
      return False;
    }

  }

	/**
	* Controller for Deleting Records.
	*/
	function delete(&$d) {
		
		if (!$this->validate_delete($d)) {
		  return False;
		}
		
		$record_id = $d["product_type_id"];
		
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
	
		$q  = 'DELETE FROM #__{vm}_product_product_type_xref WHERE product_type_id='.(int)$record_id;
		$q .= " AND product_id='".$d["product_id"]."'";
		$db->setQuery($q);   $db->query();
	
		$q  = "DELETE FROM #__{vm}_product_type_".(int)$record_id." WHERE product_id='".$d["product_id"]."'";
		$db->query($q);
		
		return True;
  }

}

?>
