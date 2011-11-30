<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Userfields
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

AdminUIHelper::startAdminArea();

$j15 = VmConfig::isJ15();

?>

<form action="<?php echo JRoute::_( 'index.php' );?>" method="post" name="adminForm" id="adminForm">
	<div id="header">
	<div id="filterbox">
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
	<div id="resultscounter"><?php echo $this->pagination->getResultsCounter();?></div>

	</div>

	<div id="editcell">
		<table class="adminlist jgrid">
		<thead>
		<tr>
			<th width="10">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->userfieldsList); ?>);" />
			</th>
			<th width="10">
				<?php echo JText::_('COM_VIRTUEMART_#'); ?>
			</th>
			<th>
			<?php echo JHTML::_('grid.sort'
					, JText::_('COM_VIRTUEMART_FIELDMANAGER_NAME')
					, 'name'
					, $this->lists['order_Dir']
					, $this->lists['order']); ?>
			</th>
			<th>
			<?php echo JText::_('COM_VIRTUEMART_FIELDMANAGER_TITLE'); ?>
			</th>
			<th>
			<?php echo JHTML::_('grid.sort'
					, JText::_('COM_VIRTUEMART_FIELDMANAGER_TYPE')
					, 'type'
					, $this->lists['order_Dir']
					, $this->lists['order']); ?>
			</th>
			<th width="20">
				<?php echo JText::_('COM_VIRTUEMART_FIELDMANAGER_REQUIRED'); ?>
			</th>
			<th width="20">
				<?php echo JText::_('COM_VIRTUEMART_PUBLISHED'); ?>
			</th>
			<th width="20">
				<?php echo JText::_('COM_VIRTUEMART_FIELDMANAGER_SHOW_ON_REGISTRATION'); ?>
			</th>
			<th width="20">
				<?php echo JText::_('COM_VIRTUEMART_FIELDMANAGER_SHOW_ON_SHIPPING'); ?>
			</th>
			<th width="20">
				<?php echo JText::_('COM_VIRTUEMART_FIELDMANAGER_SHOW_ON_ACCOUNT'); ?>
			</th>
			<th>
			<?php echo JHTML::_('grid.sort'
					, JText::_('COM_VIRTUEMART_FIELDMANAGER_REORDER')
					, 'ordering'
					, $this->lists['order_Dir']
					, $this->lists['order']); ?>
			<?php echo JHTML::_('grid.order',  $this->userfieldsList ); ?>
			</th>
		</tr>
		</thead>
		<?php
		$k = 0;
		for ($i = 0, $n = count($this->userfieldsList); $i < $n; $i++) {
			$row = $this->userfieldsList[$i];
			vmdebug('my rows',$row);
			$coreField = (in_array($row->name, $this->lists['coreFields']));
			$image = ($j15) ? 'checked_out.png' : 'admin/checked_out.png';
			$image = JHtml::_('image.administrator', $image, '/images/', null, null, JText::_('COM_VIRTUEMART_FIELDMANAGER_COREFIELD'));
			$checked = ($coreField) ?
				'<span class="hasTip" title="'. JText::_('COM_VIRTUEMART_FIELDMANAGER_COREFIELD').'">'. $image .'</span>' :
				JHTML::_('grid.id', $i, $row->virtuemart_userfield_id);
			$editlink = JROUTE::_('index.php?option=com_virtuemart&view=userfields&task=edit&virtuemart_userfield_id=' . $row->virtuemart_userfield_id);
			$required = $this->toggle($row->required, $i, 'toggle.required', $coreField);
//			$published = JHTML::_('grid.published', $row, $i);
			$published = $this->toggle($row->published, $i, 'toggle.published', $coreField);
			$registration = $this->toggle($row->registration, $i, 'toggle.registration', $coreField);
			$shipping = $this->toggle($row->shipment, $i, 'toggle.shipping', $coreField);
			$account = $this->toggle($row->account, $i, 'toggle.account', $coreField);
			$ordering = ($this->lists['order'] == 'ordering');
			$disabled = ($ordering ?  '' : 'disabled="disabled"');
		?>
			<tr class="row<?php echo $k ; ?>">
				<td width="10">
					<?php echo $checked; ?>
				</td>
				<td width="10">
					<?php echo JText::_($row->virtuemart_userfield_id); ?>
				</td>
				<td align="left">
					<a href="<?php echo $editlink; ?>"><?php echo JText::_($row->name); ?></a>
				</td>
				<td align="left">
					<?php echo JText::_($row->title); ?>
				</td>
				<td align="left">
					<?php echo JText::_($row->type); ?>
				</td>
				<td align="center">
					<?php echo $required; ?>
				</td>
				<td align="center">
					<?php echo $published; ?>
				</td>
				<td align="center">
					<?php echo $registration; ?>
				</td>
				<td align="center">
					<?php echo $shipping; ?>
				</td>
				<td align="center">
					<?php echo $account; ?>
				</td>
				<td class="order">
					<span><?php echo $this->pagination->orderUpIcon( $i, true, 'orderup', 'Move Up', $ordering ); ?></span>
					<span><?php echo $this->pagination->orderDownIcon( $i, $n, true, 'orderdown', 'Move Down', $ordering ); ?></span>
					<input type="text" name="order[]" size="5" value="<?php echo $row->ordering;?>" <?php echo $disabled ?> class="text_area" style="text-align: center" />
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
	<input type="hidden" name="view" value="userfields" />
	<?php echo VmHTML::HiddenEdit() ?>
</form>

<?php AdminUIHelper::endAdminArea(); ?>
