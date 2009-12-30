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
			    <?php
			    $checked = '';
			    if ($this->config->get('show_prices')) $checked = 'checked'; ?>
			    <input type="checkbox" name="show_prices" value="1" <?php echo $checked; ?> />
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_PRICE_ACCESS_LEVEL_TIP'); ?>"/>
			    <?php echo JText::_('VM_ADMIN_CFG_PRICE_ACCESS_LEVEL') ?>
			</td>
			<td>
			    <input type="checkbox" id="price_access_level_enabled" name="price_access_level_enabled" class="inputbox" value="<?php echo $this->config->get('price_access_level_enabled'); ?>" />
			    <?php echo JText::_('VM_CFG_ENABLE_FEATURE'); ?>
			    <?php echo JHTML::_('Select.genericlist', $this->aclGroups, 'price_access_level', 'size=5', 'name', 'text', $this->config->get('oncheckout_legalinfo_link')); ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_PRICE_SHOW_PACKAGING_PRICELABEL_TIP'); ?>"/>
			    <?php echo JText::_('VM_ADMIN_CFG_PRICE_SHOW_PACKAGING_PRICELABEL'); ?>
			</td>
			<td>
			    <?php
			    $checked = '';
			    if ($this->config->get('price_show_packaging_pricelabel')) $checked = 'checked"'; ?>
			    <input type="checkbox" name="price_show_packaging_pricelabel" value="1" <?php echo $checked; ?> />
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
			    <?php
			    $checked = '';
			    if ($this->config->get('tax_virtual')) $checked = 'checked"'; ?>
			    <input type="checkbox" name="tax_virtual" value="1" <?php echo $checked; ?> />
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
			    echo JHTML::_('Select.genericlist', $options, 'tax_mode', 'size=1');
			    ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_MULTI_TAX_RATE_EXPLAIN'); ?>"/>
			    <?php echo JText::_('VM_ADMIN_CFG_MULTI_TAX_RATE') ?>
			</td>
			<td>
			    <?php
			    $checked = '';
			    if ($this->config->get('multiple_taxrates_enable')) $checked = 'checked'; ?>
			    <input type="checkbox" name="multiple_taxrates_enable" value="1" <?php echo $checked; ?> />
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_SUBSTRACT_PAYEMENT_BEFORE_EXPLAIN'); ?>"/>
			    <?php echo JText::_('VM_ADMIN_CFG_SUBSTRACT_PAYEMENT_BEFORE') ?>
			</td>
			<td>
			    <?php
			    $checked = '';
			    if ($this->config->get('payment_discount_before')) $checked = 'checked'; ?>
			    <input type="checkbox" name="payment_discount_before" value="1" <?php echo $checked; ?> />
			</td>
		    </tr>
		</table>
	    </fieldset>
	</td></tr>
</table>