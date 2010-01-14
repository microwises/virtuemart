<?php if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/* Load the product details */
$product = JRequest::getVar('product');

$call_for_pricing = false;
if ($product->product_price['salesPrice'] == JText::_('CALL_FOR_PRICING')) $call_for_pricing = true;
$button_lbl = JText::_('VM_CART_ADD_TO');
$button_cls = 'addtocart_button';
if (VmConfig::get('check_stock') == '1' 
	&& !$product->product_in_stock 
	&& !$product->haschildren 
	&& !$product->hasattributes
	&& !$call_for_pricing) {
		$button_lbl = JText::_('VM_CART_NOTIFY');
		$button_cls = 'notify_button';
		$notify = true;
} 
/* The details and options button lead to the product detail page as customer has to make extra selections before adding to cart */
else if ($product->haschildren
	|| $product->hasattributes
	|| $call_for_pricing) {
		if($call_for_pricing) {
			$button_lbl = JText::_('DETAILS');
			$button_cls = 'details_button';
		} 
		else {
			$button_lbl = JText::_('OPTIONS');
			$button_cls = 'options_button';
		}
		$notify = true;
}
else $notify = false;

/* Make the form */
?>
<form action="<?php echo JRoute::_('index.php?option=com_virtuemart&view=productdetails&product_id='.$product->product_id.'&flypage='.$product->flypage); ?>" method="post" name="addtocart" id="addtocart<?php echo $product->product_id ?>" class="addtocart_form" <?php if( VmConfig::get('useAjaxCartActions', 1) && !$notify ) { echo 'onsubmit="handleAddToCart( this.id );return false;"'; } ?>>
	<?php if(!$notify) { echo shopFunctions::showQuantityBox($product); } ?>
	<input type="submit" class="<?php echo $button_cls ?>" value="<?php echo $button_lbl	?>" title="<?php echo $button_lbl ?>" />
    <input type="hidden" name="category_id" value="<?php echo  JRequest::getInt('category_id'); ?>" />
    <input type="hidden" name="product_id" value="<?php echo $product->product_id; ?>" />
    <input type="hidden" name="prod_id[]" value="<?php echo $product->product_id; ?>" />
    <input type="hidden" name="flypage" value="<?php echo $product->flypage; ?>" />
    <input type="hidden" name="page" value="shop.cart" />
    <input type="hidden" name="func" value="cartadd" />
    <input type="hidden" name="Itemid" value="<?php echo JRequest::getInt('Itemid'); ?>" />
 	<input type="hidden" name="option" value="com_virtuemart" />
    <input type="hidden" name="set_price[]" value="" />
    <input type="hidden" name="adjust_price[]" value="" />
    <input type="hidden" name="master_product[]" value="" />
</form>