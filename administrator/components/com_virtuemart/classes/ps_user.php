<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id$
* @package VirtueMart
* @subpackage classes
* @copyright Copyright (C) 2004-2008 soeren - 2009 VirtueMart Team - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/

class ps_user {
	
	/**
	 * Validates the input parameters onBeforeUserAddUpdate
	 *
	 * @author sources by many other rewritten by Max Milbers
	 * @param array $d
	 * @return boolean
	 */
	function validate_addUpdateUser(&$d) {
		global $my, $perm, $vmLogger, $hVendor;
		$db = new ps_DB();
		$valid = true;
		$missing = "";

		
		$requiredFields = ps_userfield::getUserFields( 'registration', true );
		
		$skipFields = array( 'username', 'password', 'password2', 'email', 'agreed');
		
		foreach( $requiredFields as $field )  {
			if( in_array( $field->name, $skipFields )) {
				continue;
			}
			switch( $field->type ) {
				case 'age_verification':
					// The Age Verification here is just a simple check if the selected date
					// is a birthday older than the minimum age (default: 18)
					$d[$field->name] = vmRequest::getInt('birthday_selector_year')
															.'-'.vmRequest::getInt('birthday_selector_month')
															.'-'.vmRequest::getInt('birthday_selector_day');
					
					break;
				default:
					if( empty( $d[$field->name]) && $field->sys == 1 ) {
						$valid = false;
						$fieldtitle = $field->title;
						$fieldtitle = JText::_($fieldtitle);
						$vmLogger->err( sprintf(JText::_('VM_USER_ERR_MISSINGVALUE'), $fieldtitle) );
					}
					break;
			}
		}
		
		if ( empty($d['email']) ) {
			$email = $hVendor->get_juser_email_by_user_id($d['id']);
			if(empty($email)){
				$vmLogger->err( 'You must enter an email address for the contact.');
				return false;			
			}else {
				$d['email'] = $email;
			}
		}
//		if (!vmValidateEmail($d['email'])) {
		$valid = preg_match( '/^[\w\.\-]+@\w+[\w\.\-]*?\.\w{1,4}$/', $d['$email'] );
		if(!$valid){
			$vmLogger->err( 'Please provide a valide email address for the contact. '.$d['email'] );
			return False;
		}
		
		if( empty( $d['country']) ) {
			$vmLogger->err('You must specify a country for this vendor/store');
			return false;
		}

		if( empty($d['perms']) ) {
			$d['perms'] = 'shopper';
//			$vmLogger->warning( JText::_('VM_USER_ERR_GROUP') );
//			$valid = false;
		}
		else {
			if( !$perm->hasHigherPerms( $d['perms'] )) {
				$vmLogger->err( sprintf(JText::_('VM_USER_ADD_ERR_NOPERMS'),$d['perms']) );
				$valid = false;
			}

		}
		return $valid;
	}

	/**
	 * Validates the Input Parameters onBeforeUserAdd
	 * Only for legecy Use directly validate_add( $d )
	 * @param array $d
	 * @return boolean
	 */
	 
	function validate_add(&$d) {
		return ps_user::validate_addUpdateUser( $d );
	}
	
	/**
	 * Validates the Input Parameters onBeforeUserUpdate
	 * Only for legecy Use directly validate_add( $d )
	 * @param array $d
	 * @return boolean
	 */
	function validate_update(&$d) {
		return ps_user::validate_addUpdateUser( $d );
	}

	/**
	 * Validates the Input Parameters onBeforeUserDelete
	 *
	 * @param int $id
	 * @return boolean
	 */
	function validate_delete( $id ) {
		global $my, $vmLogger, $perm;
		$auth = $_SESSION['auth'];
		$valid = true;

		if( empty( $id ) ) {
			$vmLogger->err( JText::_('VM_USER_DELETE_SELECT') );
			return false;
		}
		$db = new ps_DB();
		$q = 'SELECT user_id, perms FROM #__{vm}_user_info WHERE user_id='.(int)$id;
		$db->query( $q );

		// Only check VirtueMart users - the user may be only a CMS user		
		if( $db->num_rows() > 0 ) {
			$perms = $db->f('perms');

			if( !$perm->hasHigherPerms( $perms ) ) {
				$vmLogger->err( sprintf(JText::_('VM_USER_DELETE_ERR_NOPERMS'),$perms) );
				$valid = false;
			}

			if( $id == $my->id) {
				$vmLogger->err( JText::_('VM_USER_DELETE_ERR_YOURSELF') );
				$valid = false;
			}
		}
		
		return $valid;
	}

