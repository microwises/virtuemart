<?php
/**
 * Helper to create and alter database tables
 *
 * @package	VirtueMart
 * @subpackage Helpers
 * @author Oscar van Eijk
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id$
 */


/**
 * This helper class can be used to create and alter database tables,
 * without the need to know if a database exists.
 * Just set a new tablename  using the create_scheme(), next define all columns and
 * holding all indexes using 1 array for each and define them using the define_scheme()
 * and define_index() methods.
 * Next, the scheme() method will create or modify the table if necessary.
 * Do not forget to cleanup this singleton object afterwards using reset()
 *
 * @author Oscar van Eijk
 * @example
 * 	$_scheme = DbScheme::get_instance();	// Get an instance
 * 	$_table = array(						// Define the columns
 * 		 'id' => array (
 * 				 'type' => 'int'
 * 				,'length' => 11
 * 				,'auto_inc' => true	// an autoincrement value will automaticcally be set as primary key
 * 				,'null' => false
 * 		)
 * 		,'name' => array (
 * 				 'type' => 'varchar'
 * 				,'length' => 24
 * 				,'auto_inc' => false
 * 				,'null' => false
 * 		)
 * 		,'address' => array (
 * 				 'type' => 'text'
 * 				,'length' => 0
 * 				,'null' => false
 * 		)
 * 		,'phone' => array (
 * 				 'type' => 'varchar'
 * 				,'length' => 16
 * 				,'null' => true
 * 		)
 * 		,'country' => array (
 * 				 'type' => 'enum'
 * 				,'length' => 0
 * 				,'auto_inc' => false
 * 				,'options' => array('NL', 'BE', 'DE')
 * 				,'default' => 'BE'
 * 				,'null' => false
 * 		)
 * 	);
 * 	$_index = array (						// Set some indexes
 * 		 'name' => array(
 * 				 'columns' => array ('name')
 * 				,'primary' => false
 * 				,'unique' => false
 * 				,'type' => null
 * 		)
 * 		,'address' => array(
 * 				 'columns' => array ('address')
 * 				,'primary' => false
 * 				,'unique' => false
 * 				,'type' => 'FULLTEXT'
 * 		)
 * 	);
 * 	$_scheme->create_scheme('person');		// Set the tablename
 * 	$_scheme->define_scheme($_table);		// Add the columns
 * 	$_scheme->define_index($_index);		// Add the indexes
 * 	$_scheme->scheme();						// Create or alter the table if necessary
 * 	$_scheme->reset();						// Cleanup the singleton
 * 	// You can check the layout of any table using the table_description() method.
 * 	print_r($_scheme->table_description('person'));
 */
class DbScheme
{
	/**
	 * @var integer - Scheme Handle ID
	 */
	private $id;

	/**
	 * @var integer - Reference to the database class
	 */
	private $db;

	/**
	 * @var Array - table description
	 */
	private $scheme;

	/**
	 * @var string - table name
	 */
	private $table = '';

	/**
	 * @var integer - self reference
	 */
	private static $instance;

	/**
	 * @var boolean - True when a scheme is filled with data and not yet used
	 */
	private $inuse = false;

	/**
	 * Class constructor
	 * @author Oscar van Eijk
	 */
	private function __construct ()
	{
		$this->db = JFactory::getDBO();
	}

	/**
	 * Implementation of the __clone() function to prevent cloning of this singleton;
	 * it triggers a fatal (user)error
	 * @author Oscar van Eijk
	 */
	public function __clone ()
	{
		trigger_error('invalid object cloning');
	}

	/**
	 * Return a reference to my implementation. If necessary, create that implementation first.
	 * @author Oscar van Eijk
	 */
	public static function get_instance()
	{
		if (!DbScheme::$instance instanceof self) {
			DbScheme::$instance = new self();
		}
		return DbScheme::$instance;
	}

