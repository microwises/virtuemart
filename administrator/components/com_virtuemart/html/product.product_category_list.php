<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id: product.product_category_list.php 1760 2009-05-03 22:58:57Z Aravot $
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2004-2008 soeren - All rights reserved.
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
global $ps_product_category;

require_once( CLASSPATH . 'pageNavigation.class.php' );
require_once( CLASSPATH . 'htmlTools.class.php' );

$categories = ps_product_category::getCategoryTreeArray(false, $keyword ); // Get array of category objects
$result = ps_product_category::sortCategoryTreeArray( $categories );

$nrows = $size = sizeOf($categories); // Category count

$id_list = $result['id_list'];
$row_list = $result['row_list'];
$depth_list = $result['depth_list'];
$categories = $result['category_tmp'];

// Create the Page Navigation
$pageNav = new vmPageNav( $nrows, $limitstart, $limit );

for($n = $pageNav->limitstart ; $n < $nrows ; $n++) {
	@$levelcounter[$categories[$row_list[$n]]["category_parent_id"]]++;
}

// Create the List Object with page navigation
$listObj = new listFactory( $pageNav );

// print out the search field and a list heading
$listObj->writeSearchHeader(JText::_('VM_CATEGORY_LIST_LBL'), VM_ADMIN_ICON_URL.'icon_48/vm_categories_48.png', $modulename, "product_category_list");

// start the list table
$listObj->startTable();

// these are the columns in the table
$columns = Array(  "#" => "width=\"20\"", 
					"<input type=\"checkbox\" name=\"toggle\" value=\"\" onclick=\"checkAll(".$pageNav->limit.")\" />" => "width=\"20\"",
					JText::_('VM_CATEGORY_FORM_NAME') => 'width="25%"',
					JText::_('VM_CATEGORY_FORM_DESCRIPTION') => 'width="30%"',
					JText::_('VM_PRODUCTS_LBL') => 'width="10%"',
					JText::_('VM_PRODUCT_LIST_PUBLISH') => 'width="5%"',
					JText::_('VM_PRODUCT_LIST_SHARED') => 'width="5%"',
					JText::_('VM_MODULE_LIST_ORDER') => 'width="7%"',
					vmCommonHTML::getSaveOrderButton( min($nrows - $pageNav->limitstart, $pageNav->limit ) ) => 'width="8%"',
					JText::_('E_REMOVE') => "width=\"5%\"",
					'Id' => ''
				);
$listObj->writeTableHeader( $columns );

$ibg = 0;
if( $pageNav->limit < $nrows )
	if( $pageNav->limitstart+$pageNav->limit < $nrows ) {
		$nrows = $pageNav->limitstart + $pageNav->limit;
	}
	

$dbs = new ps_DB;

