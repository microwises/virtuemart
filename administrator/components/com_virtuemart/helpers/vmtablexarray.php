<?php
/**
 * Xref table abstract class to create tables specialised doing xref
 *
 * This class provides the functions for the calculatoins
 *
 * @package	VirtueMart
 * @subpackage Helpers
 * @author Max Milbers
 * @copyright Copyright (c) 2010 VirtueMart Team. All rights reserved.
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

	var $autoOrdering = false;
	var $orderable = false;

    function setOrderable($key='ordering', $auto=true){
    	$this->orderingKey = $key;
    	$this->orderable = 1;
    	$this->autoOrdering = $auto;
    }

	function setSecondaryKey($key,$keyForm=0){
		$this->_skey 		= $key;
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

		$toSelect = '`'.$this->_skey.'`';
		if($this->orderable){
			$orderby = 'ORDER BY `'.$this->orderingKey.'`';
		} else {
			$orderby = '';
		}

		$q = 'SELECT '.$toSelect.' FROM `'.$this->_tbl.'` WHERE `'.$this->_pkey.'` = "'.$id.'" '.$orderby;
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

		if(!empty($data[$this->_pkeyForm])){
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
			foreach($this->_svalue as $value){

				$obj = new stdClass;

				$pkey = $this->_pkey;
				$obj->$pkey = $this->_pvalue;

				$skey = $this->_skey;
				$obj->$skey = $value;

				//When $value is an array, then we could add more values here.
				if($this->autoOrdering){
					$oKey = $this->orderingKey;
					$obj->$oKey = $this->ordering++;
				}

				$returnCode = $this->_db->insertObject($this->_tbl, $obj, $pkey);
			}
		}

		return $returnCode;

    }

}