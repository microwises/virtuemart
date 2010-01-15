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
			
			/* Add the product link  */
			$product->link = JRoute::_('index.php?option=com_virtuemart&view=productdetails&product_id='.$product_id.'&category_id='.$product->category_id);
			
			/* Load the categories the product is in */
			$product->categories = $this->getCategories();
			
			/* Load the neighbours */
			$product->neighbours = $this->getNeighborProducts($product);
			
			/* Load the price */
			$prices = "";
			if (VmConfig::get('show_prices') == '1') {
				/* Loads the product price details */
				$calculator = new calculationHelper();
				$prices = $calculator->getProductPrices($product->product_id,$product->categories);
			}
			$product->product_price = $prices;
			
			/* Fix the product packaging */
			if ($product->product_packaging) {
				$product->packaging = $product->product_packaging & 0xFFFF;
				$product->box = ($product->product_packaging >> 16) & 0xFFFF;
			}
			else {
				$product->packaging = '';
				$product->box = '';
			}
			
			/* Load the extra files */
			$product->files = $this->getFileList($product_id);
			
			/* Load the related products */
			$product->related = $this->getRelatedProducts($product_id);
			
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
	private function getNeighborProducts($product) {
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
	
	/**
	 * List all published and non-payable files ( not images! )
	 * @author RolandD
	 * 
	 * @param int $product_id The ID of the product
	 * @return array containing all the files and their data
	 */
	private function getFileList($product_id) {
		jimport('joomla.filesystem.file');
		$db = JFactory::getDBO();
		$html = "";
		$q = "SELECT attribute_value 
			FROM #__vm_product_attribute 
			WHERE `product_id` = ".$product_id." 
			AND attribute_name='download'";
		$db->query($q);
		$exclude_filename = $db->Quote($db->loadResult());
		
		$sql = "SELECT DISTINCT file_id, file_mimetype, file_title, file_name
				FROM `#__vm_product_files` 
				WHERE ";
				if ($exclude_filename) $sql .= " file_title != ".$exclude_filename." AND
				file_product_id = '".$product_id."' 
				AND file_published = '1' 
				AND file_is_image = '0'";
		$db->setQuery($sql);
		$files = $db->loadObjectList();

		foreach ($files as $fkey => $file) {
			$filename = JPATH_ROOT.DS.'media'.DS.str_replace(JPATH_ROOT.DS.'media'.DS, '', $file->file_name);
			if (JFile::exists($filename)) $files[$fkey]->filesize = @filesize($filename) / 1048000;
			else $files[$fkey]->filesize = false;
		}
		return $files;
	}
	
	/**
	* Load any related products
	*
	* @author RolandD
	* @todo Do we need to give this link a category ID?
	* @param int $product_id The ID of the product
	* @return array containing all the files and their data
	*/
	private function getRelatedProducts($product_id) {
		$db = JFactory::getDBO();
		$q = "SELECT p.product_id, product_sku, product_name, product_thumb_image, related_products 
			FROM #__vm_product p, #__vm_product_relations r 
			WHERE r.product_id = ".$product_id."
			AND p.published = 1
			AND FIND_IN_SET(p.product_id, REPLACE(r.related_products, '|', ',' )) LIMIT 0, 4";
		$db->setQuery($q);
		$related_products = $db->loadObjectList();
		
		/* Get the price also */
		if (VmConfig::get('show_prices') == '1') {
			/* Loads the product price details */
			$calculator = new calculationHelper();
			foreach ($related_products as $rkey => $related) {
				$related_products[$rkey]->price = $calculator->getProductPrices($related->product_id);
				/* Add the product link  */
				$related_products[$rkey]->link = JRoute::_('index.php?option=com_virtuemart&view=productdetails&product_id='.$product_id);
			}
		}
		
		return $related_products;
	}

}
?>