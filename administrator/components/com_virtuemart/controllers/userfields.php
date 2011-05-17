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

if(!class_exists('VmController'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmcontroller.php');


/**
 * Controller class for the Order status
 *
 * @package    VirtueMart
 * @subpackage Userfields
 * @author     Oscar van Eijk
 */
class VirtuemartControllerUserfields extends VmController {

	/**
	 * Method to display the view
	 *
	 * @access public
	 * @author
	 */
	function __construct()
	{
		parent::__construct();

//		$this->setMainLangKey('USERFIELD');
		// Register Extra tasks
		$this->registerTask('add', 'edit');
		$this->registerTask('apply','save');

		$document =& JFactory::getDocument();
		$viewType = $document->getType();
		$view =& $this->getView('userfields', $viewType);
		$view->loadHelper('paramhelper');

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

		// Load the additional models
		$view->setModel( $this->getModel( 'vendor', 'VirtueMartModel' ));
		$view->setModel( $this->getModel( 'shoppergroup', 'VirtueMartModel' ));

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

		if ($id = $model->store()) {
			$msg = JText::_('COM_VIRTUEMART_USERFIELD_SAVED');
		} else {
			$msg = $model->getError();
		}

		$cmd = JRequest::getCmd('task');
		if($cmd == 'apply') $redirection = 'index.php?option=com_virtuemart&view=userfields&task=edit&cid[]='.$id;
		else $redirection = 'index.php?option=com_virtuemart&view=userfields';

		$this->setRedirect($redirection, $msg);

	}

	/**
	 * Handle the remove task
	 */
	function remove()
	{
		$model = $this->getModel('userfields');
		if (!$model->remove()) {
			$msg = JText::_('COM_VIRTUEMART_ERROR_USERFIELDS_COULD_NOT_BE_DELETED');
		} else {
			$msg = JText::_('COM_VIRTUEMART_USERFIELD_S_DELETED');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=userfields', $msg);
	}

	/**
	 * Move an item up in the grid.
	 */
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

	/**
	 * Move an item down in the grid.
	 */
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

	/**
	 * Save the given grid ordering.
	 */
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

	/**
	 * Interface to toggle(); switch the Publish toggle off.
	 */
	function unpublish()
	{
		self::toggle('published', 0);
	}

	/**
	 * Interface to toggle(); switch the Publish toggle on.
	 */
	function publish()
	{
		self::toggle('published', 1);
	}

	/**
	 * Interface to toggle(); switch the Required toggle off.
	 */
	function disable_required()
	{
		self::toggle('required', 0);
	}

	/**
	 * Interface to toggle(); switch the Required toggle on.
	 */
	function enable_required()
	{
		self::toggle('required', 1);
	}

	/**
	 * Interface to toggle(); switch the Registration toggle off.
	 */
	function disable_registration()
	{
		self::toggle('registration', 0);
	}

	/**
	 * Interface to toggle(); switch the Registration toggle on.
	 */
	function enable_registration()
	{
		self::toggle('registration', 1);
	}

	/**
	 * Interface to toggle(); switch the Shipping toggle off.
	 */
	function disable_shipping()
	{
		self::toggle('shipping', 0);
	}

	/**
	 * Interface to toggle(); switch the Shipping toggle on.
	 */
	function enable_shipping()
	{
		self::toggle('shipping', 1);
	}

	/**
	 * Interface to toggle(); switch the Account toggle off.
	 */
	function disable_account()
	{
		self::toggle('account', 0);
	}

	/**
	 * Interface to toggle(); switch the Account toggle on.
	 */
	function enable_account()
	{
		self::toggle('account', 1);
	}

	/**
	 * Interface to toggle(); switch the Readonly toggle off.
	 */
	function disable_readonly()
	{
		self::toggle('readonly', 0);
	}

	/**
	 * Interface to toggle(); switch the Readonly toggle on.
	 */
	function enable_readonly()
	{
		self::toggle('readonly', 1);
	}

	/**
	 * Switch the given toggle on or off.
	 *
	 * @param $field string Toggle set switch
	 * @param $value boolean on or off
	 */
	function toggle($field, $value)
	{
		$id = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($id);

		if (count( $id ) < 1) {
			JError::raiseError(500, JText::_('COM_VIRTUEMART_SELECT_ITEM_TO_UNPUBLISH') );
		}

		$model = $this->getModel('userfields');
		if(!$model->toggle($field, $id, $value)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}
		$this->setRedirect( 'index.php?option=com_virtuemart&view=userfields' );
	}
}

//No Closing tag
