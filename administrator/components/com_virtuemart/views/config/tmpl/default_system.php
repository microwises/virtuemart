<?php
defined('_JEXEC') or die('Restricted access'); 
?> 
<br />
<fieldset class="adminform">
    <legend><?php echo JText::_('VM_ADMIN_CFG_SYSTEM_SETTINGS') ?></legend>
    <table class="admintable">
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_URLSECURE_EXPLAIN'); ?>"/>
		<?php echo JText::_('VM_ADMIN_CFG_URLSECURE') ?>
	    </td>
	    <td>
		<input size="40" type="text" name="secureurl" class="inputbox" value="<?php echo JText::_($this->config->get('secureurl')); ?>" />
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_DEBUG_EXPLAIN'); ?>"/>
		<?php echo JText::_('VM_ADMIN_CFG_DEBUG') ?>
	    </td>
	    <td>
		<?php
		$checked = '';
		if ($this->config->get('debug')) $checked = 'checked="checked"'; ?>
		<input type="checkbox" name="debug" value="1" <?php echo $checked; ?> />
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_DEBUG_IP_ENABLED_EXPLAIN'); ?>"/>
		<?php echo JText::_('VM_ADMIN_CFG_DEBUG_IP_ENABLED') ?>
	    </td>
	    <td>
		<?php
		$checked = '';
		if ($this->config->get('debug_ip_enabled')) $checked = 'checked="checked"'; ?>
		<input type="checkbox" name="debug_ip_enabled" value="1" <?php echo $checked; ?> />
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_DEBUG_IP_ADDRESS_EXPLAIN'); ?>"/>
		<?php echo JText::_('VM_ADMIN_CFG_DEBUG_IP_ADDRESS') ?>
	    </td>
	    <td>
		<input size="20" type="text" name="debug_ip_address" class="inputbox" value="<?php echo $this->config->get('debug_ip_address'); ?>" />
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<?php echo JText::_('VM_ADMIN_CFG_COOKIE_CHECK') ?>
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_COOKIE_CHECK_EXPLAIN'); ?>"/>
	    </td>
	    <td>
		<?php
		$checked = '';
		if ($this->config->get('enable_cookie_check')) $checked = 'checked="checked"'; ?>
		<input type="checkbox" name="enable_cookie_check" value="1" <?php echo $checked; ?> />
	    </td>
	</tr>
    </table>
</fieldset>

