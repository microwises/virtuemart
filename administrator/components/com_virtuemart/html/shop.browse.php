<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
* This is the Main Product Listing File!
*
* @version $Id$
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

// load important class files
require_once (CLASSPATH."ps_product.php");
$ps_product = new ps_product;
require_once (CLASSPATH."ps_product_category.php");
$ps_product_category = new ps_product_category;
require_once (CLASSPATH."ps_product_files.php");
require_once (CLASSPATH."ps_reviews.php");
require_once (CLASSPATH."imageTools.class.php");
require_once (CLASSPATH."PEAR/Table.php");
require_once(CLASSPATH . 'ps_product_attribute.php' );
$ps_product_attribute = new ps_product_attribute;

$Itemid = $sess->getShopItemid();
$keyword1 = $vmInputFilter->safeSQL( urldecode(JRequest::getVar(  'keyword1', null )));
$keyword2 = $vmInputFilter->safeSQL( urldecode(JRequest::getVar(  'keyword2', null )));

$search_op= $vmInputFilter->safeSQL( JRequest::getVar(  'search_op', null ));
$search_limiter= $vmInputFilter->safeSQL( JRequest::getVar(  'search_limiter', null ));

$limit = $vmInputFilter->safeSQL( JRequest::getVar(  'limit', null ));

if (empty($category_id)) { 
	$category_id = $search_category;
} else {
	//we have a category_id so get the current page from session variables
	if(is_null($limit) && @$_SESSION['limit'][$category_id]) {
		$limit = $_SESSION["limit"][$category_id];
	} 
	if(!$limitstart && @$_SESSION['limitstart'][$category_id]) {
		$limitstart = $_SESSSION['limitstart'][$category_id];
	}
}

if(!@$_SESSION["firstlimit"][$category_id] ) {
	//If limit is null, check to see if we have set max amount of records to display
	$maxDisplay = ps_product_category::getMaxDisplayRecords($category_id);
	if(!is_null($maxDisplay)) {
		$limit = $maxDisplay;
	}
}
$_SESSION["limit"][$category_id] = $limit;
$_SESSION["limitstart"][$category_id] = $limitstart;
$_SESSION["firstlimit"][$category_id] = true;
$default['category_flypage'] = FLYPAGE;
//echo('shop.browse: $limit '.$limit.' $limitstart'.$limitstart);
$db_browse = new ps_DB;
$dbp = new ps_DB;
// NEW: Include the query section from an external file
require_once( PAGEPATH. "shop.browse_queries.php" );

$db_browse->query( $count );

$num_rows = $db_browse->f("num_rows");

if( $limitstart > 0 && $limit >= $num_rows) {

	$list = str_replace( 'LIMIT '.$limitstart, 'LIMIT 0', $list );
}
if( $category_id ) {
	/**
    * CATEGORY DESCRIPTION
    */
    
    
//	$db->query( "SELECT category_id, category_name, metadesc, metakey, metarobot, metaauthor FROM #__{vm}_category WHERE category_id='$category_id'");
	//That may be faster
	$db->query( "SELECT * FROM #__{vm}_category WHERE category_id='$category_id'");
//	$db->query( "SELECT category_id, category_name FROM #__{vm}_category WHERE category_id='$category_id'");  //this is old and can be deleted after test 12.6.09
	$db->next_record();
	$category_name = shopMakeHtmlSafe( $db->f('category_name') );

	/* Set Dynamic Page Title */
	$mainframe->setPageTitle( $db->f("category_name") );
	$desc =  $ps_product_category->get_description($category_id);
	$desc = vmCommonHTML::ParseContentByPlugins( $desc );
	if($db->f("metadesc") == "") {
		$metadesc = vmCommonHTML::ParseContentByPlugins( $desc );
		if (substr(strip_tags($metadesc ), 0, 255) == "") {
			$metadesc = $db->f("category_name");
		}
	}
	else {
		$metadesc = $db->f('metadesc');
	}
	/* Prepend Product Short Description Meta Tag "description" when applicable */
	//$mainframe->prependMetaTag( "description", substr(strip_tags($desc ), 0, 255) );
	$metadata[] = array('type' => 'prepend',
						'title' => 'description',
						'meta' => substr(strip_tags($metadesc ), 0, 255));
	if($db->f("metakey") != "") {
		$metadata[] =array ('type' => 'prepend',
						'title' => 'keywords',
						'meta' => substr(strip_tags($db->f("metakey") ), 0, 255));
	}
	if($db->f("metarobot") != "") {
		$metadata[] =array ('type' => 'set',
						'title' => 'robots',
						'meta' => substr(strip_tags($db->f("metarobot") ), 0, 255));
	}
	if($db->f("metaauthor") != "") {
		$metadata[] =array ('type' => 'prepend',
						'title' => 'author',
						'meta' => substr(strip_tags($db->f("metaauthor") ), 0, 255));
	}
	vmSetMetaData($metadata);


}
// when nothing has been found we tell this here and say goodbye
if ($num_rows == 0 && (!empty($keyword)||!empty($keyword1))) {
	echo JText::_('VM_NO_SEARCH_RESULT');
}
elseif( $num_rows == 0 && empty($product_type_id) && !empty($child_list)) {
	echo JText::_('EMPTY_CATEGORY');
}

