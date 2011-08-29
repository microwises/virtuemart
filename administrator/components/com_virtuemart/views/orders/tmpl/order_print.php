<?php
/**
 * Print orderdetails
 *
 * @package	VirtueMart
 * @subpackage Orders
 * @author Oscar van Eijk
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id$
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
?>
<head>
<?php
echo '<link rel="stylesheet" href="'.'templates'.DS.'system'.DS.'css'.DS.'system.css'.'" type="text/css" />'."\n";
echo '<link rel="stylesheet" href="'.'templates'.DS.'khepri'.DS.'css'.DS.'template.css'.'" type="text/css" />'."\n";
?>
<title><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_PO_LBL'). ' ' . $this->orderID; ?></title>
</head>
<body onload="javascript:print();">
<table class="adminlist">
	<tr>
		<td valign="top" width="50%">
		<table class="adminlist">
			<tr>
				<td><strong><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_PO_NUMBER') ?>:</strong></td>
				<td><?php printf("%08d", $this->orderbt->virtuemart_order_id);?></td>
			</tr>
			<tr>
				<td><strong><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_PO_DATE') ?>:</strong></td>
				<td><?php echo $this->orderbt->created_on;?></td>
			</tr>
			<tr>
				<td><strong><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_PO_STATUS') ?>:</strong></td>
				<td><?php echo JText::_($this->orderbt->order_status_name); ?></td>
			</tr>
			<?php if (VmConfig::get('enable_coupons') == '1') { ?>
			<tr>
				<td><strong><?php echo JText::_('COM_VIRTUEMART_COUPON_CODE') ?>:</strong></td>
				<td><?php echo $this->orderbt->coupon_code; ?></td>
			</tr>
			<?php } ?>
		</table>
		</td>
	</tr>
</table>
&nbsp;
<table class="adminlist">
	<tr>
		<td valign="top">
			<strong><em><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_BILL_TO_LBL') ?></em></strong><br/>
			<table border="0"><?php
				foreach ($this->userfields['fields'] as $_field ) {
					if (!empty($_field['value'])) {
						echo '<tr><td class="key">'.$_field['title'].'</td>'
							.'<td>'.$_field['value'].'</td></tr>';
					}
				}
			?></table>
		</td>
		<td valign="top">
			<strong><em><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_SHIP_TO_LBL') ?></em></strong><br/>
			<table border="0"><?php
				foreach ($this->shippingfields['fields'] as $_field ) {
					if (!empty($_field['value'])) {
						echo '<tr><td class="key">'.$_field['title'].'</td>'
							.'<td>'.$_field['value'].'</td></tr>';
					}
				}
			?></table>
		</td>
	</tr>
</table>
<hr width="100%">
<table class="adminlist" width="100%">
	<tr>
		<td colspan="2">
		<table class="adminlist">
			<thead>
				<tr>
					<th class="title" width="47" align="left"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_QUANTITY') ?></th>
					<th class="title" width="*" align="left"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_NAME') ?></th>
					<th class="title" width="10%" align="left"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_SKU') ?></th>
					<th class="title" width="10%"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_PO_STATUS') ?></th>
					<th class="title" width="50"><?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_PRICE_NET') ?></th>
					<th class="title" width="50"><?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_PRICE_GROSS') ?></th>
					<th class="title" width="5%"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_TOTAL') ?></th>
				</tr>
			</thead>
			<?php foreach ($this->order['items'] as $item) { ?>
			<tr valign="top">
				<td><?php echo $item->product_quantity; ?></td>
				<td><?php
				echo $item->order_item_name;
				if (!empty($item->product_attribute)) {
					echo '<div>'.$item->product_attribute.'</div>';
				}
				?></td>
				<td><?php echo $item->order_item_sku; ?></td>
				<td align="center"><?php echo $this->orderstatuslist[$item->order_status]; ?>
				</td>
				<td><?php echo $item->product_item_price.' price '.$this->currency->priceDisplay($item->product_item_price,'',false); ?>
				</td>
				<td><?php echo $this->currency->priceDisplay($item->product_final_price,'',false); ?>
				</td>
				<td><?php echo $this->currency->priceDisplay($item->product_quantity * $item->product_final_price,'',false); ?>
				</td>
			</tr>

			<?php } ?>
		</table>
		<table class="adminlist">
			<tr>
				<td align="right" colspan="5">
				<div align="right"><strong> <?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_SUBTOTAL') ?>:
				</strong></div>
				</td>
				<td width="5%" align="right" style="padding-right: 5px;"><?php echo $this->currency->priceDisplay($this->orderbt->order_subtotal,'',false); ?></td>
			</tr>
			<?php
			/* COUPON DISCOUNT */
			if (VmConfig::get('payment_discount_before') == '1') {
				if ($this->orderbt->order_discount != 0) {
					?>
			<tr>
				<td align="right" colspan="5"><strong> <?php
				if ($this->orderbt->order_discount > 0) echo JText::_('COM_VIRTUEMART_PAYMENTMETHOD_LIST_DISCOUNT');
				else echo JText::_('COM_VIRTUEMART_FEE');
				?>:</strong></td>
				<td width="5%" align="right" style="padding-right: 5px;"><?php
				if ($this->orderbt->order_discount > 0 ) echo "-" . $this->currency->priceDisplay($this->orderbt->order_discount,'',false);
				elseif ($this->orderbt->order_discount < 0 )  echo "+" . $this->currency->priceDisplay($ordert->order_discount,'',false); ?>
				</td>
			</tr>
			<?php
				}
				if ($this->orderbt->coupon_discount > 0 || $this->orderbt->coupon_discount < 0) {
					?>
			<tr>
				<td align="right" colspan="5"><strong><?php echo JText::_('COM_VIRTUEMART_COUPON_DISCOUNT') ?>:</strong></td>
				<td width="5%" align="right" style="padding-right: 5px;"><?php
				echo "- ".$this->orderbt->coupon_discount; ?></td>
			</tr>
			<?php
				}
			}?>
			<tr>
				<td align="right" colspan="5"><strong><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_TOTAL_TAX') ?>:</strong></td>
				<td width="5%" align="right" style="padding-right: 5px;"><?php echo $this->currency->priceDisplay($this->orderbt->order_tax,'',false); ?></td>
			</tr>
			<tr>
				<td align="right" colspan="5"><strong><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_SHIPPING') ?>:</strong></td>
				<td width="5%" align="right" style="padding-right: 5px;"><?php echo $this->currency->priceDisplay($this->orderbt->order_shipping,'',false); ?></td>
			</tr>
			<tr>
				<td align="right" colspan="5"><strong><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_SHIPPING_TAX') ?>:</strong></td>
				<td width="5%" align="right" style="padding-right: 5px;"><?php echo $this->currency->priceDisplay($this->orderbt->order_shipping_tax,'',false); ?></td>
			</tr>
			<?php
			if (VmConfig::get('payment_discount_before') != '1') {
				if ($this->orderbt->order_discount != 0) {
					?>
			<tr>
				<td align="right" colspan="5"><strong><?php
				if( $this->orderbt->order_discount > 0) echo JText::_('COM_VIRTUEMART_PAYMENTMETHOD_LIST_DISCOUNT');
				else echo JText::_('COM_VIRTUEMART_FEE');
				?>:</strong></td>
				<td width="5%" align="right" style="padding-right: 5px;"><?php
				if ($this->orderbt->order_discount > 0 )
				echo "-" . $this->currency->priceDisplay($this->orderbt->order_discount,'',false);
				elseif ($this->orderbt->order_discount < 0 ) echo "+".$this->currency->priceDisplay($this->orderbt->order_discount,'',false); ?>
				</td>
			</tr>
			<?php
				}
				if( $this->orderbt->coupon_discount > 0 || $this->orderbt->coupon_discount < 0) {
					?>
			<tr>
				<td align="right" colspan="5"><strong><?php echo JText::_('COM_VIRTUEMART_COUPON_DISCOUNT') ?>:</strong></td>
				<td width="5%" align="right" style="padding-right: 5px;"><?php echo "- ".$this->currency->priceDisplay($this->orderbt->coupon_discount,'',false); ?></td>
			</tr>
			<?php
				}
			}
			?>
			<tr>
				<td align="right" colspan="5"><strong><?php echo JText::_('COM_VIRTUEMART_CART_TOTAL') ?>:</strong></td>
				<td width="5%" align="right" style="padding-right: 5px;"><strong><?php echo $this->currency->priceDisplay($this->orderbt->order_total,'',false); ?></strong>
				</td>
			</tr>
			<?php
			/* Get the tax details, if any */
			//$tax_details = ps_checkout::show_tax_details( $db->f('order_tax_details'), $db->f('order_currency') );
			?>
			<?php if (!empty( $tax_details)) { ?>
			<tr>
				<td colspan="6" align="right"><?php echo $tax_details; ?></td>
			</tr>
			<?php }; ?>
		</table>
		<?php //$ps_order_change_html->html_change_add_item();
		?></td>
	</tr>
