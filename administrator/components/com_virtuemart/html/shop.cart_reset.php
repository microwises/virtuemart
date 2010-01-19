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
Global $return,$option,$option2,$product_id,$category_id,$Itemid,$flypage;
require_once(CLASSPATH. 'ps_cart.php' );
$ps_cart =& new ps_cart;

$loc = $_SERVER['HTTP_REFERER'];
$set = $ps_cart->reset();
//header("Location: $loc");

if( $option2 != "com_virtuemart") {
    vmRedirect($loc); }
else {
    vmRedirect( $sess->url( $_SERVER['PHP_SELF']."?page=$return&product_id=$product_id&category_id=$category_id&flypage=$flypage", false, false ));

  // 
}