<?php

/**
 * virtuemart table class, with some additional behaviours.
 *
 *
 * @package	VirtueMart
 * @subpackage Helpers
 * @author Max Milbers
 * @copyright Copyright (c) 2011 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */
defined('_JEXEC') or die();

jimport('joomla.user.user');

/**
 * Replaces JTable with some more advanced functions and fitting to the nooku conventions
 *
 * checked_out = locked_by,checked_time = locked_on
 *
 * Enter description here ...
 * @author Milbo
 *
 */
class VmTable extends JTable{

	protected $_pkey = '';
	protected $_pkeyForm = '';
	protected $_obkeys = array();
	protected $_unique = false;
	protected $_unique_name = array();
	protected $_orderingKey = 'ordering';
	protected $_slugAutoName = '';
	protected $_slugName = '';
	protected $_loggable = false;

	function __construct( $table, $key, &$db )
	{
		$this->_tbl		= $table;
		$this->_tbl_key	= $key;
		$this->_db		=& $db;
	}

	function setPrimaryKey($key, $keyForm=0){
		$error = JText::sprintf('COM_VIRTUEMART_STRING_ERROR_PRIMARY_KEY', JText::_('COM_VIRTUEMART_' . strtoupper($key)));
		$this->setObligatoryKeys('_pkey', $error);
		$this->_pkey = $key;
		$this->_pkeyForm = empty($keyForm) ? $key : $keyForm;
		$this->$key = 0;
	}

	public function setObligatoryKeys($key){
		$error = JText::sprintf('COM_VIRTUEMART_STRING_ERROR_OBLIGATORY_KEY', JText::_('COM_VIRTUEMART_' . strtoupper($key)));
		$this->_obkeys[$key] = $error;
	}

	public function setUniqueName($name){
		$error = JText::sprintf('COM_VIRTUEMART_STRING_ERROR_NOT_UNIQUE_NAME', JText::_('COM_VIRTUEMART_' . strtoupper($name)));
		$this->_unique = true;
		$this->_obkeys[$name] = $error;
		$this->_unique_name[$name] = $error;
	}

	public function setLoggable(){
		$this->_loggable = true;
		$this->created_on = '';
		$this->created_by = 0;
		$this->modified_on = '';
		$this->modified_by = 0;
	}

	public function setLockable(){
		$this->locked_on = '';
		$this->locked_by = 0;
	}

	function setOrderable($key='ordering', $auto=true){
		$this->_orderingKey = $key;
		$this->_orderable = 1;
		$this->_autoOrdering = $auto;
		$this->$key = 0;
	}

	function setSlug($slugAutoName, $key = 'slug'){

		$this->_slugAutoName = $slugAutoName;
		$this->_slugName = $key;
		$this->$key = '';
		$this->setUniqueName($key);
	}

	/**
	 * This function defines a database field as parameter field, which means that some values get injected there
	 * As delimiters are used | for the pair and = for key, value
	 *
	 * @author Max Milbers
	 * @param string $paramsFieldName
	 * @param string $varsToPushParam
	 */
	function setParameterable($paramsFieldName,$varsToPushParam){
		$this->_varsToPushParam = $varsToPushParam;
		$this->_xParams = $paramsFieldName;
		foreach($this->_varsToPushParam as $k=>$v){
			$this->$k = $v[0];
		}
	}

	/**
	 * This function helps to define which data fields should use the injection technic of setParameterable
	 * When you need an exampel for the array look in the vendors table.
	 * @author Max Milbers
	 * @param array $arrayToAdd
	 * @param boolean $overwrite
	 */
	function setVarsToPushParam($arrayToAdd,$overwrite=false){
		if($overwrite){
			$this->_varsToPushParam = $arrayToAdd;
		} else {
			$this->_varsToPushParam = array_merge($this->_varsToPushParam,$arrayToAdd);
		}
	}

	var $_tablePreFix = '';
	function setTableShortCut($prefix){
		$this->_tablePreFix = $prefix.'.';
	}

	/**
	* Load the fieldlist
	*/
	public function loadFields()
	{
		$_fieldlist = array();
		$_q = 'SHOW COLUMNS FROM `'.$this->_tbl.'`';
		$this->_db->setQuery($_q);
		$_fields = $this->_db->loadObjectList();
		if (count($_fields) > 0) {
			foreach ($_fields as $key => $_f) {
				$_fieldlist[$_f->Field] = $_f->Default;
			}
			$this->setProperties($_fieldlist);
		}
	}

