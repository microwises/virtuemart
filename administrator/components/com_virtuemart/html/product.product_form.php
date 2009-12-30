<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: product.product_form.php 1786 2009-05-13 13:21:59Z macallf $
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

/* Gets the $vendor_id by Max Milbers
*/
global $ps_product, $ps_product_category,$hVendor;
$vendor_id = $hVendor->getVendorIdByUserId($_SESSION['auth']["user_id"]);

mm_showMyFileName( __FILE__ );

require_once( CLASSPATH.'ps_product_discount.php' );
require_once( CLASSPATH.'ps_manufacturer.php' );

$product_id = JRequest::getVar(  'product_id');
if( is_array( $product_id )) {
	$product_id = (int)$product_id[0];
}
vmCommonHTML::loadExtjs();
echo vmCommonHTML::scriptTag( $mosConfig_live_site.'/components/'.VM_COMPONENT_NAME.'/js/product_attributes.js');
echo vmCommonHTML::scriptTag( $mosConfig_live_site .'/includes/js/calendar/calendar.js');
if( class_exists( 'JConfig' ) ) {
	// in Joomla 1.5, the name of calendar lang file is changed...
	echo vmCommonHTML::scriptTag( $mosConfig_live_site .'/includes/js/calendar/lang/calendar-en-GB.js');
} else {
	echo vmCommonHTML::scriptTag( $mosConfig_live_site .'/includes/js/calendar/lang/calendar-en.js');
}
echo vmCommonHTML::linkTag( $mosConfig_live_site .'/includes/js/calendar/calendar-mos.css');

$product_parent_id = JRequest::getVar(  'product_parent_id');
$next_page = JRequest::getVar(  'next_page', "product.product_display" );
$option = empty($option)?JRequest::getVar(  'option', 'com_virtuemart'):$option;
$clone_product = JRequest::getVar(  'clone_product', 0 );
$product_type_id = JRequest::getVar( 'product_type_id', 0);
$extra_ids = '';
$display_use_parent="";
$product_list="";
$display_header="";
$product_list_child="";
$product_list_type="";
$display_desc="";
$desc_width="20%";
$attrib_width="10%";
$display_type = "none";
$child_class_sfx ="";
$min_order="";
$max_order="";
$child_display_order="";
$display_use_parent_disabled = false;
if($product_parent_id !=0) {
	$display_use_parent_disabled = true;
}
$list = Array();
if( !empty($_REQUEST['product_categories']) && is_array($_REQUEST['product_categories'])) {
	foreach( $_REQUEST['product_categories'] as $catid ) $my_categories[$catid] = '1';
} else {
	$my_categories = array();
}

$related_products = Array();

if ($product_parent_id > 0) {
	if ($product_id) {
		$action = JText::_('VM_PRODUCT_FORM_UPDATE_ITEM_LBL');
	}
	else {
		$action = JText::_('VM_PRODUCT_FORM_NEW_ITEM_LBL');
	}
	$info_label = JText::_('VM_PRODUCT_FORM_ITEM_INFO_LBL');
	$status_label = JText::_('VM_PRODUCT_FORM_ITEM_STATUS_LBL');
	$dim_weight_label = JText::_('VM_PRODUCT_FORM_ITEM_DIM_WEIGHT_LBL');
	$images_label = JText::_('VM_PRODUCT_FORM_ITEM_IMAGES_LBL');
	$delete_message = JText::_('VM_PRODUCT_FORM_DELETE_ITEM_MSG');
}
else {
	$product_parent_id = '';
	if ($product_id = @$vars["product_id"]) {
		if( $clone_product == '1') {
			$action = JText::_('VM_PRODUCT_CLONE');
		}
		else {
			$action = JText::_('VM_PRODUCT_FORM_UPDATE_ITEM_LBL');
		}
	}
	else {
		$action = JText::_('VM_PRODUCT_FORM_NEW_PRODUCT_LBL');
	}
	$info_label = JText::_('VM_PRODUCT_FORM_PRODUCT_INFO_LBL');
	$status_label = JText::_('VM_PRODUCT_FORM_PRODUCT_STATUS_LBL');
	$dim_weight_label = JText::_('VM_PRODUCT_FORM_PRODUCT_DIM_WEIGHT_LBL');
	$images_label = JText::_('VM_PRODUCT_FORM_PRODUCT_IMAGES_LBL');
	$delete_message = JText::_('VM_PRODUCT_FORM_DELETE_PRODUCT_MSG');
}
$display_label = JText::_('VM_PRODUCT_FORM_ITEM_DISPLAY_LBL');
if (!empty($product_id)) {
	$price = $ps_product->get_retail_price($product_id);
} else {
	$price['product_price'] = JRequest::getVar('product_price', '');
}
$quantity_start = 0;
$quantity_end = 0;
$quantity_step = 1;
if (!empty($product_id)) {
	// get the Database object we're filling the product form with
	$db = $ps_product->get($product_id);

	//get quantity options
	$quantity_options = ps_product::get_quantity_options($product_id);
	extract( $quantity_options );
	//get list style
	$child_options = ps_product::get_child_options($product_id);
	extract($child_options);

	//Get min max order levels
	$order_levels = ps_product::product_order_levels($product_id);
	if($order_levels) {
		$min_order = array_shift($order_levels);
		$max_order = array_shift($order_levels);
	}


	// Get category IDs
	$db2 = new ps_DB;
	$q = "SELECT category_id FROM #__{vm}_product_category_xref WHERE product_id='$product_id'";
	$db2->query($q);
	while ($db2->next_record()) {
		$my_categories[$db2->f("category_id")] = "1";
	}

	// Get the Manufacturer ID
	$db2->query("SELECT manufacturer_id FROM #__{vm}_product_mf_xref WHERE product_id='$product_id'");
	$db2->next_record();
	$manufacturer_id = $db2->f("manufacturer_id");

	// Get the Related Products
	$db2->query("SELECT related_products FROM #__{vm}_product_relations WHERE product_id='$product_id'");
	if($db2->next_record()) {
		$related_products = explode("|", $db2->f("related_products"));
	}

}
// Get some "default" values, which are used when no other values where provided through _REQUEST
$default['attribute'] = ps_product_attribute::formatAttributeX();
$default["product_publish"] = "Y";
$default["product_weight_uom"] = JText::_('VM_PRODUCT_FORM_WEIGHT_UOM_DEFAULT');
$default["product_lwh_uom"] = JText::_('VM_PRODUCT_FORM_DIMENSION_UOM_DEFAULT');
$default["product_unit"] = JText::_('VM_PRODUCT_FORM_UNIT_DEFAULT');
if( !empty($vars['product_available_date'])) $vars['product_available_date'] = strtotime($vars['product_available_date']);
else $default["product_available_date"] = time();

// get the default shopper group
$shopper_db = new ps_DB;
$q =  "SELECT shopper_group_id,shopper_group_name FROM #__{vm}_shopper_group WHERE `default`= '1' AND vendor_id='".$db->f("vendor_id")."'";
$shopper_db->query($q);
if ($shopper_db->num_rows()<1) {  // when there is no "default", take the first in the table
	$q =  "SELECT shopper_group_id,shopper_group_name from #__{vm}_shopper_group WHERE vendor_id='$hVendor_id'";
	$shopper_db->query($q);
}
$shopper_db->next_record();
$my_shopper_group_id = $shopper_db->f("shopper_group_id");

// Get Product Types
$db_ptypes = new ps_DB;

