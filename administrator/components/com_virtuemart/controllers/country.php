<?php
/**
*
* Country controller
*
* @package	VirtueMart
* @subpackage Country
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
 * Country Controller
 *
 * @package    VirtueMart
 * @subpackage Country
 * @author RickG
 */
class VirtuemartControllerCountry extends JController {

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

		$document = JFactory::getDocument();
		$viewType	= $document->getType();
		$view = $this->getView('country', $viewType);

		// Push a model into the view
		$model = $this->getModel('country');
		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}
		$model1 = $this->getModel('ShippingZone');
		if (!JError::isError($model1)) {
			$view->setModel($model1, false);
		}
	}

	/**
	 * Display the country view
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
		JRequest::setVar('controller', 'country');
		JRequest::setVar('view', 'country');
		JRequest::setVar('layout', 'edit');
		JRequest::setVar('hidemenu', 1);

		parent::display();
	}


	/**
	 * Handle the cnacel task
	 *
	 * @author RickG
	 */
	function cancel()
	{
		$this->setRedirect('index.php?option=com_virtuemart&view=country');
	}


	/**
	 * Handle the save task
	 *
	 * @author RickG
	 */
	function save()
	{
		$model = $this->getModel('country');

		if ($id = $model->store()) {
			$msg = JText::_('Country saved!');
		} else {
			$msg = $model->getError();
		}

		$cmd = JRequest::getCmd('task');
		if($cmd == 'apply') $redirection = 'index.php?option=com_virtuemart&view=country&task=edit&cid[]='.$id;
		else $redirection = 'index.php?option=com_virtuemart&view=country';

		$this->setRedirect($redirection, $msg);
	}

	/**
	 * Handle the publish task
	 *
	 * @author RickG
	 */
	function publish()
	{
		$model = $this->getModel('country');
		if (!$model->publish(true)) {
			$msg = JText::_('Error: One or more countries could not be published!');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=country', $msg);
	}


	/**
	 * Handle the publish task
	 *
	 * @author RickG
	 */
	function unpublish()
	{
		$model = $this->getModel('country');
		if (!$model->publish(false)) {
			$msg = JText::_('Error: One or more countries could not be unpublished!');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=country', $msg);
	}
}

//pure php no tag