	/**
	 * Set a new tablename
	 * @param $_tblname Name of the table to create, check or modify
	 * @author Oscar van Eijk
	 * @return boolean, false on error, true on success
	 */
	public function create_scheme ($_tblname)
	{
		if ($this->inuse) {
			JError::raiseWarning(500, 'Cannot create scheme '.$_tblname.' while '.$this->table.' is in use - call DbScheme::reset() first');
			return false;
		}
		$this->table = $_tblname;
		$this->inuse = true;
	}

	/**
	 * Define the layout for a table
	 * @param $_scheme Array holding the table description. This is a 2 dimensional array where the
	 * first level holds the fieldnames. The second array defines the attributes for each field:
	 * - type : String; the field-type (INT|TINYINT|VARCHAR|MEDIUMTEXT|TEXT|LONGTEXT|BLOB|LONGBLOB|ENUM|SET)
	 * - length : Integer; indicating the length for fieldtypes that use that (like INT and VARCHAR)
	 * - null : Boolean; when true the value can be NULL
	 * - auto-inc : Boolean; True for auto-increment values (will be set as primary key)
	 * - default : Mixed; default value
	 * - options : Array; for SET and ENUM types. the list of possible values
	 * - comment : String; field comment
	 * @author Oscar van Eijk
	 * @return boolean, false on error, true on success
	 */
	public function define_scheme($_scheme)
	{
		if (!$this->inuse) {
			JError::raiseWarning(500, 'No scheme in use to define - call DbScheme::create_scheme() first');
			return false;
		}
		$this->scheme['columns'] = $_scheme;
		return $this->validate_scheme();
	}

	/**
	 * Define the indexes for a table
	 * @param $_index Array holding the index description. This is a 2 dimensional array where the
	 * first level holds the indexname. The second array defines the attributes for each index:
	 * - unique : Boolean; True for unique keys
	 * - primary : Boolean; True for the primary key
	 * - columns : Array; List with columnnames that will be indexed
	 * - type : String (optional); Index type, currenty only supports 'FULLTEXT'
	 * @author Oscar van Eijk
	 * @return boolean, false on error, true on success
	 */
	public function define_index($_index)
	{
		if (!$this->inuse) {
			JError::raiseWarning(500, 'No scheme in use to add indexes - call DbScheme::create_scheme() first');
			return false;
		}
		$_primary = false;
		foreach ($_index as $_name => $_descr) {
			if ($_descr['primary']) {
				if ($_primary) {
					JError::raiseWarning(500, 'Duplicate primary key found for scheme '.$this->table);
					return false;
				}
				$_name = 'PRIMARY';
				$_primary = true;
			}
			unset ($_descr['primary']);
			$this->scheme['indexes'][$_name] = $_descr;
		}
		return $this->validate_scheme();
	}

	/**
	 * If the table does not exist, of differs from the defined scheme, create of modify the table
	 * @author Oscar van Eijk
	 * @param boolean $_drops True if existing fields should be dropped; default false.
	 * If existing fields should be converted to new fields, call with DbScheme::scheme(false) first,
	 * then do the conversions, next call DbScheme::scheme(true).
	 * @return boolean, false on error, true on success
	 */
	public function scheme($_drops = false)
	{
		if (!$this->inuse) {
			JError::raiseWarning(500, 'No scheme in use to create or alter - define a scheme first');
			return false;
		}
		$_return = $this->compare();

		if ($_return === true) {
			return true; // table exists and is equal
		} elseif ($_return === false) {
			$_stat = $this->create_table(); // table does not exist
		} else {
			$_stat = $this->alter_table($_return, $_drops); // differences found
		}
		return $_stat;
	}

