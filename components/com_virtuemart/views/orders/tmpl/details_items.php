<?php
/**
*
* Order items view
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
	<tr align="left" class="sectiontableheader">
		<th align="left" ><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_SKU') ?></th>
		<th align="right" ><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_QTY') ?></th>
		<th align="left" colspan="2"><?php echo JText::_('COM_VIRTUEMART_PRODUCT_NAME_TITLE') ?></th>
		<th align="right" ><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_PRICE') ?></th>
		<th align="right" ><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_TOTAL') ?></th>
		<th align="left" ><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_PO_STATUS') ?></th>
	</tr>
<?php
	foreach($this->orderdetails['items'] as $_item) {
		$_link = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $_item->virtuemart_product_id);
?>
		<tr valign="top">
			<td align="left" >
				<?php echo $_item->order_item_sku; ?>
			</td>
			<td align="right" >
				<?php echo $_item->product_quantity; ?>
			</td>
			<td align="left" >
				<a href="<?php echo $_link; ?>"><?php echo $_item->order_item_name; ?></a>
			</td>
			<td align="left" >
				<?php echo implode(', ', explode("\n", $_item->product_attribute)); ?>
			</td>
			<td align="right" >
				<?php echo $this->currency->priceDisplay($_item->product_final_price); ?>
			</td>
			<td align="right" >
				<?php echo $this->currency->priceDisplay($_item->product_quantity * $_item->product_final_price); ?>
			</td>
			<td align="left" >
				<?php echo $this->orderstatuses[$_item->order_status]; ?>
			</td>
		</tr>

<?php
	}
?>
</table>
