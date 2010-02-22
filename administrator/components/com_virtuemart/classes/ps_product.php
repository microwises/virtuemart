<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id$
* @package VirtueMart
* @subpackage classes
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

/**
 * The class is is used to manage product repository.
 * @package VirtueMart
 * @author pablo, jep, gday, soeren
 *
 */
class ps_product extends vmAbstractObject {
	var $_key = 'product_id';
	var $_table_name = '#__{vm}_product';

	/**
	 * Validates product fields and uploaded image files.
	 *
	 * @param array $d The input vars
	 * @return boolean True when validation successful, false when not
	 */
	function validate(&$d) {
		global $vmLogger, $database, $perm, $hVendor;
		require_once(CLASSPATH . 'imageTools.class.php' );

		$valid = true;
		$db = new ps_DB;

 		/*Only the vendor itself or the admin is allowed to change the product by Max Milbers*/
		$auth = $_SESSION['auth'];
		$user_id = $auth["user_id"];

		$hVendor_id = $hVendor->getVendorIdByUserId($user_id );
		if( !$perm->check( 'admin' )) {
			if($hVendor_id!=$d['vendor_id']){
				$vmLogger->err( JText::_('VM_PRODUCT_NOT_ALLOWED_TO_CHANGE ',false) );
//				$vmLogger->debug( 'ps_vendor_id: '.$hVendor_id );
				return false;
//				$valid = false;
			}
		}

		$q = "SELECT product_id,product_thumb_image,product_full_image FROM #__{vm}_product WHERE product_sku='";
		$q .= $d["product_sku"] . "'";
		$db->setQuery($q); $db->query();
		if ($db->next_record()&&($db->f("product_id") != $d["product_id"])) {
			$vmLogger->err( "A Product with the SKU ".$d['product_sku']." already exists." );
			$valid = false;
		}
		if( !empty( $d['product_discount_id'] )) {
			if( $d['product_discount_id'] == "override" ) {

				$d['is_percent'] = "0";

				// If discount are applied before tax then base the discount on the untaxed price
				if( PAYMENT_DISCOUNT_BEFORE == '1' ) {
					$d['amount'] = (float)$d['product_price'] - (float)$d['discounted_price_override'];
				}
				// Otherwise, base the discount on the taxed price
				else {
					$d['amount'] = (float)$d['product_price_incl_tax'] - (float)$d['discounted_price_override'];
				}

				// Set the discount start date as today
				$d['start_date'] = date( 'Y-m-d' );

				require_once( CLASSPATH. 'ps_product_discount.php' );
				$ps_product_discount = new ps_product_discount;
				$ps_product_discount->add( $d );
				$d['product_discount_id'] = $database->insertid();
				vmRequest::setVar( 'product_discount_id', $d['product_discount_id'] );
			}
		}
		if (empty($d['manufacturer_id'])) {
			$d['manufacturer_id'] = "1";
		}
		if (empty( $d["product_sku"])) {
			$vmLogger->err( JText::_('VM_PRODUCT_MISSING_SKU',false) );
			$valid = false;
		}
		if (!$d["product_name"]) {
			$vmLogger->err( JText::_('VM_PRODUCT_MISSING_NAME',false) );
			$valid = false;
		}
		if (empty($d["product_available_date"])) {
			$vmLogger->err( JText::_('VM_PRODUCT_MISSING_AVAILDATE',false) );
			$valid = false;
		}
		else {
			$day = (int) substr ( $d["product_available_date"], 8, 2);
			$month= (int) substr ( $d["product_available_date"], 5, 2);
			$year = (int) substr ( $d["product_available_date"], 0, 4);
			$d["product_available_date_timestamp"] = mktime(0,0,0,$month, $day, $year);
		}

		/** Validate Product Specific Fields **/
		if (!$d["product_parent_id"]) {
			if( empty( $d['product_categories']) || !is_array(@$d['product_categories'])) {
				$d['product_categories'] = explode('|', $d['category_ids'] );
			}
			if (sizeof(@$d["product_categories"]) < 1) {
				$vmLogger->err( JText::_('VM_PRODUCT_MISSING_CATEGORY') );
				$valid = false;
			}
		}
		/** Image Upload Validation **/

		// do we have an image URL or an image File Upload?
		if (!empty( $d['product_thumb_image_url'] )) {
			// Image URL
			if (substr( $d['product_thumb_image_url'], 0, 4) != "http") {
				$vmLogger->err( JText::_('VM_PRODUCT_IMAGEURL_MUSTBEGIN',false) );
				$valid =  false;
			}

			// if we have an uploaded image file, prepare this one for deleting.
			if( $db->f("product_thumb_image") && substr( $db->f("product_thumb_image"), 0, 4) != "http") {
				$_REQUEST["product_thumb_image_curr"] = $db->f("product_thumb_image");
				$d["product_thumb_image_action"] = "delete";
				if (!vmImageTools::validate_image($d,"product_thumb_image","product")) {
					return false;
				}
			}
			$d["product_thumb_image"] = $d['product_thumb_image_url'];
		}
		else {
			// File Upload
			if (!vmImageTools::validate_image($d,"product_thumb_image","product")) {
				$valid = false;
			}
		}

		if (!empty( $d['product_full_image_url'] )) {
			// Image URL
			if (substr( $d['product_full_image_url'], 0, 4) != "http") {
				$vmLogger->err( JText::_('VM_PRODUCT_IMAGEURL_MUSTBEGIN',false) );
				return false;
			}
			// if we have an uploaded image file, prepare this one for deleting.
			if( $db->f("product_full_image") && substr( $db->f("product_full_image"), 0, 4) != "http") {
				$_REQUEST["product_full_image_curr"] = $db->f("product_full_image");
				$d["product_full_image_action"] = "delete";
				if (!vmImageTools::validate_image($d,"product_full_image","product")) {
					return false;
				}
			}
			$d["product_full_image"] = $d['product_full_image_url'];
		}
		else {
			// File Upload
			if (!vmImageTools::validate_image($d,"product_full_image","product")) {
				$valid = false;
			}
		}

		// added for advanced attribute modification
		// strips the trailing semi-colon from an attribute
        if(isset($d["product_advanced_attribute"])) {
		    if (';' == substr($d["product_advanced_attribute"], strlen($d["product_advanced_attribute"])-1,1) ) {
			    $d["product_advanced_attribute"] =substr($d["product_advanced_attribute"], 0, strlen($d["product_advanced_attribute"])-1);
		    }
        }
		// added for custom attribute modification
		// strips the trailing semi-colon from an attribute
        if(isset($d["product_custom_attribute"])) {
		    if (';' == substr($d["product_custom_attribute"], strlen($d["product_custom_attribute"])-1,1) ) {
			    $d["product_custom_attribute"] =substr($d["product_custom_attribute"], 0, strlen($d["product_custom_attribute"])-1);
		    }
        }
		$d["clone_product"] = empty($d["clone_product"]) ? "N" : "Y";
		$d["product_publish"] = empty($d["product_publish"]) ? "N" : "Y";
		$d["product_special"] = empty($d["product_special"]) ? "N" : "Y";
        //parse quantity and child options
        $d['display_headers'] = vmGet($d,'display_headers', 'Y') =='Y' ? 'Y' : 'N';
        $d['product_list_child'] = vmGet($d,'product_list_child', 'Y') =='Y' ? 'Y' : 'N';
        $d['display_use_parent'] = vmGet($d,'display_use_parent', 'Y') =='Y' ? 'Y' : 'N';
        $d['product_list_type'] = vmGet($d,'product_list_type', 'Y') =='Y' ? 'Y' : 'N';
        $d['display_desc'] = vmGet($d,'display_desc', 'Y') =='Y' ? 'Y' : 'N';
        if (@$d['product_list'] =="Y") {
            if($d['list_style'] == "one")
                $d['product_list'] = "Y";
            else
                $d['product_list'] = "YM";
        }
        else {
            $d['product_list'] = "N";
        }

        $d['quantity_options'] = ps_product::set_quantity_options($d);
		$d['child_options'] = ps_product::set_child_options($d);

        $d['order_levels'] = vmRequest::getInt('min_order_level').",".vmRequest::getInt('max_order_level');

		return $valid;
	}

	/**
	 * Validates that a product can be deleted
	 *
	 * @param array $d The input vars
	 * @return boolean Validation sucessful?
	 */
	function validate_delete( $product_id, &$d ) {
		global $vmLogger;
		require_once(CLASSPATH . 'imageTools.class.php' );
		/* Check that ps_vendor_id and product_id match
		if (!$this->check_vendor($d)) {
		$d["error"] = "ERROR: Cannot delete product. Wrong product or vendor." ;
		return false;
		}*/
		if (empty($product_id)) {
			$vmLogger->err( JText::_('VM_PRODUCT_SPECIFY_DELETE',false) );
			return false;
		}
		/* Get the image filenames from the database */
		$db = new ps_DB;
		$q  = "SELECT product_thumb_image,product_full_image ";
		$q .= "FROM #__{vm}_product ";
		$q .= "WHERE product_id='$product_id'";
		$db->setQuery($q); $db->query();
		$db->next_record();

		/* Prepare product_thumb_image for Deleting */
		if( !stristr( $db->f("product_thumb_image"), "http") ) {
			$_REQUEST["product_thumb_image_curr"] = $db->f("product_thumb_image");
			$d["product_thumb_image_action"] = "delete";
			if (!vmImageTools::validate_image($d,"product_thumb_image","product")) {
				$vmLogger->err( JText::_('VM_PRODUCT_IMGDEL_FAILED',false) );
				return false;
			}
		}
		/* Prepare product_full_image for Deleting */
		if( !stristr( $db->f("product_full_image"), "http") ) {
			$_REQUEST["product_full_image_curr"] = $db->f("product_full_image");
			$d["product_full_image_action"] = "delete";
			if (!vmImageTools::validate_image($d,"product_full_image","product")) {
				return false;
			}
		}
		return true;

	}

	/**
	 * Function to add a new product into the product table
	 *
	 * @param array $d The input vars
	 * @return boolean True, when the product was added, false when not
	 */
	function add( &$d ) {
		global $perm, $vmLogger;
		$database = new ps_DB();

		if (!$this->validate($d)) {
			return false;
		}

		if (!vmImageTools::process_images($d)) {
			return false;
		}

		$timestamp = time();
		$db = new ps_DB;

		$vendor_id = $d['vendor_id'];

        // Insert into DB
		$fields = array ( 'vendor_id' => $vendor_id,
						'product_parent_id' => vmRequest::getInt('product_parent_id'),
						'product_sku' => vmGet($d,'product_sku'),
						'product_name' => vmGet($d,'product_name'),
						'product_desc' => vmRequest::getVar('product_desc', '', 'default', '', VMREQUEST_ALLOWHTML),
						'product_s_desc' => vmRequest::getVar('product_s_desc', '', 'default', '', VMREQUEST_ALLOWHTML),
						'intnotes' => vmRequest::getVar('intnotes', '', 'default', '', VMREQUEST_ALLOWHTML),
						'product_thumb_image' => vmGet($d,'product_thumb_image'),
						'product_full_image' => vmGet($d,'product_full_image'),
						'product_publish' => $d['product_publish'],
						'product_weight' => vmRequest::getFloat('product_weight'),
						'product_weight_uom' => vmGet($d,'product_weight_uom'),
						'product_length' => vmRequest::getFloat('product_length'),
						'product_width' => vmRequest::getFloat('product_width'),
						'product_height' => vmRequest::getFloat('product_height'),
						'product_lwh_uom' => vmGet($d,'product_lwh_uom'),
						'product_unit' => vmGet($d,'product_unit'),
						'product_packaging' => (($d["product_box"] << 16) | ($d["product_packaging"]&0xFFFF)),
						'product_url' => vmGet($d,'product_url'),
						'product_in_stock' => vmRequest::getInt('product_in_stock'),
						'low_stock_notification' => vmRequest::getInt('low_stock_notification'),
						'attribute' => ps_product_attribute::formatAttributeX(),
						'custom_attribute' => vmGet($d,'product_custom_attribute'),
						'product_available_date' => $d['product_available_date_timestamp'],
						'product_availability' => vmGet($d,'product_availability'),
						'product_special' => $d['product_special'],
						'child_options' => $d['child_options'],
						'quantity_options' => $d['quantity_options'],
						'product_discount_id' => vmRequest::getInt('product_discount_id'),
						'cdate' => $timestamp,
						'mdate' => $timestamp,
						'product_tax_id' => vmRequest::getInt('product_tax_id'),
						'child_option_ids' => vmGet($d,'included_product_id'),
						'product_order_levels' => $d['order_levels'],
						'metadesc' => vmGet($d, 'metadesc',''),
						'metakey' => vmGet($d, 'metakey',''),
						'metarobot' => vmGet($d, 'metarobot',''),
						'metaauthor' => vmGet($d, 'metaauthor',''));

		$db->buildQuery('INSERT', '#__{vm}_product', $fields );
		if( $db->query() === false ) {
			$vmLogger->err( JText::_('VM_PRODUCT_ADDING_FAILED',false) );
			return false;
		}

		$d["product_id"] = $_REQUEST['product_id'] = $db->last_insert_id();

		// If is Item, add attributes from parent //
		if ($d["product_parent_id"]) {
			$q  = "SELECT attribute_name FROM #__{vm}_product_attribute_sku ";
			$q .= "WHERE product_id='" . vmRequest::getInt('product_parent_id') . "' ";
			$q .= "ORDER BY attribute_list,attribute_name";
			$db->query($q);

			$db2 = new ps_DB;
			$i = 0;
			while($db->next_record()) {
				$i++;

				$q = "INSERT INTO #__{vm}_product_attribute (`product_id`,`attribute_name`,`attribute_value`) VALUES ";
				$q .= "('".$d["product_id"]."', '".$db->f("attribute_name", false)."', '".vmGet($d,'attribute_'.$i )."')";
				$db2->query( $q );
			}
		}
		else {
			// If is Product, Insert category ids
			if( empty( $d['product_categories']) || !is_array(@$d['product_categories'])) {
				$d['product_categories'] = explode('|', $d['category_ids'] );
			}
			foreach( $d["product_categories"] as $category_id ) {
				$db->query('SELECT MAX(`product_list`) as list_order FROM `#__{vm}_product_category_xref` WHERE `category_id`='.$category_id );
				$db->next_record();

				$q  = "INSERT INTO #__{vm}_product_category_xref ";
				$q .= "(category_id,product_id,product_list) ";
				$q .= "VALUES ('$category_id','". $d["product_id"] . "', ".intval($db->f('max') +1 ) . ")";
				$db->setQuery($q); $db->query();
			}
		}
		$q = "INSERT INTO #__{vm}_product_mf_xref VALUES (";
		$q .= "'".$d['product_id']."', '".vmRequest::getInt('manufacturer_id')."')";
		$db->setQuery($q); $db->query();

		if( !empty($d["related_products"])) {
			/* Insert Pipe separated Related Product IDs */
			$related_products = vmGet( $d, "related_products" );
			$q  = "INSERT INTO #__{vm}_product_relations ";
			$q .= "(product_id, related_products) ";
			$q .= "VALUES ('".$d["product_id"]."','".$db->getEscaped($related_products)."')";
			$db->setQuery($q); $db->query();

		}
		// ADD A PRICE, IF NOT EMPTY ADD 0
		if (!empty($d['product_price'])) {

			if(empty($d['product_currency'])) {
				$d['product_currency'] = $_SESSION['vendor_currency'];
			}

			$d["price_quantity_start"] = 0;
			$d["price_quantity_end"] = "";
			require_once ( CLASSPATH. 'ps_product_price.php');
			$my_price = new ps_product_price;
			$my_price->add($d);
		}

		if( !empty( $d['product_type_id'])) {
			require_once( CLASSPATH.'ps_product_product_type.php' );
			$ps_product_product_type = new ps_product_product_type();
			$ps_product_product_type->add( $d );

			// Product Type Parameters!
			$this->handleParameters( $d );
		}

		// CLONE PRODUCT additional code
		if( $d["clone_product"] == "Y" ) {

			// Clone Parent Product's Attributes
			$q  = "INSERT INTO #__{vm}_product_attribute_sku
              SELECT '".$d["product_id"]."', attribute_name, attribute_list
              FROM #__{vm}_product_attribute_sku WHERE product_id='" . (int)$d["old_product_id"] . "' ";
			$db->query( $q );
			if( !empty( $d["child_items"] )) {

				$database->query( "SHOW COLUMNS FROM #__{vm}_product" );
				$rows = $database->record;
				while(list(,$Field) = each( $rows) ) {
					$product_fields[$Field->Field] = $Field->Field;
				}
				// Change the Field Names
				// leave empty for auto_increment
				$product_fields["product_id"] = "''";
				// Update Product Parent ID to the new one
				$product_fields["product_parent_id"] = "'".$d["product_id"]."'";
				// Rename the SKU
				$product_fields["product_sku"] = "CONCAT(product_sku,'_".$d["product_id"]."')";

				$rows = Array();
				$database->query( "SHOW COLUMNS FROM #__{vm}_product_price" );
				$rows = $database->record;
				while(list(,$Field) = each( $rows) ) {
					$price_fields[$Field->Field] = $Field->Field;
				}

				foreach( $d["child_items"] as $child_id ) {
					$q = "INSERT INTO #__{vm}_product ";
					$q .= "SELECT ".implode(",", $product_fields )." FROM #__{vm}_product WHERE product_id='$child_id'";
					$db->query( $q );
					$new_product_id = $db->last_insert_id();

					$q = "INSERT INTO #__{vm}_product_attribute
			                  SELECT NULL, '$new_product_id', attribute_name, attribute_value
			                  FROM #__{vm}_product_attribute WHERE product_id='$child_id'";
					$db->query( $q );

					$price_fields["product_price_id"] = "''";
					$price_fields["product_id"] = "'$new_product_id'";

					$q = "INSERT INTO #__{vm}_product_price ";
					$q .= "SELECT ".implode(",", $price_fields )." FROM #__{vm}_product_price WHERE product_id='$child_id'";
					$db->query( $q );
				}
			}

			// End Cloning

		}
		if( $d['clone_product'] == 'Y') {
			$vmLogger->info( JText::_('VM_PRODUCT_CLONED',false) );
		}
		else {
			$vmLogger->info( JText::_('VM_PRODUCT_ADDED',false) );
		}
		return true;
	}

