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
* @version $Id: edit.php 2233 2010-01-21 21:21:29Z SimonHodgkiss $
*/
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

AdminMenuHelper::startAdminArea(); 
?>

<form action="index.php" method="post" name="adminForm">


<div class="col50">
	<fieldset class="adminform">
	<legend><?php echo JText::_('Userfield Details'); ?></legend>
	<table class="admintable">

		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('VM_FIELDMANAGER_TYPE') ?>:
				</label>
			</td>
			<td>
				<?php echo $this->lists['type']; ?>
			</td>
		</tr>

		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('VM_FIELDMANAGER_NAME') ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="name" id="name" size="50" value="<?php echo $this->userField->name; ?>" <?php echo ($this->userfield->sys ? 'readonly="readonly"' : ''); ?> />
			</td>
		</tr>

		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('VM_FIELDMANAGER_TITLE') ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="title" id="title" size="50" value="<?php echo $this->userField->title; ?>" />
			</td>
		</tr>

		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('VM_USERFIELDS_DESCRIPTION') ?>:
				</label>
			</td>
			<td>
				<?php echo $this->editor->display('description',  $this->userField->description, '100%;', '250', '75', '20', array('image', 'pagebreak', 'readmore') ) ; ?>
			</td>
		</tr>

		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('VM_FIELDMANAGER_REQUIRED') ?>?:
				</label>
			</td>
			<td>
				<?php echo $this->lists['required']; ?>
			</td>
		</tr>

		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('VM_FIELDMANAGER_SHOW_ON_REGISTRATION') ?>?:
				</label>
			</td>
			<td>
				<?php echo $this->lists['registration']; ?>
			</td>
		</tr>

		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('VM_FIELDMANAGER_SHOW_ON_ACCOUNT') ?>?:
				</label>
			</td>
			<td>
				<?php echo $this->lists['account']; ?>
			</td>
		</tr>

		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('VM_FIELDMANAGER_SHOW_ON_SHIPPING') ?>?:
				</label>
			</td>
			<td>
				<?php echo $this->lists['shipping']; ?>
			</td>
		</tr>

		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('VM_USERFIELDS_READONLY') ?>?:
				</label>
			</td>
			<td>
				<?php echo $this->lists['readonly']; ?>
			</td>
		</tr>

		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('VM_FIELDMANAGER_PUBLISHED') ?>:
				</label>
			</td>
			<td>
				<?php echo $this->lists['published']; ?>
			</td>
		</tr>

		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('VM_USERFIELDS_SIZE') ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="size" id="size" size="5" value="<?php echo $this->userField->size; ?>" />
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
		</table>
	</fieldset>
</div>

	<input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="order_status_id" value="<?php echo $this->userField->fieldid; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="controller" value="userfields" />
</form>


<?php AdminMenuHelper::endAdminArea(); ?>
