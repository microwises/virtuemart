<?php
/**
 * Popup form to edit the formstatus
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
?>

<form action="index.php" method="post" name="orderStatForm" id="orderStatForm">
<fieldset>
<table class="admintable" width="100%">
	<tr>
		<td align="center" colspan="2">
		<h1><?php echo JText::_('VM_ORDER_PRINT_ITEMEDIT_LBL') ?></h1>
		</td>
	</tr>
	<tr>
		<td class="key"><?php echo JText::_('VM_ORDER_PRINT_PO_STATUS') ?></td>
		<td><?php echo $this->orderStatSelect; ?>
		</td>
	</tr>
	<tr>
		<td class="key"><?php echo JText::_('VM_COMMENT') ?></td>
		<td><textarea rows="6" cols="35"
			name="<?php
				echo 'order_comment['.$this->orderID.']';
			?>"></textarea>
		</td>
	</tr>
	<tr>
		<td class="key"><?php echo JText::_('VM_ORDER_LIST_NOTIFY') ?></td>
		<td><?php echo VmHTML::checkbox('notify_customer['.$this->orderID.']', true); ?>
		</td>
	</tr>
	<tr>
		<td class="key"><?php echo JText::_('VM_ORDER_HISTORY_INCLUDE_COMMENT') ?></td>
		<td><br />
		<?php echo VmHTML::checkbox('include_comment['.$this->orderID.']', true); ?>
		</td>
	</tr>
	<tr>
		<td class="key"><?php echo JText::_('VM_ORDER_UPDATE_LINESTATUS') ?></td>
		<td><br />
		<?php echo VmHTML::checkbox('update_lines['.$this->orderID.']', true); ?>
		</td>
	</tr>
	<tr>
		<td colspan="2" align="center" class="key">
		<a href="#" onClick="javascript:document.orderStatForm.submit();"><?php
			echo JHTML::_('image', 'administrator/components/com_virtuemart/assets/images/icon_16/icon-16-save.png', JText::_('Save'))
				. '&nbsp;'
				. JText::_('Save');
		?></a>&nbsp;&nbsp;&nbsp;
		<a href="#" onClick="javascript:document.orderStatForm.reset();" class="show_element[updateOrderStatus]"><?php
			echo JHTML::_('image', 'administrator/components/com_virtuemart/assets/images/icon_16/icon-16-remove.png', JText::_('Cancel'))
				. '&nbsp;'
				. JText::_('Cancel');
		?></a>
		</td>
<!-- 
		<input type="submit" value="<?php echo JText::_('SAVE');?>" style="font-size: 10px" />
		<input type="button"
			onclick="javascript: window.parent.document.getElementById( 'sbox-window' ).close();"
			value="<?php echo JText::_('CANCEL');?>" style="font-size: 10px" /></td>
 -->
	</tr>
</table>
</fieldset>

<!-- Hidden Fields -->
<input type="hidden" name="task" value="updatestatus" />
<input type="hidden" name="option" value="com_virtuemart" />
<input type="hidden" name="view" value="orders" />
<input type="hidden" name="current_order_status['<?php echo $this->orderID; ?>']" value="<?php echo $this->currentOrderStat; ?>" />
<input type="hidden" name="order_id" value="<?php echo $this->orderID; ?>" />
<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
</form>
