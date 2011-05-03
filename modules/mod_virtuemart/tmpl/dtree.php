<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
* VirtueMart dTree menu
* @ Released under GNU/GPL License : http://www.gnu.org/copyleft/gpl.html
* @ dTree mos menu created by Winfred van Kuijk <winfred@vankuijk.net>
* @ modified by soeren
* @ Uses dTree Javascript: http://www.destroydrop.com/javascripts/tree/
* @ version $Id: mod_dtreemenu.php
*
* This file is included by the virtuemart module if the module parameter
* MenuType is set to treemenu
**/

	$js_src = JURI::root() .'/modules/mod_virtuemart/tmpl';

$Itemid = JRequest::getInt('Itemid', '');
$db = JFactory::getDBO();
/*********************************************************
************* CATEGORY TREE ******************************
*/

    /* dTree API, default value
	* change to fit your needs **/
    $useSelection =  'true';
    $useLines =  'true';
    $useIcons =  'true';
    $useStatusText =  'false';
    $useCookies =  'false';
    $closeSameLevel =  'false';
    
    // if all folders should be open, we will ignore the closeSameLevel
    $openAll =  'false';
    if ( $openAll == "true" ) { $closeSameLevel = "false"; }
    
    
    $menu_htmlcode = "";
    
	// what should be used as the base of the tree?
	// ( could be *first* menu item, *site* name, *module*, *menu* name or *text* )
	$base = "first";
	
	
	// in case *text* should be the base node, what text should be displayed?
	$basetext =  "";
	
	// what category_id is selected?
	$category_id = JRequest::getInt('category_id', '');
	
	// select menu items from database
	$query  = "SELECT category_id,category_parent_id,category_name FROM #__vm_category, #__vm_category_xref ";
	$query .= "WHERE #__vm_category.published='1' AND ";
	$query .= "#__vm_category.category_id=#__vm_category_xref.category_child_id ";
	$query .= "ORDER BY category_parent_id, ordering, category_name ASC";


	//$db->next_record();
	$db->setQuery($query);
	$categories = $db->loadObjectList();


	
	// create a unique tree identifier, in case multiple dtrees are used 
	// (max one per module)
	$tree = "d".uniqid( "tree_" );
	
	
	// start creating the content
	// create left aligned table, load the CSS stylesheet and dTree code
	$menu_htmlcode .= "<table border='0' cellspacing='1' cellpadding='0' width='100%'><tr><td align='left'>\n";
	$menu_htmlcode .= "<link rel='stylesheet' href='{$js_src}/dtree/dtree.css' type='text/css' />\n";
	$menu_htmlcode .= "<script type='text/javascript' src='{$js_src}/dtree/dtree.js'></script>\n";
	$menu_htmlcode .= "<script type='text/javascript'>\n";
	
	// create the tree, using the unique name
	// pass the live_site parameter on so dTree can find the icons
	$menu_htmlcode .= "$tree = new dTree('$tree',\"$js_src\");\n";
	
	// pass on the dTree API parameters
	$menu_htmlcode .= $tree.".config.useSelection=".$useSelection.";\n";
	$menu_htmlcode .= $tree.".config.useLines=".$useLines.";\n";
	$menu_htmlcode .= $tree.".config.useIcons=".$useIcons.";\n";
	$menu_htmlcode .= $tree.".config.useCookies=".$useCookies.";\n";
	$menu_htmlcode .= $tree.".config.useStatusText=".$useStatusText.";\n";
	$menu_htmlcode .= $tree.".config.closeSameLevel=".$closeSameLevel.";\n";
	
	$basename = jRequest::getVar('root_label');
	
	// what is the ID of this node?
	
	$baseid = $categories[0]->category_parent_id;
	// create the link (if not a menu item, no link [could be: to entry page of site])
	$baselink = ( $base == "first") ? JRoute::_('index.php?option=com_virtuemart' ) : "";

	// remember which item is open, normally $Itemid
	// except when we want the first item (e.g. Home) to be the base;
	// in that case we have to pretend all remaining items belong to "Home"
	$openid = $category_id;
   
	// it could be that we are displaying e.g. mainmenu in this dtree, 
	// but item in usermenu is selected, 
	// so: for the rest of this module track if this menu contains the selected item
	// Default value: first node (=baseid), but not selected
	$opento = $baseid;
	$opento_selected = "false";
	// what do you know... the first node was selected
	if ($baseid == $openid) { $opento_selected = "true"; }
	$target = "";
	
	// create the first node, parent is always -1
	$menu_htmlcode .= $tree.".add('{$baseid}','-1','{$basename}','{$baselink}','','{$target}')\n";
	// process each of the nodes
	foreach ($categories as $cat ) {
	
		// get name and link (just to save space in the code later on)
		$name = htmlentities( $cat->category_name, ENT_QUOTES,"UTF-8").'('. $ps_product_category->countProducts( $cat->category_id).')';
		$url = JRoute::_('index.php?option=com_virtuemart&view=category&category_id='.$cat->category_id);
		$menu_htmlcode .= $tree.'.add("'.$cat->category_id.'","'.$cat->category_parent_id.'","'.$name.'","'.$url.'","","'.$target.'");'."\n";
	  
		// if this node is the selected node
		if ($cat->category_id == $openid) { 
			$opento = $openid; $opento_selected = "true"; 
		}  
	}
	
	$menu_htmlcode .= "document.write({$tree});\n";
	$menu_htmlcode .= $openAll == "true" ? $tree."openAll();\n" : $tree.".closeAll();\n";
	$menu_htmlcode .= $tree.".openTo('{$opento}','{$opento_selected}');\n";
	$menu_htmlcode .= "</script>\n";
	$menu_htmlcode .= "<noscript>\n";
	$menu_htmlcode .= $ps_product_category->get_category_tree( $category_id, $class_mainlevel );
	$menu_htmlcode .= "</noscript>\n";
	$menu_htmlcode .= "</td></tr></table>\n";
	
	echo $menu_htmlcode;
        

/************* END OF CATEGORY TREE ******************************
*********************************************************
*/
?>