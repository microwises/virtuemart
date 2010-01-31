<?php
/**
*
* Category model for the cart
*
* @package	VirtueMart
* @subpackage Cart
* @author RolandD
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id$
*/
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the model framework
jimport( 'joomla.application.component.model');

/**
* Default model class for the cart
*
* @package	VirtueMart
* @subpackage Cart
* @author RolandD
*
*/
class VirtueMartModelCart extends JModel {
	
	/**
	* Get the category ID from a product ID 
	* 
	* @author RolandD
	* @todo move it to a better model
	* @access public
	* @return mixed if found the category ID else null
	*/
	public function getCategoryId() {
		$db = JFactory::getDBO();
		$product_id = JRequest::getInt('product_id');
		$q = 'SELECT `category_id` FROM `#__vm_product_category_xref` WHERE `product_id` = '.intval($product_id).' LIMIT 1';
		$db->setQuery($q);
		return $db->loadResult();
	}
	
	/**
	* Add a product to the cart 
	* 
	* @author RolandD
	* @todo
	* @access public
	*/
	public function add() {
		$db = JFactory::getDBO();
		$post = JRequest::get('post');
		$cart = cart::getCart();
		$ci = 0;
		$request_stock = "";
		$total_quantity = 0;
		$total_updated = 0;
		$total_deleted = 0;
		$product_ids = $post['product_id'];

		if (!empty($product_ids)) {
			//Iterate through the prod_id's and perform an add to cart for each one
			foreach ($product_ids as $p_key => $product_id) {
				/** @todo Get a product object */
				
				// Check for negative quantity
				if ($post['quantity'][$p_key] < 0) {
					$vmLogger->warning( JText::_('VM_CART_ERROR_NO_NEGATIVE',false) );
					return false;
				}
	
				if (!preg_match("/^[0-9]*$/", $post['quantity'][$p_key])) {
					$vmLogger->warning( JText::_('VM_CART_ERROR_NO_VALID_QUANTITY',false) );
					return false;
				}
				
				// Check to see if checking stock quantity
				if (VmConfig::get('check_stock', false)) {
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
				/** @todo Check for child items, variants and attributes */
				/**
				if (!ps_product::product_exists($product_id)) {
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
				*/
				// Check for duplicate and do not add to current quantity
				for ($i=0;$i<$cart["idx"];$i++) {
					// modified for advanced attributes
					if ($cart[$i]["product_id"] == $product_id
					//&&
					//$cart[$i]["description"] == $e["description"]
					) {
						$cart[$i]["quantity"] += $post['quantity'][$p_key];
						$updated = 1;
					}
				}
				/**
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
				*/
				// If we did not update then add the item
				if ((!$updated) && ($post['quantity'])){
					$k = $cart["idx"];
					$cart[$k]["quantity"] = $post['quantity'][$p_key];
					$cart[$k]["product_id"] = $product_id;
					/** @todo get the parent ID from the product object */
					//$cart[$k]["parent_id"] = $e["product_id"];
					$cart[$k]["category_id"] = $post['category_id'][$p_key];
					// added for the advanced attribute modification
					//$cart[$k]["description"] = $e["description"];
					$cart["idx"]++;
					$total_quantity += $quantity;
				}
				else {
					/**
					list($updated_prod,$deleted_prod) = $this->update( $e );
					$total_updated += $updated_prod;
					$total_deleted += $deleted_prod;
					*/
				}
	
				/* next 3 lines added by Erich for coupon code */
				/* if the cart was updated we gotta update any coupon discounts to avoid ppl getting free stuff */
				/**
				if( !empty( $_SESSION['coupon_discount'] )) {
					// Update the Coupon Discount !!
					require_once(CLASSPATH.'ps_coupon.php');
					ps_coupon::process_coupon_code($d);
				}
				*/
			} // End Iteration through Prod id's
			cart::setCart($cart);
			return true;
	
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
		}

		return True;
	}
}
?>