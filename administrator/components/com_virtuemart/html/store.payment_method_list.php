<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id: store.payment_method_list.php 1760 2009-05-03 22:58:57Z Aravot $
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2004-2009 soeren - All rights reserved.
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
	$list  = "SELECT * FROM #__{vm}_payment_method LEFT JOIN #__{vm}_shopper_group ";
	$list .= "ON #__{vm}_payment_method.shopper_group_id=#__{vm}_shopper_group.shopper_group_id WHERE ";
	$count = "SELECT count(*) as num_rows FROM #__{vm}_payment_method LEFT JOIN #__{vm}_shopper_group ";
	$count .= "ON #__{vm}_payment_method.shopper_group_id=#__{vm}_shopper_group.shopper_group_id WHERE ";
	$q  = "(#__{vm}_payment_method.name LIKE '%$keyword%' ";
//	$q .= "AND #__{vm}_payment_method.vendor_id='$hVendor_id' ";   //Not vendorrelated yet TODO Max Milbers
	$q .= ") ";
	$q .= "ORDER BY #__{vm}_payment_method.ordering,#__{vm}_payment_method.name ";
	$list .= $q . " LIMIT $limitstart, " . $limit;
	$count .= $q;   
}
else {
	$q = "";
	$list = "SELECT * FROM #__{vm}_payment_method LEFT JOIN #__{vm}_shopper_group ";
	$list .= "ON #__{vm}_payment_method.shopper_group_id=#__{vm}_shopper_group.shopper_group_id WHERE ";
	$count = "SELECT count(*) as num_rows FROM #__{vm}_payment_method LEFT JOIN #__{vm}_shopper_group ";
	$count .= "ON #__{vm}_payment_method.shopper_group_id=#__{vm}_shopper_group.shopper_group_id WHERE ";
	$q .= "#__{vm}_payment_method.vendor_id='1' ";  //Not vendorrelated yet TODO Max Milbers
	$list .= $q;
	$list .= "ORDER BY #__{vm}_payment_method.ordering,#__{vm}_payment_method.name ";
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
$listObj->writeSearchHeader(JText::_('VM_PAYMENT_METHOD_LIST_LBL'), VM_ADMIN_ICON_URL.'icon_48/vm_payment_48.png', $modulename, "payment_method_list");

// start the list table
$listObj->startTable();

// these are the columns in the table
$columns = Array(  "#" => "width=\"20\"", 
					"<input type=\"checkbox\" name=\"toggle\" value=\"\" onclick=\"checkAll(".$num_rows.")\" />" => "width=\"20\"",
					JText::_('VM_PAYMENT_METHOD_LIST_NAME') => '',
					'Element' => '',
					JText::_('VM_PAYMENT_METHOD_LIST_DISCOUNT') => '',
					JText::_('VM_PAYMENT_METHOD_LIST_SHOPPER_GROUP') => '',
					JText::_('VM_PAYMENT_METHOD_LIST_ENABLE_PROCESSOR') => '',
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
	$listObj->addCell( vmCommonHTML::idBox( $i, $db->f("id"), false, "id" ) );

	$url = $_SERVER['PHP_SELF'] . "?page=$modulename.payment_method_form&limitstart=$limitstart&keyword=".urlencode($keyword)."&id=".$db->f("id");
	$tmp_cell = "<a href=\"" . $sess->url($url) . "\">". $db->f("name")."</a>";
	$listObj->addCell( $tmp_cell );
	
	$listObj->addCell(  $db->f("element") );
	if( $db->f('discount_is_percentage')) {
		$tmp_cell = $db->f("discount").'%';
	}
	else {
		$tmp_cell = $GLOBALS['CURRENCY_DISPLAY']->getFullValue( $db->f("discount") );
	}
	$listObj->addCell( $tmp_cell );
	
	$shopper_group_name = $db->f("shopper_group_name");
	$tmp_cell = empty( $shopper_group_name ) ? '' : $shopper_group_name;
    $listObj->addCell( $tmp_cell );
    
	$type = $db->f("type");
	switch($type) { 
		case "Y": 
			$tmp_cell = JText::_('VM_PAYMENT_FORM_USE_PP');
			break;
		case "N":
			$tmp_cell = JText::_('VM_PAYMENT_FORM_AO');
			break;
		case "B":
			$tmp_cell = JText::_('VM_PAYMENT_FORM_BANK_DEBIT');
			break;
		case "P":
			$tmp_cell = JText::_('VM_PAYMENT_FORM_FORMBASED');
			break;
		default:
			$tmp_cell = JText::_('VM_PAYMENT_FORM_CC');
			break;
	}
	$listObj->addCell( $tmp_cell );
    
	
	$tmpcell = "<a href=\"". $sess->url( $_SERVER['PHP_SELF']."?page=$page&id=".$db->f("id")."&func=changePublishState" );
	if ($db->f("published")=='N') {
		$tmpcell .= "&task=publish\">";
	} 
	else { 
		$tmpcell .= "&task=unpublish\">";
	}
	$tmpcell .= vmCommonHTML::getYesNoIcon( $db->f("published"), JText::_('CMN_PUBLISH'), JText::_('CMN_UNPUBLISH') );
	$tmpcell .= "</a>";
	$listObj->addCell( $tmpcell );
	
	$listObj->addCell( $ps_html->deleteButton( "id", $db->f("id"), "paymentMethodDelete", $keyword, $limitstart ) );

	$i++;
}
$listObj->writeTable();

$listObj->endTable();

$listObj->writeFooter( $keyword );
?>