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
<fieldset class="adminform">
    <legend><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_DOWNLOAD_SETTINGS') ?></legend>
    <table class="admintable">
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_ENABLE_DOWNLOADS_EXPLAIN'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_ENABLE_DOWNLOADS') ?>
		</span>
	    </td>
	    <td>
		<?php echo VmHTML::checkbox('enable_downloads', $this->config->get('enable_downloads')); ?>
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_ORDER_ENABLE_DOWNLOADS_EXPLAIN'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_ORDER_ENABLE_DOWNLOADS') ?>
		</span>
		</td>
	    <td>
		<?php echo JHTML::_('Select.genericlist', $this->orderStatusList, 'enable_download_status', 'size=1', 'order_status_code', 'order_status_name', $this->config->get('enable_download_status')); ?>
	    </td>
	</tr>
        <tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_ORDER_DISABLE_DOWNLOADS_EXPLAIN'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_ORDER_DISABLE_DOWNLOADS') ?>
		</span>
	    </td>
	    <td>
		<?php echo JHTML::_('Select.genericlist', $this->orderStatusList, 'disable_download_status', 'size=1', 'order_status_code', 'order_status_name', $this->config->get('disable_download_status')); ?>
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_DOWNLOADROOT_EXPLAIN'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_DOWNLOADROOT') ?>
		</span>
		</td>
	    <td valign="top">
		<input size="60" type="text" name="download_root" class="inputbox" value="<?php echo $this->config->get('download_root'); ?>" />
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_DOWNLOAD_MAX_EXPLAIN'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_DOWNLOAD_MAX') ?>
		</span>
	    </td>
	    <td>
		<input size="3" type="text" name="download_max" class="inputbox" value="<?php echo $this->config->get('download_max'); ?>" />
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_DOWNLOAD_EXPIRE_EXPLAIN'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_DOWNLOAD_EXPIRE') ?>
		</span>
		</td>
	    <td>
		<input size="8" type="text" name="download_expire" class="inputbox" value="<?php echo $this->config->get('download_expire'); ?>" />
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_DOWNLOAD_KEEP_STOCKLEVEL_TIP'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_DOWNLOAD_KEEP_STOCKLEVEL') ?>
		</span>
	    </td>
	    <td>
		<?php echo VmHTML::checkbox('downloadable_products_keep_stocklevel', $this->config->get('downloadable_products_keep_stocklevel')); ?>
	    </td>
	</tr>
    </table>
</fieldset>