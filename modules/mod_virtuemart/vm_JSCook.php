<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
* VirtueMart JSCookTree menu
* @ Released under GNU/GPL License : http://www.gnu.org/copyleft/gpl.html
* @ JSCookTree VirtueMart menu created by Soeren
* @ modified by soeren
* @ Uses JSCookTree Javascript: http://www.cs.ucla.edu/~heng/JSCookTree/
* @ version $Id$
*
* This file is included by the virtuemart module if the module parameter
* MenuType is set to jscooktree
**/
global $mosConfig_live_site, $mainframe, $root_label, $jscook_type, $jscookMenu_style, $jscookTree_style, $ps_product_category;
require_once( CLASSPATH . 'ps_product_category.php' );
if( !isset( $ps_product_category )) $ps_product_category = new ps_product_category;


if( !class_exists('vmCategoryTree')) {
class vmCategoryTree {
	/***************************************************
	* function traverse_tree_down
	*/
	function traverse_tree_down(&$mymenu_content, $category_id='0', $level='0') {
		static $ibg = -1;
		global $mosConfig_live_site, $sess;
		$db = new ps_DB();
		$level++;
		$query = "SELECT category_name, category_id, category_child_id "
		. "FROM #__{vm}_category as a, #__{vm}_category_xref as b "
		. "WHERE a.published='1' AND "
		. " b.category_parent_id='$category_id' AND a.category_id=b.category_child_id "
		. "ORDER BY category_parent_id, ordering, category_name ASC";
		$db->query( $query );

		while( $db->next_record() ) {
			$ibg++;
			$Treeid = $ibg == 0 ? 1 : $ibg;
			$itemid = '&Itemid='.$sess->getShopItemid();
			$mymenu_content.= ",\n[null,'".$db->f("category_name",false);
			$mymenu_content.= ps_product_category::products_in_category( $db->f("category_id") );
			$mymenu_content.= "','".sefRelToAbs('index.php?option=com_virtuemart&page=shop.browse&category_id='.$db->f("category_id").$itemid."&TreeId=$Treeid")."','_self','".$db->f("category_name",false)."'\n ";

			/* recurse through the subcategories */
			$this->traverse_tree_down($mymenu_content, $db->f("category_child_id"), $level);

			/* let's see if the loop has reached its end */
			$mymenu_content.= "]";

		}
	}
}
}
/************* END OF CATEGORY TREE ******************************
*********************************************************
*/

if( !class_exists('vmCategoryMenu')) {
class vmCategoryMenu {
	/***************************************************
	* function traverse_tree_down
	*/
	function traverse_tree_down(&$mymenu_content, $category_id='0', $level='0') {
		static $ibg = 0;
		global $mosConfig_live_site, $sess;
		$level++;
		$query = "SELECT category_name, category_id, category_child_id "
		. "FROM #__{vm}_category as a, #__{vm}_category_xref as b "
		. "WHERE a.published='1' AND "
		. " b.category_parent_id='$category_id' AND a.category_id=b.category_child_id "
		. "ORDER BY category_parent_id, ordering, category_name ASC";
		$db = new ps_DB();
		$db->query( $query );

		while($db->next_record()) {
			$itemid = '&Itemid='.$sess->getShopItemid();
			if( $ibg != 0 )
			$mymenu_content.= ",";

			$mymenu_content.= "\n[ '<img src=\"' + ctThemeXPBase + 'darrow.png\" alt=\"arr\" />','".$db->f("category_name",false)."','".sefRelToAbs('index.php?option=com_virtuemart&page=shop.browse&category_id='.$db->f("category_id").$itemid)."',null,'".$db->f("category_name",false)."'\n ";

			$ibg++;

			/* recurse through the subcategories */
			$this->traverse_tree_down($mymenu_content, $db->f("category_child_id"), $level);

			/* let's see if the loop has reached its end */
			$mymenu_content.= "]";
		}
	}
}
}


$Itemid = vmRequest::getInt( 'Itemid' );
$TreeId = vmRequest::getInt( 'TreeId' );

if( vmIsJoomla('1.5')) {
	$js_src = 'modules/mod_virtuemart';
} else {
	$js_src = 'modules';
}
echo vmCommonHTML::scriptTag( '', 'var ctThemeXPBase = "'.$js_src.'/ThemeXP/";' );
if( $jscook_type == "tree" ) {

	if($jscookTree_style == "ThemeXP") {
		$jscook_tree = "ctThemeXP1";
	}
	if($jscookTree_style == "ThemeNavy") {
		$jscook_tree = "ctThemeNavy";
	}

	echo vmCommonHTML::scriptTag( $js_src.'/JSCookTree.js' );
	echo vmCommonHTML::linkTag( $js_src."/$jscookTree_style/theme.css" );
	echo vmCommonHTML::scriptTag( $js_src."/$jscookTree_style/theme.js" );

	$vm_jscook = new vmCategoryTree();
}
else {

	echo vmCommonHTML::scriptTag( $mosConfig_live_site.'/includes/js/JSCookMenu.js' );
    echo vmCommonHTML::linkTag( $mosConfig_live_site."/includes/js/$jscookMenu_style/theme.css" );
    echo vmCommonHTML::scriptTag( $mosConfig_live_site."/includes/js/$jscookMenu_style/theme.js" );

	$vm_jscook = new vmCategoryMenu();
}

// create a unique tree identifier, in case multiple trees are used
// (max one per module)
$varname = "JSCook_".uniqid( $jscook_type."_" );

$menu_htmlcode = "<div align=\"left\" class=\"mainlevel\" id=\"div_$varname\"></div>
<script type=\"text/javascript\"><!--
var $varname = 
[
";
$vm_jscook->traverse_tree_down($menu_htmlcode);


$menu_htmlcode .= "];
";
if(  $jscook_type == "tree" ) {
	$menu_htmlcode .= "var treeindex = ctDraw ('div_$varname', $varname, $jscook_tree, '$jscookTree_style', 0, 0);";
}
else
$menu_htmlcode .= "cmDraw ('div_$varname', $varname, '$menu_orientation', cm$jscookMenu_style, '$jscookMenu_style');";

$menu_htmlcode .="
--></script>\n";

if(  $jscook_type == "tree" ) {
	if( $TreeId ) {
		$menu_htmlcode .= "<input type=\"hidden\" id=\"TreeId\" name=\"TreeId\" value=\"$TreeId\" />\n";
		$menu_htmlcode .= "<script language=\"JavaScript\" type=\"text/javascript\">ctExposeTreeIndex( treeindex, parseInt(ctGetObject('TreeId').value));</script>\n";
	}
}
$menu_htmlcode .= "<noscript>";
$menu_htmlcode .= $ps_product_category->get_category_tree( $category_id, $class_mainlevel );
$menu_htmlcode .= "\n</noscript>\n";
echo $menu_htmlcode;

?>