<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id$
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
 * The permission handler class for VirtueMart.
 *
 * @todo Further cleanup
 */
class Permissions extends JObject{

	/** @var array Contains all the user groups */ 
	var $_user_groups;	
	
	/** @var user_id for the permissions*/
	var $_user_id;		//$auth['user_id']

	var $_show_prices; //$auth['show_prices']
	
	var $_db;

	var $_perms;
	
	var $_is_registered_customer;
	
	static $_instance;
	
	public function __construct() {
		
		$this->_db = JFactory::getDBO();
		$this->getUserGroups();
		$this->doAuthentication();
		
	}
	
	function getInstance(){ 
		if(!is_object(self::$_instance)){
			self::$_instance = new Permissions();
		}else {
			//is this necessary?
//			self::$_instance->doAuthentication();
		}
 		return self::$_instance;
    }
      
	public function getUserGroups() {
		if (empty($this->_user_groups)) {
			$this->_db = JFactory::getDBO();
			$q = ('SELECT `group_id`,`group_name`,`group_level` 
					FROM `#__vm_perm_groups` 
					ORDER BY `group_level` ');
			$this->_db->setQuery($q);
			$this->_user_groups = $this->_db->loadObjectList('group_name');
		}
//		echo 'Die Usergroups: <pre>'.print_r($this->_user_groups).'</pre>';
		return $this->_user_groups;
	}
	
	/**
	* description: Validates if someone is registered customer.
	*            by checking if one has a billing address
	* parameters: user_id
	* returns: true if the user has a BT address
	*          false if the user has none
	*
	* Check if a user is registered in the shop (=customer)
	*
	* @param int $user_id the user ID to check. If no user ID is given the currently logged in user will be used.
	* @return boolean
	*/
	public function isRegisteredCustomer($user_id=0) {
		if ($user_id == 0) {
			/* Lets see if we can get the current signed in user */
			$user = JFactory::getUser();
			if ($user->id == 0) return false;
			else $user_id = $user->id;
		}

		$this->_db = JFactory::getDBO();
		/* If the registration type is neither "no registration" nor "optional registration", 
			there *must* be a related Joomla! user, we can join */
		if (VmConfig::get('vm_registration_type') != 'NO_REGISTRATION' 
			&& VmConfig::get('vm_registration_type') != 'OPTIONAL_REGISTRATION') {
			$q  = "SELECT COUNT(user_id) AS num_rows 
				FROM `#__vm_user_info`, `#__users` 
				WHERE `id`=`user_id`
				AND #__vm_user_info.user_id='" . $user_id . "'
				AND #__vm_user_info.address_type='BT'";
		} 
		else {
			$q  = "SELECT COUNT(user_id) AS num_rows 
				FROM `#__vm_user_info` 
				WHERE #__vm_user_info.user_id='" . $user_id . "'  
				AND #__vm_user_info.address_type='BT'";
		}
		$this->_db->setQuery($q);
		return $this->_db->loadResult();
	}
	
	/**
	* This function does the basic authentication
	* for a user in the shop.
	* It assigns permissions, the name, country, zip  and
	* the shopper group id with the user and the session.
	* @return array Authentication information
	*/
	function doAuthentication() {
		$this->_db = JFactory::getDBO();
		$session = JFactory::getSession();
		$vmUser = JFactory::getUser();
		
		// Check token
//		JRequest::checkToken() or jexit( 'Invalid Token' );

		
		if (VmConfig::get('vm_price_access_level') != '') {
			/* Is the user allowed to see the prices? */
			$this->_show_prices  = $vmUser->authorize( 'virtuemart', 'prices' );	
		}
		else {
			$this->_show_prices = 1;
		}
		
		/* Load the shoppr group values */
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'shoppergroup.php');
		$shopper_group =  shopperGroup::getShoppergroupById($vmUser->id);
		
		/* User has already logged in */
		if (!empty($vmUser->id) || !empty( $this->_user_id)) {
			if( $vmUser->id > 0 ) {
				$this->_user_id   = $vmUser->id;
//				$auth["username"] = $vmUser->username;
			} 
			else if(!empty($this->_user_id) 
					&& VmConfig::get('vm_registration_type') != 'NO_REGISTRATION' 
					&& VmConfig::get('vm_registration_type') != 'OPTIONAL_REGISTRATION') 
			{
				$this->_user_id = 0;
//				$auth["username"] = "demo";
			}
			
			if (self::isRegisteredCustomer($this->_user_id)) {
				$q = 'SELECT `perms`
					FROM #__vm_users 
					WHERE user_id="'.$this->_user_id.'"';
				$this->_db->setQuery($q); 
				$this->_perms = $this->_db->loadResult();

				/* We must prevent that Administrators or Managers are 'just' shoppers */
				if ($this->_perms == "shopper") {
					if (stristr($vmUser->usertype,"Administrator")) {
						$this->_perms  = "admin";
					}
					elseif (stristr($vmUser->usertype,"Manager")) {
						$this->_perms  = "storeadmin";
					}
				}
				$this->_is_registered_customer = true;
			}
			/* User is no registered customer */
			else {
				if (stristr($vmUser->usertype,"Administrator")) $this->_perms  = "admin";
				elseif (stristr($vmUser->usertype,"Manager")) $this->_perms  = "storeadmin";
				/* Default */
				else $this->_perms  = "shopper";
				$this->_is_registered_customer = false;
			}
		} // user is not logged in
		elseif (empty($this->_user_id)) {
			$this->_user_id = 0;
//			$auth["username"] = "demo";
			$this->_perms  = "";
//			$auth["first_name"] = "guest";
//			$auth["last_name"] = "";
//			$auth["shopper_group_id"] = $shopper_group["shopper_group_id"];
//			$auth["shopper_group_discount"] = $shopper_group["shopper_group_discount"];
//			$auth["show_price_including_tax"] = $shopper_group["show_price_including_tax"];
//			$auth["default_shopper_group"] = 1;
			$this->_is_registered_customer = false;
		}

	}

