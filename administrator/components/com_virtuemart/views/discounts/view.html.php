<?php
/**
* @package		VirtueMart
*/

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the VirtueMart Component
 *
 * @package		VirtueMart
 */
class VirtuemartViewDiscounts extends JView {
	
	function display($tpl = null) {
		$mainframe = Jfactory::getApplication();
		$option = JRequest::getVar('option');
		$lists = array();
		/* Load helpers */
		$this->loadHelper('adminMenu');
		
		/* Get the task */
		$task = JRequest::getVar('task');
		
		switch ($task) {
			case 'add':
			case 'edit':
				/* Get the data */
				$discount = $this->get('Discount');
				
				/* The discount type */
				$lists['discount_type'] = JHTML::_('select.booleanlist', 'is_percent', '', $discount->is_percent, JText::_('VM_PRODUCT_DISCOUNT_ISPERCENT'), JText::_('VM_PRODUCT_DISCOUNT_ISTOTAL'));
				
				/* Load the behaviours */
				JHTML::_('behavior.calendar');
				JHTML::_('behavior.tooltip');
				
				/* Set the dates to today if we are adding */
				if ($task == 'add') {
					$discount->start_date = time();
					$discount->end_date = time();
				}
				
				/* Toolbar */
				JToolBarHelper::title(JText::_( 'VM_PRODUCT_DISCOUNT_ADDEDIT' ), 'vm_coupon_48');
				JToolBarHelper::save();
				JToolBarHelper::cancel();
				
				/* Assign the data */
				$this->assignRef('discount', $discount);
				$this->assignRef('lists', $lists);
				break;
			default:
				/* Get the data */
				$discountslist = $this->get('Discounts');
				
				/* Get the pagination */
				$pagination = $this->get('Pagination');
				$lists['filter_order'] = $mainframe->getUserStateFromRequest($option.'filter_order', 'filter_order', '', 'cmd');
				$lists['filter_order_Dir'] = $mainframe->getUserStateFromRequest($option.'filter_order_Dir', 'filter_order_Dir', '', 'word');
				
				/* Toolbar */
				JToolBarHelper::title(JText::_( 'VM_PRODUCT_DISCOUNT_LIST_LBL' ), 'vm_coupon_48');
				JToolBarHelper::deleteListX();
				JToolBarHelper::editListX();
				JToolBarHelper::addNewX();
				
				/* Assign the data */
				$this->assignRef('discountslist', $discountslist);
				$this->assignRef('pagination',	$pagination);
				$this->assignRef('lists',	$lists);
				break;
		}
		
		parent::display($tpl);
	}
	
}
?>