	/**
	 * Validate the defined scheme. Some values will be modified to make sure the SQL
	 * statements can be prepared and compare() won't find differences on case diffs
	 * @author Oscar van Eijk
	 * @return boolean False if there is an error in the scheme definition, True if no errors were found
	 */
	private function validate_scheme()
	{
		$_counters = array(
			 'auto_inc' => 0
		);
		if (!array_key_exists('columns', $this->scheme) || count($this->scheme['columns']) == 0) {
			JError::raiseWarning(500, 'No columns defined for scheme '.$this->table);
			return false;
		}
		foreach ($this->scheme['columns'] as $_fld => $_desc) {
			$this->scheme['columns'][$_fld]['type'] = strtolower($_desc['type']);
			if (array_key_exists('auto_inc',$_desc) && $_desc['auto_inc'] == true) {
				$this->scheme['indexes']['PRIMARY'] = array(
							 'columns' => array ($_fld)
							,'primary' => true
							,'unique' => true
							,'type' => null
				);
				if ($_counters['auto_inc'] > 0) {
					JError::raiseWarning(500, 'Multiple AUTO_INCREMENT fields defined for scheme '.$this->table);
					return false;
				}
				$_counters['auto_inc']++;
			}
			if (array_key_exists('length',$_desc) && $_desc['length'] == 0) {
				unset ($this->scheme['columns'][$_fld]['length']);
			}
			if (array_key_exists('options',$_desc)) {
				for ($_idx = 0; $_idx < count($_desc['options']); $_idx++) {
					if (preg_match("/^'.*'$/", $_desc['options'][$_idx]) == 0) {
						$this->scheme['columns'][$_fld]['options'][$_idx] = "'" . $_desc['options'][$_idx] . "'";
					}
				}
			}

		}
		if (!array_key_exists('indexes', $this->scheme) || count($this->scheme['indexes']) == 0) {
			// No indexes exist - should we warn about that?
		}
		foreach ($this->scheme['indexes'] as $_idx => $_desc) {
			if (!array_key_exists('columns', $_desc)
				|| !is_array($_desc['columns'])
				|| count($_desc['columns']) == 0) {
					JError::raiseWarning(500, 'Index '.$_idx.' for scheme '.$this->table.' contains no columns');
					return false;
			}
			foreach ($_desc['columns'] as $_fld) {
				if (!array_key_exists($_fld, $this->scheme['columns'])) {
					JError::raiseWarning(500, 'Column '.$_fld.' used in index '.$_idx.' does not exist in scheme '.$this->table);
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Return an error message from the database
	 */
	public function get_db_error()
	{
		return $this->db->getErrorMsg();
	}

	/**
	 * Compare the scheme with an existing database table
	 * @author Oscar van Eijk
	 * @return mixed True if there are no differences, False if the table does not exist or an
	 * array with differences
	 */
	private function compare ()
	{
		$_diffs = array();
		$_current = $this->table_description($this->table);
		if (count($_current) == 0) {
			return false;
		}

		foreach ($this->scheme['columns'] as $_fld => $_descr) {
			if (!array_key_exists($_fld, $_current['columns'])) {
				$_diffs['add']['columns'][$_fld] = $_descr;
			} else {
				foreach ($_descr as $_item => $_value) {
					if (!array_key_exists($_item, $_current['columns'][$_fld])
							|| ($_value != $_current['columns'][$_fld][$_item])) {
						$_diffs['mod']['columns'][$_fld] = $_descr;
					}
				}
			}
		}
		foreach ($_current['columns'] as $_fld => $_descr) {
			if (!array_key_exists($_fld, $this->scheme['columns'])) {
				$_diffs['drop']['columns'][$_fld] = $_descr;
			}
		}
		if (count($_diffs) == 0) {
			return true;
		}
		return $_diffs;
	}

	/**
	 * Create the defined table
	 * @author Oscar van Eijk
         * @author Valérie Cartan Isaksen
	 */
	private function create_table()
	{
		$_qry = 'CREATE TABLE ' . $this->table . '(';
		$_first = true;
		foreach ($this->scheme['columns'] as $_fld => $_desc) {
			if ($_first) {
				$_first = false;
			} else {
				$_qry .= ',';
			}
			$_qry .= ('`' . $_fld . '` ' . $_desc['type']
				. $this->_define_field($_desc));
		}
		foreach ($this->scheme['indexes'] as $_idx => $_desc) {
			if ($_idx == 'PRIMARY') {
				$_qry .= ',PRIMARY KEY ';
			} else {
				if ($_desc['unique']) {
					$_qry .= ',UNIQUE KEY ';
				} elseif ($_desc['type'] == 'FULLTEXT') {
					$_qry .= ',FULLTEXT KEY ';
				} else {
					$_qry .= ',KEY ';
				}
				$_qry .= "`$_idx` ";
			}
			$_qry .= ('(' . implode(',',$_desc['columns']) . ')');
		}
		$_qry .= ')';
                $_qry .= ' ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;';
		$this->db->setQuery($_qry);
		return ($this->db->query());
	}

	/**
	 * Make changes to the table
	 * @param array $_diffs Changes to make
	 * @param boolean $_drops True if existing fields should be dropped
	 * @author Oscar van Eijk
	 * @return false on errors, true on success
	 */
	private function alter_table($_diffs, $_drops)
	{
		if ($_drops === true && array_key_exists('drop', $_diffs) && count($_diffs['drop']['columns']) > 0) {
			foreach ($_diffs['drop']['columns'] as $_fld => $_desc) {
				$this->db->setQuery('ALTER TABLE ' . $this->table . ' DROP ' . $_fld);
				if (!$this->db->query()) {
					return false;
				}
			}
		}
		if (array_key_exists('mod', $_diffs) && count($_diffs['mod']['columns']) > 0) {
			foreach ($_diffs['mod']['columns'] as $_fld => $_desc) {
				$_qry = 'ALTER TABLE ' . $this->table
					. ' CHANGE `' . $_fld . '` `' .$_fld . '` ' . $_desc['type']
					. $this->_define_field($_desc);
				$this->db->setQuery($_qry);
				if (!$this->db->query()) {
					return false;
				}
			}
		}
		if (array_key_exists('add', $_diffs) && count($_diffs['add']['columns']) > 0) {
			foreach ($_diffs['add']['columns'] as $_fld => $_desc) {
				$_qry = 'ALTER TABLE ' . $this->table
					. ' ADD `' . $_fld . '` ' . $_desc['type']
					. $this->_define_field($_desc);
				$this->db->setQuery($_qry);
				if (!$this->db->query()) {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Create the SQL code for a field definition
	 * @param array $_desc Indexed array with the field properties from scheme definition
	 * @author Oscar van Eijk
	 * @return string SQL code
	 */
	private function _define_field($_desc)
	{
		$_qry = '';
		if (array_key_exists('length', $_desc) && $_desc['length'] > 0) {
			$_qry .= ('(' . $_desc['length'] . ')');
		}
		if (array_key_exists('options', $_desc)) {
			$_qry .= ('(' . implode(',',$_desc['options']) . ')');
		}
		if (array_key_exists('unsigned', $_desc) && $_desc['unsigned']) {
			$_qry .= ' UNSIGNED';
		}
		if (array_key_exists('zerofill', $_desc) && $_desc['zerofill']) {
			$_qry .= ' ZEROFILL';
		}
		if (!array_key_exists('null', $_desc) || !$_desc['null']) {
			$_qry .= ' NOT NULL';
		}
		if (array_key_exists('auto_inc', $_desc) && $_desc['auto_inc']) {
			$_qry .= ' AUTO_INCREMENT';
		}
		if (array_key_exists('default', $_desc) && !empty($_desc['default'])) {
			$_qry .= (' DEFAULT \'' . $_desc['default'] . "'");
		}
		if (array_key_exists('comment', $_desc) && !empty($_desc['comment'])) {
			$_qry .= (' COMMENT \'' . $_desc['comment'] . "'");
		}
		return $_qry;
	}

	/**
	 * Get the columns for a given table
	 * @param $tablename The tablename
	 * @author Oscar van Eijk
	 * @return Indexed array holding all fields => datatypes, or null on errors
	 */
	private function get_table_columns($_tablename)
	{
		$_descr = array ();
		$this->db->setQuery('SHOW FULL COLUMNS FROM ' . $_tablename);
		$_data = $this->db->loadObjectList();

		if(empty($_data)){
			vmError(get_class( $this ).' failed to load columns for table '.$_tablename,get_class( $this ).' failed to load columns for table');
			//vmdebug(get_class( $this ).' failed to load columns for table '.$_tablename,$this,1);
		} else {
			foreach ($_data as $_key => $_record) {
				if (preg_match("/(.+)\((\d+,?\d*)\)\s?(unsigned)?\s?(zerofill)?/i", $_record->Type, $_matches)) {
					$_descr[$_record->Field]['type'] = $_matches[1];
					$_descr[$_record->Field]['length'] = $_matches[2];
					$_descr[$_record->Field]['unsigned'] = (@$_matches[3] == 'unsigned');
					$_descr[$_record->Field]['zerofill'] = (@$_matches[4] == 'zerofill');
				} else {
					$_descr[$_record->Field]['type'] = $_record->Type;
				}
				$_descr[$_record->Field]['null']     = ($_record->Null == 'YES');
				$_descr[$_record->Field]['auto_inc'] = (preg_match("/auto_inc/i", $_record->Extra));

				if (preg_match("/(enum|set)\((.+),?\)/i", $_record->Type, $_matches)) {
					// Value list for ENUM and SET type
					$_descr[$_record->Field]['type'] = $_matches[1];
					$_descr[$_record->Field]['options']  = explode(',', $_matches[2]);
				}

				$_descr[$_record->Field]['default'] = ($_record->Default == 'NULL') ? '' : $_record->Default;
				$_descr[$_record->Field]['comment'] = $_record->Comment;
			}
		}
		return $_descr;
	}

	/**
	 * Get the indexes for a given table
	 * @param $tablename The tablename
	 * @author Oscar van Eijk
	 * @return Indexed array holding all fields => datatypes, or null on errors
	 */
	private function get_table_indexes($_tablename)
	{
		$_descr = array ();
		$this->db->setQuery('SHOW INDEXES FROM ' . $_tablename);
		$_data = $this->db->loadObjectList();

		foreach ($_data as $key => $_record) {
			$_index[$_record->Key_name]['columns'][$_record->Seq_in_index] = $_record->Column_name;
			$_index[$_record->Key_name]['unique'] = (!$_record->Non_unique);
			$_index[$_record->Key_name]['type'] = $_record->Index_type;
			$_index[$_record->Key_name]['comment'] = $_record->Comment;
		}
		return $_index;
	}

	/**
	 * Check if a table exists in the database
	 * @param $tablename Name of the table to check
	 * @author Oscar van Eijk
	 * @return True of the table exists
	 */
	private function _table_exists($tablename)
	{

		if(VmConfig::isJ15()){
			$qry = "SHOW TABLES LIKE '".$this->db->replacePrefix($tablename)."'";
		} else {
			$realTableName = str_replace('#__', $this->db->getPrefix(),$tablename);
			$qry = "SHOW TABLES LIKE '".$realTableName."'";
		}

		$this->db->setQuery($qry);
		return ($this->db->loadResult() != null);
	}

	/**
	 * Get a description of a database table
	 * @param string $tablename The tablename
	 * @author Oscar van Eijk
	 * @return Indexed array holding all fields => datatypes (an empty array of the table does not exist)
	 */
	public function table_description ($tablename)
	{
		$data = array();
		if (!$this->_table_exists($tablename)) {
			return $data;
		}
		$data['columns'] = $this->get_table_columns($tablename);
		$data['indexes'] = $this->get_table_indexes($tablename);
		return $data;
	}

	/**
	 * Reset the scheme definitions
	 * @author Oscar van Eijk
	 */
	public function reset()
	{
		$this->scheme = array();
		$this->table = '';
		$this->inuse = false;
	}
}
