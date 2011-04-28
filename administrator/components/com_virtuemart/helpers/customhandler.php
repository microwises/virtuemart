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

		$this->custom_id = $id;

		$this->theme_url = VmConfig::get('vm_themeurl',0);
		if(empty($this->theme_url)){
			$this->theme_url = JURI::root().'components/com_virtuemart/';
		}
	}

	public function getCustomParentTitle($custom_parent_id) {

    	$q='SELECT custom_title FROM `#__vm_custom` WHERE custom_id ='.$custom_parent_id;
		if(empty($this->_db)) $this->_db = JFactory::getDBO();

		$this->_db->setQuery($q);
		return $this->_db->loadResult();
	}
		/** @var _field_types autorized Types of data */
    function getField_types(){

		return array( 'S' =>'COM_VIRTUEMART_CUSTOM_STRING',
			'I'=>'COM_VIRTUEMART_CUSTOM_INT',
			'P'=>'COM_VIRTUEMART_CUSTOM_PARENT',
			'B'=>'COM_VIRTUEMART_CUSTOM_BOOL',
			'D'=>'COM_VIRTUEMART_CUSTOM_DATE',
			'T'=>'COM_VIRTUEMART_CUSTOM_TIME',
			'C'=>'COM_VIRTUEMART_CUSTOM_PRODUCT_CHILD',
			'i'=>'COM_VIRTUEMART_CUSTOM_IMAGE'
			);
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
	 */
	public function prepareStoreCustom($data,$table){

		$custom = VmCustomHandler::createCustom($data);

		/*$data = $custom->processAction($data);
		$data = $custom->processAttributes($data);

		$attribsImage = get_object_vars($custom);
		foreach($attribsImage as $k=>$v){
			$data[$k] = $v;
		}*/

		return $custom;//$data;
	}
    /**
     * Sets the file information and paths/urls and so on.
     *
     * @author Max Milbers
     * @param unknown_type $filename
     * @param unknown_type $url
     * @param unknown_type $path
     */
    function setCustomInfo($type=0){

    	if(empty($this->file_url)){
    		$this->file_url = $this->getCustomUrlByView($type);
     		$this->file_url_folder = $this->file_url;
    		$this->file_path_folder = str_replace('/',DS,$this->file_url_folder);
    		$this->file_url_folder_thumb = $this->file_url_folder.'resized/';
    		$this->file_name = '';
    		$this->file_extension = '';
    	} else {
	     	if(!class_exists('JFile')) require(JPATH_LIBRARIES.DS.'joomla'.DS.'filesystem'.DS.'file.php');

	    	$lastIndexOfSlash= strrpos($this->file_url,'/');

	    	$name = substr($this->file_url,$lastIndexOfSlash+1);

	    	$this->file_name = JFile::stripExt($name);
	    	$this->file_url_folder = substr($this->file_url,0,$lastIndexOfSlash+1);
	    	$this->file_path_folder = str_replace('/',DS,$this->file_url_folder);
	    	$this->file_extension = strtolower(JFile::getExt($name));

			$this->file_url_folder_thumb = $this->file_url_folder.'resized/';

    	}

    	if($this->custom_boolean) $this->custom_attributes = 'custom_boolean';
	    if($this->custom_string) $this->custom_attributes = 'custom_string';
	    if($this->custom_boolean) $this->custom_attributes = 'custom_int';

    	self::determineFoldersToTest();

    }



	/**
	 * This function should return later also an icon, if there isnt any automatic thumbnail creation possible
	 * like pdf, zip, ...
	 *
	 * @author Max Milbers
	 * @param string $imageArgs
	 * @param boolean $lightbox
	 */
	function getIcon($imageArgs,$lightbox){
		//we can later add here icons for different types
		$file_url = $this->theme_url.'assets/images/vmgeneral/'.VmConfig::get('no_image_found');
		$file_alt = JText::_('COM_VIRTUEMART_NO_IMAGE_FOUND').' '.$this->file_description;
		return self::displayIt($file_url, $file_alt, $imageArgs,$lightbox);
	}

	/**
	 * This function is just for options how to display an image...
	 * we may add here plugins for displaying images
	 *
	 * @author Max Milbers
	 * @param string $file_url relative Url
	 * @param string $file_alt custom description
	 * @param string $imageArgs attributes for displaying the images
	 * @param boolean $lightbox use lightbox
	 */
	function displayIt($file_url, $file_alt, $imageArgs,$lightbox, $effect ="class='modal'"){

		if($lightbox){
			$image = JHTML::image($file_url, $file_alt, $imageArgs);
			if ($file_alt ) $file_alt = 'title="'.$file_alt.'"';
			if ($this->file_url) $href = JURI::root() .$this->file_url ;
			else $href = $image ;
			$lightboxImage = '<a '.$file_alt.' '.$effect.' href="'.$href.'">'.$image.'</a>';
			return $lightboxImage;
		} else {
			return JHTML::image($file_url, $file_alt, $imageArgs);
		}
	}


	/**
	 * Processes the choosed Action while storing the data, gets extend by the used child, use for the action clear commands.
	 * Useable commands in all customs upload, upload_delete, delete, and all of them with _thumb on it also.
	 *
	 * @author Max Milbers
	 * @param arraybyform $data
	 */
	function processAction($data){

		if( $data['custom_action'] == 'upload' ){
			$custom_name = self::uploadcustom($this->custom_url_folder);
			$this->custom_url = $this->custom_url_folder.$custom_name;
		}
		else if( $data['custom_action'] == 'upload_delete' ){
			$oldcustomUrl = $data['custom_url'];
			$custom_name = self::uploadcustom($this->custom_url_folder);
			if($this->custom_url!=$oldcustomUrl && !empty($this->custom_name)){
				self::deletecustom($oldcustomUrl);
			}
			$this->custom_url = $this->custom_url_folder.$custom_name;
			$this->custom_name = $custom_name;

		}
		else if( $data['custom_action'] == 'delete' ){
			self::deletecustom($this->custom_url);
			unset($data['custom_id']);
		}
		else if( $data['custom_action'] == 'upload_thumb' ){
			$custom_name = self::uploadcustom($this->custom_url_folder_thumb);
			$this->custom_url_thumb = $this->custom_url_folder_thumb.$custom_name;
		}
		else if( $data['custom_action'] == 'upload_delete_thumb' ){
			$oldcustomUrl = $data['custom_url_thumb'];
			$custom_name = self::uploadcustom($this->custom_url_folder_thumb);
			if($this->custom_url_thumb!=$oldcustomUrl){
				self::deletecustom($oldcustomUrl);
			}
			$this->custom_url_thumb = $this->custom_url_folder_thumb.$custom_name;
		}
		else if( $data['custom_action'] == 'delete_thumb' ){
			self::deletecustom($this->custom_url_thumb);
		}
		else{

		}

		if(empty($this->custom_title) && !empty($custom_name)) $this->custom_title = $custom_name;
		if(empty($this->custom_title) && !empty($custom_name)) $data['custom_title'] = $custom_name;

		return $data;
	}


	/**
	 * For processing the Attributes of the custom while the storing process
	 *
	 * @author Max Milbers
	 * @param unknown_type $data
	 */
	function processAttributes($data){

		if($data['custom_attributes'] == 'custom_is_product_image'){
			$this->custom_is_product_image = 1;
			$this->custom_is_downloadable = 0;
			$this->custom_is_forSale = 0;
		}
		else if($data['custom_attributes'] == 'custom_is_downloadable'){
			$this->custom_is_downloadable = 1;
			$this->custom_is_forSale = 0;
		}
		else if($data['custom_attributes'] == 'custom_is_forSale'){
			$this->custom_is_product_image = 0;
			$this->custom_is_downloadable = 0;
			$this->custom_is_forSale = 1;
		}
		return $data;
	}

	private $_actions = array();
	/**
	 * This method can be used to add extra actions to the custom
	 *
	 * @author Max Milbers
	 * @param string $optionName this is the value in the form
	 * @param string $langkey the langkey used
	 */
	function addCustomAction($optionName,$langkey){
		$this->_actions[$optionName] = $langkey ;
	}

	/**
	 * Adds the custom action which are needed in the form for all custom,
	 * you can use this function in your child calling parent. Look in VmImage for an exampel
	 * @author Max Milbers
	 */
	function addCustomActionByType(){

		self::addCustomAction(0,'COM_VIRTUEMART_NONE');

		if(empty($this->custom_url)){
			self::addCustomAction('upload','COM_VIRTUEMART_FORM_CUSTOM_FIELD_UPLOAD');
		} else {
			self::addCustomAction('upload_delete','COM_VIRTUEMART_FORM_CUSTOM_FIELD_UPLOAD_DELETE');
			self::addCustomAction('delete','COM_VIRTUEMART_FORM_CUSTOM_FIELD_DELETE');
		}

		if(empty($this->custom_url_thumb)){
			self::addCustomAction('upload_thumb','COM_VIRTUEMART_FORM_CUSTOM_FIELD_UPLOAD_THUMB');
		} else {
			self::addCustomAction('upload_delete_thumb','COM_VIRTUEMART_FORM_CUSTOM_FIELD_UPLOAD_DELETE_THUMB');
			self::addCustomAction('delete_thumb','COM_VIRTUEMART_FORM_CUSTOM_FIELD_DELETE_THUMB');
		}

	}


	private $_attributes = array();


	/**
	 * This method can be used to add extra attributes to the custom
	 *
	 * @author Max Milbers
	 * @param string $optionName this is the value in the form
	 * @param string $langkey the langkey used
	 */
	public function addCustomAttributes($optionName,$langkey=''){
		$this->_attributes[$optionName] = $langkey ;
	}

	/**
	 * Adds the attributes which are needed in the form for all custom,
	 * you can use this function in your child calling parent. Look in VmImage for an exampel
	 * @author Max Milbers
	 */
	public function addCustomAttributesByType(){
		self::addCustomAttributes('S','COM_VIRTUEMART_CUSTOM_STRING');
		self::addCustomAttributes('I','COM_VIRTUEMART_CUSTOM_INT');
		self::addCustomAttributes('P','COM_VIRTUEMART_CUSTOM_PARENT');
		self::addCustomAttributes('B','COM_VIRTUEMART_CUSTOM_BOOL');
		self::addCustomAttributes('D','COM_VIRTUEMART_CUSTOM_DATE');
		self::addCustomAttributes('T','COM_VIRTUEMART_CUSTOM_TIME');
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
	 */
	private function addHiddenByType(){

		self::addHidden('custom_id',$this->custom_id);
		self::addHidden('option','com_virtuemart');

	}

	/**
	 * Displays custom handler and file selector
	 *
	 * @author Max Milbers
	 * @param array $fileIds
	 */
	public function displayCustom($field_types){

		$html = self::displayCustomSelection();
		$html .= self::displayCustomFields('id="vm_display_image"',$field_types);
		return $html;
	}

	/**
	 * Displays a possibility to select already uploaded custom
	 * the getImagesList must be adjusted to have more search functions
	 * @author Max Milbers
	 * @param array $fileIds
	 */
	public function displayCustomSelection(){
		
		$customslist = self::getCustomsList();
		if (isset($this->custom_id)) $value = $this->custom_id ;
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
    	$q='SELECT custom_id as value ,custom_title as text FROM `#__vm_custom` where custom_parent_id=0';
		if ($publishedOnly) $q=' WHERE `published`=1 ';
		//if (isset($this->custom_id)) $q.=' and custom_id !='.$this->custom_id;
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
	public function booleanRow( $text , $value){
	$html = '<tr>
	<td class="labelcell">
		<label for="'.$value.'">'. JText::_($text) .'</label>
	</td>
	<td><fieldset class="radio">
				'.JHTML::_( 'select.booleanlist',  $value , 'class="inputbox"', $this->$value).'
		</fieldset>
	</td>
</tr>';
	return $html ;
	}
	/**
	 * This displays a custom handler.
	 *
	 * @param string $html atttributes, Just for displaying the fullsized image
	 */
	public function displayCustomFields($imageArgs='',$field_types){

		$identify = ''; // ':'.$this->custom_id;
		if (!class_exists('VmHTML')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'html.php');
		if ($this->field_type) self::addHidden('field_type',$this->field_type);
		self::addHiddenByType();

		$html = '<div id="custom_title">'.$this->custom_title.'</div>';
		//$html .= $this->displayCustomFull($imageArgs,false);

		//This makes problems, when there is already a form, and there would be form in a form. breaks js in some browsers
//		$html .= '<form name="adminForm" id="adminForm" method="post" enctype="multipart/form-data">';

		$html .= ' <table class="adminform"> ';

		if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');
		if(!Permissions::getInstance()->check('admin') ) $readonly='readonly'; else $readonly ='';
		$html .= VmHTML::inputRow('COM_VIRTUEMART_CUSTOM_TITLE','custom_title',$this->custom_title);
		$html .= VmHTML::inputRow('COM_VIRTUEMART_DESCRIPTION','custom_field_desc',$this->custom_field_desc);
		// change input by type
		$html .= VmHTML::inputRow('COM_VIRTUEMART_CUSTOM_DEFAULT','custom_value',$this->custom_value);
		$html .= VmHTML::inputRow('COM_VIRTUEMART_CUSTOM_TIP','custom_tip',$this->custom_tip);
		$html .= VmHTML::selectRow('COM_VIRTUEMART_CUSTOM_PARENT',self::getCustomsList(), 'custom_parent_id', $this->custom_id,'');
		$html .= VmHTML::booleanRow('COM_VIRTUEMART_CUSTOM_FORM_FIELD_PUBLISHED','published',$this->published);
		$html .= VmHTML::booleanRow('COM_VIRTUEMART_CUSTOM_ADMIN_ONLY','admin_only',$this->admin_only);
		$html .= VmHTML::booleanRow('COM_VIRTUEMART_CUSTOM_IS_LIST','is_list',$this->is_list);
		$html .= VmHTML::booleanRow('COM_VIRTUEMART_CUSTOM_IS_HIDDEN','is_hidden',$this->is_hidden);
		self::addCustomAttributesByType();
		// only input when not set else display
		if ($this->field_type) $html .= VmHTML::Row('COM_VIRTUEMART_CUSTOM_FIELD_TYPE', $field_types[$this->field_type] ) ; 
		else $html .= VmHTML::radioRow('COM_VIRTUEMART_CUSTOM_FIELD_TYPE',self::getOptions($field_types),'field_type', $this->field_type) ; 
		$html .= '</table>';
		$html .= VmHTML::inputHidden($this->_hidden);
//		$html .= '</form>';

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