	/**
	 * Validates the permission to do something.
	 *
	 * @param string $perms
	 * @return boolean Check successful or not
	 * @example $perm->check( 'admin,storeadmin' );
	 * 			returns true when the user is admin or storeadmin
	 */
	public function check($perms) {
		/* Set the authorization for use */
		
		// Parse all permissions in argument, comma separated
		// It is assumed auth_user only has one group per user.
		if ($perms == "none") {
			return true;
		}
		else {
			$p1 = explode(",", $this->_perms);
			$p2 = explode(",", $perms);
			while (list($key1, $value1) = each($p1)) {
				while (list($key2, $value2) = each($p2)) {
					if ($value1 == $value2) {
						return true;
					}
				}
			}
		}
		return false;
	}
	
	/**
	 * Checks if the user has higher permissions than $perm
	 * does not work properly, do not use or correct it
	 * @param string $perm
	 * @return boolean
	 * @example $perm->hasHigherPerms( 'storeadmin' );
	 * 			returns true when user is admin
	 */
	function atLeastPerms( $perm ) {

		if( $this->_perms && $this->_user_groups[$perm] >= $this->_user_groups[$this->_perms] ) {
			return true;	
		}
		else {
			return false;
		}
	
	}
	
	/**
	 * lists the permission levels in a select box
	 * @author pablo
	 * @param string $name The name of the select element
	 * @param string $group_name The preselected key
	 */
	function list_perms( $name, $group_name, $size=1, $multi=false ) {
		
		$auth = $_SESSION['auth'];
		if( $multi ) {
			$multi = 'multiple="multiple"';
		}

		// Get users current permission value 
		$dvalue = $this->user_groups[$this->_perms];
		
		$perms = $this->getUserGroups();
		arsort( $perms );
		
		if( $size==1 ) {
			$values[0] = JText::_('VM_SELECT');
		}
		while( list($key,$value) = each( $perms ) ) {
			// Display only those permission that this user can set
			if ($value >= $dvalue) {
				$values[$key] = $key;
			}
		}
		
		if( $size > 1 ) {
			$name .= '[]';
			$values['none'] = JText::_('NO_RESTRICTION');
		}
		
		echo VmHTML::selectList( $name, $group_name, $values, $size, $multi );
	}
	
	
	
	
	/**
	* Here we insert groups that are allowed to view prices
	*
	*/
	function prepareACL() {
		// The basic ACL integration in Mambo/Joomla is not awesome
		$child_groups = self::getChildGroups( '#__core_acl_aro_groups', 'g1.group_id, g1.name, COUNT(g2.name) AS level', 'g1.name', null, VmConfig::get('vm_price_access_level'));
		?><pre><?php
		print_r($child_groups);
		?></pre><?php
		
		foreach( $child_groups as $child_group ) {
			self::_addToGlobalACL( 'virtuemart', 'prices', 'users', $child_group->name, null, null );
		}
		$admin_groups = self::getChildGroups( '#__core_acl_aro_groups', 'g1.group_id, g1.name, COUNT(g2.name) AS level', 'g1.name', null, 'Public Backend' );
		foreach( $admin_groups as $child_group ) {
			self::_addToGlobalACL( 'virtuemart', 'prices', 'users', $child_group->name, null, null );
		}
		
	}
	
