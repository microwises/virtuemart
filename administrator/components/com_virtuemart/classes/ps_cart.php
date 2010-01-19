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
 * The cart class is used to store products and carry them through the user's
 * session in the store.
 *
 */
class ps_cart {

	/**
	 * Calls the constructor
	 *
	 * @return array A fresh, empty cart or the old cart of a registered user
	 */
	function initCart() {
		global $cart;

		$db = new ps_DB();
		// If the user is logged in, we can try to retrieve the current cart from the database
        // We store the items in a new SESSION var
		if( $GLOBALS['auth']['user_id'] > 0 && empty($_SESSION['savedcart'])) {
			$q = 'SELECT `cart_content` FROM `#__{vm}_cart` WHERE `user_id`='.$GLOBALS['auth']['user_id'];
			$db->query( $q );
			if( $db->next_record() ) {
				// Fill the cart from the contents of the field cart_content, which holds a serialized array
				$contents = $db->f('cart_content');
                $_SESSION['savedcart'] = array();
				$_SESSION['savedcart'] = unserialize( $contents );
				
				// Now check if all products are still published and existant
				$products_in_cart = array();
				for ($i=0;$i<$_SESSION['savedcart']["idx"];$i++) {
					$products_in_cart[$_SESSION['savedcart'][$i]['product_id']] = (int)$_SESSION['savedcart'][$i]['product_id'];
				}
				if( !empty( $products_in_cart )) {
					$db->query('SELECT `product_id` FROM #__{vm}_product WHERE `product_id` IN('.implode(',', $products_in_cart ).') AND published=\'1\'' );
					while( $db->next_record() ) {
						unset( $products_in_cart[$db->f('product_id')] );
					}
					foreach ( $products_in_cart as $product_id ) {
						// Delete those products who have been unpublished or deleted meanwhile
						ps_cart::deleteSaved( array('product_id'=>$product_id,'description'=>''), true);
					}
				}
                // If Current cart is empty populate with saved cart
                if(@$_SESSION['cart']['idx'] == 0) {
                    $_SESSION['cart'] = $_SESSION['savedcart'];
                    $_SESSION['savedcart']['idx'] = 0;
                    ps_cart::saveCart();
                }
				return $_SESSION['cart'];
			}
		}
		// Register the cart
		if (empty($_SESSION['cart'])) {
			$cart = array();
			$cart['idx'] = 0;
			$cart['cart_vendor_id'] = 0;	
			
			$_SESSION['cart'] = $cart;
			return $cart;
		}
		else {
			//echo 'auth::user_id: '.$_SESSION['auth']['user_id'] . '; $GLOBALS['auth']['user_id']: '.print_r($my);
			if( ( @$_SESSION['auth']['user_id'] != $GLOBALS['auth']['user_id'] ) && empty( $GLOBALS['auth']['user_id'] )
			&& @$_GET['cartReset'] != 'N') {
				// If the user ID has changed (after logging out)
				// empty the cart!
				//$sess->emptySession();
				ps_cart::reset();
			}
		}
		return $_SESSION['cart'];
	}
	
