<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id: order.order_list.php 1760 2009-05-03 22:58:57Z Aravot $
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
global $page, $ps_order_status;

$show = JRequest::getVar(  "show", "" );
$form_code = "";
require_once( CLASSPATH . "pageNavigation.class.php" );
require_once( CLASSPATH . "htmlTools.class.php" );

$vendor_id = $hVendor->getLoggedVendor();

$list  = "SELECT #__{vm}_orders.order_id,order_status, #__{vm}_orders.cdate,#__{vm}_orders.mdate,order_total,order_currency,#__{vm}_orders.user_id,";
$list .= "first_name, last_name FROM #__{vm}_orders, #__{vm}_order_user_info WHERE ";
$count = "SELECT count(*) as num_rows FROM #__{vm}_orders, #__{vm}_order_user_info WHERE ";
$q = "address_type = 'BT' AND ";
if (!empty($keyword)) {
        $q  .= "(#__{vm}_orders.order_id LIKE '%$keyword%' ";
        $q .= "OR #__{vm}_orders.order_status LIKE '%$keyword%' ";
        $q .= "OR first_name LIKE '%$keyword%' ";
        $q .= "OR last_name LIKE '%$keyword%' ";
		$q .= "OR CONCAT(`first_name`, ' ', `last_name`) LIKE '%$keyword%' ";
        $q .= ") AND ";
}
if (!empty($show)) {
	$q .= "order_status = '$show' AND ";
}
$q .= "(#__{vm}_orders.order_id=#__{vm}_order_user_info.order_id) ";
if (!$perm->check("admin")) {
	$q .= "AND #__{vm}_orders.vendor_id='".$vendor_id."' ";
}

$q .= "ORDER BY #__{vm}_orders.cdate DESC ";
$list .= $q . " LIMIT $limitstart, " . $limit;
$count .= $q;   

$db->query($count);
$db->next_record();

//SELECT count(*) as num_rows FROM #__{vm}_orders, #__{vm}_order_user_info WHERE (#__{vm}_orders.order_id=#__{vm}_order_user_info.order_id) ORDER BY #__{vm}_orders.cdate DESC
//$q = 'SELECT count(*) as num_rows FROM #__{vm}_orders AS o, #__{vm}_order_user_info AS oui WHERE ';
//$list  = "SELECT o.order_id, order_status, \n cdate, mdate, order_total, order_currency, \n o.user_id, ";
//$list .= "first_name, last_name \n FROM #__{vm}_orders o, #__{vm}_order_user_info oui \n WHERE ";
//$q = $list;
//$q .= " o.order_id = oui.order_id ";
//$q .= 'ORDER BY o.cdate DESC ';
//$db->query($q);
	// or reusing of this function would be more OOP
//	require_once( CLASSPATH .'ps_order.php');
//	$db = ps_order::list_order_resultSet();


$num_rows = $db->f("num_rows");

// Create the Page Navigation
$pageNav = new vmPageNav( $num_rows, $limitstart, $limit );

// Create the List Object with page navigation
$listObj = new listFactory( $pageNav );

// print out the search field and a list heading
$listObj->writeSearchHeader(JText::_('VM_ORDER_LIST_LBL'), VM_ADMIN_ICON_URL.'icon_48/vm_orders_48.png', $modulename, "order_list");

?>
<div align="center">
<?php
$navi_db = new ps_DB;
$q = "SELECT order_status_code, order_status_name ";
$q .= "FROM #__{vm}_order_status ";
if (!$perm->check("admin")) {
	$q .= "WHERE vendor_id = '$vendor_id'";
}

$navi_db->query($q);
while ($navi_db->next_record()) {  ?> 
  <a href="<?php $sess->purl($_SERVER['PHP_SELF']."?page=$modulename.order_list&show=".$navi_db->f("order_status_code")) ?>">
  <b><?php echo $navi_db->f("order_status_name")?></b></a>
      | 
<?php 
} 
?>
    <a href="<?php $sess->purl($_SERVER['PHP_SELF']."?page=$modulename.order_list&show=")?>"><b>
    <?php echo JText::_('VM_ALL') ?></b></a>
</div>
<br />
<?php 

$listObj->startTable();

// these are the columns in the table
$checklimit = ($num_rows < $limit) ? $num_rows : $limit;
$columns = Array(  "#" => "width=\"20\"", 
					"<input type=\"checkbox\" name=\"toggle\" value=\"\" onclick=\"checkAll(".$checklimit.")\" />" => "width=\"20\"",
					JText::_('VM_ORDER_LIST_ID') => '',
					JText::_('VM_ORDER_PRINT_NAME') => '',
					JText::_('VM_ORDER_LIST_PRINT_LABEL') => '',
					JText::_('VM_ORDER_LIST_TRACK') => '',
					JText::_('VM_ORDER_LIST_VOID_LABEL') => '',
					JText::_('VM_CHECK_OUT_THANK_YOU_PRINT_VIEW') => '',
					JText::_('VM_ORDER_LIST_CDATE') => '',
					JText::_('VM_ORDER_LIST_MDATE') => '',
					JText::_('VM_ORDER_LIST_STATUS') => '',
					JText::_('VM_UPDATE') => '',
					JText::_('VM_ORDER_LIST_TOTAL') => '',
					JText::_('E_REMOVE') => "width=\"5%\""
				);
