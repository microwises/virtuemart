<?php
/**
 *
 * Layout for the shopping cart
 *
 * @package	VirtueMart
 * @subpackage Cart
 * @author Max Milbers
 *
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 *
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
// jimport( 'joomla.application.component.view');
// $viewEscape = new JView();
// $viewEscape->setEscape('htmlspecialchars');

$u = & JURI::getInstance();
$root = $u->toString(array('scheme', 'host'));
?>





<table class="cart-summary" cellspacing="0" cellpadding="0" border="0" width="100%">
    <tr align="left" class="sectiontableheader">
	<th align="left" ><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_SKU') ?></th>
	<th align="left" ><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_QTY') ?></th>
	<th align="left" colspan="2"><?php echo JText::_('COM_VIRTUEMART_PRODUCT_NAME_TITLE') ?></th>
	<th align="right" ><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_PRICE') ?></th>
	<?php if (VmConfig::get('show_tax')) { ?>
    	<th align="right" ><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_PRODUCT_TAX') ?></th>
	<?php } ?>
	<th align="right" ><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_TOTAL') ?></th>

    </tr>
    <?php
    $i=1;
    foreach ($this->order['items'] as $item) {
	$_link = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $item['virtuemart_product_id']);
	?>
        <tr valign="top" class="sectiontableentry<?php echo $i ?>">
    	<td align="left" >
		<?php echo $item['order_item_sku']; ?>
    	</td>
    	<td align="left" >
		<?php echo $item['product_quantity']; ?>
    	</td>
    	<td align="left" >
    	    <a href="<?php echo $_link; ?>"><?php echo $item['order_item_name']; ?></a>
    	</td>

    	<td align="left" >
		<?php
		if (!empty($item['product_attribute'])) {
		    if (!class_exists('VirtueMartModelCustomfields'))
			require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'customfields.php');
		    $product_attribute = VirtueMartModelCustomfields::CustomsFieldOrderDisplay($item);
		    echo '<div>' . $product_attribute . '</div>';
		}
		?>
    	</td>

    	<td align="right" >
		<?php echo $this->currency->priceDisplay($item['product_final_price']); ?>
    	</td>
	    <?php if (VmConfig::get('show_tax')) { ?>
		<td align="right"><?php echo "<span  style='color:gray'>" . $this->currency->priceDisplay($item['product_tax']) . "</span>" ?></td>
	    <?php } ?>
    	<td align="right" >
		<?php echo $this->currency->priceDisplay($item['product_quantity'] * $item['product_final_price']); ?>
    	</td>

        </tr>

	<?php
	$i = 1 ? 2 : 1;
    }
    ?>
    <?php if (false) { ?>
        <tr  class="sectiontableentry<?php echo $i ?>">
    	<td align="right" style="padding-right: 10px;" colspan="3"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_SUBTOTAL') ?></td>
    	<td align="right"><?php echo $this->currency->priceDisplay($this->order['details']['BT']['order_subtotal']); ?></td>
        </tr>
    <?php
    $i = 1 ? 2 : 1;
}
?>
    <tr  class="sectiontableentry<?php echo $i ?>">
	<td align="right" style="padding-right: 10px;" colspan="5"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_SHIPPING') ?></td>

<?php if (VmConfig::get('show_tax')) { ?>
    	<td align="right"><?php echo "<span  style='color:gray'>" . $this->currency->priceDisplay($this->order['details']['BT']['order_shipment_tax']) . "</span>" ?></td>
	<?php } ?>
	<td align="right"><?php echo $this->currency->priceDisplay($this->order['details']['BT']['order_shipment'] + $this->order['details']['BT']['order_shipment_tax']); ?></td>

    </tr>
<?php
$i = 1 ? 2 : 1;
?>
    <tr   class="sectiontableentry<?php echo $i ?>">
	<td align="right" style="padding-right: 10px;" colspan="5"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_PAYMENT') ?></td>


<?php if (VmConfig::get('show_tax')) { ?>
    	<td align="right"><?php echo "<span  style='color:gray'>" . $this->currency->priceDisplay($this->order['details']['BT']['order_payment_tax']) . "</span>" ?></td>
	<?php } ?>
	<td align="right"><?php echo $this->currency->priceDisplay($this->order['details']['BT']['order_payment'] + $this->order['details']['BT']['order_payment_tax']); ?></td>


    </tr>
<?php
$i = 1 ? 2 : 1;
if (fal)
?>
    <tr   class="sectiontableentry<?php echo $i ?>">
	<td align="right" style="padding-right: 10px;" colspan="5"><?php echo JText::_('COM_VIRTUEMART_CART_SUBTOTAL_DISCOUNT_AMOUNT') ?></td>


<?php if (VmConfig::get('show_tax')) { ?>
    	<td align="right">&nbsp;</td>
	<?php } ?>
	<td align="right"><?php echo $this->currency->priceDisplay($this->order['details']['BT']['order_discount']); ?></td>
    </tr>
<?php
$i = 1 ? 2 : 1;
?>
    <?php if (VmConfig::get('coupons_enable', 0) == '1') {
	    $coupon_code=$this->order['details']['BT']['coupon_code']?' ('.$this->order['details']['BT']['coupon_code'].')':'';
?>
        <tr   class="sectiontableentry<?php echo $i ?>">
    	<td align="right" style="padding-right: 10px;" colspan="5"><?php echo JText::_('COM_VIRTUEMART_COUPON_DISCOUNT').$coupon_code?></td>


    <?php if (VmConfig::get('show_tax')) { ?>
		<td align="right">&nbsp;</td>
	    <?php } ?>
    	<td align="right"><?php echo $this->currency->priceDisplay($this->order['details']['BT']['coupon_discount']); ?></td>
        </tr>
    <?php
    $i = 1 ? 2 : 1;
    ?>
	<?php } ?>

    <tr   class="sectiontableentry<?php echo $i ?>">
	<td align="right" style="padding-right: 10px;" colspan="5"><strong><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_TOTAL') ?></strong></td>

<?php if (VmConfig::get('show_tax')) { ?>
    	<td align="right"><span  style='color:gray'><?php echo $this->currency->priceDisplay($this->order['details']['BT']['order_tax']); ?></span></td>
	<?php } ?>
	<td align="right"><strong><?php echo $this->currency->priceDisplay($this->order['details']['BT']['order_total']); ?></strong></td>
    </tr>

</table>

