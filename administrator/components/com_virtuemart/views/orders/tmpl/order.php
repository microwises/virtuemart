<?php
/**
 * Display form details
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
$document = JFactory::getDocument();
$mainframe = JFactory::getApplication('site');
$document->addScript(JURI::root().'components/com_virtuemart/assets/js/jquery.js');
//$document->addScript(JURI::base().'components/com_virtuemart/assets/js/jquery.alerts.js');

AdminUIHelper::startAdminArea();
AdminUIHelper::imitateTabs('start','COM_VIRTUEMART_ORDER_PRINT_PO_LBL');

// Get the plugins
JPluginHelper::importPlugin('vmpayment');
JPluginHelper::importPlugin('vmorderplugin');
JPluginHelper::importPlugin('vmshipper');
$tt=$this;
?>

<form name='adminForm' >
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="option" value="com_virtuemart" />
		<input type="hidden" name="view" value="orders" />
		<input type="hidden" name="virtuemart_order_id" value="<?php echo $this->orderID; ?>" />
		<input type="hidden" name="virtuemart_order_item_id" value="0" />
		<?php echo JHTML::_( 'form.token' ); ?>
		</form> 
<table class="admin-table" style="table-layout: fixed;">
	<tr>
		<td valign="top">
		<table class="admin-table" cellspacing="0" cellpadding="0">
			<tr>
				<td class="key" style="text-align: center;" colspan="2"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_PO_LBL') ?></td>
			</tr>
			<tr>
				<td class="key"><strong><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_PO_ID') ?></strong></td>
				<td><?php printf("%08d", $this->orderbt->virtuemart_order_id);?></td>
			</tr>
                        <tr>
				<td class="key"><strong><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_PO_NUMBER') ?></strong></td>
				<td><?php echo  $this->orderbt->order_number;?></td>
			</tr>
			<tr>
				<td class="key"><strong><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_PO_DATE') ?></strong></td>
				<td><?php echo $this->orderbt->created_on ; ?></td>
			</tr>
			<tr>
				<td class="key"><strong><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_PO_STATUS') ?></strong></td>
				<td><?php echo $this->orderstatuslist[$this->orderbt->order_status]; ?></td>
			</tr>
			<tr>
				<td class="key"><strong><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_PO_IPADDRESS') ?></strong></td>
				<td><?php echo $this->orderbt->ip_address; ?></td>
			</tr>
			<?php
			if (VmConfig::get('enable_coupons') == '1') { ?>
			<tr>
				<td class="key"><strong><?php echo JText::_('COM_VIRTUEMART_COUPON_CODE') ?></strong></td>
				<td><?php echo $this->orderbt->coupon_code; ?></td>
			</tr>
			<?php } ?>
		</table>
		</td>
		<td valign="top">
		<table class="admin-table" cellspacing="0" cellpadding="0">
			<thead>
				<tr>
					<th><?php echo JText::_('COM_VIRTUEMART_ORDER_HISTORY_DATE_ADDED') ?></th>
					<th><?php echo JText::_('COM_VIRTUEMART_ORDER_HISTORY_CUSTOMER_NOTIFIED') ?></th>
					<th><?php echo JText::_('COM_VIRTUEMART_ORDER_LIST_STATUS') ?></th>
					<th><?php echo JText::_('COM_VIRTUEMART_COMMENT') ?></th>
				</tr>
			</thead>
			<?php
			foreach ($this->order['history'] as $this->orderbt_event ) {
				echo "<tr>";
				echo "<td>".$this->orderbt_event->date_added."</td>\n";
				if ($this->orderbt_event->customer_notified == 1) {
					echo '<td align="center">Yes</td>';
				}
				else {
					echo '<td align="center">No</td>';
				}
				echo '<td align="center">'.$this->orderstatuslist[$this->orderbt_event->order_status_code].'</td>';
				echo "<td>".$this->orderbt_event->comments."</td>\n";
				echo "</tr>\n";
			}
			?>
			<tr>
				<td colspan="4"><?php echo JHTML::_('image',  'administrator/components/com_virtuemart/assets/images/icon_16/icon-16-editadd.png', "Update Status"); ?>
				<a href="#" class="show_element[updateOrderStatus]"> Update Status </a>
				<div style="display: none; background: white;"
					class="element-hidden vm-absolute"
					id="updateOrderStatus"><?php echo $this->loadTemplate('editstatus'); ?>
				</div>
				</td>
			</tr>

			<?php
				// Load additional plugins
				$_dispatcher = JDispatcher::getInstance();
				$_returnValues1 = $_dispatcher->trigger('plgVmOnUpdateOrderBE',array(
					 $this->orderID
				));
				$_returnValues2 = $_dispatcher->trigger('plgVmOnUpdateOrderShipperBE',array(
					 $this->orderID
				));
				$_returnValues = array_merge($_returnValues1, $_returnValues2);
				$_plg = '';
				foreach ($_returnValues as $_returnValue) {
					if ($_returnValue !== null) {
						$_plg .= ('	<td colspan="4">' . $_returnValue . "</td>\n");
					}
				}
				if ($_plg !== '') {
					echo "<tr>\n$_plg</tr>\n";
				}
			?>

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
					<td class="key" style="text-align: center;" colspan="2"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_BILL_TO_LBL') ?></td>
				</tr>
			</thead>

			<?php
			foreach ($this->userfields['fields'] as $_field ) {
				echo '		<tr>'."\n";
				echo '			<td class="key">'."\n";
				echo '				'.$_field['title']."\n";
				echo '			</td>'."\n";
				echo '			<td>'."\n";
				echo '				'.$_field['value']."\n";
				echo '			</td>'."\n";
				echo '		</tr>'."\n";
			}
			?>

		</table>
		</td>
		<td width="50%" valign="top">
		<table class="admintable" width="100%">
			<thead>
				<tr>
					<td class="key" style="text-align: center;" colspan="2"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_SHIP_TO_LBL') ?></td>
				</tr>
			</thead>

			<?php
			foreach ($this->shippingfields['fields'] as $_field ) {
				echo '		<tr>'."\n";
				echo '			<td class="key">'."\n";
				echo '				'.$_field['title']."\n";
				echo '			</td>'."\n";
				echo '			<td>'."\n";
				echo '				'.$_field['value']."\n";
				echo '			</td>'."\n";
				echo '		</tr>'."\n";
			}
			?>

		</table>
		</td>
	</tr>
</table>

<table width="100%">
	<tr>
		<td colspan="2">
		<form action="index.php" method="post" name="orderItemForm" id="orderItemForm"><!-- Update linestatus form -->
		<table class="admin-table" cellspacing="0" cellpadding="0">
			<thead>
				<tr>
					<th class="title" width="5%" align="left"><?php echo JText::_('COM_VIRTUEMART_ORDER_EDIT_ACTIONS') ?></th>
					<th class="title" width="3" align="left">&nbsp;</th>
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
			<!-- Display the order item -->
			<tr valign="top" id="showItem_<?php echo $item->virtuemart_order_item_id; ?>">
				<td>
					<?php $removeLineLink=JRoute::_('index.php?option=com_virtuemart&view=orders&orderId='.$this->orderbt->virtuemart_order_id.'&orderLineId='.$item->virtuemart_order_item_id.'&task=removeOrderItem'); ?>
					<span onclick="javascript:confirmation('<?php echo $removeLineLink; ?>');">
						<?php
							echo JHTML::_('image',  'administrator/components/com_virtuemart/assets/images/icon_16/icon-16-bug.png', "Remove", NULL, "Remove");
						?>
					</span>
					<a href="javascript:enableItemEdit(<?php echo $item->virtuemart_order_item_id; ?>)"> <?php echo JHTML::_('image',  'administrator/components/com_virtuemart/assets/images/icon_16/icon-16-category.png', "Edit", NULL, "Edit"); ?></a>
				</td>
				<td>
					<input type="checkbox" name="cid[]" value="<?php echo $item->virtuemart_order_item_id; ?>" />
				</td>
				<td>
					<?php echo $item->product_quantity; ?>
				</td>
				<td>
					<?php
						echo $item->order_item_name;
						if (!empty($item->product_attribute)) {
							echo '<table border="0" celspacing="0" celpadding="0">'
								. '<tr>'
								. '<td width="8px"></td>' // Indent
								. '<td>'.$item->product_attribute.'</td>'
								. '</tr>'
								. '</table>';
						}
						$_dispatcher = JDispatcher::getInstance();
						$_returnValues = $_dispatcher->trigger('plgVmOnShowOrderLineShipperBE',array(
							 $this->orderID
							,$item->virtuemart_order_item_id
						));
						$_plg = '';
						foreach ($_returnValues as $_returnValue) {
							if ($_returnValue !== null) {
								$_plg .= $_returnValue;
							}
						}
						if ($_plg !== '') {
							echo '<table border="0" celspacing="0" celpadding="0">'
								. '<tr>'
								. '<td width="8px"></td>' // Indent
								. '<td>'.$_plg.'</td>'
								. '</tr>'
								. '</table>';
						}
					?>
				</td>
				<td>
					<?php echo $item->order_item_sku; ?>
				</td>
				<td align="center">
					<?php echo $this->orderstatuslist[$item->order_status]; ?>
				</td>
				<td>
					<?php echo $this->currency->priceDisplay($item->product_item_price,'',false); ?>
				</td>
				<td>
					<?php echo $this->currency->priceDisplay($item->product_final_price,'',false); ?>
				</td>
				<td>
					<?php echo $this->currency->priceDisplay($item->product_quantity * $item->product_final_price,'',false); ?>
				</td>
			</tr>

			<!-- Same order item, but now in an editable format -->
			<tr valign="top" style="display: none; width: 100%" id="editItem_<?php echo $item->virtuemart_order_item_id; ?>">
				<td>
					<a href="#" onClick="javascript:resetForm(<?php echo $item->virtuemart_order_item_id; ?>);"><?php
						echo JHTML::_('image', 'administrator/components/com_virtuemart/assets/images/icon_16/icon-16-remove.png', JText::_('COM_VIRTUEMART_CANCEL'));
					?></a>
					<a href="#" onClick="javascript:submitForm('updateOrderItem');">
						<?php
							echo JHTML::_('image', 'administrator/components/com_virtuemart/assets/images/icon_16/icon-16-save.png', JText::_('COM_VIRTUEMART_SAVE'));
					?></a>
				</td>
				<td>
					<input type="checkbox" name="cid[]" value="<?php echo $item->virtuemart_order_item_id; ?>" />
				</td>
				<td>
					<input type="text" size="3" name="product_quantity_<?php echo $item->virtuemart_order_item_id; ?>" value="<?php echo $item->product_quantity; ?>"/>
				</td>
				<td>
					<?php
						echo $item->order_item_name;
						if (!empty($item->product_attribute)) {
							echo '<table border="0" celspacing="0" celpadding="0">';
							foreach ($this->itemattributesupdatefields[$item->virtuemart_order_item_id] as $_attrib) {
								echo '<tr>'
									. '<td>'.$_attrib['lbl'].'</td>'
									. '<td>'.$_attrib['fld'].'</td>'
									. '</tr>';
							}
							echo '</table>';
						}
						$_returnValues = $_dispatcher->trigger('plgVmOnEditOrderLineShipperBE',array(
							 $this->orderID
							,$item->virtuemart_order_item_id
						));
						$_plg = '';
						foreach ($_returnValues as $_returnValue) {
							if ($_returnValue !== null) {
								$_plg .= $_returnValue;
							}
						}
						if ($_plg !== '') {
							echo '<table border="0" celspacing="0" celpadding="0">'
								. '<tr>'
								. '<td width="8px"></td>' // Indent
								. '<td>'.$_plg.'</td>'
								. '</tr>'
								. '</table>';
						}
					?>
				</td>
				<td>
					<?php echo $item->order_item_sku; ?>
				</td>
				<td align="center">
					<?php echo $this->itemstatusupdatefields[$item->virtuemart_order_item_id]; ?>
				</td>
				<td>
					<input type="text" size="8" name="product_item_price_<?php echo $item->virtuemart_order_item_id; ?>" value="<?php echo $item->product_item_price; ?>"/>
				</td>
				<td>
					<input type="text" size="8" name="product_final_price_<?php echo $item->virtuemart_order_item_id; ?>" value="<?php echo $item->product_final_price; ?>"/>
				</td>
				<td>
					<?php echo $this->currency->priceDisplay($item->product_quantity * $item->product_final_price,'',false); ?>
				</td>
			</tr>


		<?php } ?>
			<tr id="updateOrderItemStatus">
					<td>&nbsp;</td>
					<td align="center">
						&nbsp;<?php echo JHTML::_('image',  'administrator/components/com_virtuemart/assets/images/vm_witharrow.png', 'With selected'); ?>
					</td>
					<td colspan="7">
						<?php echo $this->orderStatSelect; ?>
						&nbsp;&nbsp;&nbsp;
						<a href="#" onClick="javascript:submitForm('updateOrderItemStatus');">
						<?php
							echo JHTML::_('image', 'administrator/components/com_virtuemart/assets/images/icon_16/icon-16-save.png', JText::_('COM_VIRTUEMART_SAVE'))
								. '&nbsp;'
								. JText::_('COM_VIRTUEMART_SAVE');
						?></a>&nbsp;&nbsp;&nbsp;
						<a href="#" onClick="javascript:resetForm(0);"><?php
							echo JHTML::_('image', 'administrator/components/com_virtuemart/assets/images/icon_16/icon-16-remove.png', JText::_('COM_VIRTUEMART_CANCEL'))
							. '&nbsp;'
							. JText::_('COM_VIRTUEMART_CANCEL');
						?></a>
					</td>
			</tr>
		</table>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="option" value="com_virtuemart" />
		<input type="hidden" name="view" value="orders" />
		<input type="hidden" name="virtuemart_order_id" value="<?php echo $this->orderID; ?>" />
		<input type="hidden" name="virtuemart_order_item_id" value="0" />
		<?php echo JHTML::_( 'form.token' ); ?>
		</form> <!-- Update linestatus form -->
		<table class="admin-table" cellspacing="0" cellpadding="0">
			<tr>
				<td align="left" colspan="6"><?php $editLineLink=JRoute::_('index.php?option=com_virtuemart&view=orders&orderId='.$this->orderbt->virtuemart_order_id.'&orderLineId=0&tmpl=component&task=editOrderItem'); ?>
				<!-- <a href="<?php echo $editLineLink; ?>" class="modal"> <?php echo JHTML::_('image',  'administrator/components/com_virtuemart/assets/images/icon_16/icon-16-editadd.png', "New Item"); ?>
				New Item </a>--></td>
				<td align="right">
				<div align="right"><strong> <?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_SUBTOTAL') ?>:
				</strong></div>
				</td>
				<td width="20%" align="right" style="padding-right: 5px;"><?php echo $this->currency->priceDisplay($this->orderbt->order_subtotal,'',false); ?></td>
			</tr>
			<?php
			/* COUPON DISCOUNT */
			if (VmConfig::get('payment_discount_before') == '1') {
				if ($this->orderbt->order_discount != 0) {
					?>
			<tr>
				<td align="right" colspan="7"><strong> <?php
				if ($this->orderbt->order_discount > 0) echo JText::_('COM_VIRTUEMART_PAYMENTMETHOD_LIST_DISCOUNT');
				else echo JText::_('COM_VIRTUEMART_FEE');
				?>:</strong></td>
				<td   align="right" style="padding-right: 5px;"><?php
				if ($this->orderbt->order_discount > 0 ) echo "-" . $this->currency->priceDisplay($this->orderbt->order_discount,'',false);
				elseif ($this->orderbt->order_discount < 0 )  echo "+" . $this->currency->priceDisplay($ordert->order_discount,'',false); ?>
				</td>
			</tr>
			<?php
				}
				if ($this->orderbt->coupon_discount > 0 || $this->orderbt->coupon_discount < 0) {
					?>
			<tr>
				<td align="right" colspan="7"><strong><?php echo JText::_('COM_VIRTUEMART_COUPON_DISCOUNT') ?></strong></td>
				<td   align="right" style="padding-right: 5px;"><?php
				echo "- ".$this->orderbt->coupon_discount; ?></td>
			</tr>
			<?php
				}
			}?>
			<tr>
				<td align="right" colspan="7"><strong><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_TOTAL_TAX') ?>:</strong></td>
				<td   align="right" style="padding-right: 5px;"><?php echo $this->currency->priceDisplay($this->orderbt->order_tax,'',false); ?></td>
			</tr>
			<tr>
				<td align="right" colspan="7"><strong><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_SHIPPING') ?>:</strong></td>
				<td  align="right" style="padding-right: 5px;"><?php echo $this->currency->priceDisplay($this->orderbt->order_shipping,'',false); ?></td>
			</tr>
			<tr>
				<td align="right" colspan="7"><strong><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_SHIPPING_TAX') ?>:</strong></td>
				<td   align="right" style="padding-right: 5px;"><?php echo $this->currency->priceDisplay($this->orderbt->order_shipping_tax,'',false); ?></td>
			</tr>
			<?php
			if (VmConfig::get('payment_discount_before') != '1') {
				if ($this->orderbt->order_discount != 0) {
					?>
			<tr>
				<td align="right" colspan="7"><strong><?php
				if( $this->orderbt->order_discount > 0) echo JText::_('COM_VIRTUEMART_PAYMENTMETHOD_LIST_DISCOUNT');
				else echo JText::_('COM_VIRTUEMART_DISCOUNT');
				?>:</strong></td>
				<td   align="right" style="padding-right: 5px;"><?php
				if ($this->orderbt->order_discount > 0 )
				echo "-" . $this->currency->priceDisplay($this->orderbt->order_discount,'',false);
				elseif ($this->orderbt->order_discount < 0 ) echo $this->currency->priceDisplay($this->orderbt->order_discount,'',false); ?>
				</td>
			</tr>
			<?php
				}
				if( $this->orderbt->coupon_discount > 0 || $this->orderbt->coupon_discount < 0) {
					?>
			<tr>
				<td align="right" colspan="7"><strong><?php echo JText::_('COM_VIRTUEMART_COUPON_DISCOUNT') ?>:</strong></td>
				<td   align="right" style="padding-right: 5px;"><?php echo "- ".$this->currency->priceDisplay($this->orderbt->coupon_discount,'',false); ?></td>
			</tr>
			<?php
				}
			}
			?>
			<tr>
				<td align="right" colspan="7"><strong><?php echo JText::_('COM_VIRTUEMART_CART_TOTAL') ?>:</strong></td>
				<td   align="right" style="padding-right: 5px;"><strong><?php echo $this->currency->priceDisplay($this->orderbt->order_total,'',false); ?></strong>
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
		</td>
	</tr>
