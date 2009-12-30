<?php if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); ?>

<div class="vmCartContainer">
<?php
mm_showMyFileName(__FILE__);

$pageName = JRequest::getVar('page', '');
$pageName = trim($pageName);
$showDetails = false;

//atm only the shop.product_details case is used would be nice to write a nice object instead of the both addtocart_form.tpl.php

if($pageName=="shop.product_details"){

	// This function lists all product children ( = Items)
	// or, when not children are defined, the product_id
	// SO LEAVE THIS IN HERE!
	
	list($html,$children) = $ps_product_attribute->list_attribute( ( $product_parent_id > 0 )  ? $product_parent_id : $product_id );
	
	if ($children != "multi") { 
	
	    if( CHECK_STOCK == '1' && !$product_in_stock && $children != "list") {
	     	$notify = true;
	    } else {
	    	$notify = false;
	    }
	
	?>
	    <form action="<?php echo JROUTE::_('index.php?option=com_virtuemart'); ?>" method="post" name="addtocart" id="<?php echo uniqid('addtocart_') ?>" class="addtocart_form" <?php if( $this->get_cfg( 'useAjaxCartActions', 1 ) && !$notify ) { echo 'onsubmit="handleAddToCart( this.id );return false;"'; } ?>>
	
	<?php  
	}
	echo $html;
//	echo('yepp we are here $product_price '.$product_price.'  VM_PRODUCT_CALL '.JText::_('VM_PRODUCT_CALL'));
	if (USE_AS_CATALOGUE != '1' && $product_price != "" && !stristr( $product_price, JText::_('VM_PRODUCT_CALL') )) {
		?>
	        <?php if ($children != "multi") { ?> 
	    <div style="float: right;vertical-align: middle;"> <?php 
	    if ($children == "drop") { 
	    	echo $ps_product_attribute->show_quantity_box($product_parent_id,$product_id,null,"Y");
	    } 
	    if ($children == "radio") {
			echo $ps_product_attribute->show_radio_quantity_box();
	    }
	    $button_lbl = JText::_('VM_CART_ADD_TO');
	    $button_cls = 'addtocart_button';
	    if( CHECK_STOCK == '1' && !$product_in_stock && $children != "list") {
	     	$button_lbl = JText::_('VM_CART_NOTIFY');
	     	$button_cls = 'notify_button';
	    }
	  ?>    
	    <input type="submit" class="<?php echo $button_cls ?>" value="<?php echo $button_lbl ?>" title="<?php echo $button_lbl ?>" />
	    </div>
	    <?php  } ?>    
	    <input type="hidden" name="flypage" value="shop.<?php echo $flypage ?>" />
		<input type="hidden" name="page" value="shop.cart" />
	    <input type="hidden" name="manufacturer_id" value="<?php echo $manufacturer_id ?>" />
	    <input type="hidden" name="category_id" value="<?php echo $category_id ?>" />
	    <input type="hidden" name="func" value="cartAdd" />
	    <input type="hidden" name="option" value="<?php echo $option ?>" />
	    <input type="hidden" name="Itemid" value="<?php echo $Itemid ?>" />
	    <input type="hidden" name="set_price[]" value="" />
	    <input type="hidden" name="adjust_price[]" value="" />
	    <input type="hidden" name="master_product[]" value="" />
	    <?php
	}
	if ($children != "multi") { ?>
		</form>
	<?php 
	} 
	    if($children == "radio") { ?>
	    
	    <script language="JavaScript" type="text/javascript">//<![CDATA[
	    function alterQuantity(myForm) {
	        for (i=0;i<myForm.selItem.length;i++){
	            setQuantity = myForm.elements['quantity'];
	            selected = myForm.elements['selItem'];
	            j = selected[i].id.substr(7);
	            k= document.getElementById('quantity' + j);
	            if (selected[i].checked==true){
	                k.value = myForm.quantity_adjust.value; }
	            else {
	                k.value  = 0;
	            }
	        }
	    }
		//]]>   
		</script>
	<?php } ?>
	</div>
	
<?php } else {
	
	global $ps_product;
	$p_has_a = false;
	$button_lbl = JText::_('VM_CART_ADD_TO');
	$button_cls = 'addtocart_button';
	if( CHECK_STOCK == '1' && !$product_in_stock && !$ps_product->parent_has_children($product_id) && !$ps_product->product_has_attributes($product_id, true) && !$call_for_pricing) {
		$button_lbl = JText::_('VM_CART_NOTIFY');
		$button_cls = 'notify_button';
		$notify = true;
	} elseif ($ps_product->parent_has_children($product_id) || $ps_product->product_has_attributes($product_id, true) || $call_for_pricing) {
		if($call_for_pricing) {
			$button_lbl = "Details";
			$button_cls = 'details_button';
		} else {
			$button_lbl = "Options";
			$button_cls = 'options_button';
		}
		$notify = true;
		$p_has_a = true;
	} else {
		$notify = false;
	}
	?>
	
	<form action="<?php echo JROUTE::_('index.php?option=com_virtuemart'); ?>" method="post" name="addtocart" id="addtocart<?php echo $i ?>" class="addtocart_form" <?php if( $this->get_cfg( 'useAjaxCartActions', 1 ) && !$notify ) { echo 'onsubmit="handleAddToCart( this.id );return false;"'; } ?>>
	    <?php if(!$notify) { echo $ps_product_attribute->show_quantity_box($product_id,$product_id); } ?>
		<input type="submit" class="<?php echo $button_cls ?>" value="<?php echo $button_lbl	?>" title="<?php echo $button_lbl ?>" />
	    <input type="hidden" name="category_id" value="<?php echo  @$_REQUEST['category_id'] ?>" />
	    <input type="hidden" name="product_id" value="<?php echo $product_id ?>" />
	    <input type="hidden" name="prod_id[]" value="<?php echo $product_id ?>" />
	    <input type="hidden" name="flypage" value="<?php echo $flypage ?>" />
	    <input type="hidden" name="page" value="shop.cart" />
	    <input type="hidden" name="func" value="cartadd" />
	    <input type="hidden" name="Itemid" value="<?php echo $sess->getShopItemid() ?>" />
	 	<input type="hidden" name="option" value="com_virtuemart" />
	    <input type="hidden" name="set_price[]" value="" />
	    <input type="hidden" name="adjust_price[]" value="" />
	    <input type="hidden" name="master_product[]" value="" />
	    <input type="hidden" name="overide_error" value="<?php echo $p_has_a ?>" />
	</form>	
	<?php
}?>