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

/**
 * Model for VirtueMart Customs Fields
 *
 * @package		VirtueMart
 */
class VirtueMartModelCustom extends JModel {

	/** @var integer Primary key */
    private $custom_id = 0;

   /** @var integer Total number of files in the database */
    var $_total;
    /** @var pagination Pagination for file list */
    var $_pagination;
    /** @var datas  internal use to stock 'custom' datas */
    var $_datas;


	/**
	 * Constructor for product files
	 */
	function __construct(){
		parent::__construct();

//		$this->custom_id = $id;

		/* Get the custom ID */
		$this->setId(JRequest::getInt('custom_id', null));

		// Get the pagination request variables
		$mainframe = JFactory::getApplication() ;
		$limit = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$limitstart = $mainframe->getUserStateFromRequest( JRequest::getVar('option').JRequest::getVar('view').'.limitstart', 'limitstart', 0, 'int' );

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

	}

	/**
	 * Sets new Id and resets data ...
	 * @author Max Milbers
	 * @param int $id
	 */
    function setId($id) {
		$this->custom_id = $id;
		$this->_data = null;
    }

	/**
	 * Loads the pagination
	 *
	 * @author RickG
	 */
    public function getPagination() {
		if (empty($this->_pagination)) {
	    	jimport('joomla.html.pagination');
	    	$this->_pagination = new JPagination($this->_getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}
		return $this->_pagination;
	}

    /**
     * Gets the total number of currencies
     *
     * @author Max Milbers
     * @return int Total number of currencies in the database
     */
    function _getTotal() {
		if (empty($this->_total)) {
		    $query = 'SELECT `custom_id` FROM `#__vm_custom`';
		    $this->_total = $this->_getListCount($query);
		}
		return $this->_total;
    }

    /**
     * Gets a single custom by custom_id
     * .
     * @param string $type
     * @param string $mime mime type of custom, use for exampel image
     * @return customobject
     */
    function getCustom(){

    	if(empty($this->_db)) $this->_db = JFactory::getDBO();

   		$data = $this->getTable('Custom');
   		$data->load($this->custom_id);
		if (!class_exists('VmCustomHandler')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'customhandler.php');

  		$custom = VmCustomHandler::createCustom($data);

  		return $custom;

    }
    /**
     * Gets a single custom by custom_id
     * .
     * @param int $product_id
     * @return customobject
     */
    function getProductCustoms($product_id){

		$query='SELECT * FROM `#__vm_custom_field` 
		left join `#__vm_custom_field_xref_product` on  `#__vm_custom_field_xref_product`.`custom_field_id` = `#__vm_custom_field`.`custom_field_id` 
		and product_id='.$product_id;
		$this->_db->setQuery($query);
		$this->_datas->productCustoms = $this->_db->loadObjectList();
		$this->_datas->customFields = self::getCustoms() ;

  		return $this->_datas;

    }
	
	

    /**
	 * Retireve a list of customs from the database. This is meant only for backend use
	 *
	 * @author Kohl Patrick
	 * @return object List of custom objects
	 */
    function getCustoms(){

		$this->_db = JFactory::getDBO();
		$query='SELECT * FROM `#__vm_custom` ';
		if ($custom_parent_id = JRequest::getVar('custom_parent_id') ) $query .= 'WHERE custom_parent_id ='.$custom_parent_id;
		if ($keyword = JRequest::getVar('keyword') ) $query .= 'WHERE custom_title LIKE "%'.$keyword.'%"';
		$this->_db->setQuery($query);
		$datas->items = $this->_db->loadObjectList();

		if (!class_exists('VmCustomHandler')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'customhandler.php');
		if (!class_exists('VmHTML')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'html.php');
		$datas->field_types = VmCustomHandler::getField_types() ;
		foreach ($datas->items as $key => & $data) {
  		if (!empty($data->custom_parent_id)) $data->custom_parent_title = VmCustomHandler::getCustomParentTitle($data->custom_parent_id);
		else { 
			$data->custom_parent_title =  '-' ;
		}
  		$data->field_type_display = $datas->field_types[$data->field_type ];
		}
		$datas->customsSelect=VmCustomHandler::displayCustomSelection();
		
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

		$oldId = $data['custom_id'];
		$this -> setId($oldId);
		$custom_id = $this->store($type,$data);

		/* add the custom_id & delete 0 and '' from $data */
		$data['custom_ids'] = array_merge( (array)$data['custom_id'],$data['custom_ids']);
		$custom_ids = array_diff($data['custom_ids'],array('0',''));

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
			return true;
//		}
	}

	/**
	 * Store an entry of a customItem,
	 *
	 * @author Kohl Patrick
	 */
	public function store($data=0) {

		$table = $this->getTable('custom');
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

		return $table->custom_id;
	}

	/**
	 * Delete an custom field
	 * @author unknow, maybe Roland Dalmulder
	 * @author Max Milbers
	 */
	public function delete($cids) {
		$mainframe = Jfactory::getApplication('site');
//		$deleted = 0;
	 	$row = $this->getTable('custom');
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
		$mainframe->enqueueMessage(str_replace('{X}', $deleted, JText::_('COM_VIRTUEMART_DELETED_X_CUSTOM_FIELD_ITEMS')));

	}

	/**
	 * Publish/Unpublish all the ids selected
     *
     * @author Max Milbers
     * @param boolean $publishId True is the ids should be published, false otherwise.
     * @return boolean True is the delete was successful, false otherwise.
     */
	public function publish($publishId = false)
	{
		if(!class_exists('modelfunctions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'modelfunctions.php');
		return modelfunctions::publish('cid','custom',$publishId);

	}	/**
	 * Publish/Unpublish all the ids selected
     *
     * @author Kohl Patrick
     * @param boolean $publishId True is the ids should be published, false otherwise.
     * @return boolean True is the delete was successful, false otherwise.
     */
	public function toggle($field)
	{
		if(!class_exists('modelfunctions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'modelfunctions.php');
		return modelfunctions::toggle('custom',$field,'cid');
	}

	/* Save and delete from database
	* all product custom_fields and xref
	*/
	public function  saveProductfield($fields, $product_id) {

		$newIds = array();

		foreach ($fields as $field) {
			$q = 'REPLACE INTO `#__vm_custom_field` ( `custom_field_id` ,`custom_id` , `custom_value`, `custom_price`  )';
			$q .= " VALUES( '".$field['custom_field_id']."', '".$field['custom_id']."', '". $field['custom_value'] ."', '". $field['custom_price'] ."') ";
			$this->_db->setQuery($q);
			$this->_db->query();
			$custom_field_id = mysql_insert_id();
			$newIds[]=$custom_field_id;
			$q = 'REPLACE INTO #__vm_custom_field_xref_product ( custom_field_id , product_id  )';
			$q .= " VALUES( '".$custom_field_id."', '". $product_id ."') ";
			$this->_db->setQuery($q);
			$this->_db->query();
		}
		
		// slect all custom_field_id from product
		$q="select custom_field_id from `#__vm_custom_field_xref_product` where product_id=".$product_id ;
		$this->_db->setQuery($q);
		$Ids = $this->_db->loadResultArray();
		// delete from database old unused product custom fields
		$deleteIds = array_diff(  $Ids,$newIds);
		$id = '('.implode (',',$deleteIds).')';
				$this->_db->setQuery('DELETE from `#__vm_custom_field_xref_product` WHERE `custom_field_id` in  ' . $id);
		if ($this->_db->query() === false) {
			$this->setError($this->_db->getError());
			return false;
		}
		$this->_db->setQuery('DELETE from `#__vm_custom_field` WHERE `custom_field_id` in  ' . $id);
		if ($this->_db->query() === false) {
			$this->setError($this->_db->getError());
			return false;
		}
	}

}
// pure php no closing tag