	/**
	 * Adds a new User to the CMS and VirtueMart
	 * Only for legecy Use directly addUpdateUser
	 * 
	 * @param array $d
	 * @return boolean
	 */
	function add(&$d) {
		return ps_user::addUpdateUser( $d );
	}

	/**
	 * Updates a User Record
	 * Only for legecy Use directly addUpdateUser
	 * 
	 * @param array $d
	 * @return boolean
	 */
	function update(&$d) {
		
		return ps_user::addUpdateUser( $d );
	}
	
	/**
	 * Add/Update a User, user information of shopper or vendor
	 * 
	 * @author Max Milbers
	 * @param array $d
	 * @return boolean
	 */
	function addUpdateUser(&$d) {
		global  $perm, $vmLogger,$hVendor;
		
		$vmLogger->debug( 'addUpdateUser ' );
		
		$db = new ps_DB;
		$timestamp = time();

		require_once( CLASSPATH . 'ps_userfield.php' );
		if (!ps_user::validate_add($d)) {
//			return false;
		}

		// Joomla User Information stuff
		//Test if there is already an userid (admin.user_form)
		if( empty( $d['id'] )) {
			
			//Test if there is a vendor_id instead (store.store_form)
			if( isset( $d['vendor_id'] )) {
				$vendor_id = $d['vendor_id'];
				(int)$uid = $hVendor -> getUserIdByVendorId($vendor_id);
				
				//if there is nothing to find create a new user (should only happen
				// within the admin.user_form
				if( empty( $uid )) {
					$uid = ps_user::saveJoomlaUser($d);
				}
			}
		}else{
			(int)$uid = $d['id'];
		}
		
//		$vmLogger->debug('addUpdateUser saveJoomla $uid: '.$uid);
//		echo('addUpdateUser saveJoomla $uid: '.$uid);
//		JError::raiseNotice('SOME_ERROR_CODE','addUpdateUser saveJoomla $uid: '.$uid);
		// Get all fields which where shown to the user
		$userFields = ps_userfield::getUserFields('account', false, '', true);
		$skipFields = ps_userfield::getSkipFields();
		
		//Test if the user must be added to the user_info table
		$add = true;
		if(!empty($uid)){
			$db->query( 'SELECT `user_id` FROM `#__{vm}_user_info` WHERE user_id="' . $uid . '"' );
	    	$add = !(bool)$db->num_rows();
	  	}
		
		$fields = array();
		
		if($add){
			// Insert billto;		
			$hash_secret = "VirtueMartIsCool";
			
			$fields['user_info_id'] = md5(uniqid( $hash_secret));
			$fields['user_id'] =  $uid;
			$fields['address_type'] =  'BT';
			$fields['address_type_name'] =  '-default-';
			$fields['cdate'] =  $timestamp;
			$fields['mdate'] =  $timestamp;
			$fields['perms'] =  $d['perms'];
	
		}else{
			$fields['mdate'] = time();
			$fields['perms'] = $d['perms'];		
		}

		foreach( $userFields as $userField ) {
			if( !in_array($userField->name, $skipFields )) {
				$fields[$userField->name] = ps_userfield::prepareFieldDataSave( $userField->type, $userField->name, @$d[$userField->name]);
			}
		}
		$fields['shopper_group_id'] = $d['shopper_group_id'];
		
		$uid = ps_user::setUserInfoWithEmail($fields,$uid,"");
		$vmLogger->debug( 'addUpdateUser $uid '.$uid );
		if($add){
			$_REQUEST['id'] = $_REQUEST['user_id'] = $uid;
			$vmLogger->info( JText::_('VM_USER_ADDED') );
			
		}else{
			$vmLogger->info( JText::_('VM_USER_UPDATED') );
		}
	

		// if the user is a vendor update the vendor information too
		if($hVendor->isvendor($uid)) {
			ps_vendor::addUpdateVendor($d, $uid);	
		}

		return $uid;
	}
	
	
		/**
	 * Inserts or Updates the user information
	 * Attention without Validation.
	 * Important use validate_add oder validate_update.
	 * @author Max Milbers (completly rewritten
	 * @param $user_info array like $keyValues = array('email' => $emailvalue, 'last_name' => $lastname);
	 * @param int $user_id
	 * @param $and An 'AND' condition like 'AND column = value'
	 */
	function setUserInfoWithEmail( $user_info, $user_id=0, $and="" ) {
	
		$db = new ps_DB;
		
		//will probably removed later prevents form to overwrite existing data
		//Unsetting a user information is not allowed, users should write in this case a dummy
		$user_info = array_filter($user_info); 

//		for ($x = 0; $x < sizeof($user_info); ++$x){
//			$GLOBALS['vmLogger']->info('key: '.key($user_info).'   value: '.current($user_info).'');
//			next($user_info);
//		}

		//Test if shopper already exists
		$add = true;
		if(!empty($user_id)){
			$db->query( 'SELECT `user_id` FROM `#__{vm}_user_info` WHERE user_id="' . $user_id . '"' );
	    	$add = !(bool)$db->num_rows();
	  	}
		
		//This might be removed later, if all stuff is working by Max Milbers
		if(array_key_exists('country',$user_info)){
			if(!is_numeric($user_info['country'])){
				$db->query( 'SELECT `country_id` FROM `#__{vm}_country` WHERE (`country_3_code`="' . $user_info['country'] . '" OR `country_2_code`= "'. $user_info['country'] .'" )' );	
//				if($db->num_rows >0){
					JError::raiseNotice('SOME_ERROR_CODE','setUserInfoWithEmail replace country '.$user_info['country'].' with id '.$db->f('country_id'));
					$user_info['country'] = $db->f('country_id');
//				}
			}
	
		}
		//This might be removed later, if all stuff is working by Max Milbers
		if(array_key_exists('state',$user_info)){
			if(!is_numeric($user_info['state'])){
			$db->query( 'SELECT `state_id` FROM `#__{vm}_state` WHERE (`state_3_code`="' . $user_info['state'] . '"  OR `state_2_code`= "'. $user_info['state'] .'" )' );	
//				if($db->num_rows >0){
					JError::raiseNotice('SOME_ERROR_CODE','setUserInfoWithEmail replace state '.$user_info['state'].' with id '.$db->f('state_id'));
					$user_info['state'] = $db->f('state_id');
									
//				}				
			}

		}
		
		//Insert/Update mail
		if(array_key_exists('email',$user_info)){					
			if(!empty($user_id)){ // UPDATES EXISTING USER
				//Test if user exists in Joomla table
				$where =  'WHERE `id`="'.$user_id.'"';
				$q = 'SELECT `id` FROM #__users '.$where;
				
				$db->query($q);
				if($db->f('id')>0){
					$emailvalue = $user_info['email'];
					$keyValues = array('email' => $emailvalue);
					$db->buildQuery( 'UPDATE', '#__users', $keyValues, $where);
					if( $db->query() === false ) {
						JError::raiseError('SOME_ERROR_CODE','setUserInfoWithEmail UPDATE email failed for user_id '.$user_id);
						return false;
					}
				}else{		//No joomla user exists					
					JError::raiseError('SOME_ERROR_CODE','setUserInfoWithEmail VirtuemartUser exist but not Joomla; THIS IS NOT SUPPOSED TO HAPPEN no joomla user found NEW user_id '.$user_id);
				}
			}else{ 
				JError::raiseError('SOME_ERROR_CODE','setUserInfoWithEmail no Joomla/VM user exists; THIS IS NOT SUPPOSED TO HAPPEN no joomla user found NEW user_id '.$user_id);			
			}
			unset ($user_info['email']);	
		}

		if( $add ) { // INSERT NEW USER/SHOPPER
			$action = 'INSERT';
			$whereAnd = "";
		}else{
			$action = 'UPDATE';
			$whereAnd = 'WHERE `user_id`="'.$user_id.'"'.$and;
		}

		if(array_key_exists('shopper_group_id',$user_info)){
			$fields = array();
			$fields['shopper_group_id'] = $user_info['shopper_group_id'];
			$db->buildQuery( $action, '#__{vm}_shopper_vendor_xref', $fields, "" );
			$worked = $db->query();		
			unset ($user_info['shopper_group_id']);
		}
		
		JError::raiseNotice('SOME_ERROR_CODE','setUserInfoWithEmail  '.$action. ' $user_id '.$user_id);
		$db->buildQuery( $action, '#__{vm}_user_info', $user_info, $whereAnd );
		$worked = $db->query();
		if( $worked === false ) {
			JError::raiseError('SOME_ERROR_CODE','setUserInfoWithEmail '.$action.' set user_info failed for user_id '.$user_id);
			return false;
		}else{
			if( $add ) {
				//If it would be possible to get here the last inserted ID,
				//it would be possible to add vendor  directly in the first useradd
				//So long the user must exist before making him to a vendor. by Max Milbers
				$user_id = $db->last_insert_id();
				return $user_id;
			} else{
				return $user_id;
			}
		}
	}