elseif( $num_rows == 1 && ( !empty($keyword) || !empty($keyword1) ) ) {
	// If just one product has been found, we directly show the details page of it
	$db_browse->query( $list );
	$db_browse->next_record();
	$flypage = $db_browse->sf("category_flypage") ? $db_browse->sf("category_flypage") : FLYPAGE;

	$url_parameters = "page=shop.product_details&amp;flypage=$flypage&amp;product_id=" . $db_browse->f("product_id") . "&amp;category_id=" . $db_browse->f("category_id");
	vmRedirect( $sess->url($url_parameters, true, false ) );
}
else {
	// NOW START THE PRODUCT LIST
	$tpl = vmTemplate::getInstance();
	$buttons_header = $tpl->fetch( 'common/buttons.tpl.php' );
	$tpl->set('buttons_header',$buttons_header);
	if( $category_id ) {
		/**
	    * CATEGORY DESCRIPTION
	    */
		$browsepage_lbl = $category_name;
		$tpl->set( 'browsepage_lbl', $browsepage_lbl );

		$tpl->set( 'desc', $desc );

		$category_childs = $ps_product_category->get_child_list($category_id);
		$tpl->set( 'categories', $category_childs );
		$navigation_childlist = $tpl->fetch( 'common/categoryChildlist.tpl.php');
		$tpl->set( 'navigation_childlist', $navigation_childlist );

		// Set up the CMS pathway
		$category_list = array_reverse( $ps_product_category->get_navigation_list($category_id) );
		$pathway = $ps_product_category->getPathway( $category_list );
		$vm_mainframe->vmAppendPathway( $pathway );

		$tpl->set( 'category_id', $category_id );
		$tpl->set( 'category_name', $category_name );

		$browsepage_header = $tpl->fetch( 'browse/includes/browse_header_category.tpl.php' );

	}
	elseif( $manufacturer_id) {
		$db->query( "SELECT manufacturer_id, mf_name, mf_desc FROM #__{vm}_manufacturer WHERE manufacturer_id='$manufacturer_id'");
		$db->next_record();
		$mainframe->setPageTitle( $db->f("mf_name") );

		$browsepage_lbl = shopMakeHtmlSafe( $db->f("mf_name") );
		$tpl->set( 'browsepage_lbl', $browsepage_lbl );
		$browsepage_lbltext = $db->f("mf_desc");
		$tpl->set( 'browsepage_lbltext', $browsepage_lbltext );
		$browsepage_header = $tpl->fetch( 'browse/includes/browse_header_manufacturer.tpl.php' );
	}
	elseif( $keyword ) {
		$mainframe->setPageTitle( JText::_('VM_SEARCH_TITLE',false) );
		$browsepage_lbl = JText::_('VM_SEARCH_TITLE') .': '.shopMakeHtmlSafe( $keyword );
		$tpl->set( 'browsepage_lbl', $browsepage_lbl );

		$browsepage_header = $tpl->fetch( 'browse/includes/browse_header_keyword.tpl.php' );
	}
	else {
		$mainframe->setPageTitle( JText::_('VM_BROWSE_LBL',false) );#
		$browsepage_lbl = JText::_('VM_BROWSE_LBL');
		$tpl->set( 'browsepage_lbl', $browsepage_lbl );

		$browsepage_header = $tpl->fetch( 'browse/includes/browse_header_all.tpl.php' );
	}
	$tpl->set( 'browsepage_header', $browsepage_header );

	if (!empty($product_type_id) && @$_REQUEST['output'] != "pdf") {
		$tpl->set( 'ps_product_type', $ps_product_type);
		$tpl->set( 'product_type_id', $product_type_id);
		$parameter_form = $tpl->fetch( 'browse/includes/browse_searchparameter_form.tpl.php' );
	}
	else {
		$parameter_form = '';
	}
	$tpl->set( 'parameter_form', $parameter_form );

	$db_browse->query( $list );
	$db_browse->next_record();

	$products_per_row = (!empty($category_id)) ? $db_browse->f("products_per_row") : PRODUCTS_PER_ROW;

	if( $products_per_row < 1 ) {
		$products_per_row = 1;
	}
	$tpl->set('products_per_row', $products_per_row );
	$tpl->set('prodlimit', $limit );
	
	// Prepare Page Navigation
	require_once( CLASSPATH . 'pageNavigation.class.php' );
	
	$pagenav = new vmPageNav( $num_rows, $limitstart, $limit );
	$tpl->set( 'pagenav', $pagenav );
	
	// Decide whether to show the limit box
	$show_limitbox = ( $num_rows > $products_per_row && @$_REQUEST['output'] != "pdf" );
	$tpl->set( 'show_limitbox', $show_limitbox );

	// Decide whether to show the top navigation
	$show_top_navigation = ( PSHOP_SHOW_TOP_PAGENAV =='1' && $num_rows > $products_per_row );
	$tpl->set( 'show_top_navigation', $show_top_navigation );

	$search_string = '';
	if ( $num_rows > 1 && @$_REQUEST['output'] != "pdf") {
		if ( $num_rows > $products_per_row ) { // simplified logic
			$search_string = $mm_action_url."index.php?option=com_virtuemart&amp;Itemid=$Itemid&amp;category_id=$category_id&amp;page=$modulename.browse";
			$search_string .= empty($manufacturer_id) ? '' : "&amp;manufacturer_id=$manufacturer_id";
			$search_string .= empty($keyword) ? '' : '&amp;keyword='.urlencode( $keyword );
			if (!empty($keyword1)) {
				$search_string.="&amp;keyword1=".urlencode($keyword1);
				$search_string.="&amp;search_category=".urlencode($search_category);
				$search_string.="&amp;search_limiter=$search_limiter";
				if (!empty($keyword2)) {
					$search_string.="&amp;keyword2=".urlencode($keyword2);
					$search_string.="&amp;search_op=".urlencode($search_op);
				}
			}

			if (!empty($product_type_id)){
				foreach($_REQUEST as $key => $value){
					if (substr($key, 0,13) == "product_type_"){
						$val = JRequest::getVar( $key );
						if( is_array( $val )) {
							foreach( $val as $var ) {
								$search_string .="&".$key."[]=".urlencode($var);
							}
						} else {
							$search_string .="&".$key."=".urlencode($val);
						}
					}
				}
			}

		}

		$tpl->set( 'VM_BROWSE_ORDERBY_FIELDS', $VM_BROWSE_ORDERBY_FIELDS);

	    if ($DescOrderBy == "DESC") {
	        $icon = "sort_desc.png";
	        $selected = Array( "selected=\"selected\"", "" );
		  	$asc_desc = Array( "DESC", "ASC" );
		}
		else {
		  	$icon = "sort_asc.png";
	        $selected = Array( "", "selected=\"selected\"" );
	        $asc_desc = Array( "ASC", "DESC" );
	    }
		$tpl->set( 'orderby', $orderby );
		$tpl->set( 'icon', $icon );
		$tpl->set( 'selected', $selected );
		$tpl->set( 'asc_desc', $asc_desc );
		$tpl->set( 'category_id', $category_id );
		$tpl->set( 'manufacturer_id', $manufacturer_id );
		$tpl->set( 'keyword', urlencode( $keyword ) );
		$tpl->set( 'keyword1', urlencode( $keyword1 ) );
		$tpl->set( 'keyword2', urlencode( $keyword2 ) );
		$tpl->set( 'Itemid', $Itemid );

		if( $show_top_navigation ) {
			$tpl->set( 'search_string', $search_string );
		}

		$orderby_form = $tpl->fetch( 'browse/includes/browse_orderbyform.tpl.php' );
		$tpl->set( 'orderby_form', $orderby_form );
    }
    else {
    	$tpl->set( 'orderby_form', '' );
    }
	

	//$buttons_header = '';
	/**
	 *   Start caching all product details for a later loop
	 *
	 **/
	if(@$_REQUEST['output'] != "pdf") {

		// Show the PDF, Email and Print buttons
		$tpl->set('option', $option);
		$tpl->set('category_id', $category_id );
		$tpl->set('product_id', $product_id );
		$buttons_header = $tpl->fetch( 'common/buttons.tpl.php' );

		$templatefile = (!empty($category_id)) ? $db_browse->f("category_browsepage") : CATEGORY_TEMPLATE;
		if( $templatefile == 'managed' ) {
			// automatically select the browse template with the best match for the number of products per row
			$templatefile = file_exists(VM_THEMEPATH.'templates/browse/browse_'.$products_per_row.'.php' )
								? 'browse_'.$products_per_row
								: 'browse_5';
		} elseif( !file_exists(VM_THEMEPATH.'templates/browse/'.$templatefile.'.php')) {
			$templatefile = 'browse_5';
		}
	}
	else {
		$templatefile = "browse_lite_pdf";
	}

	$tpl->set( 'buttons_header', $buttons_header );
	
	$tpl->set('templatefile', $templatefile );

	$db_browse->reset();

	$products = array();
	$counter = 0;
	/*** Start printing out all products (in that category) ***/
	while ($db_browse->next_record()) {

		// If it is item get parent:
		$product_parent_id = $db_browse->f("product_parent_id");
		if ($product_parent_id != 0) {
			$dbp->query("SELECT product_full_image,product_thumb_image,product_name,product_s_desc FROM #__{vm}_product WHERE product_id='$product_parent_id'" );
			$dbp->next_record();
		}

		// Set the flypage for this product based on the category.
		// If no flypage is set then use the default as set in virtuemart.cfg.php
		$flypage = $db_browse->sf("category_flypage");

		if (empty($flypage)) {
            $flypage = FLYPAGE;
        }
        $url_parameters = "page=shop.product_details&amp;flypage=$flypage&amp;product_id=" . $db_browse->f("product_id") . "&amp;category_id=" . $db_browse->f("category_id");
        if( $manufacturer_id ) {
        	$url_parameters .= "&amp;manufacturer_id=" . $manufacturer_id;
        }
        if( $keyword != '') {
        	$url_parameters .= "&amp;keyword=".urlencode($keyword);
        }
        $url = $sess->url( $url_parameters );

        // Price: xx.xx EUR
		if (_SHOW_PRICES == '1' && $auth['show_prices']) {

			$product_price = "";
			$product_price_with_tax = "";
			$product_price_without_tax = "";

			if ($auth["show_price_including_tax"] == 1){
				$product_price = $ps_product->show_price_with_tax( $db_browse->f("product_id") );
				if (VM_PRICE_SHOW_WITHOUTTAX == 1){
					$product_price_without_tax = $ps_product->show_price_without_tax( $db_browse->f("product_id") );
				}
			}else{

				$product_price = $ps_product->show_price_without_tax( $db_browse->f("product_id") );
				if (VM_PRICE_SHOW_WITHTAX == 1){
					$product_price_with_tax = $ps_product->show_price_with_tax( $db_browse->f("product_id") );
				}
		 	}
			//$product_price = $ps_product->show_price( $db_browse->f("product_id") );
		}
		else {
			$product_price = "";
			$product_price_with_tax = "";
			$product_price_without_tax = "";
		}
		// @var array $product_price_raw The raw unformatted Product Price in Float Format
		$product_price_raw = $ps_product->get_adjusted_attribute_price($db_browse->f('product_id'));

		// i is the index for the array holding all products, we need to show. to allow sorting by discounted price,
		// we need to use the price as first part of the index name!
		$i = $product_price_raw['product_price'] . '_' . ++$counter;

        if( $db_browse->f("product_thumb_image") ) {
            $product_thumb_image = $db_browse->f("product_thumb_image");
		}
		else {
			if( $product_parent_id != 0 ) {
				$product_thumb_image = $dbp->f("product_thumb_image"); // Use product_thumb_image from Parent Product
			}
			else {
				$product_thumb_image = 0;
			}
		}
		if( $product_thumb_image ) {
			if( substr( $product_thumb_image, 0, 4) != "http" ) {
				if(PSHOP_IMG_RESIZE_ENABLE == '1') {
					$product_thumb_image = $mosConfig_live_site."/components/com_virtuemart/show_image_in_imgtag.php?filename=".urlencode($product_thumb_image)."&amp;newxsize=".PSHOP_IMG_WIDTH."&amp;newysize=".PSHOP_IMG_HEIGHT."&amp;fileout=";
				}
				elseif( !file_exists( IMAGEPATH."product/".$product_thumb_image )) {
                    $product_thumb_image = VM_THEMEURL.'images/'.NO_IMAGE;
                }
			}
		}
		else {
			$product_thumb_image = VM_THEMEURL.'images/'.NO_IMAGE;
		}

		// Get the full image path, or URL if set, or the no_image
		if( $db_browse->f("product_full_image") ) {
			$product_full_image = $db_browse->f("product_full_image");
		} elseif( $product_parent_id != 0 ) {
			$product_full_image = $dbp->f("product_full_image"); // Use product_full_image from Parent Product
		}
		else {
			$product_full_image = VM_THEMEURL . 'images/' . NO_IMAGE;

			// Get the size information for the no_image
			if( file_exists( VM_THEMEPATH . 'images/' . NO_IMAGE ) ) {
				$full_image_info = getimagesize( VM_THEMEPATH . 'images/' . NO_IMAGE );
				$full_image_width = $full_image_info[0]+40;
				$full_image_height = $full_image_info[1]+40;
			}
		}
		$products[$i]['product_full_image_no_url'] = $product_full_image;
		
		// Get image size information and add the full URL
		if( substr( $product_full_image, 0, 4) != 'http' ) {
			// This is a local image
			if( file_exists( IMAGEPATH . 'product/' . $product_full_image ) ) {
				$full_image_info = getimagesize( IMAGEPATH . 'product/' . $product_full_image );
				$full_image_width = $full_image_info[0]+40;
				$full_image_height = $full_image_info[1]+40;
			}
			$products[$i]['product_full_image_no_url'] = $product_full_image;
			
			$product_full_image = IMAGEURL . 'product/' . $product_full_image;
		} elseif( !isset( $full_image_width ) || !isset( $full_image_height ) ) {
			// This is a URL image
			$full_image_info = @getimagesize( $product_full_image );
			$full_image_width = $full_image_info[0]+40;
			$full_image_height = $full_image_info[1]+40;
		}

		$files = ps_product_files::getFilesForProduct( $db_browse->f('product_id') );
		$products[$i]['files'] = $files['files'];
		$products[$i]['images'] = $files['images'];

		$product_name = $db_browse->f("product_name");
		if( $db_browse->f("product_publish") == "N" ) {
			$product_name .= " (". JText::_('CMN_UNPUBLISHED',false) .")";
		}

		if( empty($product_name) && $product_parent_id!=0 ) {
			$product_name = $dbp->f("product_name"); // Use product_name from Parent Product
		}
		$product_s_desc = $db_browse->f("product_s_desc");
		if( empty($product_s_desc) && $product_parent_id!=0 ) {
			$product_s_desc = $dbp->f("product_s_desc"); // Use product_s_desc from Parent Product
		}
		$product_details = JText::_('VM_FLYPAGE_LBL');
		$product_vendor = JText::_('VM_BROWSE_VENDOR_LBL') . $ps_product->get_vendorname($db_browse->f("product_id"));
		if (PSHOP_ALLOW_REVIEWS == '1' && @$_REQUEST['output'] != "pdf") {
			// Average customer rating: xxxxx
	        // Total votes: x
			$product_rating = ps_reviews::allvotes( $db_browse->f("product_id") );
		}
		else {
			$product_rating = "";
		}
		// Stock Level Indicator
		$products[$i]['stock_level'] = $ps_product->stockIndicator($db_browse->f("product_in_stock"),$db_browse->f("low_stock_notification"),$db_browse->f("product_id"));		
		
		// Add-to-Cart Button 
		if (USE_AS_CATALOGUE != '1'  
			&& $tpl->get_cfg( 'showAddtocartButtonOnProductList' ) ) {
			$tpl->set('flypage',$flypage);	
			$tpl->set( 'i', $i );
			$tpl->set( 'product_id', $db_browse->f('product_id') );
			$tpl->set( 'product_in_stock', $db_browse->f('product_in_stock') );
			$tpl->set( 'ps_product_attribute', $ps_product_attribute );
			$tpl->set( 'ps_product', $ps_product );
			$tpl->set('call_for_pricing',($product_price != "" && !stristr( $product_price, JText::_('VM_PRODUCT_CALL') )) ? false : true);			
			$products[$i]['form_addtocart'] = $tpl->fetch( 'browse/includes/addtocart_form.tpl.php' );
//			$products[$i]['form_addtocart'] = $tpl->fetch('product_details/includes/addtocart_form.tpl.php' );
			$products[$i]['has_addtocart'] = true;
		}
		else {
			$products[$i]['form_addtocart'] = '';
			$products[$i]['has_addtocart'] = false;
		}
		if(stristr( $product_price, JText::_('VM_PRODUCT_CALL')) && $ps_product->parent_has_children($db_browse->f('product_id'))) {
			$product_price = '';
		}
		$products[$i]['product_flypage'] = $url;
		$products[$i]['product_thumb_image'] = $product_thumb_image;
		$products[$i]['product_full_image'] = $product_full_image;
		$products[$i]['full_image_width'] = $full_image_width;
		$products[$i]['full_image_height'] = $full_image_height;

		// Unset these for the next product
		unset($full_image_width);
		unset($full_image_height);

		$products[$i]['product_name'] = shopMakeHtmlSafe( $product_name );
		$products[$i]['product_s_desc'] = $product_s_desc;
		$products[$i]['product_details'] = $product_details;
		$products[$i]['product_vendor'] = $product_vendor;
		$products[$i]['product_rating'] = $product_rating;
		$products[$i]['product_price'] = $product_price;
		$products[$i]['product_price_with_tax'] = $product_price_with_tax;
		$products[$i]['product_price_without_tax'] = $product_price_without_tax;
		$products[$i]['product_price_raw'] = $product_price_raw;
		$products[$i]['product_sku'] = $db_browse->f("product_sku");
		$products[$i]['product_weight'] = $db_browse->f("product_weight");
		$products[$i]['product_weight_uom'] = $db_browse->f("product_weight_uom");
		$products[$i]['product_length'] = $db_browse->f("product_length");
		$products[$i]['product_width'] = $db_browse->f("product_width");
		$products[$i]['product_height'] = $db_browse->f("product_height");
		$products[$i]['product_lwh_uom'] = $db_browse->f("product_lwh_uom");
		$products[$i]['product_in_stock'] = $db_browse->f("product_in_stock");
		$products[$i]['product_available_date'] = vmFormatDate($db_browse->f("product_available_date"), JText::_('DATE_FORMAT_LC') );
		$products[$i]['product_availability'] = $db_browse->f("product_availability");
		$products[$i]['cdate'] = vmFormatDate($db_browse->f("cdate"), JText::_('DATE_FORMAT_LC') );
		$products[$i]['mdate'] = vmFormatDate($db_browse->f("mdate"), JText::_('DATE_FORMAT_LC') );
		$products[$i]['product_url'] = $db_browse->f("product_url");

	} // END OF while loop

	// Need to re-order here, because the browse query doesn't fetch discounts
	if( $orderby == 'product_price' ) {
		if ($DescOrderBy == "DESC") {
			// using krsort when the Array must be sorted reverse (Descending Order)
			krsort($products, SORT_NUMERIC);
		} else {
			// using ksort when the Array must be sorted in ascending order
			ksort($products, SORT_NUMERIC);
		}
	}
	$tpl->set( 'products', $products );
	$tpl->set( 'search_string', $search_string );

	if ( $num_rows > 1 ) {
		$browsepage_footer = $tpl->fetch( 'browse/includes/browse_pagenav.tpl.php' );
		$tpl->set( 'browsepage_footer', $browsepage_footer );
	} else {
		$tpl->set( 'browsepage_footer', '' );
	}


	$recent_products = $ps_product->recentProducts(null,$tpl->get_cfg('showRecent', 5));
	$tpl->set('recent_products',$recent_products);

	$tpl->set('ps_product',$ps_product);

	echo $tpl->fetch( $tpl->config->get( 'productListStyle' ) );
}
?>
