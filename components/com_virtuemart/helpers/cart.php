<?php
/**
*
* Category model for the cart
*
* @package	VirtueMart
* @subpackage Cart
* @author RolandD
* @author Max Milbers
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
//jimport( 'joomla.application.component.model');

/**
* Model class for the cart
* Very important, use ALWAYS the getCart function, to get the cart from the session
* @package	VirtueMart
* @subpackage Cart
* @author RolandD
* @author Max Milbers
*/
class VirtueMartCart  {
	
//	var $productIds = array();
	var $products = array();
	var $inCheckOut = false;
	var $dataValidated = false;
	//todo multivendor stuff must be set in the add function, first product determins ownership of cart, or a fixed vendor is used
	var $vendorId = 1;	
	
	private function __construct() {
				
		self::setCartIntoSession();

	}
	


	/**
	* Get the cart from the session 
	* 
	* @author Max Milbers
	* @access public
	* @param array $cart the cart to store in the session
	*/
	public static function getCart($deleteValidation=true) {
		
		$session = JFactory::getSession();
		$cartTemp = $session->get('vmcart', 0, 'vm');
		if(!empty($cartTemp) ){
			$cart = unserialize($cartTemp);
			if(!$deleteValidation) $cart->dataValidated = false;
		} else {
			$cart = new VirtueMartCart;
		}
		return $cart;
	}
	
	/**
	* Set the cart in the session 
	* 
	* @author RolandD
	*
	* @access public
	* @param array $cart the cart to store in the session
	*/
	public function setCartIntoSession() {
		$session = JFactory::getSession();
		$session->set('vmcart', serialize($this), 'vm');
	}
	
	/**
	* Remove the cart from the session 
	* 
	* @author Max Milbers
	* @todo
	* @access public
	*/
	public function removeCartFromSession() {
		$session = JFactory::getSession();
		$session->set('vmcart', 0, 'vm');
	}
	
	/**
	* Add a product to the cart 
	* 
	* @author RolandD
	* @author Max Milbers
	* @todo
	* @access public
	*/
	public function add() {
		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();
		$post = JRequest::get('post');

		$total_quantity = 0;
		$total_updated = 0;
		$total_deleted = 0;
		$product_ids = $post['product_id'];
//		$product_ids = JRequest::get('product_id');
		dump($post,'post data in add');
		if (empty($product_ids)) {
			$mainframe->enqueueMessage( JText::_('VM_CART_ERROR_NO_PRODUCT_IDS',false) );
			return false;	
		}
		
		//Iterate through the prod_id's and perform an add to cart for each one
		foreach ($product_ids as $p_key => $product_id) {
			$product = $this->getProduct($product_id);
			
			/* Check if we have a product */
			if ($product && $product->product_id > 0 && $product->published == 1) {
				$quantity = $post['quantity'][$p_key];
				$category_id = $post['category_id'][$p_key];
				
				if(!$this->checkForQuantities($product,$quantity)) return false;

				/** @todo Check for child items, variants and attributes */
				
				/** @todo check for attributes adding to the cart */
				
				
				$product->quantity = $quantity;
				$product->category_id = $category_id;
				$productKey= $product->product_id.':';
				
//				/* Check for variants being posted */
				$variants = array();
				foreach ($product->variants as $variant => $options) {
					if (array_key_exists($product->product_id.$variant, $post)) {
						$variants[$variant] = $post[$product->product_id.$variant];
						$productKey .= $post[$product->product_id.$variant];
					}
				}
				$productKey .= ':';
				$product->variant =  $variants;
				
				/* Check for custom attributes */
				$customvariants = array();
				foreach ($product->customvariants as $cvariant) {
					if (array_key_exists($product->product_id.$cvariant, $post)) {
						$customvariants[$cvariant] = $post[$product->product_id.$cvariant];
						$productKey .= ':'.$post[$product->product_id.$cvariant];
					}
				}
				$product->customvariant =  $customvariants;

				if (array_key_exists($productKey, $this->products)) {
					$product->quantity += $quantity;
					$mainframe->enqueueMessage(JText::_('VM_CART_PRODUCT_UPDATED'));
				} else {
					$this->products[$productKey] = $product;
					dump($product,'$product added to cart');
					$mainframe->enqueueMessage(JText::_('VM_CART_PRODUCT_ADDED'));
				}

			}
			else {
				$mainframe->enqueueMessage( JText::_('VM_CART_PRODUCT_NOTEXIST',false) );
				return false;
			}
		} 
		// End Iteration through Prod id's
		$this->setCartIntoSession();
		return true;
	}
	
