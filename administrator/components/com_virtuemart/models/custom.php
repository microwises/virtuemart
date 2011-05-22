<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage
* @author Max Milbers
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved by the author.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: custom.php 3057 2011-04-19 12:59:22Z Electrocity $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the model framework
jimport( 'joomla.application.component.model');

if(!class_exists('VmModel'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmmodel.php');

/**
 * Model for VirtueMart Customs Fields
 *
 * @package		VirtueMart
 */
class VirtueMartModelCustom extends VmModel {

	/**
	 * constructs a VmModel
	 * setMainTable defines the maintable of the model
	 * @author Max Milbers
	 */
	function __construct() {
		parent::__construct('virtuemart_custom_id');
		$this->setMainTable('customs');
	}

	/** @var integer Primary key */
    private $virtuemart_custom_id = 0;


//	/**
//	 * Constructor for product files
//	 */
//	function __construct(){
//		parent::__construct();
//
////		$this->virtuemart_custom_id = $id;
//
//		/* Get the custom ID */
//		$this->setId(JRequest::getInt('virtuemart_custom_id', null));
//
//		// Get the pagination request variables
//		$mainframe = JFactory::getApplication() ;
//		$limit = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
//		$limitstart = $mainframe->getUserStateFromRequest( JRequest::getVar('option').JRequest::getVar('view').'.limitstart', 'limitstart', 0, 'int' );
//
//		$this->setState('limit', $limit);
//		$this->setState('limitstart', $limitstart);
//
//	}


    /**
     * Gets a single custom by virtuemart_custom_id
     * .
     * @param string $type
     * @param string $mime mime type of custom, use for exampel image
     * @return customobject
     */
    function getCustom(){

    	if(empty($this->_db)) $this->_db = JFactory::getDBO();

   		$data = $this->getTable('customs');
   		$data->load($this->virtuemart_custom_id);
		if (!class_exists('VmCustomHandler')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'customhandler.php');

  		$custom = VmCustomHandler::createCustom($data);

  		return $custom;

    }
    /**
     * Gets a single custom by virtuemart_custom_id
     * .
     * @param int $virtuemart_product_id
     * @return customobject
     */
    function getProductCustoms($virtuemart_product_id){

		$query='SELECT * FROM `#__virtuemart_customfields`
		left join `#__virtuemart_product_customfields` on  `#__virtuemart_product_customfields`.`virtuemart_customfield_id` = `#__virtuemart_customfields`.`virtuemart_customfield_id`
		and `virtuemart_product_id`='.$virtuemart_product_id;
		$this->_db->setQuery($query);
		$this->_data->productCustoms = $this->_db->loadObjectList();
		$this->_data->customFields = self::getCustoms() ;

  		return $this->_data;

    }



    /**
	 * Retireve a list of customs from the database. This is meant only for backend use
	 *
	 * @author Kohl Patrick
	 * @return object List of custom objects
	 */
    function getCustoms(){

		$this->_db = JFactory::getDBO();
		$query='SELECT * FROM `#__virtuemart_customs` ';
		if ($custom_parent_id = JRequest::getVar('custom_parent_id') ) $query .= 'WHERE `custom_parent_id` ='.$custom_parent_id;
		if ($keyword = JRequest::getVar('keyword') ) $query .= 'WHERE `custom_title` LIKE "%'.$keyword.'%"';
		$this->_db->setQuery($query);
		$datas->items = $this->_db->loadObjectList();

		$data = $this->getTable('customs');
//   		$data->load($this->virtuemart_custom_id);
		if (!class_exists('VmCustomHandler')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'customhandler.php');
		$customHandler = VmCustomHandler::createCustom($data);
		if (!class_exists('VmHTML')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'html.php');
		$datas->field_types = $customHandler->getField_types() ;

		foreach ($datas->items as $key => & $data) {
  		if (!empty($data->custom_parent_id)) $data->custom_parent_title = $customHandler->getCustomParentTitle($data->custom_parent_id);
		else {
			$data->custom_parent_title =  '-' ;
		}
  		$data->field_type_display = $datas->field_types[$data->field_type ];
		}
		$datas->customsSelect=$customHandler->displayCustomSelection();

		return $datas;
    }


    /**
     * This function stores a custom and updates then the refered table
     *
     * @author Max Milbers
     * @author Patrick Kohl
     * @param unknown_type $data
     * @param unknown_type $table
     * @param unknown_type $type
     */
	function storeCustom($data,$table,$type){

		// Check token, how does this really work?
//		JRequest::checkToken() or jexit( 'Invalid Token, while trying to save custom' );

		$oldId = $data['virtuemart_custom_id'];
		$this -> setId($oldId);
		$virtuemart_custom_id = $this->store($type,$data);

		/* add the virtuemart_custom_id & delete 0 and '' from $data */
		$data['virtuemart_custom_ids'] = array_merge( (array)$data['virtuemart_custom_id'],$data['virtuemart_custom_ids']);
		$virtuemart_custom_ids = array_diff($data['virtuemart_custom_ids'],array('0',''));

		// Bind the form fields to the table
		if (!$table->bind($data)) {
			$this->setError($table->getError());
			return false;
		}

		// Make sure the record is valid
		if (!$table->check()) {
			$this->setError($table->getError());
			return false;
		}

		// Save the record to the database
		if (!$table->store()) {
			$this->setError($table->getError());
			return false;
		}
		$dbv = $table->getDBO();
		if(empty($this->_id)) $this->_id = $dbv->insertid();

		return $this->_id;

	}

	/**
	 * Store an entry of a customItem,
	 *
	 * @author Kohl Patrick
	 */
	public function store($data=0) {

		$table = $this->getTable('customs');
		if(empty($data))$data = JRequest::get('post');

		if (!class_exists('VmCustomHandler')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'customhandler.php');

		// Bind the form fields to the table
		if (!$table->bind($data)) {
			$this->setError($table->getError());
			return false;
		}
		// Make sure the record is valid
		if (!$table->check()) {
			if($table->getError()){
				foreach($table->getErrors() as $error){
					$this->setError($error);
				}
			}
			return false;
		}
		// Save the record to the database
		if (!$table->store()) {
			$this->setError($table->getError());
			return false;
		}
		//}

		return $table->virtuemart_custom_id;
	}

	/**
	 * Delete an custom field
	 * @author unknow, maybe Roland Dalmulder
	 * @author Max Milbers
	 */
	public function delete($cids) {
		$mainframe = Jfactory::getApplication('site');
//		$deleted = 0;
	 	$row = $this->getTable('customs');
//	 	$cids = JRequest::getVar('cid');

	 	if (is_array($cids)) {
			foreach ($cids as $key => $cid) {
				//$row->load($cid);
				if ($row->delete($cid)) $deleted++;
			}
		}
		else {
			//$row->load($cids);
			if ($row->delete($cid)) $deleted++;
		}
		$mainframe->enqueueMessage( JText::sprintf('COM_VIRTUEMART_DELETED_X_CUSTOM_FIELD_ITEMS', $deleted ));

	}
	/**
	 * Creates a clone of a given custom id
	 *
	 * @author Max Milbers
	 * @param int $virtuemart_product_id
	 */

	public function createClone($id){
		$this->virtuemart_custom_id = $id;
		$row = $this->getTable('customs');
		$row->load( $id );
		$row->virtuemart_custom_id = 0;
		$row->custom_title = $row->custom_title.' Copy';

		if (!$row->store()) {
			JError::raiseError(500, $row->getError() );
		}
		return $row->store($clone);
	}

	/* Save and delete from database
	* all product custom_fields and xref
	@ var   $table	: the xref table(eg. product,category ...)
	@array $data	: array of customfields
	@int     $id		: The concerned id (eg. product_id)
	*/
	public function saveModelCustomfields($table,$datas, $id) {
		// delete existings from modelXref and table customfields
		$this->_db->setQuery( 'DELETE PC,C FROM `#__virtuemart_'.$table.'_customfields` as PC, `#__virtuemart_customfields` as C WHERE `virtuemart_customfield_id`.PC = `virtuemart_customfield_id`.C AND  virtuemart_'.$table.'_id =' . $id );
		$this->_db->query();
		$xrefData = array();
		$xrefData['virtuemart_'.$table.'_id']= $id;
		$tableCustomfields = $this->getTable('customfields');
		dump($datas,'Field Values');
		foreach($datas as &$fields){
			// Save the fields value 
			if (!$tableCustomfields->bindChecknStore($fields)) {
			$this->setError($xrefTable->getError());
			}
			// set the Xref customfield_id
			$xrefData['virtuemart_customfield_id'][]= $tableCustomfields->_db->insertid();
		}
		// save Xref calues in right table
		$xrefTable = $this->getTable($table.'_customfields');
		if (!$xrefTable->bindChecknStore($xrefData)) {
			$this->setError($xrefTable->getError());
		}
		dump($xrefData,'Xref for '.$table);

//		$newIds = array();
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
//				
//		if ($this->_db->query() === false) {
//			$this->setError($this->_db->getError());
//			return false;
//		}
//		$this->_db->setQuery('DELETE from `#__virtuemart_customfields` WHERE `virtuemart_customfield_id` in  ' . $id);
//		if ($this->_db->query() === false) {
//			$this->setError($this->_db->getError());
//			return false;
//		}
	}

}
// pure php no closing tag