	/**
 	* adds an item to the shopping cart
 	* @author pablo
 	* @param array $d
 	*/
	function add(&$d) {
		global $sess, $cart, $vmLogger,$func;
		$GLOBALS['vmLogger']->info( 'ps_cart performed ADD $cart '.$cart);
		if(isset($_SESSION['cart'])){
			$cart = $_SESSION['cart'];
			if(isset($cart['cart_vendor_id'])){
				$cart_vendor_id = $cart['cart_vendor_id'];
			}		
		}else{
			JError::raiseError('CART_DOES_NOT_EXIST', JText::_('Cart does not exist, cant add product'));
//			JError::raiseError('SOME_ERROR', JText('Module requires the Some Extension component'));
		}
		JError::raiseWarning('Something', JText::_('Cart add product'));
		if(MAX_VENDOR_PRO_CART>0){
			if( !empty( $d['product_id'])){
				require_once(CLASSPATH.'ps_product.php');
				$vendor_id = ps_product::get_vendor_id_ofproduct($d['product_id']);
				$GLOBALS['vmLogger']->info( '$cart_vendor_id should be set to '.$vendor_id);
				if(isset($vendor_id )) {
					if(empty($cart_vendor_id)){
						//$GLOBALS['vmLogger']->debug( 'Set cart[cart_vendor_id] to '.$vendor_id);
						$cart['cart_vendor_id'] = $vendor_id;
					}else{
						if($cart_vendor_id!=$vendor_id){
							$GLOBALS['vmLogger']->info( 'Please finish your previous order first');
							return false;
						}else{
	//						$GLOBALS['vmLogger']->debug( 'Adding product to cart is all well done');
						}
					}	
				}else{
					$GLOBALS['vmLogger']->error( 'add to cart [vendor_id] '.$vendor_id.' failed no vendor_id foundt');
				}
			}else{
				$GLOBALS['vmLogger']->error( 'add to cart [product_id] empty');
			}
			$_SESSION['cart'] = $cart;
		}
//		$GLOBALS['vmLogger']->info( 'add '.$d["product_id"].'$cart_vendor_id '.$cart_vendor_id);

		
		$d = $GLOBALS['vmInputFilter']->process( $d );
		
		include_class("product");
		
		$db = new ps_DB;
		$ci = 0;
		$request_stock = "";
		$total_quantity = 0;
		$total_updated = 0;
		$total_deleted = 0;
		$_SESSION['last_page'] = "shop.product_details";
		

		if( !empty( $d['product_id']) && !isset($d["prod_id"])) {
			if( empty( $d['prod_id'] )) $d['prod_id'] = array();
			if( is_array($d['product_id'])) {
				$d['prod_id'] = array_merge( $d['prod_id'], $d['product_id'] );
			} else {
				$d['prod_id'] = array_merge( $d['prod_id'], array( $d['product_id'] ) );
			}
		}
		//Check to see if a prod_id has been set
		if (!isset($d["prod_id"])) {
			return true;
		}
		$multiple_products = sizeof($d["prod_id"]);
		//Iterate through the prod_id's and perform an add to cart for each one
		if(@$d["overide_error"]) {
			$GLOBALS['page'] = 'shop.product_details';
			return true;
		}
		for ($ikey = 0; $ikey < $multiple_products; $ikey++) {

			// Create single array from multi array
			$key_fields=array_keys($d);
			foreach($key_fields as $key) {
				if(is_array($d[$key])) {
					$e[$key] = @$d[$key][$ikey];
				}
				else {
					$e[$key] = $d[$key];
				}
			}

			if ($multiple_products > 1 ) {
				$func = "cartUpdate";
			}
			$e['product_id'] = $d['product_id'];
			$e['Itemid'] = $d['Itemid'];
			// Standard ps_cart.php with $d changed to $e
			$product_id = $e["prod_id"];
			$quantity = (int)@$e['quantity'];

			// Check for negative quantity
			if ($quantity < 0) {
				$vmLogger->warning( JText::_('VM_CART_ERROR_NO_NEGATIVE',false) );
				return False;
			}

			if (!ereg("^[0-9]*$", $quantity)) {
				$vmLogger->warning( JText::_('VM_CART_ERROR_NO_VALID_QUANTITY',false) );
				return False;
			}
			// Check to see if checking stock quantity
			if (CHECK_STOCK) {
				$product_in_stock = ps_product::get_field( $product_id, 'product_in_stock');
				if (empty($product_in_stock)) {
					$product_in_stock = 0;
				}
				if ($quantity > $product_in_stock) {
					//Create an array for out of stock items and continue to next item
					$request_stock[$ci]['product_id'] = $product_id;
					$request_stock[$ci]['quantity'] = $quantity;
					$ci++;
					continue;
				}
			}

			// Check if product exists and is published
			if ( !ps_product::product_exists($product_id)) {
				$vmLogger->tip( JText::_('VM_CART_PRODUCT_NOTEXIST',false) );
				return false;
			}
			// Quick add of item
			$q = "SELECT product_id FROM #__{vm}_product WHERE ";
			$q .= "product_parent_id = ".(int)$product_id;
			$db->query ( $q );

			if ( $db->num_rows()) {
				$vmLogger->tip( JText::_('VM_CART_SELECT_ITEM',false) );
				return false;
			}

			// Check to see if we already have it
			$updated = 0;

			$result = ps_product_attribute::cartGetAttributes( $e);

			if ( ($result["attribute_given"] == false && !empty( $result["advanced_attribute_list"] ))
			|| ($multiple_products == 1 && ($result["custom_attribute_given"] == false && !empty( $result["custom_attribute_list"] ))) ) {
				$_REQUEST['flypage'] = ps_product::get_flypage($product_id);
				$GLOBALS['page'] = 'shop.product_details';
				$vmLogger->tip( JText::_('VM_CART_SELECT_ITEM',false) );
				return true;
			}

			//Check for empty custom field and quantity>0 for multiple addto
			//Normally means no info added to a custom field, but once added to a cart the quantity is automatically placed
			//If another item is added and the custom field is left blank for another product already added this will just ignore that item
			if ($multiple_products != 1 && $quantity != 0 && ($result["custom_attribute_given"] == false && !empty( $result["custom_attribute_list"] )))  {
				$vmLogger->tip( JText::_('VM_CART_SELECT_ITEM',false) );
				continue;
			}

			// Check for duplicate and do not add to current quantity
			for ($i=0;$i<$_SESSION["cart"]["idx"];$i++) {
				// modified for advanced attributes
				if ($_SESSION['cart'][$i]["product_id"] == $product_id
				&&
				$_SESSION['cart'][$i]["description"] == $e["description"]
				) {
					$updated = 1;
				}
			}
			list($min,$max) = ps_product::product_order_levels($product_id);
			If ($min!= 0 && $quantity !=0 && $quantity < $min) {
				eval( "\$msg = \"".JText::_('VM_CART_MIN_ORDER',false)."\";" );
				$vmLogger->warning( $msg );
				continue;
			}
			if ($max !=0 && $quantity !=0 && $quantity>$max) {
				eval( "\$msg = \"".JText::_('VM_CART_MAX_ORDER',false)."\";" );
				$vmLogger->warning( $msg );
				continue;
			}

			// If we did not update then add the item
			if ((!$updated) && ($quantity)){
				$k = $_SESSION['cart']["idx"];

				$_SESSION['cart'][$k]["quantity"] = $quantity;
//				$_SESSION['cart'][$k]["cart_vendor_id"] = $cart_vendor_id;
				$_SESSION['cart'][$k]["product_id"] = $product_id;
				$_SESSION['cart'][$k]["parent_id"] = $e["product_id"];
                $_SESSION['cart'][$k]["category_id"] = vmGet($e, 'category_id', 0 );
				// added for the advanced attribute modification
				$_SESSION['cart'][$k]["description"] = $e["description"];
				$_SESSION['cart']["idx"]++;
				$total_quantity += $quantity;
			}
			else {
				list($updated_prod,$deleted_prod) = $this->update( $e );
				$total_updated += $updated_prod;
				$total_deleted += $deleted_prod;
			}

			/* next 3 lines added by Erich for coupon code */
			/* if the cart was updated we gotta update any coupon discounts to avoid ppl getting free stuff */
			if( !empty( $_SESSION['coupon_discount'] )) {
				// Update the Coupon Discount !!
				require_once(CLASSPATH.'ps_coupon.php');
				ps_coupon::process_coupon_code($d);
			}
		} // End Iteration through Prod id's
		$cart = $_SESSION['cart'];
//		$_SESSION['cart'] = $cart;
		ps_cart::saveCart();

		// Ouput info message with cart update details /*
		if($total_quantity !=0 || $total_updated !=0 || $total_deleted !=0) {
			if( $total_quantity > 0 && $total_updated ==0 ) {
				$msg = JText::_('VM_CART_PRODUCT_ADDED',false);
			} else {
				$msg = JText::_('VM_CART_PRODUCT_UPDATED',false);
			}
			
			// Comment out the following line to turn off msg i.e. //$vmLogger->tip( $msg );
			$vmLogger->info( $msg );
		}
        else if (@$request_stock && vmIsXHR() ) {
            $vmLogger->tip( JText::_('VM_CART_GOTO_WAITING_LIST',false) );
		} else {
            $vmLogger->tip( JText::_('VM_CART_QUANTITY_EXCEEDED',false) );
        }
		// end cart update message */

		// Perform notification of out of stock items
		if (@$request_stock) {
			Global $notify;
			$_SESSION['notify'] = array();
			$_SESSION['notify']['idx'] = 0;
			$k=0;
			$notify = $_SESSION['notify'];
			foreach($request_stock as $request) {
				$_SESSION['notify'][$k]["prod_id"] = $request['product_id'];
				$_SESSION['notify'][$k]["quantity"] = $request['quantity'];
				$_SESSION['notify']['idx']++;
				$k++;
			}
			if( vmIsXHR() ) {
				$GLOBALS['vm_mainframe']->scriptRedirect( $sess->url( 'index.php?page=shop.waiting_list&product_id='.$product_id, true, false ) );
			} else {
				vmRedirect( $sess->url( 'index.php?page=shop.waiting_list&product_id='.$product_id, true, false ) );
			}
		}

		return True;
	}

