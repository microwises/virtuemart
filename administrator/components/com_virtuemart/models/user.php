<?php
/**
 *
 * Data module for shop users
 *
 * @package	VirtueMart
 * @subpackage User
 * @author Oscar van Eijk
 * @author Max Milbers
 * @author	RickG
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

// Hardcoded groupID of the Super Admin
define ('__SUPER_ADMIN_GID', 25);

// Load the model framework
jimport('joomla.application.component.model');
jimport('joomla.version');

/**
 * Model class for shop users
 *
 * @package	VirtueMart
 * @subpackage	User
 * @author	RickG
 * @author Max Milbers
 */
class VirtueMartModelUser extends JModel {

	/** @var integer Primary key */
	var $_id=0;
	/** @var objectlist users */
	var $_data;
	/** @var integer Total number of users in the database */
	var $_total;
	/** @var pagination Pagination for userlist */
	var $_pagination;

	/**
	 * Constructor for the user model.
	 *
	 * The user ID is read and detmimined if it is an array of ids or just one single id.
	 */
	function __construct()
	{
		parent::__construct();

		// Get the helpers we need here, This must be in the constructor, for the RC it would be good to place the
		// "require" nearest at possible to the code, which actually using it
		if(!class_exists('ShopperGroup')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'shoppergroup.php');
		if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');
		if(!class_exists('user_info')) require(JPATH_VM_SITE.DS.'helpers'.DS.'user_info.php');

		// Get the pagination request variables
		$mainframe = JFactory::getApplication() ;
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest(JRequest::getVar('option').'.limitstart', 'limitstart', 0, 'int');

		// Set the state pagination variables
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);