<fieldset class="adminform">
    <legend><?php echo JText::_('VM_ADMIN_CFG_USER_REGISTRATION_SETTINGS') ?></legend>
    <table class="admintable">
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_CFG_REGISTRATION_TYPE_TIP'); ?>"/>
		    <?php echo JText::_('VM_CFG_REGISTRATION_TYPE') ?>
	    </td>
	    <td>
		<?php
		$options = array();
		$options[] = JHTML::_('select.option', 'NORMAL_REGISTRATION', JText::_('VM_CFG_REGISTRATION_TYPE_NORMAL_REGISTRATION') );
		$options[] = JHTML::_('select.option', 'SILENT_REGISTRATION', JText::_('VM_CFG_REGISTRATION_TYPE_SILENT_REGISTRATION'));
		$options[] = JHTML::_('select.option', 'OPTIONAL_REGISTRATION', JText::_('VM_CFG_REGISTRATION_TYPE_OPTIONAL_REGISTRATION'));
		$options[] = JHTML::_('select.option', 'NO_REGISTRATION', JText::_('VM_CFG_REGISTRATION_TYPE_NO_REGISTRATION'));
		echo JHTML::_('Select.genericlist', $options, 'registration_type', 'size=1');
		?>
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_SHOW_REMEMBER_ME_BOX_TIP'); ?>"/>
		    <?php echo JText::_('VM_SHOW_REMEMBER_ME_BOX') ?>
	    </td>
	    <td>
		<?php
		$checked = '';
		if ($this->config->get('show_remember_me_box')) $checked = 'checked="checked"'; ?>
		<input type="checkbox" name="show_remember_me_box" value="1" <?php echo $checked; ?> />
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<?php echo 'Joomla! ' . JText::_('VM_ADMIN_CFG_ALLOW_REGISTRATION'); ?>
	    </td>
	    <td><?php
		if ($this->joomlaconfig->getCfg('allowUserRegistration') == '1' ) {
		    echo '<span style="color:green;">'.JText::_('VM_ADMIN_CFG_YES').'</span>';
		}
		else {
		    echo '<span style="color:red;font-weight:bold;">'.JText::_('VM_ADMIN_CFG_NO').'</span>';
		}
		$link = JROUTE::_('index.php?option=com_config');
		echo JHTML::_('link', $link, '&nbsp;['.JText::_('VM_UPDATE').']');
		?></td>
	</tr>
	<tr>
	    <td class="key">
		<?php echo 'Joomla! ' . JText::_('VM_ADMIN_CFG_ACCOUNT_ACTIVATION'); ?>
	    </td>
	    <td><?php
		if ($this->joomlaconfig->getCfg('useractivation') == '0' ) {
		    echo '<span style="color:green;">'.JText::_('VM_ADMIN_CFG_NO').'</span>';
		}
		else {
		    echo '<span style="color:red;font-weight:bold;">'.JText::_('VM_ADMIN_CFG_YES').'</span>';
		}
		$link = JROUTE::_('index.php?option=com_config');
		echo JHTML::_('link', $link, '&nbsp['.JText::_('VM_UPDATE').']');
		?>
	    </td>
	</tr>
    </table>
</fieldset>

<fieldset class="adminform">
    <legend><?php echo JText::_('VM_ADMIN_CFG_PROXY_SETTINGS') ?></legend>
    <table class="admintable">
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_PROXY_URL_TIP'); ?>"/>
		<?php echo JText::_('VM_ADMIN_CFG_PROXY_URL') ?>
	    </td>
	    <td>
		<input size="40" type="text" name="proxy_url" class="inputbox" value="<?php JText::_($this->config->get('proxy_url')); ?>" />
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_PROXY_PORT_TIP'); ?>"/>
		<?php echo JText::_('VM_ADMIN_CFG_PROXY_PORT') ?>
	    </td>
	    <td>
		<input type="text" name="proxy_port" class="inputbox" value="<?php echo JText::_($this->config->get('proxy_port')); ?>" />
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_PROXY_USER_TIP'); ?>"/>
		<?php echo JText::_('VM_ADMIN_CFG_PROXY_USER') ?>
	    </td>
	    <td>
		<input type="text" name="proxy_user" class="inputbox" value="<?php echo JText::_($this->config->get('proxy_user'));
		       ; ?>" />
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_PROXY_PASS_TIP'); ?>"/>
		<?php echo JText::_('VM_ADMIN_CFG_PROXY_PASS') ?>
	    </td>
	    <td>
		<input autocomplete="off" type="password" name="proxy_pass" class="inputbox" value="<?php echo JText::_($this->config->get('proxy_pass')); ?>" />
	    </td>
	</tr>
    </table>
</fieldset>

