<?php
/**
*
* Lists all the categories in the shop
*
* @package	VirtueMart
* @subpackage Category
* @author RickG, jseros, RolandD
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

$nrows = count( $this->categories );

if( $this->pagination->limit < $nrows ){
	if( ($this->pagination->limitstart + $this->pagination->limit) < $nrows ) {
		$nrows = $this->pagination->limitstart + $this->pagination->limit;
	}
}
?>

<form action="index.php" method="post" name="adminForm">
	<div id="editcell">
		<table class="adminlist">
		<thead>
		<tr>
			<th width="20">
				<?php echo JText::_('COM_VIRTUEMART_#'); ?>
			</th>
			<th width="20">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->categories); ?>);" />
			</th>
			<th>
				<?php echo JHTML::_('grid.sort', 'COM_VIRTUEMART_CATEGORY_FORM_NAME', 'c.category_name', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?>
			</th>
			<th>
				<?php echo JHTML::_('grid.sort', 'COM_VIRTUEMART_CATEGORY_FORM_DESCRIPTION', 'c.category_description', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?>
			</th>
			<th width="11%">
				<?php echo JText::_('COM_VIRTUEMART_PRODUCTS_LBL'); ?>
			</th>
			<th width="5%">
				<?php echo JHTML::_('grid.sort', 'COM_VIRTUEMART_PRODUCT_LIST_PUBLISH', 'c.published', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?>
			</th>
			<!-- Commented out for future use
			<th width="5%">
				<?php echo JHTML::_('grid.sort', 'COM_VIRTUEMART_PRODUCT_LIST_SHARED', 'cx.category_shared', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?>
			</th>
			-->
			<th width="13%">
				<?php echo JHTML::_('grid.sort', 'ORDER', 'c.ordering', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?>
				<?php echo JHTML::_('grid.order', $this->categories, 'filesave.png', 'saveOrder' ); ?>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php
		$k = 0;
		$repeat = 0;

		for ($i = $this->pagination->limitstart; $i < $nrows; $i++) {

			if( !isset($this->rowList[$i])) $this->rowList[$i] = $i;
			if( !isset($this->depthList[$i])) $this->depthList[$i] = 0;

			$row = $this->categories[$this->rowList[$i]];

			$checked = JHTML::_('grid.id', $i, $row->category_id);
			$published = JHTML::_('grid.published', $row, $i);
			$editlink = JRoute::_('index.php?option=com_virtuemart&view=category&task=edit&cid[]=' . $row->category_id);
			$statelink	= JRoute::_('index.php?option=com_virtuemart&view=category&category_id=' . $row->category_id);
			$showProductsLink = JRoute::_('index.php?option=com_virtuemart&view=product&category_id=' . $row->category_id);

			$categoryLevel = '';
			$repeat = $this->depthList[$i] + 1;

			if($repeat > 1){
				$categoryLevel = str_repeat(".&nbsp;&nbsp;&nbsp;", $repeat - 1);
				$categoryLevel .= "<sup>|_</sup>&nbsp;";
			}
		?>
			<tr class="<?php echo "row".$k;?>">
				<td align="center">
					<?php echo ($i+1);?>
				</td>
				<td><?php echo $checked;?></td>
				<td align="left">
					<span class="categoryLevel"><?php echo $categoryLevel;?></span>
					<a href="<?php echo $editlink;?>"><?php echo $this->escape($row->category_name);?></a>
				</td>
				<td align="left">
					<?php echo $row->category_description; ?>
				</td>
				<td>
					<?php echo  $this->model->countProducts($row->category_id);//ShopFunctions::countProductsByCategory($row->category_id);?>
					&nbsp;<a href="<?php echo $showProductsLink; ?>">[ <?php echo JText::_('COM_VIRTUEMART_SHOW');?> ]</a>
				</td>
				<td align="center">
					<?php echo $published;?>
				</td>
				<!-- Commented out for future use
				<td align="center">
					<a href="#" onclick="return listItemTask('cb<?php echo $i;?>', 'toggleShared')" title="<?php echo ( $row->category_shared == 'Y' ) ?JText::_('JYES') : JText::_('JNO');?>">
						<img src="images/<?php echo ( $row->category_shared) ? 'tick.png' : 'publish_x.png';?>" width="16" height="16" border="0" alt="<?php echo ( $row->category_shared == 'Y' ) ? JText::_('JYES') : JText::_('JNO');?>" />
					</a>
				</td>
				-->
				<td align="center" class="order">
					<span><?php echo $this->pagination->orderUpIcon( $i, ($row->category_parent_id == 0 || $row->category_parent_id == @$this->categories[$this->rowList[$i - 1]]->category_parent_id), 'orderUp', 'Move Up'); ?></span>
					<span><?php echo $this->pagination->orderDownIcon( $i, $nrows, ($row->category_parent_id == 0 || $row->category_parent_id == @$this->categories[$this->rowList[$i + 1]]->category_parent_id), 'orderDown', 'Move Down'); ?></span>
					<input type="text" name="order[<?php echo $i?>]" id="order[<?php echo $i?>]" size="5" value="<?php echo $row->ordering; ?>" style="text-align: center" />
				</td>
			</tr>
		<?php
			$k = 1 - $k;
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
</div>

	<input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="controller" value="category" />
	<input type="hidden" name="view" value="category" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['filter_order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['filter_order_Dir']; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>


<?php AdminMenuHelper::endAdminArea(); ?>