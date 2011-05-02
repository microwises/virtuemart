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
class VirtuemartViewManufacturerCategory extends JView {

	function display($tpl = null) {

		// Load the helper(s)
		$this->loadHelper('adminMenu');

		// get necessary model
		$model = $this->getModel();

        $layoutName = JRequest::getVar('layout', 'default');

        $manufacturerCategory = $model->getManufacturerCategory();

        $isNew = ($manufacturerCategory->mf_category_id < 1);

		if ($layoutName == 'edit') {
			if ($isNew) {
				JToolBarHelper::title(  JText::_('COM_VIRTUEMART_MANUFACTURER_CATEGORY_LIST_ADD').JText::_('COM_VIRTUEMART_FORM_NEW'), 'vm_manufacturer_48');
			} else {
				JToolBarHelper::title( JText::_('COM_VIRTUEMART_MANUFACTURER_CATEGORY_LIST_ADD').JText::_('COM_VIRTUEMART_FORM_EDIT'), 'vm_manufacturer_48');
			}
			JToolBarHelper::divider();
			JToolBarHelper::apply();
			JToolBarHelper::save();
			JToolBarHelper::cancel();
			$this->assignRef('manufacturerCategory',	$manufacturerCategory);
        }
        else {
			JToolBarHelper::title( JText::_('COM_VIRTUEMART_MANUFACTURER_LIST_LBL'), 'vm_manufacturer_48' );
			JToolBarHelper::publishList();
			JToolBarHelper::unpublishList();
			JToolBarHelper::deleteList('', 'remove', 'Delete');
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
