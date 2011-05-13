<?php
/**
*
* Manufacturer View
*
* @package	VirtueMart
* @subpackage Manufacturer
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
 * HTML View class for maintaining the list of manufacturers
 *
 * @package	VirtueMart
 * @subpackage Manufacturer
 * @author Patrick Kohl
 */
class VirtuemartViewManufacturer extends JView {

	function display($tpl = null) {

		// Load the helper(s)
		$this->loadHelper('adminMenu');
		$this->loadHelper('image');

		$mainframe = JFactory::getApplication();
		global $option;
		// get necessary models
		$model = $this->getModel();
		$categoryModel = $this->getModel('manufacturercategories');

        $virtuemart_manufacturer_category_id	= $mainframe->getUserStateFromRequest( $option.'virtuemart_manufacturer_category_id', 'virtuemart_manufacturer_category_id', 0, 'int' );
		$search = $mainframe->getUserStateFromRequest( $option.'search', 'search', '', 'string' );


        $layoutName = JRequest::getVar('layout', 'default');

		if ($layoutName == 'edit') {

			$manufacturer = $model->getManufacturer();
       		$isNew = ($manufacturer->virtuemart_manufacturer_id < 1);

			if ($isNew) {
				JToolBarHelper::title(  JText::_('COM_VIRTUEMART_MANUFACTURER_FORM_MNU').JText::_('COM_VIRTUEMART_FORM_NEW'), 'vm_manufacturer_48');
			} else {
				JToolBarHelper::title( JText::_('COM_VIRTUEMART_MANUFACTURER_FORM_MNU').JText::_('COM_VIRTUEMART_FORM_EDIT'), 'vm_manufacturer_48');
			}
			JToolBarHelper::divider();
			JToolBarHelper::save();
                        JToolBarHelper::apply();
			JToolBarHelper::cancel();

			$model->addImagesToManufacturer($manufacturer);
			$this->assignRef('manufacturer',	$manufacturer);
//			 /* Process the images */
//			if(!class_exists('VirtueMartModelMedia')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'media.php');
//			$mediaModel = new VirtueMartModelMedia();
//			$mediaModel -> setId($manufacturer->file_ids);
//			$image = $mediaModel->getFile('manufacturer','image');

			$manufacturerCategories = $categoryModel->getManufacturerCategories();
			$this->assignRef('manufacturerCategories',	$manufacturerCategories);
        }
        else {
			JToolBarHelper::title( JText::_('COM_VIRTUEMART_MANUFACTURER_FORM_MNU'), 'vm_manufacturer_48' );
			JToolBarHelper::publishList();
			JToolBarHelper::unpublishList();
			JToolBarHelper::deleteList('', 'remove', 'Delete');
			JToolBarHelper::editListX();
			JToolBarHelper::addNewX();

			$pagination = $model->getPagination();
			$this->assignRef('pagination',	$pagination);


			$manufacturers = $model->getManufacturers();
			$this->assignRef('manufacturers',	$manufacturers);
			$categoryFilter = $categoryModel->getCategoryFilter();

			$list['virtuemart_manufacturer_category_id'] =  JHTML::_('select.genericlist',   $categoryFilter, 'virtuemart_manufacturer_category_id', 'class="inputbox" onchange="this.form.submit()"', 'value', 'text', $virtuemart_manufacturer_category_id );
			$list['search'] = $search;

			$this->assignRef('list', $list);


		}


		parent::display($tpl);
	}

}
// pure php no closing tag
