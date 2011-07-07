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

if(!class_exists('VmModel'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmmodel.php');

 JTable::addIncludePath(JPATH_VM_ADMINISTRATOR.DS.'tables');
/**
 * Model for VirtueMart Products
 *
 * @package VirtueMart
 * @author RolandD
 * @todo Replace getOrderUp and getOrderDown with JTable move function. This requires the vm_product_category_xref table to replace the ordering with the ordering column
 */
class VirtueMartModelProduct extends VmModel {

	/**
	 * products object
	 * @var integer
	 */
	var $products  = null ;

	/**
	 * constructs a VmModel
	 * setMainTable defines the maintable of the model
	 * @author Max Milbers
	 */
	function __construct() {
		parent::__construct('virtuemart_product_id');
		$this->setMainTable('products');
	}

	/**
	 * New function for sorting, searching, filtering and pagination for product ids.
	 *
	 * @author Max Milbers
	 */
	function sortSearchListQuery($withCalc=true,$onlyPublished=true,$group=false,$nbrReturnProducts=false){

		$app = JFactory::getApplication() ;

		$option = 'com_virtuemart';
		$view = 'product';
		$default_order = 'product_name';
		$order_dir = '';
		$groupBy = '';

		//First setup the variables for filtering
		if($app->isSite()){
			$filter_order = JRequest::getWord('orderby', VmConfig::get('browse_orderby_field','virtuemart_product_id'));
			// sanitize $filter_order and dir
			$browse_orderby_fields = VmConfig::get('browse_orderby_fields') ;
			if (!is_array($browse_orderby_fields)) $browse_orderby_fields = array($browse_orderby_fields);
			if (!in_array($filter_order, $browse_orderby_fields)) {
				$filter_order = VmConfig::get('browse_orderby_field');
			}

			$filter_order_Dir 	= strtoupper(JRequest::getWord('order', 'ASC'));
		} else {
			$filter_order_Dir = strtoupper($app->getUserStateFromRequest( $option.'.'.$view.'.filter_order_Dir', 'filter_order_Dir', $order_dir, 'word' ));
			$filter_order     = $app->getUserStateFromRequest( $option.'.'.$view.'.filter_order', 'filter_order', $default_order, 'cmd' );
		}

		//sanitize Direction
		if($filter_order_Dir!='ASC' && $filter_order_Dir!='DESC'){
			$filter_order_Dir ='';
		}

		$search 				= JRequest::getWord('search', false );
		$virtuemart_category_id = JRequest::getInt('virtuemart_category_id', 0 );

		//administrative variables to organize the joining of tables
		$joinCategory 	= false ;
		$joinMf 		= false ;
		$joinPrice 		= false ;
		$joinCustom		= false ;

		$where = array();
		if($onlyPublished){
			$where[] = ' `#__virtuemart_products`.`published`="1" ';
		}

     	// Product name Backend?


		// search fields filters set Frontend?
		if ( $search == 'true') {
			$groupBy = 'group by `#__virtuemart_products`.`virtuemart_product_id`';
			//Why keyword and search used? why not only keyword or search? notice by Max Milbers
			//$keyword = trim( str_replace(' ', '%', JRequest::getWord('keyword', '') ) );
			$keyword = JRequest::getWord('keyword', '');
			$keyword = '"%' . $this->_db->getEscaped( $keyword, true ) . '%"' ;

			$searchFields = VmConfig::get('browse_search_fields');
			foreach ($searchFields as $searchField) {
				if (($searchField == 'category_name') || ($searchField == 'category_description')) $joinCategory = true ;
				if ($searchField == 'mf_name') $joinMf = true ;
				if ($searchField == 'product_price') $joinPrice = true ;

				$filter_search[] = ' `'.$searchField.'` LIKE '.$keyword;
			}
			$where[] = " ( ".implode(' OR ', $filter_search )." ) ";
			if ($searchcustoms = JRequest::getVar('customfields', array(),	'default' ,'array')){
				$joinCustom = true ;
				foreach ($searchcustoms as $key => $searchcustom) {
					$custom_search[] = '(`#__virtuemart_product_customfields`.`virtuemart_custom_id`="'.(int)$key.'" and `#__virtuemart_product_customfields`.`custom_value` like "%' . $this->_db->getEscaped( $searchcustom, true ) . '%")';
				}
			$where[] = " ( ".implode(' OR ', $custom_search )." ) ";
			}

		} elseif ($search = JRequest::getWord('filter_product', false)){
			$search = '"%' . $this->_db->getEscaped( $search, true ) . '%"' ;
			$where[] = '#__virtuemart_products.`product_name` LIKE '.$search;
     	}

		if ($virtuemart_category_id>0){
			$joinCategory = true ;
			$where[] = ' `#__virtuemart_product_categories`.`virtuemart_category_id` = '.$virtuemart_category_id;
		}
		$product_parent_id= JRequest::getInt('product_parent_id', false );
		if ($product_parent_id){
			$where[] = ' `#__virtuemart_products`.`product_parent_id` = '.$product_parent_id;
		}

		$virtuemart_manufacturer_id = JRequest::getInt('virtuemart_manufacturer_id', false );
		if ($virtuemart_manufacturer_id) {
			$joinMf = true ;
			$where[] = ' `#__virtuemart_product_manufacturers`.`virtuemart_manufacturer_id` = '.$virtuemart_manufacturer_id;
		}

		if ($app->isSite() && VmConfig::get('check_stock') && Vmconfig::get('show_out_of_stock_products') != '1'){
			$where[] = ' `product_in_stock` > 0 ';
		}

     	// Time filter
     	if (JRequest::getVar('search_type', '') != '') {
     		$search_order = $this->_db->getEscaped(JRequest::getVar('search_order') == 'bf' ? '<' : '>');
     		switch (JRequest::getVar('search_type')) {
     			case 'product':
     				$where[] = '#__virtuemart_products.`modified_on` '.$search_order.' "'.$this->_db->getEscaped(JRequest::getVar('search_date')).'"';
     				break;
     			case 'price':
					$joinPrice = true ;
     				$where[] = '#__virtuemart_product_prices.`modified_on` '.$search_order.' "'.$this->_db->getEscaped(JRequest::getVar('search_date')).'"';
     				break;
     			case 'withoutprice':
     				$joinPrice = true ;
     				$where[] = '#__virtuemart_product_prices.`product_price` IS NULL';
     				break;
     		}
     	}

		//Group case
		if($group){
			$groupBy = 'group by `#__virtuemart_products`.`virtuemart_product_id`';
		    switch ($group) {
				case 'featured':
					$where[] = '`#__virtuemart_products`.`product_special`="Y" ';
					break;
				case 'latest':
					$date = JFactory::getDate( time()-(60*60*24*7) ); //Set on a week, maybe make that configurable
					$dateSql = $date->toMySQL();
					$where[] = '`#__virtuemart_products`.`modified_on` > "'.$dateSql.'" ';
					break;
				case 'random':
					$orderBy = 'ORDER BY RAND() LIMIT 0, '.(int)$nbrReturnProducts ; //TODO set limit LIMIT 0, '.(int)$nbrReturnProducts;
					break;
				case 'topten';
					$orderBy = 'ORDER BY product_sales LIMIT 0, '.(int)$nbrReturnProducts;  //TODO set limitLIMIT 0, '.(int)$nbrReturnProducts;
					$filter_order_Dir = 'DESC';
			}
		}

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
				//$filters[] = '`#__virtuemart_products`.`virtuemart_product_id` = p.`virtuemart_product_id`';
				$orderBy = ' ORDER BY `product_price` ';
				$joinPrice = true ;
				break;
			default ;
				$orderBy = ' ORDER BY `#__virtuemart_products`.`'.$this->_db->getEscaped($filter_order).'` ';
				break;
		}

		//write the query, incldue the tables
		$query = 'SELECT * FROM `#__virtuemart_products` ';
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
		if ($joinCustom == true) {
			$query .= ' LEFT JOIN `#__virtuemart_product_customfields` ON `#__virtuemart_products`.`virtuemart_product_id` = `#__virtuemart_product_customfields`.`virtuemart_product_id` ';
		}
		if(count($where)>0){
			$whereString = ' WHERE ('.implode(' AND ', $where ).') ';
		} else {
			$whereString = '';
		}

		//and the where conditions
		$query .= $whereString .$groupBy .$orderBy .$filter_order_Dir ;

 		$this->_db->setQuery($query);
 		if(!$this->_db->query()){
			$app->enqueueMessage('sortSearchOrder Error in query '.$query.'<br /><br />'.$this->_db->getErrorMsg().'<br />');
		} else {

			if($nbrReturnProducts){
				$this->_db->setQuery($query, 0, $nbrReturnProducts);
			} else {
				$count = $this->_db->getNumRows();
				$this->getPagination($count);
				$this->_db->setQuery($query, $this->_pagination->limitstart, $this->_pagination->limit);
			}

	     	$productIdList = $this->_db->loadResultArray();

	     	//$app -> enqueueMessage('sortSearchListQuery '.$this->_db->getQuery());

			return $productIdList;
		}

		return $query ;
	}

    /**
     * This function creates a product with the attributes of the parent.
     *
     * @param int $virtuemart_product_id
     * @param boolean $front for frontend use
     * @param boolean $withCalc calculate prices?
     */
    public function getProduct($virtuemart_product_id = null,$front=true, $withCalc = true, $onlyPublished = true){

    	if (isset($virtuemart_product_id)) {
			$virtuemart_product_id = $this->setId($virtuemart_product_id);
		} else {

			return false;
		}

    	$child = $this->getProductSingle($virtuemart_product_id,$front, false,$onlyPublished);
		if(!$child->published && $onlyPublished) return false;
    	//store the original parent id
		$pId = $child->virtuemart_product_id;
    	$ppId = $child->product_parent_id;
		$published = $child->published;

		$i = 0;
		//Check for all attributes to inherited by parent products
    	while(!empty($child->product_parent_id)){

    		$parentProduct = $this->getProductSingle($child->product_parent_id,$front, false,false);
    	    $attribs = get_object_vars($parentProduct);

	    	foreach($attribs as $k=>$v){

				if(empty($child->$k)){
					$child->$k = $v;
				}
	    	}

			$child->product_parent_id = $parentProduct->product_parent_id;

    	}
		$child->published = $published;
		$child->virtuemart_product_id = $pId;
		$child->product_parent_id = $ppId;

		if ($withCalc) {
			$child->prices = $this->getPrice($child,array(),1);
		}

    	return $child;
    }

    public function getProductSingle($virtuemart_product_id = null,$front=true, $withCalc = true, $onlyPublished=true){

		//$this->fillVoidProduct($front);
       	if (!empty($virtuemart_product_id)) {
			$virtuemart_product_id = $this->setId($virtuemart_product_id);
		}

//		if(empty($this->_data)){
		if (!empty($this->_id)) {


   			$product = $this->getTable('products');
   			$product->load($this->_id);
			//$product = $this->fillVoidProduct($product,$front);
/*   			if($onlyPublished){
   				if(empty($product->published)){
   					return false;
   				}
   			}*/

   			$xrefTable = $this->getTable('product_medias');
			$product->virtuemart_media_id = $xrefTable->load((int)$this->_id);


//   		if(!$front){
    			$ppTable = $this->getTable('product_prices');
    			$q = 'SELECT `virtuemart_product_price_id` FROM `#__virtuemart_product_prices` WHERE `virtuemart_product_id` = "'.$this->_id.'" ';
				$this->_db->setQuery($q);
    			$ppId = $this->_db->loadResult();
   				$ppTable->load($ppId);
				$product = (object) array_merge((array) $ppTable, (array) $product);
//   		}

   			$q = 'SELECT `virtuemart_manufacturer_id` FROM `#__virtuemart_product_manufacturers` WHERE `virtuemart_product_id` = "'.$this->_id.'" ';
   			$this->_db->setQuery($q);
   			$mf_id = $this->_db->loadResult();

   			$mfTable = $this->getTable('manufacturers');
   			$mfTable->load((int)$mf_id);
   			$product = (object) array_merge((array) $mfTable, (array) $product);


			/* Load the categories the product is in */
			$product->categories = $this->getProductCategories($this->_id);

			//There is someone who can explain me this?
			$product->virtuemart_category_id = JRequest::getInt('virtuemart_category_id', 0);
/*			if  ($product->virtuemart_category_id >0) {
				$q = 'SELECT `ordering`,`id` FROM `#__virtuemart_product_categories`
					WHERE `virtuemart_product_id` = "'.$this->_id.'" and virtuemart_category_id='.$product->virtuemart_category_id;
				$this->_db->setQuery($q);
				// change for faster ordering
				$ordering = $this->_db->loadObject();
				$product->ordering = $ordering->ordering;
				$product->id = $ordering->id;
			}*/
			if (empty($product->virtuemart_category_id) && isset($product->categories[0])) $product->virtuemart_category_id = $product->categories[0];

   			if(!$front && !empty($product->categories[0])){
				$catTable = $this->getTable('categories');
   				$catTable->load($product->categories[0]);
				$product->category_name = $catTable->category_name;


   			} else {
   				$product->category_name ='';
   			}
   			$this->productHasCustoms($this->_id);

			if($front){

				// Add the product link  for canonical
				$producCategory = empty($product->categories[0])? '':$product->categories[0];
				$product->link = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$this->_id.'&virtuemart_category_id='.$producCategory);

				//only needed in FE productdetails, is now loaded in the view.html.php
//				/* Load the neighbours */
//				$product->neighbours = $this->getNeighborProducts($product);

				// Fix the product packaging
				if ($product->product_packaging) {
					$product->packaging = $product->product_packaging & 0xFFFF;
					$product->box = ($product->product_packaging >> 16) & 0xFFFF;
				}
				else {
					$product->packaging = '';
					$product->box = '';
				}

				// Load the vendor details
//				if(!class_exists('VirtueMartModelVendor')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'vendor.php');
//				$product->vendor_name = VirtueMartModelVendor::getVendorName($product->virtuemart_vendor_id);


				// set the custom variants
				if ($this->hasproductCustoms) {
					if(!class_exists('VirtueMartModelCustomfields'))require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'customfields.php');
					$customfields = new VirtueMartModelCustomfields();
					// Load the custom product fields
					$product->customfields = $customfields->getProductCustomsField($product);

					//  custom product fields for add to cart
					$product->customfieldsCart = $customfields->getProductCustomsFieldCart($product);
					if ($child = $this->getProductChilds($this->_id)) $product->customsChilds = $customfields->getProductCustomsChilds($child , $this->_id);
				}


				// Check the order levels
				if (empty($product->product_order_levels)) $product->product_order_levels = '0,0';

				// Check the stock level
				if (empty($product->product_in_stock)) $product->product_in_stock = 0;

				// Get stock indicator
//				$product->stock = $this->getStockIndicator($product);

				// TODO Get the votes
//				$product->votes = $this->getVotes($this->_id);

				}
				else {
					if ($this->hasproductCustoms) {
						if(!class_exists('VirtueMartModelCustomfields'))require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'customfields.php');
						$customfields = new VirtueMartModelCustomfields();
						$product->customfields = $customfields->getproductCustomslist($this->_id,'product');
					}
				}

			} else {
				$product = new stdClass();
				return $this->fillVoidProduct($front);
			}
