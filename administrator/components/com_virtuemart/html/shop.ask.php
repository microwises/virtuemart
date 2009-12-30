<?php 
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id:shop.ask.php 431 2006-10-17 21:55:46 +0200 (Di, 17 Okt 2006) soeren_nb $
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2006-2007 soeren - All rights reserved.
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

$product_id = intval( JRequest::getVar( "product_id", null) );
$product_sku = $db->getEscaped( JRequest::getVar( "sku", '' ) );
$category_id = JRequest::getVar( "category_id", null);
$set = JRequest::getVar( "set", 0 );
$Itemid = $sess->getShopItemid();
$flypage = JRequest::getVar( "flypage", '' );
$subject = substr( urldecode( JRequest::getVar(  'subject')), 0, 150 );

$db_product = new ps_DB;
// Get the product info from the database
$q = "SELECT * FROM `#__{vm}_product` WHERE ";
if( !empty($product_id)) {
	$q .= "`product_id`=$product_id";
}
elseif( !empty($product_sku )) {
	$q .= "`product_sku`='$product_sku'";
}
else {
	vmRedirect( $sess->url( $_SERVER['PHP_SELF']."?page=shop.product_details&keyword=".urlencode($_SESSION['keyword'])."&category_id={$_SESSION['category_id']}&limitstart={$_SESSION['limitstart']}", false, false), JText::_('VM_PRODUCT_NOT_FOUND') );
}
if( !$perm->check("admin,storeadmin") ) {
	$q .= " AND `product_publish`='Y'";
	if( CHECK_STOCK && PSHOP_SHOW_OUT_OF_STOCK_PRODUCTS != "1") {
		$q .= " AND `product_in_stock` > 0 ";
	}
}
$db_product->query( $q );
// Redirect back to Product Browse Page on Error
if( !$db_product->next_record() ) {
	vmRedirect( $sess->url( $_SERVER['PHP_SELF']."?page=shop.product_details&keyword=".urlencode($_SESSION['keyword'])."&category_id={$_SESSION['category_id']}&limitstart={$_SESSION['limitstart']}", false, false ), JText::_('VM_PRODUCT_NOT_FOUND') );
}


/* Set Dynamic Page Title */
$pagetitle = JText::_('ENQUIRY').' - '.substr($db_product->f('product_name'), 0, 60 );
$mainframe->setPageTitle( $pagetitle );

// set up return to product link
$product_link = $sess->url( $mm_action_url.basename($_SERVER['PHP_SELF'])."?page=shop.product_details&flypage=$flypage&product_id=$product_id&category_id=$category_id" );

$name = $my->name;
$email = $my->email;

$pathway_item = new stdClass();
$pathway_item->link = $product_link;
$pathway_item->name = $db_product->f('product_name');

$page_pathway[] = $pathway_item;
$pathway_item = new stdClass();
$pathway_item->name = $pagetitle;
$page_pathway[] = $pathway_item;
$vm_mainframe->vmAppendPathway( $page_pathway );

$tpl = vmTemplate::getInstance();
$tpl->set_vars(array('product_id' => $product_id,
					'product_sku' => $product_sku,
					'category_id' => $category_id,
					'product_link' => $product_link,
					'set' => $set,
					'name' => $name,
					'email' => $email,
					'flypage' => $flypage,
					'subject' => $subject,
					'db_product' => $db_product
					)
				);
echo $tpl->fetch('pages/shop.ask.tpl.php');

?>