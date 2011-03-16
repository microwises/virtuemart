<?php
if(  !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id: 1.9 beta1
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2010 Kohl Patrick - All rights reserved.
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

function virtuemartBuildRoute(&$query)
{
	$view = '';
	$segments = array();

	$menu = &JSite::getMenu();
	if (empty($query['Itemid'])) {
		$menuItem = &$menu->getActive();

	} else {
		$menuItem = &$menu->getItem($query['Itemid']);
	}

	$menuView	= (empty($menuItem->query['view'])) ? null : $menuItem->query['view'];
	$menuCatid	= (empty($menuItem->query['category_id'])) ? 0 : $menuItem->query['category_id'];
	$menuProdId	= (empty($menuItem->query['product_id'])) ? null : $menuItem->query['product_id'];
	$menuComponent	= (empty($menuItem->component)) ? null : $menuItem->component;


	if(isset($query['view'])){
		$view = $query['view'];
		unset($query['view']);
	}

/* Find if a menu is set with a parent categorie 
    add the Id of joomla menu  
    fix the module routes outside of virtuemart */
	static $firsttime=true ;
	static $VirtuemartMenuCat = array();
	static $VirtuemartMenuVM = array();
	static $manufacturerMenuVM = array();
	if ($firsttime) {
	$firsttime = false ;
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu('site');
		$component	= JComponentHelper::getComponent('com_virtuemart');
		$items		= $menus->getItems('componentid', $component->id);
		// set all category and virtuemart root menu id
		foreach ($items as $item)	{
			if ( $item->query['view']=='category' && isset( $item->query['category_id'])) {
				$VirtuemartMenuCat[]  = array_merge( $item->query, array('itemId' => $item->id) );  ;
				
			} elseif ( $item->query['view']=='virtuemart' ) {
				$VirtuemartMenuVM[]  = array_merge($item->query, array('itemId' => $item->id) ); 
			} elseif ( $item->query['view']=='manufacturer' ) {
				$manufacturerMenuVM = $item->id ;
			}
			
		}
	}

	// give the 1st Virtuemart menu ID if no categories are set in joomla menu
	
	
	switch ($view) {
		case 'virtuemart';
			unset($query['view']);
		// Shop category view 
		case 'category';	
			if(!empty( $query['category_id']) && $menuCatid != $query['category_id'] ){
				// to avoid duplicate categorie if a joomla menu ID is set
				$ismenu = false ;
				$treeIds = getCategoryRecurse($query['category_id'],$menuCatid) ;
				foreach ($VirtuemartMenuCat as $menuId) {
					foreach ($treeIds as $treeId) {
						if ($menuId['category_id'] == $treeId && $ismenu == false ) {
							$query['Itemid'] = $menuId['itemId'] ;
							if ($query['category_id'] ==  $treeId) {
								$ismenu = true ;
							
							}
						}
					}
				}
				
				if (!$ismenu) {
					$segments[] = $query['category_id'];
					$segments[] = getCategoryName($query['category_id'], $menuCatid );
				}
				unset($query['category_id']);
			} else {
				unset($query['category_id']);
			}
			// Fix for search with no category
			if ( isset($query['search'])  ) $segments[] = 'search' ;
			if ( isset($query['keyword'] )) {
				$segments[] = $query['keyword'];
				unset($query['keyword']);
			}
		break;
		// Shop product details view 
		case 'productdetails';			
			$product_id_exists = false;
			$menuCatid = 0 ;
			if(isset($query['product_id'])) {
				$segments[] = $query['product_id'];
				$product_id_exists = true;
				$product_id = $query['product_id'];
				unset($query['product_id']);
			}
			if(!empty( $query['category_id'])){
				$ismenu = false ;
				$CatParentIds = getCategoryRecurse($query['category_id'],0) ;
				// control if category is joomla menu
				foreach ($VirtuemartMenuCat as $menuId) {
					if ($query['category_id'] ==  $menuId['category_id']) {
						$ismenu = true;
						$query['Itemid'] = $menuId['itemId'] ;
						break;
					}
					/* control if parent categories are joomla menu */
					foreach ($CatParentIds as $CatParentId) {
						// No ? then find te parent menu categorie !
						if ($menuId['category_id'] == $CatParentId ) {
							$query['Itemid'] = $menuId['itemId'] ;
							$menuCatid = $CatParentId;
						}
					}
				}
				if ($ismenu==false) {
					$segments[] = $query['category_id'];
					$segments[] = getCategoryName($query['category_id'], $menuCatid );
					if ($menuCatid == 0 ) $query['Itemid'] = $VirtuemartMenuVM[0]['itemId'] ;
				}
				unset($query['category_id']);
				/*} else {
					//$segments[] = $query['category_id'];
					unset($query['category_id']);
				}*/
			}
			if($product_id_exists)	{
				$segments[] = getProductName($product_id);
			}
		break;
		case 'manufacturer';
			if ( isset($manufacturerMenuVM) ) $query['Itemid'] = $manufacturerMenuVM;
			$segments[] = $view;
			if(isset($query['manufacturer_id'])) {
				$segments[] = $query['manufacturer_id'];
				unset($query['manufacturer_id']);
			}
		break;
		
		// sef only view	
		default ;
		$segments[] = $view;
		

	} 
	// sef the task
	if (isset($query['task'])) {
		$segments[] = $query['task'] ;
		unset($query['task']);
	}	// sef the task
	if (isset($query['tmpl'])) {
		if ( $query['tmpl'] = 'component') $segments[] = 'detail' ;
		unset($query['tmpl']);
	}
	if (empty ($query['Itemid'])) $query['Itemid'] = $VirtuemartMenuVM[0]['itemId'] ;

	return $segments;
}

function virtuemartParseRoute($segments)
{
	$vars = array();

	$menu =& JSite::getMenu();
	$menuItem =& $menu->getActive();
	$menuCatid = (empty($menuItem->query['category_id'])) ? 0 : $menuItem->query['category_id'];

	$segments[0]=str_replace(":", "-",$segments[0]);
	$count = count($segments)-1;	
	if ($segments[0] == 'search') {
		$vars['view'] = 'category';
		array_shift($segments);
		$count--;
	}
	
	if ($segments[$count] == 'detail') {
		$vars['tmpl'] = 'component';
		array_pop($segments);
		$count--;
	}	
	if ($segments[$count] == 'askquestion') {
		$vars['task'] = array_pop($segments);
		
		$count--;
	}
	if ($segments[0] == 'manufacturer') {
		$vars['view'] = 'manufacturer';
		unset ($segments[0]);
		$count--;
		if (isset($segments[0])  && ctype_digit ($segments[0])) {
			$vars['manufacturer_id'] = $segments[0];
			unset ($segments[0]);
		}
		$count--;
		//return $vars;
	}

	if  ($count<1 ) return $vars;
	//uppercase first (trick for product details )
	if ($segments[$count][0] == ucfirst($segments[$count][0]) ){
		$vars['view'] = 'productdetails';
		if (ctype_digit ($segments[1])){
			$vars['product_id'] = $segments[0];
			$vars['category_id'] = $segments[1];
		} else {
			$vars['product_id'] = $segments[0];
			$vars['category_id'] = $menuCatid ;
		}
		return $vars;
	} elseif (isset($segments[0]) && ctype_digit ($segments[0]) || $menuCatid>0 ) {
		$vars['category_id'] = $segments[0];
		$vars['view'] = 'category';
		return $vars;
	} elseif ($menuCatid >0 && $vars['view'] != 'productdetails') {
		$vars['category_id'] = $menuCatid ;
		$vars['view'] = 'category';
		return $vars;
	} 

		$vars['view'] = $segments[0];
		if ( isset($segments[1]) ) {
			$vars['task'] = $segments[1] ;
		}

	return $vars;
}


// This function returns category/subcatgory alias string

function getCategoryName($category_id,$catMenuId=0){

	$strings = array();
	$db = & JFactory::getDBO();
	$parents_id = array_reverse(getCategoryRecurse($category_id,$catMenuId)) ;
	
	foreach ($parents_id as $id ) {
		$q = "SELECT `category_name` as name
				FROM  `#__vm_category` 
				WHERE  `category_id`=".$id;

		$db->setQuery($q);
		$category = $db->loadResult();
		$string  = $category;
		if ( ctype_digit(trim($string)) ){
			return trim($string);
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

function getCategoryRecurse($category_id,$catMenuId,$first=true ) {
	static $idsArr = array();
	if($first) {
		$idsArr = array();
	}

	$db			= & JFactory::getDBO();	
	$q = "SELECT `category_child_id` AS `child`, `category_parent_id` AS `parent`
			FROM  #__vm_category_xref AS `xref`
			WHERE `xref`.`category_child_id`= ".$category_id;
	$db->setQuery($q);
	$ids = $db->loadObject();
	if ($ids->child) $idsArr[] = $ids->child;
	if($ids->parent != 0 and $catMenuId != $category_id and $catMenuId != $ids->parent) {
		getCategoryRecurse($ids->parent,$catMenuId,false);
	} 
	
	
		return $idsArr;
}

function getProductName($id){

	$db			= & JFactory::getDBO();
	$query = 'SELECT `product_name` FROM `#__vm_product`  ' .
	' WHERE `product_id` = ' . (int) $id;

	$db->setQuery($query);
	// gets product name of item
	$product_name = $db->loadResult();
		$string  = $product_name;
		if ( ctype_digit(trim($string)) ){
			return trim($string);
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

// pure php no closing tag