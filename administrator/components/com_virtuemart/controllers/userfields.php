<?php
/**
*
* Userfields controller
*
* @package	VirtueMart
* @subpackage Userfields
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

/**
 * Controller class for the Order status
 *
 * @package    VirtueMart
 * @subpackage Userfields
 * @author     Oscar van Eijk
 */
class VirtuemartControllerUserfields extends JController {

	/**
	 * Method to display the view
	 *
	 * @access public
	 * @author
	 */
	function __construct()
	{
		parent::__construct();

		// Register Extra tasks
		$this->registerTask('add', 'edit');

		$document =& JFactory::getDocument();
		$viewType = $document->getType();
		$view =& $this->getView('userfields', $viewType);

		// Push a model into the view
		$model =& $this->getModel('userfields');

		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}
	}

	/**
	 * Display the userfields view
	 */
	function display()
	{
		parent::display();
	}

	/**
	 * Handle the edit task
	 */
	function edit()
	{
		JRequest::setVar('controller', 'userfields');
		JRequest::setVar('view', 'userfields');
		JRequest::setVar('layout', 'edit');
		JRequest::setVar('hidemainmenu', 1);

		$document =& JFactory::getDocument();
		$viewType = $document->getType();
		$view =& $this->getView('userfields', $viewType);
		$view->setModel( $this->getModel( 'vendor', 'VirtueMartModel' ));

		parent::display();
	}

	/**
	 * Handle the cancel task
	 */
	function cancel()
	{
		$this->setRedirect('index.php?option=com_virtuemart&view=userfields');
	}

	/**
	 * Handle the save task
	 */
	function save()
	{
		$model =& $this->getModel('userfields');

		if ($model->store()) {
			$msg = JText::_('Userfield saved!');
		} else {
			$msg = JText::_($model->getError());
		}
		$this->setRedirect('index.php?option=com_virtuemart&view=userfields', $msg);
	}

	/**
	 * Handle the remove task
	 */
	function remove()
	{
		$model = $this->getModel('userfields');
		if (!$model->delete()) {
			$msg = JText::_('Error: One or more order statuses could not be deleted!');
		} else {
			$msg = JText::_( 'Order statuses Deleted!');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=orderstatus', $msg);
	}

	function orderup()
	{
		$model = $this->getModel('userfields');
		if (!$model->move(-1)) {
			$msg = JText::_($model->getError());
		} else {
			$msg = '';
		}
		$this->setRedirect('index.php?option=com_virtuemart&view=userfields', $msg);
	}

	function orderdown()
	{
		$model = $this->getModel('userfields');
		if (!$model->move(1)) {
			$msg = JText::_($model->getError());
		} else {
			$msg = '';
		}
		$this->setRedirect('index.php?option=com_virtuemart&view=userfields', $msg);
	}

	function saveorder()
	{
		$cid 	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		$order 	= JRequest::getVar( 'order', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);
		JArrayHelper::toInteger($order);

		$model = $this->getModel('userfields');
		$model->saveorder($cid, $order);

		$msg = 'New ordering saved';
		$this->setRedirect('index.php?option=com_virtuemart&view=userfields', $msg);
	}

	function unpublish()
	{
		self::toggle('published', 0);
	}

	function publish()
	{
		self::toggle('published', 1);
	}

	function disable_required()
	{
		self::toggle('required', 0);
	}

	function enable_required()
	{
		self::toggle('required', 1);
	}

	function disable_registration()
	{
		self::toggle('registration', 0);
	}

	function enable_registration()
	{
		self::toggle('registration', 1);
	}

	function disable_shipping()
	{
		self::toggle('shipping', 0);
	}

	function enable_shipping()
	{
		self::toggle('shipping', 1);
	}

	function disable_account()
	{
		self::toggle('account', 0);
	}

	function enable_account()
	{
		self::toggle('account', 1);
	}

	function disable_readonly()
	{
		self::toggle('readonly', 0);
	}

	function enable_readonly()
	{
		self::toggle('readonly', 1);
	}

	function toggle($field, $value)
	{
		$id = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($id);

		if (count( $id ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to unpublish' ) );
		}

		$model = $this->getModel('userfields');
		if(!$model->toggle($field, $id, $value)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}
		$this->setRedirect( 'index.php?option=com_virtuemart&view=userfields' );
	}
}

//No Closing tag
