<?php
/**
*
* Controller for the front end User maintenance
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
 * VirtueMart Component Controller
 *
 * @package		VirtueMart
 */
class VirtueMartControllerUser extends JController
{

	public function __construct()
	{
		parent::__construct();
	}

	function editaddress(){
		
		$view = $this->getView('user', 'html');
		
		$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR .DS.'models' );
		$view->setModel( $this->getModel( 'user', 'VirtuemartModel' ), true );
		$view->setModel( $this->getModel( 'userfields', 'VirtuemartModel' ), true );
		$view->setLayout('edit_address');

		/* Display it all */
		$view->display();
		
	}

	public function User(){
		
		$view = $this->getView('user', 'html');
		
		/* Add the default model */
		$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR .DS.'models' );
		$view->setModel( $this->getModel( 'user', 'VirtuemartModel' ), true );
		$view->setModel( $this->getModel( 'userfields', 'VirtuemartModel' ), true );
		$view->setModel( $this->getModel( 'store', 'VirtuemartModel' ), true );
		$view->setModel( $this->getModel( 'currency', 'VirtuemartModel' ), true );
		$view->setModel( $this->getModel( 'orders', 'VirtuemartModel' ), true );

		/* Set the layout */
		$view->setLayout('edit');
		
		/* Display it all */
		$view->display();
	}

	/**
	 * Save the user info. This is a copy of (modifued) the save() function from Joomla
	 * user-controller. It cannot be called since that function ends with as redirect and
	 * after that we need to save the VirtueMart specific data.
	 * 
	 */
	function save()
	{
		// For new user gistrations, call register() first
		$_new = JRequest::getVar( 'register_new', 0, 'post', 'int' );
		if ($_new) {
			if (($user =& self::register()) === false) {
				$this->setRedirect( JURI::base() );
				return;
			}
		} else {
			// Check for request forgeries
			JRequest::checkToken() or jexit( 'Invalid Token' );
			$user	 =& JFactory::getUser();
		}

		$return = JURI::base();

		$userid = ($_new ? $user->get('id') : JRequest::getVar( 'my_user_id', 0, 'post', 'int' ));
		// preform security checks
//		if ($user->get('id') == 0 || $userid == 0 ||
		if ($userid <> $user->get('id') && !Permissions::getInstance()->check("admin,storeadmin")) {
			JError::raiseError( 403, JText::_('Access Forbidden') );
			return;
		}

		// store data
		$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR .DS.'models' );
		$model = $this->getModel('user');

		// The view is loaded just for the setModel() method. Anyone a better suggestion?
		$view = $this->getView('user', 'html');
		$view->setModel( $this->getModel( 'userfields', 'VirtuemartModel' ), true );
		$view->setModel( $this->getModel( 'store', 'VirtuemartModel' ), true );
		$view->setModel( $this->getModel( 'currency', 'VirtuemartModel' ), true );
		$view->setModel( $this->getModel( 'orders', 'VirtuemartModel' ), true );

		if ($model->store()) {
			$msg	= JText::_( 'Your settings have been saved.' );
		} else {
			//$msg	= JText::_( 'Error saving your settings.' );
			$msg	= $model->getError();
		}

		$cart = cart::getCart();
		if (($cart && $cart['inCheckOut']) || ($_rview = JRequest::getVar('rview', '')) != ''){
			$return = 'index.php?option=com_virtuemart&view='.$_rview.'&'
				. ($cart['inCheckOut'] ? 'task=checkout' : '');
		}

		$this->setRedirect( $return, $msg );
	}

	
	/**
	 * Register a new user. This is a (modified) copy of the register_save() function from Joomla
	 * user-controller. It cannot be called since that function ends with as redirect and after
	 * that we need to save the VirtueMart specific data.
	 * 
	 * @return object User object
	 * @access private
	 */
	private function register()
	{
		global $mainframe;

		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		// Get required system objects
		$user 		= clone(JFactory::getUser());
		$pathway 	=& $mainframe->getPathway();
		$config		=& JFactory::getConfig();
		$authorize	=& JFactory::getACL();
		$document   =& JFactory::getDocument();

		// If user registration is not allowed, show 403 not authorized.
		$usersConfig = &JComponentHelper::getParams( 'com_users' );
		if ($usersConfig->get('allowUserRegistration') == '0') {
			JError::raiseError( 403, JText::_( 'Access Forbidden' ));
			return;
		}

		// Initialize new usertype setting
		$newUsertype = $usersConfig->get( 'new_usertype' );
		if (!$newUsertype) {
			$newUsertype = 'Registered';
		}

		// Bind the post array to the user object
		if (!$user->bind( JRequest::get('post'), 'usertype' )) {
			JError::raiseError( 500, $user->getError());
		}

		// Set some initial user values
		$user->set('id', 0);
		$user->set('usertype', $newUsertype);
		$user->set('gid', $authorize->get_group_id( '', $newUsertype, 'ARO' ));

		$date =& JFactory::getDate();
		$user->set('registerDate', $date->toMySQL());

		// If user activation is turned on, we need to set the activation information
		$useractivation = $usersConfig->get( 'useractivation' );
		if ($useractivation == '1')
		{
			jimport('joomla.user.helper');
			$user->set('activation', JUtility::getHash( JUserHelper::genRandomPassword()) );
			$user->set('block', '1');
		}

		// If there was an error with registration, set the message and display form
		if ( !$user->save() )
		{
			JError::raiseWarning('', JText::_( $user->getError()));
			return false;
		}

		// Send registration confirmation mail
		$password = JRequest::getString('password', '', 'post', JREQUEST_ALLOWRAW);
		$password = preg_replace('/[\x00-\x1F\x7F]/', '', $password); //Disallow control chars in the email

		// Let Joomla handle the mail to make sure the correct mail is sent. Therefore, we need to call the
		// user controller
		// @TODO In Joomla v1.5.x, _sendMail() has no explicit access level so defaults to public. If this changes in a future release, implement the functionality local after all...
		require_once(JPATH_SITE.DS.'components'.DS.'com_user'.DS.'controller.php');
		UserController::_sendMail($user, $password);

		// Everything went fine, set relevant message depending upon user activation state and display message
		if ( $useractivation == 1 ) {
			$message  = JText::_( 'REG_COMPLETE_ACTIVATE' );
		} else {
			$message = JText::_( 'REG_COMPLETE' );
		}

		JRequest::setVar('user_id', $user->get('id'));
		return $user;
	}

	function cancel()
	{
		$return = JURI::base();
		$cart = cart::getCart();
		if (($cart && $cart['inCheckOut']) || ($_rview = JRequest::getVar('rview', '')) != ''){
			$return = 'index.php?option=com_virtuemart&view='.$_rview.'&'
				. ($cart['inCheckOut'] ? 'task=checkout' : '');
		}
		$this->setRedirect( $return, $msg );
	}
}
// No closing tag
