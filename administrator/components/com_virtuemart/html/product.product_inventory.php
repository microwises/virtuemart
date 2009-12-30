<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id: product.product_inventory.php 1760 2009-05-03 22:58:57Z Aravot $
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
/**
* @20090204 Hack from toivo.talikka@totaldata.biz  stock on hand listing for VM 1.0
* 			stock on hand listing sorted by stock quantity, parent products excluded
*			For VM 1.1.X: _fD_ u.bruelhart@frefeldigital.com
*/

mm_showMyFileName( __FILE__ );

$category_id = JRequest::getVar( 'category_id', null );
$allproducts = JRequest::getVar( 'allproducts', 0 );
// 20090204
$stockonhand = JRequest::getVar( 'stockonhand', 0 );

require_once( CLASSPATH . "pageNavigation.class.php" );
require_once( CLASSPATH . "htmlTools.class.php" );

global $hVendor;
//by Max Milbers
if (!$perm->check("admin")) {
	$vendor_id = $hVendor->getVendorIdByUserId($auth['user_id']);
	echo("product_list: '".$vendor_id ."' and user_id '".$auth['user_id']."'");
	$GLOBALS['vmLogger']->debug("product_list: '".$vendor_id ."' and user_id '".$auth['user_id']."'");
}else{
	$vendor_id = 1;	
}

$order = " ORDER BY product_name ";
$q_no_parents = "";
if ($allproducts == 2) {
// stock on hand in starting from lowest stock
    $order = "ORDER BY product_in_stock, product_name ";
// stock on hand listing needs to exclude parent products
// get parent products
    $q = "SELECT DISTINCT product_parent_id ";
	$q .= "FROM `jos_vm_product` ";
	$q .= "WHERE product_parent_id <> 0 ";
	$q .= "ORDER BY product_parent_id";

	$db->query($q);

// create a list of parent products
	$parent_list = "";
	$list_count = 0;
	while ($db->next_record()) {
   	    $parent_id = $db->f("product_parent_id");
   		if ($list_count > 0) {
       		$parent_list .= ", ";
   		}
		$parent_list .= "'".$parent_id."'";
		$list_count++;
	}

	if ($list_count) {
		$q_no_parents = " AND #__{vm}_product.product_id NOT IN ($parent_list)";
	}
}
// 20090204 end

