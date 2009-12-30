<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
* VirtueMart TigraTree menu
* @author Greg Perkins
* @ Uses TigraTree Javascript: http://www.softcomplex.com/
* @version $Id$
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2004-2008 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.net
*
* This file is included by the virtuemart module and product categories module if the module parameter
* MenuType is set to tigratree
**/
mm_showMyFileName( __FILE__ );   

global $sess, $mosConfig_live_site;

// Decide which node to open (if any)
$Treeid = vmRequest::getInt( 'Treeid' );

// Get the root label
$root_label = $params->get( 'root_label', $VM_LANG->_('PHPSHOP_STORE_MOD') );

// Get the ps_product_category class
require_once( CLASSPATH . 'ps_product_category.php' );
if( !isset( $ps_product_category )) $ps_product_category = new ps_product_category;

// The tree generator
$vmTigraTree = new vmTigraTreeMenu();

// A unique name for our tree (to support multiple instances of the menu)
$varname = uniqid( "TigraTree_" );

// Get necessary scripts
if( vmIsJoomla('1.5')) {
	$js_src = $mosConfig_live_site.'/modules/mod_virtuemart';
} else {
	$js_src = $mosConfig_live_site.'/modules';
}
if( !defined( "_TIGRATREE_LOADED" )) {
	echo vmCommonHTML::scriptTag( $js_src.'/tigratree/tree_tpl.js.php' );
	echo vmCommonHTML::scriptTag( $js_src.'/tigratree/tree.js' );
	define ( "_TIGRATREE_LOADED", "1" );
}

// Create the menu output
$menu_htmlcode = "<div class=\"$class_mainlevel\" style=\"text-align:left;\">
<script type=\"text/javascript\"><!--
var TREE_ITEMS_$varname = [\n";

// Create the root node
$menu_htmlcode .= "['".$root_label."', '".$sess->url( 'index.php?page='.HOMEPAGE )."',\n";

// Get the actual category items
$vmTigraTree->traverse_tree_down($menu_htmlcode);

$menu_htmlcode .= "]];

var o_tree_$varname = new tree(TREE_ITEMS_$varname, TREE_TPL);
item_expand(o_tree_$varname, $Treeid);
o_tree_$varname.select($Treeid);
--></script>\n";

// Add a linked list in case JavaScript is disabled
$menu_htmlcode .= "<noscript>\n";
$menu_htmlcode .= $ps_product_category->get_category_tree( $category_id, $class_mainlevel );
$menu_htmlcode .= "\n</noscript>\n";
$menu_htmlcode .= "</div>";

echo $menu_htmlcode;

class vmTigraTreeMenu {
    /***************************************************
    * function traverse_tree_down
    */
	function traverse_tree_down(&$mymenu_content, $category_id='0', $level='0') {
		static $ibg = 0;
		global $db, $mosConfig_live_site;
		$level++;
		$query = "SELECT category_name as cname, category_id as cid, category_child_id as ccid "
		. "FROM #__{vm}_category as a, #__{vm}_category_xref as b "
		 . "WHERE a.category_publish='Y' AND "
		 . " b.category_parent_id='$category_id' AND a.category_id=b.category_child_id "
		 . "ORDER BY category_parent_id, list_order, category_name ASC";
		$db->query( $query );
		
		$categories = $db->record;
		
		if( !( $categories==null ) ) {
			$i = 1;
			$numCategories = count( $categories );
			foreach ($categories as $category) {
				$ibg++;
				$Treeid = $ibg;
				$itemid = isset($_REQUEST['Itemid']) ? '&Itemid='.intval($_REQUEST['Itemid'] ) : "";
	
				$mymenu_content.= str_repeat("\t", $level-1);
				if( $level > 1 && $i == 1 ){
					$mymenu_content.= ",";
				}
				$mymenu_content.= "['".$category->cname;
				$mymenu_content.= "','href=\"".sefRelToAbs('index.php?option=com_virtuemart&page=shop.browse&category_id='.$category->cid.'&Treeid='.$Treeid.$itemid)."\"'\n ";
				
				/* recurse through the subcategories */
				$this->traverse_tree_down($mymenu_content, $category->ccid, $level);
				$mymenu_content .= str_repeat("\t", $level-1);
	
				/* let's see if the loop has reached its end */
				if ( $i == sizeof( $categories ) && $level == 1) {
					$mymenu_content.= "]\n";
				}
				else {
					$mymenu_content.= "],\n";
				}
				$i++;
			}
		}
	}
}
?>
