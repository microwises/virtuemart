<?php
if(  !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @package VirtueMart
* @Author Kohl Patrick
* @subpackage router
* @copyright Copyright (C) 2010 Kohl Patrick - Virtuemart Team - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.net
*/


function virtuemartBuildRoute(&$query) {

	$segments = array();

	$helper =& vmrouterHelper::getInstance($query);
	/* simple route , no work , for very slow server or test purpose */
	if ($helper->router_disabled) {
		foreach ($query as $key => $value){
			if  ($key != 'option')  {
				if ($key != 'Itemid') {
					$segments[]=$key.'/'.$value;
					unset($query[$key]);
				}
			}

		}
		return $segments;
	}

		if ($helper->edit) return $segments;

	/* Full route , heavy work*/
	$lang = &$helper->lang ;
	$view = '';

	$jmenu = & $helper->menu ;
	if (empty($query['Itemid'])) $query['Itemid'] = $jmenu['virtuemart'];
	if(isset($query['langswitch'])) unset($query['langswitch']);

	if(isset($query['view'])){
		$view = $query['view'];
		unset($query['view']);
	}
	switch ($view) {
		case 'virtuemart';
			$query['Itemid'] = $jmenu['virtuemart'] ;
			break;
		/* Shop category or virtuemart view
		 All ideas are wellcome to improve this
		 because is the biggest and more used */
		case 'category';
			 $start = null;
			if 	( isset($query['limitstart'] ) ) {
				$start = $query['limitstart'] ;

			}
			if ( isset($query['start'] ) ) {
				$start = $query['start'] ;
				unset($query['start']);
			}
			if ($start) {
				$segments[] = $lang['page'] ;
				$mainframe = Jfactory::getApplication(); ;
				$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', VmConfig::get('list_limit', 20), 'int');

				//Pagination changed, maybe the +1 is wrong note by Max Milbers
					$segments[] = floor($start/$limit);
			}
			if ( isset($query['orderby']) ) {

				// $dotps = strrpos($query['orderby'], '.');
				// if($dotps!==false){
					// $prefix = substr($query['orderby'], 0,$dotps).'_';
					// $fieldWithoutPrefix = substr($query['orderby'], $dotps+1);
									//vmdebug('Found dot '.$dotps.' $prefix '.$prefix.'  $fieldWithoutPrefix '.$fieldWithoutPrefix);
				// } else {
					// $prefix = '';
					// $fieldWithoutPrefix = $query['orderby'];
				// }
				$segments[] = $lang['orderby'].','.isset($lang[ $query['orderby'] ]) ? $lang[ $query['orderby'] ] : $query['orderby'] ;
				unset($query['orderby']);
			}
			if ( isset($query['order']) ) {
				if ($query['order'] =='DESC') $segments[] = $lang['orderDesc'] ;
				unset($query['orderDesc']);
			}
			if ( isset($query['virtuemart_manufacturer_id'])  ) {
				$segments[] = $lang['manufacturer'].'/'.$helper->getManufacturerName($query['virtuemart_manufacturer_id']) ;
				unset($query['virtuemart_manufacturer_id']);

			}
			if ( isset($query['search'])  ) {
				$segments[] = $lang['search'] ;
				unset($query['search']);
			}
			if ( isset($query['keyword'] )) {
				$segments[] = $query['keyword'];
				unset($query['keyword']);
			}
			if ( isset($query['virtuemart_category_id']) ) {
				if (isset($jmenu['virtuemart_category_id'][ $query['virtuemart_category_id'] ] ) )
					$query['Itemid'] = $jmenu['virtuemart_category_id'][$query['virtuemart_category_id']];
				else {
					$categoryRoute = $helper->getCategoryRoute($query['virtuemart_category_id']);
					if ($categoryRoute->route) $segments[] = $categoryRoute->route;
					if ($categoryRoute->itemId) $query['Itemid'] = $categoryRoute->itemId;
				}
				unset($query['virtuemart_category_id']);
			}

		break;
		/* Shop product details view  */
		case 'productdetails';
			$virtuemart_product_id = false;
			if (isset($jmenu['virtuemart_product_id'][ $query['virtuemart_product_id'] ] ) ) {
				$query['Itemid'] = $jmenu['virtuemart_product_id'][$query['virtuemart_product_id']];
			} else {
				if(isset($query['virtuemart_product_id'])) {
					if ($helper->use_id) $segments[] = $query['virtuemart_product_id'];
					$virtuemart_product_id = $query['virtuemart_product_id'];
					unset($query['virtuemart_product_id']);
				}
				if(empty( $query['virtuemart_category_id'])){
					$query['virtuemart_category_id'] = $helper->getParentProductcategory($virtuemart_product_id);
				}
				if(!empty( $query['virtuemart_category_id'])){
					$categoryRoute = $helper->getCategoryRoute($query['virtuemart_category_id']);
					if ($categoryRoute->route) $segments[] = $categoryRoute->route;
					if ($categoryRoute->itemId) $query['Itemid'] = $categoryRoute->itemId;
				}
					unset($query['virtuemart_category_id']);

				if($virtuemart_product_id)
					$segments[] = $helper->getProductName($virtuemart_product_id);
			}
		break;
		case 'manufacturer';
			if ( isset($jmenu['manufacturer']) ) $query['Itemid'] = $jmenu['manufacturer'];
			if(isset($query['virtuemart_manufacturer_id'])) {
				if (isset($jmenu['virtuemart_manufacturer_id'][ $query['virtuemart_manufacturer_id'] ] ) )
				$query['Itemid'] = $jmenu['virtuemart_manufacturer_id'][$query['virtuemart_manufacturer_id']];
				else $segments[] = $lang['manufacturers'].'/'.$helper->getManufacturerName($query['virtuemart_manufacturer_id']) ;
				unset($query['virtuemart_manufacturer_id']);
			}
		break;
		case 'user';
			if ( isset($jmenu['user']) ) $query['Itemid'] = $jmenu['user'];
			else {
				$segments[] = $lang['user'] ;
			}
			if (isset($query['task'])) {
				if ($query['addrtype'] == 'BT' && $query['task']='editaddresscart') $segments[] = $lang['editaddresscartBT'] ;
				elseif ($query['addrtype'] == 'ST' && $query['task']='editaddresscart') $segments[] = $lang['editaddresscartST'] ;
				else $segments[] = $query['task'] ;
				unset ($query['task'] , $query['addrtype']);
			}
		break;
		case 'vendor';
			if ( isset($jmenu['vendor']) ) $query['Itemid'] = $jmenu['vendor'];
			else {
				$segments[] = $lang['vendor'] ;
			}
			if (isset($query['virtuemart_vendor_id'])) {
				$segments[] = $query['virtuemart_vendor_id'];
				unset ($query['virtuemart_vendor_id']);
			}
		break;
		case 'cart';
			if ( isset($jmenu['cart']) ) $query['Itemid'] = $jmenu['cart'];
			else {
				$segments[] = $lang['cart'] ;
			}

		break;
		case 'orders';
			if ( isset($jmenu['orders']) ) $query['Itemid'] = $jmenu['orders'];
			else {
				$segments[] = $lang['orders'] ;
			}
			if ( isset($query['order_number']) ) {
				$segments[] = $query['order_number'];
				unset ($query['order_number'],$query['layout']);
			} else if ( isset($query['virtuemart_order_id']) ) {
				$segments[] = $query['virtuemart_order_id'];
				unset ($query['virtuemart_order_id'],$query['layout']);
			}

			//else unset ($query['layout']);
		break;

		// sef only view
		default ;
		$segments[] = $view;


	}
	if (isset($query['task'])) {
		$segments[] = isset($lang[$query['task']]) ? $lang[$query['task']] : $query['task'] ;
		unset($query['task']);
	}
	if (isset($query['layout'])) {
		$segments[] = isset($lang[$query['layout']]) ? $lang[$query['layout']] : $query['layout'] ;
		unset($query['layout']);
	}
	// sef the slimbox View
	if (isset($query['tmpl'])) {
		if ( $query['tmpl'] = 'component') $segments[] = 'modal' ;
		unset($query['tmpl']);
	}

	return $segments;
}

/* This function can be slower because is used only one time  to find the real URL*/
function virtuemartParseRoute($segments) {

	$vars = array();
	$helper = vmrouterHelper::getInstance();
	if ($helper->router_disabled) {
		$total = count($segments);
		for ($i = 0; $i < $total; $i=$i+2) {
		$vars[ $segments[$i] ] = $segments[$i+1];
		}
		return $vars;
	}

	$lang = &$helper->lang ;
	// revert '-' (Joomla change - to :) //
	foreach  ($segments as &$value) {
		$value = str_replace(':', '-', $value);
	}

	if ($segments[0] == $lang['page']) {
		array_shift($segments);

		$mainframe = Jfactory::getApplication();
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', VmConfig::get('list_limit', 20), 'int');
		//Pagination has changed, removed the -1 note by Max Milbers NOTE: Works on j1.5, but NOT j1.7
			$vars['limitstart'] = (array_shift($segments)*$limit);

	} else 	if(JVM_VERSION === 2) {
		$vars['limitstart'] = 0 ;
	}


	$orderby = explode(',',$segments[0]);
	if ( $orderby[0] == $lang['orderby'] ) {
		$key = array_search($orderby[1],$lang );
		if ( $key ) {
			$vars['orderby'] =$key ;
			array_shift($segments);
		}
		if ( !empty($segments)) {
			if ( $segments[0] == $lang['orderDesc'] ) {
				$vars['order'] ='DESC' ;
				array_shift($segments);
			}
		}
		if (empty($segments)) {
			$vars['view'] = 'category';
			$vars['virtuemart_category_id'] = $helper->activeMenu->virtuemart_category_id ;
			return $vars;
		}
	}


	if ( $segments[0] == 'product') {
		$vars['view'] = 'product';
		$vars['task'] = $segments[1];
		$vars['tmpl'] = 'component';
		return $vars;
		}

	if ( $segments[0] == $lang['manufacturer']) {
		array_shift($segments);
		$vars['virtuemart_manufacturer_id'] =  $helper->getManufacturerId($segments[0]);
		array_shift($segments);
		$vars['search'] = 'true';
		if (empty($segments)) {
			$vars['view'] = 'category';
			$vars['virtuemart_category_id'] = $helper->activeMenu->virtuemart_category_id ;
			return $vars;
		}

	}
	if ($segments[0] == $lang['search']) {
		$vars['search'] = 'true';
		array_shift($segments);
		if ( !empty ($segments) ) {
			$vars['keyword'] = array_shift($segments);

		}
		$vars['view'] = 'category';
		$vars['virtuemart_category_id'] = $helper->activeMenu->virtuemart_category_id ;
		if (empty($segments)) return $vars;
	}
	if (end($segments) == 'modal') {
		$vars['tmpl'] = 'component';
		array_pop($segments);

	}
	if (end($segments) == $lang['askquestion']) {
		$vars['task'] = 'askquestion';
		array_pop($segments);

	} elseif (end($segments) == $lang['recommend']) {
		$vars['task'] = 'recommend';
		array_pop($segments);

	}

	if (empty($segments)) return $vars ;
	else $view = $segments[0];
	if ($view == $lang['orders'] || $helper->activeMenu->view == 'orders') {
		$vars['view'] = 'orders';
		if ($view == $lang['orders']) {
			array_shift($segments);

		}
		if (empty($segments)) {
			$vars['layout'] = 'list';
		}
		if ( !empty($segments) ) {
			$vars['order_number'] = $segments[0] ;
			$vars['layout'] = 'details';
		}
		return $vars;
	}
	else if ($view == $lang['user'] || $helper->activeMenu->view == 'user') {
		$vars['view'] = 'user';
		if ($view == $lang['user']) {
			array_shift($segments);
		}

		if ( !empty($segments) ) {
			if ( $segments[0] == $lang['editaddresscartBT'] ) {
				$vars['addrtype'] = 'BT' ;
				$vars['task'] = 'editaddresscart' ;
			}
			elseif ( $segments[0] == $lang['editaddresscartST'] ) {
				$vars['addrtype'] = 'ST' ;
				$vars['task'] = 'editaddresscart' ;
				} else $vars['task'] = $segments[0] ;
		}
		return $vars;
	}
	else if ($view == $lang['vendor'] || $helper->activeMenu->view == 'vendor') {
		$vars['view'] = 'vendor';
		if ($view == $lang['vendor']) {
			array_shift($segments);
			if (empty($segments)) return $vars;
		}
		$vars['virtuemart_vendor_id'] = array_shift($segments);
		if(!empty($segments)) {
		if ($segments[0] == $lang['contact'] ) $vars['layout'] = 'contact' ;
		elseif ($segments[0] == $lang['tos'] ) $vars['layout'] = 'tos' ;
		} else $vars['layout'] = 'details' ;

		return $vars;
	}
	else if ($view == $lang['cart'] || $helper->activeMenu->view == 'cart') {
		$vars['view'] = 'cart';
		if ($view == $lang['cart']) {
			array_shift($segments);
			if (empty($segments)) return $vars;
		}
		if ($segments[0] == $lang['edit_shipment'] ) $vars['task'] = 'edit_shipment' ;
		elseif ($segments[0] == $lang['editpayment'] ) $vars['task'] = 'editpayment' ;
		elseif ($segments[0] == $lang['delete'] ) $vars['task'] = 'delete' ;
		return $vars;
	}

	else if ($view == $lang['manufacturers'] || $helper->activeMenu->view == 'manufacturer') {
		$vars['view'] = 'manufacturer';

		if ($view == $lang['manufacturers'] ) {
			array_shift($segments);
		}

		if (!empty($segments) ) {
			$vars['virtuemart_manufacturer_id'] =  $helper->getManufacturerId($segments[0]);
			array_shift($segments);
		}
		if ( isset($segments[0]) && $segments[0] == 'modal') {
			$vars['tmpl'] = 'component';
			array_shift($segments);
		}
		// if (isset($helper->activeMenu->virtuemart_manufacturer_id))
			// $vars['virtuemart_manufacturer_id'] = $helper->activeMenu->virtuemart_manufacturer_id ;

		return $vars;
	}

	/*
	 * seo_sufix must never be used in category or router can't find it
	 * eg. suffix as "-suffix", a category with "name-suffix" get always a false return
	 * Trick : YOu can simply use "-p","-x","-" or ".htm" for better seo result if it's never in the product/category name !
	 */
	 if (substr(end($segments ), -(int)$helper->seo_sufix_size ) == $helper->seo_sufix ) {
		$vars['view'] = 'productdetails';
		if (!$helper->use_id ) {
			$product = $helper->getProductId($segments ,$helper->activeMenu->virtuemart_category_id);
			$vars['virtuemart_product_id'] = $product['virtuemart_product_id'];
			$vars['virtuemart_category_id'] = $product['virtuemart_category_id'];
		}
		elseif (ctype_digit ($segments[1])){
			$vars['virtuemart_product_id'] = $segments[0];
			$vars['virtuemart_category_id'] = $segments[1];
		} else {
			$vars['virtuemart_product_id'] = $segments[0];
			$vars['virtuemart_category_id'] = $helper->activeMenu->virtuemart_category_id ;
		}


	} elseif (!$helper->use_id && ($helper->activeMenu->view == 'category' ) )  {
		$vars['virtuemart_category_id'] = $helper->getCategoryId (end($segments) ,$helper->activeMenu->virtuemart_category_id);
		$vars['view'] = 'category' ;


	} elseif (isset($segments[0]) && ctype_digit ($segments[0]) || $helper->activeMenu->virtuemart_category_id>0 ) {
		$vars['virtuemart_category_id'] = $segments[0];
		$vars['view'] = 'category';


	} elseif ($helper->activeMenu->virtuemart_category_id >0 && $vars['view'] != 'productdetails') {
		$vars['virtuemart_category_id'] = $helper->activeMenu->virtuemart_category_id ;
		$vars['view'] = 'category';

	} elseif ($id = $helper->getCategoryId (end($segments) ,$helper->activeMenu->virtuemart_category_id )) {
		// find corresponding category . If not, segment 0 must be a view
		$vars['virtuemart_category_id'] = $id;
		$vars['view'] = 'category' ;
	} else {
		$vars['view'] = $segments[0] ;
		if ( isset($segments[1]) ) {
			$vars['task'] = $segments[1] ;
		}
	}



	return $vars;
}

class vmrouterHelper {

	/* language array */
	public $lang = null ;
	public $langTag = null ;
	public $query = array();
	/* Joomla menus ID object from com_virtuemart */
	public $menu = null ;

	/* Joomla active menu( itemId ) object */
	public $activeMenu = null ;
	public $menuVmitems = null;
	/*
	  * $use_id type boolean
	  * Use the Id's of categorie and product or not
	  */
	public $use_id = false ;
	/*
	  * $router_disabled type boolean
	  * true  = don't Use the router
	  */
	public $router_disabled = false ;

	/* instance of class */
	private static $_instances = array ();

	private static $_catRoute = array ();

	public $CategoryName = array();
	private $dbview = array('vendor' =>'vendor','category' =>'category','virtuemart' =>'virtuemart','productdetails' =>'product','cart' => 'cart','manufacturer' => 'manufacturer');

	private function __construct($instanceKey,$query) {

		if (!$this->router_disabled = VmConfig::get('seo_disabled', false)) {
			$this->setLangs($instanceKey);
			if ( JVM_VERSION===1 ) $this->setMenuItemId();
			else $this->setMenuItemIdJ17();
			$this->setActiveMenu();
			$this->use_id = VmConfig::get('seo_use_id', false);

			$this->seo_sufix = VmConfig::get('seo_sufix', '-detail');
			$this->seo_sufix_size = strlen($this->seo_sufix) ;
			$this->edit = ('edit' == JRequest::getCmd('task') );
			// if language switcher we must know the $query
			$this->query = $query;
		}

	}

	public static function getInstance(&$query = null) {

		if (!class_exists( 'VmConfig' )) {
			require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart'.DS.'helpers'.DS.'config.php');
			VmConfig::loadConfig();
		}

		if (isset($query['langswitch']) ) {
			if ($query['langswitch'] != VMLANG ) $instanceKey = $query['langswitch'] ;
			unset ($query['langswitch']);

		} else $instanceKey = VMLANG ;
		if (! array_key_exists ($instanceKey, self::$_instances)){
			self::$_instances[$instanceKey] = new vmrouterHelper ($instanceKey,$query);
		}
		return self::$_instances[$instanceKey];
	}

	/* multi language routing ? */
	public function setLangs($instanceKey){
		$langs = VmConfig::get('active_languages',false);
		if(count($langs)> 1) {
			if(!in_array($instanceKey, $langs)) {
				$this->vmlang = VMLANG ;
				$this->langTag = strtr(VMLANG,'_','-');
			} else {
				$this->vmlang = strtolower(strtr($instanceKey,'-','_'));
				$this->langTag= $instanceKey;
			}
		} else $this->vmlang = $this->langTag = VMLANG ;
		$this->setLang($instanceKey);
	}

	public function getCategoryRoute($virtuemart_category_id){

		$cache = JFactory::getCache('_virtuemart','');
		$key = $virtuemart_category_id. $this->vmlang ; // internal cache key
		if (!($CategoryRoute = $cache->get($key))) {
			$CategoryRoute = $this->getCategoryRouteNocache($virtuemart_category_id);
			$cache->store($CategoryRoute, $key);
		}
		return $CategoryRoute ;
	}
	/* Get Joomla menu item and the route for category */
	public function getCategoryRouteNocache($virtuemart_category_id){
		if (! array_key_exists ($virtuemart_category_id . $this->vmlang, self::$_catRoute)){
			$category = new stdClass();
			$category->route = '';
			$category->itemId = 0;
			$menuCatid = 0 ;
			$ismenu = false ;

			// control if category is joomla menu
			if (isset($this->menu['virtuemart_category_id'])) {
				if (isset( $this->menu['virtuemart_category_id'][$virtuemart_category_id])) {
					$ismenu = true;
					$category->itemId = $this->menu['virtuemart_category_id'][$virtuemart_category_id] ;
				} else {
					$CatParentIds = $this->getCategoryRecurse($virtuemart_category_id,0) ;
					/* control if parent categories are joomla menu */
					foreach ($CatParentIds as $CatParentId) {
						// No ? then find the parent menu categorie !
						if (isset( $this->menu['virtuemart_category_id'][$CatParentId]) ) {
							$category->itemId = $this->menu['virtuemart_category_id'][$CatParentId] ;
							$menuCatid = $CatParentId;
							break;
						}
					}
				}
			}
			if ($ismenu==false) {
				if ( $this->use_id ) $category->route = $virtuemart_category_id.'/';
				if (!isset ($this->CategoryName[$virtuemart_category_id])) {
					$this->CategoryName[$virtuemart_category_id] = $this->getCategoryNames($virtuemart_category_id, $menuCatid );
				}
				$category->route .= $this->CategoryName[$virtuemart_category_id] ;
				if ($menuCatid == 0  && $this->menu['virtuemart']) $category->itemId = $this->menu['virtuemart'] ;
			}
			self::$_catRoute[$virtuemart_category_id . $this->vmlang] = $category;
		}
		return self::$_catRoute[$virtuemart_category_id . $this->vmlang] ;
	}

	/*get url safe names of category and parents categories  */
	public function getCategoryNames($virtuemart_category_id,$catMenuId=0){

		$strings = array();
		$db = JFactory::getDBO();
		$parents_id = array_reverse($this->getCategoryRecurse($virtuemart_category_id,$catMenuId)) ;

		foreach ($parents_id as $id ) {
			$q = 'SELECT `slug` as name
					FROM  `#__virtuemart_categories_'.$this->vmlang.'`
					WHERE  `virtuemart_category_id`='.(int)$id;

			$db->setQuery($q);
			$strings[] = $db->loadResult();
		}

		if(function_exists('mb_strtolower')){
			return mb_strtolower(implode ('/', $strings ) );
		} else {
			return strtolower(implode ('/', $strings ) );
		}


	}
	/* Get parents of category*/
	public function getCategoryRecurse($virtuemart_category_id,$catMenuId,$first=true ) {
		static $idsArr = array();
		if ($first==true) $idsArr = array();

		$db			= JFactory::getDBO();
		$q = "SELECT `category_child_id` AS `child`, `category_parent_id` AS `parent`
				FROM  #__virtuemart_category_categories AS `xref`
				WHERE `xref`.`category_child_id`= ".(int)$virtuemart_category_id;
		$db->setQuery($q);
		$ids = $db->loadObject();
		if (isset ($ids->child)) {
			$idsArr[] = $ids->child;
			if($ids->parent != 0 and $catMenuId != $virtuemart_category_id and $catMenuId != $ids->parent) {
				$this->getCategoryRecurse($ids->parent,$catMenuId,false);
			}
		}
		return $idsArr ;
	}
	/* return id of categories
	 * $names are segments
	 * $virtuemart_category_ids is joomla menu virtuemart_category_id
	 */
	public function getCategoryId($slug,$virtuemart_category_id ){
		$db = JFactory::getDBO();
			$q = "SELECT `virtuemart_category_id`
				FROM  `#__virtuemart_categories_".$this->vmlang."`
				WHERE `slug` LIKE '".$db->getEscaped($slug)."' ";

			$db->setQuery($q);
			if (!$category_id = $db->loadResult()) {
				$category_id = $virtuemart_category_id;
			}

		return $category_id ;
	}

	/* Get URL safe Product name */
	public function getProductName($id){
		$db = JFactory::getDBO();
		$query = 'SELECT `slug` FROM `#__virtuemart_products_'.$this->vmlang.'`  ' .
		' WHERE `virtuemart_product_id` = ' . (int) $id;

		$db->setQuery($query);

		return $db->loadResult().$this->seo_sufix;
	}

	var $counter = 0;
	/* Get parent Product first found category ID */
	public function getParentProductcategory($id){

		$virtuemart_category_id = 0;
		$db			= JFactory::getDBO();
		$query = 'SELECT `product_parent_id` FROM `#__virtuemart_products`  ' .
			' WHERE `virtuemart_product_id` = ' . (int) $id;
		$db->setQuery($query);
		/* If product is child then get parent category ID*/
		if ($parent_id = $db->loadResult()) {
			$query = 'SELECT `virtuemart_category_id` FROM `#__virtuemart_product_categories`  ' .
				' WHERE `virtuemart_product_id` = ' . $parent_id;
			$db->setQuery($query);

			//When the child and parent id is the same, this creates a deadlock
			//add $counter, dont allow more then 10 levels
			if (!$virtuemart_category_id = $db->loadResult()){
				$this->counter++;
				if($this->counter<10){
					$this->getParentProductcategory($parent_id) ;
				}
			}

		}
		$this->counter = 0;
		return $virtuemart_category_id ;
	}


	/* get product and category ID */
	public function getProductId($names,$virtuemart_category_id = NULL ){
		$productName = array_pop($names);
		$productName =  substr($productName, 0, -(int)$this->seo_sufix_size );
		$product = array();
		$categoryName = end($names);

		$product['virtuemart_category_id'] = $this->getCategoryId($categoryName,$virtuemart_category_id ) ;
		$db = JFactory::getDBO();
		$q = 'SELECT `p`.`virtuemart_product_id`
			FROM `#__virtuemart_products_'.$this->vmlang.'` AS `p`
			LEFT JOIN `#__virtuemart_product_categories` AS `xref` ON `p`.`virtuemart_product_id` = `xref`.`virtuemart_product_id`
			WHERE `p`.`slug` LIKE "'.$db->getEscaped($productName).'" ';
		//$q .= "	AND `xref`.`virtuemart_category_id` = ".(int)$product['virtuemart_category_id'];
		$db->setQuery($q);
		$product['virtuemart_product_id'] = $db->loadResult();
		/* WARNING product name must be unique or you can't acces the product */

		return $product ;
	}

	/* Get URL safe Manufacturer name */
	public function getManufacturerName($virtuemart_manufacturer_id ){
		$db = JFactory::getDBO();
		$query = 'SELECT `slug` FROM `#__virtuemart_manufacturers_'.$this->vmlang.'` WHERE virtuemart_manufacturer_id='.(int)$virtuemart_manufacturer_id;
		$db->setQuery($query);

		return $db->loadResult();

	}

	/* Get Manufacturer id */
	public function getManufacturerId($slug ){
		$db = JFactory::getDBO();
		$query = "SELECT `virtuemart_manufacturer_id` FROM `#__virtuemart_manufacturers_".$this->vmlang."` WHERE `slug` LIKE '".$db->getEscaped($slug)."' ";
		$db->setQuery($query);

		return $db->loadResult();

	}

	/* Set $this-lang (Translator for language from virtuemart string) to load only once*/
	private function setLang($instanceKey){

		if ( VmConfig::get('seo_translate', false) ) {
			/* use translator */
			$lang = JFactory::getLanguage();
			$extension = 'com_virtuemart.sef';
			$base_dir = JPATH_SITE;
			$lang->load($extension, $base_dir);
			$this->lang = array(
				'tos' 				=> JText::_('COM_VIRTUEMART_SEF_TOS'),
				'vendor' 			=> JText::_('COM_VIRTUEMART_SEF_VENDOR'),
				'contact' 			=> JText::_('COM_VIRTUEMART_SEF_CONTACT'),
				'edit_shipment'		=> JText::_('COM_VIRTUEMART_SEF_EDITSHIPPING'),
				'manufacturer'		=> JText::_('COM_VIRTUEMART_SEF_MANUFACTURER'),
				'manufacturers'		=> JText::_('COM_VIRTUEMART_SEF_MANUFACTURERS'),
				'askquestion'		=> JText::_('COM_VIRTUEMART_SEF_ASKQUESTION'),
				'editpayment'		=> JText::_('COM_VIRTUEMART_SEF_EDITPAYMENT'),
				'user'				=> JText::_('COM_VIRTUEMART_SEF_USER'),
				'orders'			=> JText::_('COM_VIRTUEMART_SEF_ORDERS'),
				'list'				=> JText::_('COM_VIRTUEMART_SEF_LIST'),
				'cart'				=> JText::_('COM_VIRTUEMART_SEF_CART'),
				'delete'			=> JText::_('COM_VIRTUEMART_SEF_DELETE'),
				'confirm'			=> JText::_('COM_VIRTUEMART_SEF_CONFIRM'),
				'checkout'			=> JText::_('COM_VIRTUEMART_SEF_CHECKOUT'),
				'edit'				=> JText::_('COM_VIRTUEMART_SEF_EDIT'),
				'editaddresscartBT'	=> JText::_('COM_VIRTUEMART_SEF_EDITADRESSCART_BILL'),
				'editaddresscartST'	=> JText::_('COM_VIRTUEMART_SEF_EDITADRESSCART_SHIP'),
				'details'			=> JText::_('COM_VIRTUEMART_SEF_DETAILS'),
				'search'			=> JText::_('COM_VIRTUEMART_SEF_SEARCH'),
				'page'				=> JText::_('COM_VIRTUEMART_SEF_PAGE'),
				'orderDesc'			=> JText::_('COM_VIRTUEMART_SEF_ORDER_DESC'),
				'orderby'			=> JText::_('COM_VIRTUEMART_SEF_BY'),
				'p.virtuemart_product_id'=> JText::_('COM_VIRTUEMART_SEF_BY_PRODUCT_ID'),
				'product_sku'		=> JText::_('COM_VIRTUEMART_SEF_BY_PRODUCT_SKU'),
				'product_price'		=> JText::_('COM_VIRTUEMART_SEF_BY_PRODUCT_PRICE'),
				'category_name'		=> JText::_('COM_VIRTUEMART_SEF_BY_CATEGORY_NAME'),
				'category_description'=> JText::_('COM_VIRTUEMART_SEF_BY_CATEGORY_DESCRIPTION'),
				'mf_name' 			=> JText::_('COM_VIRTUEMART_SEF_BY_MF_NAME'),
				'product_s_desc'	=> JText::_('COM_VIRTUEMART_SEF_BY_PRODUCT_S_DESC'),
				'product_desc'		=> JText::_('COM_VIRTUEMART_SEF_BY_PRODUCT_DESC'),
				'product_weight'	=> JText::_('COM_VIRTUEMART_SEF_BY_PRODUCT_WEIGHT'),
				'product_weight_uom'=> JText::_('COM_VIRTUEMART_SEF_BY_PRODUCT_WEIGHT_UOM'),
				'product_length'	=> JText::_('COM_VIRTUEMART_SEF_BY_PRODUCT_LENGTH'),
				'product_width'		=> JText::_('COM_VIRTUEMART_SEF_BY_PRODUCT_WIDTH'),
				'product_height'	=> JText::_('COM_VIRTUEMART_SEF_BY_PRODUCT_HEIGHT'),
				'product_lwh_uom'	=> JText::_('COM_VIRTUEMART_SEF_BY_PRODUCT_LWH_UOM'),
				'product_in_stock'	=> JText::_('COM_VIRTUEMART_SEF_BY_PRODUCT_IN_STOCK'),
				'low_stock_notification'=> JText::_('COM_VIRTUEMART_SEF_BY_LOW_STOCK_NOTIFICATION'),
				'product_available_date'=> JText::_('COM_VIRTUEMART_SEF_BY_PRODUCT_AVAILABLE_DATE'),
				'product_availability'  => JText::_('COM_VIRTUEMART_SEF_BY_PRODUCT_AVAILABILITY'),
				'product_special'	=> JText::_('COM_VIRTUEMART_SEF_BY_PRODUCT_SPECIAL'),
				'created_on' 		=> JText::_('COM_VIRTUEMART_SEF_BY_MDATE'),
				// 'p.modified_on' 		=> JText::_('COM_VIRTUEMART_SEF_BY_MDATE'),
				'product_name'		=> JText::_('COM_VIRTUEMART_SEF_BY_PRODUCT_NAME'),
				'product_sales'		=> JText::_('COM_VIRTUEMART_SEF_BY_PRODUCT_SALES'),
				'product_unit'		=> JText::_('COM_VIRTUEMART_SEF_BY_PRODUCT_UNIT'),
				'product_packaging'	=> JText::_('COM_VIRTUEMART_SEF_BY_PRODUCT_PACKAGING'),
				'p.intnotes'			=> JText::_('COM_VIRTUEMART_SEF_BY_INTNOTES'),
				// 'metadesc'			=> JText::_('COM_VIRTUEMART_SEF_BY_METADESC'),
				// 'metakey'			=> JText::_('COM_VIRTUEMART_SEF_BY_METAKEY'),
				// 'metarobot'			=> JText::_('COM_VIRTUEMART_SEF_BY_METAROBOT'),
				// 'metaauthor'		=> JText::_('COM_VIRTUEMART_SEF_BY_METAAUTHOR'),
				'recommend'			=> JText::_('COM_VIRTUEMART_SEF_RECOMMEND')
			);
		} else {
			/* use default */
			$this->lang = array(
				'tos' => 'tos',
				'vendor' => 'vendor',
				'contact' => 'contact',
				'edit_shipment' => 'edit_shipment',
				'manufacturers' => 'manufacturers',
				'manufacturer' => 'manufacturer',
				'askquestion' => 'askquestion',
				'editpayment' => 'editpayment',
				'user' => 'user',
				'orders' => 'orders',
				'list' => 'list',
				'cart' => 'cart',
				'delete' => 'delete',
				'checkout' => 'checkout',
				'confirm' => 'confirm',
				'edit' => 'edit',
				'editaddresscartBT' => 'edit_cart_bill_to',
				'editaddresscartST' => 'edit_cart_ship_to',
				'details' => 'details',
				'search' => 'search',
				'page' => 'page',
				'orderDesc' => 'desc',
				'p.virtuemart_product_id' => 'id',
				'product_sku' => 'sku',
				'product_price' => 'price',
				'orderby' => 'by',
				'category_name' => 'category',
				'category_description' => 'category_description',
				'mf_name' => 'manufacturer',
				'product_s_desc' => 'short_desc',
				'product_desc' => 'desc',
				'product_weight' => 'weight',
				'product_weight_uom' => 'product_weight_uom',
				'product_length' => 'length',
				'product_width' => 'width',
				'product_height' => 'height',
				'product_lwh_uom' => 'product_lwh_uom',
				'product_in_stock' => 'stock',
				'low_stock_notification' => 'low_stock',
				'product_available_date' => 'available_date',
				'product_availability' => 'availability',
				'product_special' => 'product_special',
				'created_on' => 'created_on',
				// 'p.modified_on' => 'modified_on',
				'product_name' => 'name',
				'product_sales' => 'sales',
				'product_packaging' => 'packaging',
				'product_unit' => 'product_unit',
				'p.intnotes' => 'intnotes',
				// 'metadesc' => 'metadesc',
				// 'metakey' => 'metakey',
				// 'metarobot' => 'metarobot',
				// 'metaauthor' => 'metaauthor',
				'recommend' => 'recommend'
			);
		}
	}

	/* Set $this->menu with the Item ID from Joomla Menus */
	private function setMenuItemIdJ17(){

		$home 	= false ;
		$component	= JComponentHelper::getComponent('com_virtuemart');

		//else $items = $menus->getItems('component_id', $component->id);
		//get all vm menus

		$db			= JFactory::getDBO();
		$query = 'SELECT * FROM `#__menu`  where `link` like "index.php?option=com_virtuemart%" and client_id=0 and (language="*" or language="'.$this->langTag.'")'  ;
		$db->setQuery($query);
// 		vmdebug('setMenuItemIdJ17 q',$query);
		$this->menuVmitems= $db->loadObjectList();
		$homeid =0;
		if(empty($this->menuVmitems)){
			vmWarn(JText::_('COM_VIRTUEMART_ASSIGN_VM_TO_MENU'));
		} else {


			// Search  Virtuemart itemID in joomla menu
			foreach ($this->menuVmitems as $item)	{
				$linkToSplit= explode ('&',$item->link);

				$link =array();
				foreach ($linkToSplit as $tosplit) {
					$splitpos = strpos($tosplit, '=');
					$link[ (substr($tosplit, 0, $splitpos) ) ] = substr($tosplit, $splitpos+1);
				}
				//vmDebug('menu view link',$link);

				//This is fix to prevent entries in the errorlog.
				if(!empty($link['view'])){
					$view = $link['view'] ;
					if (array_key_exists($view,$this->dbview) ){
						$dbKey = $this->dbview[$view];
					}
					else {
						$dbKey = false ;
					}

					if ( isset($link['virtuemart_'.$dbKey.'_id']) && $dbKey ){
						$this->menu['virtuemart_'.$dbKey.'_id'][ $link['virtuemart_'.$dbKey.'_id'] ] = $item->id;
					}
					elseif ($home == $view ) continue;
					else $this->menu[$view]= $item->id ;

					if ($item->home === 1) {
						$home = $view;
						$homeid = $item->id;
					}
				} else {
					vmError('$link["view"] is empty');
				}

			}
		}



		// init unsetted views  to defaut front view or nothing(prevent duplicates routes)
		if ( !isset( $this->menu['virtuemart']) ) {
			if (isset ($this->menu['virtuemart_category_id'][0]) ) {
				$this->menu['virtuemart'] = $this->menu['virtuemart_category_id'][0] ;
			}else $this->menu['virtuemart'] = $homeid;
		}
		// if ( !isset( $this->menu['manufacturer']) ) {
			// $this->menu['manufacturer'] = $this->menu['virtuemart'] ;
		// }
		// if ( !isset( $this->menu['vendor']) ) {
			// $this->menu['manufacturer'] = $this->menu['virtuemart'] ;
		// }

	}

	/* Set $this->menu with the Item ID from Joomla Menus */
	private function setMenuItemId(){

		$app		= JFactory::getApplication();
		$menus		= $app->getMenu('site');
		$component	= JComponentHelper::getComponent('com_virtuemart');
		$items = $menus->getItems('componentid', $component->id);

		if(empty($items)){
			vmWarn(JText::_('COM_VIRTUEMART_ASSIGN_VM_TO_MENU'));
		} else {
			// Search  Virtuemart itemID in joomla menu
			foreach ($items as $item)	{
				$view = $item->query['view'] ;
				if ($view=='virtuemart') $this->menu['virtuemart'] = $item->id;
				$dbKey = $this->dbview[$view];
				if ( isset($item->query['virtuemart_'.$dbKey.'_id']) )
				$this->menu['virtuemart_'.$dbKey.'_id'][ $item->query['virtuemart_'.$dbKey.'_id'] ] = $item->id;
				else $this->menu[$view]= $item->id ;
			}
		}

		// init unsetted views  to defaut front view or nothing(prevent duplicates routes)
		if ( !isset( $this->menu['virtuemart'][0]) ) {
			$this->menu['virtuemart'][0] = null;
		}
		if ( !isset( $this->menu['manufacturer']) ) {
			$this->menu['manufacturer'] = $this->menu['virtuemart'][0] ;
		}

	}
	/* Set $this->activeMenu to current Item ID from Joomla Menus */
	private function setActiveMenu(){

		$menu = &JSite::getMenu();
		if ($Itemid = JRequest::getInt('Itemid',0) ) {
			$menuItem = &$menu->getItem($Itemid);
		} else {
			$menuItem = &$menu->getActive();
		}

		$this->activeMenu->view			= (empty($menuItem->query['view'])) ? null : $menuItem->query['view'];
		$this->activeMenu->virtuemart_category_id	= (empty($menuItem->query['virtuemart_category_id'])) ? 0 : $menuItem->query['virtuemart_category_id'];
		$this->activeMenu->virtuemart_product_id	= (empty($menuItem->query['virtuemart_product_id'])) ? null : $menuItem->query['virtuemart_product_id'];
		$this->activeMenu->virtuemart_manufacturer_id	= (empty($menuItem->query['virtuemart_manufacturer_id'])) ? null : $menuItem->query['virtuemart_manufacturer_id'];
		$this->activeMenu->Component	= (empty($menuItem->component)) ? null : $menuItem->component;

	}

}

// pure php no closing tag