	/**
	 * Checks if the quantity is correct
	 * 
	 * @author Max Milbers
	 */
	public function checkForQuantities($product,$quantity=0) {
		
		$mainframe = JFactory::getApplication();
		/* Check for a valid quantity */
		if (!preg_match("/^[0-9]*$/", $quantity)) {
			$mainframe->enqueueMessage( JText::_('VM_CART_ERROR_NO_VALID_QUANTITY',false) );
			return false;
		}
		
		/* Check for negative quantity */
		if ($quantity < 0) {
			$mainframe->enqueueMessage( JText::_('VM_CART_ERROR_NO_NEGATIVE',false) );
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
		
		$ci = 0;
		$request_stock = array();
		
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
		if(count($request_stock)!=0){
			foreach($request_stock as $rstock){
				$mainframe->enqueueMessage(JText::_('VM_CART_PRODUCT_OUT_OF_STOCK'), 'error');	
			}
			return false;
		}
		return true;
	}

	/**
	* Remove a product from the cart 
	* 
	* @author RolandD
	* @param array $cart_id the cart IDs to remove from the cart
	* @access public
	*/
	public function removeProductCart($prod_id=0) {
		/* Check for cart IDs */
		if (empty($prod_id)) $prod_id = JRequest::getVar('cart_product_id');
//		$prod_id = JRequest::get();
		
//		/* Check if the product ID is ok */
//		if (!$prod_ids || !is_array($prod_ids) || empty($prod_ids)) return;
//
//		if (empty($prod_variants)) $cart_ids = array(JRequest::getInt('variants'));
//		if (!$prod_variants || !is_array($prod_variants) || empty($prod_variants)) return;
//
//		if (empty($prod_customvariants)) $cart_ids = array(JRequest::getInt('customvariants'));
//		if (!$prod_customvariants || !is_array($prod_customvariants) || empty($prod_customvariants)) return;
//	
//		/* Load the cart */
//		$cart = $this->getCart();
//		
//		/* Remove the product */
//		foreach ($cart_ids as $cart_id) {
//			'P'.$product->product_id.$product->variants.$product->customvariants
			dump($prod_id,'I delete product from the cart with prod_id ');
			unset($this->products[$prod_id]);
//		}

		/* Save the cart */
		$this->setCartIntoSession();
		return true;
	}
	
	/**
	* Update a product in the cart 
	* 
	* @author Max Milbers
	* @param array $cart_id the cart IDs to remove from the cart
	* @access public
	*/
	public function updateProductCart($cart_product_id=0) {
		
		if (empty($cart_product_id)) $cart_product_id = JRequest::getVar('cart_product_id');
		if (empty($quantity)) $quantity = JRequest::getInt('quantity');
		
//		foreach($cart_product_ids as $cart_product_id){
			dump($this->products[$cart_product_id],'Searching for key '.$cart_product_id);
			if (array_key_exists($cart_product_id, $this->products)) {
				if(!empty($quantity)){
					if($this->checkForQuantities($this->products[$cart_product_id],$quantity)){
						$this->products[$cart_product_id]->quantity = $quantity;
						$updated = true;						
					}
				} else {
					//Todo when quantity is 0,  the product should be removed, maybe necessary to gather in array and execute delete func
					unset($this->products[$cart_product_id]);
					$updated = true;
				}
				
			}
//		}
		
		/* Save the cart */
		$this->setCartIntoSession();
		if ($updated) return true;
		else return false;
	}

	/**
	* Function Description 
	* 
	* @author Max Milbers
	* @access public
	* @param array $cart the cart to get the products for
	* @return array of product objects
	*/
	public function getCartPrices() {
	
		$calculator = calculationHelper::getInstance();
		return $calculator->getCheckoutPrices($this);
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
	* Get the category ID from a product ID 
	* 
	* @author RolandD
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
	* Initialise the cart, ATTENTION, started by Roland but not finished, when someone needs it, dont be shy ;-) note by Max Milbers
	* 
	* @author RolandD
	* @todo Make sure this gets called when a user is logged in
	* @access public
	*/
	public function initCart() {
		$session = JFactory::getSession();
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		$cart = $this->getCart();
		
		// If the user is logged in, we can try to retrieve the current cart from the database
        // We store the items in a new SESSION var
		if ($user->id > 0 && empty($cart)) {
			$q = 'SELECT `cart_content` 
				FROM `#__vm_cart` 
				WHERE `user_id` = '.$user->id;
			$db->setQuery($q);
			$savedcart = $db->loadObject();
			if ($savedcart) {
				// Fill the cart from the contents of the field cart_content, which holds a serialized array
				$contents = unserialize($savedcart->cart_content);
				
				// Now check if all products are still published and existant
				$products_in_cart = array();
				for ($i=0; $i < $contents["idx"]; $i++) {
					$products_in_cart[$contents[$i]['product_id']] = intval($contents[$i]['product_id']);
				}
				if (!empty($products_in_cart)) {
					$remove_products = array();
					$q = 'SELECT `product_id` 
						FROM #__vm_product 
						WHERE `product_id` IN ('.implode(',', $products_in_cart ).') 
						AND published = 0';
					$db->setQuery($q);
					$remove_products = $db->loadResultArray();
				}
				
				if (!empty($remove_products)) {
					for ($i=0; $i < $contents["idx"]; $i++) {
						if (in_array(intval($contents[$i]['product_id']), $remove_products)) self::removeProductCart(array($i));
					}
				}
			}
		}
		
		$this->setCartIntoSession();
	}
	
	/**
	* Save the cart in the database 
	* 
	* @author RolandD
	* @todo
	* @access public
	*/
	public function saveCart() {
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		$cart = $this->getCart();
		if ($user->id > 0) {
			$cart_contents = serialize($cart);
			$q = "INSERT INTO `#__vm_cart` (`user_id`, `cart_content` ) VALUES ( ".$user->id.", '".$cart_contents."' ) 
				ON DUPLICATE KEY UPDATE `cart_content` = ".$db->Quote($cart_contents);
			$db->setQuery($q);
			$db->query();
		}
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
