<?php if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 

foreach($attributes as $attribute) { 		
    foreach( $attribute as $attr => $val ) {
        // Using this we make all the variables available in the template
        // translated example: $this->set( 'product_name', $product_name );
        $this->set( $attr, $val );
    }
    ?>
    <div class="vmAttribChildDetail" style="float: left;width:30%;text-align:right;margin:3px;">
        <label for="<?php echo $attribute['titlevar'] ?>_field"><?php echo $attribute['title'] ?>
        </label>:
    </div>
    <div class="vmAttribChildDetail" style="float:left;width:60%;margin:3px;">
        <input type="text" class="inputboxattrib" id="<?php echo $attribute['titlevar'] ?>_field" size="30" name="<?php echo $attribute['titlevar'].$attribute['product_id'] ?>" />
    </div>
    <br style="clear: both;" />
    <input type="hidden" name="custom_attribute_fields[]" value="<?php echo $attribute['titlevar'].$attribute['product_id'] ?>" />
    <input type="hidden" name="custom_attribute_fields_check[<?php echo $attribute['titlevar'].$attribute['product_id'] ?>]" value="<?php echo md5($mosConfig_secret. $attribute['titlevar'].$attribute['product_id'] ) ?>" />
<?php } ?>
