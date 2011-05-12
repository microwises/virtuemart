<?php
/**
*
* Order detail view
*
* @package	VirtueMart
* @subpackage Orders
* @author Oscar van Eijk
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id$
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
?>

<table width="100%" cellspacing="2" cellpadding="4" border="0">
	<tr>
		<td align="left"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_PO_DATE') ?></td>
		<td align="left"><?php echo JHTML::_('date', $this->orderdetails['details']['BT']->created_on); ?></td>
	</tr>
	<tr>
		<td align="left"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_PO_STATUS') ?></td>
		<td align="left"><?php echo $this->orderstatuses[$this->orderdetails['details']['BT']->order_status]; ?></td>
	</tr>
	<tr>
		<td align="left"><?php echo JText::_('COM_VIRTUEMART_LAST_UPDATED') ?></td>
		<td align="left"><?php echo JHTML::_('date', $this->orderdetails['details']['BT']->modified_on); ?></td>
	</tr>
	<tr>
		<td align="left"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_SHIPPING_CARRIER_LBL') ?></td>
		<td align="left"><?php
		if(!class_exists('vmShipperPlugin')) require(JPATH_VM_SITE.DS.'helpers'.DS.'vmshipperplugin.php');
		JPluginHelper::importPlugin('vmshipper');
		$_dispatcher =& JDispatcher::getInstance();
		$_returnValues = $_dispatcher->trigger('plgVmOnShowOrderShipperFE',array(
			 $this->orderdetails['details']['BT']->virtuemart_order_id
		));
		foreach ($_returnValues as $_returnValue) {
			if ($_returnValue !== null) {
				echo $_returnValue;
			}
		}
		?></td>
	</tr>
	<tr>
		<td align="left"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_SUBTOTAL') ?></td>
		<td align="right"><?php echo $this->currency->getFullValue($this->orderdetails['details']['BT']->order_subtotal); ?></td>
	</tr>
	<tr>
		<td align="left"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_SHIPPING') ?></td>
		<td align="right"><?php echo $this->currency->getFullValue($this->orderdetails['details']['BT']->order_shipping); ?></td>
	</tr>
	<tr>
		<td align="left"><?php echo JText::_('COM_VIRTUEMART_CART_SUBTOTAL_DISCOUNT_AMOUNT') ?></td>
		<td align="right"><?php echo $this->currency->getFullValue($this->orderdetails['details']['BT']->order_discount); ?></td>
	</tr>
<?php if (VmConfig::get('coupons_enable',0)=='1') : ?>
	<tr>
		<td align="left"><?php echo JText::_('COM_VIRTUEMART_COUPON_DISCOUNT') ?></td>
		<td align="right"><?php echo $this->currency->getFullValue($this->orderdetails['details']['BT']->coupon_discount); ?></td>
	</tr>
<?php  endif; ?>
	<tr>
		<td align="left"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_TOTAL_TAX') ?></td>
		<td align="right"><?php echo $this->currency->getFullValue($this->orderdetails['details']['BT']->order_tax); ?></td>
	</tr>
	<tr>
		<td align="left"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_TOTAL') ?></td>
		<td align="right"><?php echo $this->currency->getFullValue($this->orderdetails['details']['BT']->order_total); ?></td>
	</tr>
</table>
