<?php if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); ?>

<?php
mm_showMyFileName(__FILE__);

// Start Ouputing the Child Detail
?>
<div class="vmCartDetails<?php echo $cls_suffix ?>">
<!-- Output The heading -->
<?php if($display_header == "Y") { ?>
    <div class="vmCartChildHeading<?php echo $cls_suffix ?>">
        <span style="float: left;width: <?php echo $desc_width ?>;"><?php echo JText::_('VM_PRODUCT_DESC_TITLE') ?></span >
        <?php //Ouput Each Attribute Heading
        if( !empty( $headings )) {
	        foreach($headings as $key => $value) { ?>
	            <span style="float: left;width: <?php echo $attrib_width ?>;" ><?php echo $headings[$key] ?></span>
	        <?php } ?>
	        <span style="float: right;width: 15%;">&nbsp;</span>
	        <span style="float: right;width: 10%;"><?php echo JText::_('VM_CART_QUANTITY') ?></span>
	        <span style="float: right;width: 12%;"><?php echo JText::_('VM_PRODUCT_INVENTORY_PRICE') ?></span>
	    </div><br/>
	<?php }
		}
// Loop through each row and build the table
foreach( $products as $product ) { 		

    foreach( $product as $attr => $val ) {
			// Using this we make all the variables available in the template
			// translated example: $this->set( 'product_name', $product_name );
			$this->set( $attr, $val );
    }
    
    if( CHECK_STOCK == '1' && !$product['product_in_stock'] ) {
     	$notify = true;
    } else {
    	$notify = false;
    }
    
    ?>

    <div class="vmCartChild<?php echo $cls_suffix." ".$product['bgcolor'].$cls_suffix ?>">
        <form action="<?php echo $mm_action_url ?>index.php" method="post" name="addtocart" id="addtocart<?php echo $product['product_id'] ?>" class="addtocart_form" <?php if( $this->get_cfg( 'useAjaxCartActions', 1 ) && !$notify ) { echo 'onsubmit="handleAddToCart( this.id );return false;"'; } ?>>
            <div class="vmCartChildElement<?php echo $cls_suffix ?>">
                <input type="hidden" name="prod_id[]" value="<?php echo $product['product_id'] ?>" />
                <input type="hidden" name="product_id" value="<?php echo $product['parent_id'] ?>" />
                <span class="vmChildDetail<?php echo $cls_suffix ?>" style="float: left;width: <?php echo $desc_width ?>;" />
                <?php echo $product['product_title'] ?></span>
                <?php // Ouput Each Attribute
                if( !empty( $product['attrib_value'] )) {
	                foreach($product['attrib_value'] as $attribute) { ?>
	                    <span class="vmChildDetail<?php echo $cls_suffix ?>" style="float: left;width :<?php echo $attrib_width ?>;" />
		                <?php echo " ".$attribute ?></span>
	                <?php 
					}
				} 
                if (USE_AS_CATALOGUE != '1'  && $product_price != "" && !stristr( $product_price, JText::_('VM_PRODUCT_CALL'))) { 

					$button_lbl = JText::_('VM_CART_ADD_TO');
					$button_cls = 'addtocart_button';
					if( CHECK_STOCK == '1' && !$product['product_in_stock'] ) {
						$button_lbl = JText::_('VM_CART_NOTIFY');
						$button_cls = 'notify_button';
					}

                	?>
                    <span class="vmChildDetail<?php echo $cls_suffix ?>" style="float: right;text-align: right;margin-top: 0px;">
                    <input type="submit" class="<?php echo $button_cls ?>" value="<?php echo $button_lbl ?>" title="<?php echo $button_lbl ?>" /></span>
                <?php } 
                // Output Quantity Box 
                if (USE_AS_CATALOGUE != '1' ) { ?>
                    <span class="vmChildDetail<?php echo $cls_suffix ?>" style="float: right;text-align: right;margin-top: 0px;"><?php echo $product['quantity_box'] ?></span>
                <?php } 
                // Output Price 
                if( $_SESSION['auth']['show_prices'] && _SHOW_PRICES) {  ?>           
                    <span class="vmChildDetail<?php echo $cls_suffix ?>" style="float: right;text-align: right;padding-right:5px;" >
                    <?php
                    if( $product['price'] != $product['actual_price'] ) { ?>
                        <span class="product-Old-Price"><?php echo $product['price'] ?>&nbsp;</span>
                    <?php } 
                    echo $product['actual_price'] ?></span>
                <?php } ?>
            </div>
            <br style="clear: both;">
            <input type="hidden" name="flypage" value="shop.<?php echo $product['flypage'] ?>" />
            <input type="hidden" name="category_id" value="<?php echo $product['category_id'] ?>" />
            <input type="hidden" name="page" value="shop.cart" />
            <input type="hidden" name="func" value="cartAdd" />
            <input type="hidden" name="option" value="com_virtuemart" />
            <input type="hidden" name="Itemid" value="<?php echo $product['Itemid'] ?>" />
            <input type="hidden" name="set_price[]" value="" />
            <input type="hidden" name="adjust_price[]" value="" />
            <input type="hidden" name="master_product[]" value="" />    
            <?php
            // Out Put Product Type 
            if ($display_product_type == "Y" && $product['product_type'] != "") { ?>  
                <div class="vmChildType<?php echo $cls_suffix ?>">
                <?php echo $product['product_type'] ?>
                </div>
            <?php } 
            // Output Advanced & Custom Attributes
            if(USE_AS_CATALOGUE != '1' && ($product['advanced_attribute'] != "" || $product['custom_attribute'] != "")) { ?>
                <div class="vmCartAttributes<?php echo $cls_suffix ?>">
                    <?php if($product['advanced_attribute']) {
                        echo $product['advanced_attribute'];
                    }
                    if($product['custom_attribute']) {
                        echo $product['custom_attribute'];
                    }
                ?>
                </div>
            <?php } ?>
        </form>
            </div>
    
    <?php } ?>
    </div >