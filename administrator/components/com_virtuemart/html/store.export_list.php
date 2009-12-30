<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: store.export_list.php 1760 2009-05-03 22:58:57Z Aravot $
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

if (!empty($keyword)) {
	$list  = "SELECT * FROM #__{vm}_export WHERE ";
	$count = "SELECT count(*) as num_rows FROM #__{vm}_export WHERE ";
	$q  = "(#__{vm}_export.export_name LIKE '%$keyword%' ";
	$q .= "AND #__{vm}_export.vendor_id='$hVendor_id' ";
	$q .= ") ";
	$list .= $q . " LIMIT $limitstart, " . $limit;
	$count .= $q;
}
else {
	$q = "";
	$list  = "SELECT * FROM #__{vm}_export WHERE ";
	$count = "SELECT count(*) as num_rows FROM #__{vm}_export WHERE ";
	$q .= "#__{vm}_export.vendor_id='$hVendor_id' ";
	$list .= $q;
	$list .= "LIMIT $limitstart, " . $limit;
	$count .= $q;
}
$db->query($count);
$db->next_record();
$num_rows = $db->f("num_rows");

// Create the Page Navigation
$pageNav = new vmPageNav( $num_rows, $limitstart, $limit );

// Create the List Object with page navigation
$listObj = new listFactory( $pageNav );

// print out the search field and a list heading
$listObj->writeSearchHeader(JText::_('VM_ORDER_EXPORT_MODULE_LIST_LBL'), IMAGEURL."ps_image/modules.gif", $modulename, "export_list");

// start the list table
$listObj->startTable();

// these are the columns in the table
$columns = Array(  "#" => "width=\"20\"",
"<input type=\"checkbox\" name=\"toggle\" value=\"\" onclick=\"checkAll(".$num_rows.")\" />" => "width=\"20\"",
JText::_('VM_ORDER_EXPORT_MODULE_LIST_NAME') => '',
JText::_('VM_ORDER_EXPORT_MODULE_LIST_DESC') => '',
JText::_('VM_ISSHIP_LIST_PUBLISH_LBL') => '',
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
	$listObj->addCell( vmCommonHTML::idBox( $i, $db->f("export_id"), false, "export_id" ) );

	$url = $_SERVER['PHP_SELF'] . "?page=$modulename.export_form&limitstart=$limitstart&keyword=".urlencode($keyword)."&export_id=".$db->f("export_id");
	$tmp_cell = "<a href=\"" . $sess->url($url) . "\">". $db->f('export_name')."</a>";
	$listObj->addCell( $tmp_cell );

	$description = explode("\n", $db->f('export_desc'));
	$listObj->addCell( $description[0] );

	$tmpcell = "<a href=\"". $sess->url( $_SERVER['PHP_SELF']."?page=$page&export_id=".$db->f("export_id")."&func=changePublishState" );
	if ($db->f("export_enabled")=='N') {
		$tmpcell .= "&task=publish\">";
	}
	else {
		$tmpcell .= "&task=unpublish\">";
	}
	$tmpcell .= vmCommonHTML::getYesNoIcon( $db->f("export_enabled"), "Publish", "Unpublish" );
	$tmpcell .= "</a>";
	$listObj->addCell( $tmpcell );

	$listObj->addCell( $ps_html->deleteButton( "export_id", $db->f("export_id"), "ExportDelete", $keyword, $limitstart ) );

	$i++;
}
$listObj->writeTable();

$listObj->endTable();

$listObj->writeFooter( $keyword );
?>