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
class VirtuemartViewOrders extends JView {

    function display($tpl = null) {
	$mainframe = JFactory::getApplication();
	$option = JRequest::getVar('option');
	$lists = array();

	/* Load helpers */
	$this->loadHelper('adminMenu');
	$this->loadHelper('currencydisplay');
	$this->loadHelper('shopFunctions');

	/* Get order statuses */
	$orderstatuses = $this->get('OrderStatusList');

	switch (JRequest::getVar('task')) {
	    case 'edit':
	    /* Get the data */
		$order = $this->get('Order');

		$userfields = shopFunctions::getUserFields('registration', false, '', true, true );
		$shippingfields = shopFunctions::getUserFields('shipping', false, '', true, true );

		/* Assign the data */
		$this->assignRef('order', $order);
		$this->assignRef('userfields', $userfields);
		$this->assignRef('shippingfields', $shippingfields);

		/* Load helper */
		jimport('joomla.html.pane');

		/* Toolbar */
		JToolBarHelper::title(JText::_( 'VM_ORDER_EDIT_LBL' ), 'vm_orders_48');
		JToolBarHelper::save('updateorder', JText::_('VM_UPDATE_ORDER'));
		break;
	    default:
	    /* Get the data */
		$orderslist = $this->get('OrdersList');

		/* Apply currency */
		$currencydisplay = new CurrencyDisplay();
		foreach ($orderslist as $order_id => $order) {
		    $order->order_total = $currencydisplay->getValue($order->order_total);
		}

		/* Get the pagination */
		$pagination = $this->get('Pagination');
		$lists['filter_order'] = $mainframe->getUserStateFromRequest($option.'filter_order', 'filter_order', '', 'cmd');
		$lists['filter_order_Dir'] = $mainframe->getUserStateFromRequest($option.'filter_order_Dir', 'filter_order_Dir', '', 'word');

		/* Toolbar */
		JToolBarHelper::title(JText::_( 'VM_ORDER_LIST_LBL' ), 'vm_orders_48');
		JToolBarHelper::save('updatestatus', JText::_('VM_UPDATE_STATUS'));
		JToolBarHelper::deleteListX();

		/* Assign the data */
		$this->assignRef('orderslist', $orderslist);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('lists',	$lists);
		break;
	}
	/* Assign general statuses */
	$this->assignRef('orderstatuses', $orderstatuses);

	parent::display($tpl);
    }

}
?>
