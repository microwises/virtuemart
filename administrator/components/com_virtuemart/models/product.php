<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage
* @author RolandD
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2011 VirtueMart Team. All rights reserved.
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

 JTable::addIncludePath(JPATH_VM_ADMINISTRATOR.DS.'tables');
/**
 * Model for VirtueMart Products
 *
 * @package VirtueMart
 * @author RolandD
 * @todo Replace getOrderUp and getOrderDown with JTable move function. This requires the vm_product_category_xref table to replace the product_list with the ordering column
 */
class VirtueMartModelProduct extends JModel {

	/**
	 * products object
	 * @var integer
	 */
	var $products  = null ;

	/**
	 * @var integer Primary key
	 * @access private
	 */
    private $_id;

	var $_total;
	var $_pagination;

	function __construct() {
		parent::__construct();

		// Get the pagination request variables
		$mainframe = JFactory::getApplication() ;
		$limit = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$limitstart = $mainframe->getUserStateFromRequest( JRequest::getVar('option').JRequest::getVar('view').'.limitstart', 'limitstart', 0, 'int' );

		// In case limit has been changed, adjust limitstart accordingly
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
		if (!class_exists( 'TableMedia' )) require(JPATH_VM_ADMINISTRATOR.DS.'tables'.DS.'media.php');
	}

	 /**
     * Resets the category id and data
     *
     * @author Max Milbers
     */
    public function setId($id){
    	if($this->_id!=$id){
			$this->_id = (int)$id;
			$this->_data = null;
    	}
    	return $this->_id;
    }

	/**
	 * Loads the pagination
	 */
    public function getPagination() {
		if ($this->_pagination == null) {
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}
		return $this->_pagination;
	}


//	function getPagination() {
//        if (empty($this->_pagination)) {
//            jimport('joomla.html.pagination');
//            $this->_pagination = new JPagination($this->getTotalProductsInCategory(), $this->getState('limitstart'), $this->getState('limit') );
//        }
//        return $this->_pagination;
//	}

	/**
	 * Gets the total number of products
	 */
	private function getTotal() {
    	if (empty($this->_total)) {
//    		$this->_db = JFactory::getDBO();

			$q = "SELECT #__vm_product.`product_id` ".$this->getProductListQuery().$this->getProductListFilter();
			$this->_db->setQuery($q);
			$fields = $this->_db->loadObjectList('product_id');
			$this->_total = count($fields);
        }

        return $this->_total;
    }

//  function getTotalProductsInCategory()
//  {
//        // Load the content if it doesn't already exist
//        if (empty($this->_total)) {
//            if (empty($this->_query)) $this->_query = $this->_buildQuery();
//            $this->_total = $this->_getListCount($this->_query);
//        }
//        return $this->_total;
//  }

     /**
     * Get the simple product info
     * @author RolandD
     */
     public function getProductDetails() {
     	$cids = JRequest::getVar('cid');
     	$q = "SELECT * FROM #__vm_product WHERE product_id = ".$cids[0];
     	$this->_db->setQuery($q);
     	return $this->_db->loadObject();
     }

    /**
     * This function creates a product with the attributes of the parent.
     *
     * @param int $product_id
     * @param boolean $front for frontend use
     * @param boolean $withCalc calculate prices?
     */
    public function getProduct($product_id = null,$front=true, $withCalc = true, $onlyPublished = true){

    	if (empty($product_id)) {
			$product_id = JRequest::getInt('product_id', 0);
		}
		$this->setId($product_id);

    	$child = $this->getProductSingle($product_id,$front, $withCalc,$onlyPublished);

    	//store the original parent id
		$pId = $child->product_id;
    	$ppId = $child->product_parent_id;
		$published = $child->published;

		$i = 0;
		//Check for all attributes to inherited by parent products
    	while(!empty($child->product_parent_id)){
    		$parentProduct = $this->getProductSingle($child->product_parent_id,$front, $withCalc,$onlyPublished);
    	    $attribs = get_object_vars($parentProduct);

	    	foreach($attribs as $k=>$v){
				if(is_array($child->$k)){
					if(!is_array($v)) $v =array($v);
					$child->$k = array_merge($child->$k,$v);//dump($tmp,'$tmp array '.$k);dump($child->$k,'$tmp array '.$k.' and '.$v);
				} else{
					if(empty($child->$k)){
						$child->$k = $v;
					}
				}
	    	}
			$child->product_parent_id = $parentProduct->product_parent_id;
    	}
		$child->published = $published;
		$child->product_id = $pId;
		$child->product_parent_id = $ppId;

    	return $child;
    }

    public function getProductSingle($product_id = null,$front=true, $withCalc = true, $onlyPublished=true){
    	if (empty($product_id)) {
			$product_id = JRequest::getInt('product_id', 0);
		}
		$product_id = $this->setId($product_id);

//		if(empty($this->_data)){
			if (!empty($product_id)) {
//			if($front){
//				$q = 'SELECT p.*, mx.manufacturer_id, m.mf_name ';
//			} else {
//				$q = 'SELECT p.*, mx.manufacturer_id, pp.* ';
//			}
//			$q .= 'FROM #__vm_product AS p
//					LEFT JOIN `#__vm_product_mf_xref` mx
//					ON mx.`product_id` = `p`.`product_id` ';
//			if($front){
//				$q .= 'LEFT JOIN `#__vm_product_category_xref` x
//				ON x.`product_id` = `p`.`product_id`
//				LEFT JOIN `#__vm_manufacturer` `m`
//				ON `m`.`manufacturer_id` = `mx`.`manufacturer_id` ';
//			} else{
//				$q .= 'LEFT JOIN #__vm_product_price AS pp
//				ON p.product_id = pp.product_id ';
//			}
//			$q .= 'WHERE p.product_id = '.$product_id.' ';
//
//			$this->_db->setQuery($q);
//			$product = $this->_db->loadObject();

   			$product = $this->getTable('product');
   			$product->load($product_id);
   			if($onlyPublished){
   				if(empty($product->published)){
   					return $this->fillVoidProduct($product,$front);
   				}
   			}

   			$xrefTable = $this->getTable('product_media_xref');
			$product->file_ids = $xrefTable->load((int)$this->_id);

//			if(!empty($product->file_ids)){
//				$product->file_ids = explode(',',$product->file_ids);
//			}

//   			if(!$front){
    			$ppTable = $this->getTable('product_price');
    			$q = 'SELECT `product_price_id` FROM `#__vm_product_price` WHERE `product_id` = "'.$product_id.'" ';
				$this->_db->setQuery($q);
    			$ppId = $this->_db->loadResult();
   				$ppTable->load($ppId);
				$product = (object) array_merge((array) $ppTable, (array) $product);
//   			}

   			$q = 'SELECT `manufacturer_id` FROM `#__vm_product_mf_xref` WHERE `product_id` = "'.$product_id.'" ';
   			$this->_db->setQuery($q);
   			$mf_id = $this->_db->loadResult();

   			$mfTable = $this->getTable('manufacturer');
   			$mfTable->load((int)$mf_id);
   			$product = (object) array_merge((array) $mfTable, (array) $product);


			/* Load the categories the product is in */
			$product->categories = $this->getProductCategories($product_id);
			$product->category_id = JRequest::getInt('category_id', 0);
			if (empty($product->category_id) && isset($product->categories[0])) $product->category_id = $product->categories[0];

   			if(!$front && !empty($product->categories[0])){
				$catTable = $this->getTable('category');
   				$catTable->load($product->categories[0]);
				$product->category_name = $catTable->category_name;
   			} else {
   				$product->category_name ='';
   			}

			if($front){

				/* Load the attributes */
//				$product->attributes = $this->getAttributes($product);

				/* Load the variants */
//				$product->variants = $this->getVariants($product);

				/* Load the price */
				$prices = "";
//				if (VmConfig::get('show_prices',1) == '1' && $withCalc) {
				if ($withCalc) {

					/* Loads the product price details */
					if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');
					$calculator = calculationHelper::getInstance();

					/* Calculate the modificator */
					$product_type_modificator = 0; //$calculator->calculateModificators($product->product_id,$product->variants);
					//$product_type_modificator = $calculator->calculateModificators($product->product_id,$product->variants);
					//I need here the choosen ids of the customfields
				  // getProductcustomfieldsIds HAVE THE CUSTOM FIELDS ID
					$product->ProductcustomfieldsIds = $this->getProductcustomfieldsIds($product);
					$quantityArray = JRequest::getVar('quantity',1,'post');
					$prices = $calculator->getProductPrices((int)$product->product_id,$product->categories,$product->ProductcustomfieldsIds,$quantityArray[0]);
				}

				$product->prices = $prices;

				/* Add the product link  */
				$product->link = JRoute::_('index.php?option=com_virtuemart&view=productdetails&product_id='.$product_id.'&category_id='.$product->category_id);

				/* Load the neighbours */
				$product->neighbours = $this->getNeighborProducts($product);

				/* Fix the product packaging */
				if ($product->product_packaging) {
					$product->packaging = $product->product_packaging & 0xFFFF;
					$product->box = ($product->product_packaging >> 16) & 0xFFFF;
				}
				else {
					$product->packaging = '';
					$product->box = '';
				}

				/* Load the related products */
				$product->related = $this->getRelatedProducts($product_id);

				/* Load the vendor details */
				if(!class_exists('VirtueMartModelVendor')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'vendor.php');
				$product->vendor_name = VirtueMartModelVendor::getVendorName($product->vendor_id);

				/* Check for child products */
				$product->haschildren = $this->checkChildProducts($product_id);

				/* Check for product types */
				$product->hasproducttypes = $this->hasProductType($product_id);

				/* Load the custom variants */
//				$product->customvariants = $this->getCustomVariants($product->custom_attribute); OBSELETE
				$product->hasproductCustoms = $this->hasproductCustoms($product_id);
				/* Load the custom product fields */
				$product->customfields = self::getproductCustomsField($product);

				/*  custom product fields for add to cart */
				$product->customfieldsCart = self::getproductCustomsFieldCart($product);

				/* Check the order levels */
				if (empty($product->product_order_levels)) $product->product_order_levels = '0,0';

				/* Check the stock level */
				if (empty($product->product_in_stock)) $product->product_in_stock = 0;

				/* Handle some child product data */
//				if ($product->product_parent_id > 0) {
//					/* Get the attributes */
//					$product->attributes = $this->getAttributes($product);
//				}

				/* Get stock indicator */
				$product->stock = $this->getStockIndicator($product);

				/* Get the votes */
				$product->votes = $this->getVotes($product_id);

				}
				else
				$product->customfields = self::getproductCustomslist($product_id);
			} else {
				$product = new stdClass();
				return $this->fillVoidProduct($product,$front);
			}
//		}
		$this->product = $product;
		return $product;
    }

    private function fillVoidProduct($product,$front=true){

		/* Load an empty product */
	 	 $product = $this->getTable();
	 	 $product->load();

	 	 /* Add optional fields */
	 	 $product->manufacturer_id = null;
	 	 $product->product_price_id = null;
	 	 $product->product_price = null;
	 	 $product->product_currency = null;
	 	 $product->product_price_quantity_start = null;
	 	 $product->product_price_quantity_end = null;
	 	 $product->product_tax_id = null;
	 	 $product->product_discount_id = null;
	 	 if($front){
	 	 	$product->link = '';
	 	 	$product->categories = array();
	 	 	$product->prices = array();
	 	 	$product->variants = array();// I MEAN OBSELETE ?PK
	 	 	$product->category_id = 0;
	 	 	$product->customvariants = array(); // OBSELETE ?PK TODO Add user comment in custom fields and methode for card
	 	 	$product->mf_name = '';
	 	 	$product->packaging = '';
	 	 	$product->related = '';
	 	 	$product->box = '';

	 	 }

	 	 return $product;
    }