</table>
&nbsp;
<table width="100%">
	<tr>
		<td valign="top"><?php
		$_dispatcher = JDispatcher::getInstance();
		$returnValues = $_dispatcher->trigger('plgVmOnShowOrderShipperBE',array(
			 $this->orderID
			,$this->orderbt->virtuemart_vendor_id
			,$this->ship_method_id
		));
		foreach ($returnValues as $returnValue) {
			if ($returnValue !== null) {
				echo $returnValue;
			}
		}
		?>
		</td>
		<td valign="top"><?php
		$_dispatcher = JDispatcher::getInstance();
		$_returnValues = $_dispatcher->trigger('plgVmOnShowOrderPaymentBE',array(
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
		<table class="admin-table" cellspacing="0" cellpadding="0">
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



<?php 
AdminUIHelper::imitateTabs('end');
AdminUIHelper::endAdminArea(); ?>

<script type="text/javascript">
<!--
function confirmation(destnUrl) {
	var answer = confirm("<?php echo JText::_('COM_VIRTUEMART_ORDER_DELETE_ITEM_MSG'); ?>");
	if (answer) {
		window.location = destnUrl;
	}
}
var editingItem = 0;

function submitForm(formTask) {
	document.orderItemForm.task.value = formTask;
	document.orderItemForm.submit();
}

function resetForm(id) {
	document.orderItemForm.reset();
	if (id > 0) { // Resetting the Edit Item form
		document.getElementById('updateOrderItemStatus').style['display'] = '';
		document.getElementById('showItem_'+id).style['display'] = '';
		document.getElementById('editItem_'+id).style['display'] = 'none';
		checkCkeckBoxes(id, false);
		editingItem = 0;
		document.orderItemForm.virtuemart_order_item_id.value = 0;
	}
}

function enableItemEdit(id) {
	if (editingItem > 0) {
		return; // Editing another item already
	}
	document.getElementById('updateOrderItemStatus').style['display'] = 'none';
	document.getElementById('showItem_'+id).style['display'] = 'none';
	document.getElementById('editItem_'+id).style['display'] = '';
	checkCkeckBoxes(id, true);
	editingItem = id;
	document.orderItemForm.virtuemart_order_item_id.value = id;
}

function checkCkeckBoxes(id, chk) {
	var inputElements = document.orderItemForm.elements;
	for (var Idx = 0; Idx < inputElements.length; Idx++) {
		if (inputElements[Idx].type == 'checkbox') {
			if (chk) {
				if (inputElements[Idx].value == id) {
					inputElements[Idx].checked = true;
				} else {
					inputElements[Idx].checked = false;
				}
				inputElements[Idx].disabled = true;
			} else {
				inputElements[Idx].checked = false;
				inputElements[Idx].disabled = false;
			}
		}
	}
}
//-->
</script>
