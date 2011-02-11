<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Manufacturer
* @author vhv_alex
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

AdminMenuHelper::startAdminArea();
?>

<form action="index.php" method="post" name="adminForm">


<div class="col50">
	<fieldset class="adminform">
	<legend><?php echo JText::_( 'VM_MANUFACTURER_DETAILS' ); ?></legend>
	<table class="admintable">
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_( 'VM_MANUFACTURER_NAME' ); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="mf_name" id="mf_name" size="50" value="<?php echo $this->manufacturer->mf_name; ?>" />
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('VM_MANUFACTURER_CATEGORY'); ?>:
				</label>
			</td>
			<td>
				<?php
				echo JHTML::_('Select.genericlist', $this->manufacturerCategories, 'mf_category_id', '', 'mf_category_id', 'mf_category_name', $this->manufacturer->mf_category_id); ?>
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('VM_MANUFACTURER_URL'); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="mf_url" id="mf_url" size="50" value="<?php echo $this->manufacturer->mf_url; ?>" />
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('VM_PUBLISH'); ?>:
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
				<label for="title">
					<?php echo JText::_('VM_MANUFACTURER_EMAIL'); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="mf_email" id="mf_email" size="50" value="<?php echo $this->manufacturer->mf_email; ?>" />
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('VM_MANUFACTURER_DESC'); ?>:
				</label>
			</td>
			<td>

				<textarea rows="10" cols="30" name="mf_desc" id="mf_desc"><?php echo $this->manufacturer->mf_desc; ?></textarea>
			</td>
		</tr>

	</table>
	</fieldset>
</div>

	<input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="manufacturer_id" value="<?php echo $this->manufacturer->manufacturer_id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="controller" value="manufacturer" />
</form>

<?php AdminMenuHelper::endAdminArea(); ?>