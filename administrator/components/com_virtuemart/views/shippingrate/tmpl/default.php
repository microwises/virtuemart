<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage ShippingRate
* @author RickG
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

<form action="index.php" method="post" name="adminForm">
	<div id="editcell">
		<table class="adminlist">
		<thead>
		<tr>
			<th width="10">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->shippingRates); ?>);" />
			</th>
			<th>
				<?php echo JText::_('COM_VIRTUEMART_SHIPPING_RATE_LIST_CARRIER_LBL'); ?>
			</th>
			<th>
				<?php echo JText::_('COM_VIRTUEMART_SHIPPING_RATE_LIST_RATE_NAME'); ?>
			</th>
			<th>
				<?php echo JText::_('COM_VIRTUEMART_RATE_FORM_VALUE'); ?>
			</th>
			<th>
				<?php echo JText::_('COM_VIRTUEMART_RATE_FORM_PACKAGE_FEE'); ?>
			</th>
			<th>
				<?php echo JText::_('COM_VIRTUEMART_SHIPPING_RATE_LIST_RATE_WSTART'); ?>
			</th>
			<th>
				<?php echo JText::_('COM_VIRTUEMART_SHIPPING_RATE_LIST_RATE_WEND'); ?>
			</th>
		</tr>
		</thead>
		<?php
		$k = 0;
		for ($i=0, $n=count( $this->shippingRates ); $i < $n; $i++) {
			$row =& $this->shippingRates[$i];
			/**
			 * @todo Add to database layout published column
			 */
			$row->published = 1;
			$checked = JHTML::_('grid.id', $i, $row->virtuemart_shippingrate_id);
			$editlink = JROUTE::_('index.php?option=com_virtuemart&view=shippingrate&task=edit&cid[]=' . $row->virtuemart_shippingrate_id);
			?>
			<tr class="<?php echo "row$k"; ?>">
				<td width="10">
					<?php echo $checked; ?>
				</td>
				<td align="left">
					<?php echo $row->shipping_carrier_name; ?>
				</td>
				<td align="left">
					<?php echo JHTML::_('link', $editlink, $row->shipping_rate_name); ?>
				</td>
				<td align="left">
					<?php echo $row->shipping_rate_value; ?>
				</td>
				<td align="left">
					<?php echo $row->shipping_rate_package_fee; ?>
				</td>
				<td align="left">
					<?php echo $row->shipping_rate_weight_start; ?>
				</td>
				<td align="left">
					<?php echo $row->shipping_rate_weight_end; ?>
				</td>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
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
	<input type="hidden" name="controller" value="shippingrate" />
	<input type="hidden" name="view" value="shippingrate" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
</form>



<?php AdminMenuHelper::endAdminArea(); ?>