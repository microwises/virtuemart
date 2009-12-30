<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: ps_shopper.php 1768 2009-05-11 22:24:39Z macallf $
* @package VirtueMart
* @subpackage classes
* @copyright Copyright (C) 2004-2008 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/

/**
*
* The class is meant to manage shopper entries
*/
class ps_shopper {


	/**
	 * Validates the input parameters onBeforeShopperAdd
	 *
	 * @param array $d
	 * @return boolean
	 */
	function validate_add(&$d) {

		global $my, $perm, $vmLogger, $mosConfig_absolute_path, $auth;
		$vmLogger->debug( 'ps_shopper validate_add' );

		$provided_required = true;
		$missing = "";

		require_once( CLASSPATH . 'ps_userfield.php' );
		$registrationFields = ps_userfield::getUserFields( 'registration', false, '', true );

		$skipFields = array();
		if( VM_REGISTRATION_TYPE == 'SILENT_REGISTRATION' || VM_REGISTRATION_TYPE == 'NO_REGISTRATION'
			|| (VM_REGISTRATION_TYPE == 'OPTIONAL_REGISTRATION' && empty($d['register_account']))) {
			$skipFields = array( 'username', 'password', 'password2');
		}
        if ( $my->id > 0 || (VM_REGISTRATION_TYPE != 'NORMAL_REGISTRATION' && VM_REGISTRATION_TYPE != 'OPTIONAL_REGISTRATION')) {
            $skipFields = array( 'username', 'password', 'password2');
        }
		if( $my->id ) {
			$skipFields[] = 'email';
		}
		$d['isValidVATID'] = false;
		foreach( $registrationFields as $field ) {
			/* Special checking for EU VAT ID */
			if ($field->type == 'euvatid') {
				if( $field->required == 0 && empty( $d[$field->name])) {
					break; // Do nothing when the EU VAT ID field was left empty
				}
				if( $field->required == 1 && empty( $d[$field->name])) {
						$provided_required = false;
						$missing .= $field->name . ",";
				}
				// Check the VAT ID against the validation server of the European Union
				$d['isValidVATID'] = vmValidateEUVat( $d[$field->name] );
				if( !$d['isValidVATID'] ) {
					//TODO: Roland - insert your error message here
				}
				if( !$d['isValidVATID'] && $field->required == 1) {
					$provided_required = false;
					$missing .= $field->name . ",";
				}
				$d['__euvatid_field'] = $field;
			}
			else {
				if( $field->required == 0 ) continue;
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
						$params = new vmParameters( $field->params );
						$min_age = $params->get('minimum_age', 18 );
						$min_date = (date('Y') - $min_age).'-'.date('n').'-'.date('j');

						if( $d[$field->name] > $min_date ) {
							// User too young!
							$provided_required = false;
							$missing .= $field->name . ",";
						}
						break;
					case 'captcha':
						if( file_exists($mosConfig_absolute_path.'/administrator/components/com_securityimages/server.php')) {
							include_once( $mosConfig_absolute_path.'/administrator/components/com_securityimages/server.php');
							$packageName = 'securityVMRegistrationCheck';
							$security_refid = vmGet($_POST, $packageName.'_refid');
							$security_try = vmGet($_POST, $packageName.'_try');
							$security_reload = vmGet($_POST, $packageName.'_reload');
							$checkSecurity = checkSecurityImage($security_refid, $security_try );

							if( !$checkSecurity ) {
								$provided_required = false;
								$missing .= $field->name . ",";
							}
						}
						break;

					default:
						if ( empty( $d[$field->name])) {
							$provided_required = false;
							$missing .= $field->name . ",";
						}
						break;
				}
			}
		}

		if (!$provided_required) {
			$_REQUEST['missing'] = $missing;
			return false;
		}

		$d['email'] = vmGet( $d, 'email', $my->email );
		if(isset($d['email'])){
			if (!vmValidateEmail($d["email"])) {
			$vmLogger->err( 'Please provide a valide email address for the registration.' );
			return false;
			}
		}

		$d['perms'] = 'shopper';

