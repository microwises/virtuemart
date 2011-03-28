<?php
/**
*
 * Default data model for Product details
 *
 * @package     VirtueMart
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
	  * products object
	  * @var integer
	  */
	var $products  = array();

	/**
	  * Items total
	  * @var integer
	  */
	var $_total = null;

	/**
	  * Pagination object
	  * @var object
	  */
	var $_pagination = null;

	/**
	  * product category search query 
	  * var to prevent to reload 2 time same query
	  * @var 
	  */
	var $_query = null;

	function __construct()
	{
		parent::__construct();
	
		$mainframe = Jfactory::getApplication();

		// Get pagination request variables
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = JRequest::getVar('limitstart', 0, '', 'int');
		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
		
	}


	/**
	* Load the product details
	*/
	public function getProduct($product_id=false,$withCalc =true) {
		$this->_db = JFactory::getDBO();
		if (!$product_id) $product_id = JRequest::getInt('product_id', 0);

		if ($product_id > 0) {
			$q = "SELECT `p`.*, x.`category_id`, x.product_list, m.manufacturer_id, m.mf_name
				FROM `#__vm_product` p
				LEFT JOIN `#__vm_product_category_xref` x
				ON x.`product_id` = `p`.`product_id`
				LEFT JOIN `#__vm_product_mf_xref` mx
				ON mx.`product_id` = `p`.`product_id`
				LEFT JOIN `#__vm_manufacturer` `m`
				ON `m`.`manufacturer_id` = `mx`.`manufacturer_id`
				WHERE `p`.`product_id` = ".$product_id;
			$this->_db->setQuery($q);
			$product = $this->_db->loadObject();
			if(empty($product)) return false;

			/* Load the categories the product is in */
			$product->categories = $this->getCategories();

			if (empty($product->category) && isset($product->categories[0])) $product->category_id = $product->categories[0];

			/* Load the attributes */
			$product->attributes = $this->getAttributes($product);

			/* Load the variants */
			$product->variants = $this->getVariants($product);

			/* Load the price */
			$prices = "";
			if (VmConfig::get('show_prices') == '1' && $withCalc) {

				/* Loads the product price details */
				if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');
				$calculator = calculationHelper::getInstance();

				/* Calculate the modificator */
				$product_type_modificator = $calculator->calculateModificators($product->product_id,$product->variants);
//				$product_type_modificator = $calculator->parseModifier($product->variants);
				$quantityArray = JRequest::getVar('quantity',1,'post');
//				$product->product_id.$variant_name
				$prices = $calculator->getProductPrices((int)$product->product_id,$product->categories,$product_type_modificator,$quantityArray[0]);
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

			/* Load the extra files */
			$product->files = $this->getFileList($product_id);

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
			$product->customvariants = $this->getCustomVariants($product->custom_attribute);


			/* Check the order levels */
			if (empty($product->product_order_levels)) $product->product_order_levels = '0,0';

			/* Check the stock level */
			if (empty($product->product_in_stock)) $product->product_in_stock = 0;

			/* Handle some child product data */
			if ($product->product_parent_id > 0) {
				/* Get the attributes */
				// $product->attributes = $this->getAttributes($product);
			}

			/* Get stock indicator */
			$product->stock = $this->getStockIndicator($product);

			/* Get the votes */
			$product->votes = $this->getVotes($product_id);

			return $product;
		} else{
			 return false;
		}
	}

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
		$product->categories = $this->getCategories();

		if (empty($product->category) && isset($product->categories[0])) $product->category_id = $product->categories[0];

		/* Load the attributes */
		$product->attributes = $this->getAttributes($product);

		/* Load the variants */
		$product->variants = $this->getVariants($product);

		/* Loads the product price details */
		if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');
		$calculator = calculationHelper::getInstance();

		$quantityArray = JRequest::getVar('quantity',1,'post');

		/* Calculate the modificator */
		$product_type_modificator = $calculator->calculateModificators($product->product_id,$product->variants);
//		$product_type_modificator = $calculator->parseModifier($product->variants);

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
	* Load the categories the product is in
	*
	* @author RolandD
	* @return array list of categories product is in
	*/
	private function getCategories() {
		$this->_db = JFactory::getDBO();
		$product_id = JRequest::getInt('product_id', 0);
		$categories = array();

		if ($product_id > 0) {
			$q = "SELECT `category_id` FROM `#__vm_product_category_xref` WHERE `product_id` = ".$product_id;
			$this->_db->setQuery($q);
			$categories = $this->_db->loadResultArray();
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
		$this->_db = JFactory::getDBO();
		$html = "";
		$q = "SELECT attribute_value
			FROM `#__vm_product_attribute`
			WHERE `product_id` = ".$product_id."
			AND `attribute_name`='download'";
		$this->_db->query($q);
		$exclude_filename = $this->_db->Quote($this->_db->loadResult());

		$sql = "SELECT DISTINCT file_id, file_mimetype, file_title, file_name
				FROM `#__vm_product_files`
				WHERE ";
				if ($exclude_filename) $sql .= " file_title != ".$exclude_filename." AND
				file_product_id = '".$product_id."'
				AND file_published = '1'
				AND file_is_image = '0'";
		$this->_db->setQuery($sql);
		$files = $this->_db->loadObjectList();

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
		$this->_db = JFactory::getDBO();
		$q = "SELECT `p`.`product_id`, `product_sku`, `product_name`,`product_thumb_image`, related_products
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
			JModel::addIncludePath(JPATH_VM_ADMINISTRATOR.DS.'models');
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
		$this->_db = JFactory::getDBO();
		$showall = JRequest::getBool('showall', 0);

		$q = 'SELECT `comment`, `time`, `userid`, `user_rating`, `username`, `name`
			FROM `#__vm_product_reviews` `r`
			LEFT JOIN `jos_users` `u`
			ON `u`.`id` = `r`.`userid`
			WHERE `product_id` = "'.$product_id.'"
			AND published = "1"
			ORDER BY `time` DESC ';
		if (!$showall) $q .= ' LIMIT 0, 5';
		$this->_db->setQuery($q);
		return $this->_db->loadObjectList();
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
		$this->_db = JFactory::getDBO();
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
		$this->_db = JFactory::getDBO();
		$q = "SELECT COUNT(`product_id`) AS types FROM `#__vm_product_product_type_xref` WHERE `product_id` = ".$product_id;
		$this->_db->setQuery($q);

		return ($this->_db->loadResult() > 0);
	}

	/**
	 * Returns html code for show parameters
	 * @author RolandD
	 *
	 * @param int $product_id
	 * @return array containing all product type info for the requested product
	 */
	public function getProductType($product_id) {
		$this->_db = JFactory::getDBO();
		$product_types = array();

		/* Get the product types the product is linked to */
		$q = "SELECT `t`.*
			FROM `#__vm_product_product_type_xref` `x`
			LEFT JOIN `#__vm_product_type` `t`
			ON `t`.`product_type_id` = `x`.`product_type_id`
			WHERE `product_id` = ".$product_id."
			GROUP BY `product_type_id`";
		$this->_db->setQuery($q);
		$product_types = $this->_db->loadObjectList();

		foreach ($product_types as $pkey => $product_type) {
			/* Load the details */
			$q = "SELECT * FROM `#__vm_product_type_".$product_type->product_type_id."` ORDER BY `parameter_list_order`";
			$this->_db->setQuery($q);
			$product_types[$pkey]->product_type = $this->_db->loadObjectList();
		}
		return $product_types;
	}

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
	public function getVariants($product) {

		$this->_db = &JFactory::getDBO();
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
							$variants[$variant_name][$variant_type] = $variant_price;
						}
					}
					else {
						$variants[$variant_name][$value] = '';
					}
				}
			}
		}
		return $variants;
	}

	function _buildQuery($category_id = 0)
	{
		//$mainframe = Jfactory::getApplication();
		//$option = JRequest::getWord('option');
		//$mainframe->getUserStateFromRequest( $option.'order'  , 'order' ,''	,'word' ) );
		
		$filter_order  = JRequest::getVar('orderby', VmConfig::get('browse_orderby_field'));
		
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
		
		if (VmConfig::get('check_stock') && Vmconfig::getVar('show_out_of_stock_products') != '1')
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
	* Get the products in a given category
	*
	* @author RolandD
	* @access public
	* @param int $category_id the category ID where to get the products for
	* @return array containing product objects
	*/
	public function getProductsInCategory($category_id) {

		if (empty($this->products)) {

			if (empty($this->_query)) $this->_query = $this->_buildQuery($category_id);
			$product_ids = $this->_getList($this->_query, $this->getState('limitstart'), $this->getState('limit')); 

			/* Collect the product data */
			
			foreach ($product_ids as $product_id) {
				$this->products[] = $this->getProduct($product_id->product_id);
			}

			
		}
		return $this->products;
	}
  function getTotalProductsInCategory($category_id)
  {
        // Load the content if it doesn't already exist
        if (empty($this->_total)) {
            if (empty($this->_query)) $this->_query = $this->_buildQuery($category_id);
            $this->_total = $this->_getListCount($this->_query);    
        }
        return $this->_total;
  }
  function getPagination($category_id)
  {
        // Load the content if it doesn't already exist
        if (empty($this->_pagination)) {
            jimport('joomla.html.pagination');
            $this->_pagination = new JPagination($this->getTotalProductsInCategory($category_id), $this->getState('limitstart'), $this->getState('limit') );
        }
        return $this->_pagination;
  }

	/**
	* Get the Order By Select List
	*
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
	if (empty($this->_query)) $this->_query = $this->_buildQuery($category_id);
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
			if ($manufacturer_id > 0) $manufacturerLink .='<div><a title="" href="'.JRoute::_('index.php?option=com_virtuemart&view=category'.$fieldLink.$orderTxt.$orderbyTxt ) .'">'.JText::_('VM_SEARCH_SELECT_ALL_MANUFACTURER').'</a></div>';
			if (count($manufacturers)>1) {
				foreach ($manufacturers as $mf) {
					$link = JRoute::_('index.php?option=com_virtuemart&view=category&manufacturer_id='.$mf->manufacturer_id.$fieldLink.$orderTxt.$orderbyTxt ) ;
					if ($mf->manufacturer_id != $manufacturer_id) {
						$manufacturerLink .='<div><a title="'.$mf->mf_name.'" href="'.$link.'">'.$mf->mf_name.'</a></div>';
					}
					else $currentManufacturerLink ='<div class="activeOrder">'.$mf->mf_name.'</div>';
				}
			} elseif ($manufacturer_id > 0) $currentManufacturerLink =JText::_('VM_PRODUCT_DETAILS_MANUFACTURER_LBL').'<div class="activeOrder">'. $manufacturers[0]->mf_name.'</div>';
			else $currentManufacturerLink ='<div >'.JText::_('VM_PRODUCT_DETAILS_MANUFACTURER_LBL').'</div><div> '.$manufacturers[0]->mf_name.'</div>';
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
				$text = JText::_('VM_SEARCH_ORDER_'.strtoupper($field)) ;
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
		$orderTxt = JText::_('VM_SEARCH_ORDER_DESC');
	} else {
		$orderTxt = JText::_('VM_SEARCH_ORDER_ASC');
		$orderlink ='';
	}

	/* full string list */
	if ($orderby=='') $orderby=$orderbyCfg;
	$orderby=strtoupper($orderby);
	$link = JRoute::_('index.php?option=com_virtuemart&view=category'.$fieldLink.$orderlink.$orderbyTxt.$manufacturerTxt) ;

	$orderByList ='<div class="orderlistcontainer"><div>'.JText::_('VM_ORDERBY').'</div><div class="activeOrder"><a title="'.$orderTxt.'" href="'.$link.'">'.JText::_('VM_SEARCH_ORDER_'.$orderby).' '.$orderTxt.'</a></div>';
	$orderByList .= $orderByLink.'</div>';
	if (empty ($currentManufacturerLink) ) $currentManufacturerLink = JText::_('VM_PRODUCT_DETAILS_MANUFACTURER_LBL').'<div class="activeOrder">'.JText::_('VM_SEARCH_SELECT_MANUFACTURER').'</div>';
	$orderByList .=' <div class="orderlistcontainer">'.$currentManufacturerLink;
	$orderByList .= $manufacturerLink.'</div><div class="clear"></div>';

	return $orderByList ;
  }
	
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
		$stock_tip = JText::_('VM_STOCK_LEVEL_DISPLAY_NORMAL_TIP');
		if ($stock_level <= $reorder_level) {
			$level = 'lowstock';
			$stock_tip = JText::_('VM_STOCK_LEVEL_DISPLAY_LOW_TIP');
		}
		if ($stock_level == 0) {
			$level = 'nostock';
			$stock_tip = JText::_('VM_STOCK_LEVEL_DISPLAY_OUT_TIP');
		}
    	$stock = new Stdclass();
    	$stock->stock_tip = $stock_tip;
    	$stock->stock_level = $level;
    	return $stock;
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
		if (VmConfig::get('pshop_allow_reviews', 0) == '1') {
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
	 * Function to create a DB object that holds all information
	 * from the attribute tables about item $item_id AND/OR product $product_id
	 *
	 * @author RolandD
	 * @access private
	 * @param int $product The product object
	 * @param string $attribute_name The name of the attribute to filter
	 * @return array list of attribute objects
	 */
	private function getAttributes($product, $attribute_name = '') {
		$this->_db = JFactory::getDBO();
		$attributes = array();
		if ($product->product_id && $product->product_parent_id) {
			$q  = "SELECT * FROM `#__vm_product_attribute`, `#__vm_product_attribute_sku` ";
			$q .= "WHERE `#__vm_product_attribute`.`product_id` = ".$product->product_id." ";
			$q .= "AND `#__vm_product_attribute_sku`.`product_id` = ".$product->product_parent_id." ";
			if ($attribute_name) {
				$q .= "AND `#__vm_product_attribute`.`attribute_name` = ".$this->_db->Quote($attribute_name)." ";
			}
			$q .= "AND `#__vm_product_attribute`.`attribute_name` = `#__vm_product_attribute_sku`.attribute_name ";
			$q .= "ORDER BY attribute_list, `#__vm_product_attribute`.`attribute_name`";
		}
		elseif ($product->product_id) {
			$q  = "SELECT * FROM `#__vm_product_attribute` ";
			$q .= "WHERE  `product_id` = ".$product->product_id." ";
			if ($attribute_name) {
				$q .= "AND `attribute_name` = ".$this->_db->Quote($attribute_name)." ";
			}
		}
		elseif ($product->product_parent_id) {
			$q  = "SELECT * FROM `#__vm_product_attribute_sku` ";
			$q .= "WHERE product_id = ".$product->product_parent_id." ";
			if ($attribute_name) {
				$q .= "AND `#__vm_product_attribute`.`attribute_name` = ".$this->_db->Quote($attribute_name)." ";
			}
			$q .= "ORDER BY attribute_list,`attribute_name`";
		}

		$this->_db->setQuery($q);
		$attributes = $this->_db->loadObjectList();
		return $attributes;
	}

	/**
	* Load the custom variants
	*
	* @author RolandD
	* @access private
	* @param string $custom_attr_list containing the custom variants
	* @return array containing the custom variants
	*/
	private function getCustomVariants($custom_attr_list) {
		$fields = array();
		if ($custom_attr_list) {
			if (substr($custom_attr_list, -1) == ';') $custom_attr_list = substr($custom_attr_list, 0, -1);
			$fields = explode(";", $custom_attr_list);
		}
		return $fields;
	}

}
// pure php no closing tag