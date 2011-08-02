<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $iTopTenCol = 1;
	//Number of featured products to show per row
	$product_per_row = 2;
	//Set the cell width
	$TopTen_cellwidth = (int)( (100 / $product_per_row) - 2 );

	echo "<h3>".JText::_('COM_VIRTUEMART_LATEST_PRODUCT')."</h3>";
	foreach ($this->latestProducts as $latestProduct ) {
		?>
		<div style="float:left;width:<?php echo $TopTen_cellwidth ?>%;text-align:top;padding:0px;" >
			<?php
			echo JHTML::_('link', JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$latestProduct->virtuemart_product_id.'&virtuemart_category_id='.$latestProduct->virtuemart_category_id), $latestProduct->product_name);
			?>
				<h4><?php echo $latestProduct->product_name; ?></h4>
				<?php 			if (VmConfig::get('show_prices') == '1') {
//				if( $latestProduct->product_unit && VmConfig::get('vm_price_show_packaging_pricelabel')) {
//						echo "<strong>". JText::_('COM_VIRTUEMART_CART_PRICE_PER_UNIT').' ('.$latestProduct->product_unit."):</strong>";
//					} else echo "<strong>". JText::_('COM_VIRTUEMART_CART_PRICE'). ": </strong>";

					//todo add config settings
					if( Permissions::getInstance()->check('admin')){
						echo $this->currency->createPriceDiv('basePrice','COM_VIRTUEMART_PRODUCT_BASEPRICE',$latestProduct->prices);
						echo $this->currency->createPriceDiv('basePriceVariant','COM_VIRTUEMART_PRODUCT_BASEPRICE_VARIANT',$latestProduct->prices);
					}
					echo $this->currency->createPriceDiv('variantModification','COM_VIRTUEMART_PRODUCT_VARIANT_MOD',$latestProduct->prices);
					echo $this->currency->createPriceDiv('basePriceWithTax','COM_VIRTUEMART_PRODUCT_BASEPRICE_WITHTAX',$latestProduct->prices);
					echo $this->currency->createPriceDiv('discountedPriceWithoutTax','COM_VIRTUEMART_PRODUCT_DISCOUNTED_PRICE',$latestProduct->prices);
					echo $this->currency->createPriceDiv('salesPriceWithDiscount','COM_VIRTUEMART_PRODUCT_SALESPRICE_WITH_DISCOUNT',$latestProduct->prices);
					echo $this->currency->createPriceDiv('salesPrice','COM_VIRTUEMART_PRODUCT_SALESPRICE',$latestProduct->prices);
					echo $this->currency->createPriceDiv('priceWithoutTax','COM_VIRTUEMART_PRODUCT_SALESPRICE_WITHOUT_TAX',$latestProduct->prices);
					echo $this->currency->createPriceDiv('discountAmount','COM_VIRTUEMART_PRODUCT_DISCOUNT_AMOUNT',$latestProduct->prices);
					echo $this->currency->createPriceDiv('taxAmount','COM_VIRTUEMART_PRODUCT_TAX_AMOUNT',$latestProduct->prices);
			}
				if ($latestProduct->virtuemart_media_id) {
					echo JHTML::_('link', JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$latestProduct->virtuemart_product_id.'&virtuemart_category_id='.$latestProduct->virtuemart_category_id),
						$latestProduct->images[0]->displayMediaThumb('class="browseProductImage" border="0"',true,'class="modal"'));
				?>
				<br /><br/>
				<?php } ?>
				<?php echo shopFunctionsF::limitStringByWord($latestProduct->product_s_desc, 40, '...') ?>
				<br />

				<?php //echo addToCart($latestProduct); ?>
		</div>
		<?php
		// Do we need to close the current row now?
		if ($iTopTenCol == $product_per_row) { // If the number of products per row has been reached
			echo "<br style=\"clear:both;\" />\n";
			$iTopTenCol = 1;
		}
		else {
			$iTopTenCol++;
		}
	}
	?>
	<br style="clear:both;" />
