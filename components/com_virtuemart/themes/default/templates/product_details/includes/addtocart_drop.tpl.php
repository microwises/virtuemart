<?php if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); ?>

<div class="vmCartDetails<?php echo $cls_suffix; ?>">

<?php 
if(USE_AS_CATALOGUE != '1' && ($products[0]['advanced_attribute'] != "" || $products[0]['custom_attribute'] !="")) { ?>
    <div class="vmCartChild<?php echo $cls_suffix ?> vmRowTwo<?php echo $cls_suffix ?>">
  	<?php 
}
?>
<input type="hidden" name="product_id" value="<?php echo $parent_id ?>" >
<label for="product_id_field"><?php echo JText::_( 'VM_PLEASE_SEL_ITEM' ) ?></label>: <br />
<?php 
if( VM_CONTENT_PLUGINS_ENABLE == '1' ) { ?>
	<select class="inputbox" onchange="var id = $('product_id_field')[selectedIndex].value; if(id != '') { document.location = '<?php echo  $mm_action_url . "index.php?option=com_virtuemart&amp;page=shop.product_details&amp;flypage=$flypage&amp;Itemid=$Itemid&amp;category_id=$category_id&amp;product_id=" ?>' + id; }" id="product_id_field" name="prod_id[]">
	<?php 	} else { ?>
	<select class="inputbox" onchange="var id = $('product_id_field')[selectedIndex].value; if(id != '') { loadNewPage( 'vmMainPage', '<?php echo $mm_action_url . "index.php?option=com_virtuemart&amp;page=shop.product_details&amp;flypage=$flypage&amp;Itemid=$Itemid&amp;category_id=$category_id&amp;product_id=" ?>' + id ); }" id="product_id_field" name="prod_id[]">
	<?php } ?>
<option value="<?php echo $parent_id ?>"><?php echo JText::_( 'VM_SELECT' ) ?></option>
<?php 
foreach( $products as $product ) { 			
	foreach( $product as $attr => $val ) {
			// Using this we make all the variables available in the template
			// translated example: $this->set( 'product_name', $product_name );
			$this->set( $attr, $val );
    } 
    $selected = isset( $_REQUEST['product_id'] ) ? ($product["product_id"] == $_REQUEST['product_id'] ? 'selected="selected"' : '') : '' ;
    
    ?>
    <option value="<?php echo $product["product_id"] ?>" <?php echo $selected ?>><?php echo $product["product_title"] ?> 
   	<?php 
   	$hc = 0;
   	$text = "";
   	foreach($headings as $key => $value) {
   		if($product['attrib_value'][$hc] != "") { 
   			if($hc == 0) {
   				$text = " - ";
   			}
   			$text .= $headings[$key]." - ".$product['attrib_value'][$hc].";";
   		}
   		$hc++;
   	}
   	$text = substr($text,0,-1);
   	
	echo $text." - ".$product['actual_price'];

 } ?></option></select>
<?php 
if(USE_AS_CATALOGUE != '1' && ($product['advanced_attribute'] != "" || $product['custom_attribute'] != "")) { ?>
  	<div class="vmCartAttributes<?php echo $cls_suffix ?>">
  	<?php   
  	if($product['advanced_attribute']) {
		echo $product['advanced_attribute'];
  	}
	if($product['custom_attribute']) {
		echo $product['custom_attribute'];
	}
  	?>
	</div>
	<?php 
} ?>
<?php 
if(USE_AS_CATALOGUE != '1' && ($product['advanced_attribute'] != "" || $product['custom_attribute'] !="")) { ?>
	</div>
	<?php 
}?> 
</div>
