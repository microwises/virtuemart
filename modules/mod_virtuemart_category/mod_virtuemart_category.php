<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
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

$categories = $categoryModel->getChildrenList($category_id) ;
/*		$q = "SELECT virtuemart_category_id, category_name
			FROM #__virtuemart_categories, #__virtuemart_category_categories
			WHERE #__virtuemart_category_categories.category_parent_id = ".$category_id."
			AND #__virtuemart_categories.virtuemart_category_id=#__virtuemart_category_categories.category_child_id
			AND #__virtuemart_categories.virtuemart_vendor_id = 1
			AND #__virtuemart_categories.enabled = 1
			ORDER BY #__virtuemart_categories.ordering, #__virtuemart_categories.category_name ASC";
$db->setQuery($q);
$categories = $db->loadObjectList();*/
if(empty($categories)) return false;


foreach ($categories as $category) {
$category->childs = $categoryModel->getChildrenList($category->virtuemart_category_id) ;
}
$parentCategories = $categoryModel->getCategoryRecurse($active_category_id,0);


/* Laod tmpl default */
require(JModuleHelper::getLayoutPath('mod_virtuemart_category',$layout));
?>