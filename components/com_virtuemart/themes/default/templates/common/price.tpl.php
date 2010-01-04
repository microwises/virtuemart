<?php if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); ?>

<?php
// User is not allowed to see a price or there is no price
//if( !$auth['show_prices'] || !isset($price_info["product_price_id"] )) {
//
//	$link = $sess->url( $_SERVER['PHP_SELF'].'?page=shop.ask&amp;product_id='.$product_id.'&amp;subject='. urlencode( JText::_('VM_PRODUCT_CALL').": $product_name") );
//	echo vmCommonHTML::hyperLink( $link, JText::_('VM_PRODUCT_CALL') );
//}
?>


<?php
// DISCOUNT: Show old price!
if(!empty($discount_info["amount"])) {
	?>
	<span class="product-Old-Price">
		<?php echo $CURRENCY_DISPLAY->getFullValue($undiscounted_price); ?></span>

	<br/>
	<?php
}
?>
<?php
//if( !empty( $price_info["product_price_id"] )) { ?>
	<span class="productPrice">

		<?php echo $CURRENCY_DISPLAY->getFullValue($text_including_tax); if(isset($text_excluding_tax))echo ' '.$CURRENCY_DISPLAY->getFullValue($text_excluding_tax); ?>
	</span>
<?php
//}
echo $price_table;
?>


<?php
// DISCOUNT: Show the amount the customer saves
if(!empty($discount_info["amount"])) {
	echo "<br />";
	echo JText::_('VM_PRODUCT_DISCOUNT_SAVE').": ";
	if($discount_info["is_percent"]==1) {
		echo $discount_info["amount"]."%";
	}
	else {
		echo $CURRENCY_DISPLAY->getFullValue($discount_info["amount"]);
	}
}
?>