	/**
	* Load  the product category
	*
	* @author Kohl Patrick,RolandD,Max Milbers
	* @return array list of categories product is in
	*/
	private function getProductCategories($product_id=0) {

		if(!empty($this->_db))$this->_db = JFactory::getDBO();

		$categories = array();
		if ($product_id > 0) {
			$q = 'SELECT `category_id` FROM `#__vm_product_category_xref` WHERE `product_id` = "'.$product_id.'"';
			$this->_db->setQuery($q);
			$categories = $this->_db->loadResultArray();
		}

		return $categories;
	}

	/**
	 * Returns products for given array of ids
	 *
	 * @author Max Milbers
	 * @param unknown_type $productIds
	 * @param unknown_type $front
	 * @param unknown_type $withCalc
	 * @param unknown_type $onlyPublished
	 */
	public function getProducts($productIds, $front=true, $withCalc = true, $onlyPublished = true){

		$products=array();
		foreach($productIds as $id){
			if($product = $this->getProduct($id)){
				$products[] = $product;
			}
		}
		return $products;
	}

	/**
	* Load any related products
	*
	* @author RolandD
	* @todo Do we need to give this link a category ID?
	* @param int $product_id The ID of the product
	* @return array containing all the files and their data
	*/
	public function getRelatedProducts($product_id) {
		$this->_db = JFactory::getDBO();
		$q = "SELECT `p`.`product_id`, `product_sku`, `product_name`, related_products
			FROM `#__vm_product` p, `#__vm_product_relations` `r`
			WHERE `r`.`product_id` = ".$product_id."
			AND `p`.published = 1
			AND FIND_IN_SET(`p`.`product_id`, REPLACE(`r`.`related_products`, '|', ',' )) LIMIT 0, 4";
		$this->_db->setQuery($q);
		$related_products = $this->_db->loadObjectList();

		/* Get the price also */
		if (VmConfig::get('show_prices') == '1') {
			/* Loads the product price details */
			if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');
			$calculator = calculationHelper::getInstance();
			if(!empty($related_products)){
				foreach ($related_products as $rkey => $related) {
					$related_products[$rkey]->price = $calculator->getProductPrices($related->product_id);
					$cats = $this->getProductCategories($related->product_id);
					if(!empty($cats))$related->category_id = $cats[0]; //else $related->category_id = 0;
					/* Add the product link  */
					$related_products[$rkey]->link = JRoute::_('index.php?option=com_virtuemart&view=productdetails&product_id='.$related->product_id.'&category_id='.$related->category_id);
				}
			}

		}

		return $related_products;
	}


	/**
	* Get the products in a given category
	*
	* @author RolandD
	* @access public
	* @param int $category_id the category ID where to get the products for
	* @return array containing product objects
	*/
	public function getProductsInCategory() {

		if (empty($this->products)) {

			if (empty($this->_query)) $this->_query = $this->_buildQuery();
			$product_ids = $this->_getList($this->_query, $this->getState('limitstart'), $this->getState('limit'));

			/* Collect the product data */

			foreach ($product_ids as $product_id) {
				$this->products[] = $this->getProduct($product_id->product_id);
			}
		}
		return $this->products;
	}

	function _buildQuery()
	{
		//$mainframe = Jfactory::getApplication();
		//$option = JRequest::getWord('option');
		//$mainframe->getUserStateFromRequest( $option.'order'  , 'order' ,''	,'word' ) );
		$category_id = JRequest::getInt('category_id', 0 );

		$filter_order  = JRequest::getVar('orderby', VmConfig::get('browse_orderby_field','product_id'));

		$filter_order_Dir = JRequest::getVar('order', 'ASC');

		$search = JRequest::getVar('search', false );
		$joinCategory = false ;
		$joinMf = false ;
		$joinPrice = false ;
		$where[] = " `#__vm_product`.`published`='1' ";

		/* search fields filters set */
		if ( $search == 'true') {
			$keyword = trim( str_replace(' ', '%', JRequest::getVar('keyword', '') ) );
			$searchFields = VmConfig::get('browse_search_fields');
			foreach ($searchFields as $searchField) {
				if (($searchField == 'category_name') || ($searchField == 'category_description')) $joinCategory = true ;
				if ($searchField == 'mf_name') $joinMf = true ;
				if ($searchField == 'product_price') $joinPrice = true ;
				$filter_search[] = " `".$searchField."` LIKE '%".$keyword."%' ";
			}
			$where[] = " ( ".implode(' OR ', $filter_search )." ) ";
		}
		if ($category_id>0){
			$joinCategory = true ;
			$where[] = ' `#__vm_product_category_xref`.`category_id` = '.$category_id;
		}
		/* sanitize $filter_order and dir */
		$browse_orderby_fields = VmConfig::get('browse_orderby_fields') ;
		if (!is_array($browse_orderby_fields)) $browse_orderby_fields = array($browse_orderby_fields);
		if (!in_array($filter_order, $browse_orderby_fields)) {
			$filter_order = VmConfig::get('browse_orderby_field');
		}

		$manufacturer_id = JRequest::getInt('manufacturer_id', false );
		if ($manufacturer_id) {
			$joinMf = true ;
			$where[] = ' `#__vm_product_mf_xref`.`manufacturer_id` = '.$manufacturer_id;
		}

		/* search Order fields set */

		if (VmConfig::get('check_stock') && Vmconfig::get('show_out_of_stock_products') != '1')
			$where[] = ' `product_in_stock` > 0 ';
		// special  orders case
		switch ($filter_order) {
			case 'product_special':
				$where[] = ' `#__vm_product`.`product_special`="Y" ';// TODO Change  to  a  individual button
				break;
			case 'category_name':
				$orderBy = ' ORDER BY `category_name` ';
				$joinCategory = true ;
				break;
			case 'category_description':
				$orderBy = ' ORDER BY `category_description` ';
				$joinCategory = true ;
				break;
			case 'mf_name':
				$orderBy = ' ORDER BY `mf_name` ';
				$joinMf = true ;
				break;
			case 'product_price':
				$orderBy = ' ORDER BY `product_price` ';
				$joinPrice = true ;
				break;
			default ;
				$orderBy = ' ORDER BY `#__vm_product`.`'.$filter_order.'` ';
				break;
		}

		$query = "SELECT `#__vm_product`.`product_id` FROM `#__vm_product` ";
		if ($joinCategory == true) {
			$query .= ' LEFT JOIN `#__vm_product_category_xref` ON `#__vm_product`.`product_id` = `#__vm_product_category_xref`.`product_id`
			 LEFT JOIN `#__vm_category` ON `#__vm_category`.`category_id` = `#__vm_product_category_xref`.`category_id`';
		}
		if ($joinMf == true) {
			$query .= ' LEFT JOIN `#__vm_product_mf_xref` ON `#__vm_product`.`product_id` = `#__vm_product_mf_xref`.`product_id`
			 LEFT JOIN `#__vm_manufacturer` ON `#__vm_manufacturer`.`manufacturer_id` = `#__vm_product_mf_xref`.`manufacturer_id` ';
		}
		if ($joinPrice == true) {
			$query .= ' LEFT JOIN `#__vm_product_price` ON `#__vm_product`.`product_id` = `#__vm_product_price`.`product_id` ';
		}
		$query .= ' WHERE '.implode(" AND ", $where ).' GROUP BY `#__vm_product`.`product_id` '. $orderBy .$filter_order_Dir ;
		return $query ;
	}

