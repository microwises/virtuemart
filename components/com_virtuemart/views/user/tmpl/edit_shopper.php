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
defined('_JEXEC') or die('Restricted access');

?>

<fieldset>
	<legend>
		<?php echo JText::_('VM_SHOPPER_FORM_LBL') ?>
	</legend>
	<table class="adminform">
		<tr>
			<td class="key">
				<label for="vendor_id">
					<?php echo JText::_('VM_PRODUCT_FORM_VENDOR') ?>:
				</label>
			</td>
			<td>
				<?php echo $this->lists['vendors']; ?>
			</td>
		</tr>

		<tr>
			<td class="key">
				<label for="perms">
					<?php echo JText::_('VM_USER_FORM_PERMS') ?>:
				</label>
			</td>
			<td>
				<?php echo $this->lists['perms']; ?>
			</td>
		</tr>

		<tr>
			<td class="key">
				<label for="customer_number">
					<?php echo JText::_('VM_USER_FORM_CUSTOMER_NUMBER') ?>:
				</label>
			</td>
			<td>
				<input type="text" class="inputbox" name="customer_number" size="40" value="<?php echo  $this->lists['custnumber']; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="shopper_group_id">
					<?php echo JText::_('VM_SHOPPER_FORM_GROUP') ?>:
				</label>
			</td>
			<td>
				<?php echo $this->lists['shoppergroups']; ?>
			</td>
		</tr>
	</table>
</fieldset>

<?php if ($this->userDetails->JUser->get('id') ) { ?>
<fieldset>
	<legend>
		<?php echo JText::_('VM_USER_FORM_SHIPTO_LBL'); ?>
	</legend>

	<a class="vmicon vmicon-16-editadd" href="index.php?option=com_virtuemart&view=user&layout=edit&shipto=0&cid[]=<?php echo $this->userDetails->JUser->get('id'); ?>">
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

<?php /*
<fieldset>
	<legend>
		<?php echo JText::_('VM_USERFIELDS_FORM_LBL'); ?>
	</legend>
<?php */
	$_k = 0;
	$_set = false;
	$_table = false;
	$_hiddenFields = '';

	if (count($this->userFields['functions']) > 0) {
		echo '<script language="javascript">'."\n";
		echo join("\n", $this->userFields['functions']);
		echo '</script>'."\n";
	}
	for ($_i = 0, $_n = count($this->userFields['fields']); $_i < $_n; $_i++) {
		// Do this at the start of the loop, since we're using 'continue' below!
		if ($_i == 0) {
			$_field = current($this->userFields['fields']);
		} else {
			$_field = next($this->userFields['fields']);
		}

		if ($_field['hidden'] == true) {
			$_hiddenFields .= $_field['formcode']."\n";
			continue;
		}
		if ($_field['type'] == 'delimiter') {
			if ($_set) {
				// We're in Fieldset. Close this one and start a new
				if ($_table) {
					echo '	</table>'."\n";
					$_table = false;
				}
				echo '</fieldset>'."\n";
			}
			$_set = true;
			echo '<fieldset>'."\n";
			echo '	<legend>'."\n";
			echo '		' . $_field['title'];
			echo '	</legend>'."\n";
			continue;
		}

		if (!$_table) {
			// A table hasn't been opened as well. We need one here, 
			echo '	<table class="adminform">'."\n";
			$_table = true;
		}
		echo '		<tr>'."\n";
		echo '			<td class="key">'."\n";
		echo '				<label for="'.$_field['name'].'_field">'."\n";
		echo '					'.$_field['title'] . ($_field['required']?' *': '')."\n";
		echo '				</label>'."\n";
		echo '			</td>'."\n";
		echo '			<td>'."\n";
		echo '				'.$_field['formcode']."\n";
		echo '			</td>'."\n";
		echo '		</tr>'."\n";
	}

	if ($_table) {
		echo '	</table>'."\n";
	}
	if ($_set) {
		echo '</fieldset>'."\n";
	}
	echo $_hiddenFields;
?>
<input type="hidden" name="user_info_id" value="<?php echo $this->userInfoID; ?>" />
<input type="hidden" name="address_type" value="BT" />
<? //</fieldset>  ?>