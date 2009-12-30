<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/*
* This file is to be included from the file shop.browse.php
* and uses variables from the environment of the file shop.browse.php
*
* @version $Id: shop.browse_queries.php 1768 2009-05-11 22:24:39Z macallf $
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

$mainframe = JFactory::getApplication() ;
// Descending or Ascending Order? possible values: [ASC|DESC]
//$DescOrderBy = $vmInputFilter->safeSQL( $vm_mainframe->getUserStateFromRequest( "browse{$keyword}{$category_id}{$manufacturer_id}DescOrderBy", 'DescOrderBy', "ASC" ) );
$DescOrderBy = $vmInputFilter->safeSQL( $mainframe->getUserStateFromRequest( "browse{$keyword}{$category_id}{$manufacturer_id}DescOrderBy", 'DescOrderBy', "ASC" ) );

// Sort by which factor? possible values: 
// product_list, product_name, product_price, product_sku, product_cdate (=latest additions)
//$orderby = $vmInputFilter->safeSQL( $vm_mainframe->getUserStateFromRequest( "browse{$keyword}{$category_id}{$manufacturer_id}orderby", 'orderby', VM_BROWSE_ORDERBY_FIELD ));
$orderby = $vmInputFilter->safeSQL( $mainframe->getUserStateFromRequest( "browse{$keyword}{$category_id}{$manufacturer_id}orderby", 'orderby', VM_BROWSE_ORDERBY_FIELD ));

$featured = JRequest::getVar( 'featured', 'N' );
$discounted = JRequest::getVar( 'discounted', 'N' );

/** Prepare the SQL Queries
*
*/
// These are the names of all fields we fetch data from
$fieldnames = "`product_name`,`products_per_row`,`category_browsepage`,`category_flypage`,`#__{vm}_category`.`category_id`,
				`#__{vm}_product`.`product_id`,`product_full_image`,`product_thumb_image`,`product_s_desc`,`product_parent_id`,`product_publish`,`product_in_stock`,`product_sku`, `product_url`,
				`product_weight`,`product_weight_uom`,`product_length`,`product_width`,`product_height`,`product_lwh_uom`,`product_in_stock`,`low_stock_notification`,`product_available_date`,`product_availability`,`#__{vm}_product`.`mdate`, `#__{vm}_product`.`cdate`, `#__{vm}_product`.`product_sales`";
$count_name = "COUNT(DISTINCT `#__{vm}_product`.`product_sku`) as num_rows";
$table_names = '`#__{vm}_product`, `#__{vm}_category`, `#__{vm}_product_category_xref`,`#__{vm}_shopper_group`';

$join_array = array( 'LEFT JOIN `#__{vm}_product_price` ON `#__{vm}_product`.`product_id` = `#__{vm}_product_price`.`product_id`' );
$where_clause = array();

switch( $orderby ) {
	case 'product_list':
		$orderbyField = '`#__{vm}_product_category_xref`.`product_list`'; break;
	case 'product_name':
		$orderbyField = '`#__{vm}_product`.`product_name`'; break;
	case 'product_price':
		$orderbyField = '`#__{vm}_product_price`.`product_price`'; break;
	case 'product_sku':
		$orderbyField = '`#__{vm}_product`.`product_sku`'; break;
	case 'product_cdate':
		$orderbyField = '`#__{vm}_product`.`cdate`'; break;
	case 'product_sales':
		$orderbyField = '`#__{vm}_product`.`product_sales`'; break;
	default:
		$orderbyField = '`#__{vm}_product`.`product_name`'; break;
}

$where_clause[] = "`#__{vm}_product_category_xref`.`product_id`=`#__{vm}_product`.`product_id`";
$where_clause[] = "`#__{vm}_product_category_xref`.`category_id`=`#__{vm}_category`.`category_id`";
// Filter Products by Category
if( $category_id ) {
	if( !empty( $search_this_category ) && (!empty( $keyword ) || !empty( $manufacturer_id ) )) {
		$where_clause[] = "`#__{vm}_product_category_xref`.`category_id`=".$category_id;
	} elseif( empty( $keyword ) && empty( $manufacturer_id )) {
		$where_clause[] = "`#__{vm}_product_category_xref`.`category_id`=".$category_id;
	}
}
if( strtoupper($featured) == 'Y' ) {
	// Filter all except Featured Products (="on special")
	$where_clause[] = '`#__{vm}_product`.`product_special`=\'Y\'';
}
if( strtoupper($discounted) == 'Y' ) {
	// Filter all except Discounted Products
	$where_clause[] = '`#__{vm}_product`.`product_discount_id` > 0';
}

