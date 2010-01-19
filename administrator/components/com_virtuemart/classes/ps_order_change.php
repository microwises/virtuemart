<?php
if( ! defined( '_VALID_MOS' ) && ! defined( '_JEXEC' ) )
	die( 'Direct Access to ' . basename( __FILE__ ) . ' is not allowed.' ) ;
/**
 *
 * @version $Id$
 * @author nfischer & kaltokri
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 *
 */

/****************************************************************************
 * ps_order_edit
 * The class  acts as a plugin for the order_print page.
 *************************************************************************/
class ps_order_change {
	
	var $order_id ;
	var $reload_from_db ;
	
	/**************************************************************************
	 * name: ps_order_change (constructor)
	 * created by: kaltokri
	 * description: constructor, setup initial variables
	 * parameters: Order Id
	 * returns: none
	 **************************************************************************/
	function ps_order_change( $order_id ) {
		$this->order_id = $order_id ;
	}
	
	/**************************************************************************
	 * name: recalc_order
	 * created by: kaltokri
	 * description: Recalc the order (copied & modified from ps_checkout.php)
	 * parameters: $order_id
	 * returns: nothing
	 **************************************************************************/
	function recalc_order( $order_id ) {
		//global  $vmLogger;
		

		$debug_output = False ;
		
		// Read all items from db
		$db = new ps_DB( ) ;
		$q = "SELECT * FROM #__{vm}_order_item WHERE order_id = '" . $order_id . "'" ;
		$db->query( $q ) ;
		
		$order_tax_details = array() ;
		
		while( $db->next_record() ) {
			$product_final_price = $db->f( "product_final_price" ) ;
			$product_item_price = $db->f( "product_item_price" ) ;
			$product_quantity = $db->f( "product_quantity" ) ;
			
			if ($product_item_price > 0) {
				$my_taxrate = strval(round(($product_final_price / $product_item_price) - 1,2)."00");
			} else {
				$my_taxrate = 0;
			}
			$order_tax += ($product_final_price - $product_item_price) * $product_quantity ;
			$order_subtotal += $product_item_price * $product_quantity ;
			
			if( MULTIPLE_TAXRATES_ENABLE ) {
				// Calculate the amounts for each tax rate
				if( ! isset( $order_tax_details[$my_taxrate] ) ) {
					$order_tax_details[$my_taxrate] = 0 ;
				}
				$order_tax_details[$my_taxrate] += ($product_final_price - $product_item_price) * $product_quantity ;
			}
		}
		
		$db = new ps_DB( ) ;
		
		$q = "SELECT * FROM #__{vm}_orders WHERE order_id = '" . $order_id . "'" ;
		$db->query( $q ) ;
		
		// Read fix data from db
		$order_shipping = $db->f( "order_shipping" ) ;
		$order_shipping_tax = $db->f( "order_shipping_tax" ) ;
		$coupon_discount = $db->f( "coupon_discount" ) ;
		$order_discount = $db->f( "order_discount" ) ;
		
		$order_total = $order_subtotal + round( $order_tax, 2 ) + $order_shipping + $order_shipping_tax - $coupon_discount - $order_discount ;
		
		If( PAYMENT_DISCOUNT_BEFORE == 1 ) {
			// Calculate the taxes after discounts are subtracted
			$my_total_taxrate = round( (($order_subtotal + $order_tax) / $order_subtotal) - 1, 4 ) ;
			$temp_order_subtotal = $order_subtotal - $coupon_discount - $order_discount ;
			$order_tax = $temp_order_subtotal * $my_total_taxrate ;
			
			// Recalculate the order_total
			$order_total = $temp_order_subtotal + round( $order_tax, 2 ) + $order_shipping + $order_shipping_tax ;
			
			// If multiple taxes are used, they must be corrected
			$discount_factor = ($coupon_discount + $order_discount) / $order_subtotal ;
			if( MULTIPLE_TAXRATES_ENABLE ) {
				foreach( $order_tax_details as $rate => $value ) {
					$order_tax_details[$rate] = $value * (1 - $discount_factor) ;
				}
			}
			
			// Debug information
			if( $debug_output ) {
				$vmLogger->info( "\n" . '$order_subtotal=' . $order_subtotal . "\n" . '$order_discount=' . $order_discount * - 1 . "\n" . '$coupon_discount=' . $coupon_discount * - 1 . "\n" . '$temp_order_subtotal=' . $temp_order_subtotal . "\n" . '$order_tax=' . $order_tax . "\n" . '$order_shipping=' . $order_shipping . "\n" . '$order_shipping_tax=' . $order_shipping_tax . "\n" . '$order_total=' . $order_total . "\n" . '$order_tax_details=' . serialize( $order_tax_details ) ) ;
			}
		} else {
			if( $debug_output ) {
				// Debug information	
				$vmLogger->info( "\n" . '$order_subtotal=' . $order_subtotal . "\n" . '$order_tax=' . $order_tax . "\n" . '$order_discount=' . $order_discount * - 1 . "\n" . '$coupon_discount=' . $coupon_discount * - 1 . "\n" . '$order_shipping=' . $order_shipping . "\n" . '$order_shipping_tax=' . $order_shipping_tax . "\n" . '$order_total=' . $order_total . "\n" . '$order_tax_details=' . serialize( $order_tax_details ) ) ;
			}
		}
		
		// Write data to database
		$q = "UPDATE #__{vm}_orders SET " ;
		$q .= "order_subtotal = " . $order_subtotal . ", " ;
		$q .= "order_tax = " . $order_tax . ", " ;
		$q .= "order_total = " . $order_total . ", " ;
		$q .= "order_tax_details =  '" . serialize( $order_tax_details ) . "' " ;
		$q .= " WHERE order_id = '" . $order_id . "'" ;
		
		$db->query( $q ) ;
		$db->next_record() ;
	}
	
