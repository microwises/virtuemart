<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage 
* @author
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
<form name="adminForm" id="adminForm" method="post" enctype="multipart/form-data">
<table class="adminform">
	<?php
		if (!is_null($this->productfile->file_name)) { ?>
			<tr>
			<td><?php 
				if ($this->file_type_selected == 'product_images') {
					echo JText::_('VM_FILES_FORM_CURRENT_FULL_IMAGE').':<br />'.JText::_('VM_FILES_FORM_CURRENT_THUMB_IMAGE').':';
				}
				else {
					echo JText::_('VM_FILES_FORM_CURRENT_FILE').':';
				}
				?>
			</td>
			<td>
				<?php
					echo is_null($this->productfile->file_id) ? $this->productfile->file_name.'<br />'.$this->productfile->product_thumb_image : $this->productfile->file_name;
				?>
			</td>
			</tr>
			<?php }?>
	<tr> 
		<td class="labelcell"><?php echo JText::_('VM_FILES_LIST_FILENAME') ?>:</td>
		<td> 
			<input type="file" class="inputbox" name="file_upload" size="75" />
		</td>
	</tr>
	<tr id="filename2">
		<td class="labelcell"><?php echo JText::_('VM_FILES_LIST_FILENAME') ?>:</td>
		<td><?php echo $this->filesselect; ?></td>
	</tr>
	<tr> 
		<td class="labelcell"><?php echo JText::_('VM_FILES_LIST_FILETYPE') ?>:</td>
		<td><?php echo $this->file_types; ?></td>
	</tr>
	<tr> 
		<td class="labelcell"><?php echo JText::_('VM_FILES_FORM_UPLOAD_TO') ?>:</td>
		<td> 
			<input type="radio" class="inputbox" name="upload_dir" id="upload_dir0" checked="checked" value="IMAGEPATH" />
			<label for="upload_dir0"><?php echo JText::_('VM_FILES_FORM_UPLOAD_IMAGEPATH') ?></label><br/><br/>
			<input type="radio" class="inputbox" name="upload_dir" id="upload_dir1" value="FILEPATH" />
			<label for="upload_dir1"><?php echo JText::_('VM_FILES_FORM_UPLOAD_OWNPATH') ?></label>:
			&nbsp;&nbsp;&nbsp;<strong><?php echo JPATH_SITE; ?></strong>&nbsp;<input type="text" class="inputbox" name="file_path" size="25" value="<?php echo DS.'media'.DS; ?>" /><br/><br/>
			<input type="radio" class="inputbox" name="upload_dir" id="upload_dir2" value="DOWNLOADPATH" />
			<label for="upload_dir2"><?php echo JText::_('VM_FILES_FORM_UPLOAD_DOWNLOADPATH') ?></label>
		</td>
	</tr>
	<tr> 
		<td class="labelcell">
			<label for="file_resize_fullimage"><?php echo JText::_('VM_FILES_FORM_RESIZE_IMAGE'); ?></label>
		</td>
		<td> 
			<input type="checkbox" class="inputbox" id="file_resize_fullimage" name="file_resize_fullimage" checked="checked" value="1" />
			<div id="fullsizes">&nbsp;&nbsp;&nbsp;
			<?php echo JText::_('VM_PRODUCT_FORM_HEIGHT');?>: <input type="text" name="fullimage_height" value="500" class="inputbox" />&nbsp;&nbsp;&nbsp;
			<?php echo JText::_('VM_PRODUCT_FORM_WIDTH');?>: <input type="text" name="fullimage_width" value="500" class="inputbox" /></div>
		</td>
	</tr>
	<tr> 
      <td class="labelcell">
      		<label for="file_create_thumbnail"><?php echo JText::_('VM_FILES_FORM_AUTO_THUMBNAIL') ?></label>
      	</td>
      <td> 
        <input type="checkbox" class="inputbox" id="file_create_thumbnail" name="file_create_thumbnail" checked="checked" value="1" />
        <div id="thumbsizes">&nbsp;&nbsp;&nbsp;
        <?php echo JText::_('VM_PRODUCT_FORM_HEIGHT');?>: <input type="text" name="thumbimage_height" value="<?php echo PSHOP_IMG_HEIGHT ?>" class="inputbox" />&nbsp;&nbsp;&nbsp;
        <?php echo JText::_('VM_PRODUCT_FORM_WIDTH');?>: <input type="text" name="thumbimage_width" value="<?php echo PSHOP_IMG_WIDTH ?>" class="inputbox" /></div>
        </td>
    </tr>
	<tr> 
		<td class="labelcell">
			<label for="file_published"><?php echo JText::_('VM_FILES_FORM_FILE_PUBLISHED') ?></label>
		</td>
		<td> 
			<input type="checkbox" class="inputbox" id="file_published" name="file_published" value="1" <?php if ($this->productfile->published) echo "checked=\"checked\"" ?> size="16" />
		</td>
	</tr>
	<tr> 
		<td class="labelcell"><?php echo JText::_('VM_FILES_FORM_FILE_TITLE') ?>:</td>
		<td> 
			<input type="text" class="inputbox" name="file_title" size="32" value="<?php echo $this->productfile->file_title; ?>" />
			</td>
	</tr>
	<tr> 
		<td class="labelcell"><?php echo JText::_('VM_FILES_FORM_FILE_URL') ?>:</td>
		<td> 
			<input type="text" class="inputbox" name="file_url" value="<?php $this->productfile->file_url ?>" size="75" />
		</td>
	</tr>