	/**
	 * Function to update product $d['product_id'] in the product table
	 *
	 * @param array $d The input vars
	 * @return boolean True, when the product was updated, false when not
	 */
	function update( &$d ) {
		global $vmLogger, $perm;
		require_once(CLASSPATH.'ps_product_attribute.php');

		if (!$this->validate($d)) {
			return false;
		}

		if (!vmImageTools::process_images($d)) {
			return false;
		}

		$timestamp = time();
		$db = new ps_DB;

		$vendor_id = $d['vendor_id'];

        // Insert into DB
		$fields = array ( 'vendor_id' => $vendor_id,
						'product_sku' => vmGet($d,'product_sku'),
						'product_name' => vmGet($d,'product_name'),
						'intnotes' => vmRequest::getVar('intnotes', '', 'default', '', VMREQUEST_ALLOWHTML),
						'product_desc' => vmRequest::getVar('product_desc', '', 'default', '', VMREQUEST_ALLOWHTML),
						'product_s_desc' => vmRequest::getVar('product_s_desc', '', 'default', '', VMREQUEST_ALLOWHTML),
						'product_thumb_image' => vmGet($d,'product_thumb_image'),
						'product_full_image' => vmGet($d,'product_full_image'),
						'product_publish' => $d['product_publish'],
						'product_weight' => vmRequest::getFloat('product_weight'),
						'product_weight_uom' => vmGet($d,'product_weight_uom'),
						'product_length' => vmRequest::getFloat('product_length'),
						'product_width' => vmRequest::getFloat('product_width'),
						'product_height' => vmRequest::getFloat('product_height'),
						'product_lwh_uom' => vmGet($d,'product_lwh_uom'),
						'product_unit' => vmGet($d,'product_unit'),
						'product_packaging' => (($d["product_box"] << 16) | ($d["product_packaging"]&0xFFFF)),
						'product_url' => vmGet($d,'product_url'),
						'product_in_stock' => vmRequest::getInt('product_in_stock'),
						'low_stock_notification' => vmRequest::getInt('low_stock_notification'),
						'attribute' => ps_product_attribute::formatAttributeX(),
						'custom_attribute' => vmGet($d,'product_custom_attribute'),
						'product_available_date' => $d['product_available_date_timestamp'],
						'product_availability' => vmGet($d,'product_availability'),
						'product_special' => $d['product_special'],
						'child_options' => $d['child_options'],
						'quantity_options' => $d['quantity_options'],
						'product_discount_id' => vmRequest::getInt('product_discount_id'),
						'mdate' => $timestamp,
						'product_tax_id' => vmRequest::getInt('product_tax_id'),
						'child_option_ids' => vmGet($d,'included_product_id'),
						'product_order_levels' => $d['order_levels'],
						'metadesc' => vmGet($d, 'metadesc',''),
						'metakey' => vmGet($d, 'metakey',''),
						'metarobot' => vmGet($d, 'metarobot',''),
						'metaauthor' => vmGet($d, 'metaauthor',''));

		//this line didnt worked correct. Sometimes the category was changed but not the description, updating vendor didnt worked at all by Max Milbers
//		$db->buildQuery( 'UPDATE', '#__{vm}_product', $fields,  'WHERE product_id='. (int)$d["product_id"] . ' AND vendor_id=' . (int)$d['vendor_id'] );
$db->buildQuery( 'UPDATE', '#__{vm}_product', $fields,  "WHERE product_id='". (int)$d["product_id"] ."'" );
		$db->query();

		/* notify the shoppers that the product is here */
		/* see zw_waiting_list */
		if ($d["product_in_stock"] > "0" && @$d['notify_users'] == '1' && $d['product_in_stock_old'] == '0') {
			require_once( CLASSPATH . 'zw_waiting_list.php');
			$zw_waiting_list = new zw_waiting_list;
			$zw_waiting_list->notify_list($d["product_id"]);
		}

		$q = "UPDATE #__{vm}_product_mf_xref SET ";
		$q .= 'manufacturer_id='.vmRequest::getInt('manufacturer_id').' ';
		$q .= 'WHERE product_id = '.$d['product_id'];
		$db->query($q);


		/* If is Item, update attributes */
		if( !empty($d["product_parent_id"])) {
			$q  = "SELECT attribute_name FROM #__{vm}_product_attribute_sku ";
			$q .= 'WHERE product_id=' .(int)$d["product_parent_id"] . ' ';
			$q .= "ORDER BY attribute_list,attribute_name";
			$db->query($q);

			$db2 = new ps_DB;
			$i = 0;
			while($db->next_record()) {
				$i++;
				$q2  = "UPDATE #__{vm}_product_attribute SET ";
				$q2 .= "attribute_value='" .vmGet($d,'attribute_'.$i ) . "' ";
				$q2 .= "WHERE product_id = '" . $d["product_id"] . "' ";
				$q2 .= "AND attribute_name = '" . $db->f("attribute_name", false ) . "' ";
				$db2->setQuery($q2); $db2->query();
			}
			/* If it is a Product, update Category */
		}
		else {
			// Handle category selection: product_category_xref
			$q  = "SELECT `category_id` FROM `#__{vm}_product_category_xref` ";
			$q .= "WHERE `product_id` = '" . $d["product_id"] . "' ";
			$db->setQuery($q);
			$db->query();
			$old_categories = array();
			while( $db->next_record()) {
				$old_categories[$db->f('category_id')] = $db->f('category_id');
			}
			// NOW Insert new categories
			$new_categories = array();

			if( empty( $d['product_categories']) || !is_array(@$d['product_categories'])) {
				$d['product_categories'] = explode('|', $d['category_ids'] );
			}

			foreach( $d["product_categories"] as $category_id ) {
				if( !in_array( $category_id, $old_categories ) ) {
					$db->query('SELECT MAX(`product_list`) as list_order FROM `#__{vm}_product_category_xref` WHERE `category_id`='.(int)$category_id );
					$db->next_record();

					$q  = "INSERT INTO #__{vm}_product_category_xref ";
					$q .= "(category_id,product_id,product_list) ";
					$q .= "VALUES ('".(int)$category_id."','". $d["product_id"] . "', ".intval($db->f('max') +1 ) . ")";
					$db->setQuery($q); $db->query();
					$new_categories[$category_id] = $category_id;
				}
				else {
					unset( $old_categories[$category_id]);
				}
			}
			// The rest of the old categories can be deleted
			foreach( $old_categories as $category_id ) {
				$q  = "DELETE FROM `#__{vm}_product_category_xref` ";
				$q .= "WHERE `product_id` = '" . $d["product_id"] . "' ";
				$q .= "AND `category_id` = '" . $category_id . "' ";
				$db->query($q);
			}
		}

		if( !empty($d["related_products"])) {
			/* Insert Pipe separated Related Product IDs */
			$related_products = vmGet( $d, "related_products" );
			$q  = "REPLACE INTO #__{vm}_product_relations (product_id, related_products)";
			$q .= " VALUES( '".$d["product_id"]."', '$related_products') ";
			$db->query($q);

		}
		else{
			$q  = "DELETE FROM #__{vm}_product_relations WHERE product_id='".$d["product_id"]."'";
			$db->query($q);
		}

		// UPDATE THE PRICE, IF EMPTY ADD 0
		if(empty($d['product_currency'])) {
			$d['product_currency'] = $_SESSION['vendor_currency'];
		}

		// look if we have a price for this product
		$q = "SELECT product_price_id, price_quantity_start, price_quantity_end FROM #__{vm}_product_price ";
		$q .= "WHERE shopper_group_id=" . vmRequest::getInt('shopper_group_id');
		$q .= ' AND product_id = ' . $d["product_id"];
		$db->query($q);


		if ($db->next_record()) {

			$d["product_price_id"] = $db->f("product_price_id");
			require_once ( CLASSPATH. 'ps_product_price.php');
			$my_price = new ps_product_price;

			if (@$d['product_price'] != '') {
				// update prices
				$d["price_quantity_start"] = $db->f("price_quantity_start");
				$d["price_quantity_end"] = $db->f("price_quantity_end");

				$my_price->update($d);
			}
			else {
				// delete the price
				$my_price->delete( $d );
			}
		}
		else {
			if ( $d['product_price'] != '' ) {
				// add the price
				$d["price_quantity_start"] = 0;
				$d["price_quantity_end"] = "";
				require_once ( CLASSPATH. 'ps_product_price.php');
				$my_price = new ps_product_price;
				$my_price->add($d);
			}
		}

		// Product Type Parameters!
		$this->handleParameters( $d );

		$vmLogger->info( JText::_('VM_PRODUCT_UPDATED',false) );
		return true;
	}


	/**
	 * Handles adding or updating parameter values for a product an its product types
	 * @since VirtueMart 1.1.0
	 * @param array $d
	 */
	function handleParameters( &$d ) {
		global $db;

		$product_id= intval( $d["product_id"] );

		$q  = "SELECT `product_type_id` FROM `#__{vm}_product_product_type_xref` WHERE ";
		$q .= "`product_id`=$product_id";
		$db->query($q);

		$dbpt = new ps_DB;
		$dbp = new ps_DB;

		// For every Product Type
		while ($db->next_record()) {
			$product_type_id = $db->f("product_type_id");

			$q  = "SELECT * FROM #__{vm}_product_type_parameter WHERE ";
			$q .= "product_type_id='$product_type_id' ";
			$q .= "ORDER BY parameter_list_order";
			$dbpt->query($q);

			$q  = "SELECT COUNT(`product_id`) as num_rows FROM `#__{vm}_product_type_$product_type_id` WHERE ";
			$q .= "product_id='$product_id'";
			$dbp->query($q); $dbp->next_record();

			if ( $dbp->f('num_rows') == 0 ) {  // Add record if not exist (Items)
				$q  = "INSERT INTO #__{vm}_product_type_$product_type_id (product_id) ";
				$q .= "VALUES ('$product_id')";
				$dbp->query($q);
			}

			// Update record
			$q  = "UPDATE #__{vm}_product_type_$product_type_id SET ";
			$q .= "product_id='$product_id'";
			while ($dbpt->next_record()) {
				if ($dbpt->f("parameter_type")!="B") { // if it is not breaker
					$value=$d["product_type_".$product_type_id."_".$dbpt->f("parameter_name")];
					if ($dbpt->f("parameter_type")=="V" && is_array($value)) {
						$value = join(';',$value);
					}
					if ($value=="") {
						$value='NULL';
					}
					else {
						$value="'".$dbpt->getEscaped($value)."'";
					}
					$q .= ',`'.$dbpt->f('parameter_name', false).'`='.$value;
				}
			}
			$q .= ' WHERE product_id = '.$d['product_id'];
			$dbp->query($q);
		}

	}

