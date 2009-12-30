<?php if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'image.php');
?>

<!-- The product name DIV. -->
 <?php if( $show_product_name ) : ?>
<div style="height:77px; float:left; width: 100%;line-height:14px;">
<a title="<?php echo $product_name ?>" href="<?php echo $product_link ?>"><?php echo $product_name; ?></a>
<br />
</div>
<?php endif;?>

<!-- The product image DIV. -->
<div style="height:90px;width: 100%;float:left;margin-top:-15px;">
<a title="<?php echo $product_name ?>" href="<?php echo $product_link ?>">
	<?php
		// Print the product image or the "no image available" image
		//echo ps_product::image_tag( $product_thumb_image, "alt=\"".$product_name."\"");
		ImageHelper::displayShopImage($product_thumb_image, 'product', 'alt="'.$product_name .'"');
	?>
</a>
</div>

<!-- The product price DIV. -->
<div style="width: 100%;float:left;text-align:center;">
<?php
if( !empty($price) ) {
	echo $price;
}
?>
</div>

<!-- The add to cart DIV. -->
<div style="float:left;text-align:center;width: 100%;">
<?php
if( !empty($addtocart_link) ) {
	?>
	<br />
	<form action="<?php echo  $mm_action_url ?>index.php" method="post" name="addtocart" id="addtocart">
    <input type="hidden" name="option" value="com_virtuemart" />
    <input type="hidden" name="page" value="shop.cart" />
    <input type="hidden" name="Itemid" value="<?php echo ps_session::getShopItemid(); ?>" />
    <input type="hidden" name="func" value="cartAdd" />
    <input type="hidden" name="prod_id" value="<?php echo $product_id; ?>" />
    <input type="hidden" name="product_id" value="<?php echo $product_id ?>" />
    <input type="hidden" name="quantity" value="1" />
    <input type="hidden" name="set_price[]" value="" />
    <input type="hidden" name="adjust_price[]" value="" />
    <input type="hidden" name="master_product[]" value="" />
    <input type="submit" class="addtocart_button_module" value="<?php echo JText::_('VM_CART_ADD_TO') ?>" title="<?php echo JText::_('VM_CART_ADD_TO') ?>" />
    </form>
	<br />
	<?php
}
?>

</div>