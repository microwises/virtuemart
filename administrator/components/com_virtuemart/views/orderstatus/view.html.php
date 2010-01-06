<?php
/**
 * List/add/edit/remove Order Status Types
 *
 * @package	VirtueMart
 * @subpackage OrderStatus
 * @author Oscar van Eijk
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

jimport( 'joomla.application.component.view');

/**
 * HTML View class for maintaining the list of order types
 *
 * @package	VirtueMart
 * @subpackage OrderStatus
 * @author Oscar van Eijk
 */
class VirtuemartViewOrderstatus extends JView
{
	function display($tpl = null)
	{
		global $mainframe, $option;

		// Load the helper(s)
		$this->loadHelper('adminMenu');

		$model = $this->getModel();
		$orderStatus = $model->getOrderStatus();

		$layoutName = JRequest::getVar('layout', 'default');

		if ($layoutName == 'edit') {
			$editor = JFactory::getEditor();

			if ($orderStatus->order_status_id < 1) {
				JToolBarHelper::title(  JText::_('VM_ORDER_STATUS_FORM_MNU' ).': <small><small>[ New ]</small></small>', 'vm_orderstatus_48');
				JToolBarHelper::divider();
				JToolBarHelper::save();
				JToolBarHelper::cancel();
				
				$this->assignRef('ordering', JText::_('New items default to the last place. Ordering can be changed after this item is saved.'));
			} else {
				// Ordering dropdown
				$qry = 'SELECT ordering AS value, order_status_name AS text'
					. ' FROM #__vm_order_status'
					. ' ORDER BY ordering';
				$ordering = JHTML::_('list.specificordering',  $orderStatus, $orderStatus->order_status_id, $qry);
				$this->assignRef('ordering', $ordering);

				JToolBarHelper::title( JText::_('VM_ORDER_STATUS_FORM_MNU' ).': <small><small>[ Edit ]</small></small>', 'vm_orderstatus_48');
				JToolBarHelper::divider();
				JToolBarHelper::save();
				JToolBarHelper::cancel('cancel', 'Close');
			}
			// Vendor selection
			$vendor_model = $this->getModel('vendor');
			$vendor_list = $vendor_model->getVendors();
			$lists['vendors'] = JHTML::_('select.genericlist', $vendor_list, 'vendor_id', '', 'vendor_id', 'vendor_name', $orderStatus->vendor_id);

			$this->assignRef('lists', $lists);
			$this->assignRef('orderStatus', $orderStatus);
			$this->assignRef('editor', $editor);
		} else {
			JToolBarHelper::title( JText::_('VM_ORDER_STATUS_LIST_MNU'), 'vm_orderstatus_48' );
			JToolBarHelper::deleteList('', 'remove', 'Delete');
			JToolBarHelper::editListX();
			JToolBarHelper::addNewX();

			$pagination = $model->getPagination();
			$this->assignRef('pagination', $pagination);

			$orderStatusList = $model->getOrderStatusList();
			$this->assignRef('orderStatusList', $orderStatusList);

			// Get the ordering
			$lists['order']     = $mainframe->getUserStateFromRequest( $option.'filter_order', 'filter_order', 'ordering', 'cmd' );
			$lists['order_Dir'] = $mainframe->getUserStateFromRequest( $option.'filter_order_Dir', 'filter_order_Dir', '', 'word' );
			$this->assignRef('lists', $lists);
		}

		parent::display($tpl);
	}
}

//No Closing Tag