	/**
	 * Function to delete product(s) $d['product_id'] from the product table
	 *
	 * @param array $d The input vars
	 * @return boolean True, when the product was deleted, false when not
	 */
	function delete(&$d) {

		$product_id = $d["product_id"];

		if( is_array( $product_id)) {
			foreach( $product_id as $product) {
				if( !$this->delete_product( $product, $d ))
				return false;
			}
			return true;
		}
		else {
			return $this->delete_product( $product_id, $d );
		}
	}
	/**
         * Move a product from one category to another
         *
         * @param array $d
         * @return boolean True on sucess, false on failure
         */
	function move( &$d ) {
		global $db, $vmLogger;
		if( !is_array( $d['product_id'])) {
			$vmLogger->err( JText::_('VM_PRODUCT_MOVE_NOTFOUND',false));
			return false;
		}
		if( empty( $d['category_id'])) {
			$vmLogger->err( JText::_('VM_PRODUCT_MUSTSELECT_ONE_CAT',false));
			return false;
		}
		// Loop though each product
		foreach( $d['product_id'] as $product_id ) {
			// check if the product is already assigned to the category it should be moved to
			$db->query( 'SELECT product_id FROM `#__{vm}_product_category_xref` WHERE `product_id`='.intval($product_id).' AND `category_id`='.intval($d['category_id']));
			if( !$db->next_record()) {
				// If the product is not yet in this category, move it!
				$db->query( 'SELECT MAX(`product_list`) as max FROM `#__{vm}_product_category_xref` WHERE `category_id`='.intval($d['category_id']));
				$db->next_record();
				$db->query('INSERT INTO `#__{vm}_product_category_xref` VALUES ('.intval($d['category_id']).', '.intval($product_id).', '.intval( $db->f('max') + 1) .') ');
			}
			$db->query('DELETE FROM `#__{vm}_product_category_xref` WHERE `product_id`='.intval($product_id).' AND `category_id`='.intval($d['old_category_id']));
		}
		return true;
	}

	/**
	 * The function that holds the code for deleting
	 * one product from the database and all related tables
	 * plus deleting files related to the product
	 *
	 * @param int $product_id
	 * @param array $d The input vars
	 * @return boolean True on success, false on error
	 */
	function delete_product( $product_id, &$d ) {
		global $vmLogger;
		$db = new ps_DB;
		if (!$this->validate_delete($product_id, $d)) {
			return false;
		}
		/* If is Product */
		if ($this->is_product($product_id)) {
			/* Delete all items first */
			$q  = "SELECT product_id FROM #__{vm}_product WHERE product_parent_id='$product_id'";
			$db->setQuery($q); $db->query();
			while($db->next_record()) {
				$d2["product_id"] = $db->f("product_id");
				if (!$this->delete($d2)) {
					return false;
				}
			}

			/* Delete attributes */
			$q  = "DELETE FROM #__{vm}_product_attribute_sku WHERE product_id='$product_id' ";
			$db->setQuery($q); $db->query();

			/* Delete categories xref */
			$q  = "DELETE FROM #__{vm}_product_category_xref WHERE product_id = '$product_id' ";
			$db->setQuery($q); $db->query();
		}
		/* If is Item */
		else {
			/* Delete attribute values */
			$q  = "DELETE FROM #__{vm}_product_attribute WHERE product_id='$product_id'";
			$db->setQuery($q); $db->query();
		}
		/* For both Product and Item */

		/* Delete product - manufacturer xref */
		$q = "DELETE FROM #__{vm}_product_mf_xref WHERE product_id='$product_id'";
		$db->setQuery($q); $db->query();

		/* Delete Product - ProductType Relations */
		$q  = "DELETE FROM `#__{vm}_product_product_type_xref` WHERE `product_id`=$product_id";
		$db->setQuery($q); $db->query();

		/* Delete product votes */
		$q  = "DELETE FROM #__{vm}_product_votes WHERE product_id='$product_id'";
		$db->setQuery($q); $db->query();

		/* Delete product reviews */
		$q = "DELETE FROM #__{vm}_product_reviews WHERE product_id='$product_id'";
		$db->setQuery($q); $db->query();

		/* Delete Image files */
		if (!vmImageTools::process_images($d)) {
			return false;
		}
		/* Delete other Files and Images files */
		require_once(  CLASSPATH.'ps_product_files.php' );
		$ps_product_files = new ps_product_files();

		$db->query( "SELECT file_id FROM #__{vm}_product_files WHERE file_product_id='$product_id'" );
		while($db->next_record()) {
			$d["file_id"] = $db->f("file_id");
			$ps_product_files->delete( $d );
		}

		/* Delete Product Relations */
		$q  = "DELETE FROM #__{vm}_product_relations WHERE product_id = '$product_id'";
		$db->setQuery($q); $db->query();

		/* Delete Prices */
		$q  = "DELETE FROM #__{vm}_product_price WHERE product_id = '$product_id'";
		$db->setQuery($q); $db->query();

		/* Delete entry FROM #__{vm}_product table */
		$q  = "DELETE FROM #__{vm}_product WHERE product_id = '$product_id'";
		$db->setQuery($q); $db->query();

		/* If only deleting an item, go to the parent product page after
		** the deletion. This had to be done here because the product id
		** of the item to be deleted had to be passed as product_id */
		if (!empty($d["product_parent_id"])) {
			$d["product_id"] = $d["product_parent_id"];
			$d["product_parent_id"] = "";
		}
		$vmLogger->info( str_replace('{product_id}',$product_id,JText::_('VM_PRODUCT_DELETED',false)) );
		return true;
	}

