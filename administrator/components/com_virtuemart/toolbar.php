<?php 
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
* This file is the ExtJS Toolbar controller for VirtueMart
*
* There are three main Toolbar cases:
* - a List Toolbar with 'New / Delete / Publish'
* - a Forms Toolbar with 'Save / Cancel'
* - no toolbar
*
*
* @version $Id$
* @package VirtueMart
* @subpackage core
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
if( stristr( $_SERVER['PHP_SELF'], 'administrator')) {
	@define( '_VM_IS_BACKEND', '1' );
}
defined('_VM_TOOLBAR_LOADED' ) or define('_VM_TOOLBAR_LOADED', 1 );
/*
//include( dirname(__FILE__).'/compat.joomla1.5.php');

//global $page, $sess; 
//if (!file_exists( $mosConfig_absolute_path.'/administrator/components/com_virtuemart/install.php' )) {
if (!file_exists( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'install.php' )) {
    // We parse the phpShop main code before loading the toolbar,
    // for we can catch errors and adjust the toolbar when
    // the admin has to stay on a site or is redirected back on error
//    require_once( $mosConfig_absolute_path.'/components/com_virtuemart/virtuemart_parser.php');
	require_once( JPATH_COMPONENT_SITE.DS.'virtuemart_parser.php');
//    require_once( JPATH_COMPONENT.DS.'virtuemart_parser.php');

	if( file_exists( $mosConfig_absolute_path.'/editor/editor.php' )) {
		require_once( $mosConfig_absolute_path.'/editor/editor.php' );
	}
	require_once( ADMINPATH.'toolbar.html.php' );
	require_once( CLASSPATH . 'menuBar.class.php' );
	$bar = vmToolBar::getInstance('virtuemart');
	// We have to do some page declarations here
	
	// Used for pages that allow (un)publishing items
	$allowsListPublish = Array( 'product.product_list', 
							'product.product_category_list',
                            'admin.user_field_list',
							'store.payment_method_list',
                            'store.export_list',
						);
	// The list of pages with their functions that allow batch deletion
	$allowsListDeletion = Array(
								'admin.country_list' => 'countryDelete',
								'admin.country_state_list' => 'stateDelete',
								'admin.curr_list' => 'currencyDelete',
								'admin.function_list' => 'functionDelete',
								'admin.module_list' => 'moduleDelete',
								'admin.user_list' => 'userDelete',
                                'admin.user_field_list' => 'userfieldDelete',
                                'admin.usergroup_list' => 'usergroupDelete',
								'affiliate.affiliate_list' => 'affiliateDelete',
								'coupon.coupon_list' => 'couponDelete',
								'store.creditcard_list' => 'creditcardDelete',
								'product.file_list' => 'deleteProductFile',
								'tax.tax_list' => 'deleteTaxRate',
								'manufacturer.manufacturer_category_list' => 'manufacturercategoryDelete',
								'manufacturer.manufacturer_list' => 'manufacturerDelete',
								'order.order_list' => 'orderDelete',
								'order.order_status_list' => 'orderStatusDelete',
                                'store.export_list' => 'ExportDelete',
								'store.payment_method_list' => 'paymentMethodDelete',
								'product.product_attribute_list' => 'productAttributeDelete',
								'product.product_category_list' => 'productCategoryDelete',
								'product.product_discount_list' => 'discountDelete',
								'product.product_list' => 'productDelete',
								'product.product_price_list' => 'productPriceDelete',
								'product.product_produt_type_list' => 'productProductTypeDelete',
								'product.review_list' => 'productReviewDelete',
								'product.product_type_list' => 'ProductTypeDelete',
								'product.product_type_parameter_list' => 'ProductTypeDeleteParam',
								'shipping.rate_list' => 'rateDelete',
								'shipping.carrier_list' => 'carrierDelete',
								'shopper.shopper_group_list' => 'shopperGroupDelete',
								'zone.zone_list' => 'deletezone'
								);
	// Can be used for lists that allow NO batch delete
	$noListDelete = Array();
	
	// Pages which don't allow new items to be created
	$noNewItem = array( 'order.order_list', 
										'store.shipping_module_list' );
	//  Forms Toolbar
	if ( stristr($page, 'form') || $page == 'admin.show_cfg' || $page == 'affiliate.affiliate_add' ) {
			
		TOOLBAR_virtuemart::FORMS_MENU_SAVE_CANCEL();
	}
	// Lists Toolbar
	elseif ( stristr($page,'list') && $page != 'affiliate.shopper_list' ) {		
		
		// Some lists allow special tasks like 'Add price' or 'Add State'
		TOOLBAR_virtuemart::LISTS_SPECIAL_TASKS( $page );
		
		if( !in_array( $page, $noNewItem )) {
			// For New / Cloning Items
			TOOLBAR_virtuemart::LISTS_MENU_NEW();
		}
		// For (Un)Publishing Items
		if( in_array( $page, $allowsListPublish )) {
			TOOLBAR_virtuemart::LISTS_MENU_PUBLISH( 'changePublishState' );
		}
		// Delete Items
		if( !empty( $allowsListDeletion[$page] )) {
			TOOLBAR_virtuemart::LISTS_MENU_DELETE( $allowsListDeletion[$page] );
		}
		
	}
	elseif( $page == 'zone.assign_zones' ) {
		
		$bar->custom( 'save', $page, 'save', 'Save Zone Assignments', true, 'adminForm', 'zoneassign' );
		
	}
    elseif( $page == 'product.product_move' ) {
         
         $bar->custom( 'save', 'product.product_list', 'save', 'Move Products', false, 'adminForm', 'productMove' );
         
         $bar->customHref( $sess->url( $_SERVER['PHP_SELF'].'?page=product.product_list'), 'cancel', JText::_('CMN_CANCEL') );
         
     }
}*/
// pure php no closing tag