	function checkDataContainsTableFields($from, $ignore=array()){

		if(empty($from))
		return false;
		$fromArray = is_array($from);
		$fromObject = is_object($from);

		if(!$fromArray && !$fromObject){
			$this->setError(get_class($this) . '::check if data contains table fields failed. Invalid from argument <pre>' . print_r($from, 1) . '</pre>');
			return false;
		}
		if(!is_array($ignore)){
			$ignore = explode(' ', $ignore);
		}

		foreach($this->getProperties() as $k => $v){
			// internal attributes of an object are ignored
			if(!in_array($k, $ignore)){

				if($fromArray && !empty($from[$k])){
					return true;
				}else if($fromObject && !empty($from->$k)){
					return true;
				}
			}
		}
		vmdebug('VmTable developer notice, table ' . get_class($this) . ' means that there is no data to store. When you experience that something does not get stored as expected, please write in the forum.virtuemart.net');
		return false;
	}

	function setLoggableFieldsForStore(){

		if($this->_loggable){

			$date = JFactory::getDate();
			$today = $date->toMySQL();
			$user = JFactory::getUser();

			if(!$this->created_on){
				$this->created_on = $today;
				$this->created_by = $user->id;
			}
			$this->modified_on = $today;
			$this->modified_by = $user->id;
		}

		if(isset($data->locked_on)){
			//Check if user is allowed to store, then disable or prevent storing
			$data->locked_on = 0;
		}
	}

	/**
	 * Technic to inject params as table attributes
	 * @author Max Milbers
	 */
	function load($int=null){

		parent::load($int);

		if(!empty($this->_xParams)){

			$paramFieldName = $this->_xParams;
			$paramFields = $this->$paramFieldName;
			if(!empty($this->$paramFieldName)){

				$params = explode('|', $this->$paramFieldName);
				foreach($params as $item){

					$item = explode('=',$item);
					if(count($item)===2 && isset($this->_varsToPushParam[$item[0]][1]) ){
						if($this->_varsToPushParam[$item[0]][1]==='string'){
							$this->$item[0] = base64_decode(unserialize($item[1]));
						} else {
							$this->$item[0] = unserialize($item[1]);
						}
					}
				}
			}

			foreach($this->_varsToPushParam as $key=>$v){

				if(!isset($this->$key)){
					$this->$key = $v[0];
				}
			}
		}

		return $this;
	}

	/**
	 * Technic to inject params as table attributes
	 * @author Max Milbers
	 */
	function store(){

		$this->setLoggableFieldsForStore();

		$this->storeParams();

		return parent::store();

	}


	function storeParams(){
		if(!empty($this->_xParams)){
			foreach($this->_varsToPushParam as $key=>$v){

				$paramFieldName = $this->_xParams;
				if(isset($this->$key)){
					if($v[1]==='string'){
						$this->$paramFieldName .= $key.'='.base64_encode(serialize($this->$key)).'|';
					} else {
						$this->$paramFieldName .= $key.'='.serialize($this->$key).'|';
					}
				} else {
					if($v[1]==='string'){
						$this->$paramFieldName .= $key.'='.base64_encode(serialize($v[0])).'|';
					} else {
						$this->$paramFieldName .= $key.'='.serialize($v[0]).'|';
					}
				}
				unset($this->$key);
			}
		}
		return true;
	}
	/**
	 * @author Max Milbers
	 * @param
	 */
	function check(){

		if(!empty($this->_slugAutoName)){
			$slugAutoName = $this->_slugAutoName;
			$slugName = $this->_slugName;
			if(empty($this->$slugName)){
				$this->$slugName = $this->$slugAutoName;
			}
			if(VmConfig::isJ15()){
				$this->$slugName = JFilterOutput::stringURLSafe($this->$slugName);
				if(trim(str_replace('-', '', $this->$slugName)) == ''){
					$datenow = JFactory::getDate();
					$this->$slugName = $datenow->toFormat("%Y-%m-%d-%H-%M-%S");
				}
			} else {
				$this->$slugName = JApplication::stringURLSafe($this->$slugName);
				if (trim(str_replace('-','',$this->$slugName)) == '') {
					$this->$slugName = JFactory::getDate()->format('Y-m-d-H-i-s');
				}
			}
		}

		foreach($this->_obkeys as $obkeys => $error){
			if(empty($this->$obkeys)){
				if(empty($error)){
					$error = 'Serious error cant save ' . $this->_tbl . ' without ' . $obkeys;
				}else {
					$error = get_class($this).' '.JText::_($error);
				}
				$this->setError($error);
				vmError($error);
				return false;
			}
		}

		if($this->_unique){
			if(empty($this->_db))$this->_db = JFactory::getDBO();
			foreach($this->_unique_name as $obkeys => $error){

				if(empty($this->$obkeys)){
					// 					$this->setError(JText::sprintf('COM_VIRTUEMART_NON_UNIQUE_KEY',$this->$obkeys));
					$this->setError($error);
					vmError($error);
					return false;
				} else {
					$q = 'SELECT `' . $this->_tbl_key . '`,`' . $this->_db->getEscaped($obkeys) . '` FROM `' . $this->_tbl . '` ';
					$q .= 'WHERE `' . $this->_db->getEscaped($obkeys) . '`="' . $this->_db->getEscaped($this->$obkeys) . '"';
					$this->_db->setQuery($q);
					$unique_id = $this->_db->loadResultArray();

					$tblKey = $this->_tbl_key;
					if(!empty($unique_id)){
						foreach($unique_id as $id){
							if($id != $this->$tblKey){
								//$datenow = JFactory::getDate();
								$this->$obkeys = $this->$obkeys.rand();
								vmWarn('COM_VIRTUEMART_NON_UNIQUE_WARN',$obkeys,$this->_tbl,$this->$obkeys);
							}
						}
					}
				}

				/* if(empty($error)){
				 $this->setError(JText::_($error));
				}else {
				$this->setError(JText::sprintf('COM_VIRTUEMART_NON_UNIQUE', $this->_tbl, $obkeys . ': ' . $this->$obkeys));
				}*/
				//return false;
			}
		}


		if(isset($this->virtuemart_vendor_id )){
			if(Vmconfig::get('multix','none') ==='none'){
				if(!isset($this->user_is_vendor)){
					$this->virtuemart_vendor_id = $data['virtuemart_vendor_id'] = 1;
				} else if($this->user_is_vendor){
					$this->virtuemart_vendor_id = $data['virtuemart_vendor_id'] = 1;
				} else {
					$this->virtuemart_vendor_id = $data['virtuemart_vendor_id'] = 0;
				}
			}
		}

		return true;
	}

