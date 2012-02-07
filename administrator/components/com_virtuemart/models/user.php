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
		$this->addvalidOrderingFieldName(array('ju.username','ju.name','sg.virtuemart_shoppergroup_id','shopper_group_name','shopper_group_desc') );
		array_unshift($this->_validOrderingFieldName,'ju.id');
// 		$user = JFactory::getUser();
// 		$this->_id = $user->id;
	}

	/**
	 * public function Resets the user id and data
	 *
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
// 				vmdebug('setId setCurrent $user',$user->get('id'));
			} else {
				if($cid != $user->id){
					if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');
					if(Permissions::getInstance()->check("admin")) {
						$this->setUserId($cid);
						vmdebug('Admin watches user, setId '.$cid);
					} else {
						JError::raiseWarning(1,'Hacking attempt');
						$this->setUserId($user->id);
					}
				}else {
					$this->setUserId($user->id);
				}
			}
		}
	}

	/**
	 * Internal function
	 *
	 * @param unknown_type $id
	 */
	private function setUserId($id){

		$app = JFactory::getApplication();
// 		if($app->isAdmin()){
			if($this->_id!=$id){
				$this->_id = (int)$id;
				$this->_data = null;
			}
// 		}
	}


	public function getCurrentUser(){
		$user = JFactory::getUser();
		$this->setUserId($user->id);
		return $this->getUser();
	}

	private $_defaultShopperGroup = 0;
	/**
	 * Retrieve the detail record for the current $id if the data has not already been loaded.
	 */
	function getUser(){

		if(empty($this->_db)) $this->_db = JFactory::getDBO();

// 		vmdebug('getUser id',$this->_id);
		$this->_data = $this->getTable('vmusers');
		$this->_data->load((int)$this->_id);

		$this->_data->JUser = JUser::getInstance($this->_id);

		if(empty($this->_data->perms)){

			if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');
			$this->_data->perms = Permissions::getInstance()->getPermissions((int)$this->_id);

		}

		// Add the virtuemart_shoppergroup_ids
		$xrefTable = $this->getTable('vmuser_shoppergroups');
		$this->_data->shopper_groups = $xrefTable->load($this->_id);
		if(empty($this->_data->shopper_groups)){

// 			if(empty($this->_defaultShopperGroup)){
				if(!class_exists('VirtueMartModelShopperGroup')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'shoppergroup.php');
				$shoppergroupmodel = new VirtueMartModelShopperGroup();

				$this->_defaultShopperGroup = $shoppergroupmodel->getDefault($this->_data->JUser->guest);
// 				vmdebug('$this->_defaultShopperGroup ',$this->_defaultShopperGroup);
// 			}
			$this->_data->shopper_groups = $this->_defaultShopperGroup->virtuemart_shoppergroup_id ;

		}



// 		$_ui = $this->_getList('SELECT `virtuemart_userinfo_id` FROM `#__virtuemart_userinfos` WHERE `virtuemart_user_id` = "' . (int)$this->_id.'"');
		$q = 'SELECT `virtuemart_userinfo_id` FROM `#__virtuemart_userinfos` WHERE `virtuemart_user_id` = "' . (int)$this->_id.'"';
		$this->_db->setQuery($q);
		$userInfo_ids = $this->_db->loadResultArray(0);
// 		vmdebug('my query',$this->_db->getQuery());
// 		vmdebug('my $_ui',$userInfo_ids,$this->_id);
		$this->_data->userInfo = array ();

		$BTuid = 0;
		//$userinfo = $this->getTable('userinfos');
// 		for ($i = 0, $n = count($_ui); $i < $n; $i++) {
		foreach($userInfo_ids as $uid){

			$this->_data->userInfo[$uid] = $this->getTable('userinfos');
			$this->_data->userInfo[$uid]->load($uid);

			if ($this->_data->userInfo[$uid]->address_type == 'BT') {
				$BTuid = $uid;

				$this->_data->userInfo[$BTuid]->name = $this->_data->JUser->name;
				$this->_data->userInfo[$BTuid]->email = $this->_data->JUser->email;
				$this->_data->userInfo[$BTuid]->username = $this->_data->JUser->username;
				$this->_data->userInfo[$BTuid]->address_type = 'BT';
			}

		}

// 		vmdebug('user_is_vendor ?',$this->_data->user_is_vendor);
		if($this->_data->user_is_vendor){

			// 				$this->_data->userInfo[$_ui_id]->user_is_vendor = $this->_data->user_is_vendor;
			// 				$this->_data->userInfo[$_ui_id]->name = $this->_data->JUser->name;
			if(!class_exists('VirtueMartModelVendor')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'vendor.php' );
			$vendorModel = new VirtueMartModelVendor();
			if(Vmconfig::get('multix','none')==='none'){
				$this->_data->virtuemart_vendor_id = 1;
			}
			$vendorModel->setId($this->_data->virtuemart_vendor_id);
			$this->_data->vendor = $vendorModel->getVendor();
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
	 * Functions belonging to get_groups_below_me Taken with correspondence from CommunityBuilder
	 * adjusted to the our needs
	* @version $Id$
	* @package Community Builder
	* @subpackage cb.acl.php
	* @author Beat and mambojoe
	* @author Max Milbers
	* @copyright (C) Beat, www.joomlapolis.com
	* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
	*/
	function get_groups_below_me() {

		static $gids			=	array();

		$myId					=	JUser::getInstance()->id;
		$this->_acl = JFactory::getACL();
		if ( ! isset( $gids[$myId] ) ) {
			if ( JVM_VERSION === 2 ) {
				$my_groups		=	$this->get_object_groups( $myId );
			} else {
				$aro_id			=	$this->get_object_id( 'users', $myId, 'ARO' );
				$my_groups		=	$this->get_object_groups( $aro_id, 'ARO' );
			}

			$my_gids			=	array();

			if ( $my_groups ) foreach ( $my_groups as $gid ) {
				$my_gids		=	array_unique( array_merge( $my_gids, $this->get_group_children_ids( $gid ) ) );

				if ( JVM_VERSION === 2 ) {
					$my_gids	=	array_unique( array_merge( $my_gids, $this->get_object_groups( $myId, null, 'RECURSE' ) ) );
				}
			}

			if ( ( ! is_array( $my_gids ) ) || empty( $my_gids ) ) {
				$my_gids		=	array();
			} else {
				JArrayHelper::toInteger($my_gids);//ArrayToInts( $my_gids );
			}

			$groups				=	$this->get_group_children_tree( null, 'USERS', false );

			if ( $groups ) {
				foreach ( $groups as $k => $v ) {
					if ( ! in_array( (int) $v->value, $my_gids ) ) {
						unset( $groups[$k] );
					}
				}
			}
			$gids[$myId]		=	array_values( $groups );
			vmdebug('get_groups_below_me ',$gids);
		}

		return $gids[$myId];
	}

	/**
	* Functions belonging to get_groups_below_me Taken with correspondence from CommunityBuilder
	* adjusted to the our needs
	* @version $Id$
	* @package Community Builder
	* @subpackage cb.acl.php
	* @author Beat and mambojoe
	* @author Max Milbers
	* @copyright (C) Beat, www.joomlapolis.com
	* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
	*/

	function get_object_id( $var_1 = null, $var_2 = null, $var_3 = null ) {
		if ( JVM_VERSION === 2) {
			$return		=	$var_2;
		} else {
			$return		=	$this->_acl->get_object_id( $var_1, $var_2, $var_3 );
		}

		return $return;
	}

	/**
	*  Taken with correspondence from CommunityBuilder
	* adjusted to the our needs
	* @version $Id$
	* @package Community Builder
	* @subpackage cb.acl.php
	* @author Beat and mambojoe
	* @author Max Milbers
	* @copyright (C) Beat, www.joomlapolis.com
	* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
	*/

	function get_object_groups( $var_1 = null, $var_2 = null, $var_3 = null ) {
		if ( version_compare(JVERSION,'1.6.0','ge') ) {
			$user_id	=	( is_integer( $var_1 ) ? $var_1 : $var_2 );
			$recurse	=	( $var_3 == 'RECURSE' ? true : false );
			$return		=	$this->_acl->getGroupsByUser( $user_id, $recurse );
		} else {
			if ( ! $var_2 ) {
				$var_2	=	'ARO';
			}

			if ( ! $var_3 ) {
				$var_3	=	'NO_RECURSE';
			}

			$return		=	$this->_acl->get_object_groups( $var_1, $var_2, $var_3 );
		}

		return $return;
	}


	function get_group_children_ids( $gid ) {

		$_CB_database = &$this->getDbo();

		static $gids			=	array();

		$gid					=	(int) $gid;

		if ( ! isset( $gids[$gid] ) ) {
			if ( JVM_VERSION === 2) {
				static $grps				=	null;
				static $paths				=	null;

				if ( ! isset( $grps ) ) {
					$query					=	'SELECT *'
					.	"\n FROM " . $_CB_database->NameQuote( '#__usergroups' )
					.	"\n ORDER BY " . $_CB_database->NameQuote( 'lft' );
					$_CB_database->setQuery( $query );
					$grps					=	$_CB_database->loadObjectList( 'id' );
				}

				if ( ! array_key_exists( $gid, $grps ) ) {
					return array();
				}

				if ( ! isset( $paths[$gid] ) ) {
					$paths[$gid]			=	array();

					foreach( $grps as $grp ) {
						if ( ( $grp->lft <= $grps[$gid]->lft ) && ( $grp->rgt >= $grps[$gid]->rgt ) ) {
							$paths[$gid][]	=	$grp->id;
						}
					}
				}

				$type						=	$this->get_parent_container( $grps[$gid], $grps );

				if ( in_array( $type, array( 2, 3 ) ) ) {
					$paths[$gid]			=	array_merge( $paths[$gid], array_diff( $this->get_group_parent_ids( 2 ), $this->get_group_parent_ids( $gid ) ) );
				}

				$paths[$gid]				=	array_unique( $paths[$gid] );

				sort( $paths[$gid], SORT_NUMERIC );

				$groups						=	$paths[$gid];
			} else {
				$query			=	'SELECT g1.' . $_CB_database->NameQuote( 'id' ) . ' AS group_id'
				.	', g1.' . $_CB_database->NameQuote( 'name' )
				.	"\n FROM " . $_CB_database->NameQuote( '#__core_acl_aro_groups' ) . " AS g1"
				.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__core_acl_aro_groups' ) . " AS g2"
				.	' ON g2.' . $_CB_database->NameQuote( 'lft' ) . ' >= g1.' . $_CB_database->NameQuote( 'lft' )
				.	"\n WHERE g2." . $_CB_database->NameQuote( 'id' ) . " = " . (int) $gid
				.	"\n ORDER BY g1." . $_CB_database->NameQuote( 'name' );
				$_CB_database->setQuery( $query );
				$groups			=	$_CB_database->loadResultArray();
			}
			for ( $i = 0, $n = count( $groups ); $i < $n; $i++ ) {
				$groups[$i]		=	(int) $groups[$i];
			}

			$standardlist		=	array( -2 );

			if ( $gid && ( $gid != $this->mapGroupNamesToValues( 'Public' ) ) ) {
				$standardlist[]	=	-1;
			}

			$groups				=	array_merge( $groups, $standardlist );

			$gids[$gid]			=	$groups;
		}

		return $gids[$gid];
	}

	function get_parent_container( $grp, $groups ) {
		if ( $grp && $groups ) {
			foreach ( $groups as $group ) {
				$id			=	(int) $grp->id;
				$parent		=	(int) $grp->parent_id;
				$grps		=	array( $parent, $id );

				// Go no further if group has no parent:
				if ( $parent ) {
					// Determine Joomla version:
					if (JVM_VERSION === 2 ) {
						if ( in_array( 2, $grps ) ) {
							return 1; // Registered
						} elseif ( in_array( 6, $grps ) ) {
							return 2; // Manager
						} elseif ( in_array( 8, $grps ) ) {
							return 3; // Super Administrator
						}
					} else {
						if ( in_array( 29, $grps ) ) {
							return 1; // Public Frontend
						} elseif ( in_array( 30, $grps ) ) {
							return 2; // Public Backend
						}
					}

					// Loop through for deep groups:
					return $this->get_parent_container( $groups[$parent], $groups );
				} else {
					return 0; // Root
				}
			}
		}

		return null; // Unknown
	}

	/**	 * Remap literal groups (such as in default values) to the hardcoded CMS values
	*
	* @param  string|array  $name  of int|string
	* @return int|array of int
	*/
	function mapGroupNamesToValues( $name ) {
		static $ps						=	null;

		$selected						=	(array) $name;
		foreach ( $selected as $k => $v ) {
			if ( ! is_numeric( $v ) ) {
				if ( ! $ps ) {
					if ( JVM_VERSION === 2 ) {
						$ps				=	array( 'Root' => 0 , 'Users' => 0 , 'Public' =>  1, 'Registered' =>  2, 'Author' =>  3, 'Editor' =>  4, 'Publisher' =>  5, 'Backend' => 0 , 'Manager' =>  6, 'Administrator' =>  7, 'Superadministrator' =>  8 );
					} else {
						$ps				=	array( 'Root' => 17, 'Users' => 28, 'Public' => 29, 'Registered' => 18, 'Author' => 19, 'Editor' => 20, 'Publisher' => 21, 'Backend' => 30, 'Manager' => 23, 'Administrator' => 24, 'Superadministrator' => 25 );
					}
				}
				if ( array_key_exists( $v, $ps ) ) {
					if ( $ps[$v] != 0 ) {
						$selected[$k]	=	$ps[$v];
					} else {
						unset( $selected[$k] );
					}
				} else {
					$selected[$k]		=	(int) $v;
				}
			}
		}
		if ( ! is_array( $name ) ) {
			$selected					=	$selected[0];
		}
		return $selected;
	}

	function get_group_children_tree( $var_1 = null, $var_2 = null, $var_3 = null, $var_4 = null ) {
		$_CB_database = &$this->getDbo();

		if ( ! $var_4 ) {
			$var_4						=	true;
		}

		if ( JVM_VERSION === 2 ) {
			$query						=	'SELECT a.' . $_CB_database->NameQuote( 'id' ) . ' AS value'
			.	', a.' . $_CB_database->NameQuote( 'title' ) . ' AS text'
			.	', COUNT( DISTINCT b.' . $_CB_database->NameQuote( 'id' ) . ' ) AS level'
			.	"\n FROM " . $_CB_database->NameQuote( '#__usergroups' ) . " AS a"
			.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__usergroups' ) . " AS b"
			.	' ON a.' . $_CB_database->NameQuote( 'lft' ) . ' > b.' . $_CB_database->NameQuote( 'lft' )
			.	' AND a.' . $_CB_database->NameQuote( 'rgt' ) . ' < b.' . $_CB_database->NameQuote( 'rgt' )
			.	"\n GROUP BY a." . $_CB_database->NameQuote( 'id' )
			.	"\n ORDER BY a." . $_CB_database->NameQuote( 'lft' ) . " ASC";
			$_CB_database->setQuery( $query );
			$groups						=	$_CB_database->loadObjectList();

			$user_groups				=	array();

			for ( $i = 0, $n = count( $groups ); $i < $n; $i++ ) {
				$groups[$i]->text		=	str_repeat( '- ', $groups[$i]->level ) . JText::_( $groups[$i]->text );

				if ( $var_4 ) {
					$user_groups[$i]	=	JHtml::_( 'select.option', $groups[$i]->value, $groups[$i]->text );
				} else {
					$user_groups[$i]	=	array( 'value' => $groups[$i]->value, 'text' => $groups[$i]->text );
				}
			}

			$return						=	$user_groups;
		} else {
			if ( ! $var_3 ) {
				$var_3					=	true;
			}

			$return						=	$this->_acl->get_group_children_tree( $var_1, $var_2, $var_3, $var_4 );
		}

		return $return;
	}

	/**
	 * Return a list with groups that can be set by the current user
	 *
	 * @return mixed Array with groups that can be set, or the groupname (string) if it cannot be changed.
	 */
	function getGroupList()
	{

		if(JVM_VERSION === 2) {

			//hm CB thing also not help
// 			$_grpList = $this->get_groups_below_me();
// 			return $_grpList;


/*			if(!class_exists('UsersModelUser')) require(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_users'.DS.'models'.DS.'user.php');
			$jUserModel = new UsersModelUser();
			$list = $jUserModel->getGroups();

			$user = JFactory::getUser();
			if ($user->authorise('core.edit', 'com_users') && $user->authorise('core.manage', 'com_users'))
			{
				$model = JModel::getInstance('Groups', 'UsersModel', array('ignore_request' => true));
				return $model->getItems();
			}
			else
			{
				return null;
			}*/
			$user = JFactory::getUser();
			$authGroups = JAccess::getGroupsByUser($user->id);
// 			$authGroups = $user->getAuthorisedGroups();
// 			vmdebug('getGroupList j17',$authGroups);

			$db		= &$this->getDbo();
			$where = implode($authGroups,'" OR `id` = "').'"';
			$q = 'SELECT `id` as value,`title` as text FROM #__usergroups WHERE `id` = "'.$where;

			$db->setQuery($q);
			$list = $db->loadAssocList();

// 			foreach($list as $item){
// 				vmdebug('getGroupList $item ',$item);
// 			}

// 			vmdebug('getGroupList $q '.$list);
			return $list;
		} else {

		$_aclObject = JFactory::getACL();

		if(empty($this->_data)) $this->getUser();

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

		$message = '';
		$user = '';
		$newId = 0;

		JRequest::checkToken() or jexit( 'Invalid Token, while trying to save user' );
		$mainframe = JFactory::getApplication() ;

		if(empty($data)){
			vmError('Developer notice, no data to store for user');
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

		if(!$new && !empty($data['password']) && empty($data['password2'])){
			unset($data['password']);
			unset($data['password2']);
		}

		// Bind Joomla userdata
		if (!$user->bind($data)) {

			foreach($user->getErrors() as $error) {
// 				vmError('user bind '.$error);
				vmError('user bind '.$error,'Couldnt store user '.$error);
			}
			$message = 'Couldnt bind data to joomla user';
			array('user'=>$user,'password'=>$data['password'],'message'=>$message,'newId'=>$newId,'success'=>false);
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
			    if ( JVM_VERSION===1){
				$newUsertype = 'Registered';

			    } else {
				$newUsertype=2;
			    }
			}
			// Set some initial user values
			$user->set('usertype', $newUsertype);

			if ( JVM_VERSION===1){
				$user->set('gid', $authorize->get_group_id( '', $newUsertype, 'ARO' ));
			} else {
			    	$user->groups[] = $newUsertype;
			}

			$date = JFactory::getDate();
			$user->set('registerDate', $date->toMySQL());

			// If user activation is turned on, we need to set the activation information
			$useractivation = $usersConfig->get( 'useractivation' );
			$doUserActivation=false;
			if ( JVM_VERSION===1){
			    if ($useractivation == '1' ) {
				$doUserActivation=true;
			    }
			} else {
			    if ($useractivation == '1' or $useractivation == '2') {
				$doUserActivation=true;
			    }
			}
			vmdebug('user',$useractivation , $doUserActivation);
			if ($doUserActivation )
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
				vmError(JText::_('COM_VIRTUEMART_USER_ERR_ONLYSUPERADMIN'));
				return false;
			}
		}

		// Save the JUser object
		if (!$user->save()) {
			vmError(JText::_( $user->getError()) , JText::_( $user->getError()));
			return false;
		}

		$newId = $user->get('id');
		$data['virtuemart_user_id'] = $newId;	//We need this in that case, because data is bound to table later
		$this->setUserId($newId);

		//Save the VM user stuff
		if(!$this->saveUserData($data) || !self::storeAddress($data)){
			vmError('COM_VIRTUEMART_NOT_ABLE_TO_SAVE_USER_DATA');
// 			vmError(Jtext::_('COM_VIRTUEMART_NOT_ABLE_TO_SAVE_USERINFO_DATA'));
		} else {
			if ($new) {
				$this->sendRegistrationEmail($user,$user->password_clear, $doUserActivation);
				if ($doUserActivation ) {
					vmInfo('COM_VIRTUEMART_REG_COMPLETE_ACTIVATE');
				} else {
					vmInfo('COM_VIRTUEMART_REG_COMPLETE');
				}
			} else {
				vmInfo('COM_VIRTUEMART_USER_DATA_STORED');
			}
		}

// 		if (!self::storeAddress($data)) {
// 			vmError(Jtext::_('COM_VIRTUEMART_NOT_ABLE_TO_SAVE_USERINFO_DATA'));
// 		}

		if((int)$data['user_is_vendor']==1){
// 			vmdebug('vendor recognised');
			if($this ->storeVendorData($data)){
				if ($new) {
					if ($doUserActivation ) {
						vmInfo('COM_VIRTUEMART_REG_VENDOR_COMPLETE_ACTIVATE');
					} else {
						vmInfo('COM_VIRTUEMART_REG_VENDOR_COMPLETE');
					}
				} else {
					vmInfo('COM_VIRTUEMART_VENDOR_DATA_STORED');
				}
			}
		}

		return array('user'=>$user,'password'=>$data['password'],'message'=>$message,'newId'=>$newId,'success'=>true);

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
			vmError( 'This is a notice for developers, you used this function for an anonymous user, but it is only designed for already registered ones');
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

		$alreadyStoredUserData = $usertable->load($this->_id);
		$app = JFactory::getApplication();
		if($app->isSite()){
			unset($data['user_is_vendor']);
			unset($data['virtuemart_vendor_id']);

			$data['user_is_vendor'] = $alreadyStoredUserData->user_is_vendor;
			$data['virtuemart_vendor_id'] = $alreadyStoredUserData->virtuemart_vendor_id;

		} else {

		}

		JPluginHelper::importPlugin('vmshopper');
		$dispatcher = JDispatcher::getInstance();
		//Todo to adjust to new pattern, using &
		$plg_datas = $dispatcher->trigger('plgVmOnUserStore',$data);
		foreach($plg_datas as $plg_data){
// 			$data = array_merge($plg_data,$data);
		}

		$usertable -> bindChecknStore($data);
		$errors = $usertable->getErrors();
		foreach($errors as $error){
			$this->setError($error);
			vmError('storing user adress data'.$error);
		}


		// Bind the form fields to the auth_user_group table
		if(!empty($data['virtuemart_shoppergroup_id'])){
			$shoppergroupData = array('virtuemart_user_id'=>$this->_id,'virtuemart_shoppergroup_id'=>$data['virtuemart_shoppergroup_id']);
			$user_shoppergroups_table = $this->getTable('vmuser_shoppergroups');
			$shoppergroupData = $user_shoppergroups_table -> bindChecknStore($shoppergroupData);
			$errors = $user_shoppergroups_table->getErrors();
			foreach($errors as $error){
				$this->setError($error);
				vmError('Set shoppergroup '.$error);
			}
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
			if(Vmconfig::get('multix','none')==='none' ){
				$data['virtuemart_vendor_id'] = 1;
			}
			$vendorModel->setId($data['virtuemart_vendor_id']);

			if (!$vendorModel->store($data)) {
				vmError($vendorModel->getError());
				vmdebug('Error storing vendor',$vendorModel);
				return false;
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
			vmError($userinfo->getError());
		}
		return $userinfo->virtuemart_userinfo_id;
	}




	function _prepareUserFields($data, $type)
	{
		if(!class_exists('VirtueMartModelUserfields')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'userfields.php' );
		$userFieldsModel = new VirtueMartModelUserfields();

		if ($type == 'ST') {
			$prepareUserFields = $userFieldsModel->getUserFields(
									 'shipment'
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

	function getBTuserinfo_id($id = 0){
		if(empty($this->_db)) $this->_db = JFactory::getDBO();

		if($id == 0){
			$id = $this->_id;
			vmdebug('getBTuserinfo_id is '.$this->_id);
		}

		$q = 'SELECT `virtuemart_userinfo_id` FROM `#__virtuemart_userinfos` WHERE `virtuemart_user_id` = "' .(int)$id .'" AND `address_type`="BT" ';
		$this->_db->setQuery($q);
		return $this->_db->loadResult();
	}

	/**
	 *
	 * @author Max Milbers
	 */
	function getUserInfoInUserFields($layoutName, $type,$uid,$cart=true){

		if(!class_exists('VirtueMartModelUserfields')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'userfields.php' );
		$userFieldsModel = new VirtuemartModelUserfields();

		$prepareUserFields = $userFieldsModel->getUserFieldsFor(
		$layoutName,
		$type
		);

		if($type=='ST'){
			$preFix = 'shipto_';
		} else {
			$preFix = '';
		}

// 		if(!$cart && empty($uid)) $uid = $this->_id;
		$userFields = array();
		if(!empty($uid)){

			$this->_data->userInfo[$uid] = $this->getTable('userinfos');
			$this->_data->userInfo[$uid]->load($uid);

			$data = $this->_data->userInfo[$uid];
// 			vmdebug(' user data with infoid  '.$uid,$this->_data->userInfo[$uid]);
		}
		else {
			//New Address is filled here with the data of the cart (we are in the userview)
			if($cart){
				if (!class_exists('VirtueMartCart'))
				require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
				$cart = VirtueMartCart::getCart();
				$adType = $type.'address';

				if(empty($cart->$adType)){
					$data = $cart->$type;
					if(empty($data)) $data = array();
					$jUser = JUser::getInstance($this->_id);
					if($jUser){
						if(empty($data['name'])){
							$data['name'] = $jUser->name;
						}
						if(empty($data['email'])){
							$data['email'] = $jUser->email;
						}
						if(empty($data['username'])){
							$data['username'] = $jUser->username;
						}
					}
				}
				$data = (object)$data;
			} else {
				$data = null;
			}

		}
// 		vmdebug('getUserInfoInUserFields',$data);
		$userFields[$uid] = $userFieldsModel->getUserFieldsFilled(
		$prepareUserFields
		,$data
		,$preFix
		);

		return $userFields;
	}


	/**
	* This should load the userdata in userfields so that they can easily displayed
	*
	* @author Max Milbers
	* @deprecated
	*/

	function getUserDataInFields($layoutName, $type, $id, $cart=false){

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

		$this->setId($id);
		$userdata = $this->getUser();

// 		vmdebug('$userdata',$userdata);
		$found = false;

		//Non anonymous case
		if(!$cart && !empty($id) && !empty($userdata->userInfo) && count($userdata->userInfo)>0) {

			$currentUserData = current($userdata->userInfo);

			$currentUserData->agreed = $userdata->agreed;

			for ($_i = 0; $_i < count($userdata->userInfo); $_i++) {

				if($currentUserData->address_type==$type){
					$fields = $userFieldsModel->getUserFieldsFilled(
					$prepareUserFields
					,$currentUserData
					,$preFix
					);

					$fields['virtuemart_userinfo_id'] = key($userdata->userInfo);
					$userFields[key($userdata->userInfo)] = $fields;
					$found = true;
				}
				$currentUserData = next($userdata->userInfo);

			}
		}

		//anonymous case or no data provided or new
		if(empty($userFields)){
			//New Address is filled here with the data of the cart (we are in the userview)
			if (!class_exists('VirtueMartCart'))
			require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
			$cart = VirtueMartCart::getCart();
			$adType = $type.'address';
			if(empty($cart->$adType)){
				$cart->$adType = $userFieldsModel->getUserFieldsFilled(
				$prepareUserFields
				,(object)$cart->$type
				,$preFix
				);
			}

			$cart->$adType['virtuemart_userinfo_id'] = 0;
			$userFields[0] = $cart->$adType;

		}

		return $userFields;
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
	  *
	  * @author Oscar van Eijk
	  * @author Max Milbers
	  * @author Christopher Roussel
	  * @author Valérie Isaksen
	  */
	 private function sendRegistrationEmail($user, $password, $doUserActivation){
		if(!class_exists('shopFunctionsF')) require(JPATH_VM_SITE.DS.'helpers'.DS.'shopfunctionsf.php');
		$vars = array('user' => $user);

		// Send registration confirmation mail

		$password = preg_replace('/[\x00-\x1F\x7F]/', '', $password); //Disallow control chars in the email
		$vars['password'] = $password;

		if ($doUserActivation) {
			jimport('joomla.user.helper');
			if(JVM_VERSION === 2) {
				$com_users = 'com_users';
				$activationLink = 'index.php?option='.$com_users.'&task=registration.activate&token='.$user->get('activation');
			} else {
				$com_users = 'com_user';
				$activationLink = 'index.php?option='.$com_users.'&task=activate&activation='.$user->get('activation');
			}
 			$vars['activationLink'] = $activationLink;
		}
		$vars['doVendor']=true;
		// public function renderMail ($viewName, $recipient, $vars=array(),$controllerName = null)
		shopFunctionsF::renderMail('user', $user->get('email'), $vars);

		//get all super administrator
		$query = 'SELECT name, email, sendEmail' .
				' FROM #__users' .
				' WHERE LOWER( usertype ) = "super administrator"';
		$this->_db->setQuery( $query );
		$rows = $this->_db->loadObjectList();

		$vars['doVendor']=false;
		// get superadministrators id
		foreach ( $rows as $row )
		{
			if ($row->sendEmail)
			{
				//$message2 = sprintf ( JText::_( 'COM_VIRTUEMART_SEND_MSG_ADMIN' ), $row->name, $sitename, $name, $email, $username);
				//$message2 = html_entity_decode($message2, ENT_QUOTES);
				//JUtility::sendMail($mailfrom, $fromname, $row->email, $subject2, $message2);
				//shopFunctionsF::renderMail('user', $row->email, $vars);
			}
		}


	 }

	 /**
	  * Delete all record ids selected
	  *
	  * @return boolean True is the remove was successful, false otherwise.
	  */
	 function remove($userIds)
	 {
	 	$userInfo = $this->getTable('userinfos');
	 	$vm_shoppergroup_xref = $this->getTable('vmuser_shoppergroups');
		$vmusers = $this->getTable('vmusers');
	 	$_status = true;
	 	foreach($userIds as $userId) {
	 		if ($this->getSuperAdminCount() <= 1) {
	 			// Prevent deletion of the only Super Admin
	 			$_u = JUser::getInstance($userId);
	 			if ($_u->get('gid') == __SUPER_ADMIN_GID) {
	 				vmError(JText::_('COM_VIRTUEMART_USER_ERR_LASTSUPERADMIN'));
	 				$_status = false;
	 				continue;
	 			}
	 		}

	 		if (!$userInfo->delete($userId)) {
	 			vmError($userInfo->getError());
	 			return false;
	 		}
	 		if (!$vm_shoppergroup_xref->delete($userId)) {
	 			vmError($vm_shoppergroup_xref->getError()); // Signal but continue
	 			$_status = false;
	 			continue;
	 		}
			if (!$vmusers->delete($userId)) {
	 			vmError($vmusers->getError()); // Signal but continue
	 			$_status = false;
	 			continue;
	 		}
	 		$_JUser = JUser::getInstance($userId);
	 		if (!$_JUser->delete()) {
	 			vmError($jUser->getError());
	 			return false;
	 		}
	 	}
	 	return $_status;
	 }

	 /**
	  * Retrieve a list of users from the database.
	  *
	  * @author Max Milbers
	  * @return object List of user objects
	  */
	 function getUserList() {

			$select = ' DISTINCT ju.id AS id
			, ju.name AS name
			, ju.username AS username
			, vmu.user_is_vendor AS is_vendor
			, vmu.perms AS perms
			, ju.usertype AS usertype
			, IFNULL(sg.shopper_group_name, "") AS shopper_group_name ';
			$joinedTables = ' FROM #__users AS ju
			LEFT JOIN #__virtuemart_vmusers AS vmu ON ju.id = vmu.virtuemart_user_id
			LEFT JOIN #__virtuemart_vmuser_shoppergroups AS vx ON ju.id = vx.virtuemart_user_id
			LEFT JOIN #__virtuemart_shoppergroups AS sg ON vx.virtuemart_shoppergroup_id = sg.virtuemart_shoppergroup_id ';

			return $this->_data = $this->exeSortSearchListQuery(0,$select,$joinedTables,$this->_getFilter(),'',$this->_getOrdering());

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
				return false;
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
	 	if (JVM_VERSION===1) {
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
	 	$query .= ' ORDER BY `node`.`lft`';

	 	$this->_db->setQuery($query);
		//$app = JFactory::getApplication();
		//$app -> enqueueMessage($this->_db->getQuery());
	 	$objlist = $this->_db->loadObjectList();
// 		vmdebug('getAclGroupIndentedTree',$objlist);
	 	return $objlist;
	 }
}


//No Closing tag
