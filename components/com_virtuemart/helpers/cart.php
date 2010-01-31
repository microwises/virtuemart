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
					
					if (!empty($remove_products)) self::removeProductFromCart($remove_products);
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
	* @todo Check for duplicate products
	* @param array $product_ids the product IDs to remove from the cart
	* @access public
	*/
	public function removeProductFromCart($product_ids=array()) {
		/* Check if the product ID is ok */
		if (!$product_ids || !is_array($product_ids) || empty($product_ids)) return;
		
		/* Load the cart */
		$cart = self::getCart();
		
		/* Remove the product */
		foreach ($product_ids as $product_id) {
			// Do some funky code here
		}
		
		/* Save the cart */
		self::setCart($cart);
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