<?php echo JText::_('COM_VIRTUEMART_HI',false) .' '. $this->user->full_name . "\n";
echo JText::_('COM_VIRTUEMART_ORDER_STATUS_CHANGE_SEND_MSG_1',false) . "\n"  . "\n";

if (array_key_exists($this->order->order_id, $this->includeComments) && !empty($this->_comments)) {
	echo JText::_('COM_VIRTUEMART_ORDER_HISTORY_COMMENT_EMAIL',false).":"  . "\n";
	echo $this->_comments  . "\n";
	echo ' ____________________________________________________________'  . "\n";
}

echo JText::_('COM_VIRTUEMART_ORDER_STATUS_CHANGE_SEND_MSG_2',false)  . "\n";
echo '____________________________________________________________'  . "\n";
echo $this->user->order_status_name  . "\n"  . "\n";

if (VmConfig::get('vm_registration_type') != 'NO_REGISTRATION' ) {
	echo '____________________________________________________________'  . "\n";
	echo JText::_('COM_VIRTUEMART_ORDER_STATUS_CHANGE_SEND_MSG_3',false)  . "\n";
	echo $this->url  . "\n"  . "\n";
}
echo '____________________________________________________________'  . "\n";
echo $this->vendor->vendor_name  . "\n";
echo VmConfig::get('url')  . "\n";
echo $this->vendor->email  . "\n";