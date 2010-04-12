<?php
/**
 *
 * Data module for shop users
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

// Hardcoded groupID of the Super Admin
define ('__SUPER_ADMIN_GID', 25);

// Load the model framework
jimport('joomla.application.component.model');
jimport('joomla.version');

// Get the helpers we need here
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'shoppergroup.php');

/**
 * Model class for shop users
 *
 * @package	VirtueMart
 * @subpackage	User
 * @author	RickG
 */
class VirtueMartModelUser extends JModel {

	/** @var integer Primary key */
	var $_id;
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

		// Get the pagination request variables
		$mainframe = JFactory::getApplication() ;
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest(JRequest::getVar('option').'.limitstart', 'limitstart', 0, 'int');

		// Set the state pagination variables
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		// Get the (array of) order status ID(s)
		$idArray = JRequest::getVar('cid',  0, '', 'array');
		$this->setId((int)$idArray[0]);
	}

	/**
	 * Resets the user id and data
	 */
	function setId($id)
	{
		$this->_id = $id;
		$this->_data = null;
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
	function getUser()
	{
		if (empty($this->_data)) {
			$this->_data = new stdClass();
			$this->_data->JUser =& JUser::getInstance($this->_id);
			$_ui = $this->_getList('SELECT user_info_id FROM #__vm_user_info WHERE user_id = ' . $this->_id);
			for ($i = 0, $n = count($_ui); $i < $n; $i++) {
				$_ui_id = $_ui[$i]->user_info_id;
				$this->_data->userInfo[$_ui_id] = $this->_loadUserInfo($_ui_id);
			}
			$_vid = $this->_getList('SELECT vendor_id FROM #__vm_auth_user_vendor WHERE user_id = ' . $this->_id);
			$this->_data->vendor_id = $_vid[0];
		}

		if (!$this->_data) {
			$this->_data = new stdClass();
			$this->_id = 0;
			$this->_data = null;
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
			return $_contacts[0];
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

		$_usr = $_aclObject->get_object_id ('users', $this->_data->JUser->get('id'), 'ARO');
		$_grp = $_aclObject->get_object_groups ($_usr, 'ARO');
		$_grpName = strtolower ($_aclObject->get_group_name($_grp[0], 'ARO'));

		$_currentUser =& JFactory::getUser();
		$_my_usr = $_aclObject->get_object_id ('users', $_currentUser->get('id'), 'ARO');
		$_my_grp = $_aclObject->get_object_groups ($_my_usr, 'ARO');
		$_my_grpName = strtolower ($_aclObject->get_group_name($_my_grp[0], 'ARO'));

		if ( $_grpName == $_my_grpName && $_my_grpName == 'administrator' ) {
			// administrators can't change each other
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
	 * Bind the post data to the JUser object and the VM tables save it
	 *
	 * @return boolean True is the save was successful, false otherwise.
	 */
	function store()
	{
		global $mainframe;
		$_data = JRequest::get('post');
		$_currentUser =& JFactory::getUser();

		$_new = ($_data['user_id'] < 1);
		$_user =& new JUser($_data['user_id']);
		$_gid = $_user->get('gid'); // Save original gid

		$_data['username']	= JRequest::getVar('username', '', 'post', 'username');
		$_data['password']	= JRequest::getVar('password', '', 'post', 'string', JREQUEST_ALLOWRAW);
		$_data['password2']	= JRequest::getVar('password2', '', 'post', 'string', JREQUEST_ALLOWRAW);

		// Bind Joomla userdata
		if (!$_user->bind($_data)) {
			$this->setError($_user->getError());
			return false;
		}

		$option = JRequest::getCmd( 'option');
		// If an exising superadmin gets a new group, make sure enough admins are left...
		if (!$_new && $_user->get('gid') != $_gid && $_gid == __SUPER_ADMIN_GID) {
			if ($this->getSuperAdminCount() <= 1) {
				$this->setError(JText::_('VM_USER_ERR_ONLYSUPERADMIN'));
				return false;
			}
		}

		// Save the JUser object
		if (!$_user->save()) {
			$this->setError($_user->getError());
			return false;
		}

		if ($_new) {
			$_fromMail = $mainframe->getCfg('mailfrom') || $_currentUser->get('email');
			$_fromName = $mainframe->getCfg('fromname') || $_currentUser->get('name');
			$_fromSite = $mainframe->getCfg('sitename');
			
			$_subj = JText::_('NEW_USER_MESSAGE_SUBJECT');
			$_text = sprintf ( JText::_('NEW_USER_MESSAGE')
				, $_user->get('name')
				, $_fromSite
				, JURI::root()
				, $_user->get('username')
				, $user->password_clear
			);
			JUtility::sendMail( $adminEmail, $adminName, $user->get('email'), $subject, $message );
			$_newId = $_user->get('id');
		}
		return true;
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
		$shopper_vendor_xref =& $this->getTable('shopper_vendor_xref');

		foreach($userIds as $userId) {
			if (!$userInfo->delete($userId)) {
				$this->setError($userInfo->getError());
				return false;
			}
			if (!$shopper_vendor_xref->delete($userId)) {
				$this->setError($shopper_vendor_xref->getError()); // Signal but continue
				continue;
			}
			$_JUser =& JUser::getInstance($userId);
			if (!$_JUser->delete()) {
				$this->setError($jUser->getError());
				return false;
			}
		}
		return true;
	}

	/**
	 * Retrieve a list of users from the database.
	 *
	 * @return object List of user objects
	 */
	function getUserList()
	{
		if (!$this->_data) {
			$query = $this->_getListQuery();
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}
		return $this->_data;
	}

	/**
	 * Retrieve a list of addresses for a user
	 * 
	 *  @param $_uid int User ID
	 *  @param $_type string, addess- type, ST (ShipTo, default) or BT (BillTo)
	 */
	function getUserAddress($_uid = 0, $_type = 'ST')
	{
		$_q = 'SELECT * '
			. ' FROM #__vm_user_info '
			. " WHERE user_id='" . (($_uid==0)?$this->_id:$_uid) . "' "
			. " AND address_type='$_type'";
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
		$_q = "SELECT `customer_number` FROM `#__{vm}_shopper_vendor_xref` "
			."WHERE `user_id`='" . (($_id==0)?$this->_id:$_id) . "' ";
		$this->_db->query($_q);
		$this->_db->next_record();

		return $this->_db->f("customer_number");
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
		global $mainframe, $option;

		$filter_order_Dir = $mainframe->getUserStateFromRequest( $option.'filter_order_Dir', 'filter_order_Dir', 'asc', 'word' );
		$filter_order     = $mainframe->getUserStateFromRequest( $option.'filter_order', 'filter_order', 'id', 'cmd' );

		return (' ORDER BY '.$filter_order.' '.$filter_order_Dir);
	}

	/**
	 * Build the query to list all Users
	 *
	 * @return string SQL query statement
	 */
	function _getListQuery ()
	{
		// User named fields here since user can have multiple user_info records
		$query = 'SELECT DISTINCT ju.id AS id '
			. ', ju.name AS name'
			. ', ju.username AS username '
			. ', vu.user_is_vendor AS is_vendor'
			. ', vu.perms AS perms'
			. ', ju.usertype AS usertype'
			. ", IFNULL(sg.shopper_group_name, '') AS shopper_group_name "
			. 'FROM #__users AS ju '
			. 'LEFT JOIN #__vm_user_info AS vu ON ju.id = vu.user_id '
			. 'LEFT JOIN #__vm_shopper_vendor_xref AS vx ON ju.id = vx.user_id '
			. 'LEFT JOIN #__vm_shopper_group AS sg ON vx.vendor_id = sg.vendor_id '
			. 'AND vx.shopper_group_id = sg.shopper_group_id ';
		$query .= $this->_getFilter();
		$query .= $this->_getOrdering();
		return ($query);
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
		if (count( $id ))
		{
			JArrayHelper::toInteger($id);
			$ids = implode( ',', $id );

			$query = 'UPDATE `#__vm_user_info`'
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
		$version = new JVersion();

		if (version_compare($version->getShortVersion(), '1.6.0', '>=' ) == 1) {
			$query = 'SELECT `node`.`name`, CONCAT(REPEAT("&nbsp;&nbsp;&nbsp;", (COUNT(`parent`.`name`) - 1)), `node`.`name`) AS `text` ';
			$query .= 'FROM `#__usergroups` AS node, `#__core_acl_aro_groups` AS parent ';
			$query .= 'WHERE `node`.`lft` BETWEEN `parent`.`lft` AND `parent`.`rgt` ';
			$query .= 'GROUP BY `node`.`name` ';
			$query .= 'ORDER BY `node`.`lft`';
		}
		else {
			$query = 'SELECT `node`.`name`, CONCAT(REPEAT("&nbsp;&nbsp;&nbsp;", (COUNT(`parent`.`name`) - 1)), `node`.`name`) AS `text` ';
			$query .= 'FROM `#__core_acl_aro_groups` AS node, `#__core_acl_aro_groups` AS parent ';
			$query .= 'WHERE `node`.`lft` BETWEEN `parent`.`lft` AND `parent`.`rgt` ';
			$query .= 'AND `parent`.`lft` > 2 ';
			$query .= 'GROUP BY `node`.`name` ';
			$query .= 'ORDER BY `node`.`lft`';
		}

		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}
}


//No Closing tag