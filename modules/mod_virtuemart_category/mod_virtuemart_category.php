<?php
defined('_JEXEC') or  die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/*
* Best selling Products module for VirtueMart
* @version $Id: mod_virtuemart_category.php 1160 2008-01-14 20:35:19Z soeren_nb $
* @package VirtueMart
* @subpackage modules
*
* @copyright (C) John Syben (john@webme.co.nz)
* Conversion to Mambo and the rest:
* 	@copyright (C) 2004-2005 Soeren Eberhardt
*
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* VirtueMart is Free Software.
* VirtueMart comes with absolute no warranty.
*
* www.virtuemart.net
*----------------------------------------------------------------------
* This code creates a list of the bestselling products
* and displays it wherever you want
*----------------------------------------------------------------------
*/
/* Load  VM fonction */
require('helper.php');
JTable::addIncludePath(JPATH_VM_ADMINISTRATOR.DS.'tables');

vmJsApi::jQuery();
vmJsApi::cssSite();

/* Setting */
$categoryModel = new VirtueMartModelCategory();
$category_id = $params->get('Parent_Category_id', 0);
$class_sfx = $params->get('class_sfx', '');
$moduleclass_sfx = $params->get('moduleclass_sfx','');
$layout = $params->get('layout','default');
$active_category_id = JRequest::getInt('virtuemart_category_id', '0');
$vendorId = '1';
		//$profiler = new JProfiler();
		$cache = & JFactory::getCache();
		$cache->setCaching( 1 );
		$categories = $cache->call( array( 'VirtueMartModelCategory', 'getChildCategoryList' ),$vendorId, $category_id );
// $categories = $categoryModel->getChildCategoryList($vendorId, $category_id);
// We dont use image here 
//$categoryModel->addImages($categories);

if(empty($categories)) return false;


foreach ($categories as $category) {

		$cache = & JFactory::getCache();
		$cache->setCaching( 1 );
		$category->childs = $cache->call( array( 'VirtueMartModelCategory', 'getChildCategoryList' ),$vendorId, $category->virtuemart_category_id );
   // $category->childs = $categoryModel->getChildCategoryList($vendorId, $category->virtuemart_category_id) ;
	// No image used here
	//$categoryModel->addImages($category->childs);
}
//echo $profiler->mark( ' seconds to do stuff' );
// 
$catTree = $categoryModel->getCategoriesInfo($vendorId=1 );
echo json_encode($catTree,JSON_FORCE_OBJECT);
$parentCategories = $categoryModel->getCategoryRecurse($active_category_id,0);


/* Laod tmpl default */
require(JModuleHelper::getLayoutPath('mod_virtuemart_category',$layout));
?>