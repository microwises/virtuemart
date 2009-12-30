<?php if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
mm_showMyFileName(__FILE__);
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'image.php');
 ?>
 <div class="browseProductContainer">
  <h2>
        <a style="font-size: 16px; font-weight: bold;" href="<?php echo $product_flypage ?>"><?php echo $product_name ?></a>
  </h2>

  <p><?php
	echo $product_price;
	//ct //show the ex tax when inc
	if ($product_price_without_tax != ""){echo "<br/>".$product_price_without_tax;}
	if ($product_price_with_tax != ""){echo "<br/>".$product_price_with_tax;}
  ?></p>
  <div style="float:left;width:90%" >
  		<a href="<?php echo $product_flypage ?>" title="<?php echo $product_name ?>">
         <?php /*echo ps_product::image_tag( $product_thumb_image, 'class="browseProductImage" border="0" title="'.$product_name.'" alt="'.$product_name .'"' ) */?>
         <?php ImageHelper::displayShopImage($product_thumb_image, 'product', 'class="browseProductImage" border="0" title="'.$product_name.'" alt="'.$product_name .'"'); ?>
        </a>
   </div>

  <br style="clear:both;" />

  <p><?php echo $product_s_desc ?><br />
      <a href="<?php echo $product_flypage ?>" title="<?php echo $product_name ?>"><?php echo $product_details ?>&nbsp;<strong><?php echo $product_name ?></strong></a>
  </p>
   <p><?php echo $product_rating ?></p>
   <p><?php echo $stock_level ?></p>
  <p><?php echo $form_addtocart ?></p>
</div>
