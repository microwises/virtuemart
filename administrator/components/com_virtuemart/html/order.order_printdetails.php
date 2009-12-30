<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: order.order_printdetails.php 1760 2009-05-03 22:58:57Z Aravot $
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
global $ps_order_status;
require_once(CLASSPATH.'ps_checkout.php');
require_once(CLASSPATH.'ps_userfield.php');
require_once(CLASSPATH.'ps_product.php');
require_once(CLASSPATH.'ps_order.php');
$ps_product= new ps_product;

$registrationfields = ps_userfield::getUserFields('registration', false, '', true, true );
$shippingfields = ps_userfield::getUserFields('shipping', false, '', true, true );
// Multiprint for orders by Daniel Jonsson, Xclude
$order_id_ar = vmRequest::getVar('order_id');
if(!is_array($order_id_ar)) {
	$order_id_ar = array($order_id_ar);
}

$dbc = new ps_DB;

echo "<br />". vmCommonHTML::PrintIcon() ."<br />";

for($i = 0; $i < count($order_id_ar); $i++) {
	$order_id = $order_id_ar[$i];
	//Vendor is based on order_id by Max Milbers
	$vendor_id = ps_order::get_vendor_id_by_order_id($order_id);

	$q = "SELECT * FROM #__{vm}_orders WHERE order_id=$order_id and vendor_id = $vendor_id";
	$db->query($q);
	$db->next_record();

	if (($i+1)!=count($order_id_ar)) {
		echo '<div style="page-break-after:always;">';
	} else {
		echo "<div >";
	}

	echo "<style type='text/css' media='print'>.vmNoPrint { display: none }</style>";
	?>
	<table width="100%" align="center" border="0" cellspacing="0" cellpadding="2">
  <tr>
    <td valign="top">
     <h2><?php echo JText::_('VM_ORDER_PRINT_PO_LBL') ?></h2>
     <p><?php echo ps_vendor::formatted_store_address(true,$vendor_id) ?></p>
    </td>
    <td valign="top" width="10%" align="right"><?php echo $vendor_image; ?></td>
  </tr>
</table>

<table border="0" cellspacing="0" cellpadding="2" width="100%">
  <!-- begin customer information -->
  <tr class="sectiontableheader">
    <th align="left" colspan="2"><?php echo JText::_('VM_ACC_ORDER_INFO') ?></th>
  </tr>
  <tr>
    <td><?php echo JText::_('VM_ORDER_PRINT_PO_NUMBER')?>:</td>
    <td><?php printf("%08d", $db->f("order_id")); ?></td>
  </tr>

  <tr>
    <td><?php echo JText::_('VM_ORDER_PRINT_PO_DATE') ?>:</td>
    <td><?php echo vmFormatDate( $db->f("cdate")); ?></td>
  </tr>
  <tr>
    <td><?php echo JText::_('VM_ORDER_PRINT_PO_STATUS') ?>:</td>
    <td><?php echo $ps_order_status->getOrderStatusName( $db->f("order_status") )   ?></td>
  </tr>
  <?php
  // Print the coupon code when available
  if( $db->f("coupon_code") ) { ?>
	  <tr>
		  <td><strong><?php echo JText::_('VM_COUPON_COUPON_HEADER') ?>:</strong></td>
		  <td><?php $db->p("coupon_code"); ?></td>
	  </tr>
	  <?php
  }
  ?>
  <!-- End Customer Information -->
  <!-- Begin 2 column bill-ship to -->
  <tr class="sectiontableheader">
    <th align="left" colspan="2"><?php echo JText::_('VM_ORDER_PRINT_CUST_INFO_LBL') ?></th>
  </tr>
  <tr valign="top">
    <td width="50%"> <!-- Begin BillTo --><?php
    // Get bill_to information
    $dbbt = new ps_DB;
    $q  = "SELECT * FROM #__{vm}_order_user_info WHERE user_id='" . $db->f("user_id") . "'  AND order_id='$order_id' ORDER BY address_type ASC";
    $dbbt->query($q);
    $dbbt->next_record();
    $user = $dbbt->get_row();
  ?>
      <table width="100%" cellspacing="0" cellpadding="2" border="0">
        <tr>
          <td colspan="2"><strong><?php echo JText::_('VM_ORDER_PRINT_BILL_TO_LBL') ?></strong></td>
        </tr>
	        <?php
		foreach( $registrationfields as $field ) {
			if( $field->name == 'email') $field->name = 'email';
			if($field->type == 'captcha') continue;
			?>
		  <tr>
			<td align="right"><?php echo JText::_($field->title) ? JText::_($field->title) : $field->title ?>:</td>
			<td><?php
				switch($field->name) {
		          	case 'country':
		          		require_once(CLASSPATH.'ps_country.php');
		          		$country = new ps_country();
		          		$dbc = $country->get_country_by_code($dbbt->f($field->name));
	          			if( $dbc !== false ) echo $dbc->f('country_name');
		          		break;
		          	default:
		          		echo $dbbt->f($field->name);
		          		break;
		          }
		          ?>
			</td>
		  </tr>
		  <?php
			}
		   ?>
      </table>
      <!-- End BillTo --> </td>
    <td width="50%"> <!-- Begin ShipTo --> <?php
    // Get ship_to information
    $dbbt->next_record();
    $dbst =& $dbbt;
  ?>
 <table width="100%" cellspacing="0" cellpadding="2" border="0">
        <tr>
          <td colspan="2"><strong><?php echo JText::_('VM_ORDER_PRINT_SHIP_TO_LBL') ?></strong></td>
        </tr>
        <?php
		foreach( $shippingfields as $field ) {
			if( $field->name == 'email') $field->name = 'email';
			?>
		  <tr>
			<td width="35%" align="right">&nbsp;<?php echo JText::_($field->title) ? JText::_($field->title) : $field->title ?>:</td>
			<td width="65%"><?php
				switch($field->name) {
		          	case 'country':
		          		require_once(CLASSPATH.'ps_country.php');
		          		$country = new ps_country();
		          		$dbc = $country->get_country_by_code($dbst->f($field->name));
		          		if( $dbc !== false ) echo $dbc->f('country_name');
		          		break;
		          	default:
		          		echo $dbst->f($field->name);
		          		break;
		          }
		          ?>
			</td>
		  </tr>
		  <?php
			}
		   ?>
      </table>
      <!-- End ShipTo -->
      <!-- End Customer Information -->
    </td>
  </tr>
  <tr>
    <td colspan="2">&nbsp;</td>
  </tr>
  <?php
  if ($PSHOP_SHIPPING_MODULES[0] != "no_shipping" && $db->f("ship_method_id")) {
  	$details = explode( "|", $db->f("ship_method_id"));
  	?>
  <tr>
    <td colspan="2">
      <table width="100%" border="0" cellspacing="0" cellpadding="1">

        <tr class="sectiontableheader">
          <th align="left"><?php echo JText::_('VM_ORDER_PRINT_CUST_SHIPPING_LBL') ?></th>
        </tr>
        <tr>
          <td>
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td><strong><?php echo JText::_('VM_ORDER_PRINT_SHIPPING_CARRIER_LBL') ?></strong></td>
                <td><strong><?php echo JText::_('VM_ORDER_PRINT_SHIPPING_MODE_LBL') ?></strong></td>
                <td><strong><?php echo JText::_('VM_ORDER_PRINT_PRICE') ?>&nbsp;</strong></td>
              </tr>
              <tr>
                <td><?php echo $details[1];  ?>&nbsp;</td>
                <td><?php echo $details[2]; ?></td>
                <td><?php echo $CURRENCY_DISPLAY->getFullValue($details[3], '', $db->f('order_currency')); ?></td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr><?php
     }

  ?>
  <tr>
    <td colspan="2">&nbsp;</td>
  </tr>
  <!-- Begin Order Items Information -->
  <tr class="sectiontableheader">
    <th align="left" colspan="2"><?php echo JText::_('VM_ORDER_ITEM') ?></th>
  </tr>
  <tr>
    <td colspan="2">
      <table width="100%" cellspacing="0" cellpadding="2" border="0">
        <tr align="left">
          <th><?php echo JText::_('VM_ORDER_PRINT_QTY') ?></th>
          <th><?php echo JText::_('VM_ORDER_PRINT_NAME') ?></th>
          <th><?php echo JText::_('VM_ORDER_PRINT_SKU') ?></th>
          <th><?php echo JText::_('VM_ORDER_PRINT_PRICE') ?></th>
          <th align="right"><?php echo JText::_('VM_ORDER_PRINT_TOTAL') ?>&nbsp;&nbsp;&nbsp;</th>
        </tr>
        <?php
      $dbcart = new ps_DB;
      $q  = "SELECT * FROM #__{vm}_order_item ";
      $q .= "WHERE #__{vm}_order_item.order_id='$order_id' ";
      $dbcart->query($q);
      $subtotal = 0;
      while ($dbcart->next_record()) {
?>
        <tr align="left">
          <td><?php $dbcart->p("product_quantity"); ?></td>
          <td><?php $dbcart->p("order_item_name"); echo " <font size=\"-2\">" . $dbcart->f("product_attribute") . "</font>";?></td>
          <td><?php $dbcart->p("order_item_sku"); ?></td>
          <td><?php /*
                $price = $ps_product->get_price($dbcart->f("product_id"));
                $item_price = $price["product_price"]; */
                $item_price = $dbcart->f("product_item_price");
               echo $CURRENCY_DISPLAY->getFullValue($item_price, '', $db->f('order_currency'));

           ?></td>
          <td align="right"><?php $total = $dbcart->f("product_quantity") * $item_price;
                $subtotal += $total;
                echo $CURRENCY_DISPLAY->getFullValue($total, '', $db->f('order_currency'));
           ?>&nbsp;&nbsp;&nbsp;</td>
        </tr><?php
      }
?>
        <tr>
          <td colspan="4" align="right">&nbsp;&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td colspan="4" align="right"><?php echo JText::_('VM_ORDER_PRINT_SUBTOTAL') ?> :</td>
          <td align="right"><?php echo $CURRENCY_DISPLAY->getFullValue($subtotal, '', $db->f('order_currency')) ?>&nbsp;&nbsp;&nbsp;</td>
        </tr>
<?php
      /* COUPON DISCOUNT */
      $coupon_discount = $db->f("coupon_discount");

      if( $coupon_discount > 0 ) {
        $subtotal -= $coupon_discount;
      ?>
        <tr>
          <td colspan="4" align="right"><?php echo JText::_('VM_COUPON_DISCOUNT') ?>:
          </td>
          <td align="right"><?php
            echo "- ".$CURRENCY_DISPLAY->getFullValue( $coupon_discount, '', $db->f('order_currency') ); ?>&nbsp;&nbsp;&nbsp;
          </td>
        </tr>
      <?php
      }
      if (($db->f("order_discount") != 0) && (PAYMENT_DISCOUNT_BEFORE == '1')) { ?>
        <tr>
            <td colspan="4" align="right"><?php
              if( $db->f("order_discount") > 0)
                echo JText::_('VM_PAYMENT_METHOD_LIST_DISCOUNT');
              else
                echo JText::_('VM_FEE');
                ?>:
            </td>
            <td align="right"><?php
          if ($db->f("order_discount") > 0 )
               echo "- ".$CURRENCY_DISPLAY->getFullValue(abs($db->f("order_discount")), '', $db->f('order_currency'));
          elseif ($db->f("order_discount") < 0 )
               echo "+ ".$CURRENCY_DISPLAY->getFullValue(abs($db->f("order_discount")), '', $db->f('order_currency')); ?>
            &nbsp;&nbsp;&nbsp;</td>
        </tr>

        <?php
      }
?>

        <tr>
          <td colspan="4" align="right"><?php echo JText::_('VM_ORDER_PRINT_SHIPPING') ?> :</td>
          <td align="right"><?php
            $shipping_total = $db->f("order_shipping");
            echo $CURRENCY_DISPLAY->getFullValue($shipping_total, '', $db->f('order_currency'));

            ?>&nbsp;&nbsp;&nbsp;</td>
        </tr>
        <tr>
          <td colspan="4" align="right"><?php echo JText::_('VM_ORDER_PRINT_TOTAL_TAX') ?> :</td>
          <td align="right"><?php
            $tax_total = $db->f("order_tax")+ $db->f("order_shipping_tax");
            echo $CURRENCY_DISPLAY->getFullValue($tax_total, '', $db->f('order_currency'));

            ?>&nbsp;&nbsp;&nbsp;</td>
        </tr>

        <tr>
          <td colspan="4" align="right">
          <?php if (PAYMENT_DISCOUNT_BEFORE == '1') { ?><strong><?php }

          echo JText::_('VM_CART_TOTAL') .":"; if (PAYMENT_DISCOUNT_BEFORE != '1') { ?></strong><?php } ?></td>

          <td align="right"><?php
          if (PAYMENT_DISCOUNT_BEFORE == '1') { ?><strong><?php
            $total = $db->f("order_total");
            echo $CURRENCY_DISPLAY->getFullValue($total, '', $db->f('order_currency'));
          }
          else {
            $total = $db->f("order_subtotal") + $db->f("order_tax") + $db->f("order_shipping") - $db->f("coupon_discount");
            echo $CURRENCY_DISPLAY->getFullValue($total, '', $db->f('order_currency'));
          }
          if (PAYMENT_DISCOUNT_BEFORE == '1') { ?></strong><?php } ?>&nbsp;&nbsp;&nbsp;</td>
        </tr>
        <?php
        if ($db->f("order_discount") != 0.00 && PAYMENT_DISCOUNT_BEFORE != '1') { ?>
        <tr>
        <td colspan="4" align="right"><?php
              if( $db->f("order_discount") > 0)
                echo JText::_('VM_PAYMENT_METHOD_LIST_DISCOUNT');
              else
                echo JText::_('VM_FEE');
                ?>:
        </td>
        <td align="right"><?php
          if ($db->f("order_discount") > 0 )
               echo "- ".$CURRENCY_DISPLAY->getFullValue(abs($db->f("order_discount")), '', $db->f('order_currency'));
          elseif ($db->f("order_discount") < 0 )
               echo "+ ".$CURRENCY_DISPLAY->getFullValue(abs($db->f("order_discount")), '', $db->f('order_currency'));
               ?>&nbsp;&nbsp;&nbsp;
        </td>
        </tr>
        <tr>
            <td colspan="4" align="right"><strong><?php echo JText::_('VM_CART_TOTAL') ?>: </strong></td>
        <td align="right"><strong><?php echo $CURRENCY_DISPLAY->getFullValue($db->f("order_total"), '', $db->f('order_currency')); ?>
        </strong>&nbsp;&nbsp;&nbsp;
          </td>
        </tr>
        <?php
        }
        ?>
        <tr>
            <td colspan="4" align="right">&nbsp;</td>
        <td align="right"><strong><?php echo ps_checkout::show_tax_details( $db->f('order_tax_details'), $db->f('order_currency') ); ?>
        </strong>&nbsp;&nbsp;&nbsp;
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <!-- End Order Items Information -->

<br />

  <!-- Begin Payment Information -->

      <table width="100%">
      <tr class="sectiontableheader">
        <th align="left" colspan="2"><?php echo JText::_('VM_ORDER_PRINT_PAYINFO_LBL') ?></th>
      </tr>
          <?php
          /** Retrieve Payment Info **/
          $dbpm = new ps_DB;
          $q  = "SELECT * FROM #__{vm}_payment_method, #__{vm}_order_payment, #__{vm}_orders ";
          $q .= "WHERE #__{vm}_order_payment.order_id='$order_id' ";
          $q .= "AND #__{vm}_payment_method.payment_method_id=#__{vm}_order_payment.payment_method_id ";
          $q .= "AND #__{vm}_orders.user_id='" . $db->f("user_id") . "' ";
          $q .= "AND #__{vm}_orders.order_id='$order_id' ";
          $dbpm->query($q);
          $dbpm->next_record(); ?>
      <tr>
        <td width="20%"><?php echo JText::_('VM_ORDER_PRINT_PAYMENT_LBL') ?> :</td>
        <td><?php $dbpm->p("name"); ?> </td>
      </tr>
	  <?php
          require_once(CLASSPATH.'paymentMethod.class.php');
          $vmPaymentMethod = new vmPaymentMethod();
          $payment = $dbpm->f("id");

          if ($vmPaymentMethod->is_creditcard($payment)) {

            // DECODE Account Number
            $dbaccount = new ps_DB;
            $q = 'SELECT '.VM_DECRYPT_FUNCTION.'(order_payment_number,\''.ENCODE_KEY.'\') as account_number
		  				FROM #__{vm}_order_payment WHERE order_id=\''.$order_id.'\'';
            $dbaccount->query($q);
            $dbaccount->next_record(); ?>
      <tr>
        <td width="10%"><?php echo JText::_('VM_ORDER_PRINT_ACCOUNT_NAME') ?> :</td>
        <td><?php $dbpm->p("order_payment_name"); ?> </td>
      </tr>
      <tr>
        <td><?php echo JText::_('VM_ORDER_PRINT_ACCOUNT_NUMBER') ?> :</td>
        <td><?php echo ps_checkout::asterisk_pad($dbaccount->f("account_number"),4);
    ?> </td>
      </tr>
      <tr>
        <td><?php echo JText::_('VM_ORDER_PRINT_EXPIRE_DATE') ?> :</td>
        <td><?php echo vmFormatDate($dbpm->f("order_payment_expire"), '%b-%Y'); ?> </td>
      </tr>
          <?php } ?>
      <!-- end payment information -->
      </table>

<?php // }

  /** Print out the customer note **/
  if ( $db->f("customer_note") ) {
    ?>
    <table width="100%">
      <tr>
        <td colspan="2">&nbsp;</td>
      </tr>
      <tr class="sectiontableheader">
        <th align="left" colspan="2"><?php echo JText::_('VM_ORDER_PRINT_CUSTOMER_NOTE') ?></th>
      </tr>
      <tr>
        <td colspan="2">
         <?php echo nl2br($db->f("customer_note"))."<br />"; ?>
       </td>
      </tr>
    </table>
    <?php
  }

// } /* End of security check */

	echo '</div>';
}
?>
<script type="text/javascript">
//<!--
window.document.title="<?php echo JText::_('VM_CHECK_OUT_THANK_YOU_PRINT_VIEW', false ); ?>";
//-->
</script>