<?php
/**
 * Admin form for the checkout configuration settings
 *
 * @package	VirtueMart
 * @subpackage Config
 * @author Oscar van Eijk
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2011 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id$
 */
defined('_JEXEC') or die('Restricted access'); 
/*
<table width="100%">
	<tr>
		<td valign="top" width="50%"> */ ?> 
			<fieldset>
				<legend><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_CHECKOUT_SETTINGS') ?></legend>
				<table class="admintable">
					<tr>
						<td class="key">
							<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_CHECK_STOCK_EXPLAIN'); ?>">
								<label for="check_stock"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_CHECK_STOCK') ?></label>
							</span>
						</td>
						<td>
							<?php echo VmHTML::checkbox('check_stock', $this->config->get('check_stock')); ?>
						</td>
					</tr>

					<tr>
						<td class="key">
							<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_AGREE_TERMS_ONORDER_EXPLAIN'); ?>">
								<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_AGREE_TERMS_ONORDER') ?>
							</span>
						</td>
						<td>
							<?php echo VmHTML::checkbox('agree_to_tos_onorder', $this->config->get('agree_to_tos_onorder')); ?>
						</td>
					</tr>

					<tr>
						<td class="key">
							<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_ONCHECKOUT_SHOW_LEGALINFO_TIP'); ?>">
								<label for="oncheckout_show_legal_info"><?php echo JText::_('COM_VIRTUEMART_ADMIN_ONCHECKOUT_SHOW_LEGALINFO') ?></label>
							</span>
						</td>
						<td>
							<?php echo VmHTML::checkbox('oncheckout_show_legal_info', $this->config->get('oncheckout_show_legal_info')); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_ONCHECKOUT_SHOW_REGISTER_TIP'); ?>">
								<label for="oncheckout_show_register"><?php echo JText::_('COM_VIRTUEMART_ADMIN_ONCHECKOUT_SHOW_REGISTER') ?></label>
							</span>
						</td>
						<td>
							<?php echo VmHTML::checkbox('oncheckout_show_register', $this->config->get('oncheckout_show_register')); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_ONCHECKOUT_SHOW_REGISTER_TEXT_TIP'); ?>">
								<label for="oncheckout_show_register_text"><?php echo JText::_('COM_VIRTUEMART_ADMIN_ONCHECKOUT_SHOW_TEXT_REGISTER') ?></label>
							</span>
						</td>
						<td>
							<textarea rows="6" cols="50" name="oncheckout_show_register_text"><?php echo $this->config->get('oncheckout_show_register_text'); ?></textarea>
						</td>
					</tr>
				</table>
			</fieldset>
		</td>
<?php /*		<td valign="top">

			<fieldset>
			<legend><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_TITLES') ?></legend>
				<table class="admintable">
					<tr>
					<td class="key">
						<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_TITLES_LBL_TIP'); ?>">
						<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_TITLES_LBL') ?>
						</span>
					</td>
					<td><fieldset class="checkbox">
						<?php echo $this->titlesFields ; ?>
					</fieldset></td>
					</tr>
				</table>
		</td>
	</tr>
</table> */ ?>