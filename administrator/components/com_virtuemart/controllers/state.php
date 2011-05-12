<?php
/**
*
* State controller
*
* @package	VirtueMart
* @subpackage State
* @author RickG, Max Milbers
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
 * Product Controller
 *
 * @package    VirtueMart
 * @subpackage State
 * @author RickG, Max Milbers
 */
class VirtuemartControllerState extends JController {

	/**
	 * Method to display the view
	 *
	 * @access	public
	 * @author RickG, Max Milbers
	 */
	function __construct() {
		parent::__construct();

		JRequest::setVar('view', 'state');
		// Register Extra tasks
		$this->registerTask( 'add',  'edit' );
	    $this->registerTask( 'apply',  'save' );

	}

	/**
	 * Display the state view
	 *
	 * @author Max Milbers
	 */
	function display() {

		$document = JFactory::getDocument();
		$viewType	= $document->getType();
		$view = $this->getView('state', $viewType);

		// Push a model into the view
		$model = $this->getModel('state');
		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}
		$model1 = $this->getModel('ShippingZone');
		if (!JError::isError($model1)) {
			$view->setModel($model1, false);
		}

		$model = $this->getModel('country');
		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}
		parent::display();
	}


	/**
	 * Handle the edit task
	 *
     * @author RickG, Max Milbers
	 */
	function edit(){
		JRequest::setVar('controller', 'state');

		JRequest::setVar('layout', 'edit');
		JRequest::setVar('hidemenu', 1);

		self::display();
	}


	/**
	 * Handle the cancel task
	 *
	 * @author RickG, Max Milbers
	 */
	function cancel()
	{
		$data = JRequest::get( 'post' );
		$this->setRedirect('index.php?option=com_virtuemart&view=state&virtuemart_country_id='.$data["virtuemart_country_id"]);
	}


	/**
	 * Handle the save task
	 *
	 * @author RickG, Max Milbers
	 */
	function save()
	{
		$data = JRequest::get( 'post' );
		$model =& $this->getModel( 'state' );

		if ($id = $model->store()) {
			$msg = JText::_('COM_VIRTUEMART_STATE_SAVED');
		} else {
			$msg = $model->getError();
		}

		$cmd = JRequest::getCmd('task');
		if($cmd == 'apply') $redirection = 'index.php?option=com_virtuemart&view=state&task=edit&virtuemart_state_id='.$id;
		else $redirection = 'index.php?option=com_virtuemart&view=state&virtuemart_country_id='.$data['virtuemart_country_id'];

		$this->setRedirect($redirection, $msg);

	}


	/**
	 * Handle the remove task
	 *
	 * @author RickG, Max Milbers
	 */
	function remove()
	{
		$data = JRequest::get( 'post' );
		$model = $this->getModel('state');
		if (!$model->delete()) {
			$msg = JText::_('COM_VIRTUEMART_ERROR_STATES_COULD_NOT_BE_DELETED');
		}
		else {
			$msg = JText::_('COM_VIRTUEMART_STATES_DELETED');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=state&virtuemart_country_id='.$data["virtuemart_country_id"], $msg);
	}


	/**
	 * Handle the publish task
	 *
	 * @author RickG, Max Milbers
	 */
	function publish()
	{
		$data = JRequest::get( 'post' );
		$model = $this->getModel('state');
		if (!$model->publish(true)) {
			$msg = JText::_('COM_VIRTUEMART_ERROR_STATES_COULD_NOT_BE_PUBLISHED');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=state&virtuemart_country_id='.$data["virtuemart_country_id"], $msg);
	}


	/**
	 * Handle the publish task
	 *
	 * @author RickG, Max Milbers
	 */
	function unpublish()
	{
		$data = JRequest::get( 'post' );
		$model = $this->getModel('state');
		if (!$model->publish(false)) {
			$msg = JText::_('COM_VIRTUEMART_ERROR_STATES_COULD_NOT_BE_UNPUBLISHED');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=state&virtuemart_country_id='.$data["virtuemart_country_id"], $msg);
	}


	/**
	 * Retrieve full statelist
	 */
	function getList() {

		/* Create the view object. */
		$view = $this->getView('state', 'json');

		/* Standard model */
		$view->setModel( $this->getModel( 'state', 'VirtueMartModel' ), true );

		/* Now display the view. */
		$view->display(null);
	}
}

