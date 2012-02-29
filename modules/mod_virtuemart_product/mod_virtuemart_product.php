<?php
defined('_JEXEC') or die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/*
* featured/Latest/Topten/Random Products Module
*
* @version $Id: mod_virtuemart_product.php 2789 2011-02-28 12:41:01Z oscar $
* @package VirtueMart
* @subpackage modules
*
* 	@copyright (C) 2010 - Patrick Kohl
*
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* VirtueMart is Free Software.
* VirtueMart comes with absolute no warranty.
*
* www.virtuemart.net
*/

if (!class_exists( 'VmModel' )) require(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'vmmodel.php');

// Setting
$max_items = 		$params->get( 'max_items', 2 ); //maximum number of items to display
$layout = $params->get('layout','default');
$category_id = 		$params->get( 'virtuemart_category_id', null ); // Display products from this category only
$filter_category = 	(bool)$params->get( 'filter_category', 0 ); // Filter the category
$display_style = 	$params->get( 'display_style', "div" ); // Display Style
$products_per_row = $params->get( 'products_per_row', 4 ); // Display X products per Row
$show_price = 		(bool)$params->get( 'show_price', 1 ); // Display the Product Price?
$show_addtocart = 	(bool)$params->get( 'show_addtocart', 1 ); // Display the "Add-to-Cart" Link?
$headerText = 		$params->get( 'headerText', '' ); // Display a Header Text
$footerText = 		$params->get( 'footerText', ''); // Display a footerText
$Product_group = 	$params->get( 'product_group', 'featured'); // Display a footerText

$mainframe = Jfactory::getApplication();
$virtuemart_currency_id = $mainframe->getUserStateFromRequest( "virtuemart_currency_id", 'virtuemart_currency_id',JRequest::getInt('virtuemart_currency_id',0) );


$key = 'products'.$category_id.'.'.$max_items.'.'.$filter_category.'.'.$display_style.'.'.$products_per_row.'.'.$show_price.'.'.$show_addtocart.'.'.$Product_group.'.'.$virtuemart_currency_id;

$cache	= JFactory::getCache('mod_virtuemart_product', 'output');
if (!($output = $cache->get($key))) {
	ob_start();
	// Try to load the data from cache.


	/* Load  VM fonction */
	if (!class_exists( 'mod_virtuemart_product' )) require('helper.php');



	$vendorId = JRequest::getInt('vendorid', 1);



	if ($filter_category ) $filter_category = TRUE;

	$productModel = VmModel::getModel('Product');

	$products = $productModel->getProductListing($Product_group, $max_items, $show_price, true, false,$filter_category, $category_id);
	$productModel->addImages($products);

	$totalProd = 		count( $products);
	if(empty($products)) return false;
	$currency = CurrencyDisplay::getInstance( );

	if ($show_addtocart) {
		vmJsApi::jQuery();
		vmJsApi::jPrice();
		vmJsApi::cssSite();
	}
	/* Load tmpl default */
require(JModuleHelper::getLayoutPath('mod_virtuemart_product',$layout));
	$output = ob_get_clean();
	$cache->store($output, $key);
}
echo $output;
?>
