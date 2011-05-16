<?php
/**
 * Xref table abstract class to create tables specialised doing xref
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

class VmTableData extends VmTable {


    /**
     * Records in this table do not need to exist, so we might need to create a record even
     * if the primary key is set. Therefore we need to overload the store() function.
     *
     * @author Max Milbers
     * @see libraries/joomla/database/JTable#store($updateNulls)
     */
    public function store() {

    	$id = 0;
    	$pKey = $this->_pkey;
    	if(!empty($this->$pKey)){
	        $_qry = 'SELECT `'.$this->_tbl_key.'` '
	                . 'FROM `'.$this->_tbl.'` '
	                . 'WHERE `'.$this->_pkey.'` = "' . $this->$pKey.'" ';
	        $this->_db->setQuery($_qry);
	        $id = $this->_db->loadResult();
    	}
		dump($_qry,'store vmtabledata '.$id);
		dump($this,'store vmtabledata');
        if ( !empty($id) ) {
        	$tblKey = $this->_tbl_key;
        	$this->$tblKey = $id;
            $returnCode = $this->_db->updateObject($this->_tbl, $this, $this->_tbl_key, false);
        } else {
            $returnCode = $this->_db->insertObject($this->_tbl, $this, $this->_tbl_key);
        }

        if (!$returnCode) {
            $this->setError(get_class($this) . '::store failed - ' . $this->_db->getErrorMsg());
            return false;
        }
        else
            return true;
    }


}