<?php
/**
*
* Order status controller
*
* @package	VirtueMart
* @subpackage OrderStatus
* @author Oscar van Eijk
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
 * Controller class for the Order status
 *
 * @package    VirtueMart
 * @subpackage OrderStatus
 * @author     Oscar van Eijk
 */
class VirtuemartControllerOrderstatus extends VmController {

	/**
	 * Method to display the view
	 *
	 * @access public
	 * @author
	 */
	function __construct()
	{
		parent::__construct();

		$this->setMainLangKey('ORDER_STATUS');
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
		JRequest::setVar('hidemainmenu', 1);

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
			$msg = JText::_('COM_VIRTUEMART_ORDER_STATUS_SAVED');
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
			$msg = JText::_('COM_VIRTUEMART_ERROR_ORDER_STATUSES_COULD_NOT_BE_DELETED');
		} else {
			$msg = JText::_('COM_VIRTUEMART_ORDER_STATUSES_DELETED');
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
