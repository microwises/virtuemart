<?php
/**
*
* Layout for the shopping cart, look in mailshopper for more details
*
* @package	VirtueMart
* @subpackage Cart
* @author Max Milbers, Valerie Isaksen
*
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');


//	echo JText::_('COM_VIRTUEMART_CART_MAIL_VENDOR_TITLE').$this->vendor->vendor_name.'<br/>';
	echo str_replace("<br />", "\n",  JText::sprintf('COM_VIRTUEMART_CART_MAIL_VENDOR_CONTENT',$this->vendor->vendor_store_name,$this->shopperName,$this->currency->priceDisplay($this->order['details']['BT']['order_total']),$this->order['details']['BT']['order_number'] ));

if(!empty($this->order['details']['BT']->customer_note)) {
	echo "\n" . JText::sprintf('COM_VIRTUEMART_CART_MAIL_VENDOR_SHOPPER_QUESTION', $this->order['details']['BT']->customer_note);
}
echo "\n";