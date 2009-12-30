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
            foreach($headings as $key => $value) { ?>
                <span style="float: left;width: <?php echo $attrib_width ?>;" ><?php echo $headings[$key] ?></span>
            <?php } ?>
            <span style="float: right;width: 15%;"><?php echo JText::_('VM_CART_QUANTITY') ?></span>
            <span style="float: right;width: 12%;"><?php echo JText::_('VM_PRODUCT_INVENTORY_PRICE') ?></span>
        </div>
        <br/>
    <?php }
// Loop through each row and build the table
foreach( $products as $product ) { 		
    foreach( $product as $attr => $val ) {
        // Using this we make all the variables available in the template
        // translated example: $this->set( 'product_name', $product_name );
        $this->set( $attr, $val );
    }
    ?>
    <div class="vmCartChild<?php echo $cls_suffix." ".$product['bgcolor'].$cls_suffix ?>">
        <div class="vmCartChildElement<?php echo $cls_suffix ?>">
            <input type="hidden" name="prod_id[]" value="<?php echo $product['product_id'] ?>" />
            <input type="hidden" name="product_id" value="<?php echo $product['parent_id'] ?>" />
            <?php if( $child_link ) : ?>
            <label for="selItem<?php echo $product['product_id'] ?>">
            <?php endif; ?>
            <span class="vmChildDetail<?php echo $cls_suffix ?>" style="width: <?php echo $desc_width ?>;" />
                <?php echo $product['product_title'] ?></span>
            <?php // Ouput Each Attribute
			if( !empty( $product['attrib_value'] )) {
				foreach($product['attrib_value'] as $attribute) { ?>
					<span class="vmChildDetail<?php echo $cls_suffix ?>" style="width :<?php echo $attrib_width ?>;" />
					<?php echo " ".$attribute ?></span>
				<?php 
				}
			}
			?>
			 <?php if( $child_link ) : ?>
			</label>
			<?php endif; ?>
			<?php 
            // Output Quantity Box 
            if (USE_AS_CATALOGUE != '1' ) { ?>
                <span style="float: right;padding-right:5px;"><?php echo $product['quantity_box'] ?></span>
            <?php } 
            // Output Price 
            if( $_SESSION['auth']['show_prices'] && _SHOW_PRICES) { 
                ?>
                <span class="vmChildDetail<?php echo $cls_suffix ?>" style="float: right;text-align: right;padding-right:5px;" >
                <?php
                if( $product['price'] != $product['actual_price'] ) { ?>
                    <span class="product-Old-Price"><?php echo $product['price'] ?>&nbsp;</span>
                <?php 
				}
				?> 
                <span class="productPrice"><?php echo $product['actual_price'] ?></span>
				</span> <!-- close the vmChildDetail -->
            <?php } ?>

        </div>
        <br style="clear: both;"><?php
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
    </div>
<?php } ?>
<!-- Future Use -->
<input type="hidden" name="set_price[]" value="" />
<input type="hidden" name="adjust_price[]" value="" />
<input type="hidden" name="master_product[]" value="" />
</div >

