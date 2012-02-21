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
* @version $Id: details.php 5314 2012-01-24 15:23:17Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
			if (isset($this->type)) {
			$document = &JFactory::getDocument();
			$document->setTitle(JText::_('COM_VIRTUEMART_ORDER_PRINT_PO_NUMBER').' '.$this->orderdetails['details']['BT']->order_number.' '.$this->vendor->vendor_store_name);
			$document->setName( JText::_('COM_VIRTUEMART_ACC_ORDER_INFO').' '.$this->orderdetails['details']['BT']->order_number);
			$document->setDescription( JText::_('COM_VIRTUEMART_ORDER_PRINT_PO_NUMBER').' '.$this->orderdetails['details']['BT']->order_number);
			}

$this->setLayout('details'); ?>
<style >
	th, .orders-key{font-weight:bold;}
	td.key{font-weight:bold;}
</style>

		<h1><?php echo JText::_('COM_VIRTUEMART_ACC_ORDER_INFO'); ?></h1>

		<div style="padding: 0px; margin: 5px; spacing: 0px;">
		<?php
		// echo $this->loadTemplate('order');

		?>
<table width="100%" cellspacing="0" cellpadding="0" border="0">
    <tr>
	<td   class="orders-key"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_PO_NUMBER') ?></td>
	<td class="orders-key" align="left">
	    <?php echo $this->orderdetails['details']['BT']->order_number; ?>
	</td>
    </tr>
    <tr>
	<td   class="orders-key"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_INV_NUMBER') ?></td>
	<td class="orders-key" align="left">
	    <?php echo $this->invoiceNumber; ?>
	</td>
    </tr>
    <tr>
	<td class=""><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_PO_DATE') ?></td>
	<td align="left"><?php echo vmJsApi::date($this->orderdetails['details']['BT']->created_on, 'LC4', true); ?></td>
    </tr>
    <tr>
	<td class=""><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_PO_STATUS') ?></td>
	<td align="left"><?php echo $this->orderstatuses[$this->orderdetails['details']['BT']->order_status]; ?></td>
    </tr>
    <tr>
	<td class=""><?php echo JText::_('COM_VIRTUEMART_LAST_UPDATED') ?></td>
	<td align="left"><?php echo vmJsApi::date($this->orderdetails['details']['BT']->modified_on, 'LC4', true); ?></td>
    </tr>
    <tr>
	<td class=""><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_SHIPMENT_LBL') ?></td>
	<td align="left"><?php
	    echo $this->shipment_name;
	    ?></td>
    </tr>
    <tr>
	<td class=""><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_PAYMENT_LBL') ?></td>
	<td align="left"><?php echo $this->payment_name; ?>
	</td>
    </tr>

	 <tr>
    <td><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_CUSTOMER_NOTE') ?></td>
    <td valign="top" align="left" width="50%"><?php echo $this->orderdetails['details']['BT']->customer_note; ?></td>
</tr>

     <tr>
	<td class="orders-key"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_TOTAL') ?></td>
	<td class="orders-key" align="left"><?php echo $this->currency->priceDisplay($this->orderdetails['details']['BT']->order_total); ?></td>
    </tr>

    <tr>
	<td colspan="2"> &nbsp;</td>
    </tr>
    <tr>
	<td valign="top">
	    <table border="0">
			 <tr>
				<th class="orders-key"width="100%" colspan="2"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_BILL_TO_LBL') ?></th>
			</tr>
		<?php
	    foreach ($this->userfields['fields'] as $field) {
		if (!empty($field['value'])) {
		    echo '<tr><td class="key">' . $field['title'] . '</td>'
		    . '<td>' . $field['value'] . '</td></tr>';
		}
	    }
	    ?></table>
	</td>
	<td valign="top" >
		<table border="0">
			 <tr>
				<th class="orders-key"width="100%" colspan="2"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_SHIP_TO_LBL') ?></th>
			</tr>
			<?php
			foreach ($this->shipmentfields['fields'] as $field) {
			if (!empty($field['value'])) {
				echo '<tr><td class="key">' . $field['title'] . '</td>'
				. '<td>' . $field['value'] . '</td></tr>';
			}
			}
			?>
		</table>
	</td>
    </tr>
</table>
		</div>

		<div style="padding: 0px; margin: 0px; spacing: 0px;">
		<?php echo $this->loadTemplate('items');
		?>
		</div>
		<?php	//echo $this->vendor->vendor_legal_info; ?>
