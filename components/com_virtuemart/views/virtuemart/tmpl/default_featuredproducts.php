<?php defined('_JEXEC') or die('Restricted access'); ?>
	<div class="featured-view">
	<?php
	$iTopTen = 1;


	// calculation of the categories per row
	$featured_products_per_row = 3;	
	$featuredcellwidth = floor( 100 / $featured_products_per_row);

	echo "<h4>".JText::_('VM_FEATURED_PRODUCT')."</h4>";


	foreach ($this->featuredProducts as $featProduct) {

		if ($iTopTen == 1) { // this is an indicator wether a row needs to be opened or not ?>
		<div class="featured-row">
		<?php }
		?>

			<!-- Product Listing Output -->
			<div class="width<?php echo $featuredcellwidth ?> floatleft">

				<div>
					<div class="width30 floatleft center">

			<?php
						if ($featProduct->product_thumb_image) {
					echo JHTML::_('link', JRoute::_('index.php?option=com_virtuemart&view=productdetails&product_id='.$featProduct->product_id.'&category_id='.$featProduct->category_id),VmImage::getImageByProduct($featProduct)->displayImage('class="featuredProductImage" border="0"',$featProduct->product_name));
						}
			?>

					</div>

					<div class="width70 floatright">

						<h3><?php echo JHTML::link(JRoute::_('index.php?option=com_virtuemart&view=productdetails&product_id='.$featProduct->product_id.'&category_id='.$featProduct->category_id), $featProduct->product_name, array('title' => $featProduct->product_name)); ?></h3>

						<?php // Product Short Description
						if(!empty($featProduct->product_s_desc)) { ?>
						<p class="product_s_desc">
						<?php echo $featProduct->product_s_desc; ?>
						</p>
						<?php } ?>


						<div class="product-price marginbottom12">
<?php	if (VmConfig::get('show_prices') == '1') {
//				if( $featProduct->product_unit && VmConfig::get('vm_price_show_packaging_pricelabel')) {
//						echo "<strong>". JText::_('VM_CART_PRICE_PER_UNIT').' ('.$featProduct->product_unit."):</strong>";
//					} else echo "<strong>". JText::_('VM_CART_PRICE'). ": </strong>";


					if( $this->showBasePrice ){
						echo shopFunctionsF::createPriceDiv('basePrice','VM_PRODUCT_BASEPRICE',$featProduct->prices);
						echo shopFunctionsF::createPriceDiv('basePriceVariant','VM_PRODUCT_BASEPRICE_VARIANT',$featProduct->prices);
					}
					echo shopFunctionsF::createPriceDiv('variantModification','VM_PRODUCT_VARIANT_MOD',$featProduct->prices);
					echo shopFunctionsF::createPriceDiv('basePriceWithTax','VM_PRODUCT_BASEPRICE_WITHTAX',$featProduct->prices);
					echo shopFunctionsF::createPriceDiv('discountedPriceWithoutTax','VM_PRODUCT_DISCOUNTED_PRICE',$featProduct->prices);
					echo shopFunctionsF::createPriceDiv('salesPriceWithDiscount','VM_PRODUCT_SALESPRICE_WITH_DISCOUNT',$featProduct->prices);
					echo shopFunctionsF::createPriceDiv('salesPrice','VM_PRODUCT_SALESPRICE',$featProduct->prices);
					echo shopFunctionsF::createPriceDiv('priceWithoutTax','VM_PRODUCT_SALESPRICE_WITHOUT_TAX',$featProduct->prices);
					echo shopFunctionsF::createPriceDiv('discountAmount','VM_PRODUCT_DISCOUNT_AMOUNT',$featProduct->prices);
					echo shopFunctionsF::createPriceDiv('taxAmount','VM_PRODUCT_TAX_AMOUNT',$featProduct->prices);
} ?>
	</div>
						<div>
						<?php // Product Details Button
						echo JHTML::link(JRoute::_('index.php?option=com_virtuemart&view=productdetails&product_id='.$featProduct->product_id.'&category_id='.$featProduct->category_id), JText::_('PRODUCT_DETAILS'), array('title' => $featProduct->product_name,'class' => 'product-details'));
				?>
						</div>



		</div>




				<div class="clear"></div>
				</div>


		</div>

		<?php
		// Do we need to close the current row now?
		if ($iTopTen == $featured_products_per_row) { // If the number of products per row has been reached
			echo "<div class='clear'></div></div>";
			$iTopTen = 1;
		}
		else {
			$iTopTen++;
	}
			}
	// Do we need a final closing row tag?
	if ($iTopTen != 1) {
		echo "<div class='clear'></div></div>";
	}
	?>
	<div class="clear"></div>
	</div>