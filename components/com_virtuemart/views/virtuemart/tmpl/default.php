<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage 
* @author
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id$
*/
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
?>

<?php // Vendor Store Description
if (!empty($this->vendor->vendor_store_desc)) { ?>
<div class="vendor-store-desc">
	<h1><?php echo JText::_('VM_STORE_FORM_DESCRIPTION') ?></h1>
	<?php /** @todo Add vendor description */
	echo $this->vendor->vendor_store_desc; ?>
</div>
<?php } ?>

<?php // Listing the Categories
if ($this->categories) { ?>
	<div class="category-view">
	<?php
	echo "<h4>".JText::_('VM_CATEGORIES')."</h4>";
	
	$iCol = 1;
	
	// calculation of the categories per row
	$categories_per_row = VmConfig::get('categories_per_row',1);
	$cellwidth = floor( 100 / $categories_per_row);


foreach ($this->categories as $category) {

		if ($iCol == 1) { // this is an indicator wether a row needs to be opened or not ?>
		<div class="category-row">
		<?php }
			?>
		
		<!-- Category Listing Output -->
		<div class="width<?php echo $cellwidth ?> floatleft center">
			<?php $caturl = JRoute::_('index.php?option=com_virtuemart&view=category&category_id='.$category->category_id); ?>
			<h2>
				<a href="<?php echo $caturl ?>" title="<?php echo $category->category_name ?>">
				<?php echo $category->category_name ?><span><?php // echo ' ()'?></span><br />
				<?php if ($category->category_thumb_image) {
					echo VmImage::getImageByCat($category)->displayImage();
				} ?>
				</a>	
			</h2>
		</div>

			<?php
		// Do we need to close the current row now?
		if ($iCol == $categories_per_row) { // If the number of products per row has been reached
			echo "<div class='clear'></div></div>";
			$iCol = 1;
		}
		else {
			$iCol++;
		}
	}
	// Do we need a final closing row tag?
	if ($iCol != 1) {
		echo "<div class='clear'></div></div>";
		}
		?>
	<div class="clear"></div>
	</div>
<?php } ?>

<?php // show featured products
if (VmConfig::get('showFeatured', 1) && !empty($this->featuredProducts)) {
	?>
	<div class="featured-view">
	<?php
	$iFeatured = 1;
	
	
	// calculation of the categories per row
	$featured_products_per_row = 3;	
	$featuredcellwidth = floor( 100 / $featured_products_per_row);
	
	echo "<h4>".JText::_('VM_FEATURED_PRODUCT')."</h4>";
	
	
	foreach ($this->featuredProducts as $featProduct) {
		
		if ($iFeatured == 1) { // this is an indicator wether a row needs to be opened or not ?>
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
		if ($iFeatured == $featured_products_per_row) { // If the number of products per row has been reached
			echo "<div class='clear'></div></div>";
			$iFeatured = 1;
		}
		else {
			$iFeatured++;
	} 
			} 
	// Do we need a final closing row tag?
	if ($iFeatured != 1) {
		echo "<div class='clear'></div></div>";
	}
	?>
	<div class="clear"></div>
	</div>
<?php } ?>

		<?php 
/* Recent products */
if ($this->recentProducts) {
				?>
	<div class="vmRecent">
		<!-- List of recent products -->
		<h3><?php echo JText::_('VM_RECENT_PRODUCTS') ?></h3>
		<ul class="vmRecentDetail">
				<?php
		foreach ($this->recentProducts as $recent ) { // Loop through all recent products
			/**
			 * Available indexes:
			 * 
			 * $recent["product_name"] => The user ID of the comment author
			 * $recent["category_name"] => The username of the comment author
			 * $recent["product_thumb_image"] => The name of the comment author
			 * $recent["product_url"] => The UNIX timestamp of the comment ("when" it was posted)
			 * $recent["category_url"] => The rating; an integer from 1 - 5
			 * $recent["product_s_desc"] => The comment text
			 * 
			 */
				?>
			<li>
			<a href="<?php echo $recent["product_url"]; ?>" >
			<?php echo $recent["product_name"]; ?></a>&nbsp;(<?php echo JText::_('VM_CATEGORY') ?>:&nbsp;
			<a href="<?php echo $recent["category_url"]; ?>" ><?php echo $recent["category_name"]; ?></a>)
			</li>
			<?php
		}
		?>
		</ul>
	</div>
	<?php
}
?>