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
		$this->useSSL = VmConfig::get('useSSL',0);
		$this->useXHTML = true;
	}

	/**
	 * The general user/account maintance view for editing userdata.
	 * It redirects automatically to the task register for anonymous users.
	 *
	 */
	public function User(){

		//We just setup a new task for non registered users
		$user = JFactory::getUser();
		$view = $this->getView('user', 'html');

		/* Add the default model */
		$this->addModelPath( JPATH_VM_ADMINISTRATOR.DS.'models' );
		$view->setModel( $this->getModel( 'user', 'VirtuemartModel' ), true );
		$view->setModel( $this->getModel( 'vendor', 'VirtuemartModel' ), true );
		$view->setModel( $this->getModel( 'userfields', 'VirtuemartModel' ), true );
		$view->setModel( $this->getModel( 'currency', 'VirtuemartModel' ), true );
		$view->setModel( $this->getModel( 'orders', 'VirtuemartModel' ), true );

		/* Set the layout */
		$view->setLayout('edit');
		$cid = JRequest::getVar('cid',null);
		if(!isset($cid)) JRequest::setVar('cid', (int)0);

		//Important! sanitize array to int
		jimport( 'joomla.utilities.arrayhelper' );
		JArrayHelper::toInteger($cid);

		/* Display it all */
		$view->display();

	}


	function editAddressSt(){

		$view = $this->getView('user', 'html');

		$this->addModelPath( JPATH_VM_ADMINISTRATOR.DS.'models' );
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

		$this->addModelPath( JPATH_VM_ADMINISTRATOR.DS.'models' );
		$view->setModel( $this->getModel( 'user', 'VirtuemartModel' ), true );
		$view->setModel( $this->getModel( 'userfields', 'VirtuemartModel' ), true );
		$view->setLayout('edit_address');

		$ftask ='savecartuser';
		$view->assignRef('fTask', $ftask);

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

		$this->addModelPath( JPATH_VM_ADMINISTRATOR.DS.'models' );
		$view->setModel( $this->getModel( 'user', 'VirtuemartModel' ), true );
		$view->setModel( $this->getModel( 'userfields', 'VirtuemartModel' ), true );
		$view->setLayout('edit_address');

		$ftask ='savecheckoutuser';
		$view->assignRef('fTask', $ftask);

		/* Display it all */
		$view->display();
	}

	/**
	 * This function is called from the layout edit_adress and just sets the right redirect back to the cart
	 * We use here the saveData(true) function, because within the cart shouldnt be done any registration.
	 *
	 * @author Max Milbers
	 */
	function saveCheckoutUser(){

		$msg = $this->saveData(true);

		//We may add here the option for silent registration.
		$this->setRedirect( JRoute::_('index.php?option=com_virtuemart&view=cart&task=checkout',$this->useXHTML,$this->useSSL), $msg );
	}

	function registerCheckoutUser(){
		$msg = $this->saveData(true,true);
		$this->setRedirect(JRoute::_( 'index.php?option=com_virtuemart&view=cart&task=checkout',$this->useXHTML,$this->useSSL ),$msg);
	}

	/**
	 * This function is called from the layout edit_adress and just sets the right redirect back to the cart.
	 * We use here the saveData(true) function, because within the cart shouldnt be done any registration.
	 *
	 * @author Max Milbers
	 */
	function saveCartUser(){

		$msg = $this->saveData(true);
		$this->setRedirect(JRoute::_( 'index.php?option=com_virtuemart&view=cart' ),$msg);
	}

	function registerCartuser(){

		$msg = $this->saveData(true,true);
		$this->setRedirect(JRoute::_( 'index.php?option=com_virtuemart&view=cart' ),$msg);
	}


	/**
	* This is the save function for the normal user edit.php layout.
	* We use here directly the userModel store function, because this view is for registering also
	* it redirects to the standard user view.
	*
	* @author Max Milbers
	*/
	function saveUser(){
		$this->addModelPath( JPATH_VM_ADMINISTRATOR.DS.'models' );
		$userModel = $this->getModel('user');

		$data = JRequest::get('post');

		// Store multiple selectlist entries as a ; separated string
		if (key_exists('vendor_accepted_currencies', $data) && is_array($data['vendor_accepted_currencies'])) {
			$data['vendor_accepted_currencies'] = implode(',', $data['vendor_accepted_currencies']);
		}

		$data['vendor_store_name'] = JRequest::getVar('vendor_store_name','','post','STRING',JREQUEST_ALLOWHTML);
		$data['vendor_store_desc'] = JRequest::getVar('vendor_store_desc','','post','STRING',JREQUEST_ALLOWHTML);
		$data['vendor_terms_of_service'] = JRequest::getVar('vendor_terms_of_service','','post','STRING',JREQUEST_ALLOWHTML);

		$ret = $userModel->store($data);
		$msg = (is_array($ret)) ? $ret['message'] : $ret;

		$this->saveToCart($data);
		$this->setRedirect( JRoute::_('index.php?option=com_virtuemart&view=user'), $msg );
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
	private function saveData($cart=false,$register=false) {

		$currentUser = JFactory::getUser();
		$msg = '';
		if($currentUser->id!=0 || $register){
			$this->addModelPath( JPATH_VM_ADMINISTRATOR.DS.'models' );
			$userModel = $this->getModel('user');

			$data = JRequest::get('post');

			// Store multiple selectlist entries as a ; separated string
			if (key_exists('vendor_accepted_currencies', $data) && is_array($data['vendor_accepted_currencies'])) {
				$data['vendor_accepted_currencies'] = implode(',', $data['vendor_accepted_currencies']);
			}

			$data['vendor_store_name'] = JRequest::getVar('vendor_store_name','','post','STRING',JREQUEST_ALLOWHTML);
			$data['vendor_store_desc'] = JRequest::getVar('vendor_store_desc','','post','STRING',JREQUEST_ALLOWHTML);
			$data['vendor_terms_of_service'] = JRequest::getVar('vendor_terms_of_service','','post','STRING',JREQUEST_ALLOWHTML);

			$ret = $userModel->store($data);
			$msg = (is_array($ret)) ? $ret['message'] : $ret;
		}
		$this->saveToCart($data);
		return $msg;
	}

	/**
	* This function just gets the post data and put the data if there is any to the cart
	*
	* @author Max Milbers
	*/
	private function saveToCart($data){

		if(!class_exists('VirtueMartCart')) require(JPATH_VM_SITE.DS.'helpers'.DS.'cart.php');
		$cart = VirtueMartCart::getCart();
		$cart->saveAddressInCart($data, $data['address_type']);

	}

	/**
	* Editing a user address was cancelled when called from the cart; return to the cart
	*
	* @author Oscar van Eijk
	*/
	function cancelCartUser(){

		$this->setRedirect( JRoute::_('index.php?option=com_virtuemart&view=cart'), $msg );
	}

	/**
	* Editing a user address was cancelled during chaeckout; return to the cart
	*
	* @author Oscar van Eijk
	*/
	function cancelCheckoutUser(){
		$this->setRedirect( JRoute::_('index.php?option=com_virtuemart&view=cart&task=checkout',$this->useXHTML,$this->useSSL), $msg );
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
