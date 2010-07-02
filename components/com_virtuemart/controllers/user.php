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

	/**
	 * The general user/account maintance view for editing userdata.
	 * It redirects automatically to the task register for anonymous users.
	 * 
	 */
	public function User(){
		
		//We just setup a new task for non registered users
		$user =& JFactory::getUser();
		if(!$user->id){
			$this->setRedirect( 'index.php?option=com_virtuemart&view=user&task=register');
		}else{
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
			
			$ftask ='saveuser';
			$view->assignRef('fTask', $ftask);
			
			/* Display it all */
			$view->display();
		}

	}

	function saveUser(){
		$msg = $this->saveData();
		$this->setRedirect( 'index.php?option=com_virtuemart&view=user', $msg );
	}

	/**
	 * This is the task register, which is for anonymous user who want to register to the mart.
	 * 
	 * @author Max Milbers
	 */
	function register(){

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
		
		$ftask ='saveRegister';
		$view->assignRef('fTask', $ftask);
		
		
		/* Display it all */
		$view->display();

	}
		
	function saveRegister(){
		
		$msg = $this->doRegister();
		$this->setRedirect( 'index.php?option=com_virtuemart&view=cart');
	}
	
	/**
	 * This is for use in the cart, it calls a standard template for editing user adresses. It sets the following task of the form
	 * in the template to saveCartUser, the task saveCartUser just sets the right redirect in save(). This is done just to have the 
	 * controll flow in the controller and not in the layout. The layout is everytime calling a standard joomla task.
	 * 
	 * @author Max Milbers
	 */
	 
	function editAddressCart(){
		
		$view = $this->getView('user', 'html');
		
		$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR .DS.'models' );
		$view->setModel( $this->getModel( 'user', 'VirtuemartModel' ), true );
		$view->setModel( $this->getModel( 'userfields', 'VirtuemartModel' ), true );
		$view->setLayout('edit_address');
		
		$ftask ='savecartuser';
		$view->assignRef('fTask', $ftask);
		/* Display it all */
		$view->display();
		
	}
	
	/**This function is called from the layout edit_adress and just sets the right redirect to the save function
	 * 
	 * @author Max Milbers
	 */
	function saveCartUser(){
		$msg = $this->saveData();
		$this->saveToCart();
		$this->setRedirect( 'index.php?option=com_virtuemart&view=cart');
	}

	/**
	 * This is for use in the checkout process, it is the same like editAddressCart, but it sets the save task
	 * to saveCheckoutUser, the task saveCheckoutUser just sets the right redirect. This is done just to have the 
	 * controll flow in the controller and not in the layout. The layout is everytime calling a standard joomla task.
	 * 
	 * @author Max Milbers
	 */
	function editAddressCheckout(){
		
		$view = $this->getView('user', 'html');
		
		$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR .DS.'models' );
		$view->setModel( $this->getModel( 'user', 'VirtuemartModel' ), true );
		$view->setModel( $this->getModel( 'userfields', 'VirtuemartModel' ), true );
		$view->setLayout('edit_address');

		$ftask ='savecheckoutuser';
		$view->assignRef('fTask', $ftask);

		/* Display it all */
		$view->display();
	}

	/**This function is called from the layout edit_adress and just sets the right redirect to the save function
	 * 
	 * @author Max Milbers
	 */	
	function saveCheckoutUser(){
		$msg = $this->saveData();
		$this->saveToCart();
		$this->setRedirect( 'index.php?option=com_virtuemart&view=cart&task=checkout', $msg );
	}
	
	private function saveToCart(){
		
		// Load the user_info helper
		require_once(JPATH_COMPONENT.DS.'helpers'.DS.'user_info.php' );
		if ($_billto = JRequest::getVar('billto', '')) {
			user_info::address2cart($_billto, 'BT');
		}
		// Shipto is selected in the first cartview 
		if ($_shipto = JRequest::getVar('shipto', '')) {
			user_info::address2cart($_shipto, 'ST');
		}
	}
	
	/**
	 * Save the user info. This is a copy of (modified) the save() function from Joomla
	 * user-controller. It cannot be called since that function ends with as redirect and
	 * after that we need to save the VirtueMart specific data.
	 * 
	 * We make this function private, so we can do the tests in the tasks
	 * @author Oscar van Eijk, Max Milbers
	 */
	private function saveData()
	{
		// For new user registrations, call register() first
//		$_new = JRequest::getVar( 'register_new', 0, 'post', 'int' );
		
		//todo This here made our problem, the function saveData is used in cart and in user maintance. For anonymous and registered users
		//Let us move this part to extra tasks. For exampel the redirect here is changing if in cart or not. 
		//And we dont need this construction for pure anonymous checkout
//		if ($_new) {
//			if (($user =& self::doRegister()) === false) {
//				$this->setRedirect( JURI::base() );
//				return;
//			}
//		} else {
//			// Check for request forgeries
//			JRequest::checkToken() or jexit( 'Invalid Token' );
//			$user	 =& JFactory::getUser();
//		}

		$userid = ($_new ? $user->get('id') : JRequest::getVar( 'my_user_id', 0, 'post', 'int' ));
		
		//Afaik this is not needed, we just should test if the user is logged in or not.
		//dont trust the users. Everytime think about them like evil thiefs. 
//		$unregistered = JRequest::getVar('dynaddr', 0);
		//todo  therefore we just look if a shipto_id or billto_id is in the session

		
		//Some small and for some people maybe silly hint. But try to avoid things like !UNregistered
		//In my logic !unregistered means NO-NO-registered = registered
		
		//We may make this function private and do the check in the tasks. Or maybe we should put that check into the model
		// I think into the model fits best. 
		// perform security checks
//		if($user->id){
//			$registered = true;
//		}
////		if ($user->get('id') == 0 || $userid == 0 ||
//		if ($registered && $userid <> $user->get('id') && !Permissions::getInstance()->check("admin,storeadmin")) {
//			JError::raiseError( 403, JText::_('Access Forbidden') );
//			return;
//		}

		// store data
		$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR .DS.'models' );
		$model = $this->getModel('user');

		// The view is loaded just for the setModel() method. Anyone a better suggestion?
		$view = $this->getView('user', 'html');
		$view->setModel( $this->getModel( 'userfields', 'VirtuemartModel' ), true );
		$view->setModel( $this->getModel( 'store', 'VirtuemartModel' ), true );
		$view->setModel( $this->getModel( 'currency', 'VirtuemartModel' ), true );
		$view->setModel( $this->getModel( 'orders', 'VirtuemartModel' ), true );

		//todo  Next problem, the adress must in the cart, if there exist one. Regardless if anonymous or not.
		//In other case the shopper changes his adress in the maintance, but in the cart is the old one	
		$cart = cart::getCart();
		if($cart){
			//Todo Oscar which version is the right one?
			$model->address2cart();
			$this->saveToCart();
		}
