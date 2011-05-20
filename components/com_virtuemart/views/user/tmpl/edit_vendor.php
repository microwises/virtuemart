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


<fieldset class="adminform">
	<legend>
		<?php echo JText::_('COM_VIRTUEMART_VENDOR_FORM_INFO_LBL') ?>
	</legend>
	<table class="admintable">
		<tr>
			<td class="key">
				<?php echo JText::_('COM_VIRTUEMART_STORE_FORM_STORE_NAME'); ?>
			</td>
			<td>
				<input class="inputbox" type="text" name="vendor_store_name" id="vendor_store_name" size="50" value="<?php echo $this->vendor->vendor_store_name; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('COM_VIRTUEMART_STORE_FORM_COMPANY_NAME'); ?>
			</td>
			<td>
				<input class="inputbox" type="text" name="vendor_name" id="vendor_name" size="50" value="<?php echo $this->vendor->vendor_name; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('COM_VIRTUEMART_URL'); ?>
			</td>
			<td>
				<input class="inputbox" type="text" name="vendor_url" id="vendor_url" size="50" value="<?php echo $this->vendor->vendor_url; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('COM_VIRTUEMART_STORE_FORM_MPOV'); ?>
			</td>
			<td>
				<input class="inputbox" type="text" name="vendor_min_pov" id="vendor_min_pov" size="10" value="<?php echo $this->vendor->vendor_min_pov; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('COM_VIRTUEMART_FREE_SHIPPING_AMOUNT'); ?>
			</td>
			<td>
				<input class="inputbox" type="text" name="vendor_freeshipping" id="vendor_freeshipping" size="10" value="<?php echo $this->vendor->vendor_freeshipping; ?>" />
			</td>
		</tr>
		<tr>

		</tr>
	</table>
</fieldset>


<fieldset class="adminform">
	<legend>
		<?php echo JText::_('COM_VIRTUEMART_CURRENCY_DISPLAY') ?>
	</legend>
	<table class="admintable">
		<tr>
			<td class="key">
				<?php echo JText::_('COM_VIRTUEMART_STORE_FORM_CURRENCY'); ?>
			</td>
			<td>
				<?php echo JHTML::_('Select.genericlist', $this->currencies, 'vendor_currency', '', 'virtuemart_currency_id', 'currency_name', $this->vendor->vendor_currency); ?>
			</td>
		</tr><?php /*
	<tr>
		<td class="key">
			<?php echo JText::_('COM_VIRTUEMART_CURRENCY_SYMBOL'); ?>
		</td>
		<td>
			<input class="inputbox" type="text" name="currency_symbol" id="currency_symbol" size="20" value="<?php echo $this->currency->currency_symbol; ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<?php echo JText::_('COM_VIRTUEMART_CURRENCY_DECIMALS'); ?>
		</td>
		<td>
			<input class="inputbox" type="text" name="currency_decimal_place" id="currency_decimal_place" size="20" value="<?php echo $this->currency->currency_decimal_place; ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<?php echo JText::_('COM_VIRTUEMART_CURRENCY_DECIMALSYMBOL'); ?>
		</td>
		<td>
			<input class="inputbox" type="text" name="currency_decimal_symbol" id="currency_decimal_symbol" size="10" value="<?php echo $this->currency->currency_decimal_symbol; ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<?php echo JText::_('COM_VIRTUEMART_CURRENCY_THOUSANDS'); ?>
		</td>
		<td>
			<input class="inputbox" type="text" name="currency_thousands" id="currency_thousands" size="10" value="<?php echo $this->currency->currency_thousands; ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<?php echo JText::_('COM_VIRTUEMART_CURRENCY_POSITIVE_DISPLAY'); ?>
		</td>
		<td >
			<input class="inputbox" type="text" name="currency_positive_style" id="currency_positive_style" size="50" value="<?php echo $this->currency->currency_positive_style; ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<?php echo JText::_('COM_VIRTUEMART_CURRENCY_NEGATIVE_DISPLAY'); ?>
		</td>
		<td>
			<input class="inputbox" type="text" name="currency_negative_style" id="currency_negative_style" size="50" value="<?php echo $this->currency->currency_negative_style; ?>" />
		</td>
	</tr>
<tr>
	<?php echo JText::_('COM_VIRTUEMART_CURRENCY_DISPLAY_EXPL'); ?>
</tr> */ ?>
		<tr>
			<td class="key">
				<?php echo JText::_('COM_VIRTUEMART_STORE_FORM_ACCEPTED_CURRENCIES'); ?>
			</td>
			<td><br />
				<?php echo JHTML::_('Select.genericlist', $this->currencies, 'vendor_accepted_currencies[]', 'size=20 multiple', 'currency_code_3', 'currency_name', $this->vendor->vendor_accepted_currencies); ?>
			</td>
		</tr>
	</table>
</fieldset>

<fieldset class="adminform">
	<legend>
		<?php echo JText::_('COM_VIRTUEMART_VENDOR_FORM_INFO_LBL') ?>
	</legend>
	<table class="admintable">
	<?php
		echo $this->vendor->images[0]->displayFileHandler();
	?>
	</table>
</fieldset>

<fieldset>
	<legend>
		<?php echo JText::_('COM_VIRTUEMART_STORE_FORM_DESCRIPTION');?>
	</legend>
	<?php echo $this->editor->display('vendor_store_desc', $this->vendor->vendor_store_desc, '100%', 220, 70, 15)?>
</fieldset>

<fieldset>
	<legend>
		<?php echo JText::_('COM_VIRTUEMART_STORE_FORM_TOS');?>
	</legend>
	<?php echo $this->editor->display('vendor_terms_of_service', $this->vendor->vendor_terms_of_service, '100%', 220, 70, 15)?>
</fieldset>

<input type="hidden" name="user_is_vendor" value="1" />
<input type="hidden" name="virtuemart_vendor_id" value="<?php echo $this->vendor->virtuemart_vendor_id; ?>" />