	/**************************************************************************
	 * name: change_bill_to (constructor)
	 * created by: kaltokri
	 * description: Change bill to
	 * parameters: none
	 * returns: none
	 **************************************************************************/
	function change_bill_to() {
		global  $vmLogger ;
		
		$db = new ps_DB( ) ;
		$db2 = new ps_DB( ) ;
		$bill_to = trim( JRequest::getVar(  'bill_to' ) ) ;
		
//		$q = "SELECT * FROM #__{vm}_user_info WHERE user_id = '" . $bill_to . "'" ;
//		$db->query( $q ) ;

		$db = ps_user::get_user_details($bill_to);
		echo('Hmmm '.$db->f( 'user_id' ));
		
		// Update order
		$q = "UPDATE #__{vm}_orders " ;
		$q .= "SET user_id = '" . $bill_to . "'," ;
		$q .= " user_info_id = '" . $db->f( 'user_info_id' ) . "'" ;
		$q .= " WHERE order_id = '" . $this->order_id . "'" ;
		$db2->query( $q ) ;
		$db2->next_record() ;
		
		// Update order_user_info
		$q = "UPDATE #__{vm}_order_user_info " ;
		$q .= "SET user_id = '" . $db->f( 'user_id' ) . "', " ;
		$q .= "address_type_name = '" . $db->f( 'address_type_name' ) . "', " ;
		$q .= "company = '" . $db->f( 'company' ) . "', " ;
		$q .= "title = '" . $db->f( 'title' ) . "', " ;
		$q .= "last_name = '" . $db->f( 'last_name' ) . "', " ;
		$q .= "first_name = '" . $db->f( 'first_name' ) . "', " ;
		$q .= "middle_name = '" . $db->f( 'middle_name' ) . "', " ;
		$q .= "phone_1 = '" . $db->f( 'phone_1' ) . "', " ;
		$q .= "phone_2 = '" . $db->f( 'phone_2' ) . "', " ;
		$q .= "fax = '" . $db->f( 'fax' ) . "', " ;
		$q .= "address_1 = '" . $db->f( 'address_1' ) . "', " ;
		$q .= "address_2 = '" . $db->f( 'address_2' ) . "', " ;
		$q .= "city = '" . $db->f( 'city' ) . "', " ;
		$q .= "state = '" . $db->f( 'state' ) . "', " ;
		$q .= "country = '" . $db->f( 'country' ) . "', " ;
		$q .= "zip = '" . $db->f( 'zip' ) . "', " ;
		$q .= "email = '" . $db->f( 'email' ) . "', " ;
		$q .= "extra_field_1 = '" . $db->f( 'extra_field_1' ) . "', " ;
		$q .= "extra_field_2 = '" . $db->f( 'extra_field_2' ) . "', " ;
		$q .= "extra_field_3 = '" . $db->f( 'extra_field_3' ) . "', " ;
		$q .= "extra_field_4 = '" . $db->f( 'extra_field_4' ) . "', " ;
		$q .= "extra_field_5 = '" . $db->f( 'extra_field_5' ) . "', " ;
		$q .= "bank_account_nr = '" . $db->f( 'bank_account_nr' ) . "', " ;
		$q .= "bank_name = '" . $db->f( 'bank_name' ) . "', " ;
		$q .= "bank_sort_code = '" . $db->f( 'bank_sort_code' ) . "', " ;
		$q .= "bank_iban = '" . $db->f( 'bank_iban' ) . "', " ;
		$q .= "bank_account_holder = '" . $db->f( 'bank_account_holder' ) . "', " ;
		$q .= "bank_account_type = '" . $db->f( 'bank_account_type' ) . "' " ;
		$q .= " WHERE order_id = '" . $this->order_id . "' AND address_type = 'BT'" ;
		$db2->query( $q ) ;
		$db2->next_record() ;
		
		// Delete ship to
		$q = "DELETE FROM #__{vm}_order_user_info " ;
		$q .= "WHERE order_id = '" . $this->order_id . "' AND address_type = 'ST'" ;
		$db2->query( $q ) ;
		$db2->next_record() ;
		
		$this->reload_from_db = 1 ;
		
		$vmLogger->info( JText::_( 'VM_ORDER_PRINT_BILL_TO_LBL' ) . JText::_( 'VM_ORDER_EDIT_SOMETHING_HAS_CHANGED' ) ) ;
	}
	
	/**************************************************************************
	 * name: change_ship_to
	 * created by: Kaltokri
	 * description: Change ship to
	 * parameters: none
	 * returns: none
	 **************************************************************************/
	function change_ship_to() {
		global  $vmLogger ;
		
		$ship_to = trim( JRequest::getVar( 'ship_to' ) ) ;
		$db = new ps_DB( ) ;
		
		// Delete ship to
		$q = "DELETE FROM #__{vm}_order_user_info " ;
		$q .= "WHERE order_id = '" . $this->order_id . "' AND address_type = 'ST'" ;
		$db->query( $q ) ;
		$db->next_record() ;
		
		$q = "SELECT * FROM #__{vm}_user_info " ;
		$q .= "WHERE user_info_id = '" . $ship_to . "'" ;
		$db->query( $q ) ;
		$db->next_record() ;
		
		if( $db->f( 'address_type' ) == 'ST' ) {
			// Find the required fields - 
			require_once (CLASSPATH . 'ps_userfield.php');
			$shippingFields = ps_userfield::getUserFields( '', false, '', true, true );
			$fieldlist='';
			// Skip the fields in the array
			// filter address_type just in case it will be in the Userfields some time
			$skipfields=array("email", "address_type");
			foreach($shippingFields as $shippingField) {
				// Build the list of fields
				if(!in_array($shippingField->name,$skipfields)) $fieldlist.=','.$shippingField->name;
			}
			
			// Ship to Address if applicable (copied from ps_checkout.php and changed)
			$q = "INSERT INTO `#__{vm}_order_user_info` (order_info_id,order_id,user_id, address_type $fieldlist) " ;
			$q .= "SELECT '', '".$this->order_id."', '" . $db->f( 'user_id' ) . "', 'ST' ".$fieldlist." FROM #__{vm}_user_info WHERE user_id='" . $db->f( 'user_id' ) . "' AND user_info_id='" . $ship_to . "' AND address_type='ST'" ;
			$db->query( $q ) ;
			$db->next_record() ;
		}
		$this->reload_from_db = 1 ;
		
		$vmLogger->info( JText::_( 'VM_ORDER_PRINT_SHIP_TO_LBL' ) . JText::_( 'VM_ORDER_EDIT_SOMETHING_HAS_CHANGED' ) ) ;
	}
	
