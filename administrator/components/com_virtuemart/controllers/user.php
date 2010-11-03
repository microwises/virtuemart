<?php
/**
*
* User controller
*
* @package	VirtueMart
* @subpackage User
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

/**
 * Controller class for the user
 *
 * @package    	VirtueMart
 * @subpackage 	User
 * @author     	Oscar van Eijk
 * @author 		Max Milbers
 */
class VirtuemartControllerUser extends JController {

	/**
	 * Method to display the view
	 *
	 * @access public
	 * @author
	 */
	function __construct()
	{
		parent::__construct();

		// Register Extra tasks
		$this->registerTask('add', 'edit');

		$document =& JFactory::getDocument();
		$viewType = $document->getType();
		$view = $this->getView('user', $viewType);
		
		// Push a model into the view
		$model = $this->getModel('user');

		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}else{
			echo 'Couldnt load backend model in VirtuemartControllerUser';	
		}
	}

	/**
	 * Display the userlist view
	 */
	function display(){
		parent::display();
	}

	/**
	 * Handle the edit task
	 */
	function edit(){
		JRequest::setVar('controller', 'user');
		JRequest::setVar('view', 'user');
		JRequest::setVar('layout', 'edit');
		JRequest::setVar('hidemainmenu', 1);
		
		//We set here the cid, when no cid is set to 0, for adding a new user
		//In every other case the cid is sent.
		$cid = JRequest::getVar('cid');
		if(!isset($cid)) JRequest::setVar('cid', (int)0);
		
		$document =& JFactory::getDocument();
		$viewType = $document->getType();
		$view =& $this->getView('user', $viewType);
		
		// Load the additional models
		$view->setModel( $this->getModel( 'vendor', 'VirtueMartModel' ));
		$view->setModel( $this->getModel( 'shoppergroup', 'VirtueMartModel' ));
		$view->setModel( $this->getModel( 'userfields', 'VirtueMartModel' ));
		$view->setModel( $this->getModel( 'orders', 'VirtueMartModel' ));
		$view->setModel( $this->getModel( 'currency', 'VirtueMartModel' ));

		parent::display();
	}

	
	function editshop(){

		$user = JFactory::getUser();
//		$model = $this->getModel();
//		$model->setCurrent();
		//the cid var gets overriden in the edit function, when not set. So we must set it here
		JRequest::setVar('cid', (int)$user->id);
		$this->edit();

	}	
	
	/**
	 * Handle the cancel task
	 */
	function cancel(){
		$this->setRedirect('index.php?option=com_virtuemart&view=user');
	}

	/**
	 * Handle the save task
	 */
	function save(){
		$document =& JFactory::getDocument();
		$viewType = $document->getType();
		$view =& $this->getView('user', $viewType);
		$view->setModel( $this->getModel( 'userfields', 'VirtueMartModel' ));

		$_currentUser =& JFactory::getUser();
// TODO sortout which check is correctt.....
//		if (!$_currentUser->authorize('administration', 'manage', 'components', 'com_users')) {
		if (!$_currentUser->authorize('com_users', 'manage')) {
			$msg = JText::_(_NOT_AUTH);
		} else {
			$model =& $this->getModel('user');
			if ($ret=$model->store()) {
				$msg = JText::_('User saved!');
			} else {
				$msg = JText::_($model->getError());
			}
		}
		$cmd = JRequest::getCmd('task');
		if($cmd == 'apply'){
			$redirection = 'index.php?option=com_virtuemart&view=user&task=edit&cid[]='.$ret['newId'];
		}
		else{
			$redirection = 'index.php?option=com_virtuemart&view=user';
		}
		$this->setRedirect($redirection, $msg);
	}

	/**
	 * Handle the remove task
	 */
	function remove()
	{
		$model = $this->getModel('user');
		if (!$model->delete()) {
			$msg = JText::_($model->getError());
		} else {
			$msg = JText::_( 'User(s) Deleted!');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=user', $msg);
	}

	/**
	 * Interface to toggle(); switch the IsVendor toggle off.
	 */
	function disable_vendor()
	{
		self::toggle('user_is_vendor', 0);
	}

	/**
	 * Interface to toggle(); switch the IsVendor toggle on.
	 */
	function enable_vendor()
	{
		self::toggle('user_is_vendor', 1);
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
			JError::raiseWarning(500, JText::_( 'Select a user to modify' ) );
		}

		$model = $this->getModel('user');
		if(!$model->toggle($field, $id, $value)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}
		$this->setRedirect( 'index.php?option=com_virtuemart&view=user' );
	}
}

//No Closing tag
