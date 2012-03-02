<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Config
* @author RickG
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id$
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the view framework
if(!class_exists('VmView'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmview.php');
jimport('joomla.html.pane');
jimport('joomla.version');

/**
 * HTML View class for the configuration maintenance
 *
 * @package		VirtueMart
 * @subpackage 	Config
 * @author 		RickG
 */
class VirtuemartViewConfig extends VmView {

	function display($tpl = null) {

		// Load the helper(s)

		$this->loadHelper('image');
		$this->loadHelper('html');

		$model = VmModel::getModel();
		$usermodel = VmModel::getModel('user');

		 JToolBarHelper::title( JText::_('COM_VIRTUEMART_CONFIG') , 'head vm_config_48');

		$this->addStandardEditViewCommands();

		$config = VmConfig::loadConfig();

		$this->assignRef('config', $config);

		$mainframe = JFactory::getApplication();
		$this->assignRef('joomlaconfig', $mainframe);

		$userparams = JComponentHelper::getParams('com_users');
		$this->assignRef('userparams', $userparams);

		$templateList = ShopFunctions::renderTemplateList(JText::_('COM_VIRTUEMART_ADMIN_CFG_JOOMLA_TEMPLATE_DEFAULT'));

		$this->assignRef('jTemplateList', $templateList);

		$vmLayoutList = $model->getLayoutList('virtuemart');
		$this->assignRef('vmLayoutList', $vmLayoutList);

// Outcommented to revert rev. 2916
//		$vendorList = ShopFunctions::renderVendorList(VmConfig::get('default_virtuemart_vendor_id'));
//		// We must replace the fieldname and ID 'virtuemart_vendor_id' to 'default_vendor'
//		$vendorList = preg_replace('/"virtuemart_vendor_id"/', '"default_virtuemart_vendor_id"', $vendorList);
//		$this->assignRef('vendorList', $vendorList);

		$categoryLayoutList = $model->getLayoutList('category');
		$this->assignRef('categoryLayoutList', $categoryLayoutList);

		$productLayoutList = $model->getLayoutList('productdetails');
		$this->assignRef('productLayoutList', $productLayoutList);

		$noimagelist = $model->getNoImageList();
		$this->assignRef('noimagelist', $noimagelist);

/*
		$oderstatusModel = VmModel::getModel('Orderstatus');
		$orderStatusList = $oderstatusModel->getOrderStatusList();
		$this->assignRef('orderStatusList', $orderStatusList);
*/
		$currConverterList = $model->getCurrencyConverterList();
		$this->assignRef('currConverterList', $currConverterList);
		$moduleList = $model->getModuleList();
		$this->assignRef('moduleList', $moduleList);
		//$contentLinks = $model->getContentLinks();
		//$this->assignRef('contentLinks', $contentLinks);
		$activeLanguages = $model->getActiveLanguages( VmConfig::get('active_languages') );
		$this->assignRef('activeLanguages', $activeLanguages);

		$orderByFields = $model->getProductFilterFields('browse_orderby_fields');
		$this->assignRef('orderByFields', $orderByFields);

		$searchFields = $model->getProductFilterFields( 'browse_search_fields');
		$this->assignRef('searchFields', $searchFields);

		$aclGroups = $usermodel->getAclGroupIndentedTree();
		$this->assignRef('aclGroups', $aclGroups);

		if(is_Dir(VmConfig::get('vmtemplate').DS.'images'.DS.'availability'.DS)){
			$imagePath = VmConfig::get('vmtemplate').'/images/availability/';
		} else {
			$imagePath = '/components/com_virtuemart/assets/images/availability/';
		}
		$this->assignRef('imagePath', $imagePath);

		if(VmConfig::get('forSale_path',0)===0){
// 			VmConfig::set('forSale_path',JPATH_ROOT.'/imagesimages/stories/virtuemart/forSale/');
			VmWarn('COM_VIRTUEMART_WARN_NO_SAFE_PATH_SET',JPATH_ROOT);
		}
		parent::display($tpl);
	}



}
// pure php no closing tag
