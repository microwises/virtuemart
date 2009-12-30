<?php 
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id: product.product_move.php 1755 2009-05-01 22:45:17Z rolandd $
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
global $ps_product_category;

$old_category_id = JRequest::getVar(  'old_category_id' );
if( $old_category_id ) {
	$category_id = $old_category_id;
}
$products = vmGet( $_POST, 'product_id' );
$count= count( $products );

echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post" name="adminForm">';

for( $i=0; $i < $count; $i++ ) {
	$products[$i] = intval($products[$i]);
	echo '<input type="hidden" name="product_id[]" value="'.$products[$i].'" />';
}

$db->query( 'SELECT `product_name` FROM `#__{vm}_product` WHERE `product_id` IN('.implode(',', $products).') ORDER BY `product_name`');

echo "\n<h1 class=\"header\">".JText::_('VM_PRODUCTS_MOVE_LBL')."</h1>\n";


echo '<table class="adminform"><tr>';
echo '<th>'.JText::_('VM_PRODUCTS_MOVE_TO_CATEGORY')."</th>\n";
echo '<th>'.sprintf( JText::_('VM_PRODUCTS_MOVE_LIST'), $count )."</th>\n";

echo '</tr><tr>';

echo '<td valign="top">';
$ps_product_category->list_all( 'category_id', 0, array(), 10, false, false, array($category_id) );
echo "</td>\n";

echo "<td>\n";

echo '<ul style="text-align:left;height:250px;max-height:300px;overflow:auto;">';
while( $db->next_record()) {
	echo '<li>'.$db->f('product_name')."</li>\n";
}
echo "</ul>\n";
echo '</td></tr></table>';

echo '<input type="hidden" name="page" value="product.product_list" />
<input type="hidden" name="func" value="moveProduct" />
<input type="hidden" name="vmtoken" value="'.vmSpoofValue($sess->getSessionId()).'" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="pshop_mode" value="admin" />
<input type="hidden" name="option" value="'.$option.'" />
<input type="hidden" name="old_category_id" value="'.$category_id.'" />
</form>';
?>