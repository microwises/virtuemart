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



<fieldset>
	<legend>
		<?php echo JText::_('VM_SHOPPER_FORM_LBL') ?>
	</legend>
	<table class="adminform">
		<tr>
			<td class="key">
				<label for="">
					<?php echo JText::_('VM_PRODUCT_FORM_VENDOR') ?>:
				</label>
			</td>
			<td>
				<label for="">
					<?php echo $this->lists['vendors']; ?>
				</label>
			</td>
		</tr>

		<tr>
			<td class="key">
				<?php echo JText::_('VM_USER_FORM_PERMS') ?>:
			</td>
			<td>
				<label for="">
					<?php echo $this->lists['perms']; ?>
				</label>
			</td>
		</tr>

		<tr>
			<td class="key">
				<?php echo JText::_('VM_USER_FORM_CUSTOMER_NUMBER') ?>:
			</td>
			<td>
				<label for="">
					<input type="text" class="inputbox" name="customer_number" size="40" value="<?php echo  $this->lists['custnumber']; ?>" />
				</label>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('VM_SHOPPER_FORM_GROUP') ?>:
			</td>
			<td>
				<label for="">
					<?php echo $this->lists['shoppergroups']; ?>
				</label>
			</td>
		</tr>
	</table>
</fieldset>


<?php if ($this->userDetails->JUser->get('id') ) { ?>
<fieldset>
	<legend>
		<?php echo JText::_('VM_USER_FORM_SHIPTO_LBL'); ?>
	</legend>

	<a class="vmicon vmicon-16-editadd" href="index.php?option=com_virtuemart&view=user&task=ship_address&uid=<?php echo $this->userDetails->JUser->get('id'); ?>">
		<?php echo JText::_('VM_USER_FORM_ADD_SHIPTO_LBL'); ?>
	</a>

	<table class="adminform">
		<tr>
			<td>
				<?php echo $this->lists['shipTo']; ?>
			</td>
		</tr>
	</table>
</fieldset>
<?php } ?>

<fieldset>
	<legend>
		<?php echo JText::_('VM_USERFIELDS_FORM_LBL'); ?>
	</legend>
<?php 
	$_k = 0;
	$_set = false;
	$_table = false;
	for ($_i = 0, $_n = count($this->userFields); $_i < $_n; $_i++) {
		$_field =& $this->userFields[$_i];

		if ($_field['type'] == 'delimiter') {
			if ($_set) {
				// We're in Fieldset. Close this one and start a new
				if ($_table) {
					echo '	</table>';
				}
				echo '</fieldset>';
			}
			$_set = true;
			echo '<fieldset>';
			echo '	<legend>';
			echo '		' . $_field['title'];
			echo '	</legend>';
			continue;
		}

		if (!$_table) {
			// We're not in a fieldset, so a table hasn't been opened as well.
			// We need one here, 
			echo '	<table class="adminform">';
		}

		
	}

	if ($_table) {
		echo '	</table>';
	}

	if ($_set) {
		echo '</fieldset>';
	}
?>
</fieldset>
