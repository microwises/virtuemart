<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Paymentmethod
* @author Max Milbers
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
//if($virtuemart_vendor_id==1 || $perm->check( 'admin' )){

?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
	<div id="editcell">
		<table class="adminlist" cellspacing="0" cellpadding="0">
		<thead>
		<tr>
			<th>
				<?php echo JText::_('COM_VIRTUEMART_#'); ?>
			</th>
			<th width="10">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->payms); ?>);" />
			</th>
			<th width="60">
				<?php echo JText::_('COM_VIRTUEMART_PAYMENT_LIST_NAME'); ?>
			</th>
			<?php if($this->perms->check( 'admin' )){ ?>
			<th width="20">
				<?php echo JText::_('COM_VIRTUEMART_VENDOR');  ?>
			</th><?php }?>
			<th width="20">
				<?php echo JText::_('COM_VIRTUEMART_PAYMENT_ELEMENT'); ?>
			</th>
			<th width="20">
				<?php echo JText::_('COM_VIRTUEMART_PAYMENT_SHOPPERGROUPS'); ?>
			</th>
			<th width="20">
				<?php echo JText::_('COM_VIRTUEMART_LIST_ORDER'); ?>
			</th>
			<th width="10">
				<?php echo JText::_('COM_VIRTUEMART_PUBLISHED'); ?>
			</th>
			<?php if(Vmconfig::get('multix','none')!=='none'){ ?>
			<th width="10">
				<?php echo JText::_('COM_VIRTUEMART_CALC_SHARED'); ?>
			</th>
			<?php } ?>
		</tr>
		</thead>
		<?php
		$k = 0;

		for ($i=0, $n=count( $this->payms ); $i < $n; $i++) {

			$row = $this->payms[$i];
			$checked = JHTML::_('grid.id', $i, $row->virtuemart_paymentmethod_id);
			$published = JHTML::_('grid.published', $row, $i);
			$editlink = JROUTE::_('index.php?option=com_virtuemart&view=paymentmethod&task=edit&cid[]=' . $row->virtuemart_paymentmethod_id);
			?>
			<tr class="<?php echo "row".$k; ?>">
				<td width="10" align="right">
					<?php echo $row->virtuemart_paymentmethod_id; ?>
				</td>
				<td width="10">
					<?php echo $checked; ?>
				</td>
				<td align="left">
					<a href="<?php echo $editlink; ?>"><?php echo $row->payment_name; ?></a>
				</td>
				<?php if($this->perms->check( 'admin' )){?>
				<td align="left">
					<?php echo JText::_($row->virtuemart_vendor_id); ?>
				</td>
				<?php } ?>
				<td>
					<?php echo $row->payment_element; ?>
				</td>
				<td>
					<?php echo $row->paymShoppersList; ?>
				</td>
				<td>
					<?php echo $row->ordering; ?>
				</td>
				<td align="center">
					<?php echo $published; ?>
				</td>
				<?php if(Vmconfig::get('multix','none')!=='none'){ ?>
				<td align="center">
					<?php echo $row->shared; ?>
				</td>
				<?php } ?>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		<tfoot>
			<tr>
				<td colspan="21">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
	</table>
</div>

	<input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="controller" value="paymentmethod" />
	<input type="hidden" name="view" value="paymentmethod" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>


<?php AdminUIHelper::endAdminArea(); ?>