<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );

/**
*
* @version 
* @package VirtueMart
* @subpackage Report
* @copyright Copyright (C) VirtueMart Team - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/

AdminMenuHelper::startAdminArea(); 
/* Load some variables */
$search_date = JRequest::getVar('search_date', null); // Changed search by date
$now = getdate();
$nowstring = $now["hours"].":".substr('0'.$now["minutes"], -2).' '.$now["mday"].".".$now["mon"].".".$now["year"];
$search_order = JRequest::getVar('search_order', '>');
$search_type = JRequest::getVar('search_type', 'product');
$order_id = JRequest::getInt('order_id', false);

/*$nrows = count( $this->reports );

if( $this->pagination->limit < $nrows ){
	if( ($this->pagination->limitstart + $this->pagination->limit) < $nrows ) {
		$nrows = $this->pagination->limitstart + $this->pagination->limit;
	}
}
*/

?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<div id="header">
	<div id="filterbox" style="float: left">
		<table>
			<tr>
				<td align="left" width="100%">
				<?php echo JText::_('VM_FILTER') ?>:
					<select class="inputbox" id="order_id" name="order_id" onchange="document.adminForm.submit(); return false;">
						<option value=""><?php echo JText::_('SELECT_ORDER') ?></option>
						<?php echo $this->order_list; ?>
					</select>
					<?php echo JText::_('VM_ORDER_LIST_SEARCH_BY_DATE') ?>&nbsp;
						<input type="text" value="<?php echo JRequest::getVar('filter_order'); ?>" name="filter_order" size="25" />
					<?php 
						echo $this->lists['search_type'];
						echo $this->lists['search_order']; 
						echo JHTML::calendar( JRequest::getVar('search_date', $nowstring), 'search_date', 'search_date', '%H.%M %d.%m.%Y', 'size="20"');
					?>
					<button onclick="this.form.submit();"><?php echo JText::_('Go'); ?></button>
					<button onclick="document.adminForm.filter_product.value=''; document.adminForm.search_type.options[0].selected = true;"><?php echo JText::_('Reset'); ?></button>
				</td>
			</tr>
		</table>
	</div>
<div id="resultscounter" style="float: right;"><?php echo $this->pagination->getResultsCounter();?></div>
</div>
<br clear="all" />

    <div id="editcell">
	<table class="adminlist">
	    <thead>
		<tr>
		    <th><?php echo JHTML::_('grid.sort','VM_RB_DATE','order_date',$this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
		    <th><?php echo JHTML::_('grid.sort','VM_RB_ORDERS','order_id',$this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
		    <th><?php echo JHTML::_('grid.sort','VM_RB_TOTAL_ITEMS','order_total_items',$this->lists['filter_order_Dir'],$this->lists['filter_order']); ?></th>
		    <th><?php echo JHTML::_('grid.sort','VM_RB_REVENUE','order_revenue',$this->lists['filter_order_Dir'],$this->lists['filter_order']); ?></th>
		</tr>
	    </thead>
	    <tbody>
	    </tbody>
	    <tfoot>
		<tr>
		    <td colspan="10">
			<?php echo $this->pagination->getListFooter(); ?>
		    </td>
		</tr>
	    </tfoot>
	</table>
    </div>

    <input type="hidden" name="option" value="com_virtuemart" />
    <input type="hidden" name="controller" value="report" />
    <input type="hidden" name="view" value="report" />
    <input type="hidden" name="task" value="" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['filter_order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['filter_order_Dir']; ?>" />
	<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />    
</form>

<?php AdminMenuHelper::endAdminArea(); ?>