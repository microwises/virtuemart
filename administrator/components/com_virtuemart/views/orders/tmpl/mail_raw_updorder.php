<?php

echo JText::_('COM_VIRTUEMART_HI') . ' ' . $this->user->full_name . "\n\n";
echo JText::sprintf('COM_VIRTUEMART_ORDER_STATUS_CHANGE_SEND_MSG_1', $this->orderdata['details']['BT']->order_number) . "\n" . "\n";


if ($this->order->_customer_notified) {
    echo JText::_('COM_VIRTUEMART_ORDER_HISTORY_COMMENT_EMAIL') .   "\n";
    echo $this->order->_comments . "\n";
   echo "\n";
}
echo JText::_('COM_VIRTUEMART_ORDER_STATUS_CHANGE_SEND_MSG_2').  $this->user->order_status_name . "\n" . "\n";

if (VmConfig::get('vm_registration_type') != 'NO_REGISTRATION') {
    echo "\n";
    echo JText::_('COM_VIRTUEMART_ORDER_STATUS_CHANGE_SEND_MSG_3') . "\n";
    echo JURI::root() . 'index.php?option=com_virtuemart&view=orders&layout=details&order_number=' . $this->orderdata['details']['BT']->order_number . '&order_pass=' . $this->orderdata['details']['BT']->order_pass . "\n" . "\n";
}
echo "\n";
echo $this->vendor->vendor_name . "\n";
echo VmConfig::get('url') . "\n";
echo $this->vendor->email . "\n";