	/**
	 * As shortcat, Important the & MUST be there, even in php5.3
	 *
	 * @author Max Milbers
	 * @param array/obj $data input data as assoc array or obj
	 * @param boolean $preload You can preload the data here too preserve not updated data
	 * @return array/obj $data the updated data
	 */
	public function bindChecknStore(&$data,$preload=false){

		$tblKey = $this->_tbl_key;

		if($preload){
			if(is_object($data)){
				if(!empty($data->$tblKey)){
					$this->load($data->$tblKey);
				}
			}else {
				if(!empty($data[$tblKey])){
					$this->load($data[$tblKey]);
				}
			}
		}

		$ok = true;
		$msg = '';

		if(!$this->bind($data)){
			$ok = false;
			$msg = 'bind';
			vmdebug('Problem in bind '.get_class($this).' '.$this->_db->getErrorMsg());
		}

		if($ok){
			if(!$this->checkDataContainsTableFields($data)){
				$ok = false;
				//    			$msg .= ' developer notice:: checkDataContainsTableFields';
			}
		}

		if($ok){
			if(!$this->check()){
				$ok = false;
				$msg .= ' check';
				vmdebug('Check returned false '.get_class($this).' '.$this->_db->getErrorMsg());
			}
		}

		if($ok){
			if(!$this->store()){
				$ok = false;
				$msg .= ' store';
				vmdebug('Problem in store '.get_class($this).' '.$this->_db->getErrorMsg());
			}
		}


		if(is_object($data)){
			$data->$tblKey = !empty($this->$tblKey) ? $this->$tblKey : 0;
		}else {
			$data[$tblKey] = !empty($this->$tblKey) ? $this->$tblKey : 0;
		}

// 		vmdebug('bindChecknStore '.get_class($this).' '.$this->_db->getErrorMsg());
		return $data;
	}

