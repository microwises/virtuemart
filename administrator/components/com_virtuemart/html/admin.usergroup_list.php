<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id: admin.usergroup_list.php 1755 2009-05-01 22:45:17Z rolandd $
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2004-2007 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/
mm_showMyFileName( __FILE__ );
require_once( CLASSPATH . "pageNavigation.class.php" );
require_once( CLASSPATH . "usergroup.class.php" );

$usergroup = new vmUserGroup();

$where = array();
$q = '';
if (!empty($keyword)) {
	$where[]  = "(group_name LIKE '%$keyword%' )";
}
if( !empty( $where )) {
	$q = ' WHERE '.implode(' AND ', $where );
}
$count = "SELECT count(*) as num_rows FROM ".$usergroup->_table_name . $q;

$list  = "SELECT * FROM ".$usergroup->_table_name. $q;
$list .= "\nORDER BY group_level, group_name";
$list .= "\nLIMIT $limitstart, " . $limit;

$db->query($count);
$db->next_record();
$num_rows = $db->f("num_rows");

// Create the Page Navigation
$pageNav = new vmPageNav( $num_rows, $limitstart, $limit );

// Create the List Object with page navigation
$listObj = new listFactory( $pageNav );

// print out the search field and a list heading
$listObj->writeSearchHeader(JText::_('VM_USERGROUP_LIST_LBL'), VM_ADMIN_ICON_URL.'icon_48/vm_shoppers_48.png', "admin", "usergroup_list");

// start the list table
$listObj->startTable();

// these are the columns in the table
$columns = Array(  "#" => "", 
					"<input type=\"checkbox\" name=\"toggle\" value=\"\" onclick=\"checkAll(".$num_rows.")\" />" => "",
					JText::_('VM_USERGROUP_NAME') => "width=\"40%\"",
					JText::_('VM_USERGROUP_LEVEL') => "width=\"20%\"",
					JText::_('E_REMOVE') => "width=\"5%\""
				);
$listObj->writeTableHeader( $columns );

$db->query($list);
$i = 0;

while ($db->next_record()) {
	
	
	$listObj->newRow();
	
	// The row number
	$listObj->addCell( $pageNav->rowNumber( $i ) );
		
	// The Checkbox
	$listObj->addCell( vmCommonHTML::idBox( $i, $db->f("group_id"), false, "group_id" ) );
	
	if( in_array( $db->f('group_name'), $usergroup->_protected_groups ))  {
		$tmp_cell = $db->f("group_name");
	} else {
		$tmp_cell = "<a href=\"". $sess->url($_SERVER['PHP_SELF'] ."?page=admin.usergroup_form&limitstart=$limitstart&keyword=".urlencode($keyword)."&group_id=".$db->f("group_id")) ."\">";
		$tmp_cell .= $db->f("group_name") ."</a>";
	}
	$listObj->addCell( $tmp_cell );
	
	$listObj->addCell( $db->f("group_level") );
	
	$listObj->addCell( $ps_html->deleteButton( "group_id", $db->f("group_id"), "userGroupDelete", $keyword, $limitstart ) );
	
		$i++;
	
}
$listObj->writeTable();

$listObj->endTable();

$listObj->writeFooter( $keyword );
?>
