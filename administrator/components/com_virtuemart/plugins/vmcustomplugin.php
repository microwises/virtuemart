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

	function onDisplayEditBECustom($virtuemart_custom_id, &$customPlugin){

		//if($this->plugin = $this->selectedThisByMethodId($this->_psType,$virtuemart_custom_id)){
		if($this->plugin = $this->selectedThisByMethodId( $virtuemart_custom_id)){

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
  		$customPlugin = $this->plugin;
//   		return $this->plugin;
		return true;
		}
	}

	/*
	 * helper to parse plugin parameters as object
	 *
	 */
	public function parseCustomParams(&$field) {

    	VmTable::bindParameterable($field,'custom_params',$this->_varsToPushParam);

    	if (empty($field->custom_value)) return 0 ;
    	if (!empty($field->custom_param) && is_string($field->custom_param)) $custom_param = json_decode($field->custom_param,true);
    	else return ;
    	//$field->custom_param = $custom_param;
    	foreach($custom_param as $k => $v){
    		if(!empty($v)){
				//echo ' $k:'.$k.' $v:'.$v;
    			$field->$k = $v;
    		}
    	}

	}

	protected function getPluginProductDataCustom(&$field,$product_id){

		$id = $this->getIdForCustomIdProduct( $product_id,$field->virtuemart_custom_id) ;

	 	$datas = $this->getPluginInternalData($id);
		if($datas){
			//$fields = get_object_vars($datas);
			vmdebug('datas',$datas);
			foreach($datas as $k=>$v){
				if (!is_string($v) ) continue ;// Only get real Table variable
				if (isset($field->$k) && $v===0) continue ;
				$field->$k = $v;
			}
		}

	}
	/**
	 * This is the actions which take place, when a product gets stored
	 *
	 * @param string $type atm valid 'product'
	 * @param array $data form data
	 * @param int $id virtuemart_product_id
	 */
    function OnStoreProduct($data,$plugin_param){

		if (key($plugin_param)!==$this->_name) return ;

		$key = key($plugin_param) ;
		$plugin_param[$key]['virtuemart_product_id'] = $data['virtuemart_product_id'];
		vmdebug('plgData',$plugin_param[$key]);
		// $this->id = $this->getIdForCustomIdProduct($data['virtuemart_product_id'],$plugin_param[$key]['virtuemart_custom_id']);
		$this->storePluginInternalDataProduct($plugin_param[$key],'id',$data['virtuemart_product_id']);
    }

	/**
	 * This stores the data of the plugin, attention NOT the configuration of the pluginmethod,
	 * this function should never be triggered only called from triggered functions.
	 *
	 * @author Max Milbers
	 * @param array $values array or object with the data to store
	 * @param string $tableName When different then the default of the plugin, provid it here
	 * @param string $tableKey an additionally unique key
	 */
	protected function storePluginInternalDataProduct(&$values, $primaryKey=0, $product_id = 0 ){

		if($primaryKey===0) $primaryKey = $this->_tablepkey;
		if($this->_vmpItable===0){
			$this->_vmpItable = $this->createPluginTableObject($this->_tablename,$this->tableFields,$primaryKey,$this->_tableId,$this->_loggable);
		}
		//vmdebug('storePluginInternalData',$value);
		$ok = true;
		$msg = '';

		if(!$this->_vmpItable->bind($values)){
			$ok = false;
			$msg = 'bind';
			// 			vmdebug('Problem in bind '.get_class($this).' '.$this->_db->getErrorMsg());
			vmdebug('Problem in bind '.get_class($this).' ');
		}

		if($ok){
			if(!$this->_vmpItable->checkDataContainsTableFields($values)){
				$ok = false;
				//    			$msg .= ' developer notice:: checkDataContainsTableFields';
			}
		}

		if($ok){
			if(!$this->_vmpItable->check()){
				$ok = false;
				$msg .= ' check';
				vmdebug('Check returned false '.get_class($this).' '.$this->_vmpItable->_db->getErrorMsg());
				return false;
			}
		}

		if($ok){
			$this->_vmpItable->setLoggableFieldsForStore();

			$this->_vmpItable->storeParams();

			$id = 0;
			$custom_id = $values['virtuemart_custom_id'];
				if( !empty($custom_id) && !empty($product_id) ){
					$_qry = 'SELECT `id` FROM `#__virtuemart_product_custom_plg_'.$this->_name.'` WHERE `virtuemart_product_id`='.(int)$product_id.' and `virtuemart_custom_id`='.(int)$custom_id ;
					$this->_vmpItable->_db->setQuery($_qry);
					$id = $this->_vmpItable->_db->loadResult();
				}

	//		$this->_vmpItable->setError($_qry,'$_qry');

			if ( !empty($id) ) {
				$this->_vmpItable->id = $id;
				$returnCode = $this->_vmpItable->_db->updateObject($this->_vmpItable->_tbl, $this->_vmpItable, 'id', false);
			} else {
				$returnCode = $this->_vmpItable->_db->insertObject($this->_vmpItable->_tbl, $this->_vmpItable, 'id');
			}

			if (!$returnCode) {
				$this->_vmpItable->setError(get_class($this) . '::store failed - ' . $this->_vmpItable->_db->getErrorMsg());
				return false;
			}
			else
				return true;
		}
		// $this->_vmpItable->bindChecknStore($values);
		$errors = $this->_vmpItable->getErrors();
		if(!empty($errors)){
			foreach($errors as $error){
				$this->setError($error);
			}
		}
		return $values;

	}

    /**
    * Calculate the variant price by The plugin
    * override calculateModificators() in calculatorh.
    * Eg. recalculate price by a quantity set in the plugin
    * You must reimplement modifyPrice() in your plugin
    * or price is returned defaut custom_price
    */
    // 	 public function plgVmCalculatePluginVariant( $product, $field,$selected,$row){
    public function getCustomVariant($product, &$productCustomsPrice,$selected,$row){
		if ($productCustomsPrice->custom_element !==$this->_name) return ;

		vmPlugin::declarePluginParams('vmcustom',$productCustomsPrice->custom_element,$productCustomsPrice->custom_jplugin_id,$productCustomsPrice);
// 		VmTable::bindParameterable($productCustomsPrice,'custom_params',$this->_varsToPushParam);

		static $pluginFields;
		if (!isset($pluginFields)) {
				 $pluginFields = JRequest::getVar('customPlugin',null );
				if ($pluginFields ==  null) $pluginFields = json_decode( $product->customPlugin, true);
		}
		return $pluginFields[$productCustomsPrice->virtuemart_custom_id][$this->_name] ;

    }

    /**
     * convert param for render and
     * display The plugin in cart
     */
    public function GetPluginInCart($product){
    	//$plgName = $productCustom->value;

    	if(!empty($product->param)){
    		foreach($product->param as $k => $plg){
    			if (key($plg)== $this->_name)
    			return	$plg[$this->_name];
    		}
    	}

    	return null ;

    }


	/**
	 * render the plugin with param  to display on product edit
	 * called by customfields inputTypePlugin
	 *
	 */
	public function selectSearchableCustom(&$selectList)
	{
		return null;
	}

	/**
	 * render the plugin with param  to display on product edit
	 * called by customfields inputTypePlugin
	 *
	 */
	public function plgVmAddToSearch(&$where,$searchplugin)
	{

	}

	/**
	 * render the plugin with param  to display on product edit
	 * called by customfields inputTypePlugin
	 *
	 */
	public function GetNameByCustomId($custom_id)
	{
		static $custom_element ;
		if (isset($custom_element)) return $custom_element;
		$db = & JFactory::getDBO();
		$q = 'SELECT `custom_element` FROM `#__virtuemart_customs` WHERE `virtuemart_custom_id`='.(int)$custom_id;
		$db->setQuery($q);
		$custom_element = $db->loadResult();
		return $custom_element;

	}

	/**
	 * render the plugin with param  to display on product edit
	 * called by customfields inputTypePlugin
	 *
	 */
	public function getIdForCustomIdProduct($product_id,$custom_id)
	{
		$db = & JFactory::getDBO();
		$q = 'SELECT `id` FROM `#__virtuemart_product_custom_plg_'.$this->_name.'` WHERE `virtuemart_product_id`='.(int)$product_id.' and `virtuemart_custom_id`='.(int)$custom_id;
		$db->setQuery($q);
		return $db->loadResult();
	}

}