if ($product_type_id > 0) {
	$q = "SELECT * FROM #__{vm}_product_type WHERE product_type_id=$product_type_id";
	$db_ptypes->query($q);
	echo "<input type=\"hidden\" name=\"product_type_id\" value=\"$product_type_id\" />";
} else {
	// Get Product Types
	$q  = "SELECT * FROM #__{vm}_product_product_type_xref,#__{vm}_product_type WHERE ";
	$q .= "#__{vm}_product_product_type_xref.product_type_id=#__{vm}_product_type.product_type_id ";
	$q .= 'AND product_id='.(int)$product_id;
	/*  if (!$product_parent_id) {
			$q .= "AND product_id='$product_id' ";
	}	else {
			$q .= "AND product_id='$product_parent_id' ";
	}*/
	$q .= ' ORDER BY product_type_list_order';
	$db_ptypes->query($q);
}

// For cloning a product, we just need to empty the variable product_id
if( $clone_product == "1" ) {
	$product_id = "";
}

$title = '<img src="'. VM_ADMIN_ICON_URL.'icon_48/product_code.png" border="0" align="center" alt="Product Form" />&nbsp;&nbsp;';
$title .= $action;

if( !empty( $product_id )) {
	$title .= " :: " . $db->f("product_name");
	$flypage = $ps_product->get_flypage($product_id);
	?>
	<a href="<?php echo $mosConfig_live_site."/index.php?option=com_virtuemart&page=shop.product_details&flypage=$flypage&product_id=$product_id" ?>" target="_blank">
		  <?php echo JText::_('VM_PRODUCT_FORM_SHOW_FLYPAGE') ?>
	</a>
	<?php
}
elseif(!empty($product_parent_id)) {
	$parent_product_name = $ps_product->get_field($product_parent_id, 'product_name');
	$title .= ' :: <a href="' .$sess->url( $_SERVER['PHP_SELF'].'?page=product.product_form&product_id='.$product_parent_id).'">'.$parent_product_name.'</a>';
}



//First create the object and let it print a form heading
$formObj = &new formFactory( $title );
//Then Start the form
$formObj->startForm( 'adminForm', 'enctype="multipart/form-data"');

$tabs = new vmTabPanel(0, 1, "productform");
$tabs->startPane("content-pane");
$tabs->startTab( $info_label, "info-page");
?>
<table class="adminform">
  <tr>
   <td valign="top">
     <table width="100%" border="0">
      <tr>
       <td align="left" colspan="2"><?php echo "<h2 >$info_label</h2>"; ?></td>
    </tr>
    <tr class="row0">
      <td  width="21%" ><div style="text-align:right;font-weight:bold;">
      <?php echo JText::_('VM_PRODUCT_FORM_PUBLISH') ?>:</div>
      </td>
      <td width="79%" > <?php if ($db->sf("product_publish")=="Y") {
      	echo "<input type=\"checkbox\" name=\"product_publish\" value=\"Y\" checked=\"checked\" />";
      }
      else {
      	echo "<input type=\"checkbox\" name=\"product_publish\" value=\"Y\" />";
      }
?> </td>
    </tr>
    <tr class="row1">
      <td width="21%" ><div style="text-align:right;font-weight:bold;"><?php echo JText::_('VM_PRODUCT_FORM_SKU') ?>:</div>
      </td>
      <td width="79%" height="2">
        <input type="text" class="inputbox"  name="product_sku" value="<?php $db->sp("product_sku"); ?>" size="32" maxlength="64" />
      </td>
    </tr>
    <tr class="row0">
      <td width="21%" height="18"><div style="text-align:right;font-weight:bold;">
      <?php echo JText::_('VM_PRODUCT_FORM_NAME') ?>:</div>
      </td>
      <td width="79%" height="18" >
        <input type="text" class="inputbox"  name="product_name" value="<?php echo shopMakeHtmlSafe( $db->sf("product_name")); ?>" size="32" maxlength="255" />
      </td>
    </tr>
    <tr class="row1">
      <td width="21%"><div style="text-align:right;font-weight:bold;">
        <?php echo JText::_('VM_PRODUCT_FORM_URL') ?>:</div>
      </td>
      <td width="79%">
        <input type="text" class="inputbox"  name="product_url" value="<?php $db->sp("product_url"); ?>" size="32" maxlength="255" />
      </td>
    </tr>
    <tr class="row0">
      <td width="21%"><div style="text-align:right;font-weight:bold;">
        <?php echo JText::_('VM_PRODUCT_FORM_VENDOR') ?>:</div>
      </td>
      <td width="79%" ><?php
