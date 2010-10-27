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
		$view = $this->getView('user', 'html');
		
		/* Add the default model */
		$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR .DS.'models' );
		$view->setModel( $this->getModel( 'user', 'VirtuemartModel' ), true );
		$view->setModel( $this->getModel( 'vendor', 'VirtuemartModel' ), true );
		$view->setModel( $this->getModel( 'userfields', 'VirtuemartModel' ), true );
		$view->setModel( $this->getModel( 'currency', 'VirtuemartModel' ), true );
		$view->setModel( $this->getModel( 'orders', 'VirtuemartModel' ), true );

		/* Set the layout */
		$view->setLayout('edit');
		$cid = JRequest::getVar('cid',null);
		if(!isset($cid)) JRequest::setVar('cid', (int)0);

//		$ftask ='saveuser';
//		$view->assignRef('fTask', $ftask);
		
		/* Display it all */
		$view->display();

	}
	
	/**
	 * This is the save function for the normal user edit.php layout.
	 * We use here directly the userModel store function, because this view is for registering also
	 * it redirects to the standard user view.
	 * 
	 * @author Max Milbers
	 */
	function saveUser(){
		$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR .DS.'models' );
		$userModel = $this->getModel('user');

		$msg = $userModel->store();
		$this->saveToCart();
		$this->setRedirect( 'index.php?option=com_virtuemart&view=user', $msg );
	}
	
	function editAddressSt(){
		
		$view = $this->getView('user', 'html');
		
		$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR .DS.'models' );
		$view->setModel( $this->getModel( 'user', 'VirtuemartModel' ), true );
		$view->setModel( $this->getModel( 'userfields', 'VirtuemartModel' ), true );
		$view->setLayout('edit_address');
		
		$ftask ='saveUser';
		$view->assignRef('fTask', $ftask);
		/* Display it all */
		$view->display();
		
	}
	
	/**
	 * This is for use in the cart, it calls a standard template for editing user adresses. It sets the task following into the form
	 * of the template to saveCartUser, the task saveCartUser just sets the right redirect in the js save(). This is done just to have the 
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
//		$ctask ='cancelcartuser';		//Looks like we dont need it, but atm not sure
//		$view->assignRef('cTask', $ctask);
		
		/* Display it all */
		$view->display();
		
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
//		$ctask ='cancelcheckoutuser';
//		$view->assignRef('cTask', $ctask);
		
		/* Display it all */
		$view->display();
	}
	
	/**
	 * This function is called from the layout edit_adress and just sets the right redirect back to the cart.
	 * We use here the saveData(true) function, because within the cart shouldnt be done any registration.
	 * 
	 * @author Max Milbers
	 */
	function saveCartUser(){
		$msg = $this->saveData(true);
		$this->saveToCart();
		$this->setRedirect( 'index.php?option=com_virtuemart&view=cart',$msg);
	}

	/**
	 * Editing a user address was cancelled when called from the cart; return to the cart
	 * 
	 * @author Oscar van Eijk
	 */	
	function cancelCartUser()
	{
		$this->setRedirect( 'index.php?option=com_virtuemart&view=cart&task=checkout', $msg );
	}
	
	/**
	 * This function is called from the layout edit_adress and just sets the right redirect back to the cart
	 * We use here the saveData(true) function, because within the cart shouldnt be done any registration.
	 * 
	 * @author Max Milbers
	 */	
	function saveCheckoutUser(){
		$msg = $this->saveData(true);
		$this->saveToCart();
		
		//We may add here the option for silent registration.
		$this->setRedirect( 'index.php?option=com_virtuemart&view=cart&task=checkout', $msg );
	}

	/**
	 * Editing a user address was cancelled during chaeckout; return to the cart
	 * 
	 * @author Oscar van Eijk
	 */	
	function cancelCheckoutUser()
	{
		$this->setRedirect( 'index.php?option=com_virtuemart&view=cart&task=checkout', $msg );
	}

	/**
	 * This function just gets the post data and put the data if there is any to the cart
	 * 
	 * @author Max Milbers
	 */
	private function saveToCart(){
		
		$data = JRequest::get('post');

		// Load the user_info helper
		require_once(JPATH_COMPONENT.DS.'helpers'.DS.'user_info.php' );
		
		user_info::address2cartanonym($data, $data['address_type']);
//		user_info::address2cartanonym($data, 'ST');

	}
	
	/**
	 * Save the user info. The saveData function dont use the userModel store function for anonymous shoppers, because it would register them.
	 * We make this function private, so we can do the tests in the tasks.
	 * 
	 * @author Max Milbers
	 * 
	 * @param boolean Defaults to false, the param is for the userModel->store function, which needs it to determin how to handle the data.
	 * @return String it gives back the messages.
	 */
	private function saveData($cart=false) {

		$currentUser =& JFactory::getUser();
		if($currentUser->id!=0){
			$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR .DS.'models' );
			$userModel = $this->getModel('user');
			$msg = $userModel->store();			
		} 

		return $msg;

	}


	/**
	 * Prepares the email body for shopper and vendor, renders them and sends directly the emails
	 * 
	 * This function is not fully workable. The information useremail and userpassword must be given better.
	 * 
	 * @author Max Milbers
	 * 
	 * @param $user JUser 
	 * @param boolean When one email does not work, it gives a false back
	 * 
	 */
	private function doRegisterEmail($user, $password){

		if(empty($user)){
			echo 'Internal error doRegisterEmail user object empty';
			return false;
		}
		if(empty($password)){
			echo 'Internal error doRegisterEmail email empty';
			return false;	
		} else {
			$password = preg_replace('/[\x00-\x1F\x7F]/', '', $password); //Disallow control chars in the email		
		}
		
		/* Create the view */
		$view = $this->getView('user', 'html');
		
		$view->setModel(VirtueMartCart::getCart(), true);
		$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR .DS.'models' );
		$view->setModel( $this->getModel( 'user', 'VirtuemartModel' ), false );
		$view->setModel( $this->getModel( 'userfields', 'VirtuemartModel' ), true );	
		$view->setModel( $this->getModel( 'orders', 'VirtuemartModel' ), true );  //TODO we need the order_number in the mail
		$vendor = $this->getModel( 'vendor', 'VirtuemartModel' );
		$view->setModel( $vendor, true );

		$view->setLayout('mailregisteruser');
		
		$ok=true;
		/* Render it all */
		ob_start();
		$view->display();
		$bodyShopper = ob_get_contents();
		ob_end_clean();
		$sendShopper = shopFunctionsF::sendMail($bodyShopper,$user->email); 
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
		
		$vendor->setId(1);  //TODO MaX at the moment is the new registered email for the vendor always send to the main store
		$vendor=$vendor->getVendor(true);
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
	 * Action cancelled; return to the previous view
	 * 
	 * @author Oscar van Eijk
	 */
	function cancel()
	{
		$return = JURI::base();
		$this->setRedirect( $return );
	}
}
// No closing tag