	/**
	 * Description
	 *
	 * @author Joomla Team, Max Milbers
	 * @access public
	 * @param $dirn
	 * @param $where
	 */
	function move($dirn, $where='', $orderingkey=0){

		if(!empty($orderingkey))
		$this->_orderingKey = $orderingkey;

		if(!in_array($this->_orderingKey, array_keys($this->getProperties()))){
			$this->setError(get_class($this) . ' does not support ordering');
			return false;
		}

		$k = $this->_tbl_key;

		$orderingKey = $this->_orderingKey;

		$sql = 'SELECT `' . $this->_tbl_key . '`, `' . $this->_orderingKey . '` FROM ' . $this->_tbl;

		if($dirn < 0){
			$sql .= ' WHERE `' . $this->_orderingKey . '` < ' . (int)$this->$orderingKey;
			$sql .= ( $where ? ' AND ' . $where : '');
			$sql .= ' ORDER BY `' . $this->_orderingKey . '` DESC';
		}else if($dirn > 0){
			$sql .= ' WHERE `' . $this->_orderingKey . '` > ' . (int)$this->$orderingKey;
			$sql .= ( $where ? ' AND ' . $where : '');
			$sql .= ' ORDER BY `' . $this->_orderingKey . '`';
		}else {
			$sql .= ' WHERE `' . $this->_orderingKey . '` = ' . (int)$this->$orderingKey;
			$sql .= ( $where ? ' AND ' . $where : '');
			$sql .= ' ORDER BY `' . $this->_orderingKey . '`';
		}

		$this->_db->setQuery($sql, 0, 1);


		$row = null;
		$row = $this->_db->loadObject();
		if(isset($row)){
			$query = 'UPDATE ' . $this->_tbl
			. ' SET `' . $this->_orderingKey . '` = ' . (int)$row->$orderingKey
			. ' WHERE ' . $this->_tbl_key . ' = "' . $this->_db->getEscaped($this->$k) . '"'
			;
			$this->_db->setQuery($query);

			if(!$this->_db->query()){
				$err = $this->_db->getErrorMsg();
				JError::raiseError(500, $err);
			}

			$query = 'UPDATE ' . $this->_tbl
			. ' SET `' . $this->_orderingKey . '` = ' . (int)$this->$orderingKey
			. ' WHERE ' . $this->_tbl_key . ' = "' . $this->_db->getEscaped($row->$k) . '"'
			;
			$this->_db->setQuery($query);

			if(!$this->_db->query()){
				$err = $this->_db->getErrorMsg();
				JError::raiseError(500, $err);
			}

			$this->ordering = $row->ordering;
		}else {
			$query = 'UPDATE ' . $this->_tbl
			. ' SET `' . $this->_orderingKey . '` = ' . (int)$this->$orderingKey
			. ' WHERE ' . $this->_tbl_key . ' = "' . $this->_db->getEscaped($this->$k) . '"'
			;
			$this->_db->setQuery($query);

			if(!$this->_db->query()){
				$err = $this->_db->getErrorMsg();
				JError::raiseError(500, $err);
			}
		}
		return true;
	}

	/**
	 * Returns the ordering value to place a new item last in its group
	 *
	 * @access public
	 * @param string query WHERE clause for selecting MAX(ordering).
	 */
	function getNextOrder($where='', $orderingkey = 0){

		$where = $this->_db->getEscaped($where);
		$orderingkey = $this->_db->getEscaped($orderingkey);

		if(!empty($orderingkey))
		$this->_orderingKey = $orderingkey;
		if(!in_array($this->_orderingKey, array_keys($this->getProperties()))){
			$this->setError(get_class($this) . ' does not support ordering');
			return false;
		}

		$query = 'SELECT MAX(`' . $this->_orderingKey . '`)' .
	' FROM ' . $this->_tbl .
		($where ? ' WHERE ' . $where : '');

		$this->_db->setQuery($query);
		$maxord = $this->_db->loadResult();

		if($this->_db->getErrorNum()){
			$this->setError(get_class($this) . ' getNextOrder ' . $this->_db->getErrorMsg());
			return false;
		}
		return $maxord + 1;
	}

	/**
	 * Compacts the ordering sequence of the selected records
	 *
	 * @access public
	 * @param string Additional where query to limit ordering to a particular subset of records
	 */
	function reorder($where='', $orderingkey = 0){

		$where = $this->_db->getEscaped($where);
		$orderingkey = $this->_db->getEscaped($orderingkey);

		if(!empty($orderingkey))
		$this->_orderingKey = $orderingkey;
		$k = $this->_tbl_key;

		if(!in_array($this->_orderingKey, array_keys($this->getProperties()))){
			$this->setError(get_class($this) . ' does not support ordering');
			return false;
		}

		if($this->_tbl == '#__content_frontpage'){
			$order2 = ", content_id DESC";
		}else {
			$order2 = "";
		}

		$query = 'SELECT ' . $this->_tbl_key . ', ' . $this->_orderingKey
		. ' FROM ' . $this->_tbl
		. ' WHERE `' . $this->_orderingKey . '` >= 0' . ( $where ? ' AND ' . $where : '' )
		. ' ORDER BY `' . $this->_orderingKey . '` ' . $order2
		;
		$this->_db->setQuery($query);
		if(!($orders = $this->_db->loadObjectList())){
			$this->setError(get_class($this) . ' reorder ' . $this->_db->getErrorMsg());
			return false;
		}
		$orderingKey = $this->_orderingKey;
		// compact the ordering numbers
		for($i = 0, $n = count($orders); $i < $n; $i++){
			if($orders[$i]->$orderingKey >= 0){
				if($orders[$i]->$orderingKey != $i + 1){
					$orders[$i]->$orderingKey = $i + 1;
					$query = 'UPDATE ' . $this->_tbl
					. ' SET `' . $this->_orderingKey . '` = "' . $this->_db->getEscaped($orders[$i]->$orderingKey) . '"
					 WHERE ' . $k . ' = "' . $this->_db->getEscaped($orders[$i]->$k) . '"'
					;
					$this->_db->setQuery($query);
					$this->_db->query();
				}
			}
		}

		return true;
	}

