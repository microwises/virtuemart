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
* @version $Id$
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

AdminMenuHelper::startAdminArea();
$pane = JPane::getInstance('tabs', array('startOffset' => 0));
$editor = JFactory::getEditor();
?>

<form enctype="multipart/form-data" action="index.php" method="post" name="adminForm">
<?php
	echo $pane->startPane( 'pane' );
	echo $pane->startPanel(JText::_('COM_VIRTUEMART_DESCRIPTION'), 'desc_tab');
?>
<div class="col50">
	<fieldset class="adminform">
	<legend><?php echo JText::_('COM_VIRTUEMART_MANUFACTURER').' '. JText::_('COM_VIRTUEMART_DETAILS'); ?></legend>
	<table class="admintable">
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_($this->viewName.' '. JText::_('COM_VIRTUEMART_NAME')); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="mf_name" id="mf_name" size="60" value="<?php echo $this->manufacturer->mf_name; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="slug">
					<?php echo $this->viewName.' '. JText::_('COM_VIRTUEMART_SLUG'); ?>:
				</label>
			</td>
			<td>
				<input type="text" name="slug" id="slug" size="60" value="<?php echo $this->manufacturer->slug; ?>" class="inputbox" />
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo $this->viewName.' '. JText::_('COM_VIRTUEMART_CATEGORY'); ?>:
				</label>
			</td>
			<td>
				<?php
				echo JHTML::_('Select.genericlist', $this->manufacturerCategories, 'virtuemart_manufacturercategories_id', '', 'virtuemart_manufacturercategories_id', 'mf_category_name', $this->manufacturer->virtuemart_manufacturercategories_id); ?>
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo $this->viewName.' '. JText::_('COM_VIRTUEMART_URL'); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="mf_url" id="mf_url" size="60" value="<?php echo $this->manufacturer->mf_url; ?>" />
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
				<?php echo JHTML::_('select.booleanlist',  'published', 'class="inputbox"', $this->manufacturer->published); ?>
				</fieldset>
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_($this->viewName.' '. JText::_('COM_VIRTUEMART_EMAIL')); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="mf_email" id="mf_email" size="60" value="<?php echo $this->manufacturer->mf_email; ?>" />
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_($this->viewName.' '. JText::_('COM_VIRTUEMART_DESCRIPTION')); ?>:
				</label>
			</td>
			<td>
				<?php echo $editor->display('mf_desc', $this->manufacturer->mf_desc, '100%', '300', '50', '8', array('pagebreak', 'readmore'));?>
			</td>
		</tr>

	</table>
	</fieldset>
</div>
<?php
	echo $pane->endPanel();
	echo $pane->startPanel(JText::_('COM_VIRTUEMART_IMAGES'), 'images_tab');
?>
<div class="col50">
	<div class="selectimage">
		<?php echo $this->manufacturer->images[0]->displayFilesHandler($this->manufacturer->virtuemart_media_id,'manufacturer'); ?>
	</div>
</div>
<?php
	echo $pane->endPanel();
	echo $pane->endPane();
?>
	<input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="virtuemart_manufacturer_id" value="<?php echo $this->manufacturer->virtuemart_manufacturer_id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="controller" value="manufacturer" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
<script type="text/javascript">
function toggleDisable( elementOnChecked, elementDisable, disableOnChecked ) {
	try {
		if( !disableOnChecked ) {
			if(elementOnChecked.checked==true) {
				elementDisable.disabled=false;
			}
			else {
				elementDisable.disabled=true;
			}
		}
		else {
			if(elementOnChecked.checked==true) {
				elementDisable.disabled=true;
			}
			else {
				elementDisable.disabled=false;
			}
		}
	}
	catch( e ) {}
}

function toggleFullURL() {
	if( jQuery('#manufacturer_full_image_url').val().length>0) document.adminForm.manufacturer_full_image_action[1].checked=false;
	else document.adminForm.manufacturer_full_image_action[1].checked=true;
	toggleDisable( document.adminForm.manufacturer_full_image_action[1], document.adminForm.manufacturer_thumb_image_url, true );
	toggleDisable( document.adminForm.manufacturer_full_image_action[1], document.adminForm.manufacturer_thumb_image, true );
}
</script>
<?php AdminMenuHelper::endAdminArea(); ?>