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
		$limit = $mainframe->getUserStateFromRequest(  JRequest::getVar('option').JRequest::getVar('view').'.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		if (JRequest::getVar('view') == 'category' ) {
			$limitstart = JRequest::getVar('limitstart',0) ;
		} else {
			$limitstart = $mainframe->getUserStateFromRequest( JRequest::getVar('option').JRequest::getVar('view').'.limitstart', 'limitstart', 0, 'int' );
		}

		// In case limit has been changed, adjust limitstart accordingly
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
//		if (!class_exists( 'TableMedia' )) require(JPATH_VM_ADMINISTRATOR.DS.'tables'.DS.'medias.php');
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

	/**
	 * Gets the total number of products
	 */
	private function getTotal() {
    	if (empty($this->_total)) {
//    		$this->_db = JFactory::getDBO();
			if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');
			$showall = Permissions::getInstance()->check('storeadmin');
			$where='';
			if (!$showall) $where = ' WHERE  #__virtuemart_products.`published`=1 ';
			$q = "SELECT #__virtuemart_products.`virtuemart_product_id` ".$this->getProductListQuery().$where.$this->getProductListFilter();
			$this->_db->setQuery($q);
			$fields = $this->_db->loadObjectList('virtuemart_product_id');
			$this->_total = count($fields);
        }

        return $this->_total;
    }

     /**
     * Get the simple product info
     * @author RolandD
     */
     public function getProductDetails() {
     	$cids = JRequest::getVar('cid');
     	$q = "SELECT * FROM #__virtuemart_products WHERE virtuemart_product_id = ".$cids[0];
     	$this->_db->setQuery($q);
     	return $this->_db->loadObject();
     }

    /**
     * This function creates a product with the attributes of the parent.
     *
     * @param int $virtuemart_product_id
     * @param boolean $front for frontend use
     * @param boolean $withCalc calculate prices?
     */
    public function getProduct($virtuemart_product_id = null,$front=true, $withCalc = true, $onlyPublished = true){

    	if (empty($virtuemart_product_id)) {
			$virtuemart_product_id = JRequest::getInt('virtuemart_product_id', 0);
		}
		$this->setId($virtuemart_product_id);

    	$child = $this->getProductSingle($virtuemart_product_id,$front, $withCalc,$onlyPublished);

    	//store the original parent id
		$pId = $child->virtuemart_product_id;
    	$ppId = $child->product_parent_id;
		$published = $child->published;

		$i = 0;
		//Check for all attributes to inherited by parent products
    	while(!empty($child->product_parent_id)){
    		$parentProduct = $this->getProductSingle($child->product_parent_id,$front, $withCalc,$onlyPublished);
    	    $attribs = get_object_vars($parentProduct);

	    	foreach($attribs as $k=>$v){
				if(!empty($child->$k) && is_array($child->$k)){
					if(!is_array($v)) $v =array($v);
					$child->$k = array_merge($child->$k,$v);
				} else{
					if(empty($child->$k)){
						$child->$k = $v;
					}
				}
	    	}
			$child->product_parent_id = $parentProduct->product_parent_id;
    	}
		$child->published = $published;
		$child->virtuemart_product_id = $pId;
		$child->product_parent_id = $ppId;

    	return $child;
    }

    public function getProductSingle($virtuemart_product_id = null,$front=true, $withCalc = true, $onlyPublished=true){
    	if (empty($virtuemart_product_id)) {
			$virtuemart_product_id = JRequest::getInt('virtuemart_product_id', 0);
		}
		$virtuemart_product_id = $this->setId($virtuemart_product_id);

//		if(empty($this->_data)){
			if (!empty($virtuemart_product_id)) {


   			$product = $this->getTable('products');
   			$product->load($virtuemart_product_id);
   			if($onlyPublished){
   				if(empty($product->published)){
   					return $this->fillVoidProduct($product,$front);
   				}
   			}

   			$xrefTable = $this->getTable('product_medias');
			$product->virtuemart_media_id = $xrefTable->load((int)$this->_id);


//   		if(!$front){
    			$ppTable = $this->getTable('product_prices');
    			$q = 'SELECT `virtuemart_product_price_id` FROM `#__virtuemart_product_prices` WHERE `virtuemart_product_id` = "'.$virtuemart_product_id.'" ';
				$this->_db->setQuery($q);
    			$ppId = $this->_db->loadResult();
   				$ppTable->load($ppId);
				$product = (object) array_merge((array) $ppTable, (array) $product);
//   		}

   			$q = 'SELECT `virtuemart_manufacturer_id` FROM `#__virtuemart_product_manufacturers` WHERE `virtuemart_product_id` = "'.$virtuemart_product_id.'" ';
   			$this->_db->setQuery($q);
   			$mf_id = $this->_db->loadResult();

   			$mfTable = $this->getTable('manufacturers');
   			$mfTable->load((int)$mf_id);
   			$product = (object) array_merge((array) $mfTable, (array) $product);


			/* Load the categories the product is in */
			$product->categories = $this->getProductCategories($virtuemart_product_id);
			$product->virtuemart_category_id = JRequest::getInt('virtuemart_category_id', 0);
			if (empty($product->virtuemart_category_id) && isset($product->categories[0])) $product->virtuemart_category_id = $product->categories[0];

   			if(!$front && !empty($product->categories[0])){
				$catTable = $this->getTable('categories');
   				$catTable->load($product->categories[0]);
				$product->category_name = $catTable->category_name;
   			} else {
   				$product->category_name ='';
   			}

			if($front){

				/* Load the price */
				$prices = "";
//				if (VmConfig::get('show_prices',1) == '1' && $withCalc) {
				if ($withCalc) {

					/* Loads the product price details */
					if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');
					$calculator = calculationHelper::getInstance();

					/* Calculate the modificator */
					$product->ProductcustomfieldsIds = $this->getProductcustomfieldsIds($product);
					$quantityArray = JRequest::getVar('quantity',1,'post');
					$prices = $calculator->getProductPrices((int)$product->virtuemart_product_id,$product->categories,0,$quantityArray[0]);
				}

				$product->prices = $prices;

				/* Add the product link  */
				$product->link = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$virtuemart_product_id.'&virtuemart_category_id='.$product->virtuemart_category_id);

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
				$product->related = $this->getRelatedProducts($virtuemart_product_id);

				/* Load the vendor details */
				if(!class_exists('VirtueMartModelVendor')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'vendor.php');
				$product->vendor_name = VirtueMartModelVendor::getVendorName($product->virtuemart_vendor_id);

				/* Check for child products */
				$product->haschildren = $this->checkChildProducts($virtuemart_product_id);

				/* Load the custom variants */
				$product->hasproductCustoms = $this->hasproductCustoms($virtuemart_product_id);
				/* Load the custom product fields */
				$product->customfields = self::getProductCustomsField($product);

				/*  custom product fields for add to cart */
				$product->customfieldsCart = self::getProductCustomsFieldCart($product);

				/* Check the order levels */
				if (empty($product->product_order_levels)) $product->product_order_levels = '0,0';

				/* Check the stock level */
				if (empty($product->product_in_stock)) $product->product_in_stock = 0;

				/* Get stock indicator */
				$product->stock = $this->getStockIndicator($product);

				/* Get the votes */
				$product->votes = $this->getVotes($virtuemart_product_id);

				}
				else
				$product->customfields = self::getproductCustomslist($virtuemart_product_id);
			} else {
				$product = new stdClass();
				return $this->fillVoidProduct($product,$front);
			}