	/**************************************************************************
	* name: delete()
	* created by:
	* description:
	* parameters:
	* returns:
	**************************************************************************/
	function delete(&$d) {
		$db = new ps_DB;
		$hVendor_id = 1;

		if( !is_array( $d['user_id'] )) {
			$d['user_id'] = array( $d['user_id'] );
		}

		foreach( $d['user_id'] as $user ) {
			if( !$this->validate_delete( $user ) ) {
				return false;
			}
			
			$user = (int) $user;
			
			// remove the CMS user
			if( !$this->removeUsers( $user ) ) {
				return false;
			}
			
			// Delete ALL user_info entries (billing and shipping addresses)
			$q  = "DELETE FROM #__{vm}_user_info WHERE user_id=" . $user;
			$db->query($q);

			$q = "DELETE FROM #__{vm}_shopper_vendor_xref where user_id=$user AND vendor_id=$hVendor_id";
			$db->query($q);
		}

		return True;
	}

	function saveJoomlaUser(&$d = array()){
		global $vmLogger;
//		if( vmIsJoomla( '1.5', '>=' ) ) {
//			$vmLogger->info( 'ps_user savej15' );
			$user_id = ps_user::save();
			if( empty($user_id) ) {
				return false;
			}
//		} else {
//			$vmLogger->info( 'ps_user savej1' );
//			$user_id = ps_user::saveUser( $d );
//			if( empty($user_id) ) {
//				return false;
//			}
//		}
		return $user_id;
	}
	
	
	/**
        * Function to save User Information
        * into Joomla
        */
	function saveUser( &$d ) {
		global $database, $my, $_VERSION;
		global $mosConfig_live_site, $mosConfig_mailfrom, $mosConfig_fromname, $mosConfig_sitename;

//		$aro_id = 'aro_id';
//		$group_id = 'group_id';
//		// Column names have changed since J! 1.5
//		if( vmIsJoomla('1.5', '>=')) {
			$aro_id = 'id';
			$group_id = 'id';
//		}

		$row = new mosUser( $database );
		if (!$row->bind( $_POST )) {
			echo "<script type=\"text/javascript\">alert('".vmHtmlEntityDecode($row->getError())."');</script>\n";
		}

		$isNew 	= !$row->id;
		$pwd 	= '';

		// MD5 hash convert passwords
		if ($isNew) {
			// new user stuff
			if ($row->password == '') {
				$pwd = vmGenRandomPassword();
				$row->password = md5( $pwd );
			} else {
				$pwd = $row->password;
				$row->password = md5( $row->password );
			}
			$row->registerDate = date( 'Y-m-d H:i:s' );
		} else {
			// existing user stuff
			if ($row->password == '') {
				// password set to null if empty
				$row->password = null;
			} else {
				if( !empty( $_POST['password'] )) {
					if( $row->password != @$_POST['password2'] ) {
						$d['error'] = vmHtmlEntityDecode(JText::_('REGWARN_VPASS2',false));
						return false;
					}
				}
				$row->password = md5( $row->password );
			}
		}

		// save usertype to usetype column
		$query = "SELECT name"
		. "\n FROM #__core_acl_aro_groups"
		. "\n WHERE `$group_id` = $row->gid"
		;
		$database->setQuery( $query );
		$usertype = $database->loadResult();
		$row->usertype = $usertype;

		// save params
		$params = JRequest::getVar( 'params', '' );
		if (is_array( $params )) {
			$txt = array();
			foreach ( $params as $k=>$v) {
				$txt[] = "$k=$v";
			}
			$row->params = implode( "\n", $txt );
		}

		if (!$row->check()) {
			echo "<script type=\"text/javascript\"> alert('".vmHtmlEntityDecode($row->getError())."');</script>\n";
			return false;
		}
		if (!$row->store()) {
			echo "<script type=\"text/javascript\"> alert('".vmHtmlEntityDecode($row->getError())."');</script>\n";
			return false;
		}
		if ( $isNew ) {
			$newUserId = $row->id;
		}
		else
		$newUserId = false;

		$row->checkin();

		$_SESSION['session_user_params']= $row->params;

		// update the ACL
		if ( !$isNew ) {
			$query = "SELECT `$aro_id`"
			. "\n FROM #__core_acl_aro"
			. "\n WHERE value = '$row->id'"
			;
			$database->setQuery( $query );
			$aro_id = $database->loadResult();

			$query = "UPDATE #__core_acl_groups_aro_map"
			. "\n SET group_id = $row->gid"
			. "\n WHERE aro_id = $aro_id"
			;
			$database->setQuery( $query );
			$database->query() or die( $database->stderr() );
		}

		// for new users, email username and password
		if ($isNew) {
			// Send the notification emails
			$name = $row->name;
			$email = $row->email;
			$username = $row->username;
			$password = $pwd;
			$this->_sendMail( $name, $email, $username, $password );
		}
		
		return $newUserId;
	}
	
