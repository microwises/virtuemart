<?php

/**
 * Abstract class for shipment plugins
 *
 * @package	VirtueMart
 * @subpackage Plugins
 * @author Oscar van Eijk
 * @author Valérie Isaksen
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2011 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: vmpsplugin.php 4007 2011-08-31 07:31:35Z alatak $
 */
// Load the helper functions that are needed by all plugins
if (!class_exists('VmHTML')) require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'html.php');
if (!class_exists('vmPlugin')) require(JPATH_VM_PLUGINS . DS . 'vmplugin.php');

// Get the plugin library
jimport('joomla.plugin.plugin');

if (!class_exists('vmPlugin'))
require(JPATH_VM_PLUGINS . DS . 'vmplugin.php');

/**
 * @package	VirtueMart
 * @subpackage Plugins
 * @author Oscar van Eijk
 * @author Patrick Kohl
 * @author Max Milbers
 */
abstract class vmCustomPlugin extends VmPlugin {


    /**
     * @var array List with all carriers the have been implemented with the plugin in the format
     * id => name
     */
    protected $customs;

	function __construct(& $subject, $config) {

		parent::__construct($subject, $config);

		$this->_tablepkey = 'virtuemart_product_id';
		$this->_tablename = '#__virtuemart_product_'.$this->_psType .'_plg_'. $this->_name;
		$this->_idName = 'virtuemart_custom_id';
		$this->_configTableFileName = $this->_psType.'s';
		$this->_configTableClassName = 'Table'.ucfirst($this->_psType).'s'; //TablePaymentmethods
		$this->_configTable = '#__virtuemart_customs';

	}

	function plgVmGetActiveCustomPlugin($virtuemart_custom_id){

		if($this->plugin = $this->selectedThisByMethodId($this->_psType,$virtuemart_custom_id)){

		if (empty($this->plugin)) {
			$this->plugin->custom_jplugin_id = null;
			return $this->plugin ;
		}

		//Must use here the table to get valid params
		$this->plugin = $this->getVmPluginMethod($this->plugin->virtuemart_custom_id);

  		if(empty($this->plugin->virtuemart_vendor_id)){
  		   	if(!class_exists('VirtueMartModelVendor')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'vendor.php');
   			$this->plugin->virtuemart_vendor_id = VirtueMartModelVendor::getLoggedVendor();
  		}

  		return $this->plugin;

		}
	}

	/**
	 * This is the actions which take place, when a product gets stored
	 *
	 * @param string $type atm valid 'product'
	 * @param array $data form data
	 * @param int $id virtuemart_product_id
	 */
    function plgVmOnStoreProduct($type,&$data,$id){

    }

	/**
	 * render the plugin with param  to display on product edit
	 * called by customfields inputTypePlugin
	 */
	abstract function onProductEdit($field, $product, $row);

	/**
	 * display the plugin on product FE
	 */
	abstract function onDisplayProductFE( &$field, $product, $idx);

	/**
	 * display the product plugin on cart module
	 */
	abstract function onViewCartModule( $product,$productCustom, $row);

	/**
	* display the product plugin on cart
	 */
	abstract function onViewCart($product, $productCustom, $row);

	/**
	 * display the plugin in order
	 * TODO One for customer and one for vendor
	 * Get the statut (Eg. payed. >> render only the link for downloadable )
	 */
	abstract function onViewOrderBE($product, $productCustom, $row);

	/**
	 * defaut price modifation if nothing is set in plugin
	 * you have to rewrite it in your plugin to do other calculations
	 */
	public function modifyPrice( $product, $field,$selected ) {
		if (!empty($field->custom_price)) {
			//TODO adding % and more We should use here $this->interpreteMathOp
			return $field->custom_price;
		}
	}

