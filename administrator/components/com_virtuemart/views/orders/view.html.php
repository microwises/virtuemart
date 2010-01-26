<?php
/**
 *
 * Description
 *
 * @package	VirtueMart
 * @subpackage
 * @author
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
 * HTML View class for the VirtueMart Component
 *
 * @package		VirtueMart
 * @author
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

	$curTask = JRequest::getVar('task');
	if ($curTask == 'edit') {
	    /* Get the data */
	    $order = $this->get('Order');
	    $userfields = shopFunctions::getUserFields('registration', false, '', true, true );
	    $shippingfields = shopFunctions::getUserFields('shipping', false, '', true, true );
	    $orderbt = $order['details']['BT'];
	    $orderst = (array_key_exists('ST', $order['details'])) ? $order['details']['ST'] : $orderbt;

	    /* Assign the data */
	    $this->assignRef('order', $order);
	    $this->assignRef('userfields', $userfields);
	    $this->assignRef('shippingfields', $shippingfields);
	    $this->assignRef('orderbt', $orderbt);
	    $this->assignRef('orderst', $orderst);

	    JHTML::_('behavior.modal');
	    $this->setLayout('orders_edit');

	    /* Toolbar */
	    JToolBarHelper::title(JText::_( 'VM_ORDER_EDIT_LBL' ), 'vm_orders_48');
	    JToolBarHelper::cancel();
	}
	else if ($curTask == 'editOrderItem') {
	    /* Get order statuses */
	    $orderstatuses = $this->get('OrderStatusList');
	    $this->assignRef('orderstatuses', $orderstatuses);

	    $model = $this->getModel();
	    $orderId = JRequest::getVar('orderId', '');
	    $orderLineItem = JRequest::getVar('orderLineId', '');
	    $this->assignRef('order_id', $orderId);
	    $this->assignRef('order_item_id', $orderLineItem);

	    $orderItem = $model->getOrderLineDetails($orderId, $orderLineItem);
	    $this->assignRef('orderitem', $orderItem);
	}
	else {
	    $this->setLayout('orders');

	    /* Get the data */
	    $orderslist = $this->get('OrdersList');

	    /* Get order statuses */
	    $orderstatuses = $this->get('OrderStatusList');
	    $this->assignRef('orderstatuses', $orderstatuses);

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
	}

	/* Assign general statuses */


	parent::display($tpl);
    }

}
?>