	/**
	 * This function retrieves the "neighbor" products of a product specified by $product_id
	 * Neighbors are the previous and next product in the current list
	 *
	 * @author RolandD, Max Milbers
	 * @param object $product The product to find the neighours of
	 * @return array
	 */
	private function getNeighborProducts($product) {
		$this->_db = JFactory::getDBO();
		$neighbors = array('previous' => '','next' => '');

		$q = "SELECT x.`product_id`, product_list, `p`.product_name
			FROM `#__vm_product_category_xref` x
			LEFT JOIN `#__vm_product` `p`
			ON `p`.`product_id` = `x`.`product_id`
			WHERE `category_id` = ".$product->category_id."
			ORDER BY `product_list`, `x`.`product_id`";
		$this->_db->setQuery($q);
		$products = $this->_db->loadAssocList('product_id');

		/* Move the internal pointer to the current product */
		if(!empty($products)){
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

		return false;
	}


    public function getGroupProducts($group, $vendorId='1', $categoryId='', $nbrReturnProducts) {

	    switch ($group) {
			case 'featured':
				$filter = 'AND `#__vm_product`.`product_special`="Y" ';
				break;
			case 'latest':
				$filter = 'AND `#__vm_product`.`mdate` > '.(time()-(60*60*24*7)).' ';
				break;
			case 'random':
				$filter = '';
				break;
			case 'topten';
				$filter ='';
		}

		$cat_xref_table = $categoryId? ', `#__vm_product_category_xref` ':'';
		$query = 'SELECT `product_id` ';
		$query .= 'FROM `#__vm_product`'.$cat_xref_table.' WHERE `product_id` > 0 ';
//	        $query  = 'SELECT `product_sku`,`#__vm_product`.`product_id`, `#__vm_product_category_xref`.`category_id`,`product_name`, `product_s_desc`, `#__vm_product`.`file_ids`, `product_in_stock`, `product_url` ';
//	        $query .= 'FROM `#__vm_product`, `#__vm_product_category_xref`, `#__vm_category` WHERE ';
//	        $query .= '(`#__vm_product`.`product_parent_id`="" OR `#__vm_product`.`product_parent_id`="0") ';
//	        $query .= 'AND `#__vm_product`.`product_id`=`#__vm_product_category_xref`.`product_id` ';
			if ($categoryId) {
				$query .= 'AND `#__vm_category`.`category_id`=`#__vm_product_category_xref`.`category_id` ';
				$query .= 'AND `#__vm_category`.`category_id`=' . $categoryId . ' ';
			}
	        $query .= 'AND `#__vm_product`.`published`="1" ';
	        $query .= $filter;
	        if (VmConfig::get('check_stock') && VmConfig::get('show_out_of_stock_products') != '1') {
		        $query .= ' AND `product_in_stock` > 0 ';
	        }
			$query .= ' group by `#__vm_product`.`product_id` ';

			if ( $group =='topten') {
				$query .= 'ORDER BY product_sales DESC LIMIT 0, '.(int)$nbrReturnProducts;
			} else {
				$query .= 'ORDER BY RAND() LIMIT 0, '.(int)$nbrReturnProducts;
			}

        $this->_db->setQuery($query);
		$ids = $this->_db->loadResultArray();

		$result=array();
		/* Check if we have any products */
		if($ids) {
			if ($show_prices=VmConfig::get('show_prices',1) == '1'){
				if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');
				$calculator = calculationHelper::getInstance();
			}

			/* Add some extra info */
			foreach ($ids as $id) {

				$featured = $this->getProduct($id);
				/* Product price */
				$price = "";
				if ($show_prices) {
					/* Loads the product price details */
					//Todo check if it is better just to use $featured, but needs redoing the sql above
					$price = $calculator->getProductPrices((int)$featured->product_id);
				}
				$featured->prices = $price;

				/* Child products */
				$featured->haschildren = $this->checkChildProducts($featured->product_id);

				/* Attributes */
//				$featured->hasattributes = $this->checkAttributes($featured->product_id, true);

				$result[] = $featured;
			}

		}
		dump($result, 'getGroupProducts back');

		return $result;
    }

    /**
     * Select the products to list on the product list page
     */
    public function getProductList() {
     	/* Pagination */
     	$this->getPagination();

		$cat_xref_table = (JRequest::getInt('category_id', 0) > 0)? ', `#__vm_product_category_xref` ':'';
     	$q = 'SELECT `#__vm_product`.`product_id` FROM `#__vm_product`'.$cat_xref_table.' '.$this->getProductListFilter();

     	$this->_db->setQuery($q, $this->_pagination->limitstart, $this->_pagination->limit);
     	$productIdList = $this->_db->loadResultArray();
//     	$app = JFactory::getApplication();
//     	$app -> enqueueMessage($this->_db->getQuery());

     	$products = array();
     	if(!empty($productIdList)){
     		foreach ($productIdList as $id){
     			$products[] = $this->getProduct($id,false,false,false);
     		}
     	}
     	return $products;
    }

	/**
	* Create a list of products for JSON return
	*/
	public function getProductListJson() {
//		$this->_db = JFactory::getDBO();
		$filter = JRequest::getVar('q', false);
		$q = "SELECT product_id AS id, CONCAT(product_name, '::', product_sku) AS value
			FROM #__vm_product";
		if ($filter) $q .= " WHERE product_name LIKE '%".$filter."%'";
		$this->_db->setQuery($q);
		return $this->_db->loadObjectList();
	}

    /**
    * List of tables to include for the product query
    * @author RolandD
    */
    private function getProductListQuery() {
    	return 'FROM #__vm_product
			LEFT OUTER JOIN #__vm_product_price
			ON #__vm_product.product_id = #__vm_product_price.product_id
			LEFT OUTER JOIN #__vm_product_mf_xref
			ON #__vm_product.product_id = #__vm_product_mf_xref.product_id
			LEFT OUTER JOIN #__vm_manufacturer
			ON #__vm_product_mf_xref.manufacturer_id = #__vm_manufacturer.manufacturer_id
			LEFT OUTER JOIN #__vm_product_attribute
			ON #__vm_product.product_id = #__vm_product_attribute.product_id
			LEFT OUTER JOIN #__vm_product_category_xref
			ON #__vm_product.product_id = #__vm_product_category_xref.product_id
			LEFT OUTER JOIN #__vm_category
			ON #__vm_product_category_xref.category_id = #__vm_category.category_id
			LEFT OUTER JOIN #__vm_category_xref
			ON #__vm_category.category_id = #__vm_category_xref.category_child_id
			LEFT OUTER JOIN #__vm_vendor
			ON #__vm_product.vendor_id = #__vm_vendor.vendor_id';
    }

    /**
    * Collect the filters for the query
    * @author RolandD
    */
    private function getProductListFilter() {
//    	$this->_db = JFactory::getDBO();
    	$filters = array();
    	/* Check some filters */
    	$filter_order = JRequest::getCmd('filter_order', 'product_name');
		if ($filter_order == '') $filter_order = 'product_name';
		$filter_order_Dir = JRequest::getWord('filter_order_Dir', 'desc');
		if ($filter_order_Dir == '') $filter_order_Dir = 'desc';

		/* Product Parent ID */
     	if (JRequest::getInt('product_parent_id', 0) > 0) $filters[] = '#__vm_product.`product_parent_id` = '.JRequest::getInt('product_parent_id');
     	else // $filters[] = '#__vm_product.`product_parent_id` = 0';
     	/* Category ID */
     	if (JRequest::getInt('category_id', 0) > 0){
     		$filters[] = '`#__vm_product_category_xref`.`category_id` = '.JRequest::getInt('category_id');
     		$filters[] = '`#__vm_product`.`product_id` = `#__vm_product_category_xref`.`product_id`';
     	}
     	/* Product name */
     	if (JRequest::getVar('filter_product', false)) $filters[] = '#__vm_product.`product_name` LIKE '.$this->_db->Quote('%'.JRequest::getVar('filter_product').'%');
     	/* Product type ID */
     	//if (JRequest::getInt('product_type_id', false)) $filters[] = '#__vm_product.`product_name` LIKE '.$this->_db->Quote('%'.JRequest::getVar('filter_product').'%');
     	/* Time filter */
     	if (JRequest::getVar('search_type', '') != '') {
     		$search_order = JRequest::getVar('search_order') == 'bf' ? '<' : '>';
     		switch (JRequest::getVar('search_type')) {
     			case 'product':
     				$filters[] = '#__vm_product.`mdate` '.$search_order.' '.strtotime(JRequest::getVar('search_date'));
     				break;
     			case 'price':
     				$filters[] = '#__vm_product_price.`mdate` '.$search_order.' '.strtotime(JRequest::getVar('search_date'));
     				break;
     			case 'withoutprice':
     				$filters[] = '#__vm_product_price.`product_price` IS NULL';
     				break;
     		}
     	}
     	if (count($filters) > 0) $filter = ' WHERE '.implode(' AND ', $filters).' GROUP BY #__vm_product.`product_id` ORDER BY '.$filter_order." ".$filter_order_Dir;
     	else $filter = ' GROUP BY #__vm_product.`product_id` ORDER BY '.$filter_order." ".$filter_order_Dir;
     	return $filter;
    }


    /**
    * Check if the product has any children
    *
    * @author RolandD
    * @param int $product_id Product ID
    * @return bool True if there are child products, false if there are no child products
    */
    public function checkChildProducts($product_id) {
//     	$this->_db = JFactory::getDBO();
     	$q  = "SELECT IF(COUNT(product_id) > 0, 'Y', 'N') FROM `#__vm_product` WHERE `product_parent_id` = ".$product_id;
     	$this->_db->setQuery($q);
     	if ($this->_db->loadResult() == 'Y') return true;
     	else if ($this->_db->loadResult() == 'N') return false;
    }

	/**
	 * Publish/Unpublish all the ids selected
     *
     * @author Max Milbers
     * @param boolean $publishId True is the ids should be published, false otherwise.
     * @return boolean True is the publishing was successful, false otherwise.
     */
	public function publish($publishId = false){

		if(!class_exists('modelfunctions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'modelfunctions.php');
		return modelfunctions::publish('cid','product',$publishId);

	}

    /**
	 * Retrieve a list of featured products from the database.
	 *
	 * @param string $group Specifies what kind of products need to be loaded (featured or latest)
	 * @param int $categoryId Id of the category to lookup, null for all categories
	 * @param int $nbrReturnProducts Number of products to return
	 * @return object List of  products
	 */


    /**
     * Saves products according to their order
     * @author RolandD
     */
    public function getSaveOrder() {
//    	$this->_db = JFactory::getDBO();
    	$mainframe = Jfactory::getApplication('site');
    	$order = JRequest::getVar('order');
    	$category_id = JRequest::getInt('category_id');

    	/* Check if all the entries are numbers */
		foreach( $order as $list_id ) {
			if( !is_numeric( $list_id ) ) {
				$mainframe->enqueueMessage(JText::_('COM_VIRTUEMART_SORT_ERR_NUMBERS_ONLY'), 'error');
				return false;
			}
		}

		/* Get the list of product IDs */
		$q = "SELECT product_id
			FROM #__vm_product_category_xref
			WHERE category_id = ".$category_id;
		$this->_db->setQuery($q);
		$product_ids = $this->_db->loadResultArray();

		foreach( $order as $key => $list_id ) {
			$q = "UPDATE #__vm_product_category_xref ";
			$q .= "SET product_list = ".$list_id;
			$q .= " WHERE category_id ='".$category_id."' ";
			$q .= " AND product_id ='".$product_ids[$key]."' ";
			$this->_db->setQuery($q);
			$this->_db->query();
		}
	}

	/**
     * Saves products according to their order
     * @author RolandD
     */
    public function getOrderUp() {
//    	$this->_db = JFactory::getDBO();
    	$cids = JRequest::getVar('cid');
    	$cid = (int)$cids[0];
    	$category_id = JRequest::getInt('category_id');

    	$q = "SELECT product_id, product_list
    		FROM #__vm_product_category_xref
    		WHERE category_id = ".$category_id."
    		ORDER BY product_list";
    	$this->_db->setQuery($q);
    	$products = $this->_db->loadAssocList('product_id');
    	$keys = array_keys($products);
    	while (current($keys) !== $cid) next($keys);

    	/* Get the previous ID */
    	$prev_id = prev($keys);

    	/* Check if a previous product_list exists */
    	if (is_null($products[$prev_id]['product_list'])) {
    		$products[$prev_id]['product_list'] = $prev_id;
    	}

    	/* Check if the product_listings are the same */
    	if ($products[$prev_id]['product_list'] == $products[$cid]['product_list']) {
    		$products[$cid]['product_list']++;
    	}

    	/* Update the current product */
		$q = "UPDATE #__vm_product_category_xref
			SET product_list = ".$products[$prev_id]['product_list']."
			WHERE category_id = ".$category_id."
			AND product_id = ".$products[$cid]['product_id'];
		$this->_db->setQuery($q);
		$this->_db->query();

		/* Check if a next product_list exists */
    	if (is_null($products[$cid]['product_list'])) {
    		$products[$cid]['product_list'] = $prev_id+1;
    	}
		/* Update the previous product */
		$q = "UPDATE #__vm_product_category_xref
			SET product_list = ".$products[$cid]['product_list']."
			WHERE category_id = ".$category_id."
			AND product_id = ".$products[$prev_id]['product_id'];
		$this->_db->setQuery($q);
		$this->_db->query();
	}

	/**
     * Saves products according to their order
     * @author RolandD
     */
    public function getOrderDown() {
//    	$this->_db = JFactory::getDBO();
    	$cids = JRequest::getVar('cid');
    	$cid = (int)$cids[0];
    	$category_id = JRequest::getInt('category_id');

    	$q = "SELECT product_id, product_list
    		FROM #__vm_product_category_xref
    		WHERE category_id = ".$category_id."
    		ORDER BY product_list";
    	$this->_db->setQuery($q);
    	$products = $this->_db->loadAssocList('product_id');
    	$keys = array_keys($products);
    	while (current($keys) !== $cid) next($keys);

    	/* Get the next ID */
    	$next_id = next($keys);

    	/* Check if a previous product_list exists */
    	if (is_null($products[$next_id]['product_list'])) {
    		$products[$next_id]['product_list'] = $next_id;
    	}

    	/* Check if the product_listings are the same */
    	if ($products[$next_id]['product_list'] == $products[$cid]['product_list']) {
    		$products[$cid]['product_list']--;
    	}

    	/* Update the current product */
		$q = "UPDATE #__vm_product_category_xref
			SET product_list = ".$products[$next_id]['product_list']."
			WHERE category_id = ".$category_id."
			AND product_id = ".$products[$cid]['product_id'];
		$this->_db->setQuery($q);
		$this->_db->query();

		/* Check if a next product_list exists */
    	if (is_null($products[$cid]['product_list'])) {
    		$products[$cid]['product_list'] = $next_id-1;
    	}
		/* Update the next product */
		$q = "UPDATE #__vm_product_category_xref
			SET product_list = ".$products[$cid]['product_list']."
			WHERE category_id = ".$category_id."
			AND product_id = ".$products[$next_id]['product_id'];
		$this->_db->setQuery($q);
		$this->_db->query();
	}

	//TODO merge getRelatedProducts functions
	/**
	 * Get the related products
	 */
//	 public function getRelatedProducts($product_id=false) {
//	 	 if (!$product_id) return array();
//	 	 else {
////			$this->_db = JFactory::getDBO();
//			$q = "SELECT related_products FROM #__vm_product_relations WHERE product_id='".$product_id."'";
//			$this->_db->setQuery($q);
//			$results = $this->_db->loadResult();
//			if ($results) {
//				$ids = 'product_id =' . implode(' OR product_id =', explode("|", $results));
//				$q = "SELECT product_id AS id, CONCAT(product_name, '::', product_sku) AS text
//					FROM #__vm_product
//					WHERE (".$ids.")";
//				$this->_db->setQuery($q);
//				return $this->_db->loadObjectList();
//			}
//			else return false;
//		 }
//	 }

	/**
	* Store a product
	*
	* @author RolandD
	* @author Max Milbers
	* @access public
	*/
	public function saveProduct($product=false) {

		/* Load the data */
		if($product){
			$data = (array)$product;
		} else{
			$data = JRequest::get('post', 4);
		}

		/* Setup some place holders */
		$product_data = $this->getTable('product');

		/* Load the old product details first */
		$product_data->load($data['product_id']);

		/* Get the product data */
		if (!$product_data->bind($data)) {
			$this->setError($product_data->getError());
			return false;
		}

		/* Get the attribute */
		$product_data->attribute = $this->formatAttributeX();

        /* Set the product packaging */
        $product_data->product_packaging = (($data['product_box'] << 16) | ($data['product_packaging']&0xFFFF));

        /* Set the order levels */
        $product_data->product_order_levels = $data['min_order_level'].','.$data['max_order_level'];

        if (!$product_data->check()) {
			$this->setError($product_data->getError());
			return false;
		}

        /* Store the product */
		if (!$product_data->store()) {
			$this->setError($product_data->getError());
			return false;
		}

		if(empty($data['product_id'])){
			$dbv = $product_data->getDBO();
			//I dont like the solution to use three variables
			$this->_id = $product_data->product_id = $data['product_id'] = $dbv->insertid();
		}

		if(!empty($data['file_ids'])){
			// Process the images
			if(!class_exists('VirtueMartModelMedia')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'media.php');
			$mediaModel = new VirtueMartModelMedia();
			$xrefTable = $this->getTable('product_media_xref');
			$mediaModel->storeMedia($data,$xrefTable,'product');
		}

		$product_price_table = $this->getTable('product_price');

		if (!$product_price_table->bind($data)) {
			$this->setError($product_price_table->getError());
			return false;
		}

		$setPriceTable=FALSE;
		foreach($product_price_table->getPublicProperties() as $property=>$ppvalue){
			if(!empty($ppvalue)){
				$setPriceTable=TRUE;
				break;
			}
		}

		if($setPriceTable){
			dump($product_price_table,'price table');
			// Make sure the price record is valid
			if (!$product_price_table->check()) {
				$this->setError($product_price_table->getError());
				return false;
			}

			// Save the price record to the database
			if (!$product_price_table->store()) {
				$this->setError($product_price_table->getError());
				return false;
			}
		}


		/* Update manufacturer link */
		if(!empty($data['manufacturer_id'])){
			if(!class_exists('modelfunctions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'modelfunctions.php');
			modelfunctions::storeArrayData('#__vm_product_mf_xref','product_id','manufacturer_id',$product_data->product_id,$data['manufacturer_id']);
		}

//		$q = 'INSERT INTO #__vm_product_mf_xref  (product_id, manufacturer_id) VALUES (';
//		$q .= $product_data->product_id.', ';
//		$q .= JRequest::getInt('manufacturer_id').') ';
//		$q .= 'ON DUPLICATE KEY UPDATE manufacturer_id = '.JRequest::getInt('manufacturer_id');
//		$this->_db->setQuery($q);
//		$this->_db->query();

		/* Update waiting list  */
		if ($data['product_in_stock'] > 0 && $data['notify_users'] == '1' ) {
			$waitinglist = new VirtueMartModelWaitingList();
			$waitinglist->notifyList($data['product_id']);
		}

//		/* If is Item, update attributes */
//		if ($product_data->product_parent_id > 0) {
//			$q  = 'SELECT attribute_id FROM #__vm_product_attribute ';
//			$q .= 'WHERE product_id='.$product_data->product_id;
//			$this->_db->setQuery($q);
//			$attributes = $this->_db->loadObjectList();
//			foreach ($attributes as $id => $attribute) {
//				$q  = 'UPDATE #__vm_product_attribute SET ';
//				$q .= 'attribute_value='.$this->_db->Quote($data['attribute_'.$attribute->attribute_id]);
//				$q .= ' WHERE attribute_id = '.$attribute->attribute_id;
//				$this->_db->setQuery($q);
//				$this->_db->query();
//
//			}
//		/* If it is a Product, update Category */
//		}
//		else {
		if(!empty($data['categories']) && count($data['categories'])>0){
			/* Delete old category links */
			$q  = "DELETE FROM `#__vm_product_category_xref` ";
			$q .= "WHERE `product_id` = '".$product_data->product_id."' ";
			$this->_db->setQuery($q);
			$this->_db->Query();

			/* Store the new categories */
			foreach( $data["categories"] as $category_id ) {
				$this->_db->setQuery('SELECT IF(ISNULL(`product_list`), 1, MAX(`product_list`) + 1) as list_order FROM `#__vm_product_category_xref` WHERE `category_id`='.$category_id );
				$list_order = $this->_db->loadResult();

				$q  = "INSERT INTO #__vm_product_category_xref ";
				$q .= "(category_id,product_id,product_list) ";
				$q .= "VALUES ('".$category_id."','". $product_data->product_id . "', ".$list_order. ")";
				$this->_db->setQuery($q);
				$this->_db->query();
			}
		}

		/* Update related products */
		if (array_key_exists('related_products', $data)) {
			/* Insert Pipe separated Related Product IDs */
			$q = "REPLACE INTO #__vm_product_relations (product_id, related_products)";
			$q .= " VALUES( '".$product_data->product_id."', '".implode('|', $data['related_products'])."') ";
			$this->_db->setQuery($q);
			$this->_db->query();
		}
		else {
			$q = "DELETE FROM #__vm_product_relations WHERE product_id='".$product_data->product_id."'";
			$this->_db->setQuery($q);
			$this->_db->query();
		}
		/* Update product types
		* 'product_type_tables' are all types tables in product edit view
		TODO CAN BE CUSTOM FIELDS

		if (array_key_exists('product_type_tables', $data)) {
			if(!class_exists('VirtueMartModelProducttypes')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'producttypes.php');
			$ProducttypesModel = new VirtueMartModelProducttypes();
			$ProducttypesModel->saveProductProducttypes($data['product_type_tables']);
		}
		*/
		/* Update product custom field
		* 'product_type_tables' are all types tables in product edit view
		*/
		if (array_key_exists('field', $data)) {
			dump ($data['field'] , 'customsaved' );
			if(!class_exists('VirtueMartModelCustom')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'custom.php');
			VirtueMartModelCustom::saveProductfield($data['field'],$product_data->product_id);
		}


		return $product_data->product_id;
	}

	/**
	 * This function creates a child for a given product id
	 * @author Max Milbers
	 * @param int id of parent id
	 */
	public function createChild($id){
		// cdate , mdate
		$vendorId = 1 ;
		$q = 'INSERT INTO `#__vm_product` ( `vendor_id`, `product_parent_id` ) VALUES ( '.$vendorId.', '.$id.' )';
		$this->_db->setQuery($q);
		$this->_db->query();
		return $this->_db->insertid();
	}

	/**
	 * Creates a clone of a given product id
	 *
	 * @author Max Milbers
	 * @param int $product_id
	 */

	public function createClone($id){
		$product = $this->getProduct($id);
		$product->product_id = 0;
		$this->saveProduct($product,true,true,false);
		return $this->_id;
	}

	/**
	* Remove a product
	* @author RolandD
	* @todo Add sanity checks
	*/
	public function removeProduct($old_product_id=false) {
//		$this->_db = JFactory::getDBO();

		/* Get the product IDs to remove */
		$cids = array();
		if (!$old_product_id) {
			$cids = JRequest::getVar('cid');
			if (!is_array($cids)) $cids = array($cids);
		}
		else $cids[] = $old_product_id;

		/* Start removing */
		foreach ($cids as $key => $product_id) {
			/* First copy the product in the product table */
			$product_data = $this->getTable('product');

			/* Load the product details */
			$product_data->load($product_id);

			/* Delete all children if needed */
			if ($product_data->product_parent_id == 0) {
				/* Delete all children */
				/* Get a list of child products */
				$q = "SELECT product_id FROM #__vm_product WHERE product_parent_id = ".$product_id;
				$this->_db->setQuery($q);
				$children = $this->_db->loadResultArray();
				foreach ($children as $child_key => $child_id) {
					$this->removeProduct($child_id);
				}
			}

			/* Delete attributes */
			$q  = "DELETE FROM #__vm_product_attribute_sku WHERE product_id = ".$product_id;
			$this->_db->setQuery($q);
			$this->_db->query();

			/* Delete categories xref */
			$q  = "DELETE FROM #__vm_product_category_xref WHERE product_id = ".$product_id;
			$this->_db->setQuery($q);
			$this->_db->query();

			/* Delete product - manufacturer xref */
			$q = "DELETE FROM #__vm_product_mf_xref WHERE product_id = ".$product_id;
			$this->_db->setQuery($q);
			$this->_db->query();

			/* Delete Product - ProductType Relations */
			$q  = "DELETE FROM #__vm_product_product_type_xref WHERE product_id = ".$product_id;
			$this->_db->setQuery($q);
			$this->_db->query();

			/* Delete product votes */
			$q  = "DELETE FROM #__vm_product_votes WHERE product_id = ".$product_id;
			$this->_db->setQuery($q);
			$this->_db->query();

			/* Delete product reviews */
			$q = "DELETE FROM #__vm_product_reviews WHERE product_id = ".$product_id;
			$this->_db->setQuery($q);
			$this->_db->query();

			/* Delete Product Relations */
			$q  = "DELETE FROM #__vm_product_relations WHERE product_id = ".$product_id;
			$this->_db->setQuery($q); $this->_db->query();

			/* find and delete Product Types */
			$q = "SELECT product_type_id FROM #__vm_product_product_type_xref WHERE product_id = ".$product_id;
			$this->_db->setQuery($q);
			/* TODO the product is not deleted from this tables !!*/
			$product_type_ids = $this->_db->loadResultArray();
			foreach ($product_type_ids as $product_type_id)
			$q  = "DELETE FROM #__vm_product_type_".$product_type_id." WHERE product_id = ".$product_id;
			$this->_db->setQuery($q); $this->_db->query();

			/* Delete Product Types xref */
			$q  = "DELETE FROM #__vm_product_product_type_xref WHERE product_id = ".$product_id;
			$this->_db->setQuery($q); $this->_db->query();

			/* delete Product custom fields and Xref */
			$q = "DELETE `#__vm_custom_field_xref_product`,`#__vm_custom_field`
				FROM  `#__vm_custom_field_xref_product`,`#__vm_custom_field`
				WHERE `#__vm_custom_field_xref_product`.`custom_field_id` = `#__vm_custom_field`.`custom_field_id`
				AND `#__vm_custom_field_xref_product`.`product_id` =".$product_id;
			$this->_db->setQuery($q); $this->_db->query();


			/* Delete Prices */
			$q  = "DELETE FROM #__vm_product_price WHERE product_id = ".$product_id;
			$this->_db->setQuery($q);
			$this->_db->query();

			/* Delete the product itself */
			$product_data->delete($product_id);
		}
		return true;
	}


//    /**
//    * Add a product to the recent products list
//    * @author RolandD
//    */
//    public function addRecentProduct($product_id, $category_id, $maxviewed) {
//    	$session = JFactory::getSession();
//		$recentproducts = $session->get("recentproducts", null);
//		if (empty($recentproducts)) $recentproducts['idx'] = 0;
//
//    	//Check to see if we alread have recent
//    	if ($recentproducts['idx'] !=0) {
//    		for($i=0; $i < $recentproducts['idx']; $i++){
//    			//Check if it already exists and remove and reorder array
//    			if ($recentproducts[$i]['product_id'] == $product_id) {
//    				for($k=$i; $k < $recentproducts['idx']-1; $k++){
//    					$recentproducts[$k] = $recentproducts[$k+1];
//    				}
//    				array_pop($recentproducts);
//    				$recentproducts['idx']--;
//    			}
//    		}
//    	}
//    	// add product to recently viewed
//    	$recentproducts[$recentproducts['idx']]['product_id'] = $product_id;
//    	$recentproducts[$recentproducts['idx']]['category_id'] = $category_id;
//    	$recentproducts['idx']++;
//    	//Check to see if we have reached are limit and remove first item
//    	if($recentproducts['idx'] > $maxviewed+1) {
//    		for($k=0; $k < $recentproducts['idx']-1;$k++){
//    			$recentproducts[$k] = $recentproducts[$k+1];
//    		}
//    		array_pop($recentproducts);
//    		$recentproducts['idx']--;
//    	}
//    	$session->set("recentproducts", $recentproducts);
//    }
//
//    /**
//    * Load a list of recent products
//    * @author RolandD
//    * @todo Should we setup a session initiator and include the recent products?
//    *
//    * @param  int $product_id the ID of the product currently being viewed, don't want it in the list
//    * @param  int $maxitems the number of items to retrieve
//	* @return boolean true if there are recent products, false if there are no recent products
//    */
//    public function getRecentProducts($product_id=null, $maxitems=5) {
//    	if ($maxitems == 0) return;
//
////    	$this->_db = JFactory::getDBO();
//    	$session = JFactory::getSession();
//		$recentproducts = $session->get("recentproducts", null);
//		if (empty($recentproducts)) $recentproducts['idx'] = 0;
//
//		$k=0;
//		$recent = array();
//		// Iterate through loop backwards (newest to oldest)
//		for($i = $recentproducts['idx']-1; $i >= 0; $i--) {
//			//Check if on current product and don't display
//			if($recentproducts[$i]['product_id'] == $product_id){
//				continue;
//			}
//			// If we have not reached max products add the next product
//			if ($k < $maxitems) {
//				$prod_id = $recentproducts[$i]['product_id'];
//				$category_id = $recentproducts[$i]['category_id'];
//				$q = "SELECT product_name, category_name, c.category_flypage,product_s_desc ";
//				$q .= "FROM #__vm_product as p,#__vm_category as c,#__vm_product_category_xref as cx ";
//				$q .= "WHERE p.product_id = '".$prod_id."' ";
//				$q .= "AND c.category_id = '".$category_id."' ";
//				$q .= "AND p.product_id = cx.product_id ";
//				$q .= "AND c.category_id=cx.category_id ";
//				$q .= "AND p.published='1' ";
//				$q .= "AND c.published='1' ";
//				$q .= "LIMIT 0,1";
//				$this->_db->setQuery($q);
//				$product = $this->_db->loadObject();
//
//				if ($this->_db->getAffectedRows() > 0) {
//					$recent[$k]['product_s_desc'] = $product->product_s_desc;
//					$flypage = $product->category_flypage;
//					if (empty($flypage)) $flypage = VmConfig::get('flypage');
//
//					$recent[$k]['product_url'] = JRoute::_('index.php?option=com_virtuemart&view=product&product_id='.$prod_id.'&category_id='.$category_id.'&flypage='.$flypage);
//					$recent[$k]['category_url'] = JRoute::_('index.php?option=com_virtuemart&view=category&category_id='.$category_id);
//					$recent[$k]['product_name'] = JFilterInput::clean($product->product_name);
//					$recent[$k]['category_name'] = $product->category_name;
////					$recent[$k]['file_ids'] = $product->file_ids;
//				}
//				$k++;
//			}
//		}
//
//		$session->set("recentproducts", $recent);
//
//		if($k == 0) return false;
//		else return true;
//    }

   	/**
	* Function Description
	*
	* @author RolandD
	* @todo
	* @see
	* @access public
	* @return array list of files
	*/
	public function getTemplatesList() {
		jimport('joomla.filesystem.folder');
		$path = JPATH_VM_SITE.DS.'views'.DS.'productdetails'.DS.'tmpl';
		$files = JFolder::files($path, '.', false, false, array('index.html'));
		$options = array();
		foreach ($files AS $file) {
			$file = str_ireplace('.php', '', $file);
			$options[] = JHTML::_('select.option',  $file, $file);
		}
		return $options;
	}



	/*
	 * was productdetails
	 */
	public function getPrice($product_id=false){

		$this->_db = JFactory::getDBO();
		if (!$product_id) $product_id = JRequest::getInt('product_id', 0);

		$q = "SELECT `p`.*, `x`.`category_id`, `x`.`product_list`, `m`.`manufacturer_id`, `m`.`mf_name`
			FROM `#__vm_product` `p`
			LEFT JOIN `#__vm_product_category_xref` x
			ON `x`.`product_id` = `p`.`product_id`
			LEFT JOIN `#__vm_product_mf_xref` `mx`
			ON `mx`.`product_id` = `p`.`product_id`
			LEFT JOIN `#__vm_manufacturer` `m`
			ON `m`.`manufacturer_id` = `mx`.`manufacturer_id`
			WHERE `p`.`product_id` = ".$product_id;
		$this->_db->setQuery($q);
		$product = $this->_db->loadObject();

		/* Load the categories the product is in */
		$product->categories = $this->getProductCategories();

		if (empty($product->category) && isset($product->categories[0])) $product->category_id = $product->categories[0];

		/* Load the attributes */
//		$product->attributes = $this->getAttributes($product);

		/* Load the variants */
//		$product->variants = $this->getVariants($product);

		/* NEW Load the Customs Field Cart Price */
		$product->CustomsFieldCartPrice = $this->getproductCustomsFieldWithPrice($product);
		/* Loads the product price details */
		if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');
		$calculator = calculationHelper::getInstance();

		$quantityArray = JRequest::getVar('quantity',1,'post');

		/* Calculate the modificator */
//		$product_type_modificator = $calculator->calculateModificators($product->product_id,$product->variants);		/* Calculate the modificator */
//		$product_type_modificator = $calculator->calculateCustomsCart($product->product_id,$product->CustomsFieldCartPrice); WHY NOT HERE ?
		$selectedVariants = $calculator->parseModifier($product->variants);
		$variantPriceModification = $calculator->calculateModificators($product,$selectedVariants);
		$quantityArray = JRequest::getVar('quantity',1,'post');
//				$product->product_id.$variant_name
		$prices = $calculator->getProductPrices($product->product_id,$product->categories,$product_type_modificator,$quantityArray[0]);

		//Wrong place, this must not be done in a model, display is gui, therefore it must be done in the view!
		// change display //
//		foreach ($prices as &$value  ){
//			$value = $calculator->priceDisplay($value);
//		}

		return $prices;

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
		$this->_db = JFactory::getDBO();
		$showall = JRequest::getBool('showall', 0);

		$q = 'SELECT `comment`, `time`, `userid`, `user_rating`, `username`, `name`
			FROM `#__vm_product_reviews` `r`
			LEFT JOIN `#__users` `u`
			ON `u`.`id` = `r`.`userid`
			WHERE `product_id` = "'.$product_id.'"
			AND published = "1"
			ORDER BY `time` DESC ';
		if (!$showall) $q .= ' LIMIT 0, 5';
		$this->_db->setQuery($q);
		return $this->_db->loadObjectList();
	}

	/**
	* Get the Order By Select List
	*
	* notice by Max Milbers html tags should never be in a model. This function should be moved to a helper or simular,...
	* @author Kohl Patrick
	* @access public
	* @param $fieds from config Back-end
	* @return $orderByList
	* Order,order By, manufacturer and category link List to echo Out
	**/
  function getOrderByList() {

	//$mainframe = Jfactory::getApplication();
	//$option = JRequest::getWord('option');
	//$order	= $mainframe->getUserStateFromRequest( $option.'order'  , 'order' ,''	,'word' );

	$orderTxt ='';

	$order = JRequest::getVar('order', 'ASC');
	if ($order == 'DESC' ) $orderTxt .= '&order='.$order;

	$orderbyTxt ='';
	$orderby = JRequest::getVar('orderby', VmConfig::get('browse_orderby_field'));
	$orderbyCfg 	= VmConfig::get('browse_orderby_field');
	if ($orderby != '' && $orderby != $orderbyCfg ) $orderbyTxt = '&orderby='.$orderby;

	$category_id = JRequest::getInt('category_id', 0 );
	$fieldLink = '&category_id='.$category_id;
	$search = JRequest::getVar('search', '' );
	if ($search != '' ) $fieldLink .= '&search=true&keyword='.JRequest::getVar('keyword', '' );


	/* Collect the product IDS for manufacturer list */
	$db = JFactory::getDBO();
	if (empty($this->_query)) $this->_query = $this->_buildQuery();
	$db->setQuery($this->_query);
	$mf_product_ids = $db->loadResultArray();
	//$mf_product_ids = array();
	//foreach ($product_ids as $product_id) $mf_product_ids[] = $product_id->product_id ;

	/* manufacturer link list*/
	$manufacturerTxt ='';
	$manufacturer_id = JRequest::getVar('manufacturer_id',0);
	if ($manufacturer_id != '' ) $manufacturerTxt ='&manufacturer_id='.$manufacturer_id;
	if ($mf_product_ids) {
		$query = 'SELECT DISTINCT `#__vm_manufacturer`.`mf_name`,`#__vm_manufacturer`.`manufacturer_id` FROM `#__vm_manufacturer`';
		$query .= ' LEFT JOIN `#__vm_product_mf_xref` ON `#__vm_manufacturer`.`manufacturer_id` = `#__vm_product_mf_xref`.`manufacturer_id` ';
		$query .= ' WHERE `#__vm_product_mf_xref`.`product_id` in ('.implode (',', $mf_product_ids ).') ';
		$query .= ' ORDER BY `#__vm_manufacturer`.`mf_name`';
		$db->setQuery($query);
		$manufacturers = $db->loadObjectList();

		$manufacturerLink='';
		if (count($manufacturers)>0) {
			$manufacturerLink ='<div class="orderlist">';
			if ($manufacturer_id > 0) $manufacturerLink .='<div><a title="" href="'.JRoute::_('index.php?option=com_virtuemart&view=category'.$fieldLink.$orderTxt.$orderbyTxt ) .'">'.JText::_('COM_VIRTUEMART_SEARCH_SELECT_ALL_MANUFACTURER').'</a></div>';
			if (count($manufacturers)>1) {
				foreach ($manufacturers as $mf) {
					$link = JRoute::_('index.php?option=com_virtuemart&view=category&manufacturer_id='.$mf->manufacturer_id.$fieldLink.$orderTxt.$orderbyTxt ) ;
					if ($mf->manufacturer_id != $manufacturer_id) {
						$manufacturerLink .='<div><a title="'.$mf->mf_name.'" href="'.$link.'">'.$mf->mf_name.'</a></div>';
					}
					else $currentManufacturerLink ='<div class="activeOrder">'.$mf->mf_name.'</div>';
				}
			} elseif ($manufacturer_id > 0) $currentManufacturerLink =JText::_('COM_VIRTUEMART_PRODUCT_DETAILS_MANUFACTURER_LBL').'<div class="activeOrder">'. $manufacturers[0]->mf_name.'</div>';
			else $currentManufacturerLink ='<div >'.JText::_('COM_VIRTUEMART_PRODUCT_DETAILS_MANUFACTURER_LBL').'</div><div> '.$manufacturers[0]->mf_name.'</div>';
			$manufacturerLink .='</div>';
		}
	} else $manufacturerLink = "" ;

	/* order by link list*/
	$orderByLink ='';
	$fields = VmConfig::get('browse_orderby_fields');
	if (count($fields)>1) {
		$orderByLink ='<div class="orderlist">';
		foreach ($fields as $field) {
			if ($field != $orderby) {
				$text = JText::_('COM_VIRTUEMART_SEARCH_ORDER_'.strtoupper($field)) ;
				if ($field == $orderbyCfg) $link = JRoute::_('index.php?option=com_virtuemart&view=category'.$fieldLink.$manufacturerTxt ) ;
				else $link = JRoute::_('index.php?option=com_virtuemart&view=category'.$fieldLink.$manufacturerTxt.'&orderby='.$field ) ;
				$orderByLink .='<div><a title="'.$text.'" href="'.$link.'">'.$text.'</a></div>';
			}
		}
		$orderByLink .='</div>';
	}

	/* invert order value set*/
	if ($order =='ASC') {
		$orderlink ='&order=DESC';
		$orderTxt = JText::_('COM_VIRTUEMART_SEARCH_ORDER_DESC');
	} else {
		$orderTxt = JText::_('COM_VIRTUEMART_SEARCH_ORDER_ASC');
		$orderlink ='';
	}

	/* full string list */
	if ($orderby=='') $orderby=$orderbyCfg;
	$orderby=strtoupper($orderby);
	$link = JRoute::_('index.php?option=com_virtuemart&view=category'.$fieldLink.$orderlink.$orderbyTxt.$manufacturerTxt) ;

	$orderByList ='<div class="orderlistcontainer"><div>'.JText::_('COM_VIRTUEMART_ORDERBY').'</div><div class="activeOrder"><a title="'.$orderTxt.'" href="'.$link.'">'.JText::_('COM_VIRTUEMART_SEARCH_ORDER_'.$orderby).' '.$orderTxt.'</a></div>';
	$orderByList .= $orderByLink.'</div>';
	if (empty ($currentManufacturerLink) ) $currentManufacturerLink = JText::_('COM_VIRTUEMART_PRODUCT_DETAILS_MANUFACTURER_LBL').'<div class="activeOrder">'.JText::_('COM_VIRTUEMART_SEARCH_SELECT_MANUFACTURER').'</div>';
	$orderByList .=' <div class="orderlistcontainer">'.$currentManufacturerLink;
	$orderByList .= $manufacturerLink.'</div><div class="clear"></div>';

	return $orderByList ;
  }


    /**
	* Get the votes for a given product
	*
	* @author RolandD
	* @todo Figure out how this really is supposed to work
	* @access public
	* @param int $product_id the product ID to get reviews for
	* @return array containing review data
	*/
	public function getVotes($product_id) {
		$result = array();
		if (VmConfig::get('allow_reviews', 0) == '1') {
			$this->_db = JFactory::getDBO();

			$q = "SELECT `votes`, `allvotes`, `rating`
				FROM `#__vm_product_votes`
				WHERE `product_id` = ".$product_id;
			$this->_db->setQuery($q);
			$result = $this->_db->loadObject();
		}
		return $result;
	}



	/**
	* Load the custom variants
	*
	* @author RolandD
	* @access private
	* @param string $custom_attr_list containing the custom variants
	* @return array containing the custom variants
	*/
/* OBSELETE
	private function getCustomVariants($custom_attr_list) {
		$fields = array();
		if ($custom_attr_list) {
			if (substr($custom_attr_list, -1) == ';') $custom_attr_list = substr($custom_attr_list, 0, -1);
			$fields = explode(";", $custom_attr_list);
		}
		return $fields;
	}
*/
	// **************************************************
	//Stocks
	//
	/**
	* Get the stock level for a given product
	*
	* @author RolandD
	* @access public
	* @param object $product the product to get stocklevel for
	* @return array containing product objects
	*/
	public function getStockIndicator($product) {
    	$this->_db = JFactory::getDBO();

    	/* Assign class to indicator */
    	$stock_level = $product->product_in_stock;
    	$reorder_level = $product->low_stock_notification;
		$level = 'normalstock';
		$stock_tip = JText::_('COM_VIRTUEMART_STOCK_LEVEL_DISPLAY_NORMAL_TIP');
		if ($stock_level <= $reorder_level) {
			$level = 'lowstock';
			$stock_tip = JText::_('COM_VIRTUEMART_STOCK_LEVEL_DISPLAY_LOW_TIP');
		}
		if ($stock_level == 0) {
			$level = 'nostock';
			$stock_tip = JText::_('COM_VIRTUEMART_STOCK_LEVEL_DISPLAY_OUT_TIP');
		}
    	$stock = new Stdclass();
    	$stock->stock_tip = $stock_tip;
    	$stock->stock_level = $level;
    	return $stock;
    }

	/**
	 * Decrease the stock for a given product and increase the sales amount
	 *
	 * @author Oscar van Eijk
	 * @param $_id integer Product ID
	 * @param $_amount integer Amount sold
	 * @access public
	 */
	public function decreaseStockAfterSales ($_id, $_amount)
	{
		$this->decreaseStock($_id, $_amount);
		$this->_db->setQuery('UPDATE `#__vm_product` '
					. 'SET `product_sales` = `product_sales` + ' . $_amount . ' '
					. 'WHERE `product_id` = ' . $_id
					);
		$this->_db->query();
	}

	/**
	 * Increase the stock for a given product after an order was cancelled
	 * and decrease the sales amount
	 *
	 * @author Oscar van Eijk
	 * @param $_id integer Product ID
	 * @param $_amount integer Amount sold
	 * @access public
	 */
	public function increaseStockAfterCancel ($_id, $_amount)
	{
		$this->increaseStock($_id, $_amount);
		$this->_db->setQuery('UPDATE `#__vm_product` '
					. 'SET `product_sales` = `product_sales` - ' . $_amount . ' '
					. 'WHERE `product_id` = ' . $_id
					);
		$this->_db->query();
	}

	/**
	 * Increase the stock for a given product and decrease the sales amount
	 * after an order cancellation
	 *
	 * @author Oscar van Eijk
	 * @param $_id integer Product ID
	 * @param $_amount integer Original amount sold
	 * @access public
	 */
	public function revertStockAfterCancellation ($_id, $_amount)
	{
		$this->increaseStock($_id, $_amount);
		$this->_db->setQuery('UPDATE `#__vm_product` '
					. 'SET `product_sales` = `product_sales` - ' . $_amount . ' '
					. 'WHERE `product_id` = ' . $_id
					);
		$this->_db->query();
	}

	/**
	 * Decrease the stock for a given product, calls _updateStock
	 *
	 * @author Oscar van Eijk
	 * @param $_id integer Product ID
	 * @param $_amount integer Amount sold
	 * @access public
	 */
	public function decreaseStock ($_id, $_amount)
	{
		$this->_updateStock($_id, $_amount, '-');
	}

	/**
	 * Increase the stock for a given product, calls _updateStock
	 *
	 * @author Oscar van Eijk
	 * @param $_id integer Product ID
	 * @param $_amount integer Amount sold
	 * @access public
	 */
	public function increaseStock ($_id, $_amount)
	{
		$this->_updateStock($_id, $_amount, '+');
	}

	/**
	 * Update the stock for a given product
	 *
	 * @author Oscar van Eijk
	 * @param $_id integer Product ID
	 * @param $_amount integer Amount sold
	 * @param $_sign char '+' for increase, '-' for decrease
	 * @access private
	 */
	private function _updateStock($_id, $_amount, $_sign){

		$this->_db->setQuery('UPDATE `#__vm_product` '
					. 'SET `product_in_stock` = `product_in_stock` ' . $_sign . $_amount . ' '
					. 'WHERE `product_id` = ' . $_id
					);
		$this->_db->query();

		if ($_sign == '-') {
			$this->_db->setQuery('SELECT `product_in_stock` < `low_stock_notification` '
					. 'FROM `#__vm_product` '
					. 'WHERE `product_id` = ' . $_id
			);
			if ($this->_db->loadResult() == 1) {
				// TODO Generate low stock warning
			}
		}
	}

	/**
    * Get a list of product types to assign the product to
    * @author RolandD
    */
    public function getProductTypeList() {
//    	$this->_db = JFactory::getDBO();

    	$cids = JRequest::getVar('cid');

    	$q  = "SELECT t.product_type_id AS value, product_type_name AS text
    		FROM #__vm_product_type t
			LEFT JOIN #__vm_product_product_type_xref x
			ON x.product_type_id = t.product_type_id
			WHERE (product_id != ".$cids[0]." OR product_id IS NULL)
			ORDER BY `ordering` ASC";
		$this->_db->setQuery($q);
		return $this->_db->loadObjectList();
    }

    /**
    * Add a product to a product type link
    * @todo Add unique key to table vm_product_product_type_xref
    */
    public function saveProductType() {
//    	$this->_db = JFactory::getDBO();

    	$product_id = JRequest::getInt('product_id', false);
    	$product_type_id = JRequest::getInt('product_type_id', false);

    	if ($product_id && $product_type_id) {
			/* Check if the product link already exist */
			$q  = "SELECT COUNT(*) AS count FROM #__vm_product_product_type_xref ";
			$q .= "WHERE product_id = ".$product_id." AND product_type_id = ".$product_type_id;
			$this->_db->setQuery($q);

			if ($this->_db->loadResult() == 0) {
				$q  = "INSERT INTO #__vm_product_product_type_xref (product_id, product_type_id) ";
				$q .= "VALUES (".$product_id.",".$product_type_id.")";
				$this->_db->setQuery($q);
				$this->_db->query();

				$q  = "INSERT INTO #__vm_product_type_".$product_type_id." (product_id) ";
				$q .= "VALUES (".$product_id.")";
				$this->_db->setQuery($q);
				$this->_db->query();

				return true;
			}
			else return false;
		}
		else return false;
    }


  /**
     * AUthor Kohl Patrick
     * Load the types and parameter for a product
     * return Object product type , parameters & value
     */
     public function getproductTypes() {
		$product_id = JRequest::getInt('product_id', false);

		 if ($this->hasProductType($product_id )) {

			if(!class_exists('VirtueMartModelProducttypes')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'producttypes.php');
			$ProducttypesModel = new VirtueMartModelProducttypes();
		return $ProducttypesModel->getProductProducttypes($product_id);
		}
		return ;
     }

	/* look if whe have a product type */
	private function hasProductType($product_id) {
		$this->_db = JFactory::getDBO();
		$q = "SELECT COUNT(`product_id`) AS types FROM `#__vm_product_product_type_xref` WHERE `product_id` = ".$product_id;
		$this->_db->setQuery($q);
		return ($this->_db->loadResult() > 0);
	}
  /**
     * AUthor Kohl Patrick
     * Load all custom fields for a Single product
     * return custom fields value and definition
     */
     public function getproductCustomslist($product_id) {

		 if ($this->hasproductCustoms($product_id )) {

		$query='SELECT C.`custom_id` , `custom_parent_id` , `admin_only` , `custom_title` , `custom_tip` , C.`custom_value` AS value, `custom_field_desc` , `field_type` , `is_list` , `is_cart_attribute` , `is_hidden` , C.`published` , field.`custom_field_id` , field.`custom_value`,field.`custom_price`
			FROM `#__vm_custom` AS C
			LEFT JOIN `#__vm_custom_field` AS field ON C.`custom_id` = field.`custom_id`
			LEFT JOIN `#__vm_custom_field_xref_product` AS xref ON xref.`custom_field_id` = field.`custom_field_id`
			Where xref.`product_id` ='.$product_id;
		$this->_db->setQuery($query);
		$productCustoms = $this->_db->loadObjectList();
		$row= 0 ;
		foreach ($productCustoms as & $field ) {
			$field->display = $this->inputType($field->custom_value,$field->field_type,$field->is_list,$field->custom_price,$row,$field->is_cart_attribute);
			$row++ ;
		}
		return $productCustoms;
		}
		return ;
     }
  /**
     * AUthor Kohl Patrick
     * Load the t the custom fields for a product
     * return Object product type , parameters & value
     */
     public function getproductCustoms() {
		static $productcustomFields ;
		if ($productcustomFields) return $productcustomFields;
		$product_id = JRequest::getInt('product_id', false);
		if (empty ($productcustomFields)) {
			 if ($this->hasproductCustoms($product_id )) {
				if(!class_exists(' VirtueMartModelCustom')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'custom.php');
				$productcustomFields = VirtueMartModelCustom::getProductCustoms($product_id);
			return $productcustomFields++ ;
			}
		} else return $productcustomFields;
     }

	/* look if whe have a product type */
	private function hasproductCustoms($product_id) {
		$this->_db = JFactory::getDBO();
		$q = "SELECT COUNT(`product_id`) FROM `#__vm_custom_field_xref_product` WHERE `product_id` = ".$product_id;
		$this->_db->setQuery($q);
		return ($this->_db->loadResult() > 0);
	}

	// **************************************************
	//Attributes
	//
	/**
	* Load the variants for a product
	*
	* Variants can have several attributes an example:
	* Size,XL[+1.99],M,S[-2.99];Colour,Red,Green,Yellow,ExpensiveColor[=24.00]
	*
	* @author RolandD, Max Milbers
	* @param object $product the product to get attributes for
	* @param string $extra_ids any extra id's to add to the attributes
	* @return
	*/

//    /**
//     * Load the attribute names for a product
//     */
//     public function getProductAttributeNames() {
//     	 $product_id = JRequest::getInt('product_id', 0);
//     	 $product_parent_id = JRequest::getInt('product_parent_id', 0);
////		 $this->_db = JFactory::getDBO();
//		 $q = "SELECT attribute_name
//			FROM #__vm_product_attribute_sku
//			WHERE product_id = ";
//			if ($product_parent_id > 0) $q .= $product_parent_id;
//			else $q .= $product_id;
//		 $this->_db->setQuery($q);
//		 return $this->_db->loadResultArray();
//     }
//
//     /**
//     * Load the attribute names for a product
//     */
//     public function getProductAttributeValues() {
//     	 $product_id = JRequest::getInt('product_id', 0);
//     	 /* Check if we are loading an existing product */
//     	 if ($product_id > 0) {
////     	 	 $this->_db = JFactory::getDBO();
//			 $q = "SELECT attribute_id, attribute_name, attribute_value
//				FROM #__vm_product_attribute
//				WHERE product_id = ".$product_id;
//			 $this->_db->setQuery($q);
//			 return $this->_db->loadAssocList('attribute_name');
//     	 }
//     	 else return null;
//     }

	/**
	* Load the child products for a given product
	*/
//	public function getChildAttributes($product_id) {
////		$this->_db = JFactory::getDBO();
//		$q = "SELECT p.product_id, product_name, product_sku, attribute_name, attribute_value
//			FROM #__vm_product p
//			LEFT JOIN #__vm_product_attribute
//			ON p.product_id = #__vm_product_attribute.product_id
//			WHERE p.product_parent_id = ".$product_id."
//			ORDER BY p.product_sku";
//		$this->_db->setQuery($q);
//		$products = $this->_db->loadObjectList();
//		$childproduct = array();
//		foreach ($products as $key => $product) {
//			foreach ($product as $name => $value) {
//				if (!array_key_exists($product->product_sku, $childproduct)) {
//					$childproduct[$product->product_sku] = new StdClass();
//				}
//				if ($name != 'attribute_name' && $name != 'attribute_value') {
//					$childproduct[$product->product_sku]->$name = $value;
//				}
//				else {
//					$attribute_name = $product->attribute_name;
//					$childproduct[$product->product_sku]->$attribute_name = $product->attribute_value;
//				}
//			}
//		}
//		return $childproduct;
//	}

	/**
	 * Function to create a DB object that holds all information
	 * from the attribute tables about item $item_id AND/OR product $product_id
	 *
	 * @author RolandD
	 * @access private
	 * @param int $product The product object
	 * @param string $attribute_name The name of the attribute to filter
	 * @return array list of attribute objects
	 */
//	private function getAttributes($product, $attribute_name = '') {
//		$this->_db = JFactory::getDBO();
//		$attributes = array();
//		if ($product->product_id && $product->product_parent_id) {
//			$q  = "SELECT * FROM `#__vm_product_attribute`, `#__vm_product_attribute_sku` ";
//			$q .= "WHERE `#__vm_product_attribute`.`product_id` = ".$product->product_id." ";
//			$q .= "AND `#__vm_product_attribute_sku`.`product_id` = ".$product->product_parent_id." ";
//			if ($attribute_name) {
//				$q .= "AND `#__vm_product_attribute`.`attribute_name` = ".$this->_db->Quote($attribute_name)." ";
//			}
//			$q .= "AND `#__vm_product_attribute`.`attribute_name` = `#__vm_product_attribute_sku`.attribute_name ";
//			$q .= "ORDER BY attribute_list, `#__vm_product_attribute`.`attribute_name`";
//		}
//		elseif ($product->product_id) {
//			$q  = "SELECT * FROM `#__vm_product_attribute` ";
//			$q .= "WHERE  `product_id` = ".$product->product_id." ";
//			if ($attribute_name) {
//				$q .= "AND `attribute_name` = ".$this->_db->Quote($attribute_name)." ";
//			}
//		}
//		elseif ($product->product_parent_id) {
//			$q  = "SELECT * FROM `#__vm_product_attribute_sku` ";
//			$q .= "WHERE product_id = ".$product->product_parent_id." ";
//			if ($attribute_name) {
//				$q .= "AND `#__vm_product_attribute`.`attribute_name` = ".$this->_db->Quote($attribute_name)." ";
//			}
//			$q .= "ORDER BY attribute_list,`attribute_name`";
//		}
//
//		$this->_db->setQuery($q);
//		$attributes = $this->_db->loadObjectList();//dump($attributes,'$attributes');
//		return $attributes;
//	}

    /**
	 * Function to quickly check whether a product has attributes or not
	 *
	 * @author RolandD
	 * @param int $pid The id of the product to check
	 * @return boolean True when the product has attributes, false when not
	 */
//	function checkAttributes($pid, $checkSimpleAttributes=false ) {
//		if (is_array($pid) || empty($pid)) return false;
//
//		$pid = intval($pid);
////		$this->_db = JFactory::getDBO();
//		$product_info = JRequest::getVar('product_info', false);
//
//		if (!$product_info || empty($product_info[$pid]["product_has_attributes"] )) {
//			$this->_db->setQuery("SELECT `product_id` FROM `#__vm_product_attribute_sku` WHERE `product_id`=".$pid);
//			$product_id = $this->_db->loadResult();
//
//			if ($product_id) $product_info[$pid]["product_has_attributes"] = true;
//			else if($checkSimpleAttributes) {
//				$this->_db->setQuery("SELECT `attribute`,`custom_attribute` FROM `#__vm_product` WHERE `product_id`=".$pid);
//				$attributes = $this->_db->loadObject();
//				if ($attributes->attribute || $attributes->custom_attribute) {
//					$product_info[$pid]["product_has_attributes"] = true;
//				}
//				else {
//					$product_info[$pid]["product_has_attributes"] = false;
//				}
//			}
//			else $product_info[$pid]["product_has_attributes"] = false;
//		}
//		JRequest::setVar('product_info', $product_info);
//		return $product_info[$pid]["product_has_attributes"];
//	}

//		/**
//	* Format the attributes of a product to DB format
//	*/
//	public function formatAttributeX() {
//		// request attribute pieces
//		$attributeX = JRequest::getVar( 'attributeX', array( 0 ) ) ;
//		$attribute_string = '' ;
//
//		// no pieces given? then return
//		if( empty( $attributeX ) ) {
//			return $attribute_string ;
//		}
//
//		// put the pieces together again
//		foreach( $attributeX as $attributes ) {
//			$attribute_string .= ';' ;
//			// continue only if the attribute has a name
//			if( empty( $attributes['name'] ) ) {
//				continue ;
//			}
//			$attribute_string .= trim( $attributes['name'] ) ;
//			$n2 = count( $attributes['value'] ) ;
//			for( $i2 = 0 ; $i2 < $n2 ; $i2 ++ ) {
//				$value = $attributes['value'][$i2] ;
//				$price = $attributes['price'][$i2] ;
//
//				if( ! empty( $value ) ) {
//					$attribute_string .= ',' . trim( $value ) ;
//
//					if( ! empty( $price ) ) {
//
//						// add the price only if there is an operand
//						if( strstr( $price, '+' ) or (strstr( $price, '-' )) or (strstr( $price, '=' )) ) {
//							$attribute_string .= '[' . trim( $price ) . ']' ;
//						}
//					}
//				}
//			}
//
//		}
//
//		// cut off the first attribute separators on the beginning of the string
//		// otherwise you would get an empty first attribute
//		$attribute_string = substr( $attribute_string, 1 ) ;
//		return trim( $attribute_string ) ;
//	}

    /**
	 * Since a product dont need always an image, we can attach them to the product with this function
	 * The parameter takes a single product or arrays of products, look for BE/views/product/view.html.php
	 * for an exampel using it
	 *
	 * @author Max Milbers
	 * @param object $products
	 */
	public function addImagesToProducts($products=0){

		if(!class_exists('VirtueMartModelMedia')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'media.php');
		if(empty($this->mediaModel))$this->mediaModel = new VirtueMartModelMedia();

		$this->mediaModel->attachImages($products,'file_ids','product','image');

	}
/**
 * Formating admin display by roles
 * input Types for product only !
 * $pricable if can have a price
 */

	function inputType($value,$type,$is_list=0,$price,$row,$pricable=0){
		if ($is_list>0) {
			$options = array();
			$values = explode(';',$value);
			foreach ($values as $key => $val)
				$options[] = array( 'value' => $val ,'text' =>$val);
			return JHTML::_('select.genericlist', $options,'field['.$row.'][custom_value]');
		} else {
			if ($pricable)  $priceInput = JText::_('COM_VIRTUEMART_CART_PRICE').'<input type="text" value="'.$price.'" name="field['.$row.'][custom_price]" />';
			else $priceInput = '';
			switch ($type) {
				/* variants*/
				case 'V':
				return '<input type="text" value="'.$value.'" name="field['.$row.'][custom_value]" />'.$priceInput;
				break;
				/*userfield variants*/
				case 'U':
				return '<input type="text" value="'.$value.'" name="field['.$row.'][custom_value]" />'.$priceInput;
				break;
				/* string or integer */
				case 'S':
				case 'I':
					return '<input type="text" value="'.$value.'" name="field['.$row.'][custom_value]" />'.$priceInput;
				break;
				/* bool */
				case 'B':
					return JHTML::_( 'select.booleanlist', 'field['.$row.'][custom_value]' , 'class="inputbox"', $value).$priceInput;
				break;
				/* parent */
				case 'P':
					return $value.'<input type="hidden" value="'.$value .'" name="field['.$row.'][custom_value]" />' .$priceInput;
				break;
				/* image */
				case 'i':
					$vendorId=1;
					$q='SELECT `file_id` as value,`file_title` as text FROM `#__vm_media` WHERE `published`=1
					AND (`vendor_id`= "'.$vendorId.'" OR `shared` = "1")';
					$this->_db->setQuery($q);
					$options = $this->_db->loadObjectList();
					return JHTML::_('select.genericlist', $options,'field['.$row.'][custom_value]','','value' ,'text',$value).$priceInput;
				break;
				/* Child product */
				case 'C':
					$vendorId=1;
					if (empty($product_id)) $product_id = JRequest::getInt('product_id', 0);
					$q='SELECT `product_id` as value,concat(`product_sku`,":",`product_name`) as text FROM `#__vm_product` WHERE `published`=1
					AND `product_parent_id`= "'.$product_id.'"';
					$this->_db->setQuery($q);
					if ($options = $this->_db->loadObjectList() ) return JHTML::_('select.genericlist', $options,'field['.$row.'][custom_value]','','value' ,'text',$value);
					else return JText::_('COM_VIRTUEMART_CUSTOM_NO_CHILD_PRODUCT');
				break;
			}

		}
	}
     public function getproductCustomsField($product, $cart = 0) {

		if ($product->hasproductCustoms) {

		$query='SELECT C.`custom_id` , `custom_parent_id` , `admin_only` , `custom_title` , `custom_tip` , C.`custom_value` AS value, `custom_field_desc` , `field_type` , `is_list` , `is_hidden` , C.`published` , field.`custom_field_id` , field.`custom_value`, field.`custom_price`
			FROM `#__vm_custom` AS C
			LEFT JOIN `#__vm_custom_field` AS field ON C.`custom_id` = field.`custom_id`
			LEFT JOIN `#__vm_custom_field_xref_product` AS xref ON xref.`custom_field_id` = field.`custom_field_id`
			Where xref.`product_id` ='.$product->product_id;
		$query .=' and is_cart_attribute = 0 order by custom_id' ;
		$this->_db->setQuery($query);
		$productCustoms = $this->_db->loadObjectList();
		$row= 0 ;
		foreach ($productCustoms as & $field ) {
			$field->display = $this->displayType($product,$field->custom_value,$field->field_type,$field->is_list,$field->custom_price,$row);
			$row++ ;
		}
		return $productCustoms;
		}
		return ;
     }
	 // temp function TODO better one
     public function getproductCustomsFieldCart($product) {
//		$product_id = JRequest::getInt('product_id', false);

		if ($product->hasproductCustoms)  {

			// group by custom_id
			$query='SELECT C.`custom_id`, `custom_title`, C.`custom_value`,`custom_field_desc` ,`custom_tip`,`field_type`
				FROM `#__vm_custom` AS C
				LEFT JOIN `#__vm_custom_field` AS field ON C.`custom_id` = field.`custom_id`
				LEFT JOIN `#__vm_custom_field_xref_product` AS xref ON xref.`custom_field_id` = field.`custom_field_id`
				Where xref.`product_id` ='.$product->product_id;
			$query .=' and is_cart_attribute = 1 group by custom_id' ;

			$this->_db->setQuery($query);
			$groups = $this->_db->loadObjectList();

			if (!class_exists('VmHTML')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'html.php');
			$row= 0 ;
			if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');
			$calculator = calculationHelper::getInstance();
			// render select list
			foreach ($groups as & $group) {

//				$query='SELECT  field.`custom_field_id` as value ,concat(field.`custom_value`," :bu ", field.`custom_price`) AS text
				$query='SELECT  field.`custom_field_id` as value ,field.`custom_value`, field.`custom_price`
					FROM `#__vm_custom` AS C
					LEFT JOIN `#__vm_custom_field` AS field ON C.`custom_id` = field.`custom_id`
					LEFT JOIN `#__vm_custom_field_xref_product` AS xref ON xref.`custom_field_id` = field.`custom_field_id`
					Where xref.`product_id` ='.$product->product_id;
				$query .=' and is_cart_attribute = 1 and C.`custom_id`='.$group->custom_id ;
				$this->_db->setQuery($query);
				$productCustoms = $this->_db->loadObjectList();
				foreach ($productCustoms as $productCustom) {
					$productCustom->custom_price = $calculator->priceDisplay($productCustom->custom_price,$product->product_currency,true);
				}
				if ($group->field_type == 'V'){
					dump($productCustoms,'field_type == V');
					foreach ($productCustoms as $productCustom) {
						$productCustom->text =  $productCustom->custom_value.' : '.$productCustom->custom_price;
					}
					$group->display = VmHTML::select($productCustoms,'customPrice['.$row.']['.$group->custom_id.']',$group->custom_value,'','value','text',false);
				} else {
					dump($product,'field_type == other');
					$group->display ='';
					foreach ($productCustoms as $productCustom) {
						$group->display .= '<input id="'.$productCustom->value.'" type="radio" value="'.$productCustom->value.'" name="customPrice['.$row.']['.$group->custom_id.']" /><label for="'.$productCustom->value.'">'.$this->displayType($product,$productCustom->custom_value,$group->field_type,0,'',$row).': '.$productCustom->custom_price.'</label>' ;
					}
				}
				$row++ ;
			}
				return $groups;

		}
		return ;
     }
	/*
	* GIve Product custom_field_id pricable
	**/
	public function getProductcustomfieldsIds($product) {
			$query='SELECT field.`custom_field_id` FROM `#__vm_custom` AS C
				LEFT JOIN `#__vm_custom_field` AS field ON C.`custom_id` = field.`custom_id`
				LEFT JOIN `#__vm_custom_field_xref_product` AS xref ON xref.`custom_field_id` = field.`custom_field_id`
				Where is_cart_attribute = 1 and xref.`product_id` ='.$product->product_id;
		$this->_db->setQuery($query);
		return ($this->_db->loadResult() > 0);

	}
	/*
	* Product
	*Get fields with price
	* from custom fields
	**/
     public function getproductCustomsFieldWithPrice($product) {

		if ($this->hasproductCustoms($product->product_id )) {

			// group by custom_id
			$query='SELECT C.`custom_id`, `custom_title`, C.`custom_value`,`custom_field_desc` ,`custom_tip`,`field_type`
				FROM `#__vm_custom` AS C
				LEFT JOIN `#__vm_custom_field` AS field ON C.`custom_id` = field.`custom_id`
				LEFT JOIN `#__vm_custom_field_xref_product` AS xref ON xref.`custom_field_id` = field.`custom_field_id`
				Where xref.`product_id` ='.$product->product_id;
			$query .=' and is_cart_attribute = 1 group by custom_id' ;
			$this->_db->setQuery($query);
			$groups = $this->_db->loadAssocList();

			//product custom_field  with price grouped by custom_id
			foreach ($groups as & $group) {
				$query='SELECT  field.`custom_field_id` ,field.`custom_value`,field.`custom_price`
					FROM `#__vm_custom` AS C
					LEFT JOIN `#__vm_custom_field` AS field ON C.`custom_id` = field.`custom_id`
					LEFT JOIN `#__vm_custom_field_xref_product` AS xref ON xref.`custom_field_id` = field.`custom_field_id`
					Where xref.`product_id` ='.$product->product_id;
				$query .=' and is_cart_attribute = 1 and C.`custom_id`='.$group['custom_id'] ;
				$this->_db->setQuery($query);
				$productCustomsCart = $this->_db->loadAssocList();
				$group = array_merge($group, $productCustomsCart);
			}
			return $groups;
		}
		return ;
     }
/**
  * Formating front display by roles
  *  for product only !
  */
	function displayType($product,$value,$type,$is_list=0,$price = 0,$row){

		if ($is_list>0) {
			$options = array();
			$values = explode(';',$value);
			foreach ($values as $key => $val)
				$options[] = array( 'value' => $val ,'text' =>$val);
			return JHTML::_('select.genericlist', $options,'field['.$row.'][custom_value]');
		} else {
			switch ($type) {
				/* variants*/
				case 'V':
				if ($price == 0 ) $price = JText::_('COM_VIRTUEMART_CART_PRICE_FREE') ;
				/* Loads the product price details */
				if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');
				$calculator = calculationHelper::getInstance();
				return '<input type="text" value="'.$value.'" name="field['.$row.'][custom_value]" /> '.JText::_('COM_VIRTUEMART_CART_PRICE').' : '.$calculator->priceDisplay($price,$product->product_currency,true).' ';
				break;
				/*userfield variants*/
				case 'U':
				return '<input type="text" value="'.$value.'" name="field['.$row.'][custom_value]" /> '.JText::_('COM_VIRTUEMART_CART_PRICE').' : '.$price.' ';
				break;
				/* string or integer */
				case 'S':
				case 'I':
					return $value;
				break;
				/* bool */
				case 'B':
					if ($value == 0) return JText::_('COM_VIRTUEMART_ADMIN_CFG_NO') ;
					return JText::_('COM_VIRTUEMART_ADMIN_CFG_YES') ;
				break;
				/* parent */
				case 'P':
					return '<b>'.$value.'<b/>';
				break;
				/* image */
				case 'i':
					$vendorId=1;
					$q='SELECT * FROM `#__vm_media` WHERE `published`=1
					AND (`vendor_id`= "'.$vendorId.'" OR `shared` = "1") AND file_id='.(int)$value;
					$db =& JFactory::getDBO();
					$db->setQuery($q);
					$image = $db->loadObject();
					dump($value,'C. image'.$row);
					//if(!class_exists('VirtueMartModelMedia')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'media.php');
					if (!class_exists('VmMediaHandler')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'mediahandler.php');
					$this->file_id = (int)$value;
					$imagehandler = VmMediaHandler::createMedia($image);
					//$imagehandler->createMedia($image);
					return $imagehandler->displayMediaThumb();
				break;
				/* Child product */
				case 'C':
					$vendorId=1;
					if (empty($product_id)) $product_id = JRequest::getInt('product_id', 0);
					$q='SELECT p.`product_id` , p.`product_name`, x.`category_id` FROM `#__vm_product` as p
					LEFT JOIN `#__vm_product_category_xref` as x on x.`product_id` = p.`product_id`
					WHERE `published`=1 AND p.`product_id`= "'.$value.'" ';
					$this->_db->setQuery($q);
					if ($result = $this->_db->loadObject() ) return  JHTML::link ( JRoute::_ ( 'index.php?option=com_virtuemart&view=productdetails&product_id=' . $result->product_id . '&category_id=' . $result->category_id ), $result->product_name, array ('title' => $result->product_name ) );
					else return JText::_('COM_VIRTUEMART_CUSTOM_NO_CHILD_PRODUCT');
				break;
			}
		}
	}

}
// No closing tag