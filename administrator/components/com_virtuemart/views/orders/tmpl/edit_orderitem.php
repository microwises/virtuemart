<br /><br />
<form action="index.php" method="post" name="adminForm" id="adminForm">
    <table class="admintable" width="100%">
	<tr>
	    <td class="key" style="text-align: center;" colspan="2"><?php echo JText::_('VM_ORDER_PRINT_ITEMEDIT_LBL') ?></td>
	</tr>
	<tr>
	    <td class="key"><?php echo JText::_('VM_ORDER_PRINT_QUANTITY') ?></td>
	    <td>
		<input type="text" size="10" name="product_quantity" value="<?php echo $this->orderitem->product_quantity;?>">
	    </td>
	</tr>
	<tr>
	    <td class="key"><?php echo JText::_('VM_ORDER_PRINT_NAME') ?></td>
	    <td>
		<?php echo $this->orderitem->order_item_name;?>
	    </td>
	</tr>
	<tr>
	    <td class="key"><?php echo JText::_('VM_ORDER_PRINT_SKU') ?></td>
	    <td>
		<?php echo $this->orderitem->order_item_sku;?>
	    </td>
	</tr>
	<tr>
	    <td class="key"><?php echo JText::_('VM_ORDER_PRINT_PO_STATUS') ?></td>
	    <td>
		<?php echo JHTML::_('select.genericlist', $this->orderstatuses, 'order_status', '', 'value', 'text', $this->orderitem->order_status, 'order_status'); ?>
	    </td>
	</tr>
	<tr>
	    <td class="key"><?php echo JText::_('VM_PRODUCT_FORM_PRICE_NET') ?></td>
	    <td>
		<input type="text" size="10" name="product_item_price" value="<?php echo $this->orderitem->product_item_price;?>">
	    </td>
	</tr>
	<tr>
	    <td class="key"><?php echo JText::_('VM_PRODUCT_FORM_PRICE_GROSS') ?></td>
	    <td>
		<input type="text" size="10" name="product_final_price" value="<?php echo $this->orderitem->product_final_price;?>">
	    </td>
	</tr>
	<tr>
	    <td colspan="2">
		<br />
		<input type="submit" value="<?php echo JText::_('SAVE');?>" style="font-size:10px" />
	    </td>
	</tr>
    </table>

    <!-- Hidden Fields -->
    <input type="hidden" name="task" value="saveOrderItem" />
    <input type="hidden" name="option" value="com_virtuemart" />
    <input type="hidden" name="view" value="orders" />
    <input type="hidden" name="order_id" value="<?php echo $this->order_id; ?>" />
    <input type="hidden" name="order_item_id" value="<?php echo $this->order_item_id; ?>" />
</form>