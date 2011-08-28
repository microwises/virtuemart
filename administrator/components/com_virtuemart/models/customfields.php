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
class VirtueMartModelCustomfields extends VmModel {

	/**
	 * constructs a VmModel
	 * setMainTable defines the maintable of the model
	 * @author Max Milbers
	 */
	function __construct($modelName ='product') {
		parent::__construct('virtuemart_customfield_id');
		$this->setMainTable('product_customfields');
	}

    /**
     * Gets a single custom by virtuemart_customfield_id
     * @param string $type
     * @param string $mime mime type of custom, use for exampel image
     * @return customobject
     */
    function getCustomfields(){

   		$this->data = $this->getTable('product_customfields');
   		$this->data->load($this->_id);

  		return $this;

    }
    /**
     * Gets a single custom by virtuemart_customfield_id
     * @param string $type
     * @param string $mime mime type of custom, use for exampel image
     * @return customobject
     */
    function getCustomfield(){

   		$this->data = $this->getTable('product_customfields');
   		$this->data->load($this->_id);

  		return $this;

    }
	// **************************************************
	// Custom FIELDS
	//

     function getProductCustomsChilds($childs){

		$data = array();
	     	foreach ($childs as &$child) {
	     		$query='SELECT C.* , field.*
					FROM `#__virtuemart_product_customfields` AS field
					LEFT JOIN `#__virtuemart_customs` AS C ON C.`virtuemart_custom_id` = field.`virtuemart_custom_id`
					WHERE `virtuemart_product_id` ='.(int)$child->virtuemart_product_id;
				$query .=' and C.field_type = "C" ';

				$this->_db->setQuery($query);
				$child->field = $this->_db->loadObject();
	     		$child->display = $this->displayType($child->virtuemart_product_id,'C');
	     		if ($child->field) $data[] = $child ;
	     	}
			return $data ;

     }
	public function getCustomParentTitle($custom_parent_id) {

    	$q='SELECT custom_title FROM `#__virtuemart_customs` WHERE virtuemart_custom_id ='.(int)$custom_parent_id;
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
	private function addHiddenByType($datas){

		$this->addHidden('virtuemart_custom_id',$datas->virtuemart_custom_id);
		$this->addHidden('option','com_virtuemart');

	}

	/**
	 * Displays custom handler and file selector
	 *
	 * @author Max Milbers
	 * @param array $fileIds
	 */
	public function displayCustom($field_types){

		$html = $this->displayCustomSelection();
		$html .= $this->displayCustomFields('id="vm_display_image"',$field_types);
		return $html;
	}

	/**
	 * Displays a possibility to select created custom
	 * @author Max Milbers
	 * @author Patrick Kohl
	 */
	public function displayCustomSelection(){

		$customslist = $this->getCustomsList();
		if (isset($this->virtuemart_custom_id)) $value = $this->virtuemart_custom_id ;
		else $value = JRequest::getInt( 'custom_parent_id',0);
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
		if ($ID = JRequest::getInt( 'virtuemart_custom_id',0)) $q .=' and `virtuemart_custom_id`!='.(int)$ID;
		//if (isset($this->virtuemart_custom_id)) $q.=' and virtuemart_custom_id !='.$this->virtuemart_custom_id;
		$this->_db->setQuery($q);
//		$result = $this->_db->loadAssocList();
		$result = $this->_db->loadObjectList();

    	$errMsg = $this->_db->getErrorMsg();
		$errs = $this->_db->getErrors();

		if(!empty($errMsg)){
			$app = JFactory::getApplication();
			$errNum = $this->_db->getErrorNum();
			$app->enqueueMessage('SQL-Error: '.$errNum.' '.$errMsg);
		}

		if($errs){
			$app = JFactory::getApplication();
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
	public function displayCustomFields($imageArgs='',$datas){

		$identify = ''; // ':'.$this->virtuemart_custom_id;
		if (!class_exists('VmHTML')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'html.php');
		if ($datas->field_type) $this->addHidden('field_type',$datas->field_type);
		$this->addHiddenByType($datas);

		$html = '<div id="custom_title">'.$datas->custom_title.'</div>';
		$html .= ' <table class="adminform"> ';

		if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');
		if(!Permissions::getInstance()->check('admin') ) $readonly='readonly'; else $readonly ='';
		$html .= VmHTML::inputRow('COM_VIRTUEMART_TITLE','custom_title',$datas->custom_title,VmHTML::validate('S'));
		$html .= VmHTML::inputRow('COM_VIRTUEMART_DESCRIPTION','custom_field_desc',$datas->custom_field_desc);
		// change input by type
		$html .= VmHTML::inputRow('COM_VIRTUEMART_DEFAULT','custom_value',$datas->custom_value);
		$html .= VmHTML::inputRow('COM_VIRTUEMART_CUSTOM_TIP','custom_tip',$datas->custom_tip);
		$html .= VmHTML::selectRow('COM_VIRTUEMART_CUSTOM_PARENT',$this->getCustomsList(), 'custom_parent_id', $datas->custom_parent_id,'');
		$html .= VmHTML::booleanRow('COM_VIRTUEMART_PUBLISHED','published',$datas->published);
		$html .= VmHTML::booleanRow('COM_VIRTUEMART_CUSTOM_ADMIN_ONLY','admin_only',$datas->admin_only);
		$html .= VmHTML::booleanRow('COM_VIRTUEMART_CUSTOM_IS_LIST','is_list',$datas->is_list);
		$html .= VmHTML::booleanRow('COM_VIRTUEMART_CUSTOM_IS_HIDDEN','is_hidden',$datas->is_hidden);
		$html .= VmHTML::booleanRow('COM_VIRTUEMART_CUSTOM_IS_CART_ATTRIBUTE','is_cart_attribute',$datas->is_cart_attribute);
		// only input when not set else display
		if ($datas->field_type) $html .= VmHTML::Row('COM_VIRTUEMART_CUSTOM_FIELD_TYPE', $datas->field_types[$datas->field_type] ) ;
		else $html .= VmHTML::selectRow('COM_VIRTUEMART_CUSTOM_FIELD_TYPE',$this->getOptions($datas->field_types),'field_type', $datas->field_type,VmHTML::validate('R')) ;
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

	function getProductChildCustomRelation() {

		$this->_db->setQuery(' SELECT virtuemart_custom_id as value,custom_title as text FROM `#__virtuemart_customs` WHERE `field_type` ="C"' );
		if ($results =$this->_db->loadObjectList()) return $results ;
		else return array();
	}
	function getProductChildCustom($product_id ) {
		$this->_db->setQuery(' SELECT `virtuemart_custom_id`,`custom_value` FROM `#__virtuemart_product_customfields` WHERE  `virtuemart_product_id` ='.(int)$product_id);
		if ($childcustom = $this->_db->loadObject()) return $childcustom;
		else {
			$childcustom->virtuemart_custom_id = 0;
			$childcustom->custom_value = '';
			return $childcustom;
		}
	}
	function getProductParentRelation($product_id ) {
		$this->_db->setQuery(' SELECT `custom_value` FROM `#__virtuemart_product_customfields` WHERE  `virtuemart_product_id` ='.(int)$product_id);
		if ($childcustom = $this->_db->loadResult()) return '('.$childcustom.')';
		else return JText::_('COM_VIRTUEMART_CUSTOM_NO_PARENT_RELATION');
	}
	/**
     * AUthor Kohl Patrick
     * Load all custom fields for a Single product
     * return custom fields value and definition
     */
     public function getproductCustomslist($virtuemart_product_id) {

		$query='SELECT C.`virtuemart_custom_id` , `custom_parent_id` , `admin_only` , `custom_title` , `custom_tip` , C.`custom_value` AS value, `custom_field_desc` , `field_type` , `is_list` , `is_cart_attribute` , `is_hidden` , C.`published` , field.`virtuemart_customfield_id` , field.`custom_value`,field.`custom_price`
			FROM `#__virtuemart_customs` AS C
			LEFT JOIN `#__virtuemart_product_customfields` AS field ON C.`virtuemart_custom_id` = field.`virtuemart_custom_id`
			Where `field_type`!="C" AND `virtuemart_product_id` ='.$virtuemart_product_id;
		$this->_db->setQuery($query);
		$productCustoms = $this->_db->loadObjectList();
		$row= 0 ;
		foreach ($productCustoms as $field ) {
			$field->display = $this->inputType($field->custom_value,$field->field_type,$field->is_list,$field->custom_price,$row,$field->is_cart_attribute);
			$row++ ;
		}
		return $productCustoms;
     }

  	/**
     * AUthor Kohl Patrick
     * Load the t the custom fields for a product
     * return Object product type , parameters & value
     */
     public function getproductCustoms() {
		static $productcustomFields ;
		if ($productcustomFields) return $productcustomFields;
		$virtuemart_product_id = JRequest::getInt('virtuemart_product_id', false);
		if (empty ($productcustomFields)) {
			 if ($this->hasproductCustoms($virtuemart_product_id )) {
				if(!class_exists(' VirtueMartModelCustom')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'custom.php');
				$productcustomFields = VirtueMartModelCustom::getProductCustoms($virtuemart_product_id);
			return $productcustomFields++ ;
			}
		} else return $productcustomFields;
     }

/**
 * Formating admin display by roles
 * input Types for product only !
 * $pricable if can have a price
 */

	public function inputType($value,$type,$is_list=0,$price,$row,$pricable=0){
		if ($is_list>0) {
			$options = array();
			$values = explode(';',$value);
			foreach ($values as $key => $val)
				$options[] = array( 'value' => $val ,'text' =>$val);
			return JHTML::_('select.genericlist', $options,'field['.$row.'][custom_value]');
		} else {
			if ($pricable)  $priceInput = JText::_('COM_VIRTUEMART_CART_PRICE').'<input type="text" value="'.$price.'" name="field['.$row.'][custom_price]" />';
			else $priceInput = '';
			switch ($type) {
				/* variants*/
				case 'V':
				return '<input type="text" value="'.$value.'" name="field['.$row.'][custom_value]" />'.$priceInput;
				break;
				/*userfield variants*/
				case 'U':
				return '<input type="text" value="'.$value.'" name="field['.$row.'][custom_value]" />'.$priceInput;
				break;
				/* string or integer */
				case 'S':
				case 'I':
					return '<input type="text" value="'.$value.'" name="field['.$row.'][custom_value]" />'.$priceInput;
				break;
				/* bool */
				case 'B':
					return JHTML::_( 'select.booleanlist', 'field['.$row.'][custom_value]' , 'class="inputbox"', $value).$priceInput;
				break;
				/* parent */
				case 'P':
					return $value.'<input type="hidden" value="'.$value.'" name="field['.$row.'][custom_value]" />'.$priceInput;
				break;
				/* related category*/
				case 'Z':
					$q='SELECT * FROM `#__virtuemart_categories` WHERE `published`=1 AND `virtuemart_category_id`= "'.(int)$value.'" ';
					$this->_db->setQuery($q);
					//echo $this->_db->_sql;
					if ($category = $this->_db->loadObject() ) {
						$q='SELECT `virtuemart_media_id` FROM `#__virtuemart_category_medias`WHERE `virtuemart_category_id`= "'.$category->virtuemart_category_id.'" ';
						$this->_db->setQuery($q);
						$thumb ='';
						if ($media_id = $this->_db->loadResult()) {
							$thumb = $this->displayCustomMedia($media_id);
						}
						$display = '<input type="hidden" value="'.$value.'" name="field['.$row.'][custom_value]" />';
						return  JHTML::link ( JRoute::_ ( 'index.php?option=com_virtuemart&view=category&task=edit&virtuemart_category_id=' . $category->virtuemart_category_id ), $thumb.' '.$category->category_name, array ('title' => $category->category_name ) ).$display;
					}
					else return 'no result';
				/* related product*/
				case 'R':
					if (!$value) return '';
					$q='SELECT `product_name`,`product_sku` FROM `#__virtuemart_products` WHERE `virtuemart_product_id`='.(int)$value ;
					$this->_db->setQuery($q);
					$related = $this->_db->loadObject();
					$display = $related->product_name.'('.$related->product_sku.')';
					$display .= '<input type="hidden" value="'.$value.'" name="field['.$row.'][custom_value]" />';

					$q='SELECT `virtuemart_media_id` FROM `#__virtuemart_product_medias`WHERE `virtuemart_product_id`= "'.(int)$value.'" ';
					$this->_db->setQuery($q);
					$thumb ='';
					if ($media_id = $this->_db->loadResult()) {
						$thumb = $this->displayCustomMedia($media_id);
					}
					return JHTML::link ( JRoute::_ ( 'index.php?option=com_virtuemart&view=product&task=edit&virtuemart_product_id='.$value ), $thumb.' '.$related->product_name, array ('title' => $related->product_name ) ).$display;
				break;
				/* image */
				case 'M':
					if (empty($product)){
						$vendorId=1;
					} else {
						$vendorId = $product->virtuemart_vendor_id;
					}
					$q='SELECT `virtuemart_media_id` as value,`file_title` as text FROM `#__virtuemart_medias` WHERE `published`=1
					AND (`virtuemart_vendor_id`= "'.$vendorId.'" OR `shared` = "1")';
					$this->_db->setQuery($q);
					$options = $this->_db->loadObjectList();
					return JHTML::_('select.genericlist', $options,'field['.$row.'][custom_value]','','value' ,'text',$value).$priceInput;
				break;
				/* Child product */
				case 'C':
					if (empty($product)){
						$virtuemart_product_id = JRequest::getInt('virtuemart_product_id', 0);
					} else {
						$virtuemart_product_id = $product->virtuemart_product_id;
					}
					$q='SELECT concat(`product_sku`,":",`product_name`) as text FROM `#__virtuemart_products` WHERE `published`=1
					AND `virtuemart_product_id`= "'.$virtuemart_product_id.'"';
					$this->_db->setQuery($q);
					if ($productParent = $this->_db->loadResult())
//					return '<input type="text" value="'.$value.'" name="field['.$row.'][custom_value]" />';
					return $productParent.' ('.$value.') ';
					else return JText::_('COM_VIRTUEMART_CUSTOM_NO_CHILD_PRODUCT');
				break;
			}

		}
	}
     public function getProductCustomsField($product) {

		$query='SELECT C.`virtuemart_custom_id` , `custom_parent_id` , `admin_only` , `custom_title` , `custom_tip` , C.`custom_value` AS value, `custom_field_desc` , `field_type` , `is_list` , `is_hidden` , C.`published` , field.`virtuemart_customfield_id` , field.`custom_value`, field.`custom_price`
			FROM `#__virtuemart_customs` AS C
			LEFT JOIN `#__virtuemart_product_customfields` AS field ON C.`virtuemart_custom_id` = field.`virtuemart_custom_id`
			Where `virtuemart_product_id` ='.(int)$product->virtuemart_product_id;
		$query .=' and is_cart_attribute = 0 order by virtuemart_custom_id' ;
		$this->_db->setQuery($query);
		$productCustoms = $this->_db->loadObjectList();
		$row= 0 ;

		//if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');
		//$calculator = calculationHelper::getInstance();
		foreach ($productCustoms as & $field ) {
			//$custom_price = $calculator->calculateCustomPriceWithTax($field->custom_price);
			$field->display = $this->displayType($field->custom_value,$field->field_type,$field->is_list,$field->custom_price,$row);
			$row++ ;
		}
		return $productCustoms;
     }

	 // temp function TODO better one
     public function getProductCustomsFieldCart($product) {

			// group by virtuemart_custom_id
			$query='SELECT C.`virtuemart_custom_id`, `custom_title`, C.`custom_value`,`custom_field_desc` ,`custom_tip`,`field_type`,field.`virtuemart_customfield_id`,`is_hidden`
				FROM `#__virtuemart_customs` AS C
				LEFT JOIN `#__virtuemart_product_customfields` AS field ON C.`virtuemart_custom_id` = field.`virtuemart_custom_id`
				Where `virtuemart_product_id` ='.(int)$product->virtuemart_product_id;
			$query .=' and is_cart_attribute = 1 group by virtuemart_custom_id' ;

			$this->_db->setQuery($query);
			$groups = $this->_db->loadObjectList();

			if (!class_exists('VmHTML')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'html.php');
			$row= 0 ;
			if(!class_exists('CurrencyDisplay')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'currencydisplay.php');
			$currency = CurrencyDisplay::getInstance();

			if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');
			$calculator = calculationHelper::getInstance();

			// render select list
			foreach ($groups as $group) {

//				$query='SELECT  field.`virtuemart_customfield_id` as value ,concat(field.`custom_value`," :bu ", field.`custom_price`) AS text
				$query='SELECT  field.`virtuemart_customfield_id` as value ,field.`custom_value`, field.`custom_price`
					FROM `#__virtuemart_customs` AS C
					LEFT JOIN `#__virtuemart_product_customfields` AS field ON C.`virtuemart_custom_id` = field.`virtuemart_custom_id`
					Where `virtuemart_product_id` ='.(int)$product->virtuemart_product_id;
				$query .=' and is_cart_attribute = 1 and C.`virtuemart_custom_id`='.(int)$group->virtuemart_custom_id ;
				$this->_db->setQuery($query);
				$options = $this->_db->loadObjectList();
				$group->options = array();
				foreach ( $options as $option){
					$group->options[$option->value] = $option;
				}

				if ($group->field_type == 'V'){
					$default = current($group->options);
					foreach ($group->options as $productCustom) {
						$productCustom->text =  $productCustom->custom_value.' : '.$currency->priceDisplay($calculator->calculateCustomPriceWithTax($productCustom->custom_price));
					}
					$group->display = VmHTML::select($group->options,'customPrice['.$row.']['.$group->virtuemart_custom_id.']',$group->custom_value,$default->custom_value,'value','text',false);
				} else if ($group->field_type == 'U'){
					foreach ($group->options as $productCustom) {
						$productCustom->text =  $productCustom->custom_value.' : '.$currency->priceDisplay($calculator->calculateCustomPriceWithTax($productCustom->custom_price));
					}
					$group->display .= '<label for="'.$productCustom->value.'">'.$this->displayType($productCustom->custom_value,$group->field_type,0,'',$row).': '.$currency->priceDisplay($calculator->calculateCustomPriceWithTax($productCustom->custom_price)).'</label>' ;
				} else {
					$group->display ='';
					$checked = 'checked="checked"';
					foreach ($group->options as $productCustom) {
						$group->display .= '<input id="'.$productCustom->value.'" '.$checked.' type="radio" value="'.$productCustom->value.'" name="customPrice['.$row.']['.$group->virtuemart_custom_id.']" /><label for="'.$productCustom->value.'">'.$this->displayType($productCustom->custom_value,$group->field_type,0,'',$row).': '.$currency->priceDisplay($calculator->calculateCustomPriceWithTax($productCustom->custom_price)).'</label>' ;
						$checked ='';
					}
				}
				$row++ ;
			}

			return $groups;

     }
  /**
  * Formating front display by roles
  *  for product only !
  */
	public function displayType($value,$type,$is_list=0,$price = 0,$row=''){
		if ($is_list>0) {
			$options = array();
			$values = explode(';',$value);

			foreach ($values as $key => $val){
				$options[] = array( 'value' => $val ,'text' =>$val);
			}

			return JHTML::_('select.genericlist', $options,'field['.$row.'][custom_value]',null,'value','text',false,true);
		} else {
			if ($price > 0){
				$price = $currency->priceDisplay((float)$price);
			}
			switch ($type) {

				/* variants*/
				case 'V':
				if ($price == 0 ) $price = JText::_('COM_VIRTUEMART_CART_PRICE_FREE') ;
				/* Loads the product price details */
				return '<input type="text" value="'.JText::_($value).'" name="field['.$row.'][custom_value]" /> '.JText::_('COM_VIRTUEMART_CART_PRICE').' : '.$price .' ';
				break;
				/*userfield variants*/
				case 'U':
				return '<input type="text" value="'.JText::_($value).'" name="field['.$row.'][custom_value]" /> '.JText::_('COM_VIRTUEMART_CART_PRICE').' : '.$price .' ';
				break;
				/* string or integer */
				case 'S':
				case 'I':
					return JText::_($value);
				break;
				/* bool */
				case 'B':
					if ($value == 0) return JText::_('COM_VIRTUEMART_NO') ;
					return JText::_('COM_VIRTUEMART_YES') ;
				break;
				/* parent */
				case 'P':
					return '<span class="product_custom_parent">'.JText::_($value).'<span/>';
				break;
				/* related */
				case 'R':
					$q='SELECT p.`product_name`, p.`product_parent_id` , p.`product_name`, x.`virtuemart_category_id` FROM `#__virtuemart_products` as p
					 LEFT JOIN `#__virtuemart_product_categories` as x on x.`virtuemart_product_id` = p.`virtuemart_product_id`
					 WHERE p.`published`=1 AND  p.`virtuemart_product_id`= "'.(int)$value.'" ';
					$this->_db->setQuery($q);
					$related = $this->_db->loadObject();
					if(empty ($related)) return '';
					$thumb = '';
					$q='SELECT `virtuemart_media_id` FROM `#__virtuemart_product_medias`WHERE `virtuemart_product_id`= "'.(int)$value.'" ';
					$this->_db->setQuery($q);
					if ($media_id = $this->_db->loadResult()) {
						$thumb = $this->displayCustomMedia($media_id);
						return JHTML::link ( JRoute::_ ( 'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $value . '&virtuemart_category_id=' . $related->virtuemart_category_id ), $thumb.' '.$related->product_name, array ('title' => $related->product_name ) );
					}
				break;
				/* image */
				case 'M':
					return $this->displayCustomMedia($value);
				break;
				/* categorie */
				case 'Z':
					$q='SELECT * FROM `#__virtuemart_categories` WHERE `published`=1 AND `virtuemart_category_id`= "'.(int)$value.'" ';
					$this->_db->setQuery($q);
					if ($category = $this->_db->loadObject() ) {
						$q='SELECT `virtuemart_media_id` FROM `#__virtuemart_category_medias`WHERE `virtuemart_category_id`= "'.$category->virtuemart_category_id.'" ';
						$this->_db->setQuery($q);
						$thumb ='';
						if ($media_id = $this->_db->loadResult()) {
							$thumb = $this->displayCustomMedia($media_id);
						}
						return  JHTML::link ( JRoute::_ ( 'index.php?option=com_virtuemart&view=category&virtuemart_category_id=' . $category->virtuemart_category_id ), $thumb.' '.$category->category_name, array ('title' => $category->category_name ) );
					}
					else return '';
				/* related */
				case 'R':
				/* Child product */
				case 'C':
					$q='SELECT p.`virtuemart_product_id` ,p.`product_parent_id` , p.`product_name`, x.`virtuemart_category_id` FROM `#__virtuemart_products` as p
					LEFT JOIN `#__virtuemart_product_categories` as x on x.`virtuemart_product_id` = p.`virtuemart_product_id`
					WHERE `published`=1 AND p.`virtuemart_product_id`= "'.(int)$value.'" ';
					$this->_db->setQuery($q);
					//echo $this->_db->_sql;
					if ($child = $this->_db->loadObject() ) {
						$q='SELECT `virtuemart_media_id` FROM `#__virtuemart_product_medias` WHERE `virtuemart_product_id`= "'.$child->virtuemart_product_id.'" ';
						$this->_db->setQuery($q);
						$thumb ='';
						if ($media_id = $this->_db->loadResult()) {
							$thumb = $this->displayCustomMedia($media_id);
						} else {
							$q='SELECT `virtuemart_media_id` FROM `#__virtuemart_product_medias` WHERE `virtuemart_product_id`= "'.$child->product_parent_id.'" ';
							$this->_db->setQuery($q);
							if ($media_id = $this->_db->loadResult()) $thumb = $this->displayCustomMedia($media_id);
						}
						return  JHTML::link ( JRoute::_ ( 'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $child->virtuemart_product_id . '&virtuemart_category_id=' . $child->virtuemart_category_id ), $thumb.' '.$child->product_name, array ('title' => $child->product_name ) );
					}
					else return '';
				break;
			}
		}
	}
	/*
	 * generate custom fields list to display as search in FE
	 */
	public function getSearchCustom() {

		$this->_db->setQuery('SELECT `virtuemart_custom_id`, `custom_title` FROM `#__virtuemart_customs` WHERE `field_type` ="P"');
		$this->searchCustom->selectList = $this->_db->loadAssocList();
		if ($this->searchCustom->custom_parent_id = JRequest::getInt('custom_parent_id', 0)) {
			$this->_db->setQuery('SELECT `virtuemart_custom_id`, `custom_title` FROM `#__virtuemart_customs` WHERE custom_parent_id='.$this->searchCustom->custom_parent_id);
			$this->searchCustom->selected = $this->_db->loadObjectList();
			foreach ($this->searchCustom->selected as &$selected) {
				$this->_db->setQuery('SELECT `custom_value`,`custom_value` as title FROM `#__virtuemart_product_customfields` WHERE virtuemart_custom_id='.$selected->virtuemart_custom_id);
				$selected->fields[$selected->virtuemart_custom_id] = $this->_db->loadObjectList();
			}
		}
		return $this->searchCustom;
	}
	function displayCustomMedia($media_id,$table='product'){
		$data = $this->getTable('medias');
   		$data->load((int)$media_id);

  		if (!class_exists('VmMediaHandler')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'mediahandler.php');
  		$media = VmMediaHandler::createMedia($data,$table);

		return $media->displayMediaThumb('',false);

	}
}
// pure php no closing tag
