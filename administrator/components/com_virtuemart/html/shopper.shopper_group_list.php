<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id: shopper.shopper_group_list.php 1760 2009-05-03 22:58:57Z Aravot $
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
$q = "";
if (!empty($keyword)) {
	$list = "SELECT * FROM #__{vm}_shopper_group WHERE ";
	$count = "SELECT count(*) as num_rows FROM #__{vm}_shopper_group WHERE ";
	if( !$perm->check("admin")) {
		$q = " vendor_id='$hVendor_id' ";
	}
	$q .= "AND (shopper_group_name LIKE '%$keyword%' ";
	$q .= "OR shopper_group_desc LIKE '%$keyword%' ";
	$q .= ") ";
	$q .= "ORDER BY shopper_group_name "; 
	$list .= $q . " LIMIT $limitstart, " . $limit;
	$count .= $q;   
}
else {

	$list = "SELECT * FROM #__{vm}_shopper_group ";
	$count = "SELECT count(*) as num_rows FROM #__{vm}_shopper_group ";
	if( !$perm->check("admin")) {
		$q = "WHERE vendor_id='$hVendor_id' ";
	}
	$q .= " ORDER BY vendor_id, shopper_group_name "; 
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
$listObj->writeSearchHeader(JText::_('VM_SHOPPER_GROUP_LIST_LBL'), VM_ADMIN_ICON_URL."icon_48".DS."vm_shop_users_48.png", $modulename, "shopper_group_list");

// start the list table
$listObj->startTable();

// these are the columns in the table
$columns = Array(  "#" => "width=\"20\"", 
					"<input type=\"checkbox\" name=\"toggle\" value=\"\" onclick=\"checkAll(".$num_rows.")\" />" => "width=\"20\"",
					JText::_('VM_SHOPPER_GROUP_LIST_NAME') => 'width="30%"',
					JText::_('VM_PRODUCT_FORM_VENDOR') => '',
					JText::_('VM_SHOPPER_GROUP_LIST_DESCRIPTION') => '',
					JText::_('VM_DEFAULT') => '',
					JText::_('E_REMOVE') => "width=\"5%\""
				);
$listObj->writeTableHeader( $columns );

$db->query($list);
$i=0;
while ($db->next_record()) { 
	$listObj->newRow();
	
	// The row number
	$listObj->addCell( $pageNav->rowNumber( $i ) );
	
	// The Checkbox
	$listObj->addCell( vmCommonHTML::idBox( $i, $db->f("shopper_group_id"), false, "shopper_group_id" ) );

	$url = $_SERVER['PHP_SELF'] . "?page=$modulename.shopper_group_form&limitstart=$limitstart&keyword=".urlencode($keyword)."&shopper_group_id=". $db->f("shopper_group_id");
	$tmp_cell = "<a href=\"" . $sess->url($url) . "\">".$db->f("shopper_group_name")."</a>";
	$listObj->addCell( $tmp_cell );
	
	include_class("vendor");
	global $hVendor;
	$listObj->addCell( $hVendor->get_name($db->f("vendor_id")) );
	
    $listObj->addCell( $db->f("shopper_group_desc") );
	$tmp_cell = '<img src="';
	$tmp_cell .= ($db->f("default")=="1") ? $mosConfig_live_site .'/administrator/images/tick.png"' : $mosConfig_live_site.'/administrator/images/publish_x.png"';
	$tmp_cell .= 'border="0" />';
    $listObj->addCell( $tmp_cell );
	
	$listObj->addCell( $ps_html->deleteButton( "shopper_group_id", $db->f("shopper_group_id"), "shopperGroupDelete", $keyword, $limitstart ) );

	$i++;
}
$listObj->writeTable();

$listObj->endTable();

$listObj->writeFooter( $keyword );

?>