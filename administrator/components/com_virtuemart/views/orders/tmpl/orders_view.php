<?php
defined('_JEXEC') or die('Restricted access'); 
AdminMenuHelper::startAdminArea();
$orderbt = $this->order['details']['BT'];

$orderst = (array_key_exists('ST', $this->order['details'])) ? $this->order['details']['ST'] : $orderbt;
$history = $this->order['history'];
$items = $this->order['items'];
$payment = $this->order['payment'];
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
    <table class="adminlist">
	<tr>
	    <td valign="top">
		<table class="admintable" width="100%">
		    <tr>
			<td class="key" style="text-align: center;" colspan="2"><?php echo JText::_('VM_ORDER_PRINT_PO_LBL') ?></td>
		    </tr>
		    <tr>
			<td class="key"><strong><?php echo JText::_('VM_ORDER_PRINT_PO_NUMBER') ?>:</strong></td>
			<td><?php printf("%08d", $orderbt->order_id);?></td>
		    </tr>
		    <tr>
			<td class="key"><strong><?php echo JText::_('VM_ORDER_PRINT_PO_DATE') ?>:</strong></td>
			<td><?php echo date('Y-m-d H:i:s', $orderbt->cdate);?></td>
		    </tr>
		    <tr>
			<td class="key"><strong><?php echo JText::_('VM_ORDER_PRINT_PO_STATUS') ?>:</strong></td>
			<td><?php echo $orderbt->order_status_name; ?></td>
		    </tr>
		    <tr>
			<td class="key"><strong><?php echo JText::_('VM_ORDER_PRINT_PO_IPADDRESS') ?>:</strong></td>
			<td><?php $orderbt->ip_address; ?></td>
		    </tr>
		    <?php
		    if (VmConfig::get('enable_coupons') == '1') { ?>
		    <tr>
			<td class="key"><strong><?php echo JText::_('VM_COUPON_COUPON_HEADER') ?>:</strong></td>
			<td><?php echo $orderbt->coupon_code; ?></td>
		    </tr>
			<?php } ?>
		</table>
	    </td>
	    <td valign="top">
		<table class="adminlist">
		    <thead>
		    <tr>
			<th><?php echo JText::_('VM_ORDER_HISTORY_DATE_ADDED') ?></th>
			<th><?php echo JText::_('VM_ORDER_HISTORY_CUSTOMER_NOTIFIED') ?></th>
			<th><?php echo JText::_('VM_ORDER_LIST_STATUS') ?></th>
			<th><?php echo JText::_('VM_COMMENT') ?></th>
		    </tr>
		    <?php
		    foreach ($history as $orderbt_event ) {
			echo "<tr>";
			echo "<td>".$orderbt_event->date_added."</td>\n";
			if ($orderbt_event->customer_notified == 1) {
			    echo '<td align="center">Yes</td>';
			}
			else {
			    echo '<td align="center">No</td>';
			}
			echo '<td align="center">'.$orderbt_event->order_status_code.'</td>';
			echo "<td>".$orderbt_event->comments."</td>\n";
			echo "</tr>\n";
		    }
		    ?>
		    </thead>
		</table>
	    </td>
	</tr>
    </table>
    &nbsp;
    <table width="100%">
	<tr>
	    <td width="50%" valign="top">
		<table class="admintable" width="100%">
		<thead>
		<tr>
		    <td class="key" style="text-align: center;"  colspan="2"><?php echo JText::_('VM_ORDER_PRINT_BILL_TO_LBL') ?></td>
		</tr>
		</thead>
		    <?php
		    foreach ($this->userfields['details'] as $field ) {
			if ($field->type == 'captcha') continue;
			?>
			<tr>
			<td class="key">&nbsp;<?php echo JText::_($field->title) ? JText::_($field->title) : $field->title ?>:</td>
			<td><?php
				$fieldvalue = $field->name;
				if (empty($fieldvalue) || empty($orderbt->$fieldvalue))
				    echo "&nbsp;";
				else echo $orderbt->$fieldvalue;
				?>
			</td>
			</tr>
			<?php
		    }
		    ?>
		</table>
	    </td>
	    <td width="50%" valign="top">
		<table class="admintable" width="100%">
		<thead>
		<tr>
		    <td class="key" style="text-align: center;"  colspan="2"><?php echo JText::_('VM_ORDER_PRINT_SHIP_TO_LBL') ?></td>
		</tr>
		</thead>
		<?php
		    foreach ($this->shippingfields['details'] as $field ) {
			?>
		    <tr>
			<td class="key">&nbsp;<?php echo JText::_($field->title) ? JText::_($field->title) : $field->title ?>:</td>
			<td><?php
				$fieldvalue = $field->name;
				if (empty($fieldvalue) || empty($orderst->$fieldvalue))
				    echo "&nbsp;";
				else echo $orderst->$fieldvalue;
				?>
			</td>
		    </tr>
			<?php
		    }
		    ?>
		</table>
	    </td>
	</tr>
    </table>

    <table class="adminlist">
	<tr>
	    <td colspan="2">
		<table class="adminlist">
		    <thead>
		    <tr>
			    <!-- <th class="title" width="5%" align="left"><?php echo JText::_('VM_ORDER_EDIT_ACTIONS') ?></th> -->
			<th class="title" width="50" align="left"><?php echo JText::_('VM_ORDER_PRINT_QUANTITY') ?></th>
			<th class="title" width="*" align="left"><?php echo JText::_('VM_ORDER_PRINT_NAME') ?></th>
			<th class="title" width="10%" align="left"><?php echo JText::_('VM_ORDER_PRINT_SKU') ?></th>
			<th class="title" width="10%"><?php echo JText::_('VM_ORDER_PRINT_PO_STATUS') ?></th>
			<th class="title" width="50"><?php echo JText::_('VM_PRODUCT_FORM_PRICE_NET') ?></th>
			<th class="title" width="50"><?php echo JText::_('VM_PRODUCT_FORM_PRICE_GROSS') ?></th>
			<th class="title" width="5%"><?php echo JText::_('VM_ORDER_PRINT_TOTAL') ?></th>
			<th class="title" width="22%"><?php echo JText::_('VM_ORDER_PRINT_INTNOTES') ?></th>
		    </tr>
		    <?php
		    foreach ($items as $item) { ?>
		    <tr valign="top">
			<td><?php echo $item->product_quantity; ?></td>
			<td><?php echo $item->order_item_name; ?></td>
			<td><?php echo $item->order_item_sku; ?></td>
			<td><?php echo $item->order_status; ?></td>
			<td><?php echo $item->product_item_price; ?></td>
			<td><?php echo $item->product_final_price; ?></td>
			<td><?php echo $item->product_quantity * $item->product_final_price; ?></td>
			<td><div align="right"><?php echo $item->intnotes; ?></div></td>
		    </tr>
			<?php } ?>
		    </thead>
		</table>
		<table  class="adminlist">
		    <tr>
			<td align="right" colspan="7"><div align="right"><strong> <?php echo JText::_('VM_ORDER_PRINT_SUBTOTAL') ?>: </strong></div></td>
			<td width="5%" align="right" style="padding-right: 5px;"><?php echo $orderbt->order_subtotal; ?></td>
		    </tr>
		    <?php
		    /* COUPON DISCOUNT */
		    if (VmConfig::get('payment_discount_before') == '1') {
			if ($orderbt->order_discount != 0) {
			    ?>
		    <tr>
			<td align="right" colspan="7"><strong>
					<?php
					if ($orderbt->order_discount > 0) echo JText::_('VM_PAYMENT_METHOD_LIST_DISCOUNT');
					else echo JText::_('VM_FEE');
					?>:</strong></td>
			<td width="5%" align="right" style="padding-right: 5px;"><?php
				    if ($orderbt->order_discount > 0 ) echo "-" . $orderbt->order_discount;
				    elseif ($orderbt->order_discount < 0 )  echo "+" . $ordert->order_discount; ?>
			</td>
		    </tr>
			    <?php
			}
			if ($orderbt->coupon_discount > 0 || $orderbt->coupon_discount < 0) {
			    ?>
		    <tr>
			<td align="right" colspan="7"><strong><?php echo JText::_('VM_COUPON_DISCOUNT') ?>:</strong></td>
			<td  width="5%" align="right" style="padding-right: 5px;"><?php
				    echo "- ".$orderbt->coupon_discount; ?>
			</td>
		    </tr>
			    <?php
			}
		    }?>
		    <tr>
			<td align="right" colspan="7"><strong><?php echo JText::_('VM_ORDER_PRINT_TOTAL_TAX') ?>:</strong></td>
			<td width="5%" align="right" style="padding-right: 5px;"><?php echo $orderbt->order_tax; ?></td>
		    </tr>
		    <tr>
			<td align="right" colspan="7"><strong><?php echo JText::_('VM_ORDER_PRINT_SHIPPING') ?>:</strong></td>
			<td width="5%" align="right" style="padding-right: 5px;"><?php echo $orderbt->order_shipping; ?></td>
		    </tr>
		    <tr>
			<td align="right" colspan="7"><strong><?php echo JText::_('VM_ORDER_PRINT_SHIPPING_TAX') ?>:</strong></td>
			<td width="5%" align="right" style="padding-right: 5px;"><?php echo $orderbt->order_shipping_tax; ?></td>
		    </tr>
		    <?php
		    if (VmConfig::get('payment_discount_before') != '1') {
			if ($orderbt->order_discount != 0) {
			    ?>
		    <tr>
			<td align="right" colspan="7"><strong><?php
					if( $orderbt->order_discount > 0) echo JText::_('VM_PAYMENT_METHOD_LIST_DISCOUNT');
					else echo JText::_('VM_FEE');
					?>:</strong>
			</td>
			<td width="5%" align="right" style="padding-right: 5px;"><?php
				    if ($orderbt->order_discount > 0 )
					echo "-" . $orderbt->order_discount;
				    elseif ($orderbt->order_discount < 0 ) echo "+".$orderbt->order_discount; ?>
			</td>
		    </tr>
			    <?php
			}
			if( $orderbt->coupon_discount > 0 || $orderbt->coupon_discount < 0) {
			    ?>
		    <tr>
			<td align="right" colspan="7"><strong><?php echo JText::_('VM_COUPON_DISCOUNT') ?>:</strong></td>
			<td width="5%" align="right" style="padding-right: 5px;"><?php echo "- ".$orderbt->coupon_discount; ?></td>
		    </tr>
			    <?php
			}
		    }
		    ?>
		    <tr>
			<td align="right" colspan="7"><strong><?php echo JText::_('VM_CART_TOTAL') ?>:</strong></td>
			<td width="5%" align="right" style="padding-right: 5px;">
			    <strong><?php echo $orderbt->order_total; ?></strong>
			</td>
		    </tr>
		    <?php
		    /* Get the tax details, if any */
		    //$tax_details = ps_checkout::show_tax_details( $db->f('order_tax_details'), $db->f('order_currency') );
		    ?>
		    <?php if (!empty( $tax_details)) { ?>
		    <tr>
			<td colspan="8" align="right"><?php echo $tax_details; ?></td>
		    </tr>
			<?php }; ?>
		</table>
		<?php //$ps_order_change_html->html_change_add_item();
		?>
	    </td>
	</tr>
    </table>
    &nbsp;
    <table class="adminlist">
	<tr>
	    <td valign="top" width="300">
		<table class="adminlist">
		    <thead>
		    <tr>
			<th><?php echo JText::_('VM_ORDER_PRINT_SHIPPING_LBL') ?></th>
		    </tr>
		    <tr>
			<td align="left">
			    <?php
			    if  ($orderbt->ship_method_id) {
				$details = explode( "|", $orderbt->ship_method_id);
			    }
			    ?>
			    <strong><?php echo JText::_('VM_ORDER_PRINT_SHIPPING_CARRIER_LBL') ?>: </strong>
			    <?php  echo $details[1]; ?>&nbsp;
			</td>
		    </tr>
		    <tr>
			<td align="left">
			    <strong><?php echo JText::_('VM_ORDER_PRINT_SHIPPING_MODE_LBL') ?>: </strong>
			    <?php echo $details[2]; ?>
			</td>
		    </tr>
		    <tr>
			<td align="left">
			    <strong><?php echo JText::_('VM_ORDER_PRINT_SHIPPING_PRICE_LBL') ?>: </strong>
			    <?php echo $details[3]; ?>
			</td>
		    </tr>
		    </thead>
		</table>
	    </td>
	    <td valign="top" width="*">
		<table class="adminlist">
		    <thead>
		    <tr>
			<th width="13%"><?php echo JText::_('VM_ORDER_PRINT_PAYMENT_LBL') ?></th>
			<th width="40%"><?php echo JText::_('VM_ORDER_PRINT_ACCOUNT_NAME') ?></th>
			<th width="30%"><?php echo JText::_('VM_ORDER_PRINT_ACCOUNT_NUMBER'); ?></th>
			<th width="17%"><?php echo JText::_('VM_ORDER_PRINT_EXPIRE_DATE') ?></th>
		    </tr>
		    <tr>
			<td width="13%">
			    <?php // $ps_order_change_html->html_change_payment($payment->id)
			    ?>
			</td>
			<td width="40%"><?php $payment->order_payment_name;?></td>
			<td width="30%">
			    <?php
			    //echo ps_checkout::asterisk_pad( $payment->account_number, 4, true );
			    if( $payment->order_payment_code) {
				echo '<br/>(' . JText::_('VM_ORDER_PAYMENT_CCV_CODE') . ': '.$payment->order_payment_code.') ';
			    }
			    ?>
			</td>
			<td width="17%"><?php echo date('M-Y', $payment->order_payment_expire); ?></td>
		    </tr>
		    <tr class="sectiontableheader">
			<th colspan="4"><?php echo JText::_('VM_ORDER_PRINT_PAYMENT_LOG_LBL') ?></th>
		    </tr>
		    <tr>
			<td colspan="4"><?php if($payment->order_payment_log) echo $payment->order_payment_log; else echo "./."; ?></td>
		    </tr>
		    </thead>
		</table>
	    </td>
	</tr>
	<tr>
	    <!-- Customer Note -->
	    <td valign="top" width="30%" colspan="2">
		<table class="adminlist">
		    <thead>
		    <tr>
			<th><?php echo JText::_('VM_ORDER_PRINT_CUSTOMER_NOTE') ?></th>
		    </tr>
		    <tr>
			<td valign="top" align="center" width="50%">
			    <?php //$ps_order_change_html->html_change_customer_note();
			    ?>
			</td>
		    </tr>
		    </thead>
		</table>
	    </td>
	</tr>
    </table>
    <!-- Hidden Fields -->
    <input type="hidden" name="option" value="com_virtuemart" />
    <input type="hidden" name="view" value="orders" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="order_id" value="<?php echo $orderbt->order_id; ?>">
</form>
<?php AdminMenuHelper::endAdminArea(); ?>
