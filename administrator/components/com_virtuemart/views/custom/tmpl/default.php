<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage
* @author Max Milbers, Roland?
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: default.php 2978 2011-04-06 14:21:19Z alatak $
*/

/**
 * @todo Edit link like: http://csvi/administrator/index3.php?page=product.file_form&product_id=1&file_id=7&option=com_virtuemart&no_menu=1
 */
AdminMenuHelper::startAdminArea();

/* Load some behaviors we need */
JHTML::_('behavior.modal');
JHTML::_('behavior.tooltip');
jimport('joomla.filesystem.file');

/* Get the component name */
$option = JRequest::getWord('option');

/* Load some variables */
$keyword = JRequest::getVar('keyword', null);
?>
<div id="header">
	<div style="float: left;">
	<?php
	if (JRequest::getInt('product_id', false)) echo JHTML::_('link', JRoute::_('index.php?view=custom&option='.$option), JText::_('COM_VIRTUEMART_PRODUCT_FILES_LIST_RETURN'));
	?>
	</div>
	<div style="float: left;">
		<form action="index.php" method="post" name="adminForm" id="adminForm"><?php echo $this->customs->customsSelect ?>
		<?php echo JText::_('COM_VIRTUEMART_PRODUCT_FILES_LIST_SEARCH_BY_NAME') ?>&nbsp;
			<input type="text" value="<?php echo $keyword; ?>" name="keyword" size="25" class="inputbox" />
			<input type="hidden" name="option" value="<?php echo $option; ?>" />
			<input type="hidden" name="view" value="custom" />
			
			<input class="button" type="submit" name="search" value="<?php echo JText::_('COM_VIRTUEMART_SEARCH_TITLE')?>" />
	</div>
</div>
<?php
$customs = $this->customs->items;
//$roles = $this->customlistsroles;
$pagination = $this->pagination;
?>
	<table class="adminlist">
	<thead>
	<tr>
		<th><input type="checkbox" name="toggle" value="" onclick="checkAll('<?php echo count($customs); ?>')" /></th>
		<th><?php echo JText::_('COM_VIRTUEMART_CATEGORY_FORM_PARENT'); ?></th>
		<th><?php echo JText::_('COM_VIRTUEMART_PRODUCT_FORM_TITLE'); ?></th>
		<th><?php echo JText::_('COM_VIRTUEMART_UPDATE_PATCH_DESCRIPTION'); ?></th>
		<th><?php echo JText::_('COM_VIRTUEMART_CUSTOM_FIELD_TYPE'); ?></th>
		<th><?php echo JText::_('COM_VIRTUEMART_CUSTOM_ADMIN_ONLY'); ?></th>
		<th><?php echo JText::_('COM_VIRTUEMART_CUSTOM_IS_HIDDEN'); ?></th>
		<th><?php echo JText::_('COM_VIRTUEMART_PUBLISHED'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	if (count($customs) > 0) {

		$i = 0;
		$k = 0;
		foreach ($customs as $key => $custom) {

			$checked = JHTML::_('grid.id', $i , $custom->custom_id);
			if (!is_null($custom->custom_id)) $enabled = JHTML::_('grid.enabled', $custom, $i );
			else $enabled = '';
			?>
			<tr>
				<!-- Checkbox -->
				<td><?php echo $checked; echo $custom->custom_id; ?></td>
				<?php 
				$link = "index.php?view=custom&keyword=".urlencode($keyword)."&custom_parent_id=".$custom->custom_parent_id."&option=".$option;
				?>
				<td><?php echo JHTML::_('link', JRoute::_($link), $custom->custom_parent_title, array('title' => JText::_('COM_VIRTUEMART_FILTER_BY').' '.$custom->custom_parent_title)); ?></td>
				
				<!-- Product name -->
				<?php 
				$link = "index.php?view=custom&task=edit&limitstart=".$pagination->limitstart."&keyword=".urlencode($keyword)."&custom_id=".$custom->custom_id."&option=".$option;
				?>
				<td><?php echo JHTML::_('link', JRoute::_($link), $custom->custom_title, array('title' => JText::_('COM_VIRTUEMART_EDIT').' '.$custom->custom_title)); ?></td>
				<td><?php echo $custom->custom_field_desc; ?></td>
				<td><?php echo $custom->field_type_display; ?></td>
				<td>
					<a href="javascript:void(0);" onclick="return listItemTask('cb<?php echo $i;?>','toggle_admin_only')" title="<?php echo ($custom->admin_only ) ? JText::_('COM_VIRTUEMART_YES') : JText::_('COM_VIRTUEMART_NO');?>">
					<img src="components/com_virtuemart/assets/images/icon_16/<?php echo ( $custom->admin_only  ? 'icon-16-checkin.png' : 'icon-16-bug.png' );?>" width="16" height="16" border="0" alt="<?php echo ( $custom->admin_only ) ? JText::_('COM_VIRTUEMART_YES') : JText::_('COM_VIRTUEMART_NO');?>" /></a></td>
				<td><a href="javascript:void(0);" onclick="return listItemTask('cb<?php echo $i;?>','toggle_is_hidden')" title="<?php echo ($custom->is_hidden ) ? JText::_('COM_VIRTUEMART_YES') : JText::_('COM_VIRTUEMART_NO');?>">
					<img src="components/com_virtuemart/assets/images/icon_16/<?php echo ( $custom->is_hidden  ? 'icon-16-checkin.png' : 'icon-16-bug.png' );?>" width="16" height="16" border="0" alt="<?php echo ( $custom->is_hidden ) ? JText::_('COM_VIRTUEMART_YES') : JText::_('COM_VIRTUEMART_NO');?>" /></a></td>
				<td><?php echo $enabled; ?></td>
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
	<td colspan="15">
		<?php echo $this->pagination->getListFooter(); ?>
	</td>
	</tr>
	</tfoot>
	</table>
<!-- Hidden Fields -->
<input type="hidden" name="task" value="" />
<?php if (JRequest::getInt('product_id', false)) { ?>
	<input type="hidden" name="product_id" value="<?php echo JRequest::getInt('product_id'); ?>" />
<?php } ?>
<input type="hidden" name="option" value="com_virtuemart" />
<input type="hidden" name="view" value="custom" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order" value="<?php //echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php //echo $this->lists['order_Dir']; ?>" />
<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
</form>
<?php AdminMenuHelper::endAdminArea(); ?>