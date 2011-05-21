<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage ShippingCarrier
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


<div class="col50">
	<fieldset class="adminform">
	<legend><?php echo JText::_('COM_VIRTUEMART_SHIPPING_CARRIER'); ?></legend>
	<table class="admintable">
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('COM_VIRTUEMART_CARRIER_FORM_NAME'); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="shipping_carrier_name" id="shipping_carrier_name" size="50" value="<?php echo $this->carrier->shipping_carrier_name; ?>" />
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('COM_VIRTUEMART_CARRIER_FORM_LIST_ORDER'); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="shipping_carrier_list_order" id="shipping_carrier_list_order" size="3" value="<?php echo $this->carrier->shipping_carrier_list_order; ?>" />
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('COM_VIRTUEMART_CARRIER_CLASS_NAME'); ?>:
				</label>
			</td>
			<td>
				<?php echo $this->pluginList; ?>
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('COM_VIRTUEMART_VENDOR'); ?>:
				</label>
			</td>
			<td>
				<?php echo $this->vendorList; ?>
			</td>
		</tr>
	</table>
	</fieldset>
</div>

	<input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="virtuemart_shippingcarrier_id" value="<?php echo $this->carrier->virtuemart_shippingcarrier_id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="controller" value="shippingcarrier" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>


<?php AdminMenuHelper::endAdminArea(); ?>