<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Manufacturer Category
* @author Patrick Kohl
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
	<legend><?php echo JText::_('COM_VIRTUEMART_MANUFACTURER_CATEGORY_DETAILS'); ?></legend>
	<table class="admintable">
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('COM_VIRTUEMART_MANUFACTURER_CATEGORY_NAME'); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="mf_category_name" id="mf_category_name" size="50" value="<?php echo $this->manufacturerCategory->mf_category_name; ?>" />
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('COM_VIRTUEMART_MANUFACTURER_CATEGORY_DESC'); ?>:
				</label>
			</td>
			<td>
				<textarea rows="20" cols="50" name="mf_category_desc" id="mf_category_desc"><?php echo $this->manufacturerCategory->mf_category_desc; ?></textarea>


			</td>
		</tr>

		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('COM_VIRTUEMART_PUBLISH'); ?>:
				</label>
			</td>
			<td>
				<fieldset class="radio">
				<?php echo JHTML::_('select.booleanlist',  'published', 'class="inputbox"', $this->manufacturerCategory->published); ?>
				</fieldset>
			</td>
		</tr>


	</table>
	</fieldset>
</div>

	<input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="mf_category_id" value="<?php echo $this->manufacturerCategory->mf_category_id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="controller" value="manufacturerCategory" />
</form>

<?php AdminMenuHelper::endAdminArea(); ?>