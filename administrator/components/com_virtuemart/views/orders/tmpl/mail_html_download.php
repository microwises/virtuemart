<?php
$expire = ((VmConfig::get('download_expire') / 60) / 60) / 24;
echo JText::_('COM_VIRTUEMART_HI',false) .' '. $this->user->full_name; ?>
<?php echo JText::_('COM_VIRTUEMART_DOWNLOADS_SEND_MSG_1',false); ?>
<?php echo JText::_('COM_VIRTUEMART_DOWNLOADS_SEND_MSG_2',false); ?>

<?php
foreach ($this->downloads as $key => $download) {
	echo $download->file_name . ": " . $download->download_id. "\n".
	$url . "&download_id=" . $download->download_id . "\n\n";
}
?>

<?php echo JText::_('COM_VIRTUEMART_DOWNLOADS_SEND_MSG_3',false) . VmConfig::get('download_max'); ?>
<?php echo JText::sprintf('COM_VIRTUEMART_DOWNLOADS_SEND_MSG_4',$expire); ?>

____________________________________________________________

<?php echo JText::_('COM_VIRTUEMART_DOWNLOADS_SEND_MSG_5',false); ?>

____________________________________________________________
<?php echo $this->vendor->vendor_name; ?>
<?php echo VmConfig::get('url'); ?>
<?php echo $this->vendor->email; ?>
<?php echo JText::_('COM_VIRTUEMART_DOWNLOADS_SEND_MSG_6',false) . $this->vendor->vendor_name; ?>