// 		ps_vendor::list_ornot_vendor($db->sf("vendor_id"),$vendor_id);
 		$hVendor -> list_ornot_vendor($vendor_id,$db->sf("vendor_id"));
      ?></td>
    </tr>
    <tr class="row1">
      <td width="21%" ><div style="text-align:right;font-weight:bold;">
        <?php echo JText::_('VM_PRODUCT_FORM_MANUFACTURER') ?>:</div>
      </td>
      <td width="79%" ><?php ps_manufacturer::list_manufacturer(@$manufacturer_id);  ?></td>
    </tr>
    <?php
    if (!$product_parent_id) {
    	?><tr class="row0">
    	<?php
    	$number_of_categories = ps_product_category::count_categories();
    	if( $number_of_categories > 75 ) {
    		?>
			<td style="vertical-align:top;">
			<?php echo JText::_('VM_CATEGORIES') ?>:<br/>
			<input type="text" size="40" name="catsearch" id="categorySearch" value="" />
			</td>
			<td>
			<input style="vertical-align: top;" type="button" name="remove_category" onclick="removeSelectedOptions(relatedCatSelection, 'category_ids' )" value="&nbsp; &lt; &nbsp;" />
			<?php

			foreach( array_keys($my_categories) as $cat_id ) {
				$categoriesArr[$cat_id] = ps_product_category::get_name_by_catid( $cat_id );
			}
			echo ps_html::selectList('relCats', '', $categoriesArr, 10, 'multiple="multiple"', 'id="relatedCatSelection" ondblclick="removeSelectedOptions(relatedCatSelection, \'category_ids\');"');
			?>
			<input type="hidden" name="category_ids" value="<?php echo implode('|', array_keys($my_categories) ) ?>" />
			</td>
			<?php
    	} else {
		    	?>
		      <td width="29%" valign="top"><div style="text-align:right;font-weight:bold;">
		       <?php echo JText::_('VM_CATEGORIES') ?>:<br/><br/>
		       <?php echo vmToolTip( JText::_('VM_MULTISELECT') ) ?></div>
		       </td>
		      <td width="71%" ><?php
		        $ps_product_category->list_all("product_categories[]", "", $my_categories, 10, false, true);
		        ?>
		        </td>
		    <?php
    	}
    	?>
    	</tr>
    	<?php
    }
    ?>
  </table>
 </td>
 <td>
  <table class="adminform">
    <tr class="row0">
      <td width="29%" ><div style="text-align:right;font-weight:bold;"><?php echo JText::_('VM_PRODUCT_FORM_PRICE_NET') ?>:</div>
      </td>
      <td width="71%" >
        <table border="0" cellspacing="0" cellpadding="0">
            <tr>
            <td>
                <input type="text" value="<?php echo @$price["product_price"]; ?>" class="inputbox" name="product_price" onkeyup="updateGross();" size="10" maxlength="10" />
                <input type="hidden" name="product_price_id" value="<?php echo @$price["product_price_id"] ?>" />
                <input type="hidden" name="price_quantity_start" value="<?php echo @intval($price["price_quantity_start"]) ?>" />
                <input type="hidden" name="price_quantity_end" value="<?php echo @intval($price["price_quantity_end"]) ?>" />
            </td>
            <td><?php
            if(empty($price["product_currency"])) {
            	$price["product_currency"] = $vendor_currency;
            }
              $ps_html->list_currency("product_currency",$price["product_currency"]) ?>
            </td>
            <td>&nbsp;<?php
                echo vmToolTip( JText::_('VM_PRICE_FORM_GROUP') . ": ".$shopper_db->f("shopper_group_name")); ?>
                <input type="hidden" name="shopper_group_id" value="<?php echo $my_shopper_group_id ?>" />
             </td>
            </tr>
        </table>
      </td>
    </tr>
	<tr class="row1">
      <td width="29%" ><div style="text-align:right;font-weight:bold;"><?php echo JText::_('VM_PRODUCT_FORM_PRICE_GROSS') ?>:</div>
      </td>
      <td width="71%" ><input type="text" class="inputbox" onkeyup="updateNet();" name="product_price_incl_tax" size="10" /></td>
    </tr>
    <tr class="row0">
      <td width="29%" ><div style="text-align:right;font-weight:bold;">
        <?php echo JText::_('VM_RATE_FORM_VAT_ID') ?>:</div></td>
      <td width="71%" >
        <?php
        require_once(CLASSPATH.'ps_tax.php');
        $tax_rates = ps_tax::list_tax_value("product_tax_id",$db->sf("product_tax_id"),"updateGross();") ?>
      </td>
    </tr>

    <tr class="row1">
      <td width="21%" ><div style="text-align:right;font-weight:bold;">
        <?php echo JText::_('VM_PRODUCT_FORM_DISCOUNT_TYPE') ?>:</div>
      </td>
      <td width="79%" ><?php
        echo ps_product_discount::discount_list( $db->sf("product_discount_id") ); ?>
      </td>
    </tr>
    <tr class="row0">
      <td width="21%" ><div style="text-align:right;font-weight:bold;">
        <?php echo JText::_('VM_PRODUCT_FORM_DISCOUNTED_PRICE') ?>:</div>
      </td>
      <td width="79%" >
                <input type="text" size="10" name="discounted_price_override" onchange="try { document.adminForm.product_discount_id[document.adminForm.product_discount_id.length-1].selected=true; } catch( e ) {}" />&nbsp;&nbsp;
                <?php echo vmToolTip( JText::_('VM_PRODUCT_FORM_DISCOUNTED_PRICE_TIP') ) ?>
        </td>
    </tr>
    <tr><td width="29%" valign="top"><div style="text-align:right;font-weight:bold;"><?php echo JText::_('VM_ORDER_PRINT_INTNOTES'); ?>:</div></td>
    <td width="71%" valign="top"><textarea class="inputbox" name="intnotes" id="intnotes" cols="35" rows="6" ><?php echo $db->sf("intnotes"); ?></textarea></td></tr>
    <tr class="row1"><td colspan="2">&nbsp;</td></tr>
    <tr class="row1">
      <td width="29%" valign="top"><div style="text-align:right;font-weight:bold;">
          <?php echo JText::_('VM_PRODUCT_FORM_S_DESC') ?>:</div>
      </td>
      <td width="71%"  valign="top">
          <textarea class="inputbox" name="product_s_desc" id="short_desc" cols="35" rows="6" ><?php echo $db->sf("product_s_desc"); ?></textarea>
      </td>
    </tr>
  </table>
  </td>
  </tr>
  </table>
  <table class="adminform">
    <tr class="row1">
      <td valign="top" width="20%"><div style="font-weight:bold;">
        <?php echo JText::_('VM_PRODUCT_FORM_DESCRIPTION') ?>:</div>
      </td>
      <td width="60%">
        <?php
        editorArea( 'editor1', htmlspecialchars( $db->sf("product_desc"), ENT_QUOTES ), 'product_desc', '550', '300', '55', '25' )
	?>
      </td>
      
      <td valign="top">
    	<fieldset>
        <legend><?php echo JText::_('VM_META_INFORMATION') ?></legend>		
   			<table valign="top">
   				<tr>
   					<td vlaign="top"><div style="text-align:right;font-weight:bold;"><?php echo JText::_('VM_META_DESC'); ?>: </div></td>
      				<td valign="top">
      					<textarea class="inputbox" name="metadesc" id="meta_desc" cols="30" rows="6"><?php echo $db->sf("metadesc"); ?></textarea>
      				</td>
    			</tr>
    			<tr>
    				<td ><div style="text-align:right;font-weight:bold;"><?php echo JText::_('VM_META_KEYWORDS'); ?>: </div></td>
      				<td valign="top">
      					<textarea class="inputbox" name="metakeyword" id="meta_keyword" cols="30" rows="6"><?php echo $db->sf('metakey'); ?></textarea>
      				</td>
    			</tr>
    			<tr>
    				<td ><div style="text-align:right;font-weight:bold;"><?php echo JText::_('VM_META_ROBOTS'); ?>: </div></td>
      				<td valign="top">
      					<input type="text" class="inputbox" size="20" name="metarobot" value="<?php echo $db->sf("metarobot") ?>" />
      				</td>
   				</tr>
    			<tr>
    				<td ><div style="text-align:right;font-weight:bold;"><?php echo JText::_('VM_META_AUTHOR'); ?>: </div></td>
      				<td valign="top">
      					<input type="text" class="inputbox" size="20" name="metaauthor" value="<?php echo $db->sf("metaauthor") ?>" />
      				</td>
    			</tr>
   			</table>
    	</fieldset>
	</td>
   </tr>
  </table>


<?php
$tabs->endTab();

$tabs->startTab( $status_label, "status-page");
?>
<table width="100%">
    <tr>
      <td width="50%">
	  <table class="adminform">
	    <tr class="row1">
	      <td align="left" colspan="2"><?php echo "<h2>$status_label</h2>"; ?></td>
	    </tr>
	    <tr class="row0">
	      <td width="21%"><div style="text-align:right;font-weight:bold;">
	      <?php echo JText::_('VM_PRODUCT_FORM_IN_STOCK') ?>:</div>
	      </td>
	      <td width="79%">
	        <input type="text" class="inputbox"  name="product_in_stock" value="<?php $db->sp("product_in_stock"); ?>" size="10" />
	      </td>
	    </tr>
<!-- low stock notification -->
      <tr class="row1">
					<td width="21%"><div style="text-align:right;font-weight:bold;">
						<?php echo JText::_( 'VM_LOW_STOCK_NOTIFICATION' ); ?>:</div>
					</td>
					<td width="79%">
						<input type="text" class="inputbox" name="low_stock_notification" value="<?php $db->sp("low_stock_notification"); ?>" size="3" />
          </td>
      </tr>