	 /**
	 * Calculate the variant price by The plugin
	 * override calculateModificators() in calculatorh.
	 * Eg. recalculate price by a quantity set in the plugin
	 * You must reimplement modifyPrice() in your plugin
	 * or price is returned defaut custom_price
	 */
// 	 public function plgVmCalculatePluginVariant( $product, $field,$selected,$row){
	 public function plgVmCalculatePluginVariant($product, &$productCustomsPrice,$selected,$row){

// 	 	echo '<pre>'.print_r($product,1).'</pre>';
// 	 	echo '<pre>'.print_r($productCustomsPrice,1).'</pre>';
// 	 	echo '<pre>'.print_r($selected,1).'</pre>';
	 	if(!empty($product->virtuemart_product_id)){
// 	 		VmTable::bindParameterable($field,$this->_xParams,$this->_varsToPushParam);

// 	 		$db = JFactory::getDBO();
// 	 		$query = 'SELECT  C.* , field.*
// 	 		 					FROM `#__virtuemart_customs` AS C
// 	 		 					LEFT JOIN `#__virtuemart_product_customfields` AS field ON C.`virtuemart_custom_id` = field.`virtuemart_custom_id`
// 	 		 					WHERE `virtuemart_product_id` =' . $product->virtuemart_product_id;
// 	 		$query .=' and is_cart_attribute = 1 and field.`virtuemart_customfield_id`=' . $selected;
// 	 		$db->setQuery($query);
// 	 		$productCustomsPrice = $db->loadObject();
// 	 		echo '<pre>'.print_r($productCustomsPrice,1).'</pre>';
	 		if ($productCustomsPrice->field_type =='E') {

	 		}
// 	 		return $productCustomsPrice->custom_price;
	 	}

		return $this->modifyPrice( $product, $productCustomsPrice,$selected,$row);
	 }

	 /**
	 * convert param for render and
	 * display The plugin in cart
	 * @ $view is "Module" for see in module, "" for see in cart
	 */
	 public function plgVmDisplayInCartPlugin($product,$productCustom, $row ,$view=''){
		$plgName = $productCustom->value;
		$plgFunction = 'onViewCart'.$view ;

		return $this->$plgFunction( $product,$productCustom, $row);

	 }
	 /**
	 * display The plugin in order view FE/BE
	 * @ $view is "BE" for see in back-End, default is FE
	 */
	 public function displayInOrderPlugin($item,$productCustom, $row ,$view='FE'){
		$plgName = $productCustom->value;
		$plgPath = JPATH_SITE.DS.'plugins'.DS.'vmcustom'.DS.$plgName.'.php';
		// defaut render if the plugin is not found/installed
		if (!file_exists($plgPath)) {
			echo '<div style="color: #CC0000;">plugin <b>'.$plgName.'</b> not found.</div><br/>';
			if ($view =='FE') echo implode(',',(array)$param);
			else foreach ((array)$param as $key=>$text) echo '<span>parameter : '.$key.' : '.$text. '<span><br/>';
			return ;
		}
		if ($plgName) {
// 			$plg = self::setClass($plgName) ;
			$plgFunction = 'onViewOrder'.$view ;
			$html = $this->$plgFunction( $item,$param,$productCustom, $row);
		} else return '';

		return $html;
	 }
	/*
	 * Default return $item( Object: the product item in cart)
	 * The plugins can remove or change or adding more virtuemart_product_id eg. to do packs
	 * can return an array of item or a simple item
	 * Each item in aray must return $item->virtuemart_product_id
	 * or an array of Object with $item->virtuemart_product_id in it;
	 */
	 public function GetProductStockToUpdateByPlugin($item,$productCustom) {

		return $item;
	 }

	 /**
	  * $type FE or BE
	  */
	public function plgVmOnDisplayCustoms($FE,&$field,$product,$row){

	 	VmTable::bindParameterable($field,'custom_params',$this->_varsToPushParam);

	 	if (empty($field->custom_value)) return 0 ;
	 	if (!empty($field->custom_param) && is_string($field->custom_param)) $custom_param = json_decode($field->custom_param);
	 	else $custom_param = array();
	 	$field->custom_param = $custom_param;
	 	foreach($field->custom_param as $k => $v){
	 		if(!empty($v)){
	 			$field->$k = $v;
	 		}
	 	}
	 	vmdebug('my field',$field);

	 	if($FE){
	 		$html = $this->onDisplayProductFE( $field, $product, $row);
	 	} else {
	 		$html = $this->onProductEdit( $field, $product, $row);
	 	}

	 	return $html;
	 }


	/**
	 * Select the right file and class j1.5/j1.7
	 * Return new class $plgName
	 * @deprecated
	 */
	 private function setClass($name) {
		$plgName = 'plgVmCustom'.ucfirst ($name );
		vmTrace('setClass');
		if(class_exists($plgName)) return new $plgName;
		else {
			if  ( VmConfig::isJ15() ) {
				$path = JPATH_SITE.DS.'plugins'.DS.'vmcustom'.DS.$name.'.php';
			} else {
				$path = JPATH_SITE.DS.'plugins'.DS.'vmcustom'.DS.$name.DS.$name.'.php';
			}
			if (is_file($path)) {
				require($path);
				return new $plgName;
			} else return false ;
		}
	 }
}
