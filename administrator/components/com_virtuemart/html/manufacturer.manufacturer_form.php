<?php 
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id: manufacturer.manufacturer_form.php 1760 2009-05-03 22:58:57Z Aravot $
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
mm_showMyFileName( __FILE__ );
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'image.php');

global $ps_manufacturer_category, $ps_product;
include_class('product');
$manufacturer_id = vmRequest::getInt( 'manufacturer_id', 0 );
$option = empty($option)?JRequest::getVar(  'option', 'com_virtuemart'):$option;

if (!empty($manufacturer_id)) {
  $q = "SELECT * FROM #__{vm}_manufacturer WHERE manufacturer_id=$manufacturer_id"; 
  $db->query($q);  
  $db->next_record();
}
//First create the object and let it print a form heading
$formObj = &new formFactory( JText::_('VM_MANUFACTURER_FORM_LBL') );
//Then Start the form
$formObj->startForm('adminForm', 'enctype="multipart/form-data"');

$tabs = new vmTabPanel(0, 1, "manufacturerform");
$tabs->startPane("manufacturer-pane");
$tabs->startTab( "<img src='". IMAGEURL ."ps_image/edit.png' align='absmiddle' width='16' height='16' border='0' /> ".JText::_('VM_MANUFACTURER_FORM_LBL'), "info-page");

?>
<br />
  <table class="adminform">
    <tr> 
      <td><strong><?php echo JText::_('VM_MANUFACTURER_FORM_INFO_LBL') ?></strong></td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td align="right"><?php echo JText::_('VM_MANUFACTURER_LIST_MANUFACTURER_NAME') ?></td>
      <td> 
        <input type="text" class="inputbox" name="mf_name" value="<?php $db->sp("mf_name") ?>" size="16" />
      </td>
    </tr>
    <tr> 
      <td width="22%" align="right" ><?php echo JText::_('VM_PRODUCT_FORM_URL') ?>:</td>
      <td width="78%" > 
        <input type="text" class="inputbox" name="mf_url" value="<?php $db->sp("mf_url") ?>" size="32" />
      </td>
    </tr>
    <tr> 
      <td align="right"><?php echo JText::_('VM_MANUFACTURER_FORM_CATEGORY') ?>:</td>
      <td ><?php $ps_manufacturer_category->list_category($db->f("mf_category_id"));     ?></td>
    </tr>
    <tr> 
      <td align="right">&nbsp;</td>
      <td >&nbsp;</td>
    </tr>
    <tr> 
      <td align="right" ><?php echo JText::_('VM_MANUFACTURER_FORM_EMAIL') ?>:</td>
      <td>
        <input type="text" class="inputbox" name="mf_email" value="<?php $db->sp("mf_email") ?>" size="18" />
      </td>
    </tr>
    <tr> 
      <td align="right" >&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td width="22%" align="right"  valign="top"><?php echo JText::_('VM_MANUFACTURER_FORM_DESCRIPTION') ?>:</td>
      <td width="78%" ><?php
		editorArea( 'editor1', $db->f("mf_desc"), 'mf_desc', '300', '150', '70', '25' )
	?>
      </td>
    <tr align="center"> 
      <td colspan="2" >&nbsp;</td>
    </tr>
</table>
<?php

$tabs->endTab();
$tabs->startTab( "<img src='". IMAGEURL ."ps_image/image.png' width='16' height='16' align='absmiddle' border='0' /> ".JText::_('E_IMAGES'), "status-page");

if( !stristr( $db->f("mf_thumb_image"), "http") )
  echo "<input type=\"hidden\" name=\"mf_thumb_image_curr\" value=\"". $db->f("mf_thumb_image") ."\" />";

if( !stristr( $db->f("mf_full_image"), "http") )
  echo "<input type=\"hidden\" name=\"mf_full_image_curr\" value=\"". $db->f("mf_full_image") ."\" />";
  
  $ps_html->writableIndicator( array( IMAGEPATH."manufacturer") );
