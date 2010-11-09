<?php
if(  !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id: 
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

define('VM_SHOP_BROWSE','category');
define('VM_SHOP_FEED','feed');
define('VM_PRODUCT_DETAILS','details');
define('VM_PRODUCT_ENQUIRY','enquiry');
define('VM_CHECKOUT_INDEX','checkout');
define('VM_ADVANCE_SEARCH','search');

function virtuemartBuildRoute(&$query)
{
	$page = '';
	$segments = array();
	dump($query,'My query in Route');
	if(isset($query['view'])){
		$page = $query['view'];
		unset($query['view']);
	}

	switch ($page) {		
		// Shop browse/catgory page 
		case 'category';	
			if(isset($query['category_id'])){
//				$segments[] = VM_SHOP_BROWSE;
				$segments[] = $query['category_id'];				
				$segments[] = getCategoryName($query['category_id']);		
				unset($query['category_id']);
			}else{
				$segments[] = "products";
				unset($query['category']);	
			}
		break;

		// Shop rss feed page 
		case 'shop.feed';			
			$segments[] = VM_SHOP_FEED;
			if(isset($query['category_id'])){
				$segments[] = $query['category_id'];
				$segments[] = getCategoryName($query['category_id']);		
				unset($query['category_id']);
			}			
		break;

		// Shop product details page 
		case 'productdetails';			
//			$segments[] = VM_PRODUCT_DETAILS;			
			$product_id_exists = false;
			if(isset($query['product_id']))	{
//				$segments[] = $query['product_id'];
				$product_id_exists = true;
				$product_id = $query['product_id'];
				unset($query['product_id']);
			}
			if(isset($query['category_id'])){
				$segments[] = $query['category_id'];
				$segments[] = getCategoryName($query['category_id']);		
				unset($query['category_id']);
			}
//			if(isset($query['pop']) )	{
//				unset($query['pop']);				
//			}
			if($product_id_exists)	{
				$segments[] = getProductName($product_id);
			}
		break;
			
		// Shop ASK A QUESTION ABOUT THIS PRODUCT 
		case 'shop.ask';
			$segments[] =VM_PRODUCT_ENQUIRY;				
			if(isset($query['category_id']))	{
				$segments[] = $query['category_id'];
				unset($query['category_id']);
			}
			if(isset($query['product_id']))	{
				$segments[] = $query['product_id'];
				$product_id_exists = true;
				$product_id = $query['product_id'];
				unset($query['product_id']);
			}
			if($product_id_exists)	{
				$segments[] = getProductName($product_id);
			}
		break;

		// Checkout Index page			
		case 'cart';
            		
//		$segments[] = VM_CHECKOUT_INDEX;
//		
//		if(isset($query['ssl_redirect']))	{
//			$segments[] = "ssl_redirect";
//			unset($query['ssl_redirect']);
//		}
//		if(isset($query['redirected']))	{
//			$segments[] = "redirected";
//			unset($query['redirected']);
//		}
		break;		

//		case 'account.billing';
//			$segments[] ="account-billing";
//			if(isset($query['next_page']) )	{
//				$segments[] = "checkout";
//				unset($query['next_page']);				
//			}
//		break;
//
//		case 'account.shipto';
//			$segments[] ="account-shipto";
//			if(isset($query['next_page']) )	{
//				$segments[] = "checkout";
//				unset($query['next_page']);				
//			}
//		break;
//
//		case 'account.shipping';
//			$segments[] ="account-shipping";
//			if(isset($query['next_page']) )	{
//				$segments[] = "checkout";
//				unset($query['next_page']);				
//			}
//		break;
//
//		case 'shop.registration';
//			$segments[] ="user-registration";
//		break;
//		
//		case 'shop.favourites';
//			$segments[] ="favourites";
//		break;
//
//		case 'shop.recommend';
//			$segments[] ="recommend";
//			if(isset($query['tmpl']) )	{
//				$segments[] = $query['tmpl'];
//				unset($query['tmpl']);				
//			}
//			if(isset($query['pop']) )	{
//				$segments[] = $query['pop'];
//				unset($query['pop']);				
//			}
//			if(isset($query['product_id']) ){
//				$segments[] = $query['product_id'];
//				$segments[] = getProductName($query['product_id']);
//				unset($query['product_id']);				
//			}
//		break;
//
//		case 'shop.tos';
//			$segments[] ="terms-of-service";
//		break;
//
//		case 'shop.cart';
//			$segments[] ="cart";
//		break;
//
//		case 'account.index';
//			$segments[] ="account";
//		break;
//
//		case 'account.order_details';
//			$segments[] ="order-details";
//			if(isset($query['order_id']))	{
//				$segments[] = $query['order_id'];
//				unset($query['order_id']);
//			}
//		break;
//
//		case 'shop.waiting_list';
//			$segments[] ="notify";
//			if(isset($query['product_id']))	{
//				$segments[] = $query['product_id'];
//				$product_id_exists = true;
//				$product_id = $query['product_id'];
//				unset($query['product_id']);
//			}
//			if($product_id_exists)	{
//				$segments[] = getProductName($product_id);
//			}
//		break;
//
//		case 'shop.search';
//			$segments[] =VM_ADVANCE_SEARCH;			
//		break;
//
//		case 'store.index';
//			$segments[] = 'administration';			
//		break;
	} 
	return $segments;
}

function virtuemartParseRoute($segments)
{

	$vars = array();

	$firstSegment = $segments[0]; 
	switch($firstSegment){
		
		case 'cart':
			$uri = JFactory::getURI();
			$redirect  = 1;
			// Redirect to SSL
			if($redirect == true) {
				$uri->setScheme('https');
				$application->redirect($uri->toString());
			}
		break;
		
		case VM_SHOP_BROWSE:
			$vars['page'] = "shop.browse";
			if(isset($segments[1])){
				$vars['category_id'] = $segments[1];
			}
		break;

		//This is for all products page
		case 'products':
			$vars['page'] = "shop.browse";
			$vars['category'] = "";	
		break;
		
		case VM_SHOP_FEED:
			$vars['page'] = "shop.feed";
			if(isset($segments[1])){
				$vars['category_id'] = $segments[1];
			}			
		break;

		case VM_PRODUCT_DETAILS:
			$vars['page'] = "shop.product_details";			
			if(isset($segments[1])){
				$vars['product_id'] = $segments[1];
			}
			if(isset($segments[2])){
				$vars['category_id'] = $segments[2];
			}			
		break;

		case VM_PRODUCT_ENQUIRY:
			$vars['page'] = "shop.ask";
			$vars['category_id'] = $segments[1];
			$vars['product_id'] = $segments[2];
		break; 

		case VM_CHECKOUT_INDEX:
			$vars['page'] = "checkout.index";		
			if(isset($segments[1]) && ($segments[1]=="ssl_redirect")){
				$vars['ssl_redirect'] = 1;
				$vars['redirected'] = 1;
			}
			if(isset($segments[2]) && ($segments[2]=="redirected")){
				$vars['redirected'] = 1;
			}
		break;

		case 'account:billing':
			$vars['page'] = "account.billing";
			if(isset($segments[1])){
				$vars['next_page'] = "checkout.index";
			}
		break;

		case 'account:shipto':
			$vars['page'] = "account.shipto";
			if($segments[1]){
				$vars['next_page'] = "checkout.index";
			}
		break;

		case 'account:shipping':
			$vars['page'] = "account.shipping";
			if($segments[1]){
				$vars['next_page'] = "checkout.index";
			}
		break;

		case 'favourites';
			$vars['page'] = "shop.favourites";
		break;

		case 'recommend';
			$vars['page'] = "shop.recommend";
			$vars['pop'] = 1;
			$vars['product_id'] = $segments[3];
			$vars['tmpl']= "component";
		break;

		case 'user:registration';
			$vars['page'] = "shop.registration";
		break;

		case 'account':
			$vars['page'] = "account.index";
		break; 

		case 'cart':
			$vars['page'] = "shop.cart";
		break; 

		case 'order:details':
			$vars['page'] = "account.order_details";
			$vars['order_id'] = $segments[1];				
		break;

		case 'temrs:of:service':
			$vars['page'] = "shop.tos";				
		break;

		case 'notify':
			$vars['page'] = "shop.waiting_list";
			$vars['product_id'] = $segments[1];				
		break;

		case VM_ADVANCE_SEARCH:
			$vars['page'] = "shop.search";					
		break;

		case 'administration':
			$vars['page'] = "store.index";
			$vars['pshop_mode'] = "admin";
					
		break;
	}
	return $vars;
}


// This function returns category/subcatgory alias string

function getCategoryName($id){
	
	$db			= & JFactory::getDBO();
	$catIdsList = implode( ',', getCategoryRecurse($id,true) );
	// End of need to change
	$q = "SELECT GROUP_CONCAT( `category_name`
			SEPARATOR  '/' ) 
			FROM `#__vm_category`
			WHERE `category_id` IN (".$catIdsList.")";

	$db->setQuery($q);
	$category_name = $db->loadResult();
	$category_alias = strtolower($category_name);

	//Remove following characters
	$special_chars = array('!','@','#','$','%','*','(',')');
	foreach($special_chars as $char){
		$category_alias = str_replace($char,'', $category_alias);
	}

	$category_alias = str_replace(' ','-', $category_alias);
	$category_alias = str_replace('  ','-', $category_alias);
	return $category_alias;
}