	/**
	 * updates the quantity of a product_id in the cart
	 * @author pablo
	 * @param array $d
	 * @return boolean result of the update
	 */
	function update(&$d) {
		global  $vmLogger, $func, $page;
		
		include_class("product");

		$product_id = (int)$d["prod_id"];
		$quantity = isset($d["quantity"]) ? (int)$d["quantity"] : 1;
		$_SESSION['last_page'] = "shop.cart";

		// Check for negative quantity
		if ($quantity < 0) {
			$vmLogger->warning( JText::_('VM_CART_ERROR_NO_NEGATIVE',false) );
			return False;
		}

		if (!ereg("^[0-9]*$", $quantity)) {
			$vmLogger->warning( JText::_('VM_CART_ERROR_NO_VALID_QUANTITY',false) );
			return False;
		}

		if (!$product_id) {
			return false;
		}
		$deleted_prod = 0;
		$updated_prod = 0;
		if ($quantity == 0) {
			$deleted_prod = $this->delete($d);
		}
		else {
			for ($i=0;$i<$_SESSION['cart']["idx"];$i++) {
				// modified for the advanced attribute modification
				if ( ($_SESSION['cart'][$i]["product_id"] == $product_id )
				&&
				($_SESSION['cart'][$i]["description"] == $d["description"] )
				) {
					if( strtolower( $func ) == 'cartadd' ) {
						$quantity += $_SESSION['cart'][$i]["quantity"];
					}
					// Get min and max order levels
					list($min,$max) = ps_product::product_order_levels($product_id);
					If ($min!= 0 && $quantity < $min) {
						eval( "\$msg = \"".JText::_('VM_CART_MIN_ORDER',false)."\";" );
						$vmLogger->warning( $msg );
						return false;
					}
					if ($max !=0 && $quantity>$max) {
						eval( "\$msg = \"".JText::_('VM_CART_MAX_ORDER',false)."\";" );
						$vmLogger->warning( $msg );
						return false;
					}
					$quantity_options = ps_product::get_quantity_options($product_id);
					if( !empty( $quantity_options ) && !empty($quantity_options['quantity_step'])) {
						if( $quantity % $quantity_options['quantity_step'] > 0 ) {
							continue;
						}
					}
					// Remove deleted or unpublished products from the cart
					if ( !ps_product::product_exists($product_id)) {
						$this->delete(array('product_id', $product_id));
						continue;
					}

					// Check to see if checking stock quantity
					if (CHECK_STOCK) {
						$product_in_stock = ps_product::get_field( $product_id, 'product_in_stock');

						if (empty($product_in_stock)) $product_in_stock = 0;
						if (($quantity) > $product_in_stock) {
							Global $notify;
							$_SESSION['notify'] = array();
							$_SESSION['notify']['idx'] = 0;
							$k=0;
							$notify = $_SESSION['notify'];
							$_SESSION['notify'][$k]["prod_id"] = $product_id;
							$_SESSION['notify'][$k]["quantity"] = $quantity;
							$_SESSION['notify']['idx']++;

							$page = 'shop.waiting_list';

							return true;
						}
					}
					$_SESSION['cart'][$i]["quantity"] = $quantity;
					$updated_prod++;
				}
			}
		}
		if( !empty( $_SESSION['coupon_discount'] )) {
			// Update the Coupon Discount !!
			require_once(CLASSPATH.'ps_coupon.php');
			ps_coupon::process_coupon_code($d);
		}
		ps_cart::saveCart();
		return array($updated_prod,$deleted_prod);
	}

