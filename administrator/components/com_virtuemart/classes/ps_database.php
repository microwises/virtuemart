<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: ps_database.php 1755 2009-05-01 22:45:17Z rolandd $
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

/***********************************************************************
Wrapper Class for the $database - Object
************************************************************************/

class ps_DB {

	/** @var int   Current row in query result set */
	var $row = 0;
	/** @var stdclass	Current row record data */
	var $record = null;
	/** @var string  Error Message */
	var $error = "";
	/** @var int  Error Number */
	var $errno = "";
	/** @var string   The current sql Query    */
	var $_sql = "";
	/** @var boolean Flag to see if a query has been renewed between two query calls */
	var $_query_set= false;
	/** @var boolean   true if next_record has already been called   */
	var $called = false;
	/** @var database The core database object */
	var $_database = null;
	
	function ps_DB() {
		if( is_callable(array('jfactory', 'getdbo'))) {
			$this->_database =& jfactory::getDBO();
		} else {
			$this->_database =& $GLOBALS['database'];
		}
	}
	/**
     * Clone an object
     *
     * @param mixed $obj
     * @return mixed copy of $obj
     */
	function _clone( $obj ) {
		return $obj;
	}

	/**
    * Sets the SQL query string for later execution.
    *
    * This function replaces a string identifier <var>$prefix</var> with the
    * string held is the <var>_table_prefix</var> class variable.
    *
    * @param string The SQL query
    */
	function setQuery( $sql ) {
		
		$vm_prefix = "{vm}";
		$sql = trim( $sql );
		$this->_sql = trim(str_replace( $vm_prefix, VM_TABLEPREFIX, $sql ));
		$this->_database->setQuery( $this->_sql );
		
		$this->_query_set = true;
		
		if( defined('DEBUG') && DEBUG == '1' ) {
			// Register Double-run (multiple-run) queries 
			if( !isset($GLOBALS['queries']))$GLOBALS['queries'] = array();
			if( !isset($GLOBALS['double_queries']))$GLOBALS['double_queries'] = array();
			if( !in_array($this->_database->_sql, $GLOBALS['queries'] ) ) {
				$GLOBALS['queries'][] = $this->_database->_sql;
			} else {
				$GLOBALS['double_queries'][] = $this->_database->_sql;
			}
		}
	}

	/**
	* Runs query and sets up the query id for the class.
	*
	* @param string The SQL query
	*/
	function query( $q='' ) {
		global $mosConfig_dbprefix, $mosConfig_debug, $vmLogger;
		$prefix = "#__";
		$vm_prefix = "{vm}";

		if (empty($q) ) {
			if( empty($this->_sql)) {
				$vmLogger->debug( '"'.__CLASS__.'::'.__FUNCTION__.'" called without a pending query.');
			}
			elseif( !$this->_query_set ) {
				$vmLogger->debug( '"'.__CLASS__.'::'.__FUNCTION__.'": A query was run twice without having changed the SQL text.');
			}
		}
		else {
			$this->setQuery( $q );
		}

		$this->row = 0;
		$this->called = false;
		$this->record = null;
		$this->record = Array(0);

		if (strtoupper(substr( $this->_sql , 0, 6 )) == "SELECT" 
			|| strtoupper(substr( $this->_sql , 0, 4 ))=='SHOW' 
			|| strtoupper(substr( $this->_sql , 0, 7 ))=='EXPLAIN' 
			|| strtoupper(substr( $this->_sql , 0, 8 ))=='DESCRIBE' 
			) {
			$this->record = $this->_database->loadObjectList();

			if( $this->record === false ) {
				$result = false;
			}
		}
		else {
			$result = $this->_database->query();
		}

		$this->_query_set = false;
		
		if( isset( $result )) {
			return $result;
		}
	}

