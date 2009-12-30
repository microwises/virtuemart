<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id: product.product_attribute_form.php 1760 2009-05-03 22:58:57Z Aravot $
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
mm_showMyFileName( __FILE__ );
global $ps_product;
$product_id = $vars["product_id"];

if( is_array( $product_id ))
	$product_id = (int)$product_id[0];

$product_parent_id = JRequest::getVar( 'product_parent_id', 0);
$attribute_name = JRequest::getVar( 'attribute_name', 0);
$return_args = JRequest::getVar( 'return_args' );
$option = empty($option)?JRequest::getVar(  'option', 'com_virtuemart'):$option;

$title = JText::_('VM_ATTRIBUTE_FORM_LBL').'<br />';

if (!empty($attribute_name)) {
  if (empty($product_parent_id)) {
    $title .= JText::_('VM_ATTRIBUTE_FORM_UPDATE_FOR_PRODUCT') . " ";
  } 
  else {
    $title .= JText::_('VM_ATTRIBUTE_FORM_UPDATE_FOR_ITEM') . " ";
  }
} 
else {
  if (empty($product_parent_id)) {
    $title .= JText::_('VM_ATTRIBUTE_FORM_NEW_FOR_PRODUCT') . " ";
  } 
  else {
    $title .= JText::_('VM_ATTRIBUTE_FORM_NEW_FOR_ITEM') . " ";
  }
}

$url = $_SERVER['PHP_SELF'] . "?page=$modulename.product_form&product_id=$product_id&product_parent_id=$product_parent_id";
$title .= '<a href="' . $sess->url($url) . '">'. $ps_product->get_field($product_id,'product_name').'</a>'; 

if ($attribute_name) {
  $db = new ps_DB;
  $q = "SELECT * FROM #__{vm}_product_attribute_sku WHERE product_id='$product_id' ";
  $q .= "AND attribute_name = '$attribute_name' ";
  $db->query($q); 
  $db->next_record();
}

//First create the object and let it print a form heading
$formObj = &new formFactory( $title );
//Then Start the form
$formObj->startForm();

?> 
<table class="adminform">
	<tr> 
		<td width="23%" height="20" valign="top"> 
			<div align="right"><?php echo JText::_('VM_ATTRIBUTE_FORM_NAME') ?>:</div>
		</td>
		<td width="77%" height="20"> 
			<input type="text" class="inputbox" name="attribute_name" value="<?php $db->sp("attribute_name"); ?>" size="32" maxlength="255" />
		</td>
	</tr>
	<tr> 
		<td width="23%" height="10" valign="top"> 
			<div align="right"><?php echo JText::_('VM_ATTRIBUTE_FORM_ORDER') ?>:</div>
		</td>
		<td width="77%" height="10"> 
			<input type="text" class="inputbox" name="attribute_list" value="<?php $db->sp("attribute_list"); ?>" size="5" maxlength="11" />
		</td>
	</tr>
	<tr> 
		<td colspan="2" height="22">&nbsp;</td>
	</tr>
</table>
<?php
// Add necessary hidden fields
$formObj->hiddenField( 'product_id', $product_id );
$formObj->hiddenField( 'old_attribute_name', $attribute_name );
$formObj->hiddenField( 'return_args', $return_args );

$funcname = !empty($attribute_name) ? "productAttributeUpdate" : "productAttributeAdd";

// Write your form with mixed tags and text fields
// and finally close the form:
$formObj->finishForm( $funcname, $modulename.'.product_attribute_list', $option );
?>