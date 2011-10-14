<?php

echo JText::sprintf('COM_VIRTUEMART_WELCOME_VENDOR', $this->vendor->vendor_store_name) . "\n" . "\n";
echo JText::_('COM_VIRTUEMART_QUESTION_ABOUT') . "\n" . "\n";
echo JText::sprintf('COM_VIRTUEMART_QUESTION_MAIL_FROM', $this->user->name, $this->user->email) . "\n";
echo JText::sprintf('COM_VIRTUEMART_QUESTION_MAIL_PRODUCT', $this->product->product_name) . "\n";
echo $this->comment. "\n";
