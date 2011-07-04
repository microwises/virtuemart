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
		<legend><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_PRICE_CONFIGURATION') ?></legend>
		<table class="admintable">
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOW_PRICES_EXPLAIN'); ?>">
			    <?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOW_PRICES') ?>
			    </span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('show_prices', $this->config->get('show_prices')); ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_PRICE_ACCESS_LEVEL_TIP'); ?>">
			    <?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_PRICE_ACCESS_LEVEL') ?>
			    </span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('price_access_level_published', $this->config->get('price_access_level_published')); ?>
			    <?php echo JText::_('COM_VIRTUEMART_CFG_ENABLE_FEATURE'); ?>
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
			    <span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_PRICE_SHOW_PACKAGING_PRICELABEL_TIP'); ?>">
			    <?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_PRICE_SHOW_PACKAGING_PRICELABEL'); ?>
			    </span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('price_show_packaging_pricelabel', $this->config->get('price_show_packaging_pricelabel')); ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_PRICE_SHOW_TAX_TIP'); ?>">
			    <?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_PRICE_SHOW_TAX'); ?>
			    </span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('show_tax', $this->config->get('show_tax')); ?>
			</td>
		    </tr>
		</table>
	    </fieldset>

	</td><td valign="top">

	    <fieldset class="adminform">
		<legend><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_PRICES') ?></legend>
		<table class="admintable">
			<tr>
				<th></th>
				<th></th>
				<th><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_PRICES_TEXT'); ?></th>
				<th><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_PRICES_ROUNDING'); ?></th>
			</tr>
			<?php
			echo $this->writePriceConfigLine('basePrice','COM_VIRTUEMART_ADMIN_CFG_PRICE_BASEPRICE');
			echo $this->writePriceConfigLine('variantModification','COM_VIRTUEMART_ADMIN_CFG_PRICE_VARMOD');
			echo $this->writePriceConfigLine('basePriceVariant','COM_VIRTUEMART_ADMIN_CFG_PRICE_BASEPRICE_VAR');
			echo $this->writePriceConfigLine('basePriceWithTax','COM_VIRTUEMART_ADMIN_CFG_PRICE_BASEPRICE_WTAX');
			echo $this->writePriceConfigLine('discountedPriceWithoutTax','COM_VIRTUEMART_ADMIN_CFG_PRICE_DISCPRICE_WOTAX');
			echo $this->writePriceConfigLine('salesPriceWithDiscount','COM_VIRTUEMART_ADMIN_CFG_PRICE_SALESPRICE_WD');
			echo $this->writePriceConfigLine('salesPrice','COM_VIRTUEMART_ADMIN_CFG_PRICE_SALESPRICE');
			echo $this->writePriceConfigLine('priceWithoutTax','COM_VIRTUEMART_ADMIN_CFG_PRICE_SALESPRICE_WOTAX');
			echo $this->writePriceConfigLine('discountAmount','COM_VIRTUEMART_ADMIN_CFG_PRICE_DISC_AMOUNT');
			echo $this->writePriceConfigLine('taxAmount','COM_VIRTUEMART_ADMIN_CFG_PRICE_TAX_AMOUNT');
			?>
		</table>
	    </fieldset>
	</td></tr>
</table>