	/**
	 * Checks out a row
	 *
	 * @access public
	 * @param	integer	The id of the user
	 * @param 	mixed	The primary key value for the row
	 * @return	boolean	True if successful, or if checkout is not supported
	 */
	function checkout($who, $oid = null){
		if(!in_array('locked_by', array_keys($this->getProperties()))){
			return true;
		}

		$k = $this->_tbl_key;
		if($oid !== null){
			$this->$k = $oid;
		}

		$date = JFactory::getDate();
		$time = $date->toMysql();

		$query = 'UPDATE ' . $this->_db->nameQuote($this->_tbl) .
	' SET locked_by = ' . (int)$who . ', locked_on = "' . $this->_db->getEscaped($time) . '"
			 WHERE ' . $this->_tbl_key . ' = "' . $this->_db->getEscaped($this->$k) . '"';
		$this->_db->setQuery($query);

		$this->locked_by = $who;
		$this->locked_on = $time;

		return $this->_db->query();
	}

	/**
	 * Checks in a row
	 *
	 * @access	public
	 * @param	mixed	The primary key value for the row
	 * @return	boolean	True if successful, or if checkout is not supported
	 */
	function checkin($oid=null){
		if(!(
		in_array('locked_by', array_keys($this->getProperties())) ||
		in_array('locked_on', array_keys($this->getProperties()))
		)){
			return true;
		}

		$k = $this->_tbl_key;

		if($oid !== null){
			$this->$k = $oid;
		}

		if($this->$k == NULL){
			return false;
		}

		$query = 'UPDATE ' . $this->_db->nameQuote($this->_tbl) .
	' SET locked_by = 0, locked_on = "' . $this->_db->getEscaped($this->_db->getNullDate()) . '"
				 WHERE ' . $this->_tbl_key . ' = "' . $this->_db->getEscaped($this->$k) . '"';
		$this->_db->setQuery($query);

		$this->locked_by = 0;
		$this->locked_on = '';

		return $this->_db->query();
	}

	/**
	 * Check if an item is checked out
	 *
	 * This function can be used as a static function too, when you do so you need to also provide the
	 * a value for the $against parameter.
	 *
	 * @static
	 * @access public
	 * @param integer  $with  	The userid to preform the match with, if an item is checked out
	 * 				  			by this user the function will return false
	 * @param integer  $against 	The userid to perform the match against when the function is used as
	 * 							a static function.
	 * @return boolean
	 */
	function isCheckedOut($with = 0, $against = null){
		if(isset($this) && is_a($this, 'JTable') && is_null($against)){
			$against = $this->get('locked_by');
		}

		//item is not checked out, or being checked out by the same user
		if(!$against || $against == $with){
			return false;
		}

		$session = JTable::getInstance('session');
		return $session->exists($against);
	}

	/**
	 * toggle (0/1) a field
	 * or invert by $val
	 * @author impleri
	 * @param string $field the field to toggle
	 * @param boolean $val field value (0/1)
	 * @todo could make this multi-id as well...
	 */
	function toggle($field, $val = NULL){
		if($val === NULL){
			$this->$field = !$this->$field;
		}else {
			$this->$field = $val;
		}

		return ($this->store(true));
	}

	public function resetErrors(){

		$this->_errors = array();
	}


	function delete( $oid=null ){

		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval( $oid );
		}

		$query = 'SELECT '.$this->_tbl_key.' FROM '.$this->_db->nameQuote( $this->_tbl ).
				' WHERE '.$this->_tbl_key.' = '. $this->_db->Quote($this->$k);
		$this->_db->setQuery( $query );

		if ($this->_db->query()){
                        return parent::delete($oid);
		} else {
			return true;
		}
	}


}
