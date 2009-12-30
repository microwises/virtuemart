<?php if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
mm_showMyFileName(__FILE__);
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

<?php //echo('//TODO this button exist more than one time') ?>
<form action="<?php echo $mm_action_url ?>index.php" method="post" name="addtocart" id="addtocart<?php echo $i ?>" class="addtocart_form" <?php if( $this->get_cfg( 'useAjaxCartActions', 1 ) && !$notify ) { echo 'onsubmit="handleAddToCart( this.id );return false;"'; } ?>>
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