	/**************************************************************************
	 * name: change_customer_note
	 * created by: kaltokri
	 * description: Change order customer_note
	 * parameters: none
	 * returns: none
	 **************************************************************************/
	function change_customer_note() {
		global  $vmLogger ;
		
		$db = new ps_DB( ) ;
		$customer_note = trim( JRequest::getVar( 'customer_note' ) ) ;
		
		// Update order
		$q = "UPDATE #__{vm}_orders " ;
		$q .= "SET customer_note = '" . $customer_note . "' " ;
		$q .= "WHERE order_id = '" . $this->order_id . "'" ;
		$db->query( $q ) ;
		$db->next_record() ;
		$this->reload_from_db = 1 ;
		
		$vmLogger->info( JText::_( 'VM_ORDER_PRINT_CUSTOMER_NOTE' ) . JText::_( 'VM_ORDER_EDIT_SOMETHING_HAS_CHANGED' ) ) ;
		$this->recalc_order( $this->order_id ) ;
	}
	
	/**************************************************************************
	 * name: change_standard_shipping
	 * created by: ingemar
	 * description: Change order shipping rate
	 * parameters: none
	 * returns: none
	 **************************************************************************/
	function change_standard_shipping() {
		global  $vmLogger ;
		
		$db = new ps_DB( ) ;
		$shipping = trim( JRequest::getVar( 'shipping' ) ) ;
		$q = "SELECT shipping_rate_name, shipping_carrier_name, shipping_rate_value, ((tax_rate + 1) *shipping_rate_value) AS shipping_total FROM #__{vm}_shipping_rate, #__{vm}_tax_rate, #__{vm}_shipping_carrier WHERE shipping_carrier_id = shipping_rate_carrier_id AND tax_rate_id = shipping_rate_vat_id and shipping_rate_id = '" . addslashes( $shipping ) . "'" ;
		$db->query( $q ) ;
		if( ! $db->next_record() ) {
			print "<h1>Invalid shipping id: $shipping</h1>" ;
			return ;
		}
		$shipping_carrier = $db->f( 'shipping_carrier_name' ) ;
		$shipping_name = $db->f( 'shipping_rate_name' ) ;
		$shipping_rate = $db->f( 'shipping_rate_value' ) ;
		$shipping_tax = $db->f( 'shipping_total' ) - $db->f( 'shipping_rate_value' ) ;
		$shipping_total = $db->f( 'shipping_total' ) ;
		$shipping_method = "standard_shipping|$shipping_carrier|$shipping_name|" . round( $shipping_total, 2 ) . "|$shipping" ;
		
		// Update order
		$q = "UPDATE #__{vm}_orders " ;
		$q .= "SET order_total = order_total - order_shipping - order_shipping_tax + " . $shipping_rate . " + " . $shipping_tax . ", " ;
		$q .= "order_shipping = " . $shipping_rate . ", " ;
		$q .= "order_shipping_tax =  " . $shipping_tax . ", " ;
		$q .= "ship_method_id = '" . addslashes( $shipping_method ) . "'" ;
		$q .= " WHERE order_id = '" . $this->order_id . "'" ;
		$db->query( $q ) ;
		$db->next_record() ;
		$this->reload_from_db = 1 ;
		
		$vmLogger->info( JText::_( 'VM_ORDER_PRINT_SHIPPING_MODE_LBL' ) . JText::_( 'VM_ORDER_EDIT_SOMETHING_HAS_CHANGED' ) ) ;
	}
	
	/**************************************************************************
	 * name: change_shipping
	 * created by: Greg
	 * description: Change order shipping
	 * parameters:
	 * returns:
	 **************************************************************************/
	function change_shipping( $order_id, $shipping ) {
		
		if( ! is_numeric( $shipping ) ) {
			return - 1 ;
		}
		
		$db = new ps_DB( ) ;
		$q = "UPDATE #__{vm}_orders SET " ;
		$q .= "order_shipping =  '" . $shipping . "' " ;
		$q .= "WHERE order_id = '" . $order_id . "'" ;
		$db->query( $q ) ;
		$db->next_record() ;
		
		$this->recalc_order( $order_id ) ;
		$this->reload_from_db = 1 ;
	}

	/**************************************************************************
	 * name: change_shipping_tax
	 * created by: Greg
	 * description: Change order shipping tax
	 * parameters:
	 * returns:
	 **************************************************************************/
	function change_shipping_tax( $order_id, $shipping_tax ) {
		
		if( ! is_numeric( $shipping_tax ) ) {
			return - 1 ;
		}
		
		$db = new ps_DB( ) ;
		$q = "UPDATE #__{vm}_orders SET " ;
		$q .= "order_shipping_tax =  '" . $shipping_tax . "' " ;
		$q .= "WHERE order_id = '" . $order_id . "'" ;
		$db->query( $q ) ;
		$db->next_record() ;
		
		$this->recalc_order( $order_id ) ;
		$this->reload_from_db = 1 ;
	}

	/**************************************************************************
	 * name: change_discount
	 * created by: ingemar
	 * description: Change order discount
	 * parameters:
	 * returns:
	 **************************************************************************/
	function change_discount( $order_id, $discount ) {
		
		if( ! is_numeric( $discount ) ) {
			return - 1 ;
		}
		
		$db = new ps_DB( ) ;
		$q = "UPDATE #__{vm}_orders SET " ;
		$q .= "order_discount =  '" . $discount . "' " ;
		$q .= "WHERE order_id = '" . $order_id . "'" ;
		$db->query( $q ) ;
		$db->next_record() ;
		
		$this->recalc_order( $order_id ) ;
		$this->reload_from_db = 1 ;
	}
	
	/**************************************************************************
	 * name: change_coupon_discount
	 * created by: ingemar
	 * description: Change order coupon discount
	 * parameters:
	 * returns:
	 **************************************************************************/
	function change_coupon_discount( $order_id, $discount ) {
		
		if( ! is_numeric( $discount ) ) {
			return - 1 ;
		}
		
		// Update order
		$db = new ps_DB( ) ;
		$q = "UPDATE #__{vm}_orders SET " ;
		$q .= "coupon_discount =  '" . $discount . "' " ;
		$q .= "WHERE order_id = '" . $order_id . "'" ;
		$db->query( $q ) ;
		$db->next_record() ;
		
		$this->recalc_order( $order_id ) ;
		$this->reload_from_db = 1 ;
	}
	