<!-- end low stock notification -->
	    <tr class="row0"> 
	      <td width="21%"><div style="text-align:right;font-weight:bold;">
	      <?php echo JText::_('VM_PRODUCT_FORM_MIN_ORDER') ?>:</div>
	      </td>
	      <td width="79%">
	        <input type="text" class="inputbox"  name="min_order_level" value="<?php echo $min_order; ?>" size="10" />
	      </td>
	    </tr>
	    <tr class="row1"> 
	      <td width="21%"><div style="text-align:right;font-weight:bold;">
	      <?php echo JText::_('VM_PRODUCT_FORM_MAX_ORDER') ?>:</div>
	      </td>
	      <td width="79%">
	        <input type="text" class="inputbox"  name="max_order_level" value="<?php echo $max_order; ?>" size="10" />
	      </td>
	    </tr>
	    <tr class="row0"> 
	      <td width="21%" ><div style="text-align:right;font-weight:bold;">
	        <?php echo JText::_('VM_PRODUCT_FORM_AVAILABLE_DATE') ?>:</div>
	      </td>
	      <td width="79%" >
	          <input class="inputbox" type="text" name="product_available_date" id="product_available_date" size="20" maxlength="19" value="<?php echo date('Y-m-d', $db->sf("product_available_date") ); ?>" />
	          <input name="reset" type="reset" class="button" onClick="return showCalendar('product_available_date', 'y-mm-dd');" value="..." />
	     </td>
	    </tr>
	    <tr class="row1"><td colspan="2">&nbsp;</td></tr>
	    <tr>
	      <td valign="top" width="21%" ><div style="text-align:right;font-weight:bold;">
	          <?php echo JText::_('VM_AVAILABILITY') ?>:</div>
	      </td>
	      <td width="79%" >
	        <input type="text" class="inputbox" name="product_availability" value="<?php $db->sp("product_availability"); ?>" />
	      <?php
	      echo vmToolTip(JText::_('VM_PRODUCT_FORM_AVAILABILITY_TOOLTIP1')); ?>
	      <br /><br />
	        <select class="inputbox" name="image" onchange="javascript:if (document.adminForm.image.options[selectedIndex].value!='') {document.imagelib.src='<?php echo VM_THEMEURL ?>images/availability/' + document.adminForm.image.options[selectedIndex].value; document.adminForm.product_availability.value=document.adminForm.image.options[selectedIndex].value;} else {document.imagelib.src='<?php echo VM_ADMIN_ICON_URL.'images/'.NO_IMAGE;?>'; document.adminForm.product_availability.value=''}">
	          <option value=""><?php echo JText::_('VM_PRODUCT_FORM_AVAILABILITY_SELECT_IMAGE'); ?></option><?php
	          $path = VM_THEMEPATH."images/availability";
	          $files = vmReadDirectory( "$path", ".", true, true);
	          foreach ($files as $file) {
	          	$file_info = pathinfo($file);
	          	$filename = $file_info['basename'];
	                if ($filename != "index.html") {?>
	                <option <?php echo ($db->f("product_availability")==$filename) ? "selected=\"selected\"" : "" ?> value="<?php echo $filename ?>">
	                <?php echo $filename ?>
	                </option><?php
	                }
	            }  ?>
	        </select>&nbsp;
	        <?php
			$pathrelative = str_replace($mosConfig_live_site,'',VM_THEMEURL."images/availability/");
			echo vmToolTip(sprintf(JText::_('VM_PRODUCT_FORM_AVAILABILITY_TOOLTIP2'),$pathrelative));
			?>
	        &nbsp;&nbsp;&nbsp;
	        <img src="<?php echo $db->f("product_availability") ? VM_THEMEURL."images/availability/".$db->sf("product_availability") : VM_ADMIN_ICON_URL.'images/'.NO_IMAGE; ?>" name="imagelib" border="0" alt="Preview" />
	      </td>
	    </tr>
	    <tr class="row1">
	      <td width="21%" ><div style="text-align:right;font-weight:bold;">
	      <?php echo JText::_('VM_PRODUCT_FORM_SPECIAL') ?>:</div>
	      </td>
	      <td width="79%" ><?php if ($db->sf("product_special")=="Y") { ?>
	        <input type="checkbox" name="product_special" value="Y" checked="checked" />
	    <?php    }
	       else { ?>
	        <input type="checkbox" name="product_special" value="Y" />
	    <?php }
	    ?> </td>
	    </tr>
	    <tr class="row0">
	    <td colspan="2">&nbsp;</td>
	    </tr>
	  </table>
	</td>
	<td width="50%" valign="top">
        <table class="adminform">
			<tr class="row1">
		      <td colspan="3"><h2><?php echo JText::_('VM_RELATED_PRODUCTS'); ?></h2></td>
		    </tr>
	          <tr class="row0">
				<td style="vertical-align:top;"><br />
				<?php echo JText::_('VM_PRODUCT_RELATED_SEARCH'); ?>
				<input type="text" size="40" name="search" id="relatedProductSearch" value="" />
				</td>
				<td><input type="button" name="remove_related" onclick="removeSelectedOptions(relatedSelection, 'related_products');" value="&nbsp; &lt; &nbsp;" /></td>
				<td>
				<?php
				$relProducts = array();
				foreach( $related_products as $relProd ) {
					$relProducts[$relProd] = $ps_product->get_field( $relProd, 'product_sku'). ", ". $ps_product->get_field( $relProd, 'product_name');
				}
				echo ps_html::selectList('relProds', '', $relProducts, 10, 'multiple="multiple"', 'id="relatedSelection" ondblclick="removeSelectedOptions(relatedSelection, \'related_products\');"');
				?>
				<input type="hidden" name="related_products" value="<?php echo implode('|', $related_products ) ?>" />
				</td>
			</tr>
		</table>
	</td>
</tr>
</table>
<?php

$tabs->endTab();


$tabs->startTab( 'Product Variants', 'variants-tab');

