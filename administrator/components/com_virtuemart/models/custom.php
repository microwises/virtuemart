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
		$this->setToggleName('admin_only');
		$this->setToggleName('is_hidden');
	}

    /**
     * Gets a single custom by virtuemart_custom_id
     * .
     * @param string $type
     * @param string $mime mime type of custom, use for exampel image
     * @return customobject
     */
    function getCustom(){

    	//if(empty($this->_db)) $this->_db = JFactory::getDBO();

   		$data = $this->getTable('customs');
   		$data->load($this->_id);
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
		$query='SELECT * FROM `#__virtuemart_customs` WHERE field_type <> "R" AND field_type <> "Z" ';
		if ($custom_parent_id = JRequest::getVar('custom_parent_id') ) $query .= 'AND `custom_parent_id` ='.$custom_parent_id;
		if ($keyword = JRequest::getVar('keyword') ) $query .= 'AND `custom_title` LIKE "%'.$keyword.'%"';
		$this->_db->setQuery($query);
		// set total for pagination
		$this->_total = $this->_getListCount($query);

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
		$this->_db->setQuery( 'DELETE PC.*,C.* FROM `#__virtuemart_'.$table.'_customfields` as `PC`, `#__virtuemart_customfields` as `C` WHERE `PC`.`virtuemart_customfield_id` = `C`.`virtuemart_customfield_id` AND  virtuemart_'.$table.'_id =' . $id );
		if(!$this->_db->query()){
			$this->setError('Error in saveModelCustomfields '.$this->_db->getQuery());
		}

		$customfieldIds = array();
		foreach($datas as $fields){
			$tableCustomfields = $this->getTable('customfields');
			$data = $tableCustomfields->bindChecknStore($fields);
    		$errors = $tableCustomfields->getErrors();
			foreach($errors as $error){
				$this->setError($error);
			}
			$customfieldIds[] = $data['virtuemart_customfield_id'];
		}

		$xrefData = array();
		$xrefData['virtuemart_'.$table.'_id']= $id;
		$xrefData['virtuemart_customfield_id']= $customfieldIds;

		// save Xref calues in right table
		$xrefTable = $this->getTable($table.'_customfields');
		$xrefTable->bindChecknStore($xrefData);
	    $errors = $xrefTable->getErrors();
		foreach($errors as $error){
			$this->setError($error);
		}

	}
}
// pure php no closing tag
