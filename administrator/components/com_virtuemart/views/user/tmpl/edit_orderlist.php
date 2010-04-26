<?php
/**
*
* User details, Orderlist
*
* @package	VirtueMart
* @subpackage User
* @author Oscar van Eijk
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: edit.php 2302 2010-02-07 19:57:37Z rolandd $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access'); ?>

<div id="editcell">
	<table class="adminlist">
	<thead>
	<tr>
		<th width="10">
			<?php echo JText::_('#'); ?>
		</th>
		<th>
			<?php echo JText::_('VM_ORDER_LIST_ID'); ?>
		</th>
		<th>
			<?php echo JText::_('VM_CHECK_OUT_THANK_YOU_PRINT_VIEW'); ?>
		</th>
		<th>
			<?php echo JText::_('VM_ORDER_LIST_CDATE'); ?>
		</th>
		<th>
			<?php echo JText::_('VM_ORDER_LIST_MDATE'); ?>
		</th>
		<th>
			<?php echo JText::_('VM_ORDER_LIST_STATUS'); ?>
		</th>
		<th>
			<?php echo JText::_('VM_ORDER_LIST_TOTAL'); ?>
		</th>
	</thead>
	<?php
		$k = 0;
		for ($i = 1, $n = count($this->orderlist); $i <= $n; $i++) {
			$row =& $this->orderlist[$i];
			$editlink = JROUTE::_('index.php?option=com_virtuemart&view=orders&task=edit&order_id=' . $row->order_id);

			$print_url = JURI::root().'index.php?option=com_virtuemart&view=orders&task=orderprintdetails&order_id='.$row->order_id.'&format=raw';
			$print_link = "&nbsp;<a href=\"javascript:void window.open('$print_url', 'win2', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');\">"
				. JHTML::_('image', 'images/M_images/printButton.png', JText::_('PRINT'), array('align' => 'center', 'height'=> '16',  'width' => '16', 'border' => '0')).'</a>';
			
			?>
			<tr class="<?php echo "row$k"; ?>">
				<td align="center">
					<?php echo ($n-$i+1); ?>
				</td>
				<td align="left">
					<a href="<?php echo $editlink; ?>"><?php echo $row->order_id; ?></a>
				</td>
				<td align="center">
					<?php echo $print_link; ?>
				</td>
				<td align="left">
					<?php echo ShopFunctions::formatDate($row->cdate, 'd-M-y H:i'); ?>
				</td>
				<td align="left">
					<?php echo ShopFunctions::formatDate($row->mdate, 'd-M-y H:i'); ?>
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
