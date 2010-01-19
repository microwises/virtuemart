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

global $hVendor_id;
$vendor = $hVendor_id;

$q = "";
$where = array();
$count = "SELECT COUNT(*) AS num_rows ";
$list = "SELECT product_name, p.product_id, p.vendor_id, review_id, comment, user_rating,userid,username,time,r.published ";
$q .= "FROM #__{vm}_product p, #__{vm}_product_reviews r LEFT JOIN #__users ON #__users.id=r.userid ";
if( !empty( $product_id )) {
	$where[] = "r.product_id = $product_id";
}
$where[] = "p.product_id = r.product_id";

if( !empty( $keyword )) {
	$where[] = "( comment LIKE '%$keyword%' OR username LIKE '%$keyword%' )";
}
if( !empty( $where )) {
	$q .= ' WHERE ' . implode(' AND ', $where );
}

if (!$perm->check("admin")) {
	$q  .= " AND p.vendor_id = '$vendor' ";
}


$q .= ' ORDER BY time DESC'; 
$list .= $q ." LIMIT $limitstart, $limit";
$count .= $q;
$db->query($count);
$GLOBALS['vmLogger']->debug('The query in product.review_list: '.$count);
$num_rows = $db->f('num_rows');

// Create the Page Navigation
$pageNav = new vmPageNav( $num_rows, $limitstart, $limit );

// Create the List Object with page navigation
$listObj = new listFactory( $pageNav );

$title = JText::_('VM_REVIEWS');
		  
// print out the search field and a list heading
$listObj->writeSearchHeader( $title, VM_ADMIN_ICON_URL.'icon_48/vm_reviews_48.png', $modulename, "review_list");

// start the list table
$listObj->startTable();

// these are the columns in the table
$columns = Array(  "#" => "width=\"20\"", 
					"<input type=\"checkbox\" name=\"toggle\" value=\"\" onclick=\"checkAll(".$num_rows.")\" />" => "width=\"20\"",
					JText::_('VM_PRODUCT_NAME_TITLE') => 'width="20%"',
					JText::_('VM_REVIEW_LIST_NAMEDATE') => 'width="15%"',
					JText::_('VM_REVIEWS') => 'width="35%"',
					JText::_('VM_RATE_NOM') => 'width="15%"',
					JText::_('VM_PRODUCT_LIST_PUBLISH') => 'width="5%"',
					JText::_('E_REMOVE') => 'width="10%"'
				);
$listObj->writeTableHeader( $columns );

$db->query( $list );
$i = 0;
while ($db->next_record()) {

	$listObj->newRow();
	
	// The row number
	$listObj->addCell( $pageNav->rowNumber( $i ) );
	
	// The Checkbox
	$listObj->addCell( vmCommonHTML::idBox( $i, $db->f("review_id"), false, "review_id" ) );
	
	
	$url = $_SERVER['PHP_SELF'] . "?page=$modulename.product_form&product_id=".$db->f('product_id');
	$link = "<a href=\"" . $sess->url($url) . "\">". $db->f('product_name'). "</a>";
	// The Product Name
	$listObj->addCell( $link );
	
	$text = $db->f("username")."</strong><br />(".date("Y-m-d", $db->f("time")).")";
	if( $perm->check('admin')) {
		$text = '<a href="'.$sess->url( $_SERVER['PHP_SELF'].'?page=product.review_form&amp;review_id='.$db->f('review_id')).'">'.$text.'</a>';
	}
	$listObj->addCell( $text );
	$listObj->addCell( substr($db->f("comment"), 0 , 500) );
	$listObj->addCell( '<img src="'. VM_THEMEURL.'images/stars/'.$db->f("user_rating").'.gif" border="0" alt="stars" />' );
	
	$tmpcell = "<a href=\"". $sess->url( $_SERVER['PHP_SELF']."?page=product.review_list&product_id=$product_id&review_id=".$db->f('review_id')."&func=changePublishState" );
	if ($db->f("published")=='N') {
		$tmpcell .= "&task=publish\">";
	}
	else {
		$tmpcell .= "&task=unpublish\">";
	}
	$tmpcell .= vmCommonHTML::getYesNoIcon( $db->f("published"), JText::_('CMN_PUBLISH'), JText::_('CMN_UNPUBLISH') );
	$tmpcell .= "</a>";
	$listObj->addCell( $tmpcell );
		
	$listObj->addCell( $ps_html->deleteButton( "review_id", $db->f("review_id"), "productReviewDelete", $keyword, $limitstart ) );

	$i++;
	
}

$listObj->writeTable();

$listObj->endTable();

$listObj->writeFooter( $keyword, "&product_id=$product_id" );

?>