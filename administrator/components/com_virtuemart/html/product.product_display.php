<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id: product.product_display.php 1760 2009-05-03 22:58:57Z Aravot $
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2004-2007 soeren - All rights reserved.
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

global $hVendor;
$db2 = new ps_DB; 
$product_id = vmGet($vars, 'product_id', 0 );
if( empty( $product_id )) {
	include( PAGEPATH.'product.product_list.php');
	return;
}
$product_parent_id = JRequest::getVar(  'product_parent_id', 0 );
$vars["product_parent_id"] = vmget( $vars, 'product_parent_id', 0 );

if ($product_parent_id == $vars["product_parent_id"]) {
  if ($func == "productAdd") {
    $action = JText::_('VM_PRODUCT_DISPLAY_ADD_ITEM_LBL'); 
  } else {
    $action = JText::_('VM_PRODUCT_DISPLAY_UPDATE_ITEM_LBL'); 
  }
  $info_label = JText::_('VM_PRODUCT_FORM_ITEM_INFO_LBL');
  $status_label = JText::_('VM_PRODUCT_FORM_ITEM_STATUS_LBL');
  $dim_weight_label = JText::_('VM_PRODUCT_FORM_ITEM_DIM_WEIGHT_LBL');
  $images_label = JText::_('VM_PRODUCT_FORM_ITEM_IMAGES_LBL');
} else {
  $product_parent_id ="";
  if ($func == "productAdd") {
    $action = JText::_('VM_PRODUCT_DISPLAY_ADD_PRODUCT_LBL'); 
  } else {
    $action = JText::_('VM_PRODUCT_DISPLAY_UPDATE_PRODUCT_LBL'); 
  }
  $info_label = JText::_('VM_PRODUCT_FORM_PRODUCT_INFO_LBL');
  $status_label = JText::_('VM_PRODUCT_FORM_PRODUCT_STATUS_LBL');
  $dim_weight_label = JText::_('VM_PRODUCT_FORM_PRODUCT_DIM_WEIGHT_LBL');
  $images_label = JText::_('VM_PRODUCT_FORM_PRODUCT_IMAGES_LBL');
}
$url = $_SERVER['PHP_SELF'] . "?page=$modulename.product_form&product_id=$product_id&product_parent_id=$product_parent_id";
?>

<h2><?php 
echo "$action: ";
echo "<a href=\"" . $sess->url($url) . "\">". $ps_product->get_field($product_id,"product_name")."</a>"; 
?></h2>
<?php
$q  = "SELECT * FROM #__{vm}_product WHERE product_id='$product_id' ";
$db->query($q);                                                                                    
$db->next_record();
?>
<div align="center">
  <a href="<?php echo $_SERVER['PHP_SELF']."?option=com_virtuemart&page=product.product_list" ?>">
      <h4>&gt;&gt;<?php echo JText::_('VM_PRODUCT_LIST_LBL') ?>&lt;&lt;</h4>
  </a>
</div>

<div style="width:90%;float:left;">
	<fieldset>
		<legend><strong><?php echo $info_label ?></strong></legend>
  
		<div class="formLabel"> <?php echo JText::_('VM_PRODUCT_FORM_SKU') ?>:</div>
		<div class="formField" > <?php $db->p("product_sku"); ?></div>
	  
	  
		<div class="formLabel"> <?php echo JText::_('VM_PRODUCT_FORM_NAME') ?>:</div>
		<div class="formField" > <?php $db->p("product_name"); ?></div>
	  
	  
		<div class="formLabel"> <?php echo JText::_('VM_PRODUCT_FORM_URL') ?>:</div>
		<div class="formField"><?php $db->p("product_url"); ?></div>
	  
	<?php
		if(!$product_parent_id) { ?>
			<div class="formLabel"><?php echo JText::_('VM_PRODUCT_FORM_CATEGORY') .": "; ?></div>
			<div class="formField" > <?php echo $ps_product_category->get_name($product_id); ?></div>
	<?php
		}
	?>
	  
		<div class="formLabel"><?php echo JText::_('VM_PRODUCT_FORM_VENDOR') ?>:</div>
		<div class="formField"><?php print $hVendor->get_name($db->f("vendor_id")); ?></div>
		
		<div class="formLabel"><?php echo JText::_('VM_PRODUCT_FORM_MANUFACTURER') ?>:</div>
		<div class="formField"><?php print $ps_product->get_mf_name($product_id); ?></div>
	
	<?php
	$price = $ps_product->get_retail_price($product_id);
	?>
	<br style="clear:both;"/>
	<br style="clear:both;"/>
		<div class="formLabel"><?php echo JText::_('VM_PRODUCT_FORM_PRICE_NET') ?>:</div>
		<div class="formField"><?php echo $GLOBALS['CURRENCY_DISPLAY']->getFullValue( $price['product_price'] ); ?></div>
	<br style="clear:both;" />
	<br style="clear:both;" />
		<div class="formLabel" > <?php echo JText::_('VM_PRODUCT_FORM_S_DESC') ?>:</div>
		<div class="formField"><?php $db->p("product_s_desc"); ?></div>
	  
	  
		<div class="formLabel" > <?php echo JText::_('VM_PRODUCT_FORM_DESCRIPTION') ?>:</div>
		<div style="overflow:auto;max-height:200px" class="formField"><?php $db->p("product_desc"); ?></div>
	</fieldset>
