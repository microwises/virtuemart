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

jimport( 'joomla.user.user' );

class VmTable extends JTable {

	/** @var int Primary key */
//	private $_id		= 0;

//	private $_pkey 		= 0;
	private $_obkeys	= array();
	private $_unique	= false;
	private $_unique_name = array();

    function setPrimaryKey($key,$keyForm=0,$langkey=0){
    	$this->setObligatoryKeys('_pkey',$langkey);
    	$this->_pkey = $key;
    	$this->_pkeyForm = empty($keyForm)? $key:$keyForm;
    }

	public function setObligatoryKeys($key,$langkey=0){

		$this->_obkeys[$key] = $langkey;
	}

	public function setUniqueName($name,$langkey){
		$this->_unique = true;
		$this->_obkeys[$name] = $langkey;
		$this->_unique_name[$name] = $langkey;
	}

	public function setLoggable(){
	    $this->created_on = '';
        $this->created_by = 0;
        $this->modified_on = '';
        $this->modified_by = 0;
	}

	public function setLockable(){
		$this->locked_on = '';
		$this->locked_by = 0;
	}

    /**
     * @author Max Milbers
     * @param
     */
    function check($obligatory=false) {

    	foreach($this->_obkeys as $obkeys => $error){
    		if (empty($this->$obkeys)) {
    			if(empty($error)){
    				$this->setError('Serious error cant save '.$this->_tbl.' without '.$obkeys);
    			} else {
    				$this->setError(JText::_($error));
    			}
            	return false;
        	}
    	}

    	if ($this->_unique) {
		    $db = JFactory::getDBO();
		    foreach($this->_unique_name as $obkeys => $error){

		   		$q = 'SELECT `'.$this->_tbl_key.'`,`'.$obkeys.'` FROM `'.$this->_tbl.'` ';
				$q .= 'WHERE `'.$obkeys.'`="' .  $this->$obkeys . '"';
	            $db->setQuery($q);
			    $unique_id = $db->loadResultArray();

			    $tblKey = $this->_tbl_key;

				if (!empty($unique_id) && $unique_id[0]!=$this->$tblKey) {
					if(empty($error)){
						$this->setError(JText::_($error));
					} else {
						$this->setError('Error cant save '.$this->_tbl.' without a non unique '.$obkeys);
					}

					return false;
				}
		    }

		}

       	$date = JFactory::getDate();
		$today = $date->toMySQL();
		$user = JFactory::getUser();

        if(isset($this->created_on) && empty($this->created_on) ){
        	$this->created_on = $today;
        	$this->created_by = $user->id;
        }

        if(isset($this->modified_on) ){
        	$this->modified_on = $today;
        	$this->modified_by = $user->id;
        }

        if(isset($this->locked_on) ){
        	$this->locked_on = 0;
        }

        //This is a hack for single, shouldnt be used, when we write multivendor there should be message
        if(isset($this->virtuemart_vendor_id)){
        	if(empty($this->virtuemart_vendor_id)) $this->virtuemart_vendor_id = 1;
        }

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

		// Make sure the table record is valid
		if (!$this->check($obligatory)) {
			$model->setError($this->getError());
			return false;
		}

		// Save the record to the database
		if (!$this->store()) {
			$model->setError($this->getError());
			return false;
		}
		$tblKey = $this->_tbl_key;
		$data[$this->_tbl_key] = $this->$tblKey;

		return $data;
    }
}