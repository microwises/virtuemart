<?php
/**
 * Xref table abstract class to create tables specialised doing xref
 *
 * The pkey is the Where key in the load function,
 * the skey is the select key in the load function
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

if(!class_exists('VmTable'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmtable.php');

class VmTableXarray extends VmTable {

	/** @var int Primary key */

	protected $_autoOrdering = false;
	protected $_orderable = false;

//    function setOrderable($key='ordering', $auto=true){
//    	$this->_orderingKey = $key;
//    	$this->_orderable = 1;
//    	$this->_autoOrdering = $auto;
//    	$this->$key = 0;
//    }

	function setSecondaryKey($key,$keyForm=0){
		$this->_skey 		= $key;
		$this->$key			= array();
		$this->_skeyForm	= empty($keyForm)? $key:$keyForm;

    }

    /**
     * Records in this table are arrays. Therefore we need to overload the load() function.
     *
	 * @author Max Milbers
     * @param int $id
     */
    function load($id=0){

    	if(empty($this->_skey) ) {
    		$this->setError( 'No secondary keys defined in VmTableXarray '.$this->_tbl );
    		return false;
    	}

    	if(empty($this->_db)) $this->_db = JFactory::getDBO();

		if($this->_orderable){
			$orderby = 'ORDER BY `'.$this->_orderingKey.'`';
		} else {
			$orderby = '';
		}

		$q = 'SELECT `'.$this->_skey.'` FROM `'.$this->_tbl.'` WHERE `'.$this->_pkey.'` = "'.$id.'" '.$orderby;
		$this->_db->setQuery($q);

		$result = $this->_db->loadResultArray();

		$error = $this->_db->getErrorMsg();
		if(!empty($error)){
			$this->setError( $error );
			return false;
		} else {
			if(empty($result)) return array();
			if(!is_array($result)) $result = array($result);

			return $result;
		}

    }

    /**
     * This binds the data to this kind of table. You can set the used name of the form with $this->skeyForm;
     *
     * @author Max Milbers
     * @param unknown_type $data
     */
	function bind($data){

		if(!empty($data[$this->_pkeyForm])){
			$this->_pvalue = $data[$this->_pkeyForm];
		}

		if(!empty($data[$this->_skeyForm])){
			$this->_svalue = $data[$this->_skeyForm];
		}

		return true;

	}

    /**
     *
     *
     * @author Max Milbers
     * @see libraries/joomla/database/JTable#store($updateNulls)
     */
    public function store() {

		$db = JFactory::getDBO();

		$q  = 'DELETE FROM `'.$this->_tbl.'` WHERE `'.$this->_pkey.'` = "'. $this->_pvalue.'" ';
		$db->setQuery($q);
		$db->Query();

		$returnCode = true;
		if(!empty($this->_svalue)){
			if(!is_array($this->_svalue)) $this->_svalue = array($this->_svalue);

			foreach($this->_svalue as $value){

				$obj = new stdClass;

				$pkey = $this->_pkey;
				$obj->$pkey = $this->_pvalue;

				$skey = $this->_skey;
				$obj->$skey = $value;

				//When $value is an array, then we could add more values here.
				if($this->_autoOrdering){
					$oKey = $this->_orderingKey;
					$obj->$oKey = $this->_ordering++;
				}

				$returnCode = $this->_db->insertObject($this->_tbl, $obj, $pkey);
			}
		}

//    	$newIds = array();
//
//		foreach ($fields as $field) {
//			$q = 'REPLACE INTO `#__virtuemart_customfields` ( `virtuemart_customfield_id` ,`virtuemart_custom_id` , `custom_value`, `custom_price`  )';
//			$q .= " VALUES( '".$field['virtuemart_customfield_id']."', '".$field['virtuemart_custom_id']."', '". $field['custom_value'] ."', '". $field['custom_price'] ."') ";
//			$this->_db->setQuery($q);
//			$this->_db->query();
//			$virtuemart_customfield_id = mysql_insert_id();
//			$newIds[]=$virtuemart_customfield_id;
//			$q = 'REPLACE INTO `#__virtuemart_product_customfields` ( `virtuemart_customfield_id` , `virtuemart_product_id`  )';
//			$q .= " VALUES( '".$virtuemart_customfield_id."', '". $virtuemart_product_id ."') ";
//			$this->_db->setQuery($q);
//			$this->_db->query();
//		}
//
//		// slect all virtuemart_customfield_id from product
//		$q="select virtuemart_customfield_id from `#__virtuemart_product_customfields` where `virtuemart_product_id`=".$virtuemart_product_id ;
//		$this->_db->setQuery($q);
//		$Ids = $this->_db->loadResultArray();
//		// delete from database old unused product custom fields
//		$deleteIds = array_diff(  $Ids,$newIds);
//		$id = '('.implode (',',$deleteIds).')';
//				$this->_db->setQuery('DELETE from `#__virtuemart_product_customfields` WHERE `virtuemart_customfield_id` in  ' . $id);
//		if ($this->_db->query() === false) {
//			$this->setError($this->_db->getError());
//			return false;
//		}
//		$this->_db->setQuery('DELETE from `#__virtuemart_customfields` WHERE `virtuemart_customfield_id` in  ' . $id);
//		if ($this->_db->query() === false) {
//			$this->setError($this->_db->getError());
//			return false;
//		}

		return $returnCode;

    }

}