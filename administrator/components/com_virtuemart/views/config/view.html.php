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
jimport( 'joomla.application.component.view');
jimport('joomla.html.pane');
jimport('joomla.version');

/**
 * HTML View class for the configuration maintenance
 *
 * @package		VirtueMart
 * @subpackage 	Config
 * @author 		RickG
 */
class VirtuemartViewConfig extends JView {

	function display($tpl = null) {

		// Load the helper(s)
		$this->loadHelper('adminMenu');
		$this->loadHelper('image');
		$this->loadHelper('html');

		$model = $this->getModel();
		$usermodel = $this->getModel('user');

		JToolBarHelper::title(JText::_('VM_CONFIG'), 'vm_config_48');
		JToolBarHelper::divider();
		JToolBarHelper::apply();
		JToolBarHelper::save();
		JToolBarHelper::cancel('cancel', 'Close');

		$config = $model->getConfig();
		$this->assignRef('config', $config);

		$mainframe = JFactory::getApplication();
		$this->assignRef('joomlaconfig', $mainframe);

		$userparams = JComponentHelper::getParams('com_users');
		$this->assignRef('userparams', $userparams);

		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'shopfunctions.php');
		$templateList = ShopFunctions::renderTemplateList(JText::_('VM_ADMIN_CFG_JOOMLA_TEMPLATE_DEFAULT'));

		$this->assignRef('jTemplateList', $templateList);

		$vmLayoutList = $model->getLayoutList('virtuemart');
		$this->assignRef('vmLayoutList', $vmLayoutList);

		$categoryLayoutList = $model->getLayoutList('category');
		$this->assignRef('categoryLayoutList', $categoryLayoutList);

		$productLayoutList = $model->getLayoutList('productdetails');
		$this->assignRef('productLayoutList', $productLayoutList);


		$noimagelist = $model->getNoImageList();
		$this->assignRef('noimagelist', $noimagelist);
		$orderStatusList = $model->getOrderStatusList();
		$this->assignRef('orderStatusList', $orderStatusList);
		$currConverterList = $model->getCurrencyConverterList();
		$this->assignRef('currConverterList', $currConverterList);
		$moduleList = $model->getModuleList();
		$this->assignRef('moduleList', $moduleList);
		$contentLinks = $model->getContentLinks();
		$this->assignRef('contentLinks', $contentLinks);
		$aclGroups = $usermodel->getAclGroupIndentedTree();
		$this->assignRef('aclGroups', $aclGroups);

		parent::display($tpl);
	}

}
// pure php no closing tag
