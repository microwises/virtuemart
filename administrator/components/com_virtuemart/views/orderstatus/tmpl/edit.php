<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage OrderStatus
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
AdminUIHelper::imitateTabs('start','COM_VIRTUEMART_ORDERSTATUS_DETAILS');
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">


<div class="col50">
	<fieldset class="adminform">
	<legend><?php echo JText::_('COM_VIRTUEMART_ORDERSTATUS_DETAILS'); ?></legend>
	<table class="admintable">
		<tr>
			<td width="110" class="key">
				<label for="order_status_name">
					<?php echo JText::_('COM_VIRTUEMART_ORDER_STATUS_NAME'); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="order_status_name" id="order_status_name" size="50" value="<?php echo $this->orderStatus->order_status_name; ?>" />
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="order_status_code">
                                     <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ORDER_STATUS_CODE_TIP'); ?>">
					<?php echo JText::_('COM_VIRTUEMART_ORDER_STATUS_CODE'); ?>:
                                     </span>
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="order_status_code" id="order_status_code" size="3" value="<?php echo $this->orderStatus->order_status_code; ?>" />
			</td>
		</tr>

		<tr>
			<td width="110" class="key">
				<label for="order_status_description">
					<?php echo JText::_('COM_VIRTUEMART_DESCRIPTION'); ?>:
				</label>
			</td>
			<td>
				<?php echo $this->editor->display('order_status_description',  $this->orderStatus->order_status_description, '100%;', '250', '75', '20', array('image', 'pagebreak', 'readmore') ) ; ?>
			</td>
		</tr>

		<tr>
			<td width="110" class="key">
				<label for="vendor">
					<?php echo JText::_('COM_VIRTUEMART_VENDOR'); ?>:
				</label>
			</td>
			<td>
				<?php echo $this->lists['vendors'];?>
			</td>
		</tr>

		<tr>
			<td width="110" class="key">
					<?php echo JText::_('COM_VIRTUEMART_ORDERING'); ?>:
			</td>
			<td>
				<?php echo $this->ordering; ?>
			</td>
		</tr>
	</table>
	</fieldset>
</div>

	<input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="virtuemart_orderstate_id" value="<?php echo $this->orderStatus->virtuemart_orderstate_id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="controller" value="orderstatus" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>


<?php 
AdminUIHelper::imitateTabs('end');
AdminUIHelper::endAdminArea(); ?>