?>

  <table width="100%" border="0" cellspacing="0" cellpadding="2">
    <tr> 
      <td valign="top" width="50%" style="border-right: 1px solid black;">
        <h2><?php echo JText::_('VM_PRODUCT_FORM_FULL_IMAGE') ?></h2>
        <table>
          <tr> 
            <td colspan="2" ><?php 
              if ($manufacturer_id) {
                echo JText::_('VM_PRODUCT_FORM_IMAGE_UPDATE_LBL') . "<br />"; } ?> 
              <input type="file" class="inputbox" name="mf_full_image" size="50" maxlength="255" />
            </td>
          </tr>
          <tr> 
            <td colspan="2" ><strong><?php echo JText::_('VM_IMAGE_ACTION') ?>:</strong><br/>
              <input type="radio" class="inputbox" name="mf_full_image_action" id="mf_full_image_action0" checked="checked" value="none" onchange="toggleDisable( document.adminForm.mf_full_image_action[1], document.adminForm.mf_thumb_image, true );toggleDisable( document.adminForm.mf_full_image_action[1], document.adminForm.mf_thumb_image_url, true );"/>
              <label for="mf_full_image_action0"><?php echo JText::_('VM_NONE') ?></label><br/>
              <?php
              if( function_exists('imagecreatefromjpeg')) {
              		?>
	              <input type="radio" class="inputbox" name="mf_full_image_action" id="mf_full_image_action1" value="auto_resize" onchange="toggleDisable( document.adminForm.mf_full_image_action[1], document.adminForm.mf_thumb_image, true );toggleDisable( document.adminForm.mf_full_image_action[1], document.adminForm.mf_thumb_image_url, true );"/>
	              <label for="mf_full_image_action1"><?php echo JText::_('VM_FILES_FORM_AUTO_THUMBNAIL') . "</label><br />"; 
              }
              if ($manufacturer_id and $db->f("mf_full_image")) { ?>
                <input type="radio" class="inputbox" name="mf_full_image_action" id="mf_full_image_action2" value="delete" onchange="toggleDisable( document.adminForm.mf_full_image_action[1], document.adminForm.mf_thumb_image, true );toggleDisable( document.adminForm.mf_full_image_action[1], document.adminForm.mf_thumb_image_url, true );"/>
                <label for="mf_full_image_action2"><?php echo JText::_('VM_PRODUCT_FORM_IMAGE_DELETE_LBL') . "</label><br />"; 
              } ?> 
            </td>
          </tr>
          <tr><td colspan="2">&nbsp;</td></tr>
          <tr> 
            <td width="21%" ><?php echo JText::_('URL')." (".JText::_('CMN_OPTIONAL')."!)&nbsp;"; ?></td>
            <td width="79%" >
              <?php 
              if( stristr($db->f("mf_full_image"), "http") )
                $mf_full_image_url = $db->f("mf_full_image");
              else if(!empty($_REQUEST['mf_full_image_url']))
                $mf_full_image_url = JRequest::getVar( 'mf_full_image_url');
              else
                $mf_full_image_url = "";
              ?>
              <input type="text" class="inputbox" size="50" name="mf_full_image_url" value="<?php echo $mf_full_image_url ?>" onchange="if( this.value.length>0) document.adminForm.auto_resize.checked=false; else document.adminForm.auto_resize.checked=true; toggleDisable( document.adminForm.auto_resize, document.adminForm.mf_thumb_image_url, true );toggleDisable( document.adminForm.auto_resize, document.adminForm.mf_thumb_image, true );" />
            </td>
          </tr>
          <tr><td colspan="2">&nbsp;</td></tr>
          <tr> 
            <td colspan="2" >
              <div style="overflow:auto;">
                <?php /*echo $ps_product->image_tag($db->f("mf_full_image"), "", 0, "manufacturer") */?>
                <?php ImageHelper::displayImage($db->f("mf_full_image"), 'manufacturer', '', false);?>
              </div>
            </td>
          </tr>
        </table>
      </td>

      <td valign="top" width="50%">
        <h2><?php echo JText::_('VM_PRODUCT_FORM_THUMB_IMAGE') ?></h2>
        <table>
          <tr> 
            <td colspan="2" ><?php if ($manufacturer_id) {
                echo JText::_('VM_PRODUCT_FORM_IMAGE_UPDATE_LBL') . "<br>"; } ?> 
              <input type="file" class="inputbox" name="mf_thumb_image" size="50" maxlength="255" onchange="if(document.adminForm.mf_thumb_image.value!='') document.adminForm.mf_thumb_image_url.value='';" />
            </td>
          </tr>
          <tr> 
            <td colspan="2" ><strong><?php echo JText::_('VM_IMAGE_ACTION') ?>:</strong><br/>
              <input type="radio" class="inputbox" id="mf_thumb_image_action0" name="mf_thumb_image_action" checked="checked" value="none" onchange="toggleDisable( document.adminForm.image_action[1], document.adminForm.mf_thumb_image, true );toggleDisable( document.adminForm.image_action[1], document.adminForm.mf_thumb_image_url, true );"/>
              <label for="mf_thumb_image_action0"><?php echo JText::_('VM_NONE') ?></label><br/>
              <?php 
              if ($manufacturer_id and $db->f("mf_thumb_image")) { ?>
                <input type="radio" class="inputbox" id="mf_thumb_image_action1" name="mf_thumb_image_action" value="delete" onchange="toggleDisable( document.adminForm.image_action[1], document.adminForm.mf_thumb_image, true );toggleDisable( document.adminForm.image_action[1], document.adminForm.mf_thumb_image_url, true );"/>
                <label for="mf_thumb_image_action1"><?php echo JText::_('VM_PRODUCT_FORM_IMAGE_DELETE_LBL') . "</label><br />"; 
              } ?> 
            </td>
          </tr>
          <tr><td colspan="2">&nbsp;</td></tr>
          <tr> 
            <td width="21%" ><?php echo JText::_('URL')." (".JText::_('CMN_OPTIONAL').")&nbsp;"; ?></td>
            <td width="79%" >
              <?php 
              if( stristr($db->f("mf_thumb_image"), "http") )
                $mf_thumb_image_url = $db->f("mf_thumb_image");
              else if(!empty($_REQUEST['mf_thumb_image_url']))
                $mf_thumb_image_url = JRequest::getVar( 'mf_thumb_image_url');
              else
                $mf_thumb_image_url = "";
              ?>
              <input type="text" class="inputbox" size="50" name="mf_thumb_image_url" value="<?php echo $mf_thumb_image_url ?>" />
            </td>
          </tr>
          <tr><td colspan="2">&nbsp;</td></tr>
          <tr>
            <td colspan="2" >
              <div style="overflow:auto;">
                <?php /*echo $ps_product->image_tag($db->f("mf_thumb_image"), "", 0, "manufacturer") */?>
                <?php ImageHelper::displayImage($db->f("mf_thumb_image"), 'manufacturer', '', false);?>
              </div>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
<?php
$tabs->endTab();
$tabs->endPane();

// Add necessary hidden fields
$formObj->hiddenField( 'manufacturer_id', $manufacturer_id );

$funcname = !empty($manufacturer_id) ? "manufacturerupdate" : "manufactureradd";
// Write your form with mixed tags and text fields
// and finally close the form:
$formObj->finishForm( $funcname, $modulename.'.manufacturer_list', $option );
?>
<script type="text/javascript">//<!--
function toggleDisable( elementOnChecked, elementDisable, disableOnChecked ) {
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

toggleDisable( document.adminForm.mf_full_image_action[1], document.adminForm.mf_thumb_image, true );
//-->
</script>