</div>


<div style="width:45%;float:left;">
	<fieldset>
		<legend><strong><?php echo $status_label ?></strong></legend>
  
  
			<div class="formLabel"><?php echo JText::_('VM_PRODUCT_FORM_IN_STOCK') ?>:</div>
			<div class="formField" ><?php $db->p("product_in_stock"); ?></div>
		  
		  
			<div class="formLabel"> <?php echo JText::_('VM_PRODUCT_FORM_AVAILABLE_DATE') ?>:</div>
			<div class="formField" > <?php
		if ($db->f("product_available_date")) { 
		  echo strftime("%D",$db->f("product_available_date"));
		}
		?></div>
		  
		  
			<div class="formLabel"> <?php echo JText::_('VM_PRODUCT_FORM_SPECIAL') ?>:</div>
			<div class="formField" > <?php $db->p("product_special"); ?></div>
		  
		  
			<div class="formLabel"> <?php echo JText::_('VM_PRODUCT_FORM_DISCOUNT_TYPE') ?>:</div>
			<div class="formField" > <?php 
			if( $db->f("product_discount_id") ) {
				$db2->query( "SELECT is_percent, amount FROM #__{vm}_product_discount WHERE discount_id='".$db->f("product_discount_id")."'" );
				$db2->next_record();
				if($db2->f("is_percent"))
					echo $db2->f("amount") . "% "; 
				else
					echo $CURRENCY_DISPLAY->getFullValue( $db2->f("amount") );
			}
			?>
			</div>
		  
		  
			<div class="formLabel"> <?php echo JText::_('VM_PRODUCT_FORM_PUBLISH') ?>:</div>
			<div class="formField" > <?php echo vmCommonHTML::getYesNoIcon( $db->f("product_publish") ); ?></div>
	</fieldset>
</div>
<div style="width:45%;float:left;">
	<fieldset>
		<legend><strong><?php echo $dim_weight_label ?></strong></legend>
		  
		<div class="formLabel"> <?php echo JText::_('VM_PRODUCT_FORM_LENGTH') ?>:</div>
		<div class="formField" > <?php echo $db->f("product_length") . " " . $db->f("product_lwh_uom"); ?></div>
	  
		<div class="formLabel"> <?php echo JText::_('VM_PRODUCT_FORM_WIDTH') ?>:</div>
		<div class="formField" > <?php echo $db->f("product_width") . " " . $db->f("product_lwh_uom"); ?></div>
	  
		<div class="formLabel"> <?php echo JText::_('VM_PRODUCT_FORM_HEIGHT') ?>:</div>
		<div class="formField"> <?php echo $db->f("product_height") . " " . $db->f("product_lwh_uom"); ?></div>
		
		<div class="formLabel"> <?php echo JText::_('VM_PRODUCT_FORM_WEIGHT') ?>:</div>
		<div class="formField"><?php echo $db->f("product_weight") . " " . $db->f("product_weight_uom"); ?></div>
	</fieldset>
</div>
<?php
if ($product_parent_id) { 
	$q2 = "SELECT * FROM #__{vm}_product_attribute,#__{vm}_product_attribute_sku ";
	$q2 .= "WHERE #__{vm}_product_attribute.product_id ='". $product_id ."'";
	$q2 .= "AND #__{vm}_product_attribute_sku.product_id = '". $product_parent_id ."'";
	$q2 .= "AND #__{vm}_product_attribute.attribute_name = #__{vm}_product_attribute_sku.attribute_name ";
	$q2 .= "ORDER BY attribute_list,#__{vm}_product_attribute.attribute_name"; 
	$db2->query($q2);
?> 
<div style="width:90%;">
	<fieldset>
		<legend><strong><?php echo JText::_('VM_PRODUCT_FORM_ITEM_ATTRIBUTES_LBL') ?></strong></legend>
  
		<?php
		while ($db2->next_record()) {
			?> 
			<div class="formLabel"><?php $db2->sp("attribute_name") ?>:</div>
			<div class="formField" ><?php $db2->p("attribute_value"); ?></div>
			<?php
		} ?> 
	</fieldset>
</div>
<?php
}
?> 
  
<div style="width:90%;float:left;">
	<fieldset>
		<legend><strong><?php echo $images_label ?></strong></legend>
  
  
		<div class="formLabel"> <?php echo JText::_('VM_PRODUCT_FORM_THUMB_IMAGE') ?>:</div>
		<div class="formField">
			<?php ImageHelper::displayImage($db->f("product_thumb_image"), 'product', "", false); ?>
			<?php /*$ps_product->show_image($db->f("product_thumb_image"), "", 0);*/ ?>
		</div>
	  
		<div class="formLabel"> <?php echo JText::_('VM_PRODUCT_FORM_FULL_IMAGE') ?>:</div>
		<div class="formField">
			<?php ImageHelper::displayImage($db->f("product_thumb_image"), 'product', "", false); ?>
			<?php /*$ps_product->show_image($db->f("product_full_image"), "", 0);*/ ?>
		</div>
  
	</fieldset>
</div>
