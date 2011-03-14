<?php
/**
*
* Config controller
*
* @package	VirtueMart
* @subpackage Config
* @author RickG
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
 * Configuration Controller
 *
 * @package    VirtueMart
 * @subpackage Config
 * @author RickG
 */
class VirtuemartControllerConfig extends JController {

	/**
	 * Method to display the view
	 *
	 * @access	public
	 * @author
	 */
	function __construct() {
		parent::__construct();

		$document = JFactory::getDocument();
		$viewType = $document->getType();
		$view = $this->getView('config', $viewType);

		// Push a model into the view
		$model = $this->getModel('config');
		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}
		$model = $this->getModel('user');
		if (!JError::isError($model)) {
			$view->setModel($model, false);
		}
	}

	/**
	 * Display the config view
	 *
	 * @author RickG
	 */
	function display() {
		parent::display();
	}


	/**
	 * Handle the edit task
	 *
     * @author RickG
	 */
	function edit()
	{
		JRequest::setVar('controller', 'config');
		JRequest::setVar('view', 'config');
		JRequest::setVar('layout', 'edit');
		JRequest::setVar('hidemenu', 1);

		parent::display();
	}


	/**
	 * Handle the cancel task
	 *
	 * @author RickG
	 */
	function cancel()
	{
		$this->setRedirect('index.php?option=com_virtuemart', $msg);
	}


	/**
	 * Handle the save task
	 *
	 * @author RickG
	 */
	function save()
	{
		$model = $this->getModel('config');
		$data = JRequest::get('post');

		if ($model->store($data)) {
			$msg = JText::_('VM_CONFIG_SAVED');
			// Load the newly saved values into the session.
			VmConfig::loadConfig();
		}
		else {
			$msg = $model->getError();
		}

		$this->setRedirect('index.php?option=com_virtuemart', $msg);
	}


	/**
	 * Handle the apply task
	 *
	 * @author RickG
	 */
	function apply()
	{
		$model = $this->getModel('config');
		$data = JRequest::get('post');

		if ($model->store($data)) {
			$msg = JText::_('VM_CONFIG_SAVED');
			// Load the newly saved values into the session.
			VmConfig::loadConfig();
		}
		else {
			$msg = JText::_($model->getError());
		}

		$this->setRedirect('index.php?option=com_virtuemart&view=config', $msg);
	}


	/**
	 * Handle the remove task
	 *
	 * @author RickG
	 */
	function remove()
	{
		$model = $this->getModel('config');
		if (!$model->delete()) {
			$msg = JText::_('VM_ERROR__CONFIGS_COULD_NOT_BE_DELETED');
		}
		else {
			$msg = JText::_('VM_CONFIGS_DELETED');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=config', $msg);
	}
}

//pure php no tag
