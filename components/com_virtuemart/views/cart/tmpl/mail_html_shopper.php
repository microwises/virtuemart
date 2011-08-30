<?php
/**
*
* Layout for the shopper mail, when he confirmed an ordner
*
* The addresses are reachable with $this->BTaddress, take a look for an exampel at shopper_adresses.php
*
* With $this->cartData->paymentName or shippingName, you get the name of the used paymentmethod/shippmentmethod
*
* In the array order you have details and items ($this->order['details']), the items gather the products, but that is done directly from the cart data
*
* $this->order['details'] contains the raw address data (use the formatted ones, like BTaddress). Interesting informatin here is,
* order_number ($this->order['details']['BT']->order_number), order_pass, coupon_code, order_status, order_status_name,
* user_currency_rate, created_on, customer_note, ip_address
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
defined('_JEXEC') or die('Restricted access'); ?>

<html>
	
	<head>
		<style type="text/css">
			table.html-email {margin:30px auto;background:#fff;border:solid #dad8d8 1px;padding:25px;}
			.html-email tr{border-bottom : 1px solid #eee;}
			span.grey {color:#666;}
			a.default:link, a.default:hover, a.default:visited {color:#666;line-height:25px;background: #f2f2f2;margin: 10px ;padding: 3px 8px 1px 8px;border: solid #CAC9C9 1px;border-radius: 4px;-webkit-border-radius: 4px;-moz-border-radius: 4px;text-shadow: 1px 1px 1px #f2f2f2;font-size: 12px;background-position: 0px 0px;display: inline-block;text-decoration: none;}
			a.default:hover {color:#888;background: #f8f8f8;}
			.cart-summary{font-size: 8px;}
			.sectiontableentry2, .cart-summary th{font-size:8px;background: #ccc;margin: 0px;padding: 10px;}
			.sectiontableentry1, .cart-summary td {font-size:8px;background: #fff;margin: 0px;padding: 10px;}
		</style>
	
	</head>
	
	<body style="background: f2f2f2;word-wrap: break-word;">

<table width="100%" border="0" cellpadding="0" cellspacing="0" class="html-email">
  <tr>
    <td colspan="2"><strong><?php echo JText::sprintf('COM_VIRTUEMART_CART_MAIL_SHOPPER_NAME',$this->shopperName); ?></strong><br/></td> 
	<td align="right"><span class="grey"><?php echo $this->order['details']['BT']->created_on ?></span><br/></td>
  </tr>
  <tr>
    <td colspan="3" bgcolor="#ccc">
				<?php echo JText::sprintf('COM_VIRTUEMART_CART_MAIL_SHOPPER_SUMMARY',$this->vendor->vendor_store_name); ?></td>
  </tr>
  <tr>
    <td width="25%">
		<p><?php echo JText::_('COM_VIRTUEMART_CART_MAIL_SHOPPER_YOUR_ORDER'); ?><br />
		<b><?php echo $this->order['details']['BT']->order_number ?></b>
		</p>
	</td>
    <td width="25%">
		<p><?php echo JText::_('COM_VIRTUEMART_CART_MAIL_SHOPPER_YOUR_PASSWORD'); ?><br />
		<b><?php echo $this->order['details']['BT']->order_pass ?></b></p>
	</td>
    <td width="50%">
    	<p/>
			<a class="default" title="<?php echo $this->vendor->vendor_store_name ?>" href="<?php echo JURI::root().'index.php?option=com_virtuemart&view=orders&task=details&order_number='.$this->order['details']['BT']->order_number.'&order_pass='.$this->order['details']['BT']->order_pass; ?>">
			<?php echo JText::_('COM_VIRTUEMART_CART_MAIL_SHOPPER_YOUR_ORDER_LINK'); ?></a>
		</p>
	</td>
  </tr>
  <tr>
    <td colspan="3">
				<?php echo JText::sprintf('COM_VIRTUEMART_CART_MAIL_SHOPPER_TOTAL_ORDER',$this->prices['billTotal'] ); ?></td>
  </tr>
  <tr>
    <td colspan="3">
		<?php include(JPATH_VM_SITE.DS.'views'.DS.'cart'.DS.'tmpl'.DS.'shopper_adresses.php'); ?>
	</td>
  </tr>
  <?php if(!empty($this->order['details']['BT']->customer_note)){ ?>
  <tr>
    <td colspan="3">
		<?php echo JText::sprintf('COM_VIRTUEMART_CART_MAIL_SHOPPER_QUESTION',$this->order['details']['BT']->customer_note) ?>

	</td>
  </tr>
  <?php } ?>
  <tr>
    <td colspan="3">
		<?php include(JPATH_VM_SITE.DS.'views'.DS.'cart'.DS.'tmpl'.DS.'price_list.php'); ?>
	</td>
  </tr>
</table>

</body>
</html>