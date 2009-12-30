<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id: product.product_product_type_form.php 1760 2009-05-03 22:58:57Z Aravot $
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2004-2007 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/

$product_id = JRequest::getVar( 'product_id', 0);
$return_args = JRequest::getVar( 'return_args');
$option = empty($option)?JRequest::getVar(  'option', 'com_virtuemart'):$option;

if( is_array( $product_id ))
	$product_id = (int)$product_id[0];

$product_parent_id = JRequest::getVar( 'product_parent_id', 0);

$title = '<img src="'. IMAGEURL .'ps_image/categories.gif" border="0" />'.JText::_('VM_PRODUCT_PRODUCT_TYPE_FORM_LBL');
if (!empty($product_parent_id)) {
  $title .= " " . JText::_('VM_PRODUCT_FORM_ITEM_LBL') . ": ";
} else {
  $title .= " " . JText::_('VM_PRODUCT') . ": ";
}
$url = $_SERVER['PHP_SELF'] . "?page=$modulename.product_form&product_id=$product_id&product_parent_id=$product_parent_id";
$title .= "<a href=\"" . $sess->url($url) . "\">". $ps_product->get_field($product_id,"product_name"). "</a>";

//First create the object and let it print a form heading
$formObj = &new formFactory( $title );
//Then Start the form
$formObj->startForm();

?>
<br /><br />

<table class="adminform">
    <tr> 
      <td valign="top" colspan="2"> 
        </td>
    </tr>
    <tr> 
      <td width="23%" height="20" valign="middle" > 
        <div align="right"><?php echo JText::_('VM_PRODUCT_PRODUCT_TYPE_FORM_PRODUCT_TYPE') ?>:</div>
      </td>
      <td width="77%" height="10" >
        <select class="inputbox" name="product_type_id">
          <?php 
	$q  = "SELECT * FROM #__{vm}_product_product_type_xref ";
	$q .= "WHERE product_id='".$product_id."'";
	$db->query( $q );
              
	$q  = "SELECT product_type_id, product_type_name, product_type_list_order ";
	$q .= "FROM `#__{vm}_product_type` ";
	$first = true;
	while( $db->next_record() ) {
		if( $first ) { $q .= " WHERE "; $first = false; }
		$q .= "product_type_id != '".$db->f("product_type_id")."' ";
		if (!$db->is_last_record() ) { $q .= "AND "; }
	}
	$q .= "ORDER BY product_type_list_order ASC";
	$db->query( $q );
    
	while( $db->next_record() ) {
		echo "<option value=\"".$db->f("product_type_id")."\">".$db->f("product_type_name")."</option>";
	}
	echo "</select>";
	?>
      </td>
    </tr>
</table>

<?php
// Add necessary hidden fields
$formObj->hiddenField( 'product_id', $product_id );
$formObj->hiddenField( 'product_parent_id', $product_parent_id );
$formObj->hiddenField( 'return_args', $return_args );
//
// finally close the form:
$formObj->finishForm( 'productProductTypeAdd', $modulename.'.product_product_type_list', $option );
?>
