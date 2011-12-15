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
							<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_AUTOMATIC_SHIPMENT_EXPLAIN'); ?>">
								<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_AUTOMATIC_SHIPMENT') ?>
							</span>
						</td>
						<td>
							<?php echo VmHTML::checkbox('automatic_shipment', $this->config->get('automatic_shipment')); ?>
						</td>
					</tr>
                                         <tr>
						<td class="key">
							<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_AUTOMATIC_PAYMENT_EXPLAIN'); ?>">
								<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_AUTOMATIC_PAYMENt') ?>
							</span>
						</td>
						<td>
							<?php echo VmHTML::checkbox('automatic_payment', $this->config->get('automatic_payment')); ?>
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
							<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_ONCHECKOUT_ONLY_REGISTERED_TIP'); ?>">
								<label for="oncheckout_only_registered"><?php echo JText::_('COM_VIRTUEMART_ADMIN_ONCHECKOUT_ONLY_REGISTERED') ?></label>
							</span>
						</td>
						<td>
							<?php echo VmHTML::checkbox('oncheckout_only_registered', $this->config->get('oncheckout_only_registered')); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_ONCHECKOUT_SHOW_STEPS_TIP'); ?>">
								<label for="oncheckout_show_steps"><?php echo JText::_('COM_VIRTUEMART_ADMIN_ONCHECKOUT_SHOW_STEPS') ?></label>
							</span>
						</td>
						<td>
							<?php echo VmHTML::checkbox('oncheckout_show_steps', $this->config->get('oncheckout_show_steps')); ?>
						</td>
					</tr>
						<tr>
						<td class="key">
							<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_ONCHECKOUT_SHOW_PRODUCTIMAGES_TIP'); ?>">
								<label for="oncheckout_show_images"><?php echo JText::_('COM_VIRTUEMART_ADMIN_ONCHECKOUT_SHOW_PRODUCTIMAGES') ?></label>
							</span>
						</td>
						<td>
							<?php echo VmHTML::checkbox('oncheckout_show_images', $this->config->get('oncheckout_show_images',0)); ?>
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