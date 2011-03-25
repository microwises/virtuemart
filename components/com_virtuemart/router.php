<?php
if(  !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @package VirtueMart
* @Author Kohl Patrick
* @subpackage html
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



/* views are virtuemart\user\state\productdetails\orders\category\cart\*/
/*task editAddressSt */

function virtuemartBuildRoute(&$query) {

	$segments = array();
	$helper = vmrouterHelper::getInstance();
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
		// Shop category view 
		case 'category';
			// Fix for search with no category
			if ( isset($query['start'] )) {
				$segments[] = $lang->page ;
				$mainframe = Jfactory::getApplication(); ;
				$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
				$segments[] = floor($query['start']/$limit)+1;
				unset($query['start']);
			}
			if ( isset($query['manufacturer_id'])  ) {
				$segments[] = $lang->manufacturer.'/'.$query['manufacturer_id'].'/'.$helper->getManufacturerName($query['manufacturer_id']) ;
				unset($query['manufacturer_id']);
			}
			if ( isset($query['search'])  ) {
				$segments[] = $lang->search ;
				unset($query['search']);
			}
			if ( isset($query['keyword'] )) {
				$segments[] = $query['keyword'];
				unset($query['keyword']);
			}
			if(!empty( $query['category_id']) && $menuCatid != $query['category_id'] ){
				$categoryRoute = $helper->getCategoryRoute($query['category_id']);
				if ($categoryRoute->route) $segments[] = $categoryRoute->route;
				if ($categoryRoute->itemId) $query['Itemid'] = $categoryRoute->itemId;
				unset($query['category_id']);
			} else {
				if (isset ($helper->menu->no_category_id))$query['Itemid'] = $helper->menu->no_category_id;
				elseif (isset ($helper->menu->virtuemart))$query['Itemid'] = $helper->menu->virtuemart[0]['itemId'] ;
				unset($query['category_id']);
			}


		break;
		// Shop product details view 
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
			}
			if($product_id_exists)	{
				$segments[] = $helper->getProductName($product_id);
			}
		break;
		case 'manufacturer';
			if ( isset($helper->menu->manufacturer_id) ) $query['Itemid'] = $helper->menu->manufacturer_id;
			else $segments[] = $lang->manufacturer;
			if(isset($query['manufacturer_id'])) {
				$segments[] = $query['manufacturer_id'];
				unset($query['manufacturer_id']);
			}
		break;
		case 'user';
			if ( isset($helper->menu->user_id) ) $query['Itemid'] = $helper->menu->user_id;
			else $segments[] = $lang->user ;
			if (isset($query['task'])) {
				if ($query['addrtype'] == 'BT' && $query['task']='editaddresscart') $segments[] = $lang->editaddresscartBT ;
				elseif ($query['addrtype'] == 'ST' && $query['task']='editaddresscart') $segments[] = $lang->editaddresscartST ;
				else $segments[] = $query['task'] ;
				unset ($query['task'] , $query['addrtype']);
			}
		break;
		case 'cart';
			if ( isset($helper->menu->cart_id) ) $query['Itemid'] = $helper->menu->cart_id;
			else $segments[] = $lang->cart ;
			if (isset($query['task'])) {
				if ($query['task'] == 'editshipping') $segments[] = $lang->editshipping ;
				elseif ($query['task'] == 'editpayment') $segments[] = $lang->editpayment;
				unset($query['task']);
			}
		break;
		
		// sef only view	
		default ;
		if ($helper->activeMenu->view != $view) $segments[] = $view;
		

	} 
	// sef the task
	if (isset($query['task'])) {
		if ($query['task'] == 'askquestion') $segments[] = $lang->askquestion;
		else $segments[] = $query['task'] ;
		unset($query['task']);
	}	// sef the task
	if (isset($query['tmpl'])) {
		if ( $query['tmpl'] = 'component') $segments[] = 'detail' ;
		unset($query['tmpl']);
	}
	if (empty ($query['Itemid'])) $query['Itemid'] = $helper->menu->virtuemart[0]['itemId'] ;

	return $segments;
}

