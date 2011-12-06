<?php
defined('_JEXEC') or 	die( 'Direct Access to ' . basename( __FILE__ ) . ' is not allowed.' ) ;
/**
 * @version $Id: standard.php,v 1.4 2005/05/27 19:33:57 ei
 *
 * a special type of 'product specification':
 * its fee depend on total sum
 * @author Max Milbers
 * @version $Id: standard.php 3681 2011-07-08 12:27:36Z alatak $
 * @package VirtueMart
 * @subpackage payment
 * @copyright Copyright (C) 2004-2008 soeren - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.org
 */

if (!class_exists('vmCustomPlugin')) require(JPATH_VM_PLUGINS . DS . 'vmcustomplugin.php');

class plgVmCustomSpecification extends vmCustomPlugin {


	// instance of class
	public static $_this = false;

	function __construct(& $subject, $config) {
		if(self::$_this) return self::$_this;
		parent::__construct($subject, $config);
		$this->tableFields = array_keys($this->getTableSQLFields());
		$varsToPush = array(
			'custom_specification_name1'=> array('', 'char'),
			'custom_specification_default1'=> array('', 'string'),
			'custom_specification_name2'=> array('', 'char'),
			'custom_specification_default2'=> array('', 'string'),

		);

		$this->setConfigParameterable('custom_params',$varsToPush);

		self::$_this = $this;
	}
    /**
     * Create the table for this plugin if it does not yet exist.
     * @author Valérie Isaksen
     */
    protected function getVmPluginCreateTableSQL() {
	return $this->createTableSQL('Product Specification Table');
    }
	
    function getTableSQLFields() {
	$SQLfields = array(
	    'id' => 'int(11) unsigned NOT NULL AUTO_INCREMENT',
	    'virtuemart_product_id' => 'int(11) UNSIGNED DEFAULT NULL',
	    'virtuemart_custom_id' => 'int(11) UNSIGNED DEFAULT NULL',
	    'custom_specification_name1' => 'char(128) NOT NULL DEFAULT \'\' ',
	    'custom_specification_default1' => 'varchar(1024) NOT NULL DEFAULT \'\' ',
	    'custom_specification_name2' => 'char(128) NOT NULL DEFAULT \'\' ',
	    'custom_specification_default2' => 'varchar(1024) NOT NULL DEFAULT \'\' '
	);

	return $SQLfields;
    }

	public function plgVmSelectSearchableCustom(&$selectList,$virtuemart_custom_id)
	{
		$db =&JFactory::getDBO();
		$db->setQuery('SELECT `virtuemart_custom_id`, `custom_title` FROM `#__virtuemart_customs` WHERE `custom_element` ="'.$this->_name.'"');
		$selectedPlugin = $db->loadAssocList();
		//vmdebug('selectedPlugin',$selectedPlugin);
		if ($selectedPlugin) $selectList = array_merge($selectedPlugin,$selectList);
		return true;
	}
	
	public function plgVmAddSearch(&$where,&$PluginJoinTables,$custom_id)
	{	
		$search = 'sho';
		$db = & JFactory::getDBO(); 
		if ($this->_name != $this->GetNameByCustomId($custom_id)) return;
		$search = '"%' . $db->getEscaped( $search, true ) . '%"' ;
		$where[] = 'l.`product_name` LIKE '.$search;
		$PluginJoinTables[] = $this->_name ;
		
	
	}
	
	// get product param for this plugin on edit
	function plgVmOnProductEdit($field, $product, &$row,&$retValue) { 
		if ($field->custom_element != $this->_name) return '';
		
		$this->parseCustomParams($field);
// 		$data = $this->getVmPluginMethod($field->virtuemart_custom_id);
// 		VmTable::bindParameterable($field,$this->_xParams,$this->_varsToPushParam);
//print_r($field);
// 		$html  ='<input type="text" value="'.$field->custom_title.'" size="10" name="custom_param['.$row.'][custom_title]"> ';
		$html ='<div>';
		$html .='<div>'.$field->custom_specification_name1.'</div>';
		$html .='<input type="text" value="'.$field->custom_specification_default1.'" size="10" name="plugin_param['.$row.']['.$this->_name.'][custom_specification_default1]">';
		$html .='<div>'.$field->custom_specification_name2.'</div>';
		$html .='<input type="text" value="'.$field->custom_specification_default2.'" size="10" name="plugin_param['.$row.']['.$this->_name.'][custom_specification_default2]">';
		$html .='<input type="hidden" value="'.$field->virtuemart_custom_id.'" name="plugin_param['.$row.']['.$this->_name.'][virtuemart_custom_id]">';
		$html .='</div>';
// 		$field->display = 
		$retValue .= $html  ;
		$row++;
		return true  ;
	}

