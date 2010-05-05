<?php
/**
*
* Modify user form view, User info
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
* @version $Id: edit.php 2302 2010-02-07 19:57:37Z rolandd $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access'); ?>

<script language="javascript" type="text/javascript">
function gotocontact(id) {
	var form = document.adminForm;
	form.target = "_parent";
	form.contact_id.value = id;
	form.option.value = 'com_users';
	submitform('contact');
}
</script>


<fieldset class="adminform">
	<legend>
		<?php echo JText::_('VM_USER_FORM_LEGEND_USERDETAILS'); ?>
	</legend>
	<table class="admintable" cellspacing="1">

		<tr>
			<td width="150" class="key">
				<label for="name">
					<?php echo JText::_('VM_USER_FORM_NAME'); ?>
				</label>
			</td>
			<td>
				<input type="text" name="name" id="name" class="inputbox" size="40" value="<?php echo $this->userDetails->JUser->get('name'); ?>" />
			</td>
		</tr>

		<tr>
			<td class="key">
				<label for="email">
					<?php echo JText::_('VM_USER_FORM_EMAIL'); ?>
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="email" id="email" size="40" value="<?php echo $this->userDetails->JUser->get('email'); ?>" />
			</td>
		</tr>

		<tr>
			<td class="key">
				<label for="password">
					<?php echo JText::_('VM_USER_FORM_NEWPASSWORD'); ?>
				</label>
			</td>
			<td>
				<input class="inputbox" type="password" name="password" id="password" size="40" value=""/>
			</td>
		</tr>

		<tr>
			<td class="key">
				<label for="password2">
					<?php echo JText::_('VM_USER_FORM_VERIFYPASSWORD'); ?>
				</label>
			</td>
			<td>
				<input class="inputbox" type="password" name="password2" id="password2" size="40" value=""/>
			</td>
		</tr>
	</table>
</fieldset>

<fieldset class="adminform">
	<legend>
		<?php echo JText::_('VM_USER_FORM_LEGEND_PARAMETERS'); ?>
		</legend>
	<table class="admintable" cellspacing="1">
		<tr>
			<td>
			<?php
				if (is_callable(array($this->lists['params'], 'render'))) {
					echo $this->lists['params']->render('params');
				}
			?>
			</td>
		</tr>
	</table>
</fieldset>

<input type="hidden" name="user_id" value="<?php echo $this->userDetails->JUser->get('id'); ?>" />
<input type="hidden" name="cid[]" value="<?php echo $this->userDetails->JUser->get('id'); ?>" />
<input type="hidden" name="contact_id" value="" />

