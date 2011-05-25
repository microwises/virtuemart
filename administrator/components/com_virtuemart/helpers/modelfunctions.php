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
* @version $Id$
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
		if(!$data) return false;
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

		//TODO enhance it maybe simular to this
//		$q = 'INSERT INTO #__virtuemart_product_manufacturers  (virtuemart_product_id, virtuemart_manufacturer_id) VALUES (';
//		$q .= $product_data->virtuemart_product_id.', ';
//		$q .= JRequest::getInt('virtuemart_manufacturer_id').') ';
//		$q .= 'ON DUPLICATE KEY UPDATE virtuemart_manufacturer_id = '.JRequest::getInt('virtuemart_manufacturer_id');
//		$this->_db->setQuery($q);
//		$this->_db->query();
		return true;
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

	function buildGuiList ($fieldnameXref,$tableXref,$fieldIdXref,$idXref,$fieldname,$table,$fieldId,$view,$quantity=4){

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
				$list .= JHTML::_('link', JRoute::_('index.php?option=com_virtuemart&view='.$view.'&task=edit&cid[]='.$value), $tmp). ', ';
//				$list .= $tmp. ', ';
				$i++;
				if($i==$quantity) break;
			}
			return substr($list,0,-2);
		}else{
			return '';
		}

	}

	/**
	 * does the deletion of a row
	 * @author Max Milbers
	 */
    function delete($idName,$tablename, $default=0) {

		$table =& $this->getTable($tablename);
		$ids = JRequest::getVar($idName,  0, '', 'array');

		foreach($ids as $id) {
		    if (!$table->delete($id)) {
				$this->setError('modelfunctions: delete, uses deprecated '.$table->getError());
				return false;
		    }
		}
		return true;
    }

	/**
	 * does the publishing of a row
	 * @author Max Milbers
	 */
	function publish($idName, $tablename, $publishId = false) {

		$table = $this->getTable($tablename);

		$ids = JRequest::getVar( $idName, array(0), 'post', 'array' );
		if (!$table->publish($ids, $publishId)) {
			$this->setError('modelfunctions: publish, uses deprecated '.$table->getError());
			return false;
		}

		return true;
    }

//	/**
//	 * toggle (0/1) a unique row
//	 * @author Patrick Kohl
//	 * @param string $tablename the selected table
//	 * @param string $field the field to toggle
//	 * @param string $postName the name of id Post  (same as in table Class constructor)
//	 */
//
//	function toggle($tablename, $field, $postName  ) {
//
//		$table =& $this->getTable($tablename);
//		$ids = JRequest::getVar( $postName, array(0), 'post', 'array' );
//		// load the row
//		$table->load( (int)$ids[0] );
//		if ($table->$field ==0) $table->$field = 1 ;
//		else $table->$field = 0;
//		if (!$table->store()) {
//				JError::raiseError(500, $row->getError() );
//			return false;
//		}
//		return true;
//    }

    /**
	 * Loads a row from the database and binds the fields to the object properties
	 * Derived from the joomla table load function
	 * @author joomlaTeam, Max Milbers
	 * @access	public
	 *
	 * @param 	JTable the table
	 * @param	mixed	Optional primary key.  If not specifed, the value of current key is used
	 * @return	boolean	True if successful
	 */
	function loadConsiderDate( $table, $oid=null )
	{
		$k = $table->_tbl_key;

		if ($oid !== null) {
			$table->$k = $oid;
		}

		$oid = $table->$k;

		if ($oid === null) {
			return false;
		}
		$table->reset();

		$db =& $table->getDBO();

		$nullDate		= $db->getNullDate();
		$now			=& JFactory::getDate()->toMySQL();

		$query = 'SELECT *'
		. ' FROM '.$table->_tbl
		. ' WHERE '.$table->_tbl_key.' = '.$db->Quote($oid);
		$query .= ' AND ( publish_up = '.$db->Quote($nullDate).' OR publish_up <= '.$db->Quote($now).' )' .
			' AND ( publish_down = '.$db->Quote($nullDate).' OR publish_down >= '.$db->Quote($now).' ) ';

		$this->_db->setQuery( $query );

		if ($result = $this->_db->loadAssoc( )) {
			return $table->bind($result);
		}
		else
		{
			$this->setError( 'modelfunctions: loadConsiderDate '.$this->_db->getErrorMsg() );
			return false;
		}
	}
}
// pure php no closing tag
