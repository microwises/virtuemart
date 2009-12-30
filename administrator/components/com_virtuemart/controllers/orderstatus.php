<?php
/**
 * Order status controller
 *
 * @package	VirtueMart
 * @subpackage OrderStatus
 * @author Oscar van Eijk
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

jimport('joomla.application.component.controller');

/**
 * Controller class for the Order status
 *
 * @package    VirtueMart
 * @subpackage OrderStatus
 * @author     Oscar van Eijk
 */
class VirtuemartControllerOrderstatus extends JController
{
	/**
	 * Method to display the view
	 *
	 * @access public
	 */
	function __construct()
	{
		parent::__construct();

		// Register Extra tasks
		$this->registerTask('add', 'edit');

		$document =& JFactory::getDocument();
		$viewType = $document->getType();
		$view =& $this->getView('orderstatus', $viewType);

		// Push a model into the view
		$model =& $this->getModel('orderstatus');

		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}
	}

	/**
	 * Display the order status view
	 */
	function display()
	{
//		JRequest::setVar( 'view', 'orderstatus');
		parent::display();
	}

	/**
	 * Handle the edit task
	 */
	function edit()
	{
		JRequest::setVar('controller', 'orderstatus');
		JRequest::setVar('view', 'orderstatus');
		JRequest::setVar('layout', 'edit');
		JRequest::setVar('hidemenu', 1);

		$document =& JFactory::getDocument();
		$viewType = $document->getType();
		$view =& $this->getView('orderstatus', $viewType);
		$view->setModel( $this->getModel( 'vendor', 'VirtueMartModel' ));
		
		parent::display();
	}

	/**
	 * Handle the cancel task
	 */
	function cancel()
	{
		$this->setRedirect('index.php?option=com_virtuemart&view=orderstatus');
	}

	/**
	 * Handle the save task
	 */
	function save()
	{
		$model =& $this->getModel('orderstatus');

		if ($model->store()) {
			$msg = JText::_('Order status saved!');
		} else {
			$msg = JText::_($model->getError());
		}
		$this->setRedirect('index.php?option=com_virtuemart&view=orderstatus', $msg);
	}

	/**
	 * Handle the remove task
	 */	
	function remove()
	{
		$model = $this->getModel('orderstatus');
		if (!$model->delete()) {
			$msg = JText::_('Error: One or more order statuses could not be deleted!');
		} else {
			$msg = JText::_( 'Order statuses Deleted!');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=orderstatus', $msg);
	}

	function orderup()
	{
		$model = $this->getModel('orderstatus');
		if (!$model->move(-1)) {
			$msg = JText::_($model->getError());
		} else {
			$msg = '';
		}
		$this->setRedirect('index.php?option=com_virtuemart&view=orderstatus', $msg);
	}

	function orderdown()
	{
		$model = $this->getModel('orderstatus');
		if (!$model->move(1)) {
			$msg = JText::_($model->getError());
		} else {
			$msg = '';
		}
		$this->setRedirect('index.php?option=com_virtuemart&view=orderstatus', $msg);
	}

	function saveorder()
	{
		$cid 	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		$order 	= JRequest::getVar( 'order', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);
		JArrayHelper::toInteger($order);

		$model = $this->getModel('orderstatus');
		$model->saveorder($cid, $order);

		$msg = 'New ordering saved';
		$this->setRedirect('index.php?option=com_virtuemart&view=orderstatus', $msg);
	}
}

//No Closing tag