//		}

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
    private function fillVoidProduct($front=true){

		/* Load an empty product */
	 	 $product = $this->getTable('products');
	 	 $product->load();

	 	 /* Add optional fields */
	 	 $product->virtuemart_manufacturer_id = null;
	 	 $product->virtuemart_product_price_id = null;

	 	 if(!class_exists('VirtueMartModelVendor')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'vendor.php');
	 	 //$product->virtuemart_vendor_id = VirtueMartModelVendor::getLoggedVendor();

	 	 $product->product_price = null;
	 	 $product->product_currency = null;
	 	 $product->product_price_quantity_start = null;
	 	 $product->product_price_quantity_end = null;
	 	 $product->product_tax_id = null;
	 	 $product->product_discount_id = null;
		 $product->product_override_price = null;
		 $product->override = 0;
		 $product->categories = array();

	 	 if($front){
	 	 	$product->link = '';

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

		$categories = array();
		if ($virtuemart_product_id > 0) {
			$q = 'SELECT `virtuemart_category_id` FROM `#__virtuemart_product_categories` WHERE `virtuemart_product_id` = "'.(int)$virtuemart_product_id.'"';
			$this->_db->setQuery($q);
			$categories = $this->_db->loadResultArray();
		}

		return $categories;
	}

	/**
	* Get the products in a given category
	*
	* @author RolandD
	* @access public
	* @param int $virtuemart_category_id the category ID where to get the products for
	* @return array containing product objects
	*/
	public function getProductsInCategory($categoryId) {

		$ids = $this->sortSearchListQuery();
		$this->products = $this->getProducts($ids);
		return $this->products;
	}

    /**
     * Loads different kind of product lists.
	 * you can load them with calculation or only published onces, very intersting is the loading of groups
	 * valid values are latest, topten, featured.
	 *
	 * The function checks itself by the config if the user is allowed to see the price or published products
	 *
	 * @author Max Milbers
     */
	public function getProductListing($group = false, $nbrReturnProducts = false, $withCalc = true, $onlyPublished = true, $single = false){

		$app = JFactory::getApplication();
		if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');
		if($app->isSite() ){
			$front = true;
			if(!Permissions::getInstance()->check('admin','storeadmin')){
				$onlyPublished = true;
				if ($show_prices=VmConfig::get('show_prices',1) == '0'){
					$withCalc = false;
				}
			}
		} else {
			$front = false;
		}

		$ids = $this->sortSearchListQuery($withCalc,$onlyPublished,$group,$nbrReturnProducts);

		$products = $this->getProducts($ids, $front, $withCalc, $onlyPublished,$single);
		return $products;
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
	public function getProducts($productIds, $front=true, $withCalc = true, $onlyPublished = true,$single=false){

		$products=array();
		if($single){
			foreach($productIds as $id){
				if($product = $this->getProductSingle((int)$id,$front, $withCalc, $onlyPublished)){
					if($onlyPublished && $product->published){
						$products[] = $product;
					}
					if(!$onlyPublished) $products[] = $product;
				}
			}
		} else {
			foreach($productIds as $id){
				if($product = $this->getProduct((int)$id,$front, $withCalc, $onlyPublished)){
					$products[] = $product;
				}
			}
		}

		return $products;
	}


	/**
	 * This function retrieves the "neighbor" products of a product specified by $virtuemart_product_id
	 * Neighbors are the previous and next product in the current list
	 *
	 * @author RolandD, Max Milbers
	 * @param object $product The product to find the neighours of
	 * @return array
	 */
	public function getNeighborProducts($product) {
		$this->_db = JFactory::getDBO();
		$neighbors = array('previous' => '','next' => '');

		$q = "SELECT x.`virtuemart_product_id`, ordering, `p`.product_name
			FROM `#__virtuemart_product_categories` x
			LEFT JOIN `#__virtuemart_products` `p`
			ON `p`.`virtuemart_product_id` = `x`.`virtuemart_product_id`
			WHERE `virtuemart_category_id` = ".(int)$product->virtuemart_category_id."
			ORDER BY `ordering`, `x`.`virtuemart_product_id`";
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


    /**
    * Check if the product has any children
    *
    * @author RolandD
    * @author MaxMilbers
    * @param int $virtuemart_product_id Product ID
    * @return bool True if there are child products, false if there are no child products
    */
    public function checkChildProducts($virtuemart_product_id) {

     	$q  = 'SELECT IF(COUNT(virtuemart_product_id) > 0, "0", "1") FROM `#__virtuemart_products` WHERE `product_parent_id` = "'.(int)$virtuemart_product_id.'"';
     	$this->_db->setQuery($q);

     	return $this->_db->loadResult();
//    	if ($this->_db->loadResult() == 'Y') return true;
//     	else if ($this->_db->loadResult() == 'N') return false;
    }



	/* reorder product in one category */
	 function saveorder($cid , $orders) {

		JRequest::checkToken() or jexit( 'Invalid Token' );
		global $mainframe;

		$virtuemart_category_id = JRequest::getInt('virtuemart_category_id', 0);

		$q = 'SELECT `id`,`ordering` FROM `#__virtuemart_product_categories`
			WHERE virtuemart_category_id='.(int)$virtuemart_category_id.'
			ORDER BY `ordering` ASC';
		$this->_db->setQuery($q);
		$pkey_orders = $this->_db->loadObjectList();

		$tableOrdering = array();
		foreach ($pkey_orders as $order) $tableOrdering[$order->id] = $order->ordering;
		// set and save new ordering
		foreach  ($orders as $key => $order) $tableOrdering[$key] = $order;
		asort($tableOrdering);
		$i = 1 ; $ordered = 0 ;
		foreach  ($tableOrdering as $key => $order) {
			if ($order != $i) {
				$this->_db->setQuery('UPDATE `#__virtuemart_product_categories`
					SET `ordering` = '. $i.'
					WHERE `id` = ' . (int)$key . ' ');
				if (! $this->_db->query()){
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
				$ordered ++ ;
			}
			$i++ ;
		}
		if ($ordered) $msg = JText::sprintf('COM_VIRTUEMART_ITEMS_MOVED', $ordered);
		else $msg = JText::_('COM_VIRTUEMART_ITEMS_NOT_MOVED');
		$mainframe->redirect('index.php?option=com_virtuemart&view=product&virtuemart_category_id='.$virtuemart_category_id, $msg);

	}

	/**
	* Moves the order of a record
	* @param integer The increment to reorder by
	*/
	function move($direction) {

		JRequest::checkToken() or jexit( 'Invalid Token' );
		global $mainframe;
		// Check for request forgeries
		$table = $this->getTable('product_categories');
		$table->move($direction);

		$mainframe->redirect('index.php?option=com_virtuemart&view=product&virtuemart_category_id='.JRequest::getInt('virtuemart_category_id', 0));
	}

	/**
	* Store a product
	*
	* @author RolandD
	* @author Max Milbers
	* @access public
	*/
	public function store($product=false) {

		/* Load the data */
		if($product){
			$data = (array)$product;
		} else{
			$data = JRequest::get('post');
		}

		/* Setup some place holders */
		$product_data = $this->getTable('products');

		/* Load the old product details first */
		$product_data->load((int)$data['virtuemart_product_id']);


        /* Set the product packaging */
        $data['product_packaging'] = (($data['product_box'] << 16) | ($data['product_packaging']&0xFFFF));
        /* Set the order levels */
        $data['product_order_levels'] = $data['min_order_level'].','.$data['max_order_level'];

        $product_data->bindChecknStore($data);

		$errors = $product_data->getErrors();
		foreach($errors as $error){
			$this->setError($error);
		}

		if(empty($data['virtuemart_product_id'])){
			$dbv = $product_data->getDBO();
			//I dont like the solution to use three variables
			$this->_id = $data['virtuemart_product_id'] = $product_data->virtuemart_product_id ;// = $dbv->insertid();
		}

		if (array_key_exists('field', $data)) {
			if(!class_exists('VirtueMartModelCustom')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'custom.php');
			VirtueMartModelCustom::saveModelCustomfields('product',$data['field'],$product_data->virtuemart_product_id);
		}
		if (array_key_exists('ChildCustomRelation', $data)) {
			if(!class_exists('VirtueMartModelCustom')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'custom.php');
			VirtueMartModelCustom::saveChildCustomRelation('product',$data['ChildCustomRelation'],$product_data->virtuemart_product_id);
		}

		$product_price_table = $this->getTable('product_prices');

		$product_price_table->bindChecknStore($data);

		$errors = $product_price_table->getErrors();
		foreach($errors as $error){
			$this->setError($error);
		}

		/* Update manufacturer link */
		if(!empty($data['virtuemart_manufacturer_id'])){

			$xrefTable = $this->getTable('product_manufacturers');
	    	if (!$xrefTable->bindChecknStore($data)) {
				$this->setError($xrefTable->getError());
			}
		}

		/* Update waiting list  */
		if(!empty($data['notify_users'])){
			if ($data['product_in_stock'] > 0 && $data['notify_users'] == '1' ) {
				$waitinglist = new VirtueMartModelWaitingList();
				$waitinglist->notifyList($data['virtuemart_product_id']);
			}
		}

		//Should be replaced by xref table
		if(!empty($data['categories']) && count($data['categories'])>0){
			/* Delete old category links */
			$q  = "DELETE FROM `#__virtuemart_product_categories` ";
			$q .= "WHERE `virtuemart_product_id` = '".(int)$product_data->virtuemart_product_id."' ";
			$this->_db->setQuery($q);
			$this->_db->Query();
			if(!is_array($data['categories'])) $data['categories'] = array($data['categories']);

			/* Store the new categories */
			foreach( $data["categories"] as $virtuemart_category_id ) {
				$this->_db->setQuery('SELECT IF(ISNULL(`ordering`), 1, MAX(`ordering`) + 1) as ordering FROM `#__virtuemart_product_categories` WHERE `virtuemart_category_id`='.$virtuemart_category_id );
				$list_order = $this->_db->loadResult();

				$q  = "INSERT INTO #__virtuemart_product_categories ";
				$q .= "(virtuemart_category_id,virtuemart_product_id,ordering) ";
				$q .= "VALUES ('".(int)$virtuemart_category_id."','".(int) $product_data->virtuemart_product_id . "', ".(int)$list_order. ")";
				$this->_db->setQuery($q);
				$this->_db->query();
			}
		}

		// Process the images
		if(!class_exists('VirtueMartModelMedia')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'media.php');
		$mediaModel = new VirtueMartModelMedia();
		$mediaModel->storeMedia($data,'product');
	    $errors = $mediaModel->getErrors();
		foreach($errors as $error){
			$this->setError($error);
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
		return $product_data->virtuemart_product_id;
	}

	/**
	 * This function creates a child for a given product id
	 * @author Max Milbers
	 * @param int id of parent id
	 */
	public function createChild($id){
		// created_on , modified_on
		$db = JFactory::getDBO();
		$vendorId = 1;
		//$db->setQuery('SELECT max( `virtuemart_product_id` ) FROM `#__virtuemart_product_categories`' );
	//	$slug_id = 1+$db->loadResult();
		$db->setQuery('SELECT `product_name`,`slug` FROM `#__virtuemart_products` WHERE `virtuemart_product_id`='.(int)$id );
		$parent = $db->loadObject();
		$q = 'INSERT INTO `#__virtuemart_products` ( `product_name`,`slug` ,`virtuemart_vendor_id`, `product_parent_id`) VALUES ( "'.$parent->product_name.'","P-'.$parent->slug.'", '.(int)$vendorId.', '.(int)$id.' )';
		$db->setQuery($q);
		$db->query();
		return $db->insertid();
	}

	/**
	 * Creates a clone of a given product id
	 *
	 * @author Max Milbers
	 * @param int $virtuemart_product_id
	 */

	public function createClone($id){
	//	if (is_array($cids)) $cids = array($cids);
		$product = $this->getProduct($id);
		$product->virtuemart_product_id = $product->virtuemart_product_price_id = 0;
		$product->slug = $product->slug.'-'.$id;

		$this->store($product);
		return $this->_id;
	}


	/**
	 * removes a product and related table entries
	 *
	 * @author Max Milberes
	 */
	public function remove($ids) {

		$table = $this->getTable($this->_maintablename);

		$cats = $this->getTable('product_categories');
		$customs = $this->getTable('product_customfields');
		$manufacturers = $this->getTable('product_manufacturers');
		$medias = $this->getTable('product_medias');
		$prices = $this->getTable('product_prices');
		$rating = $this->getTable('ratings');
		$review = $this->getTable('rating_reviews');

		$ok = true;
		foreach($ids as $id) {

			if(!$this->checkChildProducts($id)){
				$this->setError(JText::_('COM_VIRTUEMART_PRODUCT_CANT_DELETE_CHILD'));
				$ok = false;
				continue;
			}

		    if (!$table->delete($id)) {
				$this->setError($table->getError());
				$ok = false;
		    }

			if (!$cats->delete($id)) {
				$this->setError($cats->getError());
				$ok = false;
		    }

			if (!$customs->delete($id)) {
				$this->setError($customs->getError());
				$ok = false;
		    }

		    if (!$manufacturers->delete($id)) {
				$this->setError($manufacturers->getError());
				$ok = false;
		    }

			if (!$medias->delete($id)) {
				$this->setError($medias->getError());
				$ok = false;
		    }

		 	if (!$prices->delete($id)) {
				$this->setError($prices->getError());
				$ok = false;
		    }

			if (!$rating->delete($id)) {
				$this->setError($rating->getError());
				$ok = false;
		    }

			if (!$review->delete($id)) {
				$this->setError($review->getError());
				$ok = false;
		    }
		}

		return $ok;
	}

	/**
	* Remove a product
	* @author RolandD
	* @todo Add sanity checks, so long made private
	*/
	private function removeProduct($old_virtuemart_product_id=false) {
//		$this->_db = JFactory::getDBO();

		/* Get the product IDs to remove */
		$cids = array();
// 		if (!$old_virtuemart_product_id) {
			//$cids = JRequest::getVar('cid');
			//if (!is_array($cids)) $cids = array($cids);
// 		}
// 		else $cids[] = $old_virtuemart_product_id;
		$cids[] = $old_virtuemart_product_id;

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
			$q  = "DELETE FROM #__virtuemart_rating_reviews WHERE virtuemart_product_id = ".$virtuemart_product_id;
			$this->_db->setQuery($q);
			$this->_db->query();

			/* Delete product reviews */
			$q = "DELETE FROM #__virtuemart_ratings WHERE virtuemart_product_id = ".$virtuemart_product_id;
			$this->_db->setQuery($q);
			$this->_db->query();

			/* Delete Product Relations */
			$q  = "DELETE FROM #__virtuemart_product_relations WHERE virtuemart_product_id = ".$virtuemart_product_id;
			$this->_db->setQuery($q); $this->_db->query();

			/* find and remove Product Types */
			$q = "SELECT virtuemart_producttype_id FROM #__virtuemart_product_producttypes WHERE virtuemart_product_id = ".$virtuemart_product_id;
			$this->_db->setQuery($q);
			/* TODO the product is not removed from this tables !!*/
			$virtuemart_producttype_ids = $this->_db->loadResultArray();
			foreach ($virtuemart_producttype_ids as $virtuemart_producttype_id)
			$q  = "DELETE FROM #__virtuemart_producttypes_".$virtuemart_producttype_id." WHERE virtuemart_product_id = ".$virtuemart_product_id;
			$this->_db->setQuery($q); $this->_db->query();

			/* Delete Product Types xref */
			$q  = "DELETE FROM #__virtuemart_product_producttypes WHERE virtuemart_product_id = ".$virtuemart_product_id;
			$this->_db->setQuery($q); $this->_db->query();

			/* remove Product custom fields */
			$q = "DELETE `#__virtuemart_product_customfields` FROM  `#__virtuemart_product_customfields`
				WHERE `#__virtuemart_product_customfields`.`virtuemart_product_id` =".$virtuemart_product_id;
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

	/**
	 * Gets the price for a variant
	 *
	 * @author Max Milbers
	 */
	public function getPrice($product,$customVariant,$quantity){

		$this->_db = JFactory::getDBO();

		if(!is_object($product)){
			$product = $this->getProduct($product,true,false,false);
		}

		// Loads the product price details
		if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');
		$calculator = calculationHelper::getInstance();

		// Calculate the modificator
		$variantPriceModification = $calculator->calculateModificators($product,$customVariant);

		$prices = $calculator->getProductPrices($product,$product->categories,$variantPriceModification,$quantity);

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

		$q = 'SELECT `comment`, `created_on`, `virtuemart_user_id`, `user_rating`, `username`, `name`
			FROM `#__virtuemart_rating_reviews` `r`
			LEFT JOIN `#__users` `u`
			ON `u`.`id` = `r`.`virtuemart_user_id`
			WHERE `virtuemart_product_id` = "'.(int)$virtuemart_product_id.'"
			AND published = "1"
			ORDER BY `created_on` DESC ';
		if (!$showall) $q .= ' LIMIT 0, 5';
		$this->_db->setQuery($q);
		$array = $this->_db->loadObjectList();
		if(empty($array)) $array = array();
		return $array;
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
	$search = JRequest::getWord('search', '' );
	if ($search != '' ) $fieldLink .= '&search=true&keyword='.JRequest::getWord('keyword', '' );


	/* Collect the product IDS for manufacturer list */
/*	$db = JFactory::getDBO();
	if (empty($this->_query)) $this->_query = $this->_buildQuery();
	$db->setQuery($this->_query);
	$mf_virtuemart_product_ids = $db->loadResultArray();*/

	$mf_virtuemart_product_ids = $this->sortSearchListQuery();

	//$mf_virtuemart_product_ids = array();
	//foreach ($virtuemart_product_ids as $virtuemart_product_id) $mf_virtuemart_product_ids[] = $virtuemart_product_id->virtuemart_product_id ;

	/* manufacturer link list*/
	$manufacturerTxt ='';
	$virtuemart_manufacturer_id = JRequest::getInt('virtuemart_manufacturer_id',0);
	if ($virtuemart_manufacturer_id != '' ) $manufacturerTxt ='&virtuemart_manufacturer_id='.$virtuemart_manufacturer_id;
	if ($mf_virtuemart_product_ids) {
		$query = 'SELECT DISTINCT `#__virtuemart_manufacturers`.`mf_name`,`#__virtuemart_manufacturers`.`virtuemart_manufacturer_id` FROM `#__virtuemart_manufacturers`';
		$query .= ' LEFT JOIN `#__virtuemart_product_manufacturers` ON `#__virtuemart_manufacturers`.`virtuemart_manufacturer_id` = `#__virtuemart_product_manufacturers`.`virtuemart_manufacturer_id` ';
		$query .= ' WHERE `#__virtuemart_product_manufacturers`.`virtuemart_product_id` in ('.implode (',', $mf_virtuemart_product_ids ).') ';
		$query .= ' ORDER BY `#__virtuemart_manufacturers`.`mf_name`';
		$this->_db->setQuery($query);
		$manufacturers = $this->_db->loadObjectList();

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
				$text = JText::_('COM_VIRTUEMART_'.strtoupper($field)) ;
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

	$orderByList ='<div class="orderlistcontainer"><div class="title">'.JText::_('COM_VIRTUEMART_ORDERBY').'</div><div class="activeOrder"><a title="'.$orderTxt.'" href="'.$link.'">'.JText::_('COM_VIRTUEMART_SEARCH_ORDER_'.$orderby).' '.$orderTxt.'</a></div>';
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
				FROM `#__virtuemart_ratings`
				WHERE `virtuemart_product_id` = ".(int)$virtuemart_product_id;
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
		//sanitize fields
		$_id = (int) $_id;
		$_amount = (float) $_amount;

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
	public function increaseStockAfterCancel ($_id, $_amount){

		//sanitize fields
		$_id = (int) $_id;
		$_amount = (float) $_amount;

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
	 * @author Max Milbers
	 * @param $_id integer Product ID
	 * @param $_amount integer Original amount sold
	 * @access public
	 */
	public function revertStockAfterCancellation ($_id, $_amount){

		//sanitize fields
		$_id = (int) $_id;
		$_amount = (float) $_amount;

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
	 * @author Max Milbers
	 * @param $_id integer Product ID
	 * @param $_amount integer Amount sold
	 * @param $_sign char '+' for increase, '-' for decrease
	 * @access private
	 */
	private function _updateStock($_id, $_amount, $_sign){

		//sanitize fields
		$_id = (int) $_id;
		$_amount = (float) $_amount;

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


 	/* look if whe have a product type */
	private function productHasCustoms($virtuemart_product_id) {
		if (isset($this->hasproductCustoms)) return $this->hasproductCustoms;
		$this->_db = JFactory::getDBO();
		$q = "SELECT `virtuemart_product_id` FROM `#__virtuemart_product_customfields` WHERE `virtuemart_product_id` = ".$virtuemart_product_id." limit 0,1";
		$this->_db->setQuery($q);
		$this->hasproductCustoms = $this->_db->loadResult();
		return $this->hasproductCustoms;
	}


	function getProductChilds($product_id ) {

		$db = JFactory::getDBO();
		$db->setQuery(' SELECT virtuemart_product_id, product_name FROM `#__virtuemart_products` WHERE `product_parent_id` ='.(int)$product_id);
		return $db->loadObjectList();

	}


	function getProductParent($product_parent_id) {

		$product_parent_id = (int) $product_parent_id;
		$db = JFactory::getDBO();
		$db->setQuery(' SELECT * FROM `#__virtuemart_products` WHERE `virtuemart_product_id` ='.$product_parent_id);
                return $db->loadObject();
	}

}
// No closing tag