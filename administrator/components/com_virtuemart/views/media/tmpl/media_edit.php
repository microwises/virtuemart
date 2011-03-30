<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage
* @author Max Milbers
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
echo '<fieldset class="adminform">';
$this->media->addHidden('view','media');
$this->media->addHidden('task','');

echo $this->media->displayFileHandler();
echo '</fieldset>';
/*?>

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

	/* Downloadable products should not be given file title option
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
<?php */ AdminMenuHelper::endAdminArea(); ?>
