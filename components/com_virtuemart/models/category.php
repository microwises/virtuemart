<?php
/**
*
* Category model for Virtuemart
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
class VirtueMartModelCategory extends JModel {
	
	/**
	* Load a category and it's details
	*
	* @author RolandD
	* @return object containing the category
	*/
	public function getCategory($category_id=0) {
		$db = JFactory::getDBO();
		$row = $this->getTable('category');
		$row->load($category_id);
		
		/* Check for children */
		$row->haschildren = $this->hasChildren($category_id);
		
		/* Get children if they exist */
		if ($row->haschildren) $row->children = $this->getChildrenList($category_id);
		else $row->children = null;
		
		/* Get the product count */
		$row->productcount = $this->getProductCount($category_id);

		/* Get parent for breatcrumb */
		$row->parents = $this->getparentsList($category_id);

		return $row;
	}
	
	/**
	* Checks for children of the category $category_id
	*
	* @author RolandD
	* @param int $category_id the category ID to check
	* @return boolean true when the category has childs, false when not
	*/
	public function hasChildren($category_id) {
		$db = JFactory::getDBO();
		$q = "SELECT category_child_id 
			FROM #__vm_category_xref
			WHERE category_parent_id = ".$category_id;
		$db->setQuery($q);   
		$db->query();
		if ($db->getAffectedRows() > 0) return true;
		else return false;
	}
	
	/**
	 * Creates a bulleted of the childen of this category if they exist
	 *
	 * @author RolandD
	 * @todo Add vendor ID
	 * @param int $category_id the category ID to create the list of
	 * @return array containing the child categories
	 */
	public function getChildrenList($category_id) {
		$db = JFactory::getDBO();
		$childs = array();
		
		$q = "SELECT category_id, category_full_image, category_thumb_image, category_child_id, category_name 
			FROM #__vm_category, #__vm_category_xref
			WHERE #__vm_category_xref.category_parent_id = ".$category_id."
			AND #__vm_category.category_id=#__vm_category_xref.category_child_id
			AND #__vm_category.vendor_id = 1
			AND #__vm_category.published = 1
			ORDER BY #__vm_category.ordering, #__vm_category.category_name ASC";
		$db->setQuery($q);
		$childs = $db->loadObjectList();
		
		/* Get the products in the category */
		foreach ($childs as $ckey => $child) {
			$childs[$ckey]->number_of_products = $this->getProductCount($child->category_child_id);
		}
		
		return $childs;
	}	
	/**
	 * Creates a bulleted of the childen of this category if they exist
	 *
	 * @author RolandD
	 * @todo Add vendor ID
	 * @param int $category_id the category ID to create the list of
	 * @return array containing the child categories
	 */
	public function getparentsList($category_id) {

		$db = & JFactory::getDBO();
		$menu = &JSite::getMenu();
		$parents = array();
		if (empty($query['Itemid'])) {
			$menuItem = &$menu->getActive();
		} else {
			$menuItem = &$menu->getItem($query['Itemid']);
		}
		$menuCatid	= (empty($menuItem->query['category_id'])) ? 0 : $menuItem->query['category_id'];
		$parents_id = array_reverse($this->getCategoryRecurse($category_id,$menuCatid));
		foreach ($parents_id as $id ) {
			$q = "SELECT `category_name`,`category_id` 
				FROM  `#__vm_category` 
				WHERE  `category_id`=".$id;
			
			$db->setQuery($q);
			
			$parents[] = $db->loadObject();
		}
		return $parents;
	}

	function getCategoryRecurse($category_id,$catMenuId,$first=true ) {
		static $idsArr = array();
		if($first) {
			$idsArr = array();
		}

		$db = & JFactory::getDBO();
		$q  = "SELECT `category_child_id` AS `child`, `category_parent_id` AS `parent` 
			FROM  #__vm_category_xref AS `xref`
			WHERE `xref`.`category_child_id`= ".$category_id;
		$db->setQuery($q);
		$ids = $db->loadObject();
		if ($ids->child) $idsArr[] = $ids->child;
		if($ids->child != 0 and $catMenuId != $category_id and $catMenuId != $ids->parent) {
			$this->getCategoryRecurse($ids->parent,$catMenuId,false);
		} 
		return $idsArr;
	}

	
	/**
	 * Function to calculate and return the number of products in category $category_id
	 * @author RolandD
	 * 
	 * @todo Add vendor
	 * @param int $category_id the category ID to count products for
	 * @return int the number of products found
	 */
	public function getProductCount($category_id) {
		$db = JFactory::getDBO();
		$q = "SELECT count(#__vm_product.product_id) AS num_rows 
			FROM #__vm_product, #__vm_product_category_xref, #__vm_category 
			WHERE #__vm_product.vendor_id = 1 
			AND #__vm_product_category_xref.category_id = ".$category_id."
			AND #__vm_category.category_id = #__vm_product_category_xref.category_id
			AND #__vm_product.product_id = #__vm_product_category_xref.product_id
			AND #__vm_product.published = 1";
			if (VmConfig::get('check_stock') && VmConfig::get('pshop_show_out_of_stock_products') != "1") {
				$q .= " AND product_in_stock > 0 ";
			}
		$db->setQuery($q);
		return $db->loadResult();
	}
}

//pure php no closing tag