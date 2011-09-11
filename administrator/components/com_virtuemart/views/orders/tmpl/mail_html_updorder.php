<?php 
echo JText::_('COM_VIRTUEMART_HI') .' '. $this->user->full_name . '<br/>';
echo JText::sprintf('COM_VIRTUEMART_ORDER_STATUS_CHANGE_SEND_MSG_1', $this->order['virtuemart_order_id'] ).'<br/>'  .'<br/>';

if (array_key_exists($this->order['virtuemart_order_id'], $this->includeComments) && !empty($this->includeComments)) {
	echo JText::_('COM_VIRTUEMART_ORDER_HISTORY_COMMENT_EMAIL').":"  .'<br/>';
	echo implode('<br/>', $this->includeComments)  .'<br/>';
	echo ' ____________________________________________________________'  .'<br/>';
}

echo JText::_('COM_VIRTUEMART_ORDER_STATUS_CHANGE_SEND_MSG_2') ?><br/>
____________________________________________________________<br/>
<?php echo $this->user->order_status_name  ?><br/><br/>
<?php if (VmConfig::get('vm_registration_type') != 'NO_REGISTRATION' ) { ?>
  ____________________________________________________________<br/>
<?php echo JText::_('COM_VIRTUEMART_ORDER_STATUS_CHANGE_SEND_MSG_3') ?><br/>
	<p/>
			<a class="default" title="<?php echo $this->vendor->vendor_store_name ?>" href="<?php echo JURI::root().'index.php?option=com_virtuemart&view=orders&task=details&order_number='.$this->orderdata['details']['BT']->order_number.'&order_pass='.$this->orderdata['details']['BT']->order_pass; ?>">
			<?php echo JText::_('COM_VIRTUEMART_CART_MAIL_SHOPPER_YOUR_ORDER_LINK'); ?></a>
		</p><br/><br/>
<?php } ?>
____________________________________________________________<br/>
<?php echo $this->vendor->vendor_name  .'<br/>';
echo VmConfig::get('url')  .'<br/>';
echo $this->vendor->email  .'<br/>';
