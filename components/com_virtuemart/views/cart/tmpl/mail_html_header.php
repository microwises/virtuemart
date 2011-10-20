<?php
/**
*
* Define here the Header for order mail success !
*
* @package	VirtueMart
* @subpackage Cart
* @author Kohl Patrick
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
/* TODO Change the header place in helper or assets ???*/ ?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="html-email">
  <tr>
    <td colspan="2"><strong><?php echo JText::sprintf('COM_VIRTUEMART_CART_MAIL_SHOPPER_NAME',$this->shopperName); ?></strong><br/></td>
	<td align="right"><span class="date"><?php echo $this->order['details']['BT']->created_on ?></span><br/></td>
  </tr>
  <tr>
    <td colspan="3">
	<?php echo JURI::root().$this->cart->vendor->images[0]->displayMediaThumb('class="product-image"',false,' ')?>
	<br/>
	<?php echo JText::sprintf('COM_VIRTUEMART_CART_MAIL_SHOPPER_SUMMARY',$this->cart->vendor->vendor_store_name); ?>
    </td>
  </tr>
</table>