// Check to see if this is a search or a browse by category
// Default is to show all products
if( !empty($category_id)) {
	$list  = "SELECT * FROM #__{vm}_product, #__{vm}_product_category_xref WHERE ";
	$count  = "SELECT count(*) as num_rows FROM #__{vm}_product, 
		#__{vm}_product_category_xref WHERE ";
	$q  = "#__{vm}_product.vendor_id = '$vendor_id' ";
	$q .= "AND #__{vm}_product_category_xref.category_id='$category_id' "; 
	$q .= "AND #__{vm}_product.product_id=#__{vm}_product_category_xref.product_id ";
	$q .= "AND product_in_stock > 0 ";
	// 20090204
//	$q .= "ORDER BY product_name ";
    if ($allproducts == 2) {
    	$q .= $q_no_parents;
    }
    $q .= $order;
	$list .= $q . " LIMIT $limitstart, " . $limit;
	$count .= $q;
}
elseif( !empty($keyword)) {
	$list  = "SELECT * FROM #__{vm}_product WHERE ";
	$count = "SELECT count(*) as num_rows FROM #__{vm}_product WHERE ";
	$q  = "#__{vm}_product.vendor_id = '$vendor_id' ";
	$q .= "AND (#__{vm}_product.product_name LIKE '%$keyword%' OR ";
	$q .= "#__{vm}_product.product_sku LIKE '%$keyword%' OR ";
	$q .= "#__{vm}_product.product_s_desc LIKE '%$keyword%' OR ";
	$q .= "#__{vm}_product.product_desc LIKE '%$keyword%'";
	$q .= ") ";
	$q .= "AND product_in_stock > 0 ";
	// 20090204
//	$q .= "ORDER BY product_name ";
    if ($allproducts == 2) {
    	$q .= $q_no_parents;
    }
    $q .= $order;
// 20090204
	$list .= $q . " LIMIT $limitstart, " . $limit;
	$count .= $q;   
}
else {
	$list  = "SELECT * FROM #__{vm}_product WHERE ";
	$count = "SELECT count(*) as num_rows FROM #__{vm}_product WHERE ";
	$q  = "#__{vm}_product.vendor_id = '$vendor_id' ";
// 20090204
//	if ($allproducts != 1)
//		$q .= "AND product_in_stock > 0 ";

    if ($allproducts == 0) {
		$q .= "AND product_in_stock > 0 ";
    }
    if ($allproducts == 2) {
    	$q .= $q_no_parents;
    }
//	$q .= "ORDER BY product_name ";
    $q .= $order;
// 20090204
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
$listObj->writeSearchHeader(JText::_('VM_PRODUCT_INVENTORY_LBL'), IMAGEURL."ps_image/inventory.gif", $modulename, "product_inventory");

echo '&nbsp;&nbsp;';
if($allproducts != 1){
	$link = $sess->url($_SERVER['PHP_SELF']."?pshop_mode=admin&page=$page&allproducts=1");
	
	echo '<a href="'.$link.'" title="'.JText::_('VM_LIST_ALL_PRODUCTS').'">';
	echo JText::_('VM_LIST_ALL_PRODUCTS');
	echo '</a>';
} else{
	echo JText::_('VM_LIST_ALL_PRODUCTS');
}


echo '&nbsp;&nbsp;|&nbsp;&nbsp;';
//if ($allproducts == 1) echo '<a href="'.$sess->url($_SERVER['PHP_SELF']."?pshop_mode=admin&page=$page&allproducts=0").'" title="'.JText::_('VM_HIDE_OUT_OF_STOCK').'">';
//echo JText::_('VM_HIDE_OUT_OF_STOCK');
//if ($allproducts == 1) '</a>';
//echo '<br /><br />';
if ($allproducts != 0) {
	echo "<a href=\"".$sess->url($_SERVER['PHP_SELF']."?pshop_mode=admin&page=$page&allproducts=0").'" title="'.JText::_('VM_HIDE_OUT_OF_STOCK'). "\">";
	echo JText::_('VM_HIDE_OUT_OF_STOCK');
	echo "</a>";
}else{
	echo JText::_('VM_HIDE_OUT_OF_STOCK');
}
echo '&nbsp;&nbsp;|&nbsp;&nbsp;';

// start the list table
$listObj->startTable();

// these are the columns in the table
$columns = Array(  "#" => "width=\"20\"", 
					JText::_('VM_PRODUCT_LIST_NAME') => '',
					JText::_('VM_PRODUCT_LIST_SKU') => '',
					JText::_('VM_PRODUCT_INVENTORY_STOCK') => '',
					JText::_('VM_PRODUCT_INVENTORY_PRICE') => '',
					JText::_('VM_PRODUCT_INVENTORY_WEIGHT') => '',
					JText::_('CMN_PUBLISHED') => 'width="5%"',
				);
$listObj->writeTableHeader( $columns );

$db->query($list);
$i = 0;
while ($db->next_record()) {
	$listObj->newRow();
	
	// The row number
	$listObj->addCell( $pageNav->rowNumber( $i ) );
	
	// The product name and link
	$link = $_SERVER['PHP_SELF'] . "?page=$modulename.product_form&limitstart=$limitstart&keyword=".urlencode($keyword) . 
//					"&product_id=" . $db->f("product_id")."&product_parent_id=".$product_parent_id;
					"&product_id=" . $db->f("product_id")."&product_parent_id=".$db->f("product_parent_id");
	if( $vmLayout != 'standard' ) {
		$link .= "&no_menu=1&tmpl=component";
		$link = defined('_VM_IS_BACKEND') 
					? str_replace('index2.php', 'index3.php', str_replace('index.php', 'index3.php', $link )) 
					: str_replace('index.php', 'index2.php', $link );
	}
	$link = $sess->url( $link );
	$text = shopMakeHtmlSafe($db->f("product_name"));

	$tmpcell = vmCommonHTML::hyperLink($link, $text, '', 'Edit: '.$text, 'onclick="parent.addSimplePanel( \''.$db->getEscaped($db->f("product_name")).'\', \''.$link.'\' );return false;"');
		
	$listObj->addCell( $tmpcell );
	
	$listObj->addCell( $db->f("product_sku") );
	$listObj->addCell( $db->f("product_in_stock") );
	$price=$ps_product->get_price($db->f("product_id"));
	if ($price) {
		if (!empty($price["item"])) {
			$tmp_cell = $price["product_price"];
		} 
		else {
			$tmp_cell = "none";
		} 
	} 
	else {
		$tmp_cell = "none";
	} 
	$listObj->addCell( $tmp_cell );
       
	$listObj->addCell( $db->f("product_weight") );
	
	// The "Published" column
	$tmpcell = "<a href=\"". $sess->url( $_SERVER['PHP_SELF']."?page=product.product_inventory&product_id=".$db->f("product_id")."&func=changePublishState&allproducts=$allproducts" );
	if ($db->f("product_publish")=='N') {
		$tmpcell .= "&task=publish\">";
	}
	else {
		$tmpcell .= "&task=unpublish\">";
	}
	$tmpcell .= vmCommonHTML::getYesNoIcon( $db->f("product_publish"), JText::_('CMN_PUBLISH'), JText::_('CMN_UNPUBLISH') );
	$tmpcell .= "</a>";
	$listObj->addCell( $tmpcell, 'align="center"' );
	

	$i++;
}
$listObj->writeTable();

$listObj->endTable();

$listObj->writeFooter( $keyword, "&allproducts=$allproducts" );

?>
