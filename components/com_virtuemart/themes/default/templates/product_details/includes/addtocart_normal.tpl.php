<?php if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); ?>

<div class="vmCartDetails<?php echo $cls_suffix; ?>">

<?php 
foreach( $products as $product ) { 		

    foreach( $product as $attr => $val ) {
			// Using this we make all the variables available in the template
			// translated example: $this->set( 'product_name', $product_name );
			$this->set( $attr, $val );
    }
}
if(USE_AS_CATALOGUE != '1' && ($product['advanced_attribute'] != "" || $product['custom_attribute'] !="")) { ?>
    <div class="vmCartChild<?php echo $cls_suffix ?> vmRowTwo<?php echo $cls_suffix ?>">
  	<?php 
}
?>
            <input type="hidden" name="prod_id[]" value="<?php echo $product_id ?>" />
            <input type="hidden" name="product_id" value="<?php echo $product_id ?>" />
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
