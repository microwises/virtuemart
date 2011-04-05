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
			<?php echo JText::_('VM_FILTER'); ?>:
			<input type="text" name="filter_inventory" value="<?php echo JRequest::getVar('filter_inventory', ''); ?>" />
			<button onclick="this.form.submit();"><?php echo JText::_('VM_GO'); ?></button>
			<button onclick="document.adminForm.filter_inventory.value='';"><?php echo JText::_('VM_RESET'); ?></button>
		 </td>
		 <td>
		 	<?php echo $this->lists['stockfilter']; ?>
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
		<th><input type="checkbox" name="toggle" value="" onclick="checkAll('<?php echo count($this->inventorylist); ?>')" /></th>
		<th><?php echo JHTML::_('grid.sort', 'VM_PRODUCT_LIST_NAME', 'product_name', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
		<th><?php echo JHTML::_('grid.sort', 'VM_PRODUCT_LIST_SKU', 'product_sku', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
		<th><?php echo JHTML::_('grid.sort', 'VM_PRODUCT_INVENTORY_STOCK', 'product_in_stock', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
		<th><?php echo JHTML::_('grid.sort', 'VM_PRODUCT_INVENTORY_PRICE', 'product_price', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
		<th><?php echo JHTML::_('grid.sort', 'VM_PRODUCT_INVENTORY_WEIGHT', 'product_weight', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
		<th><?php echo JHTML::_('grid.sort', 'CMN_PUBLISHED', 'published', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	if (count($this->inventorylist) > 0) {
		$i = 0;
		$k = 0;
		$keyword = JRequest::getVar('keyword');
		foreach ($this->inventorylist as $key => $product) {
			$checked = JHTML::_('grid.id', $i , $product->product_id);
			$published = JHTML::_('grid.published', $product, $i );
			?>
			<tr class="<?php echo "row$k"; ?>">
				<!-- Checkbox -->
				<td><?php echo $checked; ?></td>
				<!-- Product name -->
				<?php
				$link = 'index.php?option=com_virtuemart&view=product&task=edit&product_id='.$product->product_id.'&product_parent_id='.$product->product_parent_id;
				?>
				<td><?php echo JHTML::_('link', JRoute::_($link), $product->product_name, array('title' => JText::_('EDIT').' '.$product->product_name)); ?></td>
				<!-- Product SKU -->
				<td><?php echo $product->product_sku; ?></td>
				<!-- Product in stock -->
				<td><?php echo $product->product_in_stock; ?></td>
				<!-- Product price -->
				<td><?php echo $product->product_price_display; ?></td>
				<!-- Product weight -->
				<td><?php echo $product->product_weight; ?></td>
				<!-- Published -->
				<td><?php echo $published; ?></td>
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
<input type="hidden" name="task" value="inventory" />
<input type="hidden" name="option" value="com_virtuemart" />
<input type="hidden" name="pshop_mode" value="admin" />
<input type="hidden" name="page" value="product.product_list" />
<input type="hidden" name="view" value="inventory" />
<input type="hidden" name="func" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
</form>
<?php AdminMenuHelper::endAdminArea(); ?>