<?php
defined('_JEXEC') or die('Restricted access');  
?> 
<br />
<fieldset class="adminform">
    <legend><?php echo JText::_('VM_ADMIN_CFG_DOWNLOAD_SETTINGS') ?></legend>
    <table class="admintable">
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_ENABLE_DOWNLOADS_EXPLAIN'); ?>"/>
		<?php echo JText::_('VM_ADMIN_CFG_ENABLE_DOWNLOADS') ?>
	    </td>
	    <td>
		<?php echo VmHTML::checkbox('enable_downloads', $this->config->get('enable_downloads')); ?>
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_ORDER_ENABLE_DOWNLOADS_EXPLAIN'); ?>"/>
		<?php echo JText::_('VM_ADMIN_CFG_ORDER_ENABLE_DOWNLOADS') ?></td>
	    <td>
		<?php echo JHTML::_('Select.genericlist', $this->orderStatusList, 'enable_download_status', 'size=1', 'order_status_code', 'order_status_name', $this->config->get('enable_download_status')); ?>
	    </td>
	</tr>
        <tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_ORDER_DISABLE_DOWNLOADS_EXPLAIN'); ?>"/>
		<?php echo JText::_('VM_ADMIN_CFG_ORDER_DISABLE_DOWNLOADS') ?>
	    </td>
	    <td>
		<?php echo JHTML::_('Select.genericlist', $this->orderStatusList, 'disable_download_status', 'size=1', 'order_status_code', 'order_status_name', $this->config->get('disable_download_status')); ?>
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_DOWNLOADROOT_EXPLAIN'); ?>"/>
		<?php echo JText::_('VM_ADMIN_CFG_DOWNLOADROOT') ?></td>
	    <td valign="top">
		<input size="40" type="text" name="download_root" class="inputbox" value="<?php echo $this->config->get('download_root'); ?>" />
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_DOWNLOAD_MAX_EXPLAIN'); ?>"/>
		<?php echo JText::_('VM_ADMIN_CFG_DOWNLOAD_MAX') ?>
	    </td>
	    <td>
		<input size="3" type="text" name="download_max" class="inputbox" value="<?php echo $this->config->get('download_max'); ?>" />
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_DOWNLOAD_EXPIRE_EXPLAIN'); ?>"/>
		<?php echo JText::_('VM_ADMIN_CFG_DOWNLOAD_EXPIRE') ?></td>
	    <td>
		<input size="8" type="text" name="download_expire" class="inputbox" value="<?php echo $this->config->get('download_expire'); ?>" />
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_DOWNLOAD_KEEP_STOCKLEVEL_TIP'); ?>"/>
		<?php echo JText::_('VM_ADMIN_CFG_DOWNLOAD_KEEP_STOCKLEVEL') ?>
	    </td>
	    <td>
		<?php echo VmHTML::checkbox('downloadable_products_keep_stocklevel', $this->config->get('downloadable_products_keep_stocklevel')); ?>
	    </td>
	</tr>
    </table>
</fieldset>