<fieldset class="adminform">
    <legend><?php echo JText::_('VM_ADMIN_CFG_LOGFILE_HEADER') ?></legend>
    <table class="admintable">
        <tr>
            <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_LOGFILE_ENABLED_EXPLAIN'); ?>"/>
		<?php echo JText::_('VM_ADMIN_CFG_LOGFILE_ENABLED') ?>
            </td>
            <td>
		<?php
		$checked = '';
		if ($this->config->get('enable_logfile')) $checked = 'checked="checked"'; ?>
		<input type="checkbox" name="enable_logfile" value="1" <?php echo $checked; ?> />
            </td>
        </tr>
        <tr>
            <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_LOGFILE_NAME_EXPLAIN'); ?>"/>
		<?php echo JText::_('VM_ADMIN_CFG_LOGFILE_NAME') ?>
            </td>
            <td>
                <input size="65" type="text" name="logfile_name" class="inputbox" value="<?php echo $this->config->get('logfile_name'); ?>" />
            </td>
        </tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_LOGFILE_LEVEL_EXPLAIN'); ?>"/>
		<?php echo JText::_('VM_ADMIN_CFG_LOGFILE_LEVEL') ?>
	    </td>
	    <td>
		<?php if (!defined('VM_LOGFILE_LEVEL')) define('VM_LOGFILE_LEVEL', 'PEAR_LOG_WARNING'); ?>
                <select class="inputbox" name="logfile_level">
		    <option value="PEAR_LOG_TIP" <?php if ($this->config->get('logfile_level') == 'PEAR_LOG_TIP') echo "selected=\"selected\""; ?>><?php echo JText::_('VM_ADMIN_CFG_LOGFILE_LEVEL_TIP') ?></option>
		    <option value="PEAR_LOG_DEBUG" <?php if ($this->config->get('logfile_level') == 'PEAR_LOG_DEBUG') echo "selected=\"selected\""; ?>><?php echo JText::_('VM_ADMIN_CFG_LOGFILE_LEVEL_DEBUG') ?></option>
		    <option value="PEAR_LOG_INFO" <?php if ($this->config->get('logfile_level') == 'PEAR_LOG_INFO') echo "selected=\"selected\""; ?>><?php echo JText::_('VM_ADMIN_CFG_LOGFILE_LEVEL_INFO') ?></option>
		    <option value="PEAR_LOG_NOTICE" <?php if ($this->config->get('logfile_level') == 'PEAR_LOG_NOTICE') echo "selected=\"selected\""; ?>><?php echo JText::_('VM_ADMIN_CFG_LOGFILE_LEVEL_NOTICE') ?></option>
		    <option value="PEAR_LOG_WARNING" <?php if ($this->config->get('logfile_level') == 'PEAR_LOG_WARNING') echo "selected=\"selected\""; ?>><?php echo JText::_('VM_ADMIN_CFG_LOGFILE_LEVEL_WARNING') ?></option>
		    <option value="PEAR_LOG_ERR" <?php if ($this->config->get('logfile_level') == 'PEAR_LOG_ERR') echo "selected=\"selected\""; ?>><?php echo JText::_('VM_ADMIN_CFG_LOGFILE_LEVEL_ERR') ?></option>
		    <option value="PEAR_LOG_CRIT" <?php if ($this->config->get('logfile_level') == 'PEAR_LOG_CRIT') echo "selected=\"selected\""; ?>><?php echo JText::_('VM_ADMIN_CFG_LOGFILE_LEVEL_CRIT') ?></option>
		    <option value="PEAR_LOG_ALERT" <?php if ($this->config->get('logfile_level') == 'PEAR_LOG_ALERT') echo "selected=\"selected\""; ?>><?php echo JText::_('VM_ADMIN_CFG_LOGFILE_LEVEL_ALERT') ?></option>
		    <option value="PEAR_LOG_EMERG" <?php if ($this->config->get('logfile_level') == 'PEAR_LOG_EMERG') echo "selected=\"selected\""; ?>><?php echo JText::_('VM_ADMIN_CFG_LOGFILE_LEVEL_EMERG') ?></option>
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
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_LOGFILE_FORMAT_EXPLAIN'); ?>"/>
		<?php echo JText::_('VM_ADMIN_CFG_LOGFILE_FORMAT') ?>
            </td>
            <td>
                <input size="65" type="text" name="logfile_format" class="inputbox" value="<?php echo $this->config->get('logfile_format') ?>" />
            </td>
        </tr>
        <tr>
	    <td>&nbsp;</td>
	    <td>
		<?php echo JText::_('VM_ADMIN_CFG_LOGFILE_FORMAT_EXPLAIN_EXTRA') ?>
	    </td>
        </tr>
    </table>
</fieldset>