if( !empty( $product_id )) {
	$db_items = $ps_product->items_sql($product_id);
} else {
	$db_items = new ps_DB();
}
if (!$product_parent_id and $product_id and $db_items->num_rows() > 0) {
	$db_heading = $ps_product->attribute_sql("",$product_id);
	$colspan=3+$db_heading->num_rows();
?>
  <table class="adminlist">
    <tr class="row0">
      <td colspan="<?php echo $colspan ?>">&nbsp;</td>
    </tr>
    <tr class="row1">
      <td colspan="<?php echo $colspan ?>"><h2>
          <?php echo JText::_('VM_PRODUCT_FORM_PRODUCT_ITEMS_LBL') ?></h2>
      </td>
    </tr>
    <tr class="row0">
      <th class="title"><?php echo JText::_('VM_PRODUCT_FORM_NAME') ?></th>
      <th class="title"><?php echo JText::_('VM_PRODUCT_FORM_SKU') ?></th>
      <th class="title"><?php echo JText::_('VM_PRODUCT_FORM_PRICE_NET') ?></th>
      <?php
      while ($db_heading->next_record()) {
		?>
      	<th class="title"><?php echo $db_heading->sf("attribute_name"); ?></th>
      	<?php
      }
?> </tr>
    <?php
    while ($db_items->next_record()) {
?>
    <tr  class="row0">
      <td><?php
	      $url = $_SERVER['PHP_SELF'] . "?page=$modulename.product_form&product_id=" . $db_items->f("product_id") . "&product_parent_id=$product_id";
	      echo "<a href=\"" . $sess->url($url) . "\">". $db_items->f("product_name"). '</a>'; ?>
      </td>
      <td><?php $db_items->p("product_sku"); ?> </td>
      <td><?php
      $price = $ps_product->get_price($db_items->f("product_id"));
      $url  = $_SERVER['PHP_SELF'] . "?page=$modulename.product_price_list&product_id=" . $db_items->f("product_id") . "&product_parent_id=$product_parent_id";
      $url .= "&return_args=" . urlencode("page=$page&product_id=$product_id");
      echo "<a href=\"" . $sess->url($url) . "\">";
      if ($price) {
      	if (!empty($price["item"])) {
      		echo $price["product_price"];
      	} else {
      		echo "none";
      	}
      } else {
      	echo "none";
      }
      echo "</a>";
?> </td>
      	<?php
      	$db_detail = $ps_product->attribute_sql($db_items->f("product_id"),$product_id);
		while ($db_detail->next_record()) {
			echo '<td>'. $db_detail->f("attribute_value").'</td>';
      	}
      ?>
        </tr>
    <?php
    }
?>
  </table>
  <?php
} elseif ($product_parent_id) {
?>
  <table class="adminform">
    <tr class="row0">
      <td colspan="2">&nbsp;</td>
    </tr>
    <tr class="row1">
      <td colspan="2"><strong><?php echo JText::_('VM_PRODUCT_FORM_ITEM_ATTRIBUTES_LBL') ?></strong></td>
    </tr>
    <?php
    if (!empty($_REQUEST['product_id'])) {
    	$db_attribute = $ps_product->attribute_sql($product_id,$product_parent_id);
    } else {
    	$db_attribute = $ps_product->attribute_sql("",$product_parent_id);
    }
    $num = 0;
    while ($db_attribute->next_record()) {
    $num++; ?>
    <tr  class="row<?php echo $num%2 ?>">
      <td width="21%" height="22" >
        <div style="text-align:right;font-weight:bold;"><?php
        echo $db_attribute->sf("attribute_name") . ":";
          $field_name = "attribute_$num"; ?></div>
      </td>
      <td width="79%" >
        <input type="text" class="inputbox"  name="<?php echo $field_name; ?>" size="32" maxlength="255" value="<?php $db_attribute->sp("attribute_value"); ?>" />
      </td>
    </tr>
    <?php
  } ?>
  </table>
  <?php
}
?><table class="adminform">
		<tr class="row0">
	        <td align="right" width="21%" valign="top"><div style="text-align:right;font-weight:bold;"><?php echo JText::_('VM_PRODUCT_FORM_ATTRIBUTE_LIST') ?>:</div></td>
			<td width="79%" id="attribute_container">
				<?php
				// ATTRIBUTE EXTENSION by Tobias (eaxs)
				ps_product_attribute::loadAttributeExtension($db->sf("attribute"));
				?>
	    </tr>
	    <tr class="row0">
	    <td>&nbsp;</td>
	        <td><?php echo JText::_('VM_PRODUCT_FORM_ATTRIBUTE_LIST_EXAMPLES') ?></td>
	    </tr>
	    <tr class="row0">
	    <td colspan="2">&nbsp;</td>
	    </tr>
	    <tr class="row1">
	        <td align="right" width="21%" valign="top"><div style="text-align:right;font-weight:bold;"><?php echo JText::_('VM_PRODUCT_FORM_CUSTOM_ATTRIBUTE_LIST') ?>:</div></td>
	        <td width="79%" >
	        <input class="inputbox" type="text" name="product_custom_attribute" value="<?php $db->sp("custom_attribute"); ?>" size="64" />
	    </tr>
	    <tr class="row1">
	     <td>&nbsp;</td>
	     <td><?php echo JText::_('VM_PRODUCT_FORM_CUSTOM_ATTRIBUTE_LIST_EXAMPLES') ?></td>
        </tr>
	</table>
	<?php
$tabs->endTab();


$tabs->startTab( $dim_weight_label, "about-page");

echo "<h2>$dim_weight_label</h2>";
?>
   <table class="adminform">
    <tr class="row1">
      <td width="21%" valign="top" >
        <div style="text-align:right;font-weight:bold;"><?php echo JText::_('VM_PRODUCT_FORM_LENGTH') ?>:</div>
      </td>
      <td width="79%" >
        <input type="text" class="inputbox"  name="product_length" value="<?php $db->sp("product_length"); ?>" size="15" maxlength="15" />
      </td>
    </tr>
    <tr class="row0">
      <td width="21%" valign="top" >
        <div style="text-align:right;font-weight:bold;"><?php echo JText::_('VM_PRODUCT_FORM_WIDTH') ?>:</div>
      </td>
      <td width="79%" >
        <input type="text" class="inputbox"  name="product_width" value="<?php $db->sp("product_width"); ?>" size="15" maxlength="15" />
      </td>
    </tr>
    <tr class="row1">
      <td width="21%" valign="top" >
        <div style="text-align:right;font-weight:bold;"><?php echo JText::_('VM_PRODUCT_FORM_HEIGHT') ?>:</div>
      </td>
      <td width="79%" >
        <input type="text" class="inputbox"  name="product_height" value="<?php $db->sp("product_height"); ?>" size="15" maxlength="15" />
      </td>
    </tr>
    <tr class="row0">
      <td width="21%" valign="top" >
        <div style="text-align:right;font-weight:bold;"><?php echo JText::_('VM_PRODUCT_FORM_DIMENSION_UOM') ?>:</div>
      </td>
      <td width="79%" >
        <input type="text" class="inputbox"  name="product_lwh_uom" value="<?php $db->sp("product_lwh_uom"); ?>" size="8" maxlength="32" />
      </td>
    </tr>
    <tr class="row1">
      <td width="21%" valign="top" >&nbsp;</td>
      <td width="79%" >&nbsp;</td>
    </tr>
    <tr class="row0">
      <td width="21%" valign="top" >
        <div style="text-align:right;font-weight:bold;"><?php echo JText::_('VM_PRODUCT_FORM_WEIGHT') ?>:</div>
      </td>
      <td width="79%" >
        <input type="text" class="inputbox"  name="product_weight" size="15" maxlength="15" value="<?php $db->sp("product_weight"); ?>" />
      </td>
    </tr>
    <tr class="row1">
      <td width="21%" valign="top" >
        <div style="text-align:right;font-weight:bold;"><?php echo JText::_('VM_PRODUCT_FORM_WEIGHT_UOM') ?>:</div>
      </td>
      <td width="79%" >
        <input type="text" class="inputbox"  name="product_weight_uom" value="<?php $db->sp("product_weight_uom"); ?>" size="8" maxlength="32" />
      </td>
    </tr>
    <!-- Changed Packaging - Begin -->
    <tr class="row0">
      <td width="21%" valign="top" >&nbsp;</td>
      <td width="21%" >&nbsp;</td>
    </tr>
    <tr class="row1">
      <td width="21%" valign="top" >
        <div align="right"><strong><?php echo JText::_('VM_PRODUCT_FORM_UNIT') ?>:</strong></div>
      </td>
      <td width="21%" >
        <input type="text" class="inputbox"  name="product_unit" size="15" maxlength="15" value="<?php $db->sp("product_unit"); ?>" />
      </td>
    </tr>
    <tr class="row0">
      <td width="21%" valign="top" >
        <div align="right"><strong><?php echo JText::_('VM_PRODUCT_FORM_PACKAGING') ?>:</strong></div>
      </td>
      <td width="21%" >
        <input type="text" class="inputbox"  name="product_packaging" value="<?php echo $db->f("product_packaging") & 0xFFFF; ?>" size="8" maxlength="32" />&nbsp;<?php
        echo vmToolTip(JText::_('VM_PRODUCT_FORM_PACKAGING_DESCRIPTION')); ?>
      </td>
    </tr>
    <tr class="row1">
      <td width="21%" valign="top" >
        <div align="right"><strong><?php echo JText::_('VM_PRODUCT_FORM_BOX') ?>:</strong></div>
      </td>
      <td width="21%" >
        <input type="text" class="inputbox"  name="product_box" value="<?php echo ($db->f("product_packaging")>>16)&0xFFFF; ?>" size="8" maxlength="32" />&nbsp;<?php
        echo vmToolTip(JText::_('VM_PRODUCT_FORM_BOX_DESCRIPTION')); ?>
      </td>
    </tr>
    <!-- Changed Packaging - End -->