	/**
	 * Saves a user into Joomla! 1.5 
	 *
	 * @return int An integer user_id if the user was saved successfully, false if not
	 */

	 
	function save()
	{
		global $mainframe, $vmLogger;

		$option = JRequest::getCmd( 'option');

		// Initialize some variables
		$db			= & JFactory::getDBO();
		$me			= & JFactory::getUser();
		$MailFrom	= $mainframe->getCfg('mailfrom');
		$FromName	= $mainframe->getCfg('fromname');
		$SiteName	= $mainframe->getCfg('sitename');

 		// Create a new JUser object
		$user = new JUser(JRequest::getVar( 'id', 0, 'post', 'int'));
		$original_gid = $user->get('gid');

		$post = JRequest::get('post');
		$post['username']	= JRequest::getVar('username', '', 'post', 'username');
		$post['password']	= JRequest::getVar('password', '', 'post', 'string', JREQUEST_ALLOWRAW);
		$post['password2']	= JRequest::getVar('password2', '', 'post', 'string', JREQUEST_ALLOWRAW);

		if (!$user->bind($post))
		{
			echo "<script type=\"text/javascript\"> alert('".vmHtmlEntityDecode( $user->getError() )."');</script>\n";
			return false;
		}

		// Are we dealing with a new user which we need to create?
		$isNew 	= ($user->get('id') < 1);
		if (!$isNew)
		{
			// if group has been changed and where original group was a Super Admin
			if ( $user->get('gid') != $original_gid && $original_gid == 25 )
			{
				// count number of active super admins
				$query = 'SELECT COUNT( id )'
					. ' FROM #__users'
					. ' WHERE gid = 25'
					. ' AND block = 0'
				;
				$db->setQuery( $query );
				$count = $db->loadResult();

				if ( $count <= 1 )
				{
					// disallow change if only one Super Admin exists
					$vmLogger->err( JText::_('VM_USER_ERR_ONLYSUPERADMIN') );
					return false;
				}
			}
		}

		/*
	 	 * Lets save the JUser object
	 	 */
		if (!$user->save())
		{
			echo "<script type=\"text/javascript\"> alert('".vmHtmlEntityDecode( $user->getError() )."');</script>\n";
			return false;
		}

		// For new users, email username and password
		if ($isNew)
		{
			$name = $user->get( 'name' );
			$email = $user->get( 'email' );
			$username = $user->get( 'username' );
			$password = $user->password_clear;
		 	$this->_sendMail( $name, $email, $username, $password );
		}
	 	
		// Capture the new user id
		if( $isNew ) {
			$newUserId = $user->get('id');
		} else {
			$newUserId = false;
		}

		return $newUserId;
	}
	