	/**
	 * Returns the next row in the RecordSet for the last query run.  
	 *
	 * @return boolean False if RecordSet is empty or the pointer is at the end.
	 */
	function next_record() {
		global $vmLogger;
		if ( empty( $this->_sql ) ) {
			$vmLogger->debug( '"'.__CLASS__.'::'.__FUNCTION__.'()" called with no query pending.' );
			return false;
		}
		if ( $this->called ) {
			$this->row++;
		}
		else {
			$this->called = true;
		}

		if ($this->row < sizeof( $this->record ) ) {
			return true;
		}
		else {
			$this->row--;
			return false;
		}
	}

	function nextRow() {
		return isset( $this->record[$this->row + 1] ) ? $this->record[$this->row + 1] : false;
	}
	function previousRow() {
		return isset( $this->record[$this->row - 1] ) ? $this->record[$this->row - 1] : false;
	}
	/**
	 * Returns the value of the given field name for the current
	 * record in the RecordSet. 
	 * f == fetch
	 * @param string  The field name
	 * @param boolean Strip slashes from the data?
	 * @return string the value of the field $field_name in the recent row of the record set
	 */
	function f($field_name, $stripslashes=true) {
		if (isset($this->record[$this->row]->$field_name)) {

			if($stripslashes) {
				return( stripslashes( $this->record[$this->row]->$field_name ) );
			}
			else {
				return( $this->record[$this->row]->$field_name );
			}
		}
	}

	/**
	 * Returns the value of the field name from the $vars variable
	 * if it is set, otherwise returns the value of the current
	 * record in the RecordSet.  Useful for handling forms that have
	 * been submitted with errors.  This way, fields retain the values 
	 * sent in the $vars variable (user input) instead of the database values.
	 * sf == selective fetch
	 * @param string  The field name
	 * @param boolean Strip slashes from the data?
	 * @return string the value of the field $field_name in the recent row of the record set
	 */
	function sf($field_name, $stripslashes=true) {
		global $vars, $default;

		if ((defined( '_VM_LOG_ERRORS' ) || isset($vars["error"])) && !empty($vars["$field_name"])) {
			if($stripslashes) {
				return  stripslashes($vars[$field_name] );
			}
			else {
				return( $vars[$field_name] );
			}
		}
		elseif (isset($this->record[$this->row]->$field_name)) {
			if($stripslashes) {
				return  stripslashes($this->record[$this->row]->$field_name );
			}
			else {
				return( $this->record[$this->row]->$field_name );
			}
		}
		elseif (isset($default[$field_name])) {
			if($stripslashes) {
				return  stripslashes($default[$field_name]);
			}
			else {
				return( $default[$field_name] );
			}
		}
	}

	/**
	* Prints the value of the given field name for the current
	* record in the RecordSet.
	* p == print
	* @param string  The field name
	* @param boolean Strip slashes from the data?
	*/
	function p($field_name, $stripslashes=true) {
		echo $this->f( $field_name, $stripslashes );
	}

	/**
  	* Prints the value of the field name from the $vars variable
  	* if it is set, otherwise prints the value of the current
  	* record in the RecordSet.  Useful for handling forms that have
  	* been submitted with errors.  This way, fields retain the values 
  	* sent in the $vars variable (user input) instead of the database
  	* values.
  	* sp == selective print
  	* @param string  The field name
  	* @param boolean Strip slashes from the data?
  	*/
	function sp($field_name, $stripslashes=true) {
		echo $this->sf( $field_name, $stripslashes);
	}
	/**
	 * Returns the object of the current row in the rowset
	 *
	 * @return mixed
	 */
	function get_row() {
		return $this->record[$this->row];
	}
	
	/**
	 * Returns the number of rows in the RecordSet from a query.
	 * @return int
	 */
	function num_rows() {
		return sizeof( $this->record );
	}

	/**
	 * Returns the ID of the last AUTO_INCREMENT INSERT.
	 *
	 * @return int
	 */
	function last_insert_id() {
		return $this->_database->insertid();
	}

	/**
	 * returns true when the actual row is the last record in the record set
	 * otherwise returns false
	 *
	 * @return boolean
	 */
	function is_last_record() {
		return ($this->row+1 >= $this->num_rows());
	}

