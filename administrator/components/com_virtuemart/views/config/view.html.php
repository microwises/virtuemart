<?php
/**
 * @package		VirtueMart
 * @subpackage 	Config
 * @author 		RickG 
*/

jimport( 'joomla.application.component.view');
jimport('joomla.html.pane');

/**
 * HTML View class for the configuration maintenance
 *
 * @package		VirtueMart
 * @subpackage 	Config
 * @author 		RickG 
 */
class VirtuemartViewConfig extends JView
{
	
	function display($tpl = null)
	{
		// Load the helper(s)
		$this->loadHelper('adminMenu');
		$this->loadHelper('image');

		$model = $this->getModel();
		$usermodel = $this->getModel('user');

		JToolBarHelper::title(JText::_('VM_CONFIG'), 'vm_config_48');
		JToolBarHelper::divider();
		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::cancel('cancel', 'Close');
	
		$config = $model->getConfig();
		$this->assignRef('config', $config);			
	
		$mainframe = JFactory::getApplication();
		$this->assignRef('joomlaconfig', $mainframe);
		$themelist = $model->getThemeList();
		$this->assignRef('themelist', $themelist);
		$templatelist = $model->getTemplateList();
		$this->assignRef('templatelist', $templatelist);
		$flypagelist = $model->getFlypageList();
		$this->assignRef('flypagelist', $flypagelist);	
		$noimagelist = $model->getNoImageList();
		$this->assignRef('noimagelist', $noimagelist);		
		$orderStatusList = $model->getOrderStatusList();
		$this->assignRef('orderStatusList', $orderStatusList);
		$moduleList = $model->getModuleList();
		$this->assignRef('moduleList', $moduleList);
		$contentLinks = $model->getContentLinks();
		$this->assignRef('contentLinks', $contentLinks);
		$aclGroups = $usermodel->getAclGroupIndentedTree();
		$this->assignRef('aclGroups', $aclGroups);
	
		parent::display($tpl);
	}
}
?>