	/**
	 * @ idx plugin index
	 * @see components/com_virtuemart/helpers/vmCustomPlugin::onDisplayProductFE()
	 * @author Patrick Kohl
	 * eg. name="customPlugin['.$idx.'][comment] save the comment in the cart & order
	 */
	function plgVmOnDisplayProductFE($product,&$idx,&$group) {
		// default return if it's not this plugin
		
		if ($group->custom_value != $this->_name) return '';
		$this->parseCustomParams($group);
		$this->plgVmGetPluginInternalDataCustom($group);
		
		// Here the plugin values
		//$html =JTEXT::_($group->custom_title) ;
		$html ='<div>';
		$html .='<div class="product-fields-title">'.$group->custom_specification_name1.'</div>';
		$html .='<div>'.$group->custom_specification_default1.'</div>';
		$html .='<div class="product-fields-title">'.$group->custom_specification_name2.'</div>';
		$html .='<div>'.$group->custom_specification_default2.'</div>';
		$html .='</div>';
		$group->display .= $html;
		// preventing 2 x load javascript


        return true;
    }

	function plgVmOnDisplayProductVariantFE($field,&$idx,&$group) {}
	/**
	 * @see components/com_virtuemart/helpers/vmCustomPlugin::plgVmOnViewCartModule()
	 * @author Patrick Kohl
	 */
	function plgVmOnViewCartModule( $product,$productCustom, $row,&$html) {
		if (!$plgParam = $this->GetPluginInCart($product)) return '' ;		
		if(!empty($plgParam['comment']) ){
			return ' = '.$plgParam['comment'];
		}
		return '';
    }

	/**
	 * @see components/com_virtuemart/helpers/vmCustomPlugin::plgVmplgVmOnViewCart()
	 * @author Patrick Kohl
	 */
	function plgVmOnViewCart($product,$productCustom, $row,&$html) {
		$comment ='';
		// foreach($plgParam as $k => $item){
			if(!empty($plgParam['comment']) ){
				$comment .= ' = '.$plgParam['comment'];
			}
		// }
// 		$comment = current($product->param);
		$html  = '<div>';
		$html .='<span>'.$comment.'</span>';
		// $html .='<span>'.$param->Morecomment.'</span>';
		return $html.'</div>';
    }


	/**
	 *
	 * vendor order display BE
	 */
	function plgVmDisplayInOrderBE($item,$productCustom, $row,$plgParam) {
		if ($productCustom->custom_value != $this->_name) return '';
		$comment ='';
			if(!empty($plgParam['comment']) ){
				$comment .= ' = '.$plgParam['comment'];
			}
		$html  = '<div>';
		$html .='<span>'.$comment.'</span>';
		return $html.'</div>';
    }

	/**
	 *
	 * shopper order display FE
	 */
	function plgVmDisplayInOrderFE($item,$productCustom, $row,$plgParam) {
		if ($productCustom->custom_value != $this->_name) return '';
		$comment ='';
			if(!empty($plgParam['comment']) ){
				$comment .= ' = '.$plgParam['comment'];
			}
		$html  = '<div>';
		$html .='<span>'.$comment.'</span>';
		return $html.'</div>';
    }

	 public function plgVmGetProductStockToUpdateByCustom($item, $pluginParam, $productCustom) {
		return $item ;
	 }

	/**
	 * We must reimplement this triggers for joomla 1.7
	 * vmplugin triggers note by Max Milbers
	 */
	protected function plgVmOnStoreInstallPluginTable($psType) {
		parent::plgVmOnStoreInstallPluginTable($psType);
	}

	function plgVmGetDeclaredPluginParams($psType,$name,$id){
		return parent::plgVmGetDeclaredPluginParams($psType,$name,$id);
	}

	/**
	 * Custom triggers note by Max Milbers
	 */
	function plgVmGetActiveCustomPlugin($virtuemart_custom_id,&$customPlugin){
		parent::plgVmGetActiveCustomPlugin($virtuemart_custom_id,$customPlugin);
	}
	/*
	 * No price modification
	 */
	public function plgVmCalculateCustomVariant($product, &$productCustomsPrice,$selected,$row){
		return ;
	}

	public function plgVmDisplayInCartCustom($product,$productCustom, $row ,$view=''){
		parent::plgVmDisplayInCartCustom($product,$productCustom, $row ,$view);
	}

	public function plgVmDisplayInOrderCustom(&$html,$item, $param,$productCustom, $row ,$view='FE'){
		parent::plgVmDisplayInOrderCustom($html,$item, $param,$productCustom, $row ,$view);
	}

	public function plgVmCreateOrderLinesCustom(&$html,$item,$productCustom, $row ){
		parent::plgVmCreateOrderLinesCustom($html,$item,$productCustom, $row );
	}
}

// No closing tag