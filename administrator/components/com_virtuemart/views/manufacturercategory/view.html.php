<?php
/**
 * Manufacturer Category View
 *
 * @package	VirtueMart
 * @subpackage Manufacturer Category
 * @author vhv_alex
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

jimport( 'joomla.application.component.view');

/**
 * HTML View class for maintaining the list of manufacturer categories
 *
 * @package	VirtueMart
 * @subpackage Manufacturer Categories
 * @author vhv_alex 
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
				JToolBarHelper::title(  JText::_('VM_MANUFACTURER_CATEGORY_LIST_ADD' ).': <small><small>[ New ]</small></small>', 'vm_manufacturer_48');
				JToolBarHelper::divider();
				JToolBarHelper::save();
				JToolBarHelper::cancel();
			}
			else {
				JToolBarHelper::title( JText::_('VM_MANUFACTURER_CATEGORY_LIST_ADD' ).': <small><small>[ Edit ]</small></small>', 'vm_manufacturer_48');
				JToolBarHelper::divider();
				JToolBarHelper::save();
				JToolBarHelper::cancel('cancel', 'Close');
			}
				
			$this->assignRef('manufacturerCategory',	$manufacturerCategory);			
        }
        else {
			JToolBarHelper::title( JText::_( 'VM_MANUFACTURER_LIST_LBL' ), 'vm_manufacturer_48' );
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
?>
