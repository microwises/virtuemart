<?php
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
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_SHOW_PRICES_EXPLAIN'); ?>"/>
			    <?php echo JText::_('VM_ADMIN_CFG_SHOW_PRICES') ?>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('show_prices', $this->config->get('show_prices')); ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_PRICE_ACCESS_LEVEL_TIP'); ?>"/>
			    <?php echo JText::_('VM_ADMIN_CFG_PRICE_ACCESS_LEVEL') ?>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('price_access_level_enabled', $this->config->get('price_access_level_enabled')); ?>
			    <?php echo JText::_('VM_CFG_ENABLE_FEATURE'); ?>
			    <?php echo JHTML::_('Select.genericlist', $this->aclGroups, 'price_access_level', 'size=5', 'name', 'text', $this->config->get('price_access_level')); ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_PRICE_SHOW_PACKAGING_PRICELABEL_TIP'); ?>"/>
			    <?php echo JText::_('VM_ADMIN_CFG_PRICE_SHOW_PACKAGING_PRICELABEL'); ?>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('price_show_packaging_pricelabel', $this->config->get('price_show_packaging_pricelabel')); ?>
			</td>
		    </tr>
		</table>
	    </fieldset>

	</td><td valign="top">

	    <fieldset class="adminform">
		<legend><?php echo JText::_('VM_ADMIN_CFG_TAX_CONFIGURATION') ?></legend>
		<table class="admintable">
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_VIRTUAL_TAX_EXPLAIN'); ?>"/>
			    <label for="conf_TAX_VIRTUAL"><?php echo JText::_('VM_ADMIN_CFG_VIRTUAL_TAX') ?></label>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('tax_virtual', $this->config->get('tax_virtual')); ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_TAX_MODE_EXPLAIN'); ?>"/>
			    <?php echo JText::_('VM_ADMIN_CFG_TAX_MODE') ?>
			</td>
			<td>
			    <?php
			    $options = array();
			    $options[] = JHTML::_('select.option', '0', JText::_('VM_ADMIN_CFG_TAX_MODE_SHIP') );
			    $options[] = JHTML::_('select.option', '1', JText::_('VM_ADMIN_CFG_TAX_MODE_VENDOR'));
			    $options[] = JHTML::_('select.option', '17749', JText::_('VM_ADMIN_CFG_TAX_MODE_EU'));
			    echo JHTML::_('Select.genericlist', $options, 'tax_mode', 'size=1', 'value', 'text', $this->config->get('tax_mode'));
			    ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_MULTI_TAX_RATE_EXPLAIN'); ?>"/>
			    <?php echo JText::_('VM_ADMIN_CFG_MULTI_TAX_RATE') ?>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('multiple_taxrates_enable', $this->config->get('multiple_taxrates_enable')); ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_SUBSTRACT_PAYEMENT_BEFORE_EXPLAIN'); ?>"/>
			    <?php echo JText::_('VM_ADMIN_CFG_SUBSTRACT_PAYEMENT_BEFORE') ?>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('payment_discount_before', $this->config->get('payment_discount_before')); ?>
			</td>
		    </tr>
		</table>
	    </fieldset>
	</td></tr>
</table>