	/**
	 * Set the "next_record" pointer back to the first row.
	 *
	 */
	function reset() {

		$this->row = 0;
		$this->called = false;

	}
	/**
	 * Returns the current row of the recordset
	 * @since VirtueMart 1.1.0
	 * @return stdClass Object
	 */
	function getCurrentRow() {
		return $this->record[$this->row];
	}
	
	/**
	 * Query Builder Functions
	 * @author soeren
	 * @since VirtueMart 1.1.0
	 * 
	 * @param string $type Either INSERT or UPDATE
	 * @param string $table Example: #__{vm}_user_info
	 * @param array $values Array of the format array( FieldName => Value ), Example: array( 'user_info_id' => md5( $hash ) )
	 * @param string $whereClause
	 *
	 */
	function buildQuery( $type='INSERT', $table, $values, $whereClause='', $doNotEnclose=array() ) {
		global $vmLogger;
		
		if( empty($table) || empty($values)) {
			return;
		}
		$table = trim( $table );
		$type = trim( $type );
		$type = strtoupper($type);
		
		switch( $type ) {
			
			case 'INSERT':
			case 'REPLACE':
				
				$q = "$type INTO `$table` (`";
				$q .= implode( "`,\n`", array_keys($values) );
				
				$q .= "`) VALUES (\n";
				$count = count( $values );
				$i = 1;
				foreach ( $values as $key => $value ) {
					if( in_array( $key, $doNotEnclose )) {
						// Important when using MySQL functions like "AES_ENCRYPT", "ENCODE", "REPLACE" or such
						$q .= $value;
					} 
					else {
						$q .= '\'' . $this->getEscaped($value)."'\n";
					}
					if( $i++ < $count ) {
						$q.= ',';
					}
				}
				$q .= ')';
				break;
				
			case 'UPDATE':
				
				$q = "UPDATE `$table` SET ";
				$count = count( $values );
				$i = 1;
				foreach ( $values as $key => $value ) {
					$q .= "`$key` = '" . $this->getEscaped($value)."'";
					if( $i++ < $count ) {
						$q.= ",\n";
					}
				}
				$q .= "\n$whereClause";
				
				break;
				
				
			default:
				$vmLogger->debug( 'Function '.__FUNCTION__.' can\'t build a query of the type "'.$type.'"' );
				return;
		}
		
		$this->setQuery( $q );

	}

	/**
	 * @param array A list of valid (and safe!) table names
	 * @return array An array of fields by table
	 */
	function getTableFields( $tables ) {
		$result = array();

		foreach ($tables as $tblval) {
			$this->setQuery( 'SHOW FIELDS FROM ' . $tblval );
			$fields = $this->loadObjectList();
			foreach ($fields as $field) {
				$result[$tblval][$field->Field] = preg_replace("/[(0-9)]/",'', $field->Type );
			}
		}

		return $result;
	}
	
	///////////////////////////////
	// Parental Database functions
	// We must overwrite them because
	// we still use a global database
	// object, not a ps_DB object
	///////////////////////////////
	function loadResult() {
		return $this->_database->loadResult();
	}
	function loadResultArray($numinarray = 0) {
		return $this->_database->loadResultArray( $numinarray );
	}
	function loadAssocList( $key='' ) {
		return $this->_database->loadAssocList( $key );
	}
	function loadObject( &$object ) {
		return $this->_database->loadObject($object);
	}
	function loadObjectList( $key='' ) {
		return $this->_database->loadObjectList( $key );
	}
	function loadRow() {
		return $this->_database->loadRow();
	}
	function loadRowList( $key='' ) {
		return $this->_database->loadRowList($key);
	}
	function getErrorMsg() {
		return $this->_database->getErrorMsg();
	}
	function getErrorNum() {
		return $this->_database->getErrorNum();
	}
	function stderr() {
		return $this->_database->stderr();
	}
	function getEscaped( $text ) {
		return $this->_database->getEscaped( $text );
	}
}
?>