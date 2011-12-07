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
     * @author Val�rie Isaksen
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
		if ($this->selectedPlugin->selectList = $db->loadAssocList() ) {
		//vmdebug('$this->selectedPlugin',$this->selectedPlugin);
			if ($virtuemart_custom_id) {
				$db->setQuery('SELECT custom_specification_default1 FROM `#__virtuemart_product_custom_plg_'.$this->_name.'` WHERE custom_parent_id='.$virtuemart_custom_id);
				$this->selectedPlugin->virtuemart_custom_id=$virtuemart_custom_id;
				$this->selectedPlugin->custom_title='plugin';
				$db->setQuery('SELECT `custom_specification_default1`,`custom_value` as title FROM `#__virtuemart_product_customfields` WHERE virtuemart_custom_id='.$virtuemart_custom_id);
				$this->selectedPlugin->selected->fields[$virtuemart_custom_id] = $db->loadObjectList();
			}
		$selectList = array_merge((array)$this->selectedPlugin,$selectList);
		}
		return true;
	}
	
	public function plgVmAddToSearch(&$where,&$PluginJoinTables,$custom_id)
	{	
		$keyword = vmRequest::uword('keyword', null, ' ');
		$db = & JFactory::getDBO(); 
		if ($this->_name != $this->GetNameByCustomId($custom_id)) return;
		$keyword = '"%' . $db->getEscaped( $keyword, true ) . '%"' ;
		$where[] = $this->_name .'.`custom_specification_default1` LIKE '.$keyword;
		$PluginJoinTables[] = $this->_name ;
		
	
	}
	
	// get product param for this plugin on edit
	function plgVmOnProductEdit($field, $product, &$row,&$retValue) { 
		if ($field->custom_element != $this->_name) return '';
		
		$this->parseCustomParams($field);
// 		$data = $this->getVmPluginMethod($field->virtuemart_custom_id);
// 		VmTable::bindParameterable($field,$this->_xParams,$this->_varsToPushParam);
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
	 *  Display product
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

        return true;
    }

    function plgVmOnStoreProduct($data,$plugin_param){
		return parent::OnStoreProduct($data,$plugin_param);
	}
	/**
	 * We must reimplement this triggers for joomla 1.7
	 * vmplugin triggers note by Max Milbers
	 */
	protected function plgVmOnStoreInstallPluginTable($psType) {
		return parent::onStoreInstallPluginTable($psType);
	}

	function plgVmGetDeclaredPluginParams($psType,$name,$id){
	return parent::getDeclaredPluginParams($psType, $name, $id);
	}

	/**
	 * Custom triggers note by Max Milbers
	 */
	function plgVmGetActiveCustomPlugin($virtuemart_custom_id,&$customPlugin){
		return parent::getActiveCustomPlugin($virtuemart_custom_id,$customPlugin);
	}

}

// No closing tag