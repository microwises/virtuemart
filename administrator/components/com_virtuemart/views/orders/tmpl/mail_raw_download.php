<?php
$expire = ((VmConfig::get('download_expire') / 60) / 60) / 24;
echo JText::_('COM_VIRTUEMART_HI',false) .' '. $this->user->full_name . "\n";
echo JText::_('COM_VIRTUEMART_DOWNLOADS_SEND_MSG_1',false) . "\n";
echo JText::_('COM_VIRTUEMART_DOWNLOADS_SEND_MSG_2',false) . "\n" . "\n";

foreach ($this->downloads as $key => $download) {
	echo $download->file_name . ": " . $download->download_id. "\n".
	$url . "&download_id=" . $download->download_id . "\n" . "\n";
}

echo JText::_('COM_VIRTUEMART_DOWNLOADS_SEND_MSG_3',false) . VmConfig::get('download_max') . "\n";
echo JText::sprintf('COM_VIRTUEMART_DOWNLOADS_SEND_MSG_4',$expire) . "\n";
echo '____________________________________________________________' . "\n";

echo JText::_('COM_VIRTUEMART_DOWNLOADS_SEND_MSG_5',false) . "\n";
echo '____________________________________________________________' . "\n";
echo $this->vendor->vendor_name . "\n";
echo VmConfig::get('url') . "\n";
echo $this->vendor->email . "\n";
echo JText::_('COM_VIRTUEMART_DOWNLOADS_SEND_MSG_6',false) . $this->vendor->vendor_name . "\n";
