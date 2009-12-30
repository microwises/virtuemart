<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id: product.specialprod.php 1760 2009-05-03 22:58:57Z Aravot $
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
----------------------------------------------------------------------
 Special Products Manager
 ----------------------------------------------------------------------
 Module designed by 
 W: www.mrphp.com.au
 E: info@mrphp.com.au
 P: +61 418 436 690
 ----------------------------------------------------------------------
 */
mm_showMyFileName( __FILE__ );

require_once( CLASSPATH . "pageNavigation.class.php" );
require_once( CLASSPATH . "htmlTools.class.php" );

global $hVendor_id;
$vendor = $hVendor_id;

$category_id = JRequest::getVar(  'category_id' );
$filter = JRequest::getVar( 'filter', "featured_and_discounted" );

$qfilter = " AND (product_special='Y' OR product_discount_id > 0) ";

$GLOBALS['vmLogger']->debug('The Vendor '.$hVendor_id);

switch( $filter ) {
	case "all":
		$qfilter = "";
		break;
	case "featured":
		$qfilter = " AND (product_special='Y') ";
		break;
	case "discounted":
		$qfilter = " AND (product_discount_id > 0) ";
		break;
	case "featured_and_discounted":
		$qfilter = " AND (product_special='Y' OR product_discount_id > 0) ";
		break;
}
// Check to see if this is a search or a browse by category
// Default is to show all products
if (!empty( $category_id )) {
	$list  = "SELECT * FROM #__{vm}_product, #__{vm}_product_category_xref WHERE ";
	$count  = "SELECT count(*) as num_rows FROM #__{vm}_product,
                product_category_xref, category WHERE ";
	if (!$perm->check("admin")) {
		$q = "#__{vm}_product.vendor_id = '$vendor' AND";
	}else{
		$q = '';
	}
	$q = "#__{vm}_product_category_xref.category_id='$category_id' ";
	$q .= "AND #__{vm}_product.product_id=#__{vm}_product_category_xref.product_id ";
	$q .= $qfilter;
	$q .= "ORDER BY product_name ";
	$list .= $q . " LIMIT $limitstart, $limit";
	$count .= $q;
}
elseif (!empty($keyword)) {
	$list  = "SELECT * FROM #__{vm}_product WHERE ";
	$count = "SELECT count(*) as num_rows FROM #__{vm}_product WHERE ";
	if (!$perm->check("admin")) {
		$q = "#__{vm}_product.vendor_id = '$vendor' AND";
	}else{
		$q = '';
	}
	//$q  = "product.vendor_id = '$hVendor_id' ";
	$q .= "(#__{vm}_product.product_name LIKE '%$keyword%' OR ";
	$q .= "#__{vm}_product.product_sku LIKE '%$keyword%' OR ";
	$q .= "#__{vm}_product.product_s_desc LIKE '%$keyword%' OR ";
	$q .= "#__{vm}_product.product_desc LIKE '%$keyword%'";
	$q .= ") ";
	$q .= $qfilter;
	$q .= "ORDER BY product_name ";
	$list .= $q . " LIMIT $limitstart, $limit";
	$count .= $q;
}
else {
	$list  = "SELECT * FROM #__{vm}_product ";
	$count = "SELECT count(*) as num_rows FROM #__{vm}_product ";
	$q = "WHERE 1=1 ";
	if (!$perm->check("admin")) {
		$q  .= "AND #__{vm}_product.vendor_id = '$vendor' ";
	}
	$q .= $qfilter;
	$q .= "ORDER BY product_name ";
	$list .= $q . " LIMIT $limitstart, $limit";
	$count .= $q;
}
$GLOBALS['vmLogger']->debug('The query in product.product_list: '.$count);
$db->query($count);

$db->next_record();
$num_rows = $db->f("num_rows");

// Create the Page Navigation
$pageNav = new vmPageNav( $num_rows, $limitstart, $limit );

// Create the List Object with page navigation
$listObj = new listFactory( $pageNav );

