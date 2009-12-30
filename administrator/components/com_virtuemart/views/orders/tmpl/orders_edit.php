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
<table class="adminlist" style="table-layout: fixed;">
	<tr> 
		<td valign="top"> 
			<table border="0" cellspacing="0" cellpadding="1">
				<tr class="sectiontableheader"> 
					<th colspan="2"><?php echo JText::_('VM_ORDER_PRINT_PO_LBL') ?></th>
				</tr>
				<tr> 
					<td><strong><?php echo JText::_('VM_ORDER_PRINT_PO_NUMBER') ?>:</strong></td>
					<td><?php printf("%08d", $orderbt->order_id);?></td>
				</tr>
				<tr> 
					<td><strong><?php echo JText::_('VM_ORDER_PRINT_PO_DATE') ?>:</strong></td>
					<td><?php echo date('Y-m-d H:i:s', $orderbt->cdate);?></td>
				</tr>
				<tr> 
					<td><strong><?php echo JText::_('VM_ORDER_PRINT_PO_STATUS') ?>:</strong></td>
					<td><?php echo $orderbt->order_status_name; ?></td>
				</tr>
				<tr>
					<td><strong><?php echo JText::_('VM_ORDER_PRINT_PO_IPADDRESS') ?>:</strong></td>
					<td><?php $orderbt->ip_address; ?></td>
				</tr>
				<?php 
				if (Vmconfig::getVar('pshop_coupons_enable') == '1') { ?>
					<tr>
						<td><strong><?php echo JText::_('VM_COUPON_COUPON_HEADER') ?>:</strong></td>
						<td><?php echo $orderbt->coupon_code; ?></td>
					</tr>
				<?php } ?>
			</table>
		</td>
		<td valign="top">
			<?php $pane = JPane::getInstance('tabs'); 
				echo $pane->startPane("order_change_page");
				echo $pane->startPanel( JText::_('VM_ORDER_STATUS_CHANGE'), 'order_change_page' );
				?>
				<table class="adminform">
					<tr>
						<td class="labelcell"><?php echo JText::_('VM_ORDER_PRINT_PO_STATUS') .":"; ?></td>
						<td>
							<?php echo JHTML::_('select.genericlist', $this->orderstatuses, 'order_status', '', 'value', 'text', $orderbt->order_status); ?>
							<input type="hidden" name="current_order_status" value="<?php $orderbt->order_status; ?>" />
						</td>
					</tr>
					<tr>
						<td class="labelcell" valign="top"><?php echo JText::_('VM_COMMENT') .":"; ?></td>
						<td><textarea name="order_comment" rows="5" cols="25"></textarea></td>
					</tr>
					<tr>
						<td class="labelcell"><label for="notify_customer"><?php echo JText::_('VM_ORDER_LIST_NOTIFY') ?></label></td>
						<td><input type="checkbox" name="notify_customer" id="notify_customer" checked="checked" value="Y" /></td> 
					</tr>
					<tr>
						<td class="labelcell">
							<label for="include_comment"><?php echo JText::_('VM_ORDER_HISTORY_INCLUDE_COMMENT') ?></label>
						</td>
						<td>
							<input type="checkbox" name="include_comment" id="include_comment" checked="checked" value="Y" /> 
						</td>
					</tr>
				</table>	
				<?php 
					echo $pane->endPanel();
					echo $pane->startPanel( JText::_('VM_ORDER_HISTORY'), 'order_history_page' );
				?>
				<table class="adminlist">
					<tr >
						<th><?php echo JText::_('VM_ORDER_HISTORY_DATE_ADDED') ?></th>
						<th><?php echo JText::_('VM_ORDER_HISTORY_CUSTOMER_NOTIFIED') ?></th>
						<th><?php echo JText::_('VM_ORDER_LIST_STATUS') ?></th>
						<th><?php echo JText::_('VM_COMMENT') ?></th>
					</tr>
					<?php 
					foreach ($history as $orderbt_event ) {
						echo "<tr>";
						echo "<td>".$orderbt_event->date_added."</td>\n";
						echo "<td align=\"center\"><img alt=\"" . JText::_('VM_ORDER_STATUS_ICON_ALT') ."\" src=\"".JURI::root()."administrator/images/";
						echo $orderbt_event->customer_notified == 1 ? 'tick.png' : 'publish_x.png';
						
						echo "\" border=\"0\" align=\"absmiddle\" /></td>\n";
						echo "<td>".$orderbt_event->order_status_code."</td>\n";
						echo "<td>".$orderbt_event->comments."</td>\n";
						echo "</tr>\n";
					}
					?>
				</table>
			<?php 
				echo $pane->endPanel();
				echo $pane->endPane();
			?>
		</td>
	</tr>
