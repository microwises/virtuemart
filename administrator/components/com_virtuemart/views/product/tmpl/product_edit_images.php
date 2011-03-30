<?php
/**
*
* The main product images
*
* @package	VirtueMart
* @subpackage Product
* @author RolandD
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

//echo $this->image->displayImageFoldersWriteAble(VmConfig::get('media_product_path'));

?>
<div class="col50">

	<?php
//	foreach($this->product->images as $image){
		if(empty($this->product->images[0]->file_id)) $this->product->images[0]->addHidden('file_is_product_image','1');
		echo $this->product->images[0]->displayFilesHandler($this->product->file_ids);
//	}
	?>

</div>
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
	if( jQuery('#product_full_image_url').val().length>0) document.adminForm.product_full_image_action[1].checked=false;
	else document.adminForm.product_full_image_action[1].checked=true;
	toggleDisable( document.adminForm.product_full_image_action[1], document.adminForm.product_thumb_image_url, true );
	toggleDisable( document.adminForm.product_full_image_action[1], document.adminForm.product_thumb_image, true );
}
</script>