		$user = JFactory::getUser();
		//anonymous sets to 0 for a new entry
		if(empty($user->id)){
			$this->setId(0);
			//			dump($this->_id,'Recogniced anonymous case');
		} else {
			$idArray = JRequest::getVar('cid',  null, '', 'array');
			//not anonymous, but no cid means already registered user edit own data
			if(!isset($idArray[0])){
				$this->setId((int)$user->id);
				//				dump($user->id,'cid was null, therefore user->id is used');
			} else {
				if($idArray[0] != $user->id){
					if(Permissions::getInstance()->check("admin,storeadmin")) {
						$this->setId((int)$idArray[0]);
					} else {
//						JError::raiseWarning(1,'Hacking attempt');
						$this->setId((int)$user->id);
					}
				}else {
					$this->setId((int)$user->id);
				}
			}
		}

	}

	/**
	 * Resets the user id and data, you should avoid external use of this function
	 * I set it now to private.
	 *
	 * @author Max Milbers
	 */
	private function setId($id)
	{
		$this->_id = $id;
		$this->_data = null;
	}

	/**
	 * Set the ID to the current user
	 */
	function setCurrent()
	{
		$_currentUser =& JFactory::getUser();
		$this->setId($_currentUser->get('id'));
	}

	/**
	 * Loads the pagination for the usertable
	 *
	 * @return JPagination Pagination for the current list of users
	 */
	function getPagination()
	{
		if (empty($this->_pagination)) {
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->_getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}
		return $this->_pagination;
	}

	/**
	 * Gets the total number of users
	 *
	 * @return int Total number of users in the database
	 */
	function _getTotal()
	{
		if (empty($this->_total)) {
			$query = $this->_getListQuery();
			$this->_total = $this->_getListCount($query);
		}
		return $this->_total;
	}

	/**
	 * Load a single user_info record
	 *
	 * @param $_ui_id string Record id
	 * @return object Database object
	 */
	function _loadUserInfo($_ui_id)
	{
		$_data = $this->getTable('user_info');
		$_data->load($_ui_id);
		return $_data;
	}

	/**
	 * Retrieve the detail record for the current $id if the data has not already been loaded.
	 */
	function getUser(){

		//This can cause trouble, when you save a new vendor in the form with apply. The new vendor_id is not set then
		//		if (empty($this->_data)) {

			$this->_data = new stdClass();

			if(empty($this->_db)) $this->_db = JFactory::getDBO();

			$this->_data = $this->getTable('vm_users');
			$this->_data->load((int)$this->_id);

			/* Add the shopper_group_ids */
			$q = 'SELECT `shopper_group_id` FROM #__vm_user_shopper_group_xref WHERE `user_id` = "'.$this->_id.'"';
			$this->_db->setQuery($q);
			$this->_data->shopper_groups = $this->_db->loadResultArray();

			$this->_data->JUser =& JUser::getInstance($this->_id);

			$_ui = $this->_getList('SELECT `user_info_id` FROM `#__vm_user_info` WHERE `user_id` = "' . $this->_id.'"');

			$this->_data->userInfo = array ();

			for ($i = 0, $n = count($_ui); $i < $n; $i++) {
				$_ui_id = $_ui[$i]->user_info_id;
				$this->_data->userInfo[$_ui_id] = $this->_loadUserInfo($_ui_id);
				/*
				 * Hack by Oscar for Ticket #296 (redmine); user_is_vendor gets reset when a BT address is saved
				 * from the cart. I don't know is this is the only location, but it can be fixed by
				 * making sure the user_is_vendor field is in the BT dataset.
				 * I make this hack here, since I'm not sure if it causes problems on more locations.
				 * @TODO Find out is there's a more decvent solution. Maybe when the user_info table gets reorganised?
				 */
				if ($this->_data->userInfo[$_ui_id]->address_type == 'BT') {
					$this->_data->userInfo[$_ui_id]->user_is_vendor = $this->_data->user_is_vendor;
				}
				// End hack
				$this->_data->userInfo[$_ui_id]->email = $this->_data->JUser->email;
			}
			if($this->_data->user_is_vendor){
				if(!class_exists('VirtueMartModelVendor')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'vendor.php' );
				$vendorModel = new VirtueMartModelVendor();

				$vendorModel->setId($this->_data->vendor_id);
				$this->_data->vendor = $vendorModel->getVendor();
			}
			//		}

			if (!$this->_data) {
				$this->_data = new stdClass();
				$this->_id = 0;
			}

			return $this->_data;
		}

		/**
		 * Retrieve contact info for a user if any
		 *
		 * @return array of null
		 */
		function getContactDetails()
		{
			if ($this->_id) {
				$this->_db->setQuery('SELECT * FROM #__contact_details WHERE user_id = ' . $this->_id);
				$_contacts = $this->_db->loadObjectList();
				if (count($_contacts) > 0) {
					return $_contacts[0];
				}
			}
			return null;
		}

		/**
		 * Return a list with groups that can be set by the current user
		 *
		 * @return mixed Array with groups that can be set, or the groupname (string) if it cannot be changed.
		 */
		function getGroupList()
		{
			$_aclObject =& JFactory::getACL();

			if (VmConfig::isAtLeastVersion('1.6.0')){
				//TODO fix this latter. It's just an workarround to make it working on 1.6
				$gids = $this->_data->JUser->get('groups');
				return array_flip($gids);
			}

			$_usr = $_aclObject->get_object_id ('users', $this->_data->JUser->get('id'), 'ARO');
			$_grp = $_aclObject->get_object_groups ($_usr, 'ARO');
			$_grpName = strtolower ($_aclObject->get_group_name($_grp[0], 'ARO'));

			$_currentUser =& JFactory::getUser();
			$_my_usr = $_aclObject->get_object_id ('users', $_currentUser->get('id'), 'ARO');
			$_my_grp = $_aclObject->get_object_groups ($_my_usr, 'ARO');
			$_my_grpName = strtolower ($_aclObject->get_group_name($_my_grp[0], 'ARO'));

			// administrators can't change each other and frontend-only users can only see groupnames
			if (( $_grpName == $_my_grpName && $_my_grpName == 'administrator' ) ||
			!$_aclObject->is_group_child_of($_my_grpName, 'Public Backend')) {
				return $_grpName;
			} else {
				$_grpList = $_aclObject->get_group_children_tree(null, 'USERS', false);

				$_remGroups = $_aclObject->get_group_children( $_my_grp[0], 'ARO', 'RECURSE' );
				if (!$_remGroups) {
					$_remGroups = array();
				}

				// Make sure privs higher than my own can't be granted
				if (in_array($_grp[0], $_remGroups)) {
					// nor can privs of users with higher privs be decreased.
					return $_grpName;
				}
				$_i = 0;
				$_j = count($_grpList);
				while ($_i <  $_j) {
					if (in_array($_grpList[$_i]->value, $_remGroups)) {
						array_splice( $_grpList, $_i, 1 );
						$_j = count($_grpList);
					} else {
						$_i++;
					}
				}

				return $_grpList;
			}
		}

		/**
		 * Bind the post data to the JUser object and the VM tables, then saves it
		 * It is used to register new users
		 * This function can also change already registered users, this is important when a registered user changes his email within the checkout.
		 *
		 * @author Max Milbers
		 * @author Oscar van Eijk
		 * @return boolean True is the save was successful, false otherwise.
		 */
		function store($data=0)
		{
			$mainframe = JFactory::getApplication() ;

			if(empty($data)) $data = JRequest::get('post');
			//		$currentUser =& JFactory::getUser();

			//To find out, if we have to register a new user, we take a look on the id of the usermodel object.
			//The constructor sets automatically the right id.
			$new = ($this->_id < 1);
			$user = new JUser($this->_id);
			$gid = $user->get('gid'); // Save original gid

			/*
			 * Before I used this "if($cart && !$new)"
			 * This construction is necessary, because this function is used to register a new JUser, so we need all the JUser data in $data.
			 * On the other hand this function is also used just for updating JUser data, like the email for the BT address. In this case the
			 * name, username, password and so on is already stored in the JUser and dont need to be entered again.
			 */
			if(empty ($data['email'])){
				$email = $user->get('email');
				if(!empty($email)){
					$data['email'] = $email;
				} else {
					$data['email'] = JRequest::getVar('email', '', 'post', 'email');
				}
			}

			//This is important, when a user changes his email address from the cart,
			//that means using view user layout edit_address (which is called from the cart)
			$user->set('email',$data['email']);


			if(empty ($data['name'])){
				$name = $user->get('name');
				if(!empty($name)){
					$data['name'] = $name;
				} else {
					$data['name'] = JRequest::getVar('name', '', 'post', 'name');
				}
			}

			if(empty ($data['username'])){
				$username = $user->get('username');
				if(!empty($username)){
					$data['username'] = $username;
				} else {
					$data['username'] = JRequest::getVar('username', '', 'post', 'username');
				}
			}

			if(empty ($data['password'])){
				//This is interesting, the passwords dont need this construction
				//			$password = $user->get('password');
				//			if(!empty($password)){
				//				$data['password'] = $password;
				//			} else {
				$data['password'] = JRequest::getVar('password', '', 'post', 'string' ,JREQUEST_ALLOWRAW);
				//			}
			}

			if(empty ($data['password2'])){
				//This is interesting, the passwords dont need this construction
				//			$password2 = $user->get('password2');
				//			if(!empty($password2)){
				//				$data['password2'] = $password2;
				//			} else {
				$data['password2'] = JRequest::getVar('password2', '', 'post', 'string' ,JREQUEST_ALLOWRAW);
				//			}
			}

			// Bind Joomla userdata
			if (!$user->bind($data)) {
				//develop
				$this->setError('user bind '.$user->getError());
				return false;
			}

			if($new){
				// If user registration is not allowed, show 403 not authorized.
				// But it is possible for admins and storeadmins to save
				$usersConfig = &JComponentHelper::getParams( 'com_users' );
				if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');

				if (!Permissions::getInstance()->check("admin,storeadmin") && $usersConfig->get('allowUserRegistration') == '0') {
					JError::raiseError( 403, JText::_('COM_VIRTUEMART_ACCESS_FORBIDDEN'));
					return;
				}
				$authorize	=& JFactory::getACL();

				// Initialize new usertype setting
				$newUsertype = $usersConfig->get( 'new_usertype' );
				if (!$newUsertype) {
					$newUsertype = 'Registered';
				}

				// Set some initial user values
				$user->set('usertype', $newUsertype);

				if ( VmConfig::isJ15()){
					$user->set('gid', $authorize->get_group_id( '', $newUsertype, 'ARO' ));
				}

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
			}

			$option = JRequest::getCmd( 'option');
			// If an exising superadmin gets a new group, make sure enough admins are left...
			if (!$new && $user->get('gid') != $gid && $gid == __SUPER_ADMIN_GID) {
				if ($this->getSuperAdminCount() <= 1) {
					$this->setError(JText::_('COM_VIRTUEMART_USER_ERR_ONLYSUPERADMIN'));
					return false;
				}
			}

			// Save the JUser object
			if (!$user->save()) {
				//This?
				$this->setError('_user save '.$user->getError());
				//or this?
				JError::raiseWarning('', JText::_( $user->getError()));
				return false;
			}

			$newId = $user->get('id');
			$data['user_id'] = $newId;		//We need this in that case, because data is bound to table later
			$this->setId($newId);

			//I would like to do this function in the FE user/controller like the other emails, with layout
			if ($new) {
				$this->sendRegistrationEmail($user);
			}

			//Save the VM user stuff
			if(!$this->saveUserData($data,$new)){
				$this->setError('Was not able to save the virtuemart user data');
				JError::raiseWarning('', JText::_('COM_VIRTUEMART_USED_RAISEWARNING_WAS_NOT_ABLE_TO_SAVE_VIRTUEMART_USER_DATA'));
			}

			// Send registration confirmation mail
			//		$password = JRequest::getString('password', '', 'post', JREQUEST_ALLOWRAW);
			//		$password = preg_replace('/[\x00-\x1F\x7F]/', '', $password); //Disallow control chars in the email

			//		self::doRegisterEmail($user, $password);

			// Everything went fine, set relevant message depending upon user activation state and display message
			if ($new) {
				if ( $useractivation == 1 ) {
					$message  = JText::_('COM_VIRTUEMART_REG_COMPLETE_ACTIVATE');
				} else {
					$message = JText::_('COM_VIRTUEMART_REG_COMPLETE');
				}
			} else {
				$message = JText::_('COM_VIRTUEMART_USER_DATA_STORED');	//TODO write right keystring
			}

			return array('user'=>$user,'password'=>$data['password'],'message'=>$message,'newId'=>$newId);

		}

		/**
		 * This function is NOT for anonymous. Anonymous just get the information directly sent by email.
		 * This function saves the vm Userdata for registered JUsers.
		 * TODO, setting of shoppergroup isnt done
		 *
		 * TODO No reason not to use this function for new users, but it requires a Joomla <user> plugin
		 * that gets fired by the onAfterStoreUser. I'll built that (OvE)
		 *
		 * Notice:
		 * As long we do not have the silent registration, an anonymous does not get registered. It is enough to send the order_id
		 * with the email. The order is saved with all information in an extra table, so there is
		 * no need for a silent registration. We may think about if we actually need/want the feature silent registration
		 * The information of anonymous is stored in the order table and has nothing todo with the usermodel!
		 *
		 * @author Max Milbers
		 * @author Oscar van Eijk
		 * return boolean
		 */
		private function saveUserData($_data,$new){

			if(empty($this->_id)){
				echo 'This is a notice for developers, you used this function for an anonymous user, but it is only designed for already registered ones';
			}

			//update user table
			$usertable = $this->getTable('vm_users');
			$vmusersData = array('user_id'=>$_data['user_id'],'user_is_vendor'=>$_data['user_is_vendor'],'vendor_id'=>$_data['vendor_id'],'customer_number'=>$_data['customer_number'],'perms'=>$_data['perms']);
			if (!$usertable->bind($vmusersData)) {
				$this->setError($usertable->getError());
				return false;
			}

			// Make sure the record is valid
			if (!$usertable->check()) {
				$this->setError($table->getError());
				return false;
			}

			// Save the record to the database
			if (!$usertable->store()) {
				$this->setError($usertable->getError());
				return false;
			}

			if(empty($_data['shopper_group_id'])){
				$q = 'SELECT `shopper_group_id` FROM `#__vm_shopper_group` WHERE `default`="1" AND `vendor_id`="1" ';
				$this->_db->setQuery($q);
				$_data['shopper_group_id']=$this->_db->loadResult();
			}

			// Bind the form fields to the auth_user_group table
			$shoppergroupData = array('user_id'=>$this->_id,'shopper_group_id'=>$_data['shopper_group_id']);
			if(!class_exists('modelfunctions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'modelfunctions.php');
			modelfunctions::storeArrayData('#__vm_user_shopper_group_xref','user_id','shopper_group_id',$this->_id,$_data['shopper_group_id']);

			if (!user_info::storeAddress($_data, 'user_info', $new)) {
				$this->setError('Was not able to save the virtuemart user data');
				return false;
			}

			if($_data['user_is_vendor']){

				//	$_data['vendor_id'] = $_data['my_vendor_id'];
				if(!class_exists('VirtueMartModelVendor')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'vendor.php');
				$vendorModel = new VirtueMartModelVendor();

				//TODO Attention this is set now to vendor_id=1, because using a vendor with different id then 1 is not completly supported and can lead to bugs
				//So we disable the possibility to store vendors not with vendor_id = 1
				//$vendorModel->setId($_data['vendor_id']);
				$vendorModel->setId(1);
				if (!$vendorModel->store($_data)) {
					$this->setError($vendorModel->getError());
					return false;
				}else{
					//Update xref Table
					$vendor_id = $vendorModel->getId();

					//update user table
					//					$usertable = $this->getTable('vm_users');
					$vmusersData = array('user_id'=>$_data['user_id'],'user_is_vendor'=>1,'vendor_id'=>$vendor_id,'customer_number'=>$_data['customer_number'],'perms'=>$_data['perms']);
					if (!$usertable->bind($vmusersData)) {
						$this->setError($table->getError());
						return false;
					}
					// Make sure the record is valid
					if (!$usertable->check()) {
						$this->setError($table->getError());
						return false;
					}

					// Save the record to the database
					if (!$usertable->store()) {
						$this->setError($table->getError());
						return false;
					}

				}
				//			}
			}
			return true;
		}


	 /**
	  * This uses the shopfunctionsF::renderAndSentVmMail function, which uses a controller and task to render the content
	  * and sents it then.
	  *
	  * deprecated: Sends a standard registration email.
	  *
	  * @author Oscar van Eijk
	  * @author Max Milbers
	  */
	 function sendRegistrationEmail($user){

	 	if(VmConfig::get('html_email',true)){
		 	if(!class_exists('shopFunctionsF')) require(JPATH_VM_SITE.DS.'helpers'.DS.'shopfunctionsf.php');

		 	/* Create the view */
			$controller = new VirtueMartControllerUser();
			$view = $controller->getView('user', 'html');

			$view->setModel( $this );
			$view->setModel( $controller->getModel( 'userfields' ) );

			$vendorModel = $controller->getModel( 'vendor' );
			$vendorId = 1;
			$vendorModel->setId($vendorId);
			$vendor = $vendorModel->getVendor();
			$vendorModel->addImagesToVendor($vendor);
			dump($vendor,'My vendor in sent email');
			$view->setModel( $vendorModel );

			// Send registration confirmation mail
			$password = JRequest::getString('password', '', 'post', JREQUEST_ALLOWRAW);
			$password = preg_replace('/[\x00-\x1F\x7F]/', '', $password); //Disallow control chars in the email
			$view->assignRef('password',$password);

		 	// If user activation is turned on, we need to set the activation information
		 	$usersConfig = &JComponentHelper::getParams( 'com_users' );
			$useractivation = $usersConfig->get( 'useractivation' );
			if ($useractivation == '1')
			{
				jimport('joomla.user.helper');
				$activationLink = "index.php?option=com_user&task=activate&activation=".$user->get('activation');
				$view->assignRef('activationLink',$activationLink);
			}

		 	$subject = JText::sprintf('COM_VIRTUEMART_NEW_USER_MESSAGE_SUBJECT',$vendor->vendor_store_name);
			$view->setLayout('mailregisteruser');
			$res = shopFunctionsF::renderAndSentVmMail(	$view,
														$user->get('email'),
														$subject,
														array($vendorModel->getVendorEmail($vendorId), $vendor->vendor_store_name)
														);

			$subject = JText::sprintf('COM_VIRTUEMART_NEW_USER_MESSAGE_VENDOR_SUBJECT',$user->get('email'));
			$view->setLayout('mailregistervendor');
			$res = shopFunctionsF::renderAndSentVmMail(	$view,
														$vendorModel->getVendorEmail($vendorId),
														$subject,
														array($user->get('email'),$user->get('email'))
														);
	 	} else {

	 		$mainframe = JFactory::getApplication() ;
		 	$fromMail = $mainframe->getCfg('mailfrom');
		 	$fromName = $mainframe->getCfg('fromname');
		 	$fromSite = $mainframe->getCfg('sitename');

		 	$subject = JText::_('COM_VIRTUEMART_NEW_USER_MESSAGE_SUBJECT');
		 	$message =  JText::sprintf('COM_VIRTUEMART_NEW_USER_MESSAGE', $user->get('name')
				, $fromSite
				, JURI::root()
				, $user->get('username')
				, $user->password_clear
				);
			JUtility::sendMail( $fromMail, $fromName, $user->get('email'), $subject, $message );
	 	}

	 }

	 /**
	  * Delete all record ids selected
	  *
	  * @return boolean True is the delete was successful, false otherwise.
	  */
	 function delete()
	 {
	 	$userIds = JRequest::getVar('cid',  0, '', 'array');
	 	$userInfo =& $this->getTable('user_info');
	 	$vm_shoppergroup_xref =& $this->getTable('user_shopper_group_xref');
                $vm_users =& $this->getTable('vm_users');
	 	$_status = true;
	 	foreach($userIds as $userId) {
	 		if ($this->getSuperAdminCount() <= 1) {
	 			// Prevent deletion of the only Super Admin
	 			$_u =& JUser::getInstance($userId);
	 			if ($_u->get('gid') == __SUPER_ADMIN_GID) {
	 				$this->setError(JText::_('COM_VIRTUEMART_USER_ERR_LASTSUPERADMIN'));
	 				$_status = false;
	 				continue;
	 			}
	 		}

	 		if (!$userInfo->delete($userId)) {
	 			$this->setError($userInfo->getError());
	 			return false;
	 		}
	 		if (!$vm_shoppergroup_xref->delete($userId)) {
	 			$this->setError($vm_shoppergroup_xref->getError()); // Signal but continue
	 			$_status = false;
	 			continue;
	 		}
                        if (!$vm_users->delete($userId)) {
	 			$this->setError($vm_users->getError()); // Signal but continue
	 			$_status = false;
	 			continue;
	 		}
	 		$_JUser =& JUser::getInstance($userId);
	 		if (!$_JUser->delete()) {
	 			$this->setError($jUser->getError());
	 			return false;
	 		}
	 	}
	 	return $_status;
	 }

	 /**
	  * Retrieve a list of users from the database.
	  *
	  * @return object List of user objects
	  */
	 function getUserList()
	 {
			$query = $this->_getListQuery();
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));

			return $this->_data;
	 }

	 /**
	  * Retrieve a list of addresses for a user
	  *
	  *  @param $_uid int User ID
	  *  @param $_type string, addess- type, ST (ShipTo, default) or BT (BillTo)
	  */
	 function getUserAddressList($_uid = 0, $_type = 'ST')
	 {
	 	$_q = 'SELECT * '
			. ' FROM #__vm_user_info '
			. " WHERE user_id='" . (($_uid==0)?$this->_id:$_uid) . "' "
			. " AND address_type='$_type'";
			return ($this->_getList($_q));
	 }

	 /**
	  * Retrieve a single address for a user
	  *
	  *  @param $_uid int User ID
	  *  @param $_user_info_id string Optional User Info ID
	  *  @param $_type string, addess- type, ST (ShipTo, default) or BT (BillTo). Empty string to ignore
	  */
	 function getUserAddress($_uid = 0, $_user_info_id = -1, $_type = 'ST')
	 {
	 	$_q = 'SELECT * '
			. ' FROM #__vm_user_info '
			. " WHERE user_id='" . (($_uid==0)?$this->_id:$_uid) . "' ";
			if ($_type !== '') {
				$_q .= " AND address_type='$_type'";
			}
			if ($_user_info_id !== -1) {
				$_q .= " AND user_info_id='$_user_info_id'";
			}
			return ($this->_getList($_q));
	 }

	 /**
	  * Retrieves the Customer Number of the user specified by ID
	  *
	  * @param int $_id User ID
	  * @return string Customer Number
	  */
	 function getCustomerNumberById($_id = 0)
	 {
	 	$_q = "SELECT `customer_number` FROM `#__vm_users` "
			."WHERE `user_id`='" . (($_id==0)?$this->_id:$_id) . "' ";
			$_r = $this->_getList($_q);
			if(!empty($_r[0])){
				return $_r[0]->customer_number;
			}else {
				return 0;
			}

	 }

	 /**
	  * Get the number of active Super Admins
	  *
	  * @return integer
	  */
	 function getSuperAdminCount()
	 {
	 	$this->_db->setQuery('SELECT COUNT(id) FROM #__users'
			. ' WHERE gid = ' . __SUPER_ADMIN_GID . ' AND block = 0');
			return ($this->_db->loadResult());
	 }

	 /**
	  * If a filter was set, get the SQL WHERE clase
	  *
	  * @return string text to add to the SQL statement
	  */
	 function _getFilter()
	 {
	 	if (JRequest::getVar('search', false)) {
	 		$_where = ' WHERE `name` LIKE ' .$this->_db->Quote('%'.JRequest::getVar('search').'%')
	 		. ' OR `username` LIKE ' .$this->_db->Quote('%'.JRequest::getVar('search').'%');
	 		return ($_where);
	 	}
	 	return ('');
	 }

	 /**
	  * Get the SQL Ordering statement
	  *
	  * @return string text to add to the SQL statement
	  */
	 function _getOrdering()
	 {
	 	$option = JRequest::getCmd( 'option');
	 	$mainframe = JFactory::getApplication() ;

	 	$filter_order_Dir = $mainframe->getUserStateFromRequest( $option.'filter_order_Dir', 'filter_order_Dir', 'asc', 'word' );
	 	$filter_order     = $mainframe->getUserStateFromRequest( $option.'filter_order', 'filter_order', 'id', 'cmd' );

	 	// FIXME this is a dirty hack since we don't have an ordering field yet...
	 	if ($filter_order == 'ordering') $filter_order = 'id';
	 	return (' ORDER BY '.$filter_order.' '.$filter_order_Dir);
	 }

	 /**
	  * Build the query to list all Users
	  *
	  * @return string SQL query statement
	  */
	 function _getListQuery (){

	 	// Used tables #__vm_users, #__vm_user_info, #__vm_user_perm_groups, #__vm_user_shopper_group_xref, #__vm_vendor
	 	$query = 'SELECT DISTINCT ju.id AS id '
			. ', ju.name AS name'
			. ', ju.username AS username '
			. ', vmu.user_is_vendor AS is_vendor'
			. ', vmu.perms AS perms'
			. ', ju.usertype AS usertype'
			. ", IFNULL(sg.shopper_group_name, '') AS shopper_group_name "
			. 'FROM #__users AS ju '
			. 'LEFT JOIN #__vm_users AS vmu ON ju.id = vmu.user_id '
			. 'LEFT JOIN #__vm_user_shopper_group_xref AS vx ON ju.id = vx.user_id '
			. 'LEFT JOIN #__vm_shopper_group AS sg ON vx.shopper_group_id = sg.shopper_group_id ';
		$query .= $this->_getFilter();
		$query .= $this->_getOrdering();

		return ($query);
	 }

	 /**
	  * Take a list of userIds and check if they all have a record in #__vm_user_info
	  *
	  * @author Oscar van Eijk
	  * @param $_ids Array with userIds to check (uId, uId, ...)
	  * @return array with invalid users (userId => userName, ...)
	  */
	 function validateUsers ($_ids = array())
	 {
	 	if (count($_ids) == 0) {
	 		return array();
	 	}
	 	$_missing = $this->_getList('SELECT j.username AS uname '
			. ',      j.id       AS uid '
			. 'FROM `#__users` j '
			. 'WHERE j.id IN (' . join(',', $_ids) . ') '
			. 'AND NOT EXISTS ('
			. 'SELECT user_id FROM `#__vm_user_info` v '
			. 'WHERE v.user_id = j.id'
			. ')'
			);
			$_missingUsers = array();
			foreach ($_missing as $_m) {
				$_missingUsers[$_m->uid] = $_m->uname;
			}
			return $_missingUsers;
	 }

	 /**
	  * Switch a toggleable field on or off
	  *
	  * @param $field string Database fieldname to toggle
	  * @param $id array list of primary keys to toggle
	  * @param $value boolean Value to set
	  * @return boolean Result
	  */
	 function toggle($field, $id = array(), $value = 1)
	 {
	 	$_missingUsers = $this->validateUsers($id);
	 	$id = array_diff($id, array_keys($_missingUsers)); // Remove missing users
	 	foreach ($_missingUsers as $_uid => $_username) {
	 		JError::raiseWarning(500, JText::sprintf('COM_VIRTUEMART_USER_USERNAME_INCOMPLETE_PROFILE', $_username) );
	 	}
	 	if (count($id) > 0)
	 	{
	 		JArrayHelper::toInteger($id);
	 		$ids = implode( ',', $id );

	 		$query = 'UPDATE `#__vm_users`'
				. ' SET `' . $field . '` = '.(int) $value
				. ' WHERE user_id IN ( '.$ids.' )'
				;
				$this->_db->setQuery( $query );
				if (!$this->_db->query()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
	 	}
	 	return true;
	 }

	 /**
	  * Return a list of Joomla ACL groups.
	  *
	  * The returned object list includes a group anme and a group name with spaces
	  * prepended to the name for displaying an indented tree.
	  *
	  * @author RickG
	  * @return ObjectList List of acl group objects.
	  */
	 function getAclGroupIndentedTree()
	 {

	 	//TODO check this out
	 	if (VmConfig::isJ15()) {
			$name = 'name';
			$as = '` AS `title`';
			$table = '#__core_acl_aro_groups';
	 		$and = 'AND `parent`.`lft` > 2 ';
	 	}
	 	else {
			$name = 'title';
			$as = '`';
			$table = '#__usergroups';
			$and = '';
	 	}

	 	$query = 'SELECT `node`.`' . $name . $as . ', CONCAT(REPEAT("&nbsp;&nbsp;&nbsp;", (COUNT(`parent`.`' . $name . '`) - 1)), `node`.`' . $name . '`) AS `text` ';
	 	$query .= 'FROM `' . $table . '` AS node, `' . $table . '` AS parent ';
	 	$query .= 'WHERE `node`.`lft` BETWEEN `parent`.`lft` AND `parent`.`rgt` ';
	 	$query .= $and;
	 	$query .= 'GROUP BY `node`.`' . $name . '` ';
	 	$query .= 'ORDER BY `node`.`lft`';

	 	$this->_db->setQuery($query);
	 	return $this->_db->loadObjectList();
	 }
}


//No Closing tag