	/**
	 * Function to check if the vendor_id of the product
	 * $d['product_id'] matches the vendor_id associated with the
	 * user that calls this function
	 *
	 * @param array $d
	 * @return boolean True, when vendor_id matches, false when not
	 */
	function check_vendor($d) {

		$hVendor_id = $_SESSION["ps_vendor_id"];

		$db = new ps_DB;
		$q  = "SELECT vendor_id  FROM #__{vm}_product ";
		$q .= "WHERE vendor_id = '$hVendor_id' ";
		$q .= "AND product_id = '" . $d["product_id"] . "' ";
		$db->query($q);
		if ($db->next_record()) {
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Function to create a ps_DB object holding the data of product $d['product_id']
	 * from the table #__{vm}_product
	 *
	 * @param int $product_id
	 * @return ps_DB DB object holding all data for product $product_id
	 */
	function sql($product_id) {
		$db = new ps_DB;
		if( !empty( $product_id )) {
			$q  = 'SELECT * FROM #__{vm}_product WHERE product_id=' . (int)$product_id;
			$db->setQuery($q); $db->query();
		}
		return $db;
	}

	/**
	 * Function to create a db object holding the data of all child items of
	 * product $product_id
	 *
	 * @param int $product_id
	 * @return ps_DB object that holds all items of product $product_id
	 */
	function items_sql($product_id) {
		$db = new ps_DB;
		if( !empty($product_id) ) {
			$q  = "SELECT * FROM #__{vm}_product ";
			$q .= "WHERE product_parent_id=".(int)$product_id.' ';
			$q .= "ORDER BY product_name";

			$db->setQuery($q); $db->query();
		}

		return $db;
	}

	/**
	 * Function to check whether a product is a parent product or not
	 * If is is a child product, it has a non-empty value for "product_parent_id"
	 *
	 * @param int $product_id
	 * @return boolean True when the product is a parent product, false when product is a child item
	 */
	function is_product($product_id) {
		$product_parent_id = ps_product::get_field($product_id, 'product_parent_id');
		return $product_parent_id == 0;
	}
	/**
	 * Function to check whether a product is published
	 *
	 *
	 * @param int $product_id
	 * @return boolean True when the product is a parent product, false when product is a child item
	 */
	function is_published($product_id, $check_stock=false) {
		if( CHECK_STOCK != '1') $check_stock=false;
		return ps_product::get_field($product_id, 'product_publish') == 'Y';

	}
	/**
	 * Checks if a product is a downloadable product
	 *
	 * @param int $product_id
	 * @return boolean
	 */
	function is_downloadable($product_id) {
		if( empty( $GLOBALS['product_info'][$product_id]['is_downloadable'] )) {
		    $db_check = new ps_DB;
	        $q_dl = "SELECT attribute_name,attribute_value
	        				FROM #__{vm}_product_attribute WHERE
							product_id=".(int)$product_id." AND attribute_name='download'";
			$db_check->query($q_dl);
			$db_check->next_record();
			if( $db_check->num_rows() > 0 ) {
				$GLOBALS['product_info'][$product_id]['is_downloadable'] = 'Y';
			} else {
				$GLOBALS['product_info'][$product_id]['is_downloadable'] = 'N';
			}
		}
		return $GLOBALS['product_info'][$product_id]['is_downloadable'] == 'Y';
	}
	/**
	 * Function to create a DB object that holds all information
	 * from the attribute tables about item $item_id AND/OR product $product_id
	 *
	 * @param int $item_id The product_id of the item
	 * @param int $product_id The product_id of the parent product
	 * @param string $attribute_name The name of the attribute to filter
	 * @return ps_DB The db object...
	 */
	function attribute_sql($item_id="",$product_id="",$attribute_name="") {
		$db = new ps_DB;
		if ($item_id and $product_id) {
			$q  = "SELECT * FROM #__{vm}_product_attribute,#__{vm}_product_attribute_sku ";
			$q .= "WHERE #__{vm}_product_attribute.product_id = '$item_id' ";
			$q .= "AND #__{vm}_product_attribute_sku.product_id ='$product_id' ";
			if ($attribute_name) {
				$q .= "AND #__{vm}_product_attribute.attribute_name = $attribute_name ";
			}
			$q .= "AND #__{vm}_product_attribute.attribute_name = ";
			$q .=     "#__{vm}_product_attribute_sku.attribute_name ";
			$q .= "ORDER BY attribute_list,#__{vm}_product_attribute.attribute_name";
		} elseif ($item_id) {
			$q  = "SELECT * FROM #__{vm}_product_attribute ";
			$q .= "WHERE product_id=$item_id ";
			if ($attribute_name) {
				$q .= "AND attribute_name = '$attribute_name' ";
			}
		} elseif ($product_id) {
			$q  = "SELECT * FROM #__{vm}_product_attribute_sku ";
			$q .= "WHERE product_id =".(int)$product_id.' ';
			if ($attribute_name) {
				$q .= "AND #__{vm}_product_attribute.attribute_name = $attribute_name ";
			}
			$q .= "ORDER BY attribute_list,attribute_name";
		} else {
			/* Error: no arguments were provided. */
			return 0;
		}

		$db->setQuery($q); $db->query();

		return $db;
	}

	/**
	 * Function to return the product ids of all child items of product $pid
	 *
	 * @param int $pid The ID of the parent product
	 * @return array $list
	 */
	function get_child_product_ids($pid) {
		$db = new ps_DB;
		$q  = "SELECT product_id FROM #__{vm}_product ";
		$q .= "WHERE product_parent_id='$pid' ";

		$db->setQuery($q); $db->query();

		$i = 0;
		$list = Array();
		while($db->next_record()) {
			$list[$i] = $db->f("product_id");
			$i++;
		}
		return $list;
	}

	/**
	 * Function to quickly check whether a product has child products or not
	 *
	 * @param int $pid The id of the product to check
	 * @return boolean True when the product has childs, false when not
	 */
	function parent_has_children($pid) {
		$db = new ps_DB;
		if( empty($GLOBALS['product_info'][$pid]["parent_has_children"] )) {
			$q  = "SELECT COUNT(product_id) as num_rows FROM #__{vm}_product WHERE product_parent_id='$pid' ";
			$db->query($q);
			$db->next_record();
			if( $db->f('num_rows') > 0 ) {
				$GLOBALS['product_info'][$pid]["parent_has_children"] = True;
			}
			else {
				$GLOBALS['product_info'][$pid]["parent_has_children"] = False;
			}
		}
		return $GLOBALS['product_info'][$pid]["parent_has_children"];
	}

	/**
	 * Function to quickly check whether a product has attributes or not
	 *
	 * @param int $pid The id of the product to check
	 * @return boolean True when the product has attributes, false when not
	 */
	function product_has_attributes($pid, $checkSimpleAttributes=false ) {
		if( is_array($pid) || empty($pid)) {
			return false;
		}
		$pid = intval( $pid );
		$db = new ps_DB;
		if( empty($GLOBALS['product_info'][$pid]["product_has_attributes"] )) {
			$db->query( "SELECT `product_id` FROM `#__{vm}_product_attribute_sku` WHERE `product_id`=$pid");
			if ($db->next_record()) {
				$GLOBALS['product_info'][$pid]["product_has_attributes"] = True;
			}
			elseif( $checkSimpleAttributes ) {
				$db->query( "SELECT `attribute`,`custom_attribute` FROM `#__{vm}_product` WHERE `product_id`=$pid");
				$db->next_record();
				if( $db->f('attribute') || $db->f('custom_attribute')) {
					$GLOBALS['product_info'][$pid]["product_has_attributes"] = True;
				}
				else {
					$GLOBALS['product_info'][$pid]["product_has_attributes"] = False;
				}
			}
			else {
				$GLOBALS['product_info'][$pid]["product_has_attributes"] = False;
			}
		}
		return $GLOBALS['product_info'][$pid]["product_has_attributes"];
	}

	/**
	 * Get the value of the field $field_name for product $product_id from the product table
	 *
	 * @param int $product_id
	 * @param string $field_name
	 * @return string The value of the field $field_name for that product
	 */
	function get_field( $product_id, $field_name, $force = false ) {
		if( $product_id == 0 ) return '';
		$db = new ps_DB;

		if( !isset($GLOBALS['product_info'][$product_id][$field_name] ) || $force ) {
			$q = 'SELECT product_id, `#__{vm}_product`.* FROM `#__{vm}_product` WHERE `product_id`='.(int)$product_id;
			$db->query($q);
			if ($db->next_record()) {
				$values = get_object_vars( $db->getCurrentRow() );

				foreach( $values as $key => $value ) {
					$GLOBALS['product_info'][$product_id][$key] = $value;
				}
				if( !isset( $GLOBALS['product_info'][$product_id][$field_name] ) && !is_null($GLOBALS['product_info'][$product_id][$field_name])) {
					$GLOBALS['vmLogger']->debug( 'The Field '.$field_name. ' does not exist in the product table!');
					$GLOBALS['product_info'][$product_id][$field_name] = true;
				}
			}
			else {
				$GLOBALS['product_info'][$product_id][$field_name] = false;
			}
		}
		return $GLOBALS['product_info'][$product_id][$field_name];
	}

	/**
	 * Sets a global value for a fieldname for a specific product
	 * Is to be used by other scripts to populate a field value for a prodct
	 * that was already fetched from the database - so it doesn't need to e fetched again
	 * Can be also used to override a value
	 *
	 * @param int $product_id
	 * @param string $field_name
	 * @param mixed $value
	 */
	function set_field( $product_id, $field_name, $value ) {

		$GLOBALS['product_info'][$product_id][$field_name] = $value;

	}

	/**
	 * This is a very time consuming function.
	 * It fetches the category flypage for a specific product id
	 *
	 * @param int $product_id
	 * @return string The flypage value for that product
	 */
	function get_flypage($product_id) {

		if( empty( $_SESSION['product_sess'][$product_id]['flypage'] )) {
			$db = new ps_DB;
			$productParentId = (int)$product_id;
			do {
				$q = "SELECT
                                `#__{vm}_product`.`product_parent_id` AS product_parent_id,
                                `#__{vm}_category`.`category_flypage`
                        FROM
                                `#__{vm}_product`

                        LEFT JOIN `#__{vm}_product_category_xref` ON `#__{vm}_product_category_xref`.`product_id` = `#__{vm}_product`.`product_id`
                        LEFT JOIN `#__{vm}_category` ON `#__{vm}_product_category_xref`.`category_id` = `#__{vm}_category`.`category_id`

                        WHERE `#__{vm}_product`.`product_id`='$productParentId'
                        ";
				$productParentId = $db->f("product_parent_id");
				$db->query($q);
				$db->next_record();
			}
			while( $db->f("product_parent_id") && !$db->f("category_flypage"));

			if ($db->f("category_flypage")) {
				$_SESSION['product_sess'][$product_id]['flypage'] = $db->f("category_flypage");
			} else {
				$_SESSION['product_sess'][$product_id]['flypage'] = FLYPAGE;
			}
		}
		return $_SESSION['product_sess'][$product_id]['flypage'];
	}

	/**
	 * Function to get the name of the vendor the product is associated with
	 *
	 * @param int $product_id
	 * @return string The name of the vendor
	 */
	function get_vendorname($product_id) {
		$db = new ps_DB;

		$q = "SELECT #__{vm}_vendor.vendor_name FROM #__{vm}_product, #__{vm}_vendor ";
		$q .= "WHERE #__{vm}_product.product_id='$product_id' ";
		$q .= "AND #__{vm}_vendor.vendor_id=#__{vm}_product.vendor_id";

		$db->query($q);
		$db->next_record();
		if ($db->f("vendor_name")) {
			return $db->f("vendor_name");
		}
		else {
			return "";
		}
	}

	/**
	 * Function to get the vendor_id of a product
	 * @author pablo
	 * @param int $product_id
	 * @return int The vendor id
	 */
	function get_vendor_id_ofproduct($product_id) {
		$db = new ps_DB;
		if( empty( $_SESSION['product_sess'][$product_id]['vendor_id'] )) {
			$q = "SELECT vendor_id FROM #__{vm}_product ";
			$q .= "WHERE product_id='$product_id' ";

			$db->query($q);
			$db->next_record();
			if ($db->f("vendor_id")) {
				$_SESSION['product_sess'][$product_id]['vendor_id'] = $db->f("vendor_id");
			}
			else {
				$_SESSION['product_sess'][$product_id]['vendor_id'] = "";
			}
		}
		return $_SESSION['product_sess'][$product_id]['vendor_id'];
	}

	/**
	 * Function to get the manufacturer id the product $product_id is assigned to
	 * @author soeren
	 * @param int $product_id
	 * @return int The manufacturer id
	 */
	function get_manufacturer_id($product_id) {
		$db = new ps_DB;

		$q = "SELECT manufacturer_id FROM #__{vm}_product_mf_xref ";
		$q .= "WHERE product_id='$product_id' ";

		$db->query($q);
		$db->next_record();
		if ($db->f("manufacturer_id")) {
			return $db->f("manufacturer_id");
		}
		else {
			return false;
		}
	}

	/**
	 * Functon to get the name of the manufacturer this product is assigned to
	 *
	 * @param int $product_id
	 * @return string the manufacturer name
	 */
	function get_mf_name($product_id) {
		$db = new ps_DB;

		$q = "SELECT mf_name,#__{vm}_manufacturer.manufacturer_id FROM #__{vm}_product_mf_xref,#__{vm}_manufacturer ";
		$q .= "WHERE product_id='$product_id' ";
		$q .= "AND #__{vm}_manufacturer.manufacturer_id=#__{vm}_product_mf_xref.manufacturer_id";

		$db->query($q);
		$db->next_record();
		if ($db->f("mf_name")) {
			return $db->f("mf_name");
		}
		else {
			return "";
		}
	}
	/**
	 * This function retrieves the "neighbor" products of a product specified by $product_id
	 * Neighbors are the previous and next product in the current list
	 *
	 * @param int $product_id
	 * @return array
	 */
	function get_neighbor_products( $product_id ) {
		global $perm, $orderby, $my, $auth, $keyword, $DescOrderBy, $limit, $limitstart, $search_limiter, $search_op,
			$category_id, $manufacturer_id, $vm_mainframe, $vmInputFilter, $product_type_id, $keyword1, $keyword2;
		$limit = 2000;
        $limitstart = 0;
		if( !empty( $_SESSION['last_browse_parameters'])) {
			foreach( $_SESSION['last_browse_parameters'] as $paramName => $paramValue ) {
				$$paramName = $paramValue;
			}
		}
		$db = new ps_DB();
		$db_browse = new ps_DB();
		include( PAGEPATH . 'shop.browse_queries.php' );

		$db->query( $list );

		$neighbors = array('previous'=>'',
							'next'=>'');

		while( $db->next_record() ) {
			if( $db->f( 'product_id' ) == $product_id ) {
				$previous_row = $db->previousRow();
				$next_row = $db->nextRow();

				if( !empty( $previous_row->product_id )) {
					$neighbors['previous']['product_id'] = $previous_row->product_id;
					$neighbors['previous']['product_name'] = $previous_row->product_name;
				}
				if( !empty( $next_row->product_id )) {
					$neighbors['next']['product_id'] = $next_row->product_id;
					$neighbors['next']['product_name'] = $next_row->product_name;
				}
			}
		}
		return $neighbors;
	}
	

// bass28 6/28/09 - Moved following 2 functions to image helper	
	/**
	 * Prints the img tag for the given product image
	 *
	 * @param string $image The name of the imahe OR the full URL to the image
	 * @param string $args Additional attributes for the img tag
	 * @param int $resize
	 * (1 = resize the image by using height and width attributes,
	 * 0 = do not resize the image)
	 * @param string $path_appendix The path to be appended to IMAGEURL / IMAGEPATH
	 */
	//function show_image($image, $args="", $resize=1, $path_appendix="product") {
	//	echo $this->image_tag($image, $args, $resize, $path_appendix);
//	}

	/**
	 * Returns the img tag for the given product image
	 *
	 * @param string $image The name of the imahe OR the full URL to the image
	 * @param string $args Additional attributes for the img tag
	 * @param int $resize
	 * (1 = resize the image by using height and width attributes,
	 * 0 = do not resize the image)
	 * @param string $path_appendix The path to be appended to IMAGEURL / IMAGEPATH
	 * @return The HTML code of the img tag
	 */
	//function image_tag($image, $args="", $resize=1, $path_appendix='product', $thumb_width=0, $thumb_height=0, $overide = false ) {
	/*	bass28 6/24/09 - Changed to use new ImageHelper class.
	
		global $mosConfig_live_site, $mosConfig_absolute_path;
		require_once( CLASSPATH . 'imageTools.class.php');

		$border="";
		if( strpos( $args, "border=" )===false ) {
			$border = 'border="0"';
		}
		$height = $width = '';

		if ($image != "") {
			// URL
			if( substr( $image, 0, 4) == "http" ) {
				$url = $image;
			}
			// local image file
			else {
				If($overide) {
					$new_img_width = $thumb_width;
					$new_img_height = $thumb_height;
				}
				else
				{
					$new_img_width = PSHOP_IMG_WIDTH;
					$new_img_height = PSHOP_IMG_HEIGHT;
				}
				if(PSHOP_IMG_RESIZE_ENABLE == '1' || $resize==1) {
					$url = $mosConfig_live_site."/components/com_virtuemart/show_image_in_imgtag.php?filename=".urlencode($image)."&amp;newxsize=".$new_img_width."&amp;newysize=".$new_img_height."&amp;fileout=";
					if( !strpos( $args, "height=" )) {
						$arr = @getimagesize( vmImageTools::getresizedfilename( $image, $path_appendix, '', $thumb_width, $thumb_height ) );
						$width = $arr[0]; $height = $arr[1];
					}
				}
				else {
					$url = IMAGEURL.$path_appendix.'/'.$image;
					if( file_exists($image)) {
						$url = str_replace( $mosConfig_absolute_path, $mosConfig_live_site, $image );
					} elseif( file_exists($mosConfig_absolute_path.'/'.$image)) {
						$url = $mosConfig_live_site.'/'.$image;
					}

					if( !strpos( $args, "height=" ) ) {
						$f = str_replace( IMAGEURL, IMAGEPATH, $url );
						  if ( file_exists($f) ) {
						    $arr = getimagesize( $f );
						    $width = $arr[0]; $height = $arr[1];
						  } else {
						    $width = PSHOP_IMG_WIDTH; $height = PSHOP_IMG_HEIGHT;
						  }

					}
					if( $resize ) {
						if( $height < $width ) {
							$width = round($width / ($height / PSHOP_IMG_HEIGHT));
							$height = PSHOP_IMG_HEIGHT;
						} else {
							$height = round($height / ($width / PSHOP_IMG_WIDTH ));
							$width = PSHOP_IMG_WIDTH;
						}
					}
				}
			}
		}
		else {
			$url = VM_THEMEURL.'images/'.NO_IMAGE;
		}
*/
	//	return vmCommonHTML::imageTag( $url, '', '', $height, $width, '', '', $args.' '.$border );
	//require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'image.php');
	//return ImageHelper::showImageInImgTag($image, $path_appendix, $args="", $resize, $thumb_width, $thumb_height, $overide);

	//}



	/**
	 * Get the tax rate...
	 * @author soeren
	 * @return int The tax rate found
	 */
	function get_taxrate() {
		global $page, $hVendor;
		$hVendor_id = $_SESSION["ps_vendor_id"];
		$auth = $_SESSION['auth'];

		if( !defined('_VM_IS_BACKEND' ) || $page == 'product.product_list') {

			$db = new ps_DB;

			if ($auth["show_price_including_tax"] == 1) {

				require_once( CLASSPATH . 'ps_checkout.php' );
				if (! ps_checkout::tax_based_on_vendor_address ()) {
					if( $auth["user_id"] > 0 ) {

						$q = "SELECT state, country FROM #__{vm}_user_info WHERE user_id='". $auth["user_id"] . "'";
						$db->query($q);

						$db->next_record();
						$state = $db->f("state");
						$country = $db->f("country");

						$q = "SELECT tax_rate FROM #__{vm}_tax_rate WHERE tax_country='$country' ";
						if( !empty($state)) {
							$q .= "AND tax_state='$state'";
						}
						$db->query($q);
						if ($db->next_record()) {
							$_SESSION['taxrate'][$hVendor_id] = $db->f("tax_rate");
						}
						else {
							$_SESSION['taxrate'][$hVendor_id] = 0;
						}
					}
					else {
						$_SESSION['taxrate'][$hVendor_id] = 0;
					}

				}
				else {
					if( empty( $_SESSION['taxrate'][$hVendor_id] )) {

						//adjusted by Max Milbers
						$vendorid= 1;
						$user_id = $hVendor->getUserIdByVendorId($vendorid);
						$q = "SELECT state, country FROM #__{vm}_user_info WHERE user_id='". $user_id . "'";
						$db->query($q);
						$db->next_record();
						$country = $db->f("country");
						$state = $db->f("state");

						// let's get the store's tax rate
						$q = "SELECT `tax_rate` FROM #__{vm}_tax_rate ";
						$q .= "WHERE vendor_id=1 ";
						if( !empty($state)) {
							$q .= "AND tax_state='$state'";
						}else{
							$q .= "AND tax_country='$country'";
						}
						$q .= "ORDER BY `tax_rate` DESC";


//						// let's get the store's tax rate
//						$q = "SELECT `tax_rate` FROM #__{vm}_vendor, #__{vm}_tax_rate ";
//						$q .= "WHERE tax_country=vendor_country AND #__{vm}_vendor.vendor_id=1 ";
//						// !! Important !! take the highest available tax rate for the store's country
//						$q .= "ORDER BY `tax_rate` DESC";
						$db->query($q);
						if ($db->next_record()) {
							$_SESSION['taxrate'][$hVendor_id] = $db->f("tax_rate");
						}
						else {
							$_SESSION['taxrate'][$hVendor_id] = 0;
						}
					}
					return $_SESSION['taxrate'][$hVendor_id];
				}

			}
			else {
				$_SESSION['taxrate'][$hVendor_id] = 0;
			}

			return $_SESSION['taxrate'][$hVendor_id];
		}
		else {
			return 0;
		}
	}

	/**
	 * Function to get the tax rate of product $product_id
	 * If not found, it uses get_taxrate()
	 *
	 * @param int $product_id
	 * @param int $weight_subtotal (tax virtual/zero-weight items?)
	 * @return int The tax rate for the product
	 */
	function get_product_taxrate( $product_id, $weight_subtotal=0 ) {

		require_once( CLASSPATH . 'ps_checkout.php' );

		if (($weight_subtotal != 0 or TAX_VIRTUAL=='1') && !ps_checkout::tax_based_on_vendor_address() ) {
			$_SESSION['product_sess'][$product_id]['tax_rate'] = $this->get_taxrate();
			return $_SESSION['product_sess'][$product_id]['tax_rate'];
		}
		elseif( ($weight_subtotal == 0 or TAX_VIRTUAL != '1' ) && !ps_checkout::tax_based_on_vendor_address() ) {
			$_SESSION['product_sess'][$product_id]['tax_rate'] = 0;
			return $_SESSION['product_sess'][$product_id]['tax_rate'];
		}

		elseif( ps_checkout::tax_based_on_vendor_address () ) {

//			if( empty( $_SESSION['product_sess'][$product_id]['tax_rate'] ) ) {
				$db = new ps_DB;
				// Product's tax rate id has priority!
				$q = "SELECT product_weight, tax_rate FROM #__{vm}_product, #__{vm}_tax_rate ";
				$q .= "WHERE product_tax_id=tax_rate_id AND product_id='$product_id'";
				$db->query($q);
				if ($db->next_record()) {
					$rate = $db->f("tax_rate");
					$product_weight = $db->f('product_weight');
					if( $weight_subtotal == 0 && $product_weight > 0 ) {
						$weight_subtotal = $product_weight;
					}
				}
				else {
					// if we didn't find a product tax rate id, let's get the store's tax rate
					$rate = $this->get_taxrate();
				}
				if ($weight_subtotal != 0 or TAX_VIRTUAL=='1') {
					$_SESSION['product_sess'][$product_id]['tax_rate'] = $rate;
					return $rate;
				}
				else {
					$_SESSION['product_sess'][$product_id]['tax_rate'] = 0;
					return 0;
				}
//			}
//			else {
//				return $_SESSION['product_sess'][$product_id]['tax_rate'];
//			}
		}
		return 0;
	}

	/**
	 * Function to get the "pure" undiscounted and untaxed price
	 * of product $product_id. Used by the administration section.
	 *
	 * @param int $product_id
	 * @return array The product price information
	 */
	function get_retail_price($product_id) {

		$db = new ps_DB;
		// Get the vendor id for this product.
		$q = "SELECT vendor_id FROM #__{vm}_product WHERE product_id='$product_id'";
		$db->setQuery($q); $db->query();
		$db->next_record();
		$vendor_id = $db->f("vendor_id");

		// Get the default shopper group id for this product and user
		$q = "SELECT shopper_group_id FROM #__{vm}_shopper_group WHERE `vendor_id`='$vendor_id' AND `default`='1'";
		$db->setQuery($q); $db->query();
		$db->next_record();
		$default_shopper_group_id = $db->f("shopper_group_id");

		$q = "SELECT product_price,product_currency,price_quantity_start,price_quantity_end
				FROM #__{vm}_product_price
				WHERE product_id='$product_id' AND
							shopper_group_id='$default_shopper_group_id'";
		$db->query($q);
		if ($db->next_record()) {
			$price_info["product_price"]= $db->f("product_price");
			$price_info["product_currency"]=$db->f("product_currency");
			$price_info["price_quantity_start"]=$db->f("price_quantity_start"); // added alatak
			$price_info["price_quantity_end"]=$db->f("price_quantity_end");// added alatak
		}
		else {
			$price_info["product_price"]= "";
			$price_info["product_currency"] = $_SESSION['vendor_currency'];
			$price_info["price_quantity_start"]=$db->f("price_quantity_start"); // added alatak
			$price_info["price_quantity_end"]=$db->f("price_quantity_end");// added alatak
		}
		return $price_info;
	}

	/**
	 * Get the price of product $product_id for the shopper group associated
	 * with $auth['user_id'] - including shopper group discounts
	 *
	 * @param int $product_id
	 * @param boolean $check_multiple_prices Check if the product has more than one price for that shopper group?
	 * @return array The product price information
	 */
	function get_price($product_id, $check_multiple_prices=false, $overrideShopperGroup='' ) {
		if( empty( $product_id)) return array();
		$auth = $_SESSION['auth'];
		$cart = $_SESSION['cart'];

		if( empty( $GLOBALS['product_info'][$product_id]['price'] )
		|| !empty($GLOBALS['product_info'][$product_id]['price']["product_has_multiple_prices"])
		|| $check_multiple_prices) {
			$db = new ps_DB;

			$vendor_id = $this->get_vendor_id_ofproduct($product_id);

			if( $overrideShopperGroup === '') {
				$shopper_group_id = $auth["shopper_group_id"];
				$shopper_group_discount = $auth["shopper_group_discount"];
			}
			else {
				$shopper_group_id = $overrideShopperGroup;
				$shopper_group_discount = 0;
			}
			$shopper_group = new ps_shopper_group;
			$shopper_group->makeDefaultShopperGroupInfo($vendor_id);

			// Get the product_parent_id for this product/item
			$product_parent_id = $this->get_field($product_id, "product_parent_id");

			if( !$check_multiple_prices ) {
				/* Added for Volume based prices */
				// This is an important decision: we add up all product quantities with the same product_id,
				// regardless to attributes. This gives "real" volume based discount, because our simple attributes
				// depend on one and the same product_id
                $quantity = 0;
                $parent_id = "";
				if ($product_parent_id) {
                	$parent = true;
                }
                else {
                    $parent = false;
                }
                for ($i=0;$i<$cart["idx"];$i++) {
                    if ($cart[$i]["product_id"] == $product_id) {
                        if ($parent) {
                        	$parent_id  = $cart[$i]["parent_id"];
                        }
                        else {
                        	$quantity += $cart[$i]["quantity"];
                        }
                    }
                }
                if ($parent) {
                    for ($i=0;$i<$cart["idx"];$i++) {
                        if (@$cart[$i]['parent_id'] == $parent_id) {
                            $quantity  += $cart[$i]["quantity"];
                        }
                    }
                }

				$volume_quantity_sql = " ORDER BY price_quantity_start";
				if( $quantity > 0 ) {
					$volume_quantity_sql = " AND (('$quantity' >= price_quantity_start AND '$quantity' <= price_quantity_end)
                                OR (price_quantity_end='0') OR ('$quantity' > price_quantity_end)) ORDER BY price_quantity_end DESC";
				}
			}
			else {
				$volume_quantity_sql = " ORDER BY price_quantity_start";
			}

			// Get the price array
			$price = $this->getPriceByShopperGroup( $product_id, $shopper_group_id, $check_multiple_prices, $volume_quantity_sql );
			if( !$price && $product_parent_id ) {
				// If this is a child product and it has not price, get the price of the parent product
				$price = $this->getPriceByShopperGroup( $product_parent_id, $shopper_group_id, $check_multiple_prices, $volume_quantity_sql );
				if( !$price ) {
					$price = $this->getPriceByShopperGroup( $product_parent_id, $GLOBALS['vendor_info'][$vendor_id]['default_shopper_group_id'], $check_multiple_prices, $volume_quantity_sql );
				}
			}
			elseif( !$price ) {
				$price = $this->getPriceByShopperGroup( $product_id, $GLOBALS['vendor_info'][$vendor_id]['default_shopper_group_id'], $check_multiple_prices, $volume_quantity_sql );
			}
			return $price;
		}
		else {
			return $GLOBALS['product_info'][$product_id]['price'];
		}
	}
	/**
	 * Returns the price for a specific shopper group,
	 * Returns nothing, when the shopper group has no price
	 *
	 * @param int $product_id
	 * @param int $shopper_group_id
	 * @param boolean $check_multiple_prices
	 * @param string $additionalSQL
	 * @return mixed
	 */
	function getPriceByShopperGroup( $product_id, $shopper_group_id, $check_multiple_prices=false, $additionalSQL='' ) {
		global $auth;
		static $resultcache = array();
		$db = new ps_DB;

		//changed by Max Milbers
		$vendor_id = $this->get_vendor_id_ofproduct($product_id);

		if( empty( $shopper_group_id )) {
			$ps_shopper_group = new ps_shopper_group();
			$ps_shopper_group->makeDefaultShopperGroupInfo($vendor_id);
			$shopper_group_id = $GLOBALS['vendor_info'][$vendor_id]['default_shopper_group_id'];
		}

		$whereClause='WHERE product_id=%s AND shopper_group_id=%s ';
		$whereClause = sprintf( $whereClause, intval($product_id), intval($shopper_group_id) );

		$q = "SELECT `product_price`, `product_price_id`, `product_currency` FROM `#__{vm}_product_price` $whereClause $additionalSQL";

		$sig = sprintf("%u\n", crc32($q));

		if( !isset($resultcache[$sig])) {
			$db->query($q);
			if( !$db->next_record() ) return false;
			$price_info["product_price"]= $db->f("product_price") * ((100 - $auth["shopper_group_discount"])/100);
			$price_info["product_currency"] = $db->f("product_currency");

			$price_info["product_base_price"]= $db->f("product_price") * ((100 - $auth["shopper_group_discount"])/100);
			$price_info["product_has_multiple_prices"] = $db->num_rows() > 1;

			$price_info["product_price_id"] = $db->f("product_price_id");
			$price_info["item"]=true;
			$GLOBALS['product_info'][$product_id]['price'] = $price_info;
			// Store the result for later
			$resultcache[$sig] = $price_info;

			return $GLOBALS['product_info'][$product_id]['price'];
		}
		else {
			return $resultcache[$sig];
		}

	}
	/**
	 * Adjusts the price from get_price for the selected attributes
	 * @author Nathan Hyde <nhyde@bigDrift.com>
	 * @author curlyroger from his post at <http://www.phpshop.org/phpbb/viewtopic.php?t=3052>
	 *
	 * @param int $product_id
	 * @param string $description
	 * @return array The adjusted price information
	 */
	function get_adjusted_attribute_price ($product_id, $description='') {

		global $mosConfig_secret;
		$auth = $_SESSION['auth'];
		$price = $this->get_price($product_id);

		$base_price = $price["product_price"];
		$setprice = 0;
		$set_price = false;
		$adjustment = 0;

		// We must care for custom attribute fields! Their value can be freely given
		// by the customer, so we mustn't include them into the price calculation
		// Thanks to AryGroup@ua.fm for the good advice
		if( empty( $_REQUEST["custom_attribute_fields"] )) {
			if( !empty( $_SESSION["custom_attribute_fields"] )) {
				$custom_attribute_fields = vmGet( $_SESSION, "custom_attribute_fields", Array() );
				$custom_attribute_fields_check = vmGet( $_SESSION, "custom_attribute_fields_check", Array() );
			}
			else
			$custom_attribute_fields = $custom_attribute_fields_check = Array();
		}
		else {
			$custom_attribute_fields = $_SESSION["custom_attribute_fields"] = JRequest::getVar(  "custom_attribute_fields", Array() );
			$custom_attribute_fields_check = $_SESSION["custom_attribute_fields_check"]= JRequest::getVar(  "custom_attribute_fields_check", Array() );
		}

		// if we've been given a description to deal with, get the adjusted price
		if ($description != '') { // description is safe to use at this point cause it's set to ''
			require_once(CLASSPATH.'ps_product_attribute.php');
			$product_attributes = ps_product_attribute::getAdvancedAttributes($product_id, true);

			$attribute_keys = explode( ";", $description );

			for($i=0; $i < sizeof($attribute_keys); $i++ ) {
				$temp_desc = $attribute_keys[$i];

				$temp_desc = trim( $temp_desc );
				// Get the key name (e.g. "Color" )
				$this_key = substr( $temp_desc, 0, strpos($temp_desc, ":") );
				$this_value = substr( $temp_desc, strpos($temp_desc, ":")+1 );

				if( in_array( $this_key, $custom_attribute_fields )) {
					if( @$custom_attribute_fields_check[$this_key] == md5( $mosConfig_secret.$this_key )) {
						// the passed value is valid, don't use it for calculating prices
						continue;
					}
				}

				$this_value=str_replace("_"," ",$this_value);
				if( isset( $product_attributes[$this_key]['values'][$this_value] )) {
					$modifier = $product_attributes[$this_key]['values'][$this_value]['adjustment'];
					$operand = $product_attributes[$this_key]['values'][$this_value]['operand'];

					// if we have a number, allow the adjustment
					if (true == is_numeric($modifier) ) {

						$modifier = $GLOBALS['CURRENCY']->convert( $modifier, $price['product_currency'], $GLOBALS['product_currency'] );
						// Now add or sub the modifier on
						if ($operand=="+") {
							$adjustment += $modifier;
						}
						else if ($operand=="-") {
						$adjustment -= $modifier;
						}
						else if ($operand=='=') {
							// NOTE: the +=, so if we have 2 sets they get added
							// this could be moded to say, if we have a set_price, then
							// calc the diff from the base price and start from there if we encounter
							// another set price... just a thought.

							$setprice += $modifier;
							$set_price = true;
						}
					}
				} else {
					continue;
				}
			}
		}

		// no set price was set from the attribs
		if ($set_price == false) {
			$price["product_price"] = $base_price + ($adjustment)*(1 - ($auth["shopper_group_discount"]/100));
		}
		else {
			// otherwise, set the price
			// add the base price to the price set in the attributes
			// then subtract the adjustment amount
			// we could also just add the set_price to the adjustment... not sure on that one.
			if (!empty($adjustment)) {
				$setprice += $adjustment;
			}
			$setprice *= 1 - ($auth["shopper_group_discount"]/100);
			$price["product_price"] = $setprice;
		}

		// don't let negative prices get by, set to 0
		if ($price["product_price"] < 0) {
			$price["product_price"] = 0;
		}
		// Get the DISCOUNT AMOUNT
		$discount_info = $this->get_discount( $product_id );

		$my_taxrate = $this->get_product_taxrate($product_id);

		// If discounts are applied after tax, but prices are shown without tax,
		// AND tax is EU mode and shopper is not in the EU,
		// then ps_product::get_product_taxrate() returns 0, so $my_taxrate = 0.
		// But, the discount still needs to be reduced by the shopper's tax rate, so we obtain it here:
		if( PAYMENT_DISCOUNT_BEFORE != '1'  && $auth["show_price_including_tax"] != 1 && !ps_checkout::tax_based_on_vendor_address() ) {
			$db = new ps_DB;
			$hVendor_id = $_SESSION["ps_vendor_id"];
			require_once( CLASSPATH . 'ps_checkout.php' );
			if (! ps_checkout::tax_based_on_vendor_address ()) {
				if( $auth["user_id"] > 0 ) {

					$q = "SELECT state, country FROM #__{vm}_user_info WHERE user_id='". $auth["user_id"] . "'";
					$db->query($q);

					$db->next_record();
					$state = $db->f("state");
					$country = $db->f("country");

					$q = "SELECT tax_rate FROM #__{vm}_tax_rate WHERE tax_country='$country' ";
					if( !empty($state)) {
						$q .= "AND tax_state='$state'";
					}
					$db->query($q);
					if ($db->next_record()) {
						$my_taxrate = $db->f("tax_rate");
					}
					else {
						$my_taxrate = 0;
					}
				}
				else {
					$my_taxrate = 0;
				}

			}
			else {
				if( empty( $_SESSION['taxrate'][$hVendor_id] )) {
					// let's get the store's tax rate
					$q = "SELECT `tax_rate` FROM #__{vm}_vendor, #__{vm}_tax_rate ";
					$q .= "WHERE tax_country=vendor_country AND #__{vm}_vendor.vendor_id=1 ";
					// !! Important !! take the highest available tax rate for the store's country
					$q .= "ORDER BY `tax_rate` DESC";
					$db->query($q);
					if ($db->next_record()) {
						$my_taxrate = $db->f("tax_rate");
					}
					else {
						$my_taxrate = 0;
					}
				}
			}
		}

		// Apply the discount
		if( !empty($discount_info["amount"])) {
			$undiscounted_price = $base_price;
			switch( $discount_info["is_percent"] ) {
				case 0:
					if( PAYMENT_DISCOUNT_BEFORE == '1' ) {
						// If we subtract discounts BEFORE tax
						// Subtract the whole discount
						$price["product_price"] -= $discount_info["amount"];
					}
					else {
						// But, if we subtract discounts AFTER tax
						// Subtract the untaxed portion of the discount
						$price["product_price"] -= $discount_info["amount"]/($my_taxrate + 1);
					}
					break;
				case 1:
					$price["product_price"] -=  $price["product_price"]*($discount_info["amount"]/100);
					break;
			}
		}

		return $price;
	}

	/**
	 * This function can parse an "advanced / custom attribute"
	 * description like
	 * Size:big[+2.99]; Color:red[+0.99]
	 * and return the same string with values, tax added
	 * Size: big (+3.47), Color: red (+1.15)
	 *
	 * @param string $description
	 * @param int $product_id
	 * @return string The reformatted description
	 */
	function getDescriptionWithTax( $description, $product_id=0 ) {
		global $CURRENCY_DISPLAY, $mosConfig_secret;
		require_once(CLASSPATH.'ps_product_attribute.php');

		$auth = $_SESSION['auth'];
		$description = stripslashes($description);
        $description = str_replace("_"," ",$description);
		// if we've been given a description to deal with, get the adjusted price
		if ($description != '' && $auth["show_price_including_tax"] == 1 && $product_id != 0 ) {
			$my_taxrate = $this->get_product_taxrate($product_id);
			$price = $this->get_price( $product_id );
			$product_currency = $price['product_currency'];
		}
		else {
			$my_taxrate = 0.00;
			$product_currency = '';
		}
		// We must care for custom attribute fields! Their value can be freely given
		// by the customer, so we mustn't include them into the price calculation
		// Thanks to AryGroup@ua.fm for the good advice
		if( empty( $_REQUEST["custom_attribute_fields"] )) {
			if( !empty( $_SESSION["custom_attribute_fields"] )) {
				$custom_attribute_fields = vmGet( $_SESSION, "custom_attribute_fields", Array() );
				$custom_attribute_fields_check = vmGet( $_SESSION, "custom_attribute_fields_check", Array() );
			}
			else {
				$custom_attribute_fields = $custom_attribute_fields_check = Array();
			}
		}
		else {
			$custom_attribute_fields = $_SESSION["custom_attribute_fields"] = JRequest::getVar(  "custom_attribute_fields", Array() );
			$custom_attribute_fields_check = $_SESSION["custom_attribute_fields_check"]= JRequest::getVar(  "custom_attribute_fields_check", Array() );
		}

		$product_attributes = ps_product_attribute::getAdvancedAttributes($product_id);
		$attribute_keys = explode( ";", $description );

		foreach( $attribute_keys as $temp_desc ) {
			$finish = strpos($temp_desc,"]");
			$temp_desc = trim( $temp_desc );
			// Get the key name (e.g. "Color" )
			$this_key = substr( $temp_desc, 0, strpos($temp_desc, ":") );
			$this_value = substr( $temp_desc, strpos($temp_desc, ":")+1 );

			if( in_array( $this_key, $custom_attribute_fields )) {
				if( @$custom_attribute_fields_check[$this_key] == md5( $mosConfig_secret.$this_key )) {
					// the passed value is valid, don't use it for calculating prices
					continue;
				}
			}
            $this_value = str_replace("_"," ",$this_value);
			if( isset( $product_attributes[$this_key]['values'][$this_value] )) {
				$modifier = $product_attributes[$this_key]['values'][$this_value]['adjustment'];
				$operand = $product_attributes[$this_key]['values'][$this_value]['operand'];

				$value_notax = $GLOBALS['CURRENCY']->convert( $modifier, $product_currency );
				if( abs($value_notax) >0 ) {
					$value_taxed = $value_notax * ($my_taxrate+1);
					$temp_desc_new  = str_replace( $operand.$modifier, $operand.' '.$CURRENCY_DISPLAY->getFullValue( $value_taxed ), $temp_desc );

					$description = str_replace( $this_key.':'.$this_value,
												 $this_key.':'.$this_value.' ('.$operand.' '.$CURRENCY_DISPLAY->getFullValue( $value_taxed ).')',
													$description);

				}
				$temp_desc = substr($temp_desc, $finish+1);
			}

		}
        $description = str_replace("_"," ",$description);
		$description = str_replace( $CURRENCY_DISPLAY->symbol, '@saved@', $description );
		$description = str_replace( "[", " (", $description );
		$description = str_replace( "]", ")", $description );
		$description = str_replace( ":", ": ", $description );
		$description = str_replace( ";", "<br/>", $description );
		$description = str_replace( '@saved@', $CURRENCY_DISPLAY->symbol, $description );

		return $description;
	}

	function calcEndUserprice( $product_id, $overrideShoppergroup ) {
		global  $CURRENCY_DISPLAY;
		$auth = $_SESSION['auth'];
		// Get the DISCOUNT AMOUNT
		$discount_info = $this->get_discount( $product_id );

		// Get the Price according to the quantity in the Cart
		$price_info = $this->get_price( $product_id, false, $overrideShoppergroup );

		if (isset($price_info["product_price_id"])) {

			$base_price = $price_info["product_price"];
			$price = $price_info["product_price"];

			if ($auth["show_price_including_tax"] == 1) {
				$my_taxrate = $this->get_product_taxrate($product_id);
				$base_price += ($my_taxrate * $price);
			}
			else {
				$my_taxrate = 0;
			}
			$tax = $my_taxrate * 100;
			$price_info['tax_rate'] = JText::_('VM_TAX_LIST_RATE').': '.$tax.'%';
			// Calculate discount
			if( !empty($discount_info["amount"])) {

				switch( $discount_info["is_percent"] ) {
					case 0:
						$base_price -= $discount_info["amount"];
						$price_info['discount_info'] = JText::_('VM_PRODUCT_DISCOUNT_LBL').': '.$CURRENCY_DISPLAY->getFullValue($discount_info['amount']);
						break;
					case 1:
						$base_price *= (100 - $discount_info["amount"])/100;
						$price_info['discount_info'] = JText::_('VM_PRODUCT_DISCOUNT_LBL').': '.$discount_info['amount'].'%';
						break;
				}
			}
			$price_info['product_price'] = $base_price;
		}
		return $price_info;
	}

	function show_price_with_tax($product_id, $hide_tax_message = false){

		return $this->show_price($product_id, $hide_tax_message, 1);

	}

	function show_price_without_tax($product_id, $hide_tax_message = false){

			return $this->show_price($product_id, $hide_tax_message, 2);

	}
	/**
         * Function to calculate the price, apply discounts from the discount table
         * and reformat the price
	 *
	 * @param int $product_id
	 * @param boolean $hide_tax Wether to show the text "(including X.X% tax)" or not
	 * @return string The formatted price
	 */
	function show_price( $product_id, $hide_tax = false, $showwithtax = 0 ) {
		
		$newCalculator=true;
		echo '<br /><br />show_price called new Calculator modell: '.$newCalculator.'<br />';
		
		global  $CURRENCY_DISPLAY,$vendor_mail;
		$auth = $_SESSION['auth'];
		$tpl = new $GLOBALS['VM_THEMECLASS']();
		
		$product_name = htmlentities( $this->get_field($product_id, 'product_name'), ENT_QUOTES );
		$tpl->set( 'product_id', $product_id );
		$tpl->set( 'product_name', $product_name );
		$tpl->set( 'vendor_mail', $vendor_mail );

		if(!$newCalculator){
					$discount_info = $base_price = array();
		$text_including_tax = '';

		if( $auth['show_prices'] ) {
			// Get the DISCOUNT AMOUNT
			$discount_info = $this->get_discount( $product_id );
			if( !$discount_info["is_percent"] && $discount_info["amount"] != 0 ) {
				$discount_info["amount"] = $GLOBALS['CURRENCY']->convert($discount_info["amount"]);
			}
			// Get the Price according to the quantity in the Cart
			$price_info = $this->get_price( $product_id );
			$tpl->set( 'price_info', $price_info );

			// Get the Base Price of the Product
			$base_price_info = $this->get_price($product_id, true );
			$tpl->set( 'base_price_info', $base_price_info );
			if( $price_info === false ) {
				$price_info = $base_price_info;
			}
			$html = "";
			$undiscounted_price = 0;
			if (isset($price_info["product_price_id"])) {
				if( $base_price_info["product_price"]== $price_info["product_price"] ) {
					$price = $base_price = $GLOBALS['CURRENCY']->convert( $base_price_info["product_price"], $price_info['product_currency'] );
				} else {
					$base_price = $GLOBALS['CURRENCY']->convert( $base_price_info["product_price"], $price_info['product_currency'] );
					$price = $GLOBALS['CURRENCY']->convert( $price_info["product_price"], $price_info['product_currency'] );
				}

				/*if ($auth["show_price_including_tax"] == 1) {
					$my_taxrate = $this->get_product_taxrate($product_id);
					$base_price += ($my_taxrate * $base_price);
				}
				else {
					$my_taxrate = 0;
				}*/

				//ct
				if ($showwithtax == 0){ //backward compatibility
					$showwithtax = $auth["show_price_including_tax"];}
				/*elseif ($showwithtax = 2){ //2 != 1
					$showwithtax = 0;}*/

				//if ($auth["show_price_including_tax"] == 1) {
				if ($showwithtax == 1){
					$my_taxrate = $this->get_product_taxrate($product_id);
					$base_price += ($my_taxrate * $base_price);
				}
				else {
					$my_taxrate = 0;
				}

				// Calculate discount
				if( !empty($discount_info["amount"])) {
					$undiscounted_price = $base_price;
					switch( $discount_info["is_percent"] ) {
						case 0:
							// If we subtract discounts BEFORE tax
							if( PAYMENT_DISCOUNT_BEFORE == '1' ) {
								// and if our prices are shown with tax
								if( $auth["show_price_including_tax"] == 1) {
									// then we add tax to the (untaxed) discount
									$discount_info['amount'] += ($my_taxrate*$discount_info['amount']);
								}
								// but if our prices are shown without tax
									// we just leave the (untaxed) discount amount as it is

							}
							// But, if we subtract discounts AFTER tax
								// and if our prices are shown with tax
									// we just leave the (untaxed) discount amount as it is
								// but if  prices are shown without tax
									// we just leave the (untaxed) discount amount as it is
									// even though this is not really a good combination of settings

							$base_price -= $discount_info["amount"];
							break;
						case 1:
							$base_price *= (100 - $discount_info["amount"])/100;
							break;
					}
				}

				$text_including_tax = "";
				/*if (!empty($my_taxrate)) {
					$tax = $my_taxrate * 100;
					// only show "including x % tax" when it shall
					// not be hidden
					if( !$hide_tax && $auth["show_price_including_tax"] == 1 && VM_PRICE_SHOW_INCLUDINGTAX) {
						$text_including_tax = JText::_('VM_INCLUDING_TAX');
						eval ("\$text_including_tax = \"$text_including_tax\";");
					}
				}*/

				$text_including_tax = "";
				if (!empty($my_taxrate)) {
					$tax = $my_taxrate * 100;
					// only show "including x % tax" when it shall
					// not be hidden	//ct
					if( !$hide_tax && $showwithtax == 1 && VM_PRICE_SHOW_INCLUDINGTAX) {
						$text_including_tax = JText::_('VM_INCLUDING_TAX');
						eval ("\$text_including_tax = \"$text_including_tax\";");
					}
				}

				//ct
				if( $showwithtax != 1 && VM_PRICE_SHOW_EXCLUDINGTAX == 1) {
					$text_excluding_tax = JText::_('VM_EXCLUDING_TAX');
					eval ("\$text_excluding_tax = \"$text_excluding_tax\";");
				}

				// Check if we need to display a Table with all Quantity <=> Price Relationships
				if( $base_price_info["product_has_multiple_prices"] && !$hide_tax ) {
					$db = new ps_DB;
					// Quantity Discount Table
					$q = "SELECT product_price, product_currency, price_quantity_start, price_quantity_end
							FROM #__{vm}_product_price
				  			WHERE product_id='$product_id'
				  			AND shopper_group_id='".$auth["shopper_group_id"]."'
				  			ORDER BY price_quantity_start";
					$db->query( $q );

					//         $prices_table = "<table align=\"right\">
					$prices_table = "<table width=\"100%\">
					  <thead><tr class=\"sectiontableheader\">
					  <th>".JText::_('VM_CART_QUANTITY')."</th>
					  <th>".JText::_('VM_CART_PRICE')."</th>
					  </tr></thead>
					  <tbody>";
					$i = 1;
					if ($db->num_rows()==0) {
						// get the vendor ID
						$q = "SELECT vendor_id FROM #__{vm}_product WHERE product_id='$product_id'";
						$db->setQuery($q); $db->query();
						$db->next_record();
						$vendor_id = $db->f("vendor_id");
						// get the default shopper group ID
						$q = "SELECT shopper_group_id FROM #__{vm}_shopper_group WHERE `vendor_id`='$vendor_id' AND `default`='1'";
						$db->setQuery($q); $db->query();
						$db->next_record();
						$default_shopper_group_id = $db->f("shopper_group_id");
						// get the current shopper group discount
						$q = "SELECT * FROM #__{vm}_shopper_group WHERE shopper_group_id=" . $auth["shopper_group_id"];
						$db->setQuery($q); $db->query();
						$db->next_record();
						$shopper_group_discount = $db->f("shopper_group_discount");
						// check for prices in default shopper group
						$q = "SELECT product_price, price_quantity_start, price_quantity_end FROM #__{vm}_product_price
							WHERE product_id='$product_id' AND shopper_group_id='".$default_shopper_group_id."' ORDER BY price_quantity_start";
						$db->query( $q );
						while( $db->next_record() ) {
							$prices_table .= "<tr class=\"sectiontableentry$i\"><td>".$db->f("price_quantity_start")." - ".$db->f("price_quantity_end")."</td>";
							$prices_table .= "<td>";
							if (!empty($my_taxrate))
								$prices_table .= $CURRENCY_DISPLAY->getFullValue( ($my_taxrate+1)*$db->f("product_price")*((100-$shopper_group_discount)/100) );
							else
								$prices_table .= $CURRENCY_DISPLAY->getFullValue( $db->f("product_price")*((100-$shopper_group_discount)/100) );
							$prices_table .= "</td></tr>";
							$i == 1 ? $i++ : $i--;
						}
					} else {
						// get the current shopper group discount
						$dbsg = new ps_DB();
						$q = "SELECT shopper_group_id,shopper_group_discount FROM #__{vm}_shopper_group WHERE shopper_group_id=" . $auth["shopper_group_id"];
						$dbsg->query($q);
						$dbsg->next_record();
						$shopper_group_discount = $dbsg->f("shopper_group_discount");
						while( $db->next_record() ) {
							$price = $GLOBALS['CURRENCY']->convert( $db->f("product_price"), $db->f("product_currency") );
							$prices_table .= "<tr class=\"sectiontableentry$i\"><td>".$db->f("price_quantity_start")." - ".$db->f("price_quantity_end")."</td>";
							$prices_table .= "<td>";
							if (!empty($my_taxrate)) {
								$prices_table .= $CURRENCY_DISPLAY->getFullValue( ($my_taxrate+1)*$price*((100-$shopper_group_discount)/100) );
							}
							else {
								$prices_table .= $CURRENCY_DISPLAY->getFullValue( $price*((100-$shopper_group_discount)/100) );
							}
							$prices_table .= "</td></tr>";
							$i == 1 ? $i++ : $i--;
						}
					}
					$prices_table .= "</tbody></table>";
					if( @$_REQUEST['page'] != "shop.product_details" ) {
						$html .= vmToolTip( $prices_table );
					}
					else
					$html .= $prices_table;
				}
			}
		}
		} else{

			require_once(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'calculationh.php');
			$calculator = new calculationHelper();
//			$priceData = $calculator -> getCheckoutPrices(array('1','1','2','3'));
			$priceData = $calculator -> getProductPrices($product_id);
			$discount_info['amount'] = $priceData['discountAmount'];
			$text_including_tax = $priceData['salesPrice'];
			
//			if( $showwithtax != 1 && VM_PRICE_SHOW_EXCLUDINGTAX == 1) $text_excluding_tax = $priceData['priceWithoutTax']; //ct
			$text_excluding_tax = $priceData['priceWithoutTax'];
			$undiscounted_price = $priceData['basePriceWithTax'];  //With Tax?
			$base_price = $priceData['basePrice'];
			//Hmm for what is this needed?
//			$html, $price_info, $base_price_info
//			// Get the Price according to the quantity in the Cart
//			$tpl->set( 'price_info', $price_info );
//			// Get the Base Price of the Product
//			$tpl->set( 'base_price_info', $base_price_info );
		}
		
		$tpl->set( 'discount_info', $discount_info );
		$tpl->set( 'text_including_tax', $text_including_tax );
//		if( $showwithtax != 1 && VM_PRICE_SHOW_EXCLUDINGTAX == 1) $tpl->set( 'text_excluding_tax', $text_excluding_tax ); //ct
		$tpl->set( 'text_excluding_tax', $text_excluding_tax );
		$tpl->set( 'undiscounted_price', @$undiscounted_price );
		$tpl->set( 'base_price', $base_price );
        $tpl->set( 'price_table', $html);

		
		return $tpl->fetch( 'common/price.tpl.php');

	}

	/**
	 * Get the information about the discount for a product
	 *
	 * @param int $product_id
	 * @return array The discount information
	 */
	function get_discount( $product_id ) {
		global $mosConfig_lifetime;

		// We use the Session now to store the discount info for
		// each product. But this info can change regularly,
		// so we check if the session time has expired
		if( empty( $_SESSION['product_sess'][$product_id]['discount_info'] )
		|| (time() - $_SESSION['product_sess'][$product_id]['discount_info']['create_time'] )>$mosConfig_lifetime) {
			$db = new ps_DB;
			$starttime = time();
			$year = date('Y');
			$month = date('n');
			$day = date('j');
			// get the beginning time of today
			$endofday = mktime(0, 0, 0, $month, $day, $year) - 1440;

			// Get the DISCOUNT AMOUNT
			$q = "SELECT amount,is_percent FROM #__{vm}_product,#__{vm}_product_discount ";
			$q .= "WHERE product_id='$product_id' AND (start_date<='$starttime' OR start_date=0) AND (end_date>='$endofday' OR end_date=0) ";
			$q .= "AND product_discount_id=discount_id";
			$db->query( $q );
			if( $db->next_record() ) {
				$discount_info["amount"] = $db->f("amount");
				$discount_info["is_percent"] = $db->f("is_percent");
                $no_discount = false;
			}
			else {
				$discount_info["amount"] = 0;
				$discount_info["is_percent"] = 0;
                $no_discount = true;
			}
            if ($no_discount) {
                $q = "SELECT product_parent_id FROM #__{vm}_product WHERE product_id=$product_id";
                $db->query($q);
                if($db->next_record()) {
                    $q = "SELECT amount,is_percent FROM #__{vm}_product,#__{vm}_product_discount ";
			        $q .= "WHERE product_id='".$db->f("product_parent_id")."' AND (start_date<='$starttime' OR start_date=0) AND (end_date>='$endofday' OR end_date=0) ";
			        $q .= "AND product_discount_id=discount_id";
			        $db->query( $q );
			        if( $db->next_record() ) {
				        $discount_info["amount"] = $db->f("amount");
				        $discount_info["is_percent"] = $db->f("is_percent");
			        }
                }
            }
			$discount_info['create_time'] = time();
			$_SESSION['product_sess'][$product_id]['discount_info'] = $discount_info;
			return $discount_info;
		}
		else
		    return $_SESSION['product_sess'][$product_id]['discount_info'];
	}

	/**
	 * display a snapshot of a product based on the product sku.
	 * This was written to provide a quick way to display a product inside of modules
	 *
	 * @param string $product_sku The SKU identifying the product
	 * @param boolean $show_price Show the product price?
	 * @param boolean $show_addtocart Show the add-to-cart link?
	 * @param boolean $show_product_name Show the product name?
	 */
	function show_snapshot($product_sku, $show_price=true, $show_addtocart=true, $show_product_name = true ) {
		echo $this->product_snapshot( $product_sku, $show_price, $show_addtocart, $show_product_name );
	}

	/**
	 * Returns HTML code for a snapshot of a product based on the product sku.
	 * This was written to provide a quick way to display a product inside of modules
	 *
	 * @param string $product_sku The SKU identifying the product
	 * @param boolean $show_price Show the product price?
	 * @param boolean $show_addtocart Show the add-to-cart link?
	 * @param boolean $show_product_name Show the product name?
	 */
	function product_snapshot( $product_sku, $show_price=true, $show_addtocart=true, $show_product_name = true ) {

		global $sess, $mm_action_url;

		$db = new ps_DB;

		require_once(CLASSPATH.'ps_product_category.php');
		$ps_product_category = new ps_product_category;

		$q = "SELECT product_id, product_name, product_parent_id, product_thumb_image FROM #__{vm}_product WHERE product_sku='$product_sku'";
		$db->query( $q );

		if ($db->next_record()) {
			$product_id = $db->f("product_id" );
			$tpl = new $GLOBALS['VM_THEMECLASS']();

			$cid = $ps_product_category->get_cid( $product_id );

			$tpl->set( 'product_id', $product_id);
			$tpl->set( 'product_name', $db->f("product_name") );
			$tpl->set( 'show_product_name', $show_product_name );

			if ($db->f("product_parent_id")) {
				$url = "?page=shop.product_details&category_id=$cid&flypage=".$this->get_flypage($db->f("product_parent_id"));
				$url .= "&product_id=" . $db->f("product_parent_id");
			} else {
				$url = "?page=shop.product_details&category_id=$cid&flypage=".$this->get_flypage($db->f("product_id"));
				$url .= "&product_id=" . $db->f("product_id");
			}
			$product_link = $sess->url($mm_action_url. "index.php" . $url);
			$tpl->set( 'product_link', $product_link );
			$tpl->set( 'product_thumb_image', $db->f("product_thumb_image"), "alt=\"".$db->f("product_name")."\"");

			if (_SHOW_PRICES == '1' && $show_price) {
				// Show price, but without "including X% tax"
				$price = $this->show_price( $db->f("product_id"), true );
				$tpl->set( 'price', $price );
			}
			if (USE_AS_CATALOGUE != 1 && $show_addtocart && isset( $GLOBALS['product_info'][$product_id]['price']['product_price_id'] )) {
				$url = "?page=shop.cart&func=cartAdd&product_id=" .  $db->f("product_id");
				$addtocart_link = $sess->url($mm_action_url. "index.php" . $url);
				$tpl->set( 'addtocart_link', $addtocart_link );
			}
			return $tpl->fetch( 'common/productsnapshot.tpl.php');
		}

		return '';

	}

	/**
	 * Use this function if you need the weight of a product
	 *
	 * @param int $prod_id
	 * @return int The weight of the product
	 */
	function get_weight($prod_id) {
		return (float)$this->get_field( $prod_id, "product_weight");
	}
	/**
	 * Print the availability HTML code for product $prod_id
	 *
	 * @param int $prod_id
	 */
	function show_availability($prod_id) {
		echo $this->get_availability($prod_id);
	}

	/**
	 * Returns the availability information as HTML code
	 * @author soeren
	 * @param int $prod_id
	 * @return string
	 */
	function get_availability($prod_id) {

		$html = '';
		$availArr = $this->get_availability_data( $prod_id );
		if( !empty( $availArr )) {
			$tpl = vmTemplate::getInstance();
			$tpl->set( 'product_id', $prod_id );
			$tpl->set( 'product_available_date', $availArr['product_available_date'] );
			$tpl->set( 'product_availability', $availArr['product_availability'] );
			$tpl->set( 'product_in_stock', $availArr['product_in_stock'] );
			$html = $tpl->fetch( 'common/availability.tpl.php');
		}
		return $html;
	}
	/**
	 * Retrieves the data related to availability information
	 *
	 * @param int $prod_id
	 * @return array
	 */
	function get_availability_data( $prod_id) {
		$is_parent = $this->parent_has_children( $prod_id );
		$availArr = array();
		if( !$is_parent ) {
			$availArr['product_id'] = $prod_id;
			$availArr['product_available_date'] = $this->get_field( $prod_id, 'product_available_date');
			$availArr['product_availability'] = $this->get_field( $prod_id, 'product_availability');
			$availArr['product_in_stock'] = $this->get_field( $prod_id, 'product_in_stock');
		}
		return $availArr;
	}
	/**
	 * Modifies the product_publish field and toggles it from Y to N or N to Y
	 * for product $d['product_id']
	 * @deprecated
	 * @param int $d $d['task'] must be "publish" or "unpublish"
	 * @return unknown
	 */
	function product_publish( &$d ) {
		$this->handlePublishState( $d );
		return;
	}
	/**
	 * Function to check if a product exists (and is published)
	 * @static
	 * @param int $product_id
	 * @param boolean $check_publishstate
	 * @return boolean
	 */
    function product_exists( $product_id, $check_publishstate=true ) {
    	$db = new ps_DB();
    	$q = "SELECT product_id FROM #__{vm}_product WHERE ";
		$q .= "product_id = ".(int)$product_id;
		if( $check_publishstate ) {
			$q.= ' AND product_publish=\'Y\'';
		}
		$db->query ( $q );
		return $db->num_rows() > 0;
    }
    /**
     * Assembles the $child_options variable for storage in the product table
     *
     * @param unknown_type $d
     * @return unknown
     */
    function set_child_options( $d ) {
		if($d["product_parent_id"] !=0) {
			$child_options = null;
        } else {
			$child_options = vmrequest::getYesOrNo('display_use_parent').","
										.vmGet( $d, 'product_list', 'N' ).","
										.vmrequest::getYesOrNo('display_headers').","
										.vmrequest::getYesOrNo('product_list_child').","
										.vmrequest::getYesOrNo('product_list_type').","
										.vmrequest::getYesOrNo('display_desc').","
										.vmrequest::getVar('desc_width').","
										.vmrequest::getVar('attrib_width').","
										.vmrequest::getVar('child_class_sfx').","
										.vmrequest::getVar('child_order_by');
        }
        return $child_options;
    }

    function &get_child_options( $product_id ) {
    	$child_options= array();
    	$child_options_string = ps_product::get_field( $product_id, 'child_options', true );
		$fields=explode(',',$child_options_string);
		if( !empty( $fields)) {
			$child_options['display_use_parent'] =array_shift($fields);
			$child_options['product_list'] =array_shift($fields);
			$child_options['display_header'] =array_shift($fields);
			$child_options['product_list_child'] =array_shift($fields);
			$child_options['product_list_type'] =array_shift($fields);
			$child_options['ddesc'] =array_shift($fields);
			$child_options['display_desc'] =& $child_options['ddesc'];
			$child_options['dw'] =array_shift($fields);
			$child_options['desc_width'] =& $child_options['dw'];
			$child_options['aw'] =array_shift($fields);
			$child_options['attrib_width'] =& $child_options['aw'];
			$child_options['class_suffix'] =array_shift($fields);
			$child_options['child_class_sfx'] =& $child_options['class_suffix'];
			$child_options['order_by'] =array_shift($fields);
			$child_options['child_order_by'] =& $child_options['order_by'];
		}
		return $child_options;
    }
    /**
     * Assembles the string "quantity_options" for storage in the product table
     *
     * @param array $d
     * @return string
     */
    function set_quantity_options( &$d ) {
    	return vmGet($d,'quantity_box').","
        			.vmRequest::getInt('quantity_start').","
        			.vmRequest::getInt('quantity_end').","
        			.vmRequest::getInt('quantity_step');
    }
    /**
     * Disassembles the comma-separated "quantity_options" string
     * and creates an array with associative indices
     *
     * @param int $product_id
     * @return array
     */
    function &get_quantity_options( $product_id ) {
    	$quantity_options = array('quantity_start' => 0, 'quantity_end' => 0, 'quantity_step' => 1 );
    	$quantity_options_string = ps_product::get_field($product_id, 'quantity_options');

    	$fields = explode(',', $quantity_options_string );
    	if( !empty( $fields )&& sizeof($fields) > 1 ) {
    		$quantity_options['quantity_box'] = $fields[0];
    		$quantity_options['display_type'] = $fields[0];
    		$quantity_options['quantity_start'] = $fields[1];
    		$quantity_options['quantity_end'] = $fields[2];
    		$quantity_options['quantity_step'] = $fields[3];
    	}
    	return $quantity_options;
    }

    /**
     * Retrieves the maximum and minimum quantity for the product specified by $product_id
     *
     * @param int $product_id
     * @return array
     */
    function product_order_levels($product_id) {

        $min_order=0;
        $max_order=0;
        $product_order_levels = ps_product::get_field( $product_id, 'product_order_levels');
        $product_parent_id = ps_product::get_field( $product_id, 'product_parent_id');


		if($product_order_levels != ',') {
			$order_levels = $product_order_levels;
			$levels = explode(",",$order_levels);
			$min_order = array_shift($levels);
			$max_order = array_shift($levels);
		}
		else if($product_parent_id > 0) {
            //check parent if product_parent_id != 0
        	$product_order_levels = ps_product::get_field( $product_parent_id, 'product_order_levels');
        	$product_parent_id = ps_product::get_field( $product_parent_id, 'product_parent_id');

			if($product_order_levels != ",") {
				$order_levels = $product_order_levels;
				$levels = explode(",",$order_levels);
				$min_order = array_shift($levels);
				$max_order = array_shift($levels);
			}
		}

        return array($min_order,$max_order);
    }


    function featuredProducts($random, $products, $categories) {
	    
	    require_once( CLASSPATH . 'ps_product_attribute.php');
	    $ps_product_attribute = new ps_product_attribute();
	    $db = new ps_DB;
	    $tpl = new $GLOBALS['VM_THEMECLASS']();
	    $category_id = null;
	    if($categories) {
	        $category_id = vmRequest::getInt('category_id');
	    }
        if ( $category_id ) {
	        $q  = "SELECT DISTINCT product_sku,#__{vm}_product.product_id,product_name,product_s_desc,product_thumb_image, product_full_image, product_in_stock, product_url FROM #__{vm}_product, #__{vm}_product_category_xref, #__{vm}_category WHERE \n";
	        $q .= "(#__{vm}_product.product_parent_id='' OR #__{vm}_product.product_parent_id='0') \n";
	        $q .= "AND #__{vm}_product.product_id=#__{vm}_product_category_xref.product_id \n";
	        $q .= "AND #__{vm}_category.category_id=#__{vm}_product_category_xref.category_id \n";
            $q .= "AND #__{vm}_category.category_id='$category_id' \n";
	        $q .= "AND #__{vm}_product.product_publish='Y' \n";
	        $q .= "AND #__{vm}_product.product_special='Y' \n";
	        if( CHECK_STOCK && PSHOP_SHOW_OUT_OF_STOCK_PRODUCTS != "1") {
		        $q .= " AND product_in_stock > 0 \n";
	        }
	        $q .= 'ORDER BY RAND() LIMIT 0, '.(int)$products;
        }
        else {
	        $q  = "SELECT DISTINCT product_sku,product_id,product_name,product_s_desc,product_thumb_image, product_full_image, product_in_stock, product_url FROM #__{vm}_product WHERE ";
			//TODO thinking about vendorrelation ship in this context by Max Milbers vendor should be related to the displayed product
//	        $q .= "(#__{vm}_product.product_parent_id='' OR #__{vm}_product.product_parent_id='0') AND vendor_id='".$_SESSION['ps_vendor_id']."' ";
	        $q .= "(#__{vm}_product.product_parent_id='' OR #__{vm}_product.product_parent_id='0')  ";
	        $q .= "AND #__{vm}_product.product_publish='Y' ";
	        $q .= "AND #__{vm}_product.product_special='Y' ";
	        if( CHECK_STOCK && PSHOP_SHOW_OUT_OF_STOCK_PRODUCTS != "1") {
		        $q .= " AND product_in_stock > 0 ";
	        }
	        $q .= 'ORDER BY RAND() LIMIT 0, '.(int)$products;
        }
        $db->query($q);
        // Output using template
        if( $db->num_rows() > 0 ) {
	        $i = 0;
            $featured_products = array();
	        while($db->next_record()) {
                $flypage = $this->get_flypage($db->f("product_id"));
                $featured_products[$i]['product_sku'] = $db->f("product_sku");
                $featured_products[$i]['product_name'] = $db->f("product_name");
                $price = "";
                if (_SHOW_PRICES == '1') {
				    // Show price, but without "including X% tax"
				    $price = $this->show_price( $db->f("product_id"), false );
			    }
                $featured_products[$i]['product_price'] = $price;
                $featured_products[$i]['product_s_desc'] = $db->f("product_s_desc");
                $featured_products[$i]['product_url'] = $db->f("product_url");
                $featured_products[$i]['product_thumb'] = $db->f("product_thumb_image");
                $featured_products[$i]['product_full_image'] = $db->f("product_full_image");
                $featured_products[$i]['product_id'] = $db->f("product_id");
                $featured_products[$i]['flypage'] = $flypage;
                $featured_products[$i]['form_addtocart'] = "";
                if (USE_AS_CATALOGUE != '1' && $price != ""
	                && !stristr( $price, JText::_('VM_PRODUCT_CALL') )
	                && !$this->product_has_attributes( $db->f('product_id'), true )
	                && $tpl->get_cfg( 'showAddtocartButtonOnProductList' ) ) {
			        $tpl->set( 'i', $i );
			        $tpl->set( 'product_id', $db->f('product_id') );
			        $tpl->set( 'ps_product_attribute', $ps_product_attribute );
			        $tpl->set( 'product_in_stock', $db->f('product_in_stock'));
			        $featured_products[$i]['form_addtocart'] = $tpl->fetch( 'browse/includes/addtocart_form.tpl.php' );
//			        $featured_products[$i]['form_addtocart'] = $tpl->fetch('product_details/includes/addtocart_form.tpl.php' );
			        $featured_products[$i]['has_addtocart'] = true;
		        }
		        $i++;
	        }
            $tpl->set( 'featured_products', $featured_products );
            return $tpl->fetch( 'common/featuredProducts.tpl.php');
        }
    }

    function latestProducts($random, $products) {
    	return "";
    }

    function addRecentProduct($product_id,$category_id,$maxviewed) {
    	global $recentproducts;
    	//Check to see if we alread have recent
    	if($_SESSION['recent']['idx'] !=0) {
    		for($i=0;$i<$_SESSION['recent']['idx'];$i++){
    			//Check if it already exists and remove and reorder array
    			if($_SESSION['recent'][$i]['product_id']==$product_id) {
    				for($k=$i;$k<$_SESSION['recent']['idx']-1;$k++){
    					$_SESSION['recent'][$k]=$_SESSION['recent'][$k+1];
    				}
    				array_pop($_SESSION['recent']);
    				$_SESSION['recent']['idx']--;
    			}
    		}
    	}
    	// add product to recently viewed
    	$_SESSION['recent'][$_SESSION['recent']['idx']]['product_id'] = $product_id;
    	$_SESSION['recent'][$_SESSION['recent']['idx']]['category_id'] = $category_id;
    	$_SESSION['recent']['idx']++;
    	//Check to see if we have reached are limit and remove first item
    	if($_SESSION['recent']['idx'] > $maxviewed+1) {
    		for($k=0;$k<$_SESSION['recent']['idx']-1;$k++){
    			$_SESSION['recent'][$k]=$_SESSION['recent'][$k+1];
    		}
    		array_pop($_SESSION['recent']);
    		$_SESSION['recent']['idx']--;
    	}
    }

    function recentProducts($product_id,$maxitems) {
    	global $db, $sess;
    	if( $maxitems == 0 ) return;

    	$recentproducts = $_SESSION['recent'];
    	//No recent products so return empty
		if($recentproducts['idx'] == 0) {
			//return "";
		}
    	$tpl = new $GLOBALS['VM_THEMECLASS']();
		$db = new ps_DB;
        $dbp = new ps_DB;
		$k=0;
		$recent = array();
		// Iterate through loop backwards (newest to oldest)
		for($i=$recentproducts['idx']-1;$i >= 0;$i--) {
			//Check if on current product and don't display
			if($recentproducts[$i]['product_id']== $product_id){
				continue;
			}
			// If we have not reached max products add the next product
			if($k < $maxitems) {
				$prod_id = $recentproducts[$i]['product_id'];
				$category_id = $recentproducts[$i]['category_id'];
				$q = "SELECT product_name, category_name, c.category_flypage,product_s_desc,product_thumb_image ";
				$q .= "FROM #__{vm}_product as p,#__{vm}_category as c,#__{vm}_product_category_xref as cx ";
				$q .= "WHERE p.product_id = '$prod_id' ";
				$q .= "AND c.category_id = '$category_id' ";
				$q .= "AND p.product_id = cx.product_id ";
				$q .= "AND c.category_id=cx.category_id ";
				$q .= "AND p.product_publish='1' ";
				$q .= "AND c.published='1' ";
				$q .= "LIMIT 0,1";
				$db->query( $q );
				if( !$db->next_record() ) {
					continue;
				}
                if(!$this->is_product($prod_id )) {
                    $prod_id_p = $this->get_field($prod_id,"product_parent_id");
                    $q = "SELECT product_name,category_name, c.category_flypage,product_s_desc,product_thumb_image ";
				    $q .= "FROM #__{vm}_product as p,#__{vm}_category as c,#__{vm}_product_category_xref as cx ";
				    $q .= "WHERE p.product_id = '$prod_id_p' ";
				    $q .= "AND c.category_id = '$category_id' ";
				    $q .= "AND p.product_id = cx.product_id ";
				    $q .= "AND c.category_id=cx.category_id LIMIT 0,1";
                    $dbp->query( $q );
                }
				$recent[$k]['product_s_desc'] = $db->f("product_s_desc");
                if($recent[$k]['product_s_desc']=="" && !empty($prod_id_p)) {
                    $recent[$k]['product_s_desc'] = $dbp->f("product_s_desc");
                }
				$flypage = $db->f("category_flypage");
                if(empty($flypage) && !empty($prod_id_p))
                    $flypage = $dbp->sf("category_flypage");
				if( empty( $flypage )) {
					$flypage = FLYPAGE;
				}
				$flypage = str_replace( 'shop.', '', $flypage);
				$flypage = stristr( $flypage, '.tpl') ? $flypage : $flypage . '.tpl';
				$recent[$k]['product_url'] = $sess->url("page=shop.product_details&amp;product_id=$prod_id&amp;category_id=$category_id&amp;flypage=$flypage");
				$recent[$k]['category_url'] = $sess->url("page=shop.browse&amp;category_id=$category_id");
				$recent[$k]['product_name'] = $db->f("product_name");
                if($recent[$k]['product_name']=="" && !empty($prod_id_p)) {
                    $recent[$k]['product_name'] = $dbp->f("product_name");
                }
                $recent[$k]['product_name'] = shopMakeHtmlSafe($recent[$k]['product_name']);
				$recent[$k]['category_name'] = $db->f("category_name");
                if($recent[$k]['category_name']=="" && !empty($prod_id_p)) {
                    $recent[$k]['category_name'] = $dbp->f("category_name");
                }
				$recent[$k]['product_thumb_image'] = $db->f("product_thumb_image");
				if($recent[$k]['product_thumb_image']=="" && !empty($prod_id_p)) {
                    $recent[$k]['product_thumb_image'] = $dbp->f("product_thumb_image");
                }
				$k++;
			}
		}
		if($k == 0) {
			return "";
		}
		$tpl->set("recent_products",$recent);
		return $tpl->fetch( 'common/recent.tpl.php' );
    }
    
    function stockIndicator($stock_level,$reorder_level,$pid,$detail="browse") {
    	global $db;
	    $tpl = new $GLOBALS['VM_THEMECLASS']();
	    //Check for parent item without stock and determine if children have levels
    	if($this->parent_has_children($pid) && $stock_level == 0 && $detail != "child") {
    		//Check if we are on the detail page, dont want to show levels for empty parent
    		if($detail=="detail") {
    			return "";
    		}
    		$db = new ps_DB;
    		//Check children for stock.
    		$q = "SELECT product_in_stock AS s,low_stock_notification AS r FROM #__{vm}_product  " ;
			$q .= " WHERE product_publish='Y' AND product_parent_id='$pid' ";
			$db->query($q);
			$stock_ok = false;
			while($db->next_record()) {				
				if($db->f('s') > $db->f('r') ) {
					$stock_level = $db->f('s');
					$reorder_level = $db->f('r');
					$stock_ok = true;
				}
				elseif (!$stock_ok) {
					$stock_level = $db->f('s');
					$reorder_level = $db->f('r');
				}
			}
    	}
    	// Assign class to indicator 
		$level = 'stock_ok';
		$stock_tip = JText::_('VM_STOCK_LEVEL_DISPLAY_NORMAL_TIP');
		if ($stock_level <= $reorder_level) {
			$level = 'stock_low';
			$stock_tip = JText::_('VM_STOCK_LEVEL_DISPLAY_LOW_TIP');
		}
		if ($stock_level == 0) {
			$level = 'stock_out';
			$stock_tip = JText::_('VM_STOCK_LEVEL_DISPLAY_OUT_TIP');
		}    	
    	switch($detail) {
    		case 'child' :
    			$label = JText::_('VM_STOCK_LEVEL_DISPLAY_CHILD_LABEL');   			
    			break;
    		case 'detail' :
    			$label = JText::_('VM_STOCK_LEVEL_DISPLAY_DETAIL_LABEL');    			
    			break;
    		case 'browse' :
    		default :
    			$label = JText::_('VM_STOCK_LEVEL_DISPLAY_BROWSE_LABEL');
    			break;
    	}
    	$tpl->set('stock_tip',$stock_tip);
    	$tpl->set('stock_level',$level);
    	$tpl->set('stock_level_label',$label);
    	return $tpl->fetch( 'common/stockIndicator.tpl.php' );
    }
    function get_build_product_id($product_id) {
    	return;
    }
    function favouritesButton($product_id) {
    	global $my, $db;
	    $tpl = new $GLOBALS['VM_THEMECLASS']();
	    $q = "SELECT * FROM `#__{vm}_favourites` WHERE `user_id`=".$my->id." AND `product_id`=".$product_id;
	    $db->query($q);
	    if($db->nextRow()) {
	    	return true;
	    }
	    return false;
    }    
}  // ENd of CLASS ps_product

?>