	/**************************************************************************
	 * name: change_delete_item
	 * created by: nfischer
	 * description: Delete an item
	 * parameters:
	 * returns:
	 **************************************************************************/
	function change_delete_item( $order_id, $order_item_id ) {
		global  $vmLogger ;
		
		if( ! is_numeric( $order_item_id ) ) {
			return - 1 ;
		}
		
		$db = new ps_DB( ) ;
		$q = "SELECT product_id, product_quantity " ;
		$q .= "FROM #__{vm}_order_item WHERE order_id = '" . $order_id . "' " ;
		$q .= "AND order_item_id = '" . addslashes( $order_item_id ) . "'" ;
		$db->query( $q ) ;
		$db->next_record() ;
		
		$product_id = $db->f( 'product_id' ) ;
		$diff = 0 - $db->f( 'product_quantity' ) ;
		
		// Delete item
		$q = "DELETE FROM #__{vm}_order_item " ;
		$q .= "WHERE order_item_id = '" . addslashes( $order_item_id ) . "'" ;
		$db->query( $q ) ;
		$db->next_record() ;
		
		// Update Stock Level and Product Sales
		$q = "UPDATE #__{vm}_product " ;
		$q .= "SET product_in_stock = product_in_stock - " . $diff ;
		$q .= " WHERE product_id = '" . $product_id . "'" ;
		$db->query( $q ) ;
		$db->next_record() ;
		
		// Update amount of saled items of this products
		$q = "UPDATE #__{vm}_product " ;
		$q .= "SET product_sales = product_sales + " . $diff ;
		$q .= " WHERE product_id='" . $product_id . "'" ;
		$db->query( $q ) ;
		$db->next_record() ;
		
		$this->recalc_order( $order_id ) ;
		$this->reload_from_db = 1 ;
	}
	
	/**************************************************************************
	 * name: change_item_quantity
	 * created by: nfischer
	 * description: Delete an item
	 * parameters:
	 * returns:
	 **************************************************************************/
	function change_item_quantity( $order_id, $order_item_id, $quantity ) {
		
		if( ! is_numeric( $quantity ) || $quantity < 1 ) {
			return - 1 ;
		}
		
		$db = new ps_DB( ) ;
		$q = "SELECT product_id, product_quantity " ;
		$q .= "FROM #__{vm}_order_item WHERE order_id = '" . $order_id . "' " ;
		$q .= "AND order_item_id = '" . addslashes( $order_item_id ) . "'" ;
		$db->query( $q ) ;
		$db->next_record() ;
		
		$product_id = $db->f( 'product_id' ) ;
		$diff = $quantity - $db->f( 'product_quantity' ) ;
		$timestamp = time() + ($mosConfig_offset * 60 * 60) ;
		
		// Update quantity of item
		$q = "UPDATE #__{vm}_order_item " ;
		$q .= "SET product_quantity = " . $quantity . ", " ;
		$q .= "mdate = " . $timestamp . " " ;
		$q .= "WHERE order_item_id = '" . addslashes( $order_item_id ) . "'" ;
		$db->query( $q ) ;
		$db->next_record() ;
		
		// Update Stock Level and Product Sales
		$q = "UPDATE #__{vm}_product " ;
		$q .= "SET product_in_stock = product_in_stock - " . $diff ;
		$q .= " WHERE product_id = '" . $product_id . "'" ;
		$db->query( $q ) ;
		$db->next_record() ;
		
		$q = "UPDATE #__{vm}_product " ;
		$q .= "SET product_sales= product_sales + " . $diff ;
		$q .= " WHERE product_id='" . $product_id . "'" ;
		$db->query( $q ) ;
		$db->next_record() ;
		
		$this->recalc_order( $order_id ) ;
		$this->reload_from_db = 1 ;
	}
	
