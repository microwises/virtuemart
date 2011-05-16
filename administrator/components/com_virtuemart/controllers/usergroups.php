<?php

/**

*

* Extensions controller

*

* @package	VirtueMart

* @subpackage Extensions

* @author StephanieS

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

 * Extensions Controller

 *

 * @package    VirtueMart

 * @subpackage Extensions

 * @author StephanieS

 */

class VirtuemartControllerUsergroups extends VmController {



	/**
	 * Method to display the view
	 *
	 * @access	public
	 * @author
	 */

	function __construct() {

		parent::__construct();

//		$this->setMainLangKey('USERGROUP');
		// Register Extra tasks
		$this->registerTask( 'add',  'edit', 'delete' );
		$this->registerTask( 'apply',  'save' );

		$document =& JFactory::getDocument();
		$viewType	= $document->getType();

		$view =& $this->getView('usergroups', $viewType);


		// Push a model into the view
		$model =& $this->getModel('usergroups');

		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}

	}


	/**

	 * Display the extensions view

	 *

	 * @author StephanieS

	 */

	function display() {

		parent::display();

	}


	/**

	 * Handle the edit task

	 *

     * @author StephanieS

	 */

	function edit(){
		JRequest::setVar('controller', 'usergroups');
		JRequest::setVar('view', 'usergroups');
		JRequest::setVar('layout', 'edit');
		JRequest::setVar('hidemenu', 1);

		parent::display();
	}

	/**

	 * Handle the cancel task

	 *

	 * @author StephanieS

	 */

	function cancel(){

		$this->setRedirect('index.php?option=com_virtuemart&view=usergroups');

	}





	/**

	 * Handle the save task

	 *

	 * @author StephanieS

	 */

	function save(){

		$model =& $this->getModel('usergroups');

		if ($model->store()) {
			$msg = JText::_('COM_VIRTUEMART_USERGROUP_SAVED');
		} else {
			$msg = JText::_($model->getError());
		}

		$this->setRedirect('index.php?option=com_virtuemart&view=usergroups', $msg);

	}





	/**

	 * Handle the remove task

	 *

	 * @author StephanieS

	 */

	function remove() {

		$model = $this->getModel('usergroups');

		if (!$model->delete()) {
			$msg = JText::_('COM_VIRTUEMART_ERROR_USERGROUPS_COULD_NOT_BE_DELETED');
		} else {
			$msg = JText::_('COM_VIRTUEMART_USERGROUP_DELETED');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=usergroups', $msg);

	}





	/**

	 * Handle the publish task

	 *

	 * @author StephanieS

	 */

	function publish() {

		$model = $this->getModel('usergroups');

		if (!$model->publish(true)) {
			$msg = JText::_('COM_VIRTUEMART_ERROR_USERGROUPS_COULD_NOT_BE_PUBLISHED');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=usergroups', $msg);

	}





	/**

	 * Handle the publish task

	 *

	 * @author StephanieS

	 */

	function unpublish(){

		$model = $this->getModel('usergroups');

		if (!$model->publish(false)) {
			$msg = JText::_('COM_VIRTUEMART_ERROR_USERGROUPS_COULD_NOT_BE_UNPUBLISHED');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=usergroups', $msg);

	}

}

// pure php no closing tag

