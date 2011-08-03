<?php // no direct access
defined('_JEXEC') or die('Restricted access');
// Ajax is displayed in vm_cart_products
// ALL THE DISPLAY IS Done by Ajax using "hiddencontainer" ?>

<!-- Virtuemart 2 Ajax Card -->
<div class="vmCartModule">
<?php
if ($show_product_list) {
	?>
	<div id="hiddencontainer" style=" display: none; ">
		<div class="container">
			<?php if ($show_price) { ?>
			  <div class="prices" style="float: right;"></div>
			<?php } ?>
			<div class="product_row">
				<span class="quantity"></span>&nbsp;x&nbsp;<span class="product_name"></span>
			</div>

			<div class="product_attributes"></div>
		</div>
	</div>
<div class="vm_cart_products">
</div>
<?php
}
?>
<div class="total" style="float: right;"></div>
<div class="total_products"><img src="components/com_virtuemart/assets/images/vmgeneral/cart-loading.gif"><img src="components/com_virtuemart/assets/images/vmgeneral/cart-loading.gif"></div>
<div class="show_cart"></div>
<noscript>
<?php echo JText::_('MOD_VIRTUEMART_CART_AJAX_CART_PLZ_JAVASCRIPT') ?>
</noscript>
</div>

