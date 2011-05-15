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


class VmXarrayTable extends JTable {

	/** @var int Primary key */

	var $autoOrdering = false;
	var $orderable = false;

    function setOrderable($key='ordering',$auto=true){
    	$this->orderingKey = $key;
    	$this->orderable = 1;
    	$this->autoOrdering = $auto;

    }

    function setPrimaryKey($key,$keyForm=0){
    	$this->_pkey = $key;
    	$this->_pkeyForm = empty($keyForm)? $key:$keyForm;
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
    		$this->setError( 'No secondary keys defined in VmXarrayTable '.$this->_tbl );
    		return false;
    	}

    	if(empty($this->_db)) $this->_db = JFactory::getDBO();
		if(!empty($id)) $this->_id = $id;


		$toSelect = '`'.$this->_skey.'`';
		if($this->orderable){
			$orderby = 'ORDER BY `'.$this->orderingKey.'`';
		} else {
			$orderby = '';
		}

		$q = 'SELECT '.$toSelect.' FROM `'.$this->_tbl.'` WHERE `'.$this->_pkey.'` = "'.$this->_id.'" '.$orderby;
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
     * @author Max Milbers
     * @param
     */
    function check($obligatory=false) {

        if (empty($this->_pvalue)) {
            $this->setError('Serious error cant save '.$this->_tbl.' without primary key value '.$this->_pkey);
            return false;
        }

		if (empty($this->_svalue) && $obligatory) {
            $this->setError('Serious error cant save '.$this->_tbl.' without '.$this->_skey);
            return false;
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

//////		$returnCode = $this->_db->insertObject($this->_tbl, $this, $this->_tbl_key);
//////		foreach( $this->_svalue as $dataid ) {
//////			$q  = 'INSERT INTO `'.$this->_tbl.'` ';
//////			$q .= '('.$this->_pkey.','.$this->_skey.', ordering ) ';
//////			$q .= 'VALUES ("'.$this->_pvalue.'","'. $dataid . '", "'.$this->ordering++.'")';
//////			$db->setQuery($q);
//////			$db->query();
//////		}
////
////		//TODO enhance it maybe simular to this
//////		$q = 'INSERT INTO #__virtuemart_product_manufacturers  (virtuemart_product_id, virtuemart_manufacturer_id) VALUES (';
//////		$q .= $product_data->virtuemart_product_id.', ';
//////		$q .= JRequest::getInt('virtuemart_manufacturer_id').') ';
//////		$q .= 'ON DUPLICATE KEY UPDATE virtuemart_manufacturer_id = '.JRequest::getInt('virtuemart_manufacturer_id');
//////		$this->_db->setQuery($q);
//////		$this->_db->query();
		$this->_id = $this->_db->insertid();

		return true;;

    }

    /**
     * As shortcat
     *
     * @author Max Milbers
     * @param unknown_type $model
     * @param unknown_type $data
     * @param unknown_type $obligatory
     */
    public function bindChecknStore($model, $data, $obligatory=false) {

    	if (!$this->bind($data)) {
			$model->setError($this->getError());
			return false;
		}

		// Make sure the calculation record is valid
		if (!$this->check($obligatory)) {
			$model->setError($this->getError());
			return false;
		}

		// Save the record to the database
		if (!$this->store()) {
			$model->setError($this->getError());
			return false;
		}
		$data[$this->_tbl_key] = $this->_id;
		return $data;
//		return $this->_id;
    }
}