</table>
<?php
$tabs->endTab();

$tabs->startTab( $display_label, "display-page");

?>
<h2><?php echo $display_label ?></h2>

  <table class="adminform">
    <tr>
      <td align="left" colspan="2"></td>
    </tr>
    <tr class="row1">
      <td width="21%"  style="vertical-align: middle;"><div style="text-align:right;font-weight:bold;"><?php echo JText::_('VM_DISPLAY_USE_PARENT_LABEL'); ?></div>
      </td>
      <td width="79%" style="vertical-align: middle;" colspan="2">
      	<input type="checkbox" class="checkbox"  id="display_use_parent" name="display_use_parent" value="Y" <?php
      if (@$display_use_parent == "Y" && !$display_use_parent_disabled) echo "checked=\"checked\"";
        else if($display_use_parent_disabled) {
        	echo ' disabled="disabled" ';
        }   ?>  />
      <label for="display_use_parent" ><?php echo JText::_('VM_DISPLAY_USE_PARENT'); ?></label><br/>
      </td>
    </tr>
    <tr class="row0">
      <td width="21%"  style="vertical-align: top;"><div style="text-align:right;font-weight:bold;"><?php echo JText::_('VM_DISPLAY_LIST_TYPE'); ?></div>
      </td>
      <td width="20%"  style="vertical-align: top;"> <?php
      echo "<input type=\"checkbox\" style=\"vertical-align: middle;\" class=\"checkbox\" id=\"product_list_check\" name=\"product_list\" value=\"Y\" onclick=\"javascript: toggleProductList( this.checked);\" ";

      if (@$product_list =="Y" || @$product_list =="YM" ) {
      	echo "checked=\"checked\" ";
      }

      if($product_parent_id !=0) {
      	echo ' disabled="disabled" ';
      }
      echo '/> <label for="product_list_check">'.JText::_('VM_DISPLAY_USE_LIST_BOX').'</label>';
      //Formatting Code
?> <br />

       <?php
       echo "<input type=\"checkbox\" style=\"vertical-align: middle;\" class=\"checkbox\" id=\"display_desc\" name=\"display_desc\" value=\"Y\" ";
       if ($display_desc) {
       	echo 'checked="checked" ';
       }
    echo '/> <label for="display_desc">'.JText::_('VM_DISPLAY_CHILD_DESCRIPTION').'</label><br />
    		<input type="inputbox" style="vertical-align: middle;" class="inputbox" size="8" id="desc_width" name="desc_width" value="'.$desc_width.'" />';
    echo JText::_('VM_DISPLAY_DESC_WIDTH'); ?>
    <br />
    <?php
    echo "<input type=\"inputbox\" style=\"vertical-align: middle;\" class=\"inputbox\" size=\"8\" id=\"attrib_width\" name=\"attrib_width\" value=\"$attrib_width\"  ";
    echo "/> ".JText::_('VM_DISPLAY_ATTRIB_WIDTH'); ?>
    <br />
    <?php
    echo JText::_('VM_DISPLAY_CHILD_SUFFIX')."<br /><input type=\"inputbox\" style=\"vertical-align: middle;\" class=\"inputbox\" size=\"20\" id=\"child_class_sfx\" name=\"child_class_sfx\" value=\"$child_class_sfx\"  ";
    echo "/> "; ?>
    <br />
      </td>

        <td width="20%" >
        <fieldset>
            <legend><?php echo JText::_('VM_DISPLAY_LIST_STYLE'); ?></legend>

        <input type="radio" class="radio" style="vertical-align: middle;" id="list_style0" name="list_style" value="one"
        <?php if (@$product_list == "Y") echo "checked=\"checked\"";
        if($product_parent_id !=0 || @$product_list =="" || @$product_list =="N") {
        	echo ' disabled="disabled" ';
        }
        ?>
        />
        <label for="list_style0" style="vertical-align: middle;"><?php echo JText::_('VM_DISPLAY_ONE'); ?></label><br/>
        <input type="radio" class="radio" style="vertical-align: middle;" id="list_style1" name="list_style" value="many"
        <?php
        if (@$product_list == "YM") echo "checked=\"checked\"";
        if($product_parent_id !=0 || @$product_list =="" || @$product_list =="N") {
        	echo ' disabled="disabled" ';
        }
        ?>
        />
        <label for="list_style1" style="vertical-align: middle;"><?php echo JText::_('VM_DISPLAY_MANY') ?> </label><br />
        <?php if (@$display_header =="Y" && (@$product_list =="Y" || @$product_list =="YM" )) {
        	echo "<input type=\"checkbox\" style=\"vertical-align: middle;\" class=\"checkbox\" id=\"display_headers\" name=\"display_headers\" value=\"Y\" checked=\"checked\" ";
        }
        else {
        	echo "<input type=\"checkbox\" style=\"vertical-align: middle;\" class=\"checkbox\" id=\"display_headers\" name=\"display_headers\" value=\"Y\" ";
        }
        if (@$product_list =="Y"  || @$product_list =="YM" ) {
            echo " /> "; }
        else {
            echo ' disabled=true /> ';
        }
        echo JText::_('VM_DISPLAY_TABLE_HEADER');
?> <br />

        <?php if (@$product_list_child =="Y" && (@$product_list =="Y"  || @$product_list =="YM" )) {
        	echo "<input type=\"checkbox\" style=\"vertical-align: middle;\" class=\"checkbox\" id=\"product_list_child\" name=\"product_list_child\" value=\"Y\" checked=\"checked\" ";
        }
        else {
        	echo "<input type=\"checkbox\" style=\"vertical-align: middle;\" class=\"checkbox\" id=\"product_list_child\" name=\"product_list_child\" value=\"Y\" ";
        }
        if (@$product_list =="Y"  || @$product_list =="YM" ) {
            echo " /> "; }
        else {
            echo ' disabled=true /> ';
        }

        echo JText::_('VM_DISPLAY_LINK_TO_CHILD')."<br />";
?>

        <?php if (@$product_list_type =="Y" && (@$product_list =="Y"  || @$product_list =="YM" )) {
        	echo "<input type=\"checkbox\" style=\"vertical-align: middle;\" class=\"checkbox\" id=\"product_list_type\" name=\"product_list_type\" value=\"Y\" checked=\"checked\" ";
        }
        else {
        	echo "<input type=\"checkbox\" style=\"vertical-align: middle;\" class=\"checkbox\" id=\"product_list_type\" name=\"product_list_type\" value=\"Y\" ";
        }
        if (@$product_list =="Y"  || @$product_list =="YM" ) {
            echo " /> "; }
        else {
            echo " disabled=true /> ";
        }
        echo JText::_('VM_DISPLAY_INCLUDE_PRODUCT_TYPE');
