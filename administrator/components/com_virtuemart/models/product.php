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

// JTable::addIncludePath(JPATH_VM_ADMINISTRATOR.DS.'tables');
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
		$this->starttime = microtime(true);
		$this->maxScriptTime = ini_get('max_execution_time')*0.95-1;
		// 	$this->addvalidOrderingFieldName(array('m.mf_name','pp.product_price'));

		$app = JFactory::getApplication();
		if($app->isSite() ){
			$browseOrderByFields = VmConfig::get('browse_orderby_fields');

		} else {
			if(!class_exists('shopFunctions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'shopfunctions.php');
			$browseOrderByFields = ShopFunctions::getValidProductFilterArray ();
			$this->addvalidOrderingFieldName(array('product_price'));
			// 	vmdebug('$browseOrderByFields',$browseOrderByFields);
		}
		$this->addvalidOrderingFieldName((array)$browseOrderByFields);
		$this->removevalidOrderingFieldName('virtuemart_product_id');

		//unset($this->_validOrderingFieldName[0]);//virtuemart_product_id
		array_unshift($this->_validOrderingFieldName,'p.virtuemart_product_id');
		$this->_selectedOrdering = VmConfig::get('browse_orderby_field','category_name');
// 			vmdebug('product allows following orderingFields ',$this->_validOrderingFieldName);

		$this->initialiseRequests();

		//This is just done now for the moment for developing, the idea is of course todo this only when needed.
		$this->updateRequests();

	}


	var $keyword 							= "0";
	var $product_parent_id 				= false;
	var $virtuemart_manufacturer_id	= false;
	var $virtuemart_category_id  = 0;
	var $search_type						= '';
	var $searchcustoms					= false;
	var $searchplugin						= 0;
	var $filter_order 					= 'p.virtuemart_product_id';
	var $filter_order_Dir 				= 'DESC';

	/**
	 * This function resets the variables holding request depended data to the initial values
	 *
	 * @author Max Milbers
	 */
	function initialiseRequests(){

		$this->keyword 							= "0";
		$this->valid_search_fields 				= array('product_name');
		$this->product_parent_id 				= false;
		$this->virtuemart_manufacturer_id		= false;
		$this->search_type						= '';
		$this->searchcustoms					= false;
		$this->searchplugin						= 0;
		$this->filter_order 					= VmConfig::get('browse_orderby_field');;
		$this->filter_order_Dir 				= 'DESC';

	}

	/**
	 * This functions updates the variables of the model which are used in the sortSearchListQuery
	 *  with the variables from the Request
	 *
	 * @author Max Milbers
	 */
	function updateRequests(){
		//hmm how to trigger that in the module or so?
		$this->keyword = vmRequest::uword('keyword', "0", ' ');
		if($this->keyword =="0"){
			$this->keyword = vmRequest::uword('filter_product', "0", ' ');
		}

		$app = JFactory::getApplication();
		$option = 'com_virtuemart';
		$view = 'product';

		//Filter order and dir  This is unecessary complex and maybe even wrong, but atm it seems to work
		if($app->isSite()){
			$filter_order = JRequest::getString('orderby', VmConfig::get('browse_orderby_field','p.virtuemart_product_id'));
			$filter_order     = $this->checkFilterOrder($filter_order);

			$filter_order_Dir 	= strtoupper(JRequest::getWord('order', 'ASC'));
			$valid_search_fields = VmConfig::get('browse_search_fields');
		} else {
			$filter_order = strtolower($app->getUserStateFromRequest( 'com_virtuemart.'.$view.'.filter_order', 'filter_order',$this->_selectedOrdering , 'cmd' ));

			$filter_order     = $this->checkFilterOrder($filter_order);
			$filter_order_Dir = strtoupper($app->getUserStateFromRequest( $option.'.'.$view.'.filter_order_Dir', 'filter_order_Dir', '', 'word' ));
			$valid_search_fields = array('product_name');
		}
		$filter_order_Dir = $this->checkFilterDir($filter_order_Dir);

		$this->filter_order = $filter_order;
		$this->filter_order_Dir = $filter_order_Dir;
		$this->valid_search_fields = $valid_search_fields;


		$this->product_parent_id= JRequest::getInt('product_parent_id', false );

		$this->virtuemart_manufacturer_id = JRequest::getInt('virtuemart_manufacturer_id', false );

		$this->search_type = JRequest::getVar('search_type', '');

		$this->searchcustoms = JRequest::getVar('customfields', array(), 'default' ,'array');

		$this->searchplugin = JRequest::getInt('custom_parent_id',0);

	}

	/**
	 * Sets the keyword variable for the search
	 *
	 * @param string $keyword
	 */
	function setKeyWord($keyword){
		$this->keyword = $keyword;
	}

	/**
	 * New function for sorting, searching, filtering and pagination for product ids.
	 *
	 * @author Max Milbers
	 */
	function sortSearchListQuery($onlyPublished=true,$virtuemart_category_id = false, $group=false,$nbrReturnProducts=false){

		$app = JFactory::getApplication() ;

		$groupBy = 'group by p.`virtuemart_product_id` ';

		//administrative variables to organize the joining of tables
		$joinCategory 	= false ;
		$joinMf 			= false ;
		$joinPrice 		= false ;
		$joinCustom		= false ;
		$joinShopper 	= false;
		$joinLang 		= true; // test fix Patrick
		$orderBy = ' ';

		$where = array();
		$useCore = true;
		if ($this->searchplugin !== 0){
			//reset generic filters ! Why? the plugin can do it, if it wishes it.
			// 			if ($this->keyword ==='') $where=array();
			JPluginHelper::importPlugin('vmcustom');
			$dispatcher = JDispatcher::getInstance();
			$PluginJoinTables = array();
			$ret = $dispatcher->trigger('plgVmAddToSearch',array(&$where, &$PluginJoinTables, $this->searchplugin));
			foreach($ret as $r){
				if(!$r) $useCore = false;
			}
		}

		if($useCore){
// 		if ( $this->keyword !== "0" and $group ===false) {
		if ( !empty($this->keyword) and $this->keyword !=='' and $group ===false) {
// 			$groupBy = 'group by p.`product_parent_id`';

			//		$keyword = trim(preg_replace('/\s+/', '%', $keyword), '%');
			$keyword = '"%' . $this->_db->getEscaped($this->keyword, true) . '%"';

			foreach ($this->valid_search_fields as $searchField) {
				if($searchField == 'category_name' || $searchField == 'category_description'){
					$joinCategory = true;
				}else if($searchField == 'mf_name'){
					$joinMf = true;
				}else if($searchField == 'product_price'){
					$joinPrice = true;
				}
				else if(strpos($searchField, '.')== 1){
					$searchField = 'p`.`'.substr($searchField, 2, (strlen($searchField))) ;
				}
				$filter_search[] = '`'.$searchField.'` LIKE '.$keyword;

			}
			if(!empty($filter_search)){
				$where[] = '('.implode(' OR ', $filter_search ).')';
			} else {
				$where[] = '`product_name` LIKE '.$search;
				//If they have no check boxes selected it will default to product name at least.
			}
			$joinLang = true;
		}

// 		vmdebug('my $this->searchcustoms ',$this->searchcustoms);
		if (!empty($this->searchcustoms)){
			$joinCustom = true ;
			foreach ($this->searchcustoms as $key => $searchcustom) {
				$custom_search[] = '(pf.`virtuemart_custom_id`="'.(int)$key.'" and pf.`custom_value` like "%' . $this->_db->getEscaped( $searchcustom, true ) . '%")';
			}
			$where[] = " ( ".implode(' OR ', $custom_search )." ) ";
		}



		if($onlyPublished){
			$where[] = ' p.`published`="1" ';
		}

		if($app->isSite() && !VmConfig::get('use_as_catalog',0) && VmConfig::get('stockhandle','none')=='disableit' ){
			$where[] = ' p.`product_in_stock`>"0" ';
		}


		if ($virtuemart_category_id>0){
			$joinCategory = true ;
			$where[] = ' `#__virtuemart_product_categories`.`virtuemart_category_id` = '.$virtuemart_category_id;
		}

		if ($this->product_parent_id){
			$where[] = ' p.`product_parent_id` = '.$this->product_parent_id;
		}


		if ($app->isSite()) {
			if(!class_exists('VirtueMartModelUser')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'user.php');
			$usermodel = VmModel::getModel('user');
			$currentVMuser = $usermodel->getUser();
			$virtuemart_shoppergroup_ids =  (array)$currentVMuser->shopper_groups;

			if(is_array($virtuemart_shoppergroup_ids)){
				$sgrgroups = array();
				foreach ($virtuemart_shoppergroup_ids as $key => $virtuemart_shoppergroup_id){
					$sgrgroups[] = 's.`virtuemart_shoppergroup_id`= "' . (int) $virtuemart_shoppergroup_id . '" ';
				}
				$sgrgroups[] = 's.`virtuemart_shoppergroup_id` IS NULL ';
				$where[] = " ( ".implode(' OR ', $sgrgroups )." ) ";

				$joinShopper = true;
			}
		}


		if ($this->virtuemart_manufacturer_id) {
			$joinMf = true ;
			$where[] = ' `#__virtuemart_product_manufacturers`.`virtuemart_manufacturer_id` = '.$this->virtuemart_manufacturer_id;
		}

		// Time filter
		if ($this->search_type != '') {
			$search_order = $this->_db->getEscaped(JRequest::getWord('search_order') == 'bf' ? '<' : '>');
			switch ($this->search_type) {
				case 'parent':
					$where[] = 'p.`product_parent_id` = "0"';
					break;
				case 'product':
					$where[] = 'p.`modified_on` '.$search_order.' "'.$this->_db->getEscaped(JRequest::getVar('search_date')).'"';
					break;
				case 'price':
					$joinPrice = true ;
					$where[] = 'pp.`modified_on` '.$search_order.' "'.$this->_db->getEscaped(JRequest::getVar('search_date')).'"';
					break;
				case 'withoutprice':
					$joinPrice = true ;
					$where[] = 'pp.`product_price` IS NULL';
					break;
				case 'stockout':
					$where[] = 'p.`product_in_stock`- p.`product_ordered` < 1';
					break;
				case 'stocklow':
					$where[] = 'p.`product_in_stock`- p.`product_ordered` < p.`low_stock_notification`';
					break;
			}
		}


		// special  orders case
		switch ($this->filter_order) {
			case 'product_special':
				$where[] = ' p.`product_special`="1" ';// TODO Change  to  a  individual button
				$orderBy = 'ORDER BY RAND()';
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
			case 'ordering':
				$orderBy = ' ORDER BY `#__virtuemart_product_categories`.`ordering` ';
				$joinCategory = true ;
				break;
			case 'product_price':
				//$filters[] = 'p.`virtuemart_product_id` = p.`virtuemart_product_id`';
				$orderBy = ' ORDER BY `product_price` ';
				$joinPrice = true ;
				break;
			case 'created_on':
				$orderBy = ' ORDER BY p.`created_on` ';
				break;
			default ;
				if(!empty($this->filter_order)){
					$orderBy = ' ORDER BY '.$this->_db->getEscaped($this->filter_order).' ';
				} else {
					$this->filter_order_Dir = '';
				}
			break;
		}

		//Group case from the modules
		if($group){

			$groupBy = 'group by p.`virtuemart_product_id` ';
			switch ($group) {
				case 'featured':
					$where[] = 'p.`product_special`="1" ';
					$orderBy = 'ORDER BY RAND()';
					break;
				case 'latest':
					$date = JFactory::getDate( time()-(60*60*24*7) ); //Set on a week, maybe make that configurable
					$dateSql = $date->toMySQL();
					$where[] = 'p.`modified_on` > "'.$dateSql.'" ';
					$orderBy = 'ORDER BY p.`modified_on`';
					$this->filter_order_Dir = 'DESC';
					break;
				case 'random':
					$orderBy = ' ORDER BY RAND() ';//LIMIT 0, '.(int)$nbrReturnProducts ; //TODO set limit LIMIT 0, '.(int)$nbrReturnProducts;
					break;
				case 'topten';
					$orderBy = ' ORDER BY product_sales ';//LIMIT 0, '.(int)$nbrReturnProducts;  //TODO set limitLIMIT 0, '.(int)$nbrReturnProducts;
					$this->filter_order_Dir = 'DESC';
			}
			// 			$joinCategory 	= false ; //creates error
			// 			$joinMf 		= false ;	//creates error
			$joinPrice 		= true ;
			$this->searchplugin	= false ;
// 			$joinLang = false;
		}
		}

		//write the query, incldue the tables
		// 		$selectFindRows = 'SELECT SQL_CALC_FOUND_ROWS * FROM `#__virtuemart_products` ';
		// 		$selectFindRows = 'SELECT COUNT(*) FROM `#__virtuemart_products` ';
		if($joinLang){
			$select = ' * FROM `#__virtuemart_products_'.VMLANG.'` as l';
			$joinedTables = ' JOIN `#__virtuemart_products` AS p using (`virtuemart_product_id`)';
		} else {
			$select = ' * FROM `#__virtuemart_products` as p';
			$joinedTables = '';
		}

		if ($joinCategory == true) {
			$joinedTables .= ' LEFT JOIN `#__virtuemart_product_categories` ON p.`virtuemart_product_id` = `#__virtuemart_product_categories`.`virtuemart_product_id`
			 LEFT JOIN `#__virtuemart_categories_'.VMLANG.'` as c ON c.`virtuemart_category_id` = `#__virtuemart_product_categories`.`virtuemart_category_id`';
		}
		if ($joinMf == true) {
			$joinedTables .= ' LEFT JOIN `#__virtuemart_product_manufacturers` ON p.`virtuemart_product_id` = `#__virtuemart_product_manufacturers`.`virtuemart_product_id`
			 LEFT JOIN `#__virtuemart_manufacturers_'.VMLANG.'` as m ON m.`virtuemart_manufacturer_id` = `#__virtuemart_product_manufacturers`.`virtuemart_manufacturer_id` ';
		}


		if ($joinPrice == true) {
			$joinedTables .= ' LEFT JOIN `#__virtuemart_product_prices` as pp ON p.`virtuemart_product_id` = pp.`virtuemart_product_id` ';
		}
		if ($this->searchcustoms) {
			$joinedTables .= ' LEFT JOIN `#__virtuemart_product_customfields` as pf ON p.`virtuemart_product_id` = pf.`virtuemart_product_id` ';
		}
		if ($this->searchplugin!==0) {
			if (!empty( $PluginJoinTables) ) {
				$plgName = $PluginJoinTables[0] ;
				$joinedTables .= ' LEFT JOIN `#__virtuemart_product_custom_plg_'.$plgName.'` as '.$plgName.' ON '.$plgName.'.`virtuemart_product_id` = p.`virtuemart_product_id` ' ;
			}
		}
		if ($joinShopper == true) {
			$joinedTables .= ' LEFT JOIN `#__virtuemart_product_shoppergroups` ON p.`virtuemart_product_id` = `#__virtuemart_product_shoppergroups`.`virtuemart_product_id`
			 LEFT  OUTER JOIN `#__virtuemart_shoppergroups` as s ON s.`virtuemart_shoppergroup_id` = `#__virtuemart_product_shoppergroups`.`virtuemart_shoppergroup_id`';
		}

		if(count($where)>0){
			$whereString = ' WHERE ('.implode(' AND ', $where ).') ';
		} else {
			$whereString = '';
		}
		//vmdebug ( $joinedTables.' joined ? ',$select, $joinedTables, $whereString, $groupBy, $orderBy, $this->filter_order_Dir );		/* jexit();  */
		$product_ids =  $this->exeSortSearchListQuery(2, $select, $joinedTables, $whereString, $groupBy, $orderBy, $this->filter_order_Dir, $nbrReturnProducts);

		// This makes products searchable, we decided that this is not good, because variant childs appear then in lists
		//So the new convention is that products which should be shown on a category or a manufacturer page should have entered this data
		/*		if ($joinCategory == true || $joinMf) {

		$tmp = array();;
		foreach($product_ids as $k=>$id){
		$tmp[] = $id;
		$children = $this->getProductChildIds($id);
		if($children){
		$tmp = array_merge($tmp,$children);
		}
		}
		$product_ids = $tmp;
		}*/

		// 		vmdebug('my product ids',$product_ids);


		return $product_ids;

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
			if(empty($this->_id)){
				return false;
			} else {
				$virtuemart_product_id = $this->_id;
			}
		}
		$productKey = (int)$virtuemart_product_id ;
		static $_products = array ();
		if (! array_key_exists ($productKey,$_products)){

			$child = $this->getProductSingle($virtuemart_product_id,$front, false,$onlyPublished);
			if(!$child->published && $onlyPublished) return false;
			//store the original parent id
			$pId = $child->virtuemart_product_id;
			$ppId = $child->product_parent_id;
			$published = $child->published;

			$i = 0;
			$runtime = microtime(true)-$this->starttime;
			//Check for all attributes to inherited by parent products
			while(!empty($child->product_parent_id) ){
				$runtime = microtime(true)-$this->starttime;
				if($runtime >= $this->maxScriptTime){
					vmdebug('Max execution time reached in model product getProduct() ',$child);
					vmError('Max execution time reached in model product getProduct() '.$child->product_parent_id);
					break;
				} else if($i>10){
					vmdebug('Time: '.$runtime.' Too many child products in getProduct() ',$child);
					vmError('Time: '.$runtime.' Too many child products in getProduct() '.$child->product_parent_id);
					break;
				}
				$parentProduct = $this->getProductSingle($child->product_parent_id,$front, false,false);
				if($child->product_parent_id === $parentProduct->product_parent_id) break;
				$attribs = get_object_vars($parentProduct);

				foreach($attribs as $k=>$v){

					if(strpos($k,'_')!==0 && empty($child->$k)){
						$child->$k = $v;
// 						vmdebug($child->product_parent_id.' $child->$k',$child->$k);
					}
				}
				$i++;
				if($child->product_parent_id != $parentProduct->product_parent_id){
					$child->product_parent_id = $parentProduct->product_parent_id;
				} else {
					$child->product_parent_id = 0;
				}

			}

			//     	vmdebug('getProduct Time: '.$runtime);

			$child->published = $published;
			$child->virtuemart_product_id = $pId;
			$child->product_parent_id = $ppId;

			if ($withCalc) {
				$child->prices = $this->getPrice($child,array(),1);
// 				vmError('deprecated use of $child->prices = $this->getPrice($child,array(),1)');
			}

			if(empty($child->product_template)){
				$child->product_template = VmConfig::get('producttemplate');
			}

			if(empty($child->layout)){
				// product_layout ?
				$child->layout = VmConfig::get('productlayout');
			}

			$app = JFactory::getApplication() ;
			if($app->isSite() and VmConfig::get('stockhandle','none')=='disableit' and ($child->product_in_stock-$child->product_ordered)<=0){
				vmdebug('STOCK 0',VmConfig::get('use_as_catalog',0), VmConfig::get('stockhandle','none') , $child->product_in_stock);
				return false;
			}
			$_products[$productKey] = $child;
		}
		return $_products[$productKey];
	}

	public function getProductSingle($virtuemart_product_id = null,$front=true, $withCalc = true, $onlyPublished=true){

		//$this->fillVoidProduct($front);
		if (!empty($virtuemart_product_id)) {
			$virtuemart_product_id = $this->setId($virtuemart_product_id);
		}

		//		if(empty($this->_data)){
		if (!empty($this->_id)) {

// 			$joinIds = array('virtuemart_product_price_id' =>'#__virtuemart_product_prices','virtuemart_manufacturer_id' =>'#__virtuemart_product_manufacturers','virtuemart_customfield_id' =>'#__virtuemart_product_customfields');
			$joinIds = array('virtuemart_manufacturer_id' =>'#__virtuemart_product_manufacturers','virtuemart_customfield_id' =>'#__virtuemart_product_customfields');

			$product = $this->getTable('products');
			$product->load($this->_id,0,0,$joinIds);

			$xrefTable = $this->getTable('product_medias');
			$product->virtuemart_media_id = $xrefTable->load((int)$this->_id);

			// Load the shoppers the product is available to for Custom Shopper Visibility
			$product->shoppergroups = $this->getProductShoppergroups($this->_id);

			if (!empty($product->shoppergroups) and $front) {
				if(!class_exists('VirtueMartModelUser')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'user.php');
				$usermodel = VmModel::getModel('user');
				$currentVMuser = $usermodel->getUser();
				$virtuemart_shoppergroup_ids =  (array)$currentVMuser->shopper_groups;

// 				vmdebug('$user->shoppergroups',$virtuemart_shoppergroup_ids);
				$commonShpgrps = array_intersect($virtuemart_shoppergroup_ids,$product->shoppergroups);
				if(empty($commonShpgrps)){
					$product = new stdClass();
					return $this->fillVoidProduct($front);
				}
			}

			$ppTable = $this->getTable('product_prices');
			$ppTable->load($this->_id);
			$product = (object) array_merge((array) $ppTable, (array) $product);

			if (!empty($product->virtuemart_manufacturer_id)) {
				$mfTable = $this->getTable('manufacturers');
				$mfTable->load((int)$product->virtuemart_manufacturer_id);
				$product = (object) array_merge((array) $mfTable, (array) $product);
			} else {
				$product->virtuemart_manufacturer_id = array();
				$product->mf_name ='';
				$product->mf_desc ='';
				$product->mf_url ='';
			}

			// Load the categories the product is in
			$product->categories = $this->getProductCategories($this->_id,$front);

			if ( !empty($product->categories) and is_array($product->categories) and !empty($product->categories[0]) ){
				$product->virtuemart_category_id = $product->categories[0];
				$q = 'SELECT `ordering`,`id` FROM `#__virtuemart_product_categories`
					WHERE `virtuemart_product_id` = "'.$this->_id.'" and `virtuemart_category_id`= "'.$product->virtuemart_category_id.'" ';
				$this->_db->setQuery($q);
				// change for faster ordering
				$ordering = $this->_db->loadObject();
				if(!empty($ordering)){
					$product->ordering = $ordering->ordering;
					//What is this? notice by Max Milbers
					$product->id = $ordering->id;
				}

			}
			if (empty($product->virtuemart_category_id)){

				if( isset($product->categories[0])){
					$product->virtuemart_category_id = $product->categories[0];
				} else {
					$product->virtuemart_category_id = 0;
				}

			}

			if(!empty($product->categories[0])){
				$virtuemart_category_id = 0;
				if ($front) {
					if(!class_exists('shopFunctionsF'))require(JPATH_VM_SITE.DS.'helpers'.DS.'shopfunctionsf.php');
					$last_category_id = shopFunctionsF::getLastVisitedCategoryId();
					if (in_array($last_category_id, $product->categories) ){
						$virtuemart_category_id = $last_category_id;

					} else $virtuemart_category_id = JRequest::getInt('virtuemart_category_id',0);
				}
				if ($virtuemart_category_id == 0 ) {
					if (array_key_exists('0', $product->categories))
					$virtuemart_category_id = $product->categories[0];
				}

				$catTable = $this->getTable('categories');
				$catTable->load($virtuemart_category_id);
				$product->category_name = $catTable->category_name;
			} else {
				$product->category_name ='';
			}

			// $this->productHasCustoms($this->_id);

			if(!$front){
// 				if (!empty($product->virtuemart_customfield_id ) ){
					$customfields = VmModel::getModel('Customfields');
					$product->customfields = $customfields->getproductCustomslist($this->_id);
					if(empty($product->customfields) and !empty($product->product_parent_id) ){
						//$product->customfields = $this->productCustomsfieldsClone($product->product_parent_id,true) ;
						$product->customfields = $customfields->getproductCustomslist($product->product_parent_id,$this->_id);
						$product->customfields_parent_id = $product->product_parent_id ;

					}

// 				vmdebug('$product->customfields',$product->customfields);
			} else {

				// Add the product link  for canonical
				$productCategory = empty($product->categories[0])? '':$product->categories[0];
				$product->canonical = 'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$this->_id.'&virtuemart_category_id='.$productCategory ;
				$product->link = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$this->_id.'&virtuemart_category_id='.$productCategory);

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
				if (!empty($product->virtuemart_customfield_id ) ) {

					$customfields = VmModel::getModel('Customfields');
					// Load the custom product fields
					$product->customfields = $customfields->getProductCustomsField($product);
					$product->customfieldsRelatedCategories = $customfields->getProductCustomsFieldRelatedCategories($product);
					$product->customfieldsRelatedProducts = $customfields->getProductCustomsFieldRelatedProducts($product);
					//  custom product fields for add to cart
					$product->customfieldsCart = $customfields->getProductCustomsFieldCart($product);
					$child = $this->getProductChilds($this->_id);
					$product->customsChilds = $customfields->getProductCustomsChilds($child , $this->_id);
				}

// 				vmdebug('my product ',$product);

				// Check the stock level
				if (empty($product->product_in_stock)) $product->product_in_stock = 0;

				// Get stock indicator
				//				$product->stock = $this->getStockIndicator($product);

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
		$product->override = null;
		$product->categories = array();
		$product->shoppergroups= array();

		if($front){
			$product->link = '';

			$product->prices = array();
			$product->virtuemart_category_id = 0;
			$product->virtuemart_shoppergroup_id = 0;
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
	private function getProductCategories($virtuemart_product_id=0,$front=false) {

		$categories = array();
		if ($virtuemart_product_id > 0) {
			$q = 'SELECT pc.`virtuemart_category_id` FROM `#__virtuemart_product_categories` as pc';
			if ($front) $q.= ' LEFT JOIN `#__virtuemart_categories` as c ON c.`virtuemart_category_id` = pc.`virtuemart_category_id`';
			$q.= ' WHERE pc.`virtuemart_product_id` = '.(int)$virtuemart_product_id;
			if ($front) $q.= ' AND `published`=1';
			$this->_db->setQuery($q);
			$categories = $this->_db->loadResultArray();
		}

		return $categories;
	}

	/**
	 * Load  the product shoppergroups
	 *
	 * @author Kohl Patrick,RolandD,Max Milbers, Cleanshooter
	 * @return array list of updateProductShoppergroupsTable that can view the product
	 */
	private function getProductShoppergroups($virtuemart_product_id=0) {

		$shoppergroups = array();
		if ($virtuemart_product_id > 0) {
			$q = 'SELECT `virtuemart_shoppergroup_id` FROM `#__virtuemart_product_shoppergroups` WHERE `virtuemart_product_id` = "'.(int)$virtuemart_product_id.'"';
			$this->_db->setQuery($q);
			$shoppergroups = $this->_db->loadResultArray();
		}

		return $shoppergroups;
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

		$ids = $this->sortSearchListQuery(true, $categoryId);
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
	public function getProductListing($group = false, $nbrReturnProducts = false, $withCalc = true, $onlyPublished = true, $single = false,$filterCategory= true, $category_id=0){

		$app = JFactory::getApplication();
		if($app->isSite() ){
			$front = true;
			if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');
			if(!Permissions::getInstance()->check('admin','storeadmin')){
				$onlyPublished = true;
				if ($show_prices=VmConfig::get('show_prices',1) == '0'){
					$withCalc = false;
				}
			}
		} else {
			$front = false;
		}

		$this->setFilter();
		if ( $filterCategory=== true)
		{
			if ($category_id)
			{
				$this->virtuemart_category_id = $category_id;
			}
		}
		else
		{
			$this->virtuemart_category_id = false;
		}
		$ids = $this->sortSearchListQuery($onlyPublished, $this->virtuemart_category_id, $group, $nbrReturnProducts);

		$products = $this->getProducts($ids, $front, $withCalc, $onlyPublished,$single);
		return $products;
	}

	/**
	 * overriden getFilter to persist filters
	 *
	 * @author OSP
	 */
	public function setFilter()
	{
		$app = JFactory::getApplication();
		if( !$app->isSite() )
		{ //persisted filter only in admin
			$view = JRequest::getWord('view');
			$mainframe = JFactory::getApplication();
			$this->virtuemart_category_id = $mainframe->getUserStateFromRequest('com_virtuemart.'.$view.'.filter.virtuemart_category_id', 'virtuemart_category_id',  0, 'int');
			$this->setState('virtuemart_category_id', $this->virtuemart_category_id );
			$this->virtuemart_manufacturer_id =  $mainframe->getUserStateFromRequest('com_virtuemart.'.$view.'.filter.virtuemart_manufacturer_id', 'virtuemart_manufacturer_id',  0, 'int');
			$this->setState('virtuemart_manufacturer_id', $this->virtuemart_manufacturer_id);
		}
		else
		{
			$this->virtuemart_category_id = JRequest::getInt('virtuemart_category_id', false );
		}
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

		if(empty($productIds)){
			// 			vmdebug('getProducts has no $productIds','No ids given to get products');
			// 			vmTrace('getProducts has no $productIds');
			return array();
		}

		$maxNumber = VmConfig::get('absMaxProducts',700);
		$products=array();
		if($single){
			foreach($productIds as $id){
				$i = 0;
				if($product = $this->getProductSingle((int)$id,$front, $withCalc, $onlyPublished)){
					// 					if($onlyPublished && $product->published){
					$products[] = $product;
					$i++;
					// 					}
					// 					if(!$onlyPublished){
					// 						$products[] = $product;
					// 						$i++;
					// 					}
				}
				if($i>$maxNumber){
					vmdebug('Better not to display more than '.$maxNumber.' products');
					return $products;
				}
			}
		} else {
			$i = 0;
			foreach($productIds as $id){
				if($product = $this->getProduct((int)$id,$front, $withCalc, $onlyPublished)){
					$products[] = $product;
					$i++;
				}
				if($i>$maxNumber){
					vmdebug('Better not to display more than '.$maxNumber.' products');
					return $products;
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
	public function getNeighborProducts($product ,$onlyPublished = true, $max=1) {
		$db = JFactory::getDBO();
		$neighbors = array('previous' => '','next' => '');
		$direction = 'DESC' ;
		$op='<';
		foreach ($neighbors as &$neighbor) {

			$q = 'SELECT `l`.`virtuemart_product_id`, `l`.`product_name`
				FROM `#__virtuemart_products` as `p`
				JOIN `#__virtuemart_products_'.VMLANG.'` as `l` using (`virtuemart_product_id`)
				JOIN `#__virtuemart_product_categories` as `pc` using (`virtuemart_product_id`)
				WHERE `virtuemart_category_id` = '.(int)$product->virtuemart_category_id;
			$q .= ' and `slug` '.$op.' "'.$product->slug.'" ' ;
			if ($onlyPublished) $q .= ' AND p.`published`= 1';
			$q .=' ORDER BY `slug` '.$direction.' LIMIT 0,'.(int)$max;

			$db->setQuery($q);
			if ($result = $db->loadAssocList()) $neighbor = $result;
			$direction = 'ASC';
			$op='>';
// 			vmdebug('getNeighborProducts '.$db->getQuery());
		}

		return $neighbors;
	}






	/* reorder product in one category
	 * TODO this not work perfect ! (Note by Patrick Kohl)
	*/
	function saveorder($cid , $orders) {

		JRequest::checkToken() or jexit( 'Invalid Token' );

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
// 			if ($order != $i) {
				$this->_db->setQuery('UPDATE `#__virtuemart_product_categories`
					SET `ordering` = '. $i.'
					WHERE `id` = ' . (int)$key . ' ');
				if (! $this->_db->query()){
					vmError($this->_db->getErrorMsg());
					return false;
				}
				$ordered ++ ;
// 			}
			$i++ ;
		}
		if ($ordered) $msg = JText::sprintf('COM_VIRTUEMART_ITEMS_MOVED', $ordered);
		else $msg = JText::_('COM_VIRTUEMART_ITEMS_NOT_MOVED');
		JFactory::getApplication()->redirect('index.php?option=com_virtuemart&view=product&virtuemart_category_id='.$virtuemart_category_id, $msg);

	}

	/**
	 * Moves the order of a record
	 * @param integer The increment to reorder by
	 */
	function move($direction) {

		JRequest::checkToken() or jexit( 'Invalid Token' );

		// Check for request forgeries
		$table = $this->getTable('product_categories');
		$table->move($direction);

		JFactory::getApplication()->redirect('index.php?option=com_virtuemart&view=product&virtuemart_category_id='.JRequest::getInt('virtuemart_category_id', 0));
	}

	/**
	 * Store a product
	 *
	 * @author RolandD
	 * @author Max Milbers
	 * @access public
	 */
	public function store($product=false,$isChild = false) {

		/* Load the data */
		if($product){
			$data = (array)$product;
		} else{
			$data = JRequest::get('post');
		}

// 				vmdebug('my data in product store ',$data);
		if(isset($data['intnotes'])){
			$data['intnotes'] = trim($data['intnotes']);
		}
		// Setup some place holders
		$product_data = $this->getTable('products');

		//Set the product packaging
		if (array_key_exists('product_box', $data )) {
			$data['product_packaging'] = (($data['product_box'] << 16) | ($data['product_packaging']&0xFFFF));
		}

		// 		if(VmConfig::get('productlayout') == $data['layout']){
		// 			$data['layout'] = 0;
		// 		}

		//with the true, we do preloading and preserve so old values, but why do we do that? I try with false note by Max Milbers
		$product_data->bindChecknStore($data,true);

		$errors = $product_data->getErrors();
		foreach($errors as $error){
			vmError($error);
			return false;
		}

		$this->_id = $data['virtuemart_product_id'] = $product_data->virtuemart_product_id ;

		if(empty($this->_id)) return false;

		// 	 	JPluginHelper::importPlugin('vmcustom');
		// 	 	$dispatcher = JDispatcher::getInstance();
		// 	 	$error = $dispatcher->trigger('plgVmOnStoreProduct', array('product',$data,$product_data->virtuemart_product_id));

		//We may need to change this, the reason it is not in the other list of commands for parents
		if(!$isChild){
			if (isset($data['save_customfields']) ){
				if(!class_exists('VirtueMartModelCustom')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'custom.php');
				VirtueMartModelCustom::saveModelCustomfields('product',$data,$product_data->virtuemart_product_id);
			}
		}


// 		vmdebug('use_desired_price '.$this->_id.' '.$data['use_desired_price']);
		if(!$isChild and isset($data['use_desired_price']) and $data['use_desired_price'] == "1"){

			if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');
			$calculator = calculationHelper::getInstance();
			$data['product_price'] = $calculator->calculateCostprice($this->_id,$data);
			unset($data['use_desired_price']);
			// 			vmdebug('product_price '.$data['product_price']);
		}
		if(isset($data['product_price'])){
			if($isChild){
				unset($data['product_override_price']);
				unset($data['override']);
			}
			$data = $this->updateXrefAndChildTables($data, 'product_prices');
		}


		if(!empty($data['childs'])){
			foreach($data['childs'] as $productId => $child){
				$child['product_parent_id'] = $data['virtuemart_product_id'];
				$child['virtuemart_product_id'] = $productId;
				$this->store($child,true);
			}
		}

		if(!$isChild){

			$data = $this->updateXrefAndChildTables($data,'product_shoppergroups');

			$data = $this->updateXrefAndChildTables($data, 'product_manufacturers');

			if(!empty($data['categories']) && count($data['categories'])>0){
				$data['virtuemart_category_id'] = $data['categories'];
			} else {
				$data['virtuemart_category_id'] = array();
			}
			$data = $this->updateXrefAndChildTables($data,'product_categories');

			// Update waiting list
			if(!empty($data['notify_users'])){
				if ($data['product_in_stock'] > 0 && $data['notify_users'] == '1' ) {
					$waitinglist = VmModel::getModel('Waitinglist');
					$waitinglist->notifyList($data['virtuemart_product_id']);
				}
			}

			// Process the images
			$mediaModel = VmModel::getModel('Media');

			$mediaModel->storeMedia($data,'product');
			$errors = $mediaModel->getErrors();
			foreach($errors as $error){
				vmError($error);
			}

		}

		return $product_data->virtuemart_product_id;
	}

	private function updateXrefAndChildTables($data,$tableName,$preload=false){

		//First we load the xref table, to get the old data
		$product_table_Parent = $this->getTable($tableName);
		$product_table_Parent->bindChecknStore($data,$preload);
		$errors = $product_table_Parent->getErrors();
		foreach($errors as $error){
			vmError($error);
		}
		return $data;

	}

	/**
	 * This function creates a child for a given product id
	 * @author Max Milbers
	 * @author Patrick Kohl
	 * @param int id of parent id
	 */
	public function createChild($id){
		// created_on , modified_on
		$db = JFactory::getDBO();
		$vendorId = 1;
		$childs = count($this->getProductChildIds($id));
		$db->setQuery('SELECT `product_name`,`slug` FROM `#__virtuemart_products` JOIN `#__virtuemart_products_'.VMLANG.'` as l using (`virtuemart_product_id`) WHERE `virtuemart_product_id`='.(int)$id );
		$parent = $db->loadObject();
		$newslug = $parent->slug.$id.rand(1,9);
		$data = array('product_name' => $parent->product_name,'slug' => $newslug,'virtuemart_vendor_id' => (int)$vendorId, 'product_parent_id' => (int)$id);

		$prodTable = $this->getTable('products');
		$prodTable->bindChecknStore($data);

		$langs = (array) VmConfig::get('active_languages');
		if(count($langs)>1){
			foreach($langs as $lang){
				$lang = str_replace('-', '_', $lang);
				$db->setQuery('SELECT `product_name` FROM `#__virtuemart_products_'.$lang.'` WHERE `virtuemart_product_id` = "'.$prodTable->virtuemart_product_id.'" ');
				$res = $db->loadResult();
				if(!$res){
					$db->setQuery('INSERT INTO `#__virtuemart_products_'.$lang.'` (`virtuemart_product_id`,`slug`) VALUES ("'.$prodTable->virtuemart_product_id.'","'.$newslug.'");');
					$db->query();
					$err = $db->getErrorMsg();
					if(!empty($err)){
						vmError('Database error: createChild '.$err);
					}
				}
			}

		}
		return $data['virtuemart_product_id'] ;
	}

	/**
	 * Creates a clone of a given product id
	 *
	 * @author Max Milbers
	 * @param int $virtuemart_product_id
	 */

	public function createClone($id){
		//	if (is_array($cids)) $cids = array($cids);
		$product = $this->getProduct($id, false, true, false);
		$product->field = $this->productCustomsfieldsClone($id);
// 		vmdebug('$product->field',$product->field);
		$product->virtuemart_product_id = $product->virtuemart_product_price_id = 0;
		$product->slug = $product->slug.'-'.$id;
		$product->save_customfields = 1;
		$this->store($product);
		return $this->_id;
	}

	/* look if whe have a product type */
	private function productCustomsfieldsClone($virtuemart_product_id) {
		$this->_db = JFactory::getDBO();
		$q  = "SELECT * FROM `#__virtuemart_product_customfields`";
		$q .=" WHERE `virtuemart_product_id` = ".$virtuemart_product_id ;
		$this->_db->setQuery($q);
		$customfields = $this->_db->loadAssocList();
		if ($customfields) {
			foreach ($customfields as &$customfield) unset($customfield['virtuemart_product_id'],$customfield['virtuemart_customfield_id']);
			return $customfields;
		}
		else return null;
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
		$shop = $this->getTable('product_shoppergroups');
		$rating = $this->getTable('ratings');
		$review = $this->getTable('rating_reviews');
		$votes = $this->getTable('rating_votes');

		$ok = true;
		foreach($ids as $id) {

			$childIds = $this->getProductChildIds($id);
			if(!empty($childIds)){
				vmError(JText::_('COM_VIRTUEMART_PRODUCT_CANT_DELETE_CHILD'));
				$ok = false;
				continue;
			}

			if (!$table->delete($id)) {
				vmError('Product delete '.$table->getError());
				$ok = false;
			}

			if (!$cats->delete($id)) {
				vmError('Product delete categories '.$cats->getError());
				$ok = false;
			}

			if (!$customs->delete($id)) {
				vmError('Product delete customs '.$customs->getError());
				$ok = false;
			}

			if (!$manufacturers->delete($id)) {
				vmError('Product delete manufacturer '.$manufacturers->getError());
				$ok = false;
			}

			if (!$medias->delete($id)) {
				vmError('Product delete medias '.$medias->getError());
				$ok = false;
			}

			if (!$prices->delete($id)) {
				vmError('Product delete prices '.$prices->getError());
				$ok = false;
			}

			if (!$shop->delete($id)) {
				vmError('Product delete shoppergroups '.$shop->getError());
				$ok = false;
			}

			if (!$rating->delete($id,'virtuemart_product_id')) {
				vmError('Product delete rating '.$rating->getError());
				$ok = false;
			}

			if (!$review->delete($id,'virtuemart_product_id')) {
				vmError('Product delete reviews '.$review->getError());
				$ok = false;
			}
			if (!$votes->delete($id,'virtuemart_product_id')) {
				vmError('Product delete votes '.$votes->getError());
				$ok = false;
			}

			// delete plugin on product delete
			// $ok must be set to false if an error occurs
			JPluginHelper::importPlugin('vmcustom');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('plgVmOnDeleteProduct', array($id , &$ok));
		}

		return $ok;
	}


/**
 * Gets the price for a variant
 *
 * @author Max Milbers
 */
public function getPrice($product,$customVariant,$quantity){

	$this->_db = JFactory::getDBO();
	// 		vmdebug('strange',$product);
	if(!is_object($product)){
// 		vmError('deprecated use of getPrice');
		$product = $this->getProduct($product,true,false,true);
// 		return false;
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
 * Get the Order By Select List
 *
 * notice by Max Milbers html tags should never be in a model. This function should be moved to a helper or simular,...
 * @author Kohl Patrick
 * @access public
 * @param $fieds from config Back-end
 * @return $orderByList
 * Order,order By, manufacturer and category link List to echo Out
 **/
function getOrderByList($virtuemart_category_id=false) {

	$getArray = (JRequest::get( 'get' ));
	$link = '';
	$fieldLink ='';
	// remove setted variable
	unset ($getArray['globalCurrencyConverter'],$getArray['virtuemart_manufacturer_id'],$getArray['order'],$getArray['orderby']);

	// foreach ($getArray as $key => $value )
		// $fieldLink .= '&'.$key.'='.$value;
	foreach ($getArray as $key => $value ){
		if (is_array($value)){
			foreach ($value as $k => $v ){
				$fieldLink .= '&'.$key.'['.$k.']'.'='.$v;
			}
		} else {
			$fieldLink .= '&'.$key.'='.$value;
		}
	}
	$fieldLink[0] = "?";
	$fieldLink = 'index.php'.$fieldLink ;
	$orderTxt ='';

	$order = JRequest::getWord('order', 'ASC');
	if ($order == 'DESC' ) $orderTxt .= '&order='.$order;

	$orderbyTxt ='';
	$orderby = JRequest::getVar('orderby', VmConfig::get('browse_orderby_field'));
	$orderbyCfg 	= VmConfig::get('browse_orderby_field');
	if ($orderby != '' && $orderby != $orderbyCfg ) $orderbyTxt = '&orderby='.$orderby;


	$manufacturerTxt ='';
	$manufacturerLink = '';
	if(VmConfig::get('show_manufacturers')){
		$tmp = $this->_noLimit;
		$this->_noLimit = true;

		$this->_noLimit = $tmp;

		// manufacturer link list

		$virtuemart_manufacturer_id = JRequest::getInt('virtuemart_manufacturer_id',0);
		if ($virtuemart_manufacturer_id != '' ){
			$manufacturerTxt ='&virtuemart_manufacturer_id='.$virtuemart_manufacturer_id;
		}

		// if ($mf_virtuemart_product_ids) {
			$query = 'SELECT DISTINCT l.`mf_name`,l.`virtuemart_manufacturer_id` FROM `#__virtuemart_manufacturers_'.VMLANG.'` as l';
			$query .=' JOIN `#__virtuemart_product_manufacturers` AS pm using (`virtuemart_manufacturer_id`)';
			$query .= ' LEFT JOIN `#__virtuemart_products` as p ON p.`virtuemart_product_id` = pm.`virtuemart_product_id` ';
			$query .= ' LEFT JOIN `#__virtuemart_product_categories` as c ON c.`virtuemart_product_id` = pm.`virtuemart_product_id` ';
			$query .= ' WHERE p.`published` =1';
			if ($virtuemart_category_id) $query .= ' AND c.`virtuemart_category_id` ='.(int)$virtuemart_category_id;
			$query .= ' ORDER BY l.`mf_name`';
			$this->_db->setQuery($query);
			$manufacturers = $this->_db->loadObjectList();
			// 		vmdebug('my manufacturers',$this->_db->getQuery());
			$manufacturerLink='';
			if (count($manufacturers)>0) {
				$manufacturerLink ='<div class="orderlist">';
				if ($virtuemart_manufacturer_id > 0) $manufacturerLink .='<div><a title="" href="'.JRoute::_($fieldLink.$orderTxt.$orderbyTxt ) .'">'.JText::_('COM_VIRTUEMART_SEARCH_SELECT_ALL_MANUFACTURER').'</a></div>';
				if (count($manufacturers)>1) {
					foreach ($manufacturers as $mf) {
						$link = JRoute::_($fieldLink.'&virtuemart_manufacturer_id='.$mf->virtuemart_manufacturer_id.$orderTxt.$orderbyTxt ) ;
						if ($mf->virtuemart_manufacturer_id != $virtuemart_manufacturer_id) {
							$manufacturerLink .='<div><a title="'.$mf->mf_name.'" href="'.$link.'">'.$mf->mf_name.'</a></div>';
						}
						else $currentManufacturerLink ='<div class="activeOrder">'.$mf->mf_name.'</div>';
					}
				} elseif ($virtuemart_manufacturer_id > 0) $currentManufacturerLink ='<div class="title">'.JText::_('COM_VIRTUEMART_PRODUCT_DETAILS_MANUFACTURER_LBL').'</div><div class="activeOrder">'. $manufacturers[0]->mf_name.'</div>';
				else $currentManufacturerLink ='<div class="title">'.JText::_('COM_VIRTUEMART_PRODUCT_DETAILS_MANUFACTURER_LBL').'</div><div class="Order"> '.$manufacturers[0]->mf_name.'</div>';
				$manufacturerLink .='</div>';
			}
		// }
	}

	/* order by link list*/
	$orderByLink ='';
	$fields = VmConfig::get('browse_orderby_fields');
	if (count($fields)>1) {
		$orderByLink ='<div class="orderlist">';
		foreach ($fields as $field) {
			if ($field != $orderby) {

				$dotps = strrpos($field, '.');
				if($dotps!==false){
					$prefix = substr($field, 0,$dotps+1);
					$fieldWithoutPrefix = substr($field, $dotps+1);
					// 				vmdebug('Found dot '.$dotps.' $prefix '.$prefix.'  $fieldWithoutPrefix '.$fieldWithoutPrefix);
				} else {
					$prefix = '';
					$fieldWithoutPrefix = $field;
				}

				$text = JText::_('COM_VIRTUEMART_'.strtoupper($fieldWithoutPrefix)) ;

				if ($field == $orderbyCfg) $link = JRoute::_($fieldLink.$manufacturerTxt ) ;
				else $link = JRoute::_($fieldLink.$manufacturerTxt.'&orderby='.$field ) ;
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
	$link = JRoute::_($fieldLink.$orderlink.$orderbyTxt.$manufacturerTxt) ;

	$dotps = strrpos($orderby, '.');
	if($dotps!==false){
		$prefix = substr($orderby, 0,$dotps+1);
		$orderby = substr($orderby, $dotps+1);
		// 				vmdebug('Found dot '.$dotps.' $prefix '.$prefix.'  $fieldWithoutPrefix '.$fieldWithoutPrefix);
	} else {
		$prefix = '';
		// 		$orderby = $orderby;
	}

	$orderByList ='<div class="orderlistcontainer"><div class="title">'.JText::_('COM_VIRTUEMART_ORDERBY').'</div><div class="activeOrder"><a title="'.$orderTxt.'" href="'.$link.'">'.JText::_('COM_VIRTUEMART_SEARCH_ORDER_'.$orderby).' '.$orderTxt.'</a></div>';
	$orderByList .= $orderByLink.'</div>';

	$manuList ='';
	if(VmConfig::get('show_manufacturers')){
		if (empty ($currentManufacturerLink) ) $currentManufacturerLink = '<div class="title">'.JText::_('COM_VIRTUEMART_PRODUCT_DETAILS_MANUFACTURER_LBL').'</div><div class="activeOrder">'.JText::_('COM_VIRTUEMART_SEARCH_SELECT_MANUFACTURER').'</div>';
		$manuList =' <div class="orderlistcontainer">'.$currentManufacturerLink;
		$manuList .= $manufacturerLink.'</div><div class="clear"></div>';

	}

	return array('orderby'=>$orderByList,'manufacturer'=>$manuList) ;
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



public function updateStockInDB($product, $amount, $signInStoc, $signOrderedStock){
// 	vmdebug( 'stockupdate in DB', $product->virtuemart_product_id,$amount, $signInStoc, $signOrderedStock );
	$validFields = array('=','+','-');
	if(!in_array($signInStoc,$validFields)){
		return false;
	}
	if(!in_array($signOrderedStock,$validFields)){
		return false;
	}
	//sanitize fields
	$id = (int) $product->virtuemart_product_id;
	$amount = (float) $amount;
	$update = array();

	if($signInStoc != '=' || $signOrderedStock != '='){

		if($signInStoc!='='){
			$update[] = '`product_in_stock` = `product_in_stock` ' . $signInStoc . $amount ;

			if(strpos($signInStoc,'+')!==false){
				$signInStoc = '-';
			} else {
				$signInStoc = '+';
			}
			$update[] = '`product_sales` = `product_sales` ' . $signInStoc . $amount ;

		}
		if($signOrderedStock!='='){
			$update[] = '`product_ordered` = `product_ordered` ' . $signOrderedStock . $amount ;
		}
		$q = 'UPDATE `#__virtuemart_products` SET '. implode(", ", $update  ) . ' WHERE `virtuemart_product_id` = ' . $id;

		$this->_db->setQuery($q);
		$this->_db->query();

		if ($signInStoc == '-') {
			$this->_db->setQuery('SELECT `product_in_stock` < `low_stock_notification` '
			. 'FROM `#__virtuemart_products` '
			. 'WHERE `virtuemart_product_id` = ' . $id
			);
			if ($this->_db->loadResult() == 1) {

				// TODO Generate low stock warning
			}
		}
	}


}


public function getUncategorizedChildren($selected){

		$q = 'SELECT * FROM `#__virtuemart_products` as p
			LEFT JOIN `#__virtuemart_products_'.VMLANG.'` as pl
			USING (`virtuemart_product_id`)
			LEFT JOIN `#__virtuemart_product_categories` as pc
			USING (`virtuemart_product_id`) ';

// 		$q .= ' WHERE (`product_parent_id` = "'.$this->_id.'" AND (pc.`virtuemart_category_id`) IS NULL  ) OR (`virtuemart_product_id` = "'.$this->_id.'" ) ';
		$q .= ' WHERE `product_parent_id` = "'.$this->_id.'"  OR `virtuemart_product_id` = "'.$this->_id.'" ';


	$app = JFactory::getApplication();
	if($app->isSite() && !VmConfig::get('use_as_catalog',0) && VmConfig::get('stockhandle','none')=='disableit' ){
			$q .= ' AND p.`product_in_stock`>"0" ';
	}

	if($app->isSite()){
		$q .= ' AND p.`published`="1"';
	}

	$q .= 'GROUP BY `virtuemart_product_id` ORDER BY ordering DESC';
	$this->_db->setQuery($q);
	$res = $this->_db->loadAssocList() ;
	$err = $this->_db->getErrorMsg();
	if(!empty($err)){
		vmError('getUncategorizedChildren sql error '.$err,'getUncategorizedChildren sql error');
		vmdebug('getUncategorizedChildren '.$err);
		return false;
	} else {
// 		vmdebug('getUncategorizedChildren '.$this->_db->getQuery());
		return $res;
	}

}

/**
* Check if the product has any children
*
* @author RolandD
* @author Max Milbers
* @param int $virtuemart_product_id Product ID
* @return bool True if there are child products, false if there are no child products
*/
public function checkChildProducts($virtuemart_product_id) {

	$q  = 'SELECT IF(COUNT(virtuemart_product_id) > 0, "0", "1") FROM `#__virtuemart_products` WHERE `product_parent_id` = "'.(int)$virtuemart_product_id.'"';
	$this->_db->setQuery($q);

	return $this->_db->loadResult();

}

// use lang table only TODO Look if this not cause errors
function getProductChilds($product_id ) {
	if(empty($product_id)) return array();
	$db = JFactory::getDBO();
	$db->setQuery(' SELECT virtuemart_product_id, product_name FROM `#__virtuemart_products_'.VMLANG.'`
			JOIN `#__virtuemart_products` as C using (`virtuemart_product_id`)
			WHERE `product_parent_id` ='.(int)$product_id);
	return $db->loadObjectList();

}

function getProductChildIds($product_id ) {

	if(empty($product_id)) return array();
	$db = JFactory::getDBO();
	$db->setQuery(' SELECT virtuemart_product_id FROM `#__virtuemart_products` WHERE `product_parent_id` ='.(int)$product_id);

	return $db->loadResultArray();

}
// use lang table only TODO Look if this not cause errors
function getProductParent($product_parent_id) {
	if(empty($product_parent_id)) return array();
	$product_parent_id = (int) $product_parent_id;
	$db = JFactory::getDBO();
	$db->setQuery(' SELECT * FROM `#__virtuemart_products_'.VMLANG.'` WHERE `virtuemart_product_id` ='.$product_parent_id);
	return $db->loadObject();
}



	function sentProductEmailToShoppers() {

		$cids = JRequest::getVar('virtuemart_product_id', JRequest::getVar('virtuemart_product_id',array(),'', 'ARRAY'), '', 'ARRAY');
		jimport( 'joomla.utilities.arrayhelper' );
		JArrayHelper::toInteger($cids);
		vmdebug('sentProductEmailToShoppers product id',$cids);
		$modelOrder = VmModel::getModel('orders');
		$app = JFactory::getApplication();
		foreach($cids as $cid){
			$orders= $this->getOrdersbyProductId($cid);
			vmdebug('$orders ',$orders);
			$vars = array();
			$productModel = VmModel::getModel('product');
			$vars['product'] = $productModel->getProduct($cid);

			$vendorModel = VmModel::getModel('vendor');
			$VendorEmail = $vendorModel->getVendorEmail($vars['product']->virtuemart_vendor_id);
			$virtuemart_vendor_id=1;
			$vendor = $vendorModel->getVendorAdressBT($virtuemart_vendor_id);
			$vendorModel->addImages($vendor);

			$vars['vendor'] = array('vendor_store_name' => $fromName );
			foreach($orders as $order){
				$vars['user'] = array('name' => $fromName, 'email' => $VendorEmail);
				if (shopFunctionsF::renderMail('productemailshoppers', $order->email, $vars,'productdetails',false)) {
					$string = 'COM_VIRTUEMART_MAIL_SEND_SUCCESSFULLY';
				} else {
					$string = 'COM_VIRTUEMART_MAIL_NOT_SEND_SUCCESSFULLY';
				}
				/* Update the product history */
				$modelOrder->_updateOrderHist($order->virtuemart_order_id,  $order->order_status_code, 1, $input['comments']);
				$app->enqueueMessage(JText::sprintf($string, $order->email));

			}
		}

	}

	function getOrdersbyProductId($virtuemart_product_id) {
	    $db = JFactory::getDbo();
	    $q = 'SELECT `ou`.`title`,`ou`.`company`,`ou`.`last_name`,`ou`.`first_name`,`ou`.`email`, `oi`.`virtuemart_order_id`,  `s`.`order_status_code`
					FROM `#__virtuemart_order_items` as `oi`
					LEFT JOIN `#__virtuemart_orders` as `o` ON `o`.`virtuemart_order_id` = `oi`.`virtuemart_order_id`
					LEFT JOIN `#__virtuemart_orderstates` as `s` ON `s`.`order_status_code` = `oi`.`order_status`
					LEFT JOIN `#__virtuemart_order_userinfos` as `ou` ON `o`.`virtuemart_order_id` = `ou`.`virtuemart_order_id`
					WHERE `oi`.`virtuemart_product_id` = "'.(int)$virtuemart_product_id.'" AND `address_type` = "BT" GROUP BY `ou`.email';

			$db->setQuery($q);

			$orders = $db->loadAssocList();
			$err = $db->getErrorMsg();
			if(!empty($err)){
				vmError('sentProductEmailToShoppers '.$err);
			}
			return $orders;
	}
}
// No closing tag