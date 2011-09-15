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
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOP_OFFLINE',false); ?>
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
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_USE_ONLY_AS_CATALOGUE') ?>
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
	<tr>
	<td class="key">
		<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_DATEFORMAT_EXPLAIN'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_DATEFORMAT') ?>
		</span>
		</td>
		<td>
		<input type="text" name="dateformat" class="inputbox" value="<?php echo $this->config->get('dateformat') ?>" />
	</td>
	</tr>
	<tr>
	<td class="key">
		<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SSL_EXPLAIN'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SSL') ?>
		</span>
		</td>
		<td>
		<?php echo VmHTML::checkbox('useSSL', $this->config->get('useSSL',0)); ?>
		</td>
	</tr>
	<tr>
	<td class="key">
		<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_DANGEROUS_TOOLS_EXPLAIN'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_DANGEROUS_TOOLS') ?>
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
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_ENABLE_GOOGLE_JQUERY') ?>
		</span>
		</td>
		<td>
		<?php
			echo VmHTML::checkbox('google_jquery', $this->config->get('google_jquery','1'));
		?>
	</td>
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
<?php /*
<fieldset>
    <legend><?php echo JText::_('COM_VIRTUEMART_ADMIN_SECURITY_SETTINGS') ?></legend>
    <table class="admintable">
	<tr>
	    <td class="key">
		<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_MODULES_FORCE_HTTPS_TIP'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_MODULES_FORCE_HTTPS') ?>
		</span>
	    </td>
	    <td>
		<?php
		echo JHTML::_('Select.genericlist', $this->moduleList, 'modules_force_https[]', 'size=4 multiple', 'module_id', 'module_name', $this->config->get('modules_force_https'));
		?>
	    </td>
	</tr>

	<tr>
	    <td class="key">
		<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_GENERALLY_PREVENT_HTTPS_TIP'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_GENERALLY_PREVENT_HTTPS') ?>
		</span>
	    </td>
	    <td>
		<?php echo VmHTML::checkbox('generally_prevent_https', $this->config->get('generally_prevent_https')); ?>
	    </td>
	</tr>
	<?php
	//if( version_compare( $database->getVersion(), '4.0.2', '>=') ) { ?>
	<tr>
	    <td class="key">
		<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_ENCRYPTION_FUNCTION_TIP'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_ENCRYPTION_FUNCTION') ?>&nbsp;&nbsp;
		</span>
	    </td>
	    <td>
		<?php
		$options = array();
		$options[] = JHTML::_('select.option', 'ENCODE', JText::_('COM_VIRTUEMART_ENCODE_INSECURE'));
		$options[] = JHTML::_('select.option', 'AES_ENCRYPT', JText::_('COM_VIRTUEMART_AES_ENCRYPT_STRONG_SECURITY'));
		echo JHTML::_('Select.genericlist', $options, 'encrypt_function', 'size=1', 'value', 'text', $this->config->get('encrypt_function'));
		?>
	    </td>
	</tr>
	<?php
	//}
	?>
	<tr>
	    <td class="key">
		<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_ENCRYPTION_KEY_TIP'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_ENCRYPTION_KEY') ?>&nbsp;&nbsp;
		</span>
		</td>
	    <td>
		<input type="text" name="encode_key" class="inputbox" size="40" value="<?php echo $this->config->get('encode_key'); ?>" />
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_STORE_CREDITCARD_DATA'); ?>&nbsp;&nbsp;
	    </td>
	    <td>
		<?php echo VmHTML::checkbox('store_creditcard_data', $this->config->get('store_creditcard_data')); ?>
	    </td>
	</tr>
	<?php
	if (stristr(JFactory::getUser()->usertype, "admin")) { ?>
	<tr>
	    <td  class="key">
		<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_FRONTENDAMDIN_EXPLAIN'); ?>">
		    <?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_FRONTENDAMDIN') ?>
		</span>
	    </td>
	    <td>
		    <?php echo VmHTML::checkbox('allow_frontendadmin_for_nonbackenders', $this->config->get('allow_frontendadmin_for_nonbackenders')); ?>
	    </td>
	</tr>
	    <?php
	}
	else {
	    echo '<input type="hidden" name="allow_frontendadmin_for_nonbackenders" value="'.$this->config->get('allow_frontendadmin_for_nonbackenders').'" />';
	}

    </table>
</fieldset> 	*/ ?>