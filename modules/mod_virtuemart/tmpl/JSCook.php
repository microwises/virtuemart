<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
* VirtueMart JSCookTree menu
* @ Released under GNU/GPL License : http://www.gnu.org/copyleft/gpl.html
* @ JSCookTree VirtueMart menu created by Soeren
* @ modified by soeren
* @ Uses JSCookTree Javascript: http://www.cs.ucla.edu/~heng/JSCookTree/
* @ version $Id: vm_JSCook.php 2413 2010-05-25 21:34:59Z milbo $
*
* This file is included by the virtuemart module if the module parameter
* MenuType is set to jscooktree
**/


if( !class_exists('vmCategoryTree')) {
	class vmCategoryTree {
		/***************************************************
		* function traverse_tree_down
		*/
		function traverse_tree_down(&$mymenu_content, $category_id='0', $level='0') {
			static $ibg = -1;
			$db = JFactory::getDBO();
			$level++;
			$q = "SELECT category_name, category_id, category_child_id "
			. "FROM #__virtuemart_categories as a, #__vm_category_xref as b "
			. "WHERE a.published='1' AND "
			. " b.category_parent_id='{$category_id}' AND a.category_id=b.category_child_id "
			. "ORDER BY category_parent_id, ordering, category_name ASC";
			$db->setQuery($q);
			
			$categories = $db->loadObjectList();

			foreach ($categories as $cat) {
				$ibg++;
				$treeid = $ibg == 0 ? 1 : $ibg;
				$itemid = '&Itemid='.JRequest::getInt('Itemid', '');
				$mymenu_content.= ",\n[null,'".htmlspecialchars($cat->category_name,ENT_QUOTES);
				//$mymenu_content.= $ps_product_category->products_in_category( $cat->category_id);
				$mymenu_content.= "','".JRoute::_('index.php?option=com_virtuemart&view=category&category_id='.$cat->category_id.$itemid.'&treeid='.$treeid)."','_self','".htmlspecialchars($cat->category_name,ENT_QUOTES)."'\n ";

				// recurse through the subcategories 
				self::traverse_tree_down($mymenu_content, $cat->category_child_id, $level);

				// let's see if the loop has reached its end 
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
			$level++;
			$db = JFactory::getDBO();
			$q = "SELECT category_name, category_id, category_child_id "
			. "FROM #__virtuemart_categories as a, #__vm_category_xref as b "
			. "WHERE a.published='1' AND "
			. " b.category_parent_id='$category_id' AND a.category_id=b.category_child_id "
			. "ORDER BY category_parent_id, ordering, category_name ASC";
			$db->setQuery($q);
			
			$categories = $db->loadObjectList();
			$itemid = '&Itemid='.JRequest::getInt('Itemid', '');
			if ($categories) {
				foreach ($categories as $category) {
					
					if( $ibg != 0 )
					$mymenu_content.= ",";

					$mymenu_content.= "\n[ '<img src=\"' + ctThemeXPBase + 'darrow.png\" alt=\"arr\" />','".htmlspecialchars($cat->category_name,ENT_QUOTES)."','".JRoute::_('index.php?option=com_virtuemart&view=category&category_id='.$cat->category_id.$itemid )."',null,'".htmlspecialchars($cat->category_name,ENT_QUOTES)."'\n ";

					$ibg=1;

					// recurse through the subcategories 
					self::traverse_tree_down($mymenu_content, $cat->category_child_id, $level);

					// let's see if the loop has reached its end 
					$mymenu_content.= "]";
				}
			}
		}
	}
}

$Itemid = JRequest::getInt('Itemid', '');
$treeid = JRequest::getInt('treeid', '');

$tmplPath = JURI::root().'/modules/mod_virtuemart/tmpl/';


	if($jscookTree_style == "ThemeXP") {
		$jscook_tree = "ctThemeXP1";
	}
	if($jscookTree_style == "ThemeNavy") {
		$jscook_tree = "ctThemeNavy";
	}
	
	JHTML::script('JSCookTree.js', $tmplPath, false);
	//JHTML::script('theme.js', $tmplPath.$jscookTree_style.'/', false);
	JHTML::stylesheet ( 'theme.css', $tmplPath.$jscookTree_style.'/', false );


	$vm_jscook = new vmCategoryTree();
	if ($jscook_tree == "ctThemeNavy" ) {
		$JStheme = "
		var ctThemeXPBase=\"".$tmplPath."/ThemeXP/\";
		var ctThemeNavy =
		{
			folderLeft: [['','']],
			folderRight: [['<img alt=\"\" src=\"' + ctThemeXPBase + 'open.gif\" />', '<img alt=\"\" src=\"' + ctThemeXPBase + 'close.gif\" />']],
			folderConnect: [[['&nbsp;','&nbsp;'],['&nbsp;','&nbsp;']]],
			itemLeft: [''],
			itemRight: [''],
			itemConnect: [['&nbsp;','&nbsp;']],
			spacer: [['&nbsp;&nbsp;&nbsp;','&nbsp;&nbsp;&nbsp;']],
			themeLevel: 1
		};";
	} elseif ($jscook_tree == "ctThemeXP1" ) {
	
		$JStheme = "	
			var ctThemeXPBase=\"".$tmplPath."/ThemeXP/\";
		var ctThemeXP1 =
		{
			folderLeft: [['<img alt=\"\" src=\"' + ctThemeXPBase + 'folder1.gif\" />', '<img alt=\"\" src=\"' + ctThemeXPBase + 'folderopen1.gif\" />']],
			folderRight: [['', '']],
			folderConnect: [[['<img alt=\"\" src=\"' + ctThemeXPBase + 'plus.gif\" />','<img alt=\"\" src=\"' + ctThemeXPBase + 'minus.gif\" />'],
							 ['<img alt=\"\" src=\"' + ctThemeXPBase + 'plusbottom.gif\" />','<img alt=\"\" src=\"' + ctThemeXPBase + 'minusbottom.gif\" />']]],
			itemLeft: ['<img alt=\"\" src=\"' + ctThemeXPBase + 'page.gif\" />'],
			itemRight: [''],
			itemConnect: [['<img alt=\"\" src=\"' + ctThemeXPBase + 'join.gif\" />', '<img alt=\"\" src=\"' + ctThemeXPBase + 'joinbottom.gif\" />']],
			spacer: [['<img alt=\"\" src=\"' + ctThemeXPBase + 'line.gif\" />', '<img alt=\"\" src=\"' + ctThemeXPBase + 'spacer.gif\" />']],
			themeLevel: 1
		};

		var ctThemeXP2 =
		{
			folderLeft: [['<img alt=\"\" src=\"' + ctThemeXPBase + 'folder2.gif\" />', '<img alt=\"\" src=\"' + ctThemeXPBase + 'folderopen2.gif\" />']],
			folderRight: [['', '']],
			folderConnect: [[['',''],['','']],[['<img alt=\"\" src=\"' + ctThemeXPBase + 'plus.gif\" />','<img alt=\"\" src=\"' + ctThemeXPBase + 'minus.gif\" />'],
							 ['<img alt=\"\" src=\"' + ctThemeXPBase + 'plusbottom.gif\" />','<img alt=\"\" src=\"' + ctThemeXPBase + 'minusbottom.gif\" />']]],
			itemLeft: ['<img alt=\"\" src=\"' + ctThemeXPBase + 'page.gif\" />'],
			itemRight: [''],
			itemConnect: [['',''],['<img alt=\"\" src=\"' + ctThemeXPBase + 'join.gif\" />', '<img alt=\"\" src=\"' + ctThemeXPBase + 'joinbottom.gif\" />']],
			spacer: [['',''],['<img alt=\"\" src=\"' + ctThemeXPBase + 'line.gif\" />', '<img alt=\"\" src=\"' + ctThemeXPBase + 'spacer.gif\" />']],
			themeLevel: 1
		};";
	}
	$document = JFactory::getDocument();
	$document->addScriptDeclaration($JStheme);
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
$menu_htmlcode .= "var treeindex = ctDraw ('div_$varname', $varname, $jscook_tree, '$jscookTree_style', 0, 0);";

$menu_htmlcode .="
--></script>\n";

if( $treeid ) {
	$menu_htmlcode .= "<input type=\"hidden\" id=\"treeid\" name=\"treeid\" value=\"$treeid\" />\n";
	$menu_htmlcode .= "<script language=\"JavaScript\" type=\"text/javascript\">ctExposeTreeIndex( treeindex, parseInt(ctGetObject('treeid').value));</script>\n";
}

$menu_htmlcode .= "<noscript>";
//$menu_htmlcode .= $ps_product_category->get_category_tree( $category_id, $class_mainlevel );
$menu_htmlcode .= "\n</noscript>\n";
echo $menu_htmlcode;

?>