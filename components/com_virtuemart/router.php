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
	$helper = vmrouterHelper::getInstance();
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
	/* Full route , heavy work*/
	$lang = &$helper->lang ;

	$view = '';


	$menuView		= $helper->activeMenu->view;
	$menuCatid		= $helper->activeMenu->category_id;
	$menuProdId		= $helper->activeMenu->product_id;
	$menuComponent	= $helper->activeMenu->Component;


	if(isset($query['view'])){
		$view = $query['view'];
		unset($query['view']);
	}
	switch ($view) {
		case 'virtuemart';
			unset($query['view']);
		/* Shop category or virtuemart view
		 All ideas are wellcome to improve this
		 because is the biggest and more used */
		case 'category';
			if ( isset($query['start'] )) {
				$segments[] = $lang['page'] ;
				$mainframe = Jfactory::getApplication(); ;
				$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
				$segments[] = floor($query['start']/$limit)+1;
				unset($query['start']);
			}
			if ( isset($query['orderby']) ) {
				$segments[] = $lang['orderby'].','.$lang[ $query['orderby'] ] ;
				unset($query['orderby']);
			}			if ( isset($query['order']) ) {
				if ($query['order'] =='DESC') $segments[] = $lang['orderDesc'] ;
				unset($query['order']);
			}
			if ( isset($query['manufacturer_id'])  ) {
				$segments[] = $lang['manufacturer'].'/'.$query['manufacturer_id'].'/'.$helper->getManufacturerName($query['manufacturer_id']) ;
				unset($query['manufacturer_id']);
			}
			if ( isset($query['search'])  ) {
				$segments[] = $lang['search'] ;
				unset($query['search']);
			}
			if ( isset($query['keyword'] )) {
				$segments[] = $query['keyword'];
				unset($query['keyword']);
			}
			if ( isset($query['category_id']) ) {
				if ( $query['category_id']>0 || $menuCatid != $query['category_id'] ){
					$categoryRoute = $helper->getCategoryRoute($query['category_id']);
					if ($categoryRoute->route) $segments[] = $categoryRoute->route;
					if ($categoryRoute->itemId) $query['Itemid'] = $categoryRoute->itemId;
					unset($query['category_id']);
				}
			} else {
				if (isset ($helper->menu->no_category_id))$query['Itemid'] = $helper->menu->no_category_id;
				elseif (isset ($helper->menu->virtuemart))$query['Itemid'] = $helper->menu->virtuemart[0]['itemId'] ;
				unset($query['category_id']);
			}


		break;
		/* Shop product details view  */
		case 'productdetails';
			$product_id_exists = false;
			$menuCatid = 0 ;
			if(isset($query['product_id'])) {
				if ($helper->use_id) $segments[] = $query['product_id'];
				$product_id_exists = true;
				$product_id = $query['product_id'];
				unset($query['product_id']);
			}
			if(!empty( $query['category_id'])){
				$categoryRoute = $helper->getCategoryRoute($query['category_id']);
				if ($categoryRoute->route) $segments[] = $categoryRoute->route;
				if ($categoryRoute->itemId) $query['Itemid'] = $categoryRoute->itemId;
				unset($query['category_id']);
			} else {
				if (isset ($helper->menu->no_category_id))$query['Itemid'] = $helper->menu->no_category_id;
				elseif (isset ($helper->menu->virtuemart))$query['Itemid'] = $helper->menu->virtuemart[0]['itemId'] ;
				unset($query['category_id']);
			}
			if($product_id_exists)	{
				$segments[] = $helper->getProductName($product_id);
			}
		break;
		case 'manufacturer';
			if ( isset($helper->menu->manufacturer_id) ) $query['Itemid'] = $helper->menu->manufacturer_id;
			else $segments[] = $lang['manufacturer'];
			if(isset($query['manufacturer_id'])) {
				$segments[] = $query['manufacturer_id'].'/'.$helper->getManufacturerName($query['manufacturer_id']) ;
				unset($query['manufacturer_id']);
			}
		break;
		case 'user';
			if ( isset($helper->menu->user_id) ) $query['Itemid'] = $helper->menu->user_id;
			else $segments[] = $lang['user'] ;
			if (isset($query['task'])) {
				if ($query['addrtype'] == 'BT' && $query['task']='editaddresscart') $segments[] = $lang['editaddresscartBT'] ;
				elseif ($query['addrtype'] == 'ST' && $query['task']='editaddresscart') $segments[] = $lang['editaddresscartST'] ;
				else $segments[] = $query['task'] ;
				unset ($query['task'] , $query['addrtype']);
			}
		break;
		case 'cart';
			if ( isset($helper->menu->cart_id) ) $query['Itemid'] = $helper->menu->cart_id;
			else $segments[] = $lang['cart'] ;
			if (isset($query['task'])) {
				if ($query['task'] == 'editshipping') $segments[] = $lang['editshipping'] ;
				elseif ($query['task'] == 'editpayment') $segments[] = $lang['editpayment'];
				unset($query['task']);
			}
		break;

		// sef only view
		default ;
		if ($helper->activeMenu->view != $view) $segments[] = $view;


	}
	// sef the task
	if (isset($query['task'])) {
		if ($query['task'] == 'askquestion') $segments[] = $lang['askquestion'];
		else $segments[] = $query['task'] ;
		unset($query['task']);
	}	// sef the slimbox View
	if (isset($query['tmpl'])) {
		if ( $query['tmpl'] = 'component') $segments[] = 'detail' ;
		unset($query['tmpl']);
	}
	if (empty ($query['Itemid']) && isset($helper->menu->virtuemart[0]['itemId'])) $query['Itemid'] = $helper->menu->virtuemart[0]['itemId'] ;

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

	$segments[0]=str_replace(":", "-",$segments[0]);
	//array_search('green', $array);
	if ($segments[0] == $lang['page']) {
		array_shift($segments);

		$mainframe = Jfactory::getApplication();
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$vars['limitstart'] = (array_shift($segments)*$limit)-1;
		if (empty($segments)) return $vars;
	}
	$orderby = explode(',',$segments[0]);
	if ( $orderby[0] == $lang['orderby'] ) {
		$key = array_search($orderby[1],$lang );
		if ( $key ) {
			$vars['orderby'] =$key ;
			array_shift($segments);
		}
		if (empty($segments)) {
			$vars['view'] = 'category';
			$vars['category_id'] = $helper->activeMenu->category_id ;
			return $vars;
		}
	}
	if ( $segments[0] == $lang['orderDesc'] ) {
		$vars['order'] ='DESC' ;
		array_shift($segments);
		if (empty($segments)) {
			$vars['view'] = 'category';
			$vars['category_id'] = $helper->activeMenu->category_id ;
			return $vars;
		}
	}

	if ( $segments[0] == $lang['manufacturer']) {
		array_shift($segments);
		$vars['manufacturer_id'] = $segments[0];
		array_shift($segments);
		array_shift($segments);
		if (empty($segments)) {
			$vars['view'] = 'category';
			$vars['category_id'] = $helper->activeMenu->category_id ;
			return $vars;
		}
	}
	if ($segments[0] == $lang['search']) {
		$vars['search'] = 'true';
		array_shift($segments);
		if ( isset ($segments[0]) ) {
			$vars['keyword'] = array_shift($segments);

		}
		$vars['view'] = 'category';
		$vars['category_id'] = $helper->activeMenu->category_id ;
		if (empty($segments)) return $vars;
	}
	$count = count($segments)-1;

	if ($segments[$count] == 'detail') {
		$vars['tmpl'] = 'component';
		array_pop($segments);
		$count--;
	}
	if ($segments[$count] == $lang['askquestion']) {
		$vars['task'] = 'askquestion';
		array_pop($segments);
		$count--;
	}
	if ($segments[0] == $lang['user'] || $helper->activeMenu->view == 'user') {
		$vars['view'] = 'user';
		if ($segments[0] == $lang['user']) {
			array_shift($segments);
			$count--;
		}
		if ( isset($segments[0]) ) {
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
	if ($segments[0] == $lang['cart'] || $helper->activeMenu->view == 'cart') {
		$vars['view'] = 'cart';
		if ($segments[0] == $lang['cart']) {
			array_shift($segments);
			if (empty($segments)) return $vars;
		}
		if ($segments[0] == $lang['editshipping'] ) $vars['task'] = 'editshipping' ;
		elseif ($segments[0] == $lang['editpayment'] ) $vars['task'] = 'editpayment' ;
		return $vars;
	}


	if ($segments[0] == $lang['manufacturers'] || $helper->activeMenu->view == 'manufacturer') {
		$vars['view'] = 'manufacturer';
		if ($segments[0] == $lang['manufacturers'] ) {
			array_shift($segments);
		}
		if (isset($segments[0])  && ctype_digit ($segments[0])) {
			$vars['manufacturer_id'] = $segments[0];
			array_shift($segments);
		}

		return $vars;
	}

	/*
	 * uppercase first (trick for product details )
	 * Product must begin with A-Z
	 */
	$ascii = ord ( $segments[$count] );

	if ($ascii >65 && $ascii<90  ){
		$vars['view'] = 'productdetails';
		if (!$helper->use_id && ($helper->activeMenu->view == 'category' || ($helper->activeMenu->view == 'virtuemart') ) ) {
			$product = $helper->getProductId ($segments ,$helper->activeMenu->category_id);
			$vars['product_id'] = $product['product_id'];
			$vars['category_id'] = $product['category_id'];
		}
		elseif (ctype_digit ($segments[1])){
			$vars['product_id'] = $segments[0];
			$vars['category_id'] = $segments[1];
		} else {
			$vars['product_id'] = $segments[0];
			$vars['category_id'] = $helper->activeMenu->category_id ;
		}
		return $vars;

	} elseif (!$helper->use_id && ($helper->activeMenu->view == 'category' || ($helper->activeMenu->view == 'virtuemart') ) ) {
		$vars['category_id'] = $helper->getCategoryId ($segments ,$helper->activeMenu->category_id);
		$vars['view'] = 'category' ;
		return $vars;

	} elseif (isset($segments[0]) && ctype_digit ($segments[0]) || $helper->activeMenu->category_id>0 ) {
		$vars['category_id'] = $segments[0];
		$vars['view'] = 'category';
		return $vars;

	} elseif ($helper->activeMenu->category_id >0 && $vars['view'] != 'productdetails') {
		$vars['category_id'] = $helper->activeMenu->category_id ;
		$vars['view'] = 'category';
		return $vars;
	}

	// whe have more vars ?
	$vars['view'] = $segments[0] ;
	if ( isset($segments[1]) ) {
		$vars['task'] = $segments[1] ;
	}

	return $vars;
}

class vmrouterHelper {

	/* language array */
	public $lang = null ;

	/* Joomla menus ID object from com_virtuemart */
	public $menu = null ;

	/* Joomla active menu( itemId ) object */
	public $activeMenu = null ;

	/*
	  * $use_id type boolean
	  * Use the Id's of categorie and product or not
	  */
	public $use_id = false ;
	/*
	  * $use_id type boolean
	  * true  = don't Use the router
	  */
	public $router_disabled = false ;

	/* instance of class */
	private static $_instance = null;

	public $CategoryName = array();


	private function __construct() {

		if (!class_exists( 'VmConfig' )) require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart'.DS.'helpers'.DS.'config.php');
		self::setLang();
		self::setMenuItemId();
		self::setActiveMenu();
		$this->use_id = VmConfig::get('seo_use_id', false);
		$this->router_disabled = VmConfig::get('seo_disabled', false) ;
	}

	public static function getInstance() {
		if(is_null(self::$_instance)) {
			self::$_instance = new vmrouterHelper();
		}
		return self::$_instance;
	}

	/* Get Joomla menu item and the route for category */
	public function getCategoryRoute($category_id){
		$category = new stdClass();
		$category->route = '';
		$category->itemId = 0;
		$menuCatid = 0 ;
		$ismenu = false ;
		$CatParentIds = self::getCategoryRecurse($category_id,0) ;
		// control if category is joomla menu
		if (isset($this->menu->category_id)) {
			foreach ($this->menu->category_id as $menuId) {
				if ($category_id ==  $menuId['category_id']) {
					$ismenu = true;
					$category->itemId = $menuId['itemId'] ;
					break;
				}
				/* control if parent categories are joomla menu */
				foreach ($CatParentIds as $CatParentId) {
					// No ? then find te parent menu categorie !
					if ($menuId['category_id'] == $CatParentId ) {
						$category->itemId = $menuId['itemId'] ;
						$menuCatid = $CatParentId;
					}
				}
			}
		}
		if ($ismenu==false) {
			if ( $this->use_id ) $category->route = $category_id.'/';
			if (!isset ($this->CategoryName[$category_id])) {
				$this->CategoryName[$category_id] = self::getCategoryNames($category_id, $menuCatid );
			}
			$category->route .= $this->CategoryName[$category_id] ;
			if ($menuCatid == 0  && isset($this->menu->virtuemart[0]['itemId'])) $category->itemId = $this->menu->virtuemart[0]['itemId'] ;
		}
		return $category ;
	}

	/*get url safe names of category and parents categories  */
	public function getCategoryNames($category_id,$catMenuId=0){

		$strings = array();
		$db = & JFactory::getDBO();
		$parents_id = array_reverse(self::getCategoryRecurse($category_id,$catMenuId)) ;

		foreach ($parents_id as $id ) {
			$q = "SELECT `category_name` as name
					FROM  `#__virtuemart_categories`
					WHERE  `category_id`=".$id;

			$db->setQuery($q);
			$category = $db->loadResult();
			$string  = trim($category);
			if ( ctype_digit($string) ){
				return $string ;
			}
			else {
				// accented chars converted
				$accents = '/&([A-Za-z]{1,2})(grave|acute|circ|cedil|uml|lig);/';
				$string_encoded = htmlentities($string,ENT_NOQUOTES,'UTF-8');
				$string = preg_replace($accents,'$1',$string_encoded);
				$string = str_replace('&micro', ' ', $string);
				// clean out the rest
				$replace = array('([\40])','/&nbsp/','/&amp/','/\//','([^a-zA-Z0-9-/])','/\-+/');
				$with = array('-','-','-','-','-','-');
				$string = preg_replace($replace,$with,$string);

			}
			$strings[] = $string;
		}

		return strtolower(implode ('/', $strings ) );

	}
	/* Get parents of category*/
	public function getCategoryRecurse($category_id,$catMenuId,$first=true ) {
		static $idsArr = array();
		if ($first==true) $idsArr = array();

		$db			= & JFactory::getDBO();
		$q = "SELECT `category_child_id` AS `child`, `category_parent_id` AS `parent`
				FROM  #__virtuemart_category_categories AS `xref`
				WHERE `xref`.`category_child_id`= ".$category_id;
		$db->setQuery($q);
		$ids = $db->loadObject();
		if (isset ($ids->child)) {
			$idsArr[] = $ids->child;
			if($ids->parent != 0 and $catMenuId != $category_id and $catMenuId != $ids->parent) {
				self::getCategoryRecurse($ids->parent,$catMenuId,false);
			}
		}
		return $idsArr ;
	}
	/* return id of categories
	 * $names are segments
	 * $category_ids is joomla menu category_id
	 */
	public function getCategoryId($names,$category_ids ){
		if ($category_ids == null) $category_ids = 0 ;
		$db = & JFactory::getDBO();
		foreach ($names as $name) {
			$name = str_replace('-', '%', $name);
			$name = str_replace(':', '%', $name);
			$q = "SELECT distinct `c`.`category_id`
				FROM  `#__virtuemart_categories` AS `c` , `#__virtuemart_category_categories` as `xref`";
			$q .=" WHERE `c`.`category_name` LIKE '".$name."' ";
			$q .=" AND `xref`.`category_child_id`=`c`.`category_id`";
			$q .=" AND `xref`.`category_parent_id` in (".$category_ids.") ";
			$db->setQuery($q);
			$result = $db->loadResultArray();
			$category_ids = implode(',',$result);
		}

		/* WARNING name in same category must be unique or you have more then 1 ID */
		return $category_ids ;
	}

	/* Get URL safe Product name */
	public function getProductName($id){

		$db			= & JFactory::getDBO();
		$query = 'SELECT `product_name` FROM `#__virtuemart_products`  ' .
		' WHERE `product_id` = ' . (int) $id;

		$db->setQuery($query);
		// gets product name of item
		$product_name = $db->loadResult();
			$string  = trim($product_name) ;
			if ( ctype_digit($string)){
				return $string ;
			}
			else {
				//$string = str_replace('�', ' ', $string);
				// accented chars converted
				$accents = '/&([A-Za-z]{1,2})(grave|acute|circ|cedil|uml|lig);/';
				$string_encoded = htmlentities($product_name,ENT_NOQUOTES,'UTF-8');
				$string = preg_replace($accents,'$1',$string_encoded);
				$string = str_replace('&micro', ' ', $string);

				// clean out the rest
				$replace = array('([\40])','/&nbsp/','/&amp/','/\//','([^a-zA-Z0-9-/])','/\-+/');
				$with = array('-','-','-','-','-','-');
				$string = preg_replace($replace,$with,$string);

			}
		return ucfirst($string);
	}
	/* get product and category ID */
	public function getProductId($names,$category_ids = NULL ){
		$productName = array_pop($names);
		$product = array();
		$product['category_id'] = self::getCategoryId($names,$category_ids ) ;
		$db = & JFactory::getDBO();
		$parentIds = array();
		//$category_ids = null;
		$productName = str_replace('-', '%', $productName);
		$productName = str_replace(':', '%', $productName);
		$q = "SELECT `p`.`product_id`
			FROM `#__virtuemart_products` AS `p`
			LEFT JOIN `#__virtuemart_product_categories` AS `xref` ON `p`.`product_id` = `xref`.`product_id`
			WHERE `p`.`product_name` LIKE '".$productName."'
			AND `xref`.`category_id` in (".$product['category_id'].") ";
		$db->setQuery($q);
		$product['product_id'] = $db->loadResult();
		/* WARNING product name must be unique or you can't acces the product */
		return $product ;
	}
	/* Get URL safe Manufacturer name */
	public function getManufacturerName($manufacturer_id ){
	$db = JFactory::getDBO();
	$query = 'SELECT `mf_name` FROM `#__virtuemart_manufacturers` WHERE manufacturer_id='.$manufacturer_id;
	$db->setQuery($query);
	$lang =& JFactory::getLanguage();
	$mfName = $lang->transliterate($db->loadResult());
	return preg_replace('([^a-zA-Z0-9-/])','-',$mfName);

	}
	/* Set $this-lang (Translator for language from virtuemart string) to load only once*/
	private function setLang(){

		if ( VmConfig::get('seo_translate', false) ) {
			/* use translator */
			$lang =& JFactory::getLanguage();
			$extension = 'com_virtuemart';
			$base_dir = JPATH_SITE;
			$lang->load($extension, $base_dir);
			$this->lang['editshipping'] 	= $lang->_('COM_VIRTUEMART_SEF_EDITSHIPPING');
			$this->lang['manufacturer'] 	= $lang->_('COM_VIRTUEMART_SEF_MANUFACTURER');
			$this->lang['manufacturers'] 	= $lang->_('COM_VIRTUEMART_SEF_MANUFACTURERS');
			$this->lang['askquestion']  	= $lang->_('COM_VIRTUEMART_SEF_ASKQUESTION');
			$this->lang['editpayment']  	= $lang->_('COM_VIRTUEMART_SEF_EDITPAYMENT');
			$this->lang['user'] 			= $lang->_('COM_VIRTUEMART_SEF_USER');
			$this->lang['cart'] 			= $lang->_('COM_VIRTUEMART_SEF_CART');
			$this->lang['editaddresscartBT']= $lang->_('COM_VIRTUEMART_SEF_EDITADRESSCART_BILL');
			$this->lang['editaddresscartST']= $lang->_('COM_VIRTUEMART_SEF_EDITADRESSCART_SHIP');
			$this->lang['search']			= $lang->_('COM_VIRTUEMART_SEF_SEARCH');
			$this->lang['page']				= $lang->_('COM_VIRTUEMART_SEF_PAGE');
			$this->lang['orderDesc']		= $lang->_('COM_VIRTUEMART_SEF_ORDER_DESC');
			$this->lang['orderby']			= $lang->_('COM_VIRTUEMART_SEF_BY');
			$this->lang['product_id'] 		= $lang->_('COM_VIRTUEMART_SEF_BY_PRODUCT_ID');
			$this->lang['product_sku'] 		= $lang->_('COM_VIRTUEMART_SEF_BY_PRODUCT_SKU');
			$this->lang['product_price']	= $lang->_('COM_VIRTUEMART_SEF_BY_PRODUCT_PRICE');
			$this->lang['category_name']	= $lang->_('COM_VIRTUEMART_SEF_BY_CATEGORY_NAME');
			$this->lang['category_description'] = $lang->_('COM_VIRTUEMART_SEF_BY_CATEGORY_DESCRIPTION');
			$this->lang['mf_name'] 			= $lang->_('COM_VIRTUEMART_SEF_BY_MF_NAME');
			$this->lang['product_s_desc'] 	= $lang->_('COM_VIRTUEMART_SEF_BY_PRODUCT_S_DESC');
			$this->lang['product_desc'] 	= $lang->_('COM_VIRTUEMART_SEF_BY_PRODUCT_DESC');
			$this->lang['product_weight'] 	= $lang->_('COM_VIRTUEMART_SEF_BY_PRODUCT_WEIGHT');
			$this->lang['product_weight_uom']= $lang->_('COM_VIRTUEMART_SEF_BY_PRODUCT_WEIGHT_UOM');
			$this->lang['product_length'] 	= $lang->_('COM_VIRTUEMART_SEF_BY_PRODUCT_LENGTH');
			$this->lang['product_width'] 	= $lang->_('COM_VIRTUEMART_SEF_BY_PRODUCT_WIDTH');
			$this->lang['product_height'] 	= $lang->_('COM_VIRTUEMART_SEF_BY_PRODUCT_HEIGHT');
			$this->lang['product_lwh_uom'] 	= $lang->_('COM_VIRTUEMART_SEF_BY_PRODUCT_LWH_UOM');
			$this->lang['product_in_stock'] = $lang->_('COM_VIRTUEMART_SEF_BY_PRODUCT_IN_STOCK');
			$this->lang['low_stock_notification'] = $lang->_('COM_VIRTUEMART_SEF_BY_LOW_STOCK_NOTIFICATION');
			$this->lang['product_available_date'] = $lang->_('COM_VIRTUEMART_SEF_BY_PRODUCT_AVAILABLE_DATE');
			$this->lang['product_availability']   = $lang->_('COM_VIRTUEMART_SEF_BY_PRODUCT_AVAILABILITY');
			$this->lang['product_special'] 	= $lang->_('COM_VIRTUEMART_SEF_BY_PRODUCT_SPECIAL');
			$this->lang['ship_code_id'] 	= $lang->_('COM_VIRTUEMART_SEF_BY_SHIP_CODE_ID');
			$this->lang['cdate'] 			= $lang->_('COM_VIRTUEMART_SEF_BY_CDATE');
			$this->lang['mdate'] 			= $lang->_('COM_VIRTUEMART_SEF_BY_MDATE');
			$this->lang['product_name'] 	= $lang->_('COM_VIRTUEMART_SEF_BY_PRODUCT_NAME');
			$this->lang['product_sales'] 	= $lang->_('COM_VIRTUEMART_SEF_BY_PRODUCT_SALES');
			$this->lang['product_unit'] 	= $lang->_('COM_VIRTUEMART_SEF_BY_PRODUCT_UNIT');
			$this->lang['product_packaging']= $lang->_('COM_VIRTUEMART_SEF_BY_PRODUCT_PACKAGING');
			$this->lang['product_order_levels']    = $lang->_('COM_VIRTUEMART_SEF_BY_PRODUCT_ORDER_LEVELS');
			$this->lang['intnotes'] 		= $lang->_('COM_VIRTUEMART_SEF_BY_INTNOTES');
			$this->lang['metadesc'] 		= $lang->_('COM_VIRTUEMART_SEF_BY_METADESC');
			$this->lang['metakey'] 			= $lang->_('COM_VIRTUEMART_SEF_BY_METAKEY');
			$this->lang['metarobot'] 		= $lang->_('COM_VIRTUEMART_SEF_BY_METAROBOT');
			$this->lang['metaauthor'] 		= $lang->_('COM_VIRTUEMART_SEF_BY_METAAUTHOR');


		} else {
			/* use default */
			$this->lang['editshipping'] = 'editshipping';
			$this->lang['manufacturers'] = 'manufacturers';
			$this->lang['manufacturer'] = 'manufacturer';
			$this->lang['askquestion']  = 'askquestion';
			$this->lang['editpayment']  = 'editpayment';
			$this->lang['user']			= 'user';
			$this->lang['cart']			= 'cart';
			$this->lang['editaddresscartBT'] = 'edit_cart_bill_to';
			$this->lang['editaddresscartST'] = 'edit_cart_ship_to';
			$this->lang['search'] = 'search';
			$this->lang['page']			= 'page';
			$this->lang['orderDesc'] 	= 'desc';
			$this->lang['product_id'] 		=  'product_id';
			$this->lang['product_sku'] 		=  'product_sku';
			$this->lang['product_price']	=  'product_price';
			$this->lang['orderby']	= 'order_by';
			$this->lang['category_name'] = 'category_name';
			$this->lang['category_description'] = 'category_description';
			$this->lang['mf_name'] = 'mf_name';
			$this->lang['product_s_desc'] = 'product_s_desc';
			$this->lang['product_desc'] = 'product_desc';
			$this->lang['product_weight'] = 'product_weight';
			$this->lang['product_weight_uom'] = 'product_weight_uom';
			$this->lang['product_length'] = 'product_length';
			$this->lang['product_width'] = 'product_width';
			$this->lang['product_height'] = 'product_height';
			$this->lang['product_lwh_uom'] = 'product_lwh_uom';
			$this->lang['product_in_stock'] = 'product_in_stock';
			$this->lang['low_stock_notification'] = 'low_stock_notification';
			$this->lang['product_available_date'] = 'product_available_date';
			$this->lang['product_availability'] = 'product_availability';
			$this->lang['product_special'] = 'product_special';
			$this->lang['ship_code_id'] = 'ship_code_id';
			$this->lang['cdate'] = 'cdate';
			$this->lang['mdate'] = 'mdate';
			$this->lang['product_name'] = 'product_name';
			$this->lang['product_sales'] = 'product_sales';
			$this->lang['product_unit'] = 'product_unit';
			$this->lang['product_packaging'] = 'product_packaging';
			$this->lang['product_order_levels'] = 'product_order_levels';
			$this->lang['intnotes'] = 'intnotes';
			$this->lang['metadesc'] = 'metadesc';
			$this->lang['metakey'] = 'metakey';
			$this->lang['metarobot'] = 'metarobot';
			$this->lang['metaauthor'] = 'metaauthor';
		}
	}

	/* Set $this->menu with the Item ID from Joomla Menus */
	private function setMenuItemId(){

		$app		= JFactory::getApplication();
		$menus		= $app->getMenu('site');
		$component	= JComponentHelper::getComponent('com_virtuemart');
		if ( VmConfig::isJ15() ) $items = $menus->getItems('componentid', $component->id);
		else $items = $menus->getItems('component_id', $component->id);
		// Search  Virtuemart itemID in joomla menu
		foreach ($items as $item)	{
			if ( $item->query['view']=='category' && isset( $item->query['category_id'])) {
				if ( isset( $item->query['category_id']) )
				$this->menu->category_id[]  = array_merge( $item->query, array('itemId' => $item->id) );
				else $this->menu->no_category_id = $item->id;

			} elseif ( $item->query['view']=='virtuemart' ) {
				$this->menu->virtuemart[]  = array_merge($item->query, array('itemId' => $item->id) );
			} elseif ( $item->query['view']=='manufacturer' ) {
				$this->menu->manufacturer_id = $item->id ;
			} elseif ( $item->query['view']=='user' ) {
				$this->menu->user_id = $item->id ;
			} elseif ( $item->query['view']=='cart' ) {
				$this->menu->cart_id = $item->id ;
			}

		}
	}

	/* Set $this->activeMenu to current Item ID from Joomla Menus */
	private function setActiveMenu(){

		$Itemid = JRequest::getInt('Itemid',null);
		$menu = &JSite::getMenu();
		if (!$Itemid) {
			$menuItem = &$menu->getActive();

		} else {
			$menuItem = &$menu->getItem($Itemid);
		}

		$this->activeMenu->view			= (empty($menuItem->query['view'])) ? null : $menuItem->query['view'];
		$this->activeMenu->category_id	= (empty($menuItem->query['category_id'])) ? null : $menuItem->query['category_id'];
		$this->activeMenu->product_id	= (empty($menuItem->query['product_id'])) ? null : $menuItem->query['product_id'];
		$this->activeMenu->Component	= (empty($menuItem->component)) ? null : $menuItem->component;

	}
}

// pure php no closing tag