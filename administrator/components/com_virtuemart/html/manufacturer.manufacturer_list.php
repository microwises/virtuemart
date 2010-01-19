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

$mf_category_id = JRequest::getVar(  'mf_category_id');

if (!empty($keyword)) {
	$list  = "SELECT * FROM #__{vm}_manufacturer WHERE ";
	$count = "SELECT count(*) as num_rows FROM #__{vm}_manufacturer WHERE ";
	$q  = "(mf_name LIKE '%$keyword%' OR ";
	$q .= "mf_desc LIKE '%$keyword%'";
	$q .= ") ";
	$q .= "ORDER BY mf_name ASC ";
	$list .= $q . " LIMIT $limitstart, " . $limit;
	$count .= $q;   
}
elseif (!empty($mf_category_id)) {
	$q = "";
	$list="SELECT * FROM #__{vm}_manufacturer, #__{vm}_manufacturer_category WHERE ";
	$count="SELECT count(*) as num_rows FROM #__{vm}_manufacturer,#__{vm}_manufacturer_category WHERE "; 
	$q = "#__{vm}_manufacturer.mf_category_id=#__{vm}_manufacturer_category.mf_category_id ";
	$q .= "ORDER BY #__{vm}_manufacturer.mf_name ASC ";
	$list .= $q . " LIMIT $limitstart, " . $limit;
	$count .= $q;   
}
else {
	$q = "";
	$list  = "SELECT * FROM #__{vm}_manufacturer ";
	$count = "SELECT count(*) as num_rows FROM #__{vm}_manufacturer ";
	$q .= "ORDER BY mf_name ASC ";
	$list .= $q . " LIMIT $limitstart, " . $limit;
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
$listObj->writeSearchHeader(JText::_('VM_MANUFACTURER_LIST_LBL'), VM_ADMIN_ICON_URL.'icon_48/vm_manufacturer_48.png', $modulename, "manufacturer_list");

// start the list table
$listObj->startTable();

// these are the columns in the table
$columns = Array(  "#" => "width=\"20\"", 
					"<input type=\"checkbox\" name=\"toggle\" value=\"\" onclick=\"checkAll(".$num_rows.")\" />" => "width=\"20\"",
					'ID' => 'width="5%"',
					JText::_('VM_MANUFACTURER_LIST_MANUFACTURER_NAME') => 'width="45%"',
					JText::_('VM_MANUFACTURER_LIST_ADMIN') => 'width="45%"',
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
	$listObj->addCell( vmCommonHTML::idBox( $i, $db->f("manufacturer_id"), false, "manufacturer_id" ) );
	
	$listObj->addCell( $db->f('manufacturer_id') );
	
	$url = $_SERVER['PHP_SELF'] . "?page=$modulename.manufacturer_form&limitstart=$limitstart&keyword=".urlencode($keyword)."&manufacturer_id=";
	$url .= $db->f("manufacturer_id");
	$tmp_cell = "<a href=\"" . $sess->url($url) . "\">"
				. $db->f("mf_name")
				. "</a><br />";
	$listObj->addCell( $tmp_cell );
	
    $tmp_cell = "<a href=\"". $sess->url($_SERVER['PHP_SELF']."?page=$modulename.manufacturer_form&manufacturer_id=" . $db->f("manufacturer_id")) ."\">".JText::_('VM_UPDATE') ."</a>";
    $listObj->addCell( $tmp_cell );
	
	$listObj->addCell( $ps_html->deleteButton( "manufacturer_id", $db->f("manufacturer_id"), "manufacturerDelete", $keyword, $limitstart ) );

	$i++;

}
$listObj->writeTable();

$listObj->endTable();

$listObj->writeFooter( $keyword );

?>