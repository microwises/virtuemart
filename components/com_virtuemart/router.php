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

	$jmenu = & $helper->menu ;
	if (empty($query['Itemid'])) $query['Itemid'] = $jmenu['virtuemart'][0];


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
				$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', VmConfig::get('list_limit', 20), 'int');
				//Pagination changed, maybe the +1 is wrong note by Max Milbers
				if(version_compare(JVERSION,'1.6.0','ge')) {
					$segments[] = floor($query['start']/$limit);
				} else {
					$segments[] = floor($query['start']/$limit)+1;
				}
				unset($query['start']);
			}
                        else {
                                // George Kostopoulos, a possible fix for J1.7 SEF enabled pagination problem (where pages must start from 0)
                                // May not work in J1.5
                                $segments[] = $lang['page'] ;                                
                                if(version_compare(JVERSION,'1.6.0','ge')) {
					$segments[] = 0;
				} else {
					$segments[] = 1;
				}				
                        }
			if ( isset($query['orderby']) ) {

				$dotps = strrpos($query['orderby'], '.');
				if($dotps!==false){
					$prefix = substr($query['orderby'], 0,$dotps).'_';
					$fieldWithoutPrefix = substr($query['orderby'], $dotps+1);
					// 				vmdebug('Found dot '.$dotps.' $prefix '.$prefix.'  $fieldWithoutPrefix '.$fieldWithoutPrefix);
				} else {
					$prefix = '';
					$fieldWithoutPrefix = $query['orderby'];
				}
				$segments[] = $lang['orderby'].','.$prefix.$lang[ $fieldWithoutPrefix ] ;
				unset($query['orderby']);
			}
			if ( isset($query['order']) ) {
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
				if (isset($jmenu['virtuemart_category_id'][ $query['virtuemart_category_id'] ] ) )
					$query['Itemid'] = $jmenu['virtuemart_category_id'][$query['virtuemart_category_id']];
				else {
					$categoryRoute = $helper->getCategoryRoute($query['virtuemart_category_id']);
					if ($categoryRoute->route) $segments[] = $categoryRoute->route;

					if ($categoryRoute->itemId) $query['Itemid'] = $categoryRoute->itemId;
					else $query['Itemid'] = $jmenu['virtuemart'][0] ;
				}
				unset($query['virtuemart_category_id']);
			} else {
				$query['Itemid'] = $jmenu['virtuemart'][0] ;
			}


		break;
		/* Shop product details view  */
		case 'productdetails';
			$virtuemart_product_id_exists = false;
			if (isset($jmenu['virtuemart_product_id'][ $query['virtuemart_product_id'] ] ) ) {
				$query['Itemid'] = $jmenu['virtuemart_product_id'][$query['virtuemart_product_id']];
			} else {
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
					$query['Itemid'] = $jmenu['virtuemart'][0] ;
					unset($query['virtuemart_category_id']);
				}
				if($virtuemart_product_id_exists)	{
					$segments[] = $helper->getProductName($virtuemart_product_id);
				}
			}
		break;
		case 'manufacturer';
			if ( isset($jmenu['virtuemart_manufacturer']) ) $query['Itemid'] = $jmenu['virtuemart_manufacturer'];
			if(isset($query['virtuemart_manufacturer_id'])) {
				if (isset($jmenu['virtuemart_manufacturer_id'][ $query['virtuemart_manufacturer_id'] ] ) )
				$query['Itemid'] = $jmenu['virtuemart_manufacturer_id'][$query['virtuemart_manufacturer_id']];
				else $segments[] = $lang['manufacturers'].'/'.$helper->getManufacturerName($query['virtuemart_manufacturer_id']) ;
				unset($query['virtuemart_manufacturer_id']);
			}
		break;
		case 'user';
			if ( isset($jmenu['virtuemart_user']) ) $query['Itemid'] = $jmenu['virtuemart_user'];
			else {
				$segments[] = $lang['user'] ;
				$query['Itemid'] = $jmenu['virtuemart'][0] ;
			}
			if (isset($query['task'])) {
				if ($query['addrtype'] == 'BT' && $query['task']='editaddresscart') $segments[] = $lang['editaddresscartBT'] ;
				elseif ($query['addrtype'] == 'ST' && $query['task']='editaddresscart') $segments[] = $lang['editaddresscartST'] ;
				else $segments[] = $query['task'] ;
				unset ($query['task'] , $query['addrtype']);
			}
		break;
		case 'cart';
			if ( isset($jmenu['virtuemart_cart']) ) $query['Itemid'] = $jmenu['virtuemart_cart'];
			else {
				$segments[] = $lang['cart'] ;
				$query['Itemid'] = $jmenu['virtuemart'][0] ;
			}

		break;
		case 'orders';
			if ( isset($jmenu['virtuemart_orders']) ) $query['Itemid'] = $jmenu['virtuemart_orders'];
			else {
				$segments[] = $lang['orders'] ;
				$query['Itemid'] = $jmenu['virtuemart'][0] ;
			}

		break;

		// sef only view
		default ;
		$segments[] = $view;


	}
	if (isset($query['task'])) {
		$segments[] = $lang[$query['task']] ;
		unset($query['task']);
	}
	// sef the slimbox View
	if (isset($query['tmpl'])) {
		if ( $query['tmpl'] = 'component') $segments[] = 'detail' ;
		unset($query['tmpl']);
	}
	if (empty ($query['Itemid']) && isset($jmenu['virtuemart'][0])) $query['Itemid'] = $jmenu['virtuemart'][0] ;

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
// 		if(version_compare(JVERSION,'1.6.0','ge')) {
// 			$vars['limitstart'] = (array_shift($segments)*$limit);
// 		} else {
			$vars['limitstart'] = (array_shift($segments)*$limit);
