<?php
/**
*
* Orders controller
*
* @package	VirtueMart
* @subpackage
* @author RolandD
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

// Load the controller framework
jimport('joomla.application.component.controller');

if(!class_exists('VmController'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmcontroller.php');


/**
* Orders Controller
*
* @package    VirtueMart
* @author
*/
class VirtuemartControllerOrders extends VmController {

	/**
	 * Method to display the view
	 *
	 * @access	public
	 * @author
	 */
	function __construct() {
		parent::__construct();

	}

	/**
	* Shows the product list screen
	*/
	public function Orders() {
		/* Create the view object */
		$view = $this->getView('orders', 'html');

		/* Default model */
		$view->setModel( $this->getModel( 'orders', 'VirtueMartModel' ), true );

		/* Set the layout */
		$view->setLayout('orders');

		/* Now display the view. */
		$view->display();
	}

	/**
	* Print the order details
	*/
	public function orderPrint()
	{
		/* Create the view object */
		$view = $this->getView('orders', 'pdf');

		/* Default model */
		$view->setModel( $this->getModel( 'orders', 'VirtueMartModel' ), true );
		/* Additional models */
		$view->setModel( $this->getModel( 'userfields', 'VirtueMartModel' ));
		$view->setModel( $this->getModel( 'product', 'VirtueMartModel' ));

		/* Set the layout */
		$view->setLayout('order_print');

		/* Now display the view. */
		$view->display();
	}

	/**
	* Shows the order details
	*/
	public function edit()
	{
		/* Create the view object */
		$view = $this->getView('orders', 'html');

		/* Default model */
		$view->setModel( $this->getModel( 'orders', 'VirtueMartModel' ), true );
		/* Additional models */
		$view->setModel( $this->getModel( 'userfields', 'VirtueMartModel' ));
		$view->setModel( $this->getModel( 'product', 'VirtueMartModel' ));

		/* Set the layout */
		$view->setLayout('order');

		/* Now display the view. */
		$view->display();
	}
	/**
	 * Generic cancel task
	 *
	 * @author Max Milbers
	 */
	public function cancel(){
		// back from order
		$this->setRedirect('index.php?option=com_virtuemart&view=orders' );
	}
	/**
	* Shows the order details
	*/
	public function editOrderStatus() {
		/* Create the view object */
		$view = $this->getView('orders', 'html');

		/* Default model */
		$model = $this->getModel('orders');
		$model->updateStatus();
		/* Now display the view. */
		$view->display();
	}

	/**
	* Update an order status
	*
	* @author RolandD
	*/
	public function updatestatus() {
		$mainframe = Jfactory::getApplication();

		/* Load the view object */
		$view = $this->getView('orders', 'html');

		/* Load the helper */
		$view->loadHelper('shopFunctions');
		$view->loadHelper('vendorHelper');

		/* Update the statuses */
		$model = $this->getModel('orders');
		$result = $model->updateStatus();

		if ($result['updated'] > 0)
		    $msg = str_replace('{X}', $result['updated'], JText::_('COM_VIRTUEMART_ORDER_UPDATED_SUCCESSFULLY'));
		if ($result['error'] > 0)
		    $msg .= str_replace('{X}', $result['error'], JText::_('COM_VIRTUEMART_ORDER_NOT_UPDATED_SUCCESSFULLY'));
		
		if ('updatestatus'== JRequest::getVar('last_task')) {
			$virtuemart_order_id = JRequest::getVar('virtuemart_order_id');
			$mainframe->redirect('index.php?option=com_virtuemart&view=orders&task=edit&virtuemart_order_id='.$virtuemart_order_id , $msg);
		}
		else {
			$mainframe->redirect('index.php?option=com_virtuemart&view=orders', $msg);
		}
	}


	/**
	 * Save changes to the order item status
	 *
	 */
	public function saveItemStatus() {
		$mainframe = Jfactory::getApplication();

		/* Load the view object */
		$view = $this->getView('orders', 'html');

		/* Load the helper */
		$view->loadHelper('shopFunctions');
		$view->loadHelper('vendorHelper');

		$data = JRequest::get('post');
		$model = $this->getModel('orders');
		$model->updateItemStatus(JArrayHelper::toObject($data), $data['new_status']);

		$mainframe->redirect('index.php?option=com_virtuemart&view=orders&task=edit&virtuemart_order_id='.$data['virtuemart_order_id']);
	}


	/**
	 * Display the order item details for editing
	 */
	public function editOrderItem() {
	    JRequest::setVar('layout', 'orders_editorderitem');
	    JRequest::setVar('hidemenu', 1);

	    parent::display();
	}


	/**
	 * correct position, working with json? actually? WHat ist that?
	 *
	* Get a list of related products
	* @author RolandD
	*/
	public function getProducts() {
		/* Create the view object */
		$view = $this->getView('orders', 'json');

		/* Default model */
		$view->setModel( $this->getModel( 'product', 'VirtueMartModel' ), true );

		$view->setLayout('orders_editorderitem');

		/* Now display the view. */
		$view->display();
	}


	/**
	 * Update status for the selected order items
	 */
	public function updateOrderItemStatus()
	{
		$mainframe = Jfactory::getApplication();
		$model = $this->getModel('orders');
		$_items = JRequest::getVar('cid',  0, '', 'array');
		$_status = JRequest::getVar('order_status', '');
		$_orderID = JRequest::getVar('virtuemart_order_id', '');
		foreach ($_items as $_item) {
			$model->updateSingleItemStatus($_item, $_status[$_orderID]);
		}
		$mainframe->redirect('index.php?option=com_virtuemart&view=orders&task=edit&virtuemart_order_id='.$_orderID);
	}

	/**
	 * Update a single order item
	 */
	public function updateOrderItem()
	{
		$mainframe = Jfactory::getApplication();
		$model = $this->getModel('orders');
		$model->updateSingleItem();
		$mainframe->redirect('index.php?option=com_virtuemart&view=orders&task=edit&virtuemart_order_id='.JRequest::getVar('virtuemart_order_id', ''));
	}

	/**
	* Save the given order item
	*/
	public function saveOrderItem() {
	    $orderId = JRequest::getVar('virtuemart_order_id', '');
	    $model = $this->getModel('orders');
	    $msg = '';

	    if (!$model->saveOrderLineItem()) {
		$msg = $model->getError();
	    }

	    $editLink = 'index.php?option=com_virtuemart&view=orders&task=edit&virtuemart_order_id=' . $orderId;
	    $this->setRedirect($editLink, $msg);
	}


	/**
	* Removes the given order item
	*/
	public function removeOrderItem() {
	    $model = $this->getModel('orders');
	    $msg = '';
	    $orderId = JRequest::getVar('orderId', '');
	    $orderLineItem = JRequest::getVar('orderLineId', '');

	    if (!$model->removeOrderLineItem($orderLineItem)) {
			$msg = $model->getError();
	    }

	    $editLink = 'index.php?option=com_virtuemart&view=orders&task=edit&virtuemart_order_id=' . $orderId;
	    $this->setRedirect($editLink, $msg);
	}
}
// pure php no closing tag
