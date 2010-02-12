<br />
<form action="index.php" method="post" name="adminForm" id="adminForm">
    <table class="admintable" width="100%">
	<tr>
	    <td align="center" colspan="2">
		<h1><?php echo JText::_('VM_ORDER_PRINT_ITEMEDIT_LBL') ?></h1>
	    </td>
	</tr>
	<tr>
	    <td class="key"><?php echo JText::_('VM_ORDER_PRINT_PO_STATUS') ?></td>
	    <td>
		<?php echo JHTML::_('select.genericlist', $this->orderstatuses, 'new_status', '', 'value', 'text', $this->orderitem->order_status, 'order_status'); ?>
	    </td>
	</tr>
	<tr>
	    <td class="key"><?php echo JText::_('VM_COMMENT') ?></td>
	    <td>
		<textarea rows="6" cols="35" name="comments"></textarea>
	    </td>
	</tr>
	<tr>
	    <td class="key"><?php echo JText::_('VM_ORDER_LIST_NOTIFY') ?></td>
	    <td>
		<?php echo VmHTML::checkbox('notify_customer', true); ?>
	    </td>
	</tr>
	<tr>
	    <td class="key"><?php echo JText::_('VM_ORDER_HISTORY_INCLUDE_COMMENT') ?></td>
	    <td>
		<br />
		<?php echo VmHTML::checkbox('include_comment', true); ?>
	    </td>
	</tr>
	<tr>
	    <td colspan="2" align="center">
		<br />
		<input type="submit" value="<?php echo JText::_('SAVE');?>" style="font-size:10px" />
		<input type="submit" value="<?php echo JText::_('CANCEL');?>" style="font-size:10px" />
	    </td>
	</tr>
    </table>

    <!-- Hidden Fields -->
    <input type="hidden" name="task" value="saveItemStatus" />
    <input type="hidden" name="option" value="com_virtuemart" />
    <input type="hidden" name="view" value="orders" />
    <input type="hidden" name="order_id" value="<?php echo $this->orderitem->order_id; ?>" />
    <input type="hidden" name="order_item_id" value="<?php echo $this->orderitem->order_item_id; ?>" />
</form>