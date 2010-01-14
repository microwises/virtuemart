<?php
/**
 * Default data model for Product details
 *
 * @package     VirtueMart
 * @author      RolandD
 * @copyright   Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.model');

/**
 * Default model class for Virtuemart
 *
 * @package	VirtueMart
 * @author RolandD
 *
 */
class VirtueMartModelProductdetails extends JModel {
	/**
	* Load the product details
	*/
	public function getProduct() {
		$db = JFactory::getDBO();
		$product_id = JRequest::getInt('product_id', 0);
		
		if ($product_id > 0) {
			$q = "SELECT p.*, x.category_id, x.product_list, m.manufacturer_id, m.mf_name 
				FROM #__vm_product p
				LEFT JOIN #__vm_product_category_xref x
				ON x.product_id = p.product_id
				LEFT JOIN #__vm_product_mf_xref mx
				ON mx.product_id = p.product_id
				LEFT JOIN #__vm_manufacturer m
				ON m.manufacturer_id = mx.manufacturer_id
				WHERE p.product_id = ".$product_id;
			$db->setQuery($q);
			$product = $db->loadObject();
			
			/* Load the categories the product is in */
			$product->categories = $this->getCategories();
			
			/* Load the neighbours */
			$product->neighbours = $this->getNeighborProducts($product);
			
			/* Load the price */
			$prices = "";
//			if (VmConfig::get('show_prices') == '1') {
				/* Loads the product price details */
				$calculator = new calculationHelper();
				//the function getProductPrices returns an array, therefore $prices not $price
				$prices = $calculator->getProductPrices($product->product_id,$product->categories);
//			}
			//The prices calculated in the calculator are dynamical calculated and mostly not senseful to save in a tabel
			//Maybe I just lern another php thing, please explain me this Roland, do you just add to the Object the data product_price? (why underscore btw)
			//you may take a look at ps_product line 2313 to see how it is used
			$product->product_price = $prices;
			
			/* Fix the product packaging */
			if ($product->product_packaging) {
				$product->packaging = $product->product_packaging & 0xFFFF;
				$product->box = ($product->product_packaging >> 16) & 0xFFFF;
			}
			else $product->product_packaging = '';
			
			/* Check for child products */
			
			return $product;
		}
		else return false;
	}
	
	/**
	* Load the categories the product is in
	*
	* @author RolandD
	* @return array list of categories product is in
	*/
	private function getCategories() {
		$db = JFactory::getDBO();
		$product_id = JRequest::getInt('product_id', 0);
		$categories = array();
		
		if ($product_id > 0) {
			$q = "SELECT category_id FROM #__vm_product_category_xref WHERE product_id = ".$product_id;
			$db->setQuery($q);
			$categories = $db->loadResultArray();
		}
		
		return $categories;
	}
	
	
	/**
	 * This function retrieves the "neighbor" products of a product specified by $product_id
	 * Neighbors are the previous and next product in the current list
	 *
	 * @author RolandD
	 * @param object $product The product to find the neighours of
	 * @return array
	 */
	function getNeighborProducts($product) {
		$db = JFactory::getDBO();
		$neighbors = array('previous' => '','next' => '');
		
		$q = "SELECT x.product_id, product_list, p.product_name
			FROM #__vm_product_category_xref x
			LEFT JOIN #__vm_product p
			ON p.product_id = x.product_id
			WHERE category_id = ".$product->category_id."
			ORDER BY product_list, x.product_id";
		$db->setQuery($q);
		$products = $db->loadAssocList('product_id');
		
		/* Move the internal pointer to the current product */
		foreach ($products as $product_id => $xref) {
			if ($product_id == $product->product_id) break;
		}
		
		/* Get the neighbours */
		$neighbours['next'] = current($products);
		if (!$neighbours['next']) end($products);
		else prev($products);
		$neighbours['previous'] = prev($products);
		
		return $neighbours;
	}

}
?>