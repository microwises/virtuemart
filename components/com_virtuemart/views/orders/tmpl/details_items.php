<?php
/**
*
* Order items view
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
<table width="100%" cellspacing="0" cellpadding="0" border="0">
	<tr align="left" class="sectiontableheader">
		<th align="left" ><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_SKU') ?></th>
		<th align="left" ><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_QTY') ?></th>
		<th align="left" colspan="2"><?php echo JText::_('COM_VIRTUEMART_PRODUCT_NAME_TITLE') ?></th>
		<th align="center" ><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_PRODUCT_STATUS') ?></th>
		<th align="right" ><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_PRICE') ?></th>
		<?php if ( VmConfig::get('show_tax')) { ?>
		<th align="right" ><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_PRODUCT_TAX') ?></th>
		  <?php } ?>
		<th align="right" ><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_TOTAL') ?></th>

	</tr>
<?php
	foreach($this->orderdetails['items'] as $item) {
		$_link = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $item->virtuemart_product_id);
?>
		<tr valign="top">
			<td align="left" >
				<?php echo $item->order_item_sku; ?>
			</td>
			<td align="left" >
				<?php echo $item->product_quantity; ?>
			</td>
			<td align="left" >
				<a href="<?php echo $_link; ?>"><?php echo $item->order_item_name; ?></a>
			</td>

			<td align="left" >
				<?php
					if (!empty($item->product_attribute)) {
							if(!class_exists('VirtueMartModelCustomfields'))require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'customfields.php');
							$product_attribute = VirtueMartModelCustomfields::CustomsFieldOrderDisplay($item);
						echo '<div>'.$product_attribute.'</div>';
					}
				?>
			</td>
			<td align="center" >
				<?php echo $this->orderstatuses[$item->order_status]; ?>
			</td>
			<td align="right" >
				<?php echo $this->currency->priceDisplay($item->product_final_price); ?>
			</td>
			<?php if ( VmConfig::get('show_tax')) { ?>
				<td align="right"><?php echo "<span  style='color:gray'>".$this->currency->priceDisplay($item->product_tax)."</span>" ?></td>
                                <?php } ?>
			<td align="right" >
				<?php echo $this->currency->priceDisplay($item->product_quantity * $item->product_final_price); ?>
			</td>

		</tr>

<?php
	}
?>
		<?php if (false) { ?>
		<tr>
		<td align="right" style="padding-right: 10px;" colspan="3"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_SUBTOTAL') ?></td>
		<td align="right"><?php echo $this->currency->priceDisplay($this->orderdetails['details']['BT']->order_subtotal); ?></td>
	</tr>
<?php } ?>
	<tr>
		<td align="right" style="padding-right: 10px;" colspan="5"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_SHIPPING') ?></td>
		<td align="right">&nbsp;</td>

			<?php if ( VmConfig::get('show_tax')) { ?>
				<td align="right"><?php echo "<span  style='color:gray'>".$this->currency->priceDisplay($this->orderdetails['details']['BT']->order_shipment_tax)."</span>" ?></td>
                                <?php } ?>
				<td align="right"><?php echo $this->currency->priceDisplay($this->orderdetails['details']['BT']->order_shipment+ $this->orderdetails['details']['BT']->order_shipment_tax); ?></td>

	</tr>

<tr>
		<td align="right" style="padding-right: 10px;" colspan="5"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_PAYMENT') ?></td>
		<td align="right">&nbsp;</td>

			<?php if ( VmConfig::get('show_tax')) { ?>
				<td align="right"><?php echo "<span  style='color:gray'>".$this->currency->priceDisplay($this->orderdetails['details']['BT']->order_payment_tax)."</span>" ?></td>
                                <?php } ?>
				<td align="right"><?php echo $this->currency->priceDisplay($this->orderdetails['details']['BT']->order_payment+ $this->orderdetails['details']['BT']->order_payment_tax); ?></td>


	</tr>

	<tr>
		<td align="right" style="padding-right: 10px;" colspan="5"><?php echo JText::_('COM_VIRTUEMART_CART_SUBTOTAL_DISCOUNT_AMOUNT') ?></td>
		<td align="right">&nbsp;</td>

			<?php if ( VmConfig::get('show_tax')) { ?>
				<td align="right">&nbsp;</td>
                                <?php } ?>
		<td align="right"><?php echo $this->currency->priceDisplay($this->orderdetails['details']['BT']->order_discount); ?></td>
	</tr>
<?php if (VmConfig::get('coupons_enable',0)=='1') { ?>
	<tr>
		<td align="right" style="padding-right: 10px;" colspan="5"><?php echo JText::_('COM_VIRTUEMART_COUPON_DISCOUNT').' '.$this->orderdetails['details']['BT']->coupon_code ?></td>
			<td align="right">&nbsp;</td>

			<?php if ( VmConfig::get('show_tax')) { ?>
				<td align="right">&nbsp;</td>
                                <?php } ?>
		<td align="right"><?php echo $this->currency->priceDisplay($this->orderdetails['details']['BT']->coupon_discount); ?></td>
	</tr>
<?php  } ?>

	<tr>
		<td align="right" style="padding-right: 10px;" colspan="5"><strong><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_TOTAL') ?></strong></td>
		<td align="right">&nbsp;</td>
		 <?php if ( VmConfig::get('show_tax')) {  ?>
		<td align="right"><span  style='color:gray'><?php echo $this->currency->priceDisplay($this->orderdetails['details']['BT']->order_tax); ?></span></td>
		 <?php } ?>
		<td align="right"><strong><?php echo $this->currency->priceDisplay($this->orderdetails['details']['BT']->order_total); ?></strong></td>
	</tr>





</table>
