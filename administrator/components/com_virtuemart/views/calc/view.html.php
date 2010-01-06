<?php
/**
 * Country View
 *
 * @package	VirtueMart
 * @subpackage Country
 * @author RickG
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

jimport( 'joomla.application.component.view');

/**
 * HTML View class for maintaining the list of countries
 *
 * @package	VirtueMart
 * @subpackage Calculation tool
 * @author Max Milbers 
 */
class VirtuemartViewCalc extends JView {
	
	function display($tpl = null) {

		// Load the helper(s)
		$this->loadHelper('adminMenu');

		$model = $this->getModel('calc');

        $calc = $model->getCalc();
        
        $layoutName = JRequest::getVar('layout', 'default');
        $isNew = ($calc->calc_id < 1);
		
		if ($layoutName == 'edit') {
			if ($isNew) {
				JToolBarHelper::title(  JText::_('VM_CALC_LIST_ADD' ).': <small><small>[ New ]</small></small>', 'vm_countries_48');
				JToolBarHelper::divider();
				JToolBarHelper::save();
				JToolBarHelper::cancel();
			}
			else {
				JToolBarHelper::title( JText::_('VM_CALC_LIST_ADD' ).': <small><small>[ Edit ]</small></small>', 'vm_countries_48');
				JToolBarHelper::divider();
				JToolBarHelper::save();
				JToolBarHelper::cancel('cancel', 'Close');
			}
			
			$this->assignRef('calc',	$calc);
			
			/* Load some common models */
			$category_model = $this->getModel('category');
			$this->assignRef('category_model',	$category_model);
			echo 'Categories: '.$calc->calc_categories.'<br />';
			$category_tree= null;
			$this->assignRef('category_tree', $category_tree);
			if (isset($calc->calc_categories)) $category_tree = $category_model->list_tree('', 0, 0, $calc->calc_categories);
				else $category_tree = $category_model->list_tree();
			
			//$this->assignRef('shippingZones',	$zoneModel->getShippingZoneSelectList());
        }
        else {
			JToolBarHelper::title( JText::_( 'VM_CALC_LIST_LBL' ), 'vm_countries_48' );
			JToolBarHelper::publishList();
			JToolBarHelper::unpublishList();
			JToolBarHelper::deleteList('', 'remove', 'Delete');
			JToolBarHelper::editListX();
			JToolBarHelper::addNewX();	
			
			$pagination = $model->getPagination();			
			$this->assignRef('pagination',	$pagination);	
			
			$calcs = $model->getCalcs();
			$this->assignRef('calcs',	$calcs);	
		}
		require_once(CLASSPATH. 'ps_perm.php' );
		$perm = new ps_perm();
		$perm->check( 'admin' );
		$this->assignRef('perm',	$perm);
		$this->assignRef('model',	$model);

//		$this->assignRef('calc_categories', $calc->calc_categories);
	
		
		parent::display($tpl);
	}
	
}
?>