	/**
	 * Function from an old Mambo phpgacl integration function
	 * @deprecated (but necessary, sigh!)
	 * @static 
	 * @param string $table
	 * @param string $fields
	 * @param string $groupby
	 * @param int $root_id
	 * @param string $root_name
	 * @param boolean $inclusive
	 * @return array
	 */
	function getChildGroups($table, $fields, $groupby=null, $root_id=null, $root_name=null, $inclusive=true) {
		$database = JFactory::getDBO();
		$root = new stdClass();
		$root->lft = 0;
		$root->rgt = 0;
		$fields = str_replace( 'group_id', 'id', $fields );
		
		if ($root_id) {
		} 
		else if ($root_name) {
			$database->setQuery("SELECT `lft`, `rgt` FROM `".$table."` WHERE `name`='".$root_name."'" );
			$root = $database->loadObject();
		}

		$where = '';
		if ($root->lft+$root->rgt != 0) {
			if ($inclusive) {
				$where = "WHERE g1.lft BETWEEN $root->lft AND $root->rgt";
			} else {
				$where = "WHERE g1.lft BETWEEN $root->lft+1 AND $root->rgt-1";
			}
		}

		$database->setQuery( "SELECT ".$fields
			. "\nFROM ".$table." AS g1"
			. "\nINNER JOIN ".$table." AS g2 ON g1.lft BETWEEN g2.lft AND g2.rgt"
			. "\n". $where
			. ($groupby ? "\nGROUP BY ".$groupby : "")
			. "\nORDER BY g1.lft"
		);

		return $database->loadObjectList();
	}
	
	/**
	* This is a temporary function to allow 3PD's to add basic ACL checks for their
	* modules and components.  NOTE: this information will be compiled in the db
	* in future versions
	 * @static 
	 * @param unknown_type $aco_section_value
	 * @param unknown_type $aco_value
	 * @param unknown_type $aro_section_value
	 * @param unknown_type $aro_value
	 * @param unknown_type $axo_section_value
	 * @param unknown_type $axo_value
	 */
	function _addToGlobalACL( $aco_section_value, $aco_value, $aro_section_value, $aro_value, $axo_section_value=NULL, $axo_value=NULL ) {
		global $acl;
		$acl->acl[] = array( $aco_section_value, $aco_value, $aro_section_value, $aro_value, $axo_section_value, $axo_value );
		$acl->acl_count = count( $acl->acl );
	}
	
	/**
	 * Returns a tree with the children of the root group id
	 * @static 
	 * @param int $root_id
	 * @param string $root_name
	 * @param boolean $inclusive
	 * @return unknown
	 */
	function getGroupChildrenTree( $root_id=null, $root_name=null, $inclusive=true ) {
		global $database, $_VERSION;

		$tree = ps_perm::getChildGroups( '#__core_acl_aro_groups',
			'g1.group_id, g1.name, COUNT(g2.name) AS level',
			'g1.name',
			$root_id, $root_name, $inclusive );

		// first pass get level limits
		$n = count( $tree );
		$min = $tree[0]->level;
		$max = $tree[0]->level;
		for ($i=0; $i < $n; $i++) {
			$min = min( $min, $tree[$i]->level );
			$max = max( $max, $tree[$i]->level );
		}

		$indents = array();
		foreach (range( $min, $max ) as $i) {
			$indents[$i] = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		}
		// correction for first indent
		$indents[$min] = '';

		$list = array();
		for ($i=$n-1; $i >= 0; $i--) {
			$shim = '';
			foreach (range( $min, $tree[$i]->level ) as $j) {
				$shim .= $indents[$j];
			}

			if (@$indents[$tree[$i]->level+1] == '.&nbsp;') {
				$twist = '&nbsp;';
			} else {
				$twist = "-&nbsp;";
			}

			if( $_VERSION->PRODUCT == 'Joomla!' && $_VERSION->RELEASE >= 1.5 ) {
				$tree[$i]->group_id = $tree[$i]->id;
			}
			$list[$tree[$i]->group_id] = $shim.$twist.$tree[$i]->name;
			if ($tree[$i]->level < @$tree[$i-1]->level) {
				$indents[$tree[$i]->level+1] = '.&nbsp;';
			}
		}

		ksort($list);
		return $list;
	}
	
	/**
	* Check if the price should be shown including tax 
	* 
	* @author RolandD
	* @todo Figure out where to get the setting from
	* @access public
	* @param 
	* @return bool true if price with tax is shown otherwise false
	*/
	public function showPriceIncludingTax() {
		return true;
	}
}

?>