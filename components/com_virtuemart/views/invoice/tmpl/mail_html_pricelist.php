<?php
/**
 *
 * Layout for the shopping cart
 *
 * @package	VirtueMart
 * @subpackage Cart
 * @author Max Milbers, Valerie Isaksen
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
	<th align="right" ><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_SUBTOTAL_DISCOUNT_AMOUNT') ?></th>
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
		    $product_attribute = VirtueMartModelCustomfields::CustomsFieldOrderDisplay($item,'FE');
		    echo '<div>' . $product_attribute . '</div>';
		}
		?>
    	</td>

    	<td align="right" >
	    <?php
	    if ( !empty($item['product_basePriceWithTax'] ) && $item['product_basePriceWithTax'] != $item['product_final_price'] ) {
			echo '<span class="line-through">'.$item['product_basePriceWithTax'] .'</span><br />' ;
		}
		?>
		<?php echo '<span class="line-through">'.$this->currency->priceDisplay($item['product_basePriceWithTax'] ).'</span><br />' ?>
		<?php echo $this->currency->priceDisplay($item['product_final_price']); ?>
    	</td>
	    <?php if (VmConfig::get('show_tax')) { ?>
		<td align="right"><?php echo "<span class='priceColor2'>" . $this->currency->priceDisplay( $item['product_tax']  ) . "</span>" ?></td>
	    <?php } ?>
    	<td align="right" >
		<?php echo $this->currency->priceDisplay(  $item['product_subtotal_discount']); ?>
    	</td>
	<td align="right" >
		<?php echo $this->currency->priceDisplay($item['product_subtotal_with_tax']  ); ?>
    	</td>

        </tr>

	<?php
	$i = 1 ? 2 : 1;
    }
    ?>
<tr class="sectiontableentry1">
			<td colspan="5" align="right"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_PRODUCT_PRICES_TOTAL'); ?></td>

                        <?php if ( VmConfig::get('show_tax')) { ?>
			<td align="right"><?php echo "<span  class='priceColor2'>".$this->currency->priceDisplay($this->order['details']['BT']['order_tax'])."</span>" ?></td>
                        <?php } ?>
			<td align="right"><?php echo "<span  class='priceColor2'>".$this->currency->priceDisplay($this->order['details']['BT']['order_discountAmount'] )."</span>" ?></td>
			<td align="right"><?php echo $this->currency->priceDisplay($this->order['details']['BT']['order_salesPrice']) ?></td>
		  </tr>
<?php if ($this->order['details']['BT']['coupon_code']) {
	    $coupon_code=$this->order['details']['BT']['coupon_code']?' ('.$this->order['details']['BT']['coupon_code'].')':'';
?>
        <tr   class="sectiontableentry<?php echo $i ?>">
    	<td align="right" style="padding-right: 10px;" colspan="5"><?php echo JText::_('COM_VIRTUEMART_COUPON_DISCOUNT').$coupon_code?></td>


    <?php if (VmConfig::get('show_tax')) { ?>
		<td align="right">&nbsp;</td>
	    <?php } ?>
    	<td align="right"><?php echo $this->currency->priceDisplay($this->order['details']['BT']['coupon_discount']); ?></td>
	<td align="right">&nbsp;</td>
        </tr>
    <?php
    $i = 1 ? 2 : 1;
    ?>

	<?php } ?>


	<?php
		foreach($this->order['calc_rules'] as $rule){
			if ($rule['calc_kind'] == 'DBTaxRulesBill') { ?>
			<tr class="sectiontableentry<?php $i ?>">
				<td colspan="5" align="right"><?php echo $rule['calc_rule_name'] ?> </td>

                                   <?php if ( VmConfig::get('show_tax')) { ?>
				<td align="right"> </td>
                                <?php } ?>
				<td align="right"> <?php echo  $this->currency->priceDisplay($rule['calc_amount']);  ?></td>
				<td align="right"><?php echo  $this->currency->priceDisplay($rule['calc_amount']);  ?> </td>
			</tr>
			<?php
			} elseif ($rule['calc_kind'] == 'taxRulesBill') { ?>
			<tr class="sectiontableentry<?php $i ?>">
				<td colspan="5" align="right"><?php echo $rule['calc_rule_name'] ?> </td>
				<?php if ( VmConfig::get('show_tax')) { ?>
				<td align="right"><?php echo $this->currency->priceDisplay($rule['calc_amount']); ?> </td>
				 <?php } ?>
				<td align="right"><?php    ?> </td>
				<td align="right"><?php echo $this->currency->priceDisplay($rule['calc_amount']);   ?> </td>
			</tr>
			<?php
			 } elseif ($rule['calc_kind'] == 'DATaxRulesBill') { ?>
			<tr class="sectiontableentry<?php $i ?>">
				<td colspan="5" align="right"><?php echo $rule['calc_rule_name'] ?> </td>
				<?php if ( VmConfig::get('show_tax')) { ?>
				<td align="right"><?php echo   $this->currency->priceDisplay($rule['calc_amount']); ?> </td>
				 <?php } ?>
				<td align="right"><?php    ?> </td>
				<td align="right"><?php echo $this->currency->priceDisplay($rule['calc_amount']);  ?> </td>
			</tr>

			<?php
			 }
			if($i) $i=1; else $i=0;
		}
		?>





    <tr  class="sectiontableentry<?php echo $i ?>">
	<td align="right" style="padding-right: 10px;" colspan="5"><?php echo $this->order['shipmentName'] ?></td>

<?php if (VmConfig::get('show_tax')) { ?>
    	<td align="right"><?php echo "<span class='priceColor2'>" . $this->currency->priceDisplay($this->order['details']['BT']['order_shipment_tax']) . "</span>" ?></td>
	<?php } ?>
	<td align="right"><?php    ?> </td>
	<td align="right"><?php echo $this->currency->priceDisplay($this->order['details']['BT']['order_shipment'] + $this->order['details']['BT']['order_shipment_tax']); ?></td>

    </tr>
<?php
$i = 1 ? 2 : 1;
?>
    <tr   class="sectiontableentry<?php echo $i ?>">
	<td align="right" style="padding-right: 10px;" colspan="5"><?php echo $this->order['shipmentName']  ?></td>


<?php if (VmConfig::get('show_tax')) { ?>
    	<td align="right"><?php echo "<span class='priceColor2'>" . $this->currency->priceDisplay($this->order['details']['BT']['order_payment_tax']) . "</span>" ?></td>
	<?php } ?>
	<td align="right"><?php    ?> </td>
	<td align="right"><?php echo $this->currency->priceDisplay($this->order['details']['BT']['order_payment'] + $this->order['details']['BT']['order_payment_tax']); ?></td>


    </tr>
<?php
$i = 1 ? 2 : 1;

?>



    <tr   class="sectiontableentry<?php echo $i ?>">
	<td align="right" style="padding-right: 10px;" colspan="5"><strong><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_TOTAL') ?></strong></td>

<?php if (VmConfig::get('show_tax')) { ?>
    	<td align="right"><span class='priceColor2'><?php echo $this->currency->priceDisplay($this->order['details']['BT']['order_billTaxAmount']); ?></span></td>
	<?php } ?>
	<td align="right"><?php   echo $this->currency->priceDisplay($this->order['details']['BT']['order_billDiscountAmount']); ?> </td>
	<td align="right"><strong><?php echo $this->currency->priceDisplay($this->order['details']['BT']['order_total']); ?></strong></td>
    </tr>

</table>

