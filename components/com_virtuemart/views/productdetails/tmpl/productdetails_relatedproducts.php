<?php if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'image.php');
$related = JRequest::getVar('related');
?>
<!--
$related_products = '';
		if( $db->num_rows() > 0 ) {
		$tpl->set( 'ps_product', $ps_product );
		$tpl->set( 'products', $db );
		$related_products = $tpl->fetch( '/common/relatedProducts.tpl.php' );
		}
		-->
<hr/>
<h3><?php echo JText::_('VM_RELATED_PRODUCTS_HEADING') ?></h3>
 
<table width="100%" align="center">
	<tr>
     	<td valign="top">
      		<!-- The product name DIV. -->
			<div style="height:77px; float:left; width: 100%;line-height:14px;">
			<?php echo JHTML::_('link', $related->link, $related->product_name); ?> 
			<br />
			</div>
			
			<!-- The product image DIV. -->
			<div style="height:90px;width: 100%;float:left;margin-top:-15px;">
				<?php 
					$img = ImageHelper::getShopImageHtml($related->product_thumb_image, 'product', 'alt="'.$related->product_name .'", title="'.$related->product_name.'"');
					echo JHTML::_('link', $related->link, $img); 
				?>
			</div>
			
			<!-- The product price DIV. -->
			<div style="width: 100%;float:left;text-align:center;">
				<?php /** @todo Format pricing */ ?>
				<?php if (is_array($related->price)) echo $related->price['salesPrice']; ?>
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
      	</td>
    </tr>
</table> 