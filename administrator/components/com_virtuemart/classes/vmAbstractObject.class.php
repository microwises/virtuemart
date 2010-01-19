<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id$
* @package VirtueMart
* @subpackage classes
* @copyright Copyright (C) 2004-2009 soeren - All rights reserved.
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
 * The abstract class for all virtuemart entities
 * @abstract 
 * @author soeren
 */
class vmAbstractObject {
	
	/** @var string The key which is used to identify this object (example: product_id) */
	var $_key = null;
	/** @var array An array holding the names of all required fields */
	var $_required_fields = array();
	
	/** @var array An array holding the names of fields that are UNIQUE => means those must be checked onAdd and onUpdate for occurences of entities with the same value */
	var $_unique_fields = array();
	/** @var string The name of the databaser table for this entity */
	var $_table_name = '';
	
	/**
	 * Retrieves a record with the specified ID from the table associated with this entitiy type
	 * In case of success, returns a ps_DB object with a prepared recordset
	 * In case of failure returns false
	 * @param mixed $id
	 * @return mixed
	 */
	function get( $id ) {
		$key = $this->getKey();
		$table = $this->getTable();
		$db = new ps_DB();
		if( !empty( $id )) {
			
			$query = 'SELECT * FROM `'.$table.'` WHERE `'.$key.'`=';
			if( is_numeric($id)) {
				$query .= (int)$id;
			} else {
				$query .= '\''.$db->getEscaped($id).'\'';
			}
			
			$db->query( $query );
			$db->next_record();
		}
		return $db;
	}
	function getKey() {
		return $this->_key;
	}
	function getTable() {
		return $this->_table_name;
	}
	function addRequiredField( $required_field ) {
		if( is_array( $required_field )) {
			foreach ( $required_field as $fieldname ) {
				$this->_required_fields[] = $fieldname;
			}
		}
		else {
			$this->_required_fields[] = $required_field;
		}
	}
	function addUniqueField( $unique_field ) {
		if( is_array( $unique_field )) {
			foreach ( $unique_field as $fieldname ) {
				$this->_unique_fields[] = $fieldname;
			}
		}
		else {
			$this->_unique_fields[] = $unique_field;
		}		
	}
	/**
	 * This function validates the input values against the _key and all required fields
	 * @abstract 
	 * @param array $d
	 * @return boolean
	 */
	function validate( &$d ) {
		global $vmLogger, $db;
		
		if( !isset( $d[$this->_key])) {
			$vmLogger->err( JText::_('VM_ABSTRACTOBJECT_VALIDATE_ERR_ID') );
			return false;
		}
		$valid = true;
		foreach( $this->_required_fields as $field ) {
			if( empty($d[$field]) && $d[$field]!=="0" && $d[$field]!==0) {
				$vmLogger->err( sprintf(JText::_('VM_ABSTRACTOBJECT_VALIDATE_ERR_FIELD'),$field) );
				$valid = false;
			}
		}
		foreach( $this->_unique_fields as $field ) {
			$q = "SELECT COUNT(`$field`) AS rowcnt FROM `{$this->_table_name}` WHERE";
			$q .= " `$field`='" .  $d[$field] . "'";
			if( !empty( $d[$this->_key]) ) {
				$q.= " AND `".$this->_key."` != ".$db->getEscaped( $d[$this->_key] );
			}
			$db->query($q);
			$db->next_record();
			if ($db->f("rowcnt") > 0) {
				$vmLogger->err( sprintf(JText::_('VM_ABSTRACTOBJECT_VALIDATE_NOTUNIQUE'),$d[$field],$field) );
				$valid = false;
			}
		}
		return $valid;
	}
	/**
	 * Abstract function for validating input values before adding an item
	 * @abstract 
	 * @param array $d
	 * @return boolean True on success, false on failure
	 */
	function validate_add( &$d ) {
		return $this->validate($d);
	}
	/**
	 * Abstract function for validating input values before updating an item
	 * @abstract 
	 * @param array $d
	 * @return boolean True on success, false on failure
	 */
	function validate_update( &$d ) {
		return $this->validate($d);
	}
	/**
	 * Abstract function for validating input values before deleting an item
	 * @abstract 
	 * @param array $d
	 * @return boolean True on success, false on failure
	 */
	function validate_delete( &$d ) {
		return $this->validate($d);
	}
	/**
	 * Prepare the change of the ordering of an item
	 *
	 * @param array $d The $_REQUEST array
	 */
	function handleOrdering( &$d ) {
		global $vmLogger, $page;
		$where = '';
		$table2_name = '';
		
		if( $page == 'admin.module_list' ) {
				$table_name = "#__{vm}_module";
				$order_field_name = 'list_order';
				$entity_name = 'module_name';
				$field_name = 'module_id';
		}
		elseif( $page == 'product.product_list' ) {
				$table_name = "#__{vm}_product_category_xref";
				$table2_name = "#__{vm}_product";
				$order_field_name = 'product_list';
				$field_name = 'product_id';
				$entity_name = 'product_name';
				$where = '`category_id`='.intval($d['category_id']);
		}
		elseif( !empty( $d['fieldid'])) {
				$table_name = "#__{vm}_userfield";
				$order_field_name = 'ordering';
				$entity_name = 'name';
				$field_name = 'fieldid';
		}
		elseif( $page == 'admin.plugin_list') {
				$table_name = "#__{vm}_plugins";
				$order_field_name = 'ordering';
				$entity_name = 'name';
				$field_name = 'id';
		}
		else {
			$vmLogger->err( JText::_('VM_ABSTRACTOBJECT_REORDER_ERR_TYPE') );
			return false;
		}
		return $this->changeOrdering( $table_name, $order_field_name, $field_name, $entity_name, $where, $table2_name );
	}
	