//		}
//		$product = $this->fillVoidProduct($product,$front);
		$this->product = $product;
		return $product;
    }

    /**
     * This fills the empty properties of a product
     * todo add if(!empty statements
     *
     * @author Max Milbers
     * @param unknown_type $product
     * @param unknown_type $front
     */
    private function fillVoidProduct($product,$front=true){

		/* Load an empty product */
	 	 $product = $this->getTable('products');
	 	 $product->load();

	 	 /* Add optional fields */
	 	 $product->virtuemart_manufacturer_id = null;
	 	 $product->virtuemart_product_price_id = null;
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
	 	 	$product->virtuemart_category_id = 0;
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
	private function getProductCategories($virtuemart_product_id=0) {

		if(!empty($this->_db))$this->_db = JFactory::getDBO();

		$categories = array();
		if ($virtuemart_product_id > 0) {
			$q = 'SELECT `virtuemart_category_id` FROM `#__virtuemart_product_categories` WHERE `virtuemart_product_id` = "'.$virtuemart_product_id.'"';
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
			if($product = $this->getProduct($id,$front, $withCalc, $onlyPublished)){
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
	* @param int $virtuemart_product_id The ID of the product
	* @return array containing all the files and their data
	*/
	public function getRelatedProducts($virtuemart_product_id) {
		$this->_db = JFactory::getDBO();
		$q = "SELECT `p`.`virtuemart_product_id`, `product_sku`, `product_name`, related_products
			FROM `#__virtuemart_products` p, `#__virtuemart_product_relations` `r`
			WHERE `r`.`virtuemart_product_id` = ".$virtuemart_product_id."
			AND `p`.published = 1
			AND FIND_IN_SET(`p`.`virtuemart_product_id`, REPLACE(`r`.`related_products`, '|', ',' )) LIMIT 0, 4";
		$this->_db->setQuery($q);
		$related_products = $this->_db->loadObjectList();

		/* Get the price also */
		if (VmConfig::get('show_prices') == '1') {
			/* Loads the product price details */
			if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');
			$calculator = calculationHelper::getInstance();
			if(!empty($related_products)){
				foreach ($related_products as $rkey => $related) {
					$related_products[$rkey]->price = $calculator->getProductPrices($related->virtuemart_product_id);
					$cats = $this->getProductCategories($related->virtuemart_product_id);
					if(!empty($cats))$related->virtuemart_category_id = $cats[0]; //else $related->virtuemart_category_id = 0;
					/* Add the product link  */
					$related_products[$rkey]->link = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$related->virtuemart_product_id.'&virtuemart_category_id='.$related->virtuemart_category_id);
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
	* @param int $virtuemart_category_id the category ID where to get the products for
	* @return array containing product objects
	*/
	public function getProductsInCategory() {

		if (empty($this->products)) {

			if (empty($this->_query)) $this->_query = $this->_buildQuery();
			$virtuemart_product_ids = $this->_getList($this->_query, $this->getState('limitstart'), $this->getState('limit'));
			/* Collect the product data */
			$this->_total = count($this->_getList($this->_query));

			foreach ($virtuemart_product_ids as $virtuemart_product_id) {
				$this->products[] = $this->getProduct($virtuemart_product_id->virtuemart_product_id);
			}
		}
		return $this->products;
	}

	function _buildQuery()
	{
		//$mainframe = Jfactory::getApplication();
		//$option = JRequest::getWord('option');
		//$mainframe->getUserStateFromRequest( $option.'order'  , 'order' ,''	,'word' ) );
		$virtuemart_category_id = JRequest::getInt('virtuemart_category_id', 0 );

		$filter_order  = JRequest::getVar('orderby', VmConfig::get('browse_orderby_field','virtuemart_product_id'));

		$filter_order_Dir = JRequest::getVar('order', 'ASC');

		$search = JRequest::getVar('search', false );
		$joinCategory = false ;
		$joinMf = false ;
		$joinPrice = false ;
		$where[] = " `#__virtuemart_products`.`published`='1' ";

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
		if ($virtuemart_category_id>0){
			$joinCategory = true ;
			$where[] = ' `#__virtuemart_product_categories`.`virtuemart_category_id` = '.$virtuemart_category_id;
		}
		/* sanitize $filter_order and dir */
		$browse_orderby_fields = VmConfig::get('browse_orderby_fields') ;
		if (!is_array($browse_orderby_fields)) $browse_orderby_fields = array($browse_orderby_fields);
		if (!in_array($filter_order, $browse_orderby_fields)) {
			$filter_order = VmConfig::get('browse_orderby_field');
		}

		$virtuemart_manufacturer_id = JRequest::getInt('virtuemart_manufacturer_id', false );
		if ($virtuemart_manufacturer_id) {
			$joinMf = true ;
			$where[] = ' `#__virtuemart_product_manufacturers`.`virtuemart_manufacturer_id` = '.$virtuemart_manufacturer_id;
		}

		/* search Order fields set */

		if (VmConfig::get('check_stock') && Vmconfig::get('show_out_of_stock_products') != '1')
			$where[] = ' `product_in_stock` > 0 ';
		// special  orders case
		switch ($filter_order) {
			case 'product_special':
				$where[] = ' `#__virtuemart_products`.`product_special`="Y" ';// TODO Change  to  a  individual button
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
				$orderBy = ' ORDER BY `#__virtuemart_products`.`'.$filter_order.'` ';
				break;
		}

		$query = "SELECT `#__virtuemart_products`.`virtuemart_product_id` FROM `#__virtuemart_products` ";
		if ($joinCategory == true) {
			$query .= ' LEFT JOIN `#__virtuemart_product_categories` ON `#__virtuemart_products`.`virtuemart_product_id` = `#__virtuemart_product_categories`.`virtuemart_product_id`
			 LEFT JOIN `#__virtuemart_categories` ON `#__virtuemart_categories`.`virtuemart_category_id` = `#__virtuemart_product_categories`.`virtuemart_category_id`';
		}
		if ($joinMf == true) {
			$query .= ' LEFT JOIN `#__virtuemart_product_manufacturers` ON `#__virtuemart_products`.`virtuemart_product_id` = `#__virtuemart_product_manufacturers`.`virtuemart_product_id`
			 LEFT JOIN `#__virtuemart_manufacturers` ON `#__virtuemart_manufacturers`.`virtuemart_manufacturer_id` = `#__virtuemart_product_manufacturers`.`virtuemart_manufacturer_id` ';
		}
		if ($joinPrice == true) {
			$query .= ' LEFT JOIN `#__virtuemart_product_prices` ON `#__virtuemart_products`.`virtuemart_product_id` = `#__virtuemart_product_prices`.`virtuemart_product_id` ';
		}
		$query .= ' WHERE '.implode(" AND ", $where ).' GROUP BY `#__virtuemart_products`.`virtuemart_product_id` '. $orderBy .$filter_order_Dir ;
		return $query ;
	}

	/**
	 * This function retrieves the "neighbor" products of a product specified by $virtuemart_product_id
	 * Neighbors are the previous and next product in the current list
	 *
	 * @author RolandD, Max Milbers
	 * @param object $product The product to find the neighours of
	 * @return array
	 */
	private function getNeighborProducts($product) {
		$this->_db = JFactory::getDBO();
		$neighbors = array('previous' => '','next' => '');

		$q = "SELECT x.`virtuemart_product_id`, product_list, `p`.product_name
			FROM `#__virtuemart_product_categories` x
			LEFT JOIN `#__virtuemart_products` `p`
			ON `p`.`virtuemart_product_id` = `x`.`virtuemart_product_id`
			WHERE `virtuemart_category_id` = ".$product->virtuemart_category_id."
			ORDER BY `product_list`, `x`.`virtuemart_product_id`";
		$this->_db->setQuery($q);
		$products = $this->_db->loadAssocList('virtuemart_product_id');

		/* Move the internal pointer to the current product */
		if(!empty($products)){
			foreach ($products as $virtuemart_product_id => $xref) {
				if ($virtuemart_product_id == $product->virtuemart_product_id) break;
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
				$filter = 'AND `#__virtuemart_products`.`product_special`="Y" ';
				break;
			case 'latest':
				$filter = 'AND `#__virtuemart_products`.`modified_on` > '.(time()-(60*60*24*7)).' ';
				break;
			case 'random':
				$filter = '';
				break;
			case 'topten';
				$filter ='';
		}

		$cat_xref_table = $categoryId? ', `#__virtuemart_product_categories` ':'';
		$query = 'SELECT `virtuemart_product_id` ';
		$query .= 'FROM `#__virtuemart_products`'.$cat_xref_table.' WHERE `virtuemart_product_id` > 0 ';
//	        $query  = 'SELECT `product_sku`,`#__virtuemart_products`.`virtuemart_product_id`, `#__virtuemart_product_categories`.`virtuemart_category_id`,`product_name`, `product_s_desc`, `#__virtuemart_products`.`virtuemart_media_id`, `product_in_stock`, `product_url` ';
//	        $query .= 'FROM `#__virtuemart_products`, `#__virtuemart_product_categories`, `#__virtuemart_categories` WHERE ';
//	        $query .= '(`#__virtuemart_products`.`product_parent_id`="" OR `#__virtuemart_products`.`product_parent_id`="0") ';
//	        $query .= 'AND `#__virtuemart_products`.`virtuemart_product_id`=`#__virtuemart_product_categories`.`virtuemart_product_id` ';
			if ($categoryId) {
				$query .= 'AND `#__virtuemart_categories`.`virtuemart_category_id`=`#__virtuemart_product_categories`.`virtuemart_category_id` ';
				$query .= 'AND `#__virtuemart_categories`.`virtuemart_category_id`=' . $categoryId . ' ';
			}
	        $query .= 'AND `#__virtuemart_products`.`published`="1" ';
	        $query .= $filter;
	        if (VmConfig::get('check_stock') && VmConfig::get('show_out_of_stock_products') != '1') {
		        $query .= ' AND `product_in_stock` > 0 ';
	        }
			$query .= ' group by `#__virtuemart_products`.`virtuemart_product_id` ';

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
					$price = $calculator->getProductPrices((int)$featured->virtuemart_product_id);
				}
				$featured->prices = $price;

				/* Child products */
				$featured->haschildren = $this->checkChildProducts($featured->virtuemart_product_id);

				$result[] = $featured;
			}

		}

		return $result;
    }

    /**
     * Select the products to list on the product list page
     */
    public function getProductList() {
     	/* Pagination */
     	$this->getPagination();

		$cat_xref_table = (JRequest::getInt('virtuemart_category_id', 0) > 0)? ', `#__virtuemart_product_categories` ':'';
     	$q = 'SELECT `#__virtuemart_products`.`virtuemart_product_id` FROM `#__virtuemart_products`'.$cat_xref_table.' '.$this->getProductListFilter();

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
		$q = "SELECT virtuemart_product_id AS id, CONCAT(product_name, '::', product_sku) AS value
			FROM #__virtuemart_products";
		if ($filter) $q .= " WHERE product_name LIKE '%".$filter."%'";
		$this->_db->setQuery($q);
		return $this->_db->loadObjectList();
	}

    /**
    * List of tables to include for the product query
    * @author RolandD
    */
    private function getProductListQuery() {
    	return 'FROM #__virtuemart_products
			LEFT OUTER JOIN #__virtuemart_product_prices
			ON #__virtuemart_products.virtuemart_product_id = #__virtuemart_product_prices.virtuemart_product_id
			LEFT OUTER JOIN #__virtuemart_product_manufacturers
			ON #__virtuemart_products.virtuemart_product_id = #__virtuemart_product_manufacturers.virtuemart_product_id
			LEFT OUTER JOIN #__virtuemart_manufacturers
			ON #__virtuemart_product_manufacturers.virtuemart_manufacturer_id = #__virtuemart_manufacturers.virtuemart_manufacturer_id ' .
			//LEFT OUTER JOIN #__virtuemart_products_attribute
			// ON #__virtuemart_products.virtuemart_product_id = #__virtuemart_products_attribute.virtuemart_product_id
			'LEFT OUTER JOIN #__virtuemart_product_categories
			ON #__virtuemart_products.virtuemart_product_id = #__virtuemart_product_categories.virtuemart_product_id
			LEFT OUTER JOIN #__virtuemart_categories
			ON #__virtuemart_product_categories.virtuemart_category_id = #__virtuemart_categories.virtuemart_category_id
			LEFT OUTER JOIN #__virtuemart_category_categories
			ON #__virtuemart_categories.virtuemart_category_id = #__virtuemart_category_categories.category_child_id
			LEFT OUTER JOIN #__virtuemart_vendors
			ON #__virtuemart_products.virtuemart_vendor_id = #__virtuemart_vendors.virtuemart_vendor_id';
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
     	if (JRequest::getInt('product_parent_id', 0) > 0) $filters[] = '#__virtuemart_products.`product_parent_id` = '.JRequest::getInt('product_parent_id');
     	else // $filters[] = '#__virtuemart_products.`product_parent_id` = 0';
     	/* Category ID */
     	if (JRequest::getInt('virtuemart_category_id', 0) > 0){
     		$filters[] = '`#__virtuemart_product_categories`.`virtuemart_category_id` = '.JRequest::getInt('virtuemart_category_id');
     		$filters[] = '`#__virtuemart_products`.`virtuemart_product_id` = `#__virtuemart_product_categories`.`virtuemart_product_id`';
     	}
     	/* Product name */
     	if (JRequest::getVar('filter_product', false)) $filters[] = '#__virtuemart_products.`product_name` LIKE '.$this->_db->Quote('%'.JRequest::getVar('filter_product').'%');
     	/* Product type ID */
     	//if (JRequest::getInt('virtuemart_producttype_id', false)) $filters[] = '#__virtuemart_products.`product_name` LIKE '.$this->_db->Quote('%'.JRequest::getVar('filter_product').'%');
     	/* Time filter */
     	if (JRequest::getVar('search_type', '') != '') {
     		$search_order = JRequest::getVar('search_order') == 'bf' ? '<' : '>';
     		switch (JRequest::getVar('search_type')) {
     			case 'product':
     				$filters[] = '#__virtuemart_products.`modified_on` '.$search_order.' '.strtotime(JRequest::getVar('search_date'));
     				break;
     			case 'price':
     				$filters[] = '#__virtuemart_product_prices.`modified_on` '.$search_order.' '.strtotime(JRequest::getVar('search_date'));
     				break;
     			case 'withoutprice':
     				$filters[] = '#__virtuemart_product_prices.`product_price` IS NULL';
     				break;
     		}
     	}
     	if (count($filters) > 0) $filter = ' WHERE '.implode(' AND ', $filters).' GROUP BY #__virtuemart_products.`virtuemart_product_id` ORDER BY '.$filter_order." ".$filter_order_Dir;
     	else $filter = ' GROUP BY #__virtuemart_products.`virtuemart_product_id` ORDER BY '.$filter_order." ".$filter_order_Dir;
     	return $filter;
    }


    /**
    * Check if the product has any children
    *
    * @author RolandD
    * @param int $virtuemart_product_id Product ID
    * @return bool True if there are child products, false if there are no child products
    */
    public function checkChildProducts($virtuemart_product_id) {
//     	$this->_db = JFactory::getDBO();
     	$q  = "SELECT IF(COUNT(virtuemart_product_id) > 0, 'Y', 'N') FROM `#__virtuemart_products` WHERE `product_parent_id` = ".$virtuemart_product_id;
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
		return modelfunctions::publish('cid','products',$publishId);

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
    	$virtuemart_category_id = JRequest::getInt('virtuemart_category_id');

    	/* Check if all the entries are numbers */
		foreach( $order as $list_id ) {
			if( !is_numeric( $list_id ) ) {
				$mainframe->enqueueMessage(JText::_('COM_VIRTUEMART_SORT_ERR_NUMBERS_ONLY'), 'error');
				return false;
			}
		}

		/* Get the list of product IDs */
		$q = "SELECT virtuemart_product_id
			FROM #__virtuemart_product_categories
			WHERE virtuemart_category_id = ".$virtuemart_category_id;
		$this->_db->setQuery($q);
		$virtuemart_product_ids = $this->_db->loadResultArray();

		foreach( $order as $key => $list_id ) {
			$q = "UPDATE #__virtuemart_product_categories ";
			$q .= "SET product_list = ".$list_id;
			$q .= " WHERE virtuemart_category_id ='".$virtuemart_category_id."' ";
			$q .= " AND virtuemart_product_id ='".$virtuemart_product_ids[$key]."' ";
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
    	$virtuemart_category_id = JRequest::getInt('virtuemart_category_id');

    	$q = "SELECT virtuemart_product_id, product_list
    		FROM #__virtuemart_product_categories
    		WHERE virtuemart_category_id = ".$virtuemart_category_id."
    		ORDER BY product_list";
    	$this->_db->setQuery($q);
    	$products = $this->_db->loadAssocList('virtuemart_product_id');
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
		$q = "UPDATE #__virtuemart_product_categories
			SET product_list = ".$products[$prev_id]['product_list']."
			WHERE virtuemart_category_id = ".$virtuemart_category_id."
			AND virtuemart_product_id = ".$products[$cid]['virtuemart_product_id'];
		$this->_db->setQuery($q);
		$this->_db->query();

		/* Check if a next product_list exists */
    	if (is_null($products[$cid]['product_list'])) {
    		$products[$cid]['product_list'] = $prev_id+1;
    	}
		/* Update the previous product */
		$q = "UPDATE #__virtuemart_product_categories
			SET product_list = ".$products[$cid]['product_list']."
			WHERE virtuemart_category_id = ".$virtuemart_category_id."
			AND virtuemart_product_id = ".$products[$prev_id]['virtuemart_product_id'];
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
    	$virtuemart_category_id = JRequest::getInt('virtuemart_category_id');

    	$q = "SELECT virtuemart_product_id, product_list
    		FROM #__virtuemart_product_categories
    		WHERE virtuemart_category_id = ".$virtuemart_category_id."
    		ORDER BY product_list";
    	$this->_db->setQuery($q);
    	$products = $this->_db->loadAssocList('virtuemart_product_id');
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
		$q = "UPDATE #__virtuemart_product_categories
			SET product_list = ".$products[$next_id]['product_list']."
			WHERE virtuemart_category_id = ".$virtuemart_category_id."
			AND virtuemart_product_id = ".$products[$cid]['virtuemart_product_id'];
		$this->_db->setQuery($q);
		$this->_db->query();

		/* Check if a next product_list exists */
    	if (is_null($products[$cid]['product_list'])) {
    		$products[$cid]['product_list'] = $next_id-1;
    	}
		/* Update the next product */
		$q = "UPDATE #__virtuemart_product_categories
			SET product_list = ".$products[$cid]['product_list']."
			WHERE virtuemart_category_id = ".$virtuemart_category_id."
			AND virtuemart_product_id = ".$products[$next_id]['virtuemart_product_id'];
		$this->_db->setQuery($q);
		$this->_db->query();
	}

	//TODO merge getRelatedProducts functions
	/**
	 * Get the related products
	 */
//	 public function getRelatedProducts($virtuemart_product_id=false) {
//	 	 if (!$virtuemart_product_id) return array();
//	 	 else {
////			$this->_db = JFactory::getDBO();
//			$q = "SELECT related_products FROM #__virtuemart_product_relations WHERE virtuemart_product_id='".$virtuemart_product_id."'";
//			$this->_db->setQuery($q);
//			$results = $this->_db->loadResult();
//			if ($results) {
//				$ids = 'virtuemart_product_id =' . implode(' OR virtuemart_product_id =', explode("|", $results));
//				$q = "SELECT virtuemart_product_id AS id, CONCAT(product_name, '::', product_sku) AS text
//					FROM #__virtuemart_products
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
			$data = JRequest::get('post', 0);	//TODO 4?
		}

		/* Setup some place holders */
		$product_data = $this->getTable('products');

		/* Load the old product details first */
		$product_data->load($data['virtuemart_product_id']);

		/* Get the product data */
		if (!$product_data->bind($data)) {
			$this->setError($product_data->getError());
			return false;
		}

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

		if(empty($data['virtuemart_product_id'])){
			$dbv = $product_data->getDBO();
			//I dont like the solution to use three variables
			$this->_id = $product_data->virtuemart_product_id = $data['virtuemart_product_id'] = $dbv->insertid();
		}

		if(!empty($data['virtuemart_media_id'])){
			// Process the images
			if(!class_exists('VirtueMartModelMedia')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'media.php');
			$mediaModel = new VirtueMartModelMedia();
			$xrefTable = $this->getTable('product_medias');
			$mediaModel->storeMedia($data,$xrefTable,'product');
		}

		$product_price_table = $this->getTable('product_prices');

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
			// Make sure the price record is valid
			if (!$product_price_table->check()) {
				$this->setError($product_price_table->getError());
				dump($product_price_table,'pricecheck error');
				return false;

			}

			// Save the price record to the database
			if (!$product_price_table->store()) {
				$this->setError($product_price_table->getError());
				dump($product_price_table,'store error');
				return false;
			}
//			dump($product_price_table,'store done');
		}


		/* Update manufacturer link */
		if(!empty($data['virtuemart_manufacturer_id'])){
			if(!class_exists('modelfunctions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'modelfunctions.php');
			modelfunctions::storeArrayData('#__virtuemart_product_manufacturers','virtuemart_product_id','virtuemart_manufacturer_id',$product_data->virtuemart_product_id,$data['virtuemart_manufacturer_id']);
		}

		/* Update waiting list  */
		if ($data['product_in_stock'] > 0 && $data['notify_users'] == '1' ) {
			$waitinglist = new VirtueMartModelWaitingList();
			$waitinglist->notifyList($data['virtuemart_product_id']);
		}

		if(!empty($data['categories']) && count($data['categories'])>0){
			/* Delete old category links */
			$q  = "DELETE FROM `#__virtuemart_product_categories` ";
			$q .= "WHERE `virtuemart_product_id` = '".$product_data->virtuemart_product_id."' ";
			$this->_db->setQuery($q);
			$this->_db->Query();

			/* Store the new categories */
			foreach( $data["categories"] as $virtuemart_category_id ) {
				$this->_db->setQuery('SELECT IF(ISNULL(`product_list`), 1, MAX(`product_list`) + 1) as list_order FROM `#__virtuemart_product_categories` WHERE `virtuemart_category_id`='.$virtuemart_category_id );
				$list_order = $this->_db->loadResult();

				$q  = "INSERT INTO #__virtuemart_product_categories ";
				$q .= "(virtuemart_category_id,virtuemart_product_id,product_list) ";
				$q .= "VALUES ('".$virtuemart_category_id."','". $product_data->virtuemart_product_id . "', ".$list_order. ")";
				$this->_db->setQuery($q);
				$this->_db->query();
			}
		}

		/* Update related products */
		if (array_key_exists('related_products', $data)) {
			/* Insert Pipe separated Related Product IDs */
			$q = "REPLACE INTO #__virtuemart_product_relations (virtuemart_product_id, related_products)";
			$q .= " VALUES( '".$product_data->virtuemart_product_id."', '".implode('|', $data['related_products'])."') ";
			$this->_db->setQuery($q);
			$this->_db->query();
		}
		else {
			$q = "DELETE FROM #__virtuemart_product_relations WHERE virtuemart_product_id='".$product_data->virtuemart_product_id."'";
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
			if(!class_exists('VirtueMartModelCustom')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'custom.php');
			VirtueMartModelCustom::saveProductfield($data['field'],$product_data->virtuemart_product_id);
		}


		return $product_data->virtuemart_product_id;
	}

	/**
	 * This function creates a child for a given product id
	 * @author Max Milbers
	 * @param int id of parent id
	 */
	public function createChild($id){
		// created_on , modified_on
		$vendorId = 1 ;
		$q = 'INSERT INTO `#__virtuemart_products` ( `virtuemart_vendor_id`, `product_parent_id` ) VALUES ( '.$vendorId.', '.$id.' )';
		$this->_db->setQuery($q);
		$this->_db->query();
		return $this->_db->insertid();
	}

	/**
	 * Creates a clone of a given product id
	 *
	 * @author Max Milbers
	 * @param int $virtuemart_product_id
	 */

	public function createClone($id){
		$product = $this->getProduct($id);
		$product->virtuemart_product_id = 0;
		$this->saveProduct($product,true,true,false);
		return $this->_id;
	}

	/**
	* Remove a product
	* @author RolandD
	* @todo Add sanity checks
	*/
	public function removeProduct($old_virtuemart_product_id=false) {
//		$this->_db = JFactory::getDBO();

		/* Get the product IDs to remove */
		$cids = array();
		if (!$old_virtuemart_product_id) {
			$cids = JRequest::getVar('cid');
			if (!is_array($cids)) $cids = array($cids);
		}
		else $cids[] = $old_virtuemart_product_id;

		/* Start removing */
		foreach ($cids as $key => $virtuemart_product_id) {
			/* First copy the product in the product table */
			$product_data = $this->getTable('products');

			/* Load the product details */
			$product_data->load($virtuemart_product_id);

			/* Delete all children if needed */
			if ($product_data->product_parent_id == 0) {
				/* Delete all children */
				/* Get a list of child products */
				$q = "SELECT virtuemart_product_id FROM #__virtuemart_products WHERE product_parent_id = ".$virtuemart_product_id;
				$this->_db->setQuery($q);
				$children = $this->_db->loadResultArray();
				foreach ($children as $child_key => $child_id) {
					$this->removeProduct($child_id);
				}
			}


			/* Delete categories xref */
			$q  = "DELETE FROM #__virtuemart_product_categories WHERE virtuemart_product_id = ".$virtuemart_product_id;
			$this->_db->setQuery($q);
			$this->_db->query();

			/* Delete product - manufacturer xref */
			$q = "DELETE FROM #__virtuemart_product_manufacturers WHERE virtuemart_product_id = ".$virtuemart_product_id;
			$this->_db->setQuery($q);
			$this->_db->query();

			/* Delete Product - ProductType Relations */
			$q  = "DELETE FROM #__virtuemart_product_producttypes WHERE virtuemart_product_id = ".$virtuemart_product_id;
			$this->_db->setQuery($q);
			$this->_db->query();

			/* Delete product votes */
			$q  = "DELETE FROM #__virtuemart_product_reviews WHERE virtuemart_product_id = ".$virtuemart_product_id;
			$this->_db->setQuery($q);
			$this->_db->query();

			/* Delete product reviews */
			$q = "DELETE FROM #__virtuemart_product_votes WHERE virtuemart_product_id = ".$virtuemart_product_id;
			$this->_db->setQuery($q);
			$this->_db->query();

			/* Delete Product Relations */
			$q  = "DELETE FROM #__virtuemart_product_relations WHERE virtuemart_product_id = ".$virtuemart_product_id;
			$this->_db->setQuery($q); $this->_db->query();

			/* find and delete Product Types */
			$q = "SELECT virtuemart_producttype_id FROM #__virtuemart_product_producttypes WHERE virtuemart_product_id = ".$virtuemart_product_id;
			$this->_db->setQuery($q);
			/* TODO the product is not deleted from this tables !!*/
			$virtuemart_producttype_ids = $this->_db->loadResultArray();
			foreach ($virtuemart_producttype_ids as $virtuemart_producttype_id)
			$q  = "DELETE FROM #__virtuemart_producttypes_".$virtuemart_producttype_id." WHERE virtuemart_product_id = ".$virtuemart_product_id;
			$this->_db->setQuery($q); $this->_db->query();

			/* Delete Product Types xref */
			$q  = "DELETE FROM #__virtuemart_product_producttypes WHERE virtuemart_product_id = ".$virtuemart_product_id;
			$this->_db->setQuery($q); $this->_db->query();

			/* delete Product custom fields and Xref */
			$q = "DELETE `#__virtuemart_product_customfields`,`#__virtuemart_customfields`
				FROM  `#__virtuemart_product_customfields`,`#__virtuemart_customfields`
				WHERE `#__virtuemart_product_customfields`.`virtuemart_customfield_id` = `#__virtuemart_customfields`.`virtuemart_customfield_id`
				AND `#__virtuemart_product_customfields`.`virtuemart_product_id` =".$virtuemart_product_id;
			$this->_db->setQuery($q); $this->_db->query();


			/* Delete Prices */
			$q  = "DELETE FROM #__virtuemart_product_prices WHERE virtuemart_product_id = ".$virtuemart_product_id;
			$this->_db->setQuery($q);
			$this->_db->query();

			/* Delete the product itself */
			$product_data->delete($virtuemart_product_id);
		}
		return true;
	}


//    /**
//    * Add a product to the recent products list
//    * @author RolandD
//    */
//    public function addRecentProduct($virtuemart_product_id, $virtuemart_category_id, $maxviewed) {
//    	$session = JFactory::getSession();
//		$recentproducts = $session->get("recentproducts", null);
//		if (empty($recentproducts)) $recentproducts['idx'] = 0;
//
//    	//Check to see if we alread have recent
//    	if ($recentproducts['idx'] !=0) {
//    		for($i=0; $i < $recentproducts['idx']; $i++){
//    			//Check if it already exists and remove and reorder array
//    			if ($recentproducts[$i]['virtuemart_product_id'] == $virtuemart_product_id) {
//    				for($k=$i; $k < $recentproducts['idx']-1; $k++){
//    					$recentproducts[$k] = $recentproducts[$k+1];
//    				}
//    				array_pop($recentproducts);
//    				$recentproducts['idx']--;
//    			}
//    		}
//    	}
//    	// add product to recently viewed
//    	$recentproducts[$recentproducts['idx']]['virtuemart_product_id'] = $virtuemart_product_id;
//    	$recentproducts[$recentproducts['idx']]['virtuemart_category_id'] = $virtuemart_category_id;
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
//    * @param  int $virtuemart_product_id the ID of the product currently being viewed, don't want it in the list
//    * @param  int $maxitems the number of items to retrieve
//	* @return boolean true if there are recent products, false if there are no recent products
//    */
//    public function getRecentProducts($virtuemart_product_id=null, $maxitems=5) {
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
//			if($recentproducts[$i]['virtuemart_product_id'] == $virtuemart_product_id){
//				continue;
//			}
//			// If we have not reached max products add the next product
//			if ($k < $maxitems) {
//				$prod_id = $recentproducts[$i]['virtuemart_product_id'];
//				$virtuemart_category_id = $recentproducts[$i]['virtuemart_category_id'];
//				$q = "SELECT product_name, category_name, c.category_flypage,product_s_desc ";
//				$q .= "FROM #__virtuemart_products as p,#__virtuemart_categories as c,#__virtuemart_product_categories as cx ";
//				$q .= "WHERE p.virtuemart_product_id = '".$prod_id."' ";
//				$q .= "AND c.virtuemart_category_id = '".$virtuemart_category_id."' ";
//				$q .= "AND p.virtuemart_product_id = cx.virtuemart_product_id ";
//				$q .= "AND c.virtuemart_category_id=cx.virtuemart_category_id ";
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
//					$recent[$k]['product_url'] = JRoute::_('index.php?option=com_virtuemart&view=product&virtuemart_product_id='.$prod_id.'&virtuemart_category_id='.$virtuemart_category_id.'&flypage='.$flypage);
//					$recent[$k]['category_url'] = JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_category_id='.$virtuemart_category_id);
//					$recent[$k]['product_name'] = JFilterInput::clean($product->product_name);
//					$recent[$k]['category_name'] = $product->category_name;
////					$recent[$k]['virtuemart_media_id'] = $product->virtuemart_media_id;
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
	public function getPrice($virtuemart_product_id=false,$customVariant=false){

		$this->_db = JFactory::getDBO();
		if (!$virtuemart_product_id) $virtuemart_product_id = JRequest::getInt('virtuemart_product_id', 0);

		//This is one of the dead sins of OOP and MUST NOT be done
//		$q = "SELECT `p`.*, `x`.`virtuemart_category_id`, `x`.`product_list`, `m`.`virtuemart_manufacturer_id`, `m`.`mf_name`
//			FROM `#__virtuemart_products` `p`
//			LEFT JOIN `#__virtuemart_product_categories` x
//			ON `x`.`virtuemart_product_id` = `p`.`virtuemart_product_id`
//			LEFT JOIN `#__virtuemart_product_manufacturers` `mx`
//			ON `mx`.`virtuemart_product_id` = `p`.`virtuemart_product_id`
//			LEFT JOIN `#__virtuemart_manufacturers` `m`
//			ON `m`.`virtuemart_manufacturer_id` = `mx`.`virtuemart_manufacturer_id`
//			WHERE `p`.`virtuemart_product_id` = ".$virtuemart_product_id;
//		$this->_db->setQuery($q);
//		$product = $this->_db->loadObject();

		$product = $this->getProduct($virtuemart_product_id);

		/* NEW Load the Customs Field Cart Price */
		$product->CustomsFieldCartPrice = $this->getProductCustomsFieldWithPrice($product);
		/* Loads the product price details */
		if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');
		$calculator = calculationHelper::getInstance();

		$quantityArray = JRequest::getVar('quantity',1,'post');

		/* Calculate the modificator */
		$variantPriceModification = $calculator->calculateModificators($product,$customVariant);
		$quantityArray = JRequest::getVar('quantity',1,'post');

		$prices = $calculator->getProductPrices($product->virtuemart_product_id,$product->categories,$variantPriceModification,$quantityArray[0]);

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
	* @param int $virtuemart_product_id the product ID to get the reviews for
	* @return array of objects with product reviews
	*/
	public function getProductReviews($virtuemart_product_id) {
		$this->_db = JFactory::getDBO();
		$showall = JRequest::getBool('showall', 0);

		$q = 'SELECT `comment`, `time`, `userid`, `user_rating`, `username`, `name`
			FROM `#__virtuemart_product_reviews` `r`
			LEFT JOIN `#__users` `u`
			ON `u`.`id` = `r`.`userid`
			WHERE `virtuemart_product_id` = "'.$virtuemart_product_id.'"
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

	$virtuemart_category_id = JRequest::getInt('virtuemart_category_id', 0 );
	$fieldLink = '&virtuemart_category_id='.$virtuemart_category_id;
	$search = JRequest::getVar('search', '' );
	if ($search != '' ) $fieldLink .= '&search=true&keyword='.JRequest::getVar('keyword', '' );


	/* Collect the product IDS for manufacturer list */
	$db = JFactory::getDBO();
	if (empty($this->_query)) $this->_query = $this->_buildQuery();
	$db->setQuery($this->_query);
	$mf_virtuemart_product_ids = $db->loadResultArray();
	//$mf_virtuemart_product_ids = array();
	//foreach ($virtuemart_product_ids as $virtuemart_product_id) $mf_virtuemart_product_ids[] = $virtuemart_product_id->virtuemart_product_id ;

	/* manufacturer link list*/
	$manufacturerTxt ='';
	$virtuemart_manufacturer_id = JRequest::getVar('virtuemart_manufacturer_id',0);
	if ($virtuemart_manufacturer_id != '' ) $manufacturerTxt ='&virtuemart_manufacturer_id='.$virtuemart_manufacturer_id;
	if ($mf_virtuemart_product_ids) {
		$query = 'SELECT DISTINCT `#__virtuemart_manufacturers`.`mf_name`,`#__virtuemart_manufacturers`.`virtuemart_manufacturer_id` FROM `#__virtuemart_manufacturers`';
		$query .= ' LEFT JOIN `#__virtuemart_product_manufacturers` ON `#__virtuemart_manufacturers`.`virtuemart_manufacturer_id` = `#__virtuemart_product_manufacturers`.`virtuemart_manufacturer_id` ';
		$query .= ' WHERE `#__virtuemart_product_manufacturers`.`virtuemart_product_id` in ('.implode (',', $mf_virtuemart_product_ids ).') ';
		$query .= ' ORDER BY `#__virtuemart_manufacturers`.`mf_name`';
		$db->setQuery($query);
		$manufacturers = $db->loadObjectList();

		$manufacturerLink='';
		if (count($manufacturers)>0) {
			$manufacturerLink ='<div class="orderlist">';
			if ($virtuemart_manufacturer_id > 0) $manufacturerLink .='<div><a title="" href="'.JRoute::_('index.php?option=com_virtuemart&view=category'.$fieldLink.$orderTxt.$orderbyTxt ) .'">'.JText::_('COM_VIRTUEMART_SEARCH_SELECT_ALL_MANUFACTURER').'</a></div>';
			if (count($manufacturers)>1) {
				foreach ($manufacturers as $mf) {
					$link = JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_manufacturer_id='.$mf->virtuemart_manufacturer_id.$fieldLink.$orderTxt.$orderbyTxt ) ;
					if ($mf->virtuemart_manufacturer_id != $virtuemart_manufacturer_id) {
						$manufacturerLink .='<div><a title="'.$mf->mf_name.'" href="'.$link.'">'.$mf->mf_name.'</a></div>';
					}
					else $currentManufacturerLink ='<div class="activeOrder">'.$mf->mf_name.'</div>';
				}
			} elseif ($virtuemart_manufacturer_id > 0) $currentManufacturerLink =JText::_('COM_VIRTUEMART_PRODUCT_DETAILS_MANUFACTURER_LBL').'<div class="activeOrder">'. $manufacturers[0]->mf_name.'</div>';
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
	* @param int $virtuemart_product_id the product ID to get reviews for
	* @return array containing review data
	*/
	public function getVotes($virtuemart_product_id) {
		$result = array();
		if (VmConfig::get('allow_reviews', 0) == '1') {
			$this->_db = JFactory::getDBO();

			$q = "SELECT `votes`, `allvotes`, `rating`
				FROM `#__virtuemart_product_votes`
				WHERE `virtuemart_product_id` = ".$virtuemart_product_id;
			$this->_db->setQuery($q);
			$result = $this->_db->loadObject();
		}
		return $result;
	}




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
		$this->_db->setQuery('UPDATE `#__virtuemart_products` '
					. 'SET `product_sales` = `product_sales` + ' . $_amount . ' '
					. 'WHERE `virtuemart_product_id` = ' . $_id
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
		$this->_db->setQuery('UPDATE `#__virtuemart_products` '
					. 'SET `product_sales` = `product_sales` - ' . $_amount . ' '
					. 'WHERE `virtuemart_product_id` = ' . $_id
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
		$this->_db->setQuery('UPDATE `#__virtuemart_products` '
					. 'SET `product_sales` = `product_sales` - ' . $_amount . ' '
					. 'WHERE `virtuemart_product_id` = ' . $_id
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

		$this->_db->setQuery('UPDATE `#__virtuemart_products` '
					. 'SET `product_in_stock` = `product_in_stock` ' . $_sign . $_amount . ' '
					. 'WHERE `virtuemart_product_id` = ' . $_id
					);
		$this->_db->query();

		if ($_sign == '-') {
			$this->_db->setQuery('SELECT `product_in_stock` < `low_stock_notification` '
					. 'FROM `#__virtuemart_products` '
					. 'WHERE `virtuemart_product_id` = ' . $_id
			);
			if ($this->_db->loadResult() == 1) {
				// TODO Generate low stock warning
			}
		}
	}


  /**
     * AUthor Kohl Patrick
     * Load all custom fields for a Single product
     * return custom fields value and definition
     */
     public function getproductCustomslist($virtuemart_product_id) {

		 if ($this->hasproductCustoms($virtuemart_product_id )) {

		$query='SELECT C.`virtuemart_custom_id` , `custom_parent_id` , `admin_only` , `custom_title` , `custom_tip` , C.`custom_value` AS value, `custom_field_desc` , `field_type` , `is_list` , `is_cart_attribute` , `is_hidden` , C.`published` , field.`virtuemart_customfield_id` , field.`custom_value`,field.`custom_price`
			FROM `#__virtuemart_customs` AS C
			LEFT JOIN `#__virtuemart_customfields` AS field ON C.`virtuemart_custom_id` = field.`virtuemart_custom_id`
			LEFT JOIN `#__virtuemart_product_customfields` AS xref ON xref.`virtuemart_customfield_id` = field.`virtuemart_customfield_id`
			Where xref.`virtuemart_product_id` ='.$virtuemart_product_id;
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
		$virtuemart_product_id = JRequest::getInt('virtuemart_product_id', false);
		if (empty ($productcustomFields)) {
			 if ($this->hasproductCustoms($virtuemart_product_id )) {
				if(!class_exists(' VirtueMartModelCustom')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'custom.php');
				$productcustomFields = VirtueMartModelCustom::getProductCustoms($virtuemart_product_id);
			return $productcustomFields++ ;
			}
		} else return $productcustomFields;
     }

	/* look if whe have a product type */
	private function hasproductCustoms($virtuemart_product_id) {
		$this->_db = JFactory::getDBO();
		$q = "SELECT COUNT(`virtuemart_product_id`) FROM `#__virtuemart_product_customfields` WHERE `virtuemart_product_id` = ".$virtuemart_product_id;
		$this->_db->setQuery($q);
		return ($this->_db->loadResult() > 0);
	}


	// **************************************************
	// Custom FIElDS
	//

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
					return $value.'<input type="hidden" value="'.$value.'" name="field['.$row.'][custom_value]" />'.$priceInput;
				break;
				/* image */
				case 'i':
					if (empty($product)){
						$vendorId=1;
					} else {
						$vendorId = $product->virtuemart_vendor_id;
					}
					$q='SELECT `virtuemart_media_id` as value,`file_title` as text FROM `#__virtuemart_medias` WHERE `published`=1
					AND (`virtuemart_vendor_id`= "'.$vendorId.'" OR `shared` = "1")';
					$this->_db->setQuery($q);
					$options = $this->_db->loadObjectList();
					return JHTML::_('select.genericlist', $options,'field['.$row.'][custom_value]','','value' ,'text',$value).$priceInput;
				break;
				/* Child product */
				case 'C':
					if (empty($product)){
						$virtuemart_product_id = JRequest::getInt('virtuemart_product_id', 0);
					} else {
						$virtuemart_product_id = $product->virtuemart_product_id;
					}
					$q='SELECT `virtuemart_product_id` as value,concat(`product_sku`,":",`product_name`) as text FROM `#__virtuemart_products` WHERE `published`=1
					AND `product_parent_id`= "'.$virtuemart_product_id.'"';
					$this->_db->setQuery($q);
					if ($options = $this->_db->loadObjectList() ) return JHTML::_('select.genericlist', $options,'field['.$row.'][custom_value]','','value' ,'text',$value);
					else return JText::_('COM_VIRTUEMART_CUSTOM_NO_CHILD_PRODUCT');
				break;
			}

		}
	}
     public function getProductCustomsField($product) {

		if ($product->hasproductCustoms) {

		$query='SELECT C.`virtuemart_custom_id` , `custom_parent_id` , `admin_only` , `custom_title` , `custom_tip` , C.`custom_value` AS value, `custom_field_desc` , `field_type` , `is_list` , `is_hidden` , C.`published` , field.`virtuemart_customfield_id` , field.`custom_value`, field.`custom_price`
			FROM `#__virtuemart_customs` AS C
			LEFT JOIN `#__virtuemart_customfields` AS field ON C.`virtuemart_custom_id` = field.`virtuemart_custom_id`
			LEFT JOIN `#__virtuemart_product_customfields` AS xref ON xref.`virtuemart_customfield_id` = field.`virtuemart_customfield_id`
			Where xref.`virtuemart_product_id` ='.$product->virtuemart_product_id;
		$query .=' and is_cart_attribute = 0 order by virtuemart_custom_id' ;
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
     public function getProductCustomsFieldCart($product) {

		if ($product->hasproductCustoms)  {

			// group by virtuemart_custom_id
			$query='SELECT C.`virtuemart_custom_id`, `custom_title`, C.`custom_value`,`custom_field_desc` ,`custom_tip`,`field_type`,field.`virtuemart_customfield_id`,`is_hidden`
				FROM `#__virtuemart_customs` AS C
				LEFT JOIN `#__virtuemart_customfields` AS field ON C.`virtuemart_custom_id` = field.`virtuemart_custom_id`
				LEFT JOIN `#__virtuemart_product_customfields` AS xref ON xref.`virtuemart_customfield_id` = field.`virtuemart_customfield_id`
				Where xref.`virtuemart_product_id` ='.$product->virtuemart_product_id;
			$query .=' and is_cart_attribute = 1 group by virtuemart_custom_id' ;

			$this->_db->setQuery($query);
			$groups = $this->_db->loadObjectList();

			if (!class_exists('VmHTML')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'html.php');
			$row= 0 ;
			if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');
			$calculator = calculationHelper::getInstance();

			if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'vendor');
//			$vendor_currency = VirtueMartModelVendor::getVendorCurrency($product->virtuemart_vendor_id)->currency_code;
			// render select list
			foreach ($groups as & $group) {

//				$query='SELECT  field.`virtuemart_customfield_id` as value ,concat(field.`custom_value`," :bu ", field.`custom_price`) AS text
				$query='SELECT  field.`virtuemart_customfield_id` as value ,field.`custom_value`, field.`custom_price`
					FROM `#__virtuemart_customs` AS C
					LEFT JOIN `#__virtuemart_customfields` AS field ON C.`virtuemart_custom_id` = field.`virtuemart_custom_id`
					LEFT JOIN `#__virtuemart_product_customfields` AS xref ON xref.`virtuemart_customfield_id` = field.`virtuemart_customfield_id`
					Where xref.`virtuemart_product_id` ='.$product->virtuemart_product_id;
				$query .=' and is_cart_attribute = 1 and C.`virtuemart_custom_id`='.$group->virtuemart_custom_id ;
				$this->_db->setQuery($query);
				$options = $this->_db->loadObjectList();
				$group->options = array();
				foreach ( $options as $option) $group->options[$option->value] = $option;

				foreach ($group->options as $productCustom) {
					$productCustom->custom_price = $calculator->priceDisplay($productCustom->custom_price,'',true);
				}
				if ($group->field_type == 'V'){
					foreach ($group->options as $productCustom) {
						$productCustom->text =  $productCustom->custom_value.' : '.$productCustom->custom_price;
					}
					$group->display = VmHTML::select($group->options,'customPrice['.$row.']['.$group->virtuemart_custom_id.']',$group->custom_value,'','value','text',false);
				} else if ($group->field_type == 'U'){
					foreach ($group->options as $productCustom) {
						$productCustom->text =  $productCustom->custom_value.' : '.$productCustom->custom_price;
					}
						$group->display .= '<label for="'.$productCustom->value.'">'.$this->displayType($product,$productCustom->custom_value,$group->field_type,0,'',$row).': '.$productCustom->custom_price.'</label>' ;
				} else {
					$group->display ='';
					foreach ($group->options as $productCustom) {
						$group->display .= '<input id="'.$productCustom->value.'" type="radio" value="'.$productCustom->value.'" name="customPrice['.$row.']['.$group->virtuemart_custom_id.']" /><label for="'.$productCustom->value.'">'.$this->displayType($product,$productCustom->custom_value,$group->field_type,0,'',$row).': '.$productCustom->custom_price.'</label>' ;
					}
				}
				$row++ ;
			}
				return $groups;

		}
		return ;
     }
	/*
	* GIve Product virtuemart_customfield_id pricable
	**/
	public function getProductcustomfieldsIds($product) {
			$query='SELECT field.`virtuemart_customfield_id` FROM `#__virtuemart_customs` AS C
				LEFT JOIN `#__virtuemart_customfields` AS field ON C.`virtuemart_custom_id` = field.`virtuemart_custom_id`
				LEFT JOIN `#__virtuemart_product_customfields` AS xref ON xref.`virtuemart_customfield_id` = field.`virtuemart_customfield_id`
				Where is_cart_attribute = 1 and xref.`virtuemart_product_id` ='.$product->virtuemart_product_id;
		$this->_db->setQuery($query);
		return ($this->_db->loadResult() > 0);

	}
	/*
	* Product
	*Get fields with price
	* from custom fields
	**/
     public function getProductCustomsFieldWithPrice($product) {

		if ($this->hasproductCustoms($product->virtuemart_product_id )) {

			// group by virtuemart_custom_id
			$query='SELECT C.`virtuemart_custom_id`, `custom_title`, C.`custom_value`,`custom_field_desc` ,`custom_tip`,`field_type`,field.`virtuemart_customfield_id`,`is_hidden`
				FROM `#__virtuemart_customs` AS C
				LEFT JOIN `#__virtuemart_customfields` AS field ON C.`virtuemart_custom_id` = field.`virtuemart_custom_id`
				LEFT JOIN `#__virtuemart_product_customfields` AS xref ON xref.`virtuemart_customfield_id` = field.`virtuemart_customfield_id`
				Where xref.`virtuemart_product_id` ='.$product->virtuemart_product_id;
			$query .=' and is_cart_attribute = 1 group by virtuemart_custom_id' ;

			$this->_db->setQuery($query);
			$groups = $this->_db->loadAssocList();

			//product custom_field  with price grouped by virtuemart_custom_id
			foreach ($groups as & $group) {
				$query='SELECT  field.`virtuemart_customfield_id` ,field.`custom_value`,field.`custom_price`
					FROM `#__virtuemart_customs` AS C
					LEFT JOIN `#__virtuemart_customfields` AS field ON C.`virtuemart_custom_id` = field.`virtuemart_custom_id`
					LEFT JOIN `#__virtuemart_product_customfields` AS xref ON xref.`virtuemart_customfield_id` = field.`virtuemart_customfield_id`
					Where xref.`virtuemart_product_id` ='.$product->virtuemart_product_id;
				$query .=' and is_cart_attribute = 1 and C.`virtuemart_custom_id`='.$group['virtuemart_custom_id'] ;
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
				return '<input type="text" value="'.$value.'" name="field['.$row.'][custom_value]" /> '.JText::_('COM_VIRTUEMART_CART_PRICE').' : '.$calculator->priceDisplay($price,$calculator->vendor_currency,true).' ';
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
					if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'vendor');

					$q='SELECT * FROM `#__virtuemart_medias` WHERE `published`=1
					AND (`virtuemart_vendor_id`= "'.$product->virtuemart_vendor_id.'" OR `shared` = "1") AND virtuemart_media_id='.(int)$value;
					$db =& JFactory::getDBO();
					$db->setQuery($q);
					$image = $db->loadObject();

					//if(!class_exists('VirtueMartModelMedia')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'media.php');
					if (!class_exists('VmMediaHandler')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'mediahandler.php');
					$this->virtuemart_media_id = (int)$value;
					$imagehandler = VmMediaHandler::createMedia($image);
					//$imagehandler->createMedia($image);
					return $imagehandler->displayMediaThumb();
				break;
				/* Child product */
				case 'C':
					$q='SELECT p.`virtuemart_product_id` , p.`product_name`, x.`virtuemart_category_id` FROM `#__virtuemart_products` as p
					LEFT JOIN `#__virtuemart_product_categories` as x on x.`virtuemart_product_id` = p.`virtuemart_product_id`
					WHERE `published`=1 AND p.`virtuemart_product_id`= "'.$value.'" ';
					$this->_db->setQuery($q);
					if ($result = $this->_db->loadObject() ) return  JHTML::link ( JRoute::_ ( 'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $result->virtuemart_product_id . '&virtuemart_category_id=' . $result->virtuemart_category_id ), $result->product_name, array ('title' => $result->product_name ) );
					else return JText::_('COM_VIRTUEMART_CUSTOM_NO_CHILD_PRODUCT');
				break;
			}
		}
	}

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

		$this->mediaModel->attachImages($products,'product','image');

	}
}
// No closing tag