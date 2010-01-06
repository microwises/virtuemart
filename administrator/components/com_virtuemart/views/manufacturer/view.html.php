<?php
/**
 * Manufacturer View
 *
 * @package	VirtueMart
 * @subpackage Manufacturer
 * @author vhv_alex
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

jimport( 'joomla.application.component.view');

/**
 * HTML View class for maintaining the list of manufacturers
 *
 * @package	VirtueMart
 * @subpackage Manufacturer
 * @author vhv_alex
 */
class VirtuemartViewManufacturer extends JView {
	
	function display($tpl = null) {
		// Load the helper(s)
		$this->loadHelper('adminMenu');
		
		$mainframe = JFactory::getApplication();
		global $option;
		// get necessary models	
		$model = $this->getModel();
		$categoryModel = $this->getModel('manufacturerCategory');
				
        $mf_category_id	= $mainframe->getUserStateFromRequest( $option.'mf_category_id', 'mf_category_id', 0, 'int' );
		$search = $mainframe->getUserStateFromRequest( $option.'search', 'search', '', 'string' );
        
		$manufacturer = $model->getManufacturer();
		
        $layoutName = JRequest::getVar('layout', 'default');
        $isNew = ($manufacturer->manufacturer_id < 1);
		
		if ($layoutName == 'edit') {
			if ($isNew) {
				JToolBarHelper::title(  JText::_('VM_MANUFACTURER_FORM_MNU' ).': <small><small>[ New ]</small></small>', 'vm_manufacturer_48');
				JToolBarHelper::divider();
				JToolBarHelper::save();
				JToolBarHelper::cancel();
			}
			else {
				JToolBarHelper::title( JText::_('VM_MANUFACTURER_FORM_MNU' ).': <small><small>[ Edit ]</small></small>', 'vm_manufacturer_48');
				JToolBarHelper::divider();
				JToolBarHelper::save();
				JToolBarHelper::cancel('cancel', 'Close');
			}
			
			$this->assignRef('manufacturer',	$manufacturer);

			$manufacturerCategories = $categoryModel->getManufacturerCategories();
			$this->assignRef('manufacturerCategories',	$manufacturerCategories);	
        }
        else {
			JToolBarHelper::title( JText::_( 'VM_MANUFACTURER_FORM_MNU' ), 'vm_manufacturer_48' );
			JToolBarHelper::publishList();
			JToolBarHelper::unpublishList();
			JToolBarHelper::deleteList('', 'remove', 'Delete');
			JToolBarHelper::editListX();
			JToolBarHelper::addNewX();	
			
			$pagination = $model->getPagination();			
			$this->assignRef('pagination',	$pagination);	
			
			
			$manufacturers = $model->getManufacturers();
			$this->assignRef('manufacturers',	$manufacturers);	
			
			
			
		}	
		$categoryFilter = $categoryModel->getCategoryFilter();

		$list['mf_category_id'] =  JHTML::_('select.genericlist',   $categoryFilter, 'mf_category_id', 'class="inputbox" onchange="this.form.submit()"', 'value', 'text', $mf_category_id );
		$list['search'] = $search;
		
		$this->assignRef('list', $list);
		$this->assignRef('manufacturers',	$manufacturers);
		
		parent::display($tpl);
	}
	
}
?>
