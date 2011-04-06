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

/* Get the component name */
$option = JRequest::getWord('option');
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<div id="header">
<div id="filterbox" style="float: left">
	<table>
		<tr>
			<td align="left" width="100%">
			<?php echo JText::_('COM_VIRTUEMART_FILTER') ?>:
				<input type="text" value="<?php echo JRequest::getVar('filter_attributes'); ?>" name="filter_attributes" size="25" />
				<button onclick="this.form.submit();"><?php echo JText::_('COM_VIRTUEMART_GO'); ?></button>
				<button onclick="document.adminForm.filter_attributes.value='';"><?php echo JText::_('COM_VIRTUEMART_RESET'); ?></button>
			</td>
		</tr>
	</table>
	</div>
	<div id="resultscounter" style="float: right;"><?php echo $this->pagination->getResultsCounter();?></div>
</div>
<br clear="all" />
<div style="text-align: left;">
<?php 
$attributeslist = $this->attributeslist;
$pagination = $this->pagination;
?>
	<table class="adminlist">
	<thead>
	<tr>
		<th><input type="checkbox" name="toggle" value="" onclick="checkAll('<?php echo count($attributeslist); ?>')" /></th>
		<th><?php echo JHTML::_('grid.sort', 'VM_ATTRIBUTE_LIST_NAME', 'a.attribute_name', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
		<th><?php echo JHTML::_('grid.sort', 'VM_ATTRIBUTE_LIST_ORDER', 'a.attribute_list', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
		<th><?php echo JHTML::_('grid.sort', 'PRODUCT_NAME', 'p.product_name', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	if (count($attributeslist) > 0) {
		$i = 0;
		$k = 0;
		foreach ($attributeslist as $key => $attribute) {
			$checked = JHTML::_('grid.id', $i , $attribute->attribute_sku_id);
			?>
			<tr class="<?php echo "row$k"; ?>">
				<!-- Checkbox -->
				<td><?php echo $checked; ?></td>
				<!-- Attribute name -->
				<?php 
				$link = 'index.php?option='.$option.'&view=attributes&task=edit&attribute_sku_id='.$attribute->attribute_sku_id;
				?>
				<td><?php echo JHTML::_('link', JRoute::_($link), $attribute->attribute_name, array('title' => JText::_('COM_VIRTUEMART_EDIT').' '.$attribute->attribute_name)); ?></td>
				<!-- List order -->
				<td><?php echo $attribute->attribute_list; ?></td>
				<!-- Product name -->
				<td><?php echo $attribute->product_name; ?></td>
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
			<?php echo $pagination->getListFooter(); ?>
		</td>
		</tr>
	</tfoot>
	</table>
</div>
<!-- Hidden Fields -->
<input type="hidden" name="option" value="com_virtuemart" />
<input type="hidden" name="task" value="attributes" />
<input type="hidden" name="view" value="attributes" />
<input type="hidden" name="pshop_mode" value="admin" />
<input type="hidden" name="page" value="product.product_list" />
<input type="hidden" name="func" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['filter_order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['filter_order_Dir']; ?>" />
<input type="hidden" name="product_id" value="<?php echo JRequest::getInt('product_id', 0); ?>" />
<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
</form>
<?php AdminMenuHelper::endAdminArea(); ?> 