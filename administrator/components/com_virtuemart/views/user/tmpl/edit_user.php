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
* @version $Id$
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


<fieldset>
	<legend>
		<?php echo JText::_('COM_VIRTUEMART_USER_FORM_LEGEND_USERDETAILS'); ?>
	</legend>
	<table class="admintable" cellspacing="1">

		<tr>
			<td width="150" class="key">
				<label for="name">
					<?php echo JText::_('COM_VIRTUEMART_USER_DISPLAYED_NAME'); ?>
				</label>
			</td>
			<td>
				<input type="text" name="name" id="name" class="inputbox" size="40" value="<?php echo $this->userDetails->JUser->get('name'); ?>" />
			</td>
		</tr>

		<tr>
			<td class="key">
				<label for="username">
					<?php echo JText::_('COM_VIRTUEMART_USERNAME'); ?>
				</label>
			</td>
			<td>
				<input type="text" name="username" id="username" class="inputbox" size="40" value="<?php echo $this->userDetails->JUser->get('username'); ?>" autocomplete="off" />
			</td>
		</tr>

		<tr>
			<td class="key">
				<label for="email">
					<?php echo JText::_('COM_VIRTUEMART_EMAIL'); ?>
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="email" id="email" size="40" value="<?php echo $this->userDetails->JUser->get('email'); ?>" />
			</td>
		</tr>

		<tr>
			<td class="key">
				<label for="password">
					<?php echo JText::_('COM_VIRTUEMART_USER_FORM_NEWPASSWORD'); ?>
				</label>
			</td>
			<td>
				<input class="inputbox" type="password" name="password" id="password" size="40" value=""/>
			</td>
		</tr>

		<tr>
			<td class="key">
				<label for="password2">
					<?php echo JText::_('COM_VIRTUEMART_USER_FORM_VERIFYPASSWORD'); ?>
				</label>
			</td>
			<td>
				<input class="inputbox" type="password" name="password2" id="password2" size="40" value=""/>
			</td>
		</tr>

		<tr>
			<td valign="top" class="key">
				<label for="gid">
					<?php echo JText::_('COM_VIRTUEMART_USER_FORM_GROUP'); ?>
				</label>
			</td>
			<td>
				<?php echo $this->lists['gid']; ?>
			</td>
		</tr>

		<?php if ($this->lists['canBlock']) : ?>
		<tr>
			<td class="key">
				<?php echo JText::_('COM_VIRTUEMART_USER_FORM_BLOCKUSER'); ?>
			</td>
			<td><fieldset class="radio">
				<?php echo $this->lists['block']; ?>
			</fieldset></td>
		</tr>
		<?php endif; ?>

		<?php if ($this->lists['canSetMailopt']) : ?>
		<tr>
			<td class="key">
				<?php echo JText::_('COM_VIRTUEMART_USER_FORM_RECEIVESYSTEMEMAILS'); ?>
			</td>
			<td>
				<fieldset class="radio">
				<?php echo $this->lists['sendEmail']; ?>
				</fieldset>
			</td>
		</tr>

		<?php else : ?>
			<input type="hidden" name="sendEmail" value="0" />
		<?php endif; ?>

		<?php if( $this->userDetails->JUser ) : ?>
		<tr>
			<td class="key">
				<?php echo JText::_('COM_VIRTUEMART_USER_FORM_REGISTERDATE'); ?>
			</td>
			<td>
				<?php echo $this->userDetails->JUser->get('registerDate');?>
			</td>
		</tr>

		<tr>
			<td class="key">
				<?php echo JText::_('COM_VIRTUEMART_USER_FORM_LASTVISITDATE'); ?>
			</td>
			<td>
				<?php echo $this->userDetails->JUser->get('lastvisitDate'); ?>
			</td>
		</tr>
		<?php endif; ?>
	</table>
</fieldset>

<fieldset>
	<legend>
		<?php echo JText::_('COM_VIRTUEMART_USER_FORM_LEGEND_PARAMETERS'); ?>
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

<fieldset>
	<legend>
		<?php echo JText::_('COM_VIRTUEMART_USER_FORM_LEGEND_CONTACTINFO'); ?>
	</legend>
	<?php if ( !$this->contactDetails ) : ?>
	<table class="admintable" cellspacing="1">
		<tr>
			<td>
				<br />
				<?php echo JText::_('COM_VIRTUEMART_USER_FORM_NOCONTACTDETAILS_1'); ?>
				<br />
				<?php echo JText::_('COM_VIRTUEMART_USER_FORM_NOCONTACTDETAILS_2'); ?>
				<br /><br />
			</td>
		</tr>
	</table>
	<?php else : ?>
	<table class="admintable" cellspacing="1">
		<tr>
			<td width="15%">
				<?php echo JText::_('COM_VIRTUEMART_USER_FORM_CONTACTDETAILS_NAME'); ?>:
			</td>
			<td>
				<strong><?php echo $this->contactDetails->name;?></strong>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo JText::_('COM_VIRTUEMART_USER_FORM_CONTACTDETAILS_POSITION'); ?>:
			</td>
			<td >
				<strong><?php echo $this->contactDetails->con_position;?></strong>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo JText::_('COM_VIRTUEMART_USER_FORM_CONTACTDETAILS_TELEPHONE'); ?>:
			</td>
			<td >
				<strong><?php echo $this->contactDetails->telephone;?></strong>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo JText::_('COM_VIRTUEMART_USER_FORM_CONTACTDETAILS_FAX'); ?>:
			</td>
			<td >
				<strong><?php echo $this->contactDetails->fax;?></strong>
			</td>
		</tr>
		<tr>
			<td></td>
			<td>
				<strong><?php echo $this->contactDetails->misc;?></strong>
			</td>
		</tr>
		<?php if ($this->contactDetails->image) : ?>
			<tr>
				<td></td>
				<td valign="top">
					<img src="<?php echo $mosConfig_live_site;?>/images/stories/<?php echo $this->contactDetails->image; ?>" align="middle" alt="Contact" />
				</td>
			</tr>
		<?php endif; ?>
		<tr>
			<td colspan="2">
				<br />
				<input class="button" type="button" value="<?php echo JText::_('COM_VIRTUEMART_USER_FORM_CONTACTDETAILS_CHANGEBUTTON'); ?>" onclick="javascript: gotocontact( '<?php echo $this->contactDetails->id; ?>' )">
			</td>
		</tr>
	</table>
	<?php endif; ?>
</fieldset>

<input type="hidden" name="virtuemart_user_id" value="<?php echo $this->userDetails->JUser->get('id'); ?>" />
<input type="hidden" name="cid[]" value="<?php echo $this->userDetails->JUser->get('id'); ?>" />
<input type="hidden" name="contact_id" value="" />

