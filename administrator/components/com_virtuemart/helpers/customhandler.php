<?php
/**
 * Custom custom handler class
 *
 * This class provides some custom handling functions that are used throughout the VirtueMart shop.
 *  Uploading, moving, deleting
 *
 * @package	VirtueMart
 * @subpackage Helpers
 * @author Max Milbers
 * @copyright Copyright (c) 2011 VirtueMart Team. All rights reserved by the author.
 */

defined('_JEXEC') or die();

class VmCustomHandler {

	var $custom_attributes = 0;


	private function __construct($id=0){

		$this->virtuemart_custom_id = $id;
	}

	public function getCustomParentTitle($custom_parent_id) {

    	$q='SELECT custom_title FROM `#__virtuemart_customs` WHERE virtuemart_custom_id ='.$custom_parent_id;
		if(empty($this->_db)) $this->_db = JFactory::getDBO();

		$this->_db->setQuery($q);
		return $this->_db->loadResult();
	}
	/** @return autorized Types of data **/
    function getField_types(){

		return array( 'S' =>'COM_VIRTUEMART_CUSTOM_STRING',
			'I'=>'COM_VIRTUEMART_CUSTOM_INT',
			'P'=>'COM_VIRTUEMART_CUSTOM_PARENT',
			'B'=>'COM_VIRTUEMART_CUSTOM_BOOL',
			'D'=>'COM_VIRTUEMART_DATE',
			'T'=>'COM_VIRTUEMART_TIME',
			'C'=>'COM_VIRTUEMART_CUSTOM_PRODUCT_CHILD',
			'M'=>'COM_VIRTUEMART_IMAGE',
			'V'=>'COM_VIRTUEMART_CUSTOM_CART_VARIANT',
			'U'=>'COM_VIRTUEMART_CUSTOM_CART_USER_VARIANT'
			);
//			'R'=>'COM_VIRTUEMART_RELATED_PRODUCT',
//			'Z'=>'COM_VIRTUEMART_RELATED_CATEGORY',
    }
	/**
	 * This function determines the type of a custom and creates it.
	 * When you want to write a child class of the customhandler, you need to manipulate this function.
	 * We may use later here a hook for plugins or simular
	 *
	 * @author Max Milbers
	 * @param object $table
	 * @param string  $type vendor,product,category,...
	 * @param string $file_mimetype such as image/jpeg
	 */
	public function createCustom($table){

		$custom = new VmCustomHandler();

		$attribs = get_object_vars($table);

		foreach($attribs as $k=>$v){
			$custom->$k = $v;
		}

		return $custom;
	}

	/**
	 * This prepares the object for storing the data. This means it does the action
	 * and returns the data for storing in the table
	 *
	 * @author Max Milbers
	 * @param object $table
	 * @param array $data
	 * @param string $type
	 * OBSELETE
	 */
	public function prepareStoreCustom($data,$table){

		$custom = VmCustomHandler::createCustom($data);
		return $custom;//$data;
	}

	private $_hidden = array();

	/**
	 * Use this to adjust the hidden fields of the displaycustomHandler to your form
	 *
	 * @author Max Milbers
	 * @param string $name for exampel view
	 * @param string $value for exampel custom
	 */
	public function addHidden($name, $value=''){
		$this->_hidden[$name] = $value;
	}

	/**
	 * Adds the hidden fields which are needed for the form in every case
	 * @author Max Milbers
	 * OBSELTE ?
	 */
	private function addHiddenByType(){

		VmCustomHandler::addHidden('virtuemart_custom_id',$this->virtuemart_custom_id);
		VmCustomHandler::addHidden('option','com_virtuemart');

	}

	/**
	 * Displays custom handler and file selector
	 *
	 * @author Max Milbers
	 * @param array $fileIds
	 */
	public function displayCustom($field_types){

		$html = VmCustomHandler::displayCustomSelection();
		$html .= VmCustomHandler::displayCustomFields('id="vm_display_image"',$field_types);
		return $html;
	}

	/**
	 * Displays a possibility to select created custom
	 * @author Max Milbers
	 * @author Patrick Kohl
	 */
	public function displayCustomSelection(){

		$customslist = VmCustomHandler::getCustomsList();
		if (isset($this->virtuemart_custom_id)) $value = $this->virtuemart_custom_id ;
		else $value = JRequest::getVar( 'custom_parent_id',0);
		return  VmHTML::selectRow('COM_VIRTUEMART_CUSTOM_PARENT',$customslist, 'custom_parent_id', $value);
	}

