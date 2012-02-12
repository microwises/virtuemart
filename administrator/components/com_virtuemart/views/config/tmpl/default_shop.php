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
<fieldset>
    <legend><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOP_SETTINGS') ?></legend>
    <table class="admintable">
	<tr>
	    <td class="key">
		<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOP_OFFLINE_TIP'); ?>">
		<label for="shop_is_offline"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOP_OFFLINE',false); ?></span>
		</span>
	    </td>
	    <td>
		<?php echo VmHTML::checkbox('shop_is_offline', $this->config->get('shop_is_offline',0)); ?>
	    </td>
	</tr>
	<tr>
	    <td class="key"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOP_OFFLINE_MSG') ?></td>
	    <td>
		<textarea rows="6" cols="50" name="offline_message"><?php echo $this->config->get('offline_message'); ?></textarea>
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_USE_ONLY_AS_CATALOGUE_EXPLAIN'); ?>">
		<label for="use_as_catalog"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_USE_ONLY_AS_CATALOGUE') ?>
		</span>
	    </td>
	    <td>
		<?php echo VmHTML::checkbox('use_as_catalog', $this->config->get('use_as_catalog')); ?>
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_CFG_CURRENCY_MODULE_TIP'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_CFG_CURRENCY_MODULE') ?>
		</span>
	    </td>
	    <td>
		<?php echo JHTML::_('Select.genericlist', $this->currConverterList, 'currency_converter_module', 'size=1', 'value', 'text', $this->config->get('currency_converter_module')); ?>
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MAIL_FORMAT_EXPLAIN'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MAIL_FORMAT') ?>
		</span>
		</td>
	    <td>
		<select name="order_mail_html" id="order_mail_html">
		    <option value="0" <?php if ($this->config->get('order_mail_html') == '0') echo 'selected="selected"'; ?>>
			<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MAIL_FORMAT_TEXT') ?>
		    </option>
		    <option value="1" <?php if ($this->config->get('order_mail_html') == '1') echo 'selected="selected"'; ?>>
			<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MAIL_FORMAT_HTML') ?>
		    </option>
		</select>
	    </td>
	</tr>
<?php	/* <tr>
	<td class="key">
		<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_DATEFORMAT_EXPLAIN'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_DATEFORMAT') ?>
		</span>
		</td>
		<td>
		<input type="text" name="dateformat" class="inputbox" value="<?php echo $this->config->get('dateformat') ?>" />
	</td>
	</tr> */ ?>
	<tr>
	<td class="key">
		<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SSL_EXPLAIN'); ?>">
		<label for="useSSL"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SSL') ?></span>
		</span>
		</td>
		<td>
		<?php echo VmHTML::checkbox('useSSL', $this->config->get('useSSL',0)); ?>
		</td>
	</tr>
	<tr>
	<td class="key">
		<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MULTILANGUE_EXPLAIN'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MULTILANGUE') ?>
		</span>
		</td>
		<td>
		<?php
			echo $this->activeLanguages ;
		?>
	</td>
	</tr>
	<tr>
	<td class="key">
		<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_ENABLE_ENGLISH_EXPLAIN'); ?>">
		<label for="enableEnglish"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_ENABLE_ENGLISH') ?></span>
		</span>
		</td>
		<td>
		<?php
			echo VmHTML::checkbox('enableEnglish', $this->config->get('enableEnglish','1'));
		?>
	</td>
	</tr>
	<tr>
	<td class="key">
		<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_DANGEROUS_TOOLS_EXPLAIN'); ?>">
		<label for="dangeroustools"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_DANGEROUS_TOOLS') ?></span>
		</span>
		</td>
		<td>
		<?php echo VmHTML::checkbox('dangeroustools', $this->config->get('dangeroustools')); ?>
		</td>
	</tr>

	<tr>
	<td class="key">
		<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_ENABLE_DEBUG_EXPLAIN'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_ENABLE_DEBUG') ?>
		</span>
		</td>
		<td>
		<?php
			$options = array(
				'none'	=>	JText::_('COM_VIRTUEMART_ADMIN_CFG_ENABLE_DEBUG_NONE'),
				'admin'	=>	JText::_('COM_VIRTUEMART_ADMIN_CFG_ENABLE_DEBUG_ADMIN'),
				'all'	=> JText::_('COM_VIRTUEMART_ADMIN_CFG_ENABLE_DEBUG_ALL')
			);
			echo VmHTML::radioList('debug_enable', $this->config->get('debug_enable','none'),$options);
		?>
	</td>
	</tr>

	<td class="key">
		<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_ENABLE_GOOGLE_JQUERY_EXPLAIN'); ?>">
		<label for="google_jquery"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_ENABLE_GOOGLE_JQUERY') ?></span>
		</span>
		</td>
		<td>
		<?php
			echo VmHTML::checkbox('google_jquery', $this->config->get('google_jquery','1'));
		?>
	</td>
	</tr>
	<td class="key">
		<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_ENABLE_CONTENT_PLUGIN_EXPLAIN'); ?>">
		<label for="enable_content_plugin"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_ENABLE_CONTENT_PLUGIN') ?></span>
		</span>
		</td>
		<td>
		<?php
			echo VmHTML::checkbox('enable_content_plugin', $this->config->get('enable_content_plugin','0'));
		?>
	</td>
	</tr>
	</tr>


	<tr>
	<td class="key">
		<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_ENABLE_MULTIX_EXPLAIN'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_ENABLE_MULTIX') ?>
		</span>
		</td>
		<td>
		<?php
			$options = array(
				'none'	=>	JText::_('COM_VIRTUEMART_ADMIN_CFG_ENABLE_MULTIX_NONE'),
				'admin'	=>	JText::_('COM_VIRTUEMART_ADMIN_CFG_ENABLE_MULTIX_ADMIN')
// 				'all'	=> JText::_('COM_VIRTUEMART_ADMIN_CFG_ENABLE_DEBUG_ALL')
			);
			echo VmHTML::radioList('multix', $this->config->get('multix','none'),$options);
		?>
	</td>
	</tr>

    </table>
</fieldset>
