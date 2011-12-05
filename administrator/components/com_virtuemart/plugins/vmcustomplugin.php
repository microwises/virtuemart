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

	/*
	 * helper to parse plugin parameters as object 
	 * 
	 */
	public function parseCustomParams(&$field) {
		
    	VmTable::bindParameterable($field,'custom_params',$this->_varsToPushParam);

    	if (empty($field->custom_value)) return 0 ;
    	if (!empty($field->custom_param) && is_string($field->custom_param)) $custom_param = json_decode($field->custom_param,true);
    	else $custom_param = array();
    	$field->custom_param = $custom_param;
    	foreach($field->custom_param as $k => $v){
    		if(!empty($v)){
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
    function plgVmOnStoreProduct($type,&$data,$id){

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
		vmPlugin::plgVmGetDeclaredPluginParams('vmcustom',$productCustomsPrice->custom_element,$productCustomsPrice->custom_jplugin_id);
		VmTable::bindParameterable($productCustomsPrice,'custom_params',$this->_varsToPushParam);
		//$product = VirtueMartModelCustomfields::addParam($product);
		static $pluginFields;
		if (!isset($pluginFields)) {
				 $pluginFields = JRequest::getVar('customPlugin',null );
				// print_r($field);
				if ($pluginFields ==  null) $pluginFields = json_decode( $product->customPlugin, true);
		}
		return $pluginFields[$productCustomsPrice->virtuemart_custom_id][$this->_name] ;
    	 
    }

    /**
     * convert param for render and
     * display The plugin in cart
     * @ $view is "Module" for see in module, "" for see in cart
     */
    public function GetPluginInCart($product){
    	//$plgName = $productCustom->value;

		foreach($product->param as $k => $plg){
			if (key($plg)== $this->_name)
			return	$plg[$this->_name];
		}
    	return null ;

    }
    /**
     * display The plugin in order view FE/BE
     * @ $view is "BE" for see in back-End, default is FE
     */
    public function plgVmDisplayInOrderCustom(&$html,$item, $param,$productCustom, $row ,$view='FE'){
		if ($productCustom->custom_value != $this->_name) return '';
		$plgFunction = 'onViewOrder'.$view ;
		//$html ='';
		$html .= $this->$plgFunction( $item,$productCustom, $row, $param );

    	// defaut render if the plugin is not found/installed
    	// if ($productCustom->value!=$this->_name) {
    		// $html.=  '<div style="color: #CC0000;">plugin <b>'.$productCustom->value.'</b> not found.</div><br/>';
    		// if ($view =='FE') $html.=  implode(',',(array)$param);
    		// else foreach ((array)$param as $key=>$text) $html.=  '<span>parameter : '.$key.' : '.$text. '<span><br/>';
    		// return ;
    	// }
    	// else {

    		// $html = $this->$plgFunction( $item,$param,$productCustom, $row);
    	// }

    	return $html;
    }

	/**
     * display The plugin in order view FE/BE
     * @ $view is "BE" for see in back-End, default is FE
     */
    public function plgVmCreateOrderLinesCustom(&$html,$item,$productCustom, $row ,$view='FE'){
		if ($productCustom->custom_value != $this->_name) return '';
		$plgFunction = 'onViewOrder'.$view ;
		$html ='';
		foreach($item->param as $k => $plg){
			if (key($plg)== $this->_name)
				$html .= $this->$plgFunction( $item,$productCustom, $row,$plg[$this->_name]);
		}

    	// defaut render if the plugin is not found/installed
    	// if ($productCustom->value!=$this->_name) {
    		// $html.=  '<div style="color: #CC0000;">plugin <b>'.$productCustom->value.'</b> not found.</div><br/>';
    		// if ($view =='FE') $html.=  implode(',',(array)$param);
    		// else foreach ((array)$param as $key=>$text) $html.=  '<span>parameter : '.$key.' : '.$text. '<span><br/>';
    		// return ;
    	// }
    	// else {

    		// $html = $this->$plgFunction( $item,$param,$productCustom, $row);
    	// }

    	return $html;
    }


	/**
	 * render the plugin with param  to display on product edit
	 * called by customfields inputTypePlugin
	 *
	 */
	abstract function plgVmOnProductEdit($field, $product, $row);

	/**
	 * display the plugin on product FE
	 */
	abstract function plgVmOnDisplayProductFE( $field, $idx);

	/**
	 * display the product plugin on cart module
	 */
	abstract function plgVmOnViewCartModule( $product,$productCustom, $row);

	/**
	* display the product plugin on cart
	 */
	abstract function plgVmOnViewCart($product, $productCustom, $row);

	/**
	 * display the plugin in order
	 * TODO One for customer and one for vendor
	 * Get the statut (Eg. payed. >> render only the link for downloadable )
	 */
	abstract function plgVmDisplayInOrderBE($item, $productCustom, $row,$plgParam);

	/**
	 * display the plugin in order
	 * TODO One for customer and one for vendor
	 * Get the statut (Eg. payed. >> render only the link for downloadable )
	 */
	abstract function plgVmDisplayInOrderFE($item, $productCustom, $row,$plgParam);
	/**
	 * StockToUpdate
	 * can remove or change or adding more virtuemart_product_id eg. to do packs
	 */
	abstract function plgVmGetProductStockToUpdateByCustom($item,$pluginParam, $productCustom);

	

}