	/**************************************************************************
	 * name: add_product
	 * created by: nfischer
	 * description: Add a new product to an existing order
	 * parameters:
	 * returns:
	 **************************************************************************/
	function add_product() {
		global  $vmLogger ;
		
		require_once (CLASSPATH . 'ps_product_attribute.php') ;
		require_once (CLASSPATH . 'ps_product.php') ;
		
		$ps_product_attribute = new ps_product_attribute( ) ;
		$ps_product = new ps_product( ) ;
		
		$product_id = JRequest::getVar(  'product_id' ) ;
		$order_item_id = JRequest::getVar(  'order_item_id' ) ;
		$add_product_validate = JRequest::getVar(  'add_product_validate' ) ;
		$d = $_REQUEST ;
		
		// Check if quantity is a numeric value
		if( $add_product_validate == 1 ) {
			$quantity = trim( JRequest::getVar( 'product_quantity' ) ) ;
			if( ! is_numeric( $quantity ) || $quantity < 1 ) {
				$vmLogger->err( JText::_( 'VM_ORDER_EDIT_ERROR_QUANTITY_MUST_BE_HIGHER_THAN_0' ) ) ;
				$add_product_validate = 0 ;
			}
		}
		
		if( $add_product_validate == 1 ) {
			$result_attributes = $ps_product_attribute->cartGetAttributes( $d ) ;
			
			$dbp = new ps_DB( ) ;
			$q = "SELECT vendor_id, product_in_stock,product_sales,product_parent_id, product_sku, product_name FROM #__{vm}_product WHERE product_id='$product_id'" ;
			$dbp->query( $q ) ;
			$dbp->next_record() ;
			$vendor_id = $dbp->f( "vendor_id" ) ;
			$product_sku = $dbp->f( "product_sku" ) ;
			$product_name = $dbp->f( "product_name" ) ;
			$product_parent_id = $dbp->f( "product_parent_id" ) ;
			
			// On r�cup�re le prix exact du produit
			$product_price_arr = $this->get_adjusted_attribute_price( $product_id, $quantity, $d["description"], $result_attributes ) ;
			$product_price = $product_price_arr["product_price"] ;
			$my_taxrate = $ps_product->get_product_taxrate( $product_id ) ;
			
			$description = $d["description"] ;
			
			$product_final_price = round( ($product_price * ($my_taxrate + 1)), 2 ) ;
			$product_currency = $product_price_arr["product_currency"] ;
			
			$db = new ps_DB( ) ;
			
			if( $product_parent_id > 0 ) {
				$q = "SELECT attribute_name, attribute_value, product_id " ;
				$q .= "FROM #__{vm}_product_attribute WHERE " ;
				$q .= "product_id='" . $product_id . "'" ;
				$db->setQuery( $q ) ;
				$db->query() ;
				while( $db->next_record() ) {
					$description .= $db->f( "attribute_name" ) . ": " . $db->f( "attribute_value" ) . "; " ;
				}
			}
			
			$q = "SELECT * FROM #__{vm}_order_item " ;
			$q .= " WHERE order_id=" . $this->order_id ;
			$db->query( $q ) ;
			$db->next_record() ;
			$user_info_id = $db->f( "user_info_id" ) ;
			$order_status = $db->f( "order_status" ) ;
			
			$timestamp = time() + ($mosConfig_offset * 60 * 60) ;
			$q = "SELECT order_item_id, product_quantity " ;
			$q .= "FROM #__{vm}_order_item WHERE order_id = '" . $this->order_id . "' " ;
			$q .= "AND product_id = '" . $product_id . "' " ;
			$q .= "AND product_attribute = '" . addslashes( $description) . "'" ;
			$db->query( $q ) ;
		
			if ($db->next_record()) {
				$this->change_item_quantity( $this->order_id, $db->f('order_item_id'), ($quantity + (int)$db->f('product_quantity')) );
			} else {
			
				$q = "INSERT INTO #__{vm}_order_item " ;
				$q .= "(order_id, user_info_id, vendor_id, product_id, order_item_sku, order_item_name, " ;
				$q .= "product_quantity, product_item_price, product_final_price, " ;
				$q .= "order_item_currency, order_status, product_attribute, cdate, mdate) " ;
				$q .= "VALUES ('" ;
				$q .= $this->order_id . "', '" ;
				$q .= $user_info_id . "', '" ;
				$q .= $vendor_id . "', '" ;
				$q .= $product_id . "', '" ;
				$q .= $product_sku . "', '" ;
				$q .= $product_name . "', '" ;
				$q .= $quantity . "', '" ;
				$q .= $product_price . "', '" ;
				$q .= $product_final_price . "', '" ;
				$q .= $product_currency . "', '" ;
				$q .= $order_status . "', '" ;
				// added for advanced attribute storage
				$q .= addslashes( $description ) . "', '" ;
				// END advanced attribute modifications
				$q .= $timestamp . "','" ;
				$q .= $timestamp . "'" ;
				$q .= ")" ;
				
				$db->query( $q ) ;
				$db->next_record() ;
			}
			// Update Stock Level and Product Sales
			$q = "UPDATE #__{vm}_product " ;
			$q .= "SET product_in_stock = product_in_stock - " . $quantity.",
							product_sales= product_sales + " . $quantity ;
			$q .= " WHERE product_id='" . $product_id . "'" ;
			$db->query( $q );
			
			$this->recalc_order( $this->order_id ) ;
			$this->reload_from_db = 1 ;
			
			$vmLogger->info( JText::_( 'VM_ORDER_EDIT_PRODUCT_ADDED' ) ) ;
		}
	
	}
	