</table>
<!-- Hidden Fields -->
<?php
	if (!is_null($this->productfile->file_product_id)) echo '<input type="hidden" name="product_id" value="'.$this->productfile->file_product_id.'" />';
	if (!is_null($this->productfile->file_id)) echo '<input type="hidden" name="product_id" value="'.$this->productfile->file_id.'" />';
?>
<input type="hidden" name="task" value="" />
<input type="hidden" name="option" value="com_virtuemart" />
<input type="hidden" name="pshop_mode" value="admin" />
<input type="hidden" name="view" value="media" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
</form>
<script type="text/javascript">
function checkThumbnailing() {
	
  if( document.adminForm.file_type[0].selected || document.adminForm.file_type[1].selected 
  	|| document.adminForm.file_type[2].selected || document.adminForm.file_type[4].selected
  ) {
  	document.adminForm.file_create_thumbnail.checked=true;
    document.adminForm.file_create_thumbnail.disabled=false;
    document.adminForm.file_resize_fullimage.checked=true;
    document.adminForm.file_resize_fullimage.disabled=false;
    jQuery("#thumbsizes").show();
    jQuery("#fullsizes").show();
    jQuery("#filename2").hide();
    
    if( document.adminForm.file_type[4].selected == false ) {
    	
    	if( document.adminForm.file_type[1].selected ) { // product full image selected
    		document.adminForm.file_create_thumbnail.checked=false;
    		document.adminForm.file_create_thumbnail.disabled=true;
    		jQuery("#thumbsizes").hide();
    	}
    	if( document.adminForm.file_type[2].selected ) { // product thumb image selected
    		document.adminForm.file_resize_fullimage.disabled=true;
    		jQuery("#fullsizes").hide();
    	}
	  	document.adminForm.upload_dir[0].disabled=false;
		document.adminForm.upload_dir[0].checked=true;
		document.adminForm.upload_dir[1].disabled=true;
		document.adminForm.upload_dir[2].disabled=true;
		document.adminForm.file_published.disabled=false;    	
    }
    else { // additional image selected
	  	document.adminForm.upload_dir[0].disabled=false;
		document.adminForm.upload_dir[0].checked=true;
		document.adminForm.upload_dir[1].disabled=false;
		document.adminForm.upload_dir[2].disabled=false;
		document.adminForm.file_published.disabled=false;
    }
  }
  else {
  	document.adminForm.file_create_thumbnail.checked=false;
    document.adminForm.file_create_thumbnail.disabled=true;
    document.adminForm.file_resize_fullimage.checked=false;
    document.adminForm.file_resize_fullimage.disabled=true;
    jQuery("#thumbsizes").hide();
    jQuery("#fullsizes").hide();
    
  	if( document.adminForm.file_type[5].selected == true) { // additional file
  		jQuery("#filename2").hide();
  		document.adminForm.upload_dir[0].disabled=true;
	    document.adminForm.upload_dir[0].checked=false;
	    document.adminForm.upload_dir[1].checked=true;
	    document.adminForm.upload_dir[1].disabled=false;
	    document.adminForm.upload_dir[2].checked=false;
	    document.adminForm.upload_dir[2].disabled=false;
	    document.adminForm.file_published.disabled=false;
	}
	else {
		// pay-download selected
		jQuery("#filename2").show();
  		document.adminForm.upload_dir[0].disabled=true;
	    document.adminForm.upload_dir[1].disabled=true;
	    document.adminForm.upload_dir[2].disabled=false;
	    document.adminForm.upload_dir[2].checked=true;
	    document.adminForm.file_published.disabled=true;
	}
	
	/* Downloadable products should not be given file title option */
	if( document.adminForm.file_type[3].selected == true) { // downloadable product
		document.adminForm.file_title.disabled=true;
	}
	else {
		document.adminForm.file_title.disabled=false;
	}
  }
}
checkThumbnailing();
</script>
<?php AdminMenuHelper::endAdminArea(); ?> 