//		if ($unregistered == 1) {
//			
//		} else {
			if ($model->store()) {
				$msg	= JText::_( 'Your settings have been saved.' );//TODO Replace by JText_::
			} else {
				$msg	= $model->getError();
			}
//		}

		return $msg;

	}

	
	/**
	 * Register a new user. This is a (modified) copy of the register_save() function from Joomla
	 * user-controller. It cannot be called since that function ends with as redirect and after
	 * that we need to save the VirtueMart specific data.
	 * 
	 * @author Oscar van Eijk
	 * @return object User object
	 * @access private
	 */
	private function doRegister()
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
//		require_once(JPATH_SITE.DS.'components'.DS.'com_user'.DS.'controller.php');
//		UserController::_sendMail($user, $password);
		self::doRegisterEmail($user, $password);

		// Everything went fine, set relevant message depending upon user activation state and display message
		if ( $useractivation == 1 ) {
			$message  = JText::_( 'REG_COMPLETE_ACTIVATE' );
		} else {
			$message = JText::_( 'REG_COMPLETE' );
		}

		JRequest::setVar('user_id', $user->get('id'));
//		return $user;
		return $message;
	}
	
		/**
	 * Prepares the body for shopper and vendor, renders them and sends directly the emails
	 * 
	 * @author Max Milbers
	 * 
	 * @param CartArray $cart
	 * @param boolean When one email does not work, it gives a false back
	 * 
	 */
	private function doRegisterEmail($user, $password){

		if(empty($user) || empty($password)){
			echo 'Internal error password and/or user object empty';
			return false;	
		}
		/* Create the view */
		$view = $this->getView('cart', 'html');
		
		$view->setModel($this->getModel('cart', 'VirtuemartModel'), true);
		$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR .DS.'models' );
		$view->setModel( $this->getModel( 'user', 'VirtuemartModel' ), false );
		$view->setModel( $this->getModel( 'userfields', 'VirtuemartModel' ), true );	
		$view->setModel( $this->getModel( 'orders', 'VirtuemartModel' ), true );  //TODO we need the order_number in the mail
		$store = $this->getModel( 'store', 'VirtuemartModel' );
		$view->setModel( $store, true );

		$view->setLayout('mailregisteruser');
		
		$ok=true;
		/* Render it all */
		ob_start();
		$view->display();
		$bodyShopper = ob_get_contents();
		ob_end_clean();
		$sendShopper = shopFunctionsF::sendMail($bodyShopper,$userEmail); 
		if ( $sendShopper !== true ) {
			$ok=false;
			//TODO set message, must be a raising one
		}
		
		$view->setLayout('mailregistervendor');
		
		/* Render it all */
		ob_start();
		$view->display();
		$bodyVendor = ob_get_contents();
		ob_end_clean();
		
		$store->setId(1);  //TODO MX at the moment is the new registered email for the vendor always send to the main store
		$vendor=$store->getStore();
		$sendVendor = shopFunctionsF::sendMail($bodyVendor,$vendor->jUser->email); //TODO MX set vendorId
		if ( $sendShopper !== true ) {
			$ok=false;
			//TODO set message, must be a raising one
		}
		
		
		//Just for developing
		echo '<br />$bodyUser '.$bodyShopper;
		echo '<br />$bodyVendor '.$bodyVendor;
		return $ok;
	}
	
	/**
	 * TODO the rView is obsolete and we need to think about how we wanna handle it
	 * 
	 * @author Oscar van Eijk
	 */
	function cancel()
	{
		$return = JURI::base();
		if (($_rview = JRequest::getVar('rview', '')) != '') {
			$return = 'index.php?option=com_virtuemart&view='.$_rview;
			if ($_rview == 'cart') {
				$cart = cart::getCart();
				if ($cart){
					$return .= ($cart['inCheckOut'] ? '&task=checkout' : '');
				}
			}
		}
		$this->setRedirect( $return, $msg );
	}
}
// No closing tag
