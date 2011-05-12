<?php
/**
*
* Orderlist
* NOTE: This is a copy of the edit_orderlist template from the user-view (which in turn is a slighly
*       modified copy from the backend)
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
defined('_JEXEC') or die('Restricted access'); ?>

<?php if (count($this->orderlist) == 0) :?>
<?php echo JText::_('COM_VIRTUEMART_ACC_NO_ORDER'); ?>
<?php else : ?>
<div id="editcell">
	<table class="adminlist">
	<thead>
	<tr>
		<th>
			<?php echo JText::_('COM_VIRTUEMART_ORDER_LIST_ID'); ?>
		</th>
		<th>
			<?php echo JText::_('COM_VIRTUEMART_ORDER_LIST_CDATE'); ?>
		</th>
		<th>
			<?php echo JText::_('COM_VIRTUEMART_ORDER_LIST_MDATE'); ?>
		</th>
		<th>
			<?php echo JText::_('COM_VIRTUEMART_ORDER_LIST_STATUS'); ?>
		</th>
		<th>
			<?php echo JText::_('COM_VIRTUEMART_ORDER_LIST_TOTAL'); ?>
		</th>
	</thead>
	<?php
		$k = 0;
		foreach ($this->orderlist as $row) {
			$editlink = JROUTE::_('index.php?option=com_virtuemart&view=orders&task=details&order_id=' . $row->order_id);
			?>
			<tr class="<?php echo "row$k"; ?>">
				<td align="left">
					<a href="<?php echo $editlink; ?>"><?php echo $row->order_id; ?></a>
				</td>
				<td align="left">
					<?php echo JHTML::_('date', $row->created_on); ?>
				</td>
				<td align="left">
					<?php echo JHTML::_('date', $row->modified_on); ?>
				</td>
				<td align="left">
					<?php echo ShopFunctions::getOrderStatusName($row->order_status); ?>
				</td>
				<td align="left">
					<?php echo $this->currency->getFullValue($row->order_total); ?>
				</td>
			</tr>
	<?php
			$k = 1 - $k;
		}
	?>
	</table>
</div>
<?php endif; ?>
