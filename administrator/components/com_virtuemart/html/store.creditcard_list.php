<?php 
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id: store.creditcard_list.php 1760 2009-05-03 22:58:57Z Aravot $
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
	$list  = "SELECT * FROM #__{vm}_creditcard WHERE ";
	$count = "SELECT count(*) as num_rows FROM #__{vm}_creditcard WHERE ";
	$q  = "(creditcard_name LIKE '%$keyword%' OR ";
	$q .= "creditcard_code LIKE '%$keyword%') ";
	$q .= "ORDER BY creditcard_name ";
	$list .= $q . " LIMIT $limitstart, " . $limit;
	$count .= $q;   
}
else {
	$list  = "SELECT * FROM #__{vm}_creditcard ";
	$list .= "ORDER BY creditcard_name ";
	$list .= "LIMIT $limitstart, " . $limit;
	$count = "SELECT count(*) as num_rows FROM #__{vm}_creditcard ";
}
$db->query($count);
$db->next_record();
$num_rows = $db->f("num_rows");
  
// Create the Page Navigation
$pageNav = new vmPageNav( $num_rows, $limitstart, $limit );

// Create the List Object with page navigation
$listObj = new listFactory( $pageNav );

// print out the search field and a list heading
$listObj->writeSearchHeader(JText::_('VM_CREDITCARD_LIST_LBL'), VM_ADMIN_ICON_URL .'icon_48'.DS.'vm_credit_48.png', $modulename, "creditcard_list");

// start the list table
$listObj->startTable();

// these are the columns in the table
$columns = Array(  "#" => "width=\"20\"", 
					"<input type=\"checkbox\" name=\"toggle\" value=\"\" onclick=\"checkAll(".$num_rows.")\" />" => "width=\"20\"",
					JText::_('VM_CREDITCARD_NAME') => '',
					JText::_('VM_CREDITCARD_CODE') => '',
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
	$listObj->addCell( vmCommonHTML::idBox( $i, $db->f("creditcard_id"), false, "creditcard_id" ) );
    
    $tmp_cell = '<a href="'. $sess->url($_SERVER['PHP_SELF'] ."?page=store.creditcard_form&limitstart=$limitstart&keyword=".urlencode($keyword)."&creditcard_id=".$db->f("creditcard_id")) .'">'.$db->f("creditcard_name")."</a>";
    $listObj->addCell( $tmp_cell );
	
    $listObj->addCell( $db->f("creditcard_code"));
	
	$listObj->addCell( $ps_html->deleteButton( "creditcard_id", $db->f("creditcard_id"), "creditcardDelete", $keyword, $limitstart ) );

	$i++;
}

$listObj->writeTable();

$listObj->endTable();

$listObj->writeFooter( $keyword );
?>