	/**
	 * deletes a given product_id from the cart
	 *
	 * @param array $d
	 * @param boolean $force Force the deletion of a product_id regardless of the description (=selected attributes)
	 * @return boolan Result of the deletion
	 */
	function delete($d, $force=false) {

		$temp = array();
		if( !empty( $d["prod_id"])) {
			$product_id = (int)$d["prod_id"];
		} else {
			$product_id = (int)$d["product_id"];
		}
		$deleted = 0;
		if (!$product_id) {
			$_SESSION['last_page'] = "shop.cart";
			return False;
		}

		$j = 0;
		for ($i=0;$i<$_SESSION['cart']["idx"];$i++) {
			// modified for the advanced attribute modification
			if ( ($_SESSION['cart'][$i]["product_id"] == $product_id )
			&& ($_SESSION['cart'][$i]["description"] == $d["description"] || $force )
			) {
				$deleted = $_SESSION['cart'][$i]['quantity'];
			}
			if ( ($_SESSION['cart'][$i]["product_id"] != $product_id
			|| $_SESSION['cart'][$i]["description"] != stripslashes($d["description"])
			 )
			) {
				if( ($_SESSION['cart'][$i]["product_id"] == $product_id && $force )) {
					continue;
				}
				$temp[$j++] = $_SESSION['cart'][$i];
			}

		}
		$temp["idx"] = $j;
		$_SESSION['cart'] = $temp;
		ps_cart::saveCart();

		return $deleted;
	}
    
