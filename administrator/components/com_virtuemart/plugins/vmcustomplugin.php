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
 * @version $Id: vmshipmentplugin.php 4007 2011-08-31 07:31:35Z alatak $
 */
// Load the helper functions that are needed by all plugins
if (!class_exists('VmHTML')) require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'html.php');
if (!class_exists('vmPlugin')) require(JPATH_VM_PLUGINS . DS . 'vmplugin.php');

// Get the plugin library
jimport('joomla.plugin.plugin');

/**
 * Abstract class for shipment plugins.
 * This class provides some standard and abstract methods that can or must be reimplemented.
 *
 * @tutorial All methods are documented, but to make life easier, here's a short overview
 * how the methods can be used in the process order.
 * 	* _createTable() is called by the constructor. Use this method to create or alter the database table.
 * 	* When a shopper selects a shipment, plgOnSelectShipment() is fired. It displays the shipment and can be used
 * 	for collecting extra - shipment specific - info.
 * 	* After selecting, plgVmpluginSelected() can be used to store extra shipment info in the cart. The selected shipment
 * 	ID will be stored in the cart by the checkout process before this method is fired.
 * 	* plgOnConfirmShipment() is fired when the order is confirmed and stored to the database. It is called
 * 	before the rest of the order or stored, when reimplemented, it *must* include a call to parent::plgOnConfirmShipment()
 * 	(or execute the same steps to put all data in the cart)
 *
 * When a stored order is displayed in the backend, the following events are used:
 * 	* plgVmOnShowOrderBE() displays specific data about (a) shipment(s) (NOTE: this plugin is
 * 	OUTSIDE any form!)
 * 	* plgVmOnShowOrderLineShipmentBE() can be used to show information about a single orderline, e.g.
 * 	display a package code at line level when more packages are shipped.
 * 	* plgVmOnEditOrderLineBE() can be used add a package code for an order line when more
 * 	packages are shipped.
 * 	* plgVmOnUpdateOrderBE is fired inside a form. It can be used to add shipment data, like package code.
 * 	* plgVmOnSaveOrderShipmentBE() is fired from the backend after the order has been saved. If one of the
 * 	show methods above have to option to add or edit info, this method must be used to save the data.
 * 	* plgVmOnUpdateOrderLine() is fired from the backend after an order line has been saved. This method
 * 	must be reimplemented if plgVmOnEditOrderLineBE() is used.
 *
 * The frontend 1 show method:
 * 	* plgVmOnShowOrderFE() collects and displays specific data about (a) shipment(s)
 *
 * @package	VirtueMart
 * @subpackage Plugins
 * @author Oscar van Eijk
 */
abstract class vmCustomPlugin extends JPlugin {


    /**
     * @var array List with all carriers the have been implemented with the plugin in the format
     * id => name
     */
    protected $customs;

	function __construct() {

		parent::__construct('vmcustom', array());

		$this->_tablename = '#__virtuemart_product_custom_' . $this->_name;
		$this->_createTable();

	}

    /**
     * This functions gets the used and configured customplugins
     * virtuemart_custom_id determines the used jplugin.
     * The right custom_param is determined by the vendor and the jplugin id.
     *
     * This function sets the custom plugin param
     * @author Patrick Kohl
     *
     */
	function getVmCustomParams($virtuemart_custom_id=0,$vendorId=0 ) {

	if (!$vendorId)
            $vendorId = 1;
		$db = JFactory::getDBO();
		$db->setQuery('SELECT `custom_params`,`custom_element` FROM `#__virtuemart_customplugins` WHERE virtuemart_custom_id =' .(int)$virtuemart_custom_id);
		$plg_params = $db->loadObject();
		if (empty($plg_params)) return array();

        return  new JParameter( $plg_params->custom_params );
    }
	/**
	 * render the plugin with param  to display on product edit
	 * called by customfields inputTypePlugin
	 */
	abstract function onProductEdit($field,$param,$row, $product_id);

