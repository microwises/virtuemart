<?php defined('_JEXEC') or die('Restricted access');
$folder = array(IMAGEPATH."product", IMAGEPATH."product/resized");
$style = 'text-align:left;margin-left:20px;';
echo '<div class="vmquote" style="'.$style.'">';
foreach( $folder as $dir ) {
	echo $dir . ' :: ';
	echo is_writable( $dir )
		 ? '<span style="font-weight:bold;color:green;">'.JText::_('VM_WRITABLE').'</span>'
		 : '<span style="font-weight:bold;color:red;">'.JText::_('VM_UNWRITABLE').'</span>';
	echo '<br/>';
}
echo '</div>';
?>
<table class="adminform" >
    <tr>
      <td valign="top" width="50%" style="border-right: 1px solid black;">
        <h2><?php echo JText::_('VM_PRODUCT_FORM_FULL_IMAGE') ?></h2>
        <table class="adminform">
          <tr class="row0">
            <td colspan="2" ><?php
            if ($this->product->product_id) {
                echo JText::_('VM_PRODUCT_FORM_IMAGE_UPDATE_LBL') . "<br />"; } ?>
              <input type="file" class="inputbox" name="product_full_image" onchange="document.adminForm.product_full_image_url.value='';if(this.value!='') { document.adminForm.product_full_image_action[1].checked=true;toggleDisable(document.adminForm.product_full_image_action[1], document.adminForm.product_thumb_image, true) }" size="50" maxlength="255" />
            </td>
          </tr>
          <tr class="row1">
            <td colspan="2" ><div style="font-weight:bold;"><?php echo JText::_('VM_IMAGE_ACTION') ?>:</div><br/>
              <input type="radio" class="inputbox" id="product_full_image_action0" name="product_full_image_action" checked="checked" value="none" onchange="toggleDisable( document.adminForm.product_full_image_action[1], document.adminForm.product_thumb_image, true );toggleDisable( document.adminForm.product_full_image_action[1], document.adminForm.product_thumb_image_url, true );"/>
              <label for="product_full_image_action0"><?php echo JText::_('VM_NONE'); ?></label><br/>
              <?php
              // Check if GD library is available
              if (function_exists('gd_info')) { ?>
	              <input type="radio" class="inputbox" id="product_full_image_action1" name="product_full_image_action" value="auto_resize" onchange="toggleDisable( document.adminForm.product_full_image_action[1], document.adminForm.product_thumb_image, true );toggleDisable( document.adminForm.product_full_image_action[1], document.adminForm.product_thumb_image_url, true );"/>
	              <label for="product_full_image_action1"><?php echo JText::_('VM_FILES_FORM_AUTO_THUMBNAIL') . "</label><br />";
              }
              if ($this->product->product_id && $this->product->product_full_image) { ?>
                <input type="radio" class="inputbox" id="product_full_image_action2" name="product_full_image_action" value="delete" onchange="toggleDisable( document.adminForm.product_full_image_action[1], document.adminForm.product_thumb_image, true );toggleDisable( document.adminForm.product_full_image_action[1], document.adminForm.product_thumb_image_url, true );"/>
                <label for="product_full_image_action2"><?php echo JText::_('VM_PRODUCT_FORM_IMAGE_DELETE_LBL') . "</label><br />";
              } ?>
            </td>
          </tr>
          <tr class="row0"><td colspan="2">&nbsp;</td></tr>
          <tr class="row0">
            <td width="21%" ><?php echo JText::_('URL')." (".JText::_('CMN_OPTIONAL')."!)&nbsp;"; ?></td>
            <td width="79%" >
              <?php
              if( stristr($this->product->product_full_image, "http") )
              $product_full_image_url = $this->product->product_full_image;
              else if(!empty($_REQUEST['product_full_image_url']))
              	  $product_full_image_url = JRequest::getVar('product_full_image_url');
              else
              $product_full_image_url = "";
              ?>
              <input type="text" class="inputbox" size="50" name="product_full_image_url" id="product_full_image_url" value="<?php echo $product_full_image_url ?>" onchange="toggleFullURL()" />
            </td>
          </tr>
          <tr class="row1"><td colspan="2">&nbsp;</td></tr>
          <tr class="row1">
            <td colspan="2" >
              <div style="overflow:auto;">
                <?php echo ImageHelper::displayShopImage($this->product->product_full_image, 'product', '', 0); ?>
              </div>
            </td>
          </tr>
        </table>
      </td>

      <td valign="top" width="50%">
        <h2><?php echo JText::_('VM_PRODUCT_FORM_THUMB_IMAGE') ?></h2>
        <table class="adminform">
          <tr class="row0">
            <td colspan="2" ><?php if ($this->product->product_id) {
                echo JText::_('VM_PRODUCT_FORM_IMAGE_UPDATE_LBL') . "<br />"; } ?>
              <input type="file" class="inputbox" name="product_thumb_image" size="50" maxlength="255" onchange="if(document.adminForm.product_thumb_image.value!='') document.adminForm.product_thumb_image_url.value='';" />
            </td>
          </tr>
          <tr class="row1">
            <td colspan="2" ><div style="font-weight:bold;"><?php echo JText::_('VM_IMAGE_ACTION') ?>:</div><br/>
              <input type="radio" class="inputbox" id="product_thumb_image_action0" name="product_thumb_image_action" checked="checked" value="none" onchange="toggleDisable( document.adminForm.product_thumb_image_action[1], document.adminForm.product_thumb_image, true );toggleDisable( document.adminForm.product_thumb_image_action[1], document.adminForm.product_thumb_image_url, true );"/>
              <label for="product_thumb_image_action0"><?php echo JText::_('VM_NONE') ?></label><br/>
              <?php
              if ($this->product->product_id and $this->product->product_thumb_image) { ?>
                <input type="radio" class="inputbox" id="product_thumb_image_action1" name="product_thumb_image_action" value="delete" onchange="toggleDisable( document.adminForm.product_thumb_image_action[1], document.adminForm.product_thumb_image, true );toggleDisable( document.adminForm.product_thumb_image_action[1], document.adminForm.product_thumb_image_url, true );"/>
                <label for="product_thumb_image_action1"><?php echo JText::_('VM_PRODUCT_FORM_IMAGE_DELETE_LBL') . "</label><br />";
              } ?>
            </td>
          </tr>
          <tr class="row0"><td colspan="2">&nbsp;</td></tr>
          <tr class="row0">
            <td width="21%" ><?php echo JText::_('URL')." (".JText::_('CMN_OPTIONAL').")&nbsp;"; ?></td>
            <td width="79%" >
              <?php
              if( stristr($this->product->product_thumb_image, "http") )
              $product_thumb_image_url = $this->product->product_thumb_image;
              else if(!empty($_REQUEST['product_thumb_image_url']))
              $product_thumb_image_url = JRequest::getVar('product_thumb_image_url');
              else
              $product_thumb_image_url = "";
              ?>
              <input type="text" class="inputbox" size="50" name="product_thumb_image_url" value="<?php echo $product_thumb_image_url ?>" />
            </td>
          </tr>
          <tr class="row1"><td colspan="2">&nbsp;</td></tr>
          <tr class="row1">
            <td colspan="2" >
              <div style="overflow:auto;">
                <?php echo ImageHelper::displayShopImage($this->product->product_thumb_image, 'product/', '', 0); ?>
              </div>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
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
