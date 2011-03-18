<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Config
* @author Max Milbers
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: default_seo.php 2387 2010-05-05 16:24:59Z oscar $
*/
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');  
?>
<br />
<fieldset class="adminform">
    <legend><?php echo JText::_('VM_ADMIN_CFG_SEO_SETTINGS') ?></legend>
    <table class="admintable">
	<tr>
	    <td class="key">
		<?php echo JHTML::tooltip(JText::_('VM_ADMIN_CFG_SEO_ENABLE_TIP'), JText::_('VM_ADMIN_CFG_SEO_ENABLE_TIP')); ?>
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_SEO_ENABLE_TIP'); ?>">
		<label for="conf_VM_SEO_ENABLED"><?php echo JText::_('VM_ADMIN_CFG_SEO_ENABLE') ?></label>
		</span>
	    </td>
	    <td>
		<?php echo VmHTML::checkbox('seo_enabled', $this->config->get('seo_enabled')); ?>
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<?php echo JHTML::tooltip(JText::_('VM_ADMIN_CFG_SEO_TRANSLATE_TIP'), JText::_('VM_ADMIN_CFG_SEO_TRANSLATE_TIP')); ?>
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_SEO_TRANSLATE_TIP'); ?>">
		<label for="conf_VM_SEO_TRANSLATE"><?php echo JText::_('VM_ADMIN_CFG_SEO_TRANSLATE') ?></label>
		</span>
	    </td>
		<td>
			<?php echo VmHTML::checkbox('seo_translate', $this->config->get('seo_translate')); ?>
		</td>
	</tr>
	<tr>
	    <td class="key">
		<?php echo JHTML::tooltip(JText::_('VM_ADMIN_CFG_SEO_USE_ID_TIP'), JText::_('VM_ADMIN_CFG_SEO_USE_ID_TIP')); ?>
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_SEO_USE_ID_TIP'); ?>">
		<label for="conf_VM_SEO_USE_ID"><?php echo JText::_('VM_ADMIN_CFG_SEO_USE_ID_TIP') ?></label>
		</span>
	    </td>
		<td>
			<?php echo VmHTML::checkbox('seo_use_id', $this->config->get('seo_use_id')); ?>
		</td>
	</tr>
 </table>
</fieldset>