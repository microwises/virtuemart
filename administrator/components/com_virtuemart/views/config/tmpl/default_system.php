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
    <legend><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SYSTEM_SETTINGS') ?></legend>
    <table class="admintable">
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_URLSECURE_EXPLAIN'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_URLSECURE') ?>
		</span>
	    </td>
	    <td>
		<input size="40" type="text" name="secureurl" class="inputbox" value="<?php echo $this->config->get('secureurl'); ?>" />
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_DEBUG_EXPLAIN'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_DEBUG') ?>
		</span>
	    </td>
	    <td>
		<?php echo VmHTML::checkbox('debug', $this->config->get('debug')); ?>
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_DEBUG_IP_published_EXPLAIN'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_DEBUG_IP_published') ?>
		</span>
	    </td>
	    <td>
		<?php echo VmHTML::checkbox('debug_ip_published', $this->config->get('debug_ip_published')); ?>
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_DEBUG_IP_ADDRESS_EXPLAIN'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_DEBUG_IP_ADDRESS') ?>
		</span>
	    </td>
	    <td>
		<input size="20" type="text" name="debug_ip_address" class="inputbox" value="<?php echo $this->config->get('debug_ip_address'); ?>" />
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_COOKIE_CHECK_EXPLAIN'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_COOKIE_CHECK') ?>
		</span>
	    </td>
	    <td>
		<?php echo VmHTML::checkbox('enable_cookie_check', $this->config->get('enable_cookie_check')); ?>
	    </td>
	</tr>
    </table>
</fieldset>

<fieldset class="adminform">
    <legend><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_USER_REGISTRATION_SETTINGS') ?></legend>
    <table class="admintable">
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_CFG_REGISTRATION_TYPE_TIP'); ?>">
		    <?php echo JText::_('COM_VIRTUEMART_CFG_REGISTRATION_TYPE') ?>
		</span>
	    </td>
	    <td>
		<?php
		$options = array();
		$options[] = JHTML::_('select.option', 'NORMAL_REGISTRATION', JText::_('COM_VIRTUEMART_CFG_REGISTRATION_TYPE_NORMAL_REGISTRATION') );
		$options[] = JHTML::_('select.option', 'SILENT_REGISTRATION', JText::_('COM_VIRTUEMART_CFG_REGISTRATION_TYPE_SILENT_REGISTRATION'));
		$options[] = JHTML::_('select.option', 'OPTIONAL_REGISTRATION', JText::_('COM_VIRTUEMART_CFG_REGISTRATION_TYPE_OPTIONAL_REGISTRATION'));
		$options[] = JHTML::_('select.option', 'NO_REGISTRATION', JText::_('COM_VIRTUEMART_CFG_REGISTRATION_TYPE_NO_REGISTRATION'));
		echo JHTML::_('Select.genericlist', $options, 'registration_type', 'size=1', 'value', 'text', $this->config->get('registration_type'));
		?>
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_SHOW_REMEMBER_ME_BOX_TIP'); ?>">
		    <?php echo JText::_('COM_VIRTUEMART_SHOW_REMEMBER_ME_BOX') ?>
		</span>
	    </td>
	    <td>
		<?php echo VmHTML::checkbox('show_remember_me_box', $this->config->get('show_remember_me_box')); ?>
	    </td>
	</tr>

<?php if (0) { ?>
<!-- Outcommented to revert rev. 2916 -->
	<tr>
		<td class="key">
			<span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_CFG_REGISTRATION_DEFAULT_VENDOR_TIP'); ?>">
				<?php echo JText::_('COM_VIRTUEMART_CFG_REGISTRATION_DEFAULT_VENDOR') ?>
			</span>
		</td>
		<td>
			<?php echo $this->vendorList; ?>
		</td>
	</tr>
<?php } ?>

	<tr>
	    <td class="key">
		<?php echo 'Joomla! ' . JText::_('COM_VIRTUEMART_ADMIN_CFG_ALLOW_REGISTRATION'); ?>
	    </td>
	    <td><?php
		if ($this->userparams->get('allowUserRegistration') == '1' ) {
		    echo '<span style="color:green;">'.JText::_('COM_VIRTUEMART_ADMIN_CFG_YES').'</span>';
		}
		else {
		    echo '<span style="color:red;font-weight:bold;">'.JText::_('COM_VIRTUEMART_ADMIN_CFG_NO').'</span>';
		}
		$link = JROUTE::_('index.php?option=com_config');
		echo '&nbsp;' . JHTML::_('link', $link, '['.JText::_('COM_VIRTUEMART_UPDATE').']');
		?></td>
	</tr>
	<tr>
	    <td class="key">
		<?php echo 'Joomla! ' . JText::_('COM_VIRTUEMART_ADMIN_CFG_ACCOUNT_ACTIVATION'); ?>
	    </td>
	    <td><?php
		if ($this->userparams->get('useractivation') == '0' ) {
		    echo '<span style="color:green;">'.JText::_('COM_VIRTUEMART_ADMIN_CFG_NO').'</span>';
		}
		else {
		    echo '<span style="color:red;font-weight:bold;">'.JText::_('COM_VIRTUEMART_ADMIN_CFG_YES').'</span>';
		}
		$link = JROUTE::_('index.php?option=com_config');
		echo '&nbsp;' . JHTML::_('link', $link, '['.JText::_('COM_VIRTUEMART_UPDATE').']');
		?>
	    </td>
	</tr>
    </table>
