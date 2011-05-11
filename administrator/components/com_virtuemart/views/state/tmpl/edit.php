<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage State
* @author RickG, Max Milbers
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

<div class="col50">
	<fieldset class="adminform">
<?php /*	<legend><?php echo JText::_('COM_VIRTUEMART_STATE_DETAILS'); ?></legend> */?>
	<legend><?php echo JHTML::_('link','index.php?option=com_virtuemart&controller=state&country_id='.$this->country_id,JText::sprintf('COM_VIRTUEMART_STATE_COUNTRY',$this->country_name)); ?></legend>
	<table class="admintable">
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('COM_VIRTUEMART_STATE_NAME'); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="state_name" id="state_name" size="50" value="<?php echo $this->state->state_name; ?>" />
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('COM_VIRTUEMART_PUBLISHED'); ?>:
				</label>
			</td>
			<td>

				<?php echo JHTML::_('select.booleanlist',  'enabled', 'class="inputbox"', $this->state->enabled); ?>

			</td>
		</tr>
		<tr>
		<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('COM_VIRTUEMART_SHIPPING_ZONE'); ?>:
				</label>
			</td>
			<td>
				<?php echo JHTML::_('Select.genericlist', $this->shippingZones, 'zone_id', '', 'zone_id', 'zone_name', $this->state->zone_id); ?>
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('COM_VIRTUEMART_STATE_3_CODE'); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="state_3_code" id="state_3_code" size="10" value="<?php echo $this->state->state_3_code; ?>" />
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('COM_VIRTUEMART_STATE_2_CODE'); ?>:
				</label>
			</td>
			<td>
				<?php /* echo JHTML::_('Select.radiolist', $this->shippingZones, 'zone_id', '', 'zone_id', 'zone_name', $this->country->zone_id);*/ ?>
				<input class="inputbox" type="text" name="state_2_code" id="state_2_code" size="10" value="<?php echo $this->state->state_2_code; ?>" />
			</td>
		</tr>
	</table>
	</fieldset>
</div>

	<input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="country_id" value="<?php echo $this->country_id; ?>" />
	<input type="hidden" name="state_id" value="<?php echo $this->state->state_id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="controller" value="state" />
</form>


<?php AdminMenuHelper::endAdminArea(); ?>