	/**
	 * display the plugin on product FE
	 */
	abstract function onDisplayProductFE( $field, $param, $product, $idx);

	/**
	 * display the product plugin on cart module
	 */
	abstract function onViewCartModule( $product,$custom_param,$productCustom, $row);

	/**
	* display the product plugin on cart
	 */
	abstract function onViewCart($product, $param,$productCustom, $row);

	/**
	 * display the plugin in order
	 * TODO One for customer and one for vendor
	 * Get the statut (Eg. payed. >> render only the link for downloadable )
	 */
	abstract function onViewOrderBE($product, $param,$productCustom, $row);

	/**
	 * defaut price modifation if nothing is set in plugin
	 * you have to rewrite it in your plugin to do other calculations
	 */
	public function modifyPrice( $product, $field,$param,$selected ) {
		if (!empty($field->custom_price)) {
			//TODO adding % and more We should use here $this->interpreteMathOp
			return $field->custom_price;
		}
	}

	/**
	 * display The plugin in Product view FE
	 * override displayType() customfields.
	 */
	 public function displayTypePlugin($field,$product,$row){

		if (empty($field->custom_value)) return '';
		if (!empty($field->custom_param)) $custom_param = json_decode($field->custom_param,true);
		else $custom_param = array();

		$plg = self::setClass($field->custom_value) ;
		return $plg->onDisplayProductFE(  $field,$custom_param, $product, $row);
	 }
	 /**
	 * Calculate the variant price by The plugin
	 * override calculateModificators() in calculatorh.
	 * Eg. recalculate price by a quantity set in the plugin
	 * You must reimplement modifyPrice() in your plugin
	 * or price is returned defaut custom_price
	 */
	 public function calculatePluginVariant( $product, $field,$selected,$row){

		if (empty($field->custom_value)) return 0 ;
		if (!empty($field->custom_param)) $custom_param = json_decode($field->custom_param,true);
		else $custom_param = array();

		$plg = self::setClass($field->custom_value) ;
		return $plg->modifyPrice( $product, $field,$custom_param,$selected,$row);
	 }

	 /**
	 * convert param for render and
	 * display The plugin in cart
	 * @ $view is "Module" for see in module, "" for see in cart
	 */
	 public function displayInCartPlugin($product,$productCustom, $row ,$view=''){
		$plgName = $productCustom->value;
		if ($plgName) {
			$plg = self::setClass($plgName) ;
			$plgFunction = 'onViewCart'.$view ;
			if ( empty($product->param[$row])) $param = null ;
			else $param = $product->param[$row] ;
			return $plg->$plgFunction( $product,$param ,$productCustom, $row);
		} else return '';
	 }
	 /**
	 * display The plugin in order view FE/BE
	 * @ $view is "BE" for see in back-End, default is FE
	 */
	 public function displayInOrderPlugin($item,$param,$productCustom, $row ,$view='FE'){
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
			$plg = self::setClass($plgName) ;
			$plgFunction = 'onViewOrder'.$view ;
			$html = $plg->$plgFunction( $item,$param,$productCustom, $row);
		} else return '';

		return $html;
	 }

	/**
	 * display The plugin in Product edit view BE
	 * extend customFields inputType
	 */
	 public function inputTypePlugin($field,$product_id,$row){

		if (!empty($field->custom_param)) $custom_param = json_decode($field->custom_param,true);
		else $custom_param = array();

		if ($field->custom_value) {
			$plg = self::setClass($field->custom_value) ;
			$html = $plg->onProductEdit(  $field,$custom_param, $row, $product_id);
		} else return '';
		return $html;
	 }

	/**
	 * Select the right file and class j1.5/j1.7
	 * Return new class $plgName
	 */
	 private function setClass($name) {
		$plgName = 'plgVmCustom'.ucfirst ($name );
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
