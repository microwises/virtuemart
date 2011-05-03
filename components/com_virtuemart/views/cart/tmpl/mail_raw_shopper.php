<?php
/**
*
* Layout for the shopper mail, when he confirmed an ordner
*
* The addresses are reachable with $this->BTaddress, take a look for an exampel at shopperadresses.php
*
* With $this->cartData->paymentName or shippingName, you get the name of the used paymentmethod/shippmentmethod
*
* In the array order you have details and items ($this->order['details']), the items gather the products, but that is done directly from the cart data
*
* $this->order['details'] contains the raw address data (use the formatted ones, like BTaddress). Interesting informatin here is,
* order_number ($this->order['details']['BT']->order_number), order_pass, coupon_code, order_status, order_status_name,
* user_currency_rate, cdate, customer_note, ip_address
*
* @package	VirtueMart
* @subpackage Cart
* @author Max Milbers
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

echo JText::sprintf('COM_VIRTUEMART_CART_MAIL_SHOPPER_CONTENT',
						$this->shopperName,
						$this->vendor->vendor_store_name,
						$this->order['details']['BT']->order_total,
						$this->order['details']['BT']->order_number,
						$this->order['details']['BT']->order_pass,
						$this->order['details']['BT']->cdate) . "\n" . "\n";

echo 'Link to view your order' .  JURI::root() . JRoute::_( 'index.php?option=com_virtuemart&controller=orders&task=details&order_number=' . $this->order['details']['BT']->order_number . '&order_pass=' . $this->order['details']['BT']->order_pass ) . "\n";
if(!empty($this->order['details']['BT']->customer_note)){
	echo "\n" . JText::sprintf('COM_VIRTUEMART_CART_MAIL_SHOPPER_QUESTION',$this->order['details']['BT']->customer_note);
}
echo "\n";

//TODO make plain text
//PriceList
include(JPATH_VM_SITE.DS.'views'.DS.'cart'.DS.'tmpl'.DS.'pricelist.php');

include(JPATH_VM_SITE.DS.'views'.DS.'cart'.DS.'tmpl'.DS.'shopperadresses.php');

//TODO if silent registration logindata
//TODO if Paymentmethod needs Bank account data of vendor

//We may wish to integrate later a kind of signature
//include(JPATH_VM_SITE.DS.'views'.DS.'cart'.DS.'tmpl'.DS.'footer.php');

	//Footer for shopper
