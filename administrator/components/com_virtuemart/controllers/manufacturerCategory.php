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
class VirtuemartControllerManufacturercategory extends JController {

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
		$view =& $this->getView('manufacturercategory', $viewType);

		// Push a model into the view
		$model =& $this->getModel('manufacturercategory');
		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}

	}

	/**
	 * Display the country view
	 *
	 */
	function display() {
		$document = JFactory::getDocument();
		$viewName = JRequest::getVar('view', '');
		$viewType = $document->getType();
		$view =& $this->getView($viewName, $viewType);

		// Push a model into the view
		$model =& $this->getModel( 'manufacturercategory' );
		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}
		parent::display();
	}


	/**
	 * Handle the edit task
	 *
	 */
	function edit()
	{
		JRequest::setVar('controller', 'manufacturercategory');
		JRequest::setVar('view', 'manufacturercategory');
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
		$this->setRedirect('index.php?option=com_virtuemart&view=manufacturerCategory',JText::_('COM_VIRTUEMART_CANCELLED'));
	}


	/**
	 * Handle the save task
	 *
	 */
	function save()
	{
		$model =& $this->getModel('manufacturerCategory');

		if ($id = $model->store()) {
			$msg = JText::_('COM_VIRTUEMART_MANUFACTURER_CATEGORY_SAVED');
		} else {
			$model->getError();
		}

		$cmd = JRequest::getCmd('task');
		if($cmd == 'apply') $redirection = 'index.php?option=com_virtuemart&view=manufacturerCategory&task=edit&cid[]='.$id;
		else $redirection = 'index.php?option=com_virtuemart&view=manufacturerCategory';

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
			$msg = JText::_('COM_VIRTUEMART_MANUFACTURER_CATEGORY_DELETE_WARNING');
		}
		else {
			$msg = JText::_('COM_VIRTUEMART_MANUFACTURER_DELETE_SUCCESS');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=manufacturerCategory', $msg);
	}


	/**
	 * Handle the publish task
	 *
	 */
	function publish()
	{
		$model = $this->getModel('manufacturerCategory');
		if (!$model->publish(true)) {
			$msg = JText::_('COM_VIRTUEMART_MANUFACTURER_CATEGORY_PUBLISH_ERROR');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=manufacturerCategory', $msg);
	}


	/**
	 * Handle the publish task
	 *
	 */
	function unpublish()
	{
		$model = $this->getModel('manufacturerCategory');
		if (!$model->publish(false)) {
			$msg = JText::_('COM_VIRTUEMART_MANUFACTURER_CATEGORY_UNPUBLISH_ERROR');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=manufacturerCategory', $msg);
	}
}
// pure php no closing tag
