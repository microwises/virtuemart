<?php // Access
defined ( '_JEXEC' ) or die ( 'Restricted access' );

// Category and Columns Counter
$iFeaturedCol = 1;
$iFeaturedProduct = 1;

// Calculating Products Per Row
$featuredProducts_per_row = VmConfig::get ( 'featured_products_per_row', 3 ) ;
$Featuredcellwidth = ' width'.floor ( 100 / $featuredProducts_per_row );

// Separator
$verticalseparator = " vertical-separator";
?>

<div class="featured-view">

	<h4><?php echo JText::_( 'VM_FEATURED_PRODUCT' ) ?></h4>
	
<?php // Start the Output
foreach ( $this->featuredProducts as $featProduct ) {
	
	// Show the horizontal seperator
	if ($iFeaturedCol == 1 && $iFeaturedProduct > $featuredProducts_per_row) { ?>
	<div class="horizontal-separator"></div>
	<?php }
	
	// this is an indicator wether a row needs to be opened or not
	if ($iFeaturedCol == 1) { ?>
	<div class="row">
	<?php }
	
	// Show the vertical seperator
	if ($iFeaturedProduct == $featuredProducts_per_row or $iFeaturedProduct % $featuredProducts_per_row == 0) {
		$show_vertical_separator = ' ';
	} else {
		$show_vertical_separator = $verticalseparator;
	} 
	
		// Show Products ?>
		<div class="product floatleft<?php echo $Featuredcellwidth . $show_vertical_separator ?>">
			<div class="spacer">
				<div class="width30 floatleft center">
					<?php // Product Image
					if ($featProduct->product_thumb_image) {
						echo JHTML::_ ( 'link', JRoute::_ ( 'index.php?option=com_virtuemart&view=productdetails&product_id=' . $featProduct->product_id . '&category_id=' . $featProduct->category_id ), VmImage::getImageByProduct ( $featProduct )->displayImage ( 'class="featuredProductImage" border="0"', $featProduct->product_name ) );
					}
					?>
				</div>

				<div class="width70 floatright">

					<h3>
					<?php // Product Name
					echo JHTML::link ( JRoute::_ ( 'index.php?option=com_virtuemart&view=productdetails&product_id=' . $featProduct->product_id . '&category_id=' . $featProduct->category_id ), $featProduct->product_name, array ('title' => $featProduct->product_name ) ); ?>
					</h3>

					<?php // Product Short Description
					if (! empty ( $featProduct->product_s_desc )) { ?>
					<p class="product_s_desc">
					<?php echo $featProduct->product_s_desc; ?>
					</p>
					<?php } ?>

					<div class="product-price marginbottom12">
					<?php
					if (VmConfig::get ( 'show_prices' ) == '1') {
					//				if( $featProduct->product_unit && VmConfig::get('vm_price_show_packaging_pricelabel')) {
					//						echo "<strong>". JText::_('VM_CART_PRICE_PER_UNIT').' ('.$featProduct->product_unit."):</strong>";
					//					} else echo "<strong>". JText::_('VM_CART_PRICE'). ": </strong>";
	
					if ($this->showBasePrice) {
						echo shopFunctionsF::createPriceDiv ( 'basePrice', 'VM_PRODUCT_BASEPRICE', $featProduct->prices );
						echo shopFunctionsF::createPriceDiv ( 'basePriceVariant', 'VM_PRODUCT_BASEPRICE_VARIANT', $featProduct->prices );
					}
					echo shopFunctionsF::createPriceDiv ( 'variantModification', 'VM_PRODUCT_VARIANT_MOD', $featProduct->prices );
					echo shopFunctionsF::createPriceDiv ( 'basePriceWithTax', 'VM_PRODUCT_BASEPRICE_WITHTAX', $featProduct->prices );
					echo shopFunctionsF::createPriceDiv ( 'discountedPriceWithoutTax', 'VM_PRODUCT_DISCOUNTED_PRICE', $featProduct->prices );
					echo shopFunctionsF::createPriceDiv ( 'salesPriceWithDiscount', 'VM_PRODUCT_SALESPRICE_WITH_DISCOUNT', $featProduct->prices );
					echo shopFunctionsF::createPriceDiv ( 'salesPrice', 'VM_PRODUCT_SALESPRICE', $featProduct->prices );
					echo shopFunctionsF::createPriceDiv ( 'priceWithoutTax', 'VM_PRODUCT_SALESPRICE_WITHOUT_TAX', $featProduct->prices );
					echo shopFunctionsF::createPriceDiv ( 'discountAmount', 'VM_PRODUCT_DISCOUNT_AMOUNT', $featProduct->prices );
					echo shopFunctionsF::createPriceDiv ( 'taxAmount', 'VM_PRODUCT_TAX_AMOUNT', $featProduct->prices );
					} ?>
					</div>
				
					<div>
					<?php // Product Details Button
					echo JHTML::link ( JRoute::_( 'index.php?option=com_virtuemart&view=productdetails&product_id=' . $featProduct->product_id . '&category_id=' . $featProduct->category_id ), JText::_ ( 'PRODUCT_DETAILS' ), array ('title' => $featProduct->product_name, 'class' => 'product-details' ) );
					?>
					</div>
				</div>
			<div class="clear"></div>
			</div>
		</div>
	<?php
	$iFeaturedProduct ++;
	
	// Do we need to close the current row now?
	if ($iFeaturedCol == $featuredProducts_per_row) { ?>
	<div class="clear"></div>
	</div>
		<?php 
		$iFeaturedCol = 1;
	} else {
		$iFeaturedCol ++;
	}
}
// Do we need a final closing row tag?
if ($iFeaturedCol != 1) { ?>
	<div class="clear"></div>
	</div>
<?php 
}
?>
</div>