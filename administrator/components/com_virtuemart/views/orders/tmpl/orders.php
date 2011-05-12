<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage
* @author
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
AdminMenuHelper::startAdminArea();

$j15 = VmConfig::isJ15();
/* Get the component name */
$option = JRequest::getWord('option');
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
    <div id="header">
	<div id="filterbox" style="float: left;">
	    <table>
		<tr>
		    <td align="left" width="100%">
			<?php echo JText::_('COM_VIRTUEMART_FILTER'); ?>:
			<input type="text" name="filter_orders" value="<?php echo JRequest::getVar('filter_orders', ''); ?>" />
			<button onclick="this.form.submit();"><?php echo JText::_('COM_VIRTUEMART_GO'); ?></button>
			<button onclick="document.adminForm.filter_orders.value='';"><?php echo JText::_('COM_VIRTUEMART_RESET'); ?></button>
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
		<th><?php echo JHTML::_('grid.sort', 'COM_VIRTUEMART_ORDER_LIST_ID', 'virtuemart_order_id', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
		<th><?php echo JHTML::_('grid.sort', 'COM_VIRTUEMART_ORDER_PRINT_NAME', 'virtuemart_order_id', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
		<th><?php echo JHTML::_('grid.sort', 'COM_VIRTUEMART_ORDER_PRINT_PAYMENT_LBL', 'virtuemart_order_id', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
		<th><?php echo JHTML::_('grid.sort', 'COM_VIRTUEMART_CHECK_OUT_THANK_YOU_PRINT_VIEW', 'virtuemart_order_id', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
		<th><?php echo JHTML::_('grid.sort', 'COM_VIRTUEMART_ORDER_LIST_CDATE', 'virtuemart_order_id', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
		<th><?php echo JHTML::_('grid.sort', 'COM_VIRTUEMART_ORDER_LIST_MDATE', 'virtuemart_order_id', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
		<th><?php echo JHTML::_('grid.sort', 'COM_VIRTUEMART_ORDER_LIST_STATUS', 'virtuemart_order_id', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
		<th><?php echo JHTML::_('grid.sort', 'COM_VIRTUEMART_ORDER_LIST_NOTIFY', 'virtuemart_order_id', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
		<th><?php echo JHTML::_('grid.sort', 'COM_VIRTUEMART_ORDER_LIST_TOTAL', 'virtuemart_order_id', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
	    </tr>
	</thead>
	<tbody>
	    <?php
	    if (count($this->orderslist) > 0) {
		$i = 0;
		$k = 0;
		$keyword = JRequest::getVar('keyword');
		foreach ($this->orderslist as $key => $order) {
		    $checked = JHTML::_('grid.id', $i , $order->virtuemart_order_id);
		    ?>
		    <tr class="<?php echo "row$k"; ?>">
		    <!-- Checkbox -->
		    <td><?php echo $checked; ?></td>
		    <!-- Order id -->
			<?php
			$link = 'index.php?option='.$option.'&view=orders&task=edit&virtuemart_order_id='.$order->virtuemart_order_id;
			?>
		<td><?php echo JHTML::_('link', JRoute::_($link), $order->virtuemart_order_id, array('title' => JText::_('COM_VIRTUEMART_EDIT').' '.$order->virtuemart_order_id)); ?></td>
		<!-- Name -->
		<td><?php echo $order->order_name; ?></td>
		<!-- Payment method -->
		<td><?php echo $order->payment_method; ?></td>
		<!-- Print view -->
			<?php
			/* Print view URL */
			$details_url = JURI::base()."?option=".$option."&view=orders&task=orderPrint&format=raw&virtuemart_order_id=".$order->virtuemart_order_id;
			$details_link = "&nbsp;<a href=\"javascript:void window.open('$details_url', 'win2', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');\">";
			$details_link .= JHTML::_('image.site', 'printButton.png', ($j15 ? '/images/M_images/' : '/images/system/'), null, null, JText::_('COM_VIRTUEMART_PRINT'), array('align' => 'center', 'height'=> '16',  'width' => '16', 'border' => '0')).'</a>';
			?>
		<td><?php echo $details_link; ?></td>
		<!-- Order date -->
		<td><?php echo $order->created_on //date('d-M-y H:i', $order->created_on); ?></td>
		<!-- Last modified -->
		<td><?php echo $order->modified_on //date('d-M-y H:i', $order->modified_on); ?></td>
		<!-- Status -->
		<td>
			    <?php
			    echo JHTML::_('select.genericlist', $this->orderstatuses, 'order_status['.$order->virtuemart_order_id.']', '', 'value', 'text', $order->order_status, 'order_status'.$i);
			    echo '<input type="hidden" name="current_order_status['.$order->virtuemart_order_id.']" value="'.$order->order_status.'" />';
			    echo '<br />';
			    echo JHTML::_('link', '#', JText::_('COM_VIRTUEMART_ADD_COMMENT'), array('class' => 'show_element[order_comment_'.$order->virtuemart_order_id.']'));
			    echo '<textarea class="element-hidden vm-absolute vm-showable" id="order_comment_'.$order->virtuemart_order_id.'" name="order_comment['.$order->virtuemart_order_id.']" value="" cols="40" rows="10"/></textarea>';
			    ?>
		</td>
		<!-- Update -->
		<td>
			    <?php
			    echo '<input type="checkbox" class="inputbox" name="notify_customer['.$order->virtuemart_order_id.']" />'.JText::_('COM_VIRTUEMART_ORDER_LIST_NOTIFY');
			    echo '<br />';
			    echo '&nbsp;&nbsp;&nbsp;<input type="checkbox" class="inputbox" name="include_comment['.$order->virtuemart_order_id.']" />'.JText::_('COM_VIRTUEMART_ORDER_HISTORY_INCLUDE_COMMENT');
			    echo '<br />';
			    echo '<input type="checkbox" class="inputbox" name="update_lines['.$order->virtuemart_order_id.']"  checked="checked" />'.JText::_('COM_VIRTUEMART_ORDER_UPDATE_LINESTATUS');
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
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="option" value="com_virtuemart" />
    <input type="hidden" name="view" value="orders" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
</form>
<?php AdminMenuHelper::endAdminArea(); ?>