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

$manufacturer_id = JRequest::getVar(  'manufacturer_id');

$mainframe->setPageTitle( JText::_('VM_CART_TITLE') );
$mainframe->appendPathWay( JText::_('VM_CART_TITLE') );

$continue_link = '';
if( !empty( $category_id)) {
        $continue_link = $sess->url( $_SERVER['PHP_SELF'].'?page=shop.browse&amp;category_id='.$category_id );
}
elseif( empty( $category_id) && !empty($product_id)) {
        $db->query( 'SELECT `category_id` FROM `#__{vm}_product_category_xref` WHERE `product_id`='.intval($product_id) );
        $db->next_record();
        $category_id = $db->f('category_id');
        $continue_link = $sess->url( $_SERVER['PHP_SELF'].'?page=shop.browse&amp;category_id='.$category_id );
}
elseif( !empty( $manufacturer_id )) {
        $continue_link = $sess->url( $_SERVER['PHP_SELF'].'?page=shop.browse&amp;manufacturer_id='.$manufacturer_id );
}

$show_basket = true;

$tpl = new $GLOBALS['VM_THEMECLASS']();
$tpl->set('show_basket', $show_basket );
$tpl->set('continue_link', $continue_link );
$tpl->set('category_id', $category_id );
$tpl->set('product_id', $product_id );
$tpl->set('manufacturer_id', $manufacturer_id );
$tpl->set('cart', $cart );

echo $tpl->fetch( "pages/$page.tpl.php" );

?>

