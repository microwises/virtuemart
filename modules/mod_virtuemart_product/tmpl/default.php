<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 
$col= 1 ;
?>
<div class="vmgroup<?php echo $params->get( 'moduleclass_sfx' ) ?>">

<?php if ($headerText) : ?>
	<div class="vmheader"><?php echo $headerText ?></div>
<?php endif; 
if ($display_style =="div") { ?>
<div class="vmproduct<?php echo $params->get('moduleclass_sfx'); ?>">
<?php foreach ($products as $product) : ?>
<div style="float:left;">
	<?php
	if ($product->product_thumb_image) {
		echo JHTML::_('link', JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$product->virtuemart_product_id.'&virtuemart_category_id='.$product->virtuemart_category_id),VmImage::getImageByProduct($product)->displayImage('class="featuredProductImage" border="0"',$product->product_name));
	}
	?>
		<?php echo JHTML::link(JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$product->virtuemart_product_id.'&virtuemart_category_id='.$product->virtuemart_category_id), $product->product_name, array('title' => $product->product_name)); ?>
	<?php if ($show_price) { echo shopFunctionsF::createPriceDiv('salesPrice','',$product->prices); 
		echo shopFunctionsF::createPriceDiv('salesPriceWithDiscount','VM_PRODUCT_SALESPRICE_WITH_DISCOUNT',$product->prices);
	}
	?>
</div>
<?php 
if ($col == $products_per_row && $products_per_row && $col < $totalProd ) {
	echo "</div><div style='clear:both;'>";
	$col= 1 ;
} else { 
	$col++; 
}
endforeach; ?>
</div>
<br style='clear:both;' />

<?php 
} else {
?>

<ul class="vmproduct<?php echo $params->get('moduleclass_sfx'); ?>">
<?php foreach ($products as $product) : ?>
<li>
	<?php
	if ($product->product_thumb_image) {
		echo JHTML::_('link', JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$product->virtuemart_product_id.'&virtuemart_category_id='.$product->virtuemart_category_id),VmImage::getImageByProduct($product)->displayImage('class="featuredProductImage" border="0"',$product->product_name));
	}
	?>
		<?php echo JHTML::link(JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$product->virtuemart_product_id.'&virtuemart_category_id='.$product->virtuemart_category_id), $product->product_name, array('title' => $product->product_name)); ?>
	<?php if ($show_price) { echo shopFunctionsF::createPriceDiv('salesPrice','',$product->prices); 
		echo shopFunctionsF::createPriceDiv('salesPriceWithDiscount','VM_PRODUCT_SALESPRICE_WITH_DISCOUNT',$product->prices);
	}
	?>
</li>
<?php 
	if ($col == $products_per_row && $products_per_row) {
		echo "</ul><ul>";
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