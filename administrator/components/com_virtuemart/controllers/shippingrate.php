<?php
/**
*
* Shipping Rate controller
*
* @package	VirtueMart
* @subpackage ShippingRate
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
 * Shipping Carrier Controller
 *
 * @package    VirtueMart
 * @subpackage ShippingRate
 * @author RickG
 */
class VirtuemartControllerShippingRate extends JController {

	/**
	 * Method to display the view
	 *
	 * @access	public
	 */
	function __construct() {
		parent::__construct();

		// Register Extra tasks
		$this->registerTask( 'add',  'edit' );
		$this->registerTask('apply','save');

		$document =& JFactory::getDocument();
		$viewType	= $document->getType();
		$view =& $this->getView('shippingrate', $viewType);

		// Push a model into the view
		$model =& $this->getModel('shippingrate');
		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}
		$model1 =& $this->getModel('country');
		if (!JError::isError($model1)) {
			$view->setModel($model1, false);
		}
		$model2 =& $this->getModel('shippingcarrier');
		if (!JError::isError($model2)) {
			$view->setModel($model2, false);
		}
		$model3 =& $this->getModel('currency');
		if (!JError::isError($model3)) {
			$view->setModel($model3, false);
		}
//		$model =& $this->getModel('taxrate');
//		if (!JError::isError($model)) {
//			$view->setModel($model, false);
//		}
	}

	/**
	 * Display the shipping rate view
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
		JRequest::setVar('controller', 'shippingrate');
		JRequest::setVar('view', 'shippingrate');
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
		$this->setRedirect('index.php?option=com_virtuemart&view=shippingrate');
	}


	/**
	 * Handle the save task
	 *
	 * @author RickG
	 */
	function save()
	{
		$model =& $this->getModel('shippingrate');

		if (($id = $model->store()) === false) {
			$msg = JText::_($model->getError());
		} else {
			$msg = JText::_('VM_SHIPPING_RATE_SAVED');
		}

		$cmd = JRequest::getCmd('task');
		if($cmd == 'apply'){
			$redirection = 'index.php?option=com_virtuemart&view=shippingrate&task=edit&cid[]='.$id;
		} else {
			$redirection = 'index.php?option=com_virtuemart&view=shippingrate';
		}

		$this->setRedirect($redirection, $msg);
	}


	/**
	 * Handle the remove task
	 *
	 * @author RickG
	 */
	function remove()
	{
		$model = $this->getModel('shippingrate');
		if (!$model->delete()) {
			$msg = JText::_('VM_ERROR_SHIPPING_RATES_COULD_NOT_BE_DELETED');
		}
		else {
			$msg = JText::_('VM_SHIPPING_RATES_DELETED');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=shippingrate', $msg);
	}
}
// pure php no closing tag
