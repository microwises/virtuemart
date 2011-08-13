<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Manufacturer
* @author Patrick Kohl
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: edit.php 3617 2011-07-05 12:55:12Z enytheme $
*/


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access'); 

$editor = JFactory::getEditor();
?>

<div class="col50">
	<fieldset>
	<legend><?php echo JText::_('COM_VIRTUEMART_MANUFACTURER_DETAILS'); ?></legend>
	<table class="admintable">
		<tr>
			<td width="110" class="key">
				<label for="mf_name">
					<?php echo  JText::_('COM_VIRTUEMART_MANUFACTURER_NAME'); ?>
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="mf_name" id="mf_name" size="60" value="<?php echo $this->manufacturer->mf_name; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="slug">
					<?php echo $this->viewName.' '. JText::_('COM_VIRTUEMART_SLUG'); ?>
				</label>
			</td>
			<td>
				<input type="text" name="slug" id="slug" size="60" value="<?php echo $this->manufacturer->slug; ?>" class="inputbox" />
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="mf_category_name">
					<?php echo  JText::_('COM_VIRTUEMART_MANUFACTURER_CATEGORY'); ?>
				</label>
			</td>
			<td>
				<?php
				echo JHTML::_('Select.genericlist', $this->manufacturerCategories, 'virtuemart_manufacturercategories_id', '', 'virtuemart_manufacturercategories_id', 'mf_category_name', $this->manufacturer->virtuemart_manufacturercategories_id); ?>
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="mf_url">
					<?php echo   JText::_('COM_VIRTUEMART_MANUFACTURER_URL'); ?>
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="mf_url" id="mf_url" size="60" value="<?php echo $this->manufacturer->mf_url; ?>" />
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="published">
					<?php echo JText::_('COM_VIRTUEMART_PUBLISH'); ?>
				</label>
			</td>
			<td>
				<fieldset class="radio">
				<?php echo JHTML::_('select.booleanlist',  'published', 'class="inputbox"', $this->manufacturer->published); ?>
				</fieldset>
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="mf_email">
					<?php echo  JText::_('COM_VIRTUEMART_MANUFACTURER_EMAIL'); ?>
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="mf_email" id="mf_email" size="60" value="<?php echo $this->manufacturer->mf_email; ?>" />
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="mf_desc">
					<?php echo  JText::_('COM_VIRTUEMART_MANUFACTURER_DESCRIPTION'); ?>
				</label>
			</td>
			<td>
				<?php echo $editor->display('mf_desc', $this->manufacturer->mf_desc, '100%', '300', '50', '8', array('pagebreak', 'readmore'));?>
			</td>
		</tr>

	</table>
	</fieldset>
</div>