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

* @version $Id: extensions.php 2227 2010-01-20 23:03:48Z SimonHodgkiss $

*/



// Check to ensure this file is included in Joomla!

defined('_JEXEC') or die('Restricted access');



// Load the controller framework

jimport('joomla.application.component.controller');



/**

 * Extensions Controller

 *

 * @package    VirtueMart

 * @subpackage Extensions

 * @author StephanieS

 */

class VirtuemartControllerUsergroups extends JController {



	/**

	 * Method to display the view

	 *

	 * @access	public

	 * @author

	 */

	function __construct() {

		parent::__construct();

		// Register Extra tasks
		$this->registerTask( 'add',  'edit', 'delete' );

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
			$msg = JText::_('Usergroup saved!');
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
			$msg = JText::_('Error: One or more usergroups could not be deleted!');
		} else {
			$msg = JText::_( 'Usergroup Deleted!');
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
			$msg = JText::_('Error: One or more extensions could not be published!');
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
			$msg = JText::_('Error: One or more extensions could not be unpublished!');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=usergroups', $msg);

	}

}

?>

