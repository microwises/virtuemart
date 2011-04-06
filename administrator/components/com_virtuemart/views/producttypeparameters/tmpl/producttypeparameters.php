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
			<?php echo JText::_('COM_VIRTUEMART_FILTER'); ?>:
			<input type="text" name="filter_producttypeparameters" value="<?php echo JRequest::getVar('filter_producttypeparameters', ''); ?>" />
			<button onclick="this.form.submit();"><?php echo JText::_('COM_VIRTUEMART_GO'); ?></button>
			<button onclick="document.adminForm.filter_producttypes.value='';"><?php echo JText::_('COM_VIRTUEMART_RESET'); ?></button>
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
		<th>#</th>
		<th><input type="checkbox" name="toggle" value="" onclick="checkAll('<?php echo count($this->producttypeparameterslist); ?>')" /></th>
		<th><?php echo JHTML::_('grid.sort', 'COM_VIRTUEMART_PRODUCT_TYPE_PARAMETER_FORM_LABEL', 'amount', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
		<th><?php echo JHTML::_('grid.sort', 'COM_VIRTUEMART_PRODUCT_TYPE_PARAMETER_FORM_NAME', 'is_percent', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
		<th><?php echo JHTML::_('grid.sort', 'COM_VIRTUEMART_PRODUCT_TYPE_FORM_DESCRIPTION', 'start_date', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
		<th><?php echo JHTML::_('grid.sort', 'COM_VIRTUEMART_MODULE_LIST_ORDER', 'list_order', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	if (count($this->producttypeparameterslist) > 0) {
		$i = 0;
		$k = 0;
		$keyword = JRequest::getVar('keyword');
		foreach ($this->producttypeparameterslist as $key => $producttypeparameter) {
			$checked = JHTML::_('grid.id', $i, $producttypeparameter->parameter_name);
			?>
			<tr class="<?php echo "row$k"; ?>">
				<!-- Row number -->
				<td><?php echo $i + 1 + $this->pagination->limitstart;?></td>
				<!-- Checkbox -->
				<td><?php echo $checked; ?></td>
				<!-- Product type parameter label -->
				<?php $link = 'index.php?option='.$option.'&view=producttypeparameters&task=edit&&product_type_id='.JRequest::getInt('product_type_id').'&cid[]='.$producttypeparameter->product_type_id; ?>
				<td><?php echo JHTML::_('link', $link, $producttypeparameter->parameter_label); ?></td>
				<!-- Product type parameter name -->
				<td><?php echo $producttypeparameter->parameter_name; ?></td>
				<!-- Description -->
				<td><?php echo $producttypeparameter->parameter_description; ?></td>
				<!-- List order -->
				<td><?php echo $producttypeparameter->parameter_list_order; ?></td>
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
<input type="hidden" name="task" value="producttypeparameters" />
<input type="hidden" name="option" value="com_virtuemart" />
<input type="hidden" name="pshop_mode" value="admin" />
<input type="hidden" name="view" value="producttypeparameters" />
<input type="hidden" name="func" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="product_type_id" value="<?php echo JRequest::getInt('product_type_id'); ?>" />
<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
</form>
<?php AdminMenuHelper::endAdminArea(); ?>