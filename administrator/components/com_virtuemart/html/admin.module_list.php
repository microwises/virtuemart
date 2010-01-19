<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id$
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
require_once( CLASSPATH . "htmlTools.class.php" );
require_once( CLASSPATH . "usergroup.class.php" );
$vmUserGroup = new vmUserGroup();

if (!empty($keyword)) {

	$list  = "SELECT * FROM #__{vm}_module WHERE ";
	$count = "SELECT count(*) as num_rows FROM #__{vm}_module WHERE ";
	$q  = "(module_name LIKE '%$keyword%' OR ";
	$q .= "module_description LIKE '%$keyword%'";
	$q .= ") ";
	$q .= "ORDER BY list_order ASC ";
	$list .= $q . " LIMIT $limitstart, " . $limit;
	$count .= $q;
}
else {
	$q = "";
	$list  = "SELECT * FROM #__{vm}_module ORDER BY list_order ASC ";
	$count = "SELECT count(*) as num_rows FROM #__{vm}_module ORDER BY list_order ";
	$list .= $q . " LIMIT $limitstart, " . $limit;
	$count .= $q;
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
$listObj->writeSearchHeader(JText::_('VM_MODULE_LIST_LBL'), VM_ADMIN_ICON_URL.'icon_48/vm_modules_48.png', "admin", "module_list");

// start the list table
$listObj->startTable();

// these are the columns in the table
$columns = Array(  "#" => 'width="3%"',
					'<input type="checkbox" name="toggle" value="" onclick="checkAll('.count($db->record).')" />' => 'width="3%"',
					JText::_('VM_MODULE_LIST_NAME') => 'width="20%"'
				);
$usergroups = $vmUserGroup->get_groups();

while($usergroups->next_record()) {
	$columns[$usergroups->f('group_name')] = 'width="5%"';
	$groupArray[] = $usergroups->f('group_name');
}
$columns['none'] = 'width="5%"';
$usergroups->reset();
$columns['<a href="javascript: document.adminForm.func.value = \'setModulePermissions\'; saveorder( '.(count($db->record)-1).' );"><img src="'.$mosConfig_live_site.'/administrator/images/filesave.png" border="0" width="16" height="16" alt="' . JText::_('SAVE_PERMISSIONS') . '" align="left"/>' . JText::_('SAVE_PERMISSIONS') . '</a>'] = '';

$columns[JText::_('VM_MODULE_LIST_FUNCTIONS')] = 'width="10%"';
$columns[JText::_('VM_FIELDMANAGER_REORDER')] = "width=\"5%\"";
$columns[vmCommonHTML::getSaveOrderButton( (count($db->record)-1), 'changeordering' )] = 'width="8%"';
$columns[JText::_('E_REMOVE')] = "width=\"5%\"";

$listObj->writeTableHeader( $columns );

$i = 0;
while ($db->next_record()) {

	$listObj->newRow();

	// The row number
	$listObj->addCell( $pageNav->rowNumber( $i ) );

	// The Checkbox
	$listObj->addCell( vmCommonHTML::idBox( $i, $db->f("module_id"), false, "module_id" ) );
	$link = "<a href=\"". $sess->url( $_SERVER['PHP_SELF'] . "?page=$modulename.module_form&limitstart=$limitstart&module_id=" . $db->f("module_id"));
	if( $vmLayout != 'standard' ) {
		$link .= "&no_menu=1&tmpl=component";
		$link = defined('_VM_IS_BACKEND') 
			? str_replace('index2.php', 'index3.php', str_replace('index.php', 'index3.php', $link )) 
			: str_replace('index.php', 'index2.php', $link );
	}
	$tmp_cell = $link."\">".$db->f("module_name")."</a>";
	$listObj->addCell( $tmp_cell );
	$module_perms = explode(',', $db->f("module_perms") );
	while($usergroups->next_record()) {
		
		$checked = in_array( $usergroups->f('group_name'), $module_perms ) ? 'checked="checked"' : '';
		if( $db->f("module_name") == 'admin' && $usergroups->f('group_name') == 'admin' ) {
			$type = 'hidden';
		} else {
			$type = 'checkbox';
		}
		$listObj->addCell( '<input type="'.$type.'" name="module_perms['.$i.']['.$usergroups->f('group_name').']" value="1" '.$checked.' />' );
	}
	
		$checked = in_array( 'none', $module_perms ) ? 'checked="checked"' : '';
		$listObj->addCell( '<input type="checkbox" name="module_perms['.$i.'][none]" value="1" '.$checked.' />' );
		
	$listObj->addCell('');
	$usergroups->reset();
	$link = "<a href=\"".$sess->url($_SERVER['PHP_SELF']."?page=$modulename.function_list&module_id=" . $db->f("module_id"));
	if( $vmLayout != 'standard' ) {
		$link .= "&no_menu=1&tmpl=component";
		$link = defined('_VM_IS_BACKEND') 
			? str_replace('index2.php', 'index3.php', str_replace('index.php', 'index3.php', $link )) 
			: str_replace('index.php', 'index2.php', $link );
	}
	$tmp_cell = $link."\">". JText::_('VM_FUNCTION_LIST_LBL') ."</a>";
	$listObj->addCell( $tmp_cell );

	$tmp_cell = "<div align=\"center\">"
	. $pageNav->orderUpIcon( $i, $i > 0, "orderup", JText::_('CMN_ORDER_UP'), $page, "changeordering" )
	. "\n&nbsp;"
	. $pageNav->orderDownIcon( $i, $db->num_rows(), $i-1 <= $db->num_rows(), 'orderdown', JText::_('CMN_ORDER_DOWN'), $page, "changeordering" )
	. "</div>";
	$listObj->addCell( $tmp_cell );

	$listObj->addCell( vmCommonHTML::getOrderingField( $db->f('list_order') ) );

	$listObj->addCell( $ps_html->deleteButton( "module_id", $db->f("module_id"), "moduleDelete", $keyword, $limitstart ) );

	$i++;
}

$listObj->writeTable();

$listObj->endTable();

$listObj->writeFooter( $keyword );

?>