	/**
	 * Sends new/updated user notification emails 
	 *
	 * @param string $name - The name of the newly created/updated user
	 * @param string $email - The email address of the newly created/updated user
	 * @param string $username - The username of the newly created/updated user
	 * @param string $password - The plain text password of the newly created/updated user
	 */
	function _sendMail( $name, $email, $username, $password ) {
		global $database;
		global $my, $mosConfig_mailfrom, $mosConfig_fromname, $mosConfig_sitename, $mosConfig_live_site;
		
		$query = "SELECT email"
			. "\n FROM #__users"
			. "\n WHERE id = $my->id"
			;
		$database->setQuery( $query );
		$adminEmail = $database->loadResult();
		
		$subject = JText::_('NEW_USER_MESSAGE_SUBJECT',false);
		$message = sprintf ( JText::_('NEW_USER_MESSAGE',false), $name, $mosConfig_sitename, $mosConfig_live_site, $username, $password );
		
		if ($mosConfig_mailfrom != "" && $mosConfig_fromname != "") {
			$adminName 	= $mosConfig_fromname;
			$adminEmail = $mosConfig_mailfrom;
		} else {
			$query = "SELECT name, email"
				. "\n FROM #__users"
				// administrator
				. "\n WHERE gid = 25"
				;
			$database->setQuery( $query );
			$admins = $database->loadObjectList();
			$admin 		= $admins[0];
			$adminName 	= $admin->name;
			$adminEmail = $admin->email;
		}

		vmMail( $adminEmail, $adminName, $email, $subject, $message );
	}


