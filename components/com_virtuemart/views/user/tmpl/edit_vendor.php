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
		<?php echo JText::_('VM_VENDOR_FORM_INFO_LBL') ?>
	</legend>
	<table class="admintable">
		<tr>
			<td class="key">
				<?php echo JText::_('VM_STORE_FORM_FULL_IMAGE'); ?>:
			</td>
			<td>
				<?php ImageHelper::generateImageHtml($this->vendor->vendor_full_image, VmConfig::get('media_path'), 'alt="Shop Image"', false); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_( 'VM_STORE_FORM_UPLOAD' ); ?>:
			</td>
			<td>
				<input type="file" name="vendor_full_image" id="vendor_full_image" size="25" class="inputbox"  />
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('VM_STORE_FORM_STORE_NAME'); ?>:
			</td>
			<td>
				<input class="inputbox" type="text" name="vendor_store_name" id="vendor_store_name" size="50" value="<?php echo $this->vendor->vendor_store_name; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('VM_STORE_FORM_COMPANY_NAME'); ?>:
			</td>
			<td>
				<input class="inputbox" type="text" name="vendor_name" id="vendor_name" size="50" value="<?php echo $this->vendor->vendor_name; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('VM_PRODUCT_FORM_URL'); ?>:
			</td>
			<td>
				<input class="inputbox" type="text" name="vendor_url" id="vendor_url" size="50" value="<?php echo $this->vendor->vendor_url; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_( 'VM_STORE_FORM_MPOV' ); ?>:
			</td>
			<td>
				<input class="inputbox" type="text" name="vendor_min_pov" id="vendor_min_pov" size="10" value="<?php echo $this->vendor->vendor_min_pov; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_( 'VM_FREE_SHIPPING_AMOUNT' ); ?>:
			</td>
			<td>
				<input class="inputbox" type="text" name="vendor_freeshipping" id="vendor_freeshipping" size="10" value="<?php echo $this->vendor->vendor_freeshipping; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_( 'VM_VENDOR_FORM_CATEGORY' ); ?>:
			</td>
			<td>
				<?php echo $this->vendorCategories; ?>:
			</td>
		</tr>
	</table>
</fieldset>

<fieldset class="adminform">
	<legend>
		<?php echo JText::_('VM_CURRENCY_DISPLAY') ?>
	</legend>
	<table class="admintable">
		<tr>
			<td class="key">
				<?php echo JText::_( 'VM_STORE_FORM_CURRENCY' ); ?>:
			</td>
			<td>
				<?php echo JHTML::_('Select.genericlist', $this->currencies, 'vendor_currency', '', 'currency_id', 'currency_name', $this->vendor->vendor_currency); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_( 'VM_CURRENCY_SYMBOL' ); ?>:
			</td>
			<td>
				<input type="hidden" name="vendor_currency_display_style[0]" value="<?php echo $this->vendor->vendor_id; ?>" />
				<input class="inputbox" type="text" name="vendor_currency_display_style[1]" id="currency_symbol" size="10" value="<?php echo $this->vendorCurrency->getSymbol(); ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_( 'VM_CURRENCY_DECIMALS' ); ?>:
			</td>
			<td>
				<input class="inputbox" type="text" name="vendor_currency_display_style[2]" id="currency_nbr_decimals" size="10" value="<?php echo $this->vendorCurrency->getNbrDecimals(); ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_( 'VM_CURRENCY_DECIMALSYMBOL' ); ?>:
			</td>
			<td>
				<input class="inputbox" type="text" name="vendor_currency_display_style[3]" id="currency_decimal_symbol" size="10" value="<?php echo $this->vendorCurrency->getDecimalSymbol(); ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_( 'VM_CURRENCY_THOUSANDS' ); ?>:
			</td>
			<td>
				<input class="inputbox" type="text" name="vendor_currency_display_style[4]" id="currency_thousands_seperator" size="10" value="<?php echo $this->vendorCurrency->getThousandsSeperator(); ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_( 'VM_CURRENCY_POSITIVE_DISPLAY' ); ?>:
			</td>
			<td>
				<?php
					$options = array();
					$options[] = JHTML::_('select.option', '0', JText::_('00Symb') );
					$options[] = JHTML::_('select.option', '1', JText::_('00 Symb'));
					$options[] = JHTML::_('select.option', '2', JText::_('Symb00'));
					$options[] = JHTML::_('select.option', '3', JText::_('Symb 00'));
					echo JHTML::_('Select.genericlist', $options, 'vendor_currency_display_style[5]', 'size=1', 'value', 'text', $this->vendorCurrency->getPositiveFormat());
				?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_( 'VM_CURRENCY_NEGATIVE_DISPLAY' ); ?>:
			</td>
			<td>
				<?php
					$options = array();
					$options[] = JHTML::_('select.option', '0', JText::_('(Symb00)') );
					$options[] = JHTML::_('select.option', '1', JText::_('-Symb00'));
					$options[] = JHTML::_('select.option', '2', JText::_('Symb00-'));
					$options[] = JHTML::_('select.option', '3', JText::_('(00Symb)'));
					$options[] = JHTML::_('select.option', '4', JText::_('-00Symb') );
					$options[] = JHTML::_('select.option', '5', JText::_('00-Symb'));
					$options[] = JHTML::_('select.option', '6', JText::_('00Symb-'));
					$options[] = JHTML::_('select.option', '7', JText::_('-00 Symb'));
					$options[] = JHTML::_('select.option', '8', JText::_('-Symb 00'));
					$options[] = JHTML::_('select.option', '9', JText::_('00 Symb-') );
					$options[] = JHTML::_('select.option', '10', JText::_('Symb 00-'));
					$options[] = JHTML::_('select.option', '11', JText::_('Symb -00'));
					$options[] = JHTML::_('select.option', '12', JText::_('(Symb 00)'));
					$options[] = JHTML::_('select.option', '13', JText::_('(00 Symb)'));
					echo JHTML::_('Select.genericlist', $options, 'vendor_currency_display_style[6]', 'size=1', 'value', 'text', $this->vendorCurrency->getNegativeFormat());
				?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_( 'VM_STORE_FORM_ACCEPTED_CURRENCIES' ); ?>:
			</td>
			<td>
				<?php echo JHTML::_('Select.genericlist', $this->currencies, 'vendor_accepted_currencies[]', 'size=10 multiple', 'currency_code', 'currency_name', $this->vendor->vendor_accepted_currencies); ?>
			</td>
		</tr>
	</table>
</fieldset>

<fieldset>
	<legend>
		<?php echo JText::_('VM_STORE_FORM_DESCRIPTION');?>
	</legend>
	<?php echo $this->editor->display('vendor_store_desc', $this->vendor->vendor_store_desc, '100%', 220, 70, 15)?>
</fieldset>

<fieldset>
	<legend>
		<?php echo JText::_('VM_STORE_FORM_TOS');?>
	</legend>
	<?php echo $this->editor->display('vendor_terms_of_service', $this->vendor->vendor_terms_of_service, '100%', 220, 70, 15)?>
</fieldset>

<input type="hidden" name="my_vendor_id" value="<?php echo $this->vendor->vendor_id; ?>" />
