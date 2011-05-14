<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
* VirtueMart TigraTree menu
* @author Greg Perkins
* @ Uses TigraTree Javascript: http://www.softcomplex.com/
* @version $Id: vm_tigratree.php 2413 2010-05-25 21:34:59Z milbo $
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
//mm_showMyFileName( __FILE__ );   

global $sess, $mosConfig_live_site;

// Decide which node to open (if any)
$Treeid = JRequest::getInt('Treeid', '');


// Get the root label
$root_label = $params->get( 'root_label', JText::_('COM_VIRTUEMART_STORE_MOD') );

// The tree generator
$vmTigraTree = new vmTigraTreeMenu();

// A unique name for our tree (to support multiple instances of the menu)
$varname = uniqid( "TigraTree_" );

// Get necessary scripts


if( !defined( "_TIGRATREE_LOADED" )) {
	$icon_path = JURI::root().'/modules/mod_virtuemart/tmpl/tigratree/icons/';
	$jsVars = " var TREE_TPL = {
	'target'  : '_self',	// name of the frame links will be opened in
							// other possible values are: _blank, _parent, _search, _self and _top

	'icon_e'  : '{$icon_path}empty.gif', // empty image
	'icon_l'  : '{$icon_path}line.gif',  // vertical line

        'icon_32' : '{$icon_path}base.gif',   // root leaf icon normal
        'icon_36' : '{$icon_path}base.gif',   // root leaf icon selected

	'icon_48' : '{$icon_path}base.gif',   // root icon normal
	'icon_52' : '{$icon_path}base.gif',   // root icon selected
	'icon_56' : '{$icon_path}base.gif',   // root icon opened
	'icon_60' : '{$icon_path}base.gif',   // root icon selected
	
	'icon_16' : '{$icon_path}folder.gif', // node icon normal
	'icon_20' : '{$icon_path}folderopen.gif', // node icon selected
	'icon_24' : '{$icon_path}folderopen.gif', // node icon opened
	'icon_28' : '{$icon_path}folderopen.gif', // node icon selected opened

	'icon_0'  : '{$icon_path}page.gif', // leaf icon normal
	'icon_4'  : '{$icon_path}page.gif', // leaf icon selected
	
	'icon_2'  : '{$icon_path}joinbottom.gif', // junction for leaf
	'icon_3'  : '{$icon_path}join.gif',       // junction for last leaf
	'icon_18' : '{$icon_path}plusbottom.gif', // junction for closed node
	'icon_19' : '{$icon_path}plus.gif',       // junctioin for last closed node
	'icon_26' : '{$icon_path}minusbottom.gif',// junction for opened node
	'icon_27' : '{$icon_path}minus.gif'       // junctioin for last opended node
	};";
	$document = JFactory::getDocument();
	$document->addScriptDeclaration($jsVars);
	JHTML::script('tree.js', 'modules/mod_virtuemart/tmpl/tigratree/', false);
	define ( "_TIGRATREE_LOADED", "1" );
}

// Create the menu output
$menu_htmlcode = "<div class=\"$class_mainlevel\" style=\"text-align:left;\">
<script type=\"text/javascript\"><!--
var TREE_ITEMS_$varname = [\n";

// Create the root node
$menu_htmlcode .= "['".$root_label."', '".JRoute::_("index.php?option=com_virtuemart" )."',\n";

// Get the actual category items
$vmTigraTree->traverse_tree_down($menu_htmlcode);

$menu_htmlcode .= "]];

var o_tree_$varname = new tree(TREE_ITEMS_$varname, TREE_TPL);
item_expand(o_tree_$varname, $Treeid);
o_tree_$varname.select($Treeid);
--></script>\n";

// Add a linked list in case JavaScript is disabled
$menu_htmlcode .= "<noscript>\n";
$menu_htmlcode .= $ps_product_category->get_category_tree( $virtuemart_category_id, $class_mainlevel );
$menu_htmlcode .= "\n</noscript>\n";
$menu_htmlcode .= "</div>";

echo $menu_htmlcode;

class vmTigraTreeMenu {
    /***************************************************
    * function traverse_tree_down
    */
	function traverse_tree_down(&$mymenu_content, $virtuemart_category_id='0', $level='0') {
		static $ibg = 0;
		$db = JFactory::getDBO();
		$level++;
		$q = "SELECT category_name as cname, virtuemart_category_id as cid, category_child_id as ccid "
		. "FROM #__virtuemart_categories as a, #__virtuemart_category_categories as b "
		 . "WHERE a.published='1' AND "
		 . " b.category_parent_id='{$virtuemart_category_id}' AND a.virtuemart_category_id=b.category_child_id "
		 . "ORDER BY category_parent_id, ordering, category_name ASC";
		$db->setQuery($q);
		$categories = $db->loadObjectList();
		
		if( $categories ) {
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
				$mymenu_content.= "['".htmlspecialchars($category->cname,ENT_QUOTES);
				$mymenu_content.= "','href=\"".JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_category_id='.$category->cid.'&Treeid='.$Treeid.$itemid)."\"'\n ";
				
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
