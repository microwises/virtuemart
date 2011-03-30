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

<div class="col50">
	<table class="admintable">
		<tr>
			<td valign="top">
				<fieldset class="adminform">
					<legend>
						<?php echo JText::_('VM_VENDOR_FORM_INFO_LBL') ?>
					</legend>
					<table class="admintable">
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
					</table>
				</fieldset>
			</td>

			<td valign="top">
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
								<?php echo JText::_( 'VM_STORE_FORM_ACCEPTED_CURRENCIES' ); ?>:
							</td>
							<td>
								<?php echo JHTML::_('Select.genericlist', $this->currencies, 'vendor_accepted_currencies[]', 'size=10 multiple', 'currency_code', 'currency_name', $this->vendor->vendor_accepted_currencies); ?>
							</td>
						</tr>
					</table>
				</fieldset>
			</td>
		</tr>
		<tr>
		<td colspan="2">
		<fieldset class="adminform">
			<legend>
				<?php echo JText::_('VM_VENDOR_FORM_INFO_LBL') ?>
			</legend>
			<?php
				echo $this->vendor->images[0]->displayFilesHandler($this->vendor->file_ids);
			?>


		</fieldset>

		</td>
		</tr>
		<tr>
		<td colspan="2">
				<fieldset>
					<legend>
						<?php echo JText::_('VM_STORE_FORM_DESCRIPTION');?>
					</legend>
					<?php echo $this->editor->display('vendor_store_desc', $this->vendor->vendor_store_desc, '100%', 220, 70, 15)?>
				</fieldset>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<fieldset>
					<legend>
						<?php echo JText::_('VM_STORE_FORM_TOS');?>
					</legend>
					<?php echo $this->editor->display('vendor_terms_of_service', $this->vendor->vendor_terms_of_service, '100%', 220, 70, 15)?>
				</fieldset>
			</td>
		</tr>
	</table>
</div>
<input type="hidden" name="user_is_vendor" value="1" />
<input type="hidden" name="vendor_id" value="<?php echo $this->vendor->vendor_id; ?>" />
