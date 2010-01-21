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

AdminMenuHelper::startAdminArea(); 
?>

<form action="index.php" method="post" name="adminForm">


<div class="col50">
	<fieldset class="adminform">
	<legend><?php echo JText::_('Order status Details'); ?></legend>
	<table class="admintable">
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('VM_ORDER_STATUS_LIST_NAME'); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="order_status_name" id="order_status_name" size="50" value="<?php echo $this->orderStatus->order_status_name; ?>" />
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('VM_ORDER_STATUS_LIST_CODE'); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="order_status_code" id="order_status_code" size="3" value="<?php echo $this->orderStatus->order_status_code; ?>" />
			</td>
		</tr>

		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('VM_MANUFACTURER_FORM_DESCRIPTION'); ?>:
				</label>
			</td>
			<td>
				<?php echo $this->editor->display('order_status_description',  $this->orderStatus->order_status_description, '100%;', '250', '75', '20', array('image', 'pagebreak', 'readmore') ) ; ?>
			</td>
		</tr>

		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('VM_PRODUCT_FORM_VENDOR'); ?>:
				</label>
			</td>
			<td>
				<?php echo $this->lists['vendors'];?>
			</td>
		</tr>

		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('VM_ORDER_STATUS_FORM_LIST_ORDER'); ?>:
				</label>
			</td>
			<td>
				<?php echo $this->ordering; ?>
			</td>
		</tr>
	</table>
	</fieldset>
</div>

	<input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="order_status_id" value="<?php echo $this->orderStatus->order_status_id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="controller" value="orderstatus" />
</form>


<?php AdminMenuHelper::endAdminArea(); ?>
