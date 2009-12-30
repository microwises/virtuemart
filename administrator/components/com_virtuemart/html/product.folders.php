<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
* Products & Categories in a dTree menu
* @author Soeren Eberhardt
* @ Uses dTree Javascript: http://www.destroydrop.com/javascripts/tree/
*
* @version $Id: product.folders.php 1760 2009-05-03 22:58:57Z Aravot $
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
* http://virtuemart.org
*/
mm_showMyFileName( __FILE__ );   
    
/*********************************************************
************* PRODUCT & CATEGORY TREE ******************************
*/

$vmFoldersMenu = new vmFoldersMenu();

vmCommonHTML::loadTigraTree();

$menu_htmlcode = "<br /><div style=\"text-align:left;margin-left:200px;\">
<script type=\"text/javascript\"><!--
var TREE_ITEMS = [
['{" . JText::_('VM_STORE_MOD') . "}', '{$_SERVER['PHP_SELF']}',
";
$vmFoldersMenu->traverse_tree_down($menu_htmlcode);
  
$menu_htmlcode .= "]];
new tree(TREE_ITEMS, TREE_TPL);
--></script>
</div>";

echo $menu_htmlcode;


class vmFoldersMenu {
    /***************************************************
    * function traverse_tree_down
    */
    function traverse_tree_down(&$mymenu_content, $category_id='0', $level='0') {
        static $ibg = -1;
        global $db, $module, $mosConfig_live_site;
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
            $Treeid = $ibg == 0 ? 1 : $ibg;
            $itemid = isset($_REQUEST['Itemid']) ? '&Itemid='.intval($_REQUEST['Itemid'] ) : "";
            $mymenu_content.= str_repeat("\t", $level-1);
            if( $level > 1 && $i == 1 ) { $mymenu_content.= ","; }
            $mymenu_content.= "['".$category->cname;
            $mymenu_content.= ps_product_category::products_in_category( $category->cid );
            $mymenu_content.= "','".$_SERVER['PHP_SELF'].'?option=com_virtuemart&page=product.product_category_form&category_id='.$category->cid."'\n ";
            
            $q = "SELECT #__{vm}_product.product_name,#__{vm}_product.product_id FROM #__{vm}_product, #__{vm}_product_category_xref ";
            $q .= "WHERE #__{vm}_product.product_id=#__{vm}_product_category_xref.product_id ";
            $q .= "AND #__{vm}_product_category_xref.category_id='".$category->cid."' ";
            $q .= "ORDER BY #__{vm}_product.product_name";
            $db->query( $q );
            $products = $db->record;
            $xx = 1;
            if( count( $products > 0 )) {
            	$mymenu_content .= ",\n";
            }
            foreach( $products as $product ) {
              // get name and link (just to save space in the code later on)
              $mymenu_content.= str_repeat("\t", $level)."['".$product->product_name;
              $url = $_SERVER['PHP_SELF'].'?option=com_virtuemart&page=product.product_form&product_id='.$product->product_id;
              $mymenu_content .= "','".$url."']";
              if( $xx++ < sizeof( $products ))
                $mymenu_content .= ",\n";
              else
                $mymenu_content .= "\n";
            }
                
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
        else {
            
        }
      }
}
/************* END OF CATEGORY TREE ******************************
*********************************************************
*/
?>