<?php 
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id: product.file_form.php 1760 2009-05-03 22:58:57Z Aravot $
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2004-2008 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/
/* ROLANDD: MVC TEST START */
require(JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'product_files.php');
$productfiles = new VirtuemartModelProduct_Files();
$productfile = $productfiles->getImageDetails();

$file_id = JRequest::getVar('file_id' );
$product_id= JRequest::getInt('product_id');
$option = JRequest::getVar('option', 'com_virtuemart');
$selected_type = array( "selected=\"selected\"", '', '', '', '','' );

require(JPATH_COMPONENT_ADMINISTRATOR.DS.'views'.DS.'product_files'.DS.'tmpl'.DS.'product_files_edit.php');

/* ROLANDD: MVC TEST END */
if (0) {
?>
<!-- <a href="#" onclick="vm_windowClose();">Close window</a> -->
<?php
mm_showMyFileName( __FILE__ );

require_once( CLASSPATH . "ps_product_files.php" );

$file_id= JRequest::getVar(  'file_id' );
$product_id= JRequest::getVar(  'product_id');
$option = empty($option)?JRequest::getVar(  'option', 'com_virtuemart'):$option;

$selected_type = Array();

$q = "SELECT product_id, product_name, product_full_image as file_name, product_thumb_image as file_name2 FROM #__{vm}_product WHERE product_id=".intval($product_id); 
$db->query($q);  
$db->next_record();
$hasProductImages = $db->f('file_name2') != '';
$selected_type = array( "selected=\"selected\"", '', '', '', '','' );

$product_name = '<a href="'.$_SERVER['PHP_SELF'].'?option='.$option.'&amp;product_id='.$product_id.'&amp;page=product.product_form">'.$db->f("product_name").'</a>';

$title ='<img src="'. $mosConfig_live_site .'/administrator/images/mediamanager.png" width="48" height="48" align="center" alt="Product List" border="0" />'
		. JText::_('VM_FILES_FORM') . ": ". $product_name ;


$attribute_id = '';
if( !empty($file_id) ) {
	if( (int)$file_id > 0 ) {
		$isProductDownload = ps_product_files::isProductDownloadFile( $file_id, $product_id );
	  	$q = "SELECT file_name,file_url,file_is_image,file_published,file_title 
			  FROM #__{vm}_product_files 
			  WHERE file_id='$file_id'"; 
	  	$db->query($q);
	  	if( $db->next_record() ) {
	  		if( $isProductDownload ) {
	  			$dbf = new ps_DB();
	  			$dbf->query('SELECT attribute_id FROM `#__{vm}_product_attribute` WHERE attribute_name=\'download\' AND attribute_value=\''.$db->f('file_title').'\' AND product_id=\''.$product_id.'\'');
	  			$dbf->next_record();
	  			$attribute_id = $dbf->f('attribute_id');
	  			vmCommonHTML::setSelectedArray( $selected_type, 3, 'selected', array(0,1,2,4) );
	  		}
	  		else {
				$index = $db->f("file_is_image")==1 ? 4 : 5;
				$disableArr = $db->f("file_is_image")==1 ? array(3,5) : array(0,1,2,4);
				vmCommonHTML::setSelectedArray( $selected_type, $index, 'selected', $disableArr );
	  		}
	  	}
	}
	else {
		vmCommonHTML::setSelectedArray( $selected_type, 0, 'selected', array(3,5) );
	}
}
else {
	if( $hasProductImages ) {
		vmCommonHTML::setSelectedArray( $selected_type, 4, 'selected', array(0,1,2) );
	}
	$isProductDownload = false;
	$default["file_title"] = $db->f('product_name');
	$default["file_published"] = "1";
	unset( $db->record );
}

//First create the object and let it print a form heading
$formObj = &new formFactory( $title );
//Then Start the form
$formObj->startForm( 'adminForm', 'enctype="multipart/form-data"');

?>
<br />
  <table class="adminform">
  <?php 
  	if( $file_id ) { ?>
    <tr> 
      <td class="labelcell"><?php echo JText::_('VM_FILES_FORM_CURRENT_FILE') ?>:</td>
      <td><?php 
      	echo $file_id == 'product_images' ? 
      		$db->f("file_name").'<br />'.$db->f("file_name2")
      		: $db->f("file_name");
      		?></td>
    </tr>
    <?php 
  	} ?>
    <tr> 
      <td class="labelcell"><?php echo JText::_('VM_FILES_LIST_FILENAME') ?>:</td>
      <td> 
        <input type="file" class="inputbox" name="file_upload" size="32" />
      </td>
    </tr>
    <tr id="filename2">
    	<td class="labelcell"><?php echo JText::_('VM_FILES_LIST_FILENAME') ?>:</td>
    	<td><?php 
    		$downloadRootFiles = vmReadDirectory(DOWNLOADROOT, '.', true);
    		$mappedDownloadRootFiles = array();
    		foreach( $downloadRootFiles as $file ) {
    			if( is_file(DOWNLOADROOT.$file) && basename($file) != 'index.html' && basename($file) != '.htaccess') {
    				$mappedDownloadRootFiles[$file] = $file;
    			}
    		}
	    	echo $ps_html->selectList('downloadable_file', basename($db->f("file_name")), $mappedDownloadRootFiles ) 
    		?>
    	</td>
    </tr>
    <tr> 
      <td class="labelcell"><?php echo JText::_('VM_FILES_LIST_FILETYPE') ?>:</td>
      <td> 
        <select name="file_type" onchange="checkThumbnailing();" class="inputbox">
        	<option value="product_images" <?php echo $selected_type[0] ?>><?php echo JText::_('VM_FILES_FORM_PRODUCT_IMAGE'); ?></option>
	        <option value="product_full_image" <?php echo $selected_type[1] ?>><?php echo JText::_('VM_PRODUCT_FORM_FULL_IMAGE') ?></option>
	        <option value="product_thumb_image" <?php echo $selected_type[2] ?>><?php echo JText::_('VM_PRODUCT_FORM_THUMB_IMAGE') ?></option>
	        <option value="downloadable_file" <?php echo $selected_type[3] ?>><?php echo JText::_('VM_FILES_FORM_DOWNLOADABLE') ?></option>
	        <option value="image" <?php echo $selected_type[4] ?>><?php echo JText::_('VM_FILES_FORM_IMAGE') ?></option>
	        <option value="file" <?php echo $selected_type[5] ?>><?php echo JText::_('VM_FILES_FORM_FILE') ?></option>
        </select>
      </td>
    </tr>
    <tr> 
      <td class="labelcell"><?php echo JText::_('VM_FILES_FORM_UPLOAD_TO') ?>:</td>
      <td> 
        <input type="radio" class="inputbox" name="upload_dir" id="upload_dir0" checked="checked" value="IMAGEPATH" />
        <label for="upload_dir0"><?php echo JText::_('VM_FILES_FORM_UPLOAD_IMAGEPATH') ?></label><br/><br/>
        <input type="radio" class="inputbox" name="upload_dir" id="upload_dir1" value="FILEPATH" />
        <label for="upload_dir1"><?php echo JText::_('VM_FILES_FORM_UPLOAD_OWNPATH') ?></label>:
        &nbsp;&nbsp;&nbsp;<strong><?php echo $mosConfig_absolute_path ?></strong>&nbsp;<input type="text" class="inputbox" name="file_path" size="25" value="/media/" /><br/><br/>
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
        <input type="checkbox" class="inputbox" id="file_published" name="file_published" value="1" <?php if($db->sf("file_published")==1) echo "checked=\"checked\"" ?> size="16" />
      </td>
    </tr>
    <tr> 
      <td class="labelcell"><?php echo JText::_('VM_FILES_FORM_FILE_TITLE') ?>:</td>
      <td> 
        <input type="text" class="inputbox" name="file_title" size="32" value="<?php echo shopMakeHtmlSafe( $db->sf("file_title") ) ?>" />
      </td>
    </tr>
    <tr> 
      <td class="labelcell"><?php echo JText::_('VM_FILES_FORM_FILE_URL') ?>:</td>
      <td> 
        <input type="text" class="inputbox" name="file_url" value="<?php $db->sp("file_url") ?>" size="32" />
      </td>
    </tr>
    <tr >
      <td colspan="2" align="center">&nbsp;</td>
    </tr>
  </table>
<?php
// Add necessary hidden fields
$formObj->hiddenField( 'file_id', $file_id );
$formObj->hiddenField( 'product_id', $product_id );
$formObj->hiddenField( 'attribute_id', $attribute_id );

$funcname = empty($file_id) ? "uploadProductFile" : "updateProductFile";

// Write your form with mixed tags and text fields
// and finally close the form:
$formObj->finishForm( $funcname, $modulename.'.file_list', $option );
?>
<script type="text/javascript">
function submitbutton(pressbutton) {
	document.adminForm.ajax_request.value='0';
	submitform(pressbutton);
}
function vm_windowClose() {
	vm_submitButton('cancel', 'adminForm', 'product.file_list');
}
function checkThumbnailing() {
	
  if( document.adminForm.file_type[0].selected || document.adminForm.file_type[1].selected 
  	|| document.adminForm.file_type[2].selected || document.adminForm.file_type[4].selected
  ) {
  	document.adminForm.file_create_thumbnail.checked=true;
    document.adminForm.file_create_thumbnail.disabled=false;
    document.adminForm.file_resize_fullimage.checked=true;
    document.adminForm.file_resize_fullimage.disabled=false;
    Ext.get('thumbsizes').show(true);
    Ext.get('fullsizes').show(true);
    Ext.get('filename2').hide(true);

    if( document.adminForm.file_type[4].selected == false ) {
    	
    	if( document.adminForm.file_type[1].selected ) { // product full image selected
    		document.adminForm.file_create_thumbnail.disabled=true;
    		Ext.get('thumbsizes').hide(true)
    	}
    	if( document.adminForm.file_type[2].selected ) { // product thumb image selected
    		document.adminForm.file_resize_fullimage.disabled=true;
    		Ext.get('fullsizes').hide(true);
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
    Ext.get('thumbsizes').hide(true);
    Ext.get('fullsizes').hide(true);
    
  	if( document.adminForm.file_type[5].selected == true) { // additional file
  		Ext.get('filename2').hide(true);
  		document.adminForm.upload_dir[0].disabled=true;
	    document.adminForm.upload_dir[0].checked=false;
	    document.adminForm.upload_dir[1].checked=true;
	    document.adminForm.upload_dir[1].disabled=false;
	    document.adminForm.upload_dir[2].checked=false;
	    document.adminForm.file_published.disabled=false;
	}
	else {
		// pay-download selected
		Ext.get('filename2').show(true);
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
Ext.get('thumbsizes').enableDisplayMode();
Ext.get('fullsizes').enableDisplayMode();
Ext.get('filename2').enableDisplayMode();
checkThumbnailing();

</script>
<?php } ?>
