<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: admin.plugin_list.php 1755 2009-05-01 22:45:17Z rolandd $
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2008 soeren - All rights reserved.
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
require_once( CLASSPATH . "pluginEntity.class.php" );
$folder = vmget( $_REQUEST, 'folder');
$folderSel = vmget( $_REQUEST, 'folderSel');
if( !empty( $folderSel) && strstr(__FILE__, $page )) {
	$folder = $folderSel;
}
if (!empty($keyword)) {

	$list  = "SELECT * FROM #__{vm}_plugins WHERE ";
	$count = "SELECT count(*) as num_rows FROM #__{vm}_plugins WHERE ";
	$q  = "(name LIKE '%$keyword%' OR ";
	$q .= "element LIKE '%$keyword%'";
	$q .= ") ";
	if( $folder != '') {
		$q .= 'AND folder=\''.$db->getEscaped($folder).'\'';
	}
    if( !$perm->check('admin')) {
    	$q.= ' AND vendor_id='.$hVendor_id;
    }
	$q .= " ORDER BY ordering ASC ";
	$list .= $q . " LIMIT $limitstart, " . $limit;
	$count .= $q;
}
else {
	$q = "WHERE 1=1";
	if( $folder != '') {
		$q .= ' AND folder=\''.$db->getEscaped($folder).'\' ';
	}
    if( !$perm->check('admin')) {
    	$q.= ' AND vendor_id='.$hVendor_id;
    }
	$list  = "SELECT * FROM #__{vm}_plugins ";
	$count = "SELECT COUNT(*) as num_rows FROM #__{vm}_plugins ";
	$list .= $q . " ORDER BY ordering LIMIT $limitstart, " . $limit;
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
$listObj->writeSearchHeader(JText::_('Plugin List'), VM_ADMIN_ICON_URL.'icon_48/vm_modules_48.png', "admin", "plugin_list");

// Hide the drop-down list of plugin types if this page was included by another page
if( strstr(__FILE__, $page ) ) {
	echo '<div align="right">Filter by Plugin Type: '.vmPluginEntity::get_folder_dropdown('folderSel', $folder ).'</div>';
}

// start the list table
$listObj->startTable();

// these are the columns in the table
$columns = Array(  "#" => 'width="3%"',
					'<input type="checkbox" name="toggle" value="" onclick="checkAll('.count($db->record).')" />' => 'width="3%"',
					JText::_('Name') => 'width="20%"',
					JText::_('Type') => "width=\"10%\"",
					JText::_('Element') => "width=\"10%\"",
					JText::_('CMN_PUBLISHED') => 'width="5%"',
					JText::_('VM_FIELDMANAGER_REORDER') => "width=\"5%\"",
					vmCommonHTML::getSaveOrderButton( (count($db->record)-1), 'changeordering' ) => 'width="8%"',
					JText::_('Id') => "width=\"5%\"",
					);

$listObj->writeTableHeader( $columns );

$i = 0;
while ($db->next_record()) {
	$listObj->newRow();
	// The row number
	$listObj->addCell( $pageNav->rowNumber( $i ) );
	// The Checkbox
	$listObj->addCell( vmCommonHTML::idBox( $i, $db->f("id"), false, "id" ) );

	$tmp_cell = "<a href=\"". $sess->url( $_SERVER['SCRIPT_NAME'] . "?id=".$db->f('id')."&page=$modulename.".str_replace('_list', '_form', $pagename)."&limitstart=$limitstart")."\">";
	$tmp_cell .= $db->f("name")."</a>";
	$listObj->addCell( $tmp_cell );
	
	$listObj->addCell( $db->f('folder') );
	$listObj->addCell( $db->f('element') );
	
	$tmpcell = "<a href=\"". $sess->url( $_SERVER['SCRIPT_NAME']."?page=admin.plugin_form&folder=$folder&plugin_id=".$db->f("id")."&id=".$db->f("id")."&func=changePublishState" );
	$tmpcell .= $db->f("published")=='0' ? '&task=publish">' : '&task=unpublish">';
	$tmpcell .= vmCommonHTML::getYesNoIcon($db->f('published'));
	$tmpcell .= '</a>';
	$listObj->addCell( $tmpcell );
	
	$tmp_cell = "<div align=\"center\">"
	. $pageNav->orderUpIcon( $i, $i > 0, "orderup", JText::_('CMN_ORDER_UP'), $page, "changeordering" )
	. "\n&nbsp;"
	. $pageNav->orderDownIcon( $i, $db->num_rows(), $i-1 <= $db->num_rows(), 'orderdown', JText::_('CMN_ORDER_DOWN'), $page, "changeordering" )
	. "</div>";
	$listObj->addCell( $tmp_cell );

	$listObj->addCell( vmCommonHTML::getOrderingField( $db->f('ordering') ) );

	$listObj->addCell($db->f('id'));

	$i++;
}

$listObj->writeTable();

$listObj->endTable();

$listObj->writeFooter( $keyword, '&folder='.$folder );

?>