$keywordArr = vmGetCleanArrayFromKeyword( $keyword );
// This is the "advanced" search, filter by Keyword1 and Keyword2
$keyword1Arr = vmGetCleanArrayFromKeyword( $keyword1 );
$keyword2Arr = vmGetCleanArrayFromKeyword( $keyword2 );

// This is the "normal" search
if( !empty($keywordArr) ) {
	$sq = "(";
	$numKeywords = count( $keywordArr );
	$i = 1;
	foreach( $keywordArr as $searchstring ) {
		$sq .= "\n (`#__{vm}_product`.`product_name` LIKE '%$searchstring%' OR ";
		$sq .= "\n `#__{vm}_product`.`product_sku` LIKE '%$searchstring%' OR ";
		$sq .= "\n `#__{vm}_product`.`product_s_desc` LIKE '%$searchstring%' OR ";
		$sq .= "\n `#__{vm}_product`.`product_desc` LIKE '%$searchstring%') ";
	
		if( $i++ < $numKeywords ) {
			$sq .= "\n  AND ";
		}
	}
	$sq .= ")";
	$where_clause[] = $sq;
}	
// Process the advanced search
elseif( !empty($keyword1Arr) ) {
	$sq = "(";
	$numKeywords = count( $keyword1Arr );
	$i = 1;
	foreach( $keyword1Arr as $searchstring ) {
		switch($search_limiter) {
			case "name":
			$sq .= "\n `#__{vm}_product`.`product_name` LIKE '%$searchstring%' ";
			break;
		case "cp":
			$sq .= "\n `#__{vm}_product`.`product_url` LIKE '%$searchstring%' ";
			break;
		case "desc":
			$sq .= "\n (`#__{vm}_product`.`product_s_desc` LIKE '%$searchstring%' OR ";
			$sq .= "\n `#__{vm}_product`.`product_desc` LIKE '%$searchstring%') ";
			break;
		default:
			$sq .= "\n (`#__{vm}_product`.`product_name` LIKE '%$searchstring%' OR ";
			$sq .= "\n `#__{vm}_product`.`product_url` LIKE '%$searchstring%' OR ";
			$sq .= "\n `#__{vm}_category`.`category_name` LIKE '%$searchstring%' OR ";
			$sq .= "\n `#__{vm}_product`.`product_sku` LIKE '%$searchstring%' OR ";
			$sq .= "\n `#__{vm}_product`.`product_s_desc` LIKE '%$searchstring%' OR ";
			$sq .= "\n `#__{vm}_product`.`product_desc` LIKE '%$searchstring%') ";
		}
		if( $i++ < $numKeywords ) {
			$sq .= "\n  AND ";
		}
	}
	$sq .= ") ";
	// KEYWORD 2 TO REFINE THE SEARCH
	if ( !empty($keyword2Arr) ) {
		$sq .= "\n $search_op (";
		$numKeywords = count( $keyword2Arr );
		$i = 1;
		foreach( $keyword2Arr as $searchstring ) {
			switch($search_limiter) {
				case "name":
				$sq .= "\n `#__{vm}_product`.`product_name` LIKE '%$searchstring%' ";
				break;
			case "cp":
				$sq .= "\n `#__{vm}_product`.`product_url` LIKE '%$searchstring%' ";
				break;
			case "desc":
				$sq .= "\n (`#__{vm}_product`.`product_s_desc` LIKE '%$searchstring%' OR ";
				$sq .= "\n `#__{vm}_product`.`product_desc` LIKE '%$searchstring%')";
				break;
			default:
				$sq .= "\n (`#__{vm}_product`.`product_name` LIKE '%$searchstring%' OR ";
				$sq .= "\n `#__{vm}_product`.`product_url` LIKE '%$searchstring%' OR ";
				$sq .= "\n `#__{vm}_category`.`category_name` LIKE '%$searchstring%' OR ";
				$sq .= "\n `#__{vm}_product`.`product_sku` LIKE '%$searchstring%' OR ";
				$sq .= "\n `#__{vm}_product`.`product_s_desc` LIKE '%$searchstring%' OR ";
				$sq .= "\n `#__{vm}_product`.`product_desc` LIKE '%$searchstring%')";
			}
			if( $i++ < $numKeywords ) {
				$sq .= "\n  AND ";
			}
		}
		$sq .= "\n ) ";
	}
	$where_clause[] = $sq;
}