	/**
	* Function to remove a user from Joomla
	*/
	function removeUsers( $cid ) {
		global $database, $acl, $my, $vmLogger;

		if (!is_array( $cid ) ) {
			$cid = array( $cid );
		}

		//TODO vendorrelationships are not deleted
		if ( count( $cid ) ) {
			$obj = new mosUser( $database );
			foreach ($cid as $id) {
				// check for a super admin ... can't delete them
				//TODO: Find out the group name of the User to be deleted
//				$groups 	= $acl->get_object_groups( 'users', $id, 'ARO' );
//				$this_group = strtolower( $acl->get_group_name( $groups[0], 'ARO' ) );
				$obj->load( $id );
				$this_group = strtolower( $obj->get('usertype') );
				if ( $this_group == 'super administrator' ) {
					$vmLogger->err( JText::_('VM_USER_DELETE_ERR_SUPERADMIN') );
					return false;
				} else if ( $id == $my->id ){
					$vmLogger->err( JText::_('VM_USER_DELETE_ERR_YOURSELF') );
					return false;
				} else if ( ( $this_group == 'administrator' ) && ( $my->gid == 24 ) ){
					$vmLogger->err( JText::_('VM_USER_DELETE_ERR_ADMIN') );
					return false;
				} else {
					$obj->delete( $id );
					$err = $obj->getError();
					if( $err ) {
						$vmLogger->err( $err );
						return false;
					}
					
					return true;
				}
			}
		}
	}
	
	/**
	 * Gets the user details, it joins 
	 * #__users ju, #__{vm}_user_info u, #__{vm}_country c and #__{vm}_state s
	 * @author Max Milbers
	 * @param int $user_id user_id of the user same ID for joomla and VM
	 * @param array $fields Columns to get
	 * @param String $orderby should be ordered by $field
	 * @param String $and this is for an additional AND condition
	 * @param Boolean $nextRecord if the nextRecord should give back or only the queryResult
	 */
	 
	function get_user_details( $user_id=0, $fields=array(), $orderby="", $and="", $nextRecord=true ) {

		$db = new ps_DB();		
		if( empty( $fields )) {
			$selector = '*';
		}else {
			$selector = implode(",",$fields);
		}
		$q = "SELECT ".$selector." FROM (#__{vm}_user_info u , #__users ju) " .
//		"LEFT JOIN #__{vm}_country c ON (u.country = c.country_2_code OR u.country = c.country_3_code) ".		
//		"LEFT JOIN #__{vm}_state s ON (u.state = s.state_2_code AND s.country_id = c.country_id) ";
		"LEFT JOIN #__{vm}_country c ON (u.country = c.country_id) ".		
		"LEFT JOIN #__{vm}_state s ON (s.country_id=c.country_id) ";
		
		if(!empty($user_id)){
			$q .= "WHERE u.user_id = ".(int)$user_id." AND ju.id = ".(int)$user_id." ";
		}
		if(!empty($and)){
			$q .= $and." ";
		}
		if(!empty($orderby)){
			$q .= "ORDER BY ".$orderby." ";
		}
//		$GLOBALS['vmLogger']->info('get_user_details query '.$q);				
		$db->query($q);
		if($nextRecord){
			if( ! $db->next_record() ) {
				print "<h1>Invalid query user id: $user_id</h1>" ;
				print "<h2>Query user id: $q</h2>" ;
			return ;
			}else{
				return $db;
			}
		}else{
			return $db;
		}
	}

	
	/**
	 * Logs in a customer
	 *
	 * @param unknown_type $username
	 * @param unknown_type $password
	 */
	function login($username, $password) {
		//not used
	}
	/**
	 * Logs out a customer from the store
	 *
	 */
	function logout($complete_logout=true) {
		global $auth, $sess, $mainframe, $page;
		$auth = array();
		$_SESSION['auth'] = array();
		if( $complete_logout ) {
			$mainframe->logout();
		}
		vmRedirect($sess->url('index.php?page='.HOMEPAGE, true, false));
	}
}

?>