$listObj->writeTableHeader( $columns );
// so we can determine if shipping labels can be printed
$dbl =& new ps_DB;

$db->query($list);
$i = 0;
while ($db->next_record()) { 
    
	$listObj->newRow();
	
	// The row number
	$listObj->addCell( $pageNav->rowNumber( $i ) );
		
	// The Checkbox
	$listObj->addCell( vmCommonHTML::idBox( $i, $db->f("order_id"), false, "order_id" ) );
	
	$url = $_SERVER['PHP_SELF']."?page=$modulename.order_print&limitstart=$limitstart&keyword=".urlencode($keyword)."&order_id=". $db->f("order_id");
	$tmp_cell = "<a href=\"" . $sess->url($url) . "\">".sprintf("%08d", $db->f("order_id"))."</a><br />";
	$listObj->addCell( $tmp_cell );

		
	$tmp_cell = $db->f('first_name').' '.$db->f('last_name');
	if( $perm->check('admin') && defined('_VM_IS_BACKEND')) {
		$url = $_SERVER['PHP_SELF']."?page=admin.user_form&amp;user_id=". $db->f("user_id");
		$tmp_cell = '<a href="'.$sess->url( $url ).'">'.$tmp_cell.'</a>';
	}
	
	$listObj->addCell( $tmp_cell );
	
	// Look in #__{vm}_shipping_label for this order and extract the
	// shipping class name.  Then check to see if the shipping module
	// supports generating shipping labels.  If so, add a print icon
	// button for printing the label, otherwise leave the column empty.
	$lq = "SELECT shipper_class, label_is_generated ";
	$lq .= "FROM #__{vm}_shipping_label ";
	$lq .= "WHERE order_id='" . $db->f("order_id") . "'";
	$dbl->query($lq);
	$display_print_label = false;
	$display_track = false;
	$display_void_label = false;
	if ($dbl->next_record()) {
		include_once(CLASSPATH."shipping/" . $dbl->f("shipper_class") .".php");
		eval( "\$ship_class =& new " . $dbl->f("shipper_class") . "();");
		if (is_callable(array($ship_class, 'generate_label')))
			$display_print_label = true;
		if (is_callable(array($ship_class, 'track')) &&
		    $dbl->f('label_is_generated')) {
			// track function must be available and a label must
			// have been generated.
			$display_track = true;
		}
		if (is_callable(array($ship_class, 'void_label')) &&
		    $dbl->f('label_is_generated')) {
			// void_label function must be available and a label must
			// have been generated.
			$display_void_label = true;
		}
	}
	if (!$display_print_label) {
		$listObj->addCell("");
	} else {
		$pl_url = $sess->url($_SERVER['PHP_SELF'] ."?page=order.label_print&order_id=" . $db->f("order_id") . "&no_menu=1&no_html=1");
		$pl_url = stristr($_SERVER['PHP_SELF'], "index2.php") ?
		    str_replace("index2.php", "index3.php", $pl_url) :
		    str_replace("index.php", "index2.php", $pl_url);

		$pl_link = "&nbsp;<a href=\"javascript:void window.open(" .
		    "'$pl_url', 'win2', 'status=yes,toolbar=yes,scrollbars=yes," .
		    "titlebar=yes,menubar=yes,resizable=yes,width=690,height=750," .
		    "directories=no,location=no');\">";
		$pl_link .= "<img src=\"$mosConfig_live_site/images/M_images/printButton.png\" " .
		    "align=\"center\" height=\"16\" width=\"16\" border=\"0\" /></a>";
		$listObj->addCell($pl_link);
	}

	if (!$display_track) {
		$listObj->addCell("");
	} else {
		$tl_url = $sess->url($_SERVER['PHP_SELF'] . "?page=order.label_track&order_id=" . $db->f("order_id") ."&no_menu=1");
		$tl_url = stristr($_SERVER['PHP_SELF'], "index2.php") ?
		    str_replace("index2.php", "index3.php", $tl_url) :
		    str_replace("index.php", "index2.php", $tl_url);

		$tl_link = "&nbsp;<a href=\"javascript:void window.open(" .
		    "'$tl_url', 'win2', 'status=yes,toolbar=yes,scrollbars=yes," .
		    "titlebar=yes,menubar=yes,resizable=yes,width=640,height=480," .
		    "directories=no,location=no');\">";
		$tl_link .= "Track</a>";
		$listObj->addCell($tl_link);
	}

	if (!$display_void_label)
		$listObj->addCell("");
	else {
		$vl_url = $sess->url($_SERVER['PHP_SELF'] ."?page=order.label_void&order_id=" . $db->f("order_id") ."&no_menu=1");
		$vl_url = stristr($_SERVER['PHP_SELF'], "index2.php") ?
		    str_replace("index2.php", "index3.php", $vl_url) :
		    str_replace("index.php", "index2.php", $vl_url);

		$vl_link = "&nbsp;<a href=\"javascript:void window.open(" .
		    "'$vl_url', 'win2', 'status=yes,toolbar=yes,scrollbars=yes," .
		    "titlebar=yes,menubar=yes,resizable=yes,width=640,height=480," .
		    "directories=no,location=no');\">";
		$vl_link .= "Void</a>";
		$listObj->addCell($vl_link);
	}
	
	// Print view URL
	$details_url = $_SERVER['PHP_SELF']."?page=order.order_printdetails&amp;order_id=".$db->f("order_id")."&amp;no_menu=1&pop=1";

//	if( vmIsJoomla( '1.5', '>=' ) ) {
		$details_url .= "&amp;tmpl=component";
//	}
	
	$details_url = $sess->url( $details_url );
    $details_url = defined( '_VM_IS_BACKEND' ) ? str_replace( "index2.php", "index3.php", $details_url ) : str_replace( "index.php", "index2.php", $details_url );
	
    // Print View Icon
    $details_link = "&nbsp;<a href=\"javascript:void window.open('$details_url', 'win2', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');\">";
    $details_link .= "<img src=\"$mosConfig_live_site/images/M_images/printButton.png\" align=\"center\" height=\"16\" width=\"16\" border=\"0\" /></a>"; 
    $listObj->addCell( $details_link );
	// Creation Date
	$listObj->addCell( vmFormatDate($db->f("cdate"), "%d-%b-%y %H:%M"));
	// Last Modified Date
    $listObj->addCell( vmFormatDate($db->f("mdate"), "%d-%b-%y %H:%M"));
	
    // Order Status Drop Down List
	$listObj->addCell( $ps_order_status->getOrderStatusList($db->f("order_status"), "onchange=\"document.adminForm$i.order_status.selectedIndex = this.selectedIndex;document.adminForm$i.changed.value='1'\""));
	
	// Notify Customer checkbox
	$listObj->addCell( '<input type="checkbox" class="inputbox" onclick="if(this.checked==true) {document.adminForm'. $i .'.notify_customer.value = \'Y\';} else {document.adminForm'. $i .'.notify_customer.value = \'N\';}" value="Y" />'
						.JText::_('VM_ORDER_LIST_NOTIFY') .'<br />
					<input type="button" class="button" onclick="if(document.adminForm'. $i .'.changed.value!=\'1\') { alert(\''. addslashes(JText::_('VM_ORDER_LIST_NOTIFY_ERR')) .'\'); return false;} else adminForm'.$i.'.submit();" name="Submit" value="'.JText::_('VM_UPDATE_STATUS').'" />' );

	$listObj->addCell( $GLOBALS['CURRENCY_DISPLAY']->getFullValue($db->f("order_total"), '', $db->f('order_currency')));
	// Change Order Status form
	$form_code .= '<form style="float:left;" method="post" action="'. $_SERVER['PHP_SELF'] .'" name="adminForm'. $i .'">';
	$form_code .= $ps_order_status->getOrderStatusList($db->f("order_status"), "style=\"visibility:hidden;\" onchange=\"document.adminForm$i.changed.value='1'\"");
	$form_code .= '<input type="hidden" class="inputbox" name="notify_customer" value="N" />
		<input type="hidden" name="page" value="order.order_list" />
		<input type="hidden" name="func" value="orderStatusSet" />
		<input type="hidden" name="vmtoken" value="'. vmSpoofValue($sess->getSessionId()) .'" />
		<input type="hidden" name="changed" value="0" />
		<input type="hidden" name="option" value="com_virtuemart" />
		<input type="hidden" name="order_id" value="'. $db->f("order_id") .'" />
		<input type="hidden" name="current_order_status" value="'. $db->f("order_status").'" />
		</form>';
	
    // Delete Order Button
	$listObj->addCell( $ps_html->deleteButton( "order_id", $db->f("order_id"), "orderDelete", $keyword, $limitstart ) );

	$i++; 
}
$listObj->writeTable();

$listObj->endTable();

$listObj->writeFooter( $keyword, "&show=$show" );

echo $form_code;
?>