</table>
&nbsp;
<table class="adminlist" width="100%">
	<tr>
		<td valign="top">
		<table class="adminlist">
			<thead>
				<tr>
					<td style="text-align: center;" colspan="2"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_SHIPPING_LBL') ?></td>
				</tr>
			</thead>
			<tr>
				<td><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_SHIPPING_CARRIER_LBL') ?>:</td>
				<td align="left"><?php echo $this->shippingInfo->carrier; ?></td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_SHIPPING_MODE_LBL') ?>:</td>
				<td><?php echo $this->shippingInfo->name; ?></td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_SHIPPING_PRICE_LBL') ?>:</td>
				<td align="left"><?php echo $this->currency->priceDisplay($this->orderbt->order_shipping,'',false); ?></td>
			</tr>
		</table>
	</td>
	<td valign="top"><?php
	JPluginHelper::importPlugin('vmpayment');
	$_dispatcher = JDispatcher::getInstance();
	$_returnValues = $_dispatcher->trigger('plgVmOnShowStoredOrder',array(
	$this->orderID
	,$this->orderbt->payment_method_id
	));
	foreach ($_returnValues as $_returnValue) {
		if ($_returnValue !== null) {
			echo $_returnValue;
		}
	}
	?></td>
	</tr>
	<tr>
		<!-- Customer Note -->
		<td valign="top" width="30%" colspan="2">
		<table class="adminlist">
			<thead>
				<tr>
					<th><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_CUSTOMER_NOTE') ?></th>
				</tr>
			</thead>
			<tr>
				<td valign="top" align="left" width="50%"><?php echo $this->orderbt->customer_note; ?></td>
			</tr>
		</table>
		</td>
	</tr>
</table>
</body>