// GET ALL PUBLISHED PRODUCTS FROM THAT MANUFACTURER
if (!empty($manufacturer_id)) {
	$table_names .= ',`#__{vm}_product_mf_xref`';	
	$where_clause[]  = "manufacturer_id='".$manufacturer_id."'";
	$where_clause[] = "`#__{vm}_product`.`product_id`=`#__{vm}_product_mf_xref`.`product_id` ";

}

// Filter Products by Product Type
if (!empty($product_type_id)) {
	require_once (CLASSPATH."ps_product_type.php");
	$ps_product_type = new ps_product_type();

	// list parameters:
	$q  = "SELECT `parameter_name`, `parameter_type` FROM `#__{vm}_product_type_parameter` WHERE `product_type_id`='$product_type_id'";
	$db_browse->query($q);

	/*** GET ALL PUBLISHED PRODUCT WHICH MATCH PARAMETERS ***/
	$join_array[] = "LEFT JOIN `#__{vm}_product_type_$product_type_id` ON `#__{vm}_product`.`product_id` = `#__{vm}_product_type_$product_type_id`.`product_id` ";
	$join_array[] = "LEFT JOIN `#__{vm}_product_product_type_xref` ON `#__{vm}_product`.`product_id` = `#__{vm}_product_product_type_xref`.`product_id` ";
	$where_clause[] = "`#__{vm}_product_product_type_xref`.`product_type_id`=$product_type_id ";

	// find by parameters
	while ($db_browse->next_record()) {
		$parameter_name = $db_browse->f("parameter_name");
		$item_name = "product_type_$product_type_id"."_".$parameter_name;
		$get_item_value = JRequest::getVar( $item_name, "");
		$get_item_value_comp = JRequest::getVar( $item_name."_comp", "");

		if (is_array($get_item_value) ? count($get_item_value) : strlen($get_item_value) ) {
			// comparison
			switch ($get_item_value_comp) {
				case "lt": $comp = " < "; break;
				case "le": $comp = " <= "; break;
				case "eq": $comp = " <=> "; break;
				case "ge": $comp = " >= "; break;
				case "gt": $comp = " > "; break;
				case "ne": $comp = " <> "; break;
				case "texteq":
					$comp = " <=> ";
					break;
				case "like":
					$comp = " LIKE ";
					$get_item_value = "%".$get_item_value."%";
					break;
				case "notlike":
					$comp = "COALESCE(`".$parameter_name."` NOT LIKE '%".$get_item_value."%',1)";
					$parameter_name = "";
					$get_item_value = "";
					break;
				case "in": // Multiple section List of values
					$comp = " IN ('".join("','",$get_item_value)."')";
					$get_item_value = "";
					break;
				case "fulltext":
					$comp = "MATCH (`".$parameter_name."`) AGAINST ";
					$parameter_name = "";
					$get_item_value = "('".$get_item_value."')";
					break;
				case "find_in_set":
					$comp = "FIND_IN_SET('$get_item_value',REPLACE(`$parameter_name`,';',','))";
					$parameter_name = "";
					$get_item_value = "";
					break;
				case "find_in_set_all":
				case "find_in_set_any":
					$comp = array();
					foreach($get_item_value as $value) {
						array_push($comp,"FIND_IN_SET('$value',REPLACE(`$parameter_name`,';',','))");
					}
					$comp = "(" . join($get_item_value_comp == "find_in_set_all"?" AND ":" OR ", $comp) . ")";
					$parameter_name = "";
					$get_item_value = "";
					break;
			}
			switch ($db_browse->f("parameter_type")) {
				case "D": $get_item_value = "CAST('".$get_item_value."' AS DATETIME)"; break;
				case "A": $get_item_value = "CAST('".$get_item_value."' AS DATE)"; break;
				case "M": $get_item_value = "CAST('".$get_item_value."' AS TIME)"; break;
				case "C": $get_item_value = "'".substr($get_item_value,0,1)."'"; break;
				default:
					if( strlen($get_item_value) ) $get_item_value = "'".$get_item_value."'";
			}
			if( !empty($parameter_name) ) $parameter_name = "`".$parameter_name."`";
			$where_clause[] = $parameter_name.$comp.$get_item_value." ";
		}
	}
	$item_name = "price";
	$get_item_value = JRequest::getVar( $item_name, "");
	$get_item_value_comp = JRequest::getVar( $item_name."_comp", "");
	// search by price
	if (!empty($get_item_value)) {
		// comparison
		switch ($get_item_value_comp) {
			case "lt": $comp = " < "; break;
			case "le": $comp = " <= "; break;
			case "eq": $comp = " = "; break;
			case "ge": $comp = " >= "; break;
			case "gt": $comp = " > "; break;
			case "ne": $comp = " <> "; break;
		}
		$where_clause[] = "( ISNULL(product_price) OR product_price".$comp.$get_item_value." ) ";
		$auth = $_SESSION['auth'];
		// get Shopper Group
		$sgq = "( ISNULL(`#__{vm}_product_price`.`shopper_group_id`) OR `#__{vm}_product_price`.`shopper_group_id` IN (";
		$comma="";
		if ($auth["user_id"] != 0) { // find user's Shopper Group
			$q2 = "SELECT `shopper_group_id` FROM `#__{vm}_shopper_vendor_xref` WHERE `user_id`='".$auth["user_id"]."'";
			$db_browse->query($q2);
			while ($db_browse->next_record()) {
				$sgq .= $comma.$db_browse->f("shopper_group_id");
				$comma=",";
			}
		}
		// find default Shopper Groups
		$q2 = "SELECT `shopper_group_id` FROM `#__{vm}_shopper_group` WHERE `default` = 1";
		$db_browse->query($q2);
		while ($db_browse->next_record()) {
			$sgq .= $comma.$db_browse->f("shopper_group_id");
			$comma=",";
		}
		$sgq .= "\n )) ";
		$where_clause[] = $sgq;
	}

}

