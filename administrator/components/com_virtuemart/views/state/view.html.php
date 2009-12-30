<?php
/**
 * State View
 *
 * @package	VirtueMart
 * @subpackage State
 * @author RickG
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

jimport( 'joomla.application.component.view');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'adminMenu.php');

/**
 * HTML View class for maintaining the list of states
 *
 * @package	VirtueMart
 * @subpackage State
 * @author Max Milbers
 */
class VirtuemartViewState extends JView {
	
	function display($tpl = null) {	
		$model = $this->getModel();
		$zoneModel = $this->getModel('ShippingZone');

		$state =& $model->getState();
        
        $layoutName = JRequest::getVar('layout', 'default');        
		$countryId = JRequest::getVar('country_id', '');
		$published = JRequest::getVar('published', '');

		$this->assignRef('country_id',	$countryId);	        
 		$this->assignRef('published',	$published);	        
        
        $isNew = ($state < 1);
		
		if ($layoutName == 'edit') {
			if ($isNew) {
				JToolBarHelper::title(  JText::_('VM_STATE_LIST_ADD' ).': <small><small>[ New ]</small></small>', 'vm_states_48');
				JToolBarHelper::divider();
				JToolBarHelper::save();
				JToolBarHelper::cancel();
			}
			else {
				JToolBarHelper::title( JText::_('VM_STATE_LIST_ADD' ).': <small><small>[ Edit ]</small></small>', 'vm_states_48');
				JToolBarHelper::divider();
				JToolBarHelper::save();
				JToolBarHelper::cancel('cancel', 'Close');
			}
			$this->assignRef('state', $state);
			
			$this->assignRef('shippingZones', $zoneModel->getShippingZoneSelectList());
        }
        else {
			JToolBarHelper::title( JText::_( 'VM_STATE_LIST_LBL' ), 'vm_states_48' );
			JToolBarHelper::publishList();
			JToolBarHelper::unpublishList();
			JToolBarHelper::deleteList('', 'remove', 'Delete');
			JToolBarHelper::editListX();
			JToolBarHelper::addNewX();	
			
			$pagination = $model->getPagination();			
			$this->assignRef('pagination',	$pagination);	

			$states = $model->getStates($countryId);
			
			$this->assignRef('states',	$states);	
		}			
		
		parent::display($tpl);
	}
	
}
?>
