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
		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();
		$post = JRequest::get('post');
		$cart = cart::getCart();
		$ci = 0;
		$request_stock = "";
		$total_quantity = 0;
		$total_updated = 0;
		$total_deleted = 0;
		$product_ids = $post['product_id'];
//		$product_ids = JRequest::get('product_id');

	//todo multivendor stuff must be set in the add function, first product determins ownership of cart, or a fixed vendor is used
		$cart['vendor_id'] = 1;
		
		if (!empty($product_ids)) {
			//Iterate through the prod_id's and perform an add to cart for each one
			foreach ($product_ids as $p_key => $product_id) {
				$product = $this->getProduct($product_id);
				
				/* Check if we have a product */
				if ($product && $product->product_id > 0 && $product->published == 1) {
					$quantity = $post['quantity'][$p_key];
					$category_id = $post['category_id'][$p_key];
					
					/* Check for negative quantity */
					if ($quantity < 0) {
//						$vmLogger->warning( JText::_('VM_CART_ERROR_NO_NEGATIVE',false) );
						return false;
					}
		
					/* Check for a valid quantity */
					if (!preg_match("/^[0-9]*$/", $quantity)) {
//						$vmLogger->warning( JText::_('VM_CART_ERROR_NO_VALID_QUANTITY',false) );
						return false;
					}
					
					/* Check for the minimum and maximum quantities */
					list($min,$max) = explode(',', $product->product_order_levels);
					if ($min != 0 && $quantity < $min) {
						$mainframe->enqueueMessage(sprintf(JText::_('VM_CART_MIN_ORDER'), $min), 'error');
						return false;
					}
					if ($max !=0 && $quantity > $max) {
						$mainframe->enqueueMessage(sprintf(JText::_('VM_CART_MAX_ORDER'), $max), 'error');
						return false;
					}
					
					/* Check to see if checking stock quantity */
					if (VmConfig::get('check_stock', false)) {
						if ($quantity > $product->product_in_stock) {
							/* Create an array for out of stock items and continue to next item */
							$request_stock[$ci]['product_id'] = $product->product_id;
							$request_stock[$ci]['quantity'] = $quantity;
							$ci++;
							continue;
						}
					}
		
					/** @todo Check for child items, variants and attributes */
					
					// Check to see if we already have it
					$updated = 0;
					
					
					/** @todo check for attributes adding to the cart */
					
					/* Check for variants being posted */
					$variants = array();
					foreach ($product->variants as $variant => $options) {
						if (array_key_exists($product->product_id.$variant, $post)) {
							$variants[$variant] = $post[$product->product_id.$variant];
						}
					}
					
					/* Check for custom attributes */
					$customvariants = array();
					foreach ($product->customvariants as $cvariant) {
						if (array_key_exists($product->product_id.$cvariant, $post)) {
							$customvariants[$cvariant] = $post[$product->product_id.$cvariant];
						}
					}
					
					/* Create a cart object */
					$item = array();
					$item['quantity'] = $quantity;
					$item['product_id'] = $product->product_id;
					$item['category_id'] = $category_id;
					$item['variants'] = $variants;
					$item['customvariants'] = $customvariants;
					
					/* Check if the item is already in the cart */
					for ($i=0; $i < $cart['idx']; $i++) {
						if ($cart[$i]['product_id'] == $item['product_id']
							&& $cart[$i]['variants'] == $item['variants']
							&& $cart[$i]['customvariants'] == $item['customvariants']
							) {
								$cart[$i]['quantity'] += $quantity;
								$updated = true;
								$mainframe->enqueueMessage(JText::_('VM_CART_PRODUCT_UPDATED'));
								break;
						}
					}
					              
					/* If we did not update then add the item */
					if (!$updated) {
						$cart[$cart['idx']] = $item;
						$cart['idx']++;
						$total_quantity += $quantity;
						$mainframe->enqueueMessage(JText::_('VM_CART_PRODUCT_ADDED'));
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
				}
				else {
//					$vmLogger->tip( JText::_('VM_CART_PRODUCT_NOTEXIST',false) );
					return false;
				}
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
//				$vmLogger->info( $msg );
			}
			else if (@$request_stock && vmIsXHR() ) {
//				$vmLogger->tip( JText::_('VM_CART_GOTO_WAITING_LIST',false) );
			} else {
//				$vmLogger->tip( JText::_('VM_CART_QUANTITY_EXCEEDED',false) );
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
					JFactory::getApplication()->scriptRedirect( $sess->url( 'index.php?page=shop.waiting_list&product_id='.$product_id, true, false ) );
				} else {
					vmRedirect( $sess->url( 'index.php?page=shop.waiting_list&product_id='.$product_id, true, false ) );
				}
			}
		}

		return true;
	}
	
	/**
	* Function Description 
	* 
	* @author RolandD 
	* @access public
	* @param array $cart the cart to get the products for
	* @return array of product objects
	*/
	public function getCartProducts($cart) {
		$products = array();
		for ($i = 0; $cart['idx'] > $i; $i++) {
			$products[$i] = $this->getProduct($cart[$i]['product_id']);
		}
		return $products;
	}
	
	/**
	* Function Description 
	* 
	* @author Max Milbers
	* @access public
	* @param array $cart the cart to get the products for
	* @return array of product objects
	*/
	public function getCartPrices($cart) {
	
		$calculator = calculationHelper::getInstance();
		return $calculator->getCheckoutPrices($cart);
	}
	
	/**
	* Proxy function for getting a product object
	*
	* @author RolandD
	* @todo Find out if the include path belongs here? For now it works.
	* @param int $product_id The product ID to get the object for
	* @return object The product details object
	*/
	private function getProduct($product_id) {
		JModel::addIncludePath(JPATH_COMPONENT.DS.'models');
		$model = JModel::getInstance('Productdetails', 'VirtueMartModel');
		return $model->getProduct($product_id, false);
	}
	
	/**
	* This checks if attributes values were chosen by the user
	* 
	* @author RolandD
	* @access public
	* @param array $d
	* @return array $result
	*/
	public function cartGetAttributes( &$d ) {
		$db = JFactory::getDBO();
		
		// added for the advanced attributes modification
		//get listing of titles for attributes (Sean Tobin)
		$attributes = array( ) ;
		if( ! isset( $d["prod_id"] ) ) {
			$d["prod_id"] = $d["product_id"] ;
		}
		$q = "SELECT product_id, attribute, custom_attribute FROM #__{vm}_product WHERE product_id='" . (int)$d["prod_id"] . "'" ;
		$db->query( $q ) ;
		
		$db->next_record() ;
		
		if( ! $db->f( "attribute" ) && ! $db->f( "custom_attribute" ) ) {
			$q = "SELECT product_parent_id FROM #__{vm}_product WHERE product_id='" . (int)$d["prod_id"] . "'" ;
			
			$db->query( $q ) ;
			$db->next_record() ;
			$q = "SELECT product_id, attribute, custom_attribute FROM #__{vm}_product WHERE product_id='" . $db->f( "product_parent_id" ) . "'" ;
			$db->query( $q ) ;
			$db->next_record() ;
		}
		
		$advanced_attribute_list = $db->f( "attribute" ) ;
		if( $advanced_attribute_list ) {
			$fields = explode( ";", $advanced_attribute_list ) ;
			foreach( $fields as $field ) {
				$field = trim( $field ) ;
				$base = explode( ",", $field ) ;
				$title = array_shift( $base ) ;
				array_push( $attributes, $title ) ;
			}
		}
		// We need this for being able to work with attribute names and values which are using non-ASCII characters
		if( strtolower( vmGetCharset() ) != 'utf-8' ) {
			$encodefunc = 'utf8_encode' ;
			$decodefunc = 'utf8_decode' ;
		} else {
			$encodefunc = 'strval' ;
			$decodefunc = 'strval' ;
		}
		
		$description = "" ;
		$attribute_given = false ;
		// Loop through the simple attributes and check if one of the valid values has been provided
		foreach( $attributes as $a ) {
			
			$pagevar = str_replace( " ", "_", $a ) ;
			$pagevar .= $d['prod_id'] ;
			
			$pagevar = $encodefunc( $pagevar ) ;
			
			if( ! empty( $d[$pagevar] ) ) {
				$attribute_given = true ;
			}
			if( $description != '' ) {
				$description .= "; " ;
			}
			
			$description .= $a . ":" ;
			$description .= empty( $d[$pagevar] ) ? '' : $decodefunc( $d[$pagevar] ) ;
		
		}
		rtrim( $description ) ;
		$d["description"] = $description ;
		// end advanced attributes modification addition
		

		$custom_attribute_list = $db->f( "custom_attribute" ) ;
		$custom_attribute_given = false ;
		// Loop through the custom attribute list and check if a value has been provided
		if( $custom_attribute_list ) {
			$fields = explode( ";", $custom_attribute_list ) ;
			
			$description = $d["description"] ;
			
			foreach( $fields as $field ) {
				$pagevar = str_replace( " ", "_", $field ) ;
				$pagevar .= $d['prod_id'] ;
				$pagevar = $encodefunc( $pagevar ) ;
				
				if( ! empty( $d[$pagevar] ) ) {
					$custom_attribute_given = true ;
				}
				if( $description != '' ) {
					$description .= "; " ;
				}
				$description .= $field . ":" ;
				$description .= empty( $d[$pagevar] ) ? '' : $decodefunc( $d[$pagevar] ) ;
			
			}
			rtrim( $description ) ;
			$d["description"] = $description ;
			// END add for custom fields by denie van kleef
		

		}
		
		$result['attribute_given'] = $attribute_given ;
		$result['advanced_attribute_list'] = $advanced_attribute_list ;
		$result['custom_attribute_given'] = $custom_attribute_given ;
		$result['custom_attribute_list'] = $custom_attribute_list ;
		
		return $result ;
	}
}
?>