<?php
/**
*
* Handles all the cart activities
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

/**
* Handles all the cart activities
*
* @package	VirtueMart
* @subpackage Cart
* @author RolandD
*
*/
class cart {
	
	/**
	* Initialise the cart 
	* 
	* @author RolandD
	* @todo Make sure this gets called when a user is logged in
	* @access public
	*/
	public function initCart() {
		$session = JFactory::getSession();
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		$cart = self::getCart();
		
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
		self::setCart($cart);
	}
	
	/**
	* Retrieve the cart from the session 
	* 
	* @author RolandD
	* @todo
	* @access public
	*/
	public function getCart() {
		$session = JFactory::getSession();
		$cart = $session->get('vmcart', array('idx' => 0), 'vm');
		return $cart;
	}
	
	/**
	* Set the cart from in the session 
	* 
	* @author RolandD
	* @todo
	* @access public
	* @param array $cart the cart to store in the session
	*/
	public function setCart($cart) {
		$session = JFactory::getSession();
		$session->set('vmcart', $cart, 'vm');
	}
	
	/**
	* Remove the cart from the session 
	* 
	* @author RolandD
	* @todo
	* @access public
	*/
	public function emptyCart() {
		$cart = array();
		$cart['idx'] = 0;
		self::setCart($cart);
	}
	
	/**
	* Remove a product from the cart 
	* 
	* @author RolandD
	* @param array $cart_id the cart IDs to remove from the cart
	* @access public
	*/
	public function removeProductCart($cart_ids=array()) {
		/* Check for cart IDs */
		if (empty($cart_ids)) $cart_ids = array(JRequest::getInt('cart_id'));
		
		/* Check if the product ID is ok */
		if (!$cart_ids || !is_array($cart_ids) || empty($cart_ids)) return;
		
		/* Load the cart */
		$cart = self::getCart();
		
		/* Remove the product */
		foreach ($cart_ids as $cart_id) {
			unset($cart[$cart_id]);
		}
		
		/* Clean up the cart */
		unset($cart['idx']);
		$cart = array_values($cart);
		$cart['idx'] = count($cart);
		
		/* Save the cart */
		self::setCart($cart);
		return true;
	}
	
	/**
	* Update a product in the cart 
	* 
	* @author RolandD
	* @param array $cart_id the cart IDs to remove from the cart
	* @access public
	*/
	public function updateProductCart($cart_ids=array()) {
		/* Check for cart IDs */
		if (empty($cart_ids)) $cart_ids = array(JRequest::getInt('cart_id'));
		
		/* Check if the product ID is ok */
		if (!$cart_ids || !is_array($cart_ids) || empty($cart_ids)) return;
		
		/* Load the cart */
		$cart = self::getCart();
		
		/* Update the product */
		foreach ($cart_ids as $cart_id) {
			if (array_key_exists($cart_id, $cart)) {
				$cart[$cart_id]['quantity'] = JRequest::getInt('quantity');
				$updated = true;
			}
		}
		
		/* Save the cart */
		self::setCart($cart);
		if ($updated) return true;
		else return false;
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
		$cart = self::getCart();
		if ($user->id > 0) {
			$cart_contents = serialize($cart);
			$q = "INSERT INTO `#__vm_cart` (`user_id`, `cart_content` ) VALUES ( ".$user->id.", '".$cart_contents."' ) 
				ON DUPLICATE KEY UPDATE `cart_content` = ".$db->Quote($cart_contents);
			$db->setQuery($q);
			$db->query();
		}
	}
}
?>