for($n = $pageNav->limitstart ; $n < $nrows ; $n++) {

	if( !isset($row_list[$n])) $row_list[$n] = $n;
	if( !isset($depth_list[$n])) $depth_list[$n] = 0;
	
	$catname = shopMakeHtmlSafe( $categories[$row_list[$n]]["category_name"] );
	
	$listObj->newRow();
	
	// The row number
	$listObj->addCell( $pageNav->rowNumber( $ibg ) );
	
	// The Checkbox
	$listObj->addCell( vmCommonHTML::idBox( $ibg, $categories[$row_list[$n]]["category_child_id"], false, "category_id" ) );
	
	// Which category depth level we are in?
	$repeat = $depth_list[$n]+1;
	$link = $_SERVER['PHP_SELF'] . "?option=com_virtuemart&page=product.product_category_form&category_id=" . $categories[$row_list[$n]]["category_child_id"]. "&category_parent_id=" . $categories[$row_list[$n]]["category_parent_id"];
	if( $vmLayout != 'standard' ) {
		$link .= "&no_menu=1&tmpl=component";
		$link = defined('_VM_IS_BACKEND') 
			? str_replace('index2.php', 'index3.php', str_replace('index.php', 'index3.php', $link )) 
			: str_replace('index.php', 'index2.php', $link );
	}
	$tmp_cell = str_repeat("&nbsp;&nbsp;&nbsp;", $repeat ) 
				. "&#095&#095;|" . $repeat ."|&nbsp;"
				."<a href=\"".$link."\">"
				. $catname
				. "</a>";
	$listObj->addCell( $tmp_cell );
	
	$desc = strlen( $categories[$row_list[$n]]["category_description"] ) > 255 ? mm_ToolTip( $categories[$row_list[$n]]["category_description"], JText::_('VM_CATEGORY_FORM_DESCRIPTION') ) :$categories[$row_list[$n]]["category_description"];
	$listObj->addCell( "&nbsp;&nbsp;". $desc );
	$link = $_SERVER['PHP_SELF'] . "?page=product.product_list&category_id=" . $categories[$row_list[$n]]["category_child_id"]."&option=com_virtuemart";
	if( $vmLayout != 'standard' ) {
		$link .= "&no_menu=1&tmpl=component";
		$link = defined('_VM_IS_BACKEND') 
			? str_replace('index2.php', 'index3.php', str_replace('index.php', 'index3.php', $link )) 
			: str_replace('index.php', 'index2.php', $link );
	}
	$listObj->addCell( ps_product_category::product_count( $categories[$row_list[$n]]["category_child_id"] )
						."&nbsp;<a href=\"". $link
						. "\">[ ".JText::_('VM_SHOW')." ]</a>"
					);
					
	// Publish / Unpublish
	$tmp_cell = "<a href=\"". $sess->url( $_SERVER['PHP_SELF']."?page=product.product_category_list&category_id=".$categories[$row_list[$n]]["category_child_id"]."&func=changePublishState" );
	if ($categories[$row_list[$n]]["published"]=='0') {
		$tmp_cell .= "&task=publish\">";
	} 
	else { 
		$tmp_cell .= "&task=unpublish\">";
	}
	$tmp_cell .= vmCommonHTML::getYesNoIcon ( $categories[$row_list[$n]]["published"] );
	$tmp_cell .= "</a>";
	$listObj->addCell( $tmp_cell );

	// Shared / notShared Category with other vendors.
	$tmp_cell = "<a href=\"". $sess->url( $_SERVER['PHP_SELF']."?page=product.product_category_list&category_child_id=".$categories[$row_list[$n]]["category_child_id"]."&func=changePublishState" );
	if ($categories[$row_list[$n]]["category_shared"]=='N') {
		$tmp_cell .= "&task=publish\">";
	} 
	else { 
		$tmp_cell .= "&task=unpublish\">";
	}
	$tmp_cell .= vmCommonHTML::getYesNoIcon ( $categories[$row_list[$n]]["category_shared"] );
	$tmp_cell .= "</a>";


	$listObj->addCell( $tmp_cell );
	
	// Order Up and Down Icons
	if( $keyword == '' ) {
		// This must be a big cheat, because we're working on sorted arrays,
		// not on database information
		// Check for predecessors and brothers and sisters
		$upCondition = $downCondition = false;
		if( !isset( $levels[$depth_list[$n]+1] ))
			$levels[$depth_list[$n]+1] = 1;
		if( $categories[$row_list[$n]]["category_parent_id"] == @$categories[$row_list[$n-1]]["category_parent_id"])
			$upCondition = true;
		if( $categories[$row_list[$n]]["category_parent_id"] == @$categories[$row_list[$n+1]]["category_parent_id"] )
			$downCondition = true;
		if( !$downCondition || !$upCondition ) {
			
			if( $levelcounter[$categories[$row_list[$n]]["category_parent_id"]] > $levels[$depth_list[$n]+1] )
				$downCondition = true;
				if( $levels[$depth_list[$n]+1] > 1 )
					$upCondition = true;
			if( $levelcounter[$categories[$row_list[$n]]["category_parent_id"]] == $levels[$depth_list[$n]+1] ) {
				$upCondition = true;
				$downCondition = false;
			}
			if( $levelcounter[$categories[$row_list[$n]]["category_parent_id"]] < $levels[$depth_list[$n]+1] ) {
				$downCondition = false;
				$upCondition = false;
			}
		}
		$levels[$depth_list[$n]+1]++;
		
		$listObj->addCell( $pageNav->orderUpIcon( $ibg, $upCondition, 'orderup', JText::_('CMN_ORDER_UP'), $page, 'reorder' )
							. '&nbsp;'
							.$pageNav->orderDownIcon( $ibg, $levelcounter[$categories[$row_list[$n]]["category_parent_id"]], $downCondition, 'orderdown', JText::_('CMN_ORDER_DOWN'), $page, 'reorder' )
						);
						
		$listObj->addCell( vmCommonHTML::getOrderingField( $categories[$row_list[$n]]["list_order"] ) );
	} else {
		$listObj->addCell( '&nbsp;' );
		$listObj->addCell( '&nbsp;' );		
	}
	$listObj->addCell( $ps_html->deleteButton( "category_id", $categories[$row_list[$n]]["category_child_id"], "productCategoryDelete", $keyword, $limitstart ) );
	
	$listObj->addCell( $categories[$row_list[$n]]["category_child_id"] );
	
	$ibg++;
}

$listObj->writeTable();

$listObj->endTable();

$listObj->writeFooter( $keyword );
?>
