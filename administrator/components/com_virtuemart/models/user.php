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

if(!class_exists('VmModel'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmmodel.php');

/**
 * Model class for shop users
 *
 * @package	VirtueMart
 * @subpackage	User
 * @author	RickG
 * @author Max Milbers
 */
class VirtueMartModelUser extends VmModel {

	/**
	 * Constructor for the user model.
	 *
	 * The user ID is read and detmimined if it is an array of ids or just one single id.
	 */
	function __construct(){

		parent::__construct();

		$this->setMainTable('vmusers');
		$this->setToggleName('user_is_vendor');

	}

	/**
	 * Resets the user id and data, you should avoid external use of this function
	 * I set it now to private.
	 *
	 * @author Max Milbers
	 */
	public function setId($cid){

		$user = JFactory::getUser();
		//anonymous sets to 0 for a new entry
		if(empty($user->id)){
			$this->setUserId(0);
			//			echo($this->_id,'Recogniced anonymous case');
		} else {

			//not anonymous, but no cid means already registered user edit own data
			if(empty($cid)){
				$this->setUserId($user->id);
				//				echo($user->id,'cid was null, therefore user->id is used');
			} else {
				if($cid != $user->id){
					if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');
					if(Permissions::getInstance()->check("admin,storeadmin")) {
						$this->setUserId($cid);
					} else {
//						JError::raiseWarning(1,'Hacking attempt');
						$this->setUserId($user->id);
					}
				}else {
					$this->setUserId($user->id);
				}
			}
		}
	}

	public function setUserId($id){

	    if($this->_id!=$id){
			$this->_id = (int)$id;
			$this->_data = null;
    	}
	}

	/**
	 * Set the ID to the current user
	 */
	function setCurrent()
	{
		$user = JFactory::getUser();
		$this->setId($user->get('id'));
	}


	/**
	 * This should load the userdata in userfields so that they can easily displayed
	 *
	 * @author Max Milbers
	 */

	function getUserDataInFields($layoutName, $type, $id, $toggles=0, $skips=0){

		if(!class_exists('VirtueMartModelUserfields')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'userfields.php' );
		$userFieldsModel = new VirtuemartModelUserfields();

		$prepareUserFields = $userFieldsModel->getUserFieldsFor(
										$layoutName,
										$type,
										$id
									);

		if($type=='ST'){
			$preFix = 'shipto_';
		} else {
			$preFix = '';
		}
		$userFields = array();
		$userdata = $this->getUser();

		if(!empty($userdata->userInfo) && count($userdata->userInfo)>0) {

			$currentUserData = current($userdata->userInfo);
			for ($_i = 0; $_i < count($userdata->userInfo); $_i++) {
				dump($currentUserData,'getUserDataInFields');

				if($currentUserData->address_type==$type){
					$fields = $userFieldsModel->getUserFieldsByUser(
											$prepareUserFields
											,$currentUserData
											,$preFix
										);
					$fields['virtuemart_userinfo_id'] = key($userdata->userInfo);
					$userFields[] = $fields;
				}
				$currentUserData = next($userdata->userInfo);

			}
		}

		if(empty($userFields)){
			$fields = $userFieldsModel->getUserFieldsByUser(
								$prepareUserFields
								,null
								,$preFix
							);
			$fields['virtuemart_userinfo_id'] = 0;
			$userFields[] = $fields;
		}

		return $userFields;
	}


	/**
	 * Retrieve the detail record for the current $id if the data has not already been loaded.
	 */
	function getUser(){

		if(empty($this->_db)) $this->_db = JFactory::getDBO();

		$this->_data = $this->getTable('vmusers');
		$this->_data->load((int)$this->_id);

		// Add the virtuemart_shoppergroup_ids
		$xrefTable = $this->getTable('vmuser_shoppergroups');
		$this->_data->shopper_groups = $xrefTable->load($this->_id);

		$this->_data->JUser = JUser::getInstance($this->_id);

		$_ui = $this->_getList('SELECT `virtuemart_userinfo_id` FROM `#__virtuemart_userinfos` WHERE `virtuemart_user_id` = "' . (int)$this->_id.'"');

		$this->_data->userInfo = array ();

		$BTuid = 0;
		//$userinfo = $this->getTable('userinfos');
		for ($i = 0, $n = count($_ui); $i < $n; $i++) {

			$_ui_id = $_ui[$i]->virtuemart_userinfo_id;

			$this->_data->userInfo[$_ui_id] = $this->getTable('userinfos');
			$this->_data->userInfo[$_ui_id]->load($_ui_id);
// 			dump($this->_data->userInfo[$_ui_id],'$_ui_id ggggg');
			/*
			 * Hack by Oscar for Ticket #296 (redmine); user_is_vendor gets reset when a BT address is saved
			 * from the cart. I don't know is this is the only location, but it can be fixed by
			 * making sure the user_is_vendor field is in the BT dataset.
			 * I make this hack here, since I'm not sure if it causes problems on more locations.
			 * @TODO Find out is there's a more decvent solution. Maybe when the user_info table gets reorganised?
			 */
			if ($this->_data->userInfo[$_ui_id]->address_type == 'BT') {
				$BTuid = $_ui_id;

// 				$this->_data->userInfo[$_ui_id]->user_is_vendor = $this->_data->user_is_vendor;
// 				$this->_data->userInfo[$_ui_id]->name = $this->_data->JUser->name;
			}
			// End hack
			//$this->_data->userInfo[$_ui_id]->email = $this->_data->JUser->email;
		}

// 		if(!empty($this->_data->userInfo[$BTuid])){
			dump('fill BT');
			$this->_data->userInfo[$BTuid]->name = $this->_data->JUser->name;
			$this->_data->userInfo[$BTuid]->email = $this->_data->JUser->email;
			$this->_data->userInfo[$BTuid]->username = $this->_data->JUser->username;
			$this->_data->userInfo[$BTuid]->address_type = 'BT';
//
//		}

		if($this->_data->user_is_vendor){

			if(!class_exists('VirtueMartModelVendor')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'vendor.php' );
			$vendorModel = new VirtueMartModelVendor();

			$vendorModel->setId($this->_data->virtuemart_vendor_id);
			$this->_data->vendor = $vendorModel->getVendor();
			dump($this->_data->vendor,'my user is vendor');
		}

		dump($this->_data,'my user data');
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
		$_aclObject = JFactory::getACL();

		if (VmConfig::isAtLeastVersion('1.6.0')){
			//TODO fix this latter. It's just an workarround to make it working on 1.6
			$gids = $this->_data->JUser->get('groups');
			return array_flip($gids);
		}

		$_usr = $_aclObject->get_object_id ('users', $this->_data->JUser->get('id'), 'ARO');
		$_grp = $_aclObject->get_object_groups ($_usr, 'ARO');
		$_grpName = strtolower ($_aclObject->get_group_name($_grp[0], 'ARO'));

		$_currentUser = JFactory::getUser();
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
	function store($data=0){

		dump('called store user');
		JRequest::checkToken() or jexit( 'Invalid Token, while trying to save user' );
		$mainframe = JFactory::getApplication() ;

		if(empty($data)){
			$mainframe->enqueueMessage('Developer notice, no data to store for user');
			return false;
		}

		//To find out, if we have to register a new user, we take a look on the id of the usermodel object.
		//The constructor sets automatically the right id.
		$new = ($this->_id < 1);
		$user = JFactory::getUser($this->_id);
		$gid = $user->get('gid'); // Save original gid

		// Before I used this "if($cart && !$new)"
		// This construction is necessary, because this function is used to register a new JUser, so we need all the JUser data in $data.
		// On the other hand this function is also used just for updating JUser data, like the email for the BT address. In this case the
		// name, username, password and so on is already stored in the JUser and dont need to be entered again.

		if(empty ($data['email'])){
			$email = $user->get('email');
			if(!empty($email)){
				$data['email'] = $email;
			}
		} else {
			$data['email'] =  JRequest::getString('email', '', 'post', 'email');
		}
		$data['email'] = str_replace(array('\'','"',',','%','*','/','\\','?','^','`','{','}','|','~'),array(''),$data['email']);

		//This is important, when a user changes his email address from the cart,
		//that means using view user layout edit_address (which is called from the cart)
		$user->set('email',$data['email']);

		if(empty ($data['name'])){
			$name = $user->get('name');
			if(!empty($name)){
				$data['name'] = $name;
			}
		} else {
				$data['name'] = JRequest::getString('name', '', 'post', 'name');
		}
		$data['name'] = str_replace(array('\'','"',',','%','*','/','\\','?','^','`','{','}','|','~'),array(''),$data['name']);

		if(empty ($data['username'])){
			$username = $user->get('username');
			if(!empty($username)){
				$data['username'] = $username;
			} else {
				$data['username'] = JRequest::getVar('username', '', 'post', 'username');
			}
		}

		if(empty ($data['password'])){
			$data['password'] = JRequest::getVar('password', '', 'post', 'string' ,JREQUEST_ALLOWRAW);

		}

		if(empty ($data['password2'])){
			$data['password2'] = JRequest::getVar('password2', '', 'post', 'string' ,JREQUEST_ALLOWRAW);
		}
		dump('before bind j user');
		// Bind Joomla userdata
		if (!$user->bind($data)) {
			//develop
			$this->setError('user bind '.$user->getError());
			return false;
		}

		if($new){
			// If user registration is not allowed, show 403 not authorized.
			// But it is possible for admins and storeadmins to save
			$usersConfig = JComponentHelper::getParams( 'com_users' );
			if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');

			if (!Permissions::getInstance()->check("admin,storeadmin") && $usersConfig->get('allowUserRegistration') == '0') {
				JError::raiseError( 403, JText::_('COM_VIRTUEMART_ACCESS_FORBIDDEN'));
				return;
			}
			$authorize	= JFactory::getACL();

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

			$date = JFactory::getDate();
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
			JError::raiseWarning('', JText::_( $user->getError()));
			dump('Was not able to store user');
			return false;
		}
		dump('stored j user');
		$newId = $user->get('id');
		$data['virtuemart_user_id'] = $newId;	//We need this in that case, because data is bound to table later
		$this->setUserId($newId);

		//Save the VM user stuff
		if(!$this->saveUserData($data)){
			$this->setError(JText::_('COM_VIRTUEMART_NOT_ABLE_TO_SAVE_USER_DATA')  );
			JError::raiseWarning('', JText::_('COM_VIRTUEMART_RAISEWARNING_NOT_ABLE_TO_SAVE_USER_DATA'));
		}

		if (!self::storeAddress($data)) {
			$this->setError(Jtext::_('COM_VIRTUEMART_NOT_ABLE_TO_SAVE_USERINFO_DATA'));
		}

		$this ->storeVendorData($data);

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
	 * As long we do not have the silent registration, an anonymous does not get registered. It is enough to send the virtuemart_order_id
	 * with the email. The order is saved with all information in an extra table, so there is
	 * no need for a silent registration. We may think about if we actually need/want the feature silent registration
	 * The information of anonymous is stored in the order table and has nothing todo with the usermodel!
	 *
	 * @author Max Milbers
	 * @author Oscar van Eijk
	 * return boolean
	 */
	public function saveUserData($data){

		if(empty($this->_id)){
			echo 'This is a notice for developers, you used this function for an anonymous user, but it is only designed for already registered ones';
		}

		JPluginHelper::importPlugin('vmextended');
		$dispatcher = JDispatcher::getInstance();
  		$plg_datas = $dispatcher->trigger('plgVmOnUserStore',$data);
		foreach($plg_datas as $plg_data){
			$data = array_merge($plg_data);
		}

		if(empty($data['customer_number'])){
			//if(!class_exists('vmUserPlugin')) require(JPATH_VM_SITE.DS.'helpers'.DS.'vmuserplugin.php');
  			///if(!$returnValues){
				$data['customer_number'] = md5($data['username']);
			//}
		} else {
			if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');
			if(!Permissions::getInstance()->check("admin,storeadmin")) {
				unset($data['customer_number']);
			}
		}

		$usertable = $this->getTable('vmusers');

		$app = JFactory::getApplication();
		if($app->isSite()){
			unset($data['user_is_vendor']);
			unset($data['virtuemart_vendor_id']);

			$alreadyStoredUserData = $usertable->load($this->_id);
			$data['user_is_vendor'] = $alreadyStoredUserData->user_is_vendor;
			$data['virtuemart_vendor_id'] = $alreadyStoredUserData->virtuemart_vendor_id;

		}

		$vmusersData = $usertable -> bindChecknStore($data);
		$errors = $usertable->getErrors();
		foreach($errors as $error){
			$this->setError($error);
		}

		if(empty($data['virtuemart_shoppergroup_id'])){
			if(!class_exists('VirtueMartModelShopperGroup')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'shoppergroup.php');
			$shoppergroupmodel = new VirtueMartModelShopperGroup();
			$defaultShopperGroup = $shoppergroupmodel->getDefault();
			$data['virtuemart_shoppergroup_id'] = $defaultShopperGroup->virtuemart_shoppergroup_id;
		}

		// Bind the form fields to the auth_user_group table
		$shoppergroupData = array('virtuemart_user_id'=>$this->_id,'virtuemart_shoppergroup_id'=>$data['virtuemart_shoppergroup_id']);
		$user_shoppergroups_table = $this->getTable('vmuser_shoppergroups');

		$shoppergroupData = $user_shoppergroups_table -> bindChecknStore($shoppergroupData);
		$errors = $user_shoppergroups_table->getErrors();
		foreach($errors as $error){
			$this->setError($error);
		}

  		$plg_datas = $dispatcher->trigger('plgVmAfterUserStore',$data);
		foreach($plg_datas as $plg_data){
			$data = array_merge($plg_data);
		}
		return $data;
	}

	public function storeVendorData($data){

		if($data['user_is_vendor']){

			//	$data['virtuemart_vendor_id'] = $data['my_virtuemart_vendor_id'];
			if(!class_exists('VirtueMartModelVendor')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'vendor.php');
			$vendorModel = new VirtueMartModelVendor();

			//TODO Attention this is set now to virtuemart_vendor_id=1, because using a vendor with different id then 1 is not completly supported and can lead to bugs
			//So we disable the possibility to store vendors not with virtuemart_vendor_id = 1
			$data['virtuemart_vendor_id'] = 1;
			$vendorModel->setId($data['virtuemart_vendor_id']);

			if (!$vendorModel->store($data)) {
				$this->setError($vendorModel->getError());

				return false;
			}
			else{
				//Update xref Table
				$virtuemart_vendor_id = $vendorModel->getId();
				if($virtuemart_vendor_id!=$data['virtuemart_vendor_id']){

					$app = JFactory::getApplication();
					$app ->enqueueMessage('Developer notice, tried to update vendor xref should not appear in singlestore');

					//update user table
					$usertable = $this->getTable('vmusers');
					$vendorsUserData =$usertable->load($this->_id);
					$vendorsUserData->virtuemart_vendor_id = $virtuemart_vendor_id;
					//$vmusersData = array('virtuemart_user_id'=>$data['virtuemart_user_id'],'user_is_vendor'=>1,'virtuemart_vendor_id'=>$virtuemart_vendor_id,'customer_number'=>$data['customer_number'],'perms'=>$data['perms']);

					if (!$usertable->bindChecknStore($vendorsUserData)){
						$this->setError($usertable->getError());
						return false;
					}
				}
			}
		}
		return true;
	}

	/**
	 * Take a data array and save any address info found in the array.
	 *
	 * @author unknown, oscar, max milbers
	 * @param array $data (Posted) user data
	 * @param sting $_table Table name to write to, null (default) not to write to the database
	 * @param boolean $_cart Attention, this was deleted, the address to cart is now done in the controller (True to write to the session (cart))
	 * @return boolean True if the save was successful, false otherwise.
	 */
	function storeAddress($data){

		if(empty($data['address_type'])) return false;

		if($data['address_type']=='ST'){
			// Check for fields with the the 'shipto_' prefix; that means a (new) shipto address.
			$_shipto = array();
			$_pattern = '/^shipto_/';
			foreach ($data as $_k => $_v) {
				if (preg_match($_pattern, $_k)) {
					$_new = preg_replace($_pattern, '', $_k);
					$data[$_new] = $_v;
				}
			}
		}

		$userfielddata = self::_prepareUserFields($data, $data['address_type']);
		$userinfo   = $this->getTable('userinfos');
    	if (!$userinfo->bindChecknStore($userfielddata)) {
			$this->setError($userinfo->getError());
		}
		return $userinfo->virtuemart_userinfo_id;
	}

	function _prepareUserFields($data, $type)
	{
		if(!class_exists('VirtueMartModelUserfields')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'userfields.php' );
		$userFieldsModel = new VirtueMartModelUserfields();

		if ($type == 'ST') {
			$prepareUserFields = $userFieldsModel->getUserFields(
									 'shipping'
									, array() // Default toggles
			);
		} else { // BT
				// The user is not logged in (anonymous), so we need tome extra fields
				$prepareUserFields = $userFieldsModel->getUserFields(
										 'account'
										, array() // Default toggles
										, array('delimiter_userinfo', 'name', 'username', 'password', 'password2', 'user_is_vendor') // Skips
				);

		}
		// Format the data
		foreach ($prepareUserFields as $_fld) {
			if(empty($data[$_fld->name])) $data[$_fld->name] = '';
			$data[$_fld->name] = $userFieldsModel->prepareFieldDataSave($_fld->type, $_fld->name, $data[$_fld->name],$data);
		}

		return $data;
	}

	/**
	 * This should store the userdata given in userfields
	 *
	 * @author Max Milbers
	 */
	function storeUserDataByFields($data,$type, $toggles, $skips){

		if(!class_exists('VirtueMartModelUserfields')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'userfields.php' );
        $userFieldsModel = new VirtueMartModelUserfields();

		$prepareUserFields = $userFieldsModel->getUserFields(
                            $type,
                            $toggles,
                            $skips
            );

        $address = array();

		// Format the data
		foreach ($prepareUserFields as $_fld) {
			if(empty($data[$_fld->name])) $data[$_fld->name] = '';
			$data[$_fld->name] = $userFieldsModel->prepareFieldDataSave($_fld->type, $_fld->name, $data[$_fld->name],$data);
		}

		$this->store($data);

		return true;

	}
	 /**
	  * This uses the shopfunctionsF::renderAndSendVmMail function, which uses a controller and task to render the content
	  * and sents it then.
	  *
	  * @deprecated: Sends a standard registration email.
	  *
	  * @author Oscar van Eijk
	  * @author Max Milbers
	  * @author Christopher Roussel
	  */
	 function sendRegistrationEmail($user){
		if(!class_exists('shopFunctionsF')) require(JPATH_VM_SITE.DS.'helpers'.DS.'shopfunctionsf.php');
		$vars = array('user' => $user);

		// Send registration confirmation mail
		$password = JRequest::getString('password', '', 'post', JREQUEST_ALLOWRAW);
		$password = preg_replace('/[\x00-\x1F\x7F]/', '', $password); //Disallow control chars in the email
		$vars['password'] = $password;

	 	// If user activation is turned on, we need to set the activation information
	 	$usersConfig = JComponentHelper::getParams('com_users');
		$useractivation = $usersConfig->get('useractivation');
		if ($useractivation == '1') {
			jimport('joomla.user.helper');
			$activationLink = 'index.php?option=com_user&task=activate&activation='.$user->get('activation');
			$vars['activationLink'] = $activationLink;
		}

		shopFunctionsF::renderMail('user', $user->get('email'), $vars);
	 }

	 /**
	  * Delete all record ids selected
	  *
	  * @return boolean True is the remove was successful, false otherwise.
	  */
	 function remove($userIds)
	 {
	 	$userInfo = $this->getTable('userinfos');
	 	$vm_shoppergroup_xref = $this->getTable('user_shopper_group_xref');
		$vmusers = $this->getTable('vmusers');
	 	$_status = true;
	 	foreach($userIds as $userId) {
	 		if ($this->getSuperAdminCount() <= 1) {
	 			// Prevent deletion of the only Super Admin
	 			$_u = JUser::getInstance($userId);
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
			if (!$vmusers->delete($userId)) {
	 			$this->setError($vmusers->getError()); // Signal but continue
	 			$_status = false;
	 			continue;
	 		}
	 		$_JUser = JUser::getInstance($userId);
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
			$this->_total = $this->_getListCount($query);
			return $this->_data;
	 }


	 /**
	  * Retrieve a single address for a user
	  *
	  *  @param $_uid int User ID
	  *  @param $_virtuemart_userinfo_id string Optional User Info ID
	  *  @param $_type string, addess- type, ST (ShipTo, default) or BT (BillTo). Empty string to ignore
	  */
	 function getUserAddressList($_uid = 0, $_type = 'ST',$_virtuemart_userinfo_id = -1)
	 {
	 	$_q = 'SELECT * FROM #__virtuemart_userinfos '
			. " WHERE virtuemart_user_id='" . (($_uid==0)?$this->_id:(int)$_uid) . "' ";
			if ($_type !== '') {
				$_q .= ' AND address_type="'.$_type.'"';
			}
			if ($_virtuemart_userinfo_id !== -1) {
				$_q .= ' AND virtuemart_userinfo_id="'.(int)$_virtuemart_userinfo_id.'"';
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
	 	$_q = "SELECT `customer_number` FROM `#__virtuemart_vmusers` "
			."WHERE `virtuemart_user_id`='" . (($_id==0)?$this->_id:(int)$_id) . "' ";
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
	 	if ($search = JRequest::getWord('search', false)) {
	 		$search = '"%' . $this->_db->getEscaped( $search, true ) . '%"' ;
			//$search = $this->_db->Quote($search, false);

	 		$where = ' WHERE `name` LIKE '.$search.' OR `username` LIKE ' .$search;
	 		return ($where);
	 	}
	 	return ('');
	 }


	 /**
	  * Build the query to list all Users
	  *
	  * @return string SQL query statement
	  */
	 function _getListQuery (){

	 	// Used tables #__virtuemart_vmusers, #__virtuemart_userinfos, #__vm_user_perm_groups, #__virtuemart_vmuser_shoppergroups, #__virtuemart_vendors
	 	$query = 'SELECT DISTINCT ju.id AS id
			, ju.name AS name
			, ju.username AS username
			, vmu.user_is_vendor AS is_vendor
			, vmu.perms AS perms
			, ju.usertype AS usertype
			, IFNULL(sg.shopper_group_name, "") AS shopper_group_name
			FROM #__users AS ju
			LEFT JOIN #__virtuemart_vmusers AS vmu ON ju.id = vmu.virtuemart_user_id
			LEFT JOIN #__virtuemart_vmuser_shoppergroups AS vx ON ju.id = vx.virtuemart_user_id
			LEFT JOIN #__virtuemart_shoppergroups AS sg ON vx.virtuemart_shoppergroup_id = sg.virtuemart_shoppergroup_id ';
		$query .= $this->_getFilter();
		$query .= $this->_getOrdering('id') ;

		return ($query);
	 }


	 /**
	  * Take a list of userIds and check if they all have a record in #__virtuemart_userinfos
	  *
	  * TODO place this to the tools
	  * @author Oscar van Eijk
	  * @param $_ids Array with userIds to check (uId, uId, ...)
	  * @return array with invalid users (userId => userName, ...)
	  */
/*	 function validateUsers ($_ids = array())
	 {
	 	if (count($_ids) == 0) {
	 		return array();
	 	}

		jimport( 'joomla.utilities.arrayhelper' );
		JArrayHelper::toInteger($_ids);

	 	$_missing = $this->_getList('SELECT j.username AS uname '
			. ',      j.id       AS uid '
			. 'FROM `#__users` j '
			. 'WHERE j.id IN (' . join(',', $_ids) . ') '
			. 'AND NOT EXISTS ('
			. 'SELECT virtuemart_user_id FROM `#__virtuemart_userinfos` v '
			. 'WHERE v.virtuemart_user_id = j.id'
			. ')'
			);
			$_missingUsers = array();
			foreach ($_missing as $_m) {
				$_missingUsers[$_m->uid] = $_m->uname;
			}
			return $_missingUsers;
	 }
*/

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
		//Ugly thing, produces Select_full_join
	 	$query = 'SELECT `node`.`' . $name . $as . ', CONCAT(REPEAT("&nbsp;&nbsp;&nbsp;", (COUNT(`parent`.`' . $name . '`) - 1)), `node`.`' . $name . '`) AS `text` ';
	 	$query .= 'FROM `' . $table . '` AS node, `' . $table . '` AS parent ';
	 	$query .= 'WHERE `node`.`lft` BETWEEN `parent`.`lft` AND `parent`.`rgt` ';
	 	$query .= $and;
	 	$query .= 'GROUP BY `node`.`' . $name . '` ';
	 	$query .= 'ORDER BY `node`.`lft`';

	 	$this->_db->setQuery($query);
		//$app = JFactory::getApplication();
		//$app -> enqueueMessage($this->_db->getQuery());
	 	return $this->_db->loadObjectList();
	 }
}


//No Closing tag