	/**************************************************************************
	 * name: get_price
	 * created by: nfischer
	 * description: Give the price of a product
	 * parameters: $product_id, $quantity ,$check_multiple_prices=false, $result_attributes
	 * returns: Price of the product
	 **************************************************************************/
	function get_price( $product_id, $quantity, $check_multiple_prices = false, $result_attributes ) {
		if( $check_multiple_prices ) {
			$db = new ps_DB( ) ;
			
			// Get the vendor id for this product.
			$q = "SELECT vendor_id FROM #__{vm}_product WHERE product_id='$product_id'" ;
			$db->setQuery( $q ) ;
			$db->query() ;
			$db->next_record() ;
			$vendor_id = $db->f( "vendor_id" ) ;
			
			$q = "SELECT svx.shopper_group_id, sg.shopper_group_discount FROM #__{vm}_shopper_vendor_xref svx, #__{vm}_orders o, #__{vm}_shopper_group sg" ;
			$q .= " WHERE svx.user_id=o.user_id AND sg.shopper_group_id=svx.shopper_group_id AND o.order_id=" . $this->order_id ;
			$db->query( $q ) ;
			$db->next_record() ;
			$shopper_group_id = $db->f( "shopper_group_id" ) ;
			$shopper_group_discount = $db->f( "shopper_group_discount" ) ;
			
			// Get the default shopper group id for this vendor
			$q = "SELECT shopper_group_id,shopper_group_discount FROM #__{vm}_shopper_group WHERE " ;
			$q .= "vendor_id='$vendor_id' AND `default`='1'" ;
			$db->setQuery( $q ) ;
			$db->query() ;
			$db->next_record() ;
			$default_shopper_group_id = $db->f( "shopper_group_id" ) ;
			$default_shopper_group_discount = $db->f( "shopper_group_discount" ) ;
			
			// Get the product_parent_id for this product/item
			$q = "SELECT product_parent_id FROM #__{vm}_product WHERE product_id='$product_id'" ;
			$db->setQuery( $q ) ;
			$db->query() ;
			$db->next_record() ;
			$product_parent_id = $db->f( "product_parent_id" ) ;
			
			$price_info = Array() ;
			if( ! $check_multiple_prices ) {
				/* Added for Volume based prices */
				// This is an important decision: we add up all product quantities with the same product_id,
				// regardless to attributes. This gives "real" volume based discount, because our simple attributes
				// depend on one and the same product_id
				

				$volume_quantity_sql = " AND (('$quantity' >= price_quantity_start AND '$quantity' <= price_quantity_end)
                                OR (price_quantity_end='0') OR ('$quantity' > price_quantity_end)) ORDER BY price_quantity_end DESC" ;
				/* End Addition */
			} else {
				$volume_quantity_sql = " ORDER BY price_quantity_start" ;
			}
			
			// Getting prices
			//
			// If the shopper group has a price then show it, otherwise
			// show the default price.
			if( ! empty( $shopper_group_id ) ) {
				$q = "SELECT product_price, product_price_id, product_currency FROM #__{vm}_product_price WHERE product_id='$product_id' AND " ;
				$q .= "shopper_group_id='$shopper_group_id' $volume_quantity_sql" ;
				
				$db->setQuery( $q ) ;
				$db->query() ;
				if( $db->next_record() ) {
					$price_info["product_price"] = $db->f( "product_price" ) ;
					if( $check_multiple_prices ) {
						$price_info["product_base_price"] = $db->f( "product_price" ) ;
						$price_info["product_has_multiple_prices"] = $db->num_rows() > 1 ;
					}
					$price_info["product_price_id"] = $db->f( "product_price_id" ) ;
					$price_info["product_currency"] = $db->f( "product_currency" ) ;
					$price_info["item"] = true ;
					$GLOBALS['product_info'][$product_id]['price'] = $price_info ;
					return $GLOBALS['product_info'][$product_id]['price'] ;
				}
			}
			// Get default price
			$q = "SELECT product_price, product_price_id, product_currency FROM #__{vm}_product_price WHERE product_id='$product_id' AND " ;
			$q .= "shopper_group_id='$default_shopper_group_id' $volume_quantity_sql" ;
			
			$db->setQuery( $q ) ;
			$db->query() ;
			if( $db->next_record() ) {
				$price_info["product_price"] = $db->f( "product_price" ) * ((100 - $shopper_group_discount) / 100) ;
				if( $check_multiple_prices ) {
					$price_info["product_base_price"] = $price_info["product_price"] ;
					$price_info["product_has_multiple_prices"] = $db->num_rows() > 1 ;
				}
				$price_info["product_price_id"] = $db->f( "product_price_id" ) ;
				$price_info["product_currency"] = $db->f( "product_currency" ) ;
				$price_info["item"] = true ;
				$GLOBALS['product_info'][$product_id]['price'] = $price_info ;
				return $GLOBALS['product_info'][$product_id]['price'] ;
			}
			
			// Maybe its an item with no price, check again with product_parent_id
			if( ! empty( $shopper_group_id ) ) {
				$q = "SELECT product_price, product_price_id, product_currency FROM #__{vm}_product_price WHERE product_id='$product_parent_id' AND " ;
				$q .= "shopper_group_id='$shopper_group_id' $volume_quantity_sql" ;
				$db->setQuery( $q ) ;
				$db->query() ;
				if( $db->next_record() ) {
					$price_info["product_price"] = $db->f( "product_price" ) ;
					if( $check_multiple_prices ) {
						$price_info["product_base_price"] = $db->f( "product_price" ) ;
						$price_info["product_has_multiple_prices"] = $db->num_rows() > 1 ;
					}
					$price_info["product_price_id"] = $db->f( "product_price_id" ) ;
					$price_info["product_currency"] = $db->f( "product_currency" ) ;
					$GLOBALS['product_info'][$product_id]['price'] = $price_info ;
					return $GLOBALS['product_info'][$product_id]['price'] ;
				}
			}
			$q = "SELECT product_price, product_price_id, product_currency FROM #__{vm}_product_price WHERE product_id='$product_parent_id' AND " ;
			$q .= "shopper_group_id='$default_shopper_group_id' $volume_quantity_sql" ;
			$db->setQuery( $q ) ;
			$db->query() ;
			if( $db->next_record() ) {
				$price_info["product_price"] = $db->f( "product_price" ) * ((100 - $shopper_group_discount) / 100) ;
				if( $check_multiple_prices ) {
					$price_info["product_base_price"] = $price_info["product_price"] ;
					$price_info["product_has_multiple_prices"] = $db->num_rows() > 1 ;
				}
				$price_info["product_price_id"] = $db->f( "product_price_id" ) ;
				$price_info["product_currency"] = $db->f( "product_currency" ) ;
				$GLOBALS['product_info'][$product_id]['price'] = $price_info ;
				return $GLOBALS['product_info'][$product_id]['price'] ;
			}
			// No price found
			$GLOBALS['product_info'][$product_id]['price'] = false ;
			return $GLOBALS['product_info'][$product_id]['price'] ;
		} else {
			return $GLOBALS['product_info'][$product_id]['price'] ;
		}
	}
	
	/**************************************************************************
	 * name: get_adjusted_attribute_price
	 * created by: nfischer
	 * description: Give the price of a product according to the attributes
	 * parameters: $product_id, $quantity ,$description='', $result_attributes
	 * returns: Price of the product
	 **************************************************************************/
	function get_adjusted_attribute_price( $product_id, $quantity, $description = '', $result_attributes ) {
		
		global $mosConfig_secret ;
		$auth = $_SESSION['auth'] ;
		$price = $this->get_price( $product_id, $quantity, true, $result_attributes ) ;
		$base_price = $price["product_price"] ;
		
		$setprice = 0 ;
		$set_price = false ;
		$adjustment = 0 ;
		
		// We must care for custom attribute fields! Their value can be freely given
		// by the customer, so we mustn't include them into the price calculation
		// Thanks to AryGroup@ua.fm for the good advice
		//***********************
		//***********************
		//***********************
		//***********************
		// A VOIR
		//***********************
		//***********************
		//***********************
		//***********************
		

		if( empty( $_REQUEST["custom_attribute_fields"] ) ) {
			if( ! empty( $_SESSION["custom_attribute_fields"] ) ) {
				$custom_attribute_fields = vmGet( $_SESSION, "custom_attribute_fields", Array() ) ;
				$custom_attribute_fields_check = vmGet( $_SESSION, "custom_attribute_fields_check", Array() ) ;
			} else
				$custom_attribute_fields = $custom_attribute_fields_check = Array() ;
		} else {
			$custom_attribute_fields = $_SESSION["custom_attribute_fields"] = JRequest::getVar(  "custom_attribute_fields", Array() ) ;
			$custom_attribute_fields_check = $_SESSION["custom_attribute_fields_check"] = JRequest::getVar(  "custom_attribute_fields_check", Array() ) ;
		}
		
		//***********************
		//***********************
		//***********************
		//***********************
		// A VOIR
		//***********************
		//***********************
		//***********************
		//***********************
		

		// if we've been given a description to deal with, get the adjusted price
		if( $description != '' ) { // description is safe to use at this point cause it's set to ''
			

			$attribute_keys = explode( ";", $description ) ;
			
			foreach( $attribute_keys as $temp_desc ) {
				
				$temp_desc = trim( $temp_desc ) ;
				// Get the key name (e.g. "Color" )
				$this_key = substr( $temp_desc, 0, strpos( $temp_desc, ":" ) ) ;
				
				if( in_array( $this_key, $custom_attribute_fields ) ) {
					if( @$custom_attribute_fields_check[$this_key] == md5( $mosConfig_secret . $this_key ) ) {
						// the passed value is valid, don't use it for calculating prices
						continue ;
					}
				}
				
				$i = 0 ;
				
				$start = strpos( $temp_desc, "[" ) ;
				$finish = strpos( $temp_desc, "]", $start ) ;
				
				$o = substr_count( $temp_desc, "[" ) ;
				$c = substr_count( $temp_desc, "]" ) ;
				//echo "open: $o<br>close: $c<br>\n";
				

				// check to see if we have a bracket
				if( True == is_int( $finish ) ) {
					$length = $finish - $start ;
					
					// We found a pair of brackets (price modifier?)
					if( $length > 1 ) {
						$my_mod = substr( $temp_desc, $start + 1, $length - 1 ) ;
						//echo "before: ".$my_mod."<br>\n";
						if( $o != $c ) { // skip the tests if we don't have to process the string
							if( $o < $c ) {
								$char = "]" ;
								$offset = $start ;
							} else {
								$char = "[" ;
								$offset = $finish ;
							}
							$s = substr_count( $my_mod, $char ) ;
							for( $r = 1 ; $r < $s ; $r ++ ) {
								$pos = strrpos( $my_mod, $char ) ;
								$my_mod = substr( $my_mod, $pos + 1 ) ;
							}
						}
						$oper = substr( $my_mod, 0, 1 ) ;
						
						$my_mod = substr( $my_mod, 1 ) ;
						
						// if we have a number, allow the adjustment
						if( true == is_numeric( $my_mod ) ) {
							// Now add or sub the modifier on
							if( $oper == "+" ) {
								$adjustment += $my_mod ;
							} else if( $oper == "-" ) {
								$adjustment -= $my_mod ;
							} else if( $oper == '=' ) {
								// NOTE: the +=, so if we have 2 sets they get added
								// this could be moded to say, if we have a set_price, then
								// calc the diff from the base price and start from there if we encounter
								// another set price... just a thought.
								

								$setprice += $my_mod ;
								$set_price = true ;
							}
						}
						$temp_desc = substr( $temp_desc, $finish + 1 ) ;
						$start = strpos( $temp_desc, "[" ) ;
						$finish = strpos( $temp_desc, "]" ) ;
					}
				}
				$i ++ ; // not necessary, but perhaps interesting? ;)
			}
		}
		
		// no set price was set from the attribs
		if( $set_price == false ) {
			$price["product_price"] = $base_price + $adjustment ;
		} else {
			// otherwise, set the price
			// add the base price to the price set in the attributes
			// then subtract the adjustment amount
			// we could also just add the set_price to the adjustment... not sure on that one.
			// $setprice += $adjustment;
			$setprice *= 1 - ($auth["shopper_group_discount"] / 100) ;
			$price["product_price"] = $setprice ;
		}
		
		// don't let negative prices get by, set to 0
		if( $price["product_price"] < 0 ) {
			$price["product_price"] = 0 ;
		}
		// Get the DISCOUNT AMOUNT
		$ps_product = new ps_product( ) ;
		$discount_info = $ps_product->get_discount( $product_id ) ;
		
		$my_taxrate = $ps_product->get_product_taxrate( $product_id ) ;
		
		if( ! empty( $discount_info["amount"] ) ) {
			if( $auth["show_price_including_tax"] == 1 ) {
				switch( $discount_info["is_percent"]) {
					case 0 :
						$price["product_price"] = (($price["product_price"] * ($my_taxrate + 1)) - $discount_info["amount"]) / ($my_taxrate + 1) ;
					break ;
					//case 1: $price["product_price"] = ($price["product_price"]*($my_taxrate+1) - $discount_info["amount"]/100*$price["product_price"])/($my_taxrate+1); break;
					case 1 :
						$price["product_price"] = ($price["product_price"] - $discount_info["amount"] / 100 * $price["product_price"]) ;
					break ;
				}
			} else {
				switch( $discount_info["is_percent"]) {
					case 0 :
						$price["product_price"] = (($price["product_price"]) - $discount_info["amount"]) ;
					break ;
					case 1 :
						$price["product_price"] = ($price["product_price"] - ($discount_info["amount"] / 100) * $price["product_price"]) ;
					break ;
				}
			}
		}
		
		return $price ;
	}
	
	/**************************************************************************
	 * name: change_product_item_price
	 * created by: kaltokri
	 * description: change product item price
	 * parameters: none
	 * returns: none
	 **************************************************************************/
	function change_product_item_price() {
		require_once (CLASSPATH . 'ps_product.php') ;
		global  $vmLogger ;
		
		$ps_product = new ps_product( ) ;
		
		$order_item_id = JRequest::getVar( 'order_item_id' ) ;
		$product_item_price_new = trim( JRequest::getVar( 'product_item_price' ) ) ;
		$product_final_price_new = trim( JRequest::getVar( 'product_final_price' ) ) ;
		
		$db = new ps_DB( ) ;
		
		$q = "SELECT product_id, product_quantity, product_final_price, product_item_price, product_final_price - product_item_price AS item_tax " ;
		$q .= "FROM #__{vm}_order_item WHERE order_id = '" . $this->order_id . "' " ;
		$q .= "AND order_item_id = '" . addslashes( $order_item_id ) . "'" ;
		$db->query( $q ) ;
		$db->next_record() ;
		
		$product_id = $db->f( 'product_id' ) ;
		$timestamp = time() + ($mosConfig_offset * 60 * 60) ;
		$my_taxrate = $ps_product->get_product_taxrate( $product_id ) ;
		
		$product_item_price = $db->f( 'product_item_price' ) ;
		$product_final_price = $db->f( 'product_final_price' ) ;
		$quantity = $db->f( 'product_quantity' ) ;
		
		if( is_numeric( $product_item_price_new ) ) {
			$product_final_price_new = round( ($product_item_price_new * ($my_taxrate + 1)), 2 ) ;
		
		}
		$product_item_price_new = ($product_final_price_new / ($my_taxrate + 1)) ;
		
		$q = "UPDATE #__{vm}_order_item " ;
		$q .= "SET product_item_price = " . $product_item_price_new . ", " ;
		$q .= "product_final_price = " . $product_final_price_new . ", " ;
		$q .= "mdate = " . $timestamp . " " ;
		$q .= "WHERE order_item_id = '" . addslashes( $order_item_id ) . "'" ;
		$db->query( $q ) ;
		$db->next_record() ;
		
		$this->recalc_order( $this->order_id ) ;
		$this->reload_from_db = 1 ;
		
		$vmLogger->info( JText::_( 'VM_ORDER_PRINT_PRICE' ) . JText::_( 'VM_ORDER_EDIT_SOMETHING_HAS_CHANGED' ) ) ;
	}
	
	/**************************************************************************
	 * name: change_payment
	 * created by: kaltokri
	 * description: Change payment
	 **************************************************************************/
	function change_payment( $order_id, $new_payment_method_id ) {
		
		$db = new ps_DB( ) ;
		
		// Get the old payment_method_id to get payment_discount in next step
		$q = "SELECT * FROM #__{vm}_order_payment" ;
		$q .= " WHERE order_id = '" . $order_id . "'" ;
		$db->query( $q ) ;
		$old_payment_method_id = $db->f( 'payment_method_id' ) ;
		
		// Get the old payment_discount
		$q = "SELECT * FROM #__{vm}_payment_method" ;
		$q .= " WHERE payment_method_id = '" . $old_payment_method_id . "'" ;
		$db->query( $q ) ;
		$old_payment_discount = $db->f( 'discount' ) ;
		
		// Get the new payment_dicount
		$q = "SELECT * FROM #__{vm}_payment_method" ;
		$q .= " WHERE payment_method_id = '" . $new_payment_method_id . "'" ;
		$db->query( $q ) ;
		$new_payment_discount = $db->f( 'discount' ) ;
		
		// Update order_payment
		$q = "UPDATE #__{vm}_order_payment " ;
		$q .= "SET payment_method_id = '" . $new_payment_method_id . "'" ;
		$q .= "WHERE order_id = '" . $order_id . "'" ;
		$db->query( $q ) ;
		$db->next_record() ;
		
		// Get the old order_discount
		$q = "SELECT * FROM #__{vm}_orders" ;
		$q .= " WHERE order_id = '" . $order_id . "'" ;
		$db->query( $q ) ;
		$old_order_discount = $db->f( 'order_discount' ) ;
		
		// Update order
		$q = "UPDATE #__{vm}_orders SET " ;
		$q .= "order_discount = order_discount + " . $new_payment_discount . " - " . $old_payment_discount ;
		$q .= " WHERE order_id = '" . $order_id . "'" ;
		$db->query( $q ) ;
		$db->next_record() ;
		
		$this->recalc_order( $order_id ) ;
		$this->reload_from_db = 1 ;
	}

}

