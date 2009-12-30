<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: shop.index.php 1755 2009-05-01 22:45:17Z rolandd $
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
require_once( CLASSPATH . 'ps_product.php');
require_once( CLASSPATH . 'ps_product_category.php');
$ps_product_category = new ps_product_category();
$ps_product = new ps_product();
// Show only top level categories and categories that are
// being published
$tpl = new $GLOBALS['VM_THEMECLASS']();
$category_childs = $ps_product_category->get_child_list(0);
$tpl->set( 'categories', $category_childs );
//echo $vendor_store_desc;
$categories = $tpl->fetch( 'common/categoryChildlist.tpl.php');
$tpl->set( 'vendor_store_desc', $vendor_store_desc );
$tpl->set( 'categories', $categories );
$tpl->set('ps_product',$ps_product);
$tpl->set('recent_products',$ps_product->recentProducts(null,$tpl->get_cfg('showRecent', 5)));
echo $tpl->fetch( 'common/shopIndex.tpl.php');
?>