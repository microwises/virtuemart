<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id: admin.function_list.php 1760 2009-05-03 22:58:57Z Aravot $
* @package VirtueMart
* @subpackage html
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
mm_showMyFileName( __FILE__ );

require_once( CLASSPATH . "pageNavigation.class.php" );
require_once( CLASSPATH . "htmlTools.class.php" );
require_once( CLASSPATH . "usergroup.class.php" );
$vmUserGroup = new vmUserGroup();

// Get module ID
$module_id = JRequest::getVar(  'module_id', 0 );

$q = "SELECT module_name FROM #__{vm}_module WHERE module_id='$module_id'";
$db->query($q);
$db->next_record();
$title = JText::_('VM_FUNCTION_LIST_LBL') . ": " . $db->f("module_name");
if (!empty( $keyword )) {
	$list  = "SELECT * FROM #__{vm}_function WHERE ";
	$count = "SELECT count(*) as num_rows FROM #__{vm}_function WHERE ";
	$q  = "(function_name LIKE '%$keyword%' OR ";
	$q .= "function_perms LIKE '%$keyword%' ";
	$q .= ") ";
	$q .= "AND module_id='$module_id' ";
	$q .= "ORDER BY function_name ";
	$list .= $q . " LIMIT $limitstart, " . $limit;
	$count .= $q;   
}
else {
	$list  = "SELECT * FROM #__{vm}_function WHERE module_id='$module_id' ";
	$list .= "ORDER BY function_name ";
	$list .= "LIMIT $limitstart, " . $limit;
	$count = "SELECT count(*) as num_rows FROM #__{vm}_function ";
	$count .= "WHERE module_id='$module_id' ";
}
$db->query($count);
$db->next_record();
$num_rows = $db->f("num_rows");

$db->query($list);

// Create the Page Navigation
$pageNav = new vmPageNav( $num_rows, $limitstart, $limit );

// Create the List Object with page navigation
$listObj = new listFactory( $pageNav );

// print out the search field and a list heading
$listObj->writeSearchHeader( $title, VM_ADMIN_ICON_URL.'icon_48/vm_functions_48.png', 'admin', 'function_list');

// start the list table
$listObj->startTable();

// these are the columns in the table
$columns = Array(  "#" => "width=\"20\"", 
					"<input type=\"checkbox\" name=\"toggle\" value=\"\" onclick=\"checkAll(".count($db->record) .")\" />" => "width=\"20\"",
					JText::_('VM_FUNCTION_LIST_NAME') => "",
					JText::_('VM_FUNCTION_LIST_CLASS') => "",
					JText::_('VM_FUNCTION_LIST_METHOD') => "" );
$usergroups = $vmUserGroup->get_groups();

while($usergroups->next_record()) {
	$columns[$usergroups->f('group_name')] = 'width="5%"';
	$groupArray[] = $usergroups->f('group_name');
}
$columns['none'] = 'width="5%"';
$usergroups->reset();
$columns['<a href="javascript: document.adminForm.func.value = \'setFunctionPermissions\'; saveorder( '.(count($db->record)-1) .' );"><img src="'.$mosConfig_live_site.'/administrator/images/filesave.png" border="0" width="16" height="16" alt="' . JText::_('SAVE_PERMISSIONS') . '" align="left"/>' . JText::_('SAVE_PERMISSIONS') . '</a>'] = '';

$columns[JText::_('E_REMOVE')] = "width=\"5%\"";

$listObj->writeTableHeader( $columns );

$i = 0;

while ($db->next_record()) {

	$listObj->newRow();
	
	// The row number
	$listObj->addCell( $pageNav->rowNumber( $i ) );
	
	// The Checkbox
	$listObj->addCell( vmCommonHTML::idBox( $i, $db->f("function_id"), false, "function_id" ) );

	$tmp_cell = "<a href=\"". $sess->url( $_SERVER['PHP_SELF']. "?page=admin.function_form&limitstart=$limitstart&keyword=".urlencode($keyword)."&module_id=$module_id&function_id=" . $db->f("function_id")) ."\">";
    $tmp_cell .= $db->f("function_name"). "</a>";
	$listObj->addCell( $tmp_cell );
	
	$listObj->addCell( $db->f("function_class") );
	$listObj->addCell( $db->f("function_method") );
	$function_perms = explode(',', $db->f("function_perms") );
	while($usergroups->next_record()) {
		
		$checked = in_array( $usergroups->f('group_name'), $function_perms ) ? 'checked="checked"' : '';
		$listObj->addCell( '<input type="checkbox" name="function_perms['.$i.']['.$usergroups->f('group_name').']" value="1" '.$checked.' />' );
	}
	
		$checked = in_array( 'none', $function_perms ) ? 'checked="checked"' : '';
		$listObj->addCell( '<input type="checkbox" name="function_perms['.$i.'][none]" value="1" '.$checked.' />' );
		
	$listObj->addCell('');
	$usergroups->reset();

	$listObj->addCell( $ps_html->deleteButton( "function_id", $db->f("function_id"), "functionDelete", $keyword, $limitstart, "&module_id=$module_id" ) );

	$i++;

}
$listObj->writeTable();

$listObj->endTable();

$listObj->writeFooter( $keyword, "&module_id=$module_id" );

?>