		return true;
	}

	/**************************************************************************
	** name: validate_update()
	** created by:
	** description:
	** parameters:
	** returns:
	***************************************************************************/
	function validate_update(&$d) {
		global $my, $perm, $vmLogger, $mosConfig_absolute_path, $auth;
		$vmLogger->err( 'Jetz sinma in validate_update shopper ' );
		if ( $my->id == 0 && $auth['user_id'] == 0 ){
			$vmLogger->err( "Please Login first." );

			return false;
		}
		$db = new ps_DB;

		$provided_required = true;
		$missing = "";

		require_once( CLASSPATH . 'ps_userfield.php' );
		$accountFields = ps_userfield::getUserFields( 'account', false, '', true );

		if( VM_REGISTRATION_TYPE == 'SILENT_REGISTRATION' || VM_REGISTRATION_TYPE == 'NO_REGISTRATION' || (VM_REGISTRATION_TYPE == 'OPTIONAL_REGISTRATION' && empty($d['register_account'] ))) {
			$skipFields = array( 'username', 'password', 'password2');
		}
        if ( $my->id > 0 || (VM_REGISTRATION_TYPE != 'NORMAL_REGISTRATION' && VM_REGISTRATION_TYPE != 'OPTIONAL_REGISTRATION')) {
            $skipFields = array( 'username', 'password', 'password2');
        }
		if( $my->id ) {
			$skipFields[] = 'email';
		}
		foreach( $accountFields as $field )  {
			if( $field->required == 0 ) {
				if( $field->type == 'euvatid' && !empty($d[$field->name])) {}
				else continue;
			}
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
					$params = new vmParameters( $field->params );
					$min_age = $params->get('minimum_age', 18 );
					$min_date = (date('Y') - $min_age).'-'.date('n').'-'.date('j');

					if( $d[$field->name] > $min_date ) {
						// User too young!
						$provided_required = false;
						$missing .= $field->name . ",";
					}
					break;
				case 'captcha':
					if( file_exists($mosConfig_absolute_path.'/administrator/components/com_securityimages/server.php')) {
						include_once( $mosConfig_absolute_path.'/administrator/components/com_securityimages/server.php');
						$packageName = 'securityVMRegistrationCheck';
						$security_refid = vmGet($_POST, $packageName.'_refid');
						$security_try = vmGet($_POST, $packageName.'_try');
						$security_reload = vmGet($_POST, $packageName.'_reload');
						$checkSecurity = checkSecurityImage($security_refid, $security_try );

						if( !$checkSecurity ) {
							$provided_required = false;
							$missing .= $field->name . ",";
						}
					}
					break;

				case 'euvatid':
					if( empty( $d[$field->name])) break; // Do nothing when the EU VAT ID field was left empty

					// Check the VAT ID against the validation server of the European Union
					$d['isValidVATID'] = vmValidateEUVat( $d[$field->name] );
					$d['__euvatid_field'] = $field;

					break; // We don't need to go further in the loop

				default:
					if ( empty( $d[$field->name])) {
						$provided_required = false;
						$missing .= $field->name . ",";
					}
					break;
			}
		}

		if (!$provided_required) {
			$_REQUEST['missing'] = $missing;
			return false;
		}

		$d['email'] = vmGet( $d, 'email', $my->email );
		if (!vmValidateEmail($d["email"])) {
			$vmLogger->err( 'Please provide a valide email address for the registration.' );
			return false;
		}
		$d['perms'] = 'shopper';

		return true;

	}

	/**************************************************************************
	** name: validate_delete()
	** created by:
	** description:
	** parameters:
	** returns:
	***************************************************************************/
	function validate_delete(&$d) {
		global $my;

		if ($my->id == 0){
			$vmLogger->err( "Please Login first." );
			return false;
		}
		if (!$d["user_id"]) {
			$vmLogger->err( "Please select a user to delete." );
			return False;
		}
		else {
			return True;
		}
	}

	/**
	 * Function to add a new Shopper into the Shop and Joomla
	 *
	 * @param array $d
	 * @return boolean
	 */
	function add( &$d ) {
		global $my, $auth, $mainframe, $mosConfig_absolute_path, $sess,
		 $vmLogger, $database, $mosConfig_useractivation;

		$vmLogger->debug( 'ps_shopper add' );

		//TODO must depend on vendor of bill,.. or on vendorS of the bill
		$vendor_id =  1; //$_SESSION["ps_vendor_id"];
		$hash_secret = "VirtueMartIsCool";
		$db = new ps_DB;
		$timestamp = time();

		if (!$this->validate_add($d)) {
			return False;
		}

		if( empty( $my->id ) ) {

			$_POST['name'] = vmGet($d,'first_name','First Name' )." ".vmGet($d,'last_name','Last Name' );
			if( VM_REGISTRATION_TYPE == 'SILENT_REGISTRATION' || VM_REGISTRATION_TYPE == 'NO_REGISTRATION' || (VM_REGISTRATION_TYPE == 'OPTIONAL_REGISTRATION' && empty($d['register_account'] ))) {
				// Silent Registration, Optional Registration with no account wanted and No Registration
				// means we need to create a hidden user

				$username_length = 100;

				$silent_username = substr( str_replace( '-', '_', vmGet($d,'email') ), 0, $username_length );
				$db->query( 'SELECT username FROM `#__users` WHERE username=\''.$silent_username.'\'');
				$i = 0;
				while( $db->next_record()) {
					$silent_username = substr_replace( $silent_username, $i, strlen($silent_username)-1 );
					$db->query( 'SELECT username FROM `#__users` WHERE username=\''.$silent_username.'\'');
					$i++;
				}
				$_POST['username'] = $d['username'] = $silent_username;
				$_POST['password'] = $d['password'] = vmGenRandomPassword();
				$_POST['password2'] = $_POST['password'];
			}

			if( VM_REGISTRATION_TYPE == 'NO_REGISTRATION' || (VM_REGISTRATION_TYPE == 'OPTIONAL_REGISTRATION' && empty($d['register_account'] ) ) ) {
				// If no user shall be registered into the global user table, we just add the registration info into the vm_user_info table
				// Make sure that "dummy" entries for non-existing Joomla! users won't ever have the same user_id as a future Joomla! user
				$db->query( "SELECT MIN(user_id)-1 as uid FROM `#__{vm}_user_info`" );
				$db->next_record();

				// Don't allow a user id of zero
				$uid = ( $db->f('uid') == 0 ) ? -1 : $db->f('uid');

 			} else {
				// Process the CMS registration
				require_once( CLASSPATH . 'ps_user.php' );
//				$uid = ps_user::saveJoomlaUser($d);
//				if( vmIsJoomla( '1.5' ) ) {
//					$uid = ps_user::savej15();
					$uid = $this->register_save();
					if( empty($uid) ) {
						return false;
					}
//				} else {
////					$uid = ps_user::savej10($d);
//					$uid = $this->saveRegistration();
//					if( empty($uid) ) {
//						return false;
//					}
//				}
				$db->query('SELECT `id` FROM `#__users` WHERE `username`="'.$d['username'].'"');
				$db->next_record();
				$uid = $db->f('id');
				$GLOBALS['vmLogger']->info('$uid '.$uid.' und username: '.$d['username'].' $db->f("id"): '.$db->f('id'));
				
 			}
		}
		else {
			$uid = $my->id;
			$d['email'] = $_POST['email'] = $my->email;
			$d['username'] = $_POST['username'] = $my->username;

		}

		// Prevent empty USER ID
		if( empty( $uid )) {
			$vmLogger->crit("Failed to retrieve a valid USER ID when attempting to add a new user");
			return false;
		}

		if( !empty($auth['user_id'])) {
			$db->query( 'SELECT user_id FROM #__{vm}_user_info WHERE user_id='.$auth['user_id'] );
			$db->next_record();

			if( $db->f('user_id')) {
				return $this->update( $d );
			}
		}
		// Get all fields which where shown to the user
		$userFields = ps_userfield::getUserFields('registration', false, '', true );
		$skipFields = ps_userfield::getSkipFields();

		// Insert billto;

		// The first 7 fields are FIX and not built dynamically
		$fields = array( 'user_info_id' => md5(uniqid( $hash_secret)),
						'user_id' => $uid,
						'address_type' => 'BT',
						'address_type_name' => '-default-',
						'cdate' => $timestamp,
						'mdate' => $timestamp,
						'perms' => 'shopper'
						);

		foreach( $userFields as $userField ) {
			if( !in_array($userField->name, $skipFields )) {

				$fields[$userField->name] = ps_userfield::prepareFieldDataSave( $userField->type, $userField->name, vmGet($d, $userField->name, strtoupper($userField->name) ) );

				// Catch a newsletter registration!
				if( stristr( $userField->params, 'newsletter' )) {
					if( !empty($d[$userField->name])) {
						$subscribeTo = new mosParameters( $userField->params );
						$vmLogger->debug( 'Adding the user to the Newsletter.');
					}
				}

			}
		}

		//New function do the same with email of juser
		//Can be used here, because a validation was already done
		require_once( CLASSPATH . 'ps_user.php' );
		ps_user::setUserInfoWithEmail($fields,$uid);


		$d['shopper_group_id'] = '';

		// Get the ID of the shopper group for this customer
		if( $d['isValidVATID'] ) {

			if( trim($d['__euvatid_field']->params) != '' ) {
				$shopper_group = new vmParameters( $d['__euvatid_field']->params );
				$d['shopper_group_id'] = $shopper_group->get('shopper_group_id');
			}
		}
		if( empty($d['shopper_group_id'])) {
			$q =  "SELECT shopper_group_id from #__{vm}_shopper_group WHERE ";
			$q .= "`default`='1' ";

			$db->query($q);
			if (!$db->num_rows()) {  // take the first in the table

				$q =  "SELECT shopper_group_id from #__{vm}_shopper_group";
				$db->query($q);
			}
			$db->next_record();
			$d['shopper_group_id'] = $db->f("shopper_group_id");
		}

		$customer_nr = uniqid( rand() );
		// Insert Shopper -ShopperGroup - Relationship
		$q  = "INSERT INTO #__{vm}_shopper_vendor_xref ";
		$q .= "(user_id,vendor_id,shopper_group_id,customer_number) ";
		$q .= "VALUES ('$uid', '$vendor_id','".$d['shopper_group_id']."', '$customer_nr')";
		$db->query($q);

		// Process the Newsletter subscription
		if( !empty( $subscribeTo ) && strtolower(get_class($subscribeTo))=='mosparameters') {
			switch( $subscribeTo->get('newsletter', 'letterman')) {
				// TODO:
				case 'ccnewsletter':
					$db->query( "INSERT INTO `#__ccnewsletter_subscribers` ( `name`, `email`, `plainText`, `enabled`, `sdate`)
							VALUES('".$d['first_name']." ". $d['last_name']."','".$d['email']."', '0', '1', NOW())" );
				// case 'anjel':
				case 'letterman':
				default:
					if( file_exists($mosConfig_absolute_path.'/components/com_letterman/letterman.php')) {
						$db->query( "INSERT INTO `#__letterman_subscribers` (`user_id`, `subscriber_name`, `subscriber_email`, `confirmed`, `subscribe_date`)
										VALUES('$uid','".$d['first_name']." ". $d['last_name']."','".$d['email']."', '1', NOW())");
					}
			}
		}
		if( VM_REGISTRATION_TYPE == 'NO_REGISTRATION' || (VM_REGISTRATION_TYPE == 'OPTIONAL_REGISTRATION' && empty($d['register_account'] ) ) ) {
			$auth['user_id'] = $uid;
			$auth['username'] = $d['email'];
			$_SESSION['auth'] = $auth;
		}
		elseif( !$my->id && $mosConfig_useractivation == '0') {
			// HANDLE LOGIN
//			if( vmIsJoomla('1.5') ) {
				// Username and password must be passed in an array
				$credentials = array('username'  => vmGet($d,'username'),
													'password' => vmGet($d,'password')
											);
				$mainframe->login( $credentials );
//			}
//			elseif( class_exists('mambocore') || ( vmIsJoomla('1.0.13', '>=', false ) ) ) {
//				// Login for Mambo 4.6.x and Joomla >= 1.0.13
//				$mainframe->login($d['username'], $d['password'] );
//			}
//			else {
//				// Login for Joomla < 1.0.13 (and Mambo 4.5.2.3)
//				$mainframe->login($d['username'], md5( $d['password'] ));
//			}
//			// Redirect to the Checkout Page if the cart is not empty
//			//if( !empty( $_SESSION['cart']['idx'])) {
//				$redirect_to_page = $d['page'];//checkout.index';
//			//} else {
//			//	$redirect_to_page = HOMEPAGE;
//			//}
			vmRedirect( $sess->url( 'index.php?page='.$redirect_to_page, false, false ), JText::_('REG_COMPLETE') );
		}

		if( !empty($my->id) || !empty($auth['user_id']) ) {
			vmRedirect( $sess->url( 'index.php?page='.$d['page'], false, false ) );
		}
		else {
			//$GLOBALS['page'] = 'shop.cart';
			$msg = strip_tags( JText::_('REG_COMPLETE_ACTIVATE',false) );
			$vmLogger->info( $msg );
		}

		return true;

	}

	/**
	 * The function from com_registration!
	 * Registers a user into Mambo/Joomla
	 *
	 * @return boolean True when the registration process was successful, False when not
	 */
	function saveRegistration() {
		global $database, $acl, $vmLogger, $mosConfig_useractivation,
		$mosConfig_allowUserRegistration, $mosConfig_live_site;

		$vmLogger->err( 'ps_shopper saveRegistration j 1.0' );

		if ($mosConfig_allowUserRegistration=='0') {
			mosNotAuth();
			return false;
		}

		$row = new mosUser( $database );

		if (!$row->bind( $_POST, 'usertype' )) {
			$error = vmHtmlEntityDecode( $row->getError() );
			$vmLogger->err( $error );
			echo "<script type=\"text/javascript\"> alert('". $error. "');</script>\n";
			return false;
		}

		mosMakeHtmlSafe($row);

		$usergroup = 'Registered';
		$row->id = 0;
		$row->usertype = $usergroup;
		$row->gid = $acl->get_group_id( $usergroup, 'ARO' );

		if ($mosConfig_useractivation == '1') {
			$row->activation = md5( vmGenRandomPassword() );
			$row->block = '1';
		}

		if (!$row->check()) {
			$error = vmHtmlEntityDecode( $row->getError() );
			$vmLogger->err( $error );
			echo "<script type=\"text/javascript\"> alert('". $error. "');</script>\n";
			return false;
		}

		$pwd 				= $row->password;
		$row->password 		= md5( $row->password );
		$row->registerDate 	= date('Y-m-d H:i:s');

		if (!$row->store()) {
			$error = vmHtmlEntityDecode( $row->getError() );
			$vmLogger->err( $error );
			echo "<script type=\"text/javascript\"> alert('". $error. "');</script>\n";
			return false;
		}
		$row->checkin();

		$name 		= $row->name;
		$email 		= $row->email;
		$username 	= $row->username;
		$component = vmIsJoomla(1.5) ? 'com_user' : 'com_registration';

		$activation_link = $mosConfig_live_site."/index.php?option=$component&task=activate&activation=".$row->activation;

		// Send the registration email
		$this->_sendMail( $name, $email, $username, $pwd, $activation_link );

		return true;
	}

	/**
	 * Save user registration and notify users and admins if required
	 * for Joomla! 1.5
	 * @return boolean
	 */
	function register_save()
	{
		global $mainframe,$mosConfig_live_site;

		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );

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
			return false;
		}

		// Initialize new usertype setting
		$newUsertype = $usersConfig->get( 'new_usertype' );
		if (!$newUsertype) {
			$newUsertype = 'Registered';
		}

		// Bind the post array to the user object
		$_post_ =& vmRequest::get('post');
		if (!$user->bind( $_post_, 'usertype' )) {
			JError::raiseError( 500, $user->getError());
		}

		// Set some initial user values
		$user->set('id', 0);
		$user->set( 'usertype', $newUsertype );
		$user->set('gid', $authorize->get_group_id( '', $newUsertype, 'ARO' ));

		// TODO: Should this be JDate?
		$user->set('registerDate', date('Y-m-d H:i:s'));

		// If user activation is turned on, we need to set the activation information
		$useractivation = $usersConfig->get( 'useractivation' );
		if ($useractivation == '1')
		{
			jimport('joomla.user.helper');
			$user->set('activation', md5( JUserHelper::genRandomPassword()) );
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

		$name = $user->get('name');
		$email = $user->get('email');
		$username = $user->get('username');
		$component = 'com_user';

		$activation_link = $mosConfig_live_site."/index.php?option=$component&task=activate&activation=".$user->get('activation');
		// Send the registration email
		$this->_sendMail( $name, $email, $username, $password, $activation_link );

		return true;
	}


	/**
	* Function to update a Shopper Entry
	* (uses who have perms='shopper')
	*/
	function update(&$d) {
		global $my, $perm, $sess, $vmLogger, $page;
$vmLogger->err( 'ps_shopper update' );
		$auth = $_SESSION['auth'];

		$vendor_id = 1;
		$db = new ps_DB;

		if ( @$d["user_id"] != $my->id && @$d["user_id"] != $auth['user_id'] && $auth["perms"] != "admin") {
			$vmLogger->crit( "Tricky tricky, but we know about this one." );
			return False;
		}

		require_once(CLASSPATH. 'ps_user.php' );
		if( !empty($d['username'])) {
			$_POST['username'] = $d['username'];
		}
		else {
			$_POST['username'] = $my->username;
		}
		$_POST['name'] = $d['first_name']." ". $d['last_name'];
		$_POST['id'] = $auth["user_id"];
		$_POST['gid'] = $my->gid;


//		$d['error'] = "";

		//I think this is now useless by Max Milbers
//		if ( VM_REGISTRATION_TYPE != 'NO_REGISTRATION' ) {
//			ps_user::saveUser( $d );
//		}

		if( !empty( $d['error']) ) {
			return false;
		}

		if (!$this->validate_update($d)) {
			return false;
		}
		$user_id = $auth["user_id"];

		/* Update Bill To */

		// Get all fields which where shown to the user
		$userFields = ps_userfield::getUserFields( 'account', false, '', true );
		$skip_fields = ps_userfield::getSkipFields();

		$fields = array(
						'mdate' => time()
						);
		foreach( $userFields as $userField ) {
			if( !in_array($userField->name, $skip_fields )) {
				$fields[$userField->name] = ps_userfield::prepareFieldDataSave( $userField->type, $userField->name, vmGet( $d, $userField->name, strtoupper($userField->name) ));
			}
		}
		//Can be used here, because a validation was already done by Max Milbers
		ps_user::setUserInfoWithEmail($fields,$user_id, " AND address_type='BT'");


		// UPDATE #__{vm}_shopper group relationship
		$q = "SELECT shopper_group_id FROM #__{vm}_shopper_vendor_xref ";
		$q .= "WHERE user_id = '".$user_id."'";
		$db->query($q);

		if (!$db->num_rows()) {
			//add

			$shopper_db = new ps_DB;
			// get the default shopper group
			$q =  "SELECT shopper_group_id from #__{vm}_shopper_group WHERE ";
			$q .= "`default`='1'";
			$shopper_db->query($q);
			if (!$shopper_db->num_rows()) {  // when there is no "default", take the first in the table
				$q =  "SELECT shopper_group_id from #__{vm}_shopper_group";
				$shopper_db->query($q);
			}

			$shopper_db->next_record();
			$my_shopper_group_id = $shopper_db->f("shopper_group_id");
			if (empty($d['customer_number'])) {
				$d['customer_number'] = "";
			}

			$q  = "INSERT INTO #__{vm}_shopper_vendor_xref ";
			$q .= "(user_id,vendor_id,shopper_group_id) ";
			$q .= "VALUES ('";
			$q .= $_SESSION['auth']['user_id'] . "','";
			$q .= $vendor_id. "','";
			$q .= $my_shopper_group_id. "')";
			$db->query($q);
		}
		//TODO In this table is stored the information of the userid of the vendor
		//so this must be worked out in a completly other way by Max Milbers
//		$q = "SELECT user_id FROM #__{vm}_auth_user_vendor ";
//		$q .= "WHERE user_id = '".$_SESSION['auth']['user_id']."'";
//		$db->query($q);
//		if (!$db->num_rows()) {
//			// Insert vendor relationship
//			$q = "INSERT INTO #__{vm}_auth_user_vendor (user_id,vendor_id)";
//			$q .= " VALUES ";
//			$q .= "('" . $_SESSION['auth']['user_id'] . "','";
//			$q .= $_SESSION['ps_vendor_id'] . "') ";
//			$db->query($q);
//		}

		return True;
	}

	/**
	* Function to delete a Shopper
	*/
	function delete(&$d) {
		global $my;

		$db = new ps_DB;

		if (!$this->validate_delete($d)) {
			return False;
		}

		// Delete user_info entries
		// and Shipping addresses
		$q = "DELETE FROM #__{vm}_user_info where user_id='" . $d["user_id"] . "'";
		$db->query($q);

		// Delete shopper_vendor_xref entries
		$q = "DELETE FROM #__{vm}_shopper_vendor_xref where user_id='" . $d["user_id"] . "'";
		$db->query($q);

		//TODO In this table is stored the information of the userid of the vendor
		//so this must be worked out in a completly other way by Max Milbers
//		$q = "DELETE FROM #__{vm}_auth_user_vendor where user_id='" . $d["user_id"] . "'";
//		$db->query($q);
		return True;
	}

	/**
	 * Sends new/updated user notification emails
	 *
	 * @param string $name - The name of the newly created/updated user
	 * @param string $email - The email address of the newly created/updated user
	 * @param string $username - The username of the newly created/updated user
	 * @param string $password - The plain text password of the newly created/updated user
	 */
	function _sendMail($name, $email, $username, $pwd, $activation_link='') {
		global $database, $acl;
		global $mosConfig_sitename, $mosConfig_live_site, $mosConfig_useractivation;
		global $mosConfig_mailfrom, $mosConfig_fromname;

		$subject 	= sprintf (JText::_('SEND_SUB',false), $name, $mosConfig_sitename);
		$subject 	= vmHtmlEntityDecode($subject, ENT_QUOTES);
		if ($mosConfig_useractivation=="1"){
			$message = sprintf (JText::_('USEND_MSG_ACTIVATE',false), $name, $mosConfig_sitename, $activation_link, $mosConfig_live_site, $username, $pwd);
		} else {
			$message = sprintf (JText::_('VM_USER_SEND_REGISTRATION_DETAILS',false), $name, $mosConfig_sitename, $mosConfig_live_site, $username, $pwd);
		}

		$message = vmHtmlEntityDecode($message, ENT_QUOTES);
		// Send email to user
		if ($mosConfig_mailfrom != "" && $mosConfig_fromname != "") {
			$adminName2 = $mosConfig_fromname;
			$adminEmail2 = $mosConfig_mailfrom;
		} else {
			$query = "SELECT name, email"
			. "\n FROM #__users"
			. "\n WHERE LOWER( usertype ) = 'superadministrator'"
			. "\n OR LOWER( usertype ) = 'super administrator'"
			;
			$database->setQuery( $query );
			$rows = $database->loadObjectList();
			$row2 			= $rows[0];
			$adminName2 	= $row2->name;
			$adminEmail2 	= $row2->email;
		}
		if( VM_REGISTRATION_TYPE != 'NO_REGISTRATION' || (VM_REGISTRATION_TYPE == 'OPTIONAL_REGISTRATION' && !empty($d['register_account']))) {
			vmMail($adminEmail2, $adminName2, $email, $subject, $message);
		}

		// Send notification to all administrators
		$subject2 = sprintf (JText::_('SEND_SUB',false), $name, $mosConfig_sitename);
		$message2 = sprintf (JText::_('ASEND_MSG',false), $adminName2, $mosConfig_sitename, $name, $email, $username);
		$subject2 = vmHtmlEntityDecode($subject2, ENT_QUOTES);
		$message2 = vmHtmlEntityDecode($message2, ENT_QUOTES);

		// get superadministrators id
		$admins = $acl->get_group_objects( 25, 'ARO' );
		if( empty( $admins['users'] )) {
			return;
		}
		foreach ( $admins['users'] AS $id ) {
			$query = "SELECT email, sendEmail"
			. "\n FROM #__users"
			."\n WHERE id = $id"
			;
			$database->setQuery( $query );
			$rows = $database->loadObjectList();

			$row = $rows[0];

			if ($row->sendEmail) {
				vmMail($adminEmail2, $adminName2, $row->email, $subject2, $message2);
			}
		}

	}

	/**
	 * Retrieves the Customer Number of the user specified by ID
	 *
	 * @internal moved from ps_shopper_group::get_customer_num($id)
	 * @since 1.2.0
	 * @param int $id
	 * @return string
	 */
	function get_customer_num($id) {
		$db = new ps_DB;

		$q = "SELECT `customer_number` FROM `#__{vm}_shopper_vendor_xref` "
			."WHERE `user_id`='". $id ."'";
		$db->query($q);
		$db->next_record();

		return $db->f("customer_number");
	}
}
$ps_shopper = new ps_shopper;
?>