</fieldset>

<fieldset class="adminform">
    <legend><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_PROXY_SETTINGS') ?></legend>
    <table class="admintable">
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_PROXY_URL_TIP'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_PROXY_URL') ?>
		</span>
	    </td>
	    <td>
		<input size="40" type="text" name="proxy_url" class="inputbox" value="<?php echo $this->config->get('proxy_url'); ?>" />
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_PROXY_PORT_TIP'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_PROXY_PORT') ?>
		</span>
	    </td>
	    <td>
		<input type="text" name="proxy_port" class="inputbox" value="<?php echo $this->config->get('proxy_port'); ?>" />
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_PROXY_USER_TIP'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_PROXY_USER') ?>
		</span>
	    </td>
	    <td>
		<input type="text" name="proxy_user" class="inputbox" value="<?php echo $this->config->get('proxy_user');
		       ; ?>" />
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_PROXY_PASS_TIP'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_PROXY_PASS') ?>
		</span>
	    </td>
	    <td>
		<input autocomplete="off" type="password" name="proxy_pass" class="inputbox" value="<?php echo $this->config->get('proxy_pass'); ?>" />
	    </td>
	</tr>
    </table>
</fieldset>

<fieldset class="adminform">
    <legend><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_LOGFILE_HEADER') ?></legend>
    <table class="admintable">
        <tr>
            <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_LOGFILE_published_EXPLAIN'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_LOGFILE_published') ?>
		</span>
            </td>
            <td>
		<?php echo VmHTML::checkbox('enable_logfile', $this->config->get('enable_logfile')); ?>
            </td>
        </tr>
        <tr>
            <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_LOGFILE_NAME_EXPLAIN'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_LOGFILE_NAME') ?>
		</span>
            </td>
            <td>
                <input size="65" type="text" name="logfile_name" class="inputbox" value="<?php echo $this->config->get('logfile_name'); ?>" />
            </td>
        </tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_LOGFILE_LEVEL_EXPLAIN'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_LOGFILE_LEVEL') ?>
		</span>
	    </td>
	    <td>
		<?php if (!defined('COM_VIRTUEMART_LOGFILE_LEVEL')) define('COM_VIRTUEMART_LOGFILE_LEVEL', 'PEAR_LOG_WARNING'); ?>
                <select class="inputbox" name="logfile_level">
		    <option value="PEAR_LOG_TIP" <?php if ($this->config->get('logfile_level') == 'PEAR_LOG_TIP') echo "selected=\"selected\""; ?>><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_LOGFILE_LEVEL_TIP') ?></option>
		    <option value="PEAR_LOG_DEBUG" <?php if ($this->config->get('logfile_level') == 'PEAR_LOG_DEBUG') echo "selected=\"selected\""; ?>><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_LOGFILE_LEVEL_DEBUG') ?></option>
		    <option value="PEAR_LOG_INFO" <?php if ($this->config->get('logfile_level') == 'PEAR_LOG_INFO') echo "selected=\"selected\""; ?>><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_LOGFILE_LEVEL_INFO') ?></option>
		    <option value="PEAR_LOG_NOTICE" <?php if ($this->config->get('logfile_level') == 'PEAR_LOG_NOTICE') echo "selected=\"selected\""; ?>><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_LOGFILE_LEVEL_NOTICE') ?></option>
		    <option value="PEAR_LOG_WARNING" <?php if ($this->config->get('logfile_level') == 'PEAR_LOG_WARNING') echo "selected=\"selected\""; ?>><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_LOGFILE_LEVEL_WARNING') ?></option>
		    <option value="PEAR_LOG_ERR" <?php if ($this->config->get('logfile_level') == 'PEAR_LOG_ERR') echo "selected=\"selected\""; ?>><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_LOGFILE_LEVEL_ERR') ?></option>
		    <option value="PEAR_LOG_CRIT" <?php if ($this->config->get('logfile_level') == 'PEAR_LOG_CRIT') echo "selected=\"selected\""; ?>><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_LOGFILE_LEVEL_CRIT') ?></option>
		    <option value="PEAR_LOG_ALERT" <?php if ($this->config->get('logfile_level') == 'PEAR_LOG_ALERT') echo "selected=\"selected\""; ?>><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_LOGFILE_LEVEL_ALERT') ?></option>
		    <option value="PEAR_LOG_EMERG" <?php if ($this->config->get('logfile_level') == 'PEAR_LOG_EMERG') echo "selected=\"selected\""; ?>><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_LOGFILE_LEVEL_EMERG') ?></option>
		</select>
	    </td>
	</tr>
        <tr>
	    <?php
	    if ($this->config->get('logfile_level') <> '') {
		$logfile_format = $this->config->get('logfile_level');
	    } else {
		$logfile_format = '%{timestamp} %{ident} [%{priority}] [%{remoteip}] [%{username}] %{message}';
	    }
	    ?>
            <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_LOGFILE_FORMAT_EXPLAIN'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_LOGFILE_FORMAT') ?>
		</span>
            </td>
            <td>
                <input size="65" type="text" name="logfile_format" class="inputbox" value="<?php echo $this->config->get('logfile_format') ?>" />
            </td>
        </tr>
        <tr>
	    <td>&nbsp;</td>
	    <td>
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_LOGFILE_FORMAT_EXPLAIN_EXTRA') ?>
	    </td>
        </tr>
    </table>
</fieldset>