    /**
	 * deletes a given product_id from the saved cart
	 *
	 * @param array $d
	 * @return boolan Result of the deletion
	 */
	function deleteSaved($d,$force = false) {
    
		$temp = array();
		if( !empty( $d["prod_id"])) {
			$product_id = (int)$d["prod_id"];
		} else {
			$product_id = (int)$d["product_id"];
		}
		$deleted = 0;
		if (!$product_id) {
			$_SESSION['last_page'] = "shop.cart";
			return False;
		}

		
		$j = 0;
		for ($i=0;$i<$_SESSION['savedcart']["idx"];$i++) {
			// modified for the advanced attribute modification
			if ( ($_SESSION['savedcart'][$i]["product_id"] != $product_id
			|| $_SESSION['savedcart'][$i]["description"] != $d["description"]
			 )
			) {
				if( ($_SESSION['savedcart'][$i]["product_id"] == $product_id && $force )) {
					continue;
				}
				$temp[$j++] = $_SESSION['savedcart'][$i];
			}

		}
		$temp["idx"] = $j;
		$_SESSION['savedcart'] = $temp;

		return true;
	}
    
    	/**
	 * updates the quantity of a product_id in the cart
	 * @author pablo
	 * @param array $d
	 * @return boolean result of the update
	 */
	function updateSaved(&$d) {
		global  $vmLogger, $page;
		$d = $GLOBALS['vmInputFilter']->process( $d );
		include_class("product");

		$db = new ps_DB;
		$product_id = $d["prod_id"];
		$quantity = isset($d["quantity"]) ? (int)$d["quantity"] : 1;
		$_SESSION['last_page'] = "shop.savedcart";

		// Check for negative quantity
		if ($quantity < 0) {
			$vmLogger->warning( JText::_('VM_CART_ERROR_NO_NEGATIVE',false) );
			return False;
		}

		if (!ereg("^[0-9]*$", $quantity)) {
			$vmLogger->warning( JText::_('VM_CART_ERROR_NO_VALID_QUANTITY',false) );
			return False;
		}

		if (!$product_id) {
			return false;
		}
		if ($quantity == 0) {
			$deleted_prod = $this->deleteSaved($d);
		}
		else {
			for ($i=0;$i<$_SESSION['savedcart']["idx"];$i++) {
				// modified for the advanced attribute modification
				if ( ($_SESSION['savedcart'][$i]["product_id"] == $product_id )
				&&
				($_SESSION['savedcart'][$i]["description"] == $d["description"])
				) {
					// Get min and max order levels
					list($min,$max) = ps_product::product_order_levels($product_id);
					If ($min!= 0 && $quantity < $min) {
						eval( "\$msg = \"".JText::_('VM_CART_MIN_ORDER',false)."\";" );
						$vmLogger->warning( $msg );
						return false;
					}
					if ($max !=0 && $quantity>$max) {
						eval( "\$msg = \"".JText::_('VM_CART_MAX_ORDER',false)."\";" );
						$vmLogger->warning( $msg );
						return false;
					}

					// Check to see if checking stock quantity
					if (CHECK_STOCK) {
						$q = "SELECT product_in_stock ";
						$q .= "FROM #__{vm}_product where product_id=";
						$q .= $product_id;
						$db->query($q);
						$db->next_record();
						$product_in_stock = $db->f("product_in_stock");
						if (empty($product_in_stock)) $product_in_stock = 0;
						if (($quantity) > $product_in_stock) {
							Global $notify;
							$_SESSION['notify'] = array();
							$_SESSION['notify']['idx'] = 0;
							$k=0;
							$notify = $_SESSION['notify'];
							$_SESSION['notify'][$k]["prod_id"] = $product_id;
							$_SESSION['notify'][$k]["quantity"] = $quantity;
							$_SESSION['notify']['idx']++;

							$page = 'shop.waiting_list';

							return true;
						}
					}
					$_SESSION['savedcart'][$i]["quantity"] = $quantity;
				}
			}
		}
		return true;
	}

