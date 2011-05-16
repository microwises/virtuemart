<?php
/**
*
* Manufacturer controller
*
* @package	VirtueMart
* @subpackage Manufacturer
* @author Patrick Kohl
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
 * Manufacturer Controller
 *
 * @package    VirtueMart
 * @subpackage Manufacturer
 * @author
 *
 */
class VirtuemartControllerManufacturer extends VmController {

	/**
	 * Method to display the view
	 *
	 * @access	public
	 * @author
	 */
	function __construct() {
		parent::__construct();

		$this->setMainLangKey('MANUFACTURER');
		// Register Extra tasks
		$this->registerTask( 'add',  'edit' );
		$this->registerTask( 'apply',  'save' );

		$document = JFactory::getDocument();
		$viewType	= $document->getType();
		$view = $this->getView('manufacturer', $viewType);

		// Push a model into the view
		$model = $this->getModel('manufacturer');
		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}
		$model1 = $this->getModel('manufacturercategories');
		if (!JError::isError($model1)) {
			$view->setModel($model1, false);
		}
	}

	/**
	 * Display the manufacturer view
	 *
	 */
	function display() {
		parent::display();
	}


	/**
	 * Handle the edit task
	 *
	 */
	function edit()
	{
		JRequest::setVar('controller', 'manufacturer');
		JRequest::setVar('view', 'manufacturer');
		JRequest::setVar('layout', 'edit');
		JRequest::setVar('hidemenu', 1);

		parent::display();
	}


	/**
	 * Handle the cnacel task
	 *
	 */
	function cancel()
	{
		$this->setRedirect('index.php?option=com_virtuemart&view=manufacturer');
	}


	/**
	 * Handle the save task
	 *
	 */
	function save()
	{

		/* Load the view object */
		$view = $this->getView('manufacturer', 'html');

		$model = $this->getModel('manufacturer');

		if ($id = $model->store()) {
			$msg = JText::_('COM_VIRTUEMART_MANUFACTURER_SAVED');
		} else {
			$msg = $model->getError();
		}

		$cmd = JRequest::getCmd('task');
		if($cmd == 'apply') $redirection = 'index.php?option=com_virtuemart&view=manufacturer&task=edit&virtuemart_manufacturer_id='.$id;
		else $redirection = 'index.php?option=com_virtuemart&view=manufacturer';

		$this->setRedirect($redirection, $msg);
	}


	/**
	 * Handle the remove task
	 *
	 */
	function remove()
	{
		$model = $this->getModel('manufacturer');
		if (!$model->delete()) {
			$msg = JText::_('COM_VIRTUEMART_MANUFACTURER_DELETE_ERROR');
		}
		else {
			$msg = JText::_('COM_VIRTUEMART_MANUFACTURER_DELETE_SUCCESS');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=manufacturer', $msg);
	}


	/**
	 * Handle the publish task
	 *
	 */
	function publish()
	{
		$model = $this->getModel('manufacturer');
		if (!$model->publish(true)) {
			$msg = JText::_('COM_VIRTUEMART_MANUFACTURER_PUBLISH_ERROR');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=manufacturer', $msg);
	}


	/**
	 * Handle the publish task
	 *
	 */
	function unpublish()
	{
		$model = $this->getModel('manufacturer');
		if (!$model->publish(false)) {
			$msg = JText::_('COM_VIRTUEMART_MANUFACTURER_UNPUBLISH_ERROR');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=manufacturer', $msg);
	}
}
// pure php no closing tag