function getCategoryRecurse($category_id,$first ) {
	static $idsArr = array();
	if($first) {
		$idsArr = array();
	}
	//
	$db			= & JFactory::getDBO();	
	$q = "SELECT `category_child_id` AS `child`, `category_parent_id` AS `parent`
			FROM  #__vm_category_xref AS `xref`
			WHERE `xref`.`category_child_id`= ".$category_id;
	$db->setQuery($q);
	$ids = $db->loadObject();
	if($ids->parent != 0) {
		getCategoryRecurse($ids->parent,false);
	} 
	$idsArr[] = $ids->child;
	
	if($first) {
		return $idsArr;
	}
	return;
}

function getProductName($id){

	$db			= & JFactory::getDBO();
	$query = 'SELECT `product_name` FROM `#__vm_product`  ' .
	' WHERE `product_id` = ' . (int) $id;

	$db->setQuery($query);
	// gets product name of item
	$product_name = $db->loadResult();
	$product_name = strtolower($product_name);

	//Remove following characters
	$special_chars = array('!','@','#','$','%','*','(',')');
	foreach($special_chars as $char){
		$product_name = str_replace($char,'', $product_name);
	}
	$product_name = str_replace(' ','-', $product_name);
	$product_name = str_replace('  ','-', $product_name);
	return $product_name;
}

// pure php no closing tag