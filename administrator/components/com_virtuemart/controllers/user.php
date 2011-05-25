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

if(!class_exists('VmController'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmcontroller.php');


/**
 * Controller class for the user
 *
 * @package    	VirtueMart
 * @subpackage 	User
 * @author     	Oscar van Eijk
 * @author 		Max Milbers
 */
class VirtuemartControllerUser extends VmController {

	/**
	 * Method to display the view
	 *
	 * @access public
	 * @author
	 */
	function __construct(){
		parent::__construct();

	}

	function User(){

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
		parent::display();
	}


	/**
	 * Handle the edit task
	 */
	function edit(){

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

		parent::edit();
	}


	function editshop(){

		$user = JFactory::getUser();
		//the cid var gets overriden in the edit function, when not set. So we must set it here
		JRequest::setVar('cid', (int)$user->id);
		$this->edit();

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
			$model = $this->getModel('user');

			$data = JRequest::get('post');

			// Store multiple selectlist entries as a ; separated string
			if (key_exists('vendor_accepted_currencies', $data) && is_array($data['vendor_accepted_currencies'])) {
			    $data['vendor_accepted_currencies'] = implode(',', $data['vendor_accepted_currencies']);
			}

			$data['vendor_store_name'] = JRequest::getVar('vendor_store_name','','post','STRING',JREQUEST_ALLOWHTML);
			$data['vendor_store_desc'] = JRequest::getVar('vendor_store_desc','','post','STRING',JREQUEST_ALLOWHTML);
			$data['vendor_terms_of_service'] = JRequest::getVar('vendor_terms_of_service','','post','STRING',JREQUEST_ALLOWHTML);


			if ($ret=$model->store($data)) {
				$msg = JText::_('COM_VIRTUEMART_USER_SAVED');
			} else {
				$msg = JText::_($model->getError());
			}
		}
		$cmd = JRequest::getCmd('task');
		if($cmd == 'apply'){
			$redirection = 'index.php?option=com_virtuemart&view=user&task=edit&cid[]='.$ret['newId'];
		}
		else{
			if (!$_currentUser->authorize('com_users', 'manage')) {
				$redirection = 'index.php?option=com_virtuemart&view=user&task=editshop';
			} else {
				$redirection = 'index.php?option=com_virtuemart&view=user';
			}

		}
		$this->setRedirect($redirection, $msg);
	}


	// /**
	 // * Interface to toggle(); switch the IsVendor toggle off.
	 // */
	// function disable_vendor()
	// {
		// TODO is only for single store, take a look on that for multivendor
		// Why was this ?? Re-published again (Oscar)
		// $this->setRedirect( 'index.php?option=com_virtuemart&view=user','Disabled for beta' );
		// self::toggle('user_is_vendor', 0);
	// }

	// /**
	 // * Interface to toggle(); switch the IsVendor toggle on.
	 // */
	// function enable_vendor()
	// {
		// TODO is only for single store, take a look on that for multivendor
		// Why was this ?? Re-published again (Oscar)
		// $this->setRedirect( 'index.php?option=com_virtuemart&view=user','Disabled for beta' );
		// self::toggle('user_is_vendor', 1);
	// }

	// /**
	 // * Switch the given toggle on or off.
	 // *
	 // * @param $field string Toggle set switch
	 // * @param $value boolean on or off
	 // */
	// function toggle($field, $value)
	// {
		// $id = JRequest::getVar( 'cid', array(), 'post', 'array' );
		// JArrayHelper::toInteger($id);
		// if (count( $id ) < 1) {
			// JError::raiseWarning(500, JText::_('COM_VIRTUEMART_SELECT_USER_TO_MODIFY') );
		// }

		// $model = $this->getModel('user');
		// if(!$model->toggle($field, $id, $value)) {
			// echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		// }
		// $this->setRedirect( 'index.php?option=com_virtuemart&view=user' );
	// }
}

//No Closing tag
