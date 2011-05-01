<?php echo JText::_('COM_VIRTUEMART_HI',false) .' '. $this->user->full_name; ?>
<?php echo JText::_('COM_VIRTUEMART_ORDER_STATUS_CHANGE_SEND_MSG_1',false); ?>

<?php
if (array_key_exists($this->order->order_id, $this->includeComments) && !empty($this->_comments)) {
echo JText::_('COM_VIRTUEMART_ORDER_HISTORY_COMMENT_EMAIL',false).":"; ?>
<?php echo $this->_comments; ?>
____________________________________________________________;
<?php } ?>

<?php echo JText::_('COM_VIRTUEMART_ORDER_STATUS_CHANGE_SEND_MSG_2',false); ?>
____________________________________________________________
<?php echo $this->user->order_status_name; ?>

<?php
if (VmConfig::get('vm_registration_type') != 'NO_REGISTRATION' ) { ?>
____________________________________________________________
<?php echo JText::_('COM_VIRTUEMART_ORDER_STATUS_CHANGE_SEND_MSG_3',false); ?>
<?php echo $this->url; ?>
<?php } ?>
____________________________________________________________
<?php echo $this->vendor->vendor_name; ?>
<?php echo VmConfig::get('url'); ?>
<?php echo $this->vendor->email; ?>