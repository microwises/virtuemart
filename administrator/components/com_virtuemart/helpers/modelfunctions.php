<?php
/**
*
* Helper for models
*
* @package	VirtueMart
* @subpackage  Calculation tool
* @author Max Milbers 
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: paymentmethod.php 2312 2010-02-19 13:09:31Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

class modelfunctions{

	/**
	 * Prepares the selection for the TreeLists
	 * 
	 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
	 * @author Max Milbers
	 * @param $value the selected values, may be single data or array
	 * @return $values prepared array to work with JHTML::_('Select.genericlist')
	 */
	function prepareTreeSelection($values){
		if (!isset($values)){
			return;
		}
		if (!is_array($values)) $values = array($values);
		foreach ($values as $value) {
			$values[$value]  = 1;
		}
		return $values;
	}

	/**
	 * Stores arrays in a table
	 * @author Max Milbers
	 * 
	 * @param $table the table
	 * @param $fieldId name of the field, that holds the id
	 * @param $fieldData name of the field that holds the data
	 * @param $id for the xref table
	 * @param $data data array
	 */
	function storeArrayData($table,$fieldId,$fieldData,$id,$data){
		$db = JFactory::getDBO();
		$q  = 'DELETE FROM `'.$table.'` WHERE `'.$fieldId.'` = "'.$id.'" ';
		$db->setQuery($q);
		$db->Query();
		
		if(!is_array($data)) $data=array($data);

		/* Store the new categories */
		foreach( $data as $dataid ) {
			$q  = 'INSERT INTO `'.$table.'` ';
			$q .= '('.$fieldId.','.$fieldData.') ';
			$q .= 'VALUES ("'.$id.'","'. $dataid . '")';
			$db->setQuery($q); 
			$db->query();
		}
	}
	
	/**
	 * Builds an enlist for information (not chooseable)
	 * @author Max Milbers
	 * 
	 * @param $fieldnameXref datafield for the xreftable, where the name is stored
	 * @param $tableXref xref table
	 * @param $fieldIdXref datafield for the xreftable, where the id is stored
	 * @param $idXref The id to query in the xref table
	 * @param $fieldname the name of the datafield in the main table
	 * @param $table main table
	 * @param $fieldId the name of the field where the id is stored
	 * @param $quantity The number of items in the list
	 * @return List as String
	 */
	 
	function buildGuiList ($fieldnameXref,$tableXref,$fieldIdXref,$idXref,$fieldname,$table,$fieldId,$quantity=4){
		
		$db = JFactory::getDBO();
		$q = 'SELECT '.$fieldnameXref.' FROM '.$tableXref.' WHERE '.$fieldIdXref.' = "'.$idXref.'"';
		$db->setQuery($q);
		$tempArray = $db->loadResultArray();
		if(isset($tempArray)){
			$list='';
			$i=0;
			foreach ($tempArray as $value) {
				$q = 'SELECT '.$fieldname.' FROM '.$table.' WHERE '.$fieldId.' = "'.$value.'"';
				$db->setQuery($q);
				$tmp = $db->loadResult();
				$list .= $tmp. ', ';
				$i++;
				if($i==$quantity) break;
			}
			return substr($list,0,-2);
		}else{
			return '';
		}
		
	}		
				
    function delete($idName,$tablename, $default=0) {

		$table =& $this->getTable($tablename);
		$ids = JRequest::getVar($idName,  0, '', 'array');

		foreach($ids as $id) {
		    if (!$table->delete($id)) {
				$this->setError($table->getError());
				return false;
		    }
		}
		return true;

    }


	function publish($idName, $tablename, $publishId = false) {

		$table = $this->getTable($tablename);
		$ids = JRequest::getVar( $idName, array(0), 'post', 'array' );
	
		if (!$table->publish($ids, $publishId)) {
			$this->setError($table->getError());
			return false;
		}
		
		return true;
    }
    
}   
?>