// 		}
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
	} elseif (end($segments) == $lang['recommend']) {
		$vars['task'] = 'recommend';
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
		elseif ($segments[0] == $lang['editpayment'] ) $vars['task'] = 'editpayment' ;
		elseif ($segments[0] == $lang['delete'] ) $vars['task'] = 'delete' ;
		return $vars;
	}
	if (isset($segments[0]) && $segments[0] == $lang['orders'] || $helper->activeMenu->view == 'orders') {
		$vars['view'] = 'orders';
		if (isset($segments[0]) && $segments[0] == $lang['orders']) {
			array_shift($segments);
			if (empty($segments)) return $vars;
		}
		if ($segments[0] == $lang['list'] ) $vars['task'] = 'list' ;
		elseif ($segments[0] == $lang['details'] ) $vars['task'] = 'details' ;
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
// find corresponding category  if not segment 0 must be a view

	if ($id = $helper->getCategoryId (end($segments) ,null )) {
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
	  * $router_disabled type boolean
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
		$this->seo_sufix = VmConfig::get('seo_sufix', '-detail');
		$this->seo_sufix_size = strlen($this->seo_sufix) ;

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
			if ($menuCatid == 0  && $this->menu['virtuemart'][0]) $category->itemId = $this->menu['virtuemart'][0] ;
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

		return $db->loadResult();

	}

	/* Get Manufacturer id */
	public function getManufacturerId($slug ){
		$db = JFactory::getDBO();
		$query = "SELECT `virtuemart_manufacturer_id` FROM `#__virtuemart_manufacturers` WHERE `slug` LIKE '".$db->getEscaped($slug)."' ";
		$db->setQuery($query);

		return $db->loadResult();

	}

	/* Set $this-lang (Translator for language from virtuemart string) to load only once*/
	private function setLang(){

		if ( VmConfig::get('seo_translate', false) ) {
			/* use translator */
			$lang = JFactory::getLanguage();
			$extension = 'com_virtuemart.sef';
			$base_dir = JPATH_SITE;
			$lang->load($extension, $base_dir);
			$this->lang = array(
				'edit_shipping'		=> JText::_('COM_VIRTUEMART_SEF_EDITSHIPPING'),
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
				'virtuemart_product_id'=> JText::_('COM_VIRTUEMART_SEF_BY_PRODUCT_ID'),
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
				'ship_code_id'		=> JText::_('COM_VIRTUEMART_SEF_BY_SHIP_CODE_ID'),
				'created_on' 		=> JText::_('COM_VIRTUEMART_SEF_BY_CDATE'),
				'modified_on' 		=> JText::_('COM_VIRTUEMART_SEF_BY_MDATE'),
				'product_name'		=> JText::_('COM_VIRTUEMART_SEF_BY_PRODUCT_NAME'),
				'product_sales'		=> JText::_('COM_VIRTUEMART_SEF_BY_PRODUCT_SALES'),
				'product_unit'		=> JText::_('COM_VIRTUEMART_SEF_BY_PRODUCT_UNIT'),
				'product_packaging'	=> JText::_('COM_VIRTUEMART_SEF_BY_PRODUCT_PACKAGING'),
				'intnotes'			=> JText::_('COM_VIRTUEMART_SEF_BY_INTNOTES'),
				'metadesc'			=> JText::_('COM_VIRTUEMART_SEF_BY_METADESC'),
				'metakey'			=> JText::_('COM_VIRTUEMART_SEF_BY_METAKEY'),
				'metarobot'			=> JText::_('COM_VIRTUEMART_SEF_BY_METAROBOT'),
				'metaauthor'		=> JText::_('COM_VIRTUEMART_SEF_BY_METAAUTHOR'),
				'recommend'			=> JText::_('COM_VIRTUEMART_SEF_RECOMMEND')
			);


		} else {
			/* use default */
			$this->lang = array(
				'edit_shipping' => 'edit_shipping',
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
				'virtuemart_product_id' => 'virtuemart_product_id',
				'product_sku' => 'product_sku',
				'product_price' => 'product_price',
				'orderby' => 'order_by',
				'category_name' => 'category_name',
				'category_description' => 'category_description',
				'mf_name' => 'mf_name',
				'product_s_desc' => 'product_s_desc',
				'product_desc' => 'product_desc',
				'product_weight' => 'product_weight',
				'product_weight_uom' => 'product_weight_uom',
				'product_length' => 'product_length',
				'product_width' => 'product_width',
				'product_height' => 'product_height',
				'product_lwh_uom' => 'product_lwh_uom',
				'product_in_stock' => 'product_in_stock',
				'low_stock_notification' => 'low_stock_notification',
				'product_available_date' => 'product_available_date',
				'product_availability' => 'product_availability',
				'product_special' => 'product_special',
				'ship_code_id' => 'ship_code_id',
				'created_on' => 'created_on',
				'modified_on' => 'modified_on',
				'product_name' => 'product_name',
				'product_sales' => 'product_sales',
				'product_unit' => 'product_unit',
				'product_packaging' => 'product_packaging',
				'intnotes' => 'intnotes',
				'metadesc' => 'metadesc',
				'metakey' => 'metakey',
				'metarobot' => 'metarobot',
				'metaauthor' => 'metaauthor',
				'recommend' => 'recommend'
			);
		}
	}

	/* Set $this->menu with the Item ID from Joomla Menus */
	private function setMenuItemId(){

		$app		= JFactory::getApplication();
		$menus		= $app->getMenu('site');
		$component	= JComponentHelper::getComponent('com_virtuemart');
		if ( VmConfig::isJ15() ) $items = $menus->getItems('componentid', $component->id);
		else $items = $menus->getItems('component_id', $component->id);

		if(empty($items)){
			vmWarn('Assign virtuemart to a menu item');
		} else {
			// Search  Virtuemart itemID in joomla menu
			foreach ($items as $item)	{
				$view = $item->query['view'] ;
				if ($view=='virtuemart') $this->menu['virtuemart'][] = $item->id;

				if ( isset($item->query['virtuemart_'.$view.'_id']) )
				$this->menu['virtuemart_'.$view.'_id'][ $item->query['virtuemart_'.$view.'_id'] ] = $item->id;
				else $this->menu['virtuemart_'.$view]= $item->id ;
			}
		}

		// init unsetted views  to defaut front view or nothing(prevent duplicates routes)
		if ( !isset( $this->menu['virtuemart'][0]) ) {
			$this->menu['virtuemart'][0] = null;
		}
		if ( !isset( $this->menu['virtuemart_manufacturer']) ) {
			$this->menu['virtuemart_manufacturer'] = $this->menu['virtuemart'][0] ;
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