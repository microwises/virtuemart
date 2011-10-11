<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/*
* featured/Latest/Topten/Random Products Module
*
* @version $Id: mod_virtuemart_product.php 2789 2011-02-28 12:41:01Z oscar $
* @package VirtueMart
* @subpackage modules
*
* 	@copyright (C) 2010 - Patrick Kohl
// W: demo.st42.fr
// E: cyber__fr|at|hotmail.com
*
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* VirtueMart is Free Software.
* VirtueMart comes with absolute no warranty.
*
* www.virtuemart.net
*/
/* Load  VM fonction */
if (!class_exists( 'mod_virtuemart_product' )) require('helper.php');

/* Setting */
$vendorId = JRequest::getInt('vendorid', 1);
$productModel = new VirtueMartModelProduct();

$max_items = 		$params->get( 'max_items', 2 ); //maximum number of items to display
$category_id = 		$params->get( 'virtuemart_category_id', null ); // Display products from this category only
$filter_category = 	(bool)$params->get( 'filter_category', 0 ); // Filter the category
$display_style = 	$params->get( 'display_style', "div" ); // Display Style
$products_per_row = $params->get( 'products_per_row', 4 ); // Display X products per Row
$show_price = 		(bool)$params->get( 'show_price', 1 ); // Display the Product Price?
$show_addtocart = 	(bool)$params->get( 'show_addtocart', 1 ); // Display the "Add-to-Cart" Link?
$headerText = 		$params->get( 'headerText', '' ); // Display a Header Text
$footerText = 		$params->get( 'footerText', ''); // Display a footerText
$Product_group = 	$params->get( 'product_group', 'featured'); // Display a footerText
if (!$filter_category ) $category_id = null;
$products = 		$productModel->getProductListing($Product_group, $max_items);
$productModel->addImages($products);

$totalProd = 		count( $products);
if(empty($products)) return false;
$currency = CurrencyDisplay::getInstance( );

if ($show_addtocart) {
	vmJsApi::jQuery();
	vmJsApi::jPrice();
	vmJsApi::cssSite();
}
/* load the template */
require(JModuleHelper::getLayoutPath('mod_virtuemart_product'));
?>