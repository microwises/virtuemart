<?php if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
mm_showMyFileName(__FILE__);
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'image.php');
 ?>
 <div class="browseProductContainer">


        <h3 class="browseProductTitle"><a title="<?php echo $product_name ?>" href="<?php echo $product_flypage ?>">
            <?php echo $product_name ?></a>
        </h3>

        <div class="browsePriceContainer">
            <?php
            	echo $product_price;
				//ct //show the ex tax when inc
				if ($product_price_without_tax != ""){echo "<br/>".$product_price_without_tax;}
				if ($product_price_with_tax != ""){echo "<br/>".$product_price_with_tax;}
			?>
        </div>

        <div class="browseProductImageContainer">
	        <script type="text/javascript">//<![CDATA[
	        document.write('<a href="javascript:void window.open(\'<?php echo $product_full_image ?>\', \'win2\', \'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=<?php echo $full_image_width ?>,height=<?php echo $full_image_height ?>,directories=no,location=no\');">');
	        document.write( '<?php ImageHelper::displayShopImage($product_thumb_image, "product", 'class="browseProductImage" border="0" title="'.$product_name.'" alt="'.$product_name .'"'); ?></a>' );
	        //]]>
	        </script>
	        <noscript>
	            <a href="<?php echo $product_full_image ?>" target="_blank" title="<?php echo $product_name ?>">
	            <?php /* echo ps_product::image_tag( $product_thumb_image, 'class="browseProductImage" border="0" title="'.$product_name.'" alt="'.$product_name .'"' ) */?>
	            <?php ImageHelper::displayShopImage($product_thumb_image, 'product', 'class="browseProductImage" border="0" title="'.$product_name.'" alt="'.$product_name .'"'); ?>
	            </a>
	        </noscript>
        </div>

        <div class="browseRatingContainer">
        <?php echo $product_rating ?>
        </div>
        <div class="browseProductDescription">
            <?php echo $product_s_desc ?>&nbsp;
			<a href="<?php echo $product_flypage ?>" title="<?php echo $product_name ?>"><?php echo $product_details ?>&nbsp;<strong><?php echo $product_name ?></strong></a>        </div>
        <br />
        <div >
    		<?php echo $stock_level ?>
    	</div>
        <span class="browseAddToCartContainer">
        <?php echo $form_addtocart ?>
        </span>

</div>
