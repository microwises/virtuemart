<?php
if( ! defined( '_VALID_MOS' ) && ! defined( '_JEXEC' ) )
	die( 'Direct Access to ' . basename( __FILE__ ) . ' is not allowed.' ) ;
/**
 *
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
 * http://virtuemart.org
 */
mm_showMyFileName( __FILE__ ) ;
require_once (CLASSPATH . 'ps_shopper_group.php') ;
global $ps_html, $ps_product,$hVendor ;
$title = JText::_( 'VM_PRICE_FORM_LBL' ) . '<br/>' ;

$product_id = JRequest::getVar(  'product_id', 0 ) ;
$product_price_id = JRequest::getVar(  'product_price_id', 0 ) ;
$product_parent_id = JRequest::getVar(  'product_parent_id' ) ;
$return_args = JRequest::getVar(  'return_args' ) ;
$option = empty( $option ) ? JRequest::getVar(  'option', 'com_virtuemart' ) : $option ;

$db = new ps_DB( ) ;
/* If Updating a Price */
if( ! empty( $product_price_id ) ) {
	/* Get field values for update */
	$q = "SELECT * FROM #__{vm}_product_price WHERE product_price_id='$product_price_id' " ;
	$db->query( $q ) ;
	$db->next_record() ;
} /* If Adding a new Price */
elseif( empty( $vars["error"] ) ) {
	/* Set default currency for product price */
	$default['product_currency'] = $vendor_currency ;
}

if( ! empty( $vars["product_price_id"] ) ) {
	$product_price_id = $vars["product_price_id"] ;
	if( empty( $product_parent_id ) ) {
		$title .= JText::_( 'VM_PRICE_FORM_UPDATE_FOR_PRODUCT' ) . " " ;
	} else {
		$title .= JText::_( 'VM_PRICE_FORM_UPDATE_FOR_ITEM' ) . " " ;
	}
} else {
	if( empty( $product_parent_id ) ) {
		$title .= JText::_( 'VM_PRICE_FORM_NEW_FOR_PRODUCT' ) . " " ;
	} else {
		$title .= JText::_( 'VM_PRICE_FORM_NEW_FOR_ITEM' ) . " " ;
	}
}

$url = $_SERVER['PHP_SELF'] . "?page=$modulename.product_form&product_id=$product_id&product_parent_id=$product_parent_id" ;
$title .= "<a href=\"" . $sess->url( $url ) . "\">" . $ps_product->get_field( $product_id, "product_name" ) . "</a>" ;

//First create the object and let it print a form heading
$formObj = &new formFactory( $title ) ;
//Then Start the form
$formObj->startForm() ;

?>

<table class="adminform">
	<tr>
		<td colspan="2"></td>
	</tr>
	<tr>
		<td class="labelcell"><?php
		echo JText::_( 'VM_PRODUCT_FORM_PRICE_NET' ) ?>:
      </td>
		<td><input type="text" class="inputbox" name="product_price"
			onkeyup="updateGross();" value="<?php
			$db->sp( "product_price" ) ;
			?>"
			size="10" maxlength="10" /></td>
	</tr>
	<tr>
		<td class="labelcell">
        <?php
								echo JText::_( 'VM_PRODUCT_FORM_PRICE_GROSS' ) ?>:
      </td>
		<td><input type="text" class="inputbox" onkeyup="updateNet();"
			name="product_price_incl_tax" size="10" /></td>
	</tr>
	<tr>
		<td class="labelcell"> 
			<?php
			echo JText::_( 'VM_PRICE_FORM_CURRENCY' ) ?>:
      </td>
		<td> 
        <?php
								$ps_html->list_currency( "product_currency", $db->sf( "product_currency" ) ) ?>
      </td>
	</tr>
	<tr>
		<td class="labelcell"> <?php
		echo JText::_( 'VM_PRICE_FORM_GROUP' ) ?>:
      </td>
		<td><?php
		echo ps_shopper_group::list_shopper_groups( "shopper_group_id", $db->sf( "shopper_group_id" ) ) ;
		?>
      </td>
	</tr>
	<tr>
		<td colspan="2" height="2">&nbsp;</td>
	</tr>
	<tr>
		<td class="labelcell"><?php
		echo JText::_( 'VM_PRODUCT_LIST_QUANTITY_START' ) ;
		?>:</td>
		<td><input type="text"
			value="<?php
			echo $db->f( "price_quantity_start" ) ?>" size="11"
			name="price_quantity_start" /></td>
	</tr>
	<tr>
		<td class="labelcell"><?php
		echo JText::_( 'VM_PRODUCT_LIST_QUANTITY_END' ) ;
		?>:</td>
		<td><input type="text"
			value="<?php
			echo $db->f( "price_quantity_end" ) ?>" size="11"
			name="price_quantity_end" /></td>
	</tr>
	<tr>
		<td colspan="2" height="22">&nbsp;</td>
	</tr>
</table>
<?php
// Add necessary hidden fields
$formObj->hiddenField( 'product_price_id', $product_price_id ) ;
$formObj->hiddenField( 'product_id', $product_id ) ;
$formObj->hiddenField( 'product_parent_id', $product_parent_id ) ;
$formObj->hiddenField( 'return_args', $return_args ) ;

$funcname = ! empty( $product_price_id ) ? "productPriceUpdate" : "productPriceAdd" ;

// finally close the form:
$formObj->finishForm( $funcname, $modulename . '.product_price_list', $option ) ;
?>

<script type="text/javascript">
// borrowed from OSCommerce with small modifications. 
// All rights reserved.

<?php
$tax_rate = $ps_product->get_product_taxrate( $product_id ) ;
echo "var tax_rate=$tax_rate;" ;
?>

function doRound(x, places) {
  return Math.round(x * Math.pow(10, places)) / Math.pow(10, places);
}

function getTaxRate() {

    return tax_rate;

}

function updateGross() {
  var taxRate = getTaxRate();
  
  var r = new RegExp("\,", "i");
  document.adminForm.product_price.value = document.adminForm.product_price.value.replace( r, "." );
  
  var grossValue = document.adminForm.product_price.value;
  
  if (taxRate > 0) {
    grossValue = grossValue * (taxRate + 1);
  }

  document.adminForm.product_price_incl_tax.value = doRound(grossValue, 5);
}

function updateNet() {
  var taxRate = getTaxRate();
  
  var r = new RegExp("\,", "i");
  document.adminForm.product_price_incl_tax.value = document.adminForm.product_price_incl_tax.value.replace( r, "." );
  
  var netValue = document.adminForm.product_price_incl_tax.value;

  if (taxRate > 0) {
    netValue = netValue / (taxRate + 1);
  }

  document.adminForm.product_price.value = doRound(netValue, 5);
}
updateGross();
</script>