//////////////////////////////////
// ASSEMBLE THE QUERY
//////////////////////////////////
$list  = "SELECT DISTINCT $fieldnames FROM ($table_names) ";
$count  = "SELECT $count_name FROM ($table_names) ";

if( $perm->is_registered_customer($auth['user_id']) ) {	
	$where_clause[] = "(`#__{vm}_product`.`product_id`=`#__{vm}_product_price`.`product_id` OR `#__{vm}_product_price`.`product_id` IS NULL) ";
	$join_array[] = 'LEFT JOIN `#__{vm}_shopper_vendor_xref` ON (`#__{vm}_shopper_vendor_xref`.`user_id` ='.$auth['user_id'].' AND `#__{vm}_shopper_vendor_xref`.`shopper_group_id`=`#__{vm}_shopper_group`.`shopper_group_id`)';
}
else {
	$where_clause[] = "((`#__{vm}_product`.`product_id`=`#__{vm}_product_price`.`product_id` AND `#__{vm}_shopper_group`.`shopper_group_id`=`#__{vm}_product_price`.`shopper_group_id`) OR `#__{vm}_product_price`.`product_id` IS NULL) ";
	$where_clause[] = '`#__{vm}_shopper_group`.`default` = 1';
}
$where_clause[] = "((`product_parent_id`='0') OR (`product_parent_id`='')) ";
if( !$perm->check("admin,storeadmin") ) {
	$where_clause[] = "`product_publish`='Y' ";
	$where_clause[] = "`category_publish`='Y' ";
	if( CHECK_STOCK && PSHOP_SHOW_OUT_OF_STOCK_PRODUCTS != "1") {
		$where_clause[] = 'product_in_stock > 0';
	}
}
$q = implode("\n", $join_array ).' WHERE '. implode("\n AND ", $where_clause );
$count .= $q;

$q .= "\n GROUP BY `#__{vm}_product`.`product_sku` ";
$q .= "\n ORDER BY $orderbyField $DescOrderBy";

// Joomla! 1.5 supports listing "All" items, which means $limit == 0
//if( vmIsJoomla(1.5) && $limit == 0 ) {
	$list .= $q;
//} else {
//	$list .= $q . " LIMIT $limitstart, " . $limit;
//}
//echo $list;
// Store current GET parameters for usage on the product details page navigation
$_SESSION['last_browse_parameters'] = array(
											'category_id' => $category_id,
											'manufacturer_id' => $manufacturer_id,
											'keyword' => $keyword,
											'keyword1' => $keyword1,
											'keyword2' => $keyword2,
											'featured' => $featured,
											'discounted' => $discounted
										);
if( !empty($product_type_id) ) {
	$_SESSION['last_browse_parameters']['product_type_id'] = $product_type_id;
}
// BACK TO shop.browse.php !
?>