if( JRequest::getVar(  'page' ) == 'order.order_print' ) {
	$ps_order_change = new ps_order_change( $order_id ) ;
	if( JRequest::getVar( 'change_bill_to' ) != '' )
		$ps_order_change->change_bill_to() ;
	elseif( JRequest::getVar( 'change_ship_to' ) != '' )
		$ps_order_change->change_ship_to() ;
	elseif( JRequest::getVar(  'change_customer_note' ) != '' )
		$ps_order_change->change_customer_note() ;
	elseif( JRequest::getVar(  'change_standard_shipping' ) != '' )
		$ps_order_change->change_standard_shipping() ;
	elseif( JRequest::getVar(  'change_shipping' ) != '' )
		$ps_order_change->change_shipping( $order_id, vmRequest::getFloat( 'order_shipping' ) );
	elseif( JRequest::getVar(  'change_shipping_tax' ) != '' )
		$ps_order_change->change_shipping_tax( $order_id, vmRequest::getFloat( 'order_shipping_tax' ) );
	elseif( JRequest::getVar(  'change_discount' ) != '' )
		if( $ps_order_change->change_discount( $order_id, trim( JRequest::getVar(  'order_discount' ) ) ) ) {
			$vmLogger->err( "Invalid Order Item ID!" ) ;
		} else {
			$vmLogger->info( JText::_( 'VM_COUPON_DISCOUNT' ) . JText::_( 'VM_ORDER_EDIT_SOMETHING_HAS_CHANGED' ) ) ;
		}
	
	elseif( JRequest::getVar(  'change_coupon_discount' ) != '' )
		if( $ps_order_change->change_coupon_discount( $order_id, trim( JRequest::getVar(  'coupon_discount' ) ) ) ) {
			$vmLogger->err( "Discount is not a number!" ) ;
		} else {
			$vmLogger->info( JText::_( 'VM_COUPON_DISCOUNT' ) . JText::_( 'VM_ORDER_EDIT_SOMETHING_HAS_CHANGED' ) ) ;
		}
	
	elseif( JRequest::getVar(  'change_delete_item' ) != '' )
		if( $ps_order_change->change_delete_item( $order_id, JRequest::getVar(  'order_item_id' ) ) ) {
			$vmLogger->err( "Discount is not a number!" ) ;
		} else {
			$vmLogger->info( JText::_( 'VM_ORDER_EDIT_PRODUCT_DELETED' ) ) ;
		}
	
	elseif( JRequest::getVar(  'change_item_quantity' ) != '' )
		if( $ps_order_change->change_item_quantity( $order_id, JRequest::getVar(  'order_item_id' ), trim( JRequest::getVar(  'product_quantity' ) ) ) ) {
			$vmLogger->err( JText::_( 'VM_ORDER_EDIT_ERROR_QUANTITY_MUST_BE_HIGHER_THAN_0' ) ) ;
		} else {
			$vmLogger->info( JText::_( 'VM_ORDER_EDIT_QUANTITY_UPDATED' ) ) ;
		}
	
	elseif( JRequest::getVar(  'add_product' ) != '' )
		$ps_order_change->add_product() ;
	
	elseif( JRequest::getVar(  'change_product_item_price' ) != '' )
		$ps_order_change->change_product_item_price() ;
	elseif( JRequest::getVar( 'change_product_final_price' ) != '' )
		$ps_order_change->change_product_item_price() ;
	
	elseif( JRequest::getVar(  'change_payment' ) != '' )
		if( $ps_order_change->change_payment( $order_id, JRequest::getVar(  'new_payment_id' ) ) ) {
		
		} else {
			$vmLogger->info( JText::_( 'VM_PAYMENT' ) . JText::_( 'VM_ORDER_EDIT_SOMETHING_HAS_CHANGED' ) ) ;
		}
	
	if( $ps_order_change->reload_from_db ) {
		$q = "SELECT * FROM #__{vm}_orders WHERE order_id='$order_id'" ;
		$db->query( $q ) ;
		$db->next_record() ;
	}
}
?>
