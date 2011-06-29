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
	$menuCatid		= $helper->activeMenu->virtuemart_category_id;
	$menuProdId		= $helper->activeMenu->virtuemart_product_id;
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
			if ( isset($query['virtuemart_manufacturer_id'])  ) {
				$segments[] = $lang['manufacturer'].'/'.$helper->getManufacturerName($query['virtuemart_manufacturer_id']) ;
				unset($query['virtuemart_manufacturer_id']);
				unset($query['search']);
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
				if ( $query['virtuemart_category_id']>0 || $menuCatid != $query['virtuemart_category_id'] ){
					$categoryRoute = $helper->getCategoryRoute($query['virtuemart_category_id']);
					if ($categoryRoute->route) $segments[] = $categoryRoute->route;
					if ($categoryRoute->itemId) $query['Itemid'] = $categoryRoute->itemId;
					unset($query['virtuemart_category_id']);
				}
			} else {
				if (isset ($helper->menu->no_virtuemart_category_id))$query['Itemid'] = $helper->menu->no_virtuemart_category_id;
				elseif (isset ($helper->menu->virtuemart))$query['Itemid'] = $helper->menu->virtuemart[0]['itemId'] ;
				else unset ($query['Itemid']) ;
				unset($query['virtuemart_category_id']);
			}


		break;
		/* Shop product details view  */
		case 'productdetails';
			$virtuemart_product_id_exists = false;
			$menuCatid = 0 ;
			if(isset($query['virtuemart_product_id'])) {
				if ($helper->use_id) $segments[] = $query['virtuemart_product_id'];
				$virtuemart_product_id_exists = true;
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
				unset($query['virtuemart_category_id']);
			} else {
				// TODO error here ?
				if (isset ($helper->menu->no_virtuemart_category_id))$segments['Itemid'] = $helper->menu->no_virtuemart_category_id;
				elseif (isset ($helper->menu->virtuemart))$segments['Itemid'] = $helper->menu->virtuemart[0]['itemId'] ;
				unset($query['virtuemart_category_id']);
			}
			if($virtuemart_product_id_exists)	{
				$segments[] = $helper->getProductName($virtuemart_product_id);
			}
		break;
		case 'manufacturer';
			if ( isset($helper->menu->virtuemart_manufacturer) ) $query['Itemid'] = $helper->menu->virtuemart_manufacturer;
			else $segments[] = $lang['manufacturers'];
			if(isset($query['virtuemart_manufacturer_id'])) {
				if (isset($helper->menu->virtuemart_manufacturer_id[ $query['virtuemart_manufacturer_id'] ] ) )
				$query['Itemid'] = $helper->menu->virtuemart_manufacturer_id[$query['virtuemart_manufacturer_id']];
				else $segments[] = $helper->getManufacturerName($query['virtuemart_manufacturer_id']) ;
				unset($query['virtuemart_manufacturer_id']);
			}
		break;
		case 'user';
			if ( isset($helper->menu->virtuemart_user_id) ) $query['Itemid'] = $helper->menu->virtuemart_user_id;
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
				if ($query['task'] == 'edit_shipping') $segments[] = $lang['editshipping'] ;
				elseif ($query['task'] == 'edit_payment') $segments[] = $lang['editpayment'];
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
	// revert '-' Joomla change - to : //
	foreach  ($segments as &$value) {
		$value = str_replace(':', '-', $value);
	}

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
			$vars['virtuemart_category_id'] = $helper->activeMenu->virtuemart_category_id ;
			return $vars;
		}
	}
	if ( $segments[0] == $lang['orderDesc'] ) {
		$vars['order'] ='DESC' ;
		array_shift($segments);
		if (empty($segments)) {
			$vars['view'] = 'category';
			$vars['virtuemart_category_id'] = $helper->activeMenu->virtuemart_category_id ;
			return $vars;
		}
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
		if ( isset ($segments[0]) ) {
			$vars['keyword'] = array_shift($segments);

		}
		$vars['view'] = 'category';
		$vars['virtuemart_category_id'] = $helper->activeMenu->virtuemart_category_id ;
		if (empty($segments)) return $vars;
	}
	$count = count($segments)-1;

	if (end($segments) == 'detail') {
		$vars['tmpl'] = 'component';
		array_pop($segments);
		$count--;
	}
	if (end($segments) == $lang['askquestion']) {
		$vars['task'] = 'askquestion';
		array_pop($segments);
		$count--;
	}
	if (isset($segments[0]) && $segments[0] == $lang['user'] || $helper->activeMenu->view == 'user') {
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
	if (isset($segments[0]) && $segments[0] == $lang['cart'] || $helper->activeMenu->view == 'cart') {
		$vars['view'] = 'cart';
		if (isset($segments[0]) && $segments[0] == $lang['cart']) {
			array_shift($segments);
			if (empty($segments)) return $vars;
		}
		if ($segments[0] == $lang['edit_shipping'] ) $vars['task'] = 'edit_shipping' ;
		elseif ($segments[0] == $lang['edit_payment'] ) $vars['task'] = 'edit_payment' ;
		return $vars;
	}


	if (isset($segments[0]) && $segments[0] == $lang['manufacturers'] || $helper->activeMenu->view == 'manufacturer') {
		$vars['view'] = 'manufacturer';

		if (isset($segments[0]) && $segments[0] == $lang['manufacturers'] ) {
			array_shift($segments);
		}

		if (isset($segments[0]) ) {
			$vars['virtuemart_manufacturer_id'] =  $helper->getManufacturerId($segments[0]);
			array_shift($segments);
		}
		if ( isset($segments[0]) && $segments[0] == 'detail') {
			$vars['tmpl'] = 'component';
			array_shift($segments);
		}
		if (isset($helper->activeMenu->virtuemart_manufacturer_id))
			$vars['virtuemart_manufacturer_id'] = $helper->activeMenu->virtuemart_manufacturer_id ;

		return $vars;
	}

	/*
	 * uppercase first (trick for product details )
	 * Product must begin with A-Z
	 */
	$ascii = ord ( $segments[$count] );

	if ($ascii >65 && $ascii<90  ){
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
		return $vars;

	} elseif (!$helper->use_id && ($helper->activeMenu->view == 'category' || ($helper->activeMenu->view == 'virtuemart') ) ) {
		$vars['virtuemart_category_id'] = $helper->getCategoryId (end($segments) ,$helper->activeMenu->virtuemart_category_id);
		$vars['view'] = 'category' ;
		return $vars;

	} elseif (isset($segments[0]) && ctype_digit ($segments[0]) || $helper->activeMenu->virtuemart_category_id>0 ) {
		$vars['virtuemart_category_id'] = $segments[0];
		$vars['view'] = 'category';
		return $vars;

	} elseif ($helper->activeMenu->virtuemart_category_id >0 && $vars['view'] != 'productdetails') {
		$vars['virtuemart_category_id'] = $helper->activeMenu->virtuemart_category_id ;
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
		$this->setLang();
		$this->setMenuItemId();
		$this->setActiveMenu();
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
	public function getCategoryRoute($virtuemart_category_id){
		$category = new stdClass();
		$category->route = '';
		$category->itemId = 0;
		$menuCatid = 0 ;
		$ismenu = false ;
		$CatParentIds = $this->getCategoryRecurse($virtuemart_category_id,0) ;
		// control if category is joomla menu
		if (isset($this->menu->virtuemart_category_id)) {
			foreach ($this->menu->virtuemart_category_id as $menuId) {
				if ($virtuemart_category_id ==  $menuId['virtuemart_category_id']) {
					$ismenu = true;
					$category->itemId = $menuId['itemId'] ;
					break;
				}
				/* control if parent categories are joomla menu */
				foreach ($CatParentIds as $CatParentId) {
					// No ? then find the parent menu categorie !
					if ($menuId['virtuemart_category_id'] == $CatParentId ) {
						$category->itemId = $menuId['itemId'] ;
						$menuCatid = $CatParentId;
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
			if ($menuCatid == 0  && isset($this->menu->virtuemart[0]['itemId'])) $category->itemId = $this->menu->virtuemart[0]['itemId'] ;
		}
		return $category ;
	}

	/*get url safe names of category and parents categories  */
	public function getCategoryNames($virtuemart_category_id,$catMenuId=0){

		$strings = array();
		$db = JFactory::getDBO();
		$parents_id = array_reverse($this->getCategoryRecurse($virtuemart_category_id,$catMenuId)) ;

		foreach ($parents_id as $id ) {
			$q = "SELECT `slug` as name
					FROM  `#__virtuemart_categories`
					WHERE  `virtuemart_category_id`=".(int)$id;

			$db->setQuery($q);
			$strings[] = $db->loadResult();
		}

		return strtolower(implode ('/', $strings ) );

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
		if ($virtuemart_category_id == null) $virtuemart_category_id = 0 ;
		$db = JFactory::getDBO();
			$q = "SELECT distinct `c`.`virtuemart_category_id`
				FROM  `#__virtuemart_categories` AS `c` , `#__virtuemart_category_categories` as `xref`";
			$q .=" WHERE `c`.`slug` LIKE '".$db->getEscaped($slug)."' ";
			// $q .=" AND `xref`.`category_child_id`=`c`.`virtuemart_category_id`";
			// $q .=" AND `xref`.`category_parent_id` = ".(int)$virtuemart_category_id;
			$db->setQuery($q);
			if (!$category_id = $db->loadResult()) {
				$category_id = $virtuemart_category_id;
			}

		/* WARNING name in same category must be unique or you have more then 1 ID */
		return $category_id ;
	}

	/* Get URL safe Product name */
	public function getProductName($id){

		$db			= JFactory::getDBO();
		$query = 'SELECT `slug` FROM `#__virtuemart_products`  ' .
		' WHERE `virtuemart_product_id` = ' . (int) $id;

		$db->setQuery($query);

		return ucfirst( $db->loadResult() );
	}

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
			if (!$virtuemart_category_id = $db->loadResult()) $this->getParentProductcategory($parent_id) ;

		}
		return $virtuemart_category_id ;
	}


	/* get product and category ID */
	public function getProductId($names,$virtuemart_category_id = NULL ){
		$productName = array_pop($names);
		$product = array();
		$categoryName = end($names);

		$product['virtuemart_category_id'] = $this->getCategoryId($categoryName,$virtuemart_category_id ) ;
		$db = JFactory::getDBO();
		$q = "SELECT `p`.`virtuemart_product_id`
			FROM `#__virtuemart_products` AS `p`
			LEFT JOIN `#__virtuemart_product_categories` AS `xref` ON `p`.`virtuemart_product_id` = `xref`.`virtuemart_product_id`
			WHERE `p`.`slug` LIKE '".$db->getEscaped($productName)."' ";
		//$q .= "	AND `xref`.`virtuemart_category_id` = ".(int)$product['virtuemart_category_id'];
		$db->setQuery($q);
		$product['virtuemart_product_id'] = $db->loadResult();
		/* WARNING product name must be unique or you can't acces the product */

		return $product ;
	}

	/* Get URL safe Manufacturer name */
	public function getManufacturerName($virtuemart_manufacturer_id ){
		$db = JFactory::getDBO();
		$query = 'SELECT `slug` FROM `#__virtuemart_manufacturers` WHERE virtuemart_manufacturer_id='.(int)$virtuemart_manufacturer_id;
		$db->setQuery($query);
		// $lang = JFactory::getLanguage();
		// $mfName = $lang->transliterate($db->loadResult());
		// return preg_replace('([^a-zA-Z0-9-/])','-',$mfName);
		return $db->loadResult();

	}

	/* Get Manufacturer id */
	public function getManufacturerId($slug ){
		$db = JFactory::getDBO();
		$query = "SELECT `virtuemart_manufacturer_id` FROM `#__virtuemart_manufacturers` WHERE `slug` LIKE '".$db->getEscaped($slug)."' ";
		$db->setQuery($query);
		// $lang = JFactory::getLanguage();
		// $mfName = $lang->transliterate($db->loadResult());
		// return preg_replace('([^a-zA-Z0-9-/])','-',$mfName);
		return $db->loadResult();

	}

	/* Set $this-lang (Translator for language from virtuemart string) to load only once*/
	private function setLang(){

		if ( VmConfig::get('seo_translate', false) ) {
			/* use translator */
			$lang = JFactory::getLanguage();
			$extension = 'com_virtuemart';
			$base_dir = JPATH_SITE;
			$lang->load($extension, $base_dir);
			$this->lang['editshipping'] 	= $lang->_('COM_VIRTUEMART_SEF_EDITSHIPPING');
			$this->lang['manufacturer'] 	= $lang->_('COM_VIRTUEMART_SEF_MANUFACTURER');
			$this->lang['manufacturers'] 	= $lang->_('COM_VIRTUEMART_SEF_MANUFACTURERS');
			$this->lang['askquestion']  	= $lang->_('COM_VIRTUEMART_SEF_ASKQUESTION');
			$this->lang['edit_payment']  	= $lang->_('COM_VIRTUEMART_SEF_EDITPAYMENT');
			$this->lang['user'] 			= $lang->_('COM_VIRTUEMART_SEF_USER');
			$this->lang['cart'] 			= $lang->_('COM_VIRTUEMART_SEF_CART');
			$this->lang['editaddresscartBT']= $lang->_('COM_VIRTUEMART_SEF_EDITADRESSCART_BILL');
			$this->lang['editaddresscartST']= $lang->_('COM_VIRTUEMART_SEF_EDITADRESSCART_SHIP');
			$this->lang['search']			= $lang->_('COM_VIRTUEMART_SEF_SEARCH');
			$this->lang['page']				= $lang->_('COM_VIRTUEMART_SEF_PAGE');
			$this->lang['orderDesc']		= $lang->_('COM_VIRTUEMART_SEF_ORDER_DESC');
			$this->lang['orderby']			= $lang->_('COM_VIRTUEMART_SEF_BY');
			$this->lang['virtuemart_product_id'] 		= $lang->_('COM_VIRTUEMART_SEF_BY_PRODUCT_ID');
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
			$this->lang['created_on'] 			= $lang->_('COM_VIRTUEMART_SEF_BY_CDATE');
			$this->lang['modified_on'] 			= $lang->_('COM_VIRTUEMART_SEF_BY_MDATE');
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
			$this->lang['editshipping'] = 'edit_shipping';
			$this->lang['manufacturers'] = 'manufacturers';
			$this->lang['manufacturer'] = 'manufacturer';
			$this->lang['askquestion']  = 'askquestion';
			$this->lang['edit_payment']  = 'edit_payment';
			$this->lang['user']			= 'user';
			$this->lang['cart']			= 'cart';
			$this->lang['editaddresscartBT'] = 'edit_cart_bill_to';
			$this->lang['editaddresscartST'] = 'edit_cart_ship_to';
			$this->lang['search'] = 'search';
			$this->lang['page']			= 'page';
			$this->lang['orderDesc'] 	= 'desc';
			$this->lang['virtuemart_product_id'] 		=  'virtuemart_product_id';
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
			$this->lang['created_on'] = 'created_on';
			$this->lang['modified_on'] = 'modified_on';
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

		$this->menu->virtuemart_manufacturer_id = null;
		$this->menu->virtuemart_user_id = null;
		$this->menu->cart_id = null;

		$app		= JFactory::getApplication();
		$menus		= $app->getMenu('site');
		$component	= JComponentHelper::getComponent('com_virtuemart');
		if ( VmConfig::isJ15() ) $items = $menus->getItems('componentid', $component->id);
		else $items = $menus->getItems('component_id', $component->id);

		// Search  Virtuemart itemID in joomla menu
		foreach ($items as $item)	{
			if ( $item->query['view']=='category' && isset( $item->query['virtuemart_category_id'])) {
				if ( isset( $item->query['virtuemart_category_id']) )
				$this->menu->virtuemart_category_id[]  = array_merge( $item->query, array('itemId' => $item->id) );
				else $this->menu->no_virtuemart_category_id = $item->id;

			} elseif ( $item->query['view']=='virtuemart' ) {
				$this->menu->virtuemart[]  = array_merge($item->query, array('itemId' => $item->id) );
			} elseif ( $item->query['view']=='manufacturer' ) {
				if (isset($item->query['virtuemart_manufacturer_id']))
					$this->menu->virtuemart_manufacturer_id[ $item->query['virtuemart_manufacturer_id'] ] = $item->id ;
				else $this->menu->virtuemart_manufacturer = $item->id ;
			} elseif ( $item->query['view']=='user' ) {
				$this->menu->virtuemart_user_id = $item->id ;
			} elseif ( $item->query['view']=='cart' ) {
				$this->menu->cart_id = $item->id ;
			}
			if ( isset( $this->menu->virtuemart[0]) ) {
				if ( !isset($this->menu->virtuemart_manufacturer)){
					$this->menu->virtuemart_manufacturer_id = $this->menu->virtuemart[0];
				}
				if ( !isset($this->menu->virtuemart_user_id)){
					$this->menu->virtuemart_user_id = $this->menu->virtuemart[0];
				}
				if ( !isset($this->menu->cart_id)){
					$this->menu->cart_id = $this->menu->virtuemart[0];
				}
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
		$this->activeMenu->virtuemart_category_id	= (empty($menuItem->query['virtuemart_category_id'])) ? null : $menuItem->query['virtuemart_category_id'];
		$this->activeMenu->virtuemart_product_id	= (empty($menuItem->query['virtuemart_product_id'])) ? null : $menuItem->query['virtuemart_product_id'];
		$this->activeMenu->virtuemart_manufacturer_id	= (empty($menuItem->query['virtuemart_manufacturer_id'])) ? null : $menuItem->query['virtuemart_manufacturer_id'];
		$this->activeMenu->Component	= (empty($menuItem->component)) ? null : $menuItem->component;

	}
}

// pure php no closing tag