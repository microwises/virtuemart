<?php // no direct access
defined('_JEXEC') or die('Restricted access');
$col= 1 ;
?>
<div class="vmgroup<?php echo $params->get( 'moduleclass_sfx' ) ?>">

<?php if ($headerText) { ?>
	<div class="vmheader"><?php echo $headerText ?></div>
<?php }
if ($display_style =="div") { ?>
<div class="vmproduct<?php echo $params->get('moduleclass_sfx'); ?>">
<?php foreach ($products as $product) { ?>
	<div style="float:left;">
		<?php
		if (!empty($product->images[0]) ) {
			echo JHTML::_('link', JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$product->virtuemart_product_id.'&virtuemart_category_id='.$product->virtuemart_category_id),$product->images[0]->displayMediaThumb('class="featuredProductImage" border="0"',$product->product_name));
		}
		?>
			<?php echo JHTML::link(JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$product->virtuemart_product_id.'&virtuemart_category_id='.$product->virtuemart_category_id), $product->product_name, array('title' => $product->product_name)); ?>
		<?php
		if ($show_price) {
	// 		echo $currency->priceDisplay($product->prices['salesPrice']);
			if (!empty($product->prices['salesPrice'] ) ) echo $currency->createPriceDiv('salesPrice','',$product->prices,true);
	// 		if ($product->prices['salesPriceWithDiscount']>0) echo $currency->priceDisplay($product->prices['salesPriceWithDiscount']);
			if (!empty($product->prices['salesPriceWithDiscount']) ) echo $currency->createPriceDiv('salesPriceWithDiscount','',$product->prices,true);
		}

		?>
	</div>
	<?php
		if ($col == $products_per_row && $products_per_row && $col < $totalProd ) {
			echo "	</div><div style='clear:both;'>";
			$col= 1 ;
		} else {
			$col++;
		}
	} ?>
</div>
<br style='clear:both;' />

<?php
} else {
?>

<ul class="vmproduct<?php echo $params->get('moduleclass_sfx'); ?>">
<?php foreach ($products as $product) : ?>
	<li>
		<?php
		$productModel->addImages($product);
		echo $product->images[0]->displayMediaThumb('class="browseProductImage" border="0"');
		?>
			<?php echo JHTML::link(JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$product->virtuemart_product_id.'&virtuemart_category_id='.$product->virtuemart_category_id), $product->product_name, array('title' => $product->product_name,'rel'=>'facebox')); ?>
	<?php
		if ($show_price) {
			echo $currency->createPriceDiv('salesPrice','',$product->prices,true);
			if ($product->prices['salesPriceWithDiscount']>0) echo $currency->createPriceDiv('salesPriceWithDiscount','',$product->prices,true);
		}
		if ($show_addtocart) echo mod_virtuemart_product::addtocart($product);
		?>
	</li>
<?php
	if ($col == $products_per_row && $products_per_row) {
		echo "
		</ul>
		<ul>";
		$col= 1 ;
	} else {
		$col++;
	}
	endforeach; ?>
</ul>

<?php }
	if ($footerText) : ?>
	<div class="vmfooter<?php echo $params->get( 'moduleclass_sfx' ) ?>">
		 <?php echo $footerText ?>
	</div>
<?php endif; ?>
</div>