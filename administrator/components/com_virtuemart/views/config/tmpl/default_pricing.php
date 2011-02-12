<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Config
* @author RickG
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
<br />
<table>
    <tr><td valign="top">

	    <fieldset class="adminform">
		<legend><?php echo JText::_('VM_ADMIN_CFG_PRICE_CONFIGURATION') ?></legend>
		<table class="admintable">
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_SHOW_PRICES_EXPLAIN'); ?>">
			    <?php echo JText::_('VM_ADMIN_CFG_SHOW_PRICES') ?>
			    </span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('show_prices', $this->config->get('show_prices')); ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_PRICE_ACCESS_LEVEL_TIP'); ?>">
			    <?php echo JText::_('VM_ADMIN_CFG_PRICE_ACCESS_LEVEL') ?>
			    </span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('price_access_level_enabled', $this->config->get('price_access_level_enabled')); ?>
			    <?php echo JText::_('VM_CFG_ENABLE_FEATURE'); ?>
			    <br />
			    <?php
				if ( VmConfig::isJ15()) {
					echo JHTML::_('Select.genericlist', $this->aclGroups, 'price_access_level', 'size=5', 'title', 'text', $this->config->get('price_access_level'));
				} else {
					$selectOptions['list.attr'] = 'size=5';
					$selectOptions['option.key'] = 'title';
					$selectOptions['option.text'] = 'text';
					$selectOptions['list.select'] = $this->config->get('price_access_level');
					$selectOptions['option.text.toHtml'] = false;
					echo JHTML::_('Select.genericlist', $this->aclGroups, 'price_access_level', $selectOptions);
				} ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_PRICE_SHOW_PACKAGING_PRICELABEL_TIP'); ?>">
			    <?php echo JText::_('VM_ADMIN_CFG_PRICE_SHOW_PACKAGING_PRICELABEL'); ?>
			    </span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('price_show_packaging_pricelabel', $this->config->get('price_show_packaging_pricelabel')); ?>
			</td>
		    </tr>
		</table>
	    </fieldset>

	</td><td valign="top">

	    <fieldset class="adminform">
		<legend><?php echo JText::_('VM_ADMIN_CFG_PRICES') ?></legend>
		<table class="admintable">

		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_PRICE_BASEPRICE_EXPLAIN'); ?>">
			    <label for="conf_TAX_VIRTUAL"><?php echo JText::_('VM_ADMIN_CFG_PRICE_BASEPRICE') ?></label>
			    </span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('basePrice', $this->config->get('basePrice')); ?>
			</td>
		    </tr>

		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_PRICE_VARMOD_EXPLAIN'); ?>">
			    <label for="conf_TAX_VIRTUAL"><?php echo JText::_('VM_ADMIN_CFG_PRICE_VARMOD') ?></label>
			    </span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('variantModification', $this->config->get('variantModification')); ?>
			</td>
		    </tr>

		    		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_PRICE_BASEPRICE_VAR_EXPLAIN'); ?>">
			    <label for="conf_TAX_VIRTUAL"><?php echo JText::_('VM_ADMIN_CFG_PRICE_BASEPRICE_VAR') ?></label>
			    </span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('basePriceVariant', $this->config->get('basePriceVariant')); ?>
			</td>
		    </tr>

		    		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_PRICE_BASEPRICE_WTAX_EXPLAIN'); ?>">
			    <label for="conf_TAX_VIRTUAL"><?php echo JText::_('VM_ADMIN_CFG_PRICE_BASEPRICE_WTAX') ?></label>
			    </span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('basePriceWithTax', $this->config->get('basePriceWithTax')); ?>
			</td>
		    </tr>

		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_PRICE_DISCPRICE_WOTAX_EXPLAIN'); ?>">
			    <label for="conf_TAX_VIRTUAL"><?php echo JText::_('VM_ADMIN_CFG_PRICE_DISCPRICE_WOTAX') ?></label>
			    </span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('discountedPriceWithoutTax', $this->config->get('discountedPriceWithoutTax')); ?>
			</td>
		    </tr>

		    		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_PRICE_SALESPRICE_WD_EXPLAIN'); ?>">
			    <label for="conf_TAX_VIRTUAL"><?php echo JText::_('VM_ADMIN_CFG_PRICE_SALESPRICE_WD') ?></label>
			    </span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('salesPriceWithDiscount', $this->config->get('salesPriceWithDiscount')); ?>
			</td>
		    </tr>

		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_PRICE_SALESPRICE_EXPLAIN'); ?>">
			    <label for="conf_TAX_VIRTUAL"><?php echo JText::_('VM_ADMIN_CFG_PRICE_SALESPRICE') ?></label>
			    </span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('salesPrice', $this->config->get('salesPrice')); ?>
			</td>
		    </tr>

		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_PRICE_SALESPRICE_WOTAX_EXPLAIN'); ?>">
			    <label for="conf_TAX_VIRTUAL"><?php echo JText::_('VM_ADMIN_CFG_PRICE_SALESPRICE_WOTAX') ?></label>
			    </span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('priceWithoutTax', $this->config->get('priceWithoutTax')); ?>
			</td>
		    </tr>

		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_PRICE_DISC_AMOUNT_EXPLAIN'); ?>">
			    <label for="conf_TAX_VIRTUAL"><?php echo JText::_('VM_ADMIN_CFG_PRICE_DISC_AMOUNT') ?></label>
			    </span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('discountAmount', $this->config->get('discountAmount')); ?>
			</td>
		    </tr>

		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_PRICE_TAX_AMOUNT_EXPLAIN'); ?>">
			    <label for="conf_TAX_VIRTUAL"><?php echo JText::_('VM_ADMIN_CFG_PRICE_TAX_AMOUNT') ?></label>
			    </span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('taxAmount', $this->config->get('taxAmount')); ?>
			</td>
		    </tr>

		</table>
	    </fieldset>
	</td></tr>
</table>