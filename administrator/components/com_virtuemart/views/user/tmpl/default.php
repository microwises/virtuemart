<?php
/**
*
* User listing view
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
* @version $Id$
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

AdminMenuHelper::startAdminArea();

?>
<form action="<?php echo JRoute::_( 'index.php' );?>" method="post" name="adminForm">
	<div id="header">
	<div id="filterbox" style="float: left">
		<table>
			<tr>
				<td width="100%">
					<?php echo JText::_('COM_VIRTUEMART_FILTER'); ?>:
					<input type="text" name="search" id="search" value="<?php echo $this->lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
					<button onclick="this.form.submit();"><?php echo JText::_('COM_VIRTUEMART_GO'); ?></button>
					<button onclick="document.adminForm.search.value='';this.form.submit();"><?php echo JText::_('COM_VIRTUEMART_RESET'); ?></button>
				</td>
			</tr>
		</table>
	</div>
	<div id="resultscounter" style="float: right;"><?php echo $this->pagination->getResultsCounter();?></div>
	</div>
	<br clear="all"/>
	<div id="editcell">
		<table class="adminlist">
		<thead>
		<tr>
			<th width="10">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->userList); ?>);" />
			</th>
			<th width="10">
				<?php echo JText::_('COM_VIRTUEMART_#'); ?>
			</th>
			<th>
			<?php echo JHTML::_('grid.sort'
					, JText::_('COM_VIRTUEMART_USER_LIST_USERNAME')
					, 'username'
					, $this->lists['order_Dir']
					, $this->lists['order']); ?>
			</th>
			<th>
			<?php echo JHTML::_('grid.sort'
					, JText::_('COM_VIRTUEMART_USER_LIST_FULL_NAME')
					, 'name'
					, $this->lists['order_Dir']
					, $this->lists['order']); ?>
			</th>
			<th width="80">
			<?php echo JText::_('COM_VIRTUEMART_USER_LIST_VENDOR'); ?>
			</th>
			<th>
			<?php echo JText::_('COM_VIRTUEMART_USER_LIST_GROUP'); ?>
			</th>
			<th>
			<?php echo JHTML::_('grid.sort'
					, JText::_('COM_VIRTUEMART_SHOPPER_FORM_GROUP')
					, 'shopper_group_name'
					, $this->lists['order_Dir']
					, $this->lists['order']); ?>
			</th>
		</thead>
		<?php
		$k = 0;
		for ($i = 0, $n = count($this->userList); $i < $n; $i++) {
			$row =& $this->userList[$i];
			$checked = JHTML::_('grid.id', $i, $row->id);
			$editlink = JROUTE::_('index.php?option=com_virtuemart&view=user&task=edit&cid[]=' . $row->id);
			$is_vendor = $this->toggle($row->is_vendor, $i, 'toggle.user_is_vendor');
		?>
			<tr class="<?php echo "row$k"; ?>">
				<td>
					<?php echo $checked; ?>
				</td>
				<td>
					<?php echo $i; ?>
				</td>
				<td align="left">
					<a href="<?php echo $editlink; ?>"><?php echo $row->username; ?></a>
				</td>
				<td align="left">
					<?php echo $row->name; ?>
				</td>
				<td align="center">
					<?php echo $is_vendor; ?>
				</td>

				<td align="left">
					<?php echo $row->perms . ' / (' . $row->usertype . ')'; ?>
				</td>
				<td align="left">
					<?php echo $row->shopper_group_name; ?>
				</td>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		<tfoot>
			<tr>
				<td colspan="11">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
	</table>
</div>

	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="controller" value="user" />
	<input type="hidden" name="view" value="user" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>

<?php AdminMenuHelper::endAdminArea(); ?>
