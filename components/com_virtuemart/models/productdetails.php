<?php
/**
*
* Default data model for Product details
*
* @package	VirtueMart
* @subpackage 
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
	public function getProduct($product_id=false) {
		$db = JFactory::getDBO();
		if (!$product_id) $product_id = JRequest::getInt('product_id', 0);
		
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
			
			if (empty($product->category) && isset($product->categories[0])) $product->category_id = $product->categories[0];
			
			/* Add the product link  */
			$product->link = JRoute::_('index.php?option=com_virtuemart&view=productdetails&product_id='.$product_id.'&category_id='.$product->category_id);
			
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
			
			/* Load the vendor details */
			$product->vendor_id = Vendor::getVendorId('product', $product_id);
			$product->vendor_name = Vendor::getVendorFields($product->vendor_id, array('vendor_name'));
			
			/* Check for child products */
			$product->haschildren = $this->checkChildProducts($product_id);
			
			/* Check for product types */
			$product->hasproducttypes = $this->hasProductType($product_id);
			
			/* Load the variants */
			$product->variants = $this->getVariants($product);
			
			/* Handle some child product data */
			if ($product->product_parent_id > 0) {
				/* Get the attributes */
				// $product->attributes = $this->getAttributes($product);
			}
			
			
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
	
	/**
	* Proxy function to check for children
	*
	* @author RolandD
	* @todo Find out if the include path belongs here? For now it works.
	* @param	int		The product ID to check
	* @return	bool	True if product has children, false if product has no children
	*/
	private function checkChildProducts($product_id) {
			JModel::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'models');
			$model = JModel::getInstance('Product', 'VirtueMartModel');
			return $model->checkChildProducts($product_id);
	}
	
	/**
	* Load the product reviews for the given product
	*
	* @author RolandD
	*
	* @todo Make number of reviews configurable
	* @param int $product_id the product ID to get the reviews for
	* @return array of objects with product reviews
	*/
	public function getProductReviews($product_id) {
		$db = JFactory::getDBO();
		$showall = JRequest::getBool('showall', 0);
		
		$q = "SELECT comment, `time`, userid, user_rating, username, name 
			FROM #__vm_product_reviews r
			LEFT JOIN jos_users u
			ON u.id = r.userid
			WHERE product_id = ".$product_id." 
			AND published = '1' 
			ORDER BY `time` DESC ";
		if (!$showall) $q .= " LIMIT 0, 5";
		$db->setQuery($q);
		return $db->loadObjectList();
	}
	
	/**
	* Get the product details for a product
	*
	* @author RolandD
	*
	* @param int $product_id the ID of the product to get product types for
	* @return array of objects with product types
	*/
	public function getProductTypes($product_id) {
		$db = JFactory::getDBO();
		if ($product_id > 0 && !$this->productHasProductType($product_id)) {
			$product_type = $ps_product_type->list_product_type( $product_id ) ;
		}
	}
	
	/**
	 * Returns true if the product is in a Product Type
	 *
	 * @author Zdenek Dvorak
	 *
	 * @param int $product_id the product to check
	 * @return boolean
	 */
	private function hasProductType($product_id) {
		$db = JFactory::getDBO();
		$q = "SELECT COUNT(product_id) AS types FROM #__vm_product_product_type_xref WHERE product_id = ".$product_id;
		$db->setQuery($q);
		
		return ($db->loadResult() > 0);
	}
	
	/**
	 * Returns html code for show parameters
	 * @author RolandD
	 *
	 * @param int $product_id
	 * @return array containing all product type info for the requested product
	 */
	public function getProductType($product_id) {
		$db = JFactory::getDBO();
		$product_types = array();
		
		/* Get the product types the product is linked to */
		$q = "SELECT t.*
			FROM #__vm_product_product_type_xref x
			LEFT JOIN #__vm_product_type t
			ON t.product_type_id = x.product_type_id
			WHERE product_id = ".$product_id."
			GROUP BY product_type_id";
		$db->setQuery($q);
		$product_types = $db->loadObjectList();
		
		foreach ($product_types as $pkey => $product_type) {
			/* Load the details */
			$q = "SELECT * FROM #__vm_product_type_".$product_type->product_type_id." ORDER BY parameter_list_order";
			$db->setQuery($q);
			$product_types[$pkey]->product_type = $db->loadObjectList();
		}
		return $product_types;
	}
	
	/**
	* Load the variants for a product
	*
	* Variants can have several attributes an example:
	* Size,XL[+1.99],M,S[-2.99];Colour,Red,Green,Yellow,ExpensiveColor[=24.00]
	*
	* @author RolandD
	* @param object $product the product to get attributes for
	* @param string $extra_ids any extra id's to add to the attributes
	* @return 
	*/
	public function getVariants($product) {
		$calculator = new calculationHelper();
		
		/* Get the variants */
		$variants_raw = explode(';', $product->attribute);
		
		/* Get the variant details */
		foreach ($variants_raw as $vkey => $variant) {
			$variant_details = explode(',', $variant);
			$variant_name = '';
			foreach ($variant_details as $dkey => $value) {
				if ($dkey == 0) {
					$variant_name = $value;
					$variants[$value] = array();
				}
				else {
					/* Get the price */
					$matches = array();
					$pattern = '/\[.*?]/';
					/* Get all matches */
					preg_match_all($pattern, $value, $matches);
					if (sizeof($matches[0]) > 0) {
						$variant_type = str_ireplace($matches[0], '', $value);
						$find = array('[', ']');
						foreach ($matches[0] as $key => $match) {
							/* Remove all obsolete characters */
							$variant_price = str_replace($find, '', $match);
							/* Get the price information */
							$variants[$variant_name][$variant_type] = $calculator->getVariantPrice($product->product_id, $variant_price);
						}
					}
					else {
						$variants[$variant_name][$value] = array();
					}
				}
			}
		}
		return $variants;
	}
}
?>