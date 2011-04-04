<?php
/**
*
* Module View
*
* @package	VirtueMart
* @subpackage Module
* @author Markus Öhler
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
jimport('joomla.application.component.view');

/**
 * HTML View class for maintaining the list of modules
 *
 * @package	VirtueMart
 * @subpackage Module
 * @author Markus Öhler
 */
class VirtuemartViewModule extends JView {

  function display($tpl = null) {
		// Load the helper(s)
		$this->loadHelper('adminMenu');

		$model = $this->getModel();
		//$vendorModel = $this->getModel('Vendor');

		$module = $model->getModule();
		$layoutName = JRequest::getVar('layout', 'default');
		$isNew = ($module->shopper_group_id < 1);

		if ($layoutName == 'edit') {
		  if ($isNew) {
				JToolBarHelper::title(  JText::_('COM_VIRTUEMART_MODULE_FORM_LBL' ).': <small><small>[ New ]</small></small>', 'vm_shop_users_48');
				JToolBarHelper::divider();
				JToolBarHelper::save();
				JToolBarHelper::cancel();
		  } else {
				JToolBarHelper::title( JText::_('COM_VIRTUEMART_MODULE_FORM_LBL' ).': <small><small>[ Edit ]</small></small>', 'vm_shop_users_48');
				JToolBarHelper::divider();
				JToolBarHelper::save();
				JToolBarHelper::cancel('cancel', 'Close');
		  }
		  $this->assignRef('shoppergroup',	$module);
		  //$this->assignRef('vendors',	$zoneModel->getShippingZoneSelectList());
		}	else {
			JToolBarHelper::title( JText::_( 'COM_VIRTUEMART_MODULE_LIST_LBL ' ), 'vm_shop_users_48' );
			JToolBarHelper::addNewX();
			JToolBarHelper::editListX();
			JToolBarHelper::deleteList('', 'remove', 'Delete');

			$pagination = $model->getPagination();
			$this->assignRef('pagination',	$pagination);

			$modules = $model->getModules();
			//$vendors = $vendorModel->getVendors());
			$this->assignRef('modules',	$modules);
			//$this->assignRef('vendors', $vendors);
		}
		parent::display($tpl);
  }

} // pure php no closing tag
