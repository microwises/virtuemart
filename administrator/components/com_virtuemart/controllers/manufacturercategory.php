<?php
/**
*
* Manufacturer category controller
*
* @package	VirtueMart
* @subpackage Manufacturer Category
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

/**
 * Manufacturer category controller
 *
 * @package    VirtueMart
 * @subpackage Manufacturer
 * @author
 */
class VirtuemartControllerManufacturerCategory extends JController {

	/**
	 * Method to display the view
	 *
	 * @access	public
	 * @author
	 */
	function __construct() {
		parent::__construct();

		// Register Extra tasks
		$this->registerTask( 'add',  'edit' );
		$this->registerTask( 'apply',  'save' );

		$document =& JFactory::getDocument();
		$viewType	= $document->getType();
		$view =& $this->getView('manufacturerCategory', $viewType);

		// Push a model into the view
		$model =& $this->getModel('manufacturerCategory');
		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}

	}

	/**
	 * Display the country view
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
		JRequest::setVar('controller', 'manufacturerCategory');
		JRequest::setVar('view', 'manufacturerCategory');
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
		$this->setRedirect('index.php?option=com_virtuemart&view=manufacturercategory',JText::_('CANCELLED'));
	}


	/**
	 * Handle the save task
	 *
	 */
	function save()
	{
		$model =& $this->getModel('manufacturerCategory');

		if ($id = $model->store()) {
			$msg = JText::_('VM_MANUFACTURER_CATEGORY_SAVED');
		} else {
			$model->getError();
		}

		$cmd = JRequest::getCmd('task');
		if($cmd == 'apply') $redirection = 'index.php?option=com_virtuemart&view=manufacturercategory&task=edit&cid[]='.$id;
		else $redirection = 'index.php?option=com_virtuemart&view=manufacturercategory';

		$this->setRedirect($redirection, $msg);

	}


	/**
	 * Handle the remove task
	 *
	 */
	function remove()
	{
		$model = $this->getModel('manufacturerCategory');
		if (!$model->delete()) {
			$msg = JText::_('VM_MANUFACTURER_CATEGORY_DELETE_WARNING');
		}
		else {
			$msg = JText::_( 'VM_MANUFACTURER_DELETE_SUCCESS');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=manufacturercategory', $msg);
	}


	/**
	 * Handle the publish task
	 *
	 */
	function publish()
	{
		$model = $this->getModel('manufacturerCategory');
		if (!$model->publish(true)) {
			$msg = JText::_('VM_MANUFACTURER_CATEGORY_PUBLISH_ERROR');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=manufacturercategory', $msg);
	}


	/**
	 * Handle the publish task
	 *
	 */
	function unpublish()
	{
		$model = $this->getModel('manufacturerCategory');
		if (!$model->publish(false)) {
			$msg = JText::_('VM_MANUFACTURER_CATEGORY_UNPUBLISH_ERROR');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=manufacturercategory', $msg);
	}
}
// pure php no closing tag
