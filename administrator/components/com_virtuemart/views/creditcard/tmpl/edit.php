<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage CreditCard
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
	<legend><?php echo JText::_('COM_VIRTUEMART_CREDIT_CARD_DETAILS'); ?></legend>
	<table class="admintable">
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('COM_VIRTUEMART_CREDIT_CARD_NAME'); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="creditcard_name" id="creditcard_name" size="50" value="<?php echo $this->creditcard->creditcard_name; ?>" />
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('COM_VIRTUEMART_PUBLISHED'); ?>:
				</label>
			</td>
			<td>
				<fieldset class="radio">
				<?php echo JHTML::_('select.booleanlist',  'enabled', 'class="inputbox"', $this->creditcard->enabled); ?>
				</fieldset>
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('COM_VIRTUEMART_VENDOR_ID'); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="vendor_id" id="vendor_id" size="50" value="<?php echo $this->creditcard->vendor_id; ?>" />
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('COM_VIRTUEMART_CREDIT_CARD_CODE'); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="creditcard_code" id="creditcard_code" size="10" value="<?php echo $this->creditcard->creditcard_code; ?>" />
			</td>
		</tr>
	</table>
	</fieldset>
</div>

	<input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="creditcard_id" value="<?php echo $this->creditcard->creditcard_id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="controller" value="creditcard" />
</form>


<?php AdminMenuHelper::endAdminArea(); ?>