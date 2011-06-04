<?php 
echo JText::_('COM_VIRTUEMART_HI') .' '. $this->user->full_name . '<br/>';
echo JText::sprintf('COM_VIRTUEMART_ORDER_STATUS_CHANGE_SEND_MSG_1', $this->order->virtuemart_order_id) .'<br/>'  .'<br/>';

if (array_key_exists($this->order->virtuemart_order_id, $this->includeComments) && !empty($this->includeComments)) {
	echo JText::_('COM_VIRTUEMART_ORDER_HISTORY_COMMENT_EMAIL').":"  .'<br/>';
	echo implode('<br/>', $this->includeComments)  .'<br/>';
	echo ' ____________________________________________________________'  .'<br/>';
}

echo JText::_('COM_VIRTUEMART_ORDER_STATUS_CHANGE_SEND_MSG_2')  .'<br/>';
echo '____________________________________________________________'  .'<br/>';
echo $this->user->order_status_name  .'<br/>'  .'<br/>';

if (VmConfig::get('vm_registration_type') != 'NO_REGISTRATION' ) {
	echo '____________________________________________________________'  .'<br/>';
	echo JText::_('COM_VIRTUEMART_ORDER_STATUS_CHANGE_SEND_MSG_3')  .'<br/>';
	echo $this->url  .'<br/>'  .'<br/>';
}
echo '____________________________________________________________'  .'<br/>';
echo $this->vendor->vendor_name  .'<br/>';
echo VmConfig::get('url')  .'<br/>';
echo $this->vendor->email  .'<br/>';