	function changeOrdering( $table, $name, $k, $entity_name, $where='', $table2_name='' ) {
		global $db, $vmLogger;
		
		if( strtolower(@$_REQUEST['task']) == 'saveorder') {
			$i = 0;
			foreach( $_REQUEST[$k] as $item ) {
				$sql = "UPDATE `$table` SET `$name` =".intval($_REQUEST['order'][$i])." WHERE `$k`=".intval($item);
				$sql .= ($where ? "\n	AND $where" : '');
				$db->query( $sql );
				$i++;
			}
			$this->fixOrdering($table, $name, $k, $where );
		}
		elseif( strtolower(@$_REQUEST['task']) == 'sort_alphabetically') {
			$select_where = $where;
			$q = 'SELECT `'.$name.'`, `'.$table.'`.`'.$k.'`, `'.$entity_name.'` FROM `'.$table.'`';
			if( $table2_name != '' ) {
				$q .= $table2_name != '' ? ',`'.$table2_name.'`' : '';
				$select_where = $where . "\n AND `$table`.`$k`=`$table2_name`.`$k`";
			}
			$q .= ' WHERE '.$select_where.' ORDER BY `'.$entity_name.'`';
			$db->query( $q );
			$i = 1;
			$dbu = new ps_DB();
			while( $db->next_record() ) {
				$fields = array( $name => $i,);
				$where_query = "WHERE `$k`=".intval($db->f( $k ) );
				$where_query .= ($where ? "\n	AND $where" : '');
				$dbu->buildQuery( 'UPDATE', $table, $fields, $where_query );
				//echo $dbu->_sql;
				$dbu->query();
				$i++;
			}
		}
		else {
			$item = intval( $_REQUEST[$k][0] );
			$db->query( "SELECT `$name` FROM `$table` WHERE `$k`=$item" );
			$db->next_record();
			$this->$name = $db->f($name);
			$this->$k = $item;
			
			$sql = "SELECT $k, $name FROM `$table`";
	
			if ($_REQUEST['task'] == 'orderup') {
				$sql .= "\n WHERE `$name` < ".intval($this->$name);
				$sql .= ($where ? "\n	AND $where" : '');
				$sql .= "\n ORDER BY `$name` DESC";
				$sql .= "\n LIMIT 1";
			} elseif ($_REQUEST['task'] == 'orderdown') {
				$sql .= "\n WHERE `$name` > ".intval($this->$name);
				$sql .= ($where ? "\n	AND $where" : '');
				$sql .= "\n ORDER BY `$name`";
				$sql .= "\n LIMIT 1";
			} else {
				$sql .= "\nWHERE `$name` = ".intval($this->$name);
				$sql .= ($where ? "\n AND $where" : '');
				$sql .= "\n ORDER BY `$name`";
				$sql .= "\n LIMIT 1";
			}
	
			$db->query( $sql );
	//echo 'A: ' . $db->_database->_sql;
	
			if ($db->next_record()) {
				$field_value = $db->f($name);
				$field_key_value = $db->f($k);
				
				$query = "UPDATE `$table`"
				. "\n SET `$name` = '".$field_value."'"
				. "\n WHERE `$k` = '". $this->$k ."'"
				;
				$db->setQuery( $query );
	
				if (!$db->query()) {
					$err = $db->getErrorMsg();
					//die( $err );
				}
	//echo 'B: ' . $db->getQuery();
	
				$query = "UPDATE `$table`"
				. "\n SET `$name` = '".$this->$name."'"
				. "\n WHERE `$k` = '". $field_key_value. "'"
				;
				$db->setQuery( $query );
	//echo 'C: ' . $db->getQuery();
	
				if (!$db->query()) {
					$err = $db->getErrorMsg();
					//die( $err );
				}
	
				$this->$name = $field_value;
			} 
			else {
				$query = "UPDATE `$table`"
				. "\n SET `$name` = '".$this->$name."'"
				. "\n WHERE `$k`= '". $this->$k ."'"
				;
				$db->setQuery( $query );
	//echo 'D: ' . $db->getQuery();	
	
				if (!$db->query()) {
					$err = $db->getErrorMsg();
					//die( $err );
				}
			}
		}
		return true;
	}
	/**
	 * This function compacts and fixes the current ordering
	 *
	 * @param string $table
	 * @param string $name
	 * @param string $k
	 * @param string $where
	 * @return boolean
	 */
	function fixOrdering( $table, $name, $k, $where ) {
		global $db, $vmLogger;
		
		$sql = "SELECT `$k`, `$name` ";
		$sql .= "FROM `$table` "; 
		$sql .= "WHERE `$k`=".intval(@$_REQUEST[$k]);
		$sql .= ($where ? "\n	AND $where" : '');
		
		$db->query( $sql );
		$db->next_record();
		
		$this->$k = $db->f($k);
		$this->$name = $db->f($name);
		
		$query = "SELECT $k, `$name`"
		. "\n FROM `$table`"
		. ($where ? "\n	WHERE $where" : '')
		. "\n ORDER BY `$name`"

		;

		$db->setQuery( $query );
		if (!($orders = $db->loadObjectList())) {
			$vmLogger->err( $db->getErrorMsg() );
			return false;
		}
		
		// first pass, compact the ordering numbers
		$n=count( $orders );		
		for ($i=0; $i < $n; $i++) {
			if ($orders[$i]->$name >= 0) {
				$orders[$i]->$name = $i+1;
			}
		}
		$shift = 0;
		$n=count( $orders );
		for ($i=0; $i < $n; $i++) {
			//echo "i=$i id=".$orders[$i]->$k." order=".$orders[$i]->$name;
			if ($orders[$i]->$k == $this->$k) {
				// place 'this' record in the desired location
				$orders[$i]->$name = min( $this->$name, $n );
				$shift = 1;
			} else if ($orders[$i]->$name >= $this->$name && $this->$name > 0) {
				$orders[$i]->$name++;
			}
		}
		// compact once more until I can find a better algorithm
		$n=count( $orders );
		for ($i=0; $i < $n; $i++) {
			if ($orders[$i]->$name >= 0) {
				$orders[$i]->$name = $i+1;
				$query = "UPDATE $table"
				. "\n SET `$name` = '". $orders[$i]->$name ."'"
				. "\n WHERE `$k` = '". $orders[$i]->$k ."'"
				. ($where ? "\n	AND $where" : '')
				;
	//echo "A: ".$db->getQuery();
				$db->query( $query);
			}
		}

		// if we didn't reorder the current record, make it last
		if ($shift == 0) {
			$order = $n+1;
			$query = "UPDATE $table"
			. "\n SET `$name` = '$order'"
			. "\n WHERE $k = '". $this->$k ."'"
			. ($where ? "\n	AND $where" : '')
			;
			$db->query( $query );
		}
		return true;
	}
	/**
	 * Prepare the change of the pulish state of an item
	 *
	 * @param array $d The REQUEST array
	 * @return boolean True on success, false on failure
	 */
	function handlePublishState( $d ) {
		global $vmLogger,$perm, $page;
		
		$has_vendor = true;
		if( !empty($d['product_id']) && empty( $d['review_id'] ) && empty( $d['file_id'] ) ) {
			$table_name = '#__{vm}_product';
			$publish_field_name = 'published';
			$field_name = 'product_id';
		}
		elseif( !empty($d['category_id'])) {
			$table_name = "#__{vm}_category";
			$publish_field_name = 'published';
			$field_name = 'category_id';
		}
		elseif( !empty($d['category_child_id'])) {
			$table_name = '#__{vm}_category_xref';
			$publish_field_name = 'category_shared';
			$field_name = 'category_child_id';
			//Has the User the right to share/unshare this category?
			if (!$perm->check("admin")) {
//				$hVendor_id = $_SESSION["ps_vendor_id"];
//				require_once(CLASSPATH. 'ps_user.php');    Maybe lead to bug, but if not,.. seems not necessary
				$vendor_id = $hVendor -> getLoggedVendor(false);
				$db = new ps_DB();
				$q = 'SELECT vendor_id FROM #__{vm}_category WHERE category_id='. $d["category_child_id"];
				$db->query( $q );
				$db->next_record();
				$vendor = $db->f("vendor_id");
				if($vendor_id == $vendor){
					$has_vendor = false;
				}
			}else{
				$has_vendor = false;
			}
			
		}
		elseif( !empty( $d['id'])) {
			$table_name = '#__{vm}_payment_method';
			$publish_field_name = 'published';
			$field_name = 'id';
		}	
		elseif( $page == 'admin.plugin_list' ) {
			$table_name = '#__{vm}_plugins';
			$publish_field_name = 'published';
			$field_name = 'id';
		}
		elseif( !empty( $d['review_id'])) {
			$table_name = '#__{vm}_product_reviews';
			$publish_field_name = 'published';
			$field_name = 'review_id';
			$has_vendor = false;
		}		
		elseif( !empty( $d['fieldid'])) {
			$table_name = '#__{vm}_userfield';
			$publish_field_name = empty($d['item']) ? 'published' : vmget( $d, 'item' );
			$field_name = 'fieldid';
		}
		elseif( !empty( $d['file_id'] ) ) {
			$table_name = '#__{vm}_product_files';
			$publish_field_name = 'file_published';
			$field_name = 'file_id';
			$has_vendor = false;
		}
		elseif( !empty( $d['user_id'] ) ) {
			echo('user_is_vendor');
			$table_name = '#__{vm}_user_info';
//			$publish_field_name = 'user_id';
//			$field_name = 'user_is_vendor';
			$publish_field_name = 'user_is_vendor';
			$field_name = 'user_id';
			$has_vendor = false;
		}
		else {
			$vmLogger->err( JText::_('VM_ABSTRACTOBJECT_PUBLISH_ERR_TYPE') );
			return false;
		}
		
		return $this->changePublishState( $d[$field_name], $d['task'], $table_name, $publish_field_name, $field_name, $has_vendor );
		
	}
	/**
	 * Updates the $publish_field_name of the item(s) $itemId to Y or N ($task)
	 * in the table $table_name for field $field_name
	 *
	 * @param int/array $itemId (A single integer is later converted into an array)
	 * @param string $task Either 'publish' or 'unpublish'
	 * @param string $table_name
	 * @param string $publish_field_name
	 * @param string $field_name
	 * @return boolean
	 */
	function changePublishState( $itemId, $task, $table_name, $publish_field_name, $field_name, $has_vendor ) {
		global $vmLogger;
		
		$db = new ps_DB();
//		if( $field_name == 'id' || $field_name == 'fieldid' || $field_name == 'file_id' || $field_name == 'user_id') {
			$value = ($task == 'unpublish') ? '0' : '1';
//		}
//		else {
//			$value = ($task == 'unpublish') ? 'N' : 'Y';
//		}
		
		if( !is_array( $itemId )) {
			$set[] = $itemId;
		}
		else {
			$set =& $itemId;
		}
		$set = implode( ',', $set );
		
		$q = "UPDATE `$table_name` SET `$publish_field_name` = '$value' ";
		$q .= "WHERE FIND_IN_SET( `$field_name`, '$set' )";

		$db->query( $q );
		$vmLogger->info('changePublishState '.  $q);
		
		switch ($task) {
			case 'publish':
				$tasklang = JText::_('CMN_PUBLISHED');
				break;
			case 'unpublish':
				$tasklang = JText::_('CMN_UNPUBLISHED');
				break;
			default:
				$tasklang = $task;
				break;
		}
		
		$infomessage = JText::_('VM_ABSTRACTOBJECT_TASK_OK');
		$infomessage = str_replace('{field_name}',$field_name,$infomessage);
		$infomessage = str_replace('{set}',$set,$infomessage);
		$infomessage = str_replace('{task}',$tasklang,$infomessage);
		
		$vmLogger->info( $infomessage );
		
		return true;
	}
}
?>