?>

        </fieldset>
        </td>
        <td width="39%">
        </td>
    </tr>
    <tr class="row1">
      <td width="21%"  style="vertical-align: top;"><div style="text-align:right;font-weight:bold;"><?php echo JText::_('VM_DISPLAY_CHILD_ORDER_BY'); ?></div>
      </td>
      <td width="79%" colspan="2">
      <?php 
      $order_list =array();
      $order_list['`#__{vm}_product`.`product_sku`'] = JText::_('VM_CART_SKU');
      $order_list['`#__{vm}_product`.`product_id`'] = 'Child Product ID';
      $order_list['`#__{vm}_product`.`product_name`'] = JText::_('VM_PRODUCT_NAME_TITLE');
      echo ps_html::selectList('child_order_by', $child_order_by, $order_list, 1, '', 'id="child_order_by"'); ?>
      
      <label for="child_order_by" style="vertical-align: middle;"><?php echo JText::_('VM_DISPLAY_CHILD_ORDER_DESC'); ?></label><br/>
      </td>
    </tr>
    <tr class="row0">
      <td width="21%"  style="vertical-align: top;"><div style="text-align:right;font-weight:bold;"><?php echo JText::_('VM_EXTRA_PRODUCT_ID'); ?></div>
      </td>
      <td width="79%" colspan="2"><input type="inputbox" class="inputbox" size="35" id="included_product_id" name="included_product_id" value="<?php echo $db->f("child_option_ids") ?>" />
      <label for="included_product_id" style="vertical-align: middle;"><?php echo JText::_('VM_INCLUDED_PRODUCT_ID'); ?></label><br/>
      </td>
    </tr>
</table>
<table class="adminform">
    <tr class="row0">
        <td width="21%" style="vertical-align: top;">
        <fieldset>
                <legend><?php echo JText::_('VM_DISPLAY_QUANTITY_LABEL') ?></legend>

            <input type="radio" class="radio" style="vertical-align: middle;" id="quantity_box0" name="quantity_box" value="none" <?php
            	if ($display_type == "none") echo "checked=\"checked\""; ?>  />
            <label for="quantity_box0" style="vertical-align: middle;"><?php echo JText::_('VM_DISPLAY_NORMAL'); ?></label><br/>
            <input type="radio" class="radio" style="vertical-align: middle;" id="quantity_box1" name="quantity_box" value="hide" <?php
            	if ($display_type == "hide") echo "checked=\"checked\""; ?> />
            <label for="quantity_box1" style="vertical-align: middle;"><?php echo JText::_('VM_DISPLAY_HIDE') ?> </label><br />
            <input type="radio" class="radio" style="vertical-align: middle;" id="quantity_box2" name="quantity_box" value="drop" <?php
            	if ($display_type == "drop") echo "checked=\"checked\""; ?> />
            <label for="quantity_box2" style="vertical-align: middle;"><?php echo JText::_('VM_DISPLAY_DROPDOWN') ?> </label><br />
            <input type="radio" class="radio" style="vertical-align: middle;" id="quantity_box3" name="quantity_box" value="check" <?php
            	if ($display_type == "check") echo "checked=\"checked\""; ?>
            />
            <label for="quantity_box3" style="vertical-align: middle;"><?php echo JText::_('VM_DISPLAY_CHECKBOX') ?> </label><br />
            <input type="radio" class="radio" style="vertical-align: middle;" id="quantity_box4" name="quantity_box" value="radio" <?php
	            if ($display_type == "radio") echo 'checked="checked"';
	            if($product_parent_id !=0) echo ' disabled="true"'; ?>  />
            <label for="quantity_box4" style="vertical-align: middle;"><?php echo JText::_('VM_DISPLAY_RADIOBOX') ?> </label><br />

        </td>
        <td width="20%" style="vertical-align: top;">
            <fieldset>
                <legend><?php echo JText::_('VM_DISPLAY_QUANTITY_DROPDOWN_LABEL') ?></legend>
            <input type="text" class="inputbox" style="vertical-align: middle;" id="quantity_start" name="quantity_start" size="4" value="<?php echo $quantity_start; ?>" />
            <label for="quantity_start" style="vertical-align: middle;"><?php echo JText::_('VM_DISPLAY_START') ?> </label><br />
            <input type="text" class="inputbox" style="vertical-align: middle;" id="quantity_end" name="quantity_end" size="4" value="<?php echo $quantity_end; ?>" />
            <label for="quantity_end" style="vertical-align: middle;"><?php echo JText::_('VM_DISPLAY_END') ?> </label><br />
            <input type="text" class="inputbox" style="vertical-align: middle;" id="quantity_step" name="quantity_step" size="4" value="<?php echo $quantity_step; ?>" />
            <label for="quantity_step" style="vertical-align: middle;"><?php echo JText::_('VM_DISPLAY_STEP') ?> </label><br />
            </fieldset>
        </td>
        <td width="39%">
        </td>
    </tr>
  </table>
<?php
$tabs->endTab();

$tabs->startTab( $images_label, "images-page");

