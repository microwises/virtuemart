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
			<?php echo JHTML::_('select.genericlist', $this->orderstatuses, 'order_status['.$this->order_id.']', '', 'value', 'text', $this->cur_order_status, 'order_status'); ?>
		</td>
	</tr>
	<tr>
		<td class="key"><?php echo JText::_('VM_COMMENT') ?></td>
		<td>
			<textarea rows="6" cols="35" name="<?php echo 'order_comment['.$this->order_id.']'; ?>"></textarea>
		</td>
	</tr>
	<tr>
		<td class="key"><?php echo JText::_('VM_ORDER_LIST_NOTIFY') ?></td>
		<td>
			<?php echo VmHTML::checkbox('notify_customer['.$this->order_id.']', true); ?>
		</td>
	</tr>
	<?php if ($this->line_only == 0) : ?>
	<tr>
		<td class="key"><?php echo JText::_('VM_ORDER_HISTORY_INCLUDE_COMMENT') ?></td>
		<td>
			<br />
			<?php echo VmHTML::checkbox('include_comment['.$this->order_id.']', true); ?>
		</td>
	</tr>
	<tr>
		<td class="key"><?php echo JText::_('VM_ORDER_UPDATE_LINESTATUS') ?></td>
		<td>
			<br />
			<?php echo VmHTML::checkbox('update_lines['.$this->order_id.']', true); ?>
		</td>
	</tr>
	<?php endif;?>
	<tr>
		<td colspan="2" align="center">
			<br />
			<input type="submit" value="<?php echo JText::_('SAVE');?>" style="font-size:10px" />
			<input type="button" onclick="javascript: window.parent.document.getElementById( 'sbox-window' ).close();" value="<?php echo JText::_('CANCEL');?>" style="font-size:10px" />
		</td>
	</tr>
</table>

<!-- Hidden Fields -->
<input type="hidden" name="task" value="updatestatus" />
<input type="hidden" name="option" value="com_virtuemart" />
<input type="hidden" name="view" value="orders" />
<input type="hidden" name="line_only" value="<?php echo $this->line_only; ?>" />
<input type="hidden" name="current_order_status['<?php echo $this->order_id; ?>']" value="<?php echo $this->cur_order_status; ?>" />
<input type="hidden" name="order_id" value="<?php echo $this->order_id; ?>" />
<?php if ($this->line_only == 1) : ?>
<input type="hidden" name="order_item_id[]" value="<?php echo $this->orderitem->order_item_id; ?>" />
<?php endif; ?>
</form>