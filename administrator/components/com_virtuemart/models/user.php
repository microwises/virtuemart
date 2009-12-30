<?php
/**
 * Data module for shop users
 *
 * @package	VirtueMart
 * @subpackage	User
 * @author	RickG
 * @copyright	Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.model');
jimport('joomla.version');

/**
 * Model class for shop users
 *
 * @package	VirtueMart
 * @subpackage	User
 * @author	RickG
 */
class VirtueMartModelUser extends JModel
{


    /**
     * Return a list of Joomla ACL groups.
     *
     * The returned object list includes a group anme and a group name with spaces
     * prepended to the name for displaying an indented tree.
     *
     * @author RickG
     * @return ObjectList List of acl group objects.
     */
    function getAclGroupIndentedTree() {
	$db = JFactory::getDBO();
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

	$db->setQuery($query);
	return $db->loadObjectList();
    }

}

?>