$ps_html->writableIndicator( array( IMAGEPATH."product", IMAGEPATH."product/resized") );

 ?>
  <table class="adminform" >
    <tr>
      <td valign="top" width="50%" style="border-right: 1px solid black;">
        <h2><?php echo JText::_('VM_PRODUCT_FORM_FULL_IMAGE') ?></h2>
        <table class="adminform">
          <tr class="row0">
            <td colspan="2" ><?php
            if ($product_id) {
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
              if( function_exists('imagecreatefromjpeg')) { ?>
	              <input type="radio" class="inputbox" id="product_full_image_action1" name="product_full_image_action" value="auto_resize" onchange="toggleDisable( document.adminForm.product_full_image_action[1], document.adminForm.product_thumb_image, true );toggleDisable( document.adminForm.product_full_image_action[1], document.adminForm.product_thumb_image_url, true );"/>
	              <label for="product_full_image_action1"><?php echo JText::_('VM_FILES_FORM_AUTO_THUMBNAIL') . "</label><br />";
              }
              if ($product_id and $db->f("product_full_image")) { ?>
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
              if( stristr($db->f("product_full_image"), "http") )
              $product_full_image_url = $db->f("product_full_image");
              else if(!empty($_REQUEST['product_full_image_url']))
              $product_full_image_url = JRequest::getVar( 'product_full_image_url');
              else
              $product_full_image_url = "";
              ?>
              <input type="text" class="inputbox" size="50" name="product_full_image_url" value="<?php echo $product_full_image_url ?>" onchange="if( this.value.length>0) document.adminForm.product_full_image_action[1].checked=false; else document.adminForm.product_full_image_action[1].checked=true; toggleDisable( document.adminForm.product_full_image_action[1], document.adminForm.product_thumb_image_url, true );toggleDisable( document.adminForm.product_full_image_action[1], document.adminForm.product_thumb_image, true );" />
            </td>
          </tr>
          <tr class="row1"><td colspan="2">&nbsp;</td></tr>
          <tr class="row1">
            <td colspan="2" >
              <div style="overflow:auto;">
                <?php
                if( $clone_product != "1" ) {
                	echo $ps_product->image_tag($db->f("product_full_image"), "", 0);
                }
                ?>
              </div>
            </td>
          </tr>
        </table>
      </td>

      <td valign="top" width="50%">
        <h2><?php echo JText::_('VM_PRODUCT_FORM_THUMB_IMAGE') ?></h2>
        <table class="adminform">
          <tr class="row0">
            <td colspan="2" ><?php if ($product_id) {
                echo JText::_('VM_PRODUCT_FORM_IMAGE_UPDATE_LBL') . "<br />"; } ?>
              <input type="file" class="inputbox" name="product_thumb_image" size="50" maxlength="255" onchange="if(document.adminForm.product_thumb_image.value!='') document.adminForm.product_thumb_image_url.value='';" />
            </td>
          </tr>
          <tr class="row1">
            <td colspan="2" ><div style="font-weight:bold;"><?php echo JText::_('VM_IMAGE_ACTION') ?>:</div><br/>
              <input type="radio" class="inputbox" id="product_thumb_image_action0" name="product_thumb_image_action" checked="checked" value="none" onchange="toggleDisable( document.adminForm.product_thumb_image_action[1], document.adminForm.product_thumb_image, true );toggleDisable( document.adminForm.product_thumb_image_action[1], document.adminForm.product_thumb_image_url, true );"/>
              <label for="product_thumb_image_action0"><?php echo JText::_('VM_NONE') ?></label><br/>
              <?php
              if ($product_id and $db->f("product_thumb_image")) { ?>
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
              if( stristr($db->f("product_thumb_image"), "http") )
              $product_thumb_image_url = $db->f("product_thumb_image");
              else if(!empty($_REQUEST['product_thumb_image_url']))
              $product_thumb_image_url = JRequest::getVar( 'product_thumb_image_url');
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
                <?php
                if( $clone_product != "1" ) {
                	echo $ps_product->image_tag($db->f("product_thumb_image"), "", 0);
                }
                ?>
              </div>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

<?php
$tabs->endTab();

while ($db_ptypes->next_record()) {

	$product_type_id = $db_ptypes->f("product_type_id");

	$tabs->startTab( $db_ptypes->f('product_type_name'), "parameter-page-$product_type_id");
	$ps_product_type = new ps_product_type;
	echo $ps_product_type->get_product_type_form($product_type_id, $product_id);

	$tabs->endTab();
  //
}
// if there are currently no product types, this product is assigned to (e.g. if it's a new product) we offer a drop down list
// with all available Product Types
if( $db_ptypes->num_rows() < 1 ) {
	$q = 'SELECT product_type_id,product_type_name FROM #__{vm}_product_type ORDER BY product_type_name';
	$db_ptypes->query( $q );

	if( $db_ptypes->num_rows() > 0 ) {
		$tabs->startTab('Product Types', 'product-types' );
		echo '<br />
		<div style="padding-left: 20px;">
		<h2>Product Types</h2>
		<br />
		';

		$types = array(''=> JText::_('VM_SELECT'));
		while( $db_ptypes->next_record() ) {
			$types[$db_ptypes->f('product_type_id')] = $db_ptypes->f('product_type_name');
		}
		echo ps_html::selectList('product_type_id', 0, $types, 1, '', 'onchange="loadProductTypeForm(this.options[this.selectedIndex].value)"' );
		echo '<br />
		<br />
		<div id="ProductTypeFormContainer"></div>';
		echo '</div>';
		$tabs->endTab();
	}
}
if( $clone_product == "1" ) {

	echo '<input type="hidden" name="clone_product" value="Y" />';
	echo '<input type="hidden" name="old_product_id" value="'.JRequest::getVar( 'product_id').'" />';
	$db_att = new ps_DB;
	$db->query( "SELECT product_id, product_name
                FROM #__{vm}_product
                WHERE product_parent_id='".JRequest::getVar( 'product_id')."' " );
	if( $db->num_rows() > 0 ) {
		$tabs->startTab( JText::_('VM_PRODUCT_CLONE_OPTIONS_TAB'), 'clone-page' );
		echo "<h3>" . JText::_('VM_PRODUCT_CLONE_OPTIONS_LBL') . ":</h3>";

		while( $db->next_record() ) {
			$db_att->query( "SELECT attribute_name, attribute_value FROM #__{vm}_product_attribute
	                      WHERE product_id ='".$db->f("product_id")."'" );
			echo '<input type="checkbox" checked="checked" name="child_items[]" value="'.$db->f("product_id").'" id="child_'.$db->f("product_id").'" />
	    <label for="child_'.$db->f("product_id").'">'.$db->f("product_name").' (';
			while( $db_att->next_record() ) {
				echo $db_att->f("attribute_name").": ".$db_att->f("attribute_value")."; ";
			}
			echo ')</label><br/>';
		}

		$tabs->endTab();
	}
}

if( $product_id ) {
	// SHOW THE WAITING LIST!
	$dbw = new ps_DB;
	$dbw->query( 'SELECT name, username, user_id, notify_email, notified, notify_date FROM `#__{vm}_waiting_list`
					LEFT JOIN `#__users` ON `user_id` = `id`
					WHERE `product_id`=' . $product_id );
	if( $dbw->num_rows() > 0 ) {
		$tabs->startTab( JText::_('PRODUCT_WAITING_LIST_TAB'), 'waiting-list-tab' );

		echo '<table class="adminform"><tr><td><h2>' . JText::_('PRODUCT_WAITING_LIST_USERLIST') . ':</h2></td></tr>';
		echo '<tr><td><input type="hidden" value="'.$db->f('product_in_stock').'" name="product_in_stock_old" />';
		echo '<input type="checkbox" value="1" checked="checked" id="notify_users" name="notify_users" /> <label for="notify_users">' . JText::_('PRODUCT_WAITING_LIST_NOTIFYUSERS') . '</label><br /><br /></td></tr>';
		echo '<tr><td>';
		while( $dbw->next_record() ) {
			if ($dbw->f("notified")==1) {
				$waiting_notified = ' - <strong style="font-weight:bold">' . JText::_('PRODUCT_WAITING_LIST_NOTIFIED') . ' ' . $dbw->f("notify_date") . '</strong>';
			} else {
				$waiting_notified = '';
			}
			if ($dbw->f("user_id")==0) {
				$waitinglist[] = '<a href="mailto:' . $dbw->f('notify_email') . '">' . $dbw->f('notify_email') . '</a>' . $waiting_notified;
			} else {
				$waitinglist[] = $dbw->f('name') . ' ('.$dbw->f('username') . ' - ' . '<a href="mailto:' . $dbw->f('notify_email') . '">' . $dbw->f('notify_email') . '</a>' . ')' . $waiting_notified;
			}
		}
		echo vmCommonHTML::getList( $waitinglist );
		echo '</td></tr></table>';
		$tabs->endTab();
	}
}
$tabs->endPane();

// Add necessary hidden fields
$formObj->hiddenField( 'product_id', $product_id );
$formObj->hiddenField( 'product_parent_id', $product_parent_id );
$formObj->hiddenField( 'pshop_mode', 'admin' );

if( !stristr( $db->f("product_thumb_image"), "http") && $clone_product != "1" ) {
	$formObj->hiddenField( 'product_thumb_image_curr', $db->f("product_thumb_image") );
}
if( !stristr( $db->f("product_full_image"), "http") && $clone_product != "1" ) {
	$formObj->hiddenField( 'product_full_image_curr', $db->f("product_full_image") );
}

$funcname = !empty($product_id) ? "productUpdate" : "productAdd";

// finally close the form:
$formObj->finishForm( $funcname, $next_page, $option );

?>
<script type="text/javascript">//<!--
<?php include($mosConfig_absolute_path.'/components/com_virtuemart/js/product_form.js.php' ) ?>
//-->
</script>
