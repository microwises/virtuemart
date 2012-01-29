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
		<th align="left" width="5%"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_SKU') ?></th>
		<th align="left" width="5%"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_QTY') ?></th>
		<th align="left" colspan="2" width="49%" ><?php echo JText::_('COM_VIRTUEMART_PRODUCT_NAME_TITLE') ?></th>
		<th align="center" width="10%"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_PRODUCT_STATUS') ?></th>
		<th align="right" width="10%" ><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_PRICE') ?></th>
		<?php if ( VmConfig::get('show_tax')) { ?>
		<th align="right" width="10%" ><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_PRODUCT_TAX') ?></th>
		  <?php } ?>
		<th align="right" width="10%"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_TOTAL') ?></th>

	</tr>
<?php
	foreach($this->orderdetails['items'] as $item) {
		$_link = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $item->virtuemart_product_id);
?>
		<tr valign="top">
			<td align="left" width="5%">
				<?php echo $item->order_item_sku; ?>
			</td>
			<td align="left" width="5%">
				<?php echo $item->product_quantity; ?>
			</td>
			<td align="left" width="49%" colspan="2" >
				<a href="<?php echo $_link; ?>"><?php echo $item->order_item_name; ?></a>
				<?php
					if (!empty($item->product_attribute)) {
							if(!class_exists('VirtueMartModelCustomfields'))require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'customfields.php');
							$product_attribute = VirtueMartModelCustomfields::CustomsFieldOrderDisplay($item);
						echo $product_attribute;
					}
				?>
			</td>
			<td align="center" width="10%">
				<?php echo $this->orderstatuses[$item->order_status]; ?>
			</td>
			<td align="right" width="10%">
				<?php echo $this->currency->priceDisplay($item->product_final_price); ?>
			</td>
			<?php if ( VmConfig::get('show_tax')) { ?>
				<td align="right" width="10%"><?php echo "<span  class='priceColor2'>".$this->currency->priceDisplay($item->product_tax)."</span>" ?></td>
                                <?php } ?>
			<td align="right" width="10%">
				<?php echo $this->currency->priceDisplay($item->product_quantity * $item->product_final_price); ?>
			</td>

		</tr>

<?php
	}
?>
		<?php if (false) { ?>
		<tr>
		<td align="right" class="pricePad" colspan="6"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_SUBTOTAL') ?></td>
		<td align="right"><?php echo $this->currency->priceDisplay($this->orderdetails['details']['BT']->order_subtotal); ?></td>
	</tr>
<?php } ?>
	<tr>
		<td align="right" class="pricePad" colspan="5"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_SHIPPING') ?></td>
		<td align="right">&nbsp;</td>

			<?php if ( VmConfig::get('show_tax')) { ?>
				<td align="right"><?php echo "<span  class='priceColor2'>".$this->currency->priceDisplay($this->orderdetails['details']['BT']->order_shipment_tax)."</span>" ?></td>
                                <?php } ?>
				<td align="right"><?php echo $this->currency->priceDisplay($this->orderdetails['details']['BT']->order_shipment+ $this->orderdetails['details']['BT']->order_shipment_tax); ?></td>

	</tr>

<tr>
		<td align="right" class="pricePad" colspan="5"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_PAYMENT') ?></td>
		<td align="right">&nbsp;</td>

			<?php if ( VmConfig::get('show_tax')) { ?>
				<td align="right"><?php echo "<span  class='priceColor2'>".$this->currency->priceDisplay($this->orderdetails['details']['BT']->order_payment_tax)."</span>" ?></td>
                                <?php } ?>
				<td align="right"><?php echo $this->currency->priceDisplay($this->orderdetails['details']['BT']->order_payment+ $this->orderdetails['details']['BT']->order_payment_tax); ?></td>


	</tr>

	<tr>
		<td align="right" class="pricePad" colspan="5"><?php echo JText::_('COM_VIRTUEMART_CART_SUBTOTAL_DISCOUNT_AMOUNT') ?></td>
		<td align="right">&nbsp;</td>

			<?php if ( VmConfig::get('show_tax')) { ?>
				<td align="right">&nbsp;</td>
                                <?php } ?>
		<td align="right"><?php echo $this->currency->priceDisplay($this->orderdetails['details']['BT']->order_discount); ?></td>
	</tr>
<?php if (VmConfig::get('coupons_enable',0)=='1') {
    $coupon_code=$this->orderdetails['details']['BT']->coupon_code?' ('.$this->orderdetails['details']['BT']->coupon_code.')':'';
	?>
	<tr>
		<td align="right" class="pricePad" colspan="5"><?php echo JText::_('COM_VIRTUEMART_COUPON_DISCOUNT').$coupon_code ?></td>
			<td align="right">&nbsp;</td>

			<?php if ( VmConfig::get('show_tax')) { ?>
				<td align="right">&nbsp;</td>
                                <?php } ?>
		<td align="right"><?php echo '- '.$this->currency->priceDisplay($this->orderdetails['details']['BT']->coupon_discount); ?></td>
	</tr>
<?php  } ?>

	<tr>
		<td align="right" class="pricePad" colspan="5"><strong><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_TOTAL') ?></strong></td>
		<td align="right">&nbsp;</td>
		 <?php if ( VmConfig::get('show_tax')) {  ?>
		<td align="right"><span  class='priceColor2'><?php echo $this->currency->priceDisplay($this->orderdetails['details']['BT']->order_tax); ?></span></td>
		 <?php } ?>
		<td align="right"><strong><?php echo $this->currency->priceDisplay($this->orderdetails['details']['BT']->order_total); ?></strong></td>
	</tr>





</table>