</table>
&nbsp;
<table class="adminlist" width="100%" >
	<tr> 
		<th width="50%"  valign="top"><?php echo JText::_('VM_ORDER_PRINT_BILL_TO_LBL') ?></th>
		<th width="50%" valign="top"><?php echo JText::_('VM_ORDER_PRINT_SHIP_TO_LBL') ?></th>
	</tr>
	<tr> 
		<td valign="top"> 
			<table width="100%" border="0" cellspacing="0" cellpadding="1">
			<?php 
			foreach ($this->userfields as $field ) {
				if ($field->type == 'captcha') continue;
				?>
				<tr> 
					<td width="35%" align="right">&nbsp;<?php echo JText::_($field->title) ? JText::_($field->title) : $field->title ?>:</td>
					<td width="65%" align="left"><?php
						$fieldvalue = $field->name;
						if (empty($fieldvalue)) echo "&nbsp;";
						else echo $orderbt->$fieldvalue;
						?>
					</td>
				</tr>
				<?php
			}
			?>
			<?php // $ps_order_change_html->html_change_bill_to($user_id) 
			?>  
			</table>
		</td>
		<td valign="top">
			<table width="100%" border="0" cellspacing="0" cellpadding="1">
			<?php 
			foreach ($this->shippingfields as $field ) {
				?>
				<tr> 
					<td width="35%" align="right">&nbsp;<?php echo JText::_($field->title) ? JText::_($field->title) : $field->title ?>:</td>
					<td width="65%" align="left"><?php
					$fieldvalue = $field->name;
					if (empty($fieldvalue)) echo "&nbsp;";
					else echo $orderst->$fieldvalue;
					?>
					</td>
				</tr>
				<?php
			}
			?>
			<?php // $ps_order_change_html->html_change_ship_to($user_id) 
			?>  
			</table>
		</td>
	</tr>
</table>
<table class="adminlist">
	<tr> 
		<td colspan="2"> 
			<table class="adminlist">
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
					<td><div align="right"><?php echo $item->intnotes; ?></td>
				</tr>
			<?php } ?>
			</table>
			<table  class="adminlist">
				<tr> 
					<td align="right" colspan="7"><div align="right"><strong> <?php echo JText::_('VM_ORDER_PRINT_SUBTOTAL') ?>: </strong></div></td>
					<td width="5%" align="right" style="padding-right: 5px;"><?php echo $orderbt->order_subtotal; ?></td>
				</tr>
				<?php
				/* COUPON DISCOUNT */
				if (Vmconfig::getVar('payment_discount_before') == '1') {
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
				if (Vmconfig::getVar('payment_discount_before') != '1') {
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
				<?php // $ps_order_change_html->html_change_shipping(); 
				?>
			</table>
		</td>
		<td valign="top" width="*">
			<table class="adminlist">
				<tr class="sectiontableheader"> 
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
				<tr>
					<td colspan="2" align="center">
						<?php // $ps_order_change_html->html_change_discount();
						?>
					</td>
					<td colspan="2" align="center">
						<?php //$ps_order_change_html->html_change_coupon_discount();
						?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<!-- Customer Note -->
		<td valign="top" width="30%" colspan="2">
			<table class="adminlist">
				<tr>
					<th><?php echo JText::_('VM_ORDER_PRINT_CUSTOMER_NOTE') ?></th>
				</tr>
				<tr>
					<td valign="top" align="center" width="50%">
						<?php //$ps_order_change_html->html_change_customer_note();
						?>  
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<!-- Hidden Fields -->
<input type="hidden" name="task" value="save" />
<input type="hidden" name="option" value="com_virtuemart" />
<input type="hidden" name="pshop_mode" value="admin" />
<input type="hidden" name="page" value="product.product_list" />
<input type="hidden" name="view" value="orders" />
<input type="hidden" name="func" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
</form>
<?php AdminMenuHelper::endAdminArea(); ?>
