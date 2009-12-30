<?php
/**
 * Credit Card View
 *
 * @package	VirtueMart
 * @subpackage CreditCard
 * @author RickG
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

jimport( 'joomla.application.component.view');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'adminMenu.php');

/**
 * HTML View class for maintaining the list of Credit Cards
 *
 * @package	VirtueMart
 * @subpackage CreditCard
 * @author RickG 
 */
class VirtuemartViewCreditcard extends JView {
	
	function display($tpl = null) {	
		$model = $this->getModel();
		
        $creditcard =& $model->getCreditCard();
        
        $layoutName = JRequest::getVar('layout', 'default');
        $isNew = ($creditcard->creditcard_id < 1);
		
		if ($layoutName == 'edit') {
			if ($isNew) {
				JToolBarHelper::title(  JText::_('VM_CREDITCARD_LIST_ADD' ).': <small><small>[ New ]</small></small>', 'vm_credit_48');
				JToolBarHelper::divider();
				JToolBarHelper::save();
				JToolBarHelper::cancel();
			}
			else {
				JToolBarHelper::title( JText::_('VM_CREDITCARD_LIST_ADD' ).': <small><small>[ Edit ]</small></small>', 'vm_credit_48');
				JToolBarHelper::divider();
				JToolBarHelper::save();
				JToolBarHelper::cancel('cancel', 'Close');
			}
			$this->assignRef('creditcard',	$creditcard);
        }
        else {
			JToolBarHelper::title( JText::_( 'VM_CREDITCARD_LIST_LBL' ), 'vm_credit_48' );
			JToolBarHelper::deleteList('', 'remove', 'Delete');
			JToolBarHelper::editListX();
			JToolBarHelper::addNewX();	
			
			$pagination = $model->getPagination();			
			$this->assignRef('pagination',	$pagination);	
			
			$creditcards = $model->getCreditCards();
			$this->assignRef('creditcards',	$creditcards);	
		}			
		
		parent::display($tpl);
	}
	
}
?>