function virtuemartParseRoute($segments)
{
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
	
	if ($segments[0] == $lang->page) {
		array_shift($segments);
		
		$mainframe = Jfactory::getApplication(); ;
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$vars['limitstart'] = (array_shift($segments)*$limit)-1;
		if (empty($segments)) return $vars;
	}
	
	if ( $segments[0] == $lang->manufacturer) {
		array_shift($segments);
		$vars['manufacturer_id'] = $segments[0];
		array_shift($segments);
		array_shift($segments);
		if (empty($segments)) {
			$vars['view'] = 'category';
			$vars['category_id'] = '0';
			return $vars;
		}
	}
	if ($segments[0] == $lang->search) {
		$vars['search'] = 'true';
		array_shift($segments);
		if ( isset ($segments[0]) ) {
			$vars['keyword'] = array_shift($segments);
			
		}
		$vars['view'] = 'category';
		if (empty($segments)) return $vars;
	}
	$count = count($segments)-1;
	//print_r($segments);
	if ($segments[$count] == 'detail') {
		$vars['tmpl'] = 'component';
		array_pop($segments);
		$count--;
	}
	if ($segments[$count] == $lang->askquestion) {
		$vars['task'] = 'askquestion';
		array_pop($segments);
		$count--;
	}
	if ($segments[0] == $lang->user || $helper->activeMenu->view == 'user') {
		$vars['view'] = 'user';
		if ($segments[0] == $lang->user) {
			array_shift($segments);
			$count--;
		}
		if ( isset($segments[0]) ) {
			if ( $segments[0] == $lang->editaddresscartBT ) {
				$vars['addrtype'] = 'BT' ;
				$vars['task'] = 'editaddresscart' ;
			}
			elseif ( $segments[0] == $lang->editaddresscartST ) {
				$vars['addrtype'] = 'ST' ;
				$vars['task'] = 'editaddresscart' ;
				} else $vars['task'] = $segments[0] ;
		}
		return $vars;
	}
	if ($segments[0] == $lang->cart || $helper->activeMenu->view == 'cart') {
		$vars['view'] = 'cart';
		if ($segments[0] == $lang->cart) {
			array_shift($segments);
			$count--;
		}
		if ($segments[0] == $lang->editshipping ) $vars['task'] = 'editshipping' ;
		elseif ($segments[0] == $lang->editpayment ) $vars['task'] = 'editpayment' ;
		return $vars;
	}


	if ($segments[0] == $lang->manufacturer || $helper->activeMenu->view == 'manufacturer') {
		$vars['view'] = 'manufacturer';
		if ($segments[0] == $lang->manufacturer) {
			array_shift($segments);
		}
		if (isset($segments[0])  && ctype_digit ($segments[0])) {
			$vars['manufacturer_id'] = $segments[0];
			array_shift($segments);
		}

		return $vars;
	}


	//if  ($count<1 ) return $vars;
	//uppercase first (trick for product details )

	if ($segments[$count][0] == ucfirst($segments[$count][0]) ){
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

		//($helper->activeMenu->view) $vars['view'] = $helper->activeMenu->view;
	
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
		if ($ismenu==false) {
			if ( $this->use_id ) $category->route = $category_id.'/';
			$category->route .= self::getCategoryName($category_id, $menuCatid );
			if ($menuCatid == 0 ) $category->itemId = $this->menu->virtuemart[0]['itemId'] ;
		}
		return $category ;
	}
	/*get url safe names of category and parents categories  */
	public function getCategoryName($category_id,$catMenuId=0){

		$strings = array();
		$db = & JFactory::getDBO();
		$parents_id = array_reverse(self::getCategoryRecurse($category_id,$catMenuId)) ;

		foreach ($parents_id as $id ) {
			$q = "SELECT `category_name` as name
					FROM  `#__vm_category` 
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
				FROM  #__vm_category_xref AS `xref`
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
	public function getCategoryId($names,$category_ids = NULL ){
		
		$db = & JFactory::getDBO();
		$parentIds = array();
		//$category_ids = null;
		foreach ($names as $name) {
			$name = str_replace('-', '%', $name);
			$name = str_replace(':', '%', $name);
			$q = "SELECT `c`.`category_id` 
				FROM  `#__vm_category` AS `c` ";
			if (isset ($category_ids)) $q .= ", #__vm_category_xref as `xref`";
			$q .=" WHERE `c`.`category_name` LIKE '".$name."' ";
			if (isset ($category_ids)) $q .=" AND `xref`.`category_parent_id` in (".$category_ids.")";

			$db->setQuery($q);
			$result = $db->loadResultArray();
			$category_ids = implode(',',$result);
		}
		/* WARNING name in last category must be unique or you have more then 1 ID */
		return $category_ids ;
	}

	/* Get URL safe Product name */
	public function getProductName($id){

		$db			= & JFactory::getDBO();
		$query = 'SELECT `product_name` FROM `#__vm_product`  ' .
		' WHERE `product_id` = ' . (int) $id;

		$db->setQuery($query);
		// gets product name of item
		$product_name = $db->loadResult();
			$string  = trim($product_name) ;
			if ( ctype_digit($string)){
				return $string ;
			}
			else {	
				// accented chars converted
				$accents = '/&([A-Za-z]{1,2})(grave|acute|circ|cedil|uml|lig);/';
				$string_encoded = htmlentities($string,ENT_NOQUOTES,'UTF-8');
				$string = preg_replace($accents,'$1',$string_encoded);
				
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
			FROM `jos_vm_product` AS `p`
			LEFT JOIN `jos_vm_product_category_xref` AS `xref` ON `p`.`product_id` = `xref`.`product_id`
			WHERE `p`.`product_name` LIKE '".$productName."'
			AND `xref`.`category_id` in (".$product['category_id'].") ";
		$db->setQuery($q);
		$product['product_id'] = $db->loadResult();
		/* WARNING product name must be unique or you can't acces the product */
		return $product ; 
	}
	public function getManufacturerName($manufacturer_id ){
	$db = JFactory::getDBO();
	$query = 'SELECT `mf_name` FROM `#__vm_manufacturer` WHERE manufacturer_id='.$manufacturer_id;
	$db->setQuery($query);
	return $db->loadResult();

	}
	/* Set $this-lang (Translator for language from virtuemart string) to load only once*/
	private function setLang(){

		if ( VmConfig::get('seo_translate', false) ) {
			/* use translator */
			$lang =& JFactory::getLanguage();
			$extension = 'com_virtuemart';
			$base_dir = JPATH_SITE;
			$lang->load($extension, $base_dir);
			$this->lang->editshipping = $lang->_('VM_SEF_EDITSHIPPING');
			$this->lang->manufacturer = $lang->_('VM_SEF_MANUFACTURER');
			$this->lang->askquestion  = $lang->_('VM_SEF_ASKQUESTION');
			$this->lang->editpayment  = $lang->_('VM_SEF_EDITPAYMENT');
			$this->lang->user         = $lang->_('VM_SEF_USER');
			$this->lang->cart         = $lang->_('VM_SEF_CART');
			$this->lang->editaddresscartBT  = $lang->_('VM_SEF_EDITADRESSCART_BILL');
			$this->lang->editaddresscartST  = $lang->_('VM_SEF_EDITADRESSCART_SHIP');
			$this->lang->search       = $lang->_('VM_SEF_SEARCH');
			$this->lang->manufacturer = $lang->_('VM_SEF_MANUFACTURER');
			$this->lang->page         = $lang->_('VM_SEF_PAGE');
		} else {
			/* use default */
			$this->lang->editshipping = 'editshipping';
			$this->lang->manufacturer = 'manufacturer';
			$this->lang->askquestion  = 'askquestion';
			$this->lang->editpayment  = 'editpayment';
			$this->lang->user         = 'user';
			$this->lang->cart         = 'cart';
			$this->lang->editaddresscartBT  = 'edit_cart_bill_to';
			$this->lang->editaddresscartBT  = 'edit_cart_ship_to';
			$this->lang->search       = 'search';
			$this->lang->manufacturer       = 'manufacturer';
			$this->lang->page         = 'page';
			
		}  
	}

	/* Set $this->menu with the Item ID from Joomla Menus */
	private function setMenuItemId(){

		$app		= JFactory::getApplication();
		$menus		= $app->getMenu('site');
		$component	= JComponentHelper::getComponent('com_virtuemart');
		$items		= $menus->getItems('componentid', $component->id);
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