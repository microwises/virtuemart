<?php
defined('_JEXEC') or die('Restricted access'); 
AdminMenuHelper::startAdminArea(); 
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<div id="header">
	<div id="filterbox" style="float: left;">
	<table>
	  <tr>
		 <td align="left" width="100%">
			<?php echo JText::_('Filter'); ?>:
			<input type="text" name="filter_orders" value="<?php echo JRequest::getVar('filter_orders', ''); ?>" />
			<button onclick="this.form.submit();"><?php echo JText::_('Go'); ?></button>
			<button onclick="document.adminForm.filter_orders.value='';"><?php echo JText::_('Reset'); ?></button>
		 </td>
	  </tr>
	</table>
	</div>
	<div id="resultscounter" style="float: right;"><?php echo $this->pagination->getResultsCounter();?></div>
</div>
<br clear="all" />
<table class="adminlist">
<thead>
<tr>
	<th><input type="checkbox" name="toggle" value="" onclick="checkAll('<?php echo count($this->orderslist); ?>')" /></th>
	<th><?php echo JHTML::_('grid.sort', 'VM_ORDER_LIST_ID', 'order_id', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
	<th><?php echo JHTML::_('grid.sort', 'VM_ORDER_PRINT_NAME', 'order_id', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
	<th><?php echo JHTML::_('grid.sort', 'VM_ORDER_PAYMENT_NAME', 'order_id', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
	<th><?php echo JHTML::_('grid.sort', 'VM_CHECK_OUT_THANK_YOU_PRINT_VIEW', 'order_id', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
	<th><?php echo JHTML::_('grid.sort', 'VM_ORDER_LIST_CDATE', 'order_id', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
	<th><?php echo JHTML::_('grid.sort', 'VM_ORDER_LIST_MDATE', 'order_id', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
	<th><?php echo JHTML::_('grid.sort', 'VM_ORDER_LIST_STATUS', 'order_id', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
	<th><?php echo JHTML::_('grid.sort', 'VM_ORDER_LIST_NOTIFY', 'order_id', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
	<th><?php echo JHTML::_('grid.sort', 'VM_ORDER_LIST_TOTAL', 'order_id', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
</tr>
</thead>
<tbody>
<?php
if (count($this->orderslist) > 0) {
	$i = 0;
	$k = 0;
	$keyword = JRequest::getVar('keyword');
	foreach ($this->orderslist as $key => $order) {
		$checked = JHTML::_('grid.id', $i , $order->order_id);
		?>
		<tr class="<?php echo "row$k"; ?>">
			<!-- Checkbox -->
			<td><?php echo $checked; ?></td>
			<!-- Order id -->
			<?php 
			$link = 'index.php?option='.$option.'&view=orders&task=edit&order_id='.$order->order_id;
			?>
			<td><?php echo JHTML::_('link', JRoute::_($link), $order->order_id, array('title' => JText::_('EDIT').' '.$order->order_id)); ?></td>
			<!-- Name -->
			<td><?php echo $order->order_name; ?></td>
			<!-- Payment method -->
			<td><?php echo $order->payment_method; ?></td>
			<!-- Print view -->
			<?php
			/* Print view URL */
			$details_url = JURI::root()."?option=".$option."&view=orders&task=orderprintdetails&order_id=".$order->order_id."&format=raw";
			$details_link = "&nbsp;<a href=\"javascript:void window.open('$details_url', 'win2', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');\">";
			$details_link .= JHTML::_('image', 'images/M_images/printButton.png', JText::_('PRINT'), array('align' => 'center', 'height'=> '16',  'width' => '16', 'border' => '0')).'</a>';
			?>
			<td><?php echo $details_link; ?></td>
			<!-- Order date -->
			<td><?php echo date('d-M-y H:i', $order->cdate); ?></td>
			<!-- Last modified -->
			<td><?php echo date('d-M-y H:i', $order->mdate); ?></td>
			<!-- Status -->
			<td>
				<?php
					echo JHTML::_('select.genericlist', $this->orderstatuses, 'order_status['.$order->order_id.']', '', 'value', 'text', $order->order_status, 'order_status'.$i);
					echo '<input type="hidden" name="current_order_status['.$order->order_id.']" value="'.$order->order_status.'" />';
					echo '<br />';
					echo JHTML::_('link', 'index.php', JText::_('ADD_COMMENT'), array('onClick' => 'jQuery(\'#order_comment_'.$order->order_id.'\').toggle().focus(); return false;"'));
					echo '<textarea style="display: none;" id="order_comment_'.$order->order_id.'" name="order_comment['.$order->order_id.']" value="" cols="40" rows="10"/></textarea>';
				?>
			</td>
			<!-- Update -->
			<td>
				<?php 
				echo '<input type="checkbox" class="inputbox" name="notify_customer['.$order->order_id.']" />'.JText::_('VM_ORDER_LIST_NOTIFY');
				echo '<br />';
				echo '<input type="checkbox" class="inputbox" name="include_comment['.$order->order_id.']" />'.JText::_('VM_ORDER_HISTORY_INCLUDE_COMMENT');
				?>
			</td>
			<!-- Total -->
			<td><?php echo $order->order_total; ?></td>
		</tr>
	<?php 
		$k = 1 - $k;
		$i++;
	}
}	
?>
</tbody>
<tfoot>
<tr>
	<td colspan="10">
		<?php echo $this->pagination->getListFooter(); ?>
	</td>
</tr>
</tfoot>
</table>
<!-- Hidden Fields -->
<input type="hidden" name="filter_order" value="<?php echo $this->lists['filter_order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['filter_order_Dir']; ?>" />
<input type="hidden" name="task" value="orders" />
<input type="hidden" name="option" value="com_virtuemart" />
<input type="hidden" name="pshop_mode" value="admin" />
<input type="hidden" name="page" value="product.product_list" />
<input type="hidden" name="view" value="orders" />
<input type="hidden" name="func" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
</form>
<?php AdminMenuHelper::endAdminArea(); ?>