    /**
     * Retrieve a list of layouts from the default and choosen templates directory.
     *
     * We may use here the getCustoms function of the custom model or write something simular
     * @author Max Milbers
     * @param name of the view
     * @return object List of flypage objects
     */
    function getCustomsList( $publishedOnly = FALSE ) {
    	$vendorId=1;
		// get custom parents
    	$q='SELECT virtuemart_custom_id as value ,custom_title as text FROM `#__virtuemart_customs` where custom_parent_id=0 
			AND field_type <> "R" AND field_type <> "Z" ';
		if ($publishedOnly) $q.='AND `published`=1';
		if ($ID = JRequest::getVar( 'virtuemart_custom_id',false)) $q .=' and `virtuemart_custom_id`!='.$ID;
		//if (isset($this->virtuemart_custom_id)) $q.=' and virtuemart_custom_id !='.$this->virtuemart_custom_id;
		if(empty($this->_db)) $this->_db = JFactory::getDBO();

		$this->_db->setQuery($q);
//		$result = $this->_db->loadAssocList();
		$result = $this->_db->loadObjectList();

    	$errMsg = $this->_db->getErrorMsg();
		$errs = $this->_db->getErrors();

		if(!empty($errMsg)){
			$app =& JFactory::getApplication();
			$errNum = $this->_db->getErrorNum();
			$app->enqueueMessage('SQL-Error: '.$errNum.' '.$errMsg);
		}

		if($errs){
			$app =& JFactory::getApplication();
			foreach($errs as $err){
				$app->enqueueMessage($err);
			}
		}

		return $result;
    }
	/**
	 * This displays a custom handler.
	 *
	 * @param string $html atttributes, Just for displaying the fullsized image
	 */
	public function displayCustomFields($imageArgs='',$field_types){

		$identify = ''; // ':'.$this->virtuemart_custom_id;
		if (!class_exists('VmHTML')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'html.php');
		if ($this->field_type) VmCustomHandler::addHidden('field_type',$this->field_type);
		VmCustomHandler::addHiddenByType();

		$html = '<div id="custom_title">'.$this->custom_title.'</div>';
		$html .= ' <table class="adminform"> ';

		if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');
		if(!Permissions::getInstance()->check('admin') ) $readonly='readonly'; else $readonly ='';
		$html .= VmHTML::inputRow('COM_VIRTUEMART_TITLE','custom_title',$this->custom_title,VmHTML::validate('S'));
		$html .= VmHTML::inputRow('COM_VIRTUEMART_DESCRIPTION','custom_field_desc',$this->custom_field_desc);
		// change input by type
		$html .= VmHTML::inputRow('COM_VIRTUEMART_DEFAULT','custom_value',$this->custom_value);
		$html .= VmHTML::inputRow('COM_VIRTUEMART_CUSTOM_TIP','custom_tip',$this->custom_tip);
		$html .= VmHTML::selectRow('COM_VIRTUEMART_CUSTOM_PARENT',VmCustomHandler::getCustomsList(), 'custom_parent_id', $this->custom_parent_id,'');
		$html .= VmHTML::booleanRow('COM_VIRTUEMART_PUBLISHED','published',$this->published);
		$html .= VmHTML::booleanRow('COM_VIRTUEMART_CUSTOM_ADMIN_ONLY','admin_only',$this->admin_only);
		$html .= VmHTML::booleanRow('COM_VIRTUEMART_CUSTOM_IS_LIST','is_list',$this->is_list);
		$html .= VmHTML::booleanRow('COM_VIRTUEMART_CUSTOM_IS_HIDDEN','is_hidden',$this->is_hidden);
		$html .= VmHTML::booleanRow('COM_VIRTUEMART_CUSTOM_IS_CART_ATTRIBUTE','is_cart_attribute',$this->is_cart_attribute);
		// only input when not set else display
		if ($this->field_type) $html .= VmHTML::Row('COM_VIRTUEMART_CUSTOM_FIELD_TYPE', $field_types[$this->field_type] ) ;
		else $html .= VmHTML::selectRow('COM_VIRTUEMART_CUSTOM_FIELD_TYPE',VmCustomHandler::getOptions($field_types),'field_type', $this->field_type,VmHTML::validate('R')) ;
		$html .= '</table>';
		$html .= VmHTML::inputHidden($this->_hidden);

		return $html;
	}

	/**
	 * child classes can add their own options and you can get them with this function
	 *
	 * @param array $optionsarray
	 */
	private function getOptions($field_types){
		$options=array();
		foreach($field_types as $optionName=>$langkey){
			$options[] = JHTML::_('select.option',  $optionName, JText::_( $langkey ) );
		}
		return $options;
	}

	/**
	 * Just for creating simpel rows
	 *
	 * @author Max Milbers
	 * @param string $descr
	 * @param string $name
	 */
	private function displayRow($descr, $name,$readonly=''){
		$html = '<tr>
		<td class="labelcell">'.JText::_($descr).'</td>
		<td> <input type="text" '.$readonly.'class="inputbox" name="'.$name.'" size="70" value="'.$this->$name.'" /></td>
	</tr>';
		return $html;
	}



}