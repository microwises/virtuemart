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


class VmXrefTable extends JTable {

	/** @var int Primary key */
//	var $au_idkey	= 'shoppergroup_id';
	var $_id		= 0;

	var $_pkey 		= '';
	var $pkeyForm	= '';
	var $_pvalue 	= '';

	var $_skey 		= '';
	var $skeyForm	= '';
	var $_svalue 	= array();

    /**
     * Records in this table are arrays. Therefore we need to overload the load() function.
     *
	 * @author Max Milbers
     * @param int $id
     */
    function load($id=0){
    	if(empty($this->_db)) $this->_db = JFactory::getDBO();
		if(!empty($id)) $this->_id = $id;

		$q = 'SELECT `'.$this->_skey.'` FROM `'.$this->_tbl.'` WHERE `'.$this->_pkey.'` = "'.$this->_id.'"';
		$this->_db->setQuery($q);
		dump($this->_db);
		$result = $this->_db->loadResultArray();
		if($this->_db->getError()){
			$this->setError( $this->_db->getErrorMsg() );
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

		$pkeyForm = empty($this->pkeyForm)? $this->_pkey:$this->pkeyForm;
		if(!empty($data[$pkeyForm])){
			$this->_pvalue = $data[$pkeyForm];
		}

		$skeyForm = empty($this->skeyForm)? $this->_skey:$this->skeyForm;
		if(!empty($data[$skeyForm])){
			$this->_svalue = $data[$skeyForm];
		}

		return true;

	}

    /**
     * @author Max Milbers
     * @param
     */
    function check($obligatory=false) {

        if (empty($this->_pvalue)) {
            $this->setError('Serious error cant save '.$this->_tbl.' without '.$this->_pkey);
            return false;
        }


		if (empty($this->_svalue) && $obligatory) {
            $this->setError('Serious error cant save '.$this->_tbl.' without '.$this->_skey);
            return false;
        }

        return true;
    }

    /**
     * Records in this table do not need to exist, so we might need to create a record even
     * if the primary key is set. Therefore we need to overload the store() function.
     *
     * @author Max Milbers
     * @see libraries/joomla/database/JTable#store($updateNulls)
     */
    public function store() {

		$db = JFactory::getDBO();

		$q  = 'DELETE FROM `'.$this->_tbl.'` WHERE `'.$this->_pkey.'` = "'. $this->_pvalue.'" ';
		$db->setQuery($q);
		$db->Query();
		if(!is_array($this->_svalue)) $this->_svalue=array($this->_svalue);

		/* Store the new categories */
		foreach( $this->_svalue as $dataid ) {
			$q  = 'INSERT INTO `'.$this->_tbl.'` ';
			$q .= '('.$this->_pkey.','.$this->_skey.') ';
			$q .= 'VALUES ("'.$this->_pvalue.'","'. $dataid . '")';
			$db->setQuery($q);
			$db->query();

		}

		//TODO enhance it maybe simular to this
//		$q = 'INSERT INTO #__virtuemart_product_manufacturers  (product_id, manufacturer_id) VALUES (';
//		$q .= $product_data->product_id.', ';
//		$q .= JRequest::getInt('manufacturer_id').') ';
//		$q .= 'ON DUPLICATE KEY UPDATE manufacturer_id = '.JRequest::getInt('manufacturer_id');
//		$this->_db->setQuery($q);
//		$this->_db->query();
		return true;

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
    }
}