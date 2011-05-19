<?php
/**
*
* Manufacturer Category View
*
* @package	VirtueMart
* @subpackage Manufacturer Category
* @author Patrick Kohl
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

/**
 * HTML View class for maintaining the list of manufacturer categories
 *
 * @package	VirtueMart
 * @subpackage Manufacturer Categories
 * @author Patrick Kohl
 */
class VirtuemartViewManufacturercategories extends JView {

	function display($tpl = null) {

		// Load the helper(s)
		$this->loadHelper('adminMenu');

		// get necessary model
		$model = $this->getModel();
        $layoutName = JRequest::getVar('layout', 'default');
        $manufacturerCategory = $model->getManufacturerCategory();

		$viewName = JText::_('COM_VIRTUEMART_CONTROLLER_MANUFACTURERCATEGORIES');
		$this->assignRef('viewName',$viewName); 
		$taskName = JText::_('COM_VIRTUEMART_'.JRequest::getVar('task', 'list'));
		JToolBarHelper::title( JText::sprintf( 'COM_VIRTUEMART_STRING1_STRING2' ,$viewName, $taskName , 'vm_manufacturer_48');

		if ($layoutName == 'edit') {
			JToolBarHelper::divider();
			JToolBarHelper::apply();
			JToolBarHelper::save();
			JToolBarHelper::cancel();
			$this->assignRef('manufacturerCategory',	$manufacturerCategory);
        }
        else {
			JToolBarHelper::publishList();
			JToolBarHelper::unpublishList();
			JToolBarHelper::deleteList();
			JToolBarHelper::editListX();
			JToolBarHelper::addNewX();

			$pagination = $model->getPagination();
			$this->assignRef('pagination',	$pagination);

			$manufacturerCategories = $model->getManufacturerCategories();
			$this->assignRef('manufacturerCategories',	$manufacturerCategories);

		}
		parent::display($tpl);
	}

}
// pure php no closing tag
