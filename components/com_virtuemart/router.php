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
				
			}
			if ( $item->query['view']=='virtuemart' ) {
				$VirtuemartMenuVM[]  = array_merge($item->query, array('itemId' => $item->id) ); 
			}
			
		}

	}

	// give the 1st Virtuemart menu ID if no categories are set in joomla menu
	if ($menuComponent != 'com_virtuemart' ) $query['Itemid'] = $VirtuemartMenuVM[0]['itemId'] ;
	
	switch ($view) {
		case 'virtuemart';
			unset($query['view']);
		// Shop category view 
		case 'category';	
			if(!empty( $query['category_id']) && $menuCatid != $query['category_id'] ){
				// to avoid duplicate categorie if a joomla menu ID is set
				$ismenu = false ;
				$treeIds = getCategoryRecurse($query['category_id'],true,$menuCatid) ;
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
					$segments[] = getCategoryName($query['category_id'], true, $menuCatid );
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
			if(isset($query['product_id'])) {
				$segments[] = $query['product_id'];
				$product_id_exists = true;
				$product_id = $query['product_id'];
				unset($query['product_id']);
			}
			if(isset( $query['category_id'])){
				// to avoid duplicate categorie if a joomla menu ID is set 
				if ($menuCatid != $query['category_id'] ){
				$ismenu = false ;
				$treeIds = getCategoryRecurse($query['category_id'],true,$menuCatid) ;
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
				$segments[] = $query['category_id'];
				if (!$ismenu) {
					$segments[] = getCategoryName($query['category_id'], true, $menuCatid );
				}
				unset($query['category_id']);
				} else {
					$segments[] = $query['category_id'];
					unset($query['category_id']);
				}
			}
			if($product_id_exists)	{
				$segments[] = getProductName($product_id);
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
	}
	return $segments;
}

function virtuemartParseRoute($segments)
{
	$vars = array();
	$count = count($segments) ;
	$menu =& JSite::getMenu();
	$menuItem =& $menu->getActive();
	$menuCatid = (empty($menuItem->query['category_id'])) ? 0 : $menuItem->query['category_id'];

	$segments[0]=str_replace(":", "-",$segments[0]);
	
	if ($segments[0] == 'search') {
		$vars['view'] = 'category';
		array_shift($segments);
	}
	if  (!$segments) return $vars;
	
	if (ctype_digit ($segments[0])) {
		if (ctype_digit ($segments[1]) ) {
			$vars['product_id'] = $segments[0];
			$vars['category_id'] = $segments[1];
			$vars['view'] = 'productdetails';
		}
		else {
			$vars['category_id'] = $segments[0];
			$vars['view'] = 'category';
		}
	return $vars;
	} else {
		$vars['view'] = $segments[0];
		if ( isset($segments[1]) ) {
			$vars['task'] = $segments[1] ;
		}
	}
	return $vars;
}


// This function returns category/subcatgory alias string

function getCategoryName($category_id,$catMenuId=0,$menu){

	$strings = array();
	$db = & JFactory::getDBO();
	$parents_id = getCategoryRecurse($category_id,true,$catMenuId,$menu) ;
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

function getCategoryRecurse($category_id,$first,$catMenuId ) {
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
	if($ids->parent != 0 and $catMenuId != $category_id and $catMenuId != $ids->parent) {
		getCategoryRecurse($ids->parent,false,$catMenuId);
	} 
	$idsArr[] = $ids->child;
	
	if($first) {
		return $idsArr;
	}
	return;
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
	return $string;
}

// pure php no closing tag