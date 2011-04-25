<?php
/**
*
* Description layout for toptenproducts
*
* @package	VirtueMart
* @subpackage
* @author
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2011 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: view.html.php 3051 2011-04-17 21:04:06Z Milbo $
*/
// Access
defined ( '_JEXEC' ) or die ( 'Restricted access' );

// Category and Columns Counter
$iTopTenCol = 1;
$iTopTenProduct = 1;

// Calculating Products Per Row
$TopTenProducts_per_row = VmConfig::get ( 'topten_products_per_row', 3 ) ;
$TopTen_cellwidth = ' width'.floor ( 100 / $TopTenProducts_per_row );

// Separator
$verticalseparator = " vertical-separator";
?>

<div class="topten-view">

	<h4><?php echo JText::_('COM_VIRTUEMART_TOPTEN_PRODUCT') ?></h4>

<?php // Start the Output
foreach ( $this->toptenProducts as $topProduct ) {

	// Show the horizontal seperator
	if ($iTopTenCol == 1 && $iTopTenProduct > $TopTenProducts_per_row) { ?>
	<div class="horizontal-separator"></div>
	<?php }

	// this is an indicator wether a row needs to be opened or not
	if ($iTopTenCol == 1) { ?>
	<div class="row">
	<?php }

	// Show the vertical seperator
	if ($iTopTenProduct == $TopTenProducts_per_row or $iTopTenProduct % $TopTenProducts_per_row == 0) {
		$show_vertical_separator = ' ';
	} else {
		$show_vertical_separator = $verticalseparator;
	}

		// Show Products ?>
		<div class="product floatleft<?php echo $TopTen_cellwidth . $show_vertical_separator ?>">
			<div class="spacer">
				<div class="width30 floatleft center">
					<?php // Product Image
					if ($topProduct->images) {
						echo JHTML::_ ( 'link', JRoute::_ ( 'index.php?option=com_virtuemart&view=productdetails&product_id=' . $topProduct->product_id . '&category_id=' . $topProduct->category_id ), $topProduct->images[0]->displayMediaThumb( 'class="featuredProductImage" border="0"' ) );
					}
					?>
				</div>

				<div class="width70 floatright">

					<h3>
					<?php // Product Name
					echo JHTML::link ( JRoute::_ ( 'index.php?option=com_virtuemart&view=productdetails&product_id=' . $topProduct->product_id . '&category_id=' . $topProduct->category_id ), $topProduct->product_name, array ('title' => $topProduct->product_name ) ); ?>
					</h3>

					<?php // Product Short Description
					if (! empty ( $topProduct->product_s_desc )) { ?>
					<p class="product_s_desc">
					<?php echo shopFunctionsF::limitStringByWord($topProduct->product_s_desc, 40, '...') ?>
					</p>
					<?php } ?>

					<div class="product-price marginbottom12">
					<?php
					if (VmConfig::get ( 'show_prices' ) == '1') {
					//				if( $featProduct->product_unit && VmConfig::get('vm_price_show_packaging_pricelabel')) {
					//						echo "<strong>". JText::_('COM_VIRTUEMART_CART_PRICE_PER_UNIT').' ('.$featProduct->product_unit."):</strong>";
					//					} else echo "<strong>". JText::_('COM_VIRTUEMART_CART_PRICE'). ": </strong>";

					if ($this->showBasePrice) {
						echo shopFunctionsF::createPriceDiv ( 'basePrice', 'COM_VIRTUEMART_PRODUCT_BASEPRICE', $topProduct->prices );
						echo shopFunctionsF::createPriceDiv ( 'basePriceVariant', 'COM_VIRTUEMART_PRODUCT_BASEPRICE_VARIANT', $topProduct->prices );
					}
					echo shopFunctionsF::createPriceDiv ( 'variantModification', 'COM_VIRTUEMART_PRODUCT_VARIANT_MOD', $topProduct->prices );
					echo shopFunctionsF::createPriceDiv ( 'basePriceWithTax', 'COM_VIRTUEMART_PRODUCT_BASEPRICE_WITHTAX', $topProduct->prices );
					echo shopFunctionsF::createPriceDiv ( 'discountedPriceWithoutTax', 'COM_VIRTUEMART_PRODUCT_DISCOUNTED_PRICE', $topProduct->prices );
					echo shopFunctionsF::createPriceDiv ( 'salesPriceWithDiscount', 'COM_VIRTUEMART_PRODUCT_SALESPRICE_WITH_DISCOUNT', $topProduct->prices );
					echo shopFunctionsF::createPriceDiv ( 'salesPrice', 'COM_VIRTUEMART_PRODUCT_SALESPRICE', $topProduct->prices );
					echo shopFunctionsF::createPriceDiv ( 'priceWithoutTax', 'COM_VIRTUEMART_PRODUCT_SALESPRICE_WITHOUT_TAX', $topProduct->prices );
					echo shopFunctionsF::createPriceDiv ( 'discountAmount', 'COM_VIRTUEMART_PRODUCT_DISCOUNT_AMOUNT', $topProduct->prices );
					echo shopFunctionsF::createPriceDiv ( 'taxAmount', 'COM_VIRTUEMART_PRODUCT_TAX_AMOUNT', $topProduct->prices );
					} ?>
					</div>

					<div>
					<?php // Product Details Button
					echo JHTML::link ( JRoute::_ ( 'index.php?option=com_virtuemart&view=productdetails&product_id=' . $topProduct->product_id . '&category_id=' . $topProduct->category_id ), JText::_ ( 'COM_VIRTUEMART_PRODUCT_DETAILS' ), array ('title' => $topProduct->product_name, 'class' => 'product-details' ) );
					?>
					</div>
				</div>
			<div class="clear"></div>
			</div>
		</div>
	<?php
	$iTopTenProduct ++;

	// Do we need to close the current row now?
	if ($iTopTenCol == $TopTenProducts_per_row) { ?>
	<div class="clear"></div>
	</div>
		<?php
		$iTopTenCol = 1;
	} else {
		$iTopTenCol ++;
	}
}
// Do we need a final closing row tag?
if ($iTopTenCol != 1) { ?>
	<div class="clear"></div>
	</div>
<?php
}
?>
</div>