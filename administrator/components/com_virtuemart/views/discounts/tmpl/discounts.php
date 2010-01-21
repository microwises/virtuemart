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
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<div id="header">
	<div id="filterbox" style="float: left;">
	<table>
	  <tr>
		 <td align="left" width="100%">
			<?php echo JText::_('Filter'); ?>:
			<input type="text" name="filter_discounts" value="<?php echo JRequest::getVar('filter_discounts', ''); ?>" />
			<button onclick="this.form.submit();"><?php echo JText::_('Go'); ?></button>
			<button onclick="document.adminForm.filter_discounts.value='';"><?php echo JText::_('Reset'); ?></button>
		 </td>
	  </tr>
	</table>
	</div>
	<div id="resultscounter" style="float: right;"><?php echo $this->pagination->getResultsCounter();?></div>
</div>
<br clear="all" />
<div style="text-align: left;">
	<table class="adminlist">
	<thead>
	<tr>
		<th><input type="checkbox" name="toggle" value="" onclick="checkAll('<?php echo count($this->discountslist); ?>')" /></th>
		<th><?php echo JHTML::_('grid.sort', 'VM_PRODUCT_DISCOUNT_AMOUNT', 'amount', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
		<th><?php echo JHTML::_('grid.sort', 'VM_PRODUCT_DISCOUNT_AMOUNTTYPE', 'is_percent', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
		<th><?php echo JHTML::_('grid.sort', 'VM_PRODUCT_DISCOUNT_STARTDATE', 'start_date', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
		<th><?php echo JHTML::_('grid.sort', 'VM_PRODUCT_DISCOUNT_ENDDATE', 'end_date', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	if (count($this->discountslist) > 0) {
		$i = 0;
		$k = 0;
		$keyword = JRequest::getVar('keyword');
		foreach ($this->discountslist as $key => $discount) {
			$checked = JHTML::_('grid.id', $i , $discount->discount_id);
			?>
			<tr class="<?php echo "row$k"; ?>">
				<!-- Checkbox -->
				<td><?php echo $checked; ?></td>
				<!-- Amount -->
				<?php $link = 'index.php?option='.$option.'&view=discounts&task=edit&cid[]='.$discount->discount_id; ?>
				<td><?php echo JHTML::_('link', $link, $discount->amount); ?></td>
				<!-- Type -->
				<td><?php $disc_type = $discount->is_percent == 1 ? JText::_('VM_PRODUCT_DISCOUNT_ISPERCENT') : JText::_('VM_PRODUCT_DISCOUNT_ISTOTAL'); echo $disc_type;?></td>
				<!-- Start date -->
				<td><?php echo strftime("%Y-%m-%d", $discount->start_date); ?></td>
				<!-- End date -->
				<td><?php echo strftime("%Y-%m-%d", $discount->end_date); ?></td>
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
		<td colspan="16">
			<?php echo $this->pagination->getListFooter(); ?>
		</td>
		</tr>
	</tfoot>
	</table>
</div>
<!-- Hidden Fields -->
<input type="hidden" name="filter_order" value="<?php echo $this->lists['filter_order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['filter_order_Dir']; ?>" />
<input type="hidden" name="task" value="discounts" />
<input type="hidden" name="option" value="com_virtuemart" />
<input type="hidden" name="pshop_mode" value="admin" />
<input type="hidden" name="view" value="discounts" />
<input type="hidden" name="func" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
</form>
<?php AdminMenuHelper::endAdminArea(); ?>