	/**
	 * Saves the cart array into the table jos_vm_cart
	 *
	 */
	function saveCart() {
		global $db;
		if( $GLOBALS['auth']['user_id'] > 0 ) {
			$cart_contents = serialize( $_SESSION['cart'] );
			//$cart_contents = mysql_real_escape_string( $cart_contents );
			$q = "REPLACE INTO `#__{vm}_cart` (`user_id`, `cart_content` ) VALUES ( ".$GLOBALS['auth']['user_id'].", '$cart_contents' )";
			$db->query( $q );
		}
	}
    
    /**
	 * Replaces the cart array with the saved cart
	 *
	 */
	function replaceCart(&$d) {
		global $cart,$page;
		if( $GLOBALS['auth']['user_id'] > 0 ) {
			$this->reset();
            $_SESSION['cart'] = $_SESSION['savedcart'];
            $page = "shop.cart";
            $_SESSION['savedcart']['idx']=0;
            $cart = $_SESSION['cart'];
            $this->saveCart();
            return True;
		}
	}
    
    /**
	 * Merges the cart array with the saved cart
	 *
	 */
	function mergeSaved(&$d) {
		global $my, $cart,$page,$func;
		if( $GLOBALS['auth']['user_id'] > 0 ) {
            // Iterate through saved cart
            for($i=0;$i<$_SESSION['savedcart']['idx'];$i++) {
                $updated = false;
                // iterate through actual cart
                for($k=0;$k<$_SESSION['cart']['idx'];$k++) {
                    // Check if it exists in actual cart
                    if(($_SESSION['savedcart'][$i]['product_id'] == $_SESSION['cart'][$k]['product_id']) &&
                    ($_SESSION['savedcart'][$i]["description"] == $_SESSION['cart'][$k]["description"] )) {
                        $temp = array();
                        $temp['prod_id'] = $_SESSION['savedcart'][$i]["product_id"];
                        $temp['quantity'] = $_SESSION['savedcart'][$i]["quantity"];
                        $temp['description'] = $_SESSION['savedcart'][$i]["description"];
                        $func = 'cartadd';
                        $this->update($temp);
                        $updated = true;
                    }
                }
                // If it hasn't been updated add to the current cart
                if(!$updated) {
                    $_SESSION['cart'][$_SESSION['cart']['idx']] = $_SESSION['savedcart'][$i];
                    $_SESSION['cart']['idx']++;
                }
            }
            $page = "shop.cart";
            $_SESSION['savedcart']['idx']=0;
            $cart = $_SESSION['cart'];
            $this->saveCart();
            return True;
		}
	}
    
    /**
	 * merges the cart array with the saved cart
	 *
	 */
	function deleteCart(&$d) {
		global $my, $page;
		if( $GLOBALS['auth']['user_id'] > 0 ) {
            $page = "shop.cart";
            $_SESSION['savedcart']['idx']=0;
            return True;
		}
	}
	
	/**
	 * Empties the cart
	 * @author pablo
	 * @return boolean true
	 */
	function reset() {
		global $cart;
		$_SESSION['cart'] = array();
		$_SESSION['cart']["idx"]=0;
		$cart = $_SESSION['cart'];
		return True;
	}
}

?>