// print out the search field and a list heading
$listObj->writeSearchHeader(JText::_('VM_FEATURED_PRODUCTS_LIST_LBL'), IMAGEURL."ps_image/product_code.png", $modulename, "specialprod");

echo '<strong>'.JText::_('VM_FILTER').':</strong>&nbsp;&nbsp;';
if($filter != "all") echo '<a href="'.$sess->url($_SERVER['PHP_SELF']."?page=$page&filter=all").'" title="'.JText::_('VM_LIST_ALL_PRODUCTS').'">';
echo JText::_('VM_LIST_ALL_PRODUCTS');
if ($filter != 'all') echo '</a>';

echo '&nbsp;&nbsp;|&nbsp;&nbsp;';
if ($filter != 'featured_and_discounted') echo '<a href="'.$sess->url($_SERVER['PHP_SELF']."?page=$page&filter=featured_and_discounted").'" title="'.JText::_('VM_SHOW_FEATURED_AND_DISCOUNTED').'">';
echo JText::_('VM_SHOW_FEATURED_AND_DISCOUNTED');
if ($filter != 'featured_and_discounted') echo '</a>';

echo '&nbsp;&nbsp;|&nbsp;&nbsp;';
if ($filter != 'featured') echo '<a href="'.$sess->url($_SERVER['PHP_SELF']."?page=$page&filter=featured").'" title="'.JText::_('VM_SHOW_FEATURED').'">';
echo JText::_('VM_SHOW_FEATURED');
if ($filter != 'featured') echo '</a>';

echo '&nbsp;&nbsp;|&nbsp;&nbsp;';
if ($filter != 'discounted') echo '<a href="'.$sess->url($_SERVER['PHP_SELF']."?page=$page&filter=discounted").'" title="'.JText::_('VM_SHOW_DISCOUNTED').'">';
echo JText::_('VM_SHOW_DISCOUNTED');
if ($filter != 'discounted') echo '</a>';

echo '<br /><br />';

// start the list table
$listObj->startTable();

// these are the columns in the table
$columns = Array(  "#" => "width=\"20\"", 
					JText::_('VM_PRODUCT_LIST_NAME') => '',
					JText::_('VM_PRODUCT_LIST_SKU') => '',
					JText::_('VM_PRODUCT_INVENTORY_PRICE') => '',
					JText::_('VM_FEATURED') => '',
					JText::_('VM_PAYMENT_METHOD_LIST_DISCOUNT') => '',
					JText::_('VM_FILEMANAGER_PUBLISHED') => ''
				);
$listObj->writeTableHeader( $columns );

$db->query($list);

$i = 0;
while ($db->next_record()) {

	$listObj->newRow();
	
	// The row number
	$listObj->addCell( $pageNav->rowNumber( $i ) );
	
	$url = $_SERVER['PHP_SELF']."?page=$modulename.product_form&product_id=" . $db->f("product_id");
	if ($db->f("product_parent_id")) {
		$url .= "&product_parent_id=" . $db->f("product_parent_id");
	}
	$tmp_cell = "<a href=\"" . $sess->url($url) . "\">". $db->f("product_name")."</a>";
	$listObj->addCell( $tmp_cell );
	
	$listObj->addCell( $db->f("product_sku") );
	
	$price=$ps_product->get_price($db->f("product_id"));
	if ($price) {
		if (!empty($price["item"])) {
			$tmp_cell = $CURRENCY_DISPLAY->getFullValue( $price["product_price"] );
		} else {
			$tmp_cell = "none";
		}
	} else {
		$tmp_cell = "none";
	}
	$listObj->addCell( $tmp_cell );
       
	$listObj->addCell( vmCommonHTML::getYesNoIcon( $db->f("product_special"), "On Special?" ));
	
	$listObj->addCell( $db->f("product_discount_id") );
	
	$listObj->addCell( vmCommonHTML::getYesNoIcon( $db->f("product_publish"), "Published?" ) );
    
	$i++;
}

$listObj->writeTable();

$listObj->